<?php
/*
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/embedded/includes/fields/file.php $
 * $LastChangedDate: 2015-03-16 12:03:31 +0000 (Mon, 16 Mar 2015) $
 * $LastChangedRevision: 1113864 $
 * $LastChangedBy: iworks $
 *
 */
/**
 * Register data (called automatically).
 *
 * @return type
 */
function wpcf_fields_file() {
    return array(
        'id' => 'wpcf-file',
        'title' => __( 'File', 'wpcf' ),
        'description' => __( 'File', 'wpcf' ),
        'validate' => array('required'),
    );
}

/**
 * Form data for post edit page.
 *
 * @global object $wpdb
 *
 * @param type $field
 */
function wpcf_fields_file_meta_box_form( $field ) {
    add_thickbox();
    $button_text = sprintf( __( 'Select %s', 'wpcf' ), $field['type'] );
    // Set ID
    $element_id = 'wpcf-fields-' . wpcf_unique_id( serialize( func_get_args() ) );
    $attachment_id = false;

    // Get attachment by guid
    global $wpdb;
    if ( !empty( $field['value'] ) ) {
        $attachment_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'attachment' AND guid=%s",
                $field['value']
            )
        );
    }

    // Set preview
    $preview = '';
    // TODO WPML move
    if ( !wpcf_wpml_field_is_copied( $field ) ) {
        if ( !empty( $attachment_id ) ) {
            $preview = wp_get_attachment_image( $attachment_id, 'thumbnail' );
        } else {
            // If external image set preview
            $file_path = parse_url( $field['value'] );
            if ( $file_path && isset( $file_path['path'] ) )
                $file = pathinfo( $file_path['path'] );
            else
                $file = pathinfo( $field['value'] );
            if ( isset( $file['extension'] )
                    && in_array( strtolower( $file['extension'] ),
                            array('jpg', 'jpeg', 'gif', 'png') ) ) {
                $preview = '<img alt="" src="' . $field['value'] . '" />';
            }
        }
    }

    // Set button
    // TODO WPML move
    if ( !wpcf_wpml_field_is_copied( $field ) ) {
        if ( !empty( $field['#attributes']['readonly'] ) || !empty( $field['#attributes']['disabled'] ) ) {
            $button = '';
        } else {
            $button = '<a href="javascript:void(0);"'
                    . ' class="wpcf-fields-'
                    . ( $field['type'] == 'image' ? 'image' : 'file' )
                    . '-upload-link button-secondary"'
                    . ' id="' . $element_id . '-upload" '
                    . "data-types='{\"type\":\"{$field['type']}\",\"id\":\"{$field['id']}\"}'>"
                    . $button_text . '</a>';
        }
    } else {
        $button = '';
    }

    // Set form
    $form = array(
        '#type' => 'textfield',
        '#id' => $element_id . '-upload-holder',
        '#name' => 'wpcf[' . $field['slug'] . ']',
        '#suffix' => '&nbsp;' . $button,
        '#after' => '<div id="' . $element_id
        . '-upload-holder-preview"'
        . ' class="wpcf-fields-file-preview">' . $preview . '</div>',
        '#attributes' => array('class' => 'wpcf-fields-file-textfield'),
    );

    return $form;
}

/**
 * Adds 'Types' column to media item table.
 *
 * @param type $form_fields
 * @param type $post
 * @return type
 */
function wpcf_fields_file_attachment_fields_to_edit_filter( $form_fields, $post ) {
    // Reset form
    $form_fields = array();
    $type = (strpos( $post->post_mime_type, 'image/' ) !== false) ? 'image' : 'file';
    /*
     * Since Types 1.3.2 we use wp_get_attachment_url()
     * See
     * /wp-includes/post.php
     * wp_get_attachment_url()
     * line #4228
     * If any of the above options failed, Fallback on the GUID as used pre-2.7,
     * not recommended to rely upon this.
     */
//    $url = $post->guid;
    $url = wp_get_attachment_url( $post->ID );
    $form_fields['wpcf_fields_file'] = array(
        'label' => __( 'Types', 'wpcf' ),
        'input' => 'html',
        'html' => '<a href="#" title="' . $url
        . '" class="wpcf-fields-file-insert-button'
        . ' button-primary" onclick="wpcfFieldsFileMediaTrigger(\''
        . $url . '\', \'' . $type . '\')">'
        . __( 'Use as field value', 'wpcf' ) . '</a><br /><br />',
    );
    return $form_fields;
}

/**
 * View function.
 *
 * @param type $params
 */
function wpcf_fields_file_view( $params ) {
    $output = '';
    if ( isset( $params['link'] ) && $params['link'] == 'true' ) {
        $title = '';
        $add = '';
        if ( !empty( $params['title'] ) ) {
            $add .= ' title="' . $params['title'] . '"';
            $title .= $params['title'];
        } else {
            $add .= ' title="' . $params['field_value'] . '"';
            $title .= $params['field_value'];
        }
        if ( !empty( $params['class'] ) ) {
            $add .= ' class="' . $params['class'] . '"';
        }
        if ( !empty( $params['style'] ) ) {
            $add .= ' style="' . $params['style'] . '"';
        }
        $output = '<a href="' . $params['field_value'] . '"' . $add . '>'
                . $title . '</a>';
    } else {
        $output = $params['field_value'];
    }

    return $output;
}

/**
 * Editor callback form.
 *
 * @global object $wpdb
 *
 */
function wpcf_fields_file_editor_callback( $field, $data, $meta_type, $post ) {

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
        'supports' => array('styling', 'style'),
        'tabs' => array(
            'display' => array(
                'menu_title' => __( 'Display', 'wpcf' ),
                'title' => __( 'Display', 'wpcf' ),
                'content' => WPCF_Loader::template( 'editor-modal-file', $data ),
            )
        ),
        'settings' => $data,
    );
}

/**
 * Editor callback form submit.
 */
function wpcf_fields_file_editor_submit( $data, $field, $context ) {
    $add = '';
    $add .= ' link="true"';
    if ( !empty( $data['title'] ) ) {
        $add .= ' title="' . strval( $data['title'] ) . '"';
    }
    if ( $context == 'usermeta' ) {
        $add .= wpcf_get_usermeta_form_addon_submit();
        $shortcode = wpcf_usermeta_get_shortcode( $field, $add );
    } else {
        $shortcode = wpcf_fields_get_shortcode( $field, $add );
    }

    return $shortcode;
}

/**
 * Filters media TABs.
 *
 * @param type $tabs
 * @return type
 */
function wpcf_fields_file_media_upload_tabs_filter( $tabs ) {
    unset( $tabs['type_url'] );
    return $tabs;
}
