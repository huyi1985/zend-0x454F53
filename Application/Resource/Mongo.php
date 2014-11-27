<?php

/**
 * MongoDB application resource
 *
 * Example configuration:
 *
 * Resource for settings MongoDB connection options
 *
 * @author JackalHu kgo_yoi@hotmail.com
 * @see http://www.zendcasts.com/creating-custom-application-resources/2011/06/
 */
class Eos_Application_Resource_Mongo extends Zend_Application_Resource_ResourceAbstract {

    public function init() {
        Shanty_Mongo::addConnections($this->getOptions());
    }

}
