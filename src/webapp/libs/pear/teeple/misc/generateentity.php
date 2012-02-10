<?php

require_once 'creole/Creole.php';

/* fix $dsn, $datasource, $outputdir to suit your environment */
$dsn = array('phptype' => 'mysql',
             'hostspec' => 'localhost',
             'username' => 'default',
             'password' => 'default',
             'database' => 'default');
$datasource = "default";
$outputdir = dirname(__FILE__)."/../../../../components/entity";

/*
 * start. 
 */
$conn = Creole::getConnection($dsn);
$dbinfo = $conn->getDatabaseInfo();

$base_dir = $outputdir .'/'. 'base';
if (! file_exists($base_dir) && ! mkdir($base_dir)) {
    print "could not make directory for base: {$outputdir}";
    exit;
}

foreach($dbinfo->getTables() as $tbl) {
    $classname = "";
    $tablename = "";
    $capName = "";
    $pkdef = "";
    $coldef = "";
    $joindef = "";
    $aliasesdef = "";
    
    $tablename = strtolower($tbl->getName());
    $ar = split('_', $tablename);
    foreach($ar as $key => $val) {
        $ar[$key] = ucfirst($val);
    }
    $capName = join('', $ar);
    $classname = "Entity_{$capName}";
    $baseclassname = "Entity_Base_{$capName}";

    // columns
    $cols = array();
    foreach($tbl->getColumns() as $col) {
        $cols[] = strtolower($col->getName());
    }
    
    // primary key
    $pks = array();
    $pk = $tbl->getPrimaryKey();
    if (is_object($pk)) {
        foreach($pk->getColumns() as $pkcol) {
            $pks[] = strtolower($pkcol->getName());
        }
    }
    
    // join
    $aliases = array();
    $joindef_buf = array();
    $fks = $tbl->getForeignKeys();
    if (is_array($fks)) {
        foreach($fks as $fk) {
            $alias = "";
            $entity = "";
            $refdef = array();
            
            $refs = $fk->getReferences();
            if (is_array($refs)) {
                //$alias = strtolower($fk->getName());
                $alias = strtolower($refs[0][1]->getTable()->getName());
                $entity = 'Entity_'. ucfirst(strtolower($refs[0][1]->getTable()->getName()));
                $aliases[] = array(
                    'name' => $alias,
                    'entity' => $entity
                );
                
                foreach($refs as $ref) {
                    $p = strtolower($ref[0]->getName());
                    $c = strtolower($ref[1]->getName());
                    $refdef[] = "                '{$p}' => '{$c}'";
                }
                
                $buf = "";
                $buf .= "        '{$alias}' => array(\n";
                $buf .= "            'entity' => '{$entity}',\n";
                $buf .= "            'type' => 'INNER JOIN',\n";
                $buf .= "            'relation' => array(\n";
                $buf .= implode(",\n", $refdef);
                $buf .= "\n";
                $buf .= "            )\n";
                $buf .= "        )";
                
                $joindef_buf[] = $buf;
            }
        }
    }
    if (count($joindef_buf)) {
        $joindef = implode(",\n", $joindef_buf);
    }
    
    if (count($aliases)) {
        foreach ($aliases as $alias) {
            $aliasesdef .= <<<EOT
    /**
     * @var {$alias['entity']} 
     */
    public \${$alias['name']};


EOT;
        }
    }
    
    //$cols = array_diff($cols, $pks);
    foreach ($cols as $col) {
        $coldef .= "    public \$".$col.";\n";
    }
    
    //$coldef = "'". implode("',\n        '", $cols) ."'";
    $pkdef = "'". implode("',\n'", $pks) ."'";

    // ベースクラスの作成
    $filepath = $outputdir ."/base/{$capName}.php";
    if (file_exists($filepath)) {
        print "{$tablename}: base class already exists. \n";
        continue;
    }
    if (!$handle = fopen($filepath, 'wb')) {
        print "{$filepath}: could not open file. \n";
        continue;
    }
        
    $contents = <<<EOT
class {$baseclassname} extends Teeple_ActiveRecord
{

    /**
     * 使用するデータソース名を指定します。
     * 指定が無い場合は、DEFAULT_DATASOURCE で設定されているDataSource名が使用されます。
     *
     * @var string
     */
    public static \$_DATASOURCE = '{$datasource}';
    
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
    public static \$_TABLENAME = '{$tablename}';
    
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
    public static \$_PK = array(
        {$pkdef}
    );
    
    /**
     * このテーブルのカラム名をpublicプロパティとして設定します。(プライマリキーを除く)
     * <pre>
     * 子クラスにて必ずセットする必要があります。
     * </pre>
     */
{$coldef}
    
    /**
     * プライマリキーが自動セット(auto increment)かどうかを設定します。
     * 
     * <pre>
     * 子クラスにて必ずセットする必要があります。
     * </pre>
     * 
     * @var bool 
     */
    public static \$_AUTO = TRUE;

}
EOT;

    $contents = "<?php \n\n". $contents ."\n\n?>";
    if (fwrite($handle, $contents) === FALSE) {
        print "{$filepath}: failed to write to the file. \n";
        continue;
    }

    print "{$tablename}: entity base class created . \n";
    fclose($handle);

    // エンティティクラスの作成
    $filepath = $outputdir ."/{$capName}.php";
    if (file_exists($filepath)) {
        print "{$tablename}: entity class already exists. \n";
        continue;
    }
    if (!$handle = fopen($filepath, 'wb')) {
        print "{$filepath}: could not open file. \n";
        continue;
    }
        
    $contents = <<<EOT
/**
 * Entity Class for {$tablename}
 *
 * エンティティに関するロジック等はここに実装します。
 * @package entity
 */
class {$classname} extends {$baseclassname}
{
    /**
     * インスタンスを取得します。
     * @return {$classname}
     */
    public static function get() {
        return Teeple_Container::getInstance()->getEntity('{$classname}');
    }
    
    /**
     * 単一行の検索を実行します。
     * @param \$id
     * @return {$classname}
     */
    public function find(\$id=null) {
        return parent::find(\$id);
    }
    
    /**
     * JOINするテーブルを設定します。
     * ※generatorが吐き出した雛形を修正してください。
     * 
     * ここに設定してある定義は、\$this->join('aliasname') で利用できる。<br/>
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
     * \$join_config = array(
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
    public static \$_JOINCONFIG = array(
{$joindef}
    );
    
{$aliasesdef}    

}
EOT;

    $contents = "<?php \n\n". $contents ."\n\n?>";
    if (fwrite($handle, $contents) === FALSE) {
        print "{$filepath}: failed to write to the file. \n";
        continue;
    }

    print "{$tablename}: entity class created . \n";
    fclose($handle);
    
}

?>
