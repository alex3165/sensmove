<?php
/**
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/embedded/common/toolset-forms/classes/class.submit.php $
 * $LastChangedDate: 2015-03-25 12:38:40 +0000 (Wed, 25 Mar 2015) $
 * $LastChangedRevision: 1120400 $
 * $LastChangedBy: iworks $
 *
 */
require_once 'class.textfield.php';

class WPToolset_Field_Submit extends WPToolset_Field_Textfield
{

    public function metaform()
    {
        $attributes = $this->getAttr();

        $metaform = array();
        $metaform[] = array(
            '#type' => 'submit',
            '#title' => $this->getTitle(),
            '#description' => $this->getDescription(),
            '#name' => $this->getName(),
            '#value' => esc_attr(__($this->getValue(), 'wpv-views')),
            '#validate' => $this->getValidationData(),
            '#attributes' => array(
                'class' => '',
            ),
        );
        if (array_key_exists( 'class', $attributes )) {
            $metaform[0]['#attributes']['class'] = $attributes['class'];
        }
        if ( array_key_exists( 'use_bootstrap', $this->_data ) && $this->_data['use_bootstrap'] ) {
            $metaform[0]['#attributes']['class'] .= ' btn btn-primary';
        }
        $this->set_metaform($metaform);
        return $metaform;
    }

}
