<?php

/**
 * 認証と承認を行なうフィルターです。
 *
 * @package     teeple.filter
 * @author      Mitsutaka Sato
 */
class Teeple_Filter_MobileAuth extends Teeple_Filter
{
	
	private static $DEFAULT_AUTH = '_DEFAULT_';
	private static $NOAUTH = '_NOAUTH_';
	private static $NOROLE = '_NOROLE_';
	private static $REJECT = '_REJECT_';
	
    /**
     * ログインアカウント
     *
     * @var LoginAccountMember
     */
    private $loginaccount;
    public function setSessionComponent_LoginAccountMember($c) {
        $this->loginaccount = $c;
    }
	
	/**
	 * @var Teeple_DataSource
	 */
	private $dataSource;
	public function setComponent_Teeple_DataSource($c) {
	    $this->dataSource = $c;
	}

    /**
     * @var Teeple_Response
     */
    private $response;
    public function setComponent_Teeple_Response($c) {
        $this->response = $c;
    }
    
    /**
     * @var Logic_MobileLogin
     */
    private $loginLogic;
    public function setComponent_Logic_MobileLogin($c) {
        $this->loginLogic = $c;
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
		
		// guid=ONのチェック mod_rewriteとの兼ね合いで問題があるのでやめる
		/*
		if ($this->checkGuidRedirect($action_name)) {
		    return;
		}
		*/
		
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
		
		// 自動ログイン
		if (! $this->loginaccount->isAuthed()) {
		    if ($this->loginLogic->autoLogin()) {
		        $this->log->info("自動ログインに成功しました。");
		    }
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
	    $this->request->setParameter("referer", $_SERVER['REQUEST_URI']);
	    $this->response->setView('redirect:login');
	    $this->completeAction();
		return;
	}
	
	/**
	 * GETでguid=ONがついてなくてdocomoだったらリダイレクト
	 */
	private function checkGuidRedirect($action_name) {
	    
	    $mobile_agent = Net_UserAgent_Mobile::singleton();
	    if ($this->request->getMethod() == 'GET' && 
	        $this->request->getParameter('guid') != 'ON' &&
	        $mobile_agent->isDoCoMo()) {
	        $this->setGuidRedirectResponse($action_name);
	        return TRUE;
	    }
	    return FALSE;
	}
	
	/**
	 * guid=ONをつけたURLをresponseにセットします。
	 * @param unknown_type $action_name
	 */
	private function setGuidRedirectResponse($action_name) {
	    
	    $url = Teeple_Util::getAbsoluteUrlFromActionName($action_name, $this->request->isHttps());
	    $params = $this->request->getParameters();
	    $params['guid'] = 'ON';
	    $q = U::getQueryString($params);
	    $this->response->setView("location: {$url}?{$q}");
	    $this->completeAction();
	    return;
	}

}
?>