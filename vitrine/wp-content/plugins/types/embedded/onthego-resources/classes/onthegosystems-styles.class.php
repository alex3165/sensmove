<?php

define('ON_THE_GO_SYSTEMS_BRANDING_STYLES_CLASS_PATH', dirname(__FILE__) );

class OnTheGoSystemsStyles_Class{

    private static $instance;

    /**
     * Class is singleton
     */
    private function __construct( )
    {
        // load wp-admin
        add_action( 'admin_enqueue_scripts', array(&$this, 'register_and_enqueue_styles') );
        // load front-end
        if (!is_admin()) {
            add_action( 'admin_bar_init', array(&$this, 'register_and_enqueue_styles') );
        }
    }

    public function register_and_enqueue_styles()
    {
        if ( is_admin() || defined('WPDDL_VERSION') ) {
            wp_register_style('onthego-admin-styles', ON_THE_GO_SYSTEMS_BRANDING_REL_PATH .'onthego-styles/onthego-styles.css');
            wp_enqueue_style( 'onthego-admin-styles' );
        }
    }

    public static function getInstance( )
    {
        if (!self::$instance)
        {
            self::$instance = new OnTheGoSystemsStyles_Class();
        }

        return self::$instance;
    }
};
