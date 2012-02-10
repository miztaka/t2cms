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
 * Utilクラスです。
 *
 * @package teeple
 */
class Teeple_Util {
    
    /**
     * iniファイルを読込みます。
     *
     * @param string $configfile iniファイルのパス
     * @return array
     */
    public static function readIniFile($configfile) {
        
        if(! file_exists($configfile)) {
            $this->log->info("Filterの設定ファイルが存在しません。($configfile)");
            return NULL;
        }
        
        $config = parse_ini_file($configfile, true);
        if (! is_array($config)) {
            $this->log->error("Filterの設定ファイルに誤りがあります。($configfile)");
            return NULL;
        }
        if (CONFIG_CODE != INTERNAL_CODE) {
            mb_convert_variables(INTERNAL_CODE, CONFIG_CODE, $config);
        }
        
        return $config;
    }
    
    /**
     * ハイフン区切りでCapitalizeされたクラス名を取得します。
     *
     * @param string $name クラス名
     * @return string
     */
    public static function capitalizedClassName($name) {
        
        $pathList = explode("_", $name);
        $ucPathList = array_map('ucfirst', $pathList);
        return join("_", $ucPathList);
    }

    /**
     * 値が空白かどうかをチェックします。
     *
     * @param mixed $value
     * @param boolean $trim
     * @return boolean
     */
    public static function isBlank($value, $trim = TRUE) {
        
        if (is_array($value)) {
            return (count($value) == 0);
        }
        if ($trim) {
            $value = trim($value);
        }
        return ($value === NULL || $value === "");
    }
    
    /**
     * エラーメッセージにパラメータを埋め込んで返します。
     *
     * @param string $msg
     * @param array $param
     * @return string
     */
    public static function formatErrorMessage($msg, &$param) {
        
        foreach($param as $i => $arg) {
            $msg = str_replace("{".$i."}", $arg, $msg);
        }
        return $msg;
    }
    
    /**
     * クラスファイルをincludeします。
     *
     * @param string $name
     * @return boolean
     */
    public static function includeClassFile($name) {
        
        $pathList = explode('_', $name);
        $path = "";
        for($i=0; $i<count($pathList); $i++) {
            if ($i != count($pathList) - 1) {
                $path .= strtolower($pathList[$i]);
                $path .= '/';
            } else {
                $path .= $pathList[$i];
            }
        }
        $path .= ".php";
        $result = @include_once $path;
        if ($result !== FALSE) {
            return TRUE;
        }
        
        $path = implode('/', $pathList) .".php";
        $result = @include_once $path;
        
        return $result === FALSE ? FALSE : TRUE;
    }
    
    /**
     * オブジェクトまたは配列から指定された名前のプロパティを取り出します。
     *
     * @param mixed $obj
     * @param string $fieldName
     * @return mixed
     */
    public static function getProperty($obj, $fieldName) {
        
        if (is_object($obj)) {
            return $obj->$fieldName;
        }
        if (is_array($obj)) {
            return isset($obj[$fieldName]) ? $obj[$fieldName] : NULL;
        }
        return $obj;
    }
    
    /**
     * オブジェクトまたは配列に指定された名前のプロパティをセットします。
     *
     * @param mixed $obj
     * @param string $fieldName
     * @param mixed $value
     */
    public static function setProperty(&$obj, $fieldName, $value) {
        
        if (is_object($obj)) {
            $obj->$fieldName = $value;
        }
        if (is_array($obj)) {
            $obj[$fieldName] = $value;
        }
        return;
    }
    
    /**
     * オブジェクトまたは配列にセットされているプロパティの名前をすべて取得します。
     *
     * @param mixed $obj
     * @return array
     */
    public static function getPropertyNames($obj) {
        
        if (is_object($obj)) {
            return array_keys(get_object_vars($obj));
        }
        if (is_array($obj)) {
            return array_keys($obj);
        }
        return array();
    }
    
    /**
     * アクション名から絶対URLを取得します。
     * @param string $actionName
     * @param bool $isHttps
     * @return string
     */
    public static function getAbsoluteUrlFromActionName($actionName, $isHttps) {
        
        $url = self::getBaseUrl($isHttps);
        $uri = str_replace('_','/',$actionName);
        return $url ."/{$uri}.html";
    }
    
    /**
     * アプリケーションの基底URLを http://から取得します。
     * @param bool $isHttps
     */
    public static function getBaseUrl($isHttps) {
        
        $base = self::getBasePath();
        $url = $isHttps ? 'https://' : 'http://';
        $url .= str_replace(":443", "", $_SERVER["HTTP_HOST"]);
        $url .= $base;
        return $url;
    }
    
    /**
     * アプリケーションの基底URIを取得します。
     */
    public static function getBasePath() {
        $base = str_replace("/teeple_controller.php", "", Teeple_Util::getScriptName());
        return $base;
    }
    
    /**
     * PATH_INFOを取得します。
     */
    public static function getPathInfo() {
        foreach (array('ORIG_PATH_INFO','PATH_INFO') as $key) {
            if (isset($_SERVER[$key]) && strlen($_SERVER[$key])) {
                return $_SERVER[$key];
            }
        }
        // REQUEST_URIからBASE_PATHを引いて生成
        if (isset($_SERVER['REQUEST_URI'])) {
            $request_uri = $_SERVER['REQUEST_URI']; 
            $base = self::getBasePath();
            $len = strlen($base);
            if ($len == 0) {
                return $request_uri;
            }
            if (strpos($request_uri, $base) === 0) {
                return substr($request_uri, $len);
            }
        }
        throw new Exception("PATH_INFOが取得できません。");
    }
    
    /**
     * SCRIPT_NAMEを取得します。
     */
    public static function getScriptName() {
        foreach (array('ORIG_SCRIPT_NAME','SCRIPT_NAME') as $key) {
            if (isset($_SERVER[$key]) && strlen($_SERVER[$key])) {
                return $_SERVER[$key];
            }
        }
        throw new Exception("SCRIPT_NAMEが取得できません。");
    }
    
    /**
     * PATHからAction名を取得します。
     * @param $path
     * @return string
     */
    public static function path2Action($path) {
        
        if ($path == NULL || strlen($path) == 0 || $path == '/') {
            return 'index';
        }
        if ($path{strlen($path)-1} == '/') {
            $path .= "index.html";
        }
        $path = preg_replace('/^\/?(.*)$/', '$1', $path);
        $path = preg_replace('/(\..*)?$/', '', $path);
        $path = str_replace('/','_',$path);
        
        return $path;        
    }
    
    /**
     * 文字列が指定された文字列で始まっているか
     * @param $haystack
     * @param $needle
     */
    public static function startsWith($haystack, $needle) {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }
    
    /**
     * 文字列が指定された文字列で終わっているか
     * @param $haystack
     * @param $needle
     */
    public static function endsWith($haystack, $needle) {
        $length = strlen($needle);
        $start  = $length * -1; //negative
        return (substr($haystack, $start) === $needle);
    }    

}

?>