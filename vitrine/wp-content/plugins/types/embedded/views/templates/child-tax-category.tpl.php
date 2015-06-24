<?php
/*
 * Child table taxonomy non-hierarchical form.
 */
$defaults = array('taxonomy' => 'category');
extract( wp_parse_args( $data, $defaults ), EXTR_SKIP );
$tax = get_taxonomy( $taxonomy );
$html_id = "wpcf-reltax-{$taxonomy}-" . wpcf_unique_id( $taxonomy );

	?>
	<div id="<?php echo $html_id; ?>" class="js-types-child-categorydiv types-child-categorydiv" data-types-reltax="<?php echo $taxonomy; ?>">
		<!--<ul id="<?php echo $html_id; ?>-tabs" class="category-tabs">
			<li class="tabs"><a href="#<?php echo $taxonomy; ?>-all"><?php echo $tax->labels->all_items; ?></a></li>
			<li class="hide-if-no-js"><a href="#<?php echo $taxonomy; ?>-pop"><?php _e( 'Most Used' ); ?></a></li>
		</ul>

		<div id="<?php echo $taxonomy; ?>-pop" class="tabs-panel" style="display: none;">
			<ul id="<?php echo $taxonomy; ?>checklist-pop" class="categorychecklist form-no-clear" >
				<?php $popular_ids = wp_popular_terms_checklist($taxonomy); ?>
			</ul>
		</div>-->

		<div id="<?php echo $html_id; ?>-all" class="tabs-panel">
			<?php
//            $name = ( $taxonomy == 'category' ) ? 'post_category' : 'tax_input[' . $taxonomy . ']';
            echo "<input type='hidden' name='{$_wpcf_name}[]' value='0' />"; // Allows for an empty term set to be sent. 0 is an invalid Term ID and will be ignored by empty() checks.
            ?>
			<ul id="<?php echo $html_id; ?>checklist" data-wp-lists="list:<?php echo $taxonomy?>" class="categorychecklist form-no-clear" data-types="<?php echo $_wpcf_name; ?>">
				<?php wp_terms_checklist($post->ID, array( 'taxonomy' => $taxonomy, 'popular_cats' => $popular_ids ) ) ?>
			</ul>
		</div>
	<?php if ( current_user_can($tax->cap->edit_terms) ) : ?>
			<div id="<?php echo $html_id; ?>-adder" class="wp-hidden-children js-types-reltax-adder">
				<h4>
					<a id="<?php echo $html_id; ?>-add-toggle" href="#<?php echo $html_id; ?>-add" class="hide-if-no-js">
						<?php
							/* translators: %s: add new taxonomy label */
							printf( __( '+ %s' ), $tax->labels->add_new_item );
						?>
					</a>
				</h4>
				<p id="<?php echo $html_id; ?>-add" class="category-add wp-hidden-child">
					<label class="screen-reader-text" for="new<?php echo $html_id; ?>"><?php echo $tax->labels->add_new_item; ?></label>
					<input type="text" name="types_reltax[<?php echo $html_id; ?>][<?php echo $taxonomy; ?>]" id="new<?php echo $html_id; ?>" class="form-required form-input-tip" value="<?php echo esc_attr( $tax->labels->new_item_name ); ?>" aria-required="true"/>
					<label class="screen-reader-text" for="new<?php echo $html_id; ?>_parent">
						<?php echo $tax->labels->parent_item_colon; ?>
					</label>
					<?php wp_dropdown_categories( array( 'taxonomy' => $taxonomy, 'hide_empty' => 0, 'name' => 'new'.$taxonomy.'_parent', 'orderby' => 'name', 'hierarchical' => 1, 'show_option_none' => '&mdash; ' . $tax->labels->parent_item . ' &mdash;' ) ); ?>
					<input type="button" id="<?php echo $html_id; ?>-add-submit" data-wp-lists="add:<?php echo $html_id ?>checklist:<?php echo $html_id ?>-add" class="button category-add-submit" value="<?php echo esc_attr( $tax->labels->add_new_item ); ?>" />
                    <input type="hidden" name="types_reltax_nonce[<?php echo $html_id; ?>][<?php echo $taxonomy; ?>]" value="<?php echo wp_create_nonce("add-{$taxonomy}"); ?>" />
					<?php //wp_nonce_field( 'add-'.$taxonomy, '_ajax_nonce-add-'.$taxonomy, false ); ?>
					<span id="<?php echo $html_id; ?>-ajax-response"></span>
				</p>
			</div>
		<?php endif; ?>
	</div>
