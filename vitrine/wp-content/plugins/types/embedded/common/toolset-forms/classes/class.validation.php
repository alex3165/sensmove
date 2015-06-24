<?php
/*
 * Libraries
 * - CakePHP library for PHP validation
 * - jQuery Validation plugin for JS validation
 *
 * Flow
 * - Hooks to form filtering to collect data
 * - Filters data-wpt-validation (adds array of rules) to form element
 * - Queues scripts if any field is conditional
 * - JS is initialized and checks performed
 * - On form submission PHP checks are performed also (used in specific context,
 * on client's side (CRED or Types) for e.g. aborting saving/processing form)
 */

/**
 * Class description
 *
 * @author Srdjan
 */
class WPToolset_Forms_Validation
{
    private $__formID;
    protected $_cake;
    protected $_rules_map = array(
        'rangelength' => 'between',
        'number' => 'numeric'
    );

    function __construct( $formID ){
        $this->__formID = trim( $formID, '#' );
        // Register
        wp_register_script( 'wptoolset-form-jquery-validation',
                WPTOOLSET_FORMS_RELPATH . '/lib/js/jquery-form-validation/jquery.validate.js',
                array('jquery'), WPTOOLSET_FORMS_VERSION, true );
        wp_register_script( 'wptoolset-form-jquery-validation-additional',
                WPTOOLSET_FORMS_RELPATH . '/lib/js/jquery-form-validation/additional-methods.min.js',
                array('wptoolset-form-jquery-validation'),
                WPTOOLSET_FORMS_VERSION, true );
        wp_register_script( 'wptoolset-form-validation',
                WPTOOLSET_FORMS_RELPATH . '/js/validation.js',
                array('wptoolset-form-jquery-validation-additional', 'underscore'),
                WPTOOLSET_FORMS_VERSION, true );

        // Filter JS validation data
        add_action( 'wptoolset_forms_field_js_validation_data_' . $this->__formID,
                array($this, 'filterJsValidation') );
        // Filter form field PHP validation
        add_filter( 'wptoolset_form_' . $this->__formID . '_validate_field',
                array($this, 'filterFormField'), 10, 2 );
        // Render classes
        add_action('wptoolset_field_class', array($this, 'actionFieldClass') );

        // Render settings
        add_action( 'admin_print_footer_scripts', array($this, 'renderJsonData'), 30 );
        add_action( 'wp_footer', array($this, 'renderJsonData'), 30 );

        wp_enqueue_script( 'wptoolset-form-validation' );
    }

    /**
     * Adjusts validation data for JS processing (data-wpt-validate HTML attribute)
     *
     * @param type $rules
     * @return type
     */
    public function filterJsValidation( $rules ) {
        foreach ( $rules as $r => $rule ) {
            // Possible change of rule (like DateITA)
            $_r = apply_filters( 'wptoolset_validation_rule_js', $r );
            if ( $_r != $r ) {
                $rules[$_r] = $rule;
                unset( $rules[$r] );
                continue;
            }
        }
        foreach ( $rules as $r => &$rule ) {
            $rule['args'] = apply_filters( 'wptoolset_validation_args_js',
                        $rule['args'], $r );
            // Remove value in args - search string '$value' or unset first element
            $replace = array_search( '$value', $rule['args'] );
            if ( $replace !== false ) {
                unset( $rule['args'][$replace] );
            } else {
                array_shift( $rule['args'] );
            }
//            unset( $rule['message'] );
        }
        return $rules;
    }

