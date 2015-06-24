function wpv_wp_pointer_ignore( option, nonce, value, id ) {
    
    jQuery.ajaxSetup({async:false});
    jQuery.post(ajaxurl, { 
            action: 'wpv_wp_pointer_set_ignore', 
            option: option,
            wpv_nonce: nonce,
            value: value
        }, function(data) { 
        }
    );
    
    if (value == 'ignore') {
        jQuery('#wpv_wp_pointer_clear_ignores_' + id).fadeIn();
    }
}

