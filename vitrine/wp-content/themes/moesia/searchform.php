<?php
/**
 * Search form
 *
 * @package Moesia
 */
?>

<div class="search-wrapper">
	<form role="search" method="get" class="moesia-search-form" action="<?php echo home_url( '/' ); ?>">
		<span class="search-close"><i class="fa fa-times"></i></span>
		<label>
			<span class="screen-reader-text"><?php echo __( 'Search for:', 'moesia' ) ?></span>
			<input type="search" class="search-field" placeholder="<?php echo esc_attr_x( 'Search &#8230;', 'moesia' ) ?>" value="<?php echo get_search_query() ?>" name="s" title="<?php echo esc_attr_x( 'Search for:', 'moesia' ) ?>" />
		</label>
		<input type="submit" class="search-submit" value="&#xf002;" />
	</form>
</div>