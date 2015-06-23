/** * * Use this file only for scripts needed in full version.
 * Before moving from embedded JS - make sure it's needed only here.
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/resources/js/basic.js $
 * $LastChangedDate: 2015-01-16 14:28:15 +0000 (Fri, 16 Jan 2015) $
 * $LastChangedRevision: 1069430 $
 * $LastChangedBy: iworks $
 *
 */
jQuery(document).ready(function($){
    $('input[name=file]').on('change', function() {
        if($(this),$(this).val()) {
            $('input[name=import-file]').removeAttr('disabled');
        }
    });
    $('a.current').each( function() {
        var href = $(this).attr('href');
        if ('undefined' != typeof(href) && href.match(/page=wpcf\-edit(\-(type|usermeta))?/)) {
            $(this).attr('href', window.location.href);
        }
    });
    /**
     * settings toolset messages
     */
    $('#wpcf-toolset-messages-form input[type=checkbox]').on('change', function() {
        parent = $(this).closest('form');
        $('.spinner', parent).show();
        $('.updated', parent).hide();
        var data = {
            action: 'toolset_messages',
            value: $(this).attr('checked')

        };
        $.post(ajaxurl, data, function(response) {
            $('.spinner', parent).hide().after(response);
        });
    });
});
