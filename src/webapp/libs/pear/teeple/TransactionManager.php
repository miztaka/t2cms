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
 * TransactionManager - トランザクションを管理するクラスです。
 * 
 * @package teeple
 *
 */
class Teeple_TransactionManager {
    
    /**
     * @var Teeple_Container
     */
    private $container;
    public function setComponent_container($c) {
        $this->container = $c;
    }
    
    private $txs = array();
    
    /**
     * 新しいトランザクションを取得します。
     *
     * @return Teeple_Transaction
     */
    public function getTransaction() {
        $tx = $this->container->getPrototype('Teeple_Transaction');
        $this->txs[] = $tx;
        return $tx;
    }
    
    /**
     * 全てのトランザクションをコミットします。
     *
     */
    public function commitAll() {
        foreach($this->txs as $tx) {
            if ($tx->isStarted() && ! $tx->isClosed()) {
                $tx->commit();
            }
        }
        return;
    }
    
    /**
     * 全てのトランザクションをロールバックします。
     *
     */
    public function rollbackAll() {
        foreach($this->txs as $tx) {
            if ($tx->isStarted() && ! $tx->isClosed()) {
                $tx->rollback();
            }
        }
        return;
    }
    
}
?>