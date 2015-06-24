<?php
/**
 * Types-field: Numeric
 *
 * Description: Displays a text input to user but forces numeric value to be
 * entered.
 *
 * Rendering: Raw DB data or HTML formatted output. Also predefined values can
 * be used to set rendering - FIELD_NAME and FIELD_VALUE. This works similar to
 * sprintf() PHP function.
 * 
 * Parameters:
 * 'raw' => 'true'|'false' (display raw data stored in DB, default false)
 * 'output' => 'html' (wrap data in HTML, optional)
 * 'show_name' => 'true' (show field name before value e.g. My date: $value)
 * 'format' => e.g. 'Value of FIELD_NAME is FIELD_VALUE'
 *      FIELD_NAME will be replaced with field name
 *      FIELD_VALUE will be replaced with field value
 *
 * Example usage:
 * With a short code use [types field="my-numeric"]
 * In a theme use types_render_field("my-numeric", $parameters)
 * 
 */