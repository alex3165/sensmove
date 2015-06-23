<?php

/**
 * Register data (called automatically).
 * 
 * @return type 
 */
function wpcf_fields_email() {
    return array(
        'id' => 'wpcf-email',
        'title' => __( 'Email', 'wpcf' ),
        'description' => __( 'Email', 'wpcf' ),
        'validate' => array('required', 'email'),
        'inherited_field_type' => 'textfield',
    );
}

/**
 * View function.
 * 
 * @param type $params 
 */
function wpcf_fields_email_view( $params ) {
    $add = '';
    if ( !empty( $params['title'] ) ) {
        $add .= ' title="' . $params['title'] . '"';
        $title = $params['title'];
    } else {
        $add .= ' title="' . $params['field_value'] . '"';
        $title = $params['field_value'];
    }
    if ( !empty( $params['class'] ) ) {
        $add .= ' class="' . $params['class'] . '"';
    }
    if ( !empty( $params['style'] ) ) {
        $add .= ' style="' . $params['style'] . '"';
    }
    $output = '<a href="mailto:' . $params['field_value'] . '"' . $add . '>'
            . $title . '</a>';
    return $output;
}

/**
 * Editor callback form.
 */
function wpcf_fields_email_editor_callback( $field, $settings ) {
    return array(
        'supports' => array('styling', 'style'),
        'tabs' => array(
            'display' => array(
                'menu_title' => __( 'Display', 'wpcf' ),
                'title' => __( 'Display', 'wpcf' ),
                'content' => WPCF_Loader::template( 'editor-modal-email',
                        $settings ),
            )
        )
    );
}

/**
 * Editor callback form submit.
 */
function wpcf_fields_email_editor_submit( $data, $field, $context ) {
    $add = '';
    if ( !empty( $data['title'] ) ) {
        $add = ' title="' . strval( $data['title'] ) . '"';
    }
    if ( $context == 'usermeta' ) {
        $add .= wpcf_get_usermeta_form_addon_submit();
        $shortcode = wpcf_usermeta_get_shortcode( $field, $add );
    } else {
        $shortcode = wpcf_fields_get_shortcode( $field, $add );
    }

    return $shortcode;
}