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

/**
 * exception class for Teeple_ActiveRecord.
 * 
 * @package teeple
 * 
 */
class TeepleActiveRecordException extends Exception
{
    /**
     * コンストラクタです。
     * @param string $message
     */
    function __construct($message)
    {
        return parent::__construct($message);
    }
}

/**
 * ActiveRecordクラスです。
 * 
 * TODO oneToMany -- いったんなしにしとく。
 * TODO oracle等のsequence対応。
 * 
 * <pre>
 * [Entityクラスの作成]
 * このクラスを継承して各テーブルのレコードを表すクラスを作成します。
 * ファイルの配置場所は、ENTITY_DIR で定義されたディレクトリです。
 * ファイル名は、`table名`.class.php, クラス名は Entity_`table名` とします。
 * 
 * 各テーブルの以下のプロパティを定義する必要があります。
 *   // 使用するデータソース名(Teepleフレームワークを使用しない場合は不要。)
 * 　public static $_DATASOURCE = "";
 *   // テーブル名称
 *   public static $_TABLENAME = "";
 *   // プライマリキーのカラム名(配列)
 *   public static $_PK = array();
 *   // プライマリキー以外のカラム名(配列)
 *   public static $_COLUMNS = array();
 *   // PKが単一で、AUTOINCREMENT等の場合にTRUEをセット
 *   // ※シーケンスには対応できていません。
 *   public static $_AUTO = TRUE;
 *   // joinするテーブルの定義
 *   public static $_JOINCONFIG = array();
 * </pre>
 * 
 * @package teeple
 * 
 */
class Teeple_ActiveRecord
{
    /**
     * 使用するデータソース名を指定します。
     * 指定が無い場合は、DEFAULT_DATASOURCE で設定されているDataSource名が使用されます。
     *
     * @var string
     */
    public static $_DATASOURCE = "";
    
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
    public static $_TABLENAME = "";
    private $_tablename;
    
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
    public static $_PK = array('id');
    private $_pk;
    
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
    private $_auto;
    
    /**
     * JOINするテーブルを設定します。
     * 
     * ここに設定してある定義は、$this->join('aliasname') を呼ぶことで初めて結合対象となる。<br/>
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
     * 
     * 値の例:
     * 
     * public static $_JOINCONFIG = array(
     *     'fuga' => array(
     *         'entity' => 'Entity_Fuga',
     *         'columns' => 'foo, bar, hoge',
     *         'type' => 'LEFT JOIN',
     *         'relation' => array(
     *             'foo_id' => 'bar_id'
     *         )
     *     )
     * );
     * </pre>
     * 
     * @var array
     */
    public static $_JOINCONFIG = array();
    private $_joinconfig;
    
    /**
     * Loggerを格納します。
     * 
     * @var object
     */
    protected $_log = null;
    
    protected $_join = array();
    protected $_pdo = null;
    protected $_constraints = array();
    protected $_criteria = array();
    protected $_bindvalue = array();
    protected $_children = array();
    protected $_afterwhere = array();

    /**
     * コンストラクタです。
     * 
     * <pre>
     * テーブル名が定義されていないときはクラス名称からEntity_を取り除いたものを
     * テーブル名として使用します。
     * </pre>
     * 
     * @param object $pdo PDOインスタンスを指定します。
     */
    public function __construct($pdo)
    {
        $class_name = get_class($this);
        // TODO LoggerMangerに依存している
        $this->_log = LoggerManager::getLogger($class_name);
        $this->_pdo = $pdo;
        
        // 子クラスの設定値を取得
        $ref = new ReflectionClass($class_name);
        $this->_tablename = $ref->getStaticPropertyValue("_TABLENAME");
        $this->_pk = $ref->getStaticPropertyValue("_PK");
        $this->_auto = $ref->getStaticPropertyValue("_AUTO");
        $this->_joinconfig = $ref->getStaticPropertyValue("_JOINCONFIG");
        
        if ($this->_tablename == "") {
            $this->_tablename = preg_replace('/^entity_/', '', strtolower($class_name));
        }
        
        return;
    }

    /**
     * PDOオブジェクトを取得します。
     * 
     * @return PDO PDOオブジェクト
     */
    public function getPDO()
    {
        return $this->_pdo;
    }

    /**
     * このクラスの新しいインスタンスを返します。
     * @return Teeple_ActiveRecord
     */
    public function newInstance()
    {
        $class_name = get_class($this);
        $obj = new $class_name($this->_pdo);
        return $obj;
    }

    /**
     * 制約を設定します。
     * この機能は必要？ -> 必要 楽観的排他制御などに使う
     * 
     * @param string $name カラム名称
     * @param mixed $value カラム値
     */
    public function setConstraint($name, $value)
    {
        $this->_constraints[$name] = $value;
    }

    /**
     * 制約を取得します。
     * TODO
     * 
     * @param string $name カラム名称
     * @return mixed カラム値
     */
    public function getConstraint($name)
    {
        return array_key_exists($name, $this->_constraints) ? $this->_constraints[$name] : null;
    }

    //--------------------- ここから流れるインターフェース ----------------------//
    
