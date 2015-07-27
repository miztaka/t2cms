<?php

/**
 * CMS管理画面の認可を行います。
 * loginaccount->canAccess()でチェックします。
 *
 * @package     teeple.filter
 * @author      Mitsutaka Sato
 */
class Teeple_Filter_AdminCmsAuth extends Teeple_Filter
{
	
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
    	$meta_entity_id = $this->request->getParameter("meta_entity_id");
    	if (! $meta_entity_id) {
    		$id = $this->request->getParameter("id");
    		if ($id) {
    			$record = Entity_MetaRecord::get()->find($id);
    			if ($record) {
    				$meta_entity_id = $record->meta_entity_id;
    			}
    		}
    	}
    	if ($meta_entity_id && ! $this->loginaccount->canAccess($meta_entity_id)) {
    		$this->request->addErrorMessage("このメニューにはアクセスできません。");
    		throw new Exception("このメニューにはアクセスできません。");
    	}
        return;
    }
    
    public function postfilter() {}

}
