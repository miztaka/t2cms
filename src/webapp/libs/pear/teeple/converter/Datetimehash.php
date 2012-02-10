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
 * 連想配列で渡された日時から文字列を作成します。
 * 日時は Year,Month,Day,Hour,Minute,Secondをキーとする連想配列に格納されている前提です。
 * 日付は必須、時刻は任意とします。
 * target属性で指定されたフィールド名に値を格納します。
 * format属性でフォーマットを指定できます。
 *
 * @package teeple.converter
 */
class Teeple_Converter_Datetimehash extends Teeple_Converter
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
        
        // 初期化
        Teeple_Util::setProperty($obj, $this->target, "");
        
        $value = Teeple_Util::getProperty($obj, $fieldName);
        if (! is_array($value) || count($value) < 3) {
            return FALSE;
        }
        
        $y = $value['Year'];
        $m = $value['Month'];
        $d = $value['Day'];
        if ($y != "" && $m != "" && $d != "") {
            $h = isset($value['Hour']) ? $value['Hour'] : 0;
            $i = isset($value['Minute']) ? $value['Minute'] : 0;
            $s = isset($value['Second']) ? $value['Second'] : 0;
            
            if (!is_numeric($y) || !is_numeric($m) || !is_numeric($d) ||
                !is_numeric($h) || !is_numeric($i) || !is_numeric($s)) {
                return FALSE;
            }
            
            $time = mktime($h, $i, $s, $m, $d, $y);
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