    /**
     * JOINするテーブルを設定します。
     * 
     * <pre>
     * $aliasnameで指定された _JOINCONFIGの設定で結合します。
     * JOINする条件を追加する場合は第2引数以降に where()と同じ方法で指定します。
     * 
     * 結合をネストする場合は、
     * $this->join('hoge')->join('hoge$fuga')
     * のように、エイリアス名を $ で繋げて指定します。
     * 'hoge'のEntityに定義されている _JOINCONFIG の 'fuga'が適用されます。
     * </pre>
     *
     * @param mixed $aliasname エイリアス名
     * @param string $condition 追加する条件
     * @param string $params 可変長引数($condition)
     * @return Teeple_ActiveRecord
     */
    public function join() {
        
        $args_num = func_num_args();
        if ($args_num < 1) {
            throw new TeepleActiveRecordException("args too short.");
        }
        
        $args_ar = func_get_args();
        $aliasname = array_shift($args_ar);
        
        // conditionが指定されているか？
        $condition = null;
        $cond_params = null;
        if (count($args_ar)) {
            $condition = array_shift($args_ar);
            if (count($args_ar)) {
                if (is_array($args_ar[0])) {
                    $cond_params = $args_ar[0];
                } else {
                    $cond_params = $args_ar;
                }
            }
        }
        if ($condition != null && $cond_params != null) {
            $this->_checkPlaceHolder($condition, $cond_params);
        }
        
        $alias_ar = explode('$', $aliasname);
        if (count($alias_ar) == 1) {
            // ネスト無しの場合
            if (! isset($this->_joinconfig[$aliasname])) {
                throw new TeepleActiveRecordException("join config not found: {$aliasname}");
            }
            $this->_join[$aliasname] = $this->_joinconfig[$aliasname];
        } else {
            // ネストありの場合
            $child_name = array_pop($alias_ar);
            $base_name = implode('$', $alias_ar);
            if (! isset($this->_join[$base_name])) {
                throw new TeepleActiveRecordException("nest join parent not set: {$base_name}");
            }
            $entity = $this->_join[$base_name]['entity'];
            $joinconfig = $this->_getEntityConfig($entity, '_JOINCONFIG');
            if (! isset($joinconfig[$child_name])) {
                throw new TeepleActiveRecordException("nest join alias not found: {$child_name}");
            }
            $this->_join[$aliasname] = $joinconfig[$child_name];
        }

        // condition
        if ($condition != null) {
            $this->_join[$aliasname]['condition'] = array($condition => $cond_params);
        }
        return $this;
    }
    
    /**
     * Where句を追加します。
     * 
     * @param String Where句です。プレースホルダーに?を使用します。カラム名は、エイリアス名.カラム名で指定します。(主テーブルのエイリアスは 'base'を指定します。)
     * @param mixed プレースホルダーにセットする値です。複数ある場合は１つの配列を渡してもよいし、複数の引数として渡してもよいです。
     * @return Teeple_ActiveRecord
     */
    public function where() {
        
        $args_num = func_num_args();
        if ($args_num < 1) {
            throw new TeepleActiveRecordException("args too short.");
        }
        
        $args_ar = func_get_args();
        $where_clause = array_shift($args_ar);
        if (! strlen($where_clause)) {
            $this->_log->info("no where clause.");
            return $this;
        }
        
        // 引数の数とプレースホルダーの数を確認する。
        if (@is_array($args_ar[0])) {
            $args_ar = $args_ar[0];
        }
        
        $this->_checkPlaceHolder($where_clause, $args_ar);
        //$this->_criteria[$where_clause] = $args_ar;
        $this->_criteria[] = array(
            'str' => $where_clause,
            'val' => $args_ar
        );
        
        return $this;
    }
    
    /**
     * property = ? の条件を追加します。
     * 
     * <pre>
     * $notnullonly が trueのときは $valueに値がセットされている場合のみ追加されます。
     * falseのときは、 property IS NULL が追加されます。
     * </pre>
     *
     * @param string $property プロパティ名
     * @param mixed $value 値
     * @param boolean $notnullonly NULLチェックフラグ 
     * @return Teeple_ActiveRecord
     */
    public function eq($property, $value, $notnullonly=true) {
        
        if ($value === NULL || $value === "") {
            if (! $notnullonly) {
                $this->where("{$property} IS NULL");
            }
        } else {
            $this->where("{$property} = ?", $value);
        }
        return $this;
    }
    
    /**
     * property <> ? の条件を追加します。
     * 
     * <pre>
     * $notnullonly が trueのときは $valueに値がセットされている場合のみ追加されます。
     * falseのときは、 property IS NOT NULL が追加されます。
     * </pre>
     *
     * @param string $property プロパティ名
     * @param mixed $value 値
     * @param boolean $notnullonly NULLチェックフラグ 
     * @return Teeple_ActiveRecord
     */
    public function ne($property, $value, $notnullonly=true) {
        
        if ($value === NULL || $value === "") {
            if (! $notnullonly) {
                $this->where("{$property} IS NOT NULL");
            }
        } else {
            $this->where("{$property} <> ?", $value);
        }
        return $this;
    }
    
    /**
     * property < ? の条件を追加します。
     * 
     * <pre>
     * $valueの値がセットされているときのみ追加します。
     * </pre>
     * 
     * @param string $property プロパティ名
     * @param mixed $value 値
     * @return Teeple_ActiveRecord
     */
    public function lt($property, $value) {
        
        if ($value === NULL || $value === "") {
            // do nothing
        } else {
            $this->where("{$property} < ?", $value);
        }
        return $this;
    }
    
