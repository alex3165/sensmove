<?php
/**
 * Custom taxonomies registration.
 */

/**
 * Returna default custom taxonomy structure.
 *
 * @return array
 */
function wpcf_custom_taxonomies_default() {
    return array(
        'slug' => '',
        'description' => '',
        'supports' => array(),
        'public' => true,
        'show_in_nav_menus' => true,
        'hierarchical' => false,
        'show_ui' => true,
        'show_tagcloud' => true,
        'show_admin_column' => false,
        'update_count_callback' => '',
        'query_var_enabled' => true,
        'query_var' => '',
        'rewrite' => array(
            'enabled' => true,
            'slug' => '',
            'with_front' => true,
            'hierarchical' => true
        ),
        'capabilities' => false,
        'labels' => array(
            'name' => '',
            'singular_name' => '',
            'search_items' => 'Search %s',
            'popular_items' => 'Popular %s',
            'all_items' => 'All %s',
            'parent_item' => 'Parent %s',
            'parent_item_colon' => 'Parent %s:',
            'edit_item' => 'Edit %s',
            'update_item' => 'Update %s',
            'add_new_item' => 'Add New %s',
            'new_item_name' => 'New %s Name',
            'separate_items_with_commas' => 'Separate %s with commas',
            'add_or_remove_items' => 'Add or remove %s',
            'choose_from_most_used' => 'Choose from the most used %s',
            'menu_name' => '%s',
        ),
        'meta_box_cb' => array(
            'disabled' => false,
            'callback' => null,
        ),
    );
}

/**
 * Inits custom taxonomies.
 */
function wpcf_custom_taxonomies_init() {
    $custom_taxonomies = get_option( 'wpcf-custom-taxonomies', array() );
    if ( !empty( $custom_taxonomies ) ) {
        foreach ( $custom_taxonomies as $taxonomy => $data ) {
            wpcf_custom_taxonomies_register( $taxonomy, $data );
        }
    }
}

/**
 * Registers custom taxonomies.
 *
 * @param type $post_type
 * @param type $data
 */
function wpcf_custom_taxonomies_register( $taxonomy, $data ) {
    if ( !empty( $data['disabled'] ) ) {
        return false;
    }
    // Set object types
    if ( !empty( $data['supports'] ) && is_array( $data['supports'] ) ) {
        $object_types = array_keys( $data['supports'] );
    } else {
        $object_types = array();
    }
    $data = apply_filters( 'types_taxonomy', $data, $taxonomy );
    // Set labels
    if ( !empty( $data['labels'] ) ) {
        if ( !isset( $data['labels']['name'] ) ) {
            $data['labels']['name'] = $taxonomy;
        }
        if ( !isset( $data['labels']['singular_name'] ) ) {
            $data['labels']['singular_name'] = $data['labels']['name'];
        }
        foreach ( $data['labels'] as $label_key => $label ) {
            $data['labels'][$label_key] = $label = stripslashes( $label );
            switch ( $label_key ) {
                case 'parent_item':
                case 'parent_item_colon':
                case 'edit_item':
                case 'update_item':
                case 'add_new_item':
                case 'new_item_name':
                    $data['labels'][$label_key] = sprintf( $label,
                            $data['labels']['singular_name'] );
                    break;

                case 'search_items':
                case 'popular_items':
                case 'all_items':
                case 'separate_items_with_commas':
                case 'add_or_remove_items':
                case 'choose_from_most_used':
                case 'menu_name':
                    $data['labels'][$label_key] = sprintf( $label,
                            $data['labels']['name'] );
                    break;
            }
        }
    }
    $data['description'] = !empty( $data['description'] ) ? htmlspecialchars( stripslashes( $data['description'] ),
                    ENT_QUOTES ) : '';
    $data['public'] = (empty( $data['public'] ) || strval( $data['public'] ) == 'hidden') ? false : true;
    $data['show_ui'] = (empty( $data['show_ui'] ) || !$data['public']) ? false : true;
    $data['hierarchical'] = (empty( $data['hierarchical'] ) || $data['hierarchical'] == 'flat') ? false : true;
    $data['show_in_nav_menus'] = !empty( $data['show_in_nav_menus'] );
    if ( empty( $data['query_var_enabled'] ) ) {
        $data['query_var'] = false;
    } else if ( empty( $data['query_var'] ) ) {
        $data['query_var'] = true;
    }
    if ( !empty( $data['rewrite']['enabled'] ) ) {
        $data['rewrite']['with_front'] = !empty( $data['rewrite']['with_front'] );
        $data['rewrite']['hierarchical'] = !empty( $data['rewrite']['hierarchical'] );
        // Make sure that rewrite/slug has a value
        if ( !isset( $data['rewrite']['slug'] ) || $data['rewrite']['slug'] == '' ) {
            $data['rewrite']['slug'] = $data['slug'];
        }
    } else {
        $data['rewrite'] = false;
    }
    /**
     * meta_box_cb
     */
    if ( isset($data['meta_box_cb']['disabled'])) {
        $data['meta_box_cb'] = false;
    } else if ( isset($data['meta_box_cb']['callback']) && !empty($data['meta_box_cb']['callback']) ){
        $data['meta_box_cb'] = $data['meta_box_cb']['callback'];
    } else {
        unset($data['meta_box_cb']);
    }
    // Force removing capabilities here
    unset( $data['capabilities'] );
    register_taxonomy( $taxonomy,
            apply_filters( 'wpcf_taxonomy_objects', $object_types, $taxonomy ),
            apply_filters( 'wpcf_taxonomy_data', $data, $taxonomy, $object_types ) );
}

/**
 * Returns only active taxonomies.
 *
 * @return type
 */
function wpcf_get_active_custom_taxonomies() {
    $taxonomies = get_option('wpcf-custom-taxonomies', array());
    foreach ($taxonomies as $taxonomy => $data) {
        if (!empty($data['disabled'])) {
            unset($taxonomies[$taxonomy]);
        }
    }
    return $taxonomies;
}
