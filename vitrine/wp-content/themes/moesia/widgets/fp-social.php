<?php
 class Moesia_Fp_Social_Profile extends WP_Widget {

	function __construct() {
		$widget_ops = array( 'description' => __('Add your social profile.', 'moesia') );
		parent::__construct( 'fp_social', __('Moesia FP: Social Profile', 'moesia'), $widget_ops );
	}

	function widget($args, $instance) {
		// Get menu
		$nav_menu = ! empty( $instance['nav_menu'] ) ? wp_get_nav_menu_object( $instance['nav_menu'] ) : false;		

		if ( !$nav_menu )
			return;

		$image_uri = isset( $instance['image_uri'] ) ? esc_url($instance['image_uri']) : '';

		/** This filter is documented in wp-includes/default-widgets.php */
		$instance['title'] = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );

		?>
		<section id="social" class="social-area">		

		<?php
		if ( !empty($instance['title']) )
			echo $args['before_title'] . '<span class="wow bounce">' . $instance['title'] . '</span>' . $args['after_title'];
		?>
			<div class="container wow zoomInUp">
				<?php wp_nav_menu(
					array( 
						'fallback_cb' => false,
						'menu' => $nav_menu,
						'link_before' => '<span class="screen-reader-text">',
						'link_after' => '</span>',
						'menu_class' => 'menu clearfix'
						) 
				); ?>
			</div>
		</section>
		<?php if ($image_uri != '') : ?>
			<style type="text/css">
				.social-area {
				    display: block;			    
					background: url(<?php echo $image_uri; ?>) no-repeat;
					background-position: center top;
					background-attachment: fixed;
					background-size: cover;
					z-index: -1;
				}
			</style>
		<?php endif; ?>			
		
		<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance['title'] = strip_tags( stripslashes($new_instance['title']) );
		$instance['nav_menu'] = (int) $new_instance['nav_menu'];
	    $instance['image_uri'] = esc_url_raw( $new_instance['image_uri'] );			
		return $instance;
	}

	function form( $instance ) {
		$title = isset( $instance['title'] ) ? $instance['title'] : '';
		$nav_menu = isset( $instance['nav_menu'] ) ? $instance['nav_menu'] : '';
		$image_uri = isset( $instance['image_uri'] ) ? esc_url_raw( $instance['image_uri'] ) : '';		

		// Get menus
		$menus = wp_get_nav_menus( array( 'orderby' => 'name' ) );

		// If no menus exists, direct the user to go and create some.
		if ( !$menus ) {
			echo '<p>'. sprintf( __('No menus have been created yet. <a href="%s">Create some</a>.', 'moesia'), admin_url('nav-menus.php') ) .'</p>';
			return;
		}
		?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'moesia') ?></label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $title; ?>" />
		</p>
		<p><em><?php _e('In order to display your social icons in a widget, all you need to do is go to <strong>Appearance > Menus</strong> and create a menu containing links to your social profiles, then assign that menu here. Supported networks: Facebook, Twitter, Google Plus, Instagram, Dribble, Vimeo, Linkedin, Youtube, Flickr, Pinterest, Tumblr, Foursquare, Behance.', 'moesia'); ?></em></p>
		<p>
			<label for="<?php echo $this->get_field_id('nav_menu'); ?>"><?php _e('Select your social menu:', 'moesia'); ?></label>
			<select id="<?php echo $this->get_field_id('nav_menu'); ?>" name="<?php echo $this->get_field_name('nav_menu'); ?>">
				<option value="0"><?php _e( '&mdash; Select &mdash;', 'moesia' ) ?></option>
		<?php
			foreach ( $menus as $menu ) {
				echo '<option value="' . $menu->term_id . '"'
					. selected( $nav_menu, $menu->term_id, false )
					. '>'. esc_html( $menu->name ) . '</option>';
			}
		?>
			</select>
		</p>
	    <?php
	        if ( $image_uri != '' ) :
	           echo '<p><img class="custom_media_image" src="' . $image_uri . '" style="max-width:100px;" /></p>';
	        endif;
	    ?>
	    <p><label for="<?php echo $this->get_field_id('image_uri'); ?>"><?php _e('[DEPRECATED - Go to Edit Row > Theme > Background image] Upload an image for the background if you want. It will get a parallax effect.', 'moesia'); ?></label></p> 
	    <p><input type="button" class="button button-primary custom_media_button" id="custom_media_button" name="<?php echo $this->get_field_name('image_uri'); ?>" value="Upload Image" style="margin-top:5px;" /></p>
		<p><input class="widefat custom_media_url" id="<?php echo $this->get_field_id( 'image_uri' ); ?>" name="<?php echo $this->get_field_name( 'image_uri' ); ?>" type="text" value="<?php echo $image_uri; ?>" size="3" /></p>		
		<?php
	}
}