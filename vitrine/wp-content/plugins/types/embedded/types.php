<?php
/*
 * Track all Types code instances and load properly.
 * 
 * Here we make sure that code is loaded properly regarded embedded versions.
 * After right code is loaded, we proceed to bootstrapping.
 * 
 * Use TYPES_LOAD_EMBEDDED to force embedded code.
 * If not available plugin code will be loaded and vice versa.
 * 
 * Use WPCF_RUNNING_EMBEDDED or wpcf_is_embedded() to check if
 * running in embedded or plugin mode.
 * 
 * since: 1.2.1
 * author: srdjan@icanlocalize.com
 */

/*
 * Record all instances.
 * Used to track all active Types code.
 */
if ( empty( $GLOBALS['types_instances'] ) ) {
    $GLOBALS['types_instances'] = array();
}

// Add tracker
$GLOBALS['types_instances']['files'][] = __FILE__;

/*
 * Set main files.
 * 
 * Note that first loaded embedded file will be main embedded file.
 */
if ( defined( 'WPCF_ABSPATH' ) ) {
    if ( dirname( dirname( __FILE__ ) ) == WPCF_ABSPATH ) {
        // Mark plugin file
        $GLOBALS['types_instances']['plugin_file'] = __FILE__;
    } else if ( !isset( $GLOBALS['types_instances']['embedded_file'] ) ) {
        // Mark embedded file
        $GLOBALS['types_instances']['embedded_file'] = __FILE__;
    }
} else if ( !isset( $GLOBALS['types_instances']['embedded_file'] ) ) {
    // Mark embedded file
    $GLOBALS['types_instances']['embedded_file'] = __FILE__;
}

// Check if embedded bootstrap is already loaded
if ( defined( 'WPCF_RUNNING_EMBEDDED' ) ) {
    return;
}

/*
 * Add bootstraping code only after theme is set.
 * This way embedded code theme is triggered.
 * 
 * 'after_setup_theme' hook is called few steps before 'init'hook.
 * Note that Types code is bootstrapped on 'init' (priority 10)
 */
add_action( 'after_setup_theme', 'wpcf_bootstrap', 9 );

// Checks if bootstrapping function already loaded
if ( !function_exists( 'wpcf_bootstrap' ) ) {

    /**
     * Checks running mode (embedded or not) and resolves conflict
     * between embedded versions.
     * 
     * WPCF_RUNNING_EMBEDDED is defined if embedded code is already in use
     *      and no Types plugin available.
     * TYPES_LOAD_EMBEDDED can be forced by developer.
     * 
     * Note that this could be triggered across other embedded code.
     * If developer defines it as true anywhere in the code before 'init' hook
     * it will disable embedded code for all.
     * 
     * @since 1.2
     * @return type
     */
    function wpcf_bootstrap() {

        global $types_instances;

        // If only one instance - do not complicate, just load it
        if ( count( $types_instances['files'] ) == 1 ) {
            wpcf_load_instance( __FILE__ );
            return;
        }

        /*
         * First occurance of TYPES_LOAD_EMBEDDED
         * 
         * Check if developer forces embedded usage code and there is no plugin code.
         */
        // FORCED embedded code
        if ( defined( 'TYPES_LOAD_EMBEDDED' ) ) {

            // No plugin!
            if ( !defined( 'WPCF_VERSION' ) ) {
                // If user tries to force core code without plugin.
                if ( TYPES_LOAD_EMBEDDED == false ) {
                    // Issue warning if needed
                    do_action( 'types_warning', 'missing_plugin' );
                }
                // Do nothing more, let file load
                do_action( 'types_embedded_code_init' );
                /*
                 * 
                 * Next check if user forces embedded code usage
                 * and there is plugin available.
                 */
            } else if ( defined( 'WPCF_VERSION' ) ) {
                // Check if plugin path
                $in_plugin = dirname( dirname( __FILE__ ) ) == WPCF_ABSPATH;
                /*
                 * Check if user forces plugin code and if file is part of plugin code.
                 * If plugin code should be used and this is not plugin file stop execution.
                 */
                if ( TYPES_LOAD_EMBEDDED == false ) {
                    wpcf_load_instance( 'plugin' );
                    return;
                }

                if ( TYPES_LOAD_EMBEDDED == true ) {
                    /*
                     * Check if inside of Types
                     */
                    if ( $in_plugin ) {
                        // This is plugin file
                        wpcf_load_instance( 'embedded' );
                        return;
                    } else {
                        do_action( 'types_embedded_code_init' );
                    }
                }
            }
        } else {
            // Set loading embedded code to true
            define( 'TYPES_LOAD_EMBEDDED', false );
        }

        if ( defined( 'WPCF_RUNNING_EMBEDDED' ) ) {
            return;
        }

        wpcf_load_instance( __FILE__ );

    }

    /**
     * Bootstrap instance.
     * 
     * @param type $file
     */
    function wpcf_load_instance( $file ) {

        global $types_instances;

        $dirname = dirname( strval( __FILE__ ) );

        if ( $file == 'plugin' ) {
            if ( isset( $types_instances['plugin_file'] ) ) {
                $dirname = dirname( strval( $types_instances['plugin_file'] ) );
            }
        } else if ( $file == 'embedded' ) {
            if ( isset( $types_instances['embedded_file'] ) ) {
                $dirname = dirname( strval( $types_instances['embedded_file'] ) );
            }
        } else {
            $dirname = dirname( strval( $file ) );
        }
        $bootstrap = $dirname . '/bootstrap.php';
//        echo 'Running file: ' . $bootstrap;
        if ( file_exists( $bootstrap ) ) {
            require_once $bootstrap;
        }

        $types_instances['running_file'] = $bootstrap;
    }

}