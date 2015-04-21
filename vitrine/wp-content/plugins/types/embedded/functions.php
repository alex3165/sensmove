<?php
/*
 * Basic and init functions.
 * Since Types 1.2 moved from /embedded/types.php
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/embedded/functions.php $
 * $LastChangedDate: 2015-04-03 10:15:58 +0000 (Fri, 03 Apr 2015) $
 * $LastChangedRevision: 1126927 $
 * $LastChangedBy: iworks $
 *
 */

/**
 * Caches get_post_meta() calls.
 *
 * @staticvar array $cache
 * @param type $post_id
 * @param type $meta_key
 * @param type $single
 * @return string
 */
function wpcf_get_post_meta($post_id, $meta_key, $single)
{
    static $cache = array();

    if ( !isset( $cache[$post_id] ) ) {
        $cache[$post_id] = get_post_custom( $post_id );
    }
    if ( isset( $cache[$post_id][$meta_key] ) ) {
        if ( $single && isset( $cache[$post_id][$meta_key][0] ) ) {
            return maybe_unserialize( $cache[$post_id][$meta_key][0] );
        } elseif ( !$single && !empty( $cache[$post_id][$meta_key] ) ) {
            return maybe_unserialize( $cache[$post_id][$meta_key] );
        }
    }
    return '';
}

/**
 * Calculates relative path for given file.
 *
 * @param type $file Absolute path to file
 * @return string Relative path
 */
function wpcf_get_file_relpath($file)
{
    $is_https = isset( $_SERVER['HTTPS'] ) && strtolower( $_SERVER['HTTPS'] ) == 'on';
    $http_protocol = $is_https ? 'https' : 'http';
    $base_root = $http_protocol . '://' . $_SERVER['HTTP_HOST'];
    $base_url = $base_root;
    $dir = rtrim( dirname( $file ), '\/' );
    if ( $dir ) {
        $base_path = $dir;
        $base_url .= $base_path;
        $base_path .= '/';
    } else {
        $base_path = '/';
    }
    $relpath = $base_root
            . str_replace(
                    str_replace( '\\', '/',
                            realpath( $_SERVER['DOCUMENT_ROOT'] ) )
                    , '', str_replace( '\\', '/', dirname( $file ) )
    );
    return $relpath;
}

/**
 * after_setup_theme hook.
 */
function wpcf_embedded_after_setup_theme_hook()
{
    $custom_types = get_option( 'wpcf-custom-types', array() );
    if ( !empty( $custom_types ) ) {
        foreach ( $custom_types as $post_type => $data ) {
            if ( !empty( $data['supports']['thumbnail'] ) ) {
                if ( !current_theme_supports( 'post-thumbnails' ) ) {
                    add_theme_support( 'post-thumbnails' );
                    remove_post_type_support( 'post', 'thumbnail' );
                    remove_post_type_support( 'page', 'thumbnail' );
                } else {
                    add_post_type_support( $post_type, 'thumbnail' );
                }
            }
        }
    }
}

/**
 * Inits custom types and taxonomies.
 */
function wpcf_init_custom_types_taxonomies()
{
    $custom_taxonomies = get_option( 'wpcf-custom-taxonomies', array() );
    if ( !empty( $custom_taxonomies ) ) {
        require_once WPCF_EMBEDDED_INC_ABSPATH . '/custom-taxonomies.php';
        wpcf_custom_taxonomies_init();
    }
    $custom_types = get_option( 'wpcf-custom-types', array() );
    if ( !empty( $custom_types ) ) {
        require_once WPCF_EMBEDDED_INC_ABSPATH . '/custom-types.php';
        wpcf_custom_types_init();
    }
}

/**
 * bind build-in taxonomies
 */

function wpcf_init_build_in_taxonomies()
{
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/custom-types.php';
    wpcf_init_bind_build_in_taxonomies();
}

/**
 * Returns meta_key type for specific field type.
 *
 * @param type $type
 * @return type
 */
function types_get_field_type($type)
{
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
    $data = wpcf_fields_type_action( $type );
    if ( !empty( $data['meta_key_type'] ) ) {
        return $data['meta_key_type'];
    }
    return 'CHAR';
}

/**
 * Imports settings.
 */
