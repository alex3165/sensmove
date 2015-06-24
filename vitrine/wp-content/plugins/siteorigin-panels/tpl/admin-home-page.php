<?php $settings = siteorigin_panels_setting(); ?>

<div class="wrap" id="panels-home-page" data-post-id="<?php echo get_the_ID() ?>">
	<form action="<?php echo add_query_arg('page', 'so_panels_home_page') ?>" class="hide-if-no-js" method="post" id="panels-home-page-form">
		<div id="icon-index" class="icon32"><br></div>
		<h2>
			<label class="switch">
				<input class="switch-input" type="checkbox" <?php checked( ( get_option('siteorigin_panels_home_page_id') && get_option('siteorigin_panels_home_page_id') == get_option('page_on_front') && get_option('show_on_front') == 'page' ) ) ?> name="siteorigin_panels_home_enabled">
				<span class="switch-label" data-on="<?php _e('On', 'siteorigin-panels') ?>" data-off="<?php _e('Off', 'siteorigin-panels') ?>"></span>
				<span class="switch-handle"></span>
			</label>

			<?php esc_html_e('Custom Home Page', 'siteorigin-panels') ?>

			<?php if( get_option('siteorigin_panels_home_page_id') && ($the_page = get_post( get_option('siteorigin_panels_home_page_id') ) ) ) : ?>
				<div id="panels-view-as-page">
					<a href="<?php echo admin_url('post.php?post='.$the_page->ID.'&action=edit') ?>" class="add-new-h2">Edit As Page</a>
				</div>
			<?php endif; ?>
		</h2>

		<?php if( filter_input( INPUT_POST, '_sopanels_home_nonce' ) ) : global $post; ?>
			<div id="message" class="updated">
				<p><?php printf( __('Home page updated. <a href="%s">View page</a>', 'siteorigin-panels'), get_the_permalink( $post ) ) ?></p>
			</div>
		<?php endif; ?>

		<div class="siteorigin-panels-builder so-panels-loading">

		</div>

		<input name="panels_data" value="" type="hidden" class="siteorigin-panels-data-field" id="panels-data-field-home" />
		<script type="text/javascript">
			document.getElementById('panels-data-field-home').value = decodeURIComponent("<?php echo rawurlencode( json_encode($panels_data) ); ?>");
		</script>

		<p><input type="submit" class="button button-primary" id="panels-save-home-page" value="<?php esc_attr_e('Save Home Page', 'siteorigin-panels') ?>" /></p>

		<?php wp_nonce_field('save', '_sopanels_home_nonce') ?>
	</form>
	<noscript><p><?php _e('This interface requires Javascript', 'siteorigin-panels') ?></p></noscript>
</div> 