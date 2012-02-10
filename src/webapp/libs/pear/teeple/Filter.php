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
 * Filterのインタフェースを規定するクラス
 * 各フィルタはprefilter()とpostfilter()を実装する必要があります。
 *
 * @package     teeple
 */
abstract class Teeple_Filter
{
    /**
     * 必要に応じて属性を持つことができる
     * @var array
     */
    protected $_attributes = array();

    /**
     * @var Logger
     */
    protected $log;
    
    /**
     * @var Teeple_FilterChain
     */
    protected $filterChain;
    public function setComponent_Teeple_FilterChain($c) {
        $this->filterChain = $c;
    }
    
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
     * @var Teeple_Session
     */
    protected $session;
    public function setComponent_Teeple_Session($c) {
        $this->session = $c;
    }
    
    /**
     * コンストラクタ
     *
     */
    public function __construct() {
        $this->log = LoggerManager::getLogger(get_class($this));
    }

    /**
     * Filterの処理を実行します。
     *
     */
    public function execute() {

        $clsname = get_class($this);
        
        // 現在のアクション名を取得
        $actionName = $this->actionChain->getCurActionName();
        
        // 実行対象かどうかのチェック
        $isTarget = $this->isTarget($actionName);
        if (! $isTarget) {
            $this->log->info("{$clsname} は実行対象外のためスキップします。");
        }
        
        // prefilterを実行
        if ($isTarget) {
            $this->log->debug("{$clsname}のprefilterを実行します。");
            $this->prefilter();
            $this->log->debug("{$clsname}のprefilterを実行しました。");
        }
        
        // filterChain
        $this->filterChain->execute();
        
        // postfilterを実行
        if ($isTarget) {
            $this->log->debug("{$clsname}のpostfilterを実行します。");
            $this->postfilter();
            $this->log->debug("{$clsname}のpostfilterを実行しました。");
        }
        
        return;
    }
    
    /**
     * アクションの実行前に実行される処理を記述します。
     *
     */
    abstract public function prefilter();
    
    /**
     * アクションの実行後に実行される処理を記述します。
     *
     */
    abstract public function postfilter();
    
    /**
     * アクションの実行完了をセットします。
     *
     */
    protected function completeAction() {
        $this->filterChain->completeAction();
    }
    
    /**
     * レスポンスの返却完了をセットします。
     *
     */
    protected function completeResponse() {
        $this->filterChain->completeResponse();
    }
    
    protected function isCompleteAction() {
        return $this->filterChain->isCompleteAction();
    }
    
    protected function isCompleteResponse() {
        return $this->filterChain->isCompleteResponse();
    }

    /**
     * 属性の数を返却
     *
     * @return  integer 属性の数
     */
    public function getSize() {
        return count($this->_attributes);
    }

    /**
     * 指定された属性を返却
     *
     * @param   string  $key    属性名
     * @param   mixed  $default 指定された属性がない場合のデフォルト値
     * @return  string  属性の値
     */
    public function getAttribute($key, $default = null) {
        
        return isset($this->_attributes[$key]) ?
            $this->_attributes[$key] : $default;
    }

    /**
     * 指定された属性に値をセット
     *
     * @param   string  $key    属性名
     * @param   string  $value  属性の値
     */
    public function setAttribute($key, $value) {
        $this->_attributes[$key] = $value;
    }

    /**
     * 属性を配列で返却
     *
     * @return  array   属性の値(配列)
     */
    public function getAttributes() {
        return $this->_attributes;
    }

    /**
     * 指定された属性に値をセット(配列でまとめてセット)
     *
     * @param   array   $attributes 属性の値(配列)
     */
    public function setAttributes($attributes) {

        if (is_array($attributes)) {
            foreach ($attributes as $key => $value) {
                $this->setAttribute($key, $value);
            }
        }
        return;
    }
    
    /**
     * Filter実行対象のアクションかどうかをチェックします。
     *
     * @param string $actionName
     * @return boolean
     */
    private function isTarget($actionName) {
        
        // excludesに含まれている場合は無条件に対象外
        if (@is_array($this->_attributes['excludes'])) {
            foreach($this->_attributes['excludes'] as $pattern) {
                if (preg_match("/^{$pattern}$/", $actionName)) {
                    return FALSE;
                }
            }
        }
        
        // includeが設定されている場合は、includeに含まれていないとだめ。
        if (! @is_array($this->_attributes['includes'])) {
            return TRUE;
        } else {
            foreach($this->_attributes['includes'] as $pattern) {
                if (preg_match("/^{$pattern}$/", $actionName)) {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }
    
}
?>
