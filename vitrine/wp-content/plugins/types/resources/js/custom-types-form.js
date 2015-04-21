/**
 *
 * Custom Types form JS
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/resources/js/custom-types-form.js $
 * $LastChangedDate: 2014-11-18 06:47:25 +0000 (Tue, 18 Nov 2014) $
 * $LastChangedRevision: 1027712 $
 * $LastChangedBy: iworks $
 *
 */

jQuery(document).ready(function(){
    if ( jQuery('#wpcf-types-icon') ) {
        classes = 'wpcf-types-menu-image dashicons-before';
        icon = jQuery('#wpcf-types-icon');
        icon.before('<div class="'+classes+'"><br></div>');
        jQuery('div.wpcf-types-menu-image').addClass('dashicons-'+icon.val());
        icon.bind('change', function() {
            jQuery('div.wpcf-types-menu-image').removeClass().addClass('dashicons-'+jQuery(this).val()).addClass(classes);
        });
    }
    /*
     * 
     * Submit form trigger
     */
    jQuery('.wpcf-types-form').submit(function(){
        /*
         * Check if singular and plural are same
         */
        if (jQuery('#name-singular').val().toLowerCase() == jQuery('#name-plural').val().toLowerCase()) {
            if (jQuery('#wpcf_warning_same_as_slug input[type=checkbox]').is(':checked')) {
                return true;
            }
            jQuery('#wpcf_warning_same_as_slug').fadeOut();
            alert(jQuery('#name-plural').data('wpcf_warning_same_as_slug'));
            jQuery('#name-plural').after(
                '<div class="wpcf-error message updated" id="wpcf_warning_same_as_slug"><p>'
                + jQuery('#name-plural').data('wpcf_warning_same_as_slug')
                + '</p><p><input type="checkbox" name="ct[labels][ignore]" />'
                + jQuery('#name-plural').data('wpcf_warning_same_as_slug_ignore')
                + '</p></div>'
                ).focus().bind('click', function(){
                jQuery('#wpcf_warning_same_as_slug').fadeOut();
            });
            wpcfLoadingButtonStop();
            jQuery('html, body').animate({
                scrollTop: 0
            }, 500);
            return false;
        }
    });
});
