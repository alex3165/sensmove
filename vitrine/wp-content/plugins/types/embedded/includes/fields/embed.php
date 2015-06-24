<?php

/**
 * Register data (called automatically).
 *
 * @return type
 */
function wpcf_fields_embed() {
    return array(
        'id' => 'wpcf-embed',
        'title' => __( 'Embedded Media', 'wpcf' ),
        'description' => __( 'Embedded Media', 'wpcf' ),
        'validate' => array('required', 'url' => array('forced' => true)),
        'wp_version' => '3.6',
    );
}

/**
 * Meta box form.
 *
 * @param type $field
 * @return string
 */
function wpcf_fields_embed_meta_box_form( $field ) {
    $form = array();
    $form['name'] = array(
        '#type' => 'textfield',
        '#name' => 'wpcf[' . $field['slug'] . ']',
    );
    return $form;
}

/**
 * View function.
 *
 * @global type $wp_embed
 * @param type $field
 * @return string
 */
function wpcf_fields_embed_view( $params ) {
    global $wp_embed;
    $value = $params['field_value'];
    if ( empty( $value ) ) {
        return '__wpcf_skip_empty';
    }
    list($default_width, $default_height) = wpcf_media_size();
    $url = trim( strval( $value ) );
    if ( !types_validate( 'url', $url ) ) {
        return '__wpcf_skip_empty';
    }
    $width = !empty( $params['width'] ) ? intval( $params['width'] ) : $default_width;
    $height = !empty( $params['height'] ) ? intval( $params['height'] ) : $default_height;

    $shortcode = '[embed width="' . $width . '" height="' . $height . '"]' . $url . '[/embed]';
    $output = $wp_embed->run_shortcode( $shortcode );
    if ( empty( $output ) ) {
        return '__wpcf_skip_empty';
    }
    return $output;
}

/**
 * Editor callback form.
 *
 * @global object $wpdb
 */
function wpcf_fields_embed_editor_callback( $field, $data, $meta_type, $post ) {

    // Get attachment
    $attachment_id = false;
    if ( !empty( $post->ID ) ) {
        $file = get_post_meta( $post->ID,
                wpcf_types_get_meta_prefix( $field ) . $field['slug'], true );
        if ( empty( $file ) ) {
            $user_id = wpcf_usermeta_get_user();
            $file = get_user_meta( $user_id,
                    wpcf_types_get_meta_prefix( $field ) . $field['slug'], true );
        }
        if ( !empty( $file ) ) {
            // Get attachment by guid
            global $wpdb;
            $attachment_id = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'attachment' AND guid=%s",
                    $file
                )
            );
        }
    }

    // Set data
    $data['attachment_id'] = $attachment_id;
    $data['file'] = !empty($file) ? $file : '';

    return array(
        'supports' => array(),
        'tabs' => array(
            'display' => array(
                'menu_title' => __( 'Display', 'wpcf' ),
                'title' => __( 'Display', 'wpcf' ),
                'content' => WPCF_Loader::template( 'editor-modal-embed', $data ),
            )
        ),
        'settings' => $data,
    );
}

/**
 * Editor callback form submit.
 */
function wpcf_fields_embed_editor_submit( $data, $field, $context ) {
    $add = '';
    if ( !empty( $data['width'] ) ) {
        $add .= " width=\"{$data['width']}\"";
    }
    if ( !empty( $data['height'] ) ) {
        $add .= " height=\"{$data['height']}\"";
    }
    if ( $context == 'usermeta' ) {
        $add .= wpcf_get_usermeta_form_addon_submit();
        $shortcode = wpcf_usermeta_get_shortcode( $field, $add );
    } else {
        $shortcode = wpcf_fields_get_shortcode( $field, $add );
    }

    return $shortcode;
}
