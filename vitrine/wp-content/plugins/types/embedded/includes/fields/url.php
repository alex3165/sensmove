<?php

/**
 * Register data (called automatically).
 * 
 * @return type 
 */
function wpcf_fields_url() {
    return array(
        'id' => 'wpcf-url',
        'title' => 'URL',
        'description' => 'URL',
        'validate' => array('required', 'url'),
        'inherited_field_type' => 'textfield',
    );
}

/**
 * View function.
 * 
 * @param type $params 
 */
function wpcf_fields_url_view( $params ) {
    $title = '';
    $add = '';
    if ( !empty( $params['title'] ) ) {
        $add .= ' title="' . $params['title'] . '"';
        $title .= $params['title'];
    } else {
        if ( !empty( $params['no_protocol'] ) && $params['no_protocol'] == 'true' ) {
            $title = preg_replace( "/^([a-zA-Z]+:\/\/)/", '',
                    $params['field_value'] );
            $add .= ' title="' . $title . '"';
        } else {
            $add .= ' title="' . $params['field_value'] . '"';
            $title .= $params['field_value'];
        }
    }
    if ( !empty( $params['class'] ) ) {
        $add .= ' class="' . $params['class'] . '"';
    }
    if ( !empty( $params['style'] ) ) {
        $add .= ' style="' . $params['style'] . '"';
    }
    if ( !empty( $params['target'] ) ) {
        $add .= ' target="' . $params['target'] . '"';
    }
    $output = '<a href="' . $params['field_value'] . '"' . $add . '>'
            . $title . '</a>';
    return $output;
}

/**
 * Editor callback form.
 */
function wpcf_fields_url_editor_callback( $field, $settings ) {
    if ( empty( $settings['target'] ) ) {
        $settings['target'] = '_self';
    }
    $settings['target_options'] = array(
        '_blank' => __( '_blank: Opens in a new window or tab', 'wpcf' ),
        '_self' => __( '_self: Opens in the same frame as it was clicked',
                'wpcf' ),
        '_parent' => __( '_parent: Opens in the parent frame', 'wpcf' ),
        '_top' => __( '_top: Opens in the full body of the window', 'wpcf' ),
        'framename' => __( 'framename: Opens in a named frame', 'wpcf' ),
    );
    return array(
        'supports' => array('styling', 'style'),
        'tabs' => array(
            'display' => array(
                'menu_title' => __( 'Display', 'wpcf' ),
                'title' => __( 'Display', 'wpcf' ),
                'content' => WPCF_Loader::template( 'editor-modal-url',
                        $settings ),
            ),
            'target' => array(
                'menu_title' => __( 'Target', 'wpcf' ),
                'title' => __( 'Target', 'wpcf' ),
                'content' => '<div data-bind="template: {name:\'tpl-types-modal-url-target\'}"></div>',
            )
        ),
    );
}

/**
 * Editor callback form submit.
 */
function wpcf_fields_url_editor_submit( $data, $field, $context ) {
    $add = '';
    if ( !empty( $data['title'] ) ) {
        $add .= ' title="' . strval( $data['title'] ) . '"';
    }
    if ( !empty( $data['no_protocol'] ) ) {
        $add .= ' no_protocol="true"';
    }
    if ( !empty( $data['target'] ) ) {
        if ( $data['target'] == 'framename' ) {
            $add .= ' target="' . strval( $data['framename'] ) . '"';
        } else if ( $data['target'] != '_self' ) {
            $add .= ' target="' . strval( $data['target'] ) . '"';
        }
    }

    if ( $context == 'usermeta' ) {
        $add .= wpcf_get_usermeta_form_addon_submit();
        $shortcode = wpcf_usermeta_get_shortcode( $field, $add );
    } else {
        $shortcode = wpcf_fields_get_shortcode( $field, $add );
    }

    return $shortcode;
}

/*
 * If no_protocl is set to true remove protocol from URL
 */
//add_filter( 'wpcf_fields_type_url_value_display',
//        'wpcf_fields_no_protocol_parser', 10, 4 );

function wpcf_fields_no_protocol_parser( $value, $params, $post, $meta ){
    if ( !empty( $params['no_protocol'] ) && $params['no_protocol'] == 'true' ) {
        $params['field_value'] = preg_replace( "/^([a-zA-Z]+:\/\/)/", '',
                $params['field_value'] );
    }
    return $params['field_value'];
}