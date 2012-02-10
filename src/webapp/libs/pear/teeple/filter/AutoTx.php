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
 * 自動トランザクション制御
 *
 * @package     teeple.filter
 */
class Teeple_Filter_AutoTx extends Teeple_Filter
{
    
    /**
     * @var Teeple_Transaction
     */
    private $defaultTx;
    public function setComponent_DefaultTx($c) {
        $this->defaultTx = $c;
    }
    
    /**
     * コンストラクタ
     *
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * トランザクションを開始します。
     *
     */
    public function prefilter() {

        if ($this->isCompleteAction()) {
            $this->log->info("CompleteActionフラグが立っているため、AutoTxは実行されませんでした。");
            return;
        }
        
        if (! $this->defaultTx->isStarted()) {
            $this->defaultTx->start();
        }
        
        return;
    }
    
    /**
     * トランザクションをコミットします。
     *
     */
    public function postfilter() {
        
        if (! $this->defaultTx->isClosed()) {
            $this->defaultTx->commit();
        }
        return;
    }
}
?>
