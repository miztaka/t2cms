<?php

/**
 * Admin_Page_List
 */
class Admin_Page_List extends AdminActionBase
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
    public $session_key = "admin_page_list.searchConds";    
    
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
        
        $searchConds = $this->session->getParameter($this->session_key);
        if (! is_object($searchConds)) {
            return $this->doSearch();
        }
        $searchConds->copyTo($this);
        
        return $this->execSearch($searchConds);
    }
    
    /**
     * ページを削除します。
     * @return unknown_type
     */
    public function doDelete() {
        
        $entity = $this->getEntity($this->id);
        if ($entity == NULL) {
            return NULL;
        }
        
        /*
        $entity->delete_flg = 1;
        $entity->delete_time = $entity->now();
        $entity->update();
        */
        $entity->delete();
        $this->request->addNotification("ページを削除しました。");
        
        return $this->doSearch();
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
            "srch_name",
            "srch_url",
            "srch_meta_entity_id",
            "limit",
            "offset",
            "pagenum"
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
        
        // クエリーの作成
        $entity = Entity_Page::get()
            ->eq('delete_flg', '0')
            ->contains("name", $searchConds->srch_name)
            ->contains("url", $searchConds->srch_url)
            ->eq('meta_entity_id', $searchConds->srch_meta_entity_id)
        ;
        
        // 件数
        $this->numOfResults = $entity->count();
        
        // ページング
        if ($this->numOfResults > 0) {
            $offset = $this->pagenum * $this->limit;
            $this->searchResult = $entity
                ->limit($searchConds->limit)
                ->offset($searchConds->offset)
                ->order('id')
                ->select();
        }
        
        $this->dispResult = true;
        return NULL;
    }
    
    /**
     * ページ情報を取得します。
     * @param int $id
     * @return Entity_Page
     */
    private function getEntity($id) {
        
        if (Teeple_Util::isBlank($id)) {
            $this->request->addErrorMessage("不正なアクセスです。");
            return NULL;
        }
        $entity = Entity_Page::get()->find($id);
        if ($entity == NULL) {
            $this->log->info("指定されたPageは存在しません。");
            $this->request->addErrorMessage("不正なアクセスです。");
            return NULL;
        }
        return $entity;
    }

}

?>