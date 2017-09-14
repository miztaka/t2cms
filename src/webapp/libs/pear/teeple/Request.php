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
 * POST/GETで受け取った値を格納する
 *
 * @package     teeple
 */
class Teeple_Request
{
    /**
     * @return Teeple_Request
     */
    public static function instance() {
        return Teeple_Container::getInstance()->getComponent(__CLASS__);
    }
    
    /**
     * POT/GETで受け取った値を保持する
     * @var array
     */
    private $_params;
    
    /**
     * @var array
     */
    private $errorList = array();
    
    /**
     * @var array
     */
    private $_notification = array();
    
    /**
     * @var string
     */
    private $filterError;
    
    /**
     * @var string
     */
    private $actionMethod;
    
    /**
     * Actionの実行が完了したかどうか
     * @var boolean
     */
    private $completeActionFlg = FALSE;
    
    /**
     * Responseの返却が完了したかどうか
     * @var boolean
     */
    private $completeResponseFlg = FALSE;
    
    /**
     * CSRFトークンをformに強制的にセットするかどうか
     * @var voolean
     */
    public $forceTokenFlg = FALSE;
    
    /**
     * 
     * @var boolean
     */
    public $isRedirect = FALSE;
    
    private $_encoding = array();
    
    private $_pathinfo = NULL;
    
    /**
     * @var Logger
     */
    private $log;

    /**
     * コンストラクタ
     *
     */
    public function __construct() {

        $this->log = LoggerManager::getLogger(get_class($this));
        
        $request = array_merge($_POST, $_GET);
        if (get_magic_quotes_gpc()) {
            $request = $this->_stripSlashesDeep($request);
        }
        if (!ini_get("mbstring.encoding_translation") &&
            (INPUT_CODE != INTERNAL_CODE)) {
             mb_convert_variables(INTERNAL_CODE, INPUT_CODE, $request);
        }
        
        // action:~ではじまるパラメータがあればactionMethodをセットする
        $methodName = "execute";
        $key = NULL;
        foreach($request as $k => $val) {
            if (preg_match('/^action:(.+)$/', $k, $m)) {
                $methodName = $m[1];
                $this->log->debug("actionMethodが指定されました。 {$methodName}");
                $key = $k;
                break;
            }
        }
        $this->actionMethod = $methodName;
        if ($key != NULL) {
            unset($request[$key]);
        }

        $this->_params = $request;
        
        return;
    }
    
    /**
     * stripslashes() 関数を再帰的に実行する
     *
     * @param   mixed  $value  処理する変数
     * @return  mixed  処理結果
     * @see     http://www.php.net/manual/ja/function.stripslashes.php#AEN181588
     */
    private function _stripSlashesDeep($value) {

        if (is_array($value)) {
            $value = array_map(array($this, '_stripSlashesDeep'), $value);
        } else {
            $value = stripslashes($value);
        }
        return $value;
    }

    /**
     * REQUEST_METHODの値を返却
     *
     * @return  string  REQUEST_METHODの値
     */
    public function getMethod() {
        return $_SERVER["REQUEST_METHOD"];
    }

    /**
     * POST/GETの値を返却
     *
     * @param   string  $key    パラメータ名
     * @return  string  パラメータの値
     */
    public function getParameter($key) {
        return isset($this->_params[$key]) ?
            $this->_params[$key] : NULL;
    }

    /**
     * POST/GETの値をセット
     *
     * @param   string  $key    パラメータ名
     * @param   string  $value  パラメータの値
     */
    function setParameter($key, $value) {
        $this->_params[$key] = $value;
    }

    /**
     * リクエストパラメータを削除する
     *
     * @param string $key
     */
    function removeParameter($key) {
        unset($this->_params[$key]);
    }
    
    /**
     * POST/GETの値を返却(配列で返却)
     *
     * @param   string  $key    パラメータ名
     * @return  array  パラメータの値(配列)

     */
    function getParameters() {
        return $this->_params;
    }

    /**
     * エラーメッセージを追加します。
     *
     * @param string $message
     * @param string $target
     */
    public function addErrorMessage($message, $target="__DEFAULT") {
        
        if (! isset($this->errorList[$target])) {
            $this->errorList[$target] = array();
        }
        $this->errorList[$target][] = $message;
        return;
    }
    
