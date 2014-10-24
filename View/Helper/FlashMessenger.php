<?php

class Eos_View_Helper_FlashMessenger extends Zend_View_Helper_Abstract {

    /**
     *
     * @var Zend_Controller_Action_Helper_FlashMessenger
     */
    private $_flashMessenger;
    
    /**
     * 
     * @return Zend_Controller_Action_Helper_FlashMessenger
     */
    public function flashMessenger() {
        if ($this->_flashMessenger === null) {
            $this->_flashMessenger = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');            
        }
        
        return $this->_flashMessenger;
    }

}