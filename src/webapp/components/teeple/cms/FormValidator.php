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
 * @package     teeple.cms
 * @author      Mitsutaka Sato <miztaka@gmail.com>
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 */

require_once 'plugin/ObjectPlugin.php';

/**
 * CMSページのフォーム入力値検証クラスです。
 * @package teeple.cms
 */
class Teeple_Cms_FormValidator {

    /**
     * @var Logger
     */
    protected $log;
    
    /**
     * @var Teeple_Request
     */
    protected $request;
    public function setComponent_Teeple_Request($c) {
        $this->request = $c;
    }
    
    /**
     * @var Teeple_Validator_Required
     */
    private $requireValidator;
    public function setComponent_Teeple_Validator_Required($c) {
        $this->requireValidator = $c;
    }
    
    /**
     * @var Teeple_Validator_Mask
     */
    private $maskValidator;
    public function setComponent_Teeple_Validator_Mask($c) {
        $this->maskValidator = $c;
    }
    
    /**
     * @var Teeple_Resource
     */
    private $resource;
    public function setComponent_Teeple_Resource($c) {
        $this->resource = $c;
    }    

    /**
     * コンストラクタです。
     */
    public function __construct() {
        $this->log = LoggerManager::getLogger(get_class($this));
    }
    
    /**
     * attributeの定義に従って入力値を検証します。
     * @param Teeple_EavRecord $record
     * @param Teeple_ActionBase $action
     * @return bool
     */
    public function validate($record, $action) {
        
        foreach ($record->_metaAttributes as $attr) {
            if ($attr->require_flg) {
                $this->requireCheck($attr, $action);
            }
            if (! Teeple_Util::isBlank($attr->validation)) {
                $this->maskCheck($attr, $action);
            }
        }
        
        // バリデーションプラグイン
        $clsname = "Plugin_Object_{$record->_metaEntity->pname}";
        if (class_exists($clsname) && method_exists($clsname, "validate")) {
            $plugin = new $clsname();
            $plugin->validate($action, $this->request);
        }
        
        return ! $this->request->isError();
    }

    /**
     * 必須チェックを行ないます。
     * @param Entity_MetaAttribute $attr
     * @param Teeple_ActionBase $action
     */
    private function requireCheck($attr, $action) {
        $pname = $attr->pname;
        if ($attr->data_type == Entity_MetaAttribute::DATA_TYPE_IMAGE) {
            $pname .= "_h";
        }
        if ($attr->data_type == Entity_MetaAttribute::DATA_TYPE_HTML) {
            if (Teeple_Util::isBlank($action->$pname) || trim($action->$pname) == '&nbsp;') {
                $this->addError($attr, 'required');
            }
        } elseif (! $this->requireValidator->validate($action, $pname)) {
            $this->addError($attr, 'required');
        }
        return;
    }
    
    /**
     * 正規表現チェックを行ないます。
     * @param Entity_MetaAttribute $attr
     * @param Teeple_ActionBase $action
     */
    private function maskCheck($attr, $action) {
        $pname = $attr->pname;
        if (Teeple_Util::isBlank($action->$pname)) {
            return;
        }
        $regex = $attr->validation;
        $this->maskValidator->mask = $regex;
        if (! $this->maskValidator->validate($action, $pname)) {
            $this->addError($attr, 'mask');
        }
        return;
    }
    
    /**
     * エラーメッセージを登録します。
     * @param Entity_MetaAttribute $attr
     */
    private function addError($attr, $type) {
        
        if (! Teeple_Util::isBlank($attr->validation_message)) {
            $this->request->addErrorMessage($attr->validation_message, $attr->pname);
            return ;
        }
        // 標準メッセージ
        $msg = $this->resource->getResource('errors.'. $type);
        if (Teeple_Util::isBlank($msg)) {
            $msg = Teeple_ValidatorManager::DEFAULT_MESSAGE;
        }
        $param = array($attr->label);
        $errorMessage = Teeple_Util::formatErrorMessage($msg, $param);
        $this->request->addErrorMessage($errorMessage, $attr->pname);
        return;
    }
    
}

?>