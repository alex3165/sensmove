<?php
add_filter( 'wpcf_relationship_meta_form',
        'wpcf_filds_checkboxes_relationship_form_filter' );

/**
 * Register data (called automatically).
 * 
 * @return type 
 */
function wpcf_fields_checkboxes() {
    return array(
        'id' => 'wpcf-checkboxes',
        'title' => __( 'Checkboxes', 'wpcf' ),
        'description' => __( 'Checkboxes', 'wpcf' ),
//        'validate' => array('required'),
        'meta_key_type' => 'BINARY',
    );
}

// Add filter when using wpv_condition()
add_filter( 'wpv_condition', 'wpcf_fields_checkboxes_wpv_conditional_trigger' );
add_filter( 'wpv_condition_end',
        'wpcf_fields_checkboxes_wpv_conditional_trigger_end' );

/**
 * Form data for post edit page.
 * 
 * @param type $field 
 */
function wpcf_fields_checkboxes_meta_box_form( $field, $field_object ) {
    $options = array();
    if ( !empty( $field['data']['options'] ) ) {
        global $pagenow;
        foreach ( $field['data']['options'] as $option_key => $option ) {
            // Set value
            $options[$option_key] = array(
                '#value' => $option['set_value'],
                '#title' => wpcf_translate( 'field ' . $field['id'] . ' option '
                        . $option_key . ' title', $option['title'] ),
                '#default_value' => (!empty( $field['value'][$option_key] )// Also check new post
                || ($pagenow == 'post-new.php' && !empty( $option['checked'] ))) ? 1 : 0,
                '#name' => 'wpcf[' . $field['id'] . '][' . $option_key . ']',
                '#id' => $option_key . '_'
                . wpcf_unique_id( serialize( $field ) ),
                '#inline' => true,
                '#after' => '<br />',
            );
        }
    }
    return array(
        '#type' => 'checkboxes',
        '#options' => $options,
    );
}

/**
 * Editor callback form.
 */
function wpcf_fields_checkboxes_editor_callback( $field, $settings ) {
    $data = array();
    if ( !empty( $field['data']['options'] ) ) {
        $index = 0;
        foreach ( $field['data']['options'] as $option_key => $option ) {
            $data['checkboxes'][$option_key] = array(
                'id' => $option_key,
                'title' => $option['title'],
                'selected' => isset( $settings['options'][$index]['selected'] ) ? $settings['options'][$index]['selected'] : htmlspecialchars( stripslashes( strval( ( $option['display_value_selected'] )))),
                'not_selected' => isset( $settings['options'][$index]['not_selected'] ) ? $settings['options'][$index]['not_selected'] : htmlspecialchars(stripslashes( strval(  $option['display_value_not_selected'] ))),
            );
            $index++;
        }
    }
    return array(
        'supports' => array('style'),
        'tabs' => array(
            'display' => array(
                'menu_title' => __( 'Display', 'wpcf' ),
                'title' => __( 'Display', 'wpcf' ),
                'content' => WPCF_Loader::template( 'editor-modal-checkboxes',
                        $data ),
            )
        )
    );
}

/**
 * Editor callback form submit.
 */
