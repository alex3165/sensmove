<?php
/**
 * Author bio
 *
 */
?>

<div class="author-bio clearfix">

	<div class="col-md-3 col-sm-3 col-xs-12">
		<?php echo get_avatar( get_the_author_meta( 'user_email' ), 80 ); ?>
		<div class="author-social">
			<?php
				$author_name = get_the_author_meta('display_name');	
				$author_facebook = get_the_author_meta('moesia_facebook');
				$author_twitter = get_the_author_meta('moesia_twitter');
				$author_googleplus = get_the_author_meta('moesia_googleplus');
				$author_linkedin = get_the_author_meta('moesia_linkedin');
			?>		
			<?php if ( $author_facebook != '' ) : ?>
				<a href="<?php echo esc_url($author_facebook); ?>" title="Facebook"><i class="fa fa-facebook-square"></i></a>
			<?php endif; ?>
			<?php if ( $author_twitter != '' ) : ?>
				<a href="<?php echo esc_url($author_twitter); ?>" title="Twitter"><i class="fa fa-twitter-square"></i></a>
			<?php endif; ?>
			<?php if ( $author_googleplus != '' ) : ?>
				<a href="<?php echo esc_url($author_googleplus); ?>" title="Google Plus"><i class="fa fa-google-plus-square"></i></a>
			<?php endif; ?>
			<?php if ( $author_linkedin != '' ) : ?>
				<a href="<?php echo esc_url($author_linkedin); ?>" title="Linkedin"><i class="fa fa-linkedin-square"></i></a>
			<?php endif; ?>
		</div>		
	</div>
	
	<div class="col-md-9 col-sm-9 col-xs-12">
		<h3 class="author-name">
			<?php printf(__('About %s', 'moesia'), esc_html($author_name) ); ?>				
		</h3>
	
		<div class="author-desc">
			<?php esc_html(the_author_meta( 'description' )); ?>
		</div>
		
		<div class="view-all"><a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>"><?php printf(__('See all posts by %s', 'moesia'), esc_html($author_name) ); ?></a>&nbsp;<i class="fa fa-long-arrow-right"></i></div>
	</div>
</div> 


