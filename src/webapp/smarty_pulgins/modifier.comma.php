<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * 数字をカンマで区切って表示する Smarty plugin
 *
 * Type:     modifier<br>
 * Name:     comma<br>
 * Date:     Mar 3, 2007
 * Purpose:  数字をカンマで区切って表示する
 * Example:  {$price|comma}
 * 
 * @version  1.0
 * @author   Mitsutaka Sato
 * @param string 
 * @return string
 */
function smarty_modifier_comma($string)
{
    return number_format($string);
}

/* vim: set expandtab: */

?>
