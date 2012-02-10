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
 * 文字列の最大バイト数をチェックします。
 *
 * @package teeple.validator
 */
class Teeple_Validator_Maxwidth extends Teeple_Validator
{

    public $maxwidth;
    public $charset;
    
    /**
     * エラーメッセージの引数に渡すプロパティ名
     * @var array
     */
    public $args = array('maxwidth');
    
    protected function execute($obj, $fieldName) {
        
        $value = $this->getTargetValue($obj, $fieldName);
        if (Teeple_Util::isBlank($value)) {
            return TRUE;
        }
        
        if (Teeple_Util::isBlank($this->maxwidth) || ! is_numeric($this->maxwidth)) {
            throw new Teeple_Exception("maxwidthが正しくセットされていません。");
        }
        
        if (! Teeple_Util::isBlank($this->charset)) {
            $value = mb_convert_encoding($value, $this->charset);
        } else {
            $this->charset = INTERNAL_CODE;
        }
        
        $log = LoggerManager::getLogger(get_class($this));
        $log->debug("width: ".mb_strwidth($value, $this->charset));
        return $this->maxwidth >= mb_strwidth($value, $this->charset);
    }
    
}
?>