function wpcf_embedded_check_import()
{
    if ( file_exists( WPCF_EMBEDDED_ABSPATH . '/settings.php' ) ) {
        require_once WPCF_EMBEDDED_ABSPATH . '/admin.php';
        require_once WPCF_EMBEDDED_ABSPATH . '/settings.php';
        $dismissed = get_option( 'wpcf_dismissed_messages', array() );
        if ( in_array( $timestamp, $dismissed ) ) {
            return false;
        }
        if ( $timestamp > get_option( 'wpcf-types-embedded-import', 0 ) ) {
            if ( !$auto_import ) {
                $link = "<a href=\"" . admin_url( '?types-embedded-import=1&amp;_wpnonce=' . wp_create_nonce( 'embedded-import' ) ) . "\">";
                $text = sprintf( __( 'You have Types import pending. %sClick here to import.%s %sDismiss message.%s',
                                'wpcf' ), $link, '</a>',
                        "<a onclick=\"jQuery(this).parent().parent().fadeOut();\" class=\"wpcf-ajax-link\" href=\""
                        . admin_url( 'admin-ajax.php?action=wpcf_ajax&amp;wpcf_action=dismiss_message&amp;id='
                                . $timestamp . '&amp;_wpnonce=' . wp_create_nonce( 'dismiss_message' ) ) . "\">",
                        '</a>' );
                wpcf_admin_message( $text );
            }
            if ( $auto_import || (isset( $_GET['types-embedded-import'] ) && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'],
                            'embedded-import' )) ) {
                if ( file_exists( WPCF_EMBEDDED_ABSPATH . '/settings.xml' ) ) {
                    $_POST['overwrite-groups'] = 1;
                    $_POST['overwrite-fields'] = 1;
                    $_POST['overwrite-types'] = 1;
                    $_POST['overwrite-tax'] = 1;
                    $_POST['post_relationship'] = 1;
                    require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
                    require_once WPCF_EMBEDDED_INC_ABSPATH . '/import-export.php';
                    $data = @file_get_contents( WPCF_EMBEDDED_ABSPATH . '/settings.xml' );
                    wpcf_admin_import_data( $data, false, 'types-auto-import' );
                    update_option( 'wpcf-types-embedded-import', $timestamp );
                    wp_redirect( admin_url() );
                } else {
                    $code = __( 'settings.xml file missing', 'wpcf' );
                    wpcf_admin_message( $code, 'error' );
                }
            }
        }
    }
}

/**
 * Actions for outside fields control.
 *
 * @param type $action
 */
function wpcf_types_cf_under_control( $action = 'add', $args = array(),
        $post_type = 'wp-types-group', $meta_name = 'wpcf-fields' ) {
    global $wpcf_types_under_control;
    $wpcf_types_under_control['errors'] = array();
    switch ( $action ) {
        case 'add':
            $fields = wpcf_admin_fields_get_fields( false, true, false,
                    $meta_name, false );
            foreach ( $args['fields'] as $field_id ) {
                $field_type = !empty( $args['type'] ) ? $args['type'] : 'textfield';
                if ( strpos( $field_id, md5( 'wpcf_not_controlled' ) ) !== false ) {
                    $field_id_name = str_replace( '_' . md5( 'wpcf_not_controlled' ), '', $field_id );
                    $field_id_add = preg_replace( '/^wpcf\-/', '', $field_id_name );
                    $adding_field_with_wpcf_prefix = $field_id_add != $field_id_name;

                    // Activating field that previously existed in Types
                    if ( array_key_exists( $field_id_add, $fields ) ) {
                        $fields[$field_id_add]['data']['disabled'] = 0;
                    } else { // Adding from outside
                        $fields[$field_id_add]['id'] = $field_id_add;
                        $fields[$field_id_add]['type'] = $field_type;
                        if ($adding_field_with_wpcf_prefix) {
                            $fields[$field_id_add]['name'] = $field_id_add;
                            $fields[$field_id_add]['slug'] = $field_id_add;
                        } else {
                            $fields[$field_id_add]['name'] = $field_id_name;
                            $fields[$field_id_add]['slug'] = $field_id_name;
                        }
                        $fields[$field_id_add]['description'] = '';
                        $fields[$field_id_add]['data'] = array();
                        if ($adding_field_with_wpcf_prefix) {
                            // This was most probably a previous Types field
                            // let's take full control
                            $fields[$field_id_add]['data']['controlled'] = 0;
                        } else {
                            // @TODO WATCH THIS! MUST NOT BE DROPPED IN ANY CASE
                            $fields[$field_id_add]['data']['controlled'] = 1;
                        }
                    }
                    $unset_key = array_search( $field_id, $args['fields'] );
                    if ( $unset_key !== false ) {
                        unset( $args['fields'][$unset_key] );
                        $args['fields'][$unset_key] = $field_id_add;
                    }
                }
            }
            wpcf_admin_fields_save_fields( $fields, true, $meta_name );
            return $args['fields'];
            break;

        case 'check_exists':
            $fields = wpcf_admin_fields_get_fields( false, true, false,
                    $meta_name, false );
            $field = $args;
            if ( array_key_exists( $field, $fields ) && empty( $fields[$field]['data']['disabled'] ) ) {
                return true;
            }
            return false;
            break;

        case 'check_outsider':
            $fields = wpcf_admin_fields_get_fields( false, true, false,
                    $meta_name, false );
            $field = $args;
            if ( array_key_exists( $field, $fields ) && !empty( $fields[$field]['data']['controlled'] ) ) {
                return true;
            }
            return false;
            break;

        default:
            break;
    }
}

