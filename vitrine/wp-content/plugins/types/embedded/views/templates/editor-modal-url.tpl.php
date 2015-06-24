<?php
/*
 * URL field editor form.
 */

if ( !defined( 'ABSPATH' ) ) {
    die( 'Security check' );
}

if ( !isset( $data ) ) {
    $data = array();
}

$data = array_merge( array(
    'title' => '',
    'target' => '_self',
    'framename' => '',
    'no_protocol' => false,
        ), (array) $data );
?>

<div data-bind="template: {name:'tpl-types-modal-url'}"></div>

<!--TYPES MODAL URL-->
<script id="tpl-types-modal-url" type="text/html">

<div class="fieldset">
	<label class="input-title" for="url-title"><?php _e( 'Title', 'wpcf' ); ?></label>
	<input id="url-title" type="text" name="title" value="<?php echo $data['title']; ?>" />
	<span class="help-text"><?php _e( 'If set, this text will be displayed instead of raw data', 'wpcf' ); ?></span>
</div>

<div class="fieldset">
    <label class="input-title" for="url-protocol"><?php _e( 'No protocol', 'wpcf' ); ?></label>
	<input id="url-protocol" type="checkbox" name="no_protocol" value="1"<?php if ( $data['no_protocol'] ) echo ' checked="checked"'; ?> />
	<span class="help-text"><?php _e( 'If checked, link will be rendered without http or https', 'wpcf' ); ?></span>
</div>

</script><!--END TYPES MODAL URL-->

<!--TYPES MODAL URL TARGET-->
<script id="tpl-types-modal-url-target" type="text/html">

<ul class="form-inline">
	<?php foreach ( $data['target_options'] as $target => $title ): ?>
	<li>
		<input id="url-target-<?php echo $target; ?>" type="radio" name="target" value="<?php echo $target; ?>" data-bind="checked: url_target" />
		<label for="url-target-<?php echo $target; ?>"><?php echo $title; ?></label>
	</li>
	<?php endforeach; ?>

	<div class="group-nested" data-bind="visible: url_target() == 'framename'">
	    <label for="url-target-framename"><?php _e( 'Enter framename', 'wpcf' ); ?></label>
	    <input id="url-target-framename" type="text" name="framename" value="<?php echo $data['framename']; ?>" />
	</div>
</ul>

</script><!--END TYPES MODAL URL TARGET-->