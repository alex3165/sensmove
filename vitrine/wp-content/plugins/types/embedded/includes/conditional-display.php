<?php
/*
 * Conditional display embedded code.
 */

/*
 * Post page filters
 */
add_filter( 'wpcf_post_edit_field', 'wpcf_cd_post_edit_field_filter', 10, 4 );
add_filter( 'wpcf_post_groups', 'wpcf_cd_post_groups_filter', 10, 3 );

/*
 *
 * These hooks check if conditional failed
 * but form allowed to be saved
 * Since Types 1.2
 */
add_filter( 'wpcf_post_form_error', 'wpcf_conditional_post_form_error_filter',
        10, 2 );


/*
 * Logger
 */
if ( !function_exists( 'wplogger' ) ) {
    require_once WPCF_EMBEDDED_ABSPATH . '/common/wplogger.php';
}
if ( !function_exists( 'wpv_filter_parse_date' ) ) {
    require_once WPCF_EMBEDDED_ABSPATH . '/common/wpv-filter-date-embedded.php';
}

require_once WPTOOLSET_FORMS_ABSPATH . '/classes/class.conditional.php';
require_once WPTOOLSET_FORMS_ABSPATH . '/classes/class.types.php';

/**
 * Filters groups on post edit page.
 *
 * @param type $groups
 * @param type $post
 * @return type
 */
function wpcf_cd_post_groups_filter( $groups, $post, $context ) {
    if ( $context != 'group' ) {
        return $groups;
    }


    foreach ( $groups as $key => &$group ) {

        $conditions = null;
        if (
            array_key_exists( 'conditional_display', $group )
            && array_key_exists( 'conditions', $group['conditional_display'] )
        ) {
            $conditions = $group['conditional_display'];
        } else {
            $conditions = get_post_meta( $group['id'], '_wpcf_conditional_display', true );
        }

        if ( !empty( $conditions['conditions'] ) ) {
            $meta_box_id = "wpcf-group-{$group['slug']}";
            $prefix = 'wpcf-';
            $suffix = '';

            $cond = array();
            if (isset( $post->ID )) {
                $cond_values = get_post_custom( $post->ID );
            } else {
                $cond_values = array();
            }
            $_cond_values = array();
            foreach ( $cond_values as $k => $v ) {
                $v = maybe_unserialize( $v[0] );
                $_cond_values[$k . $suffix] = is_array( $v ) ? strval( array_shift( $v ) ) : $v;
            }
            unset( $cond_values );
            $cond = array();
            if ( !empty( $conditions['custom_use'] ) ) {
                if ( !empty( $conditions['custom'] ) ) {
                    $custom = WPToolset_Types::getCustomConditional($conditions['custom']);
                    $passed = WPToolset_Forms_Conditional::evaluateCustom($custom['custom'], $_cond_values);
                    $cond = array(
                        'custom' => $custom['custom'],
                        'custom_use' => true
                    );
                }
            } else {
                $cond = array(
                    'relation' => $conditions['relation'],
                    'conditions' => array(),
                    'values' => $_cond_values,
                );
                foreach ( $conditions['conditions'] as $d ) {
                    $c_field = types_get_field( $d['field'] );
                    if ( !empty( $c_field ) ) {
                        $_c = array(
                            'id' => wpcf_types_get_meta_prefix( $c_field ) . $d['field'] . $suffix,
                            'type' => $c_field['type'],
                            'operator' => $d['operation'],
                            'args' => array($d['value']),
                        );
                        $cond['conditions'][] = $_c;
                    }
                }
                $passed = wptoolset_form_conditional_check( array( 'conditional' => $cond ) );
            }
            $data = array(
                'id' => $meta_box_id,
                'conditional' => $cond,
            );
            wptoolset_form_add_conditional( 'post', $data );
            if ( !$passed ) {
                $group['_conditional_display'] = 'failed';
            } else {
                $group['_conditional_display'] = 'passed';
            }
        }
    }
    return $groups;
}

/**
 * Checks if there is conditional display.
 *
 * This function filters all fields that appear in form.
 * It checks if field is Check Trigger or Conditional.
 * Since Types 1.2 this functin is simplified and should stay that way.
 * It's important core action.
 *
 *
 * @param type $element
 * @param type $field
 * @param type $post
 * @return type
 */
