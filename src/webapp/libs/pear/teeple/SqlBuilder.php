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
 * SQLを構築するためのクラスです。
 * ActiveRecordやEavRecordから利用するために外だししてみる。
 * 将来的にはmysql以外のdialectに対応することも目指す。
 * 
 * @package teeple
 * 
 */
class Teeple_SqlBuilder
{
    
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
    protected $_tablename;
    
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
    protected $_pk;
    
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
    protected $_auto;
    
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
    protected $_joinconfig;
    
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
    public function lt($property, $value, $numeric=false) {
        
        if ($value === NULL || $value === "") {
            // do nothing
        } else {
            if ($numeric) {
                $this->where("{$property} < CAST(? AS DECIMAL)", $value);
            } else {
                $this->where("{$property} < ?", $value);
            }
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
    public function gt($property, $value, $numeric=false) {
        
        if ($value === NULL || $value === "") {
            // do nothing
        } else {
            if ($numeric) {
                $this->where("{$property} > CAST(? AS DECIMAL)", $value);
            } else {
                $this->where("{$property} > ?", $value);
            }
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
    public function le($property, $value, $numeric=false) {
        
        if ($value === NULL || $value === "") {
            // do nothing
        } else {
            if ($numeric) {
                $this->where("{$property} <= CAST(? AS DECIMAL)", $value);
            } else {
                $this->where("{$property} <= ?", $value);
            }
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
    public function ge($property, $value, $numeric=false) {
        
        if ($value === NULL || $value === "") {
            // do nothing
        } else {
            if ($numeric) {
                $this->where("{$property} >= CAST(? AS DECIMAL)", $value);
            } else {
                $this->where("{$property} >= ?", $value);
            }
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
     * SELECT文を構築します。
     *
     * @return String SELECT文
     */
    protected function _buildSelectSql() {
        
        $select_str = $this->_buildSelectClause();
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
