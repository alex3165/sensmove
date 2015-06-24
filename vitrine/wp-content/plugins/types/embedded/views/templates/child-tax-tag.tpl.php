<?php
/*
 * Child table taxonomy non-hierarchical form.
 */
$defaults = array('taxonomy' => 'post_tag');
extract( wp_parse_args($data, $defaults), EXTR_SKIP );
$tax_name = esc_attr($taxonomy);
$taxonomy = get_taxonomy($taxonomy);
$user_can_assign_terms = current_user_can( $taxonomy->cap->assign_terms );
$comma = _x( ',', 'tag delimiter' );
$html_id = wpcf_unique_id( $tax_name );

if ( !isset( $box ) || !array_key_exists( 'title', $box ) ) {
    $box['title'] = '';
}

?>
<div class="js-types-child-tagsdiv" id="<?php echo $html_id; ?>">
	<div class="jaxtag">
	<div class="nojs-tags hide-if-js">
	<p><?php echo $taxonomy->labels->add_or_remove_items; ?></p>
	<textarea name="<?php echo $_wpcf_name; ?>" rows="3" cols="20" class="the-tags" id="tax-input-<?php echo $html_id; ?>" <?php disabled( ! $user_can_assign_terms ); ?>><?php echo str_replace( ',', $comma . ' ', get_terms_to_edit( $post->ID, $tax_name ) ); // textarea_escaped by esc_attr() ?></textarea></div>
 	<?php if ( $user_can_assign_terms ) : ?>
    <div class="ajaxtag hide-if-no-js">
		<label class="screen-reader-text" for="new-tag-<?php echo $tax_name; ?>"><?php echo $box['title']; ?></label>
		<div class="taghint"><?php echo $taxonomy->labels->add_new_item; ?></div>
		<p><input type="text" id="new-tag-<?php echo $html_id; ?>" name="newtag<?php echo $html_id; ?>[<?php echo $tax_name; ?>]" class="js-types-newtag form-input-tip" size="16" autocomplete="off" value="" data-types-tax="<?php echo $tax_name; ?>" />
		<input type="button" class="button js-types-addtag" value="<?php esc_attr_e('Add'); ?>" /></p>
	</div>
	<p class="howto"><?php echo $taxonomy->labels->separate_items_with_commas; ?></p>
	<?php endif; ?>
	</div>
	<div class="tagchecklist"></div>
</div>
<?php if ( $user_can_assign_terms ) : ?>
<p class="hide-if-no-js"><a href="#titlediv" class="js-types-child-tagcloud-link" id="link-<?php echo $html_id; ?>" data-types-tax="<?php echo $tax_name; ?>"><?php echo $taxonomy->labels->choose_from_most_used; ?></a></p>
<?php endif; ?>
