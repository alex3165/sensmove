/*
 * Validation JS
 *
 * - Initializes validation on selector (forms)
 * - Adds/removes rules on elements contained in var wptoolsetValidationData
 * - Checks if elements are hidden by conditionals
 *
 * @see class WPToolset_Validation
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/embedded/common/toolset-forms/js/validation.js $
 * $LastChangedDate: 2015-04-01 14:15:17 +0000 (Wed, 01 Apr 2015) $
 * $LastChangedRevision: 1125405 $
 * $LastChangedBy: iworks $
 *
 */
//var wptValidationData = {};

var wptValidationForms = [];
var wptValidation = (function($) {
    function init() {
        /**
         * add extension to validator method
         */
        $.validator.addMethod("extension", function(value, element, param) {
            param = typeof param === "string" ? param.replace(/,/g, "|") : param;
            return this.optional(element) || value.match(new RegExp(".(" + param + ")$", "i"));
        });

        /**
         * add hexadecimal to validator method
         */
        $.validator.addMethod("hexadecimal", function(value, element, param) {
            return value=="" || /(^#[0-9A-F]{6}$)|(^#[0-9A-F]{3}$)/i.test(value);
        });

        /**
         * add skype to validator method
         */
        $.validator.addMethod("skype", function(value, element, param) {
            return value=="" || /^([a-z0-9\.\_\,\-\#]+)$/i.test(value);
        });

        /**
         * add extension to validator method require
         */
        $.validator.addMethod("required", function(value, element, param) {
                // check if dependency is met
                if ( !this.depend(param, element) )
                        return "dependency-mismatch";

                switch( element.nodeName.toLowerCase() ) {
                case 'select':
                        var val = $(element).val();
                        //Fix https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/189231348/comments
                        // we have data-types-value that in select contains the exactly value
                        $(element).find('option').each(function(index, option){
                            if ($(option).val()==value) {
	                        //if $(option).data('typesValue') is undefined i am in backend side
                                val = ($(option).data('typesValue')!=undefined)?$(option).data('typesValue'):val;
                                return;
                            }
                        });
                        //#########################################################################
                        return val && $.trim(val).length > 0;
                case 'input':
//                        if (jQuery(element).hasClass("hasDatepicker")) {
//                            element = jQuery(element).siblings( 'input[type="hidden"]' );
//                            value = element.val();
//                            element = element[0];
//                            console.log(value+" -> "+this.getLength(value, element));
//                            return this.getLength(value, element) > 0;
//                        }

                        if (jQuery(element).hasClass("hasDatepicker")) {
                            return false;
                        }

                        if ( this.checkable(element) )
                                return this.getLength(value, element) > 0;
                default:
                        return $.trim(value).length > 0;
                }
        });

        /**
         * Add validation method for datepicker adodb_xxx format for date fields
         */
        $.validator.addMethod(
            "dateADODB_STAMP",
            function(a,b){
                return this.optional(b)||/^-?(?:\d+|\d{1,3}(?:,\d{3})+)(?:\.\d+)?$/.test(a) &&  -12219292800 < a && a < 32535215940
            },
            "Please enter a valid date"
        );
        _.each(wptValidationForms, function(formID) {
            _initValidation(formID);
            applyRules(formID);
        });
    }

    function _initValidation(formID) {
        var $form = $(formID);
        $form.validate({
            // :hidden is kept because it's default value.
            // All accepted by jQuery.not() can be added.
            ignore: 'input[type="hidden"]:not(.js-wpt-date-auxiliar),:not(.js-wpt-validate)',
            errorPlacement: function(error, element) {
                error.insertBefore(element);
            },
            highlight: function(element, errorClass, validClass) {
                // Expand container
                $(element).parents('.collapsible').slideDown();
                if (formID == '#post') {
                    var box = $(element).parents('.postbox');
                    if (box.hasClass('closed')) {
                        $('.handlediv', box).trigger('click');
                    }
                }
                // $.validator.defaults.highlight(element, errorClass, validClass); // Do not add class to element
            },
            unhighlight: function(element, errorClass, validClass) {
                $("input#publish, input#save-post").removeClass("button-primary-disabled").removeClass("button-disabled");
                // $.validator.defaults.unhighlight(element, errorClass, validClass);
            },
            invalidHandler: function(form, validator) {
                if (formID == '#post') {
                    $('#publishing-action .spinner').css('visibility', 'hidden');
                    $('#publish').bind('click', function() {
                        $('#publishing-action .spinner').css('visibility', 'visible');
                    });
                    $("input#publish").addClass("button-primary-disabled");
                    $("input#save-post").addClass("button-disabled");
                    $("#save-action .ajax-loading").css("visibility", "hidden");
                    $("#publishing-action #ajax-loading").css("visibility", "hidden");
                }
            },
//            submitHandler: function(form) {
//                // Remove failed conditionals
//                $('.js-wpt-remove-on-submit', $(form)).remove();
//                form.submit();
//            },
            errorClass: 'wpt-form-error'
        });
        $form.on('submit', function() {
            if ( $form.valid() ) {
                $('.js-wpt-remove-on-submit', $(this)).remove();
            }
        });
    }

    function isIgnored($el) {
        var ignore = $el.parents('.js-wpt-field').hasClass('js-wpt-validation-ignore') ||  // Individual fields
                        $el.parents('.js-wpt-remove-on-submit').hasClass('js-wpt-validation-ignore'); // Types group of fields
        return ignore;
    }

    function applyRules(container) {
        $('[data-wpt-validate]', $(container)).each(function() {
            _applyRules($(this).data('wpt-validate'), this, container);
        });
    }

    function _applyRules(rules, selector, container) {
        var element = $(selector, $(container));
        if (element.length > 0) {
            if (isIgnored(element)) {
                element.rules('remove');
                element.removeClass('js-wpt-validate');
            } else if (!element.hasClass('js-wpt-validate')) {
                _.each(rules, function(value, rule) {
                    var _rule = {messages: {}};
                    _rule[rule] = value.args;
                    if (value.message !== 'undefined') {
                        _rule.messages[rule] = value.message;
                    }
                    element.rules('add', _rule);
                    element.addClass('js-wpt-validate');
                });
            }
        }
    }

    return {
        init: init,
        applyRules: applyRules,
        isIgnored: isIgnored,
    };

})(jQuery);


jQuery(document).ready(function(){
    wptCallbacks.reset.add(function() {
        wptValidation.init();
    });
    wptCallbacks.addRepetitive.add(function(container) {
        wptValidation.applyRules(container);
    });
    wptCallbacks.removeRepetitive.add(function(container) {
        wptValidation.applyRules(container);
    });
    wptCallbacks.conditionalCheck.add(function(container) {
        wptValidation.applyRules(container);
    });
});
