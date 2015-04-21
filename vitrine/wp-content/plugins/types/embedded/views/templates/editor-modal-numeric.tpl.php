<?php
/*
 * Numeric editor form.
 */

if ( !defined( 'ABSPATH' ) ) {
    die( 'Security check' );
}

if ( !isset( $data ) ) {
    $data = array();
}

$data = array_merge( array(
    'format' => 'FIELD_NAME: FIELD_VALUE',
        ), (array) $data );
?>

<div data-bind="template: {name:'tpl-types-modal-numeric'}"></div>

<!--TYPES MODAL NUMERIC-->
<script id="tpl-types-modal-numeric" type="text/html">

<div class="fieldset">
	<p>
		<label for="numeric-format" class="input-title"><?php _e( 'Output format', 'wpcf' ); ?></label>
		<input id="numeric-format" type="text" name="format" value="<?php echo $data['format']; ?>" />
	</p>
	<p>
		<?php _e( "Use FIELD_NAME for title and FIELD_VALUE for value", 'wpcf' ); ?>
        <br />
        <?php _e( "e.g. Value of FIELD_NAME is FIELD_VALUE", 'wpcf' ); ?>
	</p>
</div>

</script><!--END TYPES MODAL NUMERIC-->