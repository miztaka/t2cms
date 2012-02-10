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
 * Actionの実行準備および実行を行うFilter
 *
 * @package     teeple.filter
 */
class Teeple_Filter_Action extends Teeple_Filter
{
    
    /**
     * コンストラクタ
     *
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Actionを実行
     *
     */
    public function prefilter() {

        if ($this->isCompleteAction()) {
            $this->log->info("CompleteActionフラグが立っているため、Actionは実行されませんでした。");
            return;
        }
        
        // ActionChainの実行
        $this->actionChain->execute();
        $this->completeAction();
        return;
    }
    
    public function postfilter() {
        return;
    }
}
?>
