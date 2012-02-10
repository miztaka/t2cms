<?php 

/**
 * Entity Class for meta_attribute
 *
 * エンティティに関するロジック等はここに実装します。
 * @package entity
 */
class Entity_MetaAttribute extends Entity_Base_MetaAttribute
{
    
    const DATA_TYPE_TEXT        = 1;
    const DATA_TYPE_TEXTAREA    = 2;
    const DATA_TYPE_CHECK       = 3;
    const DATA_TYPE_RADIO       = 4;
    const DATA_TYPE_SELECT      = 5;
    const DATA_TYPE_MULTISELECT = 6;
    const DATA_TYPE_IMAGE       = 7;
    const DATA_TYPE_REF         = 8;
    const DATA_TYPE_HTML        = 9;
    const DATA_TYPE_RESOURCE    = 10;
    
    public static $_data_typeOptions = array(
        self::DATA_TYPE_TEXT => '1行テキスト',
        self::DATA_TYPE_TEXTAREA => '複数行テキスト',
        self::DATA_TYPE_HTML => 'HTMLエディタ',
        self::DATA_TYPE_CHECK => 'チェックボックス(複数選択)',
        self::DATA_TYPE_RADIO => 'ラジオボタン(単一選択)',
        self::DATA_TYPE_SELECT => 'プルダウン(単一選択)',
        self::DATA_TYPE_MULTISELECT => 'プルダウン(複数選択)',
        self::DATA_TYPE_IMAGE => '画像',
        self::DATA_TYPE_REF => '参照関係',
        self::DATA_TYPE_RESOURCE => 'リソース'
    );
    
    /**
     * インスタンスを取得します。
     * @return Entity_MetaAttribute
     */
    public static function get() {
        return Teeple_Container::getInstance()->getEntity('Entity_MetaAttribute');
    }
    
    /**
     * 単一行の検索を実行します。
     * @param $id
     * @return Entity_MetaAttribute
     */
    public function find($id=null) {
        return parent::find($id);
    }
    
    /**
     * 選択肢のハッシュを取得します。
     */
    public function getOptions() {
        
        $result = array();
        if (! Teeple_Util::isBlank($this->options)) {
            $ar = explode("\n", $this->options);
            foreach ($ar as $opt) {
                $value = trim($opt);
                if (preg_match('/^[0-9a-zA-z]+\|\|.+$/', $value)) {
                    $v = explode("||", $value, 2);
                    $result[$v[0]] = $v[1];
                } else {
                    $result[$value] = $value;
                }
            }
        }
        return $result;
    }
    
    /**
     * 参照先オブジェクトの選択肢を取得します。
     */
    public function getRefOptions() {
        
        $result = array();
        if ($this->ref_entity_id && $this->data_type == self::DATA_TYPE_REF) {
            $values = Entity_MetaValue::get()
                ->join('meta_record')
                ->join('meta_attribute')
                ->eq('meta_record.meta_entity_id', $this->ref_entity_id)
                ->eq('meta_attribute.seq', 1)
                ->eq('meta_record.delete_flg', 0)
                ->order("IFNULL(meta_record.seq, 999), meta_record.id DESC")
                ->select();
            foreach ($values as $val) {
                $result[$val->meta_record_id] = $val->value;
            }
        }
        return $result;
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