function wpcf_cd_post_edit_field_filter( $element, $field, $post,
        $context = 'group' ) {

    // Do not use on repetitive
    if ( defined( 'DOING_AJAX' ) && $context == 'repetitive' ) {
        return $element;
    }

    // Use only with postmeta
    if ( $field['meta_type'] != 'postmeta' ) {
        return $element;
    }

    global $wpcf;

    /*
     *
     *
     * Since Types 1.2
     * Automatically evaluates WPCF_Conditional::set()
     * Evaluation moved to WPCF_Conditional::evaluate()
     */
    if ( $wpcf->conditional->is_conditional( $field )
            || $wpcf->conditional->is_trigger( $field ) ) {

        wpcf_conditional_add_js();
        $wpcf->conditional->set( $post, $field );

        /*
         * Check if field is check trigger and wrap it
         * (add CSS class 'wpcf-conditonal-check-trigger')
         */
        if ( $wpcf->conditional->is_trigger( $field ) ) {
            $element = $wpcf->conditional->wrap_trigger( $element );
        }

        /*
         * If conditional
         */
        if ( $wpcf->conditional->is_conditional( $field ) ) {
            $element = $wpcf->conditional->wrap( $element );
        }
    }

    return $element;
}

/**
 * Operations.
 *
 * @return type
 */
function wpcf_cd_admin_operations() {
    return array(
        '=' => __( 'Equal to', 'wpcf' ),
        '>' => __( 'Larger than', 'wpcf' ),
        '<' => __( 'Less than', 'wpcf' ),
        '>=' => __( 'Larger or equal to', 'wpcf' ),
        '<=' => __( 'Less or equal to', 'wpcf' ),
        '===' => __( 'Identical to', 'wpcf' ),
        '<>' => __( 'Not identical to', 'wpcf' ),
        '!==' => __( 'Strictly not equal', 'wpcf' ),
//        'between' => __('Between', 'wpcf'),
    );
}

/**
 * Compares values.
 *
 * @param type $operation
 * @return type
 */
function wpcf_cd_admin_compare( $operation ) {
    $args = func_get_args();
    switch ( $operation ) {
        case '=':
            return $args[1] == $args[2];
            break;

        case '>':
            return intval( $args[1] ) > intval( $args[2] );
            break;

        case '>=':
            return intval( $args[1] ) >= intval( $args[2] );
            break;

        case '<':
            return intval( $args[1] ) < intval( $args[2] );
            break;

        case '<=':
            return intval( $args[1] ) <= intval( $args[2] );
            break;

        case '===':
            return $args[1] === $args[2];
            break;

        case '!==':
            return $args[1] !== $args[2];
            break;

        case '<>':
            return $args[1] <> $args[2];
            break;

        case 'between':
            return intval( $args[1] ) > intval( $args[2] ) && intval( $args[1] ) < intval( $args[3] );
            break;

        default:
            break;
    }
    return true;
}

/**
 * Setsa all JS.
 */
function wpcf_conditional_add_js() {
    wpcf_cd_add_field_js();
}

/**
 * JS for fields AJAX.
 */
function wpcf_cd_add_field_js() {
    global $wpcf;
    $wpcf->conditional->add_js();
}


/**
 * Passes $_POST values for AJAX call.
 *
 * @todo still used by group.
 *
 * @param type $null
 * @param type $object_id
 * @param type $meta_key
 * @param type $single
 * @return type
 */
function wpcf_cd_meta_ajax_validation_filter( $null, $object_id, $meta_key, $single )
{
    $meta_key = str_replace( 'wpcf-', '', $meta_key );
    $field = wpcf_admin_fields_get_field( $meta_key );
    $value = !empty( $field ) && isset( $_POST['wpcf'][$meta_key] ) ? $_POST['wpcf'][$meta_key] : '';
    /**
     * be sure do not return string if array is expected!
     */
    if ( !$single && !is_array($value) ) {
        return array($value);
    }
    return $value;
}

/**
 * Post form error filter.
 *
 * Leave element as not_valid (it will prevent saving) just remove warning.
 *
 * @global type $wpcf
 * @param type $_error
 * @param type $_not_valid
 * @return boolean
 */
function wpcf_conditional_post_form_error_filter( $_error, $_not_valid ) {
    if ( !empty( $_not_valid ) ) {

        global $wpcf;

        $count = 0;
        $count_non_conditional = 0;
        $error_conditional = false;

        foreach ( $_not_valid as $f ) {
            $field = $f['_field'];
            /*
             * Here we add simple check
             *
             * TODO Improve this check
             * We can not tell for sure if it failed except to again check
             * conditionals
             */
            // See if field is conditional
            if ( isset( $field->cf['data']['conditional_display'] ) ) {

                // Use Conditional class
                $test = new WPCF_Conditional();
                $test->set( $wpcf->post, $field->cf );

                // See if evaluated right
                $passed = $test->evaluate();

                // If evaluated FALSE that means error is expected
                if ( $passed ) {
                    $error_conditional = true;
                }

                // Count it
                $count++;
            } else {
                $count_non_conditional++;
            }
            /*
             * If non-conditional fields are not valid - return $_error TRUE
             * If at least one conditional failed - return FALSE
             */
            if ( $count_non_conditional > 0 ) {
                return true;
            }
            return $error_conditional;
        }
    }
    return $_error;
}
