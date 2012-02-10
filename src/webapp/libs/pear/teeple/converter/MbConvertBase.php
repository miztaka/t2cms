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
 * mb_convert_kanaを使って変換するConverterの基底クラス
 *
 * @package teeple.converter
 */
abstract class Teeple_Converter_MbConvertBase extends Teeple_Converter
{
    
    protected function execute(&$obj, $fieldName) {
        
        $value = Teeple_Util::getProperty($obj, $fieldName);
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                $value[$key] = $this->convertMethod($val); 
            }
            Teeple_Util::setProperty($obj, $fieldName, $value);
        } else if (! Teeple_Util::isBlank($value)) {
            $value = $this->convertMethod($value);
            Teeple_Util::setProperty($obj, $fieldName, $value);
        }
        return TRUE;
    }
    
    abstract protected function convertMethod($val);
    
}
?>
