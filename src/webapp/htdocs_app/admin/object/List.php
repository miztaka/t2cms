<?php

/**
 * Admin_Object_List
 */
class Admin_Object_List extends AdminActionBase
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
    
    public function roleOptions() {
        return Entity_LoginAccount::$_roleOptions;
    }    

    /**
     * @var string
     */
    public $session_key = "admin_object_list.searchConds";    
    
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
     * アカウントを削除します。
     * @return unknown_type
     */
    public function doDelete() {
        
        $entity = $this->getEntity($this->id);
        if ($entity == NULL) {
            throw new Exception("不正なアクセスです。");
        }
        // ページに使われていないこと
        $pages = Entity_Page::get()
            ->eq('meta_entity_id', $entity->id)
            ->eq('delete_flg', 0)
            ->count();
        if ($pages > 0) {
            $this->request->addErrorMessage("先にこのオブジェクトを使用しているページを全て削除してください。");
            return $this->doSearch();
        }
        
        $entity->delete_flg = 1;
        $entity->delete_time = $entity->now();
        $entity->update();
        $this->request->addNotification("オブジェクトを削除しました。");
        
        return $this->doSearch();
    }
    
    private $colmap = array(
        "id" => "_dummy",
        "create_time" => "_dummy",
        "timestamp" => "_dummy",
        "created_by" => "_dummy",
        "modified_by" => "_dummy",
        "delete_time" => "_dummy",
        "version" => "_dummy",
        "meta_entity_id" => "_dummy"
    );
    
    /**
     * 全オブジェクト設定をXMLにして出力します。
     */
    public function doExport() {
        
        $objects = Entity_MetaEntity::get()
            ->eq('delete_flg', 0)
            ->order("IFNULL(seq,999), id")
            ->select();
        
        $results = array();
        foreach ($objects as $object) {
            $one = new stdClass();
            $object->convert2Page($one, $this->colmap);
            $one->_dummy = NULL;
            $one->_attributes = array();
            $attrs = Entity_MetaAttribute::get()
                ->eq('meta_entity_id', $object->id)
                ->order("IFNULL(seq,999), id")
                ->select();
            foreach ($attrs as $attr) {
                $oneAttr = new stdClass();
                $attr->convert2Page($oneAttr, $this->colmap);
                $oneAttr->_dummy = NULL;
                array_push($one->_attributes, $oneAttr);
            }
            array_push($results, $one);
        }
        
        if(isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'],'MSIE')) {
            header('Pragma:');
        }
        header("Content-disposition: attachment; filename=objects.json");
        header("Content-type: application/octet-stream; name=objects.json");
        print json_encode($results);
        $this->request->completeResponse();
        return null;
    }
    
    /**
     * オブジェクト設定をインポートします。
     */
    public function doImport() {
        
        if (! is_uploaded_file($_FILES["import_file"]["tmp_name"])) {
            $this->request->addErrorMessage("ファイルが選択されていません。");
            return $this->doBack();
        }
        $jsonStr = file_get_contents($_FILES["import_file"]["tmp_name"]);
        if (Teeple_Util::isBlank($jsonStr)) {
            $this->request->addErrorMessage("ファイルが読み込めませんでした。");
            return $this->doBack();
        }
        $objects = json_decode($jsonStr);
        foreach ($objects as $obj) {
            $entity = Entity_MetaEntity::get()
                ->eq("pname", $obj->pname, FALSE)
                ->find();
            if ($entity != null) {
                $this->updateObjectDef($obj, $entity);
            } else {
                $this->insertObjectDef($obj);
            }
        }
        
        $this->request->addNotification("オブジェクト設定をインポートしました。");
        return $this->doBack();
    }
    
    /**
     * オブジェクト設定を更新します。
     * @param unknown_type $obj
     * @param Entity_MetaEntity $entity
     */
    private function updateObjectDef($obj, $entity) {
        
        $attrs = $obj->_attributes;
        $obj->_attributes = null;
        $this->copy2Entity($obj, $entity);
        $entity->update();
        
        foreach ($attrs as $attr) {
            $metaAttr = Entity_MetaAttribute::get()
                ->eq("meta_entity_id", $entity->id, FALSE)
                ->eq("pname", $attr->pname, FALSE)
                ->find();
            if ($metaAttr == null) {
                if ($attr->delete_flg != 1) {
                    $metaAttr = Entity_MetaAttribute::get();
                    $this->copy2Entity($attr, $metaAttr);
                    $metaAttr->meta_entity_id = $entity->id;
                    $metaAttr->insert();
                }
            } else {
                $this->copy2Entity($attr, $metaAttr);
                $metaAttr->update();
            }
        }
        return;
    }
    
    /**
     * オブジェクト設定を追加します。
     * @param unknown_type $obj
     */
    private function insertObjectDef($obj) {
        
        $attrs = $obj->_attributes;
        $obj->_attributes = null;
        $entity = Entity_MetaEntity::get();
        $this->copy2Entity($obj, $entity);
        $entity->insert();
        
        foreach ($attrs as $attr) {
            $metaAttr = Entity_MetaAttribute::get();
            $this->copy2Entity($attr, $metaAttr);
            $metaAttr->meta_entity_id = $entity->id;
            $metaAttr->insert();
        }
        return;
    }
    
    /**
     * オブジェクトの内容をコピーします。
     * @param unknown_type $obj
     * @param Teeple_ActiveRecord $entity
     */
    private function copy2Entity($obj, $entity) {
        
        foreach (get_object_vars($obj) as $prop => $value) {
            if (! Teeple_Util::startsWith($prop, "_")) {
                $entity->$prop = $value;
            }
        }
        return;
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
            "limit",
            "offset",
            "pagenum"
        ));
        //$srch_conds->copyFrom($this);
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
        $entity = Entity_MetaEntity::get()
            ->eq('delete_flg', '0')
        ;
        
        // 件数
        $this->numOfResults = $entity->count();
        
        // ページング
        if ($this->numOfResults > 0) {
            $offset = $this->pagenum * $this->limit;
            $this->searchResult = $entity
                ->limit($searchConds->limit)
                ->offset($searchConds->offset)
                ->order("IFNULL(seq,999), id")
                ->select();
        }
        
        $this->dispResult = true;
        return NULL;
    }
    
    /**
     * オブジェクト情報を取得します。
     * @param int $id
     * @return Entity_MetaEntity
     */
    private function getEntity($id) {
        
        if (Teeple_Util::isBlank($id)) {
            $this->request->addErrorMessage("不正なアクセスです。");
            return NULL;
        }
        $entity = Entity_MetaEntity::get()->find($id);
        if ($entity == NULL) {
            $this->log->info("指定されたオブジェクトは存在しません。");
            $this->request->addErrorMessage("不正なアクセスです。");
            return NULL;
        }
        return $entity;
    }

}

?>