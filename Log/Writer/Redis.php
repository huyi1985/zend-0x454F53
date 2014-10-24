<?php

class Eos_Log_Writer_Redis extends Zend_Log_Writer_Abstract {

    /**
     *
     * @var string
     */
    protected $_key;
    
    /**
     *
     * @var Redis
     */
    protected $_redis;

    /**
     * @param string $key
     * @param Redis $redis
     */
    public function __construct($key, Redis $redis) {
        assert('!empty($key)');
        assert('!empty($redis)');
        
        $this->_key = $key;       
        $this->_redis = $redis;
    }

    /**
     * Formatting is not possible on this writer
     *
     * @return void
     * @throws Zend_Log_Exception
     */
    public function setFormatter($formatter) {
        require_once 'Zend/Log/Exception.php';
        throw new Zend_Log_Exception(get_class($this) . ' does not support formatting');
    }

    /**
     * Remove reference to database adapter
     *
     * @return void
     */
    public function shutdown() {
        $this->_redis->close();
        $this->_redis = null;
    }

    /**
     * Write a message to the log.
     *
     * @param  array  $event  event data
     * @return void
     * @throws Zend_Log_Exception
     */
    protected function _write($event) {
        if ($this->_redis === null) {
            require_once 'Zend/Log/Exception.php';
            throw new Zend_Log_Exception('Redis is null');
        }

        $this->_redis->rPush($this->_key, json_encode($event));
    }

    /**
     * 
     * @param array|Zend_Config $config
     * @return Zend_Log_Writer_Redis
     */
    public static function factory($config) {
        $config = self::_parseConfig($config);
        $config = array_merge(array(
            'key'       => null,
            'redis'     => null,
        ), $config);

        return new self(
            $config['key'],
            $config['redis']
        );
    }

}
