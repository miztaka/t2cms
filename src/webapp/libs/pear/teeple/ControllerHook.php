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
 * コントローラのHookロジックです
 * @package     teeple
 */
class Teeple_ControllerHook
{
    /**
     * @var Logger
     */
    protected $log;
    
    /**
     * @var Teeple_DevHelper
     */
    protected $devhelper;
    public function setComponent_Teeple_DevHelper($c) {
        $this->devhelper = $c;
    }

    /**
     * コンストラクター
     */
    public function __construct() {
        $this->log = LoggerManager::getLogger(get_class($this));
        return;
    }

    /**
     * URIからAction名を特定します。
     *
     * @return string
     */
    public function makeActionName() {
        $path = Teeple_Util::getPathInfo();
        return Teeple_Util::path2Action($path);
    }
    
    /**
     * Actionが見つからなかったときの処理です。
     * @param string $actionName
     * @return controllerに処理を続けさせるかどうか？
     */
    public function actionClassNotFound($actionName) {
        
        if (defined('USE_DEVHELPER') && USE_DEVHELPER) {
            $this->devhelper->execute($actionName);
        } else {
            // 404で終了する
            header("HTTP/1.1 404 Not Found");
            print('Page Not Found');
        }
        return FALSE; // actionChainに進まない
    }
}

?>