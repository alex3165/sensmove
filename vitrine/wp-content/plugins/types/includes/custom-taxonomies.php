<?php
/*
 * Custom taxonomies functions.
 */
require_once WPCF_EMBEDDED_INC_ABSPATH . '/custom-taxonomies.php';

/**
 * Returns HTML formatted AJAX activation link.
 * 
 * @param type $taxonomy
 * @return type 
 */
function wpcf_admin_custom_taxonomies_get_ajax_activation_link($taxonomy) {
    return '<a href="' . admin_url('admin-ajax.php?action=wpcf_ajax'
                    . '&amp;wpcf_action=activate_taxonomy&amp;wpcf-tax='
                    . $taxonomy . '&amp;wpcf_ajax_update=wpcf_list_ajax_response_'
                    . $taxonomy) . '&amp;wpcf_ajax_callback=wpcfRefresh&amp;_wpnonce='
            . wp_create_nonce('activate_taxonomy')
            . '" class="wpcf-ajax-link" id="wpcf-list-activate-'
            . $taxonomy . '">'
            . __('Activate', 'wpcf') . '</a>';
}

/**
 * Returns HTML formatted AJAX deactivation link.
 * 
 * @param type $taxonomy
 * @return type 
 */
function wpcf_admin_custom_taxonomies_get_ajax_deactivation_link($taxonomy) {
    return '<a href="' . admin_url('admin-ajax.php?action=wpcf_ajax&amp;'
                    . 'wpcf_action=deactivate_taxonomy&amp;wpcf-tax='
                    . $taxonomy . '&amp;wpcf_ajax_update=wpcf_list_ajax_response_'
                    . $taxonomy) . '&amp;wpcf_ajax_callback=wpcfRefresh&amp;_wpnonce='
            . wp_create_nonce('deactivate_taxonomy')
            . '" class="wpcf-ajax-link" id="wpcf-list-activate-'
            . $taxonomy . '">'
            . __('Deactivate', 'wpcf') . '</a>';
}