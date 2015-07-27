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

include_once dirname(__FILE__) .'/teeple.inc.php';

//
// Action自動生成機能のON/OFF
//
define('USE_DEVHELPER', false);

//
//Smartyテンプレートの設定
//
Teeple_Smarty4Maple::setOptions(array(
    "caching"           => false,
    //"cache_lifetime"    => 3600,
    "cache_lifetime"    => 5,
    "compile_check"     => false,
    "force_compile"     => true
    //"default_modifiers" => array("escape:html")
));

//
// DataSourceの設定
//
define('DEFAULT_DATASOURCE','teeple2cms');
Teeple_DataSource::setDataSource(array(
    'teeple2cms' => array(
        'dsn' => 'mysql:host=127.0.0.1;dbname=teeple2cms;charset=utf8',
        'user' => 'teeple2cms',
        'pass' => 'teeple2cms'
    )
));

//
// DB insert,update時のコールバック
//
function teeple_activerecord_before_insert($obj) {
    if (method_exists($obj, 'beforeInsertHook')) {
        $obj->beforeInsertHook();
    }
    $container = Teeple_Container::getInstance();
    $loginaccount = $container->getSessionComponent("LoginAccountAdmin");
    if (is_object($loginaccount) && $loginaccount->isAuthed()) {
        if (property_exists($obj, 'created_by')) {
            $obj->created_by = $loginaccount->info->id;
        }
        if (property_exists($obj, 'modified_by')) {
            $obj->modified_by = $loginaccount->info->id;
        }
    }
    if (property_exists($obj, 'create_time')) {
        $obj->create_time = $obj->now();
    }
    if (property_exists($obj, 'timestamp')) {
        $obj->timestamp = $obj->now();
    }
    return;
}

function teeple_activerecord_before_update($obj) {
    if (method_exists($obj, 'beforeUpdateHook')) {
        $obj->beforeUpdateHook();
    }
    $container = Teeple_Container::getInstance();
    $loginaccount = $container->getSessionComponent("LoginAccountAdmin");
    
    // modified_by
    if (is_object($loginaccount) && $loginaccount->isAuthed()) {
        if (property_exists($obj, 'modified_by')) {
            $obj->modified_by = $loginaccount->info->id;
        }
    }
    // timestamp
    if (property_exists($obj, 'timestamp')) {
        $obj->timestamp = $obj->now();
    }
    // version
    if (property_exists($obj, 'version')) {
        // 楽観的排他制御
        $obj->setConstraint('version', $obj->version);
        $obj->version += 1;
    }
    
    return;
}

function teeple_activerecord_before_updateAll($obj) {
    $container = Teeple_Container::getInstance();
    $loginaccount = $container->getSessionComponent("LoginAccountAdmin");
    if (is_object($loginaccount) && $loginaccount->isAuthed()) {
        if (property_exists($obj, 'modified_by')) {
            $obj->modified_by = $loginaccount->info->id;
        }
    }
    if (property_exists($obj, 'timestamp')) {
        $obj->timestamp = $obj->now();
    }
    
    return;
}


//
// Componentの差し替え
//
Teeple_Container::$namingDefs = array(
    'Teeple_ControllerHook' => 'Teeple_Cms_ControllerHook'
);

?>