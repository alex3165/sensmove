<?php

/*

Template Name: Employees

*/
	get_header();
?>



	<div id="primary" class="content-area fullwidth">
		<main id="main" class="site-main" role="main">

			<header class="entry-header">
				<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
			</header><!-- .entry-header -->

			<?php 
				$employees = new WP_Query( array(
					'no_found_rows'       => true,
					'post_status'         => 'publish',
					'post_type' 		  => 'employees',
					'posts_per_page'	  => -1
				) );

				if ($employees->have_posts()) :			
			?>
				<?php while ( $employees->have_posts() ) : $employees->the_post(); ?>
					<?php //Get the custom field values
						$photo = get_post_meta( get_the_ID(), 'wpcf-photo', true );
						$position = get_post_meta( get_the_ID(), 'wpcf-position', true );
						$facebook = get_post_meta( get_the_ID(), 'wpcf-facebook', true );
						$twitter = get_post_meta( get_the_ID(), 'wpcf-twitter', true );
						$google = get_post_meta( get_the_ID(), 'wpcf-google-plus', true );
						$linkedin = get_post_meta( get_the_ID(), 'wpcf-linkedin', true ); 
					?>
					<div class="employee col-md-4 col-sm-6 col-xs-6">
						<?php if ($photo != '') : ?>
							<img class="employee-photo wow zoomInDown" src="<?php echo esc_url($photo); ?>" alt="<?php the_title(); ?>">
						<?php endif; ?>
						<h4 class="employee-name wow fadeInUp"><?php the_title(); ?></h4>
						<?php if ($position != '') : ?>
							<span class="employee-position wow fadeInUp"><?php echo esc_html($position); ?></span>
						<?php endif; ?>
						<div class="employee-desc wow fadeInUp"><?php the_content(); ?></div>
						<?php if ( ($facebook != '') || ($twitter != '') || ($google != '') || ($linkedin != '') ) : ?>
							<div class="employee-social wow fadeInUp">
								<?php if ($facebook != '') : ?>
									<a href="<?php echo esc_url($facebook); ?>" target="_blank"><i class="fa fa-facebook"></i></a>
								<?php endif; ?>
								<?php if ($twitter != '') : ?>
									<a href="<?php echo esc_url($twitter); ?>" target="_blank"><i class="fa fa-twitter"></i></a>
								<?php endif; ?>
								<?php if ($google != '') : ?>
									<a href="<?php echo esc_url($google); ?>" target="_blank"><i class="fa fa-google-plus"></i></a>
								<?php endif; ?>											
								<?php if ($linkedin != '') : ?>
									<a href="<?php echo esc_url($linkedin); ?>" target="_blank"><i class="fa fa-linkedin"></i></a>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div>
				<?php endwhile; ?>

			<?php moesia_paging_nav(); ?>

			<?php else : ?>

				<?php get_template_part( 'content', 'none' ); ?>

			<?php endif; ?>


		</main><!-- #main -->
	</div><!-- #primary -->

<?php get_footer(); ?>

