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
 * FileUpload関連の処理を行う（複数アップロード対応版）
 *
 * @package     teeple
 */
class Teeple_FileUpload
{
    /**
     * @var フォームで指定したフィールド名を保持
     *
     * @access  private
     * @since   3.1.0
     */
    var $_name;
    
    /**
     * @var ファイル移動後のファイルのモードを保持する
     *
     * @access  private
     * @since   3.1.0
     */
    var $_filemode;

    /**
     * コンストラクター
     *
     * @access  public
     * @since   3.1.0
     */
    function __construct()
    {
        $this->_name     = "";   //ファイル名を配列に格納
        $this->_filemode = 0644;
    }

    /**
     * フォームで指定したフィールド名を返却
     *
     * @return  string  フィールド名
     * @access  public
     * @since   3.1.0
     */
    function getName()
    {
        return $this->_name;
    }
    
    /**
     * フォームで指定したフィールド名をセット
     *
     * @param   string  $name   フィールド名
     * @access  public
     * @since   3.1.0
     */
    function setName($name)
    {
        $this->_name = $name;
    }
    
    /**
     * ファイル移動後のファイルのモードを返却
     *
     * @return  integer ファイルのモード
     * @access  public
     * @since   3.1.0
     */
    function getFilemode()
    {
        return $this->_filemode;
    }
    
    /**
     * ファイル移動後のファイルのモードをセット
     *
     * @param   integer $filemode   ファイルのモード
     * @access  public
     * @since   3.1.0
     */
    function setFilemode($filemode)
    {
        $this->_filemode = octdec($filemode);
    }

    /**
     * アップロードされた数を返却
     *
     * @return  integer アップロードされた数
     * @access  public
     * @since   3.1.0
     */
    function count() {
        $name = $this->getName();
        if (is_array($_FILES[$name]["name"])) {
            return count($_FILES[$name]["name"]);
        } else {
            return 1;
        }
    }
    
    /**
     * クライアントマシンの元のファイル名を返却
     *
     * @return  array   クライアントマシンの元のファイル名の配列
     * @access  public
     * @since   3.1.0
     */
    function getOriginalName()
    {
        $original_name = array();
        //配列で返す
        $name = $this->getName();
        if (($name != "") && isset($_FILES[$name])) {
            if (is_array($_FILES[$name]["name"])) {
                foreach ($_FILES[$name]["name"] as $key => $value) {
                    $original_name[$key] = $value;
                }
            }else if (isset($_FILES[$name]["name"])){
                $original_name[0] = $_FILES[$name]["name"];
            }
        }
        return $original_name;
    }
    
    /**
     * ファイルのMIME型を返却
     *
     * @return  array   ファイルのMIME型の配列
     * @access  public
     * @since   3.1.0
     */
    function getMimeType()
    {
        $mime_type = array();
        //配列で返す
        $name = $this->getName();
        if (($name != "") && isset($_FILES[$name])) {
            if (is_array($_FILES[$name]["type"])) {
                foreach ($_FILES[$name]["type"] as $key => $value) {
                    $mime_type[$key] = $value;
                }
            }else if (isset($_FILES[$name]["type"])){
                $mime_type[0] = $_FILES[$name]["type"];
            }
        }
        return $mime_type;
    }
    
    /**
     * アップロードされたファイルのバイト単位のサイズを返却
     *
     * @return  array   ファイルのサイズの配列
     * @access  public
     * @since   3.1.0
     */
    function getFilesize()
    {
        $filesize = array();
        //配列で返す
        $name = $this->getName();
        if (($name != "") && isset($_FILES[$name])) {
            if (is_array($_FILES[$name]["size"])) {
                foreach ($_FILES[$name]["size"] as $key => $value) {
                    $filesize[$key] = $value;
                }
            }else if (isset($_FILES[$name]["size"])){
                $filesize[0] = $_FILES[$name]["size"];
            }
        }
        return $filesize;
    }
    
    /**
     * テンポラリファイルの名前を返却
     *
     * @return  array   テンポラリファイルの名前の配列
     * @access  public
     * @since   3.1.0
     */
    function getTmpName()
    {
        $tmp_name = array();
        //配列で返す
        $name = $this->getName();
        if (($name != "") && isset($_FILES[$name])) {
            if (is_array($_FILES[$name]["tmp_name"])) {
                foreach ($_FILES[$name]["tmp_name"] as $key => $value) {
                    $tmp_name[$key] = $value;
                }
            }else if (isset($_FILES[$name]["tmp_name"])){
                $tmp_name[0] = $_FILES[$name]["tmp_name"];
            }
        }
        return $tmp_name;
    }
    
    /**
     * ファイルアップロードに関するエラーコードを返却
     *
     * @return  array   ファイルアップロードに関するエラーコードの配列
     * @access  public
     * @since   3.1.0
     */
    function getError()
    {
        $error_list = array();
        //配列で返す
        $name = $this->getName();
        if (($name != "") && isset($_FILES[$name])) {
            if (is_array($_FILES[$name]["error"])) {
                foreach ($_FILES[$name]["error"] as $key => $value) {
                    $error_list[$key] = $value;
                }
            }else if (isset($_FILES[$name]["error"])){
                $error_list[0] = $_FILES[$name]["error"];
            }
        }
        return $error_list;
    }
    
    /**
     * 指定されたMIME型になっているか？
     *
     * @param   array    $type  MIME型の配列
     * @return  array[boolean]  指定されたMIME型になっているか？の配列
     * @access  public
     * @since   3.1.0
     */
    function checkMimeType($type_list)
    {
        $mime_type_check = array();
        $mime_type = $this->getMimeType();
        if (count($mime_type) > 0) {
            foreach ($mime_type as $key => $val) {
                if (isset($type_list[$key])) {
                    $type = $type_list[$key];
                } else if (isset($type_list["default"])){
                    $type = $type_list["default"];
                } else {
                    $type = "";
                }
                if ($type == "" || in_array($val,$type)  ) {
                    $mime_type_check[$key] = true;
                }else {
                    $mime_type_check[$key] = false;
                }
            }
        }
        return $mime_type_check;
    }
    
    /**
     * ファイルサイズが指定されたサイズ以下かどうか？
     *
     * @param   array   $size_list  基準となるファイルサイズの配列
     * @return  array[boolean]    ファイルサイズが指定されたサイズ以下かどうか？の配列
     * @access  public
     * @since   3.1.0
     */
    function checkFilesize($size_list)
    {
        $filesize_check = array();
        $filesize = $this->getFilesize();
        if (count($filesize) > 0) {
            foreach ($filesize as $key => $val) {
                if (isset($size_list[$key])) {
                    $size = $size_list[$key];
                } else if (isset($size_list["default"])) {
                    $size = $size_list["default"];
                } else {
                    $size = "";
                }
                if ($size == "" || $val <= $size) {
                    $filesize_check[$key] = true;
                }else {
                    $filesize_check[$key] = false;
                }
            }
        }
        return $filesize_check;
    }

    /**
     * 指定されたパスへファイルを移動(one file)
     *
     * @param   strint  $name   移動元のファイルの索引番号
     * @param   strint  $dest   移動先のファイル名
     * @return  boolean 移動に成功したかどうか
     * @access  public
     * @since   3.1.0
     */
    function move($id,$dest)
    {
        $tmp_name = $this->getTmpName();
        if (isset($tmp_name[$id])) {
            if (move_uploaded_file($tmp_name[$id], $dest)) {
                chmod($dest, $this->getFilemode());
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
?>
