<?php 

/**
 * Entity Class for meta_value
 *
 * エンティティに関するロジック等はここに実装します。
 * @package entity
 */
class Entity_MetaValue extends Entity_Base_MetaValue
{
    /**
     * インスタンスを取得します。
     * @return Entity_MetaValue
     */
    public static function get() {
        return Teeple_Container::getInstance()->getEntity('Entity_MetaValue');
    }
    
    /**
     * 単一行の検索を実行します。
     * @param $id
     * @return Entity_MetaValue
     */
    public function find($id=null) {
        return parent::find($id);
    }
    
    /**
     * JOINするテーブルを設定します。
     * ※generatorが吐き出した雛形を修正してください。
     * 
     * ここに設定してある定義は、$this->join('aliasname') で利用できる。<br/>
     * ※ここに設定しただけではJOINされない。
     * 
     * <pre>
     * 指定方法: 'アクセスするための別名' => 設定値の配列
     * 設定値の配列：
     *   'entity' => エンティティのクラス名
     * 　'columns' => 取得するカラム文字列(SQLにセットするのと同じ形式)
     *   'type' => JOINのタイプ(SQLに書く形式と同じ)(省略した場合はINNER JOIN)
     *   'relation' => JOINするためのリレーション設定
     *      「本クラスのキー名 => 対象クラスのキー名」となります。
     *   'condition' => JOINするための設定だがリテラルで指定するもの
     * 
     * 値の例:
     * 
     * $join_config = array(
     *     'aliasname' => array(
     *         'entity' => 'Entity_Fuga',
     *         'columns' => 'foo, bar, hoge',
     *         'type' => 'LEFT JOIN',
     *         'relation' => array(
     *             'foo_id' => 'bar_id'
     *         ),
     *         'condition' => 'aliasname.status = 1 AND parent.status = 1'
     *     )
     * );
     * </pre>
     * 
     * @var array
     */
    public static $_JOINCONFIG = array(
        'meta_record' => array(
            'entity' => 'Entity_MetaRecord',
            'type' => 'INNER JOIN',
            'relation' => array(
                'meta_record_id' => 'id'
            )
        ),
        'meta_attribute' => array(
            'entity' => 'Entity_MetaAttribute',
            'type' => 'INNER JOIN',
            'relation' => array(
                'meta_attribute_id' => 'id'
            )
        )
    );
    
    /**
     * @var Entity_MetaRecord 
     */
    public $meta_record;

    /**
     * @var Entity_MetaAttribute 
     */
    public $meta_attribute;



}

?>