    /**
     * property > ? の条件を追加します。
     * 
     * <pre>
     * $valueの値がセットされているときのみ追加します。
     * </pre>
     * 
     * @param string $property プロパティ名
     * @param mixed $value 値
     * @return Teeple_ActiveRecord
     */
    public function gt($property, $value) {
        
        if ($value === NULL || $value === "") {
            // do nothing
        } else {
            $this->where("{$property} > ?", $value);
        }
        return $this;
    }
    
    /**
     * property <= ? の条件を追加します。
     * 
     * <pre>
     * $valueの値がセットされているときのみ追加します。
     * </pre>
     * 
     * @param string $property プロパティ名
     * @param mixed $value 値
     * @return Teeple_ActiveRecord
     */
    public function le($property, $value) {
        
        if ($value === NULL || $value === "") {
            // do nothing
        } else {
            $this->where("{$property} <= ?", $value);
        }
        return $this;
    }
    
    /**
     * property >= ? の条件を追加します。
     * 
     * <pre>
     * $valueの値がセットされているときのみ追加します。
     * </pre>
     * 
     * @param string $property プロパティ名
     * @param mixed $value 値
     * @return Teeple_ActiveRecord
     */
    public function ge($property, $value) {
        
        if ($value === NULL || $value === "") {
            // do nothing
        } else {
            $this->where("{$property} >= ?", $value);
        }
        return $this;
    }
    
    /**
     * property in (?,?...) の条件を追加します。
     * 
     * <pre>
     * $valueの値がセットされているときのみ追加します。
     * </pre>
     * 
     * @param string $property プロパティ名
     * @param array $value 値
     * @return Teeple_ActiveRecord
     */
    public function in($property, $value) {
        
        if (! is_array($value) || count($value) == 0) {
            // do nothing
        } else {
            $num = count($value);
            $placeholder = "";
            for($i=0; $i<$num; $i++) {
                $placeholder .= "?,";
            }
            $placeholder = substr($placeholder, 0, -1);
            
            $this->where("{$property} IN ({$placeholder})", $value);
        }
        return $this;
    }
    
    /**
     * property not in (?,?...) の条件を追加します。
     * 
     * <pre>
     * $valueの値がセットされているときのみ追加します。
     * </pre>
     * 
     * @param string $property プロパティ名
     * @param array $value 値
     * @return Teeple_ActiveRecord
     */
    public function notin($property, $value) {
        
        if (! is_array($value) || count($value) == 0) {
            // do nothing
        } else {
            $num = count($value);
            $placeholder = "";
            for($i=0; $i<$num; $i++) {
                $placeholder .= "?,";
            }
            $placeholder = substr($placeholder, 0, -1);
            
            $this->where("{$property} NOT IN ({$placeholder})", $value);
        }
        return $this;
    }
    
    /**
     * property like ? の条件を追加します。
     * 
     * <pre>
     * $valueの値がセットされているときのみ追加します。
     * </pre>
     * 
     * @param string $property プロパティ名
     * @param mixed $value 値
     * @return Teeple_ActiveRecord
     */
    public function like($property, $value) {
        
        if ($value === NULL || $value === "") {
            // do nothing
        } else {
            $this->where("{$property} LIKE ?", $value);
        }
        return $this;
    }
    
    /**
     * property like ? の条件を追加します。
     * 
     * <pre>
     * 値の最後に%をつけます。
     * $valueの値がセットされているときのみ追加します。
     * </pre>
     * 
     * @param string $property プロパティ名
     * @param mixed $value 値
     * @return Teeple_ActiveRecord
     */
    public function starts($property, $value) {
        
        if ($value === NULL || $value === "") {
            // do nothing
        } else {
            $this->where("{$property} LIKE ?", addslashes($value) .'%');
        }
        return $this;
    }

    /**
     * property like ? の条件を追加します。
     * 
     * <pre>
     * 値の最初に%をつけます。
     * $valueの値がセットされているときのみ追加します。
     * </pre>
     * 
     * @param string $property プロパティ名
     * @param mixed $value 値
     * @return Teeple_ActiveRecord
     */
    public function ends($property, $value) {
        
        if ($value === NULL || $value === "") {
            // do nothing
        } else {
            $this->where("{$property} LIKE ?", '%'. addslashes($value));
        }
        return $this;
    }
    
    /**
     * property like ? の条件を追加します。
     * 
     * <pre>
     * 値の最初と最後に%をつけます。
     * $valueの値がセットされているときのみ追加します。
     * </pre>
     * 
     * @param string $property プロパティ名
     * @param mixed $value 値
     * @return Teeple_ActiveRecord
     */
    public function contains($property, $value) {
        
        if ($value === NULL || $value === "") {
            // do nothing
        } else {
            $this->where("{$property} LIKE ?", '%'. addslashes($value) .'%');
        }
        return $this;
    }
    
    /**
     * order by を指定します。
     *
     * @param string $clause order by 句
     * @return Teeple_ActiveRecord
     */
    public function order($clause) {
        
        $this->_afterwhere['order'] = addslashes($clause);
        return $this;
    }
    
    /**
     * limit を指定します。
     *
     * @param int $num 最大件数
     * @return Teeple_ActiveRecord
     */
    public function limit($num) {
        
        if (is_numeric($num)) {
            $this->_afterwhere['limit'] = $num;
        }
        return $this;
    }
    
    /**
     * offset を指定します。
     *
     * @param int $num 開始位置
     * @return Teeple_ActiveRecord
     */
    public function offset($num) {
        
        if (is_numeric($num)) {
            $this->_afterwhere['offset'] = $num;
        }
        return $this;
    }
    
