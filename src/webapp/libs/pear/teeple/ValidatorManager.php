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
 * フォームセットのバリデーションを実行します。
 * 
 * <pre>
 * ■configの仕様:
 *   array(
 *       array(
 *           'name' => 'フィールド名', // 必須
 *           'label' => 'ラベル名',    // 任意
 *           'validation' => array(
 *               'Validator名' => array(
 *                   '設定1' => '設定値1',
 *                   '設定2' => '設定値2',
 *                   ....
 *               ),
 *               ....
 *           ),
 *       ),
 *       ....
 *   )
 *  
 * ■エラーメッセージについて
 * 
 * </pre>
 * 
 *
 * @package teeple
 */
class Teeple_ValidatorManager
{
    
    const DEFAULT_MESSAGE = '{0}が正しく入力されていません。';

    /**
     * @var Logger
     */
    protected $log;
    
    /**
     * @var Teeple_Request
     */
    private $request;
    public function setComponent_Teeple_Request($c) {
        $this->request = $c;
    }
    
    /**
     * @var Teeple_Resource
     */
    private $resource;
    public function setComponent_Teeple_Resource($c) {
        $this->resource = $c;
    }
    
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
     * YAML形式の設定をパースします。
     * @param $yaml YAML形式の文字列
     * @return array config
     */
    public function parseYAML($yaml) {
        
        $yamlConfig = Horde_Yaml::load($yaml);
        if (! is_array($yamlConfig)) {
            throw new Teeple_Exception("Validationの設定を解析できませんでした。");
        }
        //$this->log->debug(var_export($yamlConfig, true));
        
        $validationConfig = array();
        foreach($yamlConfig as $field => $validations) {
            $oneConfig = array();
            $fields = explode('.',$field, 2);
            if (count($fields) == 2) {
                $oneConfig['label'] = $fields[1];
            }
            $oneConfig['name'] = $fields[0];
            $oneConfig['validation'] = $validations;
            
            array_push($validationConfig, $oneConfig);
        }
        //$this->log->debug(var_export($validationConfig, true));
        
        return $validationConfig;
    }
        
    /**
     * 指定されたconfigでバリデーションを実行します。
     * エラーがあった場合はエラーメッセージを組み立てて
     * Requestにメッセージを追加します。
     *
     * @param object $obj
     * @param array $config
     * @return boolean
     */
    public function execute($obj, &$config) {
        
        $result = TRUE;
        foreach($config as $fieldConfig) {
            $fieldName = $fieldConfig['name'];
            foreach($fieldConfig['validation'] as $validatorName => $attr) {
                
                // Validatorインスタンスを作成
                $validator = $this->container->getPrototype("Teeple_Validator_". ucfirst($validatorName));
                if (! is_object($validator)) {
                    throw new Teeple_Exception("Validatorのインスタンスを作成できません。($validatorName)");
                }
                // 属性をセット
                foreach($attr as $key => $value) {
                    $validator->$key = $value;
                }
                // Validatorを実行
                if (! $validator->validate($obj, $fieldName)) {
                    $result = FALSE;
                    // エラーメッセージをセット
                    $this->setErrorMessage($validator, $validatorName, $attr, $fieldConfig);
                    break;
                }
            }
        }
        return $result;
    }
    
    /**
     * エラーメッセージをセットします。
     *
     * @param Teeple_Validator $validator
     * @param string $validatorName
     * @param array $validatorConfig
     * @param array $fieldConfig
     */
    private function setErrorMessage($validator, $validatorName, &$validatorConfig, &$fieldConfig) {
        
        // メッセージとラベルの取得
        $msg = $this->getMessage($validatorName, $validatorConfig);
        $label = $this->getLabel($fieldConfig);
        
        // パラメータを準備
        $param = array();
        // {0}は必ずラベル名
        array_push($param, $label);
        foreach($validator->args as $propName) {
            array_push($param, $validator->$propName);
        }
        
        // メッセージをフォーマットしてセット
        $errorMessage = Teeple_Util::formatErrorMessage($msg, $param);
        $this->request->addErrorMessage($errorMessage, $fieldConfig['name']);
        return;
    }
    
    /**
     * エラーメッセージを取得します。
     *
     * @param string $validatorName
     * @param array $validatorConfig
     * @return string
     */
    private function getMessage($validatorName, &$validatorConfig) {
        
        if (isset($validatorConfig['msg']) && ! Teeple_Util::isBlank($validatorConfig['msg'])) {
            return $validatorConfig['msg'];
        }
        $msg = $this->resource->getResource('errors.'. $validatorName);
        if (Teeple_Util::isBlank($msg)) {
            $msg = self::DEFAULT_MESSAGE;
        }
        return $msg;
    }
    
    /**
     * ラベル名を取得します。
     *
     * @param array $fieldConfig
     * @return string
     */
    private function getLabel(&$fieldConfig) {
        
        if (isset($fieldConfig['label']) && ! Teeple_Util::isBlank($fieldConfig['label'])) {
            return $fieldConfig['label'];
        }
        $label = $this->resource->getResource('form.'. $fieldConfig['name']);
        if (Teeple_Util::isBlank($label)) {
            $label = $fieldConfig['name'];
        }
        return $label;
    }
    
}
?>
