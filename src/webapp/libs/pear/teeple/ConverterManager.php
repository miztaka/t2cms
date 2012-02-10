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
 * フォームセットの値の変換処理を実行します。
 * 
 * <pre>
 * ■configの仕様:
 *   array(
 *       'フィールド名' => array(
 *           'Converter名' => array(
 *               '設定1' => '設定値1',
 *               '設定2' => '設定値2',
 *               ....
 *           ),
 *       ),
 *       ....
 *   )
 * </pre>
 *
 * @package teeple
 */
class Teeple_ConverterManager
{
    
    const FIELD_ALL = '__all';
    
    /**
     * @var Logger
     */
    protected $log;
    
    /**
     * @var Teeple_Container
     */
    private $container;
    public function setComponent_container($c) {
        $this->container = $c;
    }
    
    /**
     * コンストラクタ
     *
     */
    public function __construct() {
        $this->log = LoggerManager::getLogger(get_class($this));
    }

    /**
     * 指定されたconfigで変換を実行します。
     *
     * @param mixed $obj
     * @param array $config
     */
    public function execute(&$obj, &$config) {
        
        foreach($config as $fieldName => $fieldConfig) {
            foreach($fieldConfig as $converterName => $attr) {
                // Converterインスタンスを作成
                $converter = $this->container->getPrototype("Teeple_Converter_". ucfirst($converterName));
                if (! is_object($converter)) {
                    throw new Teeple_Exception("Converterのインスタンスを作成できません。($converterName)");
                }
                // 属性をセット
                foreach($attr as $key => $value) {
                    $converter->$key = $value;
                }
                // Converterを実行
                if ($fieldName == self::FIELD_ALL) {
                    $keys = Teeple_Util::getPropertyNames($obj);
                } else {
                    $keys = array($fieldName);
                }
                foreach ($keys as $key) {
                    if (! $converter->convert($obj, $key)) {
                        $this->log->info("{$converterName}は{$key}に対して実行されませんでした。");
                    }
                }
            }
        }
        return;
    }
    
}
?>
