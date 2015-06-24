<?php
/**
 *
 * Types Marketing Class
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/classes/class.wpcf-marketing.php $
 * $LastChangedDate: 2015-02-18 14:28:53 +0000 (Wed, 18 Feb 2015) $
 * $LastChangedRevision: 1093394 $
 * $LastChangedBy: iworks $
 *
 */

/**
 * Types Marketing Class
 *
 * @since Types 1.6.5
 * @package Types
 * @subpackage Classes
 * @version 0.1
 * @category Help
 * @author marcin <marcin.p@icanlocalize.com>
 */
class WPCF_Types_Marketing
{
    protected $option_name = 'types-site-kind';
    protected $option_disable = 'types-site-kind-disable';
    protected $options;
    protected $adverts;

    public function __construct()
    {
        $this->options = include WPCF_ABSPATH.'/marketing/etc/types-site-kinds.php';
        $this->adverts = include WPCF_ABSPATH.'/marketing/etc/types.php';
        add_filter('admin_body_class', array($this, 'admin_body_class'));
        add_action( 'wpcf_menu_plus', array( $this, 'add_getting_started_to_admin_menu'), PHP_INT_MAX);
    }

    public function admin_body_class($classes)
    {
        $screen = get_current_screen();
        if ( isset($screen->id) && preg_match( '@marketing/getting-started/index$@', $screen->id ) ) {
            if ( !isset($_GET['kind'] )) {
                $classes = 'wpcf-marketing';
            }
            else if ( isset($_POST['marketing'])) {
                $classes = 'wpcf-marketing';
            }
        }

        return $classes;
    }

    protected function get_page_type()
    {
        $screen = get_current_screen();
        switch($screen->id) {
        case 'types_page_wpcf-edit-type':
            return 'cpt';
        case 'types_page_wpcf-edit-tax':
            return 'taxonomy';
        case 'types_page_wpcf-edit':
        case 'types_page_wpcf-edit-usermeta':
            return 'fields';
        }
        return false;
    }

    public function get_options()
    {
        return $this->options;
    }

    public function get_option_name()
    {
        return $this->option_name;
    }

    public function get_default_kind()
    {
        if ( isset($this->options) && is_array($this->options) ) {
            foreach ( $this->options as $kind => $options ) {
                if ( array_key_exists('default', $options ) && $options['default']) {
                    return $kind;
                }
            }
        }
        return false;
    }

    public function get_kind()
    {
        $kind = get_option($this->option_name, false);
        if (
            $kind
            && isset($this->options)
            && is_array($this->options)
            && array_key_exists( $kind, $this->options )
        ) {
            return $kind;
        }
        return false;
    }

    public function get_kind_url($kind = false)
    {
        if ( empty($kind) ) {
            $kind = $this->get_kind();
        }
        if (
            $kind
            && isset($this->options)
            && is_array($this->options)
            && array_key_exists('url', $this->options[$kind] )
        ) {
            return $this->options[$kind]['url'];
        }
        return;
    }

    public function get_option_disiable_value()
    {
        return get_option($this->option_disable, 0);
    }

    public function get_option_disiable_name()
    {
        return $this->option_disable;
    }

    protected function add_ga_campain($url, $utm_medium = 'getting-started')
    {
        $url = add_query_arg(
            array(
                'utm_source' => 'typesplugin',
                'utm_medium' =>  $utm_medium,
                'utm_campaig' => sprintf('%s-howto', $this->get_kind() ),
            ),
            $url
        );
        return $url;
    }

    /**
     * add Getting Started to menu
     */
    public function add_getting_started_to_admin_menu()
    {
        $menu = array(
            'page_title' => __( 'What kind of site are you building?', 'wpcf' ),
            'menu_title' => __( 'Getting Started', 'wpcf' ),
            'menu_slug' => basename(dirname(dirname(__FILE__))).'/marketing/getting-started/index.php',
            'hook' => 'wpcf_marketing',
            'load_hook' => 'wpcf_marketing_hook',
        );
        wpcf_admin_add_submenu_page($menu);
    }


}
