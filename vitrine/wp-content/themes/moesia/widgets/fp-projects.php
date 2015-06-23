<?php

class Moesia_Projects extends WP_Widget {

// constructor
    function moesia_projects() {
		$widget_ops = array('classname' => 'moesia_projects_widget', 'description' => __( 'Show your most intresting projects.', 'moesia') );
        parent::WP_Widget(false, $name = __('Moesia FP: Projects', 'moesia'), $widget_ops);
		$this->alt_option_name = 'moesia_projects_widget';
		
		add_action( 'save_post', array($this, 'flush_widget_cache') );
		add_action( 'deleted_post', array($this, 'flush_widget_cache') );
		add_action( 'switch_theme', array($this, 'flush_widget_cache') );		
    }
	
	// widget form creation
	function form($instance) {

	// Check values
		$title     		= isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$category   	= isset( $instance['category '] ) ? esc_attr( $instance['category '] ) : '';
		$number    		= isset( $instance['number'] ) ? intval( $instance['number'] ) : -1;
		$see_all   		= isset( $instance['see_all'] ) ? esc_url_raw( $instance['see_all'] ) : '';
		$see_all_text  	= isset( $instance['see_all_text'] ) ? esc_html( $instance['see_all_text'] ) : '';		
		$image_uri 		= isset( $instance['image_uri'] ) ? esc_url_raw( $instance['image_uri'] ) : '';		
	?>

	<p><?php _e('In order to display this widget, you must first add some projects from the dashboard. Add as many as you want and the theme will automatically display them all.', 'moesia'); ?></p>
	<p>
	<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'moesia'); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
	</p>
	<p><label for="<?php echo $this->get_field_id( 'category' ); ?>"><?php _e( 'Enter the slug for your category or leave empty to show all projects.', 'moesia' ); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id( 'category' ); ?>" name="<?php echo $this->get_field_name( 'category' ); ?>" type="text" value="<?php echo $category; ?>" size="3" /></p>

    <?php
        if ( $image_uri != '' ) :
           echo '<p><img class="custom_media_image" src="' . $image_uri . '" style="max-width:100px;" /></p>';
        endif;
    ?>
    <p><label for="<?php echo $this->get_field_id('image_uri'); ?>"><?php _e('[DEPRECATED - Go to Edit Row > Theme > Background image] Upload an image for the background if you want. It will get a parallax effect.', 'moesia'); ?></label></p> 
    <p><input type="button" class="button button-primary custom_media_button" id="custom_media_button" name="<?php echo $this->get_field_name('image_uri'); ?>" value="Upload Image" style="margin-top:5px;" /></p>
	<p><input class="widefat custom_media_url" id="<?php echo $this->get_field_id( 'image_uri' ); ?>" name="<?php echo $this->get_field_name( 'image_uri' ); ?>" type="text" value="<?php echo $image_uri; ?>" size="3" /></p>	
	<p><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of projects to show (-1 shows all of them):', 'moesia' ); ?></label>
	<input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" size="3" /></p>
    <p><label for="<?php echo $this->get_field_id('see_all'); ?>"><?php _e('Enter the URL for your projects page [optional]', 'moesia'); ?></label>
	<input class="widefat custom_media_url" id="<?php echo $this->get_field_id( 'see_all' ); ?>" name="<?php echo $this->get_field_name( 'see_all' ); ?>" type="text" value="<?php echo $see_all; ?>" size="3" /></p>	
    <p><label for="<?php echo $this->get_field_id('see_all_text'); ?>"><?php _e('The text for the button [Defaults to <em>See all our projects</em> if left empty]', 'moesia'); ?></label>
	<input class="widefat custom_media_url" id="<?php echo $this->get_field_id( 'see_all_text' ); ?>" name="<?php echo $this->get_field_name( 'see_all_text' ); ?>" type="text" value="<?php echo $see_all_text; ?>" size="3" /></p>	
	
