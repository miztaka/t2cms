<?php

/**
 * Admin_Cms_Create
 */
class Admin_Cms_Create extends AdminActionBase
{
    public static function actionName() {
        return strtolower(__CLASS__);
    }

    // 特定のメソッド時にValidationを実行したいときに定義。
    const VALIDATION_TARGET = "doRegist";
    
    // Validationを実行したいときに定義。(ex. sample/Validator.php)
    const VALIDATION_CONFIG = '
"publish_flg.公開ステータス":
    required: {}
"publish_start_dt_ar.公開開始日":
    datetimehash: {}
"publish_end_dt_ar.公開終了日":
    datetimehash: {}
    ';
    
    // Converterを実行したいときに定義。(ex. sample/Converter.php)
    const CONVERTER_CONFIG = '
__all:
  trim: {}
publish_start_dt_ar:
    datetimehash: { target: publish_start_dt }
publish_end_dt_ar:
    datetimehash: { target: publish_end_dt }
    ';
    
    /**
     * @var Teeple_Converter_Todatetimehash
     */
    private $converter;
    public function setComponent_Teeple_Converter_Todatetimehash($c) {
        $this->converter = $c;
    }
    
    /**
     * @var Teeple_Cms_FormValidator
     */
    private $cmsValidator;
    public function setComponent_Teeple_Cms_FormValidator($c) {
        $this->cmsValidator = $c;
    }
    
    /**
     * @var Logic_FileUpload
     */
    private $uploadLogic;
    public function setComponent_Logic_FileUpload($c) {
        $this->uploadLogic = $c;
    }
    
    /**
     * 
     * @var Teeple_EavRecord
     */
    public $_record;
    
    /**
     * 標準で実行されるメソッドです。
     */
    public function execute() {
        
        if (Teeple_Util::isBlank($this->id)) {
            if (Teeple_Util::isBlank($this->meta_entity_id)) {
                throw new Exception("不正なアクセスです。");
            }
            $this->crudType = "C";
            $this->publish_flg = 1;
            $this->_record = new Teeple_EavRecord($this->meta_entity_id);
        } else {
            $this->crudType = "U";
            $this->log->debug("EavRecordを取得します。 {$this->id}");
            $this->_record = Teeple_EavRecord::find($this->id);
            $this->log->debug("EavRecordを取得しました。 {$this->id}");
            if ($this->_record == NULL) {
                $this->log->info("指定されたオブジェクトが見つかりません。");
                $this->request->addErrorMessage("不正なアクセスです。");
                return $this->redirect(Admin_Cms_List::actionName());
            }
            $this->_record->convert2Page($this);
            
            // dateフィールド
            foreach(array('publish_start_dt','publish_end_dt') as $prop) {
                if (! Teeple_Util::isBlank($this->$prop)) {
                    $this->converter->target = "{$prop}_ar";
                    $this->converter->convert($this, $prop);
                }
            }
            // 画像用hiddenフィールド
            foreach ($this->_record->getImageFieldNames() as $prop) {
                $prop_h = "{$prop}_h";
                $this->$prop_h = $this->$prop;
            }
            
            // コピー
            if (! Teeple_Util::isBlank($this->_copy)) {
                $this->crudType = "C";
                $this->id = null;
                foreach ($this->_record->getImageFieldNames() as $prop) {
                    // 元画像をtmpディレクトリにコピーしておく必要がある。
                    if (! Teeple_Util::isBlank($this->$prop)) {
                        $src = UPLOAD_DIR."/".$this->$prop;
                        $dst = UPLOAD_TMP_DIR."/".$this->$prop;
                        copy($src, $dst);
                    }
                }
            }
        }
        
        return NULL;
    }
    
    /**
     * 登録処理を実行します。
     * @return unknown_type
     */
    public function doRegist() {

        if ($this->crudType == "U") {
            // 更新
            return $this->doUpdate();
        } else {
            // 新規作成
            return $this->doCreate();
        }
        
        return $this->onValidateError();
    }
    
    /**
     * レコードを更新します。
     */
    private function doUpdate() {
        
        // eav_record取得
        $record = Teeple_EavRecord::find($this->id);
        if ($record == null) {
            throw new Exception("レコードが見つかりません。");
        }
        $this->_record = $record;
        if (! $this->cmsValidator->validate($record, $this)) {
            return $this->onValidateError();
        }
        $record->convert2Entity($this);
        foreach ($record->_metaAttributes as $attr) {
            if ($attr->data_type == Entity_MetaAttribute::DATA_TYPE_CHECK) {
                $pname = $attr->pname;
                if (Teeple_Util::isBlank($this->$pname)) {
                    $record->$pname = null;
                }
            }
        }
        $this->uploadLogic->updateFile($this, $record, $record->getImageFieldNames(), $record->_metaEntity->pname);
        $record->update();
        
        $this->request->addNotification("レコードを更新しました。");
        //return $this->redirect(Admin_Cms_List::actionName());
        return $this->back2list("update");
    }
    
    /**
     * レコードを新規作成します。
     */
    private function doCreate() {
        
        // meta_record作成
        $record = new Teeple_EavRecord($this->meta_entity_id);
        $this->_record = $record;
        if (! $this->cmsValidator->validate($record, $this)) {
            return $this->onValidateError();
        }
        $record->convert2Entity($this);
        $record->insert();
        $imageFields = $record->getImageFieldNames();
        if ($imageFields && !empty($imageFields)) {
            $this->uploadLogic->updateFile($this, $record, $record->getImageFieldNames(), $record->_metaEntity->pname);
            $record->update();
        }
        $this->request->addNotification("レコードを作成しました。");
        return $this->redirect(Admin_Cms_List::actionName());
        return $this->back2list("create");
    }
    
    /**
     * Validationエラー時の処理。
     */
    public function onValidateError() {
        
        if (! is_object($this->_record)) {
            $this->_record = new Teeple_EavRecord($this->meta_entity_id);
        }
        return NULL;
    }
    
    protected function back2list($done) {
        $url = "list.html?meta_entity_id={$this->meta_entity_id}&action:doBack=true&_done={$done}";
        return "location: $url";
    }

}

?>