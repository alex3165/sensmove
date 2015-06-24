<?php
/**
 * Types-field: Image
 *
 * Description: Displays a file (image) upload or input to the user.
 *
 * Rendering: Raw DB data (image URI) or HTML formatted image.
 * 
 * Parameters:
 * 'raw' => 'true'|'false' (display raw data stored in DB, default false)
 * 'output' => 'html' (wrap data in HTML, optional)
 * 'show_name' => 'true' (show field name before value e.g. My date: $value)
 * 'alt' => alternative text e.g. 'My image'
 * 'title' => hover text e.g. 'My image'
 * 'size' => 'thumbnail'|'medium'|'large'|'full' (WP predefined sizes)
 * 'width' => image width e.g. 300 (overriden if 'size' is specified)
 * 'height' => image height e.g. 100 (overriden if 'size' is specified)
 * 'proportional' => 'true'|'false' (overriden if 'size' is specified)
 * 'url' => 'true'|'false' - When true it will output the url of the image instead of the img tag. Works with the size parameter to output the url of the re-sized image
 *
 * Example usage:
 * With a short code use [types field="my-image"]
 * Output url of thumbnail image [types field="my-image" size="thumbnail" url="true"]
 * In a theme use types_render_field("my-image", $parameters)
 * 
 */

/**
 * Form data for group form.
 * 
 * @return type 
 */
function wpcf_fields_image_insert_form() {
    $filename = WPCF_INC_ABSPATH . '/fields/file.php';
    require_once $filename;

    if ( function_exists( 'wpcf_fields_file_insert_form' ) ) {
        return wpcf_fields_file_insert_form();
    }
}