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
 * 設定ファイルの内容を保持する
 *
 * @package     teeple
 */
class Teeple_ConfigUtils
{
    /**
     * @var 各セクションの値を保持する
     *
     * @access  private
     * @since   3.0.0
     */
    var $_config;

    /**
     * 設定情報を一時的に保存する
     * 
     * @var  String  $_configPool
     * @since 3.2.0
     */
    var $_configPool;

    /**
     * Actionフィルタの名称
     * 
     * @var  String  $_actionKey  
     * @since 3.2.0
     */
    var $_actionKey;

    /**
     * Loggerを格納する
     * 
     * @var Logger
     */
    var $log;
    
    /**
     * @var Teeple_ActionChain
     */
    private $actionChain;
    public function setComponent_Teeple_ActionChain($c) {
        $this->actionChain = $c;
    }
    
    /**
     * コンストラクタ
     *
     */
    function __construct()
    {
        $this->clear();
        $this->log = LoggerManager::getLogger(get_class($this));
    }

    /**
     * 設定情報をクリア
     *
     * @access  public
     * @since   3.0.0
     */
    function clear()
    {
        $this->_config     = array();
        $this->_configPool = array();
        $this->_actionKey  = "Action";
    }

    /**
     * 一時保存された情報を取得する
     * 存在しない場合は空の配列を返す
     * 
     * @since  3.2.0
     * @param  String    $key
     * @return array
     */
    function _getPreserved($key)
    {
        return isset($this->_configPool[$key]) ? $this->_configPool[$key] : array();
    }

    /**
     * 設定情報を一時的に保存する
     * 
     * @since  3.2.0
     * @param  String    $key
     * @param  array     $values
     */
    function _preserve($key, $values)
    {
        //To keep the order of keys, can't use array operator '+'.
        foreach($values as $k => $v) {
            $this->_configPool[$key][$k] = $v;
        }
    }

    /**
     * 既に追加されている場合はマージ、
     * そうでなければ一時保存
     * 
     * @since  3.2.0
     * @param  String    $key
     * @param  array     $values
     */
    function _mergeOrPreserve($key, $values)
    {
        if(isset($this->_config[$key])) {
            $this->_mergeOrAdd($key, $values);
        } else {
            $this->_preserve($key, $values);
        }
    }

    /**
     * 既に追加されていたらマージ、
     * そうでなければ新規追加
     * 
     * @since  3.2.0
     * @param  String    $key
     * @param  array     $values
     */
    function _mergeOrAdd($key, $values)
    {
        if(!isset($this->_config[$key])) {
            $this->_config[$key] = $this->_getPreserved($key);
        }
        //To keep the order of keys, can't use array operator '+'.
        foreach($values as $k => $v) {
            $this->_config[$key][$k] = $v;
        }
    }

    /**
     * シンプルに設定を読み込む
     * オプションで読み込むキーを指定することができる
     * 
     * このメソッドではActionフィルタは一時保存されるだけで
     * 登録されない
     * 
     * @since  3.2.0
     * @param  array   $config
     * @param  array   $keys [optional]  keys to be read
     */
    function readSimpleConfig($config, $keys=null)
    {
        if(!is_array($keys)) {
            $keys = array_keys($config);
        }

        foreach($keys as $key) {
            if(!isset($config[$key])) {
                throw new Teeple_Exception("keyの値が不正です。($key)");
            }
            
            if($this->_isActionFilter($key)) {
                $this->_preserve($key, $config[$key]);
                $this->_actionKey = $key;
            } else {
                $this->_mergeOrAdd($key, $config[$key]);
            }
        }
    }

    /**
     * GlobalFilterの処理も含め、
     * 最下層かどうかも加味して、設定を読み込む
     * 
     * このメソッドではActionフィルタは一時保存されるだけで
     * 登録されない
     * 
     * @since  3.2.0
     * @param  array      $config
     * @param  boolean    $isDeepest
     */
    function readConfig($config, $isDeepest)
    {
        $globalFilter = null;
        if(isset($config['GlobalFilter'])) {
            $globalFilter = $config['GlobalFilter'];
            unset($config['GlobalFilter']);
        }

        if($globalFilter === null || $isDeepest) {
            //globalfilterが無い、もしくは最下層
            $this->readSimpleConfig($config);
            return;
        }

        //globalFilter処理
        foreach($config as $key => $values) {
            //ここではActionフィルタかどうかは調べなくて良い
            if(!isset($globalFilter[$key])) {
                $this->_mergeOrPreserve($key, $values);
            }
        }
        $this->readSimpleConfig($config, array_keys($globalFilter));
    }

    /**
     * 設定ファイルを読み込む
     * 
     * このメソッドではActionフィルタは一時保存されるだけで
     * 登録されない
     * 
     * @since  3.2.0
     * @param  String    $filename
     * @param  boolean    $isDeepest
     */
    function readConfigFile($filename, $isDeepest)
    {
        if(file_exists($filename) &&
           ($config = parse_ini_file($filename, true))) {
            
            if (CONFIG_CODE != INTERNAL_CODE) {
                mb_convert_variables(INTERNAL_CODE, CONFIG_CODE, $config);
            }
            $this->readConfig($config, $isDeepest);
        }
    }

    /**
     * アクションに対する全ての設定ファイルを読み込む
     * Debugフィルタは最初に、
     * Actionフィルタは最後に登録する
     * 
     * $readerFuncはテスタビリティのための存在
     * 
     * @since  3.2.0
     * @param  String    $actionName
     * @param  array or string     $readerFunc
     */
    function readConfigFiles($actionName, $readerFunc='readConfigFile')
    {
        $obj = $this;
        $method = $readerFunc;
        if(is_array($readerFunc) && is_callable($readerFunc)) {
            $obj = $readerFunc[0];
            $method = $readerFunc[1];
        }

        $paths    = array_merge(array(""), explode('_', $actionName));
        //$basename = array_pop($paths);
        $crrPath  = MODULE_DIR;
        $depth    = 0;
        $maxDepth = count($paths) - 1;

        $this->log->debug("アクション $actionName のconfigを読み込みます。");
        foreach($paths as $p) {
            $crrPath .= "{$p}/";
            $configPath = $maxDepth == $depth ?
                substr($crrPath, 0, -1).".".CONFIG_FILE : "{$crrPath}". CONFIG_FILE;
                // "{$crrPath}{$basename}.maple.ini" : "{$crrPath}". CONFIG_FILE;
            $this->log->debug("configPath: {$configPath}");
            $obj->$method($configPath, ($maxDepth == $depth++));
        }

        $this->_mergeOrAdd($this->_actionKey, array());
    }
    
    /**
     * 設定ファイルを読み込む
     *
     * @access  public
     * @since   3.0.0
     */
    function execute()
    {
        $this->readConfigFiles($this->actionChain->getCurActionName());
    }

    /**
     * Actionフィルタの一種か調べる
     * 
     * @since  3.2.0
     * @param  String    $key
     * @return boolean
     */
    function _isActionFilter($key)
    {
        return preg_match('/Action$/', $key);
    }
    
    /**
     * セクションの設定情報を返却
     *
     * @return  array   セクションの設定情報の配列
     * @access  public
     * @since   3.0.0
     */
    function getConfig()
    {
        return $this->_config;
    }

    /**
     * 指定されたセクションの設定情報を返却
     *
     * @param   string  $section    セクション名
     * @return  array   設定情報の配列
     * @access  public
     * @since   3.0.0
     */
    function getSectionConfig($section)
    {
        if (isset($this->_config[$section])) {
            return $this->_config[$section];
        }
    }
}
?>
