<?php

/**
 * 汎用BEANクラスです。
 * @author miztaka
 * @package teeple
 *
 */
class Teeple_Bean
{
    /**
     * フィールド名称を保持
     * @var array
     */
    protected $fields = array();
    
    public function __construct($fields)
    {
        $this->fields = $fields;
    }
    
    /**
     * オブジェクトのプロパティをこのインスタンスにコピーします。
     *
     * @param object $obj オブジェクト
     * @param array $colmap 'BEANのカラム名' => 'オブジェクトのプロパティ名' の配列
     */
    public function copyFrom($obj, $colmap=null) {
        
        if ($colmap == null) {
            $colmap = array();
        }
        
        $isObj = is_object($obj); 
        
        $columns = $this->fields;
        foreach($columns as $column) {
            $prop = array_key_exists($column, $colmap) ? $colmap[$column] : $column;
            if (isset($obj->$prop)) {
                $this->$column = $isObj ? $obj->$prop : $obj[$prop];
            }
        }
        return;
    }
    
    /**
     * BEANのプロパティからオブジェクトのプロパティを生成します。
     *
     * @param object $obj 
     * @param array $colmap 'BEANのカラム名' => 'オブジェクトのプロパティ名' の配列
     */
    public function copyTo($obj, $colmap=null) {

        if ($colmap == null) {
            $colmap = array();
        }
        
        $isObj = is_object($obj);
        
        $columns = $this->fields;
        foreach($columns as $column) {
            if (@isset($this->$column)) {
                $prop = array_key_exists($column, $colmap) ? $colmap[$column] : $column;
                if ($isObj) {
                    $obj->$prop = $this->$column;
                } else {
                    $obj[$prop] = $this->$column;
                }
            }
        }
        return;
    }

    /**
     * 定義されていないプロパティが呼ばれたとき
     * @param $name
     * @return unknown_type
     */
    public function __get($name) {
        if (in_array($name, $this->fields)) {
            return NULL;
        }
    }
    
}