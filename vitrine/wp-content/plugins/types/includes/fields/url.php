<?php
/**
 * Types-field: URL
 *
 * Description: Displays a URL input to the user and forces input check.
 *
 * Rendering: HTML formatted DB data (link).
 * 
 * Parameters:
 * 'raw' => 'true'|'false' (display raw data stored in DB, default false)
 * 'output' => 'html' (wrap data in HTML, optional)
 * 'show_name' => 'true' (show field name before value e.g. My checkbox: $value)
 * 'title' => link title e.g. 'Go here'
 * 'class' => CSS class applied to link e.g. 'my-link'
 * 'no_protocol' => 'true'|'false' (display URL without protocol "http:// and https://", default false)
 *
 * Example usage:
 * With a short code use [types field="my-url"]
 * In a theme use types_render_field("my-url", $parameters)
 * 
 */