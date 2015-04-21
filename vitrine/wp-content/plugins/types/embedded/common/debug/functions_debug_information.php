<?php
/**
  * produce debug information
  *
  * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/embedded/common/debug/functions_debug_information.php $
  * $LastChangedDate: 2015-03-16 12:03:31 +0000 (Mon, 16 Mar 2015) $
  * $LastChangedRevision: 1113864 $
  * $LastChangedBy: iworks $
  *
  */

class ICL_Debug_Information
{
	function __construct() {
	}
	function __destruct() {
    }

	function get_debug_info($info=array()) {
		if (!is_array($info)) {
			$info = explode(',', $info);
		}
		if (empty($info)) {
			$info = array('core', 'plugins', 'theme', 'extra-debug');
		}

		$output = array();
		foreach ($info as $type) {
			switch ($type) {
				case 'core':
					$output['core'] = $this->get_core_info();
					break;
				case 'plugins':
					$output['plugins'] = $this->get_plugins_info();
					break;
				case 'theme':
					$output['theme'] = $this->get_theme_info();
					break;
				case 'extra-debug':
                    $output['extra-debug'] = apply_filters('icl_get_extra_debug_info', array());
					break;
			}
		}
		return $output;
    }

    /**
     *
     * @global object $wpdb
     *
     */
	function get_core_info() {

		global $wpdb;

		$core = array(
			'Wordpress' => array(
				'Multisite' => is_multisite() ? 'Yes' : 'No',
				'SiteURL' => site_url(),
				'HomeURL' => home_url(),
				'Version' => get_bloginfo( 'version' ),
				'PermalinkStructure' => get_option( 'permalink_structure' ),
				'PostTypes' => implode( ', ', get_post_types( '', 'names' ) ),
				'PostSatus' => implode( ', ', get_post_stati() )
			),
			'Server' => array(
				'jQueryVersion' => wp_script_is( 'jquery', 'registered' ) ? $GLOBALS['wp_scripts']->registered['jquery']->ver : __( 'n/a', 'wpv-views' ),
				'PHPVersion' => phpversion(),
				'MySQLVersion' => $wpdb->db_version(),
				'ServerSoftware' => $_SERVER['SERVER_SOFTWARE']
			),
			'PHP' => array(
				'MemoryLimit' => ini_get( 'memory_limit' ),
				'UploadMax' => ini_get( 'upload_max_filesize' ),
				'PostMax' => ini_get( 'post_max_size' ),
				'TimeLimit' => ini_get( 'max_execution_time' ),
				'MaxInputVars' => ini_get( 'max_input_vars' ),
			),
		);

		return $core;
	}

	function get_plugins_info() {

		if ( ! function_exists( 'get_plugins' ) ) {
			$admin_includes_path = str_replace( site_url('/', 'admin'), ABSPATH, admin_url('includes/', 'admin') );
			require_once $admin_includes_path . 'plugin.php';
		}

		$plugins = get_plugins();
		$active_plugins = get_option('active_plugins');
		$active_plugins_info = array();
		foreach ($active_plugins as $plugin) {
			if (isset($plugins[$plugin])) {
				unset($plugins[$plugin]['Description']);
				$active_plugins_info[$plugin] = $plugins[$plugin];
			}
		}

		$mu_plugins = get_mu_plugins();

		$dropins = get_dropins();

		$output =array(
			'active_plugins' => $active_plugins_info,
			'mu_plugins' => $mu_plugins,
			'dropins' => $dropins,
		);

		return $output;
	}

	function get_theme_info() {

		if ( get_bloginfo( 'version' ) < '3.4' ) {
			$current_theme = get_theme_data( get_stylesheet_directory() . '/style.css' );
			$theme = $current_theme;
			unset($theme['Description']);
			unset($theme['Satus']);
			unset($theme['Tags']);
		} else {
			$current_theme = wp_get_theme();
			$theme = array(
				'Name' => $current_theme->Name,
				'ThemeURI' => $current_theme->ThemeURI,
				'Author' => $current_theme->Author,
				'AuthorURI' => $current_theme->AuthorURI,
				'Template' => $current_theme->Template,
				'Version' => $current_theme->Version,
				'TextDomain' => $current_theme->TextDomain,
				'DomainPath' => $current_theme->DomainPath,
			);
		}

		return $theme;
	}


    function do_json_encode($data)
    {
        if (version_compare(phpversion(), '5.3.0', '<')) {
            return json_encode($data);
        }
        $json_options = 0;
        if (defined('JSON_HEX_TAG')) {
            $json_options += JSON_HEX_TAG;
        }
        if (defined('JSON_HEX_APOS')) {
            $json_options += JSON_HEX_APOS;
        }
        if (defined('JSON_HEX_QUOT')) {
            $json_options += JSON_HEX_QUOT;
        }
        if (defined('JSON_HEX_AMP')) {
            $json_options += JSON_HEX_AMP;
        }
        if (defined('JSON_UNESCAPED_UNICODE')) {
            $json_options += JSON_UNESCAPED_UNICODE;
        }
        return json_encode($data, $json_options);
    }

}
