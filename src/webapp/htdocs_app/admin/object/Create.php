<?php

/**
 * Admin_Object_Create
 */
class Admin_Object_Create extends AdminActionBase
{
    public static function actionName() {
        return strtolower(__CLASS__);
    }

    // 特定のメソッド時にValidationを実行したいときに定義。
    const VALIDATION_TARGET = "doRegist";
    
    // Validationを実行したいときに定義。(ex. sample/Validator.php)
    const VALIDATION_CONFIG = '
"label.表示ラベル":
    required: {}
    maxbytelength: { maxbytelength: 128 }
"pname.参照名":
    required: {}
    maxbytelength: { maxbytelength: 32 }
    mask: { mask: "/^[a-z][0-9a-z_]*$/", msg: "参照名は英小文字ではじまり英数小文字と_(アンダースコア)を使用した文字列にしてください。" }
"seq.並び順":
    tinyint: {}    
    ';
    
    // Converterを実行したいときに定義。(ex. sample/Converter.php)
    const CONVERTER_CONFIG = '
__all:
  trim: {}
pname:
  lower: {}
    ';
    
    /**
     * 標準で実行されるメソッドです。
     */
    public function execute() {

        if (Teeple_Util::isBlank($this->id)) {
            $this->crudType = "C";
            $this->list_control = array('publish_flg', 'publish_start_dt', 'publish_end_dt');
        } else {
            $entity = Entity_MetaEntity::get()->find($this->id);
            if ($entity == NULL) {
                $this->log->info("指定されたオブジェクトが見つかりません。");
                $this->request->addErrorMessage("不正なアクセスです。");
                return $this->redirect(Admin_Object_List::actionName());
            }

            $entity->convert2Page($this);
            if (! Teeple_Util::isBlank($this->list_control)) {
                $this->list_control = unserialize($this->list_control);
            }
            $this->crudType = "U";
            
            if ($this->_copy == 1) {
                $this->crudType = "C";
                $this->pname = "copyOf_". $this->pname;
                $this->label .= "のコピー";
            }
        }
        
        return NULL;
    }
    
    /**
     * 登録処理を実行します。
     * @return unknown_type
     */
    public function doRegist() {
        
        if (! $this->validate()) {
            return $this->onValidateError();
        }
        
        if ($this->crudType == "U") {
            // 更新
            $entity = Entity_MetaEntity::get()->find($this->id);
            $entity->convert2Entity($this);
            if (Teeple_Util::isBlank($this->list_control)) {
                $entity->list_control = NULL;
            }
            if (Teeple_Util::isBlank($this->api_flg)) {
                $entity->api_flg = 0;
            }
            if (Teeple_Util::isBlank($this->hide_flg)) {
                $entity->hide_flg = 0;
            }
            if (Teeple_Util::isBlank($this->exclude_search_flg)) {
                $entity->exclude_search_flg = 0;
            }
            if (Teeple_Util::isBlank($this->single_page_flg)) {
                $entity->single_page_flg = 0;
            }
            $entity->update();
            $this->request->addNotification("オブジェクトを更新しました。");
        } else {
            // 新規作成
            $entity = Entity_MetaEntity::get();
            $entity->convert2Entity($this);
            $entity->id = NULL;
            $entity->insert();
            if ($this->_copy != 1) {
                $this->request->addNotification("オブジェクトを作成しました。属性を登録してください。");
            } else {
                // 属性をコピー
                $attrs = Entity_MetaAttribute::get()
                    ->eq('meta_entity_id', $this->id, FALSE)
                    ->eq('delete_flg', 0)
                    ->select();
                $this->log->info("Entity no.{$this->id} から Entity no.{$entity->id} に属性をコピーします。");
                foreach ($attrs as $attr) {
                    $attr->id = NULL;
                    $attr->meta_entity_id = $entity->id;
                    $attr->insert();
                }
                $this->request->addNotification("オブジェクトをコピーして作成しました。");
                $this->_copy = "";
            }
            
            $entity->convert2Page($this);
            $this->crudType = "U";
            return null;
        }
        
        return $this->redirect(Admin_Object_List::actionName());
    }
    
    /**
     * Validationエラー時の処理。
     */
    public function onValidateError() {
        return NULL;
    }
    
    /**
     * 追加でバリデーションを行ないます。
     */
    private function validate() {
        
        // pnameの重複チェック
        $check = Entity_MetaEntity::get()
            ->eq('pname', $this->pname);
        if ($this->crudType == 'U') {
            $check->ne('id', $this->id);
        }
        if ($check->count() > 0) {
            $this->request->addErrorMessage("この参照名は既に登録されています。");
            return false;
        }
        
        return true;
    }

}

?>