<?php
/*
 * Bootstrap code.
 *
 * Types plugin or embedded code is initialized here.
 * Here is determined if code is used as plugin or embedded code.
 *
 * @since Types 1.2
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/embedded/bootstrap.php $
 * $LastChangedDate: 2015-04-10 07:30:43 +0000 (Fri, 10 Apr 2015) $
 * $LastChangedRevision: 1131818 $
 * $LastChangedBy: iworks $
 *
 */

// Main functions
require_once dirname( __FILE__ ) . '/functions.php';

/*
 *
 *
 * If WPCF_VERSION is not defined - we're running embedded code
 */
if ( !defined( 'WPCF_VERSION' ) ) {
    // Mark that!
    define( 'WPCF_RUNNING_EMBEDDED', true );
    require_once dirname( __FILE__ ) . '/classes/loader.php';
}

/*
 *
 * Forced priority
 */
if ( !defined( 'TYPES_INIT_PRIORITY' ) ) {
    // Early start ( some plugins use 'init' with priority 0 ).
    define( 'TYPES_INIT_PRIORITY', -1 );
}

/*
 *
 * Init
 */
add_action( 'init', 'wpcf_embedded_init', TYPES_INIT_PRIORITY );
add_action( 'init', 'wpcf_init_custom_types_taxonomies', TYPES_INIT_PRIORITY );

/**
 * register_post_type & register_taxonomy - must be with default pririty to 
 * handle defult taxonomies
 */
add_action('init', 'wpcf_init_build_in_taxonomies');

/*
 *
 *
 * Define necessary constants
 */
define( 'WPCF_EMBEDDED_ABSPATH', dirname( __FILE__ ) );
define( 'WPCF_EMBEDDED_INC_ABSPATH', WPCF_EMBEDDED_ABSPATH . '/includes' );
define( 'WPCF_EMBEDDED_RES_ABSPATH', WPCF_EMBEDDED_ABSPATH . '/resources' );

/*
 *
 * Always set DEBUG as false
 */
if ( !defined( 'WPCF_DEBUG' ) ) {
    define( 'WPCF_DEBUG', false );
}
if ( !defined( 'TYPES_DEBUG' ) ) {
    define( 'TYPES_DEBUG', false );
}

/*
*
* Load common and local localization
*/
if ( !defined( 'WPT_LOCALIZATION' ) ) {
	require_once( WPCF_EMBEDDED_ABSPATH . '/common/localization/wpt-localization.php' );
}
new WPToolset_Localization( 'wpcf', WPCF_EMBEDDED_ABSPATH . '/locale', 'types-%s' );

/*
 *
 * Include common code.
 */
if ( !defined( 'ICL_COMMON_FUNCTIONS' ) ) {
    require_once WPCF_EMBEDDED_ABSPATH . '/common/functions.php';
    if ( !defined( 'WPTOOLSET_COMMON_PATH' ) ) {
        define( 'WPTOOLSET_COMMON_PATH', WPCF_EMBEDDED_ABSPATH . '/common' );
    }
} else if ( !defined( 'WPTOOLSET_COMMON_PATH' ) ) {
    //$__types_common_reflection = new ReflectionFunction( 'wpv_condition' );
    //define( 'WPTOOLSET_COMMON_PATH', dirname( $__types_common_reflection->getFileName() ) );
	define( 'WPTOOLSET_COMMON_PATH', WPCF_EMBEDDED_ABSPATH . '/common' );
}
if ( !defined( 'WPTOOLSET_FORMS_VERSION' ) ) {
    require_once WPTOOLSET_COMMON_PATH . '/toolset-forms/bootstrap.php';
}

/*
 *
 * Register theme options
 */
wpcf_embedded_after_setup_theme_hook();

/*
 *
 *
 * Set $wpcf global var as generic class
 */
$GLOBALS['wpcf'] = new stdClass();

/**
 * Main init hook.
 *
 * All rest of init processes are continued here.
 * Sets locale, constants, includes...
 *
 * @todo Make sure plugin AND embedded code are calling this function on 'init'
 * @todo Test priorities
 */
