<?php
/**
 * Types-field: WYSIWYG
 *
 * Description: Displays a WYSIWYG editor to the user.
 *
 * Rendering: HTML formatted DB data.
 * 
 * Parameters:
 * 'raw' => 'true'|'false' (display raw data stored in DB, default false)
 * 'output' => 'html' (wrap data in HTML, optional)
 * 'show_name' => 'true' (show field name before value e.g. My checkbox: $value)
 *
 * Example usage:
 * With a short code use [types field="my-wysiwyg"]
 * In a theme use types_render_field("my-wysiwyg", $parameters)
 * 
 */