    /**
     * group by を指定します。
     *
     * @param string $clause group by 句
     * @return Teeple_ActiveRecord
     */
    public function group($clause) {
        
        $this->_afterwhere['group'] = addslashes($clause);
        return $this;
    }
    
    /**
     * SELECTを実行します。
     * 結果を配列で返します。
     *
     * @param string $columns SELECT句を指定したい場合は指定できます。(書き方特殊)
     * @return array ResultSetをこのクラスの配列として返します。
     */
    public function select($columns=NULL) {
        
        $this->_bindvalue = array();
        
        $sql = $this->_buildSelectSql($columns);
        $this->_log->debug("exec select: $sql");
        $this->_log->debug("param is: \n".@var_export($this->_bindvalue,TRUE));
        
        $sth = $this->_pdo->prepare($sql);
        if(! $sth) {
            $err = $this->_pdo->errorInfo();
            throw new TeepleActiveRecordException("pdo prepare failed: {$err[2]}:{$sql}");
        }
        if(! $sth->execute(@$this->_bindvalue)) {
            $err = $sth->errorInfo();
            throw new TeepleActiveRecordException("pdo execute failed: {$err[2]}:{$sql}");
        }
        
        $row_list = $sth->fetchAll(PDO::FETCH_ASSOC);
        $this->_log->debug("exec select result: \n".@var_export($row_list, TRUE));
        
        $item_list = array();
        foreach($row_list as $row) {
            $item = clone($this);
            $item->_buildResultSet($row);
            $item->resetInstance();
            $item_list[] = $item;
        }
        
        $this->resetInstance();
        return $item_list;
    }
    
    /**
     * paginationに対応したSELECTをします。
     * @param int $limit
     * @param int $offset
     * @return array [0]=全件数, [1]=limit,offsetに対応した結果セット(entityの配列)
     */
    public function selectPage($limit, $offset) {
        
        $total = $this->count();
        if ($total == 0) {
            return array(0, null);
        }
        $result = $this->limit($limit)->offset($offset)->select();
        
        return array($total, $result);
    }
    
    /**
     * 単一行の検索を実行します。
     *
     * @param mixed $id 配列で無い場合は、単一PKの値として扱います。配列の場合は、カラム名 => 値 のHashとして扱います。
     * @return Teeple_ActiveRecord
     */
    public function find($id=null) {
        
        $this->_bindvalue = array();
        
        if ($id != null) {
            if (! is_array($id)) {
                if (count($this->_pk) != 1) {
                    throw new TeepleActiveRecordException("pk is not single.");
                }
                $this->setConstraint($this->_pk[0], $id);
            } else {
                foreach($id as $col => $val) {
                    $this->setConstraint($col, $val);
                }
            }
        }
        
        $sql = $this->_buildSelectSql();
        $this->_log->debug("exec find: $sql");
        $this->_log->debug("param is: \n".@var_export($this->_bindvalue,TRUE));
        
        $sth = $this->_pdo->prepare($sql);
        if(! $sth) {
            $err = $this->_pdo->errorInfo();
            throw new TeepleActiveRecordException("pdo prepare failed: {$err[2]}:{$sql}");
        }
        if(! $sth->execute(@$this->_bindvalue)) {
            $err = $sth->errorInfo();
            throw new TeepleActiveRecordException("pdo execute failed: {$err[2]}:{$sql}");
        }
        
        $row_list = $sth->fetchAll(PDO::FETCH_ASSOC);
        $this->_log->debug("exec select result: \n".@var_export($row_list, TRUE));
        
        if (count($row_list) > 1) {
            throw new TeepleActiveRecordException("too many rows.");
        }
        if (count($row_list) == 0) {
            return null;
        }
        
        $item = clone($this);
        $item->_buildResultSet($row_list[0]);
        $item->resetInstance();
        
        $this->resetInstance();
        return $item;
    }
    
    /**
     * 条件に該当するレコード数を取得します。
     * 
     * @return int 該当件数
     */
    public function count($column=NULL)
    {
        $this->_bindvalue = array();
        
        if (Teeple_Util::isBlank($column)) {
            $column = '*';
        }
        $select_str = "SELECT COUNT({$column})";
        $from_str = $this->_buildFromClause();
        $where_str = $this->_buildWhereClause();
        $other_str = $this->_buildAfterWhereClause();
        
        $sql = implode(" ", array($select_str,$from_str,$where_str,$other_str));
        $this->_log->debug("exec count: $sql");
        $this->_log->debug("param is: \n".@var_export($this->_bindvalue,TRUE));
        
        $sth = $this->_pdo->prepare($sql);
        if(! $sth) {
            $err = $this->_pdo->errorInfo();
            throw new TeepleActiveRecordException("pdo prepare failed: {$err[2]}:{$sql}");
        }
        if(! $sth->execute(@$this->_bindvalue)) {
            $err = $sth->errorInfo();
            throw new TeepleActiveRecordException("pdo execute failed: {$err[2]}:{$sql}");
        }
        
        $count = $sth->fetchColumn();
        $this->_log->debug("count result: $count");
        
        //$this->resetInstance();
        return $count;
    }

