<?php

/**
 * 提供一系列静态方法，用于存取登录用户（已通过认证的用户）的信息
 */
class Eos_Login_Storage {

    /**
     * 获得登录用户的信息集合
     * 
     * @return stdClass     因为我们使用了Zend_Auth_Adapter_DbTable
     * @throws Exception
     * @see Basemanage_Service_Login::authenticate()
     */
    private static function _getIdentity() {
        $auth = Zend_Auth::getInstance();
                         //->setStorage(new Zend_Auth_Storage_Session('EOS_Auth'));
        
        /* @var $identity stdClass */
        $identity = $auth->getIdentity();        
        if ($identity === null) {
            throw new Exception('还没有登录。<a href="/login">重新登录</a>');
        }
        
        return $identity;
    }

    /**
     * 获取用户Id
     * @return string
     */
    public static function getUserId() {
        return self::_getIdentity()->userId;
    }
    
    /**
     * 获得用户名
     * @return unknown_type
     */
    public static function getUsername() {
        return self::_getIdentity()->userName;
    }

    /**
     * 获得邮箱
     */
    public static function getEmail() {
        return self::_getIdentity()->email;
    }

    /**
     * 获得真实姓名
     */
    public static function getName() {
        return self::_getIdentity()->trueName;
    }
    
    /**
     * 获得真实姓名
     * @return string
     */
    public static function getTrueName() {
        return self::getName();
    }
    
    public static function getDepartmentName() {
        return self::_getIdentity()->departmentName;
    }
    
    public static function getDepartmentId() {
        return self::_getIdentity()->departmentId;
    }

    public static function getSessionStorage() {
        return new Zend_Session_Namespace("EOS");
    }

    public static function writeSessionStorage($key, $value) {
        $storage = self::getSessionStorage();
        $storage->$key = $value;
    }

    public static function getSessionValueByKey($key) {
        return self::getSessionStorage()->$key;
    }

}
