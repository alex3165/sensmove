<?php
/*
 * Types Access teaser.
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/plus/types-access.php $
 * $LastChangedDate: 2015-03-02 10:49:00 +0000 (Mon, 02 Mar 2015) $
 * $LastChangedRevision: 1103173 $
 * $LastChangedBy: iworks $
 *
 */

add_action( 'plugins_loaded', 'wpcf_access_teaser_init', 15 );

/**
 * Teaser init.
 */
function wpcf_access_teaser_init() {
    global $pagenow;
    if ( !defined( 'WPCF_ACCESS_VERSION' ) ) {
        // Check if Access is activating right now
        if ( $pagenow == 'plugins.php'
                && (isset( $_GET['action'] ) && $_GET['action'] == 'activate'
                && isset( $_GET['plugin'] )
                && basename( $_GET['plugin'] ) == 'types-access.php') ) {
            return false;
        }
        define( 'WPCF_ACCESS_ABSPATH', dirname( __FILE__ ) . '/types-access' );
        define( 'WPCF_ACCESS_RELPATH',
                plugins_url() . '/' . basename( WPCF_ABSPATH ) . '/plus/types-access' );
        define( 'WPCF_ACCESS_INC', WPCF_ACCESS_ABSPATH . '/includes' );
        $locale = get_locale();
        load_textdomain( 'wpcf_access',
                WPCF_ACCESS_ABSPATH . '/locale/types-access-' . $locale . '.mo' );
        add_action( 'wpcf_menu_plus', 'wpcf_access_teaser_admin_menu' );
    }
}

/**
 * Teaser menu hook.
 */
function wpcf_access_teaser_admin_menu() {
    $hook = wpcf_admin_add_submenu_page(
        array(
            'page_title' => __( 'Access Control and User Roles', 'wpcf' ),
            'menu_title' => __( 'Access Control and User Roles', 'wpcf' ),
            'menu_slug' => 'wpcf-access',
            'function' => 'wpcf_access_teaser_admin_menu_page',
            'load_hook' => 'wpcf_access_teaser_admin_menu_load',
        )
    );
}

/**
 * Teaser menu load.
 */
function wpcf_access_teaser_admin_menu_load() {
    require_once WPCF_ACCESS_ABSPATH . '/embedded.php';
    wp_enqueue_style( 'wpcf-access-wpcf',
            WPCF_EMBEDDED_RES_RELPATH . '/css/basic.css', array(), WPCF_VERSION );
    wp_enqueue_style( 'wpcf-access', WPCF_ACCESS_RELPATH . '/css/basic.css',
            array(), WPCF_VERSION );
    wp_enqueue_style( 'wpcf-access-suggest',
            WPCF_ACCESS_RELPATH . '/css/suggest.css', array(), WPCF_VERSION );
    wp_enqueue_script( 'wpcf-access', WPCF_ACCESS_RELPATH . '/js/basic.js',
            array('jquery') );
}

/**
 * Teaser admin screen.
 */
function wpcf_access_teaser_admin_menu_page()
{
    $access_buy_link = 'http://wp-types.com/buy/?add-to-cart=38997&buy_now=1&utm_source=typesplugin&utm_medium=accessadmin&utm_term=Buy&utm_campaign=typesplugin';
    /**
     * get link by installer
     */
    if ( class_exists('WP_Installer_API') && method_exists('WP_Installer_API', 'get_product_installer_link') ) {
        $access_buy_link = WP_Installer_API::get_product_installer_link('toolset', 'access');
    }
    /**
     * show message
     */
    wpcf_add_admin_header( __( 'Access', 'wpcf' ), 'icon-wpcf-access' );
    echo '<div class="types-help"><div class="types-help-content"';
    echo '<p>' . sprintf(__( 'This screen shows a preview of %sAccess%s - the access control and roles management addon for Types.',
            'wpcf' ), '<strong><a href="http://wp-types.com/home/types-access/?utm_source=typesplugin&utm_medium=accessadmin&utm_term=Access&utm_campaign=typesplugin" target="_blank">','</a></strong>')
    . '</p>'
    . '<p>' . __('Access lets you control what content types different users can read, edit and publish on your site and create custom roles.','wpcf') . '</p>'
    . '<p>' . sprintf(__('%sBuy Access%s to unlock this screen and add access control management to your site.','wpcf'),
                      sprintf('<strong><a href="%s" target="_blank">', $access_buy_link),
                      '</a></strong>')
    . '</p>'
    . '<p><a href="http://wp-types.com/home/types-access/?utm_source=typesplugin&utm_medium=accessadmin&utm_term=AccessFeatures&utm_campaign=typesplugin" class="button-primary" target="_blank">'
    . sprintf(__( 'Access Features' ) . '</a>&nbsp;<a href="%s" class="button-primary" target="_blank">', $access_buy_link )
    . __( 'Buy Access - $39 (USD)' ) . '</a>' . '</p>';
    echo '</div></div>';
    require_once WPCF_ACCESS_INC . '/admin-edit-access.php';
    wpcf_access_admin_edit_access( false );
    wpcf_add_admin_footer();
}
