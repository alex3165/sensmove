<?php
/**
 * Types-field: Embedded Media
 *
 * Description: Displays a textfield input to the user.
 *
 * Rendering: Embedded player.
 * 
 * Parameters:
 * 'raw' => 'true'|'false' (display raw data stored in DB, default false)
 * 'output' => 'html' (wrap data in HTML, optional)
 * 'show_name' => 'true' (show field name before value e.g. My embed: $value)
 * 'width' => integer
 * 'height' => integer
 *
 * Example usage:
 * With a short code use [types field="my-youtube"]
 * In a theme use types_render_field("my-youtube", $parameters)
 * 
 */
