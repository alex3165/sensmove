<?php
/**
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/embedded/includes/fields/video.php $
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
function wpcf_fields_video() {
    return array(
        'id' => 'wpcf-video',
        'title' => __( 'Video', 'wpcf' ),
        'description' => __( 'Video', 'wpcf' ),
        'wp_version' => '3.6',
        'inherited_field_type' => 'file',
        'validate' => array('required'),
    );
}

/**
 * View function.
 * 
 * @global type $wp_embed
 * @global object $wpdb
 *
 * @param type $field
 * @return string
 */
function wpcf_fields_video_view( $params ) {
    if ( is_string( $params['field_value'] ) ) {
        $params['field_value'] = stripslashes( $params['field_value'] );
    }
    $value = $params['field_value'];
    if ( empty( $value ) ) {
        return '__wpcf_skip_empty';
    }
    list($default_width, $default_height) = wpcf_media_size();
    $url = trim( strval( $value ) );
    $width = !empty( $params['width'] ) ? intval( $params['width'] ) : $default_width;
    $height = !empty( $params['height'] ) ? intval( $params['height'] ) : $default_height;
    $add = '';
    if ( !empty( $params['poster'] ) ) {
        $add .=" poster=\"{$params['poster']}\"";
    }
    if ( !empty( $params['loop'] ) ) {
        $add .= " loop=\"{$params['loop']}\"";
    }
    if ( !empty( $params['autoplay'] ) ) {
        $add .=" autoplay=\"{$params['autoplay']}\"";
    }
    if ( !empty( $params['preload'] ) ) {
        $add .=" preload=\"{$params['preload']}\"";
    }
    
    $shortcode = "[video width=\"{$width}\" height=\"{$height}\" src=\"{$url}\"{$add}]";
    $output = do_shortcode( $shortcode );
    if ( empty( $output ) ) {
        return '__wpcf_skip_empty';
    }
    return $output;
}

/**
 * Editor callback form.
 */
function wpcf_fields_video_editor_callback( $field, $data, $meta_type, $post ) {

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
    $data['file'] = !empty( $file ) ? $file : '';

    return array(
        'supports' => array(),
        'tabs' => array(
            'display' => array(
                'menu_title' => __( 'Display', 'wpcf' ),
                'title' => __( 'Display', 'wpcf' ),
                'content' => WPCF_Loader::template( 'editor-modal-video', $data ),
            )
        ),
        'settings' => $data,
    );
}

/**
 * Editor callback form submit.
 */
function wpcf_fields_video_editor_submit( $data, $field, $context ) {
    $add = '';
    if ( !empty( $data['width'] ) ) {
        $add .= " width=\"{$data['width']}\"";
    }
    if ( !empty( $data['height'] ) ) {
        $add .= " height=\"{$data['height']}\"";
    }
    if ( !empty( $data['poster'] ) ) {
        $add .=" poster=\"{$data['poster']}\"";
    }
    if ( !empty( $data['loop'] ) ) {
        $add .= " loop=\"{$data['loop']}\"";
    }
    if ( !empty( $data['autoplay'] ) ) {
        $add .=" autoplay=\"{$data['autoplay']}\"";
    }
    if ( !empty( $data['preload'] ) ) {
        $add .=" preload=\"{$data['preload']}\"";
    }
    if ( $context == 'usermeta' ) {
        $add .= wpcf_get_usermeta_form_addon_submit();
        $shortcode = wpcf_usermeta_get_shortcode( $field, $add );
    } else {
        $shortcode = wpcf_fields_get_shortcode( $field, $add );
    }

    return $shortcode;
}