/**
 * Controlls meta prefix.
 *
 * @param array $field
 */
function wpcf_types_get_meta_prefix( $field = array() )
{
    if ( empty( $field ) ) {
        return WPCF_META_PREFIX;
    }
    if ( !empty( $field['data']['controlled'] ) ) {
        return '';
    }
    return WPCF_META_PREFIX;
}

/**
 * Compares WP versions
 * @global type $wp_version
 * @param type $version
 * @param type $operator
 * @return type
 */
function wpcf_compare_wp_version($version = '3.2.1', $operator = '>')
{
    global $wp_version;
    return version_compare( $wp_version, $version, $operator );
}

/**
 * Gets post type with data to which belongs.
 *
 * @param type $post_type
 * @return type
 */
function wpcf_pr_get_belongs($post_type)
{
    require_once WPCF_EMBEDDED_ABSPATH . '/includes/post-relationship.php';
    return wpcf_pr_admin_get_belongs( $post_type );
}

/**
 * Gets all post types and data that owns.
 *
 * @param type $post_type
 * @return type
 */
function wpcf_pr_get_has($post_type)
{
    require_once WPCF_EMBEDDED_ABSPATH . '/includes/post-relationship.php';
    return wpcf_pr_admin_get_has( $post_type );
}

/**
 * Gets individual post ID to which queried post belongs.
 *
 * @param type $post_id
 * @param type $post_type Post type of owner
 * @return type
 */
function wpcf_pr_post_get_belongs($post_id, $post_type)
{
    return get_post_meta( $post_id, '_wpcf_belongs_' . $post_type . '_id', true );
}

/**
 * Gets all posts that belong to queried post, grouped by post type.
 *
 * @param type $post_id
 * @param type $post_type
 * @return type
 */
function wpcf_pr_post_get_has($post_id, $post_type_q = null)
{
    $post_type = get_post_type( $post_id );
    $has = array_keys( wpcf_pr_get_has( $post_type ) );
    $add = is_null( $post_type_q ) ? '&post_type=any' : '&post_type=' . $post_type_q;
    $posts = get_posts( 'numberposts=-1&post_status=null&meta_key=_wpcf_belongs_'
            . $post_type . '_id&meta_value=' . $post_id . $add );

    $results = array();
    foreach ( $posts as $post ) {
        if ( !in_array( $post->post_type, $has ) ) {
            continue;
        }
        $results[$post->post_type][] = $post;
    }
    return is_null( $post_type_q ) ? $results : array_shift( $results );
}

/**
 * Gets settings.
 */
function wpcf_get_settings($specific = false)
{
    $defaults = array(
        'add_resized_images_to_library' => 0,
        'register_translations_on_import' => 1,
        'images_remote' => 0,
        'images_remote_cache_time' => '36',
        'help_box' => 'by_types',
    );
    $settings = wp_parse_args( get_option( 'wpcf_settings', array() ), $defaults );
    $settings = apply_filters( 'types_settings', $settings );
    if ( $specific ) {
        return isset( $settings[$specific] ) ? $settings[$specific] : false;
    }
    return $settings;
}

/**
 * Saves settings.
 */
function wpcf_save_settings($settings)
{
    update_option( 'wpcf_settings', $settings );
}

/**
 * Check if it can be repetitive
 * @param type $field
 * @return type
 */
function wpcf_admin_can_be_repetitive($type)
{
    return !in_array( $type,
                    array('checkbox', 'checkboxes', 'wysiwyg', 'radio', 'select') );
}

/**
 * Check if field is repetitive
 * @param type $type
 * @return type
 */
function wpcf_admin_is_repetitive($field)
{
    if ( !isset( $field['data']['repetitive'] ) || !isset( $field['type'] ) ) {
        return false;
    }
    $check = intval( $field['data']['repetitive'] );
    return !empty( $check ) && wpcf_admin_can_be_repetitive( $field['type'] );
}

