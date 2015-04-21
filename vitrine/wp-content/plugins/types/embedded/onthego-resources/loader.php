<?php

// This file is responsible for loading the latest version of the on the
// go system branding resources.
//
// To use it in a plugin or theme you should include this file early in the
// plugin loader and then call the onthego_initialize function.
// The onthego_initialize should be passed the file path to the directory
// where this file is located and also the url to this directory.

// 

// -----------------------------------------------------------------------//

// This version number should always be incremented by 1 whenever a change
// is made to the onthego-resources code.
// The version number will then be used to work out which plugin has the latest
// version of the code.

$onthegosystems_branding_version = 1;


// ----------------------------------------------------------------------//
// WARNING * WARNING *WARNING
// ----------------------------------------------------------------------//

// Don't modify or add to this code.
// This is only responsible for making sure the latest version of the resources
// is loaded.

global $onthegosystems_branding_paths;

if (!isset($onthegosystems_branding_paths)) {
    $onthegosystems_branding_paths = array();
}

if (!isset($onthegosystems_branding_paths[$onthegosystems_branding_version])) {
    // Save the path to this version.
    $onthegosystems_branding_paths[$onthegosystems_branding_version]['path'] = str_replace('\\', '/', dirname(__FILE__));
}

if( !function_exists('on_the_go_systems_branding_plugins_loaded') ) {
    function on_the_go_systems_branding_plugins_loaded()
    {
        global $onthegosystems_branding_paths;

        // find the latest version
        $latest = 0;
        foreach ($onthegosystems_branding_paths as $key => $data) {
            if ($key > $latest) {
                $latest = $key;
            }
        }
        if ($latest > 0) {
            require_once $onthegosystems_branding_paths[$latest]['path'] . '/onthegosystems-branding-loader.php';
            ont_set_on_the_go_systems_uri_and_start( $onthegosystems_branding_paths[$latest]['url'] );
        }
    }

    add_action( 'after_setup_theme', 'on_the_go_systems_branding_plugins_loaded');
}

if( !function_exists('onthego_initialize') ) {

    function onthego_initialize($path, $url) {
        global $onthegosystems_branding_paths;

        $path = str_replace('\\', '/', $path);
        
        if (substr($path, strlen($path) - 1) == '/') {
            $path = substr($path, 0, strlen($path) - 1);
        }
        
        // Save the url in the matching path
        foreach ($onthegosystems_branding_paths as $key => $data) {
            if ($onthegosystems_branding_paths[$key]['path'] == $path) {
                $onthegosystems_branding_paths[$key]['url'] = $url;
                break;
            }
        }
    }
}

