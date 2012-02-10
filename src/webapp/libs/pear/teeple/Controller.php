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
 * コントローラクラス
 * @package     teeple
 */
class Teeple_Controller
{
    /**
     * @var Logger
     */
    protected $log;
    
    /**
     * @var Teeple_ActionChain
     */
    protected $actionChain;
    public function setComponent_Teeple_ActionChain($c) {
        $this->actionChain = $c;
    }
    
    /**
     * @var Teeple_ConfigUtils 
     */
    protected $configUtils;
    public function setComponent_Teeple_ConfigUtils($c) {
        $this->configUtils = $c;
    }
    
    /**
     * @var Teeple_FilterChain
     */
    protected $filterChain;
    public function setComponent_Teeple_FilterChain($c) {
        $this->filterChain = $c;
    }
    
    /**
     * @var Teeple_TransactionManager
     */
    protected $txManager;
    public function setComponent_Teeple_TransactionManager($c) {
        $this->txManager = $c;
    }
    
    /**
     * @var Teeple_ControllerHook
     */
    protected $hook;
    public function setComponent_Teeple_ControllerHook($c) {
        $this->hook = $c;
    }
    
    /**
     * @var Teeple_Container
     */
    protected $container;
    public function setComponent_container($c) {
        $this->container = $c;
    }
    
    /**
     * コントローラーを実行します。
     *
     */
    public static function start() {
        
        $log = LoggerManager::getLogger(Teeple_Util::getPathInfo());
        $log->info("*** リクエストを開始します。");
        
        try {
            // コンテナの取得
            $container = Teeple_Container::getInstance();
            //$container->setup(WEBAPP_DIR .'/config/dicon.ini');
            
            // セッションを作成 TODO セッションのパラメータ制御
            $session = $container->getComponent("Teeple_Session");
            $session->start();
            
            // リダイレクトスコープのリクエスト復元
            $request = $session->getParameter("__REDIRECT_SCOPE_REQUEST");
            if (is_object($request)) {
                $request->setActionMethod("execute");
                $request->resetCompleteFlag();
                $request->isRedirect = TRUE;
                $container->register("Teeple_Request", $request);
                $session->removeParameter("__REDIRECT_SCOPE_REQUEST");
            }
            
            // controllerの実行
            $controller = $container->getComponent('Teeple_Controller');
            $controller->execute();
            
        } catch (Exception $e) {
            $txManager = $container->getComponent('Teeple_TransactionManager');
            $txManager->rollbackAll();
            Teeple_ErrorHandler::handle($e);
        }
        return;
    }
    
    /**
     * コンストラクター
     */
    public function __construct() {

        $this->log = LoggerManager::getLogger(get_class($this));
        $this->log->debug("############ コントローラstart.");
        return;
    }
    
    /**
     * フレームワークを起動させる
     *
     * @access  public
     * @since   3.0.0
     */
    public function execute()
    {
        $this->log->debug("************** controller#execute called.");

        // デフォルトトランザクションをコンテナに登録
        $defaultTx = $this->txManager->getTransaction();
        $this->container->register('DefaultTx', $defaultTx);

        // 実行するActionを決定
        $actionName = $this->hook->makeActionName();
        if ($actionName == NULL) {
            throw new Teeple_Exception("アクションが特定できません。");
        }
        
        // 初期ActionをActionChainにセット
        $this->log->debug("****actionName: $actionName");
        if (! $this->actionChain->add($actionName)) {
            $isContinue = $this->hook->actionClassNotFound($actionName);
            if (! $isContinue) {
                return;
            }
        }
        
        // FilterChainのセットアップと実行
        $this->filterChain->build();
        $this->filterChain->execute();
        //$this->filterChain->clear();
    }
    
}

?>