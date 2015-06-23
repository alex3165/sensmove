<?php
/*
 *
 *
 * Repeater fields class.
 */

/**
 * Repater class
 *
 * Very useful, should be used to finish small tasks for repeater field.
 *
 * Example:
 *
 * // Setup field
 * global $wpcf;
 * $my_field = new WPCF_Repeater();
 * $my_field->set($wpcf->post, wpcf_admin_fields_get_field('image'));
 *
 * // Use it
 * $my_field->save();
 *
 * Generic instance can be found in global $wpcf.
 * global $wpcf;
 * $wpcf->repeater->set(...);
 *
 * @since Types 1.2
 * @package Types
 * @subpackage Classes
 * @version 0.1
 * @category repeater
 * @author srdjan <srdjan@icanlocalize.com>
 */
class WPCF_Repeater extends WPCF_Field
{

    /**
     * Field order
     *
     * @var type
     */
    var $order;

    /**
     * Indexing
     *
     * Set counts when processing fields.
     *
     * @var type
     */
    var $index = 0;

    /**
     * Field title
     * @var type
     */
    var $title = '';

    /**
     * Field description.
     *
     * @var type
     */
    var $description = '';

    function __construct() {
        parent::__construct();
        if ( is_admin() ) {
            wpcf_admin_add_js_settings( 'wpcf_repetitive_last_warning',
                    __( 'Sorry, can not delete all fields.', 'wpcf' ) );
        }
    }

    /**
     * Calls parent set func.
     *
     * @param type $post
     * @param type $field
     */
    function set( $post, $field ) {
        parent::set( $post, $field );
        $this->index = 0;
    }

    /**
     * Save fields
     *
     * If $data empty, $_POST will be checked
     *
     * @global type $wpcf
     * @param type $data
     * @return boolean
     */
    function save( $data = null ) {

        global $wpcf;

        // Delete all fields
        do_action('wpcf_postmeta_before_delete_repetitive', $this->post, $this->cf);
        delete_post_meta( $this->post->ID, $this->slug );
        do_action('wpcf_postmeta_after_delete_repetitive', $this->post, $this->cf);

        // Allow $data to replace $_POST
        if ( is_null( $data ) && isset( $_POST['wpcf'][$this->cf['slug']] ) ) {
            $data = $_POST['wpcf'][$this->cf['slug']];
        }

        // Set data
        if ( !empty( $data ) ) {

            do_action('wpcf_postmeta_before_add_repetitive', $this->post, $this->cf);

            // Insert new meta and collect all new mids
            $mids = array();
            $i = 1;
            foreach ( $data as $meta_value ) {

                /*
                 *
                 * Deprecated!
                 */
                if ( is_array( $meta_value ) && isset( $meta_value['new_value'] ) ) {
                    $meta_value = $meta_value['new_value'];
                    $wpcf->debug->deprecated['repetitive_new_value_used'] = 'repetitive_new_value_used';
                }

                // Apply filters
                $meta_value = apply_filters( 'types_field_get_submitted_data',
                        $meta_value, $this );

                // Apply other filters
                $_meta_value = $this->_filter_save_value( $meta_value );

                // Adding each field will return $mid
                if ( count($data) == $i++ ) {
                    do_action('wpcf_postmeta_before_add_last_repetitive', $this->post, $this->cf);
                }
                $mid = add_post_meta( $this->post->ID, $this->slug, $_meta_value, false );

                $mids[] = $mid;

                // Call insert post actions on each field
                $this->_action_save( $this->cf, $_meta_value, $mid, $meta_value );
            }

            do_action('wpcf_postmeta_after_add_repetitive', $this->post, $this->cf);

            // Save order
            if ( !empty( $mids ) ) {
                update_post_meta( $this->post->ID, $this->order_meta_name, $mids );
            }

            // Return true - field found
            return true;
        }

        // Return false if field missed
        return false;
    }

