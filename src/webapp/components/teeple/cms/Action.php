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

/**
 * CMS用のアクションクラスです。
 * @package     teeple.cms
 */
class Teeple_Cms_Action extends MyActionBase
{
    public static function actionName() {
        return strtolower(__CLASS__);
    }

    // 特定のメソッド時にValidationを実行したいときに定義。
    //const VALIDATION_TARGET = "";
    
    // Validationを実行したいときに定義。(ex. sample/Validator.php)
    //const VALIDATION_CONFIG = '
    //';
    
    // Converterを実行したいときに定義。(ex. sample/Converter.php)
    const CONVERTER_CONFIG = '
__all:
  trim: {}
    ';
    
    /**
     * @var Teeple_Cms_FormValidator
     */
    private $cmsValidator;
    public function setComponent_Teeple_Cms_FormValidator($c) {
        $this->cmsValidator = $c;
    }

    /**
     * Teeple_Resource
     * @var Teeple_Resource
     */
    private $resource;
    public function setComponent_Teeple_Resource($c) {
        $this->resource = $c;
    }
    
    /**
     * @var Logic_FileUpload
     */
    private $uploadLogic;
    public function setComponent_Logic_FileUpload($c) {
        $this->uploadLogic = $c;
    }
    
    /**
     * @var Plugin_PagePlugin
     */
    private $pagePlugin;
    public function setComponent_Plugin_PagePlugin($c) {
        $this->pagePlugin = $c;
    }
    
    /**
     * @var Entity_Page
     */
    public $_pageInfo;
    
    /**
     * @var Teeple_EavRecord
     */
    protected $_record;
    
    /**
     * @return Teeple_EavRecord
     */
    public function getRecord() {
        if ($this->_record == null) {
            $this->_record = new Teeple_EavRecord($this->_pageInfo->meta_entity_id);
        }
        return $this->_record;
    }
    
    /**
     * 標準で実行されるメソッドです。
     */
    public function execute() {
        
        // 公開フラグのチェック
        if ($this->_pageInfo->publish_flg != 1) {
            if (Teeple_Util::isBlank($this->preview) || ! $this->checkAdminSession()) {
                $this->log->info("このページは公開されていません。 ({$this->_pageInfo->url})");
                return $this->exit404("ページが見つかりません。");
            }
        }
        
        // テンプレートにプラグインをセット
        $this->pagePlugin->pageInfo = $this->_pageInfo;
        $this->__smarty->assign('plugin', $this->pagePlugin);
        
        switch($this->_pageInfo->page_type) {
            case '1':
                return $this->detailPage();
            case '2':
                return $this->listPage();
            case '3':
                return $this->formPage();
        }
        $this->request->completeResponse();
        return null;
    }
    
    /**
     * 詳細ページを表示します。
     */
    protected function detailPage() {
        
        // プレビューチェック
        $publishedOnly = TRUE;
        if (! Teeple_Util::isBlank($this->preview) && $this->checkAdminSession()) {
            $publishedOnly = FALSE; 
        }
        
        // URLからIDを取得
        $basename = basename(Teeple_Util::getPathInfo());
        $buf = explode('.', $basename);
        $id = $buf[0];
        $record = NULL;
        if (! is_numeric($id)) {
            // 先頭のレコードを取得
            $records = Teeple_EavRecord::neu($this->_pageInfo->meta_entity_id)
                ->limit(1)
                ->select($publishedOnly);
            if (empty($records)) {
                return $this->exit404("ページが見つかりません。");
            }
            $record = $records[0];
        } else {
            $record = Teeple_EavRecord::find($id, $publishedOnly);
        }
        if ($record == null) {
            return $this->exit404("ページが見つかりません。");
        }
        
        // レコードをテンプレートにセット
        $this->__smarty->assign('record', $record);
        
        return $this->_pageInfo->template_path .".html";
    }
    
    /**
     * 一覧ページを表示します。
     */
    protected function listPage() {
        
        // ページング
        $pager = new Teeple_Pager();
        $pager->page = 1;
        $pager->limit = 0;
        if (! Teeple_Util::isBlank($this->_pageInfo->page_limit)) {
            $pager->limit = $this->_pageInfo->page_limit;
        }
        if (! Teeple_Util::isBlank($this->limit) && is_numeric($this->limit)) {
            $pager->limit = $this->limit;
        }
        if (! Teeple_Util::isBlank($this->page) && is_numeric($this->page)) {
            $pager->page = $this->page;
        }
        
        // レコードの一覧を取得
        $query = Teeple_EavRecord::neu($this->_pageInfo->meta_entity_id);
        $this->buildQuery($query);
        $records = $query->select(true, $pager);
        $this->log->debug("レコード数： ". count($records));
        $this->log->debug("Pager: ". var_export($pager, true));
        
        // レコード一覧をテンプレートにセット
        $this->__smarty->assign('records', $records);
        $this->__smarty->assign('pager', $pager);
        //$this->pager_json = json_encode($pager);
        
        return $this->_pageInfo->template_path .".html";
    }
    
