<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Stateの値を見てclass属性にdelを追加
 *
 * Type:     modifier<br>
 * Name:     delclass<br>
 * Date:     Mar 3, 2007
 * Purpose:  Stateの値を見てclass属性にdelを追加
 * Example:  {$state|delclass}
 * 
 * @version  1.0
 * @author   Mitsutaka Sato
 * @param string 
 * @return string
 */
function smarty_modifier_delclass($state)
{
    return $state == '1' ? NULL : 'del';
}

/* vim: set expandtab: */

?>
