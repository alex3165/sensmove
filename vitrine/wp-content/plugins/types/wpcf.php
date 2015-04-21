<?php
/*
  Plugin Name: Types - Complete Solution for Custom Fields and Types
  Plugin URI: http://wordpress.org/extend/plugins/types/
  Description: Define custom post types, custom taxonomy and custom fields.
  Author: OnTheGoSystems
  Author URI: http://www.onthegosystems.com
  Version: 1.6.6.2
 */
/**
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/wpcf.php $
 * $LastChangedDate: 2015-04-10 07:30:43 +0000 (Fri, 10 Apr 2015) $
 * $LastChangedRevision: 1131818 $
 * $LastChangedBy: iworks $
 *
 */
// Added check because of activation hook and theme embedded code
if ( !defined( 'WPCF_VERSION' ) ) {
    /**
     * make sure that WPCF_VERSION in embedded/bootstrap.php is the same!
     */
    define( 'WPCF_VERSION', '1.6.6.2' );
}

define( 'WPCF_REPOSITORY', 'http://api.wp-types.com/' );

define( 'WPCF_ABSPATH', dirname( __FILE__ ) );
define( 'WPCF_RELPATH', plugins_url() . '/' . basename( WPCF_ABSPATH ) );
define( 'WPCF_INC_ABSPATH', WPCF_ABSPATH . '/includes' );
define( 'WPCF_INC_RELPATH', WPCF_RELPATH . '/includes' );
define( 'WPCF_RES_ABSPATH', WPCF_ABSPATH . '/resources' );
define( 'WPCF_RES_RELPATH', WPCF_RELPATH . '/resources' );

// Add installer
$installer = dirname( __FILE__ ) . '/plus/installer/loader.php';
if ( file_exists($installer) ) {
    include_once $installer;
    if ( class_exists('WP_Installer_Setup') ) {
        WP_Installer_Setup(
            $wp_installer_instance,
            array(
                'plugins_install_tab' => '1',
                'repositories_include' => array('toolset', 'wpml')
            )
        );
    }
}

require_once WPCF_INC_ABSPATH . '/constants.php';
/*
 * Since Types 1.2 we load all embedded code without conflicts
 */
require_once WPCF_ABSPATH . '/embedded/types.php';

require_once WPCF_ABSPATH . '/embedded/onthego-resources/loader.php';
onthego_initialize(WPCF_ABSPATH . '/embedded/onthego-resources/',
                                   WPCF_RELPATH . '/embedded/onthego-resources/' );

// Plugin mode only hooks
add_action( 'plugins_loaded', 'wpcf_init' );

// init hook for module manager
add_action( 'init', 'wpcf_wp_init' );

register_deactivation_hook( __FILE__, 'wpcf_deactivation_hook' );
register_activation_hook( __FILE__, 'wpcf_activation_hook' );

/**
 * Deactivation hook.
 *
 * Reset some of data.
 */
function wpcf_deactivation_hook()
{
    // Delete messages
    delete_option( 'wpcf-messages' );
    delete_option( 'WPCF_VERSION' );
    /**
     * check site kind and if do not exist, delete types_show_on_activate
     */
    if ( !get_option('types-site-kind') ) {
        delete_option('types_show_on_activate');
    }
}

/**
 * Activation hook.
 *
 * Reset some of data.
 */
function wpcf_activation_hook()
{
    $version = get_option('WPCF_VERSION');
    if ( empty($version) ) {
        $version = 0;
        add_option('WPCF_VERSION', 0, null, 'no');
    }
    if ( version_compare($version, WPCF_VERSION) < 0 ) {
        update_option('WPCF_VERSION', WPCF_VERSION);
    }
    if( 0 == version_compare(WPCF_VERSION, '1.6.5')) {
        add_option('types_show_on_activate', 'show', null, 'no');
        if ( get_option('types-site-kind') ) {
            update_option('types_show_on_activate', 'hide');
        }
    }
}

/**
 * Main init hook.
 */
function wpcf_init()
{
    if ( !defined( 'EDITOR_ADDON_RELPATH' ) ) {
        define( 'EDITOR_ADDON_RELPATH', WPCF_RELPATH . '/embedded/common/visual-editor' );
    }

    if ( is_admin() ) {
        require_once WPCF_ABSPATH . '/admin.php';
    }
    /**
     * remove unused option
     */
    $version_from_db = get_option('wpcf-version', 0);
    if ( version_compare(WPCF_VERSION, $version_from_db) > 0 ) {
        delete_option('wpcf-survey-2014-09');
        update_option('wpcf-version', WPCF_VERSION);
    }
}

//Render Installer packages
function installer_content()
{
    echo '<div class="wrap">';
    $config['repository'] = array(); // required
    WP_Installer_Show_Products($config);
    echo "</div>";
}

