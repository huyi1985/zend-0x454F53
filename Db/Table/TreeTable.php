<?php

/**
 * Extend Zend_Db_Table for accessing Hierarchical Data with Adjacency List 
 * (parent ID) plus Path Enumeration solutions
 *
 * @author huyi
 * @todo 多主键抛异常
 * @todo protected setter
 */
abstract class Eos_Db_Table_TreeTable extends Zend_Db_Table_Abstract {
    
    const EXCEPTION_MESSAGE_MULTIPLE_PRIMARY_KEYS = '';
    
    /**
     * Separator used by the path column required by the Path Enumeration Solution
     * @var string 
     */
    protected $_separator;
    
    /**
     * Name of the path column required by the Path Enumeration Solution
     * @var string
     */
    protected $_pathColumn;
    
    /**
     * Name of the parent id column required by the Adjacency List Solution
     * @var string
     */
    protected $_parentIdColumn;
    
    /**
     *
     * @var int|string
     */
    protected $_parentIdOfRoot;
    
    /**
     *
     * @var string
     */
    protected $_lastInsertedPath;
    
    /**
     * Retrieve the default separator used by the path column 
     * @return string
     */
    public function getDefaultSeparator() {
        return '/';
    }
    
    /**
     * Retrieve the separator used by the path column. If there is no separator
     * has been set, it will return the default separator
     * 
     * @return string 
     */
    public function getSeparator() {
        if (!isset($this->_separator)) {
            return $this->getDefaultSeparator();
        }
        
        return $this->_separator;
    }
    
    /**
     * Retrieve the default name of the path column. If there is no path column
     * name has been specified, it will return the default name of the path column
     * 
     * @return string
     */
    public function getDefaultPathColumn() {
        return 'path';
    }
    
    /**
     * Retrieve the name of the path column
     * @return string
     */    
    public function getPathColumn() {
        if (!isset($this->_pathColumn)) {
            return $this->getDefaultPathColumn();
        }
        
        return $this->_pathColumn;
    }
    
    /**
     * Retrieve the default name of the parent id column
     * @return string
     */
    public function getDefaultParentIdColumn() {
        return 'parentId';
    }
    
    /**
     * Retrieve the name of the parent id column
     * @return string
     */    
    public function getParentIdColumn() {
        if (!isset($this->_parentIdColumn)) {
            return $this->getDefaultParentIdColumn();
        }
        
        return $this->_parentIdColumn;
    }
      
    public function getParentIdOfRoot() {        
        return $this->_parentIdOfRoot;
    }

    protected function _setParentIdOfRoot($parentIdOfRoot) {
        $this->_parentIdOfRoot = $parentIdOfRoot;
        return $this;
    }
    
    public function getLastInsertedPath() {
        return $this->_lastInsertedPath;
    }

    protected function _setLastInsertedPath($lastInsertedPath) {
        $this->_lastInsertedPath = $lastInsertedPath;
        return $this;
    }
        
    /**
     * Fetches all children of a specified parent.
     *
     * @param int|string                        $parentId Parent Id 
     * @param string|array|Zend_Db_Table_Select $where  OPTIONAL An SQL WHERE clause or Zend_Db_Table_Select object.
     * @param string|array                      $order  OPTIONAL An SQL ORDER clause.
     * @param int                               $count  OPTIONAL An SQL LIMIT count.
     * @param int                               $offset OPTIONAL An SQL LIMIT offset.
     * @return Zend_Db_Table_Rowset_Abstract The row results per the Zend_Db_Adapter fetch mode.
     */
    public function fetchChildren($parentId, $where = null, $order = null, $count = null, $offset = null) {
        $where = (array) $where;
        $parentIdColumn = $this->getParentIdColumn();
        if ($parentId === null) {
            $where["{$parentIdColumn} IS NULL"] = '';
        } else {
            $where["{$parentIdColumn} = ?"] = $parentId;
        }

        return $this->fetchAll($where, $order, $count, $offset);
    }

    /**
     * Fetches the parent of the specified child. 
     * @param int|string $id    Id of the child
     * @return Zend_Db_Table_Row_Abstract
     */
    public function fetchParent($id) {
        $primaryKey = current($this->info(self::PRIMARY));
        $parentIdColumn = $this->getParentIdColumn();
        $select = $this->select();
        $select->from(array('child' => $this->info(self::NAME)))
               ->joinInner(array('parent' => $this->info(self::NAME)), 
                           "child.{$parentIdColumn} = parent.{$primaryKey}")
               ->where("child.{$primaryKey} = ?", $id)
               ->limit(1);
                           
        return $this->fetchRow($select);
    }

