<?php
/*
 * Debug code.
 */

/**
 * Admin footer.
 * 
 * @global type $wpcf
 */
function wpcf_debug( $plain = false ) {
    if ( WPCF_DEBUG ) {
        global $wpcf, $types_instances;
        $clone = clone $wpcf;

        if ( $plain ) {
            ob_start();
        }

        echo '<div style="margin:20px; padding:20px; background-color:#F5F5F5; border: 2px dashed #9E9E9E"><strong>Types DEBUG</strong><br /><br />


';
        echo '<div onclick="jQuery(this).next().slideToggle();" style="cursor:pointer;padding: 10px 0px;"><strong><em>Instances</em></strong></div><div style="display:none;"><br /><br /><pre>';
        print_r( $types_instances );
        echo '</pre></div>';

        if ( !empty( $clone->debug->images ) ) {
            echo '<div onclick="jQuery(this).next().slideToggle();" style="cursor:pointer;padding: 10px 0px;"><strong><em>Images</em></strong></div><div style="display:none;"><br /><br /><pre>';
            print_r( $clone->debug->images );
            unset( $clone->debug->images );
            echo '</pre></div>';
        }

        if ( !empty( $clone->debug ) ) {
            echo '<div onclick="jQuery(this).next().slideToggle();" style="cursor:pointer;padding: 10px 0px;"><strong><em>Debug</em></strong></div><div style="display:none;"><br /><br /><pre>';
            print_r( $clone->debug );
            echo '</pre></div>';
        }

        if ( !empty( $clone->errors ) ) {
            echo '
            
<div onclick="jQuery(this).next().slideToggle();" style="cursor:pointer;padding: 10px 0px; color:Red;"><strong><em>ERRORS</em></strong></div><div style="display:none;"><br /><br /><pre>';
            print_r( $clone->errors );
            echo '</pre></div>';
        }

        echo '<div onclick="jQuery(this).next().slideToggle();" style="cursor:pointer;padding: 10px 0px;"><strong><em>ALL</em></strong></div><div style="display:none;"><br /><br /><pre>';
        print_r( $clone );
        echo '</pre></div>';

        echo '
            
<div onclick="jQuery(this).next().slideToggle();" style="cursor:pointer;padding: 10px 0px;"><strong><em>WP Query</em></strong></div><div style="display:none;"><br /><br /><pre>';
        global $wp_query;
        print_r( $wp_query );
        echo '</pre></div>';

        echo '
            
<div onclick="jQuery(this).next().slideToggle();" style="cursor:pointer;padding: 10px 0px;"><strong><em>WP User</em></strong></div><div style="display:none;"><br /><br /><pre>';
        print_r( wp_get_current_user() );
        echo '</pre></div>';

        echo '</div>';

        if ( $plain ) {
            $out = ob_get_contents();
            ob_end_clean();
            echo '<pre>';
            echo strip_tags( $out );
            echo '</pre>';
        }
    }
}
