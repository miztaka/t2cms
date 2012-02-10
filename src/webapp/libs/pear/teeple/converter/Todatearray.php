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
 * 日付の文字列から配列を作成します。(Datearrayの逆)
 * 年月日はそれぞれ、$foo[0],$foo[1],$foo[2]に格納されます。
 * 日付のフォーマットは "%Y-%m-%d"
 * target属性で指定されたフィールド名に値を格納します。
 *
 * @package teeple.converter
 */
class Teeple_Converter_Todatearray extends Teeple_Converter
{
    
    /**
     * 変換後の値を格納するプロパティ名
     * @var string
     */
    public $target;
    
    /**
     * 変換する際のフォーマット(strftimeの形式)
     * @var string
     */
    public $format = "%Y-%m-%d";
    
    protected function execute(&$obj, $fieldName) {
        
        if (Teeple_Util::isBlank($this->target)) {
            throw new Teeple_Exception("targetが指定されていません。");
        }
        
        $result = array();
        $value = Teeple_Util::getProperty($obj, $fieldName);
        if (Teeple_Util::isBlank($value)) {
            return FALSE;
        }
        
        list($y,$m,$d) = explode('-', $value);
        $result[0] = $y;
        $result[1] = $m;
        $result[2] = $d;
        
        Teeple_Util::setProperty($obj, $this->target, $result);
        return TRUE;
    }
    
}
?>
