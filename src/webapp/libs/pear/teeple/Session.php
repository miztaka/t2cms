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
 * Session管理を行う
 *
 * @package     teeple
 */
class Teeple_Session
{
    /**
     * @return Teeple_Session
     */
    public static function instance() {
        return Teeple_Container::getInstance()->getComponent(__CLASS__);
    }

    /**
     * 設定されている値を返却
     *
     * @param   string  $key    パラメータ名
     * @return  string  パラメータの値
     */
    public function getParameter($key) {
        return isset($_SESSION[$key]) ?
            $_SESSION[$key] : NULL;
    }

    /**
     * 値をセット
     *
     * @param   string  $key    パラメータ名
     * @param   string  $value  パラメータの値
     */
    public function setParameter($key, $value) {
        $_SESSION[$key] = $value;
    }

    /**
     * 値をセット(オブジェクトをセット)
     *
     * @param   string  $key    パラメータ名
     * @param   Object  $value  パラメータの値
     */
    public function setParameterRef($key, &$value) {
        $_SESSION[$key] =& $value;
    }

    /**
     * 値を返却(配列で返却)
     *
     * @param   string  $key    パラメータ名
     * @return  string  パラメータの値(配列)
     */
    public function getParameters() {
        return isset($_SESSION) ? $_SESSION : NULL;
    }

    /**
     * 値を削除する
     *
     * @param   string  $key    パラメータ名
     */
    public function removeParameter($key) {
        unset($_SESSION[$key]);
    }

    /**
     * セッション処理を開始
     * TODO regenerateするとブラウザバックとかしたときにおかしくなるのでコメントアウト
     */
    public function start() {
        
        if (! Net_UserAgent_Mobile::singleton()->isNonMobile()) {
            @ini_set('session.use_trans_sid','1');
        }
        @session_start();
        //@session_regenerate_id(true);
        /*
        if (! isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST']) === FALSE) {
            @session_regenerate_id(true);
        }
        */        
    }

    /**
     * セッション処理を終了
     *
     */
    public function close() {
        $_SESSION = array();
        session_destroy();
    }

    /**
     * セッション名を返却
     *
     * @return  string  セッション名
     */
    public function getName() {
        return session_name();
    }

    /**
     * セッション名をセット
     *
     * @param   string  $name   セッション名
     */
    public function setName($name = '') {
        if ($name) {
            session_name($name);
        }
    }

    /**
     * セッションIDを返却
     *
     * @return  string  セッションID
     */
    public function getID() {
        return session_id();
    }

    /**
     * セッションIDをセット
     *
     * @param   string  $id セッションID
     */
    public function setID($id = '') {
        if ($id) {
            session_id($id);
        }
    }

    /**
     * save_pathをセット
     *
     * @param   string  $savePath   save_path
     */
    public function setSavePath($savePath) {
        if (!isset($savePath)) {
            return;
        }
        session_save_path($savePath);
    }

    /**
     * cache_limiterをセット
     *
     * @param   string  $cacheLimiter   cache_limiter
     */
    public function setCacheLimiter($cacheLimiter) {
        if (!isset($cacheLimiter)) {
            return;
        }
        session_cache_limiter($cacheLimiter);
    }

    /**
     * cache_expireをセット
     *
     * @param   string  $cacheExpire    cache_expire
     */
    public function setCacheExpire($cacheExpire) {
        if (!isset($cacheExpire)) {
            return;
        }
        session_cache_expire($cacheExpire);
    }

    /**
     * use_cookies をセット
     *
     * @param   string  $useCookies use_cookies 
     */
    public function setUseCookies($useCookies) {
        if (!isset($useCookies)) {
            return;
        }
        ini_set('session.use_cookies', $useCookies ? 1 : 0);
    }

    /**
     * cookie_lifetime をセット
     *
     * @param   string  $cookieLifetime cookie_lifetime
     */
    public function setCookieLifetime($cookieLifetime) {
        
        if (!isset($cookieLifetime)) {
            return;
        }
        $cookie_params = session_get_cookie_params();
        session_set_cookie_params($cookieLifetime, $cookie_params['path'], $cookie_params['domain'], $cookie_params['secure']);
    }

    /**
     * cookie_path をセット
     *
     * @param   string  $cookiePath cookie_path
     */
    public function setCookiePath($cookiePath) {

        if (!isset($cookiePath)) {
            return;
        }
        $cookie_params = session_get_cookie_params();
        session_set_cookie_params($cookie_params['lifetime'], $cookiePath, $cookie_params['domain'], $cookie_params['secure']);
    }

    /**
     * cookie_domain をセット
     *
     * @param   string  $cookieDomain   cookie_domain
     */
    public function setCookieDomain($cookieDomain) {

        if (!isset($cookieDomain)) {
            return;
        }
        $cookie_params = session_get_cookie_params();
        session_set_cookie_params($cookie_params['lifetime'], $cookie_params['path'], $cookieDomain, $cookie_params['secure']);
    }

    /**
     * cookie_secure をセット(SSL利用時などにsecure属性を設定する)
     *
     * @param   string  $cookieSecure   cookie_secure
     */
    public function setCookieSecure($cookieSecure) {

        if (!isset($cookieSecure)) {
            return;
        }
        if (preg_match('/^true$/i', $cookieSecure) ||
            preg_match('/^secure$/i', $cookieSecure) ||
            preg_match('/^on$/i', $cookieSecure) ||
            ($cookieSecure === '1') || ($cookieSecure === 1)) {
            $cookieSecure = true;
        } else {
            $cookieSecure = false;
        }

        $cookie_params = session_get_cookie_params();
        session_set_cookie_params($cookie_params['lifetime'], $cookie_params['path'], $cookie_params['domain'], $cookieSecure);
    }
}
?>
