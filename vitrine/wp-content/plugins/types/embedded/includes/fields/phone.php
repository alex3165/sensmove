<?php
/**
 * Register data (called automatically).
 * 
 * @return type 
 */
function wpcf_fields_phone() {
    return array(
        'id' => 'wpcf-phone',
        'title' => __('Phone', 'wpcf'),
        'description' => __('Phone', 'wpcf'),
        'validate' => array('required'),
        'inherited_field_type' => 'textfield',
    );
}