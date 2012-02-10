<?php
/**
 * TSVを出力するクラスです。
 * @author miztaka
 *
 */
class Writer_TsvWriter
{
    protected $columnDef = array();
    protected $isHeader = FALSE;
    protected $charset = "sjis-win";
	/**
	 * @var Logger
	 */
	protected $log;

	/**
	 * コンストラクタです。
	 */
    public function __construct($columnDef, $isHeader = FALSE) {
		$this->log = LoggerManager::getLogger(get_class($this));
    	$this->columnDef = $columnDef;
        $this->isHeader = $isHeader;
        return;
    }
    
    public function outputTsv($list, $renderer=NULL) {
        
        // rendererクラス
        $refclass = NULL;
        if (is_object($renderer)) {
            $refclass = new ReflectionClass(get_class($renderer));
        }
        
        $result = array();
        
        if ($this->isHeader) {
            $buff = array();
            foreach(array_keys($this->columnDef) as $v) {
                $buff[] = $v;
            }
            $result[] = implode(chr(9), $buff);
        }
        
        foreach($list as $line) {
            $buff = array();
            foreach($this->columnDef as $head => $colname) {
                $render_method = 'render_'.$colname;
                if ($refclass && $refclass->hasMethod('render_'.$colname)) {
                    $v = $renderer->$render_method($line);
                } else {
                    $v = is_object($line) ? @$line->$colname : @$line[$colname];
                }
                $buff[] = $v;
                //$buff[] = $this->makeTsvValue($v);
            }
            $result[] = implode(chr(9), $buff);
        }
		$buf = implode("\r\n", $result); 
		//$this->log->debug("TsvWriter Output\n".$buf);         
        print mb_convert_encoding($buf, $this->charset, SCRIPT_CODE);
        return;
    }
}