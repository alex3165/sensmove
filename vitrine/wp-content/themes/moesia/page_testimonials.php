<?php

/*

Template Name: Testimonials

*/
	get_header();
?>



	<div id="primary" class="content-area fullwidth">
		<main id="main" class="site-main" role="main">

			<header class="entry-header">
				<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
			</header><!-- .entry-header -->

			<?php 
				$testimonials = new WP_Query( array(
					'no_found_rows'       => true,
					'post_status'         => 'publish',
					'post_type' 		  => 'testimonials',
					'posts_per_page'	  => -1
				) );

				if ($testimonials->have_posts()) :			
			?>
				<div class="testimonials clearfix">
					<?php while ( $testimonials->have_posts() ) : $testimonials->the_post(); ?>
						<?php $function = get_post_meta( get_the_ID(), 'wpcf-client-function', true ); ?>
						<?php $photo = get_post_meta( get_the_ID(), 'wpcf-client-photo', true ); ?>
						<div class="testimonial col-md-6 col-sm-6 fadeInUp">
							<div class="testimonial-body"><?php the_content(); ?></div>
							<?php if ($photo != '') : ?>
								<img class="client-photo col-md-4" src="<?php echo esc_url($photo); ?>" alt="<?php the_title(); ?>">
							<?php endif; ?>
							<h4 class="client-name col-md-8"><?php the_title(); ?></h4>
							<?php if ($function != '') : ?>
								<span class="client-function col-md-8"><?php echo esc_html($function); ?></span>
							<?php endif; ?>						
						</div>
					<?php endwhile; ?>
				</div>	

				<?php moesia_paging_nav(); ?>

			<?php else : ?>

				<?php get_template_part( 'content', 'none' ); ?>

			<?php endif; ?>


		</main><!-- #main -->
	</div><!-- #primary -->

<?php get_footer(); ?>

