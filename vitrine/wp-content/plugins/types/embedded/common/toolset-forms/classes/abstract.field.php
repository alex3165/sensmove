<?php
/**
 * 
 * Field Abstraction
 * @author onTheGo System
 *
 */
abstract class FieldAbstract {
    //global name of field
    protected $_nameField;
    //field config
    protected $_data;
    //config for enlimbo
    protected $_metaform;                    
    
    abstract public function init();
    abstract public function set_nameField($nameField); 
    abstract public function get_nameField(); 
    abstract public function set_data($data);
    abstract public function get_data();    
    abstract public function set_metaform($metaform);
    abstract public function get_metaform();    
    
    abstract public function getId();
    abstract public function getName();
    abstract public function getType();
    abstract public function getValue();
    abstract public function getAttr();
    abstract public function getTitle();
    abstract public function getDescription();    
    abstract public function getData();
    abstract public function getValidationData();
    abstract public function setValidationData($validation);
}
