var wptDate = (function ($) {
    var _tempConditions, _tempField;
    function init(parent) {
        if ($.isFunction($.fn.datepicker)) {
            $('input.js-wpt-date', $(parent)).each(function (index) {
                if (!$(this).is(':disabled') && !$(this).hasClass('hasDatepicker')) {
                    a = wptDate.add($(this));
                    //a.next().after('<span style="margin-left:10px"><i>' + wptDateData.dateFormatNote + '</i></span>').data( 'dateFormatNote', true );
                }
            });
        }

        $(document).on('click', '.js-wpt-date-clear', function () {
            var thiz = $(this), thiz_close, el, el_aux, el_select;
            if (thiz.closest('.js-wpt-field-item').length > 0) {
                thiz_close = thiz.closest('.js-wpt-field-item');
                el_aux = thiz_close.find('.js-wpt-date-auxiliar');
                el = thiz_close.find('.js-wpt-date');
                el_select = thiz_close.find('select');
            } else if (thiz.closest('.wpt-repctl').length > 0) {
                thiz_close = thiz.closest('.wpt-repctl');
                el_aux = thiz_close.find('.js-wpt-date-auxiliar');
                el = thiz_close.find('.js-wpt-date');
                el_select = thiz_close.find('select');
            } else if (thiz.closest('.js-wpt-field-items').length > 0) {
                thiz_close = thiz.closest('.js-wpt-field-items');
                el_aux = thiz_close.find('.js-wpt-date-auxiliar');
                el = thiz_close.find('.js-wpt-date');
                el_select = thiz_close.find('select');
            } else {
                // This should be an empty object, but as we use the variable later we need to set it
                el_aux = thiz.closest('.js-wpt-field-items');
                el = thiz.closest('.js-wpt-date');
                el_select = thiz.closest('select');
            }
            //Added trigger('wptDateSelect'); fix trigger validation and condition on click of clear
            el_aux.val('').trigger('change').trigger('wptDateSelect');
            el.val('');
            el_select.val('0');
            thiz.hide();
            
        });
    }

    function add(el)
    {
        // Before anything, return if this is readonly
        if (el.hasClass('js-wpv-date-readonly')) {
            if (!el.hasClass('js-wpv-date-readonly-added')) {
                el.addClass('js-wpv-date-readonly-added').after('<img src="' + wptDateData.readonly_image + '" alt="' + wptDateData.readonly + '" title="' + wptDateData.readonly + '" class="ui-datepicker-readonly" />');
            }
            return;
        }
        // First, a hacky hack: make the id of each el unique, because the way they are produced on repetitive date fields does not ensure it
        var rand_number = 1 + Math.floor(Math.random() * 150),
                old_id = el.attr('id');
        el.attr('id', old_id + '-' + rand_number);
        // Walk along, nothing to see here...
        return el.datepicker({
            onSelect: function (dateText, inst) {
                //	The el_aux element depends on the scenario: backend or frontend
                var el_close, el_aux, el_clear;
                el.val('');
                if (el.closest('.js-wpt-field-item').length > 0) {
                    el_close = el.closest('.js-wpt-field-item');
                    el_aux = el_close.find('.js-wpt-date-auxiliar');
                    el_clear = el_close.find('.js-wpt-date-clear');
                } else if (el.closest('.wpt-repctl').length > 0) {
                    el_close = el.closest('.wpt-repctl');
                    el_aux = el_close.find('.js-wpt-date-auxiliar');
                    el_clear = el_close.find('.js-wpt-date-clear');
                } else if (el.closest('.js-wpt-field-items').length > 0) {
                    el_close = el.closest('.js-wpt-field-items');
                    el_aux = el_close.find('.js-wpt-date-auxiliar');
                    el_clear = el_close.find('.js-wpt-date-clear');
                } else {
                    // This should be an empty object, but as we use the variable later we need to set it
                    el_aux = el.closest('.js-wpt-field-items');
                    el_clear = el.closest('.js-wpt-date-clear');
                }
                var data = 'date=' + dateText;
                data += '&date-format=' + wptDateData.dateFormatPhp;
                data += '&action=wpt_localize_extended_date';
                                
                $.post(wptDateData.ajaxurl, data, function (response) {
                    response = $.parseJSON(response);
                    if (el_aux.length > 0) {
                        el_aux.val(response['timestamp']).trigger('wptDateSelect');
                    }
                    el.val(response['display']);
                    el_clear.show();
                    
                    //Fix adding remove label on date
                    el.prev('label.wpt-form-error').remove();
                });
                //el.trigger('wptDateSelect');
            },
            showOn: "both",
            buttonImage: wptDateData.buttonImage,
            buttonImageOnly: true,
            buttonText: wptDateData.buttonText,
            dateFormat: 'ddmmyy',
            //dateFormat: wptDateData.dateFormat,
            //altFormat: wptDateData.dateFormat,
            changeMonth: true,
            changeYear: true,
            yearRange: wptDateData.yearMin + ':' + wptDateData.yearMax,
            beforeShow: function(input) {
                $(input).css({
                    zIndex: 159999 // media library has z-index 160000
                })
            }
        });
    }

    function ajaxConditional(formID, conditions, field) {
        _tempConditions = conditions;
        _tempField = field;
        wptCallbacks.conditionalCheck.add(wptDate.ajaxCheck);
    }
    function ajaxCheck(formID) {
        wptCallbacks.conditionalCheck.remove(wptDate.ajaxCheck);
        wptCond.ajaxCheck(formID, _tempField, _tempConditions);
    }
    function ignoreConditional(val) {
        if ('' == val) {
            return '__ignore_negative';
        }
        return val;
        //return Date.parse(val);
    }
    function bindConditionalChange($trigger, func, formID) {
        $trigger.on('wptDateSelect', func);
        //var lazy = _.debounce(func, 1000);
        //$trigger.on('keyup', lazy);
        return false;
    }
    function triggerAjax(func) {
        if ($(this).val().length >= wptDateData.dateFormatPhp.length)
            func();
    }
    return {
        init: init,
        add: add,
        ajaxConditional: ajaxConditional,
        ajaxCheck: ajaxCheck,
        ignoreConditional: ignoreConditional,
        bindConditionalChange: bindConditionalChange,
        triggerAjax: triggerAjax
    };
})(jQuery);

jQuery(document).ready(function () {
    wptDate.init('body');
    //fixing unknown Srdjan error
    jQuery('.ui-datepicker-inline').hide();
});

if ('undefined' != typeof (wptCallbacks)) {
    wptCallbacks.reset.add(function (parent) {
        wptDate.init(parent);
    });
    wptCallbacks.addRepetitive.add(wptDate.init);
}

//add_action('conditional_check_date', wptDate.ajaxConditional, 10, 3);
if ('function' == typeof (add_filter)) {
    add_filter('conditional_value_date', wptDate.ignoreConditional, 10, 1);
}
if ('function' == typeof (add_action)) {
    add_action('conditional_trigger_bind_date', wptDate.bindConditionalChange, 10, 3);
}
