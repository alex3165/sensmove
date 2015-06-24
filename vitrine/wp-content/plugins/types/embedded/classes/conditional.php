<?php
/*
 * Conditional class
 * 
 * Few break-points summarized:
 * 1. Wrapping fields when in form
 * 2. Filtering AJAX check
 * 3. Calling JS
 */

/**
 * Conditional class.
 * 
 * Very useful, should be used to finish small tasks for conditional field.
 * 
 * Example:
 * 
 * // Setup field
 * global $wpcf;
 * $my_field = new WPCF_Conditional();
 * $my_field->set($wpcf->post, wpcf_admin_fields_get_field('image'));
 * 
 * // Use it
 * $is_valid = $my_field->evaluate();
 * 
 * Generic instance can be found in global $wpcf.
 * global $wpcf;
 * $wpcf->conditional->set(...);
 * 
 * @since Types 1.2
 * @package Types
 * @subpackage Classes
 * @version 0.1
 * @category core
 * @author srdjan <srdjan@icanlocalize.com>
 */
class WPCF_Conditional extends WPCF_Field
{

    /**
     * Holds all processed fields using one instance.
     */
    var $collected = null;

    /**
     * Holds all triggers on which check should fire.
     * 
     * @var type 
     */
    var $triggers = null;

    /**
     * Marks if currently processed field is valid.
     * 
     * 
     * @var type 
     */
    var $passed = true;

    /**
     * Trigger CSS class.
     * @var type 
     */
    var $css_class_trigger = 'wpcf-conditional-check-trigger';

    /**
     * Field CSS class.
     * @var type 
     */
    var $css_class_field = 'wpcf-conditional';

    /**
     * Evaluate object.
     * 
     * @var type 
     */
    var $evaluate = null;

    function __construct() {
        parent::__construct();
    }

    function set( $post, $cf ) {
        parent::set( $post, $cf );
    }

    /**
     * Collect all fields and conditions.
     */
    function collect() {
        if ( is_null( $this->triggers ) ) {
            $this->collected = array();
            $this->triggers = array();
            $fields = WPCF_Fields::getFields();
            if ( is_array( $fields ) && !empty( $fields ) ) {
                foreach ( $fields as $f_id => $f ) {
                    if ( !empty( $f['data']['conditional_display']['conditions'] ) ) {
                        foreach ( $f['data']['conditional_display']['conditions'] as $condition ) {
                            $this->collected[$f_id] = $condition;
                            if ( !empty( $condition['field'] ) ) {
                                $this->triggers[$condition['field']][$f_id][] = $condition;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Checks if field is conditional.
     * 
     * @param type $field
     * @return type 
     */
    function is_conditional( $field = array() ) {
        if ( is_array( $field ) ) {
            return !empty( $field['data']['conditional_display']['conditions'] );
        } else {
            $this->collect();
            $field_id = $this->__get_slug_no_prefix( strval( $field ) );
            return isset( $this->collected[$field_id] );
        }
    }

    /**
     * Checks if field is check trigger.
     * 
     * @param type $field
     * @return type 
     */
    function is_trigger( $field = array() ) {
        $this->collect();
        return !empty( $this->triggers[$field['id']] );
    }

    /**
     * Enqueues scripts. 
     */
    function add_js() {
        wp_enqueue_script( 'types-conditional' );
        wpcf_admin_add_js_settings( 'wpcfConditionalVerify_nonce',
                wp_create_nonce( 'cd_verify' )
        );
    }

    /**
     * Wraps each trigger check field with $this->css_class_trigger
     * and corespondive classes.
     * 
     * @param type $element
     * @return type 
     */
    function wrap_trigger( $element = array() ) {

        // Set attribute class to $this->css_class_trigger
        if ( isset( $element['#attributes']['class'] ) ) {
            $element['#attributes']['class'] .= ' ' . $this->css_class_trigger;
        } else {
            $element['#attributes']['class'] = $this->css_class_trigger;
        }

        /*
         * 
         * Radios needs per option
         */
        if ( $element['#type'] == 'radios'
                && ( isset( $element['#options'] ) && is_array( $element['#options'] )) ) {
            foreach ( $element['#options'] as $_k => $_v ) {
                if ( isset( $_v['#attributes']['class'] ) ) {
                    $element['#options'][$_k]['#attributes']['class'] .= ' ' . $this->css_class_trigger;
                } else {
                    $element['#options'][$_k]['#attributes']['class'] = $this->css_class_trigger;
                }
            }
        }

        return apply_filters( 'types_conditional_field_trigger', $element, $this );
    }

    /**
     * Wraps each field with $this->css_class_field and corespondive classes.
     * 
     * @param type $element
     * @return type 
     */
    function wrap( $element = array() ) {
        if ( !empty( $element ) ) {
            $passed = $this->evaluate();
            if ( !$passed ) {
                $wrap = '<div class="' . $this->css_class_field . ' '
                        . $this->css_class_field . '-failed" style="display:none;">';
            } else {
                $wrap = '<div class="' . $this->css_class_field . ' '
                        . $this->css_class_field . '-passed">';
            }
            if ( isset( $element['#before'] ) ) {
                $element['#before'] = $wrap . $element['#before'];
            } else {
                $element['#before'] = $wrap;
            }
            if ( isset( $element['#after'] ) ) {
                $element['#after'] = $element['#after'] . '</div>';
            } else {
                $element['#after'] = '</div>';
            }
        }

        return apply_filters( 'types_conditional_field', $element, $this );
    }

    /**
     * Evaluates if check passed.
     * 
     * @return type 
     */
    function evaluate() {
        WPCF_Loader::loadClass( 'evaluate' );
        $this->passed = WPCF_Evaluate::evaluate( $this );
        return $this->passed;
    }

    /**
     * Processes AJAX call 'cd_verify'.
     * 
     * @param type $data
     * @return type
     */
    function ajaxVerify( $data ) {
        WPCF_Loader::loadClass( 'helper.ajax' );
        return WPCF_Helper_Ajax::conditionalVerify( $data );
    }

}