    /**
     * リストページの検索条件を組み立てます。
     * @param $query Teeple_EavRecord
     */
    protected function buildQuery($query) {
        
        $params = $this->request->getParameters();
        foreach ($params as $name => $value) {
            if (Teeple_Util::isBlank($value)) {
                continue;
            }
            $op = NULL;
            $prop = NULL;
            if (Teeple_Util::endsWith($name, "_eq")) {
                $op = 'eq';
                $prop = substr($name, 0, strlen($name)-3);
            } elseif (Teeple_Util::endsWith($name, "_like")) {
                $op = 'contains';
                $prop = substr($name, 0, strlen($name)-5);
            }
            if ($op == NULL) {
                continue;
            }
            
            // propが存在するか？
            if ($query->getAttributeByPname($prop)) {
                $query->$op($prop, $value);
            } elseif (in_array($prop, Teeple_EavRecord::$_SELECT_FIELD)) {
                $query->$op($prop, $value);
            } elseif ("year" == $prop && preg_match('/^\d{4}$/', $value)) {
                $ny = $value + 1;
                $query->ge("publish_start_dt", "{$value}-01-01 00:00");
                $query->lt("publish_start_dt", "{$ny}-01-01 00:00");
            }
        }
        return;
    }
    
    /**
     * フォームページを表示します。
     * 
     */
    protected function formPage() {
        return $this->_pageInfo->template_path .".html";
    }
    
    /**
     * フォームページの確認画面を表示します。
     */
    public function doConfirm() {
        
        // テンプレートにプラグインをセット
        $this->pagePlugin->pageInfo = $this->_pageInfo;
        $this->__smarty->assign('plugin', $this->pagePlugin);
        
        // フォームページ以外ははじく。
        if ($this->_pageInfo->page_type != Entity_Page::PAGE_TYPE_FORM) {
            return $this->exit404("ページが見つかりません。");
        }
        // 入力値検証
        if (! $this->validate()) {
            return $this->onValidateError();
        }
        
        return $this->_pageInfo->template_path ."-confirm.html";
    }
    
    /**
     * フォームページの投稿内容をデータベースに登録します。
     */
    public function doRegist() {
        
        // テンプレートにプラグインをセット
        $this->pagePlugin->pageInfo = $this->_pageInfo;
        $this->__smarty->assign('plugin', $this->pagePlugin);
        
        // フォームページ以外ははじく。
        if ($this->_pageInfo->page_type != Entity_Page::PAGE_TYPE_FORM) {
            return $this->exit404("ページが見つかりません。");
        }
        // 入力値検証
        if (! $this->validate()) {
            return $this->onValidateError();
        }
        // データ登録
        $this->_record->convert2Entity($this);
        if (! Teeple_Util::isBlank($this->_pageInfo->default_publish_flg) && $this->_pageInfo->default_publish_flg == 1) {
            $this->_record->publish_flg = 1;
        } else {
            $this->_record->publish_flg = 0;
        }
        $this->_record->insert();
        /*
        $imageFields = $this->_record->getImageFieldNames();
        if ($imageFields && !empty($imageFields)) {
            $this->uploadLogic->updateFile($this, $this->_record, $this->_record->getImageFieldNames(), $this->_record->_metaEntity->pname);
            $this->_record->update();
        }
        */
        // 通知メールの送信
        if (! Teeple_Util::isBlank($this->_pageInfo->notify_email)) {
            $this->sendNotifyEmail();
        }
        
        // 完了ページへリダイレクト (コンバージョンを取得できるようにURLを変える)
        $act = str_replace("/", "_", $this->_pageInfo->url);
        $act .= "-complete";
        $url = Teeple_Util::getAbsoluteUrlFromActionName($act, $this->request->isHttps());
        return "location:$url";
    }
    
    /**
     * 入力値検証を行います。
     */
    protected function validate() {
        // EAVレコードを取得
        $record = new Teeple_EavRecord($this->_pageInfo->meta_entity_id);
        $this->_record = $record;
        return $this->cmsValidator->validate($record, $this);
    }
    
    /**
     * Validationエラー時の処理。
     */
    public function onValidateError() {
        return $this->_pageInfo->template_path .".html";
    }
    
    /**
     * @return bool
     */
    private function checkAdminSession() {
        
        $adminSession = Teeple_Container::getInstance()->getSessionComponent('LoginAccountAdmin');
        if (is_object($adminSession) && $adminSession->isAuthed()) {
            return true;
        }
        return false;
    }
    
    /**
     * 投稿フォームの通知を行います。
     */
    protected function sendNotifyEmail() {
        
        $subject = $this->resource->getResource("mail.notify.subject");
        $body = $this->resource->getResource("mail.notify.body");
        $form_data = array();
        foreach ($this->_record->_metaAttributes as $attr) {
            $pname = $attr->pname;
            $buff = is_array($this->_record->$pname) ? 
                implode("\n", $this->_record->$pname) : $this->_record->$pname;
            $form_data[] = "[". $attr->label ."]\n". $buff;
        }
        $data = array(
            'mail' => $this->_pageInfo->notify_email,
            'page_name' => $this->_pageInfo->name,
            'form_data' => implode("\n\n", $form_data)
        );
        QdmailFactory::instance()->sendTextEmail($data, $subject, $body);
        return;
    }
    
    /**
     * 指定されたpnameの属性の選択肢を取得します。
     * @param $pname
     */
    public function getAttrOptions($pname) {
        $attr = $this->getRecord()->getAttributeByPname($pname);
        if ($attr) {
            return $attr->data_type == Entity_MetaAttribute::DATA_TYPE_REF ?
                $attr->getRefOptions() : $attr->getOptions();
        }
        return array();
    }
    
    /**
     * 参照フィールドの表示ラベルを取得します。
     * @param String $pname
     */
    public function refLabel($pname) {
        
        $record = $this->getRecord();
        $record->$pname = $this->$pname;
        return $record->refLabel($pname);
    }
    
}

?>