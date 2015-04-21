jQuery(document).ready(function(){
    jQuery('.wpcf-collapsible-button').live('click', function() {
        var toggleButton = jQuery(this);
        var toggleDiv = jQuery('#'+jQuery(this).attr('id')+'-toggle');
        toggleDiv.slideToggle(function(){
            if (jQuery(this).is(':visible')) {
                jQuery.get(toggleButton.attr('href')+'&hidden=0');
                toggleButton.removeClass('wpcf-collapsible-button-collapsed');
            } else {
                jQuery.get(toggleButton.attr('href')+'&hidden=1');
                toggleButton.addClass('wpcf-collapsible-button-collapsed');
            }
        });
        return false;
    });
    jQuery('.wpcf-toggle-wrapper').each(function(){
        if (typeof wpcf_collapsed != 'undefined') {
            if (jQuery.inArray(jQuery(this).attr('id'), wpcf_collapsed) == -1) {
                jQuery(this).slideDown();
            } else {
                var toggleButton = jQuery('#'+jQuery(this).attr('id').replace('-toggle', ''));
                toggleButton.addClass('wpcf-collapsible-button-collapsed');
            }
        } else {
            jQuery(this).slideDown();
        }
    });
});