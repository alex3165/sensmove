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
    'poster' => '',
    'autoplay' => false,
    'loop' => false,
    'preload' => false,
        ), (array) $data );
?>

<div data-bind="template: {name:'tpl-types-modal-video'}"></div>

<!--TYPES MODAL VIDEO-->
<script id="tpl-types-modal-video" type="text/html">
<div class="fieldset">
    <p>
        <label for="video-width" class="input-title"><?php _e( 'Width', 'wpcf' ); ?></label>
        <input id="video-width" type="text" name="width" value="<?php echo $data['width']; ?>" />
    </p>
    <p>
        <label for="video-height" class="input-title"><?php _e( 'Height', 'wpcf' ); ?></label>
        <input id="video-height" type="text" name="height" value="<?php echo $data['height']; ?>" />
    </p>
    <p>
        <label for="video-poster" class="input-title"><?php _e( 'Poster', 'wpcf' ); ?></label>
        <input id="video-poster" type="text" name="poster" value="<?php echo $data['poster']; ?>" />
        <span class="help-text"><?php _e('URL for the poster image. Defines image to show as placeholder before the media plays.', 'wpcf'); ?></span>
    </p>
</div>
<div class="fieldset form-inline">
    <p>
        <input id="video-loop" type="checkbox" name="loop" value="on" data-bind="checked: ted.params.loop"/>
        <label for="video-loop" class="input-title"><?php _e( 'Loop', 'wpcf' ); ?></label>
        <span class="help-text"><?php _e('Allows for the looping of media.', 'wpcf'); ?></span>
    </p>
    <p>
        <input id="video-autoplay" type="checkbox" name="autoplay" value="on" data-bind="checked: ted.params.autoplay" />
        <label for="video-autoplay" class="input-title"><?php _e( 'Autoplay', 'wpcf' ); ?></label>
        <span class="help-text"><?php _e('Causes the media to automatically play as soon as the media file is ready.', 'wpcf'); ?></span>
    </p>
    <p>
        <input id="video-preload" type="checkbox" name="preload" value="on" data-bind="checked: ted.params.preload" />
        <label for="video-preload" class="input-title"><?php _e( 'Preload', 'wpcf' ); ?></label>
    </p>
</div>
</script><!--END TYPES MODAL VIDEO-->