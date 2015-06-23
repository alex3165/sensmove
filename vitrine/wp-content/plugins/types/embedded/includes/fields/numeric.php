<?php
/*
 * Numeric field.
 */

// Filters
add_filter( 'wpcf_fields_type_numeric_value_save',
        'wpcf_fields_numeric_value_save_filter', 10, 3 );
add_filter( 'wpcf_fields_type_numeric_value_display',
        'wpcf_fields_type_numeric_value_display_by_locale', 10, 3 );
add_filter( 'wpcf_fields_numeric_meta_box_form_value_display',
        'wpcf_fields_numeric_meta_box_form_value_display_by_locale', 10, 3 );

/**
 * Register data (called automatically).
 * 
 * @return type 
 */
function wpcf_fields_numeric() {
    return array(
        'id' => 'wpcf-numeric',
        'title' => __( 'Numeric', 'wpcf' ),
        'description' => __( 'Numeric', 'wpcf' ),
        'validate' => array('required', 'number' => array('forced' => true)),
        'inherited_field_type' => 'textfield',
        'meta_key_type' => 'NUMERIC',
        'meta_box_js' => array('wpcf_field_number_validation_fix' => array(
                'inline' => 'wpcf_field_number_validation_fix')),
    );
}

function wpcf_fields_numeric_meta_box_form_value_display_by_locale( $field ){
    $locale = localeconv();
    if ( $locale['decimal_point'] != '.' ) {
        $field['#value'] = str_replace( '.', ',', $field['#value'] );
    }
    return $field;
}

/**
 * wpcf_fields_numeric_value_save_filter
 *
 * if decimal_point = comma, replace point to comma.
 */
function wpcf_fields_type_numeric_value_display_by_locale( $val ){
    $locale = localeconv();
    if ( $locale['decimal_point'] != '.' ) {
        $val = str_replace( '.', ',', $val );
    }
    return $val;
}

/**
 * wpcf_fields_numeric_value_save_filter
 *
 * if decimal_point = comma, replace comma to point.
 */
function wpcf_fields_numeric_value_save_filter( $val ){
    $locale = localeconv();
    if ( $locale['decimal_point'] != '.' ) {
        $val = str_replace( ',', '.', $val );
    }
    return $val;
}

/**
 * wpcf_field_number_validation_fix
 *
 * Fix JS validation for field:numeric. Allow comma validation 
 */
function wpcf_field_number_validation_fix(){
    $locale = localeconv();
    if ( $locale['decimal_point'] != '.' ) {
        wp_enqueue_script( 'wpcf-numeric',
                WPCF_EMBEDDED_RES_RELPATH
                . '/js/numeric_fix.js', array('jquery'), WPCF_VERSION );
    }
}

/**
 * Editor callback form.
 */
function wpcf_fields_numeric_editor_callback( $field, $settings ) {
    return array(
        'supports' => array('style'),
        'tabs' => array(
            'display' => array(
                'menu_title' => __( 'Display', 'wpcf' ),
                'title' => __( 'Display', 'wpcf' ),
                'content' => WPCF_Loader::template( 'editor-modal-numeric',
                        $settings ),
            )
        )
    );
}

/**
 * Editor callback form submit.
 */
function wpcf_fields_numeric_editor_submit( $data, $field, $context ) {
    $add = '';
    if ( !empty( $data['format'] ) ) {
        $add .= ' format="' . strval( $data['format'] ) . '"';
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
 * View function.
 * 
 * @param type $params 
 */
function wpcf_fields_numeric_view( $params ) {
    $output = '';
    if ( !empty( $params['format'] ) ) {
        $patterns = array('/FIELD_NAME/', '/FIELD_VALUE/');
        $replacements = array($params['field']['name'], $params['field_value']);
        $output = preg_replace( $patterns, $replacements, $params['format'] );
        $output = sprintf( $output, $params['field_value'] );
    } else {
        $output = $params['field_value'];
    }
    return $output;
}