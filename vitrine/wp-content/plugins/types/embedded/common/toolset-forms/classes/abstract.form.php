<?php

abstract class FormAbstract {
    /*
     * Create and return form as string
     */ 
    abstract public function createForm($nameForm); 
    /*
     * Create and display form
     */ 
    abstract public function displayForm($nameForm); 
    /*
     * Check id forms
     */
    abstract public function formNameExists(&$nameForm);
    /*
     * Get Field Object from type
     */
    abstract public function getFieldObject($data, $global_name_field, $value);
    /*
     * Add field to a form
     */
    abstract public function addFormField($data);
    /*
     *  Loads field (queue script and styles)
     */
    abstract public function loadField( $data, $global_name_field, $value );
    /*
     *  Single field form (not added to form fields)
     */
    abstract public function metaform( $config, $global_name_field, $value );
    /*
     *  Single field edit  form (not added to form fields)
     */
    // abstract public function editform( $config );
    /*
     *  Checks if validation is required and inits it per form
     */
    abstract protected function _checkValidation( $config );
    /*
     * checkConditional
     */
    abstract protected function _checkConditional( $config );
    /*
     * repetitive
     */
    abstract protected function _repetitive();
}