function wpcf_fields_checkboxes_editor_submit( $data, $field, $context ) {
    $add = '';
    $types_attr = $context == 'usermeta' ? 'usermeta' : 'field';
    $shortcode = '';
    if ( $context == 'usermeta' ) {
        $add .= wpcf_get_usermeta_form_addon_submit();
    }
    if ( !empty( $data['options'] ) ) {
        if ( $data['display'] == 'display_all' ) {
            $separator = !empty( $data['cbs_separator'] ) ? $data['cbs_separator'] : '';
            $_add = $add . ' separator="' . $separator . '"';
            if ( $context == 'usermeta' ) {
                $shortcode .= wpcf_usermeta_get_shortcode( $field, $_add );
            } else {
                $shortcode .= wpcf_fields_get_shortcode( $field, $_add );
            }
        } else {
            $i = 0;
            foreach ( $data['options'] as $option ) {
                if ( $data['display'] == 'value' ) {
                    $checked_add = $add . ' option="' . $i . '" state="checked"';
                    $unchecked_add = $add . ' option="' . $i . '" state="unchecked"';

                    if ( $context == 'usermeta' ) {
                        $shortcode_checked = wpcf_usermeta_get_shortcode( $field,
                                $checked_add, $option['selected'] );
                        $shortcode_unchecked = wpcf_usermeta_get_shortcode( $field,
                                $unchecked_add, $option['not_selected'] );
                    } else {
                        $shortcode_checked = wpcf_fields_get_shortcode( $field,
                                $checked_add, $option['selected'] );
                        $shortcode_unchecked = wpcf_fields_get_shortcode( $field,
                                $unchecked_add, $option['not_selected'] );
                    }
                    $shortcode .= $shortcode_checked . $shortcode_unchecked;
                } else {
                    $add = ' option="' . $i . '"';
                    if ( $context == 'usermeta' ) {
                        $add .= wpcf_get_usermeta_form_addon_submit();
                    }
                    if ( $types_attr == 'usermeta' ) {
                        $shortcode .= wpcf_usermeta_get_shortcode( $field, $add );
                    } else {
                        $shortcode .= wpcf_fields_get_shortcode( $field, $add );
                    }
                }
                $i++;
            }
        }
    } else {
        if ( $types_attr == 'usermeta' ) {
            $shortcode .= wpcf_usermeta_get_shortcode( $field, $add );
        } else {
            $shortcode .= wpcf_fields_get_shortcode( $field, $add );
        }
    }
    return $shortcode;
}

/**
 * View function.
 * 
 * @param type $params 
 */
function wpcf_fields_checkboxes_view( $params ) {
    $option = array();
    // Basic checks
    if ( empty( $params['field']['data']['options'] )
            || !is_array( $params['field_value'] ) ) {
        return '__wpcf_skip_empty';
    }

    /*
     * 
     * NO OPTION specified
     * loop over all options and display all of them
     */
    if ( !isset( $params['option'] ) ) {
        $separator = isset( $params['separator'] ) ? html_entity_decode( $params['separator'] ) : ', ';
        foreach ( $params['field_value'] as $name => &$value ) {
            /*
             * 
             * Set option
             */
            if ( isset( $params['field']['data']['options'][$name] ) ) {
                $option = $params['field']['data']['options'][$name];
            } else {
                // Unset if not valid
                unset( $params['field_value'][$name] );
                continue;
            }
            /*
             * 
             * Set output according to settings.
             * 'db' or 'value'
             */
            if ( $option['display'] == 'db'
                    && !empty( $option['set_value'] ) && !empty( $value ) ) {
                $value = $option['set_value'];
                $value = wpcf_translate( 'field ' . $params['field']['id'] . ' option ' . $name . ' value',
                        $value );
            } else if ( $option['display'] == 'value' ) {
                if ( isset( $option['display_value_selected'] ) && !empty( $value ) ) {
                    $value = $option['display_value_selected'];
                    $value = wpcf_translate( 'field ' . $params['field']['id'] . ' option ' . $name . ' display value selected',
                            $value );
                } else {
                    $value = $option['display_value_not_selected'];
                    $value = wpcf_translate( 'field ' . $params['field']['id'] . ' option ' . $name . ' display value not selected',
                            $value );
                }
            } else {
                unset( $params['field_value'][$name] );
            }
        }
        $output = implode( array_values( $params['field_value'] ), $separator );
        return empty( $output ) ? '__wpcf_skip_empty' : stripslashes($output);
    }

    /*
     * 
     * 
     * OPTION specified - set required option.
     */
    $i = 0;
    foreach ( $params['field']['data']['options'] as $option_key => $option_value ) {
        if ( intval( $params['option'] ) == $i ) {
            $option['key'] = $option_key;
            $option['data'] = $option_value;
            $option['value'] = !empty( $params['field_value'][$option_key] ) ? $params['field_value'][$option_key] : '__wpcf_unchecked';
            break;
        }
        $i++;
    }

    $output = '';

    /*
     * STATE set - use #content is as render value.
     * If setings are faulty - return '__wpcf_skip_empty'.
     */
    if ( isset( $params['state'] ) ) {
        $content = !empty( $params['#content'] ) ? htmlspecialchars_decode( $params['#content'] ) : '__wpcf_skip_empty';
        if ( $params['state'] == 'checked'
                && $option['value'] != '__wpcf_unchecked' ) {
            return $content;
        } else if ( $params['state'] == 'unchecked'
                && $option['value'] == '__wpcf_unchecked' ) {
            return $content;
        } else if ( isset( $params['state'] ) ) {
            return '__wpcf_skip_empty';
        }
    }

    /*
     * 
     * MAIN settings
     * 'db'      - Use 'set_value' as render value
     * 'value'   - Use values set in Group form data 'display_value_selected'
     *                  or 'display_value_not_selected'
     * 
     * Only set if it matches settings.
     * Otherwise leave empty and '__wpcf_skip_empty' will be returned.
     *
     */

    if ( isset($option['data']) && $option['data']['display'] == 'db' ) {
        /*
         * 
         * Only if NOT unchecked!
         */
        if ( !empty( $option['data']['set_value'] )
                && $option['value'] != '__wpcf_unchecked' ) {
            $output = $option['data']['set_value'];
            $output = wpcf_translate( 'field ' . $params['field']['id']
                    . ' option ' . $option['key'] . ' value', $output );
        }
    } else if ( isset($option['data']) && $option['data']['display'] == 'value' ) {
        /*
         * 
         * Checked
         */
        if ( $option['value'] != '__wpcf_unchecked' ) {
            if ( isset( $option['data']['display_value_selected'] ) ) {
                $output = $option['data']['display_value_selected'];
                $output = wpcf_translate( 'field ' . $params['field']['id'] . ' option ' . $option['key'] . ' display value selected',
                        $output );
            }
            /*
             * 
             * 
             * Un-checked
             */
        } else if ( isset( $option['data']['display_value_not_selected'] ) ) {
            $output = $option['data']['display_value_not_selected'];
            $output = wpcf_translate( 'field ' . $params['field']['id'] . ' option ' . $option['key'] . ' display value not selected',
                    $output );
        }
    }

    if ( empty( $output ) ) {
        return '__wpcf_skip_empty';
    }

    return $output;
}

