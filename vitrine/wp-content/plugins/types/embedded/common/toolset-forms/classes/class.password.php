<?php
/**
 *
 * $HeadURL: https://www.onthegosystems.com/misc_svn/common/trunk/toolset-forms/classes/class.password.php $
 * $LastChangedDate: 2014-07-10 11:46:40 +0300 (Чт, 10 июл 2014) $
 * $LastChangedRevision: 24820 $
 * $LastChangedBy: francesco $
 *
 */
require_once 'class.field_factory.php';

/**
 * Generic Cred field: password
 *
 * @author Gen
 */
class WPToolset_Field_Password extends FieldFactory
{

    public function metaform() {
        $attributes = $this->getAttr();
        
        $metaform = array();
        $metaform[] = array(
            '#type' => 'password',
            '#title' => $this->getTitle(),
            '#description' => $this->getDescription(),
            '#name' => $this->getName(),
            '#value' => $this->getValue(),
            '#validate' => $this->getValidationData(),
            '#repetitive' => $this->isRepetitive(),
            '#attributes' => $attributes
        );
        return $metaform;
    }

}
