/**
 * @see WPToolset_Forms_Conditional (classes/conditional.php)
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/embedded/common/toolset-forms/js/conditional.js $
 * $LastChangedDate: 2015-04-01 14:15:17 +0000 (Wed, 01 Apr 2015) $
 * $LastChangedRevision: 1125405 $ 
 * $LastChangedBy: iworks $ Riccardo
 *
 */
var wptCondTriggers = {}
, wptCondFields = {}
, wptCondCustomTriggers = {}
, wptCondCustomFields = {}
, wptCondDebug = false;

var wptCond = (function ($) {

    function init()
    {
        _.each(wptCondTriggers, function (triggers, formID) {
            _.each(triggers, function (fields, trigger) {
                var $trigger = _getTrigger(trigger, formID);
                _bindChange(formID, $trigger, function (e) {
                    _check(formID, fields);
                });
                _check(formID, fields); // Check conditional on init
            });
        });
        _.each(wptCondCustomTriggers, function (triggers, formID) {
            _.each(triggers, function (fields, trigger) {
                var $trigger = _getTrigger(trigger, formID);
                _bindChange(formID, $trigger, function (e) {
                    _custom(formID, fields);
                });
                _custom(formID, fields); // Check conditional on init
            });
        });
        // Fire validation after init conditional
        wptCallbacks.validationInit.fire();
        // check initial custom NOTE this might be deprecated and not needed anymore
        //Fix double showHide on submition failed: commenting this 
        //_init_custom();
    }

    // hide / show effects

    $.fn.condSlideFadeDown = function (speed, easing, callback) {
        easing = easing || 'linear';
        return this.each(function () {
            $(this).animate({opacity: 'show', height: 'show'}, speed, easing, function () {
                $(this).css('height', 'auto');
                if ($.browser.msie) {
                    this.style.removeAttribute('filter');
                }
                if ($.isFunction(callback)) {
                    callback.call(this);
                }
            });
        });
    };

    $.fn.condSlideFadeUp = function (speed, easing, callback) {
        easing = easing || 'linear';
        return this.each(function () {
            $(this).animate({opacity: 'hide', height: 'hide'}, speed, easing, function () {
                $(this).css('height', 'auto');
                if ($.browser.msie) {
                    this.style.removeAttribute('filter');
                }
                if ($.isFunction(callback)) {
                    callback.call(this);
                }
            });
        });
    };

    function _getTrigger(trigger, formID)
    {
        var $trigger = $('[data-wpt-name="' + trigger + '"]', formID);
        /**
         * wp-admin
         */
        if ($('body').hasClass('wp-admin')) {
            trigger = trigger.replace(/wpcf\-/, 'wpcf[') + ']';
            $trigger = $('[data-wpt-name="' + trigger + '"]', formID);

        }
        /**
         * handle skype field
         */
        if ($trigger.length < 1) {
            $trigger = $('[data-wpt-name="' + trigger + '[skypename]"]', formID);
        }
        /**
         * handle date field
         */
        if ($trigger.length < 1) {
            $trigger = $('[data-wpt-name="' + trigger + '[datepicker]"]', formID);
        }
        /**
         * handle checkboxes and multiselect
         */
        if ($trigger.length < 1) {
            $trigger = $('[data-wpt-name="' + trigger + '[]"]', formID);
        }
        /**
         * handle select
         */
        if ($trigger.length > 0 && 'option' == $trigger.data('wpt-type')) {
            $trigger = $trigger.parent();
        }
        /**
         * debug
         */
        if (wptCondDebug) {
            console.info('_getTrigger');
            console.log('trigger', trigger);
            console.log('$trigger', $trigger);
            console.log('formID', formID);
        }
        return $trigger;
    }

    function _getTriggerValue($trigger, formID)
    {
        if (wptCondDebug) {
            console.info('_getTriggerValue');
            console.log('$trigger', $trigger);
            console.log('$trigger.type', $trigger.data('wpt-type'));
        }
        // Do not add specific filtering for fields here
        // Use add_filter() to apply filters from /js/$type.js
        var val = null;
        // NOTE we might want to set val = ''; by default?
        switch ($trigger.data('wpt-type')) {
            case 'radio':
            case 'radios':
                radio = $('[name="' + $trigger.attr('name') + '"]:checked', formID);
                // If no option was selected, the value should be empty
                val = '';
                if ('undefined' == typeof (radio.data('types-value'))) {
                    val = radio.val();
                } else {
                    val = radio.data('types-value');
                }
                if (wptCondDebug) {
                    console.log('radio', radio);
                }
                break;
            case 'select':                
                option = $('[name="' + $trigger.attr('name') + '"] option:selected', formID);
                // If no option was selected, the value should be empty
                val = '';
                if (wptCondDebug) {
                    console.log('option', option);
                }                
                if (option.length == 1) {
                    if ('undefined' == typeof (option.data('types-value'))) {
                        val = option.val();
                    } else {
                        val = option.data('types-value');
                    }
                } else if (option.length > 1) {
                    val = [];
                    option.each(function () {
                        if ('undefined' == typeof ($(this).data('types-value'))) {
                            val.push($(this).val());
                        } else {
                            val.push($(this).data('types-value'));
                        }
                    });                    
                }
                break;
            case 'checkbox':
                var $trigger_checked = $trigger.filter(':checked');
                // If no checkbox was checked, the value should be empty
                val = '';
                //added data-value checking in order to fix
                //https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/188528502/comments
                if ($trigger_checked.length == 1) {
                    val = ($trigger_checked.attr('data-value')) ? $trigger_checked.attr('data-value') : $trigger_checked.val();
                } else if ($trigger_checked.length > 1) {
                    val = [];
                    $trigger_checked.each(function () {
                        val.push(($(this).attr('data-value')) ? $(this).attr('data-value') : $(this).val());
                    });
                }
                //#########################################################################################
                break;
            case 'file':
                var $trigger_checked = $trigger.filter(':not([disabled])');
                val = $trigger_checked.val();
                break;
            default:
                val = $trigger.val();
        }
        if (wptCondDebug) {
            console.log('val', val);
        }
        return val;
    }

    function _getAffected(affected, formID)
    {
        if (wptCondDebug) {
            console.info('_getAffected');
        }
        var $el = $('[data-wpt-id="' + affected + '"]', formID);
        if ($('body').hasClass('wp-admin')) {
            $el = $el.closest('.wpt-field');
            if ($el.length < 1) {
                $el = $('#' + affected, formID).closest('.form-item');
            }
        } else {
            if ($el.length < 1) {
                /**
                 * get pure field name, without form prefix
                 */
                re = new RegExp(formID + '_');
                name = '#' + affected;
                name = name.replace(re, '');
                /**
                 * try get element
                 */
                $obj = $('[data-wpt-id="' + affected + '_file"]', formID);
                /**
                 * handle by wpt field name
                 */
                if ($obj.length < 1) {
                    $obj = $('[data-wpt-name="' + name + '"]', formID);
                }
                /**
                 * handle date field
                 */
                if ($obj.length < 1) {
                    $obj = $('[data-wpt-name="' + name + '[datepicker]"]', formID);
                }
                /**
                 * handle skype field
                 */
                if ($obj.length < 1) {
                    $obj = $('[data-wpt-name="' + name + '[skypename]"]', formID);
                }
                /**
                 * handle checkboxes field
                 */
                if ($obj.length < 1) {
                    $obj = $('[data-wpt-name="' + name + '[]"]', formID);
                }
                /**
                 * catch by id
                 */
                if ($obj.length < 1) {
                    $obj = $('#' + affected, formID);
                }

            } else {
                $obj = $el;
            }
            /**
             * finally catch parent: we should have catched the $obj
             */
            if ($obj.length > 0) {
                $el = $obj.closest('.js-wpt-conditional-field');
                if ($el.length < 1) {
                    $el = $obj.closest('.cred-field');// This for backwards compatibility
                    if ($el.length < 1) {
                        $el = $obj.closest('.js-wpt-field-items');
                    }
                }
            }
            /**
             * debug
             */
            if (wptCondDebug) {
                console.log('$obj', $obj);
            }
        }
        if ($el.length < 1) {
            $el = $('#' + affected, formID);
        }
        /**
         * generic conditional field
         */
        if ($el.length < 1) {
            $el = $('.cred-group.' + affected, formID);
        }
        /**
         * debug
         */
        if (wptCondDebug) {
            console.log('affected', affected);
            console.log('$el', $el);
        }
        return $el;
    }

    function _checkOneField(formID, field, next)
    {
        var __ignore = false;
        var c = wptCondFields[formID][field];
        var passedOne = false, passedAll = true, passed = false;
        var $trigger;
        _.each(c.conditions, function (data) {
            if (__ignore) {
                return;
            }
            $trigger = _getTrigger(data.id, formID);
            var val = _getTriggerValue($trigger, formID);
            if (wptCondDebug) {
                console.log('formID', formID);
                console.log('$trigger', $trigger);
                console.log('val', 1, val);
            }

            var field_type = $trigger.data('wpt-type');
            if (data.type == 'date') {
                field_type = 'date';
            }
            val = apply_filters('conditional_value_' + field_type, val, $trigger);
            if (wptCondDebug) {
                console.log('val', 2, val);
            }
            do_action('conditional_check_' + data.type, formID, c, field);
            var operator = data.operator, _val = data.args[0];
            /**
             * handle types
             */
            // Not needed anymore
            // NEVER Date.parse timestamps coming from adodb_xxx functions
            /*
             switch(data.type) {
             case 'date'://alert(_val);alert(val);
             if ( _val ) {//alert('this is _val ' + _val);
             //    _val = Date.parse(_val);//alert('this is _val after parse ' + _val);
             }//alert('val is ' + val);
             //val = Date.parse(val);//alert('parsed val is ' + val);
             break;
             }
             */
            if ('__ignore' == val) {
                __ignore = true;
                return;
            }
            /**
             * debug
             */
            if (wptCondDebug) {
                console.log('val', 3, val);
                console.log('_val', _val);
            }
            /**
             * for __ignore_negative set some dummy operator
             */
            if (0 && '__ignore_negative' == val) {
                operator = '__ignore';
            }

            if ($.isArray(val)) {
                // If the selected value is an array, we just can check == and != operators, which means in_array and not_in_array
                // We return false in any other scenario
                switch (operator) {
                    case '===':
                    case '==':
                    case '=':
                        passed_single = jQuery.inArray(_val, val) !== -1;
                        break;
                    case '!==':
                    case '!=':
                    case '<>':
                        passed_single = jQuery.inArray(_val, val) == -1;
                        break;
                    default:
                        passed_single = false;
                        break;
                }
            } else {
                // Note: we can use parseInt here although we are dealing with extended timestamps coming from adodb_xxx functions
                // Because javascript parseInt can deal with integers up to ±1e+21
                switch (operator) {
                    case '===':
                    case '==':
                    case '=':
                        if ($.isArray(val)) {

                        } else {
                            passed_single = val == _val;
                        }
                        break;
                    case '!==':
                    case '!=':
                    case '<>':
                        passed_single = val != _val;
                        break;
                    case '>':
                        passed_single = parseInt(val) > parseInt(_val);
                        break;
                    case '<':
                        passed_single = parseInt(val) < parseInt(_val);
                        break;
                    case '>=':
                        passed_single = parseInt(val) >= parseInt(_val);
                        break;
                    case '<=':
                        passed_single = parseInt(val) <= parseInt(_val);
                        break;
                    case 'between':
                        passed_single = parseInt(val) > parseInt(_val) && parseInt(val) < parseInt(data.args[1]);
                        break;
                    default:
                        passed_single = false;
                        break;
                }
            }
            if (!passed_single) {
                passedAll = false;
            } else {
                passedOne = true;
            }
        });

        if (c.relation === 'AND' && passedAll) {
            passed = true;
        }
        if (c.relation === 'OR' && passedOne) {
            passed = true;
        }
        /**
         * debug
         */
        if (wptCondDebug) {
            console.log('passedAll', passedAll, 'passedOne', passedOne, 'passed', passed, '__ignore', __ignore);
            console.log('field', field);
        }
        if (!__ignore) {
            _showHide(passed, _getAffected(field, formID));
        }
        // No need to set a timeout anymore
        //if ( $trigger.length && next && $trigger.hasClass('js-wpt-date' ) ) {
        //    setTimeout(function() {
        //        _checkOneField( formID, field, false );
        //    }, 200);
        //}
    }

    function _check(formID, fields)
    {
        if (wptCondDebug) {
            console.info('_check');
        }
        _.each(fields, function (field) {
            _checkOneField(formID, field, true);
        });
        wptCallbacks.conditionalCheck.fire(formID);
    }

    function _bindChange(formID, $trigger, func)
    {
        // Do not add specific binding for fields here
        // Use add_action() to bind change trigger from /js/$type.js
        // if not provided - default binding will be performed
        var binded = do_action('conditional_trigger_bind_' + $trigger.data('wpt-type'), $trigger, func, formID);
        if (binded) {
            return;
        }
        /**
         * debug
         */
        if (wptCondDebug) {
            console.info('_bindChange');
            console.log('$trigger', $trigger);
            console.log('wpt-type', $trigger.data('wpt-type'));
        }
        switch ($trigger.data('wpt-type')) {
            case 'checkbox':
                $trigger.on('click', func);
                break;
            case 'radio':
            case 'radios':
                /**
                 * when selecting again, do not forget about formID
                 */
                $('[name="' + $trigger.attr('name') + '"]', formID).on('click', func);
                break;
            case 'select':
                $trigger.on('change', func);
                break;
            case 'date':
                $trigger.on('change', func);
                break;
            case 'file':
                $trigger.on('change', func);
                break;
            default:
                if ($trigger.hasClass('js-wpt-colorpicker')) {
                    $trigger.data('_bindChange', func)
                }
                $($trigger).on('blur', func);
                break;
        }
    }

    function _custom(formID, fields)
    {
        /**
         * debug
         */
        if (wptCondDebug) {
            console.log('_custom');
            console.log('formID', formID);
            console.log('fields', fields);
        }
        _.each(fields, function (field) {
            var c = wptCondCustomFields[formID][field];
            var expression = c.custom;

            // Get the values and update the expression.
            _.each(c.triggers, function (t) {
                var $trigger = _getTrigger(t, formID),
                        value = _getTriggerValue($trigger, formID),
                        is_array = $trigger.length > 1 ? true : false;
                if (wptCondDebug) {
                    console.log("The value is ", value, " for element: ", t, $trigger);
                }

                //Fix https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/193595717/comments
                //group issue on select
                if (false) {
                    if ($trigger.is('select') && !$trigger.attr('multiple')) {                   
                        $("#" + $trigger.attr('id') + " > option").each(function () {
                            //console.log(value + " " + this.text + ' ' + this.value + ' ' + $(this).data('typesValue'));                        
                            if ($(this).data('typesValue') && $(this).data('typesValue') == value || this.text==value || value==this.value)
                                value = this.text;
                        });                  
                    }
                }
                //#####################################################################################

                if (typeof value != 'undefined') {

                    // make it a string by wrapping in quotes if
                    //    1. the value is an empty string
                    //    2. or it's not a number

                    // if the trigger is an array, eg checkboxes
                    // then convert value to ARRAY(...)


                    if (is_array === true) {

                        var val_array = '';

                        if (wptCondDebug) {
                            console.log();
                        }

                        if (value instanceof Array) {
                            for (var i = 0; i < value.length; i++) {
                                var val = value[i];
                                if (val === '' || isNaN(val)) {
                                    val = '\'' + val + '\'';
                                }

                                if (val_array == '') {
                                    val_array = val;
                                } else {
                                    val_array += ',' + val;
                                }
                            }
                        } else {
                            if (isNaN(value)) {
                                value = '\'' + value + '\'';
                            }
                            val_array = value;
                        }

                        value = 'ARRAY(' + val_array + ')';

                    }
                    else
                    {
                        if (value === '' || isNaN(value)) {
                            value = '\'' + value + '\'';
                        }
                    }

                    // First replace the $(field_name) format
                    var replace = new RegExp('\\$\\(' + t + '\\)', 'g');

                    expression = expression.replace(replace, value);

                    // next replace the $field_name format
                    var replace_old = new RegExp('\\$' + t, 'g');

                    expression = expression.replace(replace_old, value);

                }

            });

            var result = false;
            //https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/196173370/comments#309696464
            //Added a new check using text element on select
            var result2 = false;

            try {
                var parser = new ToolsetParser.Expression(expression);
                parser.parse();
                result = parser.eval();
            }
            catch (e) {
                console.info("Error in Tokenizer", e, expression, " there may be an error in your expression syntax");
            }
            
            //https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/196173370/comments#309696464
            //Added a new check using text element on select
            // Get the values and update the expression.
            _.each(c.triggers, function (t) {
                var $trigger = _getTrigger(t, formID),
                        value = _getTriggerValue($trigger, formID),
                        is_array = $trigger.length > 1 ? true : false;
                if (wptCondDebug) {
                    console.log("The value is ", value, " for element: ", t, $trigger);
                }

                //Fix https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/193595717/comments
                //group issue on select
                if ($trigger.is('select') && !$trigger.attr('multiple')) {                   
                    $("#" + $trigger.attr('id') + " > option").each(function () {
                        //console.log(value + " " + this.text + ' ' + this.value + ' ' + $(this).data('typesValue'));                        
                        if ($(this).data('typesValue') && $(this).data('typesValue') == value || this.text==value || value==this.value)
                            value = this.text;
                    });                  
                }
                //#####################################################################################

                if (typeof value != 'undefined') {

                    // make it a string by wrapping in quotes if
                    //    1. the value is an empty string
                    //    2. or it's not a number

                    // if the trigger is an array, eg checkboxes
                    // then convert value to ARRAY(...)


                    if (is_array === true) {

                        var val_array = '';

                        if (wptCondDebug) {
                            console.log();
                        }

                        if (value instanceof Array) {
                            for (var i = 0; i < value.length; i++) {
                                var val = value[i];
                                if (val === '' || isNaN(val)) {
                                    val = '\'' + val + '\'';
                                }

                                if (val_array == '') {
                                    val_array = val;
                                } else {
                                    val_array += ',' + val;
                                }
                            }
                        } else {
                            if (isNaN(value)) {
                                value = '\'' + value + '\'';
                            }
                            val_array = value;
                        }

                        value = 'ARRAY(' + val_array + ')';

                    }
                    else
                    {
                        if (value === '' || isNaN(value)) {
                            value = '\'' + value + '\'';
                        }
                    }

                    // First replace the $(field_name) format
                    var replace = new RegExp('\\$\\(' + t + '\\)', 'g');

                    expression = expression.replace(replace, value);

                    // next replace the $field_name format
                    var replace_old = new RegExp('\\$' + t, 'g');

                    expression = expression.replace(replace_old, value);

                }

            });
            
            try {
                var parser = new ToolsetParser.Expression(expression);
                parser.parse();
                result2 = parser.eval();
            }
            catch (e) {
                console.info("Error in Tokenizer", e, expression, " there may be an error in your expression syntax");
            }

            _showHide(result||result2, _getAffected(field, formID));

        });
        wptCallbacks.conditionalCheck.fire(formID);
    }

    function _showHide(show, $el)
    {
        //Fix https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/193353994/comments#302703480
        
        //TODO: check this cause side effect
        /*if (jQuery('.wpt-form-error').length) {            
            jQuery('.wpt-form-error').hide();
        }*/

        if (wptCondDebug) {
            console.info('_showHide');
            console.log(show, $el);
        }

        var effectmode = '',
                dur = 'slow',
                delay = 50;

        if ($el.attr('data-effectmode')) {
            effectmode = $el.data('effectmode');
        } else {
            effectmode = 'slide';
        }

        if (show) {
            if ( $el.hasClass('wpt-date') && 'object' == typeof wptDate) {
                $('.js-wpt-date', $el).removeAttr('disabled');
                wptDate.init('body');
            }
            $el.addClass('wpt-conditional-visible').removeClass('wpt-conditional-hidden js-wpt-remove-on-submit js-wpt-validation-ignore');
            switch (effectmode) {
                case 'fade-slide':
                    setTimeout(function () {
                        $el.stop(true).condSlideFadeDown(dur, 'linear');
                    }, delay);
                    break;
                case 'slide':
                    setTimeout(function () {
                        $el.stop(true).slideDown(dur, 'linear', function () {
                            $el.css('height', 'auto');
                        });
                    }, delay);
                    break;
                case 'fade':
                    setTimeout(function () {
                        $el.stop(true).fadeIn(dur);
                    }, delay);
                    break;
                case 'none':
                    break;
                default:
                    $el.show();
                    break;
            }
            $($el).find('input, textarea, button, select').prop("disabled", false);
        } else {
            $el.addClass('wpt-conditional-hidden js-wpt-remove-on-submit js-wpt-validation-ignore').removeClass('wpt-conditional-visible');
            switch (effectmode) {
                case 'fade-slide':
                    setTimeout(function () {
                        $el.stop(true).condSlideFadeUp(dur, 'linear');
                    }, delay);
                    break;
                case 'slide':
                    setTimeout(function () {
                        $el.stop(true).slideUp(dur, 'linear', function () {
                            $el.css('height', 'auto');
                        });
                    }, delay);
                    break;
                case 'fade':
                    setTimeout(function () {
                        $el.stop(true).fadeOut(dur);
                    }, delay);
                    break;
                case 'none':
                    break;
                default:
                    $el.hide();
                    break;
            }
            $($el).find('input, textarea, button, select').attr('disabled','disabled');            
        }
    }

    function ajaxCheck(formID, field, conditions)
    {
        var values = {};
        _.each(conditions.conditions, function (c) {
            var $trigger = _getTrigger(c.id, formID);
            values[c.id] = _getTriggerValue($trigger);
        });
        var data = {
            'action': 'wptoolset_conditional',
            'conditions': conditions,
            'values': values
        };
        $.post(wptConditional.ajaxurl, data, function (passed) {
            _showHide(passed, _getAffected(field, formID));
            wptCallbacks.conditionalCheck.fire(formID);
        }).fail(function (data) {
            //alert(data);
        });
    }

    function addConditionals(data)
    {
        _.each(data, function (c, formID) {
            if (typeof c.triggers != 'undefined'
                    && typeof wptCondTriggers[formID] != 'undefined') {
                _.each(c.triggers, function (fields, trigger) {
                    wptCondTriggers[formID][trigger] = fields;
                    var $trigger = _getTrigger(trigger, formID);
                    _bindChange(formID, $trigger, function () {
                        _check(formID, fields);
                    });
                });
            }
            if (typeof c.fields != 'undefined'
                    && typeof wptCondFields[formID] != 'undefined') {
                _.each(c.fields, function (conditionals, field) {
                    wptCondFields[formID][field] = conditionals;
                });
            }
            if (typeof c.custom_triggers != 'undefined'
                    && typeof wptCondCustomTriggers[formID] != 'undefined') {
                _.each(c.custom_triggers, function (fields, trigger) {
                    wptCondCustomTriggers[formID][trigger] = fields;
                    var $trigger = _getTrigger(trigger, formID);
                    _bindChange(formID, $trigger, function () {
                        _custom(formID, fields);
                    });
                });
            }
            if (typeof c.custom_fields != 'undefined'
                    && typeof wptCondCustomFields[formID] != 'undefined') {
                _.each(c.custom_fields, function (conditionals, field) {
                    wptCondCustomFields[formID][field] = conditionals;
                });
            }
        });
    }

    /**
     * deprecated
     * @returns {undefined}
     */
    function _init_custom() {
        $('.js-wpt-field-items').each(function () {
            var init_custom = $(this).data('initial-conditional');
            if (init_custom) {
                var field = $(this).closest('.cred-field');
                if (field.length) {
                    _showHide(false, field);
                }
            }
        })
    }

    return {
        init: init,
        ajaxCheck: ajaxCheck,
        addConditionals: addConditionals
    };

})(jQuery);

