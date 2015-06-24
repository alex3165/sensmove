<?php

class Moesia_Skills extends WP_Widget {

// constructor
    function moesia_skills() {
		$widget_ops = array('classname' => 'moesia_skills_widget', 'description' => __( 'Show your strongest five skills.', 'moesia') );
        parent::WP_Widget(false, $name = __('Moesia FP: Skills', 'moesia'), $widget_ops);
		$this->alt_option_name = 'moesia_skills_widget';
		
		add_action( 'save_post', array($this, 'flush_widget_cache') );
		add_action( 'deleted_post', array($this, 'flush_widget_cache') );
		add_action( 'switch_theme', array($this, 'flush_widget_cache') );		
    }
	
	// widget form creation
	function form($instance) {

	// Check values
		$title     		= isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$skills_desc 	= isset( $instance['skills_desc'] ) ? esc_textarea( $instance['skills_desc'] ) : '';
		$skill_one   	= isset( $instance['skill_one'] ) ? esc_html( $instance['skill_one'] ) : '';
		$skill_one_max  = isset( $instance['skill_one_max'] ) ? absint( $instance['skill_one_max'] ) : '';
		$skill_two   	= isset( $instance['skill_two'] ) ? esc_html( $instance['skill_two'] ) : '';
		$skill_two_max  = isset( $instance['skill_two_max'] ) ? absint( $instance['skill_two_max'] ) : '';
		$skill_three   	= isset( $instance['skill_three'] ) ? esc_html( $instance['skill_three'] ) : '';
		$skill_three_max= isset( $instance['skill_three_max'] ) ? absint( $instance['skill_three_max'] ) : '';
		$skill_four   	= isset( $instance['skill_four'] ) ? esc_html( $instance['skill_four'] ) : '';		
		$skill_four_max = isset( $instance['skill_four_max'] ) ? absint( $instance['skill_four_max'] ) : '';
		$skill_five   	= isset( $instance['skill_five'] ) ? esc_html( $instance['skill_five'] ) : '';
		$skill_five_max = isset( $instance['skill_five_max'] ) ? absint( $instance['skill_five_max'] ) : '';	
		$image_uri = isset( $instance['image_uri'] ) ? esc_url( $instance['image_uri'] ) : '';		
	?>

	<p>
	<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'moesia'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
	</p>

	<p>
	<label for="<?php echo $this->get_field_id('skills_desc'); ?>"><?php _e('Enter your description for the skills area.', 'moesia'); ?></label>
	<textarea class="widefat" id="<?php echo $this->get_field_id('skills_desc'); ?>" name="<?php echo $this->get_field_name('skills_desc'); ?>"><?php echo $skills_desc; ?></textarea>
	</p>

	<!-- Skill one -->
	<p>
	<label for="<?php echo $this->get_field_id('skill_one'); ?>"><?php _e('First skill name', 'moesia'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('skill_one'); ?>" name="<?php echo $this->get_field_name('skill_one'); ?>" type="text" value="<?php echo $skill_one; ?>" />
	</p>

	<p>
	<label for="<?php echo $this->get_field_id('skill_one_max'); ?>"><?php _e('First skill value', 'moesia'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('skill_one_max'); ?>" name="<?php echo $this->get_field_name('skill_one_max'); ?>" type="text" value="<?php echo $skill_one_max; ?>" />
	</p>

	<!-- Skill two -->
	<p>
	<label for="<?php echo $this->get_field_id('skill_two'); ?>"><?php _e('Second skill name', 'moesia'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('skill_two'); ?>" name="<?php echo $this->get_field_name('skill_two'); ?>" type="text" value="<?php echo $skill_two; ?>" />
	</p>

	<p>
	<label for="<?php echo $this->get_field_id('skill_two_max'); ?>"><?php _e('Second skill value', 'moesia'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('skill_two_max'); ?>" name="<?php echo $this->get_field_name('skill_two_max'); ?>" type="text" value="<?php echo $skill_two_max; ?>" />
	</p>

	<!-- Skill three -->
	<p>
	<label for="<?php echo $this->get_field_id('skill_three'); ?>"><?php _e('Third skill name', 'moesia'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('skill_three'); ?>" name="<?php echo $this->get_field_name('skill_three'); ?>" type="text" value="<?php echo $skill_three; ?>" />
	</p>

	<p>
	<label for="<?php echo $this->get_field_id('skill_three_max'); ?>"><?php _e('Third skill value', 'moesia'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('skill_three_max'); ?>" name="<?php echo $this->get_field_name('skill_three_max'); ?>" type="text" value="<?php echo $skill_three_max; ?>" />
	</p>

	<!-- Skill four -->
	<p>
	<label for="<?php echo $this->get_field_id('skill_four'); ?>"><?php _e('Fourth skill name', 'moesia'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('skill_four'); ?>" name="<?php echo $this->get_field_name('skill_four'); ?>" type="text" value="<?php echo $skill_four; ?>" />
	</p>

	<p>
	<label for="<?php echo $this->get_field_id('skill_four_max'); ?>"><?php _e('Fourth skill value', 'moesia'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('skill_four_max'); ?>" name="<?php echo $this->get_field_name('skill_four_max'); ?>" type="text" value="<?php echo $skill_four_max; ?>" />
	</p>

	<!-- Skill five -->
	<p>
	<label for="<?php echo $this->get_field_id('skill_five'); ?>"><?php _e('Fifth skill name', 'moesia'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('skill_five'); ?>" name="<?php echo $this->get_field_name('skill_five'); ?>" type="text" value="<?php echo $skill_five; ?>" />
	</p>

	<p>
	<label for="<?php echo $this->get_field_id('skill_five_max'); ?>"><?php _e('Fifth skill value', 'moesia'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('skill_five_max'); ?>" name="<?php echo $this->get_field_name('skill_five_max'); ?>" type="text" value="<?php echo $skill_five_max; ?>" />
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
		$instance['skills_desc'] 	= strip_tags($new_instance['skills_desc']);
		$instance['skill_one'] 		= strip_tags($new_instance['skill_one']);
		$instance['skill_one_max'] 	= intval($new_instance['skill_one_max']);
		$instance['skill_two'] 		= strip_tags($new_instance['skill_two']);
		$instance['skill_two_max'] 	= intval($new_instance['skill_two_max']);
		$instance['skill_three'] 	= strip_tags($new_instance['skill_three']);
		$instance['skill_three_max']= intval($new_instance['skill_three_max']);
		$instance['skill_four'] 	= strip_tags($new_instance['skill_four']);
		$instance['skill_four_max'] = intval($new_instance['skill_four_max']);
		$instance['skill_five'] 	= strip_tags($new_instance['skill_five']);
		$instance['skill_five_max'] = intval($new_instance['skill_five_max']);
	    $instance['image_uri'] 		= esc_url_raw( $new_instance['image_uri'] );			
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['moesia_skills']) )
			delete_option('moesia_skills');		  
		  
		return $instance;
	}
	
	function flush_widget_cache() {
		wp_cache_delete('moesia_skills', 'widget');
	}
	
	// display widget
	function widget($args, $instance) {
		$cache = array();
		if ( ! $this->is_preview() ) {
			$cache = wp_cache_get( 'moesia_skills', 'widget' );
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

		$title 			= ( ! empty( $instance['title'] ) ) ? $instance['title'] : __( 'Our skills', 'moesia' );
		$title 			= apply_filters( 'widget_title', $title, $instance, $this->id_base );
		$skills_desc 	= isset( $instance['skills_desc'] ) ? esc_textarea($instance['skills_desc']) : '';
		$skill_one   	= isset( $instance['skill_one'] ) ? esc_html( $instance['skill_one'] ) : '';
		$skill_one_max  = isset( $instance['skill_one_max'] ) ? absint( $instance['skill_one_max'] ) : '';
		$skill_two   	= isset( $instance['skill_two'] ) ? esc_attr( $instance['skill_two'] ) : '';
		$skill_two_max  = isset( $instance['skill_two_max'] ) ? absint( $instance['skill_two_max'] ) : '';
		$skill_three   	= isset( $instance['skill_three'] ) ? esc_attr( $instance['skill_three'] ) : '';
		$skill_three_max= isset( $instance['skill_three_max'] ) ? absint( $instance['skill_three_max'] ) : '';
		$skill_four   	= isset( $instance['skill_four'] ) ? esc_attr( $instance['skill_four'] ) : '';		
		$skill_four_max = isset( $instance['skill_four_max'] ) ? absint( $instance['skill_four_max'] ) : '';
		$skill_five   	= isset( $instance['skill_five'] ) ? esc_attr( $instance['skill_five'] ) : '';
		$skill_five_max = isset( $instance['skill_five_max'] ) ? absint( $instance['skill_five_max'] ) : '';		
		$image_uri 		= isset( $instance['image_uri'] ) ? esc_url($instance['image_uri']) : '';		

?>
		<section id="skills" class="skills-area">
			<div class="container">
				<?php if ( $title ) echo $before_title . '<span class="wow bounce">' . $title . '</span>' . $after_title; ?>
				<?php if ($skills_desc !='') : ?>				
					<div class="skills-desc col-md-6 col-sm-6 wow fadeInLeft">
						<?php echo esc_textarea($skills_desc); ?>
					</div>
				<?php endif; ?>
				<div class="skills-list col-md-6 col-sm-6">
					<?php 
						if ($skill_one !='') {
							echo '<div class="col-md-2 wow fadeIn">' . esc_html($skill_one) . ':</div><div class="skill-bar col-md-10" id="' . absint($skill_one_max) . '"><div></div></div>';
						}
						if ($skill_two !='') {
							echo '<div class="col-md-2 wow fadeIn">' . esc_html($skill_two) . ':</div><div class="skill-bar col-md-10" id="' . absint($skill_two_max) . '"><div></div></div>';
						}
						if ($skill_three !='') {
							echo '<div class="col-md-2 wow fadeIn">' . esc_html($skill_three) . ':</div><div class="skill-bar col-md-10" id="' . absint($skill_three_max) . '"><div></div></div>';
						}
						if ($skill_four !='') {
							echo '<div class="col-md-2 wow fadeIn">' . esc_html($skill_four) . ':</div><div class="skill-bar col-md-10" id="' . absint($skill_four_max) . '"><div></div></div>';
						}
						if ($skill_five !='') {
							echo '<div class="col-md-2 wow fadeIn">' . esc_html($skill_five) . ':</div><div class="skill-bar col-md-10" id="' . absint($skill_five_max) . '"><div></div></div>';
						}
					?>								
				</div>
			</div>
		<?php if ($image_uri != '') : ?>
			<style type="text/css">
				.skills-area {
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
			wp_cache_set( 'moesia_skills', $cache, 'widget' );
		} else {
			ob_end_flush();
		}
	}
	
}