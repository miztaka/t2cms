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
 * Transactionクラスです。
 * 
 * @package teeple
 */
class Teeple_Transaction {

    /**
     * @var Teeple_DataSource
     */
    private $dataSource;
    public function setComponent_Teeple_DataSource($c) {
        $this->dataSource = $c;
    }
    
	/**
	 * コネクションを格納する配列です。
	 */
	private $connection = array();

	private $isstart = false;
	private $isclose = false;
	
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
	}
	
	/**
	 * トランザクションを開始します。
	 *
	 */
	public function start() {
        if ($this->isclose == true) {
            $this->log->error('トランザクションを開始しようとしましたが既に終了しています。');
            throw new Teeple_Exception('トランザクションを開始しようとしましたが既に終了しています。');
        }
	    if ($this->isstart == true) {
	        $this->log->info("トランザクションを開始しようとしましたが既に開始されています。");
	        return;
	    }
	    $this->log->info("トランザクションを開始します。");
        foreach($this->connection as $name => $conn) {
            $conn->beginTransaction();
        }
	    $this->isstart = true;
	}
	
	/**
	 * トランザクションをコミットします。
	 *
	 */
    public function commit() {
        if ($this->isclose == true) {
            $this->log->info('トランザクションをコミットしようとしましたが既に終了しています。');
            throw new Teeple_Exception('トランザクションをコミットしようとしましたが既に終了しています。');
        }
        if ($this->isstart == false) {
            $this->log->info("トランザクションをコミットしようとしましたがまだ開始されていません。");
            return;
        }
        $this->log->info("トランザクションをコミットします。");
        foreach($this->connection as $name => $conn) {
            $conn->commit();
        }
        //$this->isclose = true;
        $this->isstart = false;
        $this->log->info("トランザクションをコミットしました。");        
        return;
    }
	
    /**
     * トランザクションをロールバックします。
     *
     */
    public function rollback() {
        if ($this->isclose == true) {
            $this->log->error('トランザクションをロールバックしようとしましたが既に終了しています。');
            throw new Teeple_Exception('トランザクションをロールバックしようとしましたが既に終了しています。');
        }
        if ($this->isstart == false) {
            $this->log->info("トランザクションをロールバックしようとしましたがまだ開始されていません。");
            return;
        }
        $this->log->info("トランザクションをロールバックします。");
        foreach($this->connection as $name => $conn) {
            $conn->rollback();
        }
        $this->isclose = true;
        $this->log->info("トランザクションをロールバックしました。");
        return;
    }
    
    /**
     * Entityを取得するマジックメソッドです。
     *
     * @param string $name
     * @return Teeple_ActiveRecord
     */
    public function __get($name) {
        $this->log->debug("エンティティ $name を取得します。");
        if (preg_match('/^Entity_/', $name)) {
            $ref = new ReflectionClass($name);
            $ds = $ref->getStaticPropertyValue('_DATASOURCE');
            if ($ds == "") {
                $ds = DEFAULT_DATASOURCE;
            }
            $this->log->debug("データソース: {$ds}");
            if (isset($this->connection[$ds])) {
                $conn = $this->connection[$ds];
            } else {
                $conn = $this->dataSource->getConnection($ds);
                $this->connection[$ds] = $conn;
                if ($this->isstart) {
                    $conn->beginTransaction();
                }
            }
            return new $name($conn->getDB());
        }
        return NULL;
    }
    
    public function isStarted() {
        return $this->isstart;
    }
    
    public function isClosed() {
        return $this->isclose;
    }
	
}

?>