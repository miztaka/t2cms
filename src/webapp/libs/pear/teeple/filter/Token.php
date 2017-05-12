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
 * @package     teeple
 * @author      Mitsutaka Sato <miztaka@gmail.com>
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 */

/**
 * Token処理を行うFilter
 * Getリクエストだった場合は、build, Postリクエストだった場合は checkを行なう
 *
 * @package     teeple.filter
 */
class Teeple_Filter_Token extends Teeple_Filter
{
    
    /**
     * @var Teeple_Token
     */
    private $token;
    public function setComponent_Teeple_Token($c) {
        $this->token = $c;
    }
    
    /**
     * コンストラクタ
     *
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * トークンの処理を行う
     *
     */
    public function prefilter() {
    	
    	$action_name = $this->actionChain->getCurActionName();
    	$token_name = "token_{$action_name}";
    	$this->token->setName($token_name);
    	
    	$method = strtolower($this->request->getMethod());
    	switch ($method) {
    		case 'get':
    			$this->token->build();
    			break;
    		case 'post':
    			if (! $this->token->check()) {
    				$this->request->setFilterError('Token');
    				$this->request->addErrorMessage('不正なアクセスです。');
    				$this->log->warn('Tokenが不正です。');
    			}
    			break;
    	}
        return;
    }
    
    public function postfilter() {}
}

?>