/**
 * WP Main init hook.
 */
function wpcf_wp_init()
{
    if ( is_admin() ) {
        require_once WPCF_ABSPATH . '/admin.php';
        add_action('wpcf_menu_plus', 'setup_installer');
        //Add submenu Installer to Types
        function setup_installer()
        {
            wpcf_admin_add_submenu_page(
                array(
                    'page_title' => __('Installer', 'wpcf'),
                    'menu_title' => __('Installer', 'wpcf'),
                    'menu_slug' => 'installer',
                    'function' => 'installer_content'
                )
            );
        }
    }
}

/**
 * Checks if name is reserved.
 *
 * @param type $name
 * @return type
 */
function wpcf_is_reserved_name($name, $context, $check_pages = true)
{
    $name = strval( $name );
    /*
     *
     * If name is empty string skip page cause there might be some pages without name
     */
    if ( $check_pages && !empty( $name ) ) {
        global $wpdb;
        $page = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type='page'",
                sanitize_title( $name )
            )
        );
        if ( !empty( $page ) ) {
            return new WP_Error( 'wpcf_reserved_name', __( 'You cannot use this slug because there is already a page by that name. Please choose a different slug.',
                                    'wpcf' ) );
        }
    }

    // Add custom types
    $custom_types = (array) get_option( 'wpcf-custom-types', array() );
    $post_types = get_post_types();
    if ( !empty( $custom_types ) ) {
        $custom_types = array_keys( $custom_types );
        $post_types = array_merge( array_combine( $custom_types, $custom_types ),
                $post_types );
    }
    // Unset to avoid checking itself
    if ( $context == 'post_type' && isset( $post_types[$name] ) ) {
        unset( $post_types[$name] );
    }

    // Add taxonomies
    $custom_taxonomies = (array) get_option( 'wpcf-custom-taxonomies', array() );
    $taxonomies = get_taxonomies();
    if ( !empty( $custom_taxonomies ) ) {
        $custom_taxonomies = array_keys( $custom_taxonomies );
        $taxonomies = array_merge( array_combine( $custom_taxonomies,
                        $custom_taxonomies ), $taxonomies );
    }
    // Unset to avoid checking itself
    if ( $context == 'taxonomy' && isset( $taxonomies[$name] ) ) {
        unset( $taxonomies[$name] );
    }

    $reserved_names = wpcf_reserved_names();
    $reserved = array_merge( array_combine( $reserved_names, $reserved_names ),
            array_merge( $post_types, $taxonomies ) );

    return in_array( $name, $reserved ) ? new WP_Error( 'wpcf_reserved_name', __( 'You cannot use this slug because it is a reserved word, used by WordPress. Please choose a different slug.',
                            'wpcf' ) ) : false;
}

/**
 * Reserved names.
 *
 * @return type
 */
function wpcf_reserved_names()
{
    $reserved = array(
        'attachment',
        'attachment_id',
        'author',
        'author_name',
        'calendar',
        'cat',
        'category',
        'category__and',
        'category__in',
        'category__not_in',
        'category_name',
        'comments_per_page',
        'comments_popup',
        'cpage',
        'day',
        'debug',
        'error',
        'exact',
        'feed',
        'format',
        'hour',
        'link_category',
        'm',
        'minute',
        'monthnum',
        'more',
        'name',
        'nav_menu',
        'nopaging',
        'offset',
        'order',
        'orderby',
        'p',
        'page',
        'page_id',
        'paged',
        'pagename',
        'pb',
        'perm',
        'post',
        'post__in',
        'post__not_in',
        'post_format',
        'post_mime_type',
        'post_status',
        'post_tag',
        'post_type',
        'posts',
        'posts_per_archive_page',
        'posts_per_page',
        'preview',
        'robots',
        's',
        'search',
        'second',
        'sentence',
        'showposts',
        'static',
        'subpost',
        'subpost_id',
        'tag',
        'tag__and',
        'tag__in',
        'tag__not_in',
        'tag_id',
        'tag_slug__and',
        'tag_slug__in',
        'taxonomy',
        'tb',
        'term',
        'type',
        'w',
        'withcomments',
        'withoutcomments',
        'year',
        'lang',
//        'comments',
//        'blog',
//        'files'
    );

    return apply_filters( 'wpcf_reserved_names', $reserved );
}

add_action( 'icl_pro_translation_saved', 'wpcf_fix_translated_post_relationships' );

function wpcf_fix_translated_post_relationships($post_id)
{
    require_once WPCF_EMBEDDED_ABSPATH . '/includes/post-relationship.php';
    wpcf_post_relationship_set_translated_parent( $post_id );
    wpcf_post_relationship_set_translated_children( $post_id );
}
