/**
 *
 * $HeadURL: https://www.onthegosystems.com/misc_svn/common/tags/1.5/toolset-forms/js/file-wp35.js $
 * $LastChangedDate: 2015-02-18 12:50:24 +0100 (śro, 18 lut 2015) $
 * $LastChangedRevision: 31718 $
 * $LastChangedBy: marcin $
 *
 */
var wptFile = (function($, w) {
    var frame = [];
    var $item, $parent, $preview;

    function init() {
        // Fetch available headers and apply jQuery.masonry
        // once the images have loaded.
        var $headers = $('.available-headers');

        $headers.imagesLoaded( function() {
            $headers.masonry({
                itemSelector: '.default-header',
                isRTL: !! ( 'undefined' != typeof isRtl && isRtl )
            });
        });
        /*
        $('.js-wpt-field').on('click', 'a.js-wpt-file-upload', function() {
            if ( $(this).data('attched-thickbox') ) {
                return;
            }
            return wptFile.open(this, true);
        });
        */
        // Build the choose from library frame.
        $('.js-wpt-field').on('click', 'a.js-wpt-file-upload', function( event ) {
            var $el = $(this);
            var $type = $el.data('wpt-type');
            var $id = $el.parent().attr('id');
            event.preventDefault();

            // If the media frame already exists, reopen it.
            if ( frame[$id] ) {
                frame[$id].open();
                return;
            }

            // Create the media frame.
            frame[$id] = wp.media.frames.customHeader = wp.media({
                // Set the title of the modal.
                title: $el.html(),

                // Tell the modal to show only images.
                library: {
                    type: 'file' == $type? null:$type
                },

                // Customize the submit button.
                button: {
                    // Set the text of the button.
                    text: $el.data('update'),
                    // Tell the button not to close the modal, since we're
                    // going to refresh the page when the image is selected.
                    close: false
                }
            });

            // When an image is selected, run a callback.
            frame[$id].on( 'select', function() {
                // Grab the selected attachment.
                var attachment = frame[$id].state().get('selection').first();
                var $parent = $el.parent();
                switch( $type ) {
                    case 'image':
                        $('.textfield', $parent).val(attachment.attributes.sizes.full.url);
                        if ( 0 == $('.wpt-file-preview img', $parent.parent()).length) {
                            $('.wpt-file-preview', $parent.parent()).append('<img src="">');
                        }
                        if ( 'undefined' != typeof attachment.attributes.sizes.thumbnail ) {
                            $('.wpt-file-preview img', $parent.parent()).attr('src', attachment.attributes.sizes.thumbnail.url);
                        } else {
                            $('.wpt-file-preview img', $parent.parent()).attr('src', attachment.attributes.sizes.full.url);
                        }
                        break;
                    default:
                        $('.textfield', $parent).val(attachment.attributes.url);
                        break;
                }
                frame[$id].close();
            });

            frame[$id].open();
        });
    }
    return {
        init: init,
    };
})(jQuery);

jQuery(document).ready(wptFile.init);

