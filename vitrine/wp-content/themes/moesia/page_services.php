<?php

/*

Template Name: Services

*/
	get_header();
?>



	<div id="primary" class="content-area fullwidth">
		<main id="main" class="site-main" role="main">

			<header class="entry-header">
				<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
			</header><!-- .entry-header -->

			<?php 
				$services = new WP_Query( array(
					'no_found_rows'       => true,
					'post_status'         => 'publish',
					'post_type' 		  => 'services',
					'posts_per_page'	  => -1
				) );

				if ($services->have_posts()) :			
			?>
			<?php while ( $services->have_posts() ) : $services->the_post(); ?>
				<?php $icon = get_post_meta( get_the_ID(), 'wpcf-service-icon', true ); ?>
				<?php $link = get_post_meta( get_the_ID(), 'wpcf-service-link', true ); ?>
				<div class="service col-md-4 col-sm-6 col-xs-6">
					<?php if ($icon) : ?>
						<span class="service-icon wow zoomInDown"><?php echo '<i class="fa ' . esc_html($icon) . '"></i>'; ?></span>
					<?php endif; ?>
					<h4 class="service-title wow fadeInUp">
						<?php if ($link) : ?>
							<a href="<?php echo esc_url($link); ?>"><?php the_title(); ?></a>
						<?php else : ?>
							<?php the_title(); ?>
						<?php endif; ?>
					</h4>
					<div class="service-desc wow fadeInUp"><?php the_content(); ?></div>
				</div>
			<?php endwhile; ?>

			<?php moesia_paging_nav(); ?>

			<?php else : ?>

				<?php get_template_part( 'content', 'none' ); ?>

			<?php endif; ?>


		</main><!-- #main -->
	</div><!-- #primary -->

<?php get_footer(); ?>

