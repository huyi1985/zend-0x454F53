<?php

/**
 * Description of Forest
 *
 * @author huyi
 */
class Eos_Easyui_Forest {
    /**
     *
     * @var array   array of instance of Eos_Easyui_Node
     */
    private $_rootNodes;
            
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
                $this->_rootNodes[] = $node;
            }
        }
    }
    
    public function toJson() {
        if ($this->_rootNodes === null) {
            return json_encode(array());
        } else {
            return json_encode($this->_rootNodes);
        }
    }
}
