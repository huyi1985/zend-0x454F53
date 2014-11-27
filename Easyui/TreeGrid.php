<?php


/**
 * Description of TreeGrid
 *
 * @author JackalHu
 */
class Eos_Easyui_TreeGrid {
    
    protected $_parentIdFieldName;
    
    protected $_idFieldName;
    
    protected $_rows = array();
    
    protected $_roots = array();


    public function getParentIdFieldName() {
        return $this->_parentIdFieldName;
    }

    public function getIdFieldName() {
        return $this->_idFieldName;
    }

    public function getRows() {
        return $this->_rows;
    }

    public function setParentIdFieldName($parentIdFieldName) {
        $this->_parentIdFieldName = $parentIdFieldName;
        return $this;
    }

    public function setIdFieldName($idFieldName) {
        $this->_idFieldName = $idFieldName;
        return $this;
    }

    public function addRow(array $row) {
        $id = $row[$this->getIdFieldName()];
        
        $rowObject = new stdClass();
        foreach ($row as $key => $value) {
            $rowObject->$key = $value;
        }
        $this->_rows[$id] = $rowObject;
        
        return $this;
    }
    
    /**
     * Treeize flat row data
     */
    protected function _treeize() {
        foreach ($this->_rows as $_row) {
            $parentIdFieldName = $this->getParentIdFieldName();
            $parentId = $_row->$parentIdFieldName;
            if (isset($this->_rows[$parentId])) {
                $this->_rows[$parentId]->children[] = $_row;
            } else {
                $this->_roots[] = $_row;
            }
        }
        
        return $this->_roots;
    }
    
    public function toJson() {
        $this->_treeize();
        return json_encode($this->_roots);
    }

}
