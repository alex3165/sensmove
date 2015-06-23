<?php
/**
 * Moesia functions and definitions
 *
 * @package Moesia
 */


if ( ! function_exists( 'moesia_setup' ) ) :
/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function moesia_setup() {

	/*
	 * Make theme available for translation.
	 * Translations can be filed in the /languages/ directory.
	 * If you're building a theme based on Moesia, use a find and replace
	 * to change 'moesia' to the name of your theme in all the template files
	 */
	load_theme_textdomain( 'moesia', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	// Set the content width based on the theme's design and stylesheet.
	global $content_width;
	if ( ! isset( $content_width ) ) {
		$content_width = 1140; /* pixels */
	}	

	/*
	 * Enable support for Post Thumbnails on posts and pages.
	 *
	 * @link http://codex.wordpress.org/Function_Reference/add_theme_support#Post_Thumbnails
	 */
	add_theme_support( 'post-thumbnails' );
	add_image_size('project-image', 350, 250, true);
	add_image_size('moesia-thumb', 750);
	add_image_size('moesia-news-thumb', 400);
	add_image_size('moesia-employees-thumb', 430);
	add_image_size('moesia-clients-thumb', 150);
	add_image_size('moesia-testimonials-thumb', 100);

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'moesia' ),
	) );
	
	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'search-form', 'comment-form', 'comment-list', 'gallery', 'caption'
	) );

	/*
	 * Enable support for Post Formats.
	 * See http://codex.wordpress.org/Post_Formats
	 */
	add_theme_support( 'post-formats', array(
		'aside', 'image', 'video', 'quote', 'link'
	) );

	// Setup the WordPress core custom background feature.
	add_theme_support( 'custom-background', apply_filters( 'moesia_custom_background_args', array(
		'default-color' => 'f5f5f5',
		'default-image' => '',
	) ) );
}
endif; // moesia_setup
add_action( 'after_setup_theme', 'moesia_setup' );

/**
 * Register widget area.
 *
 * @link http://codex.wordpress.org/Function_Reference/register_sidebar
 */
