<?php
require_once 'class.textfield.php';

class WPToolset_Field_Hidden extends WPToolset_Field_Textfield
{
    public function metaform() {
        $metaform = array();
        $metaform[] = array(
            '#type' => 'hidden',
            '#title' => $this->getTitle(),
            '#description' => $this->getDescription(),
            '#name' => $this->getName(),
            '#value' => $this->getValue(),
            '#validate' => $this->getValidationData()
        );
        $this->set_metaform($metaform); 
        return $metaform;
    }

}
