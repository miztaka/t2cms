<?php

/**
 * Admin_Cms_Detail
 */
class Admin_Cms_Detail extends AdminActionBase
{
    public static function actionName() {
        return strtolower(__CLASS__);
    }

    // 特定のメソッド時にValidationを実行したいときに定義。
    //const VALIDATION_TARGET = "";
    
    // Validationを実行したいときに定義。(ex. sample/Validator.php)
    //const VALIDATION_CONFIG = '
    //';
    
    // Converterを実行したいときに定義。(ex. sample/Converter.php)
    //const CONVERTER_CONFIG = '
    //';
    
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
            throw new Exception("不正なアクセスです。");
        }
        $this->_record = Teeple_EavRecord::find($this->id);
        if ($this->_record == NULL) {
            $this->log->info("指定されたオブジェクトが見つかりません。");
            throw new Exception("不正なアクセスです。");
        }
        $this->_record->convert2Page($this);
        return NULL;
    }
    
    /**
     * Validationエラー時の処理。
     */
    public function onValidateError() {
        
        $this->_record = new Teeple_EavRecord($this->meta_entity_id);
        return NULL;
    }

}

?>