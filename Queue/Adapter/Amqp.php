<?php

/**
 * A Zend_Queue Adapter for Amqp
 *
 * @author huyi
 */
class Eos_Queue_Adapter_Amqp extends Zend_Queue_Adapter_AdapterAbstract {

    /**
     *
     * @var AMQPConnection
     */
    private $_connection;
    
    /**
     *
     * @var AMQPChannel
     */
    private $_channel;
    
    /**
     *
     * @var AMQPExchange
     */
    private $_exchange = null;
    
    
    /**
     *
     * @var AMQPQueue
     */
    private $_amqpQueue = null;
    
    /* ******************************************************************
     * Constructor / Destructor
     * ******************************************************************* */

    /**
     * Constructor
     *
     * @param  array|Zend_Config $options
     * @param  null|Zend_Queue $queue
     * @return void
     */
    public function __construct($options, Zend_Queue $queue = null) {
        if (!extension_loaded('amqp')) {
            require_once 'Zend/Queue/Exception.php';
            throw new Zend_Queue_Exception('amqp extension does not appear to be loaded');
        }

        parent::__construct($options, $queue);

        $driverOptions = $this->_options['driverOptions'];
        $host = $driverOptions['host'];

        $this->_connection = new AMQPConnection($driverOptions);
        $result = $this->_connection->connect();
        if ($result === false) {
            throw new Zend_Queue_Exception("Could not connect to amqp({$host})");
        }
        
        $this->_channel = new AMQPChannel($this->_connection);
        
        // 创建交换机
        $exchange = new AMQPExchange($this->_channel);
        $exchange->setName($this->_options['exchangeName']);
        $exchange->setType(AMQP_EX_TYPE_DIRECT);
        $exchange->setFlags(AMQP_DURABLE | AMQP_AUTODELETE); // 交换器进行持久化，即 RabbitMQ 重启后会自动重建

        if (method_exists($exchange, 'declareExchange')) {
            $exchange->declareExchange();
        } else {
            $exchange->declare();
        }

        $this->_exchange = $exchange;
    }

    /**
     * Destructor
     *
     * @return void
     */
    public function __destruct() {
        if ($this->_connection instanceof AMQPConnection) {
            $this->_connection->disconnect();
        }
    }

    /* ******************************************************************
     * Queue management functions
     * ******************************************************************* */

    /**
     * Does a queue already exist?
     *
     * Throws an exception if the adapter cannot determine if a queue exists.
     * use isSupported('isExists') to determine if an adapter can test for
     * queue existance.
     *
     * @param  string $name
     * @return boolean
     * @throws Zend_Queue_Exception
     */
    public function isExists($name) {
        throw new Zend_Queue_Exception('isExists() is not supported in this adapter');
    }

    /**
     * Create a new queue
     *
     * Visibility timeout is how long a message is left in the queue "invisible"
     * to other readers.  If the message is acknowleged (deleted) before the
     * timeout, then the message is deleted.  However, if the timeout expires
     * then the message will be made available to other queue readers.
     *
     * @param  string  $name    queue name
     * @param  integer $timeout default visibility timeout
     * @return boolean
     * @throws Zend_Queue_Exception
     */
    public function create($name, $timeout = null) {
        throw new Zend_Queue_Exception('create() is not supported in this adapter');
    }

    /**
     * Delete a queue and all of it's messages
     *
     * Returns false if the queue is not found, true if the queue exists
     *
     * @param  string  $name queue name
     * @return boolean
     * @throws Zend_Queue_Exception
     */
    public function delete($name) {
        throw new Zend_Queue_Exception('delete() is not supported in this adapter');
    }

    /**
     * Get an array of all available queues
     *
     * Not all adapters support getQueues(), use isSupported('getQueues')
     * to determine if the adapter supports this feature.
     *
     * @return array
     * @throws Zend_Queue_Exception
     */
    public function getQueues() {
        throw new Zend_Queue_Exception('getQueues() is not supported in this adapter');
    }

