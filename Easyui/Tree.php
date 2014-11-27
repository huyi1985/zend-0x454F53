<?php

/**
 * Description of Tree
 *
 * @author huyi
 */
class Eos_Easyui_Tree {

    /**
     *
     * @var Eos_Easyui_Node
     */
    private $_rootNode;
    
    public function getRootNode() {
        return $this->_rootNode;
    }

    public function setRootNode(Eos_Easyui_Node $rootNode) {
        $this->_rootNode = $rootNode;
        return $this;
    }
        
    public function __construct(array $nodes) {
        $nodeMap = array();
        foreach ($nodes as $node) {
            /* @var $node Eos_Easyui_Node */
            $nodeMap[$node->getId()] = $node;
        }
        
        foreach ($nodes as $node) {
            $parentId = $node->getParentId();
            if (isset($nodeMap[$parentId])) {
                $nodeMap[$parentId]->addChild($node);
            } else {
                $this->_rootNode = $node;
            }
        }
    }
    
    public function toJson() {
        if ($this->_rootNode === null) {
            return json_encode(array());
        } else {
            return json_encode(array($this->_rootNode));
        }
    }
}
