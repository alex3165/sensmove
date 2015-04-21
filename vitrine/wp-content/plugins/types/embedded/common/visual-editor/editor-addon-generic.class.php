<?php

if( !class_exists( 'Editor_addon_generic' ) )
{
	if ( !defined( 'ICL_COMMON_FUNCTIONS' ) ) {
        require_once dirname( dirname( __FILE__ ) ) . '/functions.php';
    }

    define( 'EDITOR_ADDON_ABSPATH', dirname( __FILE__ ) );
    if ( !defined( 'EDITOR_ADDON_RELPATH' ) ) {
        define( 'EDITOR_ADDON_RELPATH', icl_get_file_relpath( __FILE__ ) );
    }

    add_action( 'admin_enqueue_scripts', 'icl_editor_admin_enqueue_styles' );
	
	if ( ! function_exists( 'icl_editor_admin_enqueue_styles' ) ) {
		function icl_editor_admin_enqueue_styles() {
	        global $pagenow;
	        if ( 
				$pagenow == 'post.php'
				|| $pagenow == 'post-new.php'
				|| (
					$pagenow == 'admin.php'
					&& isset( $_GET['page'] )
					&& (
						$_GET['page'] == 'views-editor'
						|| $_GET['page'] == 'view-archives-editor'
						|| $_GET['page'] == 'dd_layouts_edit'
					) 
				) // add the new Views edit screens
	        ) {
	            wp_enqueue_style( 'editor_addon_menu',
	                    EDITOR_ADDON_RELPATH . '/res/css/pro_dropdown_2.css' );
	            wp_enqueue_style( 'editor_addon_menu_scroll',
	                    EDITOR_ADDON_RELPATH . '/res/css/scroll.css' );
	        }
	    }
	}
    

    add_action( 'admin_enqueue_scripts', 'icl_editor_admin_enqueue_scripts' );
	
	if ( ! function_exists( 'icl_editor_admin_enqueue_scripts' ) ) {
        function icl_editor_admin_enqueue_scripts() {
            global $pagenow;
			if ( 
				$pagenow == 'post.php'
				|| $pagenow == 'post-new.php'
				|| ( 
					$pagenow == 'admin.php' 
					&& isset( $_GET['page'] )
					&& (
						$_GET['page'] == 'views-editor'
						|| $_GET['page'] == 'view-archives-editor'
						|| $_GET['page'] == 'dd_layouts_edit'
					) 
				)
            ) {
				wp_register_script( 'icl_editor-script', EDITOR_ADDON_RELPATH . '/res/js/icl_editor_addon_plugin.js', array( 'jquery', 'quicktags', 'wplink' ) );
                wp_enqueue_script( 'icl_editor-script' );
            }
			if (
				$pagenow == 'admin.php' 
				&& isset( $_GET['page'] )
				&& (
					$_GET['page'] == 'views-editor'
					|| $_GET['page'] == 'view-archives-editor'
					|| $_GET['page'] == 'dd_layouts_edit' 
				)
				&& !wp_script_is( 'views-redesign-media-manager-js', 'enqueued' )
			) {
				$media_manager_translations = array(
					'only_img_allowed_here' => __( "You can only use an image file here", 'wpv-views' )
				);
				wp_enqueue_media();
				wp_enqueue_script( 'icl_media-manager-js',
						EDITOR_ADDON_RELPATH . '/res/js/icl_media_manager.js',
						array( 'jquery', 'icl_editor-script' ) );
				wp_localize_script( 'icl_media-manager-js', 'icl_media_manager', $media_manager_translations );
			}
        }
    }
    
    class Editor_addon_generic
    {
		protected $items;
		public $logger = null;
		protected $template = '';
		public $view = null;
				
        public function __construct( $name, $button_text, $plugin_js_url,
            $media_button_image = '', $print_button = true, $icon_class = '' ) {
            
            global $wplogger;
            $this->logger = $wplogger;
	        
            $this->name = $name;
            $this->plugin_js_url = $plugin_js_url;
            $this->button_text = $button_text;
            $this->media_button_image = $media_button_image;
            $this->initialized = false;
            $this->icon_class = $icon_class;


            if ( ( $media_button_image != '' || $icon_class != '' ) && $print_button ) {
                // Media buttons
                //Adding "embed form" button
                // WP 3.3 changes
                global $wp_version;
                if ( version_compare( $wp_version, '3.1.4', '>' ) ) {
                    add_action( 'media_buttons',
                            array($this, 'add_form_button'), 10, 2 );
                } else {
                    add_action( 'media_buttons_context',
                            array($this, 'add_form_button'), 10, 2 );
                }
            }

//            add_action('media_buttons', array($this, 'media_buttons'), 11);
//            wp_enqueue_style('editor_addon', plugins_url() . '/' . basename(dirname(dirname(dirname(__FILE__)))) . '/common/' . basename(dirname(__FILE__)) . '/res/css/style.css');
        }

        public function __destruct() {
            
        }

        /*

          Add a menu item that will insert the shortcode.

          To use sub menus, add a '-!-' separator between levels in
          the $menu parameter.
          eg.  Field-!-image
          This will create/use a menu "Field" and add a sub menu "image"

          $function_name is the javascript function to call for the on-click
          If it's left blank then a function will be created that just
          inserts the shortcode.

         */

        public function add_insert_shortcode_menu( $text, $shortcode, $menu,
                $function_name = '' ) {
            $this->items[] = array($text, $shortcode, $menu, $function_name);
        }
        
        public function add_form_button( $context, $text_area, $standard_v, $add_views, $codemirror_button )
        {
        	throw new Exception( 'You should implement this method '. __METHOD__ );
        }
        
        public static function getWpForbiddenNames()
        {
        	global $wp_post_types;
        	
	        $reserved_list = array(
				'attachment', 'attachment_id', 'author', 'author_name', 'calendar', 'cat', 'category', 'category__and', 'category__in',
				'category__not_in', 'category_name', 'comments_per_page', 'comments_popup', 'customize_messenger_channel',
				'customized', 'cpage', 'day', 'debug', 'error', 'exact', 'feed', 'hour', 'link_category', 'm', 'minute',
				'monthnum', 'more', 'name', 'nav_menu', 'nonce', 'nopaging', 'offset', 'order', 'orderby', 'p', 'page', 'page_id',
				'paged', 'pagename', 'pb', 'perm', 'post', 'post__in', 'post__not_in', 'post_format', 'post_mime_type', 'post_status',
				'post_tag', 'post_type', 'posts', 'posts_per_archive_page', 'posts_per_page', 'preview', 'robots', 's', 'search',
				'second', 'sentence', 'showposts', 'static', 'subpost', 'subpost_id', 'tag', 'tag__and', 'tag__in', 'tag__not_in',
				'tag_id', 'tag_slug__and', 'tag_slug__in', 'taxonomy', 'tb', 'term', 'theme', 'type', 'w', 'withcomments', 'withoutcomments',
				'year '
			);
	
	
	    	$reserved_post_types = array_keys( $wp_post_types );
	    	
	    	$wpv_taxes = get_taxonomies();
	    	$reserved_taxonomies = array_keys( $wpv_taxes );
	    	
	    	$wpv_forbidden_parameters = array(
				'wordpress' => $reserved_list,
				'post_types' => $reserved_post_types,
				'taxonomies' => $reserved_taxonomies,
	    	);
	    	
	    	return $wpv_forbidden_parameters;
        }
                         
    }

}

