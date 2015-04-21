<?php
/*
 * CRED related code.
 * srdjan
 */

/*
 * Adds all groups and fields to be processed when on CRED form.
 * This is needed because meta-boxes are omitted but editor dropdown needed. 
 * 
 * added by srdjan
 * USE THIS IF YOU WANT TO SHOW TYPES ICON AND DROPDOWN
 */
//add_filter( 'wpcf_post_groups', 'wpcf_cred_post_groups_filter', 10, 3 );

/**
 * Filters groups on post edit page.
 * 
 * @param type $groups
 * @param type $post
 * @return type 
 */
function wpcf_cred_post_groups_filter( $groups, $post ) {
    if ( isset( $post->post_type ) && $post->post_type == 'cred-form' ) {
        return wpcf_admin_fields_get_groups( 'wp-types-group', 'group_active',
                        'fields_active' );
        return wpcf_admin_fields_get_groups();
    }
    return $groups;
}
