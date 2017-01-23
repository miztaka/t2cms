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

if (!defined('UPLOAD_ERR_OK')) {
    define('UPLOAD_ERR_OK',        0);
    define('UPLOAD_ERR_INI_SIZE',  1);
    define('UPLOAD_ERR_FORM_SIZE', 2);
    define('UPLOAD_ERR_PARTIAL',   3);
    define('UPLOAD_ERR_NO_FILE',   4);
}

define('UPLOAD_ERROR_TYPE', 'FileUpload');

/**
 * FileUpload処理を行うFilter
 * TODO 未検証
 *
 * @package     teeple.filter
 */
class Teeple_Filter_FileUpload extends Teeple_Filter
{
    
    /**
     * @var Teeple_ActionChain
     */
    private $actionChain;
    public function setComponent_Teeple_ActionChain($c) {
        $this->actionChain = $c;
    }
    
    /**
     * @var Teeple_FileUpload
     */
    private $fileUpload;
    public function setComponent_Teeple_FileUpload($c) {
        $this->fileUpload = $c;
    }
    
    /**
     * @var Teeple_Request
     */
    private $request;
    public function setComponent_Teeple_Request($c) {
        $this->request = $c;
    }
    
    /**
     * コンストラクタ
     *
     */
    public function __construct() {
        parent::__construct();
    }
    