    /**
     * Fetch and sort fields.
     *
     * @global object $wpdb
     */
    function _get_meta() {
        global $wpdb;

        $cache_key = md5( 'repeater::_get_meta' . $this->post->ID . $this->slug );
        $cache_group = 'types_cache';
        $cached_object = wp_cache_get( $cache_key, $cache_group );

        if ( $this->use_cache ) {
			if ( false != $cached_object && is_array( $cached_object ) ) {
				return $cached_object;
			}
        }

        $this->order_meta_name = '_' . $this->slug . '-sort-order';
        $_meta = parent::_get_meta();
        $ordered = array();
        $this->order = get_post_meta( $this->post->ID, $this->order_meta_name,
                true );

		$cache_key_field = md5( 'field::_get_meta' . $this->post->ID . $this->slug );
        $cached_object_field = wp_cache_get( $cache_key_field, $cache_group );

        if ( $this->use_cache ) {
			if ( false != $cached_object_field && is_array( $cached_object_field ) ) {// WordPress cache
				$r = $cached_object_field;
			} else {
				$r = $wpdb->get_results(
						$wpdb->prepare(
								"SELECT * FROM $wpdb->postmeta
								WHERE post_id=%d
								AND meta_key=%s",
								$this->post->ID, $this->slug )
				);
			}
		} else {
			// If not using cache, get straight from DB
			$r = $wpdb->get_results(
					$wpdb->prepare(
							"SELECT * FROM $wpdb->postmeta
					WHERE post_id=%d
					AND meta_key=%s",
							$this->post->ID, $this->slug )
			);
		}
        if ( !empty( $r ) ) {
            $_meta = array();
            $_meta['by_meta_id'] = array();
            $_meta['by_meta_key'] = array();

            // Default order
            foreach ( $r as $meta ) {
                // This will use last item in array if multiple values exist
                $_meta['single'] = maybe_unserialize( $meta->meta_value );
                // Sort by meta_id column
                $_meta['by_meta_id'][$meta->meta_id]
                        = maybe_unserialize( $meta->meta_value );
                // Sort by meta_key
                $_meta['by_meta_key'][] = maybe_unserialize( $meta->meta_value );
            }
            ksort( $_meta['by_meta_id'] );

            // Custom order
            if ( !empty( $this->order ) ) {
                foreach ( $this->order as $meta_id ) {
                    if ( isset( $_meta['by_meta_id'][$meta_id] ) ) {
                        $_meta['custom_order'][$meta_id] = $_meta['by_meta_id'][$meta_id];
                    }
                }
                // This ones are orphaned
                foreach ( $_meta['by_meta_id'] as $meta_id => $meta ) {
                    if ( !isset( $ordered[$meta_id] ) ) {
                        $_meta['custom_order'][$meta_id] = $meta;
                    }
                }
            } else {
                $_meta['custom_order'] = $_meta['by_meta_id'];
            }
        } else if ( !empty( $this->meta_object->meta_id ) ) {
            $_meta = array();
            $_meta['single'] = maybe_unserialize( $this->meta_object->meta_value );
            // Sort by meta_id column
            $_meta['by_meta_id'][$this->meta_object->meta_id] = maybe_unserialize( $this->meta_object->meta_value );
            // Sort by meta_key
            $_meta['by_meta_key'][] = maybe_unserialize( $this->meta_object->meta_value );
        } else {
            $_meta = array();
            $_meta['single'] = '';
            $_meta['by_meta_id'] = array();
            $_meta['by_meta_key'] = array();
        }

        if ( empty( $_meta['custom_order'] ) ) {
            $_meta['custom_order'] = $_meta['by_meta_id'];
        }

        // Cache it
        wp_cache_add( $cache_key, $_meta, $cache_group );// WordPress cache

        return $_meta;
    }

