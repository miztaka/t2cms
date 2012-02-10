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
 * TODO 未検証
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
     * @var Teeple_ActionChain
     */
    private $actionChain;
    public function setComponent_Teeple_ActionChain($c) {
        $this->actionChain = $c;
    }
    
    /**
     * @var Logger
     */
    private $log;
    
    /**
     * コンストラクタ
     *
     */
    public function __construct() {
        parent::__construct();
        $this->log = LoggerManager::getLogger(get_class($this));
    }

    /**
     * トークンの処理を行う
     *
     */
    public function prefilter() {

        $this->token->setSession($this->session);

        $attributes = $this->getAttributes();
        if (isset($attributes["name"])) {
            $this->token->setName($attributes["name"]);
        }

        $modeArray = array();
        if (isset($attributes["mode"])) {
            $modeArray = explode(",", $attributes["mode"]);
            foreach ($modeArray as $key => $value) {
                $modeArray[$key] = trim($value);
            }
        } else {
            $modeArray[] = "build";
        }

        foreach ($modeArray as $value) {
            switch ($value) {
            case 'check':
                if (!$this->token->check($this->request)) {
                    $this->request->setFilterError('Token');
                    $this->request->addErrorMessage('不正なアクセスです。');
                    $this->log->warn('Tokenが不正です。');
                }
                break;
            case 'remove':
                $this->token->remove();
                break;
            case 'build':
            default:
                $this->token->build();
                break;
            }
        }
        return;
    }
    
    public function postfilter() {}
}
?>
