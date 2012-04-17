<?php

/**
 * Admin_Cms_List
 */
class Admin_Cms_List extends AdminActionBase
{
    public static function actionName() {
        return strtolower(__CLASS__);
    }

    // 特定のメソッド時にValidationを実行したいときに定義。
    //const VALIDATION_TARGET = "";
    
    // Validationを実行したいときに定義。(ex. sample/Validator.php)
    //const VALIDATION_CONFIG = '';
    
    // Converterを実行したいときに定義。(ex. sample/Converter.php)
    const CONVERTER_CONFIG = '
__all:
  trim: {}
    ';
    
    /**
     * @var string
     */
    public $session_key = "admin_cms_list.searchConds";    
    
    /**
     * 検索結果
     * @var unknown_type
     */
    public $searchResult;
    public $numOfResults = 0;
    public $pagenum = 0;
    public $limit = PAGE_LIMIT;
    public $dispResult = false;    
    
    /**
     * 標準で実行されるメソッドです。
     */
    public function execute() {
        return $this->doSearch();
    }
    
    /**
     * 検索を実行します。
     * @return unknown_type
     */
    public function doSearch() {
        $searchConds = $this->makeSearchBeans();
        return $this->execSearch($searchConds);
    }
    
    /**
     * 戻り用アクション：セッションの検索条件で検索
     * @return unknown_type
     */
    public function doBack() {
        
        $done = $this->request->getParameter("_done");
        if ($done == 'create') {
            $this->request->addNotification("レコードを作成しました。");
        } elseif ($done == 'update') {
            $this->request->addNotification("レコードを更新しました。");
        }
        
        $searchConds = $this->session->getParameter($this->session_key);
        if (! is_object($searchConds)) {
            return $this->doSearch();
        }
        $searchConds->copyTo($this);
        return $this->execSearch($searchConds);
    }
    
    /**
     * レコードを削除します。
     * @return unknown_type
     */
    public function doDelete() {
        
        // meta_record
        $entity = $this->getEntity($this->id);
        if ($entity == NULL) {
            return NULL;
        }
        
        // 参照関係のチェック
        if (! $this->checkRef($entity)) {
            $this->request->addErrorMessage("このレコードは他のレコードから参照されているため削除できません。");
            return $this->doBack();
        }
        $entity->delete_flg = 1;
        $entity->delete_time = $entity->now();
        $entity->update();
        $this->meta_entity_id = $entity->meta_entity_id;
        
        $this->request->addNotification("レコードを削除しました。");
        return $this->doSearch();
    }
    
    /**
     * 参照関係があるかどうかチェックします
     * @param $entity Entity_MetaRecord
     */
    protected function checkRef($entity) {
        
        $count = Entity_MetaValue::get()
            ->join('meta_attribute')
            ->join('meta_record')
            ->eq('meta_attribute.ref_entity_id', $entity->meta_entity_id)
            ->eq('meta_attribute.data_type', Entity_MetaAttribute::DATA_TYPE_REF)
            ->eq('meta_attribute.delete_flg', 0)
            ->eq('meta_record.delete_flg', 0)
            ->eq('base.delete_flg', 0)
            ->eq('value', $entity->id)
            ->count();
        
        return $count == 0;
    }
    
    /**
     * Validationエラー時の処理。
     */
    public function onValidateError() {
        return NULL;
    }
    
    /**
     * 検索条件のBeanを作成します。
     * @return Teeple_Bean
     */
    private function makeSearchBeans() {
        
        $srch_conds = new Teeple_Bean(array(
            "meta_entity_id",
            "limit",
            "offset",
            "pagenum",
            "search_field",
            "search_word",
            "search_ope",
            "search_ref",
            "published_only"
        ));
        $srch_conds->copyFrom($this);
        $srch_conds->limit = $this->limit;
        $srch_conds->offset = $this->pagenum * $this->limit;
        $this->session->setParameter($this->session_key, $srch_conds);
        
        return $srch_conds;
    }
    
    /**
     * 実際の検索処理
     * @param $searchConds stdClass
     * @return string
     */
    private function execSearch($searchConds) {
        
        if (! $this->init()) {
            throw new Exception("不正なアクセスです。");
        }
        
        // ページング
        $pager = new Teeple_Pager();
        $pager->page = $searchConds->pagenum + 1;
        $pager->limit = $searchConds->limit;
        
        // EAV検索
        $eav = Teeple_EavRecord::neu($searchConds->meta_entity_id);
        if (is_array($searchConds->search_field)) {
            $fields = $searchConds->search_field;
            for ($i=0; $i<count($fields); $i++) {
                $pname = $fields[$i];
                $ope = $searchConds->search_ope[$i];
                $word = $searchConds->search_word[$i];
                if (! Teeple_Util::isBlank($pname) && ! Teeple_Util::isBlank($ope) && ! Teeple_Util::isBlank($word)) {
                    if ($ope == 'eq') {
                        $eav->eq($pname, $word);
                    } elseif ($ope == 'like') {
                        $eav->contains($pname, $word);
                    }
                }
            }
        }
        if (is_array($searchConds->search_ref)) {
            foreach ($searchConds->search_ref as $pname => $value) {
                $eav->eq($pname, $value);
            }
        }
        
        $publishedOnly = $searchConds->published_only == '1' ? true : false;
        $this->searchResult = $eav->select($publishedOnly, $pager);
        $this->numOfResults = $pager->total;
        
        $this->dispResult = true;
        return NULL;
    }
    
