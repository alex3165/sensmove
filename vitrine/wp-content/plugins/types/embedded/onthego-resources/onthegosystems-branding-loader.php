<?php

if( class_exists('OnTheGoSystemsStyles_Class') ) {
    return;
};

if( !defined('ON_THE_GO_SYSTEMS_BRANDING_ABS_PATH') ){
    define( 'ON_THE_GO_SYSTEMS_BRANDING_ABS_PATH', dirname(__FILE__) );
}


if( !defined('ON_THE_GO_SYSTEMS_BRANDING_CLASSES_PATH') ){
    define( 'ON_THE_GO_SYSTEMS_BRANDING_CLASSES_PATH', dirname(__FILE__) . '/classes/' );
}


require_once( ON_THE_GO_SYSTEMS_BRANDING_CLASSES_PATH . 'onthegosystems-styles.class.php' );


if( !function_exists('on_the_go_systems_branding_init') )
{
    function on_the_go_systems_branding_init()
    {
        global $on_the_go_system_branding;

        $on_the_go_system_branding = OnTheGoSystemsStyles_Class::getInstance();
    }

    function ont_set_on_the_go_systems_uri_and_start( $path )
    {
        if( !defined('ON_THE_GO_SYSTEMS_BRANDING_REL_PATH') )
        {
            define( 'ON_THE_GO_SYSTEMS_BRANDING_REL_PATH', $path );
        }
    }
    // make sure we load styles after font awesome
    add_action( 'init', 'on_the_go_systems_branding_init', 110 );
}


