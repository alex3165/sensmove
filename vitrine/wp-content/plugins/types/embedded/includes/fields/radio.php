<?php

/**
 * Register data (called automatically).
 * 
 * @return type 
 */
function wpcf_fields_radio() {
    return array(
        'id' => 'wpcf-radio',
        'title' => __( 'Radio', 'wpcf' ),
        'description' => __( 'Radio', 'wpcf' ),
        'validate' => array('required'),
    );
}

/**
 * Form data for post edit page.
 * 
 * @param type $field 
 */
function wpcf_fields_radio_meta_box_form( $field ) {
    $options = array();
    $default_value = '';

    if ( !empty( $field['data']['options'] ) ) {
        foreach ( $field['data']['options'] as $option_key => $option ) {
            // Skip default value record
            if ( $option_key == 'default' ) {
                continue;
            }
            // Set default value
            if ( !empty( $field['data']['options']['default'] )
                    && $option_key == $field['data']['options']['default'] ) {
                $default_value = $option['value'];
            }
            $options[$option['title']] = array(
                '#value' => $option['value'],
                '#title' => wpcf_translate( 'field ' . $field['id'] . ' option '
                        . $option_key . ' title', $option['title'] ),
            );
        }
    }

    if ( !empty( $field['value'] )
            || ($field['value'] === 0 || $field['value'] === '0') ) {
        $default_value = $field['value'];
    }

    return array(
        '#type' => 'radios',
        '#default_value' => $default_value,
        '#options' => $options,
    );
}

/**
 * Editor callback form.
 */
function wpcf_fields_radio_editor_callback( $field, $data ) {


    if ( !isset( $data['options'] ) ) {
        $data['options'] = array();
    }

    if ( !empty( $field['data']['options'] ) ) {
        foreach ( $field['data']['options'] as $option_id => $option ) {
            if ( $option_id == 'default' ) {
                continue;
            }
            if ( isset( $data['options'][$option_id] ) ) {
                $value = $data['options'][$option_id];
                $data['options'][$option_id] = array(
                    'title' => $option['title'],
                    'value' => $value,
                );
                continue;
            }
            $value = isset( $option['display_value'] ) ? $option['display_value'] : $option['value'];
            $data['options'][$option_id] = array(
                'title' => $option['title'],
                'value' => $value,
            );
        }
    }

    return array(
        'supports' => array('style'),
        'tabs' => array(
            'display' => array(
                'menu_title' => __( 'Display', 'wpcf' ),
                'title' => __( 'Display', 'wpcf' ),
                'content' => WPCF_Loader::template( 'editor-modal-radio', $data ),
            )
        ),
    );
}

/**
 * Editor callback form submit.
 */
function wpcf_fields_radio_editor_submit( $data, $field, $context ) {
    $add = '';
    $types_attr = $context == 'usermeta' ? 'usermeta' : 'field';
    if ( $context == 'usermeta' ) {
        $add .= wpcf_get_usermeta_form_addon_submit();
    }
    if ( isset( $data['display'] ) && $data['display'] == 'value' && !empty( $data['options'] ) ) {
        $shortcode = '';
        foreach ( $data['options'] as $option_id => $value ) {
            $shortcode .= '[types ' . $types_attr . '="' . $field['slug']
                    . '" ' . $add . ' option="' . $option_id . '"]' . $value
                    . '[/types] ';
        }
    } else {
        if ( $context == 'usermeta' ) {
            $shortcode = wpcf_usermeta_get_shortcode( $field, $add );
        } else {
            $shortcode = wpcf_fields_get_shortcode( $field, $add );
        }
    }
    return $shortcode;
}

/**
 * View function.
 * 
 * @param type $params 
 */
function wpcf_fields_radio_view( $params ) {
    if ( isset( $params['style'] ) && $params['style'] == 'raw' ) {
        return '';
    }
    if ( isset( $params['usermeta'] ) && !empty( $params['usermeta'] ) ) {
        $field = wpcf_fields_get_field_by_slug( $params['field']['slug'],
                'wpcf-usermeta' );
    } else {
        $field = wpcf_fields_get_field_by_slug( $params['field']['slug'] );
    }

    $output = '';

    // See if user specified output for each field
    if ( isset( $params['option'] ) ) {
        foreach ( $field['data']['options'] as $option_key => $option ) {
            if ( isset( $option['value'] ) ) {
                $test_val = stripslashes( strval( $option['value'] ) );
                if ($test_val == $params['field_value']
                    && $option_key == $params['option'] ) {

                    return htmlspecialchars_decode($params['#content']);
                }
            }
        }
//        return ' ';
        return '__wpcf_skip_empty';
    }

    if ( !empty( $field['data']['options'] ) ) {
        $field_value = $params['field_value'];
        foreach ( $field['data']['options'] as $option_key => $option ) {
            if ( isset( $option['value'] )
                    && stripslashes( $option['value'] )  == stripslashes( $params['field_value'] ) ) {
                $field_value = wpcf_translate( 'field ' . $params['field']['id'] . ' option '
                        . $option_key . ' title', $option['title'] );
                if ( isset( $params['field']['data']['display'] )
                        && $params['field']['data']['display'] != 'db'
                        && !empty( $option['display_value'] ) ) {
                    $field_value = wpcf_translate( 'field ' . $params['field']['id'] . ' option '
                            . $option_key . ' display value',
                            $option['display_value'] );
                }
            }
        }
        $output = $field_value;
    }

    return   $output;
}