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
 * 連想配列に格納された日付が妥当かどうかをチェックします。
 * $foo['Year'], $foo['Month'], $foo['Day'] に値が格納されていることが前提です。
 *
 * @package teeple.validator
 */
class Teeple_Validator_Datehash extends Teeple_Validator
{

    protected function execute($obj, $fieldName) {
        
        $value = $this->getTargetValue($obj, $fieldName);
        if (! is_array($value)) {
            throw new Teeple_Exception("対象となる値が配列ではありません。");
        }
        
        if (count($value) == 3 && $value['Year'] != "" && $value['Month'] != "" && $value['Day'] != "") {
            return checkdate($value['Month'], $value['Day'], $value['Year']);
        }
        return TRUE;
    }
    
}
?>
