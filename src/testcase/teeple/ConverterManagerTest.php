<?php
class Teeple_ConverterManagerTest extends UnitTestCase
{
    
    protected $container;
    
    public function setUp() {
        $this->container = Teeple_Container::getInstance();
    }
    
    public function testConvert() {
        
        $obj = array(
            'datear' => array(2008,2,9)
        );
        
        $config = array(
            'datear' => array(
                'datearray' => array('target' => 'datestr')
            )
        );

        $cm = $this->container->getComponent('Teeple_ConverterManager');
        $cm->execute($obj, $config);
        
        $this->assertTrue(isset($obj['datestr']));
        $this->assertEqual('2008-02-09', $obj['datestr']);

    }
    
}
 
?>