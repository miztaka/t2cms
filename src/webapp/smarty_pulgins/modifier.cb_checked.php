<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * checkboxでchecked="checked" 表示
 *
 * @version  1.0
 * @author   Mitsutaka Sato
 * @param string 
 * @return string
 */
function smarty_modifier_cb_checked($selected, $value)
{
    if (is_array($selected) && in_array($value, $selected)) {
        return 'checked="checked"';
    }
    return "";
}

/* vim: set expandtab: */

?>