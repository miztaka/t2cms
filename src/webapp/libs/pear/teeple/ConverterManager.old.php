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
 * Converterを管理するクラス
 * TODO 修正
 *
 * @package     teeple
 */
class Teeple_ConverterManagerOld
{
    /**
     * @var Converterを保持する
     *
     * @access  private
     * @since   3.0.0
     */
    protected $_list;

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
     * @var Teeple_Container
     */
    private $container;
    public function setComponent_container($c) {
        $this->container = $c;
    }
    
    /**
     * コンストラクタ
     *
     * @access  public
     * @since   3.0.0
     */
    public function __construct()
    {
        $this->_list = array();
        $this->log = LoggerManager::getLogger(get_class($this));
    }

    /**
     * Convertを行う
     *
     * @param   array   $params Convertする条件が入った配列
     * @access  public
     * @since   3.0.0
     */
    public function execute($params)
    {
        if (!is_array($params) || (count($params) < 1)) {
            return true;
        }

        // ConverterのListを生成
        $this->_buildConverterList($params);

        //
        // Convertを実行
        //
        $this->_convert($params);

        return true;
    }

    /**
     * ConverterのListを生成
     *
     * @param   array   $params Convertする条件が入った配列
     * @access  private
     * @since   3.0.0
     */
    private function _buildConverterList($params)
    {
        foreach ($params as $key => $value) {
            $key   = preg_replace("/\s+/", "", $key);
            $value = preg_replace("/\s+/", "", $value);

            if ($key == "") {
                throw new Teeple_Exception("Converterの設定が不正です。");
            }

            //
            // $key は attribute.name のパターン
            //
            $keyArray = explode(".", $key);
            if (count($keyArray) != 2) {
                throw new Teeple_Exception("Converterのキーが不正です。");
            }
            $attribute = $keyArray[0];     // 属性の名前
            $name      = $keyArray[1];     // Converterの名前 

            $className = "Converter_" . ucfirst($name);
            
            //
            // 既に同名のConverterが追加されていたら何もしない
            //
            if (isset($this->_list[$name]) && is_object($this->_list[$name])) {
                continue;
            }

            //
            // オブジェクトの生成に失敗していたらエラー
            //
            $converter = $this->container->getComponent($className);
            if (!is_object($converter)) {
                throw new Teeple_Exception("Converter {$className} の生成に失敗しました。");
            }

            $this->_list[$name] = $converter;
        }
    }

    /**
     * Converterを実行
     *
     * @param   array   $params Convertする条件が入った配列
     * @access  private
     * @since   3.0.0
     */
    private function _convert($params)
    {
        foreach ($params as $key => $value) {
            $key   = preg_replace("/\s+/", "", $key);
            $value = preg_replace("/\s+/", "", $value);

            if ($key == "") {
                throw new Teeple_Exception("Converterの設定が不正です。キーがありません。");
            }

            //
            // $key は attribute.name のパターン
            //
            $keyArray = explode(".", $key);
            if (count($keyArray) != 2) {
                throw new Teeple_Exception("Converterのkeyの形式が不正です。");
            }
            $attribute = $keyArray[0];     // 属性の名前
            $name      = $keyArray[1];     // Converterの名前 

            //
            // $value にはConvert後の値を入れる変数名がセットできる
            //
            $newAttribute = $value;

            //
            // Converterを取得
            //
            $converter = $this->_list[$name];

            if (!is_object($converter)) {
                throw new Teeple_Exception("Converter {$className} の生成に失敗しました。");
            }

            //
            // attributeに * が指定されている場合は
            // リクエストパラメータ全てが変換対象となる
            //
            if ($attribute == '*') {
                $attribute = join(",", array_keys($this->request->getParameters()));
            }

            if (preg_match("/,/", $attribute)) {
                $attributes = array();
                foreach (explode(",", $attribute) as $param) {
                    if ($param) {
                       $attributes[$param] = $this->request->getParameter($param);
                    }
                }
            } else {
                $attributes = $this->request->getParameter($attribute);
            }

            //
            // Converterを適用
            //
            $result = $converter->convert($attributes);

            if ($newAttribute != "") {
                $this->request->setParameter($newAttribute, $result);
            } else {
                if (is_array($attributes)) {
                    foreach ($result as $key => $value) {
                        if ($key) {
                            $this->request->setParameter($key, $value);
                        }
                    }
                } else {
                    $this->request->setParameter($attribute, $result);
                }
            }
        }
    }
}
?>
