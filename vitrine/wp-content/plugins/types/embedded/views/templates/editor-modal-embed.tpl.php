<?php
/*
 * Image editor form.
 */

if ( !defined( 'ABSPATH' ) ) {
    die( 'Security check' );
}

if ( !isset( $data ) ) {
    $data = array();
}

//list($default_width, $default_height) = wpcf_media_size();
$data = array_merge( array(
    'width' => '',//$default_width,
    'height' => '',//$default_height,
        ), (array) $data );
?>

<div data-bind="template: {name:'tpl-types-modal-embed'}"></div>

<!--TYPES MODAL EMBED-->
<script id="tpl-types-modal-embed" type="text/html">
<div class="fieldset">
    <p>
        <label for="embed-width" class="input-title"><?php _e( 'Width', 'wpcf' ); ?></label>
        <input id="embed-width" type="text" name="width" value="<?php echo $data['width']; ?>" />
    </p>
    <p>
        <label for="embed-height" class="input-title"><?php _e( 'Height', 'wpcf' ); ?></label>
        <input id="embed-height" type="text" name="height" value="<?php echo $data['height']; ?>" />
    </p>
</div>
</script><!--END TYPES MODAL EMBED-->