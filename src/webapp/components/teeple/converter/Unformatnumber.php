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
 * カンマで区切られた数値を通常の数値に変換します。
 *
 * @package teeple.converter
 */
class Teeple_Converter_Unformatnumber extends Teeple_Converter
{
    
    protected function execute(&$obj, $fieldName) {
        
        $value = Teeple_Util::getProperty($obj, $fieldName);
        $value = str_replace(",", "", $value);
        Teeple_Util::setProperty($obj, $fieldName, $value);
        return TRUE;
    }
    
}
?>