    /**
     * Form PHP validation.
     *
     * Called from Form_Factory or save_post hook.
     * Form Factory should check if element has 'error' property (WP_Error)
     * and use WP_Error::get_error_message() to display error message
     *
     * @param type $element
     * @param type $value
     * @return type
     */
    public function filterFormField( $element, $value ) {
        $rules = $this->_parseRules( $element['#validate'], $value );
        // If not required but empty - skip
        if ( !isset( $rules['required'] )
                && ( is_null( $value ) || $value === false || $value === '' ) ) {
            return true;
        }
        try {
            $errors = array();
            foreach ( $rules as $rule => $args ) {
                if ( !$this->validate( $rule, $args['args'] ) ) {
                    $errors[] = $args['message'];
                }
            }
            if ( !empty( $errors ) ) {
                throw new Exception();
            }
        } catch ( Exception $e ) {
            $element['error'] =  new WP_Error( __CLASS__ . '::' . __METHOD__,
                    'Field not validated', $errors );
        }
        return $element;
    }

    /**
     * Bulk PHP validation.
     *
     * @param type $field Field class instance
     * @param type $value
     * @return \WP_Error|boolean
     * @throws Exception
     */
    public function validateField( $field ) {
        $value = apply_filters( 'wptoolset_validation_value_' . $field->getType(), $field->getValue() );
        $rules = $this->_parseRules( $field->getValidationData(), $value );
        // If not required but empty - skip
        if ( !isset( $rules['required'] )
                && ( is_null( $value ) || $value === false || $value === '' ) ) {
            return true;
        }

        try {
            $errors = array();
            foreach ( $rules as $rule => $args ) {
                if ( !$this->validate( $rule, $args['args'] ) ) {
                    $errors[] = $field->getTitle() .  ' ' . $args['message'];
                }
            }
            if ( !empty( $errors ) ) {
                throw new Exception();
            }
        } catch ( Exception $e ) {
            return new WP_Error( __CLASS__ . '::' . __METHOD__,
                    'Field not validated', $errors );
        }
        return true;
    }

    protected function _parseRules( $rules, $value ) {
        $_rules = array();
        foreach ( $rules as $rule => $args ) {
            $rule = apply_filters( 'wptoolset_validation_rule_php', $rule );
            $args['args'] = apply_filters( 'wptoolset_validation_args_php',
                    $args['args'], $rule );
            // Set value in args - search string '$value' or replace first element
            $replace = array_search( '$value', $args['args'] );
            if ( $replace !== false ) {
                $args['args'][$replace] = $value;
            } else {
                $args['args'][0] = $value;
            }
            $_rules[$rule] = $args;
        }
        return $_rules;
    }

    /**
     * Single rule PHP validation.
     *
     * Accepts e.g. validate('maxlength', array($value, '15'))
     *
     * @param type $method
     * @param type $args
     * @return boolean
     */
    public function validate( $rule, $args ) {
        $validator = $this->_cake();
        $rule = $this->_map_rule_js_to_php( $rule );

        if ( 'skype' == $rule ) {
            return $validator->custom($args[0]['skypename'], '/^([a-zA-Z0-9\,\.\-\_]+)$/');
        }

        if ( is_callable( array($validator, $rule) ) ) {
            return call_user_func_array( array($validator, $rule), $args );
        }
        return false;
    }

    /**
     * Loads CakePHP Validation class.
     *
     * @return type
     */
    protected function _cake() {
        if ( is_null( $this->_cake ) ) {
            require_once WPTOOLSET_FORMS_ABSPATH . '/lib/CakePHP-Validation.php';
            $this->_cake = new WPToolset_Cake_Validation;
        }
        return $this->_cake;
    }

    /**
     * Maps rules between JS and PHP.
     *
     * @param type $rule
     * @return type
     */
    protected function _map_rule_js_to_php( $rule ) {
        return isset( $this->_rules_map[$rule] ) ? $this->_rules_map[$rule] : $rule;
    }

    /**
     * Renders JSON data.
     */
    public function renderJsonData() {
        printf('<script type="text/javascript">wptValidationForms.push("#%s");</script>', $this->__formID);
    }

    public function actionFieldClass( $config ) {
        if ( !empty( $config['validation'] ) ) {
            foreach ($config['validation'] as $rule => $data) {
                echo " wpt-validation-{$rule}";
            }
        }
    }

}
