<?php
/*
 * Custom types functions.
 */
require_once WPCF_EMBEDDED_INC_ABSPATH . '/custom-types.php';

/**
 * Returns HTML formatted AJAX activation link.
 * 
 * @param type $post_type
 * @return type 
 */
function wpcf_admin_custom_types_get_ajax_activation_link($post_type) {
    return '<a href="' . admin_url('admin-ajax.php?action=wpcf_ajax&amp;'
                    . 'wpcf_action=activate_post_type&amp;wpcf-post-type='
                    . $post_type . '&amp;wpcf_ajax_update=wpcf_list_ajax_response_'
                    . $post_type) . '&amp;wpcf_ajax_callback=wpcfRefresh&amp;_wpnonce='
            . wp_create_nonce('activate_post_type')
            . '" class="wpcf-ajax-link" id="wpcf-list-activate-'
            . $post_type . '">'
            . __('Activate', 'wpcf') . '</a>';
}

/**
 * Returns HTML formatted AJAX deactivation link.
 * @param type $group_id
 * @return type 
 */
function wpcf_admin_custom_types_get_ajax_deactivation_link($post_type) {
    return '<a href="' . admin_url('admin-ajax.php?action=wpcf_ajax&amp;'
                    . 'wpcf_action=deactivate_post_type&amp;wpcf-post-type='
                    . $post_type . '&amp;wpcf_ajax_update=wpcf_list_ajax_response_'
                    . $post_type) . '&amp;wpcf_ajax_callback=wpcfRefresh&amp;_wpnonce='
            . wp_create_nonce('deactivate_post_type')
            . '" class="wpcf-ajax-link" id="wpcf-list-activate-'
            . $post_type . '">'
            . __('Deactivate', 'wpcf') . '</a>';
}