function wpcf_embedded_init() {

    global $types_instances, $wp_current_filter;

    // Record hook
    $types_instances['hook'] = $wp_current_filter;
    $types_instances['init_queued'] = '#' . did_action( 'init' );
    $types_instances['init_priority'] = TYPES_INIT_PRIORITY;
    $types_instances['forced_embedded'] = defined( 'TYPES_LOAD_EMBEDDED' ) && TYPES_LOAD_EMBEDDED;

    // Loader
    require_once WPCF_EMBEDDED_ABSPATH . '/classes/loader.php';

    do_action( 'wpcf_before_init' );
    do_action( 'types_before_init' );

    // Define necessary constants if plugin is not present
    // This ones are skipped if used as embedded code!
    if ( !defined( 'WPCF_VERSION' ) ) {
        define( 'WPCF_VERSION', '1.6.6.2' );
        define( 'WPCF_META_PREFIX', 'wpcf-' );
    }

    // If forced embedded mode use path to __FILE__
    if ( ( defined( 'TYPES_LOAD_EMBEDDED' ) && TYPES_LOAD_EMBEDDED )
        || !defined('WPCF_RELPATH') ) {
        define( 'WPCF_EMBEDDED_RELPATH', wpcf_get_file_url( __FILE__, false ) );
    } else {
        define( 'WPCF_EMBEDDED_RELPATH', WPCF_RELPATH . '/embedded' );
    }

    // Define embedded paths
    define( 'WPCF_EMBEDDED_INC_RELPATH', WPCF_EMBEDDED_RELPATH . '/includes' );
    define( 'WPCF_EMBEDDED_RES_RELPATH', WPCF_EMBEDDED_RELPATH . '/resources' );

    // TODO INCLUDES!
    //
    // Please add all required includes here
    // Since Types 1.2 we can consider existing code as core.
    // All new functionalities should be added as includes HERE
    // and marked with @since Types $version.
    //
    // Thanks!
    //

    // Basic
    /*
     *
     * Mind class extensions queue
     */
    require_once WPCF_EMBEDDED_ABSPATH . '/classes/fields.php';
    require_once WPCF_EMBEDDED_ABSPATH . '/classes/field.php';
    require_once WPCF_EMBEDDED_ABSPATH . '/classes/usermeta_field.php'; // Added by Gen, usermeta fields class

    // Repeater
    require_once WPCF_EMBEDDED_ABSPATH . '/classes/repeater.php';
    require_once WPCF_EMBEDDED_ABSPATH . '/classes/usermeta_repeater.php'; // Added by Gen, usermeta repeater class
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/repetitive-fields-ordering.php';
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/repetitive-usermetafields-ordering.php';

    // Relationship
    require_once WPCF_EMBEDDED_ABSPATH . '/classes/relationship.php';

    // Conditional
    require_once WPCF_EMBEDDED_ABSPATH . '/classes/conditional.php';

    // API
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/api.php';

    // Validation
    require_once WPCF_EMBEDDED_ABSPATH . '/classes/validation.php';

    // Post Types
    require_once WPCF_EMBEDDED_ABSPATH . '/classes/class.wpcf-post-types.php';

    // Import Export
    require_once WPCF_EMBEDDED_ABSPATH . '/classes/class.wpcf-import-export.php';

    // Module manager
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/module-manager.php';

    // WPML specific code
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/wpml.php';

    // CRED specific code.
    if ( defined( 'CRED_FE_VERSION' ) ) {
        require_once WPCF_EMBEDDED_INC_ABSPATH . '/cred.php';
    }

    /*
     *
     *
     * TODO This is a must for now.
     * See if any fields need to be loaded.
     *
     * 1. Checkboxes - may be missing when submitted
     */
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields/checkbox.php';


    /*
     *
     *
     * Use this call to load basic scripts and styles if necesary
     * wpcf_enqueue_scripts();
     */
    require_once WPCF_EMBEDDED_ABSPATH . '/usermeta-init.php';
    // Include frontend or admin code
    if ( is_admin() ) {

        require_once WPCF_EMBEDDED_ABSPATH . '/admin.php';

        /*
         * TODO Check if called twice
         *
         * Watch this! This is actually called twice everytime
         * in both modes (plugin or embedded)
         */
        wpcf_embedded_admin_init_hook();
    } else {
        require_once WPCF_EMBEDDED_ABSPATH . '/frontend.php';
    }

    global $wpcf;

    // TODO since Types 1.2 Continue adding new functionalities HERE
    /*
     * Consider code already there as core.
     * Use hooks to add new functionalities
     *
     * Introduced new global object $wpcf
     * Holds useful objects like:
     * $wpcf->field - Field object (base item object)
     * $wpcf->repeater - Repetitive field object
     */

    // Set debugging
    if ( !defined( 'WPCF_DEBUG' ) ) {
        define( 'WPCF_DEBUG', false );
    } else if ( WPCF_DEBUG ) {
        wp_enqueue_script( 'jquery' );
    }
    $wpcf->debug = new stdClass();
    require WPCF_EMBEDDED_INC_ABSPATH . '/debug.php';
    add_action( 'wp_footer', 'wpcf_debug', PHP_INT_MAX);
    add_action( 'admin_footer', 'wpcf_debug', PHP_INT_MAX);

    // Set field object
    $wpcf->field = new WPCF_Field();

    // Set fields object
    $wpcf->fields = new WPCF_Fields();

    // Set usermeta field object
    $wpcf->usermeta_field = new WPCF_Usermeta_Field();

    // Set repeater object
    $wpcf->usermeta_repeater = new WPCF_Usermeta_Repeater();

    // Set repeater object
    $wpcf->repeater = new WPCF_Repeater();

    // Set relationship object
    $wpcf->relationship = new WPCF_Relationship();

    // Set conditional object
    $wpcf->conditional = new WPCF_Conditional();

    // Set validate object
    $wpcf->validation = new WPCF_Validation();

    // Set import export objects
    $wpcf->import = new WPCF_Import_Export();
    $wpcf->export = new WPCF_Import_Export();

    // Set post object
    $wpcf->post = new stdClass();

    // Set post types object
    $wpcf->post_types = new WPCF_Post_Types();

    // Define exceptions - privileged plugins and their data
    $wpcf->toolset_post_types = array(
        'view', 'view-template', 'cred-form'
    );
    // 'attachment' = Media
    //
    $wpcf->excluded_post_types = array(
        'dd_layouts',
        'cred-form',
        'mediapage',
        'nav_menu_item',
        'revision',
        'view',
        'view-template',
        'wp-types-group',
        'wp-types-user-group',
    );

    // Init loader
    WPCF_Loader::init();

    /*
     * TODO Check why we enabled this
     *
     * I think because of CRED or Views using Types admin functions on frontend
     * Does this need review?
     */
    if ( defined( 'DOING_AJAX' ) ) {
        require_once WPCF_EMBEDDED_ABSPATH . '/frontend.php';
    }

    // Check if import/export request is going on
    wpcf_embedded_check_import();

    do_action( 'types_after_init' );
    do_action( 'wpcf_after_init' );
}
