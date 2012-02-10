<?php 

class Entity_Base_MetaValue extends Teeple_ActiveRecord
{

    /**
     * 使用するデータソース名を指定します。
     * 指定が無い場合は、DEFAULT_DATASOURCE で設定されているDataSource名が使用されます。
     *
     * @var string
     */
    public static $_DATASOURCE = 'teeple2cms';
    
    /**
     * このエンティティのテーブル名を設定します。
     * 
     * <pre>
     * スキーマを設定する場合は、"スキーマ.テーブル名"とします。
     * 子クラスにて必ずセットする必要があります。
     * </pre>
     *
     * @var string
     */
    public static $_TABLENAME = 'meta_value';
    
    /**
     * プライマリキー列を設定します。
     * 
     * <pre>
     * プライマリキーとなるカラム名を配列で指定します。
     * 子クラスにて必ずセットする必要があります。
     * </pre>
     * 
     * @var array 
     */
    public static $_PK = array(
        'id'
    );
    
    /**
     * このテーブルのカラム名をpublicプロパティとして設定します。(プライマリキーを除く)
     * <pre>
     * 子クラスにて必ずセットする必要があります。
     * </pre>
     */
    public $id;
    public $meta_record_id;
    public $meta_attribute_id;
    public $value;
    public $create_time;
    public $timestamp;
    public $created_by;
    public $modified_by;
    public $delete_flg;
    public $delete_time;
    public $version;

    
    /**
     * プライマリキーが自動セット(auto increment)かどうかを設定します。
     * 
     * <pre>
     * 子クラスにて必ずセットする必要があります。
     * </pre>
     * 
     * @var bool 
     */
    public static $_AUTO = TRUE;

}

?>