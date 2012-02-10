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

//
//基本となるディレクトリの設定
//
if (!defined('BASE_DIR')) {
	define('BASE_DIR', dirname(dirname(__FILE__)));
}
if (!defined('HTML_DIR')) {
    define('HTML_DIR', dirname(dirname(dirname(__FILE__)))."/html");
}

//
// include_pathの設定
//
define('CONFIG_FILE',   'maple.ini');
if (!defined('PATH_SEPARATOR')) {
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        define('PATH_SEPARATOR', ';');
    } else {
        define('PATH_SEPARATOR', ':');
    }
}
ini_set('include_path', BASE_DIR.'/libs/pear' . PATH_SEPARATOR . ini_get('include_path'));
ini_set('include_path', BASE_DIR.'/components' . PATH_SEPARATOR . ini_get('include_path'));
ini_set('include_path', BASE_DIR.'/htdocs_app' . PATH_SEPARATOR . ini_get('include_path'));

//
//基本となる定数の読み込み
//
Teeple_GlobalConfig::loadConstantsFromFile(dirname(__FILE__) .'/constants.ini');
mb_internal_encoding(INTERNAL_CODE);

//
// 基本クラスの読み込み
//
//ini_set('include_path', LOG4PHP_DIR . PATH_SEPARATOR . ini_get('include_path'));
//include_once LOG4PHP_DIR .'/Logger.php';
include_once LOG4PHP_DIR .'/LoggerManager.php';

//
// autoload
//
ini_set('unserialize_callback_func', 'loadComponentClass');
function loadComponentClass($name) {
    include_once 'teeple/Util.php';
    Teeple_Util::includeClassFile($name);
}
function __autoload($clsname) {
    loadComponentClass($clsname);
}
if (function_exists('spl_autoload_register')) {
    spl_autoload_register('loadComponentClass');
}

?>