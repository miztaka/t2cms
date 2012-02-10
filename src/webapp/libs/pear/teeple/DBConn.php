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
 * DB接続を表すオブジェクトです。
 * 
 * @package teeple
 */
class Teeple_DBConn {

	/**
	 * PDOオブジェクトを格納します。
	 */
	private $pdo;
	
	private $id;
	
	/**
	 * @var Logger
	 */
	private $log;
	
	/**
	 * コンストラクタです。
	 * 
	 * @param string $dsn DSN文字列
	 * @param string $user user名
	 * @param string $pass password
	 */
	public function __construct($dsn, $user, $pass) {
	    $this->log = LoggerManager::getLogger(get_class($this));
		$this->pdo = new PDO($dsn, $user, $pass);
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		// TODO 是非は微妙
		$this->pdo->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, TRUE);
		$this->pdo->exec('SET CHARACTER SET utf8');
		$this->id = mt_rand();
		$this->log->debug("DBConnを作成しました。({$this->id})");
	}

	/**
	 * トランザクションを開始します。
	 */
	public function beginTransaction() {
		$this->pdo->beginTransaction();
		$this->log->debug("コネクション({$this->id})のトランザクションを開始しました。");
	}
		
	/**
	 * トランザクションをコミットします。
	 */
	public function commit() {
		$this->pdo->commit();
		$this->log->debug("コネクション({$this->id})をコミットしました。");
	}
	
	/**
	 * トランザクションをロールバックします。
	 */
	public function rollback() {
		$this->pdo->rollBack();
		$this->log->debug("コネクション({$this->id})をロールバックしました。");
	}		

	/**
	 * DBを取得します。
	 * 
	 * @return Object PDOオブジェクト。
	 */
	public function getDB() {
		return $this->pdo;
	}
	
	/**
	 * コネクションをクローズします。
	 * 
	 */
	public function close() {
		$this->pdo = NULL;
	}
}

?>