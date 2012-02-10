<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {html_hidden} function plugin
 *
 * Type:     function<br>
 * Name:     html_hidden<br>
 * Input:<br>
 *           - values     (required) - array
 * 
 * Purpose:  Prints the list of <hidden> tags for array parameter values
 * 
 * @author Mitsutaka Sato
 * @param array
 * @param Smarty
 * @return string
 * @uses smarty_function_escape_special_chars()
 */
function smarty_function_html_hidden($params, &$smarty)
{
    require_once $smarty->_get_plugin_filepath('shared','escape_special_chars');
    
    $values = null;
    
    foreach($params as $_key => $_val) {
        switch($_key) {
            case 'values':
                $$_key = $_val;
                break;
        }
    }

    if (! is_array($values)) {
        return '';
    }

    $_html_result = '';

    foreach ($values as $_key => $_val) {
        if (is_array($_val)) {
            foreach ($_val as $i => $v) {
                $_html_result .= 
                    '<input type="hidden" name="'. 
                    smarty_function_escape_special_chars($_key.'['.$i.']') 
                    .'" value="'.
                    smarty_function_escape_special_chars($v)
                    .'" />' ."\n";
            }
        } else {
            $_html_result .= 
                '<input type="hidden" name="'. 
                smarty_function_escape_special_chars($_key)
                .'" value="'.
                smarty_function_escape_special_chars($_val)
                .'" />' ."\n";
        }
    }
    
    return $_html_result;
    
}
    
?>