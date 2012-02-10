<?php 

error_reporting(E_ALL);
ini_set('display_error','ON');

require_once 'simpletestconfig.php';
set_time_limit(3000);

class AllTests extends TestSuite {
    
    function AllTests() {
        $this->TestSuite('All tests');
        $d = dir(dirname(dirname(__FILE__))."/testcase");
        while (FALSE !== ($file = $d->read())) {
            if (preg_match('/Test\.php$/', $file)) {
                $this->addFile($d->path."/{$file}");
            }
        }
    }
    
}

?>