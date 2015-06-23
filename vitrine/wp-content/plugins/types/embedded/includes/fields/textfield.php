<?php

/**
 * Register data (called automatically).
 * 
 * @return type 
 */
function wpcf_fields_textfield() {
    return array(
        'id' => 'wpcf-texfield',
        'title' => __( 'Single line', 'wpcf' ),
        'description' => __( 'Textfield', 'wpcf' ),
        'validate' => array('required'),
    );
}

/**
 * Meta box form.
 * 
 * @param type $field
 * @return string 
 */
function wpcf_fields_textfield_meta_box_form( $field ) {
    $form = array();
    $form['name'] = array(
        '#type' => 'textfield',
        '#name' => 'wpcf[' . $field['slug'] . ']',
    );
    return $form;
}