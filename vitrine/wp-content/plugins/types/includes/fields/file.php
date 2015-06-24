<?php
/**
 * Types-field: File
 *
 * Description: Displays a file upload or input to the user.
 *
 * Rendering: Raw DB data (file URI) or link to file.
 * 
 * Parameters:
 * 'raw' => 'true'|'false' (display raw data stored in DB, default false)
 * 'output' => 'html' (wrap data in HTML, optional)
 * 'show_name' => 'true' (show field name before value e.g. My date: $value)
 * 'link' => 'true'|'false'
 * 'title' => link title ('link' parameter must be 'true') e.g. 'Download'
 *
 * Example usage:
 * With a short code use [types field="my-file"]
 * In a theme use types_render_field("my-file", $parameters)
 * 
 */