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
 * 郵便番号を1つにまとめます。(ハイフンで結合)
 * 郵便番号はそれぞれ、$foo[0],$foo[1]に格納されている前提です。
 * target属性で指定されたフィールド名に値を格納します。
 *
 * @package teeple.converter
 */
class Teeple_Converter_Zip extends Teeple_Converter
{
    
    /**
     * 変換後の値を格納するプロパティ名
     * @var string
     */
    public $target;
    
    protected function execute(&$obj, $fieldName) {
        
        if (Teeple_Util::isBlank($this->target)) {
            throw new Teeple_Exception("targetが指定されていません。");
        }
        
        $value = Teeple_Util::getProperty($obj, $fieldName);
        if (! is_array($value) || count($value) != 2) {
            return FALSE;
        }
        
        if ($value[0] != "" && $value[1] != "") {
            $newvalue = sprintf("%s-%s", $value[0], $value[1]);
            Teeple_Util::setProperty($obj, $this->target, $newvalue);
            return TRUE;
        }
        return FALSE;
    }
    
}
?>
