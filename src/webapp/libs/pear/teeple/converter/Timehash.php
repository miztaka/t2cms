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
 * 連想配列で渡された時刻から文字列を作成します。
 * 時刻は Hour,Minute,Secondをキーとする連想配列に格納されている前提です。
 * 時分は必須、秒は任意とします。
 * target属性で指定されたフィールド名に値を格納します。
 * format属性でフォーマットを指定できます。
 *
 * @package teeple.converter
 */
class Teeple_Converter_Timehash extends Teeple_Converter
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
    public $format = "%H:%M:%S";
    
    protected function execute(&$obj, $fieldName) {
        
        if (Teeple_Util::isBlank($this->target)) {
            throw new Teeple_Exception("targetが指定されていません。");
        }
        
        $value = Teeple_Util::getProperty($obj, $fieldName);
        if (! is_array($value) || count($value) < 2) {
            return FALSE;
        }
        
        $h = $value['Hour'];
        $i = $value['Minute'];
        $s = isset($value['Second']) ? $value['Second'] : 0;
        if ($h != "" && $i != "") {
            $time = mktime($h, $i, $s);
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