    /**
     * Fetches a specified node's all ancestors via its path.
     *
     * @param string                            $path   path
     * @param string|array|Zend_Db_Table_Select $where  OPTIONAL An SQL WHERE clause or Zend_Db_Table_Select object.
     * @param string|array                      $order  OPTIONAL An SQL ORDER clause.
     * @param int                               $count  OPTIONAL An SQL LIMIT count.
     * @param int                               $offset OPTIONAL An SQL LIMIT offset.
     * @return Zend_Db_Table_Rowset_Abstract The row results per the Zend_Db_Adapter fetch mode.
     * @throws InvalidArgumentException
     */
    public function fetchAncestors($path, $where = null, $order = null, $count = null, $offset = null) {        
        $this->_checkPath($path);
        
        $pathColumn = $this->getPathColumn();        
        $where = (array) $where;
        $where["? LIKE CONCAT({$pathColumn}, '%')"] = $path;
        $where["{$pathColumn} != ?"] = $path;        
        $result = $this->fetchAll($where, $order, $count, $offset);
        
        return $result;
    }

    /**
     * Fetches a specified node's all descendants via its path.
     *
     * @param string                            $path   path
     * @param string|array|Zend_Db_Table_Select $where  OPTIONAL An SQL WHERE clause or Zend_Db_Table_Select object.
     * @param string|array                      $order  OPTIONAL An SQL ORDER clause.
     * @param int                               $count  OPTIONAL An SQL LIMIT count.
     * @param int                               $offset OPTIONAL An SQL LIMIT offset.
     * @return Zend_Db_Table_Rowset_Abstract The row results per the Zend_Db_Adapter fetch mode.
     * @throws InvalidArgumentException
     */
    public function fetchDescendants($path, $where = null, $order = null, $count = null, $offset = null) {
        $this->_checkPath($path);
        
        $pathColumn = $this->getPathColumn();
        $where = (array) $where;
        $where["{$pathColumn} LIKE ?"] = $path . '%';
        $where["{$pathColumn} != ?"] = $path;        
        $result = $this->fetchAll($where, $order, $count, $offset);
        
        return $result;
    }

    /**
     * Fetches a specified node's all siblings via its path.
     *
     * @param string                            $path   path
     * @param string|array|Zend_Db_Table_Select $where  OPTIONAL An SQL WHERE clause or Zend_Db_Table_Select object.
     * @param string|array                      $order  OPTIONAL An SQL ORDER clause.
     * @param int                               $count  OPTIONAL An SQL LIMIT count.
     * @param int                               $offset OPTIONAL An SQL LIMIT offset.
     * @return Zend_Db_Table_Rowset_Abstract|null The row results per the Zend_Db_Adapter fetch mode.
     * @throws InvalidArgumentException
     */
    public function fetchSiblings($path, $where = null, $order = null, $count = null, $offset = null) {
        $this->_checkPath($path);
        
        $separator = $this->getSeparator();
        $tokens = explode($separator, trim($path, $separator));
        if (count($tokens) === 1) {
            return null;
        }        
        $parentId = $tokens[count($tokens) - 2];

        $pathColumn = $this->getPathColumn();
        $where = (array) $where;
        $where["{$pathColumn} != ?"] = $path;
        
        $result = $this->fetchChildren($parentId, $where, $order, $count, $offset);

        return $result;
    }
    
    /**
     * Fetch root nodes
     * @param type $where
     * @param type $order
     * @param type $count
     * @param type $offset
     * @return Zend_Db_Table_Rowset_Abstract
     */
    public function fetchRoots($where = null, $order = null, $count = null, $offset = null) {
        $parentIdColumn = $this->getParentIdColumn();
        
        $parentIdOfRoot = $this->getParentIdOfRoot();        
        if ($parentIdOfRoot === null) {
            $where["{$parentIdColumn} IS NULL"] = '';
        } else {
            $where["{$parentIdColumn} = ?"] = $parentIdOfRoot;
        }
        
        return $this->fetchAll($where, $order, $count, $offset);
    }

    /**
     * Checks specified path
     * @param string $path
     * @throws InvalidArgumentException
     */
    private function _checkPath($path) {
        $separator = $this->getSeparator();
        if (strpos($path, $separator) === false
                || substr($path, -1) !== $separator) {
            throw new InvalidArgumentException("Invalid path '{$path}'");
        }
    }
    
    /**
     * 
     * @param int $nodeId
     * @return Zend_Db_Table_Row_Abstract
     * @throws Eos_Db_Table_NodeNotExistException
     */
    private function _getNode($nodeId) {
        $node = $this->find($nodeId)->current();
        if (empty($node)) {
            throw new Eos_Db_Table_NodeNotExistException($nodeId, 'parent not exist');
        }
        
        return $node;
    }
    
