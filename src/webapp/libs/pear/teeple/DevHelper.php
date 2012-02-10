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

require_once SMARTY_DIR ."Smarty.class.php";

/**
 * Actionクラスを自動生成するためのクラスです。
 *
 * @package teeple
 */
class Teeple_DevHelper {

    /**
     * @var Logger
     */
    protected $log;
    
    /**
     * @var Teeple_Request
     */
    protected $request;
    public function setComponent_Teeple_Request($c) {
        $this->request = $c;
    }

    /**
     * @var Teeple_Session
     */
    protected $session;
    public function setComponent_Teeple_Session($c) {
        $this->session = $c;
    }
    
    /**
     * @var Teeple_ActionChain
     */
    protected $actionChain;
    public function setComponent_Teeple_ActionChain($c) {
        $this->actionChain = $c;
    }
    
    /**
     * @var Teeple_FileUtil
     */
    protected $fileutil;
    public function setPrototype_Teeple_FileUtil($c) {
        $this->fileutil = $c;
    }
    
    /**
     * @var Teeple_FilterChain
     */
    protected $filterChain;
    public function setComponent_Teeple_FilterChain($c) {
        $this->filterChain = $c;
    }
    
    /**
     * コンストラクタです。
     */
    public function __construct() {
        $this->log = LoggerManager::getLogger(get_class($this));
    }
    
    public function execute($actionName) {
        
        // 作成モードであれば、Actionクラスと設定ファイルを作成
        if ($this->request->getParameter('__CREATE_ACTION') == 1) {
            $this->createActionTemplate($actionName);
        } else {
            $this->confirmPage($actionName);
        }
    }
    
    private function createActionTemplate($actionName) {
        
        $this->log->debug("Actionクラスの雛形を作成します。");
        list($className, $classfile, $templatefile) = $this->makeNames($actionName);
        
        $renderer = new Smarty();
        $renderer->template_dir = VIEW_TEMPLATE_DIR;
        $renderer->compile_dir = VIEW_COMPILE_DIR;
        $renderer->assign('className', $className);
        $renderer->assign('actionName', $actionName);
        $renderer->assign('templatefile', $templatefile);
        
        if (!@file_exists($classfile)) {
            $result = $renderer->fetch('devhelper/template/Action.class.php.txt');
            if (! $this->fileutil->write($classfile, $result)) {
                print "Actionクラスの自動生成に失敗しました。ファイルが作成できません。";
                return;
            }
        }
        if (!@file_exists($templatefile)) {
            $result = $renderer->fetch('devhelper/template/template.html');
            if (! $this->fileutil->write($templatefile, $result)) {
                print "HTMLの自動生成に失敗しました。ファイルが作成できません。";
                return;
            }
        }
        print "Actionクラスを自動生成しました。{$classfile} <br/>";
        
        $this->actionChain->add($actionName);
        return;
    }

    private function confirmPage($actionName) {
        
        list($className, $classfile, $templatefile) = $this->makeNames($actionName);
        
        $isClassfile = "新規作成";
        $isTemplatefile = "新規作成";
        if (@file_exists($classfile)) {
            $isClassfile = "存在します";
        }
        if (@file_exists($templatefile)) {
            $isTemplatefile = "存在します";
        }
        
        $renderer = Teeple_Smarty4Maple::getInstance();
        $renderer->assign('actionName', $actionName);
        $renderer->assign('classfile', $classfile);
        $renderer->assign('templatefile', $templatefile);
        $renderer->assign('isClassfile', $isClassfile);
        $renderer->assign('isTemplatefile', $isTemplatefile);
        $result = $renderer->fetch('devhelper/confirm.html');
        print $result;
        exit;
    }
    
    private function makeNames($actionName) {
        
        $className = Teeple_Util::capitalizedClassName($actionName);
        
        $ar = split('_',$actionName);
        $action = array_pop($ar);
        
        $basedir = MODULE_DIR .'/'. implode('/', $ar) .'/';
        
        $classfile = $basedir . ucfirst($action) . ".php";
        $templatefile = $basedir . $action .".html"; 
        $this->log->debug("classファイル: {$classfile}");
        $this->log->debug("templateファイル: {$templatefile}");

        return array($className, $classfile, $templatefile);
    }

}

?>