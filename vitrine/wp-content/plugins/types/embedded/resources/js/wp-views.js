/*
 * Toolset Views plugin.
 *
 * Loaded on Views or Views Template edit screens.
 */
var typesWPViews = (function(window, $){

    function openFrame( fieldID, metaType, postID, shortcode )
    {
        var colorboxWidth = 750 + 'px';

        if ( !( jQuery.browser.msie && parseInt(jQuery.browser.version) < 9 ) ) {
            var documentWidth = jQuery(document).width();
            if ( documentWidth < 750 ) {
                colorboxWidth = 600 + 'px';
            }
        }

        var url = ajaxurl+'?action=wpcf_ajax&wpcf_action=editor_callback'
        + '&_typesnonce=' + types.wpnonce
        + '&callback=views_wizard'
        + '&field_id=' + fieldID
        + '&field_type=' + metaType
        + '&post_id=' + postID
        + '&shortcode=' + shortcode;

        jQuery.colorbox({
            href: url,
            iframe: true,
            inline : false,
            width: colorboxWidth,
            opacity: 0.7,
            closeButton: false
        });
    }

    return {
        wizardEditShortcode: function( fieldID, metaType, postID, shortcode ) {
            openFrame( fieldID, metaType, postID, shortcode );
        },
        wizardSendShortcode: function( shortcode ) {
            window.wpv_restore_wizard_popup(shortcode);
        },
        wizardCancel: function() {
            window.wpv_cancel_wizard_popup();
        }
    };
})(window, jQuery, undefined);