    public function insertRoot(array $data) {
        return $this->insert($data);
    }
    
    public function insertChild(array $data, $parentId) {
        return $this->insert($data, $parentId);
    }
    
    /**
     * Add a child
     * @param array $data       Column-value pairs.
     * @param int   $parentId   Indicates the parent node. 
     *          If the child to be added has no parent(root), keep the param as null
     * @return int                  Last inserted id
     */
    public function insert(array $data, $parentId = null) {
        // using DESC result
        $pathColumn = $this->getPathColumn();
        // 万一path字段是NOT NULL而且没有DEFAULT
        // If the column CAN take NULL as a value, the column is defined with an
        // explicit DEFAULT NULL clause.
        // If the column cannot take NULL as the value, MySQL defines the column
        // with no explicit DEFAULT clause.
        $data[$pathColumn] = '';
        
        if ($parentId === null) {
            // insert a root
            $parentId = $this->getParentIdOfRoot();
            $data[$this->getParentIdColumn()] = $parentId;            
            $parentPath = '';
        } else {
            $data[$this->getParentIdColumn()] = $parentId;
            $parent = $this->_getNode($parentId);   
            $parentPath = $parent[$this->getPathColumn()];
        }

        $lastInsertId = parent::insert($data);        
        $path = $parentPath . $lastInsertId . $this->getSeparator();
        $primaryKey = current((array) $this->info(self::PRIMARY));                
        $this->update(array($pathColumn => $path), 
                      array("{$primaryKey} = ?" => $lastInsertId));
                      
        $this->_setLastInsertedPath($path);

        return $lastInsertId;
    }
    
    /**
     * 把结点$sourceId移动到结点$targetId中
     * @param int $sourceId   要移动的源结点ID
     * @param int $targetId   作为移动目标的结点ID
     * @param array $newData  在移动过程中要更新的数据
     * @return array          被移动结点的ID列表
     * @throws Exception
     * @throws App_Db_InvalidTargetException
     * @todo Level changed
     */
    public function move($sourceId, $targetId, $newData = array()) {
        if ($sourceId == $targetId) {
            return array();
        }

        $source = $this->_getNode($sourceId);
        $sourcePath = $source[$this->getPathColumn()];

        $targetParent = $this->_getNode($targetId);
        $targetParentPath = $targetParent[$this->getPathColumn()];

        if (strpos($targetParentPath, $sourcePath) === 0) {
            throw new Eos_Db_Table_InvalidTargetException('the target can not be a child of the source');
        }
        
        $parentIdColumn = $this->getParentIdColumn();
        $pathColumn = $this->getPathColumn();
        
        $newPath = $targetParentPath . $sourceId . $this->getSeparator();
        $sourceNewData = array(
            $parentIdColumn => $targetId,
            $pathColumn     => $newPath,
        ) + $newData;
       
        $primaryKey = current($this->info(self::PRIMARY));
        $this->update($sourceNewData, array("{$primaryKey} = ?" => $sourceId));

        $descendants = $this->fetchDescendants($sourcePath);
        $movedIds = array($sourceId);
        foreach ($descendants as $_descendant) {
            $movedIds[] = $_descendant->{$primaryKey};
            $_descendant->{$pathColumn} = str_replace($sourcePath, 
                                                      $newPath, 
                                                      $_descendant->{$pathColumn});
            if (!empty($newData)) {
                foreach ($newData as $_field => $_value) {
                    $_descendant->{$_field} = $_value;
                }
            }                                                      
            $_descendant->save();
        }

        return $movedIds;
    }

        /**
     * Promote one node to root node
     * @param int $nodeId
     * @param type $newData
     */
    public function promoteToRoot($nodeId, $newData = array()) {
        $children = $this->fetchChildren($nodeId);
        $primaryKey = current($this->info(self::PRIMARY));
        $movedIds = array();
        
        // promote the node to root node
        $rootData = array(
            $this->getParentIdColumn()  => $this->getParentIdOfRoot(),
            $this->getPathColumn()      => $nodeId . $this->getSeparator()
        ) + $newData;
        $where = array("{$primaryKey} = ?" => $nodeId);
        $this->update($rootData, $where);
        $movedIds[] = $nodeId;
        
        // move its children into it
        foreach ($children as $child) {
            $_movedIds = $this->move($child[$primaryKey], $nodeId, $newData);
            $movedIds = array_merge($movedIds, $_movedIds);
        }
        
        return $movedIds;
    }
}
