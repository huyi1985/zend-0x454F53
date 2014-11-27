<?php

/**
 * Description of Node
 *
 * @author huyi
 */
class Eos_Easyui_Node {

    const STATE_OPEN = 'open';
    const STATE_CLOSED = 'closed';
    
    /**
     *
     * @var mixed
     */
    private $_parentId;
    
    /**
     * node id, which is important to load remote data
     * @var mixed
     */
    public $id;
        
    /**
     * node text to show 
     * @var string
     */
    public $text;
    
    /**
     * node state, 'open' or 'closed', default is 'open'. When set to 'closed', 
     * the node have children nodes and will load them from remote site
     * @var type 
     */
    public $state;
    
    /**
     * Indicate whether the node is checked selected.
     * @var boolean 
     */
    public $checked;
    
    /**
     * custom attributes can be added to a node
     * @var array
     */
    public $attributes = array();
    
    /**
     * an array nodes defines some children nodes
     * @var array
     */
    public $children = array();
    
    public function __construct() {
        $this->setState(self::STATE_OPEN)
             ->setChecked(false);
    }
    
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }
    
    public function getParentId() {
        return $this->_parentId;
    }

    public function setParentId($parentId) {
        $this->_parentId = $parentId;
        return $this;
    }
        
    public function getText() {
        return $this->text;
    }

    public function setText($text) {
        $this->text = $text;
        return $this;
    }

    public function getState() {
        if ($this->state === null) {
            $this->state = self::STATE_OPEN;
        }

        return $this->state;
    }

    public function setState($state) {
        $this->state = $state;
        return $this;
    }

    public function getChecked() {
        return $this->checked;
    }

    public function setChecked($checked) {
        $this->checked = $checked;
        return $this;
    }

    public function getAttributes() {
        return $this->attributes;
    }

    public function setAttributes($attributes) {
        $this->attributes = $attributes;
        return $this;
    }
    
    public function addAttribute($name, $value) {
        $this->attributes[$name] = $value;
        
        return $this;
    }
    
    public function getAttribute($name, $defaultValue = null) {
        $attributes = $this->getAttributes();
        if (array_key_exists($name, $attributes)) {
            return $attributes[$name];
        } else {
            return $defaultValue;
        }
    }

    public function getChildren() {
        return $this->children;
    }

    public function setChildren($children) {
        $this->children = $children;
        return $this;
    }

    public function addChild(Eos_Easyui_Node $node) {
        $this->children[] = $node;
        
        return $this;
    }
    
    public function setNoChildren() {
        unset($this->children);
        
        return $this;
    }
}
