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
 * DIコンテナ
 *
 * @package     teeple
 */
class Teeple_Container
{

    const SESSION_KEY = '__SESSION_COMPONENTS';
    
    /**
     * コンポーネントのクラス名を変更したいときに定義します。
     * @var array
     */
    public static $namingDefs = array();
    
    /**
     * Teeple_Container
     * @var unknown_type
     */
    private static $instance;
    
    /**
     * Containerとして管理するインスタンスを格納
     * @var array
     */
    protected $_components = array();

    /**
     * 設定内容を保持する配列
     * @var array
     */
    //protected $_dicon = array();
    
    /**
     * @var Logger
     */
    protected $log;
    
    /**
     * ContainerのSingletonインスタンスを取得します。
     *
     * @return Teeple_Container
     */
    public static function getInstance() {

        if (self::$instance === NULL) {
            self::$instance = new Teeple_Container();
        }
        return self::$instance;
    }

    /**
     * コンストラクタ
     *
     */
    public function __construct() {
        $this->log = LoggerManager::getLogger(get_class($this));
        // 自分を登録
        $this->_components['container'] = $this;
    }

    /**
     * コンテナをセットアップします。
     *
     * @param string $configfile
     */
    /*
    public function setup($configfile) {

        $config = parse_ini_file($configfile, TRUE);
        if (count($config) < 1) {
            throw new Teeple_Exception("コンテナの設定ファイルを読込めませんでした。");
        }
        
        foreach($config as $key => $value) {
            list($clsname, $path) = explode(':', $key);
            $this->_dicon[$clsname] = array(
                'path' => $path,
                'attributes' => $value
            );
        }
        
    }
    */
    
    /**
     * ContainerにComponentのインスタンスをセット
     *
     * @param   string  $name   Component名
     * @param   Object  $component  Componentのインスタンス
     */
    public function register($name, $component) {
        if (!is_object($component)) {
            throw new Teeple_Exception("登録しようとしたコンポーネントはオブジェクトではありません。({$name})");
            return;
        }
        $this->_components[$name] = $component;
        return;
    }
    
    /**
     * Componentのインスタンスを取得します。
     * (RequestスコープのComponent)
     *
     * @param   string  $name   Component名
     * @return  Object  Componentのインスタンス
     */
    public function getComponent($name) {
        
        $component = NULL;
        if (isset($this->_components[$name])) {
            $component = $this->_components[$name];
        } else {
            $component = $this->_createComponent($name);
        }
        
        return $component;
    }
    
    /**
     * SessionスコープのComponentを取得します。
     *
     * @param string $name
     * @return Object
     */
    public function getSessionComponent($name) {

        $component = NULL;
        if (! isset($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = array();
        }
        if (isset($_SESSION[self::SESSION_KEY][$name])) {
            $component = $_SESSION[self::SESSION_KEY][$name];
        } else {
            $this->log->debug("セッションに存在しないので作成します。");
            $component = $this->_createComponent($name, FALSE);
            $_SESSION[self::SESSION_KEY][$name] = $component;
        }
        
        return $component;
    }

    /**
     * prototypeのComponentを取得します。
     *
     * @param string $name
     * @return Object
     */
    public function getPrototype($name) {
        return $this->_createComponent($name, FALSE);
    }

    /**
     * Teeple_ActiveRecordのエンティティを取得します。
     * DefaultTxから取得されます。
     *
     * @param string $name
     * @return Teeple_ActiveRecord
     */
    public function getEntity($name) {
        
        $defaultTx = $this->getComponent('DefaultTx');
        if (is_object($defaultTx)) {
            return $defaultTx->$name;
        }
        return NULL;
    }
    
    /**
     * コンポーネントをインスタンス化します。
     *
     * @param string $name
     * @return Object
     */
    private function _createComponent($name, $register=TRUE) {
        
        $this->log->debug("コンポーネント {$name} を作成します。");

        // インスタンスを作成
        if (isset(self::$namingDefs[$name])) {
            $className = self::$namingDefs[$name];
            $this->log->debug("クラス名は {$className}です。");
        } else {
            $className = $name;
        }
        if (! Teeple_Util::includeClassFile($className)) {
            throw new Teeple_Exception("クラス{$className}の定義が存在しません。");
        }
        $instance = new $className();
        if ($register) {
            $this->register($name, $instance);
        }
        
        // 自動インジェクション
        $methods = get_class_methods($className);
        foreach($methods as $method) {
            if (preg_match('/^setComponent_(.+)$/', $method, $m)) {
                $this->log->debug("自動セット: {$m[1]}");
                $cp = $this->getComponent($m[1]);
                $instance->$method($cp);
            }
            elseif (preg_match('/^setSessionComponent_(.+)$/', $method, $m)) {
                $this->log->debug("自動セット(s): {$m[1]}");
                $cp = $this->getSessionComponent($m[1]);
                $instance->$method($cp);
            }
            elseif (preg_match('/^setPrototype_(.+)$/', $method, $m)) {
                $this->log->debug("自動セット(p): {$m[1]}");
                $cp = $this->getPrototype($m[1]);
                $instance->$method($cp);
            }
            elseif (preg_match('/^set(Entity_.+)$/', $method, $m)) {
                $this->log->debug("自動セット(e): {$m[1]}");
                $cp = $this->getEntity($m[1]);
                $instance->$method($cp);
            }
        }
        
        return $instance;
    }

}
?>
