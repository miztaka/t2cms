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
 * @package     teeple
 * @author      Mitsutaka Sato <miztaka@gmail.com>
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 */

/**
 * Viewの実行準備および実行を行うFilter
 *
 * @package     teeple.filter
 */
class Teeple_Filter_View extends Teeple_Filter
{
    /**
     * @var Teeple_Response
     */
    private $response;
    public function setComponent_Teeple_Response($c) {
        $this->response = $c;
    }
    
    /**
     * @var Teeple_Token
     */
    private $token;
    public function setComponent_Teeple_Token($c) {
        $this->token = $c;
    }
    
    /**
     * コンストラクタ
     *
     */
    public function __construct() {
        parent::__construct();
    }

    public function prefilter() {}
    
    /**
     * Viewの処理を実行
     *
     **/
    public function postfilter() {

        if ($this->isCompleteResponse()) {
            $this->log->info("CompleteResponseフラグが立っているため、Viewは実行されませんでした。");
            return;
        }        

        $view = $this->response->getView();
        $this->log->debug("view: $view");
        
        if ($view == "") {
            $view = Teeple_Util::getPathInfo();
        }

        if ($view != "") {
            $template = preg_replace("/^\//", "", $view);
            if ($template == "") {
                throw new Teeple_Exception("テンプレートの指定が不正です。($template)");
            }
            
            if (preg_match("/^location:/", $template)) {
                $url = preg_replace("/^location:/", "", $template);
                $url = trim($url);
                $this->response->setRedirect($url);
            } else if (preg_match("/^redirect:/", $template)) {
                $url = preg_replace("/^redirect:/", "", $template);
                $url = trim($url);
                $url = Teeple_Util::getAbsoluteUrlFromActionName($url, $this->request->isHttps());
                $this->request->setFilterError(NULL);
                // TODO 定数化
                $this->session->setParameter("__REDIRECT_SCOPE_REQUEST", $this->request);
                $this->response->setRedirect($url);
            } else {
                $renderer = Teeple_Smarty4Maple::getInstance();
                $renderer->registerFilters();
                $action = $this->actionChain->getCurAction();
                $renderer->setAction($action);
                if (is_object($this->token)) {
                    $renderer->setToken($this->token);
                }
                if (is_object($this->session)) {
                    $renderer->setSession($this->session);
                }
                if (is_object($this->request)) {
                    $renderer->setRequest($this->request);
                }
                $renderer->setScriptName(Teeple_Util::getScriptName());
                
                $result = $renderer->fetch($template);
                /*
                if ($result == "") {
                    throw new Teeple_Exception("Viewのレンダリングに失敗しました。");
                }
                */
                
                $this->response->setResult($result);
            }
        }

        $contentDisposition = $this->response->getContentDisposition();
        $contentType        = $this->response->getContentType();
        $result             = $this->response->getResult();
        $redirect           = $this->response->getRedirect();

        if ($redirect) {
            $this->redirect($redirect);
        } else {
            if ($contentDisposition != "") {
                header("Content-disposition: ${contentDisposition}");
            }
            if ($contentType != "") {
                header("Content-type: ${contentType}");
            }

            print $result;
        }

        return;
    }
    
    /**
     * リダイレクトします。
     * @param string $url
     */
    private function redirect($url) {
        
        if (! isset($_COOKIE[session_name()]) && session_id() != "") {
            if (strpos($url, '?') === FALSE) {
                $url .= '?';
            } else {
                $url .= '&';
            }
            $url .= session_name() ."=". session_id();
        }
        
        header("Location: $url");
    }
    
}
?>
