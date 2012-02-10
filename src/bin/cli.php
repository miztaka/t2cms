<?php

/**
 * CLIからTeeple_Componentを実行します。
 * 
 */
error_reporting(E_ALL);
//error_reporting(0);

if ($argc < 2) {
    fwrite(STDERR, "Invalid Parameter.");
    exit;
}
$cmpName = $argv[1];

$param = $argv;
array_shift($param);
array_shift($param);

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

// 実行するコンポーネントを取得
$component = $container->getComponent($cmpName);
if (! is_object($component)) {
    fwrite(STDERR, "No component found.");
    exit;
}
if (count($param) > 0) {
    $component->_argv = $param;
}

try {
    $component->execute();
} catch (Exception $e) {
    $txManager->rollbackAll();
    Teeple_ErrorHandler::handle($e);
}

?>
