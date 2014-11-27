<?php

/**
 * Description of ControllerTestCase
 *
 * @author huyi
 */
class App_Test_PHPUnit_ControllerTestCase extends PHPUnit_Framework_TestCase {

    /**
     *
     * @var Zend_Controller_Request_HttpTestCase
     */
    protected $request;
    
    /**
     *
     * @var Zend_Controller_Response_HttpTestCase
     */
    protected $response;
            
    protected function setUp() {
        parent::setUp();
        
        // populate $_SESSION
        $reflection = new ReflectionClass('Zend_Session');
        $property = $reflection->getProperty('_unitTestEnabled');
        $property->setAccessible(true);
        Zend_Session::$_unitTestEnabled = true;
        
        $this->request = new Zend_Controller_Request_HttpTestCase();
        $this->response = new Zend_Controller_Response_HttpTestCase();
    }

    protected function tearDown() {
        parent::tearDown();
    }
    
    protected function dispatch($requestUri = null) {
        $this->request->setRequestUri($requestUri);
        
        return Zend_Controller_Front::getInstance()
                                    ->dispatch($this->request, $this->response);        
    }
    
    protected function resetRequest() {
        $this->request = new Zend_Controller_Request_HttpTestCase();
    }

    protected function resetResponse() {
        $this->response = new Zend_Controller_Response_HttpTestCase();
    }
}
