<?php
/*
 * Embedded functions.
 */

/**
 * Defines predefined capabilities.
 * 
 * @return array 
 */
function wpcf_access_types_caps_predefined() {
    $modes = array(
        'read' => array(
            'title' => __('Read', 'wpcf_access'),
            'role' => 'guest',
            'predefined' => 'read',
        ),
        'edit_own' => array(
            'title' => __('Edit own', 'wpcf_access'),
            'role' => 'contributor',
            'predefined' => 'edit_own',
        ),
        'delete_own' => array(
            'title' => __('Delete own', 'wpcf_access'),
            'role' => 'contributor',
            'predefined' => 'delete_own',
        ),
        'edit_any' => array(
            'title' => __('Edit any', 'wpcf_access'),
            'role' => 'editor',
            'predefined' => 'edit_any',
        ),
        'delete_any' => array(
            'title' => __('Delete any', 'wpcf_access'),
            'role' => 'editor',
            'predefined' => 'delete_any',
        ),
        'publish' => array(
            'title' => __('Publish', 'wpcf_access'),
            'role' => 'author',
            'predefined' => 'publish',
        ),
    );
    return $modes;
}

/**
 * Defines capabilities.
 * 
 * @return type 
 */
function wpcf_access_types_caps() {
    $caps = array(
        //
        // READ
        //
        'read_post' => array(
            'title' => __('Read post', 'wpcf_access'),
            'role' => 'guest',
            'predefined' => 'read',
        ),
        'read_private_posts' => array(
            'title' => __('Read private posts', 'wpcf_access'),
            'role' => 'contributor',
            'predefined' => 'edit_own',
        ),
        //
        // EDIT OWN
        //
        'edit_post' => array(
            'title' => __('Edit post', 'wpcf_access'),
            'role' => 'contributor',
            'predefined' => 'edit_own',
        ),
        'edit_posts' => array(
            'title' => __('Edit posts', 'wpcf_access'),
            'role' => 'contributor',
            'predefined' => 'edit_own',
        ),
        'edit_comment' => array(
            'title' => __('Moderate comments', 'wpcf_access'),
            'role' => 'author',
            'predefined' => 'edit_own',
            'fallback' => array('edit_published_posts', 'edit_others_posts'),
        ),
        //
        // DELETE OWN
        //
        'delete_post' => array(
            'title' => __('Delete post', 'wpcf_access'),
            'role' => 'contributor',
            'predefined' => 'delete_own',
        ),
        'delete_posts' => array(
            'title' => __('Delete posts', 'wpcf_access'),
            'role' => 'contributor',
            'predefined' => 'delete_own',
        ),
        'delete_private_posts' => array(
            'title' => __('Delete private posts', 'wpcf_access'),
            'role' => 'editor',
            'predefined' => 'delete_own',
        ),
        //
        // EDIT ANY
        //
        'edit_others_posts' => array(
            'title' => __('Edit others posts', 'wpcf_access'),
            'role' => 'editor',
            'predefined' => 'edit_any',
        ),
        // TODO this should go in publish
        'edit_published_posts' => array(
            'title' => __('Edit published posts', 'wpcf_access'),
            'role' => 'author',
            'predefined' => 'edit_own',
        ),
        'edit_private_posts' => array(
            'title' => __('Edit private posts', 'wpcf_access'),
            'role' => 'editor',
            'predefined' => 'edit_any',
        ),
        'moderate_comments' => array(
            'title' => __('Moderate comments', 'wpcf_access'),
            'role' => 'contributor',
            'predefined' => 'edit_any',
            'fallback' => array('edit_others_posts', 'edit_published_posts'),
        ),
        //
        // DELETE ANY
        //
        'delete_others_posts' => array(
            'title' => __('Delete others posts', 'wpcf_access'),
            'role' => 'editor',
            'predefined' => 'delete_any',
        ),
        // TODO this should go in publish
        'delete_published_posts' => array(
            'title' => __('Delete published posts', 'wpcf_access'),
            'role' => 'author',
            'predefined' => 'delete_own',
        ),
        //
        // PUBLISH
        //
        'publish_posts' => array(
            'title' => __('Publish post', 'wpcf_access'),
            'role' => 'author',
            'predefined' => 'publish',
        ),
        //
        // NOT SURE
        //
        'read' => array(
            'title' => __('Read', 'wpcf_access'),
            'role' => 'guest',
            'predefined' => 'read',
        ),
    );
    return apply_filters('wpcf_access_types_caps', $caps);
}

/**
 * Defines capabilities.
 * 
 * @return type 
 */
function wpcf_access_tax_caps() {
    $caps = array(
        'manage_terms' => array(
            'title' => __('Manage terms', 'wpcf_access'),
            'role' => 'editor',
            'predefined' => 'manage',
            'match' => array(
                'manage_' => array(
                    'match' => 'edit_others_',
                    'default' => 'manage_categories',
                ),
            ),
            'default' => 'manage_categories',
        ),
        'edit_terms' => array(
            'title' => __('Edit terms', 'wpcf_access'),
            'role' => 'contributor',
            'predefined' => 'edit',
            'match' => array(
                'edit_' => array(
                    'match' => 'edit_others_',
                    'default' => 'manage_categories',
                ),
            ),
            'default' => 'manage_categories',
        ),
        'delete_terms' => array(
            'title' => __('Delete terms', 'wpcf_access'),
            'role' => 'contributor',
            'predefined' => 'edit',
            'match' => array(
                'delete_' => array(
                    'match' => 'edit_others_',
                    'default' => 'manage_categories',
                ),
            ),
            'default' => 'manage_categories',
        ),
        'assign_terms' => array(
            'title' => __('Assign terms', 'wpcf_access'),
            'role' => 'contributor',
            'predefined' => 'edit',
            'match' => array(
                'assign_' => array(
                    'match' => 'edit_',
                    'default' => 'edit_posts',
                ),
            ),
            'default' => 'edit_posts',
        ),
    );
    return apply_filters('wpcf_access_tax_caps', $caps);
}

