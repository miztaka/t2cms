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
 * 指定された正規表現に沿っているかどうかをチェックします。
 *
 * @package teeple.validator
 */
class Teeple_Validator_Mask extends Teeple_Validator
{

    public $mask;
    
    protected function execute($obj, $fieldName) {
        
        $value = $this->getTargetValue($obj, $fieldName);
        if (Teeple_Util::isBlank($value)) {
            return TRUE;
        }
        
        if (Teeple_Util::isBlank($this->mask)) {
            throw new Teeple_Exception("maskが設定されていません。");
        }
        
        return preg_match($this->mask, $value);
    }
    
}
?>