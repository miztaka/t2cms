<?php

/**
 * Admin_Object_Attribute_Create
 */
class Admin_Object_Attribute_Create extends AdminActionBase
{
    public static function actionName() {
        return strtolower(__CLASS__);
    }
    
    // 特定のメソッド時にValidationを実行したいときに定義。
    const VALIDATION_TARGET = "doRegist";
    
    // Validationを実行したいときに定義。(ex. sample/Validator.php)
    const VALIDATION_CONFIG = '
"meta_entity_id.オブジェクト":
    required: {}
"label.表示ラベル":
    required: {}
    maxbytelength: { maxbytelength: 128 }
"pname.参照名":
    required: {}
    maxbytelength: { maxbytelength: 32 }
    mask: { mask: "/^[a-z][0-9a-z_]*$/", msg: "参照名は英小文字ではじまり英数小文字と_(アンダースコア)を使用した文字列にしてください。" }    
"data_type.入力種別":
    required: {}
"validation.入力値検証ルール":
    mask: { mask: "/^[\/].+[\/]$/" }    
    ';
    
    // Converterを実行したいときに定義。(ex. sample/Converter.php)
    const CONVERTER_CONFIG = '
__all:
  trim: {}
    ';
    
    /**
     * フォームを表示します。
     */
    public function execute() {
        
        if (Teeple_Util::isBlank($this->id)) {
            if (Teeple_Util::isBlank($this->meta_entity_id)) {
                throw new Exception("不正なアクセスです。");
            }
            $this->crudType = "C";
        } else {
            $this->crudType = "U";
            $entity = Entity_MetaAttribute::get()->find($this->id);
            if ($entity == null) {
                throw new Exception("不正なアクセスです。");
            }
            $entity->convert2Page($this);
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
            return $this->doUpdate();
        } else {
            // 新規作成
            return $this->doCreate();
        }
        
        return $this->onValidateError();
    }
        
    /**
     * Attributeを新規登録します。
     */
    private function doCreate() {
        
        $entity = Entity_MetaAttribute::get();
        $entity->seq = $this->getNextSeq();
        $entity->convert2Entity($this);
        $entity->id = null;
        $entity->insert();
        
        $this->message = 'カラムを追加しました。';
        return 'admin/parts/reload_attribute.tpl';
    }
    
    private function getNextSeq() {
        
        $row = Entity_MetaAttribute::get()->findQuery(
            "SELECT MAX(seq) seq FROM meta_attribute WHERE meta_entity_id = ?",
            array($this->meta_entity_id)
        );
        if ($row) {
            return $row->seq + 1;
        }
        return 1;
    }
    
    /**
     * Attributeを更新します。
     */
    private function doUpdate() {
        
        $entity = Entity_MetaAttribute::get()
            ->eq('id', $this->id, false)
            ->eq('meta_entity_id', $this->meta_entity_id, false)
            ->eq('delete_flg', 0)
            ->find();
        if ($entity == null) {
            throw new Exception("不正なアクセスです。");
        }
        $entity->convert2Entity($this);
        if (Teeple_Util::isBlank($this->list_flg)) {
            $entity->list_flg = NULL;
        }
        if (Teeple_Util::isBlank($this->require_flg)) {
            $entity->require_flg = 0;
        }
        $entity->update();

        $this->message = 'カラム定義を更新しました。';
        return 'admin/parts/reload_attribute.tpl';
    }
    
    /**
     * 属性の削除を実行します。
     */
    public function doDelete() {
        
        if (Teeple_Util::isBlank($this->id)) {
            $this->request->addErrorMessage("不正なアクセスです。");
            return $this->onValidateError();
        }
        $entity = Entity_MetaAttribute::get()->find($this->id);
        if ($entity == null) {
            $this->request->addErrorMessage("不正なアクセスです。");
            return $this->onValidateError();
        }
        
        $entity->delete_flg = 1;
        $entity->delete_time = $entity->now();
        $entity->update();
        //$entity->delete();
        
        $this->message = 'カラムを削除しました。';
        return 'admin/parts/reload_attribute.tpl';
    }
    
    /**
     * Validationを実行します。
     */
    public function validate() {
        
        // meta_entityの存在チェック
        if (Entity_MetaEntity::get()
            ->eq('id', $this->meta_entity_id, false)
            ->eq('delete_flg', 0)
            ->count() == 0) {
            $this->request->addErrorMessage("不正なアクセスです。");
            return false;
        }
        
        // options の必須チェック
        if ($this->data_type == Entity_MetaAttribute::DATA_TYPE_CHECK || $this->data_type == Entity_MetaAttribute::DATA_TYPE_MULTISELECT) {
            if (Teeple_Util::isBlank($this->options)) {
                $this->request->addErrorMessage("選択肢を１つ以上設定してください。");
                return false;
            }
        }
        
        // ref の必須チェック
        if ($this->data_type == Entity_MetaAttribute::DATA_TYPE_REF) {
            if (Teeple_Util::isBlank($this->ref_entity_id)) {
                $this->request->addErrorMessage("参照先オブジェクトを選択してください。");
                return false;
            }
        }
        
        // pnameの重複チェック
        $entity = Entity_MetaAttribute::get()
            ->eq('meta_entity_id', $this->meta_entity_id)
            ->eq('pname', $this->pname)
        ;
        if ($this->crudType == 'U') {
            $entity->ne('id', $this->id);
        }
        if ($entity->count() > 0) {
            $this->request->addErrorMessage("既に使用されている参照名です。");
            return false;
        }
        
        // 予約語
        $reserved = get_class_vars("Entity_MetaRecord");
        if (array_key_exists($this->pname, $reserved)) {
            $this->request->addErrorMessage("{$this->pname}は予約語のため参照名として使用できません。");
            return false;
        }
        
        return true;
    }
    
    /**
     * Validationエラー時の処理。
     */
    public function onValidateError() {
        return 'admin/parts/message_area.tpl';
    }

}

?>