/**
 * Maps role to level.
 * 
 * @return string 
 */
function wpcf_access_role_to_level_map() {
    $map = array(
        'administrator' => 'level_10',
        'editor' => 'level_7',
        'author' => 'level_2',
        'contributor' => 'level_1',
        'subscriber' => 'level_0',
    );
    if (is_user_logged_in()) {
        require_once ABSPATH . '/wp-admin/includes/user.php';
        $roles = get_editable_roles();
        foreach ($roles as $role => $data) {
            for ($index = 10; $index > -1; $index--) {
                if (isset($data['capabilities']['level_' . $index])) {
                    $map[$role] = 'level_' . $index;
                    break;
                }
            }
        }
    }
    return $map;
}

/**
 * Maps role to level.
 * 
 * @param type $role
 * @return type 
 */
function wpcf_access_role_to_level($role) {
    $map = wpcf_access_role_to_level_map();
    return isset($map[$role]) ? $map[$role] : false;
}

/**
 * Checks if role is ranked higher.
 * 
 * @param type $role
 * @param type $compare
 * @return boolean 
 */
function wpcf_access_is_role_ranked_higher($role, $compare) {
    if ($role == $compare) {
        return true;
    }
    $level_role = wpcf_access_role_to_level($role);
    $level_compare = wpcf_access_role_to_level($compare);
    return wpcf_access_is_level_ranked_higher($level_role, $level_compare);
}

/**
 * Checks if level is ranked higher.
 * 
 * @param type $level
 * @param type $compare
 * @return boolean 
 */
function wpcf_access_is_level_ranked_higher($level, $compare) {
    if ($level == $compare) {
        return true;
    }
    $level = strpos($level, 'level_') === 0 ? substr($level, 0, 5) : $level;
    $compare = strpos($compare, 'level_') === 0 ? substr($compare, 0, 5) : $compare;
    return intval($level) > intval($compare);
}

/**
 * Orders roles by level.
 * 
 * @param type $roles
 * @return type 
 */
function wpcf_access_order_roles_by_level($roles) {
    $ordered_roles = array();
    for ($index = 10; $index > -1; $index--) {
        foreach ($roles as $role => $data) {
            if (isset($data['capabilities']['level_' . $index])) {
                $ordered_roles[$index][$role] = $data;
                unset($roles[$role]);
            }
        }
    }
    $ordered_roles['not_set'] = !empty($roles) ? $roles : array();
    return $ordered_roles;
}

/**
 * Gets all caps by level.
 * 
 * @global type $wpcf_access
 * @param type $level
 * @param type $context
 * @return type 
 */
function wpcf_access_user_get_caps_by_type($user_id, $context = 'types') {
    global $wpcf_access;
    static $cache = array();
    if (isset($cache[$user_id][$context])) {
        return $cache[$user_id][$context];
    }
    list($role, $level) = wpcf_access_rank_user($user_id);
    if (empty($role) || $level === false || empty($wpcf_access->settings->{$context})) {
        return array();
    }
    $caps = array();
    foreach ($wpcf_access->settings->{$context} as $type => $data) {
        if (!empty($data['permissions']) && is_array($data['permissions'])) {
            foreach ($data['permissions'] as $_cap => $_data) {
                if (isset($_data['role'])) {
                    $can = wpcf_access_is_level_ranked_higher($level,
                            wpcf_access_role_to_level($_data['role']));
                    $cap_data['context'] = $context;
                    $cap_data['parent'] = $type;
                    $cap_data['caps'][$_cap] = (bool) $can;
                    $caps[$type] = $cap_data;
                }
            }
        }
    }
    $cache[$user_id][$context] = $caps;
    return $caps;
}

/**
 * Determines highest ranked role and it's level.
 * 
 * @param type $user_id
 * @param type $rank
 * @return type 
 */
function wpcf_access_rank_user($user_id, $rank = 'high') {
    $user = get_userdata($user_id);
    if (empty($user)) {
        return array('guest', false);
    }
    $roles = get_editable_roles();
    $levels = wpcf_access_order_roles_by_level($roles);
    $role = false;
    $level = false;
    foreach ($levels as $_levels => $_level) {
        $current_level = $_levels;
        foreach ($_level as $_role => $_role_data) {
            if (in_array($_role, $user->roles)) {
                $role = $_role;
                $level = $current_level;
                if ($rank != 'low') {
                    return array($role, $level);
                }
            }
        }
    }
    if (!$role || !$level) {
        return array('guest', false);
    }
    return array($role, $level);
}

/**
 * Search for cap in collected rules.
 * 
 * @global type $wpcf_access
 * @param type $cap
 * @return type 
 */
function wpcf_access_search_cap($cap) {
    global $wpcf_access;
    $settings = array();
    if (isset($wpcf_access->rules->types[$cap])) {
        $settings = $wpcf_access->rules->types[$cap];
        $settings['_context'] = 'types';
    } else if (isset($wpcf_access->rules->tax[$cap])) {
        $settings = $wpcf_access->rules->tax[$cap];
        $settings['_context'] = 'tax';
    }
    return empty($settings) ? false : $settings;
}