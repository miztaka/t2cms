<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * valueのlabelを表示するSmarty plugin
 *
 * Type:     modifier<br>
 * Name:     label<br>
 * Date:     Mar 3, 2007
 * Purpose:  valueのlabelを表示するSmarty plugin
 * Example:  {$foo|label:$fooLabel}
 * 
 * @version  1.0
 * @author   Mitsutaka Sato
 * @param string 
 * @return string
 */
function smarty_modifier_label($string, &$ar)
{
    return @$ar[$string];
}

/* vim: set expandtab: */

?>
