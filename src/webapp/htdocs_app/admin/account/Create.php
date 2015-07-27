<?php

/**
 * Admin_Account_Create
 */
class Admin_Account_Create extends AdminActionBase
{
    public static function actionName() {
        return strtolower(__CLASS__);
    }

    // 特定のメソッド時にValidationを実行したいときに定義。
    const VALIDATION_TARGET = "doRegist";
    
    // Validationを実行したいときに定義。(ex. sample/Validator.php)
    const VALIDATION_CONFIG = '
"name.お名前":
    required: {}
    maxbytelength: { maxbytelength: 256 }
"login_id.メールアドレス":
    required: {}
    maxbytelength: { maxbytelength: 128 }
    email: {}
"role.権限":
    required: {}
    maxbytelength: { maxbytelength: 16 }
    ';
    
    // Converterを実行したいときに定義。(ex. sample/Converter.php)
    const CONVERTER_CONFIG = '
__all:
  trim: {}
    ';
    
    public function roleOptions() {
        $roleopt = Entity_LoginAccount::$_roleOptions;
        if (! $this->loginaccount->hasRole('sysadm')) {
            unset($roleopt['sysadm']);
        }
        return $roleopt; 
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
        
        if (! Teeple_Util::isBlank($this->id)) {
            $account = Entity_LoginAccount::get()->find($this->id);
            if ($account == NULL) {
                $this->log->info("指定されたオブジェクトが見つかりません。");
                $this->request->addErrorMessage("不正なアクセスです。");
                return $this->redirect(Admin_Account_List::actionName());
            }

            $account->convert2Page($this);
            if (! Teeple_Util::isBlank($this->allowed_entity)) {
            	$this->allowed_entity = unserialize($this->allowed_entity);
            }
            $this->crudType = "U";
        }
        
        return NULL;
    }
    
    /**
     * 登録処理を実行します。
     * @return unknown_type
     */
    public function doRegist() {
    	
    	// limitedアカウントの場合はアクセス可能なメニューが１つ以上選択されていること
    	if ($this->role == 'limited' && empty($this->allowed_entity)) {
    		$this->request->addErrorMessage("アクセス可能なメニューを１つ以上選択してください。");
    		return $this->onValidateError();
    	}
        
        if ($this->crudType == "U") {
            // 更新
            $account = Entity_LoginAccount::get()->find($this->id);

            // 重複チェック
            if ($this->login_id != $account->login_id && ! $this->checkDuplicate()) {
                return $this->onValidateError();
            }
                
            $account->convert2Entity($this);
            $account->update();
            $this->request->addNotification("アカウントを更新しました。");
        } else {
            // 新規作成
            if (! $this->checkDuplicate()) {
                return $this->onValidateError();
            }
            
            $entity = Entity_LoginAccount::get();
            $entity->convert2Entity($this);
            $raw_pw = U::randString(8);
            $entity->login_pw = U::hashPassword($raw_pw);
            $entity->pw_change_date = $entity->now();
            $entity->insert();
            
            $this->request->addNotification("アカウントを作成しました。パスワードは {$raw_pw} です。忘れずにメモしてください。");
            $this->login_pw = $raw_pw;
            return "admin/account/create_complete.html";
        }
        
        return $this->redirect(Admin_Account_List::actionName());
    }
    
    /**
     * Validationエラー時の処理。
     */
    public function onValidateError() {
        return NULL;
    }
    
    private function checkDuplicate() {
        
        $num = Entity_LoginAccount::get()
            ->eq('login_id', $this->login_id)
            ->count();
        if ($num > 0) {
            $this->request->addErrorMessage("このメールアドレスは既に登録されています。");
            return false;
        }
        return true;
    }

}

?>