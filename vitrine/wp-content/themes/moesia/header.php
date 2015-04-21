<?php
/**
 * The header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="content">
 *
 * @package Moesia
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php wp_title( '|', true, 'right' ); ?></title>
<link rel="profile" href="http://gmpg.org/xfn/11">
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
<?php if ( get_theme_mod('site_favicon') ) : ?>
	<link rel="shortcut icon" href="<?php echo esc_url(get_theme_mod('site_favicon')); ?>" />
<?php endif; ?>

<?php if ( get_theme_mod('apple_touch_144') ) : ?>
	<link rel="apple-touch-icon" sizes="144x144" href="<?php echo esc_url(get_theme_mod('apple_touch_144')); ?>" />
<?php endif; ?>
<?php if ( get_theme_mod('apple_touch_114') ) : ?>
	<link rel="apple-touch-icon" sizes="114x114" href="<?php echo esc_url(get_theme_mod('apple_touch_114')); ?>" />
<?php endif; ?>
<?php if ( get_theme_mod('apple_touch_72') ) : ?>
	<link rel="apple-touch-icon" sizes="72x72" href="<?php echo esc_url(get_theme_mod('apple_touch_72')); ?>" />
<?php endif; ?>
<?php if ( get_theme_mod('apple_touch_57') ) : ?>
	<link rel="apple-touch-icon" href="<?php echo esc_url(get_theme_mod('apple_touch_57')); ?>" />
<?php endif; ?>

<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<div id="page" class="hfeed site">
	<a class="skip-link screen-reader-text" href="#content"><?php _e( 'Skip to content', 'moesia' ); ?></a>

	<?php //Single page header image data
		$himage  = get_post_meta( get_the_ID(), 'wpcf-header-image', true );
		$htitle  = get_post_meta( get_the_ID(), 'wpcf-header-title', true );
		$hlogo 	 = get_post_meta( get_the_ID(), 'wpcf-header-logo', true );
		$htext 	 = get_post_meta( get_the_ID(), 'wpcf-header-text', true );
		$hbutton = get_post_meta( get_the_ID(), 'wpcf-header-button-title', true );
		$hlink 	 = get_post_meta( get_the_ID(), 'wpcf-header-button-link', true );
	?>
	<?php tha_header_before(); ?>
	<?php if ( get_header_image() && $himage == '' ) : ?>
		<?php if ( get_theme_mod('moesia_banner') == 1 && !is_front_page() ) : ?>
			<header id="masthead" class="site-header" role="banner">
			<?php tha_header_top(); ?>
		<?php else : ?>
			<header id="masthead" class="site-header has-banner" role="banner">
			<?php tha_header_top(); ?>	
			<?php if ( get_theme_mod('mobile_header') ) : ?>
				<img class="header-image" src="<?php echo esc_url( get_theme_mod('mobile_header') ); ?>">
			<?php else : ?>
				<img class="header-image" src="<?php echo esc_url(get_header_image()); ?>">
			<?php endif; ?>
		<?php endif; ?>
		<?php if ( (get_theme_mod('moesia_banner') == 1 && is_front_page()) ||  (get_theme_mod('moesia_banner') != 1)) : ?>
			<?php if ( get_theme_mod('header_overlay') != 1 ) : ?>
				<div class="overlay"></div>
			<?php endif; ?>
			<div class="welcome-info">
				<?php if ( get_theme_mod('header_logo') ) : ?>
					<img class="welcome-logo wow bounceInDown" src="<?php echo esc_url(get_theme_mod('header_logo')); ?>" />
				<?php elseif ( get_theme_mod('header_title') ) : ?>
					<div class="welcome-title wow bounceInDown"><?php echo esc_attr(get_theme_mod('header_title')); ?></div>
				<?php endif; ?>
				<?php if ( get_theme_mod('header_desc') ) : ?>
					<div class="welcome-desc wow bounceInRight" data-wow-delay="0.2s"><?php echo esc_html(get_theme_mod('header_desc')); ?></div>
				<?php endif; ?>
				<?php if (get_theme_mod('header_btn_text') && get_theme_mod('header_btn_link')) : ?>
					<a href="<?php echo esc_url(get_theme_mod('header_btn_link')); ?>" class="welcome-button wow bounceInUp" data-wow-delay="0.3s"><?php echo esc_html(get_theme_mod('header_btn_text')); ?></a>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<?php tha_header_bottom(); ?>
		</header><!-- #masthead -->
	<?php elseif ( $himage != '' ) : ?>	
		<header id="masthead" class="site-header has-banner" role="banner">
			<?php tha_header_top(); ?>
			<img class="header-image" src="<?php echo esc_url($himage); ?>">
			<div class="welcome-info">
				
				<?php if ( $hlogo ) : ?>
					<img class="welcome-logo wow bounceInDown" src="<?php echo esc_url($hlogo); ?>" />
				<?php elseif ( $htitle ) : ?>
					<div class="welcome-title wow bounceInDown"><?php echo esc_html($htitle); ?></div>
				<?php endif; ?>

				<?php if ( $htext ) : ?>
					<div class="welcome-desc wow bounceInRight" data-wow-delay="0.2s"><?php echo esc_html($htext); ?></div>
				<?php endif; ?>
				<?php if ($hbutton && $hlink) : ?>
					<a href="<?php echo esc_url($hlink); ?>" class="welcome-button wow bounceInUp" data-wow-delay="0.3s"><?php echo esc_html($hbutton); ?></a>
				<?php endif; ?>
			</div>
			<?php tha_header_bottom(); ?>			
		</header><!-- #masthead -->	
	<?php endif; ?>
	<?php tha_header_after(); ?>

	<?php if ( !is_page_template('page_front-page.php') || ( 'posts' == get_option( 'show_on_front' ) ) ) : ?>
		<?php $container = "container"; ?>
	<?php else : ?>
		<?php $container = ""; ?>
	<?php endif; ?>
	<?php tha_content_before(); ?>
	<div id="content" class="site-content clearfix <?php echo $container; ?>">
		<?php tha_content_top(); ?>
