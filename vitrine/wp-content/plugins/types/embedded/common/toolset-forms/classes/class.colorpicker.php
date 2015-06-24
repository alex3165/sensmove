<?php
/**
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/embedded/common/toolset-forms/classes/class.colorpicker.php $
 * $LastChangedDate: 2015-03-25 12:38:40 +0000 (Wed, 25 Mar 2015) $
 * $LastChangedRevision: 1120400 $
 * $LastChangedBy: iworks $
 *
 */
require_once 'class.field_factory.php';

/**
 * Description of class
 *
 * @author Srdjan
 */
class WPToolset_Field_Colorpicker extends FieldFactory
{
    public function init()
    {
        if ( !is_admin() ) {
            wp_enqueue_style( 'wp-color-picker' );
            wp_enqueue_script(
                'iris',
                admin_url( 'js/iris.min.js' ),
                array( 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch' ),
                false,
                1
            );
            wp_enqueue_script(
                'wp-color-picker',
                admin_url( 'js/color-picker.min.js' ),
                array( 'iris' ),
                false,
                1
            );
            $colorpicker_l10n = array(
                'clear' => __( 'Clear' ),
                'defaultString' => __( 'Default', 'wpv-views' ),
                'pick' => __( 'Select', 'wpv-views' )." Color"
            );
            wp_localize_script( 'wp-color-picker', 'wpColorPickerL10n', $colorpicker_l10n );
        }
        wp_register_script(
            'wptoolset-field-colorpicker',
            WPTOOLSET_FORMS_RELPATH . '/js/colorpicker.js',
            array('iris'),
            WPTOOLSET_FORMS_VERSION,
            true
        );
        wp_enqueue_script( 'wptoolset-field-colorpicker' );
        $this->set_placeholder_as_attribute();
    }

    static public function registerScripts()
    {
    }

    public function enqueueScripts()
    {

    }

    public function addTypeValidation($validation) {
        $validation['hexadecimal'] = array(
            'args' => array(
                'hexadecimal'
            ),
            'message' => __( 'You can add valid hexadecimal.', 'wpv-views' ),
        );
        return $validation;
    }

    public function metaform()
    {
        $validation = $this->getValidationData();
        $validation = $this->addTypeValidation($validation);
        $this->setValidationData($validation);

        $attributes = $this->getAttr();
        if ( isset($attributes['class'] ) ) {
            $attributes['class'] .= ' ';
        } else {
            $attributes['class'] = '';
        }
        $attributes['class'] = 'js-wpt-colorpicker';

        $form = array();
        $form['name'] = array(
            '#type' => 'textfield',
            '#title' => $this->getTitle(),
            '#description' => $this->getDescription(),
            '#value' => $this->getValue(),
            '#name' => $this->getName(),
            '#attributes' => $attributes,
            '#validate' => $validation,
            '#after' => '',
            '#repetitive' => $this->isRepetitive(),
        );
        return $form;
    }

    public static function filterValidationValue($value)
    {
        if ( isset( $value['datepicker'] ) ) {
            return $value['datepicker'];
        }
        return $value;
    }
}
