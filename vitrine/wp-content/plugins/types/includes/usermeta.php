<?php
/*
 * User Meta and groups functions exitend includes/fields.php 
 */
require_once WPCF_EMBEDDED_INC_ABSPATH . '/usermeta.php';

/**
 * Returns HTML formatted AJAX activation link for usermeta.
 * 
 * @param type $group_id
 * @return type 
 */
function wpcf_admin_usermeta_get_ajax_activation_link($group_id)
{
    return sprintf(
        '<a href="%s" class="wpcf-ajax-link" id="wpcf-list-activate-%d">%s</a>',
        wpcf_admin_usermeta_get_ajax_link('activate', $group_id),
        $group_id,
        __('Activate', 'wpcf')
    );
}

/**
 * Returns HTML formatted AJAX deactivation link for usermeta.
 * @param type $group_id
 * @return type 
 */
function wpcf_admin_usermeta_get_ajax_deactivation_link($group_id) {
    return sprintf(
        '<a href="%s" class="wpcf-ajax-link" id="wpcf-list-activate-%d">%s</a>',
        wpcf_admin_usermeta_get_ajax_link('deactivate', $group_id),
        $group_id,
        __('Deactivate', 'wpcf')
    );
}

/**
 * Helper function to build url
 *
 * @param string $status status of action
 * @param int $group_id group id
 * @return string link for Activate/Deactivate action
 */
function wpcf_admin_usermeta_get_ajax_link($status, $group_id)
{
    /**
     * sanitize status
     */
    if ( !preg_match('/^(de)?activate$/', $status ) ) {
        return '#wrong-status';
    }
    /**
     * sanitize group_id
     */
    if ( !is_numeric($group_id) ) {
        return '#wrong-group_id';
    }
    /**
     * build link
     */
    return add_query_arg(
        array(
            'action' => 'wpcf_ajax',
            'wpcf_action' => $status.'_user_group',
            'group_id' => $group_id,
            'wpcf_ajax_update' => 'wpcf_list_ajax_response_' . $group_id,
            '_wpnonce' => '' . wp_create_nonce($status.'_user_group'),
        ),
        admin_url('admin-ajax.php')
    );
}