	<?php
	}

	// update widget
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] 			= strip_tags($new_instance['title']);
	    $instance['image_uri'] 		= esc_url_raw( $new_instance['image_uri'] );
		$instance['number'] 		= strip_tags($new_instance['number']);
		$instance['see_all'] 		= esc_url_raw( $new_instance['see_all'] );	
		$instance['see_all_text'] 	= strip_tags($new_instance['see_all_text']);	    
	    $instance['category'] 		= strip_tags($new_instance['category']);			
		$this->flush_widget_cache();

		$alloptions = wp_cache_get( 'alloptions', 'options' );
		if ( isset($alloptions['moesia_projects']) )
			delete_option('moesia_projects');		  
		  
		return $instance;
	}
	
	function flush_widget_cache() {
		wp_cache_delete('moesia_projects', 'widget');
	}
	
	// display widget
	function widget($args, $instance) {
		$cache = array();
		if ( ! $this->is_preview() ) {
			$cache = wp_cache_get( 'moesia_projects', 'widget' );
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

		$title = ( ! empty( $instance['title'] ) ) ? $instance['title'] : __( 'Our projects', 'moesia' );

		/** This filter is documented in wp-includes/default-widgets.php */
		$title 			= apply_filters( 'widget_title', $title, $instance, $this->id_base );
		$image_uri 		= isset( $instance['image_uri'] ) ? esc_url($instance['image_uri']) : '';		
		$category 		= isset( $instance['category'] ) ? esc_attr($instance['category']) : '';
		$see_all 		= isset( $instance['see_all'] ) ? esc_url($instance['see_all']) : '';
		$see_all_text 	= isset( $instance['see_all_text'] ) ? esc_html($instance['see_all_text']) : '';
		$number 		= ( ! empty( $instance['number'] ) ) ? intval( $instance['number'] ) : -1;
		if ( ! $number ) {
			$number = -1;
		}

		$r = new WP_Query( apply_filters( 'widget_posts_args', array(
			'no_found_rows'       => true,
			'post_status'         => 'publish',
			'post_type' 		  => 'projects',
			'posts_per_page'	  => -1,
			'category_name'		  => $category,
			'posts_per_page'	  => $number
		) ) );

		if ($r->have_posts()) :
?>
		<section id="projects" class="projects-area">
			<div class="container">
				<?php if ( $title ) echo $before_title . '<span class="wow bounce">' . $title . '</span>' . $after_title; ?>
				
				<?php $c = 1; ?>				
				<?php while ( $r->have_posts() ) : $r->the_post(); ?>
					<?php $project_url = get_post_meta( get_the_ID(), 'wpcf-project-link', true ); //Get the custom URL for this project ?>
					<?php $video_url = get_post_meta( get_the_ID(), 'wpcf-video-url', true ); //Get the video URL ?>
					<div class="project col-md-4 col-sm-6 col-xs-6 wow zoomIn">
						<div class="project-image">
							<?php the_post_thumbnail('project-image'); ?>
							<?php if ( $project_url ) : ?>
								<a class="link-icon" href="<?php echo esc_url($project_url); ?>" title="<?php the_title(); ?>"><i class="fa fa-link"></i></a>
							<?php else : ?>
								<a class="link-icon" href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><i class="fa fa-link"></i></a>
							<?php endif; ?>
							<?php 
								global $post;
								$url = wp_get_attachment_url( get_post_thumbnail_id($post->ID) ); 
							?>
							<?php if (!$video_url) : ?>
							<a class="pp-icon" href="<?php echo esc_url($url); ?>" rel="prettyPhoto[pp_gallery]"><i class="fa fa-search"></i></a>
							<?php else : ?>
							<a class="pp-icon" href="<?php echo esc_url($video_url); ?>" rel="prettyPhoto[pp_gallery]"><i class="fa fa-search"></i></a>	
							<?php endif; ?>													
						</div>
					</div>
				<?php endwhile; ?>
			</div>
			<?php if ($see_all != '') : ?>
				<a href="<?php echo esc_url($see_all); ?>" class="all-news">
					<?php if ($see_all_text) : ?>
						<?php echo $see_all_text; ?>
					<?php else : ?>
						<?php echo __('See all our projects', 'moesia'); ?>
					<?php endif; ?>
				</a>
			<?php endif; ?>			
		<?php if ($image_uri != '') : ?>
			<style type="text/css">
				.projects-area {
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
		// Reset the global $the_post as this query will have stomped on it
		wp_reset_postdata();

		endif;

		if ( ! $this->is_preview() ) {
			$cache[ $args['widget_id'] ] = ob_get_flush();
			wp_cache_set( 'moesia_projects', $cache, 'widget' );
		} else {
			ob_end_flush();
		}
	}
	
}