    /**
     * レコードを登録します。
     * 
     * <pre>
     * constraintとrowに設定されている値で、レコードを１つ作成します。
     * PKが単一カラムで、$_auto がTRUEに設定されている場合で、INSERT値にPKが設定されていなかった場合は、
     * INSERT後、インスタンスにPK値をセットします。
     * </pre>
     * 
     * @return bool 登録できたかどうか。
     */
    public function insert()
    {
        if (function_exists('teeple_activerecord_before_insert')) {
            teeple_activerecord_before_insert($this);
        }

        $this->_bindvalue = array();
        $row = $this->_convertObject2Array($this, true);
        $this->_log->info("insert ". $this->_tablename. ": \n".@var_export($row,TRUE));
        
        $binding_params = $this->_makeBindingParams($row);
        $sql = "INSERT INTO `". $this->_tablename ."` (" .
            implode(', ', array_keys($row)) . ') VALUES(' .
            implode(', ', array_keys($binding_params)) . ');';

        $this->_log->debug("sql: $sql");
        $sth = $this->_pdo->prepare($sql);
        if(! $sth) { 
            $err = $this->_pdo->errorInfo();
            throw new TeepleActiveRecordException("pdo prepare failed: {$err[2]}:{$sql}");
        }

        if(! $sth->execute($binding_params)) {
            $err = $sth->errorInfo();
            throw new TeepleActiveRecordException("pdo execute failed: {$err[2]}:{$sql}");
        }

        if (count($this->_pk) == 1 && $this->_auto && (! isset($this->{$this->_pk[0]}) || $this->{$this->_pk[0]} == "")) {
            $this->{$this->_pk[0]} = $this->_pdo->lastInsertId();
            $this->_log->info("AUTO: ". $this->_pk[0] ." = {$this->{$this->_pk[0]}}");
        }
        
        $this->_log->info("insert ". $this->_tablename .": result=(".$sth->rowCount().")");
        
        $this->resetInstance();
        return $sth->rowCount() > 0;
    }
    
    /**
     * レコードの更新を実行します。
     * 
     * <pre>
     * rowにセットされているPKで更新を行ないます。
     * </pre>
     * 
     * @return int 変更のあったレコード数
     */
    public function update()
    {
        if (function_exists('teeple_activerecord_before_update')) {
            teeple_activerecord_before_update($this);
        }
        $this->_bindvalue = array();
        if (! $this->isSetPk()) {
            throw new TeepleActiveRecordException("primary key not set.");
        }
        
        $values = $this->_convertObject2Array($this, false);
        $this->_log->info("update ". $this->_tablename .": \n".@var_export($values,TRUE));
        $pks = array();
        // primary key は 更新しない。
        foreach ($this->_pk as $pk) {
            unset($values[$pk]);
            $this->setConstraint($pk, $this->$pk);
        }
        if (! count($values)) {
            throw new TeepleActiveRecordException("no columns to update.");
        }

        $sql = "UPDATE `". $this->_tablename ."` ".
            $this->_buildSetClause($values).
            " ". $this->_buildConstraintClause(false);
        
        $this->_log->debug("update ". $this->_tablename .": {$sql}");
        $this->_log->debug(@var_export($this->_bindvalue,TRUE));
        
        $sth = $this->_pdo->prepare($sql);
        if (! $sth) {
            $err = $this->_pdo->errorInfo();
            throw new TeepleActiveRecordException("pdo prepare failed: {$err[2]}:{$sql}");
        }
        if (! $sth->execute($this->_bindvalue)) {
            $err = $sth->errorInfo();
            throw new TeepleActiveRecordException("pdo execute failed: {$err[2]}:{$sql}");
        }
        
        $this->_log->info("update ". $this->_tablename .": result=(".$sth->rowCount().")");
        
        if ($sth->rowCount() != 1) {
            throw new TeepleActiveRecordException('更新に失敗しました。他の処理と重なった可能性があります。');
        }
        
        $this->resetInstance();
        return $sth->rowCount();
    }
    
    /**
     * 条件に該当するレコードを全て更新します。
     * 
     * <pre>
     * セットされているconstraints及びcriteriaに
     * 該当するレコードを全て更新します。
     * </pre>
     * 
     * @return int 更新件数
     */
    public function updateAll()
    {
        if (function_exists('teeple_activerecord_before_updateAll')) {
            teeple_activerecord_before_updateAll($this);
        }
         
        $this->_bindvalue = array();
        
        $row = $this->_convertObject2Array($this, true);
        $sql = "UPDATE `". $this->_tablename ."` ".
            $this->_buildSetClause($row).
            " ". $this->_buildWhereClause(false);
        
        $this->_log->info("updateAll ". $this->_tablename .": $sql");
        $this->_log->info("param is: \n". @var_export($this->_bindvalue, TRUE));
        $sth = $this->_pdo->prepare($sql);
        
        if (! $sth) {
            $err = $this->_pdo->errorInfo();
            throw new TeepleActiveRecordException("pdo prepare failed: {$err[2]}:{$sql}");
        }
        if (! $sth->execute($this->_bindvalue)) {
            $err = $sth->errorInfo();
            throw new TeepleActiveRecordException("pdo execute failed: {$err[2]}:{$sql}");
        }
        
        $this->_log->info("updateAll ". $this->_tablename .": result=(".$sth->rowCount().")");
        
        $this->resetInstance();
        return $sth->rowCount();        
    }
    
