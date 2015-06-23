
var wptSkype = (function($) {
    var $parent, $skypename, $style, $preview;
    var $popup = $('#tpl-wpt-skype-edit-button > div');
    function init() {
        $('body').on('click', '.js-wpt-skype-edit-button', function() {
            $parent = $(this).parents('.js-wpt-field-item');
            $skypename = $('.js-wpt-skypename', $parent);
            $style = $('.js-wpt-skypestyle', $parent);
            $preview = $('.js-wpt-skype-preview', $parent);
            $('.js-wpt-skypename-popup', $popup).val($skypename.val());
            $('[name="wpt-skypestyle-popup"][value="' + $style.val() + '"]', $popup)
                    .attr('checked', true);
            tb_show(wptSkypeData.title, "#TB_inline?inlineId=tpl-wpt-skype-edit-button&height=500&width=600", "");
        });
        $('#wpt-skype-edit-button-popup').on('click', '.js-wpt-close-thickbox', function() {
            $skypename.val($('.js-wpt-skypename-popup', $popup).val());
            var $selected = $('[name="wpt-skypestyle-popup"]:checked', $popup);
            $style.val($selected.val());
            $preview.replaceWith($selected.next().clone());
            tb_remove();
        });
    }
    return {
        init: init
    };
})(jQuery);

jQuery(document).ready(wptSkype.init);
