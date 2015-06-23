<?php
/*
 * Ordering repetitive fields.
 * 
 * @todo sorting option.
 * @todo drag-and-drop.
 * @todo CSS adjustment
 * @todo move buttons and inserting new field
 * 
 * @since Types 1.1.3.2 and WP 3.4.2 (3.5 RC)
 */

// Add buttons
//'wpcf_post_edit_field';

/**
 * HTML formatted output for 'Add Another Field'.
 * 
 * @param type $field
 * @param type $post
 * @return string 
 */
function wpcf_repetitive_add_another_button( $field, $post ) {

    global $wpcf;

    $title = wpcf_translate( "field {$field['id']} name", $field['name'] );
    $button = '<a href="'
            . admin_url( 'admin-ajax.php?action=wpcf_ajax'
                    . '&amp;wpcf_action=repetitive_add'
                    . '&amp;_wpnonce=' . wp_create_nonce( 'repetitive_add' ) )
            . '&amp;field_id=' . $field['id'] . '&amp;field_id_md5='
            . md5( $field['id'] )
            . '&amp;post_id=' . $post->ID
            . '" class="wpcf-repetitive-add button-primary">'
            . sprintf( __( 'Add Another %s', 'wpcf' ), $title ) . '</a>';
    return $button;
}

/**
 * HTML formatted output for 'Delete Field'.
 * 
 * @param type $field
 * @param type $post
 * @return string 
 */
function wpcf_repetitive_delete_button( $field, $post, $meta_id ) {

    // TODO WPML move Add repetitive control buttons if not copied by WPML
    if ( wpcf_wpml_field_is_copied( $field ) ) {
        return '';
    }

    // Let's cache calls
    static $cache = array();
    if ( empty( $field ) ) {
        $field = array();
    }
    if ( empty( $post ) ) {
        $post = array();
    }
    $cache_key = md5( serialize( (array) $field ) . $meta_id . serialize( (array) $post ) );

    // Return cached if there
    if ( isset( $cache[$cache_key] ) ) {
        return $cache[$cache_key];
    }

    // If post is new show different delete button
    if ( empty( $post->ID ) ) {
        $cache[$cache_key] = wpcf_repetitive_delete_new_button( $field, $post );
        return $cache[$cache_key];
    }

    // Regular delete button
    $button = '';
    $title = wpcf_translate( "field {$field['id']} name", $field['name'] );
    /*
     * No need for old_value anymore.
     * Use meta_id.
     */
    $button .= '&nbsp;<a href="'
            . admin_url( 'admin-ajax.php?action=wpcf_ajax'
                    . '&amp;wpcf_action=repetitive_delete'
                    . '&amp;_wpnonce=' . wp_create_nonce( 'repetitive_delete' )
                    . '&amp;post_id=' . $post->ID . '&amp;field_id='
                    . $field['id'] . '&amp;meta_id='
                    . $meta_id )
            . '&amp;wpcf_warning=' . __( 'Are you sure?', 'wpcf' )
            . '&amp;field_id_md5='
            . md5( $field['id'] )
            . '" class="wpcf-repetitive-delete button-secondary">'
            . sprintf( __( 'Delete %s', 'wpcf' ), $title ) . '</a>';


    // Cache it
    $cache[$cache_key] = $button;
    return $button;
}

/**
 * HTML formatted output for NEW 'Delete Field'.
 * 
 * @param type $field
 * @param type $post
 * @return string 
 */
function wpcf_repetitive_delete_new_button( $field, $post ) {
    $button = '&nbsp;<a href="javascript:void(0);"'
            . ' class="wpcf-repetitive-delete wpcf-repetitive-delete-new button-secondary">'
            . __( 'Delete Field', 'wpcf' ) . '</a>';
    return $button;
}

/**
 * HTML formatted repetitive form.
 * 
 * Add this for each field processed.
 * 
 * @uses hook 'wpcf_post_edit_field'
 * @param type $field
 * @return string 
 */
function wpcf_repetitive_form( $field, $post ) {
    // TODO WPML move
    // Add repetitive control buttons if not copied by WPML
    if ( wpcf_wpml_field_is_copied( $field ) ) {
        return '<div style="clear:both;"></div>';
    }
    $repetitive_form = '';
    $repetitive_form .= '<div class="wpcf-repetitive-buttons">';
    $repetitive_form .= wpcf_repetitive_add_another_button( $field, $post );
    $repetitive_form .= '</div><div style="clear:both;"></div>';
    return $repetitive_form;
}

/**
 * Returns HTML formatted drag button.
 * 
 * @param type $field
 * @param type $post
 * @return string 
 */
function wpcf_repetitive_drag_button( $field, $post ) {
    // TODO WPML move
    if ( wpcf_wpml_field_is_copied( $field ) ) {
        return '';
    }
    return '<div class="wpcf-repetitive-drag">&nbsp;</div>';
}