<?php
/**
 *
 * Custom Post Types embedded code.
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/embedded/includes/custom-types.php $
 * $LastChangedDate: 2015-04-03 10:15:58 +0000 (Fri, 03 Apr 2015) $
 * $LastChangedRevision: 1126927 $
 * $LastChangedBy: iworks $
 *
 */
add_action( 'wpcf_type', 'wpcf_filter_type', 10, 2 );

/**
 * Returns default custom type structure.
 *
 * @return array
 */
function wpcf_custom_types_default() {
    return array(
        'labels' => array(
            'name' => '',
            'singular_name' => '',
            'add_new' => 'Add New',
            'add_new_item' => 'Add New %s',
//          'edit' => 'Edit',
            'edit_item' => 'Edit %s',
            'new_item' => 'New %s',
//          'view' => 'View',
            'view_item' => 'View %s',
            'search_items' => 'Search %s',
            'not_found' => 'No %s found',
            'not_found_in_trash' => 'No %s found in Trash',
            'parent_item_colon' => 'Parent %s',
            'menu_name' => '%s',
            'all_items' => '%s',
        ),
        'slug' => '',
        'description' => '',
        'public' => true,
        'capabilities' => false,
        'menu_position' => null,
        'menu_icon' => '',
        'taxonomies' => array(
            'category' => false,
            'post_tag' => false,
        ),
        'supports' => array(
            'title' => true,
            'editor' => true,
            'trackbacks' => false,
            'comments' => false,
            'revisions' => false,
            'author' => false,
            'excerpt' => false,
            'thumbnail' => false,
            'custom-fields' => false,
            'page-attributes' => false,
            'post-formats' => false,
        ),
        'rewrite' => array(
            'enabled' => true,
            'slug' => '',
            'with_front' => true,
            'feeds' => true,
            'pages' => true,
        ),
        'has_archive' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_menu_page' => '',
        'publicly_queryable' => true,
        'exclude_from_search' => false,
        'hierarchical' => false,
        'query_var_enabled' => true,
        'query_var' => '',
        'can_export' => true,
        'show_in_nav_menus' => true,
        'register_meta_box_cb' => '',
        'permalink_epmask' => 'EP_PERMALINK'
    );
}

/**
 * Inits custom types.
 */
function wpcf_custom_types_init() {
    $custom_types = get_option( 'wpcf-custom-types', array() );
    if ( !empty( $custom_types ) ) {
        foreach ( $custom_types as $post_type => $data ) {
            wpcf_custom_types_register( $post_type, $data );
        }
    }
}

/**
 * Registers custom post type.
 * 
 * @param type $post_type
 * @param type $data 
 */
