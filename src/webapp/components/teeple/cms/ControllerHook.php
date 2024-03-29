<?php 
/**
 * Teeple2 - PHP5 Web Application Framework inspired by Seasar2
 *
 * PHP versions 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @package     teeple.cms
 * @author      Mitsutaka Sato <miztaka@gmail.com>
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 */

/**
 * コントローラのHookロジックです
 * @package     teeple.cms
 */
class Teeple_Cms_ControllerHook extends Teeple_ControllerHook
{
    
    /**
     * @var Teeple_ActionChain
     */
    protected $actionChain;
    public function setComponent_Teeple_ActionChain($c) {
        $this->actionChain = $c;
    }    
    
    /**
     * @var Teeple_Request
     */
    protected $request;
    public function setComponent_Teeple_Request($c) {
        $this->request = $c;
    }
    
    /**
     * Actionが見つからなかったときの処理です。
     * @param string $actionName
     * @return controllerに処理を続けさせるかどうか？
     */
    public function actionClassNotFound($actionName) {
        
        $page = $this->getPageInfo($actionName);
        if ($page != null) {
            // encodingのセット
            $encoding = $page->encoding;
            $this->request->setEncoding('OUTPUT_CODE', $encoding);
            $this->request->setEncoding('TEMPLATE_CODE', $encoding);
            $this->request->setEncoding('INPUT_CODE', $encoding);
            if ($encoding != INTERNAL_CODE) {
                $this->request->convertEncoding($encoding);
            }
            // actionセットアップ
            $this->actionChain->add("teeple_cms_action");
            $action = $this->actionChain->getCurAction();
            $action->_pageInfo = $page;
            $action->__smarty = Teeple_Smarty4Maple::getInstance();
            return TRUE;
        }
        
        // 404ページ
        if (defined("ERROR404_PAGE")) {
        	header("HTTP/1.1 301 Moved Permanently");
        	header("Location: ". ERROR404_PAGE);
        } else {
        	header("HTTP/1.1 404 Not Found");
        	print('Page Not Found');
        }
        return FALSE;
        //return parent::actionClassNotFound($actionName);
    }
    
    /**
     * Entity_Pageを取得します。
     * @param string $actionName
     * @return Entity_Page
     */
    protected function getPageInfo($actionName) {
        
        $path = str_replace('_', '/', $actionName);
        $path = preg_replace("/\\/[0-9]+$/", "", $path);
        
        $page = Entity_Page::get()
            ->eq('url', $path)
            // ->eq('publish_flg', 1) プレビューのためにAction側でチェック
            ->eq('delete_flg', 0)
            ->find();
        if ($page) {
            return $page;
        }
        
        // フォームの完了ページの可能性
        if (preg_match("/-complete$/", $path)) {
            $path = preg_replace("/-complete$/", "", $path);
            $page = Entity_Page::get()
                ->eq('url', $path)
                // ->eq('publish_flg', 1) プレビューのためにAction側でチェック
                ->eq('delete_flg', 0)
                ->find();
            if ($page) {
                $page->template_path .= "-complete"; // FIXME adhocなやり方だなあ。。
                return $page;
            }
        }
        
        // オリジナルURL
        $path = Teeple_Util::getPathInfo();
        if ($path{strlen($path)-1} == '/') {
            $path .= "index.html";
        }
        $path = preg_replace('/(\..*)?$/', '', $path); // 拡張子を除く
        
        $url = Entity_RecordUrl::get()
            ->join("page")
            ->join("meta_record")
            ->eq("base.url", $path, FALSE)
            ->eq("meta_record.publish_flg", 1)
            ->eq("meta_record.delete_flg", 0)
            ->where('meta_record.publish_start_dt IS NULL OR meta_record.publish_start_dt <= now()')
            ->where('meta_record.publish_end_dt IS NULL OR meta_record.publish_end_dt >= now()')
            ->order('meta_record.id DESC')
            ->limit(1)
            ->find();
        if ($url) {
            $url->page->_page_record = $url->meta_record;
            return $url->page;
        }
        
        return null;
    }
    
}

?>