    /**
     * 指定されたレコードを削除します。 
     * 
     * <pre>
     * constraintまたは $idパラメータで指定されたPKに該当するレコードを削除します。
     * $id がハッシュでないときは、id列の値とみなしてDELETEします。
     * $id がハッシュのときは、key値をPKのカラム名とみなしてDELETEします。
     * </pre>
     * 
     * @param mixed $id PKの値
     * @return bool 実行結果
     */
    public function delete($id = null)
    {
        $this->_bindvalue = array();
        
        // rowにセットされているPKがあればconstraintに
        foreach ($this->_pk as $pk) {
            if (isset($this->$pk) && $this->getConstraint($pk) == "") {
                $this->setConstraint($pk, $this->$pk);
            }
        }
        
        if ($id != null) {
            if (! is_array($id)) {
                if (count($this->_pk) != 1) {
                    throw new TeepleActiveRecordException("pk is not single.");
                }
                $this->setConstraint($this->_pk[0], $id);
            } else {
                foreach($id as $col => $val) {
                    $this->setConstraint($col, $val);
                }
            }
        }
        
        $sql = "DELETE FROM `". $this->_tablename ."` ".
            $this->_buildConstraintClause(false);

        $this->_log->info("delete ". $this->_tablename .": $sql");
        $this->_log->debug("param is: \n". @var_export($this->_bindvalue, TRUE));
        
        $sth = $this->_pdo->prepare($sql);
        if (! $sth) {
            $err = $this->_pdo->errorInfo();
            throw new TeepleActiveRecordException("pdo prepare failed: {$err[2]}:{$sql}");
        }
        if (! $sth->execute($this->_bindvalue)) {
            $err = $sth->errorInfo();
            throw new TeepleActiveRecordException("pdo execute failed: {$err[2]}:{$sql}");
        }
        
        $this->_log->info("delete ". $this->_tablename .": result=(".$sth->rowCount().")");

        $props = array_keys(get_class_vars(get_class($this)));
        foreach ($props as $key) {
            $this->$key = NULL;
        }
        
        $this->resetInstance();
        return $sth->rowCount() > 0;
    }
    
    /**
     * 条件に該当するレコードを全て削除します。
     * 
     * <pre>
     * セットされているconstraints及びcriteriaに
     * 該当するレコードを全て削除します。
     * </pre>
     * 
     * @return int 削除件数
     */
    public function deleteAll()
    {
        $this->_bindvalue = array();
        
        $sql = "DELETE FROM `". $this->_tablename ."` ".
            $this->_buildWhereClause(false);
        
        $this->_log->info("deleteAll ". $this->_tablename .": $sql");
        $this->_log->info("param is: \n". @var_export($this->_bindvalue, TRUE));
        $sth = $this->_pdo->prepare($sql);
        
        if (! $sth) {
            $err = $this->_pdo->errorInfo();
            throw new TeepleActiveRecordException("pdo prepare failed: {$err[2]}:{$sql}");
        }
        if (! $sth->execute($this->_bindvalue)) {
            $err = $sth->errorInfo();
            throw new TeepleActiveRecordException("pdo execute failed: {$err[2]}:{$sql}");
        }
        
        $this->_log->info("deleteAll ". $this->_tablename .": result=(".$sth->rowCount().")");
        
        $this->resetInstance();
        return $sth->rowCount();
    }
    
    /**
     * 指定されたSELECT文を実行します。
     * 結果はstdClassの配列になります。
     * 結果が0行の場合は空の配列が返ります。
     *
     * @param string $query
     * @param array $bindvalues
     * @return array
     */
    public function selectQuery($query, $bindvalues) {
        
        // prepare
        $sth = $this->getPDO()->prepare($query); 
        
        // bind
        if (is_array($bindvalues) && count($bindvalues) > 0) {
            foreach($bindvalues as $i => $value) {
                $sth->bindValue($i+1, $value);
            }
        }
        
        // 実行
        $sth->execute();
        
        // 結果を取得
        $result = array();
        while ($row = $sth->fetch()) {
            $obj = new stdClass;
            foreach($row as $col => $value) {
                $obj->$col = $value;
            }
            array_push($result, $obj);
        }
        return $result;
    }
    
    /**
     * 指定されたSELECT文を実行します。(単一行)
     * 結果はstdClassになります。
     * 結果が0行の場合はNULLが返ります。
     *
     * @param string $query
     * @param array $bindvalues
     * @return stdClass
     */
    public function findQuery($query, $bindValues) {
        
        $result = $this->selectQuery($query, $bindValues);
        if (count($result) > 0) {
            return $result[0];
        }
        return NULL;
    }    
    
    /**
     * インスタンスのcriteriaをリセットします。
     * 値は保持されます。
     *
     */
    public function resetInstance() {
        $this->_join = array();
        $this->_criteria = array();
        $this->_constraints = array();
        $this->_bindvalue = array();
        $this->_afterwhere = array();
        
        return;
    }
    
    /**
     * ActionクラスのプロパティからEntityのプロパティを生成します。
     *
     * @param Object $obj Actionクラスのインスタンス
     * @param array $colmap 'entityのカラム名' => 'Actionのプロパティ名' の配列
     */
    public function convert2Entity($obj, $colmap=null) {
        
        if ($colmap == null) {
            $colmap = array();
        }
        
        $columns = $this->_getColumns(get_class($this));
        foreach($columns as $column) {
            $prop = array_key_exists($column, $colmap) ? $colmap[$column] : $column;
            if (isset($obj->$prop)) {
                $this->$column = $obj->$prop;
            }
        }
        return;
    }
    