/**
 * Returns unique ID.
 *
 * @staticvar array $cache
 * @param type $cache_key
 * @return type
 */
function wpcf_unique_id($cache_key)
{
    $cache_key = md5( strval( $cache_key ) . strval( time() ) . rand() );
    static $cache = array();
    if ( !isset( $cache[$cache_key] ) ) {
        $cache[$cache_key] = 1;
    } else {
        $cache[$cache_key] += 1;
    }
    return $cache_key . '-' . $cache[$cache_key];
}

/**
 * Determine if platform is Win
 *
 * @return type
 */
function wpcf_is_windows()
{
    global $wpcf;
    $is_windows = PHP_OS == "WIN32" || PHP_OS == "WINNT";
    if ( isset( $wpcf->debug ) ) {
        $wpcf->debug->is_windows = $is_windows;
    }
    return $is_windows;
}

/**
 * Parses array as string
 *
 * @param type $array
 */
function wpcf_parse_array_to_string($array)
{
    $s = '';
    foreach ( (array) $array as $param => $value ) {
        $s .= strval( $param ) . '=' . urlencode( strval( $value ) ) . '&';
    }
    return trim( $s, '&' );
}

/**
 * Get main post ID.
 *
 * @param type $context
 * @return type
 */
function wpcf_get_post_id($context = 'group')
{
    if ( !is_admin() ) {
        /*
         *
         * TODO Check if frontend is fine (rendering children).
         * get_post() previously WP 3.5 requires $post_id
         */
        $post_id = null;
        if ( wpcf_compare_wp_version( '3.5', '<' ) ) {
            global $post;
            $post_id = !empty( $post->ID ) ? $post->ID : -1;
        }
        $_post = get_post( $post_id );
        return !empty( $_post->ID ) ? $_post->ID : -1;
    }
    /*
     * TODO Explore possible usage for $context
     */
    $post = wpcf_admin_get_edited_post();
    return empty( $post->ID ) ? -1 : $post->ID;
}

/**
 * Basic scripts
 */
function wpcf_enqueue_scripts()
{
    if ( !wpcf_is_embedded() ) {
        /**
         * Basic JS
         */
        wp_enqueue_script(
            'wpcf-js',
            WPCF_RES_RELPATH . '/js/basic.js',
            array('jquery', 'jquery-ui-sortable', 'jquery-ui-draggable', 'jquery-ui-tabs'),
            WPCF_VERSION
        );
    }
    /**
     * Basic JS
     */
    wp_enqueue_script(
        'wpcf-js-embedded',
        WPCF_EMBEDDED_RES_RELPATH . '/js/basic.js',
        array('jquery', 'jquery-ui-sortable', 'jquery-ui-draggable', 'jquery-ui-tabs'),
        WPCF_VERSION
    );
    /*
     *
     * Basic CSS
     */
    wp_enqueue_style( 'wpcf-css-embedded' );

    /*
     *
     * Other components
     */
    if ( !defined( 'WPTOOLSET_FORMS_ABSPATH' ) ) {
        // Repetitive
        wp_enqueue_script(
                'wpcf-repeater',
                WPCF_EMBEDDED_RES_RELPATH . '/js/repetitive.js',
                array('wpcf-js-embedded'), WPCF_VERSION
        );
        wp_enqueue_style(
                'wpcf-repeater',
                WPCF_EMBEDDED_RES_RELPATH . '/css/repetitive.css',
                array('wpcf-css-embedded'), WPCF_VERSION
        );
    }

    // Conditional
    wp_enqueue_script( 'types-conditional' );
    wpcf_admin_add_js_settings( 'wpcfConditionalVerify_nonce',
            wp_create_nonce( 'cd_verify' )
    );
    wpcf_admin_add_js_settings( 'wpcfConditionalVerifyGroup',
            wp_create_nonce( 'cd_group_verify' ) );

    // RTL
    if ( is_rtl() ) {
        wp_enqueue_style(
                'wpcf-rtl', WPCF_EMBEDDED_RES_RELPATH . '/css/rtl.css',
                array('wpcf-css-embedded'), WPCF_VERSION
        );
    }
}

/**
 * Load all scripts required on edit post screen.
 *
 * @since 1.2.1
 * @todo Make loading JS more clear for all components.
 */
