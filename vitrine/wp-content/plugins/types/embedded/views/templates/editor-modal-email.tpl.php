<?php
/*
 * Email editor form.
 */

if ( !defined( 'ABSPATH' ) ) {
    die( 'Security check' );
}

if ( !isset( $data ) ) {
    $data = array();
}

$data = array_merge( array(
    'title' => '',
        ), (array) $data );
?>

<div data-bind="template: {name:'tpl-types-modal-email'}"></div>

<!--TYPES MODAL EMAIL-->
<script id="tpl-types-modal-email" type="text/html">

<div class="fieldset">
	<p>
		<label for="email-title" class="input-title"><?php _e( 'Title', 'wpcf' ); ?></label>
		<input id="email-title" type="text" name="title" value="<?php echo $data['title']; ?>" />
		<span class="help-text"><?php _e( 'If set, this text will be displayed instead of raw data', 'wpcf' ); ?></span>
	</p>
</div>

</script><!--END TYPES MODAL EMAIL-->