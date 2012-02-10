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
 * 日時の文字列から連想配列を作成します。(Datetimehashの逆)
 * 日時は Year,Month,Day,Hour,Minute,Secondをキーとする連想配列に格納されます。
 * 日付は必須、時刻は任意とします。
 * 日付のフォーマットは "%Y-%m-%d %H:%M:%S"
 * target属性で指定されたフィールド名に値を格納します。
 *
 * @package teeple.converter
 */
class Teeple_Converter_Todatetimehash extends Teeple_Converter
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
    public $format = "%Y-%m-%d %H:%M:%S";
    
    protected function execute(&$obj, $fieldName) {
        
        if (Teeple_Util::isBlank($this->target)) {
            throw new Teeple_Exception("targetが指定されていません。");
        }
        
        $result = array();
        $value = Teeple_Util::getProperty($obj, $fieldName);
        
        list($day, $time) = explode(' ', $value);
        if (Teeple_Util::isBlank($day)) {
            return FALSE;
        }
        list($y,$m,$d) = explode('-', $day);
        $result['Year'] = $y;
        $result['Month'] = $m;
        $result['Day'] = $d;
        
        if (! Teeple_Util::isBlank($time)) {
            list($h, $i, $s) = explode(':', $time);
            $result['Hour'] = $h;
            $result['Minute'] = $i;
            $result['Second'] = $s;
        }
        
        Teeple_Util::setProperty($obj, $this->target, $result);
        return TRUE;
    }
    
}
?>
