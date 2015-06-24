<?php
/*
 * Evaluate class.
 */

/**
 * Evaluate class.
 * 
 * Applies filters:
 * 'relationship_custom_statement_meta_ajax_validation_filter'
 * 'meta_ajax_validation_filter'
 * 
 * @since Types 1.2
 * @package Types
 * @subpackage Conditional
 * @version 0.1
 * @category core
 * @author srdjan <srdjan@icanlocalize.com>
 */
class WPCF_Evaluate
{

    /**
     * Main conditinal evaluation function.
     * 
     * @since 1.2
     * @version 0.2
     * @param type $o
     * @return boolean 
     */
    public static function evaluate( $o ) {

        // Set vars
        $post = $o->post;
        $field = $o->cf;

        /*
         * 
         * Since Types 1.2
         * We force initial value to be FALSE.
         * Better to have restricted than allowed because of sensitive data.
         * If conditional is set on field and it goes wrong - better to abort
         * so user can report bug without exposing his content.
         */
        $passed = false;

        if ( empty( $post->ID ) ) {
            /*
             * 
             * Keep all forbidden if post is not saved.
             */
            $passed = false;
            /*
             * 
             * 
             * 
             * 
             * 
             * 
             * 
             * 
             * 
             * VIEWS
             * 
             * Custom call uses Views code
             * wpv_filter_parse_date()
             * wpv_condition()
             */
        } else if ( isset( $field['data']['conditional_display']['custom_use'] ) ) {
            /*
             * 
             * 
             * More malformed forbids
             */
            if ( empty( $field['data']['conditional_display']['custom'] ) ) {
                return false;
            }

            /*
             * 
             * 
             * Filter meta values (switch them with $_POST values)
             * Used by Views, Types do not need it.
             */

            if ( $o->context == 'relationship' ) {
                add_filter( 'get_post_metadata',
                        array('WPCF_Evaluate', 'relationship_custom_statement_meta_ajax_validation_filter'),
                        10, 4 );
            } else {
                add_filter( 'get_post_metadata',
                        array('WPCF_Evaluate', 'meta_ajax_validation_filter'),
                        10, 4 );
            }
            do_action( 'types_custom_conditional_statement', $o );

            /*
             * 
             * Set statement
             */
            $evaluate = trim( stripslashes( $field['data']['conditional_display']['custom'] ) );
            // Check dates
            $evaluate = wpv_filter_parse_date( $evaluate );
            // Add quotes = > < >= <= === <> !==
            $strings_count = preg_match_all( '/[=|==|===|<=|<==|<===|>=|>==|>===|\!===|\!==|\!=|<>]\s(?!\$)(\w*)[\)|\$|\W]/',
                    $evaluate, $matches );
            if ( !empty( $matches[1] ) ) {
                foreach ( $matches[1] as $temp_match ) {
                    $temp_replace = is_numeric( $temp_match ) ? $temp_match : '\'' . $temp_match . '\'';
                    $evaluate = str_replace( ' ' . $temp_match . ')',
                            ' ' . $temp_replace . ')', $evaluate );
                }
            }
            preg_match_all( '/\$([^\s]*)/',
                    $field['data']['conditional_display']['custom'], $matches );



            if ( empty( $matches ) ) {
                /*
                 * 
                 * If statement false
                 */
                $passed = false;
            } else {
                /*
                 * 
                 * 
                 * If statement right, check condition
                 */
                $fields = array();
                foreach ( $matches[1] as $field_name ) {
                    /*
                     * 
                     * 
                     * This field value is checked
                     */
                    $f = wpcf_admin_fields_get_field( trim( strval( $field_name ) ) );
                    if ( empty( $f ) ) {
                        return false;
                    }

                    $c = new WPCF_Field();
                    $c->set( $post, $f );

                    // Set field
                    $fields[$field_name] = $c->slug;
                }
                $fields['evaluate'] = $evaluate;
                $check = wpv_condition( $fields, $post );

                /*
                 * 
                 * 
                 * Views return string malformed,
                 * boolean if call completed.
                 */
                if ( !is_bool( $check ) ) {
                    $passed = false;
                } else {
                    $passed = $check;
                }
            }

            /*
             * 
             * 
             * Remove filter meta values
             */
            if ( $o->context == 'relationship' ) {
                remove_filter( 'get_post_metadata',
                        array('WPCF_Evaluate', 'relationship_custom_statement_meta_ajax_validation_filter'),
                        10, 4 );
            } else {
                remove_filter( 'get_post_metadata',
                        array('WPCF_Evaluate', 'meta_ajax_validation_filter'),
                        10, 4 );
            }
        } else {
            /*
             * 
             * 
             * 
             * 
             * 
             * 
             * 
             * 
             * TYPES
             * 
             * If not custom code, use Types built-in check.
             * wpcf_cd_admin_compare()
             */
            $passed_all = true;
            $passed_one = false;

            // Basic check
            if ( empty( $field['data']['conditional_display']['conditions'] ) ) {
                return false;
            }

            // Keep count to see if OR/AND relation needed
            $count = count( $field['data']['conditional_display']['conditions'] );

            foreach ( $field['data']['conditional_display']['conditions'] as $condition ) {
                /*
                 * 
                 * 
                 * Malformed condition and should be treated as forbidden
                 */
                if ( !isset( $condition['field'] ) || !isset( $condition['operation'] )
                        || !isset( $condition['value'] ) ) {
                    $passed_one = false;
                    continue;
                }
                /*
                 * 
                 * 
                 * This field value is checked
                 */
                $f = wpcf_admin_fields_get_field( trim( strval( $condition['field'] ) ) );
                if ( empty( $f ) ) {
                    return false;
                }

                $c = new WPCF_Field();
                $c->set( $post, $f );

                /*
                 * 
                 * Since Types 1.2
                 * meta is property of WPCF_Field::$__meta
                 * 
                 * BREAKPOINT
                 * This is where values for evaluation are set.
                 * Please do not allow other places - use hooks.
                 * 
                 * TODO Monitor this
                 * 1.3 Change use of $c->_get_meta( 'POST' )
                 * to $c->get_submitted_data()
                 */
//                $value = defined( 'DOING_AJAX' ) ? $c->_get_meta( 'POST' ) : $c->__meta;
                $value = defined( 'DOING_AJAX' ) ? $c->get_submitted_data() : $c->__meta;

                /*
                 * 
                 * Apply filters
                 */
                $value = apply_filters( 'wpcf_conditional_display_compare_meta_value',
                        $value, $c->cf['id'], $condition['operation'], $c->slug,
                        $post );
                $condition['value'] = apply_filters( 'wpcf_conditional_display_compare_condition_value',
                        $condition['value'], $c->cf['id'],
                        $condition['operation'], $c->slug, $post );

                /*
                 * 
                 * 
                 * Call built-in Types compare func
                 */
                WPCF_Loader::loadInclude( 'conditional-display' );
                $passed = wpcf_cd_admin_compare( $condition['operation'],
                        $value, $condition['value'] );

                if ( !$passed ) {
                    $passed_all = false;
                } else {
                    $passed_one = true;
                }
            }

            /*
             * 
             * 
             * Check OR/AND relation
             */
            if ( $count > 1 ) {
                if ( $field['data']['conditional_display']['relation'] == 'AND' ) {
                    $passed = $passed_all;
                } else if ( $field['data']['conditional_display']['relation'] == 'OR' ) {
                    $passed = $passed_one;
                }
            }
        }

        return (bool) $passed;
    }

    /**
     * Filters $_POST for relationship.
     * 
     * @global type $wpcf
     * @param type $null
     * @param type $object_id
     * @param type $meta_key
     * @param type $single
     * @return type
     */
    public static function relationship_custom_statement_meta_ajax_validation_filter( $null,
            $object_id, $meta_key, $single ){

        global $wpcf;

        $value = $wpcf->relationship->get_submitted_data(
                $wpcf->relationship->parent->ID, $wpcf->relationship->child->ID,
                $meta_key );

        $null = is_null( $value ) ? $null : $value;

        // Date
        if ( !empty( $null ) && !empty( $field ) && $field['type'] == 'date' ) {
            $time = strtotime( $null );
            if ( $time ) {
                $null = $time;
            }
            /**
             * be sure do not return string if array is expected!
             */
            if ( !$single && !is_array($null) ) {
                return array($null);
            }
        }

        return $null;
    }

    /**
     * Filters $_POST values for AJAX call.
     * 
     * @param type $null
     * @param type $object_id
     * @param type $meta_key
     * @param type $single
     * @return type 
     */
    public static function meta_ajax_validation_filter( $null, $object_id,
            $meta_key, $single ) {
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

}
