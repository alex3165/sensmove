<?php
require_once 'class.textfield.php';

/**
 * Description of class
 *
 * @author Franko
 */
class WPToolset_Field_Button extends WPToolset_Field_Textfield
{

    public function metaform() {
        $config = $this->_config;
        $metaform = array();
        $metaform[] = array(
            '#type' => 'button',
            '#title' => $this->title,
            '#description' => $this->description,
            '#name' => $this->name,
            '#value' => $this->value,
            '#validate' => $config['validation']
        );
        return $metaform;
    }
}
