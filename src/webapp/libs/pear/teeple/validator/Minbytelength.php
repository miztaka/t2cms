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
 * 文字列の最小バイト数をチェックします。
 *
 * @package teeple.validator
 */
class Teeple_Validator_Minbytelength extends Teeple_Validator
{

    public $minbytelength;
    public $charset;
    
    /**
     * エラーメッセージの引数に渡すプロパティ名
     * @var array
     */
    public $args = array('minbytelength');
    
    protected function execute($obj, $fieldName) {
        
        $value = $this->getTargetValue($obj, $fieldName);
        if (Teeple_Util::isBlank($value)) {
            return TRUE;
        }
        
        if (Teeple_Util::isBlank($this->minbytelength) || ! is_numeric($this->minbytelength)) {
            throw new Teeple_Exception("minbytelengthが正しくセットされていません。");
        }
        
        if (! Teeple_Util::isBlank($this->charset)) {
            $value = mb_convert_encoding($value, $this->charset);
        }
        
        return $this->minbytelength <= strlen($value);
    }
    
}
?>