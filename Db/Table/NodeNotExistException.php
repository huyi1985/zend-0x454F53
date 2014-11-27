<?php

/**
 * Description of NodeNotExistException
 *
 * @author huyi
 */
class Eos_Db_Table_NodeNotExistException extends Exception {
    protected $_nodeId;
    
    public function __construct($nodeId, $message = '', $code = 0, $previous = null) {
        $this->_nodeId = $nodeId;
        parent::__construct($message, $code, $previous);
    }
    
    public function getNodeId() {
        return $this->_nodeId;
    }

    public function setNodeId($nodeId) {
        $this->_nodeId = $nodeId;
        return $this;
    }

}
