<?php

/**
 * Admin_Page_Create
 */
class Admin_Page_Create extends AdminActionBase
{
    public static function actionName() {
        return strtolower(__CLASS__);
    }

    // 特定のメソッド時にValidationを実行したいときに定義。
    const VALIDATION_TARGET = "doRegist";
    
    // Validationを実行したいときに定義。(ex. sample/Validator.php)
    const VALIDATION_CONFIG = '
"publish_flg.状態":
    required: {}
"name.ページ名":
    required: {}
    maxbytelength: { maxbytelength: 128 }
"url.ページURL":
    required: {}
    maxbytelength: { maxbytelength: 128 }
    mask: { mask: "/^([a-z][0-9a-z]*\/?)+$/", msg: "ページURLは英数小文字を使用して構成してください。(例. foo/bar.html)" }
"template_path.テンプレートファイル名":
    required: {}
    mask: { mask: "/^([a-z][0-9a-z]*\/?)+$/", msg: "テンプレートファイル名は英数小文字を使用して構成してください。(例. foo/bar.html)" }    
"meta_entity_id.対象オブジェクト":
    required: {}
"page_type.ページの種類":
    required: {}
"page_limit.1ページ表示件数":
    integer: {}
"notify_email.通知先メールアドレス":
    maxbytelength: { maxbytelength: 128 }
    email: {}
    ';
    
    // Converterを実行したいときに定義。(ex. sample/Converter.php)
    const CONVERTER_CONFIG = '
__all:
  trim: {}
    ';
    
    private $checkbox_fields = array('mobile_flg');

    /**
     * 標準で実行されるメソッドです。
     */
    public function execute() {
        
        if (Teeple_Util::isBlank($this->id)) {
            $this->crudType = "C";
            $this->publish_flg = 1;
        } else {
            $entity = Entity_Page::get()
                ->eq('id', $this->id)
                ->eq('delete_flg', 0)
                ->find();
            if ($entity == NULL) {
                $this->log->info("指定されたオブジェクトが見つかりません。");
                $this->request->addErrorMessage("不正なアクセスです。");
                return $this->redirect(Admin_Page_List::actionName());
            }
            $entity->convert2Page($this);
            $this->crudType = "U";
        }
        return NULL;
    }
    
    /**
     * 登録処理を実行します。
     * 
     * @return unknown_type
     */
    public function doRegist() {
        
        if (! $this->validate()) {
            return $this->onValidateError();
        }
        
        if ($this->crudType == "U") {
            // 更新
            $entity = Entity_Page::get()->find($this->id);
            $old_template_path = $entity->template_path;
            $entity->convert2Entity($this);
            foreach ($this->checkbox_fields as $field) {
                if (Teeple_Util::isBlank($this->$field)) {
                    $entity->$field = 0;
                }
            }
            $entity->update();
            $this->request->addNotification("ページ設定を更新しました。");
            if ($old_template_path != $entity->template_path) {
                $this->moveTemplate($old_template_path, $entity->template_path);
            }
        } else {
            // 新規作成
            $entity = Entity_Page::get();
            $entity->convert2Entity($this);
            $entity->insert();
            $this->request->addNotification("ページ設定を登録しました。");
            // テンプレートを生成
            $this->generateTemplate($entity);
        }
        
        return $this->redirect(Admin_Page_List::actionName());
    }
    
    /**
     * Validationエラー時の処理。
     */
    public function onValidateError() {
        return NULL;
    }
    
    /**
     * バリデーションを実施します。
     */
    private function validate() {
        
        // URLの形式チェック
        if (substr($this->url, strlen($this->url)-1, 1) == '/') {
            $this->request->addErrorMessage('ページURLの最後は/で終われません。');
            return false;
        }
        if (preg_match("/^([^\/]+\/)*[0-9]+$/", $this->url)) {
            $this->request->addErrorMessage('ページURLのパスは数字だけで構成できません。');
            return false;
        }
        // テンプレートパスの形式チェック
        if (substr($this->template_path, strlen($this->template_path)-1, 1) == '/') {
            $this->request->addErrorMessage('テンプレートファイル名の最後は/で終われません。');
            return false;
        }
        
        // URLの重複チェック
        $check = Entity_Page::get()->eq('url', $this->url);
        if ($this->crudType == 'U') {
            $check->ne('id', $this->id);
        }
        if ($check->count() > 0) {
            $this->request->addErrorMessage('このページURLは既に使用されています。');
            return false;
        }
        
        // テンプレートの重複チェック
        $check = Entity_Page::get()->eq('template_path', $this->template_path);
        if ($this->crudType == 'U') {
            $check->ne('id', $this->id);
        }
        if ($check->count() > 0) {
            $this->request->addErrorMessage('このテンプレートファイル名は既に使用されています。');
            return false;
        }
        
        return true;
    }
    
