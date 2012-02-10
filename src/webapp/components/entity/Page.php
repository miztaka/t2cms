<?php 

/**
 * Entity Class for page
 *
 * エンティティに関するロジック等はここに実装します。
 * @package entity
 */
class Entity_Page extends Entity_Base_Page
{
    
    const PAGE_TYPE_DETAIL = 1;
    const PAGE_TYPE_LIST   = 2;
    const PAGE_TYPE_FORM   = 3;
    
    public static $_page_typeOptions = array(
        self::PAGE_TYPE_DETAIL => '詳細ページ',
        self::PAGE_TYPE_LIST   => '一覧ページ',
        self::PAGE_TYPE_FORM   => '投稿フォーム'
    );
    
    public static $_encodingOptions = array(
        'UTF-8' => 'UTF-8',
        'sjis-win' => 'Shift_JIS', 
        'EUC-JP' => 'EUC-JP'
    );
    
    /**
     * インスタンスを取得します。
     * @return Entity_Page
     */
    public static function get() {
        return Teeple_Container::getInstance()->getEntity('Entity_Page');
    }
    
    /**
     * 単一行の検索を実行します。
     * @param $id
     * @return Entity_Page
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
        'meta_entity' => array(
            'entity' => 'Entity_MetaEntity',
            'type' => 'INNER JOIN',
            'relation' => array(
                'meta_entity_id' => 'id'
            )
        )
    );
    
    /**
     * @var Entity_MetaEntity 
     */
    public $meta_entity;



}

?>