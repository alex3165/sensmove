<?php
/*
 * File editor form.
 */

if ( !defined( 'ABSPATH' ) ) {
    die( 'Security check' );
}

if ( !isset( $data ) ) {
    $data = array();
}

$data = array_merge( array(
    'title' => '',
    'link' => false,
    'file' => '',
        ), (array) $data );
?>

<div data-bind="template: {name:'tpl-types-modal-file'}"></div>

<!--TYPES MODAL FILE-->
<script id="tpl-types-modal-file" type="text/html">

<div class="fieldset">
	<p>
		<label for="file-title" class="input-title"><?php _e( 'Link title', 'wpcf' ); ?></label>
		<input id="file-title" type="text" name="title" value="<?php echo $data['title']; ?>" />
	</p>

</div>


</script><!--END TYPES MODAL FILE-->