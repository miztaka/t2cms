<?php
class LearningTest extends UnitTestCase
{
    public function testObjectVars() {
        
        $obj = new StdClass();
        $obj->aa = 'foo';
        $obj->bb = 'bar';
        
        var_dump(get_object_vars($obj));
        
        $obj = new ActionChild();
        $obj->aa = 'foo';
        $obj->bb = 'bar';
        
        var_dump(get_object_vars($obj));
    }
    
    /*
    public function testCsvParse() {
        
        $pattern = '("[^"]*(?:""[^"]*)*"|[^,]*),';
        $test = "47201,\"90101\",\"9,010154\",\"ｵｷﾅﾜｹﾝ\",\"ﾅﾊｼ\",\"ｱｶﾐﾈ\",\"沖縄県\",\"那\"\"覇市\",\"赤嶺\",0";
        $test .= ',';
        $matches = array();
        
        preg_match_all($pattern, $test, $matches);
        print_r($matches);
    }
    
    public function testCalendar() {
        
        $calendar = new Calendar_Week(2010, 2, 1, 2);
        print_r($calendar->thisWeek('array'));
    }
    */

    
}

class ActionChild extends Teeple_ActionBase
{
    
}

?>
