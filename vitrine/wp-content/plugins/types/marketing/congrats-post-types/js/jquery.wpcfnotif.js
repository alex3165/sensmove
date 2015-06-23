jQuery(function(){
	if( jQuery('.wpcf-notif .wpcf-notif-dropdown').length > 0 ) {

		jQuery('.wpcf-notif a.wpcf-button.show').click(function(){
			if ( jQuery('.wpcf-notif .wpcf-notif-dropdown').is(':hidden') ) {
				jQuery(this).slideUp(200);
				jQuery('.wpcf-notif .wpcf-notif-dropdown').slideDown(200);
			}
		});

		jQuery('.wpcf-notif a.wpcf-button.hide').click(function(){
			if ( jQuery(".wpcf-notif .wpcf-notif-dropdown").is(':visible') ) {
				jQuery('.wpcf-notif a.wpcf-button.show').slideDown(200);
				jQuery('.wpcf-notif .wpcf-notif-dropdown').slideUp(200);
                jQuery('.wpcf-notif a.wpcf-button.show').show();
			}
		});
	}
});