    /**
     * Sets repetitive field form.
     *
     * @todo Make more distinction between $field_form and $form_field
     */
    function get_fields_form() {
        $form = array();
        $form_id = $this->cf['id'];
        $unique_id = wpcf_unique_id( serialize( $this->cf ) );

        // Process fields
        // Check if has any value
        if ( empty( $this->meta['by_meta_id'] ) ) {

            // To prevent passing array to field
            $this->meta = null;
            $this->__meta = null;
            $this->cf['value'] = null;

            $field_form = $this->get_field_form( '' );

            foreach ( $field_form as $field_key => $field ) {
                $form_field[$form_id . '_repetitive_0_' . $field_key] = $field;
            }
        } else {
            $ordered = !empty( $this->meta['custom_order'] ) ? $this->meta['custom_order'] : $this->meta['by_meta_id'];
            foreach ( $ordered as $meta_id => $meta_value ) {

                $this->cf['value'] = $meta_value;
                $this->meta_object->meta_id = $meta_id;

                // Set single field form
                $field_form = $this->get_field_form( $meta_value, $meta_id );
                foreach ( $field_form as $field_key => $field ) {
                    $form_field[$form_id . '_repetitive_' . strval( $meta_id ) . '_' . $field_key] = $field;
                }

                $form_field[$form_id . '_repetitive_meta_id_' . strval( $meta_id )] = array(
                    '#type' => 'hidden',
                    '#name' => '',
                    '#value' => $meta_id,
                );
            }
        }

        // Set main wrapper
        // Check if conditional
        global $wpcf;

        // Set style
        /*
         *
         *
         * Hide if field not passed check
         * TODO Move this to WPCF_Conditional
         */
        $show = true;
        if ( $wpcf->conditional->is_conditional( $this->cf ) ) {
            $wpcf->conditional->set( $this->post, $this->cf );
            $show = $wpcf->conditional->evaluate();
        }
        $css_cd = !$show ? 'display:none;' : '';

        /**
         *
         *
         *
         *
         * Set title and description
         * TODO See if can be improved getting main element
         *
         * Get first element and extract details
         * Pass emty string as value to avoid using meta as array
         */
        //
        $_c = array_values( parent::_get_meta_form( '' ) );
        array_shift( $_c );
        $_main_element = array_shift( $_c );
        // Set title and desc
        if ( !empty( $_main_element['#title'] ) ) {
            $this->title = $_main_element['#title'];
        }
        if ( !empty( $_main_element['#description'] ) ) {
            $this->description = $_main_element['#description'];
        }

        /*
         *
         *
         * Start wrapper
         */
        $form[$unique_id . '_repetitive_wrapper_open'] = array(
            '#type' => 'markup',
            '#markup' => ''
            . '<div id="wpcf_'
            . $form_id
            . '_repetitive_wrapper_' . $unique_id
            . '" class="wpcf-wrap wpcf-repetitive-wrapper" style="' . $css_cd . '">',
        );

        // Set title
        $form[$unique_id . '_main_title'] = array(
            '#type' => 'markup',
            '#markup' => '<strong>' . $this->title . '</strong><br/>',
        );

        // Set hidden mark field
        /*
         *
         *
         *
         * This actually marks field as repetitive
         * IMPORTANT!!! IF NOT marked field won't be saved at all!
         *
         * @see wpcf_admin_post_save_post_hook()
         */
        $form[$form_id . '_hidden_mark'] = array(
            '#type' => 'hidden',
            '#name' => '__wpcf_repetitive[' . $this->slug . ']',
            '#value' => 1,
            '#id' => $form_id . '_hidden_mark',
        );

        // Sortable
        $form[$form_id . '_repetitive_sortable_open'] = array(
            '#type' => 'markup',
            '#markup' => '<div id="wpcf_'
            . $form_id
            . '_repetitive_sortable_' . wpcf_unique_id( serialize( $this->cf ) )
            . '" class="wpcf-repetitive-sortable-wrapper">',
            '#id' => $form_id . '_repetitive_sortable_wrapper_open',
        );

        // Append field form
        $form = $form + $form_field;

        // Close sortable wrapper
        $form[$form_id . '_repetitive_sortable_close'] = array(
            '#type' => 'markup',
            '#markup' => '</div>',
            '#id' => $form_id . '_repetitive_sortable_close',
        );

        // Add AJAX response at the end of repetitive field
        // Show only on page load not when calling AJAX
        if ( !defined( 'DOING_AJAX' ) ) {
            $form[$form_id . '_repetitive_ajax_response'] = array(
                '#type' => 'markup',
                '#markup' => '<div class="wpcf-repetitive-response"></div>',
                '#id' => $form_id . '_repetitive_ajax_response',
            );

            // Add description
            $form[$unique_id . '_main_description'] = array(
                '#type' => 'markup',
                '#markup' => '<div class="wpcf-repetitive-description">'
                . wpautop( stripslashes( strip_tags( $this->description ) ) )
                . '</div>',
            );

            // 'Add' button
            $form[$form_id . '_repetitive_form'] = array(
                '#type' => 'markup',
                '#markup' => wpcf_repetitive_form( $this->cf, $this->post ),
                '#id' => $form_id . '_repetitive_form',
            );
        }

        // Close wrapper
        $form[$unique_id . '_repetitive_wrapper_close'] = array(
            '#type' => 'markup',
            '#markup' => '</div>',
        );

        return $form;
    }

