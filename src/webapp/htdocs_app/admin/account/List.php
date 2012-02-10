<?php

/**
 * Admin_Account_List
 */
class Admin_Account_List extends AdminActionBase
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
    public $session_key = "admin_account_list.searchConds";    
    
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
     * Teeple_Resource
     * @var Teeple_Resource
     */
    private $resource;
    public function setComponent_Teeple_Resource($c) {
        $this->resource = $c;
    }
    
    /**
     * QdmailFactory
     *
     * @var QdmailFactory
     */
    private $qdmailFactory;
    public function setComponent_QdmailFactory($c) {
        $this->qdmailFactory = $c;
    }
    
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
        
        $account = $this->getAccount($this->id);
        if ($account == NULL) {
            return NULL;
        }
        
        $account->delete();
        $this->request->addErrorMessage("アカウントを削除しました。");
        
        return $this->doSearch();
    }
    
    /**
     * パスワードを再発行します。
     * @return null
     */
    public function doChangePw() {
        
        $account = $this->getAccount($this->id);
        if ($account == NULL) {
            return NULL;
        }
        
        $account->login_pw = U::randString(8);
        $account->pw_change_date = $account->now();
        $account->lock_flg = 0;
        $account->pw_fail_num = 0;
        $account->update();
        
        // メールを送信
        /*
        $data = array(
            'mail' => $account->email,
            'name'  => $account->name ."様",
            'login_pw' => $account->login_pw
        );
        $subject = $this->resource->getResource("mail.subject.change_pw");
        $body = $this->resource->getResource("mail.body.change_pw");
        if (! $this->qdmailFactory->sendTextEmail($data, $subject, $body)) {
            // エラー
            $this->request->addErrorMessage("メール送信に失敗しました。少し時間を置いてから再度操作して下さい。");
            $this->defaultTx->rollback();
            return NULL;
        }
        */
        
        $this->request->addNotification("再発行したパスワードは {$account->login_pw} です。忘れずにメモしてください。 ");
        $account->convert2Page($this);
        return "admin/account/create_complete.html";
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
            "srch_email",
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
        $entity = Entity_LoginAccount::get()
            ->eq('delete_flg', '0')
            ->contains("name", $searchConds->srch_name)
            ->contains("login_id", $searchConds->srch_email)
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
     * アカウント情報を取得します。
     * @param int $id
     * @return Entity_LoginAccount
     */
    private function getAccount($id) {
        
        if (Teeple_Util::isBlank($id)) {
            $this->request->addErrorMessage("不正なアクセスです。");
            return NULL;
        }
        $account = Entity_LoginAccount::get()->find($id);
        if ($account == NULL) {
            $this->log->info("指定されたLoginAccountは存在しません。");
            $this->request->addErrorMessage("不正なアクセスです。");
            return NULL;
        }
        return $account;
    }

}

?>