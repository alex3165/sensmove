<?php

class Moesia_Facts extends WP_Widget {

// constructor
    function moesia_facts() {
		$widget_ops = array('classname' => 'moesia_facts_widget', 'description' => __( 'Show your visitors some facts about your company.', 'moesia') );
        parent::WP_Widget(false, $name = __('Moesia FP: Facts', 'moesia'), $widget_ops);
		$this->alt_option_name = 'moesia_facts_widget';
		
		add_action( 'save_post', array($this, 'flush_widget_cache') );
		add_action( 'deleted_post', array($this, 'flush_widget_cache') );
		add_action( 'switch_theme', array($this, 'flush_widget_cache') );		
    }
	
	// widget form creation
	function form($instance) {

	// Check values
		$title     		= isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$fact_one   	= isset( $instance['fact_one'] ) ? esc_html( $instance['fact_one'] ) : '';
		$fact_one_max  = isset( $instance['fact_one_max'] ) ? absint( $instance['fact_one_max'] ) : '';
		$fact_two   	= isset( $instance['fact_two'] ) ? esc_attr( $instance['fact_two'] ) : '';
		$fact_two_max  = isset( $instance['fact_two_max'] ) ? absint( $instance['fact_two_max'] ) : '';
		$fact_three   	= isset( $instance['fact_three'] ) ? esc_attr( $instance['fact_three'] ) : '';
		$fact_three_max= isset( $instance['fact_three_max'] ) ? absint( $instance['fact_three_max'] ) : '';
		$fact_four   	= isset( $instance['fact_four'] ) ? esc_attr( $instance['fact_four'] ) : '';		
		$fact_four_max = isset( $instance['fact_four_max'] ) ? absint( $instance['fact_four_max'] ) : '';	
		$image_uri = isset( $instance['image_uri'] ) ? esc_url( $instance['image_uri'] ) : '';		
	?>

	<p>
	<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'moesia'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
	</p>

	<!-- fact one -->
	<p>
	<label for="<?php echo $this->get_field_id('fact_one'); ?>"><?php _e('First fact name', 'moesia'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('fact_one'); ?>" name="<?php echo $this->get_field_name('fact_one'); ?>" type="text" value="<?php echo $fact_one; ?>" />
	</p>

	<p>
	<label for="<?php echo $this->get_field_id('fact_one_max'); ?>"><?php _e('First fact value', 'moesia'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('fact_one_max'); ?>" name="<?php echo $this->get_field_name('fact_one_max'); ?>" type="text" value="<?php echo $fact_one_max; ?>" />
	</p>

	<!-- fact two -->
	<p>
	<label for="<?php echo $this->get_field_id('fact_two'); ?>"><?php _e('Second fact name', 'moesia'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('fact_two'); ?>" name="<?php echo $this->get_field_name('fact_two'); ?>" type="text" value="<?php echo $fact_two; ?>" />
	</p>

	<p>
	<label for="<?php echo $this->get_field_id('fact_two_max'); ?>"><?php _e('Second fact value', 'moesia'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('fact_two_max'); ?>" name="<?php echo $this->get_field_name('fact_two_max'); ?>" type="text" value="<?php echo $fact_two_max; ?>" />
	</p>

	<!-- fact three -->
	<p>
	<label for="<?php echo $this->get_field_id('fact_three'); ?>"><?php _e('Third fact name', 'moesia'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('fact_three'); ?>" name="<?php echo $this->get_field_name('fact_three'); ?>" type="text" value="<?php echo $fact_three; ?>" />
	</p>

	<p>
	<label for="<?php echo $this->get_field_id('fact_three_max'); ?>"><?php _e('Third fact value', 'moesia'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('fact_three_max'); ?>" name="<?php echo $this->get_field_name('fact_three_max'); ?>" type="text" value="<?php echo $fact_three_max; ?>" />
	</p>

	<!-- fact four -->
	<p>
	<label for="<?php echo $this->get_field_id('fact_four'); ?>"><?php _e('Fourth fact name', 'moesia'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('fact_four'); ?>" name="<?php echo $this->get_field_name('fact_four'); ?>" type="text" value="<?php echo $fact_four; ?>" />
	</p>

	<p>
	<label for="<?php echo $this->get_field_id('fact_four_max'); ?>"><?php _e('Fourth fact value', 'moesia'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('fact_four_max'); ?>" name="<?php echo $this->get_field_name('fact_four_max'); ?>" type="text" value="<?php echo $fact_four_max; ?>" />
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
		$instance['title'] 			= strip_tags($new_instance['title']);
		$instance['fact_one'] 		= strip_tags($new_instance['fact_one']);
		$instance['fact_one_max'] 	= intval($new_instance['fact_one_max']);
		$instance['fact_two'] 		= strip_tags($new_instance['fact_two']);
		$instance['fact_two_max'] 	= intval($new_instance['fact_two_max']);
		$instance['fact_three'] 	= strip_tags($new_instance['fact_three']);
		$instance['fact_three_max']= intval($new_instance['fact_three_max']);
		$instance['fact_four'] 	= strip_tags($new_instance['fact_four']);
		$instance['fact_four_max'] = intval($new_instance['fact_four_max']);
	    $instance['image_uri'] 		= esc_url_raw( $new_instance['image_uri'] );			
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['moesia_facts']) )
			delete_option('moesia_facts');		  
		  
		return $instance;
	}
	
	function flush_widget_cache() {
		wp_cache_delete('moesia_facts', 'widget');
	}
	
	// display widget
	function widget($args, $instance) {
		$cache = array();
		if ( ! $this->is_preview() ) {
			$cache = wp_cache_get( 'moesia_facts', 'widget' );
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

		$title 			= ( ! empty( $instance['title'] ) ) ? $instance['title'] : __( 'Our facts', 'moesia' );
		$title 			= apply_filters( 'widget_title', $title, $instance, $this->id_base );
		$fact_one   	= isset( $instance['fact_one'] ) ? esc_html( $instance['fact_one'] ) : '';
		$fact_one_max  = isset( $instance['fact_one_max'] ) ? absint( $instance['fact_one_max'] ) : '';
		$fact_two   	= isset( $instance['fact_two'] ) ? esc_attr( $instance['fact_two'] ) : '';
		$fact_two_max  = isset( $instance['fact_two_max'] ) ? absint( $instance['fact_two_max'] ) : '';
		$fact_three   	= isset( $instance['fact_three'] ) ? esc_attr( $instance['fact_three'] ) : '';
		$fact_three_max= isset( $instance['fact_three_max'] ) ? absint( $instance['fact_three_max'] ) : '';
		$fact_four   	= isset( $instance['fact_four'] ) ? esc_attr( $instance['fact_four'] ) : '';		
		$fact_four_max = isset( $instance['fact_four_max'] ) ? absint( $instance['fact_four_max'] ) : '';		
		$image_uri 		= isset( $instance['image_uri'] ) ? esc_url($instance['image_uri']) : '';		

?>
		<section id="facts" class="facts-area">
			<div class="container">
				<?php if ( $title ) echo $before_title . '<span class="wow bounce">' . $title . '</span>' . $after_title; ?>
				<?php if ($fact_one !='') : ?>
					<div class="col-md-3 col-sm-3 col-xs-6">
						<span class="fact wow rotateIn" id="<?php echo absint($fact_one_max); ?>">0</span>
						<div class="fact-name wow fadeInUp"><?php echo esc_html($fact_one); ?></div>
					</div>
				<?php endif; ?>
				<?php if ($fact_two !='') : ?>
					<div class="col-md-3 col-sm-3 col-xs-6">
						<span class="fact wow rotateIn" id="<?php echo absint($fact_two_max); ?>">0</span>
						<div class="fact-name wow fadeInUp"><?php echo esc_html($fact_two); ?></div>
					</div>
				<?php endif; ?>
				<?php if ($fact_three !='') : ?>
					<div class="col-md-3 col-sm-3 col-xs-6">
						<span class="fact wow rotateIn" id="<?php echo absint($fact_three_max); ?>">0</span>
						<div class="fact-name wow fadeInUp"><?php echo esc_html($fact_three); ?></div>
					</div>
				<?php endif; ?>
				<?php if ($fact_four !='') : ?>
					<div class="col-md-3 col-sm-3 col-xs-6">
						<span class="fact wow rotateIn" id="<?php echo absint($fact_four_max); ?>">0</span>
						<div class="fact-name wow fadeInUp"><?php echo esc_html($fact_four); ?></div>
					</div>
				<?php endif; ?>																			
			</div>
			<?php if ($image_uri != '') : ?>
				<style type="text/css">
					.facts-area {
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
			wp_cache_set( 'moesia_facts', $cache, 'widget' );
		} else {
			ob_end_flush();
		}
	}
	
}