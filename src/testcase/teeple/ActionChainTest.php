<?php
class Teeple_ActionChainTest extends UnitTestCase
{
    
    protected $container;
    
    /**
     * @var Teeple_ActionChain
     */
    protected $actionChain;
    
    /**
     * @var Teeple_Request
     */
    protected $request;
    
    /**
     * @var Teeple_Response
     */
    protected $response;
    
    public function setUp() {
        $this->container = Teeple_Container::getInstance();
        $this->actionChain = $this->container->getComponent('Teeple_ActionChain');
        $this->request = $this->container->getComponent('Teeple_Request');
        $this->container->register('DefaultTx', new StdClass);
        $this->response = $this->container->getComponent('Teeple_Response');
    }
    
    public function testValidation() {
        
        $this->request->setParameter('str1','test');
        $this->request->setParameter('str2','てすとです。ほげほげ');
        $this->request->setActionMethod("doLogin");

        $this->actionChain->add('teeple_test_action');
        $this->actionChain->execute();
        
        $errors = $this->request->getAllErrorMessages();
        
        $this->assertEqual(2, count($errors));
        $this->assertEqual("値1は10文字以上5文字以下で入力してください。", $errors[0]);
        $this->assertEqual("文字列２の長さが間違ってるで。", $errors[1]);
        
        $this->assertEqual("result/validateError", $this->response->getView());
        
        // executeメソッドにはValidationは実行されない。
        $this->actionChain->clear();
        $this->actionChain->add('teeple_test_action');
        $this->request->setActionMethod('execute');
        $this->request->resetErrorMessages();
        $this->request->setFilterError(NULL);
        
        $this->actionChain->execute();
        
        $errors = $this->request->getAllErrorMessages();
        $this->assertEqual(0, count($errors));
        $this->assertEqual('result/execute', $this->response->getView());

    }
    
}
 
?>