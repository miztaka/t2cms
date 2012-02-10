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
 * Resourceを保持するクラスです。
 *
 * @package teeple
 */
class Teeple_Resource {
    
    /**
     * @return Teeple_Resource
     */
    public static function instance() {
        return Teeple_Container::getInstance()->getComponent(__CLASS__);
    }
    
    /**
     * Resourceを保持する配列です
     *
     * @var array
     */
    private $config = array();
    
    public function __construct() {
        $this->config = Teeple_Util::readIniFile(TEEPLE_RESOURCE_CONFIG);
    }
    
    /**
     * 指定されたkeyに該当するResourceを取得します。
     *
     * @param string $key
     * @return string
     */
    public function getResource($key) {
        
        if (is_array($this->config)) {
            return @$this->config[$key];
        }
        return "";
    }
    
}
?>
