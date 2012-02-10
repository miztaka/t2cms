<?php



class Teeple_Converter_TrimTest extends UnitTestCase
{
    
    public function testTrim() {
        
        $obj = array(
            'aa' => '     foo    ',
            'bb' => 'bar    '
        );
        
        $converter = new Teeple_Converter_Trim();
        $converter->convert($obj, 'aa');
        
        var_dump($obj);
        
        $this->assertEqual('foo', $obj['aa']);
        
        $obj = new StdClass();
        $obj->aa = '      fooo   ';
        $converter->convert($obj, 'aa');
        $this->assertEqual('fooo', $obj->aa);
    }
    
}
?>