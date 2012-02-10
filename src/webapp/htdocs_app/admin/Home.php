<?php

/**
 * Admin_Home
 */
class Admin_Home extends AdminActionBase
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
    
    /**
     * ページの生成を実行します。
     */
    public function doGeneratePage() {
        
        set_time_limit(600);
        ini_set("memory_limit", -1);
        
        $pages = Entity_Page::get()
            ->eq('delete_flg', 0)
            ->eq('publish_flg', 1)
            ->ne('page_type', Entity_Page::PAGE_TYPE_FORM)
            ->select();
        foreach ($pages as $page) {
            $url = $page->url.".html";
            $this->generatePage($url);
            if ($page->page_type == Entity_Page::PAGE_TYPE_DETAIL) {
                // 削除
                $records = Entity_MetaRecord::get()
                    ->eq('meta_entity_id', $page->meta_entity_id)
                    ->select();
                foreach ($records as $record) {
                    $url = $page->url."/".$record->id.".html";
                    $this->deletePage($url);
                }
                
                // 詳細ページ(レコード番号付URL)
                $records = Entity_MetaRecord::get()
                    ->eq('delete_flg', 0)
                    ->eq('meta_entity_id', $page->meta_entity_id)
                    ->eq('publish_flg', 1)
                    ->where('publish_start_dt IS NULL OR publish_start_dt <= now()')
                    ->where('publish_end_dt IS NULL OR publish_end_dt >= now()')
                    ->select();
                foreach ($records as $record) {
                    $url = $page->url."/".$record->id.".html";
                    $this->generatePage($url);
                }
            }
        }
        $this->request->addNotification("ページの書き出しが完了しました。");
        return NULL;
    }
    
    /**
     * ページの生成を行います。
     * @param $url
     */
    protected function generatePage($url) {
        
        $destination_path = HTML_DIR ."/". $url;
        if (file_exists($destination_path) && ! @unlink($destination_path)) {
            $this->request->addErrorMessage("ページの書き出しに失敗しました。 $url");
            return false;
        } else {
            $dir = dirname($destination_path);
            @mkdir($dir, 0777, true);
            if (! is_writable($dir)) {
                $this->request->addErrorMessage("ページの書き出しに失敗しました。 $url");
                return false;
            }
        }
        
        // コンテンツを取得
        $contents = file_get_contents(Teeple_Util::getBaseUrl(false)."/".$url);
        $result = file_put_contents($destination_path, $contents);
        if (! $result) {
            $this->request->addErrorMessage("ページの書き出しに失敗しました。 $url");
            return false;
        }
        return true;
    }
    
    /**
     * ページを削除します。
     * @param unknown_type $url
     */
    protected function deletePage($url) {
        
        $destination_path = HTML_DIR ."/". $url;
        if (file_exists($destination_path)) {
            @unlink($destination_path);
        }
        return;
    }

}

?>