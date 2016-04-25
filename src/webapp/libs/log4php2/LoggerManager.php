<?php 

if (!defined('LOG4PHP_DIR')) define('LOG4PHP_DIR', dirname(__FILE__));

require_once(LOG4PHP_DIR . '/Logger.php');

/**
 * log4php 1.x との互換性のために作ったクラス。wrapper
 * @author miztaka
 *
 */
class LoggerManager {
	
	public static function getLogger($name) {
		return Logger::getLogger($name);
	}

}
