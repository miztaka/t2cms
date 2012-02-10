<?php

/**
 * Admin_Page_Template
 */
class Admin_Page_Template extends AdminActionBase
{
    public static function actionName() {
        return strtolower(__CLASS__);
    }

    // 特定のメソッド時にValidationを実行したいときに定義。
    const VALIDATION_TARGET = "doRegist";
    
    // Validationを実行したいときに定義。(ex. sample/Validator.php)
    const VALIDATION_CONFIG = '
template_path:
    required: {}
template:
    required: {}
encoding:
    required: {}    
    ';
    
    // Converterを実行したいときに定義。(ex. sample/Converter.php)
    //const CONVERTER_CONFIG = '
    //';

    /**
     * 標準で実行されるメソッドです。
     */
    public function execute() {
        
        if (Teeple_Util::isBlank($this->id)) {
            $this->request->addNotification("不正なアクセスです。");
            return $this->redirect(Admin_Page_List::actionName());
        }
        $entity = Entity_Page::get()->find($this->id);
        if ($entity == NULL) {
            $this->log->info("指定されたオブジェクトが見つかりません。");
            $this->request->addNotification("不正なアクセスです。");
            return $this->redirect(Admin_Page_List::actionName());
        }
        $template_path = MODULE_DIR ."/". $entity->template_path .".html";
        if ($entity->page_type == Entity_Page::PAGE_TYPE_FORM && ! Teeple_Util::isBlank($this->tpl)) {
            if ($this->tpl == 'confirm') {
                $template_path = MODULE_DIR ."/". $entity->template_path ."-confirm.html";
            } elseif ($this->tpl == 'complete') {
                $template_path = MODULE_DIR ."/". $entity->template_path ."-complete.html";
            }
        }
        if (! is_writable($template_path)) {
            $this->request->addNotification("テンプレートファイルの編集権限がありません。ファイルのパーミッションを確認してください。($template_path");
            return $this->redirect(Admin_Page_List::actionName());
        }
        
        $this->template = file_get_contents($template_path);
        if ($entity->encoding != INTERNAL_CODE) {
            $this->template = mb_convert_encoding($this->template, INTERNAL_CODE, $entity->encoding);
        }
        $this->template_path = $template_path;
        $this->encoding = $entity->encoding;
        $this->page_type = $entity->page_type;
        return NULL;
    }
    
    /**
     * テンプレートを更新します。
     * TODO ファイルアクセス時刻のチェック
     * @return unknown_type
     */
    public function doRegist() {
        
        if ($this->encoding != INTERNAL_CODE) {
            $this->template = mb_convert_encoding($this->template, $this->encoding, INTERNAL_CODE);
        }
        $fp = fopen($this->template_path, 'w');
        fwrite($fp, $this->template);
        fclose($fp);
        
        $this->request->addNotification("テンプレートを更新しました。");
        return $this->redirect(Admin_Page_List::actionName());
    }
    
    /**
     * Validationエラー時の処理。
     */
    public function onValidateError() {
        return NULL;
    }

}

?>