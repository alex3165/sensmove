/* 
 * Validation JS
 * 
 * - Initializes validation on selector (forms)
 * - Adds/removes rules on elements contained in var types.validation
 * - Checks if elements are hidden by conditionals
 * 
 * @see WPCF_Validation::renderJsonData( $selector ) how rules are added
 * Use wp_enqueue_script( 'types-validation' ) to enqueue this script.
 * Use wpcf_form_render_js_validation( $selector ) to render validation data used here.
 * 
 * Used in post-relationship.js in 2 places for callback.
 */

var typesValidation = (function($) {

    function init() {
        $.each(types.validation, function() {
            _initValidation(this.selector);
            _setRules(this.elements);
        });
    }

    function setRules() {
        $.each(types.validation, function() {
            _setRules(this.elements);
        });
    }

    function _initValidation(selector) {
        $(selector).validate({
            // :hidden is kept because it's default value.
            // All accepted by jQuery.not() can be added.
            ignore: 'input[type="hidden"], .wpcf-form-groups-support-post-type, .wpcf-form-groups-support-tax, .wpcf-form-groups-support-templates, :not(.js-types-validate)',
            errorPlacement: function(error, element) {
                error.insertBefore(element);
            },
            highlight: function(element, errorClass, validClass) {
                $('#publishing-action .spinner').css('visibility', 'hidden');
                $('#publish').bind('click', function() {
                    $('#publishing-action .spinner').css('visibility', 'visible');
                });
                $(element).parents('.collapsible').slideDown();
                if (selector == '#post') {
                    var box = jQuery(element).parents('postbox');
                    if (box.hasClass('closed')) {
                        box.find('.handlediv').trigger('click');
                    }
                }
                $(element).parents('.collapsible').slideDown();
                $("input#publish").addClass("button-primary-disabled");
                $("input#save-post").addClass("button-disabled");
                $("#save-action .ajax-loading").css("visibility", "hidden");
                $("#publishing-action #ajax-loading").css("visibility", "hidden");
                // $.validator.defaults.highlight(element, errorClass, validClass); // Do not add class to element
            },
            unhighlight: function(element, errorClass, validClass) {
                $("input#publish, input#save-post").removeClass("button-primary-disabled").removeClass("button-disabled");
                // $.validator.defaults.unhighlight(element, errorClass, validClass);
            },
            invalidHandler: function(form, validator) {
                var elements = new Array(), form = $(selector), passed = false;
                /*
                 * validator.errorList contains an array of objects,
                 * where each object has properties "element" and "message".
                 * element is the actual HTML Input.
                 */
                for (var i = 0; i < validator.errorList.length; i++) {
                    var el = validator.errorList[i].element;
                    elements.push($(el).attr('id'));
                }
                /*
                 * Valid if conditional is hidden by other conditional
                 */
                if (_checkConditional(selector, elements, form, validator)) {
                    passed = true;
                }
                if (passed) {
                    $(selector).validate().cancelSubmit = true;
                    $(selector).submit();
                }
                wpcfLoadingButtonStop();
            },
            errorClass: "wpcf-form-error"
        });
    }

    function _checkConditional(selector, elements, form, validator) {
        var element, failed = new Array(), failedHidden = new Array();
        for (var i = 0; i < elements.length; i++) {
            selector = elements[i];
            element = jQuery('#' + selector);
            if (element.length > 0) {
                if (conditionalIsHidden(element)) {
                    failedHidden.push(selector);
                } else {
                    if (element.parents('.inside').is(':hidden')) {
                        element.parents('.postbox').find('.handlediv').trigger('click');
                    }
                    failed.push(selector);
                }
            }
        }
        if (failed.length > 0) {
            return false;
        } else if (failedHidden.length > 0) {
            return true;
        }
        return false;
    }

    function conditionalIsHidden(object) {
        // Check if meta-box is hidden
        if (object.parents('.wpcf-conditional').length > 0
                && object.parents('.inside').is(':hidden')) {
            if (object.parents('.wpcf-conditional').css('display') == 'none') {
                return true;
            }
            return false;
        } else {
            return object.parents('.wpcf-conditional').length > 0 && object.is(':hidden');
        }
    }

    function _setRules(elements) {
        $.each(elements, function() {
            element = this;
            if ($(element.selector).length > 0) {
                $.each(element.rules, function() {
                    if (conditionalIsHidden($(element.selector))) {
                        $(element.selector).rules("remove", this.method);
                        $(element.selector).removeClass('js-types-validate');
                    } else {
                        var rule = {messages: {}};
                        rule[this.method] = this.value == 'true' ? true : this.value;
                        rule.messages[this.method] = this.message;
                        $(element.selector).rules("add", rule);
                        $(element.selector).addClass('js-types-validate');
                    }
                });
            }
        });
    }

    return {
        init: init,
        setRules: setRules,
        conditionalIsHidden: conditionalIsHidden
    };

})(jQuery);

jQuery(document).ready(function($) {
    typesValidation.init();
});