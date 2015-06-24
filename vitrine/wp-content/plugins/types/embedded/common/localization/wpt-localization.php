<?php

/*
* wpt-localization.php
*
* Common localization for shared code on the Toolset and also common way for adding textdomains
*
* @since Jul 18 2014
*/

if ( defined( 'WPT_LOCALIZATION' ) ) {
    return; 
}

define( 'WPT_LOCALIZATION', true );
define( 'WPT_LOCALIZATION_ABSPATH', dirname( __FILE__ ) . '/locale' );

/*
* WPToolset_Localization
*
* Methods for registering textdomains
*
*/

class WPToolset_Localization {

	/*
	* @param $textdomain (string) the textdomain to use
	* @param $path (string) the path to the folder containing the mo files
	* @param $mo_file (string) the .mo file name, using %s as a placeholder for the locale - do not add the .mo extension!
	*/
	
	function __construct( $textdomain = 'wpv-views', $path = WPT_LOCALIZATION_ABSPATH, $mo_name = 'views-%s' ) {
		// Set instance properties
		$this->textdomain = $textdomain;
		$this->path = $path;
		$this->mo_name = $mo_name;
		$this->mo_processed_name = '';
		// Set init action
		add_action( 'init', array( $this, 'load_textdomain' ) );
	}
	
	/*
	* load_textdomain
	*
	* Initializes localization given a textdomain, a path and a .mo file name
	*
	* @uses load_textdomain
	*
	* @since July 18, 2014
	*/
	
	function load_textdomain() {
		$locale = get_locale();
		$this->mo_processed_name = sprintf( $this->mo_name, $locale );
		load_textdomain( $this->textdomain, $this->path . '/' . $this->mo_processed_name . '.mo' );
	}

}

new WPToolset_Localization();