function moesia_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Sidebar', 'moesia' ),
		'id'            => 'sidebar-1',
		'description'   => '',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
	register_sidebar( array(
		'name'          => __( 'Footer A', 'moesia' ),
		'id'            => 'sidebar-3',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );	
	register_sidebar( array(
		'name'          => __( 'Footer B', 'moesia' ),
		'id'            => 'sidebar-4',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );	
	register_sidebar( array(
		'name'          => __( 'Footer C', 'moesia' ),
		'id'            => 'sidebar-5',
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	//Register the front page widgets
	if ( function_exists('siteorigin_panels_activate') ) {
		register_widget( 'Moesia_Services' );
		register_widget( 'Moesia_Employees' );
		register_widget( 'Moesia_Fp_Social_Profile' );
		register_widget( 'Moesia_Blockquote' );
		register_widget( 'Moesia_Skills' );
		register_widget( 'Moesia_Facts' );
		register_widget( 'Moesia_Testimonials' );
		register_widget( 'Moesia_Clients' );
		register_widget( 'Moesia_Projects' );
		register_widget( 'Moesia_Action' );
		register_widget( 'Moesia_Latest_News' );
	}
	
	//Register the sidebar widgets
	register_widget( 'Moesia_Recent_Comments' );
	register_widget( 'Moesia_Recent_Posts' );
	register_widget( 'Moesia_Social_Profile' );
	register_widget( 'Moesia_Video_Widget' );
	register_widget( 'Moesia_Contact_Info' );	
}
add_action( 'widgets_init', 'moesia_widgets_init' );

/**
 * Load the front page widgets.
 */
if ( function_exists('siteorigin_panels_activate') ) {
	require get_template_directory() . "/widgets/fp-services.php";
	require get_template_directory() . "/widgets/fp-employees.php";
	require get_template_directory() . "/widgets/fp-social.php";
	require get_template_directory() . "/widgets/fp-blockquote.php";
	require get_template_directory() . "/widgets/fp-skills.php";
	require get_template_directory() . "/widgets/fp-facts.php";
	require get_template_directory() . "/widgets/fp-testimonials.php";
	require get_template_directory() . "/widgets/fp-clients.php";
	require get_template_directory() . "/widgets/fp-projects.php";
	require get_template_directory() . "/widgets/fp-call-to-action.php";
	require get_template_directory() . "/widgets/fp-latest-news.php";
}

/**
 * Load the sidebar widgets.
 */
require get_template_directory() . "/widgets/recent-comments.php";
require get_template_directory() . "/widgets/recent-posts.php";
require get_template_directory() . "/widgets/social-profile.php";
require get_template_directory() . "/widgets/video-widget.php";
require get_template_directory() . "/widgets/contact-info.php";

/**
 * Enqueue scripts and styles.
 */
function moesia_scripts() {

	wp_enqueue_style( 'moesia-bootstrap', get_template_directory_uri() . '/css/bootstrap/bootstrap.min.css', array(), true );
	
	wp_enqueue_style( 'moesia-style', get_stylesheet_uri() );


	if ( ! function_exists('moesia_fonts_extended') ) { //Check that the Moesia Fonts extension is not active
	//Load the fonts
		$headings_font = esc_html(get_theme_mod('headings_fonts'));
		$body_font = esc_html(get_theme_mod('body_fonts'));
		if( $headings_font ) {
			wp_enqueue_style( 'moesia-headings-fonts', '//fonts.googleapis.com/css?family='. $headings_font );	
		} else {
			wp_enqueue_style( 'moesia-roboto-condensed', '//fonts.googleapis.com/css?family=Roboto+Condensed:700');
		}	
		if( $body_font ) {
			wp_enqueue_style( 'moesia-body-fonts', '//fonts.googleapis.com/css?family='. $body_font );	
		} else {
			wp_enqueue_style( 'moesia-roboto', '//fonts.googleapis.com/css?family=Roboto:400,400italic,700,700italic');
		}
	}

	wp_enqueue_style( 'moesia-font-awesome', get_template_directory_uri() . '/fonts/font-awesome.min.css' );

	wp_enqueue_script( 'moesia-navigation', get_template_directory_uri() . '/js/navigation.js', array(), '20120206', true );

	wp_enqueue_script( 'moesia-waypoints', get_template_directory_uri() . '/js/waypoints.min.js', array('jquery'), true );

	if ( get_theme_mod('moesia_scroller') != 1 )  {
		
		wp_enqueue_script( 'moesia-nicescroll', get_template_directory_uri() . '/js/jquery.nicescroll.min.js', array('jquery'), true );	

		wp_enqueue_script( 'moesia-nicescroll-init', get_template_directory_uri() . '/js/nicescroll-init.js', array('jquery'), true );

	}	

	if ( is_page_template('page_front-page.php') ) {
	
		wp_enqueue_script( 'moesia-carousel', get_template_directory_uri() .  '/js/slick.min.js', array( 'jquery' ), true );
					
		wp_enqueue_script( 'moesia-carousel-init', get_template_directory_uri() .  '/js/carousel-init.js', array(), true );

		wp_enqueue_style( 'moesia-pretty-photo', get_template_directory_uri() . '/inc/prettyphoto/css/prettyPhoto.min.css' );

		wp_enqueue_script( 'moesia-pretty-photo-js', get_template_directory_uri() .  '/inc/prettyphoto/js/jquery.prettyPhoto.min.js', array(), true );	

		wp_enqueue_script( 'moesia-pretty-photo-init', get_template_directory_uri() .  '/inc/prettyphoto/js/prettyphoto-init.js', array(), true );

	}

	if ( get_theme_mod('moesia_animate') != true ) {
		
		wp_enqueue_script( 'moesia-wow', get_template_directory_uri() .  '/js/wow.min.js', array( 'jquery' ), true );

		wp_enqueue_style( 'moesia-animations', get_template_directory_uri() . '/css/animate/animate.min.css' );

		wp_enqueue_script( 'moesia-wow-init', get_template_directory_uri() .  '/js/wow-init.js', array( 'jquery' ), true );

	}

	wp_enqueue_script( 'moesia-sticky', get_template_directory_uri() .  '/js/jquery.sticky.js', array(), true );

	wp_enqueue_script( 'moesia-scripts', get_template_directory_uri() . '/js/scripts.js', array('jquery'), true );

	wp_enqueue_script( 'moesia-fitvids', get_template_directory_uri() . '/js/jquery.fitvids.js', array('jquery'), true );	

	wp_enqueue_script( 'moesia-skip-link-focus-fix', get_template_directory_uri() . '/js/skip-link-focus-fix.js', array(), '20130115', true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	if ( is_home() && get_theme_mod('blog_layout') == 'masonry' ) {

		wp_enqueue_script( 'jquery-masonry');

		wp_enqueue_script( 'moesia-imagesloaded', get_template_directory_uri() . '/js/imagesloaded.pkgd.min.js', array(), true );

		wp_enqueue_script( 'moesia-masonry-init', get_template_directory_uri() . '/js/masonry-init.js', array(), true );		
	}

}
add_action( 'wp_enqueue_scripts', 'moesia_scripts' );

/**
 * Enqueues the necessary script for image uploading in widgets
 */
add_action('admin_enqueue_scripts', 'moesia_image_upload');
function moesia_image_upload($post) {
    if( 'post.php' != $post )
        return;	
    wp_enqueue_script('moesia-image-upload', get_template_directory_uri() . '/js/image-uploader.js', array('jquery'), true );
	if ( did_action( 'wp_enqueue_media' ) )
		return;    
    wp_enqueue_media();    
}

/**
 * Load html5shiv
 */
function moesia_html5shiv() {
    echo '<!--[if lt IE 9]>' . "\n";
    echo '<script src="' . esc_url( get_template_directory_uri() . '/js/html5shiv.js' ) . '"></script>' . "\n";
    echo '<![endif]-->' . "\n";
}
add_action( 'wp_head', 'moesia_html5shiv' ); 

/**
 * Adds more contact methods in the User profile screen (links used for the author bio).
 */
function moesia_contactmethods( $contactmethods ) {
	
	$contactmethods['moesia_facebook'] = __( 'Author Bio: Facebook', 'moesia' );
	$contactmethods['moesia_twitter'] = __( 'Author Bio: Twitter', 'moesia' );	
	$contactmethods['moesia_googleplus'] = __( 'Author Bio: Google Plus', 'moesia' );
	$contactmethods['moesia_linkedin'] = __( 'Author Bio: Linkedin', 'moesia' );
	
	return $contactmethods;
}
add_filter( 'user_contactmethods', 'moesia_contactmethods', 10, 1);


/**
 * Change the excerpt length
 */
function moesia_excerpt_length( $length ) {
	
	$excerpt = get_theme_mod('exc_lenght', '30');
	return $excerpt;

}
add_filter( 'excerpt_length', 'moesia_excerpt_length', 999 );

/**
 * Nav bar
 */
if ( ! function_exists( 'moesia_nav_bar' ) ) {
function moesia_nav_bar() {
	echo '<div class="top-bar">
			<div class="container">
				<div class="site-branding col-md-4">';
				if ( get_theme_mod('site_logo') ) :
					echo '<a href="' . esc_url( home_url( '/' ) ) . '" title="';
						bloginfo('name');
					echo '"><img class="site-logo" src="' . esc_url(get_theme_mod('site_logo')) . '" alt="';
						bloginfo('name');
					echo '" /></a>';
				else :
					echo '<h1 class="site-title"><a href="' . esc_url( home_url( '/' ) ) . '" rel="home">';
						bloginfo( 'name' );
					echo '</a></h1>';
					echo '<h2 class="site-description">';
						bloginfo( 'description' );
					echo '</h2>';
				endif;
			echo '</div>';
			echo '<button class="menu-toggle btn"><i class="fa fa-bars"></i></button>
				<nav id="site-navigation" class="main-navigation col-md-8" role="navigation">';
				wp_nav_menu( array( 'theme_location' => 'primary' ) );
			echo '</nav>';
			
			if ( get_theme_mod('toggle_search', 0) ) :
				echo '<span class="nav-search"><i class="fa fa-search"></i></span>';
				echo '<span class="nav-deco"></span>';
				echo '<div class="nav-search-box">';
					get_search_form();
				echo '</div>';
			endif;
		echo '</div>';
	echo '</div>';
}
}
if (get_theme_mod('moesia_menu_top', 0) == 0) {
	add_action('tha_header_after', 'moesia_nav_bar');
} else {
	add_action('tha_header_before', 'moesia_nav_bar');
}

/**
 * Get image IDs
 */
function moesia_get_image_id($photo) {
	global $wpdb;
	$attachment = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid='%s';", $photo )); 
    return $attachment[0]; 
}

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Custom functions that act independently of the theme templates.
 */
require get_template_directory() . '/inc/extras.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
require get_template_directory() . '/inc/jetpack.php';
/**
 * Dynamic styles
 */
require get_template_directory() . '/styles.php';
/**
 * Page builder styles
 */
require get_template_directory() . '/inc/rows.php';
/**
 * Theme Hook Alliance
 */
require get_template_directory() . '/inc/tha-theme-hooks.php';

/**
 *TGM Plugin activation.
 */
require_once dirname( __FILE__ ) . '/plugins/class-tgm-plugin-activation.php';
 
add_action( 'tgmpa_register', 'moesia_recommend_plugin' );
function moesia_recommend_plugin() {
 
    $plugins = array(
        array(
            'name'               => 'Page Builder by SiteOrigin',
            'slug'               => 'siteorigin-panels',
            'required'           => false,
        ),
        array(
            'name'               => 'Types - Custom Fields and Custom Post Types Management',
            'slug'               => 'types',
            'required'           => false,
        ),          
    );
 
    tgmpa( $plugins);
 
}