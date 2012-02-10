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
 * Actionを管理するクラス。
 * このクラスを使ってActionのForwardに対応する。
 * @package     teeple
 */
class Teeple_ActionChain
{
    /**
     * Actionのリスト
     * @var array
     */
    protected $_list = array();

    /**
     * @var 現在実行されているActionの位置を保持する
     */
    protected $_index = 0;
    
    /**
     * @var Logger
     */
    protected $log;
    
    /**
     * @var Teeple_Request
     */
    private $request;
    public function setComponent_Teeple_Request($c) {
        $this->request = $c;
    }
    
    /**
     * @var Teeple_Container
     */
    private $container;
    public function setComponent_container($c) {
        $this->container = $c;
    }
    
    /**
     * @var Teeple_Response
     */
    private $response;
    public function setComponent_Teeple_Response($c) {
        $this->response = $c;
    }
    
    /**
     * @var Teeple_ValidatorManager
     */
    private $validatorManager;
    public function setComponent_Teeple_ValidatorManager($c) {
        $this->validatorManager = $c;
    }

    /**
     * @var Teeple_ConverterManager
     */
    private $converterManager;
    public function setComponent_Teeple_ConverterManager($c) {
        $this->converterManager = $c;
    }
    
    /**
     * コンストラクター
     */
    public function __construct() {
        $this->log = LoggerManager::getLogger(get_class($this));
    }

    /**
     * Actionを追加する
     *
     * @param   string  $name   Actionのクラス名
     * @return bool
     */
    public function add($name) {

        // Actionのクラス名をチェック
        if (!preg_match("/^[0-9a-zA-Z_]+$/", $name)) {
            $this->log->debug("Actionクラス名が不正です。 $name");
            return false;
        }

        // Actionクラスのインスタンス化
        $className = Teeple_Util::capitalizedClassName($name);
        if (! Teeple_Util::includeClassFile($className)) {
            $this->log->debug("Actionクラスの生成に失敗しました。({$className})");
            return false;
        }
        $action = $this->container->getPrototype($className);
        $base = 'Teeple_ActionBase';
        if (!is_object($action) || ! $action instanceof $base) {
            $this->log->debug("Actionクラスの生成に失敗しました。({$className})");
            return false;
        }
        array_push($this->_list, $action);
        return true;
    }

    /**
     * ActionChainをクリアする。
     *
     */
    public function clear() {
        $this->_list = array();
        $this->_index     = 0;
    }

    /**
     * ActionChainに次のActionがあるかどうか？
     * @return boolean
     */
    public function hasNext() {
        return ($this->_index < (count($this->_list) - 1));
    }

    /**
     * ActionChainを次に進める
     *
     */
    public function next() {
        
        if ($this->_index >= count($this->_list)) {
            throw new Teeple_Exception("次のアクションはありません。");
        }
        $this->_index++;
        return;
    }

    /**
     * 現在のAction名を返却
     *
     * @return  string  Actionの名前
     */
    public function getCurActionName() {
        return strtolower(get_class($this->getCurAction()));
    }
    
    /**
     * リストの先頭のActionのインスタンスを返却
     *
     * @return Teeple_ActionBase
     */
    public function getCurAction() {
        return $this->_list[$this->_index];
    }
    
    /**
     * ActionChainを実行します。
     *
     */
    public function execute() {
        
        if (count($this->_list) == 0) {
            throw new Teeple_Exception("Actionが1つも登録されていません。");
        }
        
        while (true) {
            // Actionを実行する
            $view = $this->executeAction();
            if ($view == "") {
                $actionName = $this->getCurActionName();
                $view = str_replace('_','/',$actionName).".html";
            }
            // Actionへのフォワードの場合
            if (preg_match("/^action:/", $view)) {
                $action = preg_replace("/action:/", "", $view);
                $this->add(trim($action));
                $this->request->setFilterError(NULL);
            } else {
                $this->response->setView($view);
            }
            // 次へ進む
            if (! $this->hasNext()) {
                break;
            }
            $this->next();
        }
        return;
    }

    /**
     * Actionを実行
     *
     * @return string viewのパス 
     */
    private function executeAction() {

        // 現在のアクションを取得
        $action = $this->getCurAction();
        if (!is_object($action)) {
            throw new Teeple_Exception("Actionオブジェクトが取得できません。");
        }
        $className = get_class($action);
        $this->log->info("アクション {$className} を実行します。");
        
        // メソッド名
        $methodName = $this->request->getActionMethod();
        if (! method_exists($action, $methodName)) {
            $methodName = 'execute';
        }
        
        // Converterの実行
        $params = $this->request->getParameters();
        $this->doConverter($action, $params);
        
        // Requestの値をActionに移す
        if (count($params) > 0) {
            foreach($params as $name => $value) {
                if (preg_match('/^__/', $name)) {
                    continue;
                }
                $action->$name = $params[$name];
            }
        }
        
        if (! $this->request->isFilterError()) {
            // Validatorの実行
            $this->doValidation($action, $methodName);
        }
        
        // エラーが発生している場合は、onXXError()メソッドを実行する。
        if ($this->request->isFilterError()) {
            $type = $this->request->getFilterError();
            $this->log->debug("errortype: $type");
            if ($type != "") {
                $methodName = 'on'.$type.'Error';
                $this->log->info("メソッド {$methodName} を実行します。");
                if (method_exists($action, $methodName)) {
                    return $action->$methodName();
                }
                $this->log->warn("メソッド {$methodName} が存在しません。");
                return NULL;
            }
        }
        
        // Actionメソッドを実行する。
        $this->log->info("メソッド {$methodName} を実行します。");
        return $action->$methodName();
    }
    
    /**
     * バリデーションを実行します。
     *
     * @param Teeple_ActionBase $action
     * @param string $methodName
     */
    private function doValidation($action, $methodName) {
        
        $className = get_class($action);
        if (! defined($className."::VALIDATION_CONFIG")) {
            return;
        }
        if (defined($className."::VALIDATION_TARGET")) {
            $targets = explode(',', constant($className."::VALIDATION_TARGET"));
            array_walk($targets, 'trim');
            if (! in_array($methodName, $targets)) {
                $this->log->info("メソッド{$methodName}はValidation対象ではありません。");
                return;
            }
        }
        
        $validationConfig = $this->validatorManager->parseYAML(constant($className."::VALIDATION_CONFIG"));
        if (! $this->validatorManager->execute($action, $validationConfig)) {
            $this->request->setFilterError('Validate');
        }
        
        return;
    }
    
    /**
     * Converterを実行します。
     *
     * @param Teeple_ActionBase $action
     * @param array $params
     */
    private function doConverter($action, &$params) {
        
        $className = get_class($action);
        if (! defined($className."::CONVERTER_CONFIG")) {
            return;
        }
        
        $yamlConfig = Horde_Yaml::load(constant($className."::CONVERTER_CONFIG"));
        if (! is_array($yamlConfig)) {
            throw new Teeple_Exception("Converterの設定を解析できませんでした。");
        }
        //$this->log->debug(var_export($yamlConfig, true));
        
        $this->converterManager->execute($params, $yamlConfig);
    }

}
?>
