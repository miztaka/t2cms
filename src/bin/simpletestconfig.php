<?php 

/*
 * Teeple設定ファイルの読込み
 */
define('BASE_DIR', dirname(dirname(__FILE__)) ."/webapp");
include_once BASE_DIR . "/config/user.inc.php";

/*
 * コンテナからComponentを取得
 */
$container = Teeple_Container::getInstance();

// デフォルトトランザクションをコンテナに登録
$txManager = $container->getComponent("Teeple_TransactionManager");
$defaultTx = $txManager->getTransaction();
$container->register('DefaultTx', $defaultTx);

/*
 * simpletest
 */
define('SIMPLETEST_DIR', BASE_DIR ."/libs/simpletest");
require_once SIMPLETEST_DIR."/autorun.php";

?>