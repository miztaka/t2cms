<?php

/**
 * Admin_Ajax_Upload
 */
class Admin_Ajax_Upload extends AdminActionBase
{
    public static function actionName() {
        return strtolower(__CLASS__);
    }

    // 特定のメソッド時にValidationを実行したいときに定義。
    const VALIDATION_TARGET = "";
    
    // Validationを実行したいときに定義。(ex. sample/Validator.php)
    const VALIDATION_CONFIG = '
    ';
    
    // Converterを実行したいときに定義。(ex. sample/Converter.php)
    const CONVERTER_CONFIG = '
    ';
    
    /**
     * Logic_FileUpload
     * @var Logic_FileUpload
     */
    private $uploadLogic;
    public function setComponent_Logic_FileUpload($c) {
        $this->uploadLogic = $c;
    }
    
    /**
     * 標準で実行されるメソッドです。
     */
    public function execute() {
        
        $files = $this->uploadLogic->uploadTmpFile();
        $this->log->debug(var_export($files, true));
        
        if (count($files) == 0) {
            $this->message = 'ファイルアップロードに失敗しました。';
            return "/admin/ajax/upload_error.html";
        }
        
        $this->fieldName = $files[0]['name'];
        $this->fieldValue = $files[0]['value'];
        
        return NULL;
    }
    
    /**
     * Validationエラー時の処理。
     */
    public function onValidateError() {
        return NULL;
    }

}

?>