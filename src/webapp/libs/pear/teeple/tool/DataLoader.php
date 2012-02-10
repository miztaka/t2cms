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
 * @package     teeple.tool
 * @author      Mitsutaka Sato <miztaka@gmail.com>
 * @license     http://www.php.net/license/3_0.txt  PHP License 3.0
 */

require_once 'PHPExcel/IOFactory.php';

/**
 * Excelからデータベースにデータを読み込みます。
 *
 * @package teeple.tool
 */
class Teeple_Tool_DataLoader {
    
    /**
     * 
     * @var Teeple_Container
     */
    protected $c;
    public function setComponent_container($c) {
        $this->c = $c;
    }
    
    /**
     * @var Logger
     */
    protected $log;
    
    /**
     * コンストラクタです。
     */
    public function __construct() {
        $this->log = LoggerManager::getLogger(get_class($this));
    }

    /**
     * Excelからデータを読み込みます。
     */
    public function execute() {
        
        $filename = $this->_argv[0];
        if (! $filename || ! file_exists($filename)) {
            print "file not found. $filename";
            return;
        }
        
        //$excel = PHPExcel_IOFactory::load($filename);
        $objReader = PHPExcel_IOFactory::createReaderForFile($filename);
        $objReader->setReadDataOnly(true);
        $excel = $objReader->load($filename);
        
        $sheets = $excel->getAllSheets();
        if (isset($this->_argv[1]) && $this->_argv[1] == 'replace') {
            $this->clearTables($sheets);
        }
        foreach($sheets as $sheet) {
            $this->loadSheet($sheet);
        }
        return;
    }
    
    //================ hook ==================//
    
    /**
     * 指定された名前のEntityが存在するかどうか
     * @param string $name
     */
    protected function checkEntityExists($name) {
        $entity_name = "Entity_$name";
        return class_exists($entity_name, true);
    }
    
    /**
     * 指定された名前のEntityを作成します。
     * 
     * @param string $name
     */
    protected function getEntity($name) {
        $entity_name = "Entity_$name";
        return $this->c->getEntity($entity_name);
    }
    
    /**
     * シートからテーブルにデータを読み込みます。
     * @param PHPExcel_Worksheet $sheet
     */
    protected function loadSheet($sheet) {
        
        $name = $sheet->getTitle();
        if (! $this->checkEntityExists($name)) {
            print "entity class not found. skip. $name";
            return;
        }
        
        // ヘッダを読み込む
        $rowIterator = $sheet->getRowIterator();
        if (! $rowIterator->valid()) {
            print "no data. skip. $name";
            return;
        }
        $col = array();
        $row = $rowIterator->current();
        $cellIterator = $row->getCellIterator();
        
        while ($cellIterator->valid()) {
            $cell = $cellIterator->current();
            $col[] = trim($cell->getValue());
            $cellIterator->next();
        }
        $rowIterator->next();
        
        print "load $name .... ";
        
        // データを登録
        while ($rowIterator->valid()) {
            $row = $rowIterator->current();
        //foreach ($rowIterator as $row) {
            
            $entity = $this->getEntity($name);
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            $i=0;
            //while ($cellIterator->valid()) {
                //$cell = $cellIterator->current();
            foreach ($cellIterator as $cell) {
                $prop = $col[$i];
                $entity->$prop = trim($cell->getValue());
                //$cellIterator->next();
                $i++;
            }
            try {
                $entity->insert();
            } catch (Exception $e) {
                try {
                    $entity->update();
                } catch (Exception $e) {
                    print $e->getMessage() ."\n";
                }
            }
            $rowIterator->next();
        }
        print $sheet->getHighestRow() -1 ." rows loaded.\n";
        return;
    }
    
    /**
     * データを削除します。シートの逆順に実行します。
     * @param array $sheets
     */
    protected function clearTables($sheets) {
        
        $names = array();
        foreach ($sheets as $sheet) {
            $names[] = $sheet->getTitle();
        }
        foreach (array_reverse($names) as $name) {
            $entity_name = "Entity_$name";
            if (! class_exists($entity_name, true)) {
                print "entity class not found. skip. $entity_name";
                continue;
            }
            $entity = $this->c->getEntity($entity_name);
            $entity->deleteAll();
        }
        return;
    }
    
}

?>