    /**
     * ファイルアップロード処理を行う
     *
     */
    public function prefilter() {

        if ($this->isCompleteAction()) {
            $this->log->info("CompleteActionフラグが立っているため、FileUploadは実行されませんでした。");
            return;
        }
        
        $attributes = $this->getAttributes();
        if (isset($attributes["name"])) {
            $this->fileUpload->setName($attributes["name"]);
            
            if (isset($attributes["filemode"])) {
                $this->fileUpload->setFilemode($attributes["filemode"]);
            }
            
            //maple.iniを分析
            $maxsize_ini = array();
            $type_ini = array();
            $sizeError_ini = array();
            $typeError_ini = array();
            $noFileError_ini = array();
            foreach($attributes as $key => $value) {
                if (substr($key,0,7) == "maxsize") {
                    if (strlen($key) == 7) {
                        $maxsize_ini["default"] = $value;
                    } else if (is_numeric(substr($key,8,strlen($key)-9))) {
                        $maxsize_ini[substr($key,8,strlen($key)-9)] = $value;
                    }
                }
                
                if (substr($key,0,4) == "type") {
                    $typeArray = array();
                    if (strlen($key) == 4) {
                        $typeArray = explode(",", $value);
                        $type_ini["default"] = $typeArray;
                    } else if (is_numeric(substr($key,5,strlen($key)-6))) {
                        $typeArray = explode(",", $value);
                        $type_ini[substr($key,5,strlen($key)-6)] = $typeArray;
                    }
                }
                
                if (substr($key,0,9) == "sizeError") {
                    if (strlen($key) == 9) {
                        $sizeError_ini["default"] = $value;
                    } else if (is_numeric(substr($key,10,strlen($key)-11))) {
                        $sizeError_ini[substr($key,10,strlen($key)-11)] = $value;
                    }
                }
                
                if (substr($key,0,9) == "typeError") {
                    if (strlen($key) == 9) {
                        $typeError_ini["default"] = $value;
                    } else if (is_numeric(substr($key,10,strlen($key)-11))) {
                        $typeError_ini[substr($key,10,strlen($key)-11)] = $value;
                    }
                }
                
                if (substr($key,0,11) == "noFileError") {
                    if (strlen($key) == 11) {
                        $noFileError_ini["default"] = $value;
                    } else if (is_numeric(substr($key,12,strlen($key)-13))) {
                        $noFileError_ini[substr($key,12,strlen($key)-13)] = $value;
                    } else if (substr($key,12,7) == "whether") {
                        $noFileError_ini["whether"] = $value;
                        $noFileError_whether = 0;
                    }
                }
            }
            
            //関連配列
            $error = $this->fileUpload->getError();
            
            $mime_type_check = $this->fileUpload->checkMimeType($type_ini);
            
            $filesize_check = $this->fileUpload->checkFilesize($maxsize_ini);
            //以下はforeachで各ファイルでエラーチェックを行う
            foreach ($error as $key => $val) {
                if ($val != UPLOAD_ERR_OK) {// PHP自体が感知するエラーが発生した場合
                    if ($val == UPLOAD_ERR_INI_SIZE) {
                        $this->request->setFilterError('FileUpload');
                        //$errorList->setType(UPLOAD_ERROR_TYPE);
                        if (isset($attributes["iniSizeError"])) {
                            $message = $attributes["iniSizeError"];
                        } else {
                            $message = "アップロードされたファイルは、php.ini の upload_max_filesize ディレクティブの値を超えています。";
                        }
                        //$errorList->add($this->fileUpload->getName()."[".$key."]", $message);
                        $this->request->addErrorMessage($message);
                        break;
                    } else if ($val == UPLOAD_ERR_FORM_SIZE) {
                        //$errorList->setType(UPLOAD_ERROR_TYPE);
                        $this->request->setFilterError(UPLOAD_ERROR_TYPE);
                        if (isset($attributes["formSizeError"])) {
                            $message = $attributes["formSizeError"];
                        } else {
                            $message = "アップロードされたファイルは、HTMLフォームで指定された MAX_FILE_SIZE を超えています。";
                        }
                        //$errorList->add($this->fileUpload->getName()."[".$key."]", $message);
                        $this->request->addErrorMessage($message);
                        break;
                    } else if ($val == UPLOAD_ERR_PARTIAL) {
                        //$errorList->setType(UPLOAD_ERROR_TYPE);
                        $this->request->setFilterError(UPLOAD_ERROR_TYPE);
                        if (isset($attributes["partialError"])) {
                            $message = $attributes["partialError"];
                        } else {
                            $message = "アップロードされたファイルは一部のみしかアップロードされていません。";
                        }
                        //$errorList->add($this->fileUpload->getName()."[".$key."]", $message);
                        $this->request->addErrorMessage($message);
                        break;
                    } else if ($val == UPLOAD_ERR_NO_FILE) {
                        if (isset($noFileError_ini[$key])) {
                            //$errorList->setType(UPLOAD_ERROR_TYPE);
                            $this->request->setFilterError(UPLOAD_ERROR_TYPE);
                            $message = $noFileError_ini[$key];
                            //$errorList->add($this->fileUpload->getName()."[".$key."]", $message);
                            $this->request->addErrorMessage($message);
                        }else if (isset($noFileError_ini["default"])) {
                            //$errorList->setType(UPLOAD_ERROR_TYPE);
                            $this->request->setFilterError(UPLOAD_ERROR_TYPE);
                            $message = $noFileError_ini["default"];
                            //$errorList->add($this->fileUpload->getName()."[".$key."]", $message);
                            $this->request->addErrorMessage($message);
                            break;
                        } else if (isset($noFileError_ini["whether"])) {
                            $noFileError_whether = $noFileError_whether +1;
                        }
                    }
                }else {// PHP自体が感知するエラーは発生していない場合
                    //
                    // maple.iniで設定されたサイズを超えていた場合
                    //
                    if (count($maxsize_ini) > 0) {
                        if (!$filesize_check[$key]) {
                            //$errorList->setType(UPLOAD_ERROR_TYPE);
                            $this->request->setFilterError(UPLOAD_ERROR_TYPE);
                            if (isset($sizeError_ini[$key])) {
                                $message = $sizeError_ini[$key];
                            }else if (isset($sizeError_ini["default"])) {
                                $message = $sizeError_ini["default"];
                            } else {
                                $message = "ファイルはアップロードされませんでした。";
                            }
                            //$errorList->add($this->fileUpload->getName()."[".$key."]", $message);
                            $this->request->addErrorMessage($message);
                        }
                    }
                    //
                    // maple.iniで設定されたMIME-Typeではなかった場合
                    //
                    if (count($type_ini) > 0) {
                        if (!$mime_type_check[$key]) {
                            //$errorList->setType(UPLOAD_ERROR_TYPE);
                            $this->request->setFilterError(UPLOAD_ERROR_TYPE);
                            if (isset($typeError_ini[$key])) {
                                $message = $typeError_ini[$key];
                            }else if (isset($typeError_ini["default"])) {
                                $message = $typeError_ini["default"];
                            } else {
                                $message = "指定されたファイル形式ではありません。";
                            }
                            //$errorList->add($this->fileUpload->getName()."[".$key."]", $message);
                            $this->request->addErrorMessage($message);
                        }
                    }
                }
            }
            if (isset($noFileError_whether) && count($error) == $noFileError_whether) {
                //$errorList->setType(UPLOAD_ERROR_TYPE);
                $this->request->setFilterError(UPLOAD_ERROR_TYPE);
                $message = $noFileError_ini["whether"];
                //$errorList->add($this->fileUpload->getName(), $message);
                $this->request->addErrorMessage($message);
            }
        } else {
            $this->log->trace("フィールド名が指定されていません", "Filter_FileUpload#execute");
        }

        return;
    }
    
    public function postfilter() {}
}
?>
