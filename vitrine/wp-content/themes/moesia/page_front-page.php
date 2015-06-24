<?php
/*
Template Name: Front Page
*/

get_header(); ?>

	<div id="primary" class="fp-content-area">
		<main id="main" class="site-main" role="main">

			<div class="entry-content">
				<?php the_content(); ?>
				<?php
					wp_link_pages( array(
						'before' => '<div class="page-links">' . __( 'Pages:', 'moesia' ),
						'after'  => '</div>',
					) );
				?>
			</div><!-- .entry-content -->

		</main><!-- #main -->
	</div><!-- #primary -->

<?php get_footer(); ?>
