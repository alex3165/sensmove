<?php
/** provide default implementation of [wpml-string] shortcode for when
 * wpml plugin is not active.
 */

if (!isset($wpml_string_sub_active)) {

    add_action('init', 'stub_wpml_add_shortcode', 100);
    
    $wpml_string_sub_active = true;

    if( !function_exists('stub_wpml_add_shortcode') )
    {
        function stub_wpml_add_shortcode() {
            global $WPML_String_Translation;

            if (!isset($WPML_String_Translation)) {
                // WPML string translation is not active
                // Add our own do nothing shortcode

                add_shortcode('wpml-string', 'stub_wpml_string_shortcode');

            }
        }
    }

    if( !function_exists('stub_wpml_string_shortcode') )
    {
        function stub_wpml_string_shortcode($atts, $value) {
            // return un-processed.
            return do_shortcode($value);
        }
    }
}