<?php
/**
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/embedded/common/toolset-forms/classes/class.textfield.php $
 * $LastChangedDate: 2015-03-02 10:49:00 +0000 (Mon, 02 Mar 2015) $
 * $LastChangedRevision: 1103173 $
 * $LastChangedBy: iworks $
 *
 */
require_once "class.field_factory.php";
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class
 *
 * @author Franko
 */
class WPToolset_Field_Textfield extends FieldFactory
{
    public function metaform()
    {
        $metaform = array();
        $metaform[] = array(
            '#type' => 'textfield',
            '#title' => $this->getTitle(),
            '#description' => $this->getDescription(),
            '#name' => $this->getName(),
            '#value' => $this->getValue(),
            '#validate' => $this->getValidationData(),
            '#repetitive' => $this->isRepetitive(),
            '#attributes' => $this->getAttr(),
            'wpml_action' => $this->getWPMLAction(),
        );
        return $metaform;
    }

}
