<?php
/**
 * Types-field: Video
 *
 * Description: Displays a textfield input to the user with option to select from Media Library.
 *
 * Rendering: Embedded player.
 * 
 * Parameters:
 * 'raw' => 'true'|'false' (display raw data stored in DB, default false)
 * 'output' => 'html' (wrap data in HTML, optional)
 * 'show_name' => 'true' (show field name before value e.g. My video: $value)
 * 'width' => integer
 * 'height' => integer
 *
 * Example usage:
 * With a short code use [types field="my-video"]
 * In a theme use types_render_field("my-video", $parameters)
 * 
 */