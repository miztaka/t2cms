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
 * Smarty内にActionの実行結果をIncludeするためのクラスです。
 *
 * @package teeple
 */
class Teeple_Cms_IncludePageRenderer {

    /**
     * @var Logger
     */
    protected $log;
    
    protected $_path_info;
    protected $_params = array();
    
    /**
     * コンストラクタです。
     * @param string $path_info
     */
    public function __construct($path_info) {
        $this->log = LoggerManager::getLogger(get_class($this));
        if (strpos($path_info, '?') === FALSE) {
            $this->_path_info = $path_info;
        } else {
            $p = explode('?', $path_info, 2);
            $this->_path_info = $p[0];
            parse_str($p[1], $this->_params);
        }
    }
    
    /**
     * 指定されたPATH_INFOに基づいてActionを実行し結果を取得します。
     * @return string renderしたHTML
     */
    public function render() {
        
        $this->log->info("{$this->_path_info} をインクルードします。");
        
        $container = Teeple_Container::getInstance();
        $session = $container->getComponent('Teeple_Session');
        $request = $container->getPrototype('Teeple_Request'); // 新しいリクエストを作る
        $this->resetRequest($request);
        $request->setPathInfo($this->_path_info);
        if (count($this->_params)) {
            foreach ($this->_params as $key => $val) {
                $request->setParameter($key, $val);
            }
        }
        
        // 実行するActionを特定
        $actionName = Teeple_Util::path2Action($this->_path_info);
        if (!preg_match("/^[0-9a-zA-Z_]+$/", $actionName)) {
            $this->log->info("Actionクラス名が不正です。 $actionName");
            return "";
        }
        $className = Teeple_Util::capitalizedClassName($actionName);
        $pageInfo = null;
        if (! Teeple_Util::includeClassFile($className)) {
            $pageInfo = $this->findCmsAction($actionName);
            if ($pageInfo == null) {
                $this->log->info("Actionがみつかりません。 $actionName");
                return "";
            }
            $className = "Teeple_Cms_Action";
        }
        $actionObj = $container->getPrototype($className);
        $actionObj->__smarty = Teeple_Smarty4Maple::newInstance();
        $base = 'Teeple_ActionBase';
        if (!is_object($actionObj) || ! $actionObj instanceof $base) {
            $this->log->info("Actionクラスの生成に失敗しました。({$className})");
            return "";
        }
        if ($pageInfo != null) {
            $actionObj->_pageInfo = $pageInfo;
        }
    
        // Actionを実行 TODO Converter Validatorの実行
        $actionObj->setComponent_Teeple_Request($request);
        foreach ($this->_params as $key => $val) {
            if (preg_match('/^__/', $key)) {
                continue;
            }
            $actionObj->$key = $val;
        }
        $view = $actionObj->execute();
        if ($view == "") {
            $view = str_replace('_','/',$actionName).".html";
        }
    
        // Viewを実行
        if (strpos($view, "action:") === 0 || strpos($view, "location:") === 0 || strpos($view, "redirect:") === 0) {
            // サポート対象外
            $this->log->info("Viewのレンダリングサポート対象外: $view");
            return "";
        }
        $template = preg_replace("/^\//", "", $view);
        $renderer = $actionObj->__smarty;
        $renderer->registerFilters();
        $renderer->setAction($actionObj);
        $renderer->setSession($session);
        $renderer->setRequest($request);
        $renderer->setScriptName(Teeple_Util::getScriptName());
        $result = $renderer->fetch($template);
        return $result;
    }
    
    /**
     * CMS管理化のActionかどうかチェックします。
     * @param string $actionName
     * @return Entity_Page
     */
    protected function findCmsAction($actionName) {
        
        $path = str_replace('_', '/', $actionName);
        $path = preg_replace("/\\/[0-9]+$/", "", $path);
        
        $page = Entity_Page::get()
            ->eq('url', $path)
            // ->eq('publish_flg', 1) プレビューのためにAction側でチェック
            ->eq('delete_flg', 0)
            ->find();
        return $page;
    }
    
    /**
     * リクエストパラメータをリセットする
     * @param Teeple_Request $request
     */
    protected function resetRequest($request) {
        
        $buf = $request->getParameters();
        if (is_array($buf)) {
            foreach ($buf as $key => $val) {
                $request->removeParameter($key);
            }
        }
        return;
    }
    
}

?>