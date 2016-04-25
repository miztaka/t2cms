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

require_once SMARTY_DIR ."Smarty.class.php";

/**
 * Smartyクラスを拡張して使用する
 *
 * @package     teeple
 */
class Teeple_Smarty4Maple extends Smarty
{
    /**
     * コンストラクター
     * Smarty4MapleクラスはSingletonとして使うので直接newしてはいけない
     *
     */
    protected function __construct()
    {
        $this->Smarty();

        $constants = array(
            'VIEW_TEMPLATE_DIR'     => 'template_dir',
            'VIEW_COMPILE_DIR'      => 'compile_dir',
            'VIEW_CONFIG_DIR'       => 'config_dir',
            'VIEW_CACHE_DIR'        => 'cache_dir',
            
            'SMARTY_TEMPLATE_DIR'   => 'template_dir',
            'SMARTY_COMPILE_DIR'    => 'compile_dir',
            'SMARTY_CONFIG_DIR'     => 'config_dir',
            'SMARTY_CACHE_DIR'      => 'cache_dir',
            
            'SMARTY_CACHING'        => 'caching',
            'SMARTY_CACHE_LIFETIME' => 'cache_lifetime',
            'SMARTY_COMPILE_CHECK'  => 'compile_check',
            'SMARTY_FORCE_COMPILE'  => 'force_compile',
        
            'SMARTY_LEFT_DELIMITER' => 'left_delimiter',
            'SMARTY_RIGHT_DELIMITER' => 'right_delimiter'
            );
        
        foreach($constants as $constName => $attr) {
            if(defined($constName)) {
                $this->$attr = constant($constName);
            }
        }
        
        if(defined('SMARTY_DEFAULT_MODIFIERS')) {
            $this->default_modifiers = array(SMARTY_DEFAULT_MODIFIERS);
        }
        
        if (defined('SMARTY_PULGINS_DIR')) {
            array_push($this->plugins_dir, SMARTY_PULGINS_DIR);
        }

        //$this->_registerFilters(); Encodingをあとで変更できるようにするためここでは呼ばない。
    }

    /**
     * Smarty4Mapleクラスの唯一のインスタンスを返却
     *
     * @return  Teeple_Smarty4Maple 
     */
    static public function getInstance() {

        static $instance;
        if ($instance === NULL) {
            $instance = new Teeple_Smarty4Maple();
        }
        return $instance;
    }
    
    public function newInstance() {
        $instance = new Teeple_Smarty4Maple();
        return $instance;
    }

    /**
     * 唯一のインスタンスに対して設定を行う
     * 
     * @static
     * @param  array    $opts
     */
    public static function setOptions($opts) {

        $instance = Teeple_Smarty4Maple::getInstance();

        foreach($opts as $attr => $value) {
            if(array_key_exists($attr, $instance)) {
                $instance->$attr = $value;
            }
        }
    }

    /**
     * filterを登録する
     */
    public function registerFilters() {
        
        if ($this->filterRegistered) {
            return;
        }
        $request = Teeple_Request::instance(); 
        if ($request->getEncoding('TEMPLATE_CODE') != INTERNAL_CODE) {
            // プリフィルタを登録
            $this->register_prefilter('smarty4maple_prefilter');
        }
        if ($request->getEncoding('OUTPUT_CODE') != INTERNAL_CODE) {
            // アウトプットフィルタを登録
            $this->register_outputfilter('smarty4maple_outputfilter');
        }
        $this->filterRegistered = true;
        return;
    }
    protected $filterRegistered = false;

    /**
     * コンパイルディレクトリの中身を全て破棄する
     *
     */
    public function clearTemplates_c() {

        $result = $this->clear_compiled_tpl();

        if ($result) {
            echo "Clear";
        } else {
            echo "NG";
        }

        return true;
    }

    /**
     * テンプレートのキャッシュを破棄する
     *
     * @param   string  $tpl    テンプレート名
     */
    function clearCache($tpl = "") {

        $result = $this->clear_cache($tpl);

        if ($result) {
            echo "Cache Clear";
        } else {
            echo "NG";
        }

        return true;
    }

    /**
     * Actionをセットする
     *
     * @param   Teeple_ActionBase  $action Actionのインスタンス
     */
    public function setAction($action) {
		$this->assign('a', $action);
    }

    /**
     * Tokenをセットする
     *
     * @param   Teeple_Token $token  Tokenのインスタンス
     */
    public function setToken($token) {

        $this->register_object("token", $token);
        $this->assign('token', array(
            'name'  => $token->getName(),
            'value' => $token->getValue(),
        ));
    }

    /**
     * Sessionをセットする
     *
     * @param   Teeple_Session  $session    Sessionのインスタンス
     */
    public function setSession($session) {
        $this->assign('s', $session);
    }

    /**
     * Requestをセットする
     *
     * @param   Teeple_Request  $request    Requestのインスタンス
     */
    public function setRequest($request) {
        $this->assign('r', $request);
    }
    
    /**
     * ScriptNameをセットする
     *
     * @param   string  $scriptName ScriptName
     */
    function setScriptName($scriptName) {
        $scriptName = htmlspecialchars($scriptName, ENT_QUOTES);
        $this->assign('scriptName', $scriptName);
    }
}

/**
 * プリフィルタ
 */
function smarty4maple_prefilter($source, &$Smarty)
{
    return mb_convert_encoding($source, INTERNAL_CODE, Teeple_Request::instance()->getEncoding('TEMPLATE_CODE'));
}

/**
 * ポストフィルタ
 */
function smarty4maple_postfilter($source, &$Smarty)
{
    return mb_convert_encoding($source, Teeple_Request::instance()->getEncoding('OUTPUT_CODE'), INTERNAL_CODE);
}

/**
 * アウトプットフィルタ
 */
function smarty4maple_outputfilter($source, &$Smarty)
{
    return mb_convert_encoding($source, Teeple_Request::instance()->getEncoding('OUTPUT_CODE'), INTERNAL_CODE);
}
?>