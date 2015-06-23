<?php
/**
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/embedded/includes/fields/audio.php $
 * $LastChangedDate: 2014-11-18 06:47:25 +0000 (Tue, 18 Nov 2014) $
 * $LastChangedRevision: 1027712 $
 * $LastChangedBy: iworks $
 *
 */

/**
 * Register data (called automatically).
 * 
 * @return type 
 */
function wpcf_fields_audio() {
    return array(
        'id' => 'wpcf-audio',
        'title' => __( 'Audio', 'wpcf' ),
        'description' => __( 'Audio', 'wpcf' ),
        'wp_version' => '3.6',
        'inherited_field_type' => 'file',
        'validate' => array('required'),
    );
}

/**
 * View function.
 * 
 * @global type $wp_embed
 * @param type $params
 * @return string
 */
function wpcf_fields_audio_view( $params ) {
    if ( is_string( $params['field_value'] ) ) {
        $params['field_value'] = stripslashes( $params['field_value'] );
    }
    $value = $params['field_value'];
    if ( empty( $value ) ) {
        return '__wpcf_skip_empty';
    }
    $url = trim( strval( $value ) );
    $add = '';
    if ( !empty( $params['loop'] ) ) {
        $add .= " loop=\"{$params['loop']}\"";
    }
    if ( !empty( $params['autoplay'] ) ) {
        $add .=" autoplay=\"{$params['autoplay']}\"";
    }
    if ( !empty( $params['preload'] ) ) {
        $add .=" preload=\"{$params['preload']}\"";
    }

    $shortcode = "[audio src=\"{$url}\"{$add}]";
    $output = do_shortcode( $shortcode );
    if ( empty( $output ) ) {
        return '__wpcf_skip_empty';
    }
    return $output;
}



/**
 * Editor callback form.
 */
function wpcf_fields_audio_editor_callback( $field, $data, $meta_type, $post ) {
    return array(
        'supports' => array(),
        'tabs' => array(
            'display' => array(
                'menu_title' => __( 'Display', 'wpcf' ),
                'title' => __( 'Display', 'wpcf' ),
                'content' => WPCF_Loader::template( 'editor-modal-audio', $data ),
            )
        ),
        'settings' => $data,
    );
}

/**
 * Editor callback form submit.
 */
function wpcf_fields_audio_editor_submit( $data, $field, $context ) {
    $add = '';
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
