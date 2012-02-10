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
 * DB Migrationを行なうロジックです。
 *
 * @package teeple
 */
class Teeple_Migration {
    
    const TABLE_NAME = 'migration';

    /**
     * @var Logger
     */
    protected $log;

    /**
     * @var Teeple_DataSource
     */
    protected $dataSource;
    public function setComponent_Teeple_DataSource($c) {
        $this->dataSource = $c;
    }
    
    /**
     * 
     * @var PDO
     */
    protected $pdo;

    /**
     * コンストラクタです。
     */
    public function __construct() {
        $this->log = LoggerManager::getLogger(get_class($this));
    }

    /**
     * Migrationを実行します。
     * 
     */
    public function execute() {
        $config = $this->dataSource->getDataSourceConfig();
        foreach ($config as $name => $dsn) {
            print("** start migration for $name. \n");
            $conn = $this->dataSource->getConnection($name);
            $this->pdo = $conn->getDB();
            
            // DBのmigratoin番号を取得
            $remote_num = $this->getRemoteNum();
            print("remote: {$remote_num}\n");
            
            // ローカルのmigration番号を取得
            $local = new Teeple_Migration_Local(dirname(BASE_DIR)."/migration/".$name);
            $local_num = $local->getMaxNum();
            print("local: {$local_num}\n");
            
            while ($remote_num < $local_num) {
                $remote_num++;
                print("apply {$remote_num}..");
                $ddl = $local->getContent($remote_num);
                $ddllist = explode(';', $ddl);
                try {
                    foreach ($ddllist as $q) {
                        $q = trim($q);
                        if (strlen($q)) {
                            $stmt = $this->pdo->prepare($q);
                            $stmt->execute();
                        }
                    }
                } catch (Exception $e) {
                    print("fail.\n");
                    print($e->getMessage());
                    return;
                }
                $this->pdo->beginTransaction();
                $stmt = $this->pdo->prepare("UPDATE ".self::TABLE_NAME." SET version = {$remote_num}");
                $stmt->execute();
                $this->pdo->commit();
                print("success.\n");
            }
            print("** finish migration for $name. \n");
        }
        
        // --entityがセットされていたらEntityも更新する
        if (isset($this->_argv) && in_array('--entity', $this->_argv)) {
            print("\n*** update entity class.\n");
            $entityGenerator = Teeple_Container::getInstance()->getComponent('Teeple_EntityGenerator');
            $entityGenerator->_argv = array('--force');
            $entityGenerator->execute();
        }
    }
    
    /**
     * DBのmigration番号を取得します
     */
    protected function getRemoteNum() {
        
        if (! $this->migrationExists()) {
            $this->createTable();
        }
        $stmt = $this->pdo->prepare("SELECT * FROM ".self::TABLE_NAME);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $stmt->closeCursor();
        return $result[0];
    }
    
    protected function migrationExists() {
        $stmt = $this->pdo->prepare('SHOW TABLES');
        $stmt->execute();
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $stmt->closeCursor();
        return in_array(self::TABLE_NAME, $tables);
    }
    
    protected function createTable() {
        $stmt = $this->pdo->prepare("CREATE TABLE ".self::TABLE_NAME." (version INT NOT NULL DEFAULT 0)");
        $stmt->execute();
        $stmt = $this->pdo->prepare("INSERT INTO ".self::TABLE_NAME." VALUES (0)");
        $stmt->execute();
    }

}

class Teeple_Migration_Local
{
    protected $files = array();
    protected $dir;
    
    public function __construct($dirname) {
        
        $d = dir($dirname);
        while (false !== ($entry = $d->read())) {
            if (preg_match('/\.sql$/', $entry)) {
                list($num, $rest) = preg_split('/[-_]/', $entry, 2);
                $this->files[intval($num)] = $entry;
            }
        }
        $this->dir = $d;
    }
    
    public function getMaxNum() {
        return count($this->files) > 0 ? max(array_keys($this->files)) : 0;
    }
    
    public function getContent($num) {
        $path = $this->dir->path ."/". $this->files[$num];
        return file_get_contents($path);
    }
    
}

?>