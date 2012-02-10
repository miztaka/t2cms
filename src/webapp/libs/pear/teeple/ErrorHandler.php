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
 * エラーハンドラークラスです。
 * 
 * @package teeple
 */
class Teeple_ErrorHandler
{
    /**
     * エラーハンドリングを行ないます。
     * 
     * @param Exception $e 例外クラス
     */
    public static function handle($e) {

        // ロギング
        self::handleLogging($e);

        // エラーページを出力
        $display = $e->getMessage();
        $template = 'common/exception.html';
        
        $renderer = Teeple_Smarty4Maple::getInstance();
        $renderer->assign('display', $display);
        $renderer->assign('stack', $e->__toString());
        $result = $renderer->fetch($template);
        if ($result == "") {
            self::print_error($display);
        }
        print $result;
        
        return;
    }
    
    /**
     * ちょっと危険なので使わない。
     *
     * @param unknown_type $errno
     * @param unknown_type $errmsg
     * @param unknown_type $filename
     * @param unknown_type $linenum
     * @param unknown_type $vars
     */
    /*
    public static function handlePHPError($errno, $errmsg, $filename, $linenum, $vars) {

        // エラー番号 => 文字列
        $errtype = array(
            E_ERROR => 'E_ERROR',
            E_PARSE => 'E_PARSE',
            E_CORE_ERROR => 'E_CORE_ERROR',
            E_CORE_WARNING => 'E_CORE_WARNING',  
            E_COMPILE_ERROR => 'E_COMPILE_ERROR',
            E_USER_ERROR => 'E_USER_ERROR',
            E_USER_WARNING => 'E_USER_WARNING',
            E_STRICT => 'E_STRICT',
            E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR', 
            E_WARNING => 'E_WARNING',
            E_NOTICE => 'E_NOTICE',
            E_COMPILE_WARNING => 'E_COMPILE_WARNING',
            E_USER_NOTICE => 'E_USER_NOTICE'
        );        
        
        // ロギング (エラー種別にあわせてレベルを代える。)
        $level = NULL;
        switch ($errno) {
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:                
            case E_CORE_WARNING:  
            case E_COMPILE_ERROR:
            case E_USER_ERROR:
            case E_USER_WARNING:
            case E_RECOVERABLE_ERROR:
                $level = 'fatal';
                break;
            case E_WARNING:
            case E_NOTICE:
            case E_COMPILE_WARNING:
            case E_USER_NOTICE:
                $level = 'warn';
                break;
            case E_STRICT:
            default:
                break;
        }
        
        if ($level != NULL) {
            $log =& LoggerManager::getLogger(get_class($this));
            $log->$level("[PHPError]errno={$errtype[$errno]},errmsg={$errmsg},filename={$filename},linenum={$linenum},vars=". var_export($vars, TRUE));

            // fatalの場合はエラー画面表示する
            if ($level == 'fatal') {
                self::handle(new Teeple_Exception(MC::getLog(MSG_MA999), MSG_MA999));
            }
        }        
        
    }
    */
    
    /**
     * 例外のロギングを行ないます。
     *
     * @param Exception $e 例外クラス。
     * 
     */
    public static function handleLogging($e) {
        
        $log = LoggerManager::getLogger('ErrorHandler');
        
        // メッセージとStackTraceをerrorでロギング
        $log->fatal($e->getMessage());
        $log->fatal("*** stack trace ***\n". $e->__toString());
        $log->fatal("*** request dump ***\n". var_export(@$_REQUEST, TRUE));
        $log->fatal("*** session dump ***\n". var_export(@$_SESSION, TRUE));
    }
    
    private static function print_error($display) {
?><HTML>
<BODY>
<h3>エラーが発生しました。</h3>
<p><?php echo $display ?></p>
</BODY>
<?php

        exit;
    }
    
}

?>