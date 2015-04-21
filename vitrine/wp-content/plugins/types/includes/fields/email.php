<?php
/**
 * Types-field: Email
 *
 * Description: Displays a email input to the user.
 *
 * Rendering: Link with mailto as href. Link text can be specified otherwise
 * equals to email address.
 * 
 * Parameters:
 * 'raw' => 'true'|'false' (display raw data stored in DB, default false)
 * 'output' => 'html' (wrap data in HTML, optional)
 * 'show_name' => 'true' (show field name before value e.g. My date: $value)
 * 'title' => link title e.g. 'Mail me!'
 *
 * Example usage:
 * With a short code use [types field="my-email"]
 * In a theme use types_render_field("my-email", $parameters)
 * 
 */