    /**
     * EntityのプロパティからActionクラスのプロパティを生成します。
     *
     * @param Object $obj Actionクラスのインスタンス
     * @param array $colmap 'entityのカラム名' => 'Actionのプロパティ名' の配列
     */
    public function convert2Page($obj, $colmap=null) {

        if ($colmap == null) {
            $colmap = array();
        }
        
        $columns = $this->_getColumns(get_class($this));
        foreach($columns as $column) {
            if (@isset($this->$column)) {
                $prop = array_key_exists($column, $colmap) ? $colmap[$column] : $column;
                $obj->$prop = $this->$column;
            }
        }
        return;
    }
    
    /**
     * 現在の時刻を返します。
     * @return string 
     */
    public function now() {
        return date('Y-m-d H:i:s');
    }
    
    /**
     * SELECT文を構築します。
     *
     * @return String SELECT文
     */
    protected function _buildSelectSql($columns=NULL) {
        
        $select_str = $columns == NULL ? 
            $this->_buildSelectClause() :
            "SELECT {$columns}";
        $from_str = $this->_buildFromClause();
        $where_str = $this->_buildWhereClause();
        $other_str = $this->_buildAfterWhereClause();
        
        return implode(" \n", array($select_str, $from_str, $where_str, $other_str));
    }
    
    /**
     * SELECT clause を構築します。
     *
     * @return String SELECT clause
     */
    protected function _buildSelectClause() {
        
        $buff = array();
        
        // 本クラスのカラム
        $columns = $this->_getColumns(get_class($this));
        foreach($columns as $col) {
            $buff[] = "base.{$col} AS base\${$col}";
        }
        
        // JOINするテーブルのカラム
        if (count($this->_join)) {
            foreach($this->_join as $alias => $config) {
                $join_columns = $this->_getColumns($config['entity']);
                foreach ($join_columns as $col) {
                    $buff[] = "{$alias}.{$col} AS {$alias}\${$col}";
                }
            }
        }
        
        return "SELECT ". implode(', ', $buff);
    }

    /**
     * FROM clause を構築します。
     *
     * @return unknown
     */
    protected function _buildFromClause() {
        
        $buff = array();
        $buff[] = "FROM ". $this->_tablename ." base";
        
        // join
        if (count($this->_join)) {
            foreach ($this->_join as $alias => $conf) {
                $base = 'base';
                $alias_ar = explode('$', $alias);
                if (count($alias_ar) > 1) {
                    array_pop($alias_ar);
                    $base = implode('$', $alias_ar);
                }
                
                $tablename = $this->_getEntityConfig($conf['entity'], '_TABLENAME');
                
                if (! isset($conf['type'])) {
                    $conf['type'] = 'INNER JOIN';
                }
                
                $conds = array();
                foreach ($conf['relation'] as $here => $there) {
                    array_push($conds, "{$base}.{$here} = {$alias}.{$there}");
                }
                if (isset($conf['condition'])) {
                    foreach($conf['condition'] as $statement => $params) {
                        array_push($conds, " ( {$statement} ) ");
                        if (is_array($params)) {
                            foreach($params as $item) {
                                array_push($this->_bindvalue, $item);
                            }
                        }
                    }
                }
                
                $conditions = "(". implode(' AND ', $conds) .")";
                
                $buff[] = "{$conf['type']} {$tablename} {$alias} ON {$conditions}";
            }
        }
        return implode(" \n", $buff);
    }
    
    /**
     * WHERE clause を構築します。
     *
     * @return unknown
     */
    protected function _buildWhereClause($usebase=true) {
        
        $buff = array();
        
        // constraints
        if (count($this->_constraints)) {
            foreach($this->_constraints as $col => $val) {
                if ($val != null) {
                    $buff[] = $usebase ? "base.{$col} = ?" : "{$col} = ?";
                    array_push($this->_bindvalue, $val);
                } else {
                    $buff[] = $usebase ? "base.{$col} IS NULL" : "{$col} IS NULL";
                }
            }
        }
        
        // criteria
        if (count($this->_criteria)) {
            //foreach($this->_criteria as $str => $val) {
            foreach($this->_criteria as $cri) {
                $str = $cri['str'];
                $val = $cri['val'];
                $buff[] = $str;
                if ($val != null) {
                    if (is_array($val)) {
                        foreach($val as $item) {
                            array_push($this->_bindvalue, $item);
                        }
                    }
                }
            }
        }
        
        if (count($buff)) {
            return "WHERE (". implode(") \n AND (", $buff) .")";
        }        
        return "";
    }

    /**
     * WHERE clause を構築します。
     *
     * @return unknown
     */
    protected function _buildConstraintClause($usebase=true) {
        
        $buff = array();
        
        // constraints
        if (count($this->_constraints)) {
            foreach($this->_constraints as $col => $val) {
                if ($val != null) {
                    $buff[] = $usebase ? "base.{$col} = ?" : "{$col} = ?";
                    array_push($this->_bindvalue, $val);
                } else {
                    $buff[] = $usebase ? "base.{$col} IS NULL" : "{$col} IS NULL";
                }
            }
        }
        if (count($buff)) {
            return "WHERE ". implode(' AND ', $buff);
        }
        return "";
    }    
    