    /**
     * エラーがあるかどうか
     *
     * @return boolean
     */
    public function isError() {
        return count($this->errorList) > 0;
    }
    
    /**
     * 指定されたtargetのエラーメッセージを取得します。
     *
     * @param string $target
     * @return array
     */
    public function getErrorMessages($target=NULL) {
        
        if ($target == NULL) {
            return $this->getAllErrorMessages();
        }
        return isset($this->errorList[$target]) ?
            $this->errorList[$target] : array();
    }
    
    /**
     * 指定されたtargetのエラーがあるかどうか
     * @param string $target
     */
    public function hasError($target) {
        return isset($this->errorList[$target]);
    }    
    
    /**
     * 全てのエラーメッセージを取得します。
     *
     * @return array
     */
    public function getAllErrorMessages() {
        
        $allmessages = array();
        foreach($this->errorList as $target => $errors) {
            $allmessages = array_merge($allmessages, $errors);
        }
        return $allmessages;
    }
    
    /**
     * エラーメッセージをクリアします。
     *
     */
    public function resetErrorMessages() {
        $this->errorList = array();
        return;
    }

    /**
     * 通知メッセージを追加します。
     * @param string $msg
     */
    public function addNotification($msg) {
        array_push($this->_notification, $msg);
    }
    
    /**
     * 通知メッセージを取得します。
     * @return array
     */
    public function getNotification() {
        return $this->_notification;
    }
    
    /**
     * 通知メッセージがあるかどうか
     * @return bool
     */
    public function hasNotification() {
        return count($this->_notification) > 0;
    }
    
    public function setFilterError($errorType) {
        $this->filterError = $errorType;
        return;
    }
    
    public function getFilterError() {
        return $this->filterError;
    }
    
    public function isFilterError() {
        return isset($this->filterError) && strlen($this->filterError);
    }
    
    public function getActionMethod() {
        return $this->actionMethod;
    }
    
    public function setActionMethod($method) {
        $this->actionMethod = $method;
    }
    
    
    /**
     * Actionの完了をセットします。 
     *
     */
    public function completeAction() {
        $this->completeActionFlg = TRUE;
    }
    
    /**
     * Responseの完了をセットします。
     *
     */
    public function completeResponse() {
        $this->completeActionFlg = TRUE;
        $this->completeResponseFlg = TRUE;
    }
    
    /**
     * Actionが完了したかどうか？
     * @return boolean
     */
    public function isCompleteAction() {
        return $this->completeActionFlg;
    }
    
    /**
     * Responseが完了したかどうか？
     * @return boolean
     */
    public function isCompleteResponse() {
        return $this->completeResponseFlg;
    }
    
    public function resetCompleteFlag() {
        $this->completeActionFlg = FALSE;
        $this->completeResponseFlg = FALSE;
    }    

    /**
     * HTTPSかどうか
     */
    public function isHttps() {
        return isset($_SERVER['HTTPS']) 
            && ! Teeple_Util::isBlank($_SERVER['HTTPS'])
            && $_SERVER['HTTPS'] !== 'off';
    }
    
    /**
     * 指定されたキーのEncodingを取得する
     * @param $key INPUT_CODE, OUTPUT_CODE, TEMPLATE_CODEのいずれか
     */
    public function getEncoding($key) {
        if (isset($this->_encoding[$key])) {
            return $this->_encoding[$key];
        }
        return constant($key);
    }
    
    /**
     * 指定されたキーのEncodingをセットします。
     * @param $key
     * @param $value
     */
    public function setEncoding($key, $value) {
        $this->_encoding[$key] = $value;
    }
    
    /**
     * 指定されたEncodingからリクエストパラメータを変換します。
     * @param string $encoding INPUT_ENCODING
     */
    public function convertEncoding($encoding) {
        if (!ini_get("mbstring.encoding_translation") && ($encoding != INTERNAL_CODE)) {
            mb_convert_variables(INTERNAL_CODE, $encoding, $this->_params);
        }
    }
    
    public function getPathInfo() {
        if ($this->_pathinfo) {
            return $this->_pathinfo;
        }
        return Teeple_Util::getPathInfo();
    }
    
    public function setPathInfo($path) {
        $this->_pathinfo = $path;
    }
    
}
?>