    /**
     * Return the approximate number of messages in the queue
     *
     * @param  Zend_Queue $queue
     * @return integer
     * @throws Zend_Queue_Exception (not supported)
     */
    public function count(Zend_Queue $queue = null) {
        throw new Zend_Queue_Exception('count() is not supported in this adapter');
    }

    /* ******************************************************************
     * Messsage management functions
     * ******************************************************************* */

    /**
     * Send a message to the queue
     *
     * @param  string     $message Message to send to the active queue
     * @param  Zend_Queue $queue
     * @return Zend_Queue_Message
     * @throws Zend_Queue_Exception
     */
    public function send($message, Zend_Queue $queue = null) {
        if ($queue === null) {
            $queue = $this->_queue;
        }

        $message = (string) $message;

        $result = $this->_exchange->publish($message, $this->_options['routingKey']);
        if ($result === false) {
            throw new Zend_Queue_Exception('Failed to insert message into queue:' . $queue->getName());
        }

        $options = array(
            'queue' => $queue,
            'data'  => array($message),
        );

        $classname = $queue->getMessageClass();
        if (!class_exists($classname)) {
            require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($classname);
        }
        return new $classname($options);
    }

    /**
     * Get messages in the queue
     *
     * @param  integer    $maxMessages  Maximum number of messages to return
     * @param  integer    $timeout      Visibility timeout for these messages
     * @param  Zend_Queue $queue
     * @return Zend_Queue_Message_Iterator
     * @throws Zend_Queue_Exception
     */
    public function receive($maxMessages = null, $timeout = null, Zend_Queue $queue = null) {
        if ($maxMessages === null) {
            $maxMessages = 1;
        }

        if ($timeout === null) {
            $timeout = 5;
        }
        
        if ($queue === null) {
            $queue = $this->_queue;
        }
        
        $amqpQueue = new AMQPQueue($this->_channel);
        $amqpQueue->setName($this->_options['queueName']);
        $amqpQueue->setFlags(AMQP_DURABLE); // 持久化
        if (method_exists($amqpQueue, 'declareQueue')) {
            $amqpQueue->declareQueue();
        } else {
            $amqpQueue->declare();
        }

        $msgs = array();
        if ($maxMessages > 0) {
            for ($i = 0; $i < $maxMessages; $i++) {
                $message = $amqpQueue->get(AMQP_NOPARAM);
                if ($message === false) {
                    continue;
                }
                
                $data = array(
                    'handle' => md5(uniqid(rand(), true)),
                    'body'   => $message->getBody(),
                );                
                $msgs[] = $data;
                
                $amqpQueue->ack($message->getDeliveryTag());
            }
        }

        $options = array(
            'queue'        => $queue,
            'data'         => $msgs,
            'messageClass' => $queue->getMessageClass(),
        );
        
        $classname = $queue->getMessageSetClass();
        if (!class_exists($classname)) {
            require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($classname);
        }
        return new $classname($options);
    }

    /**
     * Delete a message from the queue
     *
     * Returns true if the message is deleted, false if the deletion is
     * unsuccessful.
     *
     * @param  Zend_Queue_Message $message
     * @return boolean
     * @throws Zend_Queue_Exception (unsupported)
     */
    public function deleteMessage(Zend_Queue_Message $message) {
        require_once 'Zend/Queue/Exception.php';
        throw new Zend_Queue_Exception('deleteMessage() is not supported in  ' . get_class($this));
    }

    /* ******************************************************************
     * Supporting functions
     * ******************************************************************* */

    /**
     * Return a list of queue capabilities functions
     *
     * $array['function name'] = true or false
     * true is supported, false is not supported.
     *
     * @param  string $name
     * @return array
     */
    public function getCapabilities() {
        return array(
            'create'    => false,
            'delete'    => false,
            'send'      => true,
            'receive'   => true,
            'deleteMessage' => false,
            'getQueues' => false,
            'count' => false,
            'isExists' => false,
        );
    }   

}
