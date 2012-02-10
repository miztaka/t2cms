<?php



class Teeple_Validator_LengthTest extends UnitTestCase
{
    
    public function testLength() {
        
        $obj = new StdClass();
        $obj->str1 = "test";
        $obj->str2 = "てすとです。ほげほげ";
        $obj->str3 = "";
        $obj->str4 = NULL;
        
        $validator = new Teeple_Validator_Length();
        $this->assertTrue($validator->validate($obj, "str1"));
        $this->assertTrue($validator->validate($obj, "str3"));
        $this->assertTrue($validator->validate($obj, "str4"));
        
        // minlength
        $validator->minlength = 5;
        $this->assertFalse($validator->validate($obj, "str1"));
        $this->assertTrue($validator->validate($obj, "str2"));
        $this->assertTrue($validator->validate($obj, "str3"));
        $this->assertTrue($validator->validate($obj, "str4"));
        
        // maxlength
        $validator->minlength = NULL;
        $validator->maxlength = 5;
        $this->assertTrue($validator->validate($obj, "str1"));
        $this->assertFalse($validator->validate($obj, "str2"));
        $this->assertTrue($validator->validate($obj, "str3"));
        $this->assertTrue($validator->validate($obj, "str4"));
        
        $validator->minlength = 5;
        $validator->maxlength = 10;
        $this->assertTrue($validator->validate($obj, "str2"));
        $this->assertFalse($validator->validate($obj, "str1"));
        
        $validator->minlength = 5;
        $validator->maxlength = 8;
        $this->assertFalse($validator->validate($obj, "str2"));
        $this->assertFalse($validator->validate($obj, "str1"));

    }
    
}
?>