function wpcf_edit_post_screen_scripts()
{
    wpcf_enqueue_scripts();
    wp_enqueue_script( 'wpcf-fields-post',
            WPCF_EMBEDDED_RES_RELPATH . '/js/fields-post.js', array('jquery'),
            WPCF_VERSION );
    // TODO Switch to 1.11.1 jQuery Validation
//        wp_enqueue_script( 'types-js-validation' );
    if ( !defined( 'WPTOOLSET_FORMS_ABSPATH' ) ) {
        wp_enqueue_script( 'wpcf-form-validation',
                WPCF_EMBEDDED_RES_RELPATH . '/js/'
                . 'jquery-form-validation/jquery.validate.js', array('jquery'),
                WPCF_VERSION );
        wp_enqueue_script( 'wpcf-form-validation-additional',
                WPCF_EMBEDDED_RES_RELPATH . '/js/'
                . 'jquery-form-validation/additional-methods.min.js',
                array('jquery'), WPCF_VERSION );
    }
    wp_enqueue_style( 'wpcf-fields-basic',
            WPCF_EMBEDDED_RES_RELPATH . '/css/basic.css', array(), WPCF_VERSION );
    wp_enqueue_style( 'wpcf-fields-post',
            WPCF_EMBEDDED_RES_RELPATH . '/css/fields-post.css',
            array('wpcf-fields-basic'), WPCF_VERSION );
    wp_enqueue_style( 'wpcf-usermeta',
            WPCF_EMBEDDED_RES_RELPATH . '/css/usermeta.css',
            array('wpcf-fields-basic'), WPCF_VERSION );
    wp_enqueue_script( 'toolset-colorbox' );
    wp_enqueue_style( 'toolset-colorbox' );
    wp_enqueue_style( 'toolset-font-awesome' );
}

/**
 * Check if running embedded version.
 *
 * @return type
 */
function wpcf_is_embedded()
{
    return defined( 'WPCF_RUNNING_EMBEDDED' ) && WPCF_RUNNING_EMBEDDED;
}

/**
 * Returns custom post type settings.
 *
 * @param type $post_type
 * @return type
 */
function wpcf_get_custom_post_type_settings($item)
{
    $custom = get_option( 'wpcf-custom-types', array() );
    return !empty( $custom[$item] ) ? $custom[$item] : array();
}

/**
 * Returns taxonomy settings.
 *
 * @param type $taxonomy
 * @return type
 */
function wpcf_get_custom_taxonomy_settings($item)
{
    $custom = get_option( 'wpcf-custom-taxonomies', array() );
    return !empty( $custom[$item] ) ? $custom[$item] : array();
}

/**
 * Load JS and CSS for field type.
 *
 * Core function. Works and stable. Do not move or change.
 * If required, add hooks only.
 *
 * @staticvar array $cache
 * @param string $type
 * @return string
 */
function wpcf_field_enqueue_scripts($type)
{
    global $wpcf;
    static $cache = array();

    $config = wpcf_fields_type_action( $type );

    if ( !empty( $config ) ) {

        // Check if cached
        if ( isset( $cache[$config['id']] ) ) {
            return $cache[$config['id']];
        }

        // Use field object
        $wpcf->field->enqueue_script( $config );
        $wpcf->field->enqueue_style( $config );

        $cache[$config['id']] = $config;

        return $config;
    } else {
        $wpcf->debug->errors['missing_type_config'][] = $type;
        return array();
    }

}

/**
 * Get file URL.
 *
 * @uses WPCF_Path (functions taken from CRED_Loader)
 * @param type $file
 * @return type
 */
function wpcf_get_file_url($file, $use_baseurl = true)
{
    WPCF_Loader::loadClass( 'path' );
    return WPCF_Path::getFileUrl( $file, $use_baseurl );
}

/**
 * Checks if timestamp supports negative values.
 *
 * @return type
 */
function fields_date_timestamp_neg_supported()
{
    return strtotime( 'Fri, 13 Dec 1950 20:45:54 UTC' ) === -601010046;
}

/**
 * Returns media size.
 *
 * @global type $content_width
 * @param type $widescreen
 * @return type
 */
function wpcf_media_size($widescreen = false)
{
    global $content_width;
    if ( !empty( $content_width ) ) {
        $height = $widescreen ? round( $content_width * 9 / 16 ) : round( $content_width * 3 / 4 );
        return array($content_width, $height);
    }
    return $widescreen ? array(450, 253) : array(450, 320);
}

/**
 * Validation wrapper.
 *
 * @param type $method
 * @param type $args
 * @return boolean
 */
function types_validate($method, $args)
{
    WPCF_Loader::loadClass( 'validation-cakephp' );
    if ( is_callable( array('Wpcf_Cake_Validation', $method) ) ) {
        if ( !is_array( $args ) ) {
            $args = array($args);
        }
        return @call_user_func_array( array('Wpcf_Cake_Validation', $method),
                        $args );
    }
    return false;
}
