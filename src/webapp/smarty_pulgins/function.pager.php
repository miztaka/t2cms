<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Smarty {pager} plugin
 *
 * Type:     function<br>
 * Name:     pager<br>
 * Purpose:  Prints <a> tag for page-navigation link
 * 
 * <pre>
 * 使い方：
 * {pager total=100 offset=0 limit=10 href='hoge.php?offset=$1$&limit=$2$'}
 * 
 *  total: 全レコード数
 *  offset: 表示開始レコード(0スタート)
 *  limit: 最大表示件数
 *  href: linkのURL $1$=>offsetに置換、$2$=>limitに置換
 *
 * @param array $params 上記パラメータの配列
 * @param Smarty &$smarty Smartyオブジェクト
 * @return string 表示するHTML
 */
function smarty_function_pager($params, &$smarty)
{
    //require_once $smarty->_get_plugin_filepath('shared','escape_special_chars');
    //require_once $smarty->_get_plugin_filepath('shared','make_timestamp');
    //require_once $smarty->_get_plugin_filepath('function','html_options');

    // デフォルト値
    $total = 0;
    $offset = 0;
    $limit = 10;
    $href = '';

    // パラメータのセット
    foreach ($params as $_key=>$_value) {
        switch ($_key) {
            case 'total':
            case 'offset':
            case 'limit':
            case 'href':
                $$_key = $_value;
                break;
        }
    }
    
    // 前処理
    $html_result = "<div><ul>\n";
    
    // 前ページがある場合は、表示
    $need_sep = FALSE;
    if ($total > 0 && $offset > 0) {
        $of = ($offset - $limit) > 0 ? ($offset - $limit) : 0;
        $fr = $of+1;
        $to = ($of + $limit) < $total ? ($of + $limit) : $total;
        $hr = str_replace(array('$1$','$2$'), array("{$of}", "{$limit}"), $href);
        $html_result .= "<li><a href=\"{$hr}\">前へ</a> << {$fr}-{$to}</li>\n";
        $need_sep = TRUE;
    }
    
    // 次ページがある場合は、表示
    if ($total > $offset + $limit) {
        $of = $offset + $limit;
        $fr = $of + 1;
        $to = ($of + $limit) < $total ? ($of + $limit) : $total;        
        $hr = str_replace(array('$1$','$2$'), array("{$of}", "{$limit}"), $href);
        if ($need_sep) {
            $html_result .= "<li>|</li>\n";
        }
        $html_result .= "<li>{$fr}-{$to} >> <a href=\"{$hr}\">次へ</a></li>\n";
    }
    
    // 後処理
    $html_result .= "</ul></div>\n";

    return $html_result;
}

?>