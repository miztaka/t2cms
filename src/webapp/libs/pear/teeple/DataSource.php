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
 * DataSourceクラスです。
 * 
 * @package teeple
 */
class Teeple_DataSource {
    
    /**
     * DB接続情報を格納します。
     *
     * @var array
     */
    private static $ds;
    
	/**
	 * コネクションを格納する配列です。
	 */
	private $connection = array();

	/**
	 * DB接続情報を設定します。
	 *
	 * @param array $ds DB接続情報
	 */
	public static function setDataSource($ds) {
	    self::$ds = $ds;
	}
	
	/**
	 * 指定されたDB名のConnectionを取得します。
	 * 
	 * @param dbname string DB名
	 * @return Teeple_DBConn DBConnオブジェクト 
	 */	
	public function getConnection($dbname) {

        $conn = new Teeple_DBConn(
            self::$ds[$dbname]['dsn'], 
            self::$ds[$dbname]['user'],
            self::$ds[$dbname]['pass']);
        $this->connection[] = $conn;
		return $conn;
	}
	
	/**
	 * 全ての接続をクローズします。
	 * 
	 */
	public function closeAll() {
		foreach ($this->connection as $conn) {
			if ($conn != NULL) {
				$conn->close();
			}
		}
	}
	
	/**
	 * DataSourceの設定を取得します。
	 */
	public function getDataSourceConfig() {
	    return self::$ds;
	}
	
}

?>