<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Date型で0000-00-00が帰ってくる対処
 *
 * Type:     modifier<br>
 * Name:     datepatch<br>
 * Date:     Mar 3, 2007
 * Purpose:  Date型で0000-00-00はNULLにする。
 * Example:  {$foo|datepatch}
 * 
 * @version  1.0
 * @author   Mitsutaka Sato
 * @param string 
 * @return string
 */
function smarty_modifier_datepatch($string)
{
    return $string === "0000-00-00" ? NULL : $string;
}

/* vim: set expandtab: */

?>