/**
 * This marks child posts checkboxes.
 * 
 * Because if all unchecked, on submit there won't be any data.
 * 
 * @param string $form
 * @param type $cf
 * @return string
 */
function wpcf_filds_checkboxes_relationship_form_filter( $form, $cf ) {
    if ( $cf->cf['type'] == 'checkboxes' ) {
        $form[wpcf_unique_id( serialize( $cf ) . 'rel_child' )] = array(
            '#type' => 'hidden',
            '#name' => '_wpcf_check_checkboxes[' . $cf->post->ID . ']['
            . $cf->slug . ']',
            '#value' => '1'
        );
    }
    return $form;
}

/**
 * Triggers post_meta filter.
 * 
 * @param type $post
 * @return type
 */
function wpcf_fields_checkboxes_wpv_conditional_trigger( $post ) {
    add_filter( 'get_post_metadata',
            'wpcf_fields_checkboxes_conditional_filter_post_meta', 10, 4 );
}

/**
 * Returns string.
 * 
 * @global type $wpcf
 * @param type $null
 * @param type $object_id
 * @param type $meta_key
 * @param type $single
 * @return type
 */
function wpcf_fields_checkboxes_conditional_filter_post_meta( $null, $object_id,
        $meta_key, $single ) {
    global $wpcf;
    $field = wpcf_admin_fields_get_field( $wpcf->field->__get_slug_no_prefix( $meta_key ) );
    if ( !empty( $field ) && $field['type'] == 'checkboxes' ) {
        $_meta = maybe_unserialize( wpcf_get_post_meta( $object_id, $meta_key,
                        $single ) );
        if ( is_array( $_meta ) ) {
            $null = empty( $_meta ) ? '1' : '';
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
 * Triggers post_meta filter.
 * 
 * @param type $post
 * @return type
 */
function wpcf_fields_checkboxes_wpv_conditional_trigger_end( $post ) {
    remove_filter( 'get_post_metadata',
            'wpcf_fields_checkboxes_conditional_filter_post_meta', 10, 4 );
}