function wpcf_custom_types_register( $post_type, $data ) {

    global $wpcf;

    if ( !empty( $data['disabled'] ) ) {
        return false;
    }
    $data = apply_filters( 'types_post_type', $data, $post_type );
    // Set labels
    if ( !empty( $data['labels'] ) ) {
        if ( !isset( $data['labels']['name'] ) ) {
            $data['labels']['name'] = $post_type;
        }
        if ( !isset( $data['labels']['singular_name'] ) ) {
            $data['labels']['singular_name'] = $data['labels']['name'];
        }
        foreach ( $data['labels'] as $label_key => $label ) {
            $data['labels'][$label_key] = $label = stripslashes( $label );
            switch ( $label_key ) {
                case 'add_new_item':
                case 'edit_item':
                case 'new_item':
                case 'view_item':
                case 'parent_item_colon':
                    $data['labels'][$label_key] = sprintf( $label,
                            $data['labels']['singular_name'] );
                    break;

                case 'search_items':
                case 'all_items':
                case 'not_found':
                case 'not_found_in_trash':
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
    $data['publicly_queryable'] = !empty( $data['publicly_queryable'] );
    $data['exclude_from_search'] = !empty( $data['exclude_from_search'] );
    $data['show_ui'] = (empty( $data['show_ui'] ) || !$data['public']) ? false : true;
    if ( empty( $data['menu_position'] ) ) {
        unset( $data['menu_position'] );
    } else {
        $data['menu_position'] = intval( $data['menu_position'] );
    }
    $data['hierarchical'] = !empty( $data['hierarchical'] );
    $data['supports'] = !empty( $data['supports'] ) && is_array( $data['supports'] ) ? array_keys( $data['supports'] ) : array();
    $data['taxonomies'] = !empty( $data['taxonomies'] ) && is_array( $data['taxonomies'] ) ? array_keys( $data['taxonomies'] ) : array();
    $data['has_archive'] = !empty( $data['has_archive'] );
    $data['can_export'] = !empty( $data['can_export'] );
    $data['show_in_nav_menus'] = !empty( $data['show_in_nav_menus'] );
    $data['show_in_menu'] = !empty( $data['show_in_menu'] );
    if ( empty( $data['query_var_enabled'] ) ) {
        $data['query_var'] = false;
    } else if ( empty( $data['query_var'] ) ) {
        $data['query_var'] = true;
    }
    if ( !empty( $data['show_in_menu_page'] ) ) {
        $data['show_in_menu'] = $data['show_in_menu_page'];
        $data['labels']['all_items'] = $data['labels']['name'];
    }
    /**
     * menu_icon
     */
    if ( empty( $data['menu_icon'] ) ) {
        unset( $data['menu_icon'] );
    } else {
        $data['menu_icon'] = stripslashes( $data['menu_icon'] );
        if ( strpos( $data['menu_icon'], '[theme]' ) !== false ) {
            $data['menu_icon'] = str_replace( '[theme]',
                    get_stylesheet_directory_uri(), $data['menu_icon'] );
        }
    }
    if ( empty($data['menu_icon'] ) && !empty( $data['icon'] ) ) {
        $data['menu_icon'] = sprintf( 'dashicons-%s', $data['icon'] );
    }
    /**
     * rewrite
     */
    if ( !empty( $data['rewrite']['enabled'] ) ) {
        $data['rewrite']['with_front'] = !empty( $data['rewrite']['with_front'] );
        $data['rewrite']['feeds'] = !empty( $data['rewrite']['feeds'] );
        $data['rewrite']['pages'] = !empty( $data['rewrite']['pages'] );
        if ( !empty( $data['rewrite']['custom'] ) && $data['rewrite']['custom'] != 'custom' ) {
            unset( $data['rewrite']['slug'] );
        }
        unset( $data['rewrite']['custom'] );
    } else {
        $data['rewrite'] = false;
    }

    // Set permalink_epmask
    if ( !empty( $data['permalink_epmask'] ) ) {
        $data['permalink_epmask'] = constant( $data['permalink_epmask'] );
    }

    /**
     * set default support options
     */
    $support_fields = array(
        'editor' => false,
        'author' => false,
        'thumbnail' => false,
        'excerpt' => false,
        'trackbacks' => false,
        'custom-fields' => false,
        'comments' => false,
        'revisions' => false,
        'page-attributes' => false,
        'post-formats' => false,
    );
    $data['supports'] = array_merge_recursive( $data['supports'], $support_fields );

    $args = register_post_type( $post_type, apply_filters( 'wpcf_type', $data, $post_type ) );

    do_action( 'wpcf_type_registered', $args );
}

/**
 * Revised rewrite.
 * 
 * We force slugs now. Submitted and sanitized slug. Set slugs localized (WPML).
 * More solid way to force WP slugs.
 * 
 * @see https://icanlocalize.basecamphq.com/projects/7393061-wp-views/todo_items/153925180/comments
 * @since 1.1.3.2
 * @param type $data
 * @param type $post_type
 * @return boolean 
 */
function wpcf_filter_type( $data, $post_type ) {
    if ( !empty( $data['rewrite']['enabled'] ) ) {
        $data['rewrite']['with_front'] = !empty( $data['rewrite']['with_front'] );
        $data['rewrite']['feeds'] = !empty( $data['rewrite']['feeds'] );
        $data['rewrite']['pages'] = !empty( $data['rewrite']['pages'] );

        // If slug is not submitted use default slug
        if ( empty( $data['rewrite']['slug'] ) ) {
            $data['rewrite']['slug'] = $data['slug'];
        }

        // Also force default slug if rewrite mode is 'normal'
        if ( !empty( $data['rewrite']['custom'] ) && $data['rewrite']['custom'] != 'normal' ) {
            $data['rewrite']['slug'] = $data['rewrite']['slug'];
        }

        // Register with _x()
        $data['rewrite']['slug'] = _x( $data['rewrite']['slug'], 'URL slug',
                'wpcf' );
        //
        // CHANGED leave it for reference if we need
        // to return handling slugs back to WP.
        // 
        // We unset slug settings and leave WP to handle it himself.
        // Let WP decide what slugs should be!
//        if (!empty($data['rewrite']['custom']) && $data['rewrite']['custom'] != 'normal') {
//            unset($data['rewrite']['slug']);
//        }
        // Just discard non-WP property
        unset( $data['rewrite']['custom'] );
    } else {
        $data['rewrite'] = false;
    }

    return $data;
}

/**
 * Returns active custom post types.
 * 
 * @return type 
 */
function wpcf_get_active_custom_types() {
    $types = get_option('wpcf-custom-types', array());
    foreach ($types as $type => $data) {
        if (!empty($data['disabled'])) {
            unset($types[$type]);
        }
    }
    return $types;
}

/** This action is documented in wp-admin/includes/dashboard.php */
add_action('dashboard_glance_items', 'wpcf_dashboard_glance_items');

/**
 * Add CPT info to "At a Glance"
 *
 * Add to "At a Glance" WordPress admin dashboard widget information
 * about number of posts.
 *
 * @since 1.6.6
 *
 */
function wpcf_dashboard_glance_items()
{
    $custom_types = get_option( 'wpcf-custom-types', array() );
    ksort($custom_types);
    if ( !empty( $custom_types ) ) {
        foreach ( $custom_types as $post_type => $data ) {
            if ( !isset($data['dashboard_glance']) || !$data['dashboard_glance']) {
                continue;
            }
            if ( isset($data['disabled']) && $data['disabled'] ) {
                continue;
            }
            $num_posts = wp_count_posts($post_type);
            $num = number_format_i18n($num_posts->publish);
            $text = _n( $data['labels']['singular_name'], $data['labels']['name'], intval($num_posts->publish) );
            printf(
                '<li class="page-count %s-count"><a href="%s"%s>%d %s</a></li>',
                $post_type,
                add_query_arg(
                    array(
                        'post_type' => $post_type,
                    ),
                    admin_url('edit.php')
                ),
                isset($data['icon'])? sprintf('class="dashicons-%s"', $data['icon']):'',
                $num,
                $text
            );
        }
    }
}

/**
 * Register build-in taxonomies for CPT
 *
 * Register build-in taxonomies for CPT in the proper time, after this 
 * taxonomies are avaiable to register, becouse register_post_type is called 
 * in Types before register_taxonomy is called for build-in taxonomies.
 *
 * @since 1.6.6.1
 *
 */
function wpcf_init_bind_build_in_taxonomies()
{
    $custom_types = get_option( 'wpcf-custom-types', array() );
    if ( empty($custom_types) ) {
        return;
    }
    foreach( $custom_types as $custom_post_slug => $data ) {
        if ( !isset($data['taxonomies']) || empty($data['taxonomies'])) {
            continue;
        }
        $build_in_taxonomies = array_keys(get_taxonomies(array('_builtin'=>true)));
        foreach(array_keys($data['taxonomies']) as $taxonomy_name) {
            if ( !in_array($taxonomy_name, $build_in_taxonomies) ) {
                continue;
            }
            register_taxonomy_for_object_type($taxonomy_name, $custom_post_slug);
        }
    }
}

