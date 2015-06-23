<?php
/**
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/embedded/common/toolset-forms/classes/class.select.php $
 * $LastChangedDate: 2015-02-18 14:28:53 +0000 (Wed, 18 Feb 2015) $
 * $LastChangedRevision: 1093394 $
 * $LastChangedBy: iworks $
 *
 */
require_once 'class.field_factory.php';

/**
 * Description of class
 *
 * @author Srdjan
 */
class WPToolset_Field_Select extends FieldFactory
{

    public function metaform() {
        $value = $this->getValue();
        $data = $this->getData();
        $attributes = $this->getAttr();

        $form = array();
        $options = array();
        if (isset($data['options'])) {
            foreach ( $data['options'] as $option ) {
                $one_option_data = array(
                    '#value' => $option['value'],
                    '#title' => stripslashes($option['title']),
                );
                /**
                 * add default value if needed
                 * issue: frontend, multiforms CRED
                 */
                if ( array_key_exists( 'types-value', $option ) ) {
                    $one_option_data['#types-value'] = $option['types-value'];
                }
                /**
                 * add to options array
                 */
                $options[] = $one_option_data;
            }
        }

        /**
         * for user fields we reset title and description to avoid double 
         * display
         */
        $title = $this->getTitle();
        if ( empty($title) ) {
            $title = $this->getTitle(true);
        }
        $options = apply_filters( 'wpt_field_options', $options, $title, 'select' );
        /**
         * default_value
         */
        if ( !empty( $value ) || $value == '0' ) {
            $data['default_value'] = $value;
        }

        $is_multiselect = array_key_exists('multiple', $attributes) && 'multiple' == $attributes['multiple'];
        $default_value = isset( $data['default_value'] ) ? $data['default_value'] : null;
        //Fix https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/189219391/comments
        if ($is_multiselect) {
            $default_value = new RecursiveIteratorIterator(new RecursiveArrayIterator($default_value));
            $default_value = iterator_to_array($default_value,false);
        }
        //##############################################################################################

        /**
         * metaform
         */
        $form[] = array(
            '#type' => 'select',
            '#title' => $this->getTitle(),
            '#description' => $this->getDescription(),
            '#name' => $this->getName(),
            '#options' => $options,
            '#default_value' => $default_value,
            '#multiple' => $is_multiselect,
            '#validate' => $this->getValidationData(),
            '#class' => 'form-inline',
            '#repetitive' => $this->isRepetitive(),
        );

        return $form;
    }

}
