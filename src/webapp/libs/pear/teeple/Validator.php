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
 * Validatorの基底クラス
 *
 * @package teeple
 */
abstract class Teeple_Validator
{
    
    /**
     * メッセージの引数に設定されるプロパティの名称を配列で持ちます。
     * @var array
     */
    public $args = array();
    
    /**
     * Validationを実行します。
     * 子クラスで実装されるexecuteメソッドを呼び出します。
     *
     * @param object $obj
     * @param string $fieldName
     * @return boolean
     */
    public function validate($obj, $fieldName) {
        return $this->execute($obj, $fieldName);
    }
    
    /**
     * 実際のValidationロジックです。子クラスで実装します。
     *
     * @param object $obj
     * @param string $fieldName
     */
    abstract protected function execute($obj, $fieldName);

    /**
     * Validation対象の値を取得します。
     *
     * @param object $obj
     * @param string $fieldName
     */
    protected function getTargetValue($obj, $fieldName) {
        return Teeple_Util::getProperty($obj, $fieldName);
    }
    
}
?>
