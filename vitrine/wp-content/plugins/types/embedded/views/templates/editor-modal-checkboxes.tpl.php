<?php
/*
 * Checkbox editor form.
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'Security check' );
}

if ( ! isset( $data ) ) {
	$data = array( );
}

$data = array_merge( array(
	'checkboxes' => array( ),
		), (array) $data );
?>

<div data-bind="template: {name:'tpl-types-modal-checkboxes'}"></div>

<!--TYPES MODAL CHECKBOXES-->
<script id="tpl-types-modal-checkboxes" type="text/html">
	<div class="fieldset">
		<ul class="form-inline">
			<li>
				<input id="cbs-display-db" type="radio" name="display" value="db" data-bind="checked: cbs_mode"/>
				<label for="cbs-display-db"><?php _e( 'Display the value of this field from the database', 'wpcf' ); ?></label>
			</li>
			<li>
				<input id="cbs-display-sep" type="radio" name="display" value="display_all" data-bind="checked: cbs_mode" />
				<label for="cbs-display-sep"><?php _e( 'Display all values with separator', 'wpcf' ); ?></label>
				<input type="text" name="cbs_separator" data-bind="value: ted.params.separator || ', '" />
			</li>
			<li>
				<input id="cbs-display-val" type="radio" name="display" value="value" data-bind="checked: cbs_mode" />
				<label for="cbs-display-val"><?php _e( 'Enter values for \'selected\' and \'not selected\' states', 'wpcf' ); ?></label>

				<div class="group-nested" data-bind="visible: cbs_mode() == 'value'">

					<?php foreach ( $data[ 'checkboxes' ] as $key => $cb ): ?>
						<div id="cbs-states-<?php echo $key ?>">
							<h3><?php echo $cb[ 'title' ]; ?></h3>
							<p>
								<label for="cbs-sel-<?php echo $key ?>" class="input-title"><?php _e( 'Selected:', 'wpcf' ); ?></label>
								<input id="cbs-sel-<?php echo $key ?>" type="text" name="options[<?php echo $cb[ 'id' ]; ?>][selected]" value="<?php echo $cb[ 'selected' ]; ?>" placeholder="<?php _e('Enter selected value', 'wpcf'); ?>" />
							</p>
							<p>
								<label for="cbs-not-sel-<?php echo $key ?>" class="input-title"><?php _e( 'Not selected:', 'wpcf' ); ?></label>
								<input id="cbs-not-sel-<?php echo $key ?>" type="text" name="options[<?php echo $cb[ 'id' ]; ?>][not_selected]" value="<?php echo $cb[ 'not_selected' ]; ?>" placeholder="<?php _e('Enter not selected value', 'wpcf'); ?>" />
							</p>
						</div>
					<?php endforeach; ?>

				</div>

			</li>
		</ul>



	</div>

</script><!--END TYPES MODAL CHECKBOXES-->
