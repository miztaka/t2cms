<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Smarty {include_page} plugin
 *
 * Type:     function<br>
 * Name:     include_page<br>
 * Purpose:  CMSで生成される動的ページを読み込みます。
 * 
 * <pre>
 * 使い方：
 * {include_page page='foo/bar.html'}
 * 
 *  page: 読み込むページのURL(PATH_INFO)
 *
 * @param array $params 上記パラメータの配列
 * @param Smarty &$smarty Smartyオブジェクト
 * @return string 表示するHTML
 */
function smarty_function_include_page($params, &$smarty)
{
    //require_once $smarty->_get_plugin_filepath('shared','escape_special_chars');
    //require_once $smarty->_get_plugin_filepath('shared','make_timestamp');
    //require_once $smarty->_get_plugin_filepath('function','html_options');

    // デフォルト値
    $path_info = null;

    // パラメータのセット
    foreach ($params as $_key=>$_value) {
        switch ($_key) {
            case 'page':
                $path_info = $_value;
                break;
        }
    }
    if ($path_info == null) {
        return "";
    }
    
    $renderer = new Teeple_Cms_IncludePageRenderer($path_info);
    return $renderer->render();
}

?>