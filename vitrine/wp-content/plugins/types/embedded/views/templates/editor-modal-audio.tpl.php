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
$data = array_merge( array(
    'autoplay' => false,
    'loop' => false,
    'preload' => false,
        ), (array) $data );
?>

<div data-bind="template: {name:'tpl-types-modal-audio'}"></div>

<!--TYPES MODAL AUDIO-->
<script id="tpl-types-modal-audio" type="text/html">
<div class="fieldset form-inline">
    <p>
        <input id="audio-loop" type="checkbox" name="loop" value="on" data-bind="checked: ted.params.loop"/>
        <label for="audio-loop" class="input-title"><?php _e( 'Loop', 'wpcf' ); ?></label>
        <span class="help-text"><?php _e('Allows for the looping of media.', 'wpcf'); ?></span>
    </p>
    <p>
        <input id="audio-autoplay" type="checkbox" name="autoplay" value="on" data-bind="checked: ted.params.autoplay" />
        <label for="audio-autoplay" class="input-title"><?php _e( 'Autoplay', 'wpcf' ); ?></label>
        <span class="help-text"><?php _e('Causes the media to automatically play as soon as the media file is ready.', 'wpcf'); ?></span>
    </p>
    <p>
        <input id="audio-preload" type="checkbox" name="preload" value="on" data-bind="checked: ted.params.preload" />
        <label for="audio-preload" class="input-title"><?php _e( 'Preload', 'wpcf' ); ?></label>
    </p>
</div>
</script><!--END TYPES MODAL AUDIO-->