<?php

/**
 * Admin_Login
 *
 * @package     modules
 * @author      
 * @access      public
 */
class Admin_Login  extends AdminActionBase
{
    
    public static function actionName() {
        return strtolower(__CLASS__);
    }   
	
    const VALIDATION_TARGET = "doLogin";
    const VALIDATION_CONFIG = '
login_id:
  required: {}
login_pw:
  required: {}
    ';
    
    const CONVERTER_CONFIG = '
__all:
  trim: {}
    ';    
    
	/**
	 * Entity_LoginAccount
	 *
	 * @var Entity_LoginAccount
	 */
	private $entity;
	public function setEntity_LoginAccount($c) {
	    $this->entity = $c;
	}
	
    /**
     * ログインページを表示します。
     * セッションは全て削除します。
     *
     * @access  public
     */
    public function execute()
    {
        // セッション変数を全て解除する
        $_SESSION = array();

        // セッションを切断するにはセッションクッキーも削除する。
        // Note: セッション情報だけでなくセッションを破壊する。
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time()-42000, '/');
        }

        // 最終的に、セッションを破壊する
        session_destroy();        
        
        return NULL;
    }
	
	/**
	 * ログイン認証を実行します。
	 * @return string
	 */
	public function doLogin()
	{
	    $account = Entity_LoginAccount::get()
	       ->eq('login_id', $this->login_id, FALSE)
	       //->eq('login_pw', $this->login_pw, FALSE)
	       ->eq('delete_flg', 0)
	       ->find();
	       
	    if ($account == NULL) {
	        // ログインID間違い
	        $this->request->addErrorMessage("ログインIDまたはパスワードが間違っています。");
	        return NULL;
	    }
        if ($account->lock_flg == 1) {
            $this->request->addErrorMessage("現在アカウントはロックされています。パスワードの再発行を申請してください。");
            return NULL;
        }
        /*
        if (! $this->checkPwChange($account)) {
            $this->request->addErrorMessage("現在アカウントはロックされています。パスワードの再発行を申請してください。");
            return NULL;
        }
        */
	    if ($account->login_pw != $this->login_pw) {
	        // パスワード間違い
	        $account->pw_fail_num += 1;
	        if ($account->pw_fail_num >= PW_FAIL_LIMIT) {
	            $account->lock_flg = 1;
	        }
	        $account->update();
	        $this->request->addErrorMessage("ログインIDまたはパスワードが間違っています。");
	        return NULL;
	    }
	    
	    if ($account->pw_fail_num > 0) {
	        $account->pw_fail_num = 0;
	        $account->update();
	    }
	    
	    // 認証成功
	    $this->loginaccount->authorize();
	    $this->loginaccount->roles[] = $account->role;
	    $this->loginaccount->info = new stdClass;
	    $account->convert2Page($this->loginaccount->info);

	    return $this->redirect(Admin_Home::actionName());
	}
	
	/**
	 * パスワードの変更が6ヶ月以内に行なわれているかチェックする
	 * @param Entity_LoginAccount $account
	 */
	private function checkPwChange($account) {
	    list($y,$m,$d) = explode('-', date('Y-n-j'));
	    $before6 = date('Y-m-d', mktime(0,0,0,$m-6,$d,$y));
	    return $account->pw_change_date > $before6;
	}
	
}

?>