    /**
     * レコード情報を取得します。
     * @param int $id
     * @return Entity_MetaRecord
     */
    private function getEntity($id) {
        
        if (Teeple_Util::isBlank($id)) {
            $this->request->addErrorMessage("不正なアクセスです。");
            return NULL;
        }
        $entity = Entity_MetaRecord::get()->find($id);
        if ($entity == NULL) {
            $this->log->info("指定されたレコードは存在しません。");
            $this->request->addErrorMessage("不正なアクセスです。");
            return NULL;
        }
        return $entity;
    }
    
    protected function init() {
        
        if (Teeple_Util::isBlank($this->meta_entity_id)) {
            return false;
        }
        $this->_record =  new Teeple_EavRecord($this->meta_entity_id);
        if (! is_object($this->_record)) {
            return false;
        }
        $this->_list_columns = $this->_record->getListColumns();
        return true;
    }
    
    /**
     * 
     */
    public function metaAttributeOptions() {
        
        if ($this->_metaAttributeOptions == null) {
            if (! is_object($this->_record)) {
                $this->init();
            }
            $result = array();
            foreach ($this->_record->_metaAttributes as $attr) {
                $result[$attr->pname] = $attr->label;
            }
            $this->_metaAttributeOptions = $result;
        }
        return $this->_metaAttributeOptions;
    }
    private $_metaAttributeOptions;
    
    public function search_opeOptions() {
        return array(
            'eq' => 'と一致する',
            'like' => 'を含む'
        );
    }
    
    /**
     * 参照フィールドのAttributeを取得します。
     */
    public function refAttributes() {
        
        if (! is_object($this->_record)) {
            $this->init();
        }
        $result = array();
        foreach ($this->_record->_metaAttributes as $attr) {
            if ($attr->data_type == Entity_MetaAttribute::DATA_TYPE_REF) {
                array_push($result, $attr);
            }
        }
        return $result;
    }
    
    /**
     * 指定された検索条件でダウンロードする
     */
    public function doDownload() {
        
        set_time_limit(3000);
        $searchConds = $this->makeSearchBeans();
        $searchConds->pagenum = 0;
        $searchConds->limit = 0;
        $this->execSearch($searchConds);
        
        $csvdef = array(
            "レコードID" => "id",
            "並び順" => "seq",
            "公開状態" => "publish_flg",
            "公開開始日" => "publish_start_dt",
            "公開終了日" => "publish_end_dt",
            "レコード作成日" => "create_time"
        );
        foreach ($this->_record->_metaAttributes as $attr) {
            $csvdef[$attr->label] = $attr->pname;
        }
        
        $csvWriter = new Writer_CsvWriter($csvdef, true);
        
        if(isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'],'MSIE')) {
            header('Pragma:');
        }
        $entity_name = $this->_record->_metaEntity->pname;
        header("Content-disposition: attachment; filename={$entity_name}.csv");
        header("Content-type: application/octet-stream; name={$entity_name}.csv");
        $csvWriter->outputCsv($this->searchResult, new Admin_Cms_List_Renderer($this));
        
        $this->request->completeResponse();
        return NULL;        
    }
    
    /**
     * レコードの詳細ページをプレビューします。
     * 複数の詳細ページがある場合は一覧を表示します。
     */
    public function doPreview() {
        
        // meta_record
        $entity = $this->getEntity($this->id);
        if ($entity == NULL) {
            $this->request->addErrorMessage('不正なアクセスです。');
            return "admin/cms/previewlist.html";
        }
        
        // 詳細ページを取得
        $pages = Entity_Page::get()
            ->eq('meta_entity_id', $entity->meta_entity_id, FALSE)
            ->eq('page_type', Entity_Page::PAGE_TYPE_DETAIL)
            ->eq('delete_flg', 0)
            ->select();
        if (count($pages) == 0) {
            $this->request->addErrorMessage('該当する詳細ページが１つもありませんでした。');
            return "admin/cms/previewlist.html";
        }
        if (count($pages) == 1) {
            $url = Teeple_Util::getBaseUrl(false)."/".$pages[0]->url."/{$entity->id}.html?preview=1";
            return "location: $url";
        }
        
        // 詳細ページが複数ある場合: URLの一覧を作成
        $urls = array();
        foreach ($pages as $page) {
            $url = new StdClass();
            $url->href = Teeple_Util::getBaseUrl(false)."/".$page->url."/{$entity->id}.html?preview=1";
            $url->name = $page->name;
            $urls[] = $url;
        }
        $this->urls = $urls;
        return "admin/cms/previewlist.html";
    }
    
    /**
     * リソースが画像かどうか？
     */
    public function isImageResource($val) {
        if (!$val) {
            return false;
        }
        return preg_match("/(jpg|gif|png)$/i", $val);
    }

}

/**
 * CSVレンダラー
 * @author miztaka
 *
 */
class Admin_Cms_List_Renderer {

    /**
     * @var Admin_Cms_List
     */
    protected $action;
    
    public function __construct($act) {
        $this->action = $act;
    }
    
    public function render_publish_flg($obj) {
        return $obj->publish_flg == 1 ? '公開' : '非公開';
    }
    
}

?>