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
 * Actionクラスの基底クラスです。
 *
 * @package teeple
 */
abstract class Teeple_ActionBase {

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
     * @var Teeple_DataSource
     */
    protected $dataSource;
    public function setComponent_Teeple_DataSource($c) {
        $this->dataSource = $c;
    }
    
    /**
     * @var Teeple_TransactionManager
     */
    protected $txManager;
    public function setComponent_Teeple_TransactionManager($c) {
        $this->txManager = $c;
    }
    
    /**
     * @var Teeple_Transaction
     */
    protected $defaultTx;
    public function setComponent_DefaultTx($c) {
        $this->defaultTx = $c;
    }
    
    /**
     * コンストラクタです。
     */
    public function __construct() {
        $this->log = LoggerManager::getLogger(get_class($this));
    }

    /**
     * エンティティに定義されているoptionsの定義を取得します。
     * @param string $table
     * @param string $column
     */
    public function getOptions($table, $column) {
        $ref = new ReflectionClass('Entity_'.$table);
        return $ref->getStaticPropertyValue('_'.$column.'Options');
    }
        
    public function __get($name) {
        if (preg_match('/^Entity_/', $name) && isset($this->defaultTx)) {
            return $this->defaultTx->$name;
        }
        return NULL;
    }
    
    /**
     * 404 not found を返したいときに使用します。
     * @return string
     */
    public function exit404($message = 'Page Not Found.') {
        
        header("HTTP/1.1 404 Not Found");
        print($message);
        $this->request->completeResponse();
        return NULL;
    }   

    /**
     * TeepleActionにリダイレクトします。(リクエストを引継ぎます)
     * @param string $actionName
     * @return string
     */
    protected function redirect($actionName) {
        return "redirect:{$actionName}";
    }    
    
}

?>