    /**
     * ページテンプレートを生成します。
     * @param Entity_Page $page
     */
    private function generateTemplate($page) {
        
        $template_path = MODULE_DIR."/". $page->template_path .".html";
        $this->log->debug("テンプレートのパス： $template_path");
        if (file_exists($template_path)) {
            $this->request->addNotification("このページのテンプレートは既に存在します。($template_path)");
            return;
        }
        
        // ディレクトリ作成
        $dir = dirname($template_path);
        @mkdir($dir, 0777, true);
        if (! is_writable($dir)) {
            $this->request->addNotification("テンプレートの書き込み権限がありません。($template_path)");
            return;
        }
        
        // ページ生成
        $metaEntity = Entity_MetaEntity::get()->find($page->meta_entity_id);
        $metaAttribute = Entity_MetaAttribute::get()
            ->eq('meta_entity_id', $page->meta_entity_id)
            ->eq('delete_flg', 0)
            ->order('seq')
            ->select();
        $page_url = Teeple_Util::getBaseUrl(false) ."/". $page->url;
        if ($page->page_type == Entity_Page::PAGE_TYPE_DETAIL) {
            $page_url .= "(/レコードID).html";
        } else {
            $page_url .= ".html";
        }
        $template_dir = "template/";
        if ($page->mobile_flg) {
            $template_dir = "template/m/";
        }
        ob_start();
        if ($page->page_type == Entity_Page::PAGE_TYPE_DETAIL) {
            include $template_dir."detail.php";
        } elseif ($page->page_type == Entity_Page::PAGE_TYPE_LIST) {
            include $template_dir."list.php";
        } else {
            include $template_dir."form.php";
        }
        $contents = ob_get_contents();
        ob_end_clean();
        if ($page->encoding != INTERNAL_CODE) {
            $contents = mb_convert_encoding($contents, $page->encoding, INTERNAL_CODE);
        }
        $fp = fopen($template_path, 'w');
        fwrite($fp, $contents);
        fclose($fp);
        
        $this->request->addNotification("このページのテンプレートを生成しました。($template_path)");
        
        if ($page->page_type == Entity_Page::PAGE_TYPE_FORM) {
            
            // 確認画面生成
            $template_path = MODULE_DIR."/". $page->template_path ."-confirm.html";
            if (! file_exists($template_path)) {
                ob_start();
                include $template_dir."form-confirm.php";
                $contents = ob_get_contents();
                ob_end_clean();
                if ($page->encoding != INTERNAL_CODE) {
                    $contents = mb_convert_encoding($contents, $page->encoding, INTERNAL_CODE);
                }
                $fp = fopen($template_path, 'w');
                fwrite($fp, $contents);
                fclose($fp);
                $this->request->addNotification("このページのテンプレートを生成しました。($template_path)");
            }
            
            // 完了画面生成
            $template_path = MODULE_DIR."/". $page->template_path ."-complete.html";
            if (! file_exists($template_path)) {
                ob_start();
                include $template_dir."form-complete.php";
                $contents = ob_get_contents();
                ob_end_clean();
                if ($page->encoding != INTERNAL_CODE) {
                    $contents = mb_convert_encoding($contents, $page->encoding, INTERNAL_CODE);
                }
                $fp = fopen($template_path, 'w');
                fwrite($fp, $contents);
                fclose($fp);
                $this->request->addNotification("このページのテンプレートを生成しました。($template_path)");
            }
        }
        return;
    }
    
    /**
     * テンプレートファイルを移動します。
     * @param string $old
     * @param string $new
     */
    private function moveTemplate($old, $new) {
        
        $old_path = MODULE_DIR."/". $old .".html";
        $new_path = MODULE_DIR."/". $new .".html";
        
        if (! file_exists($old_path)) {
            $this->request->addNotification("テンプレートファイルが存在しないのでFTP等でUPしてください。($new_path)");
            return;
        }
        
        // ディレクトリ作成
        $dir = dirname($new_path);
        @mkdir($dir, 0777, true);
        if (! is_writable($dir)) {
            $this->request->addNotification("書き込み権限が無いためテンプレートは生成しませんでした。FTP等でUPしてください。($new_path)");
            return;
        }
        
        // ファイルを移動
        if (! rename($old_path, $new_path)) {
            $this->request->addNotification("テンプレートファイルの移動に失敗しました。FTP等でUPしてください。($new_path)");
            return;
        }
        
        $this->request->addNotification("テンプレートファイルを移動しました。($new_path)");
        return;
    }

}

?>