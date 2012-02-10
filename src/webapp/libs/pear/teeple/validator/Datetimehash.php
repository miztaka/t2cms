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
 * 連想配列に格納された日時が妥当かどうかをチェックします。
 * 検証する配列は、Year,Month,Day,Hour,Minute,Secondのパラメータを持つ配列です。
 *
 * @package teeple.validator
 */
class Teeple_Validator_Datetimehash extends Teeple_Validator
{

    protected function execute($obj, $fieldName) {
        
        $value = $this->getTargetValue($obj, $fieldName);
        if (! is_array($value)) {
            throw new Teeple_Exception("対象となる値が配列ではありません。");
        }
        
        $year   = $value['Year'];
        $month  = $value['Month'];
        $day    = $value['Day'];
        $hour   = $value['Hour'];
        $minute = $value['Minute'];
        $second = isset($value['Second']) ? $second = $value['Second'] : '00';
        
        // 全部空だったらTRUE
        if (Teeple_Util::isBlank($year) && Teeple_Util::isBlank($month) && Teeple_Util::isBlank($day) &&
            Teeple_Util::isBlank($hour) && Teeple_Util::isBlank($minute)) {
            return TRUE;
        }

        if (($year == "") || ($month == "") || ($day == "") || ($hour == "") || ($minute == "")) {
            return FALSE;
        } else if (!is_numeric($year) || !is_numeric($month) ||
                    !is_numeric($day) || !is_numeric($hour) || !is_numeric($minute)) {
            return FALSE;
        } else if (checkdate($month, $day, $year)) {
            if (0 > intval($hour) || 23 < intval($hour)) {
                return FALSE;
            }
            if (0 > intval($minute) || 59 < intval($minute)) {
                return FALSE;
            }
            if (0 > intval($second) || 59 < intval($second)) {
                return FALSE;
            }
            return TRUE;
        } else {
            return FALSE;
        }
        return TRUE;
    }
    
}
?>
