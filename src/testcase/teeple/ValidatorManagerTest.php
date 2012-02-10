<?php
class Teeple_ValidatorManagerTest extends UnitTestCase
{
    
    protected $container;
    
    public function setUp() {
        $this->container = Teeple_Container::getInstance();
    }
    
    public function testValid() {
        
        $obj = new StdClass();
        $obj->str1 = "test";
        $obj->str2 = "てすとです。ほげほげ";
        $obj->str3 = "";
        $obj->str4 = NULL;

        $config = array(
            array(
                'name' => 'str1',
                'validation' => array(
                    'length' => array(
                        'args' => array('maxlength','minlength'),
                        'minlength' => 5,
                        'maxlength' => 10
                    )
                )
            ),
            array(
                'name' => 'str2',
                'label' => '文字列２',
                'validation' => array(
                    'required' => array(),
                    'length' => array(
                        'minlength' => 3,
                        'maxlength' => 9,
                        'msg' => '{0}の長さが間違ってるで。'
                    )
                )
            )
        );
        
        $vm = $this->container->getComponent('Teeple_ValidatorManager');
        $vm->execute($obj, $config);
        
        $request = $this->container->getComponent('Teeple_Request');
        $errors = $request->getAllErrorMessages();
        
        $this->assertEqual(2, count($errors));
        $this->assertEqual("値1は10文字以上5文字以下で入力してください。", $errors[0]);
        $this->assertEqual("文字列２の長さが間違ってるで。", $errors[1]);

    }
    
}
 
?>