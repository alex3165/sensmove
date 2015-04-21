<?php
/**
 * The template for displaying all single posts.
 *
 * @package Moesia
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

		<?php while ( have_posts() ) : the_post(); ?>

			<?php if ( !is_singular( 'projects' ) ): ?>
				<?php get_template_part( 'content', 'single' ); ?>
			<?php else : ?>
				<?php get_template_part( 'content', 'project' ); ?>
			<?php endif; ?>	

			<?php if (get_theme_mod('author_bio') != '') : ?>
				<?php get_template_part( 'author-bio' ); ?>
			<?php endif; ?>			

			<?php moesia_post_nav(); ?>

			<?php
				// If comments are open or we have at least one comment, load up the comment template
				if ( comments_open() || '0' != get_comments_number() ) :
					comments_template();
				endif;
			?>

		<?php endwhile; // end of the loop. ?>

		</main><!-- #main -->
	</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>