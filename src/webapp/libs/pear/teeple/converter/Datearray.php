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
 * 配列で渡された年、月、日から文字列を作成します。
 * 年月日はそれぞれ、$foo[0],$foo[1],$foo[2]に格納されている前提です。
 * target属性で指定されたフィールド名に値を格納します。
 * format属性でフォーマットを指定できます。
 *
 * @package teeple.converter
 */
class Teeple_Converter_Datearray extends Teeple_Converter
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
        
        $value = Teeple_Util::getProperty($obj, $fieldName);
        if (! is_array($value) || count($value) != 3) {
            return FALSE;
        }
        
        if ($value[0] != "" && $value[1] != "" && $value[2] != "") {
            $time = mktime(0,0,0,$value[1],$value[2],$value[0]);
            if ($time !== FALSE) {
                $datestr = strftime($this->format, $time);
                Teeple_Util::setProperty($obj, $this->target, $datestr);
                return TRUE;
            }
            
        }
        return FALSE;
    }
    
}
?>
