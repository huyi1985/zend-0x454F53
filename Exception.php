<?php

/**
 * Description of ApiException
 *
 * @author huyi
 */
class Eos_Exception extends Exception {
    /**
     * @override
     * @param int   $code           异常代码
     * @param array $replacements   替代异常信息中占位符的值。Key为占位符（包括前缀“:”），Value为替代值
     * @throws Exception            当传入了未定义的异常代码
     */
    public function __construct($code, $replacements = array()) {
        $config = require PROJECT_ROOT . '/configs/exception.php';
        $config = new Zend_Config($config);
        $exceptions = $config->exceptions->toArray();
        
        if (!isset($exceptions[$code])) {
            throw new Exception("未定义的异常代码（{$code}）");
        }
        
        $message = $exceptions[$code];
        if (!empty($replacements)) {
            $message = str_replace(array_keys($replacements), 
                                   array_values($replacements), $message);
        }
        
        parent::__construct($message, $code);
    }
}
