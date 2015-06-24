var wptColorpicker = (function($) {
    function init(parent) {
        $('input.js-wpt-colorpicker').iris({
            change: function(event, ui) {
                if ( 'function' == typeof ( $(event.target).data('_bindChange') ) ) {
                    $(event.target).data('_bindChange')();
                }
                
            }
        });
        $(document).click(function (e) {
            if (!$(e.target).is("input.js-wpt-colorpicker, .iris-picker, .iris-picker-inner")) {
                $('input.js-wpt-colorpicker').iris('hide');
            }
        });
        $('input.js-wpt-colorpicker').click(function (event) {
            $('input.js-wpt-colorpicker').iris('hide');
            $(this).iris('show');
            return false;
        });
    }
    return {
        init: init
    };
})(jQuery);

jQuery(document).ready(function() {
    wptColorpicker.init('body');
});
wptCallbacks.reset.add(function(parent) {
    wptColorpicker.init(parent);
});
/**
 * add for new repetitive field
 */
wptCallbacks.addRepetitive.add(wptColorpicker.init);
