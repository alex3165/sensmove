<?php
/*
 * Checkbox editor form.
 */

if ( !defined( 'ABSPATH' ) ) {
    die( 'Security check' );
}

if ( !isset( $data ) ) {
    $data = array();
}

$data = array_merge( array(
    'selected' => '',
    'not_selected' => '',
        ), (array) $data );
?>

<div data-bind="template: {name:'tpl-types-modal-checkbox'}"></div>

<!--TYPES MODAL CHECKBOX-->
<script id="tpl-types-modal-checkbox" type="text/html">
<div class="fieldset">
	<ul class="form-inline">
		<li>
			<input id="cb-display-db" type="radio" name="display" value="db" data-bind="checked: cb_mode" />
			<label for="cb-display-db"><?php _e( 'Display the value of this field from the database', 'wpcf' ); ?></label>
		</li>
		<li>
			<input id="cb-display-val" type="radio" name="display" value="value" data-bind="checked: cb_mode" />
			<label for="cb-display-val"><?php _e( 'Enter values for \'selected\' and \'not selected\' states', 'wpcf' ); ?></label>

			<div id="cb-states" class="group-nested" data-bind="visible: cb_mode() == 'value'">
				<p>
					<label for="cb-sel" class="input-title"><?php _e( 'Selected:', 'wpcf' ); ?></label>
					<input id="cb-sel" type="text" name="selected" value="<?php echo $data['selected']; ?>"placeholder="<?php _e('Enter selected value', 'wpcf'); ?>" />
				</p>
				<p>
					<label for="cb-not-sel" class="input-title"><?php _e( 'Not selected:', 'wpcf' ); ?></label>
                    <input id="cb-not-sel" type="text" name="not_selected" value="<?php echo $data['not_selected']; ?>" placeholder="<?php _e('Enter not selected value', 'wpcf'); ?>" />
				</p>
			</div>

		</li>
	</ul>


</div>


</script><!--END TYPES MODAL CHECKBOX-->
