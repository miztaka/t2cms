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
 * セッション処理を行うFilter
 *
 * @package     teeple.filter
 */
class Teeple_Filter_Session extends Teeple_Filter
{
    
    /**
     * @var array
     */
    private $modeArray;
    
    /**
     * コンストラクタ
     *
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * セッション処理を行う
     *
     */
    public function prefilter() {

        $attributes = $this->getAttributes();
        $this->modeArray = array();

        if (isset($attributes["mode"])) {
            $this->modeArray = explode(",", $attributes["mode"]);
            foreach ($this->modeArray as $key => $value) {
                $this->modeArray[$key] = trim($value);
            }
        } else {
            $this->modeArray[] = "start";
        }

        if (isset($attributes["name"])) {
            $this->session->setName($attributes["name"]);
        }
        if (isset($attributes["id"])) {
            $this->session->setID($attributes["id"]);
        }
        if (isset($attributes["savePath"])) {
            $this->session->setSavePath($attributes["savePath"]);
        }
        if (isset($attributes["cacheLimiter"])) {
            $this->session->setCacheLimiter($attributes["cacheLimiter"]);
        }
        if (isset($attributes["cacheExpire"])) {
            $this->session->setCacheExpire($attributes["cacheExpire"]);
        }
        if (isset($attributes["useCookies"])) {
            $this->session->setUseCookies($attributes["useCookies"]);
        }
        if (isset($attributes["lifetime"])) {
            $this->session->setCookieLifetime($attributes["lifetime"]);
        }
        if (isset($attributes["path"])) {
            $this->session->setCookiePath($attributes["path"]);
        }
        if (isset($attributes["domain"])) {
            $this->session->setCookieDomain($attributes["domain"]);
        }
        if (isset($attributes["secure"])) {
            $this->session->setCookieSecure($attributes["secure"]);
        }

        if (in_array('start', $this->modeArray)) {
            $this->session->start();
        }
        
        return;
    }
    
    /**
     * セッションのクローズを行います。
     *
     */
    public function postfilter() {
        if (in_array('close', $this->modeArray)) {
            $this->session->close();
        }
        return;
    }
}
?>
