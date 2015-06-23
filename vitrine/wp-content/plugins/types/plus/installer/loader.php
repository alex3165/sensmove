<?php
/*
Plugin Name: Installer
Plugin URI: http://wp-compatibility.com/installer-plugin/
Description: Need help buying, installing and upgrading commercial themes and plugins? **Installer** handles all this for you, right from the WordPress admin. Installer lets you find themes and plugins from different sources, then, buy them from within the WordPress admin. Instead of manually uploading and unpacking, you'll see those themes and plugins available, just like any other plugin you're getting from WordPress.org.
Version: 1.5.3
Author: OnTheGoSystems Inc.     
Author URI: http://www.onthegosystems.com/
*/

  
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

//It should only be loaded on the admin side
if( !is_admin() ){
    if(!function_exists('WP_Installer_Setup')){ function WP_Installer_Setup(){} }
    $wp_installer_instance = null;
    return;
}


$wp_installer_instance = dirname(__FILE__) . '/installer.php';


// Global stack of instances
global $wp_installer_instances;
$wp_installer_instances[$wp_installer_instance] = array(
    'bootfile'  => $wp_installer_instance,
    'version'   => '1.5.3'
);

// Only one of these in the end
remove_action('after_setup_theme', 'wpml_installer_instance_delegator', 1);
add_action('after_setup_theme', 'wpml_installer_instance_delegator', 1);

// When all plugins load pick the newest version
if(!function_exists('wpml_installer_instance_delegator')){
    function wpml_installer_instance_delegator(){
        global $wp_installer_instances;
        
        foreach($wp_installer_instances as $instance){

            if(!isset($delegate)){
                $delegate = $instance;
                continue;
            }
            
            if(version_compare($instance['version'], $delegate['version'], '>') || !empty($instance['args']['high_priority'])){
                $delegate = $instance;    
            }
        }

        include_once $delegate['bootfile'];
        
        // set configuration
        if(strpos(realpath($delegate['bootfile']), realpath(TEMPLATEPATH)) === 0){
            $delegate['args']['in_theme_folder'] = dirname(ltrim(str_replace(realpath(TEMPLATEPATH), '', realpath($delegate['bootfile'])), '\\/'));            
        }        
        if(isset($delegate['args']) && is_array($delegate['args'])){
            foreach($delegate['args'] as $key => $value){                
                WP_Installer()->set_config($key, $value);                
            }
        }
        
    }
}  

if(!function_exists('WP_Installer_Setup')){
    
    // $args:
    // plugins_install_tab = true|false (default: true) 
    // repositories_include = array() (default: all)
    // repositories_exclude = array() (default: none)
    // template = name (default: default)            
    // 
    // Ext function 
    function WP_Installer_Setup($wp_installer_instance, $args = array()){
        global $wp_installer_instances;
        
        $wp_installer_instances[$wp_installer_instance]['args'] = $args;

    }
    
}

