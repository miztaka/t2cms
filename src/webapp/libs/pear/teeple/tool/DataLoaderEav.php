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
 * @package     teeple.tool
 * @author      Mitsutaka Sato <miztaka@gmail.com>
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 */

require_once 'PHPExcel/IOFactory.php';

/**
 * Excelからデータベースにデータを読み込みます。
 *
 * @package teeple.tool
 */
class Teeple_Tool_DataLoaderEav extends Teeple_Tool_DataLoader {
    
    //================ hook ==================//
    
    /**
     * 指定された名前のEntityが存在するかどうか
     * @param string $name
     */
    protected function checkEntityExists($name) {
        
        if (Entity_MetaEntity::get()->eq('pname', $name)->count() == 0) {
            return false;
        }
        return true;
    }
    
    /**
     * 指定された名前のEntityを作成します。
     * 
     * @param string $name
     */
    protected function getEntity($name) {
        $entity = new Teeple_EavRecord($name);
        return $entity;
    }
    
    /**
     * データをエンティティにセットします。
     * @param Teeple_EavRecord $entity
     * @param string $prop
     * @param string $value
     */
    protected function setValue2Entity($entity, $prop, $value) {
        
        mb_regex_encoding('UTF-8');
        $data_type = $entity->getAttributeByPname($prop)->data_type;
        if ($data_type == Entity_MetaAttribute::DATA_TYPE_CHECK ||
            $data_type == Entity_MetaAttribute::DATA_TYPE_MULTISELECT) {
            if (! Teeple_Util::isBlank($value)) {
                $ar = mb_split("，", $value);
                $value = serialize($ar);
            }
        }
        $entity->$prop = $value;
    }
    
}

?>