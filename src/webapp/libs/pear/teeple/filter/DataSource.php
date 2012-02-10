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
 * DataSourceのハンドリングを行なうFilterです。
 *
 * @package     teeple.filter
 */
class Teeple_Filter_DataSource extends Teeple_Filter
{
    
    /**
     * @var Teeple_DataSource
     */
    private $dataSource;
    public function setComponent_Teeple_DataSource($c) {
        $this->dataSource = $c;
    }
    
    /**
     * コンストラクタ
     *
     */
    public function __construct() {
        parent::__construct();
    }

    public function prefilter() {}
    
    /**
     * DataSourceをすべてクローズします。
     *
     */
    public function postfilter() {
        $this->dataSource->closeAll();
    }
        
}
?>
