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
 * Filterを保持するクラス
 *
 * @package     teeple
 */
class Teeple_FilterChain
{
    /**
     * Filterを保持する
     * @var array
     */
    protected $_list = array();

    /**
     * Filterの位置を保持する
     * @var array
     */
    protected $_position = array();

    /**
     * 現在実行されているFilterの位置を保持する
     * @var int
     */
    protected $_index = -1;

    /**
     * @var Logger
     */
    protected $log;
    
    
    /**
     * @var Teeple_Container
     */
    private $container;
    public function setComponent_container($c) {
        $this->container = $c;
    }
    
    /**
     * @var Teeple_Request
     */
    private $request;
    public function setComponent_Teeple_Request($c) {
        $this->request = $c;
    }
        
    /**
     * コンストラクタ
     *
     */
    public function __construct() {
        $this->log = LoggerManager::getLogger(get_class($this));
    }

    /**
     * FilterChainの最後にFilterを追加
     *
     * @param   string  $name   Filterのクラス名
     * @param   string  $alias  Filterのエイリアス名
     * @param   array   $attributes Filterの属性値
     */
    public function add($name, $alias = '', $attributes = NULL) {

        // エイリアス名が指定されていない場合はクラス名をセット
        if (empty($alias)) {
            $alias = $name;
        }

        // Filterの実行が既に始まっていたらエラー(実行後の追加はエラー)
        if ($this->_index > -1) {
            throw new Teeple_Exception("既にフィルターが実行されています。");
        }

        // Filterのクラス名が不正だったらエラー
        if (!preg_match("/^[0-9a-zA-Z_]+$/", $name)) {
            throw new Teeple_Exception("フィルターのクラス名が不正です。({$name})");
        }

        // 既に同名のFilterが追加されていたら何もしない
        if (isset($this->_list[$alias]) && is_object($this->_list[$alias])) {
            $this->log->info("このFilterは既に登録されています(${name}[alias:${alias}])");
            return;
        }

        // オブジェクトの生成に失敗していたらエラー
        $className = "Teeple_Filter_" . ucfirst($name);
        $filter = $this->container->getComponent($className);
        if (!is_object($filter)) {
            throw new Teeple_Exception("Filterオブジェクトの生成に失敗しました。({$name})");
        }
        if (is_array($attributes)) {
            $filter->setAttributes($attributes);
        }        

        $this->_list[$alias] = $filter;
        $this->_position[] = $alias;

        return;
    }

    /**
     * FilterChainをクリア
     *
     */
    public function clear() {

        $this->_list     = array();
        $this->_position = array();
        $this->_index    = -1;
    }

    /**
     * FilterChainの長さを返却
     *
     * @return  integer FilterChainの長さ
     */
    public function getSize() {
        return count($this->_list);
    }

    /**
     * FilterChainを組み立てる
     *
     * @param string $configfile
     */
    public function build($configfile = TEEPLE_FILTER_CONFIG) {
        
        $this->log->debug("FilterChainをセットアップします。");
        
        $config = Teeple_Util::readIniFile($configfile);
        //$this->log->debug(var_export($config, true));
        if (is_array($config)) {
            foreach ($config as $section => $value_ar) {
                // フィルタ名とエイリアス名を取得
                $sections = explode(':', $section);
                $filterName = $sections[0]; // フィルタ名
                $alias = $filterName;
                if (isset($sections[1]) && $sections[1]) {
                    $alias = $sections[1]; // エイリアス名
                }
                // FilterChainに追加
                $this->add($filterName, $alias, $value_ar);
            }
        }
        
        // 最後にActionとViewを追加する。
        $this->add('Action');
        $this->add('View');
        
        return;
    }

    /**
     * FilterChainの中の次のFilterを実行
     *
     * このメソッドはクラスメソッド
     *
     * @access  public
     * @since   3.0.0
     */
    public function execute() {

        if ($this->getSize() < 1) {
            throw new Teeple_Exception("実行するフィルタがありません。");
        }

        if ($this->_index < ($this->getSize() - 1)) {
            $this->_index++;
            $name = $this->_position[$this->_index];
            $filter = $this->_list[$name];
            if (!is_object($filter)) {
                throw new Teeple_Exception("Filterオブジェクトの取得に失敗しました。({$name})");
            }
            $filter->execute();
        }
        
        return;
    }
    
    /**
     * Actionの完了をセットします。
     *
     */
    public function completeAction() {
        $this->request->completeAction();
    }
    
    /**
     * Responseの完了をセットします。
     *
     */
    public function completeResponse() {
        $this->request->completeResponse();
    }
    
    /**
     * Actionが完了したかどうか？
     * @return boolean
     */
    public function isCompleteAction() {
        return $this->request->isCompleteAction();
    }
    
    /**
     * Responseが完了したかどうか？
     * @return boolean
     */
    public function isCompleteResponse() {
        return $this->request->isCompleteResponse();
    }
    
}
?>
