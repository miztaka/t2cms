<?php

/**
 * CSVファイル等のファイルを読み込むロジックです。
 * @author miztaka
 *
 */
class Logic_DefaultFileReader implements Logic_FileReader
{
    
    protected $validationConfig = NULL;
    protected $converterConfig = NULL;
    
    public $separator = ',';
    public $linebreak = "\r\n";
    public $hasHeader = true;
    //public $charset = "SJIS";
    public $charset = "sjis-win";
    public $columns = array();
    
    public $contents;
    public $header;
    
    /**
     * @var Logger
     */
    protected $log;
    
    /**
     * @var Teeple_Request
     */
    protected $request;
    public function setComponent_Teeple_Request($c) {
        $this->request = $c;
    }

    /**
     * @var Teeple_ValidatorManager
     */
    protected $validatorManager;
    public function setComponent_Teeple_ValidatorManager($c) {
        $this->validatorManager = $c;
    }
    
    /**
     * @var Teeple_ConverterManager
     */
    protected $converterManager;
    public function setComponent_Teeple_ConverterManager($c) {
        $this->converterManager = $c;
    }
    
    /**
     * コンストラクタです。
     */
    public function __construct() {
        $this->log = LoggerManager::getLogger(get_class($this));
    }
    
    /**
     * ファイルを読み込みオブジェクトの配列を返します。
     * @param $filename string ファイル名(パス)
     * @param $validate boolean バリデーションするかどうか
     * @return array オブジェクトのリスト
     */
    public function readFile($filepath, $validate = true) {
        
        // 初期値設定
        $sep = $this->separator;
        $line_sep = $this->linebreak;
        $isheader = $this->hasHeader;
        $charset = $this->charset;
        
        $this->errors = array();
        $this->contents = array();
        $this->headers = new stdClass;
        
        // ファイルオープン
        if (($fp = fopen($filepath, 'r')) === FALSE) {
            $this->log->fatal("ファイルを開けません。({$filepath})");
            $this->request->addErrorMessage("システムエラーが発生しました。");
            return NULL;
        }
        
        // データを取得
        $contents = fread($fp, filesize($filepath));
        $contents = mb_convert_encoding($contents, SCRIPT_CODE, $charset);
        $lines = explode($line_sep, $contents);
        fclose($fp);
        
        $i=1;
        foreach ($lines as $tmp_line) {
            
            if ($sep == ',') {
                $line = $this->parseCSV($tmp_line);
            } else {
                $line = explode($sep, trim($tmp_line));
            }
            $this->log->debug(var_export($line, true));

            // 空白行は無視
            if (count($line) == 1 && $line[0] == "") {
                continue;
            }
            
            if (count($line) != count($this->columns)) {
                $this->log->info("データフォーマットが間違っています。({$i}行目)");
                $this->request->addErrorMessage("データフォーマットが間違っています。({$i}行目)");
                return NULL;
            }
            $line_data = new stdClass;
            for($p=0; $p<count($line); $p++) {
                
                // 前後のクオートを取り外す
                $val = preg_replace('/^"(.*)"$/', '$1', trim($line[$p]));
                $colname = $this->columns[$p];
                if ($isheader && $i === 1) {
                    $this->headers->$colname = $val;
                } else {
                    $line_data->$colname = $val;
                }
            }
            if (! ($isheader && $i === 1)) {
                $l = $isheader ? $i-1 : $i;
                // コンバータ
                if ($this->converterConfig != NULL) {
                    $this->converterManager->execute($line_data, $this->converterConfig);
                }
                // 値チェック
                if ($validate && ! $this->doValidate($line_data)) {
                    $this->request->addErrorMessage("({$i}行目でエラーが発生しました。)");
                    // return NULL; 最後までチェックする
                }
                // 値を配列に格納
                $this->contents[] = $line_data;
            }
            $i++;
        }
        if ($this->request->isError()) {
            return NULL;
        }
        if (count($this->contents) === 0) {
            $this->log->info("データが一件も存在しません。");
            $this->request->addErrorMessage("データが一件も存在しません。");
            return NULL;
        }
        
        return $this->contents;
    }
        
    /**
     * バリデーション定義をセットします。
     * 
     * @param $config string YAML形式のバリデーション定義
     * @return void
     */
    public function setValidationConfig($config) {
        
        $this->validationConfig = $this->validatorManager->parseYAML($config);
        return;
    }
    
    /**
     * コンバータ定義をセットします。
     * 
     * @param $config string YAML形式のコンバータ定義
     * @return void
     */
    public function setConverterConfig($config) {
        $this->converterConfig = Horde_Yaml::load($config);
        return;
    }
    
    /**
     * バリデーションを実行します。
     * @param $data stdClass チェック対象のオブジェクト
     * @return boolean
     */
    private function doValidate($data) {
        return $this->validatorManager->execute($data, $this->validationConfig);
    }
    
    /**
     * CSVレコードをパースして配列で返します。
     * @param $target string 対象文字列
     * @return array
     */
    public function parseCSV($target) {
        
        $pattern = '/("[^"]*(?:""[^"]*)*"|[^,]*),/';
        $target .= ",";
        $matches = array();
        preg_match_all($pattern, $target, $matches);
        
        foreach($matches[1] as &$val) {
            $val = preg_replace('/^"(.*)"$/', "$1", $val);
            $val = str_replace('""', '"', $val);
        }
        unset($val);

        return $matches[1];
    }

}

?>