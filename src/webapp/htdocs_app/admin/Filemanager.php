<?php

/**
 * Admin_Filemanager
 */
class Admin_Filemanager extends AdminActionBase
{
    public static function actionName() {
        return strtolower(__CLASS__);
    }

    /**
     * 標準で実行されるメソッドです。
     */
    public function execute() {
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