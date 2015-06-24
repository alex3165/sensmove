<?php
//           'wp-types-user-group'
//      'wpcf-usermeta';
define("WORKINGTYPE", "usermeta");

/**
 * Saves group's user roles.
 *
 * @param type $group_id
 * @param type $post_types
 */
function wpcf_admin_fields_save_group_showfor($group_id, $post_types) {
    if (empty($post_types)) {
        update_post_meta($group_id, '_wp_types_group_showfor', 'all');
        return true;
    }
    $post_types = ',' . implode(',', (array) $post_types) . ',';
    update_post_meta($group_id, '_wp_types_group_showfor', $post_types);
}



/**
 * Gets user roles supported by specific group.
 *
 * @param type $group_id
 * @return type
 */
function wpcf_admin_get_groups_showfor_by_group($group_id) {
    $for_users = get_post_meta($group_id, '_wp_types_group_showfor', true);
    if (empty($for_users) || $for_users == 'all') {
        return array();
    }
    $for_users = explode(',', trim($for_users, ','));
    return $for_users;
}