    /**
     * Sete repetitive form for single field.
     *
     * @param type $meta
     * @return string
     */
    function get_field_form( $meta_value = null, $meta_id = null ) {

        $form = array();
        if ( is_null( $meta_value ) ) {
            $key = 'wpcf_field_' . wpcf_unique_id( md5( $this->index ) . $meta_id );
        } else {
            $key = 'wpcf_field_' . md5( maybe_serialize( $meta_value ) . $meta_id );
        }
        /*
         *
         *
         * TODO We prevented array because of some fails we had before.
         * Now it should work fine
         * Add debug log if meta_value['custom_order'] passed.
         * That means setting meta_value did not went well.
         */
//        if ( is_null( $meta_value ) || is_array( $meta_value ) ) {
        if ( is_null( $meta_value ) ||
                (is_array( $meta_value ) && isset( $meta_value['custom_order'] ) )
        ) {
            $meta_value = $this->meta['single'];
        }

        // Open drag div
        $form[$key . '_drag_open'] = array(
            '#type' => 'markup',
            '#markup' => '<div class="wpcf-repetitive-drag-and-drop">'
        );

        // Use WPCF_Field::get_field_form()
        $field_form = parent::_get_meta_form( $meta_value, $meta_id, false );

        /*
         *
         * Apply filters to each form element.
         * Here we add specific properties
         * e.g. Skype alters fields.
         */
        $_loop = false;
        foreach ( $field_form as $k => $field ) {

            /*
             *
             * IMPORTANT
             * We change name to hold array
             */
            if ( isset( $field['#name'] ) ) {
                $temp = explode( '[' . $this->cf['slug'] . ']', $field['#name'] );

                // Assign new name
                $field['#name'] = $temp[0] . '[' . $this->cf['slug'] . ']' . '['
                        . $key . ']';

                // Append rest if any
                if ( isset( $temp[1] ) ) {
                    $field['#name'] .= $temp[1];
                }
            }

            // Apply filters
            $field_form[$k] = apply_filters( 'wpcf_repetitive_field', $field,
                    $this->post, $this->cf, $k );

            // BREAKPOINT
            /*
             * This is place where we clean display.
             * First item is displayed as it is, each after is reduced.
             * If called via AJAX - that means it added and should be reduced.
             */
//            if ( $_loop == true || defined( 'DOING_AJAX' ) ) {
            /*
             * See if field has Repeater pattern defined
             */
            if ( isset( $field['__repeater_restricted'] )
                    && is_array( $field['__repeater_restricted'] ) ) {
                foreach ( $field['__repeater_restricted'] as $_e => $_v ) {
                    if ( isset( $field[$_e] ) ) {
                        unset( $field[$_e] );
                    }
                }
            } else {
                unset( $field['#title'], $field['#description'] );
            }
            // Set main
            $field_form[$k] = $field;
//            }
//            $_loop = true;
        }

        // Just append form
        $form = $form + $field_form;

        // Open control div
        $form[$key . '_control_open'] = array(
            '#type' => 'markup',
            '#markup' => '<div class="wpcf-repetitive-control">'
        );

        // Drag button
        $form[$key . '_drag_button'] = array(
            '#type' => 'markup',
            '#markup' => wpcf_repetitive_drag_button( $this->cf, $this->post ),
        );

        // 'Delete' button
        $form[$key . '_delete_button'] = array(
            '#type' => 'markup',
            '#markup' => wpcf_repetitive_delete_button( $this->cf, $this->post,
                    $meta_id ),
        );

        // Close control div
        $form[$key . '_control_close'] = array(
            '#type' => 'markup',
            '#markup' => '</div>',
        );

        // Close drag div
        $form[$key . '_drag_close'] = array(
            '#type' => 'markup',
            '#markup' => '</div>',
        );

        // Count it and set JS var
        $this->_set_form_count();
        wpcf_admin_add_js_settings( 'wpcf_repetitive_count_' . md5( $this->cf['id'] ),
                $this->index );

        return $form;
    }

    /**
     * Set counting elements.
     */
    function _set_form_count() {
        if ( $this->index === 0 ) {
            if ( defined( 'DOING_AJAX' ) && isset( $_POST['count'] ) ) {
                $this->index = intval( $_POST['count'] );
            }
        }
        $this->index += 1;
    }

    /**
     * Deletes meta.
     *
     * @global object $wpdb
     *
     * @param type $meta_key
     *
     */
    function delete( $meta_id ) {
        global $wpdb;
        $r = $wpdb->query(
                $wpdb->prepare(
                        "DELETE FROM $wpdb->postmeta
                        WHERE post_id = %d
                        AND meta_id = %d",
                        $this->post->ID, intval( $meta_id )
                )
        );
        if ( $r === false ) {
            return WP_Error( 'wpcf_repeater_delete_field',
                            'Repeater failed deleting post_ID: '
                            . $this->post->ID . '; meta_ID: ' . intval( $meta_id ) );
        }
        return $r;
    }

}