    /**
     * WHERE以降の clause を作成します。
     *
     * @return unknown
     */
    protected function _buildAfterWhereClause() {
        
        $buff = array();
        
        // ORDER BYから書かないとだめ！
        if (count($this->_afterwhere)) {
            if (isset($this->_afterwhere['group'])) {
                $buff[] = "GROUP BY {$this->_afterwhere['group']}";
            }
            if (isset($this->_afterwhere['order'])) {
                $buff[] = "ORDER BY {$this->_afterwhere['order']}";
            }
            if (isset($this->_afterwhere['limit'])) {
                $buff[] = "LIMIT {$this->_afterwhere['limit']}";
            }
            if (isset($this->_afterwhere['offset'])) {
                $buff[] = "OFFSET {$this->_afterwhere['offset']}";
            }
        }
        
        if (count($buff)) {
            return implode(' ', $buff);
        }
        return "";
    }
    
    /**
     * UPDATE文のVALUES部分を作成します。
     *
     * @param array $array アップデートする値の配列 
     * @return string SQL句の文字列
     */
    protected function _buildSetClause($array) {
        foreach($array as $key => $value) {
            $expressions[] ="{$key} = ?";
            array_push($this->_bindvalue, $value);
        }
        return "SET ". implode(', ', $expressions);
    }
    
    /**
     * 単一レコードの値をセットします。
     *
     * @param unknown_type $row
     */
    protected function _buildResultSet($row) {
        
        foreach($row as $key => $val) {
            $alias_ar = explode('$', $key);
            $col = array_pop($alias_ar); 
            
            if (count($alias_ar) == 1 && $alias_ar[0] == 'base') {
                $this->$col = $val;
                continue;
            }
            
            $ref = $this;
            $base = "";
            while($alias = array_shift($alias_ar)) {
                $base .= $base == "" ? $alias : "$".$alias;
                if ($ref->$alias == NULL) {
                    $class_name = $this->_join[$base]['entity'];
                    $obj = new $class_name($this->_pdo);
                    $ref->$alias = $obj;
                }
                $ref = $ref->$alias;
            }
            
            $ref->$col = $val;
        }
        
        return;
    }
    
    protected function _checkPlaceHolder($condition, $params) {
        
        $param_num = count($params);
        $holder_num = substr_count($condition, '?');
        if ($param_num != $holder_num) {
            throw new TeepleActiveRecordException("The num of placeholder is wrong.");
        }
    }
    
    protected function _getEntityConfig($clsname, $property) {
        
        $ref = new ReflectionClass($clsname);
        return $ref->getStaticPropertyValue($property);
    }

    /**
     * 設定された制約でWHERE句を作成します。
     *
     * @param array $array 制約値
     * @return string SQL句の文字列
     */
    protected function _makeUpdateConstraints($array) {
        foreach($array as $key => $value) {
            if(is_null($value)) {
                $expressions[] = "{$key} IS NULL";
            } else {
                $expressions[] = "{$key}=:{$key}";
            }
        }
        return implode(' AND ', $expressions);
    }

    /**
     * バインドするパラメータの配列を作成します。
     *
     * @param array $array バインドする値の配列
     * @return array バインドパラメータを名前にした配列
     */
    protected function _makeBindingParams( $array )
    {
        $params = array();
        foreach( $array as $key=>$value )
        {
            $params[":{$key}"] = $value;
        }
        return $params;
    }

    /**
     * IN句に設定するIDのリストを作成します。
     * 
     * @param array $array IDの配列
     * @return string IN句に設定する文字列
     */
    protected function _makeIDList( $array )
    {
        $expressions = array();
        foreach ($array as $id) {
            $expressions[] = "`". $this->_tablename ."`.id=".
                $this->_pdo->quote($id, isset($this->has_string_id) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        return '('.implode(' OR ', $expressions).')';
    }
    
    /**
     * PKがセットされているかどうかをチェックします。
     * 
     * @return PKがセットされている場合はTRUE
     */
    protected function isSetPk()
    {
        if (! isset($this->_pk)) {
            return isset($this->id);
        }
        
        foreach ($this->_pk as $one) {
            if (! isset($this->$one)) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Entityのカラム値をArrayとして取り出す
     *
     * @param Teeple_ActiveRecord $obj
     * @param boolean $excludeNull
     * @return array
     */
    protected function _convertObject2Array($obj, $excludeNull=false) {
        
        $columns = $this->_getColumns(get_class($obj));
        $result = array();
        foreach ($columns as $name) {
            $val = $obj->$name;
            if (@is_array($val)) {
                $result[$name] = serialize($val);
            } else if (! $excludeNull || ($val !== NULL && strlen($val) > 0)) {
                $result[$name] = $this->_null($val);
            }
        }

        return $result;
    }
    
    /**
     * Entityクラスのカラム名一覧を取得する
     *
     * @param string $clsname
     * @return array
     */ 
    protected function _getColumns($clsname) {
        
        $joinconfig = $this->_getEntityConfig($clsname, "_JOINCONFIG");
        $joinNames = array_keys($joinconfig);

        $result = array();
        $vars = get_class_vars($clsname);
        foreach($vars as $name => $value) {
            // _で始まるものは除外
            if (substr($name, 0, 1) === '_') {
                continue;
            }
            // _joinconfigで指定されている名前は除外
            if (in_array($name, $joinNames)) {
                continue;
            }
            
            array_push($result, $name);
        }
        
        return $result;
    }

    protected function _null($str) {
        return $str !== NULL && strlen($str) > 0 ? $str : NULL;
    }

}

?>
