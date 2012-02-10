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
 * @author      Kenzoh Sekitoh <sekitoh@acudbase.jp>
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 */

/**
 * アルファベットであるかどうかをチェックします。
 *
 * @package teeple.validator
 */
class Teeple_Validator_AlphaNumeric extends Teeple_Validator
{

    protected function execute($obj, $fieldName) {

        $value = $this->getTargetValue($obj, $fieldName);
        if (Teeple_Util::isBlank($value)) {
            return TRUE;
        }
	
        return ctype_alnum($value);
        //return preg_match('/^[\p{Ll}\p{Lu}\p{Nd}]+/u', $value);
    }

}
?>
