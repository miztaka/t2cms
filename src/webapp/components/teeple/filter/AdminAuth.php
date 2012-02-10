<?php

/**
 * 認証と承認を行なうフィルターです。
 *
 * @package     teeple.filter
 * @author      Mitsutaka Sato
 */
class Teeple_Filter_AdminAuth extends Teeple_Filter
{
	
	private static $DEFAULT_AUTH = '_DEFAULT_';
	private static $NOAUTH = '_NOAUTH_';
	private static $NOROLE = '_NOROLE_';
	private static $REJECT = '_REJECT_';
	
	/**
	 * ログインアカウント
	 *
	 * @var LoginAccountAdmin 
	 */
	private $loginaccount;
	public function setSessionComponent_LoginAccountAdmin($c) {
	    $this->loginaccount = $c;
	}
	
    /**
     * @var Teeple_Response
     */
    private $response;
    public function setComponent_Teeple_Response($c) {
        $this->response = $c;
    }
	
    /**
     * コンストラクター
     *
     * @access  public
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * 管理画面の認証を行います。
     *
     * @access  public
     */
    public function prefilter()
    {
        $className = get_class($this);
        
        $this->log->debug(var_export($this->loginaccount, true));
        
		// アクション名を取得する		
		$action_name = $this->actionChain->getCurActionName();
		$this->log->debug("actionName: ${action_name}", "${className}#execute");
		
		// このActionに必要なROLEを取得する。
		$role = NULL;
		$attributes = $this->getAttributes();
		foreach ($attributes as $regex => $role_name) {
			if (preg_match("/^${regex}/", $action_name)) {
				$this->log->debug("${regex} にマッチしました。");
				$role = $role_name;
				break;
			}
		}
		if ($role == NULL && isset($attributes[self::$DEFAULT_AUTH])) {
			$role = $attributes[self::$DEFAULT_AUTH];
		}
		if ($role == NULL) {
			$role = self::$REJECT;
		}

		// REJECT の場合、ログイン画面へ
		if ($role === self::$REJECT) {
		    $this->log->info("この機能へはアクセスできません。");
		    $this->request->addErrorMessage("この機能へはアクセスできません。");
			$this->forwardLogin();
			return;
		}
		
		// NOAUTH以外の場合、認証されていることが必要
		if ($role !== self::$NOAUTH) {

			if (! $this->loginaccount->isAuthed()) {
			    $this->log->info("認証されていません。");
			    $this->request->addErrorMessage("ログインしてください。");
				$this->forwardLogin();
				return;
			}
			
			// NOROLE以外の場合、ROLEを保持していることが必要
			if ($role !== self::$NOROLE && ! $this->loginaccount->hasRole($role)) {
			    $this->log->info("この機能にはアクセス権がありません。");
			    $this->request->addErrorMessage("この機能にはアクセス権がありません。別のIDでログインしてください。");
				$this->forwardLogin();
				return;
			}
		}
		
        return;
    }
    
    public function postfilter() {}
    
	private function forwardLogin() {
	    
	    if (array_key_exists('HTTP_X_REQUESTED_WITH', $_SERVER) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
	        $this->request->addErrorMessage('セッションがタイムアウトしました。ログインし直してください。');
	        $this->response->setView('common/message_area.tpl');
	        $this->completeAction();
	    } else {
	       $this->response->setView('redirect:admin_login');
	       $this->completeAction();
	    }
		return;
	}

}
?>
