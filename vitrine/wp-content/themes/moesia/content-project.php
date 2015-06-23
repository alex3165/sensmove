<?php
/**
 * @package Moesia
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<header class="entry-header">
		<h4 class="project-name"><?php _e('Project name:', 'moesia'); ?></h4>
		<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
	</header><!-- .entry-header -->

	<div class="entry-content">
		<h4 class="project-desc"><?php _e('Project description:', 'moesia'); ?></h4>
		<?php the_content(); ?>
		<?php
			wp_link_pages( array(
				'before' => '<div class="page-links">' . __( 'Pages:', 'moesia' ),
				'after'  => '</div>',
			) );
		?>
	</div><!-- .entry-content -->

	<footer class="entry-footer">
		<?php edit_post_link( __( 'Edit', 'moesia' ), '<span class="edit-link"><i class="fa fa-edit"></i>&nbsp;', '</span>' ); ?>
	</footer><!-- .entry-footer -->
</article><!-- #post-## -->
