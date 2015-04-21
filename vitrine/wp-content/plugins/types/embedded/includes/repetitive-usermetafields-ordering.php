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
function wpcf_repetitive_add_another_umbutton( $field, $user_id ) {

    global $wpcf;

    $title = wpcf_translate( "field {$field['id']} name", $field['name'] );
    $button = '<a href="'
            . admin_url( 'admin-ajax.php?action=wpcf_ajax'
                    . '&amp;wpcf_action=um_repetitive_add'
                    . '&amp;_wpnonce=' . wp_create_nonce( 'um_repetitive_add' ) )
            . '&amp;field_id=' . $field['id'] . '&amp;field_id_md5='
            . md5( $field['id'] )
            . '&amp;user_id=' . $user_id
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
function wpcf_repetitive_delete_umbutton( $field, $user_id, $meta_id ) {

    // TODO WPML move Add repetitive control buttons if not copied by WPML
    if ( wpcf_wpml_is_translated_profile_page( $field ) ) {
        return '';
    }

    // Let's cache calls
    static $cache = array();
    if ( empty( $field ) ) {
        $field = array();
    }
    if ( empty( $user_id ) ) {
        $post = array();
    }
    $cache_key = md5( serialize( (array) $field ) . $meta_id . serialize( (array) $user_id ) );

    // Return cached if there
    if ( isset( $cache[$cache_key] ) ) {
        return $cache[$cache_key];
   }

    // If post is new show different delete button
    if ( empty( $user_id ) ) {
        $cache[$cache_key] = wpcf_repetitive_delete_new_umbutton( $field, $user_id );
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
                    . '&amp;wpcf_action=um_repetitive_delete'
                    . '&amp;_wpnonce=' . wp_create_nonce( 'um_repetitive_delete' )
                    . '&amp;user_id=' . $user_id . '&amp;field_id='
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
function wpcf_repetitive_delete_new_umbutton( $field, $post ) {
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
function wpcf_repetitive_umform( $field, $user_id ) {
    // Add repetitive control buttons if not copied by WPML
    // TODO WPML move
    if ( wpcf_wpml_is_translated_profile_page( $field ) ) {
        return '';
    }
    $repetitive_form = '';
    $repetitive_form .= '<div class="wpcf-repetitive-buttons">';
    $repetitive_form .= wpcf_repetitive_add_another_umbutton( $field, $user_id );
    $repetitive_form .= '</div><div style="clear:both;"></div>';
    return $repetitive_form;
}
