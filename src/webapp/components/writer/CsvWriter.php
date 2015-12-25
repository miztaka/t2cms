<?php

/**
 * CSVを出力するクラスです。
 * @author miztaka
 *
 */
class Writer_CsvWriter
{
    protected $columnDef = array();
    public $isHeader = FALSE;
    public $charset = "sjis-win";
    
    
    public function __construct($columnDef, $isHeader = FALSE) {
        $this->columnDef = $columnDef;
        $this->isHeader = $isHeader;
        return;
    }
    
    public function outputCsv($list, $renderer=NULL, $filename=NULL) {
        
        // rendererクラス
        $refclass = NULL;
        if (is_object($renderer)) {
            $refclass = new ReflectionClass(get_class($renderer));
        }
        
        $result = array();
        
        if ($this->isHeader) {
            $buff = array();
            foreach(array_keys($this->columnDef) as $v) {
                $buff[] = $this->makeCsvValue($v);
            }
            $result[] = implode(',', $buff);
        }
        
        foreach($list as $line) {
            $buff = array();
            foreach($this->columnDef as $head => $colname) {
                $render_method = 'render_'.$colname;
                if (strpos($colname, '.') !== FALSE) {
                    $v = $this->getStructuredValue($line, $colname);
                } else if ($refclass && $refclass->hasMethod('render_'.$colname)) {
                    $v = $renderer->$render_method($line);
                } else {
                    $v = is_object($line) ? @$line->$colname : @$line[$colname];
                }
                $buff[] = $this->makeCsvValue($v);
            }
            $result[] = implode(',', $buff);
        }
        
        $output = mb_convert_encoding(implode("\r\n", $result), $this->charset, SCRIPT_CODE);
        $output .= "\r\n"; // 最後にも改行
        if ($filename) {
            if (! ($fp = fopen($filename, 'wb'))) {
                throw new Teeple_Exception('ファイルを開けません。');
            }
            fwrite($fp, $output);
            fclose($fp);
        } else {
            print $output;
        }
        return;
    }
    
    protected function makeCsvValue($v) {
        if (is_array($v)) {
            $v = implode('，', $v);
        }
        $buf = str_replace('"', '""', $v);
        return '"'. $buf .'"';
    }
    
    /**
     * Entityの階層構造を解釈して値を取得します。
     * @param object $line
     * @param string $colname
     */
    protected function getStructuredValue($line, $colname) {
        
        $value = $line;
        foreach (explode('.', $colname) as $prop) {
            $value = $value->$prop;
        }
        return $value;
    }
    
}

?>