<?php

class Moesia_Action extends WP_Widget {

// constructor
    function moesia_action() {
		$widget_ops = array('classname' => 'moesia_action_widget', 'description' => __( 'Display a call to action block.', 'moesia') );
        parent::WP_Widget(false, $name = __('Moesia FP: Call to action', 'moesia'), $widget_ops);
		$this->alt_option_name = 'moesia_action_widget';
		
		add_action( 'save_post', array($this, 'flush_widget_cache') );
		add_action( 'deleted_post', array($this, 'flush_widget_cache') );
		add_action( 'switch_theme', array($this, 'flush_widget_cache') );		
    }
	
	// widget form creation
	function form($instance) {

	// Check values
		$title     			= isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$action_text 		= isset( $instance['action_text'] ) ? esc_textarea( $instance['action_text'] ) : '';
		$action_btn_link 	= isset( $instance['action_btn_link'] ) ? esc_url( $instance['action_btn_link'] ) : '';
		$action_btn_text 	= isset( $instance['action_btn_text'] ) ? esc_html( $instance['action_btn_text'] ) : '';
		$image_uri 			= isset( $instance['image_uri'] ) ? esc_url( $instance['image_uri'] ) : '';		
	?>

	<p>
	<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'moesia'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
	</p>

	<p>
	<label for="<?php echo $this->get_field_id('action_text'); ?>"><?php _e('Enter your call to action.', 'moesia'); ?></label>
	<textarea class="widefat" id="<?php echo $this->get_field_id('action_text'); ?>" name="<?php echo $this->get_field_name('action_text'); ?>"><?php echo $action_text; ?></textarea>
	</p>

	<p>
	<label for="<?php echo $this->get_field_id('action_btn_link'); ?>"><?php _e('Link for the button', 'moesia'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('action_btn_link'); ?>" name="<?php echo $this->get_field_name('action_btn_link'); ?>" type="text" value="<?php echo $action_btn_link; ?>" />
	</p>

	<p>
	<label for="<?php echo $this->get_field_id('action_btn_text'); ?>"><?php _e('Title for the button', 'moesia'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('action_btn_text'); ?>" name="<?php echo $this->get_field_name('action_btn_text'); ?>" type="text" value="<?php echo $action_btn_text; ?>" />
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

	// update widget
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] 			 = strip_tags($new_instance['title']);
		$instance['action_text'] 	 = strip_tags($new_instance['action_text']);
		$instance['action_btn_link'] = esc_url_raw($new_instance['action_btn_link']);
		$instance['action_btn_text'] = strip_tags($new_instance['action_btn_text']);
	    $instance['image_uri'] 		 = esc_url_raw( $new_instance['image_uri'] );			
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['moesia_action']) )
			delete_option('moesia_action');		  
		  
		return $instance;
	}
	
	function flush_widget_cache() {
		wp_cache_delete('moesia_action', 'widget');
	}
	
	// display widget
	function widget($args, $instance) {
		$cache = array();
		if ( ! $this->is_preview() ) {
			$cache = wp_cache_get( 'moesia_action', 'widget' );
		}

		if ( ! is_array( $cache ) ) {
			$cache = array();
		}

		if ( ! isset( $args['widget_id'] ) ) {
			$args['widget_id'] = $this->id;
		}

		if ( isset( $cache[ $args['widget_id'] ] ) ) {
			echo $cache[ $args['widget_id'] ];
			return;
		}

		ob_start();
		extract($args);

		$title 			 = ( ! empty( $instance['title'] ) ) ? $instance['title'] : '';
		$title 			 = apply_filters( 'widget_title', $title, $instance, $this->id_base );
		$action_text 	 = isset( $instance['action_text'] ) ? esc_textarea($instance['action_text']) : '';
		$action_btn_link = isset( $instance['action_btn_link'] ) ? esc_url($instance['action_btn_link']) : '';
		$action_btn_text = isset( $instance['action_btn_text'] ) ? esc_html($instance['action_btn_text']) : '';
		$image_uri 		 = isset( $instance['image_uri'] ) ? esc_url($instance['image_uri']) : '';		

?>
		<section id="call-to-action" class="action-area">
			<div class="container">
				<?php if ( $title ) echo $before_title . '<span class="wow bounce">' . $title . '</span>' . $after_title; ?>
				<?php if ($action_text !='') : ?>				
					<div class="action-text wow zoomIn">
						<?php echo $action_text; ?>
					</div>
				<?php endif; ?>
				<?php if ($action_btn_link !='') : ?>	
					<a href="<?php echo esc_url($action_btn_link); ?>" class="call-to-action wow zoomInUp"><?php echo esc_html($action_btn_text); ?></a>
				<?php endif; ?>
			</div>
		<?php if ($image_uri != '') : ?>
			<style type="text/css">
				.action-area {
				    display: block;			    
					background: url(<?php echo $image_uri; ?>) no-repeat;
					background-position: center top;
					background-attachment: fixed;
					background-size: cover;
					z-index: -1;
				}
			</style>
		<?php endif; ?>											
		</section>		
	<?php

		if ( ! $this->is_preview() ) {
			$cache[ $args['widget_id'] ] = ob_get_flush();
			wp_cache_set( 'moesia_action', $cache, 'widget' );
		} else {
			ob_end_flush();
		}
	}
	
}