/**
 * Loop through each check trigger field
 * (marked with .wpcf-conditional-trigger
 */
function wpcfConditionalInit(selector) {
    selector = typeof selector !== 'undefined' ? selector+' ' : '';
    var triggered = false;
    jQuery(selector+'.wpcf-conditional-check-trigger').each(function(){

        // If triggered from relationship table send just row
        if (jQuery(this).parents('.wpcf-pr-table-wrapper').length > -1) {
            var inputs = jQuery(this).parents('tr').find(':input');
        } else {
            var inputs = jQuery(this).parents('.inside').find(':input');
        }

        // Already binded!
        if (jQuery(this).hasClass('wpcf-cd-binded')) {
            return false;
        }

        // Mark as binded
        jQuery(this).addClass('wpcf-cd-binded');

        // Bind actions according to form element type
        if (jQuery(this).hasClass('radio')
            || jQuery(this).hasClass('checkbox')) {
            jQuery(this).bind('click', function(){
                wpcfConditionalVerify(jQuery(this), jQuery(this).attr('name'), jQuery(this).val());
            });
        } else if (jQuery(this).hasClass('select')) {
            jQuery(this).bind('change', function(){
                wpcfConditionalVerify(jQuery(this), jQuery(this).attr('name'), jQuery(this).val());
            });
        } else if (jQuery(this).hasClass('wpcf-datepicker')) {
            jQuery(this).bind('wpcfDateBlur', function(){
                wpcfConditionalVerify(jQuery(this), jQuery(this).attr('name'), jQuery(this).val());
            });
        } else {
            jQuery(this).bind('blur', function(){
                wpcfConditionalVerify(jQuery(this), jQuery(this).attr('name'), jQuery(this).val());
            });
        }
        /*
         * Trigger initial check only once
         */
        if (triggered == false) {
            wpcfConditionalVerify(jQuery(this), jQuery(this).attr('name'), jQuery(this).val());
            triggered = true;
        }
    });
    /**
     * and bind to logic switcher
     */
    wpcfConditionalLogiButtonsBindClick();
    /**
     * check state
     */
    jQuery('.conditional-display-custom-use').each(function(){
        wpcfConditionalLogic(jQuery(this));
    });
}

function wpcfConditionalLogiButtonsBindClick()
{
    jQuery('.wpcf-cd-display-logic-button').each(function(){
        if ( jQuery(this).val() ) {
            return; // this is jQuery "continue"
        }
        jQuery(this).bind('click',function(){
            wpcfConditionalLogicButton(jQuery(this), true);
        });
        wpcfConditionalLogicButton(jQuery(this), false);
    });
}

function wpcfConditionalLogicButton(button, changeState)
{
    parent = jQuery(button).closest('.wpcf-cd-fieldset');
    el = jQuery('.conditional-display-custom-use', parent);
    if ( changeState ) {
        el.val(parseInt(el.val())?0:1);
    }
    wpcfConditionalLogic(el);
}

function wpcfConditionalVerify(object, name, value) {

    /*
     *
     * Skip post relationship entries
     * TODO Obsolete - all fields on screen processed
     */
    if (object.hasClass('wpcf-pr-binded')) {
        return false;
    }

    /*
    * Define Form
    *
    * Fields can depend on fields from other meta-groups.
    * So we should submit whole #post form without #_wpnonce
    */
    //    var form = object.parents('.postbox').find(':input');
    var form = jQuery('#post').find(':input').not('#_wpnonce');

    // Get group slug
    var group = object.parents('.postbox').attr('id').substr(11);

    // If triggered from relationship table send just row
    if (object.parents('.wpcf-pr-table-wrapper').length > 0) {
        form = object.parents('tr').find(':input');
        group = 'relationship';
    }

    // Check if form is found
    if (form.length > 0) {

        // Do AJAX call
        /*
         *
         *
         * AJAX should return JSON.execute JS script.
         * TODO Review safety
         */
        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            dataType: 'json',
            data: form.serialize()+'&wpcf_group='+group+'&wpcf_main_post_id='+jQuery('#post_ID').val()+'&action=wpcf_ajax&wpcf_action=cd_verify&_wpnonce='+wpcfConditionalVerify_nonce,
            cache: false,
            beforeSend: function() {
                typesSpinner.show(object);
            },
            success: function(data) {
                if (data != null) {

                    // See if data.execute exists and eval() it
                    // TODO Review safety
                    if (typeof data.execute != 'undefined'
                        && (typeof data.wpcf_nonce_ajax_callback != 'undefined'
                            && data.wpcf_nonce_ajax_callback == wpcf_nonce_ajax_callback)) {
                        eval(data.execute);
                    }
                }
                typesSpinner.hide(object);
                if (typeof typesValidation != 'undefined') {
                    typesValidation.setRules();
                }
            }
        });
    }
}

/**
 * Disables 'Add Condition' field.
 */
function wpcfDisableAddCondition(id) {
    jQuery('#wpcf_conditional_add_condition_field_'+id)
    .attr('disabled', 'disabled').unbind('click')
    .removeClass('wpcf-ajax-link').attr('onclick', '');
}

/**
 * Checks if group is valid
 */
function wpcfCdGroupVerify(object, group_id) {
    var form = jQuery('#post');
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        dataType: 'json',
        data: form.serialize()+'&group_id='+group_id+'&action=wpcf_ajax&wpcf_action=cd_group_verify&_wpnonce='+window.wpcfConditionalVerifyGroup,
        cache: false,
        beforeSend: function() {
            typesSpinner.show(object);
        },
        success: function(data) {
            if (data != null) {
                if (typeof data.execute != 'undefined'
                    && (typeof data.wpcf_nonce_ajax_callback != 'undefined'
                        && data.wpcf_nonce_ajax_callback == wpcf_nonce_ajax_callback)) {
                    eval(data.execute);
                }
            }
            typesSpinner.hide(object);
            if (typeof typesValidation != 'undefined') {
                typesValidation.setRules();
            }
        }
    });
}

/**
 * Trigger JS
 * TODO Check if obsolete
 * /
jQuery(document).ready(function(){
    jQuery('.wpcf-cd-fieldset, #wpcf-cd-group').each(function(){
        if (jQuery(this).find('.wpcf-cd-entry').length > 1) {
            jQuery(this).find('.toggle-cd').show();
            jQuery(this).find('.wpcf-cd-relation').show();
        }
    });
});

/**
 * Create conditional statement
 */
function wpcfCdCreateSummary(id)
{
    var condition = '';
    var skip = true;
    parent = jQuery('#'+id).closest('.wpcf-cd-fieldset');
    jQuery('.wpcf-cd-entry', parent).each(function(){
        if (!skip) {
            condition += jQuery(this).parent().parent().find('input[type=radio]:checked').val() + ' ';
        }
        skip = false;
        //                }
        var field = jQuery(this).find('.wpcf-cd-field :selected');

        condition += '($(' + jQuery(this).find('.wpcf-cd-field').val() + ')';
        condition += ' ' + jQuery(this).find('.wpcf-cd-operation').val();
        // Date
        if (field.hasClass('wpcf-conditional-select-date')) {
            var date = jQuery(this).find('.wpcf-custom-field-date');
            var month = date.children(':first');
            var mm = month.val();
            var jj = month.next().val();
            var aa = month.next().next().val();
            condition += ' DATE(' + jj + ',' + mm + ',' + aa + ')) ';
        } else {
            condition += ' ' + jQuery(this).find('.wpcf-cd-value').val() + ') ';
        }
    });
    jQuery('#'+id).val(condition);
}

function wpcfConditionalLogic(el)
{
    parent = el.closest('.wpcf-cd-fieldset');
    button = jQuery('input.wpcf-cd-display-logic-button', parent);
    button.val(button.data('wpcf-custom-logic-simple'));

    if ( parseInt(el.val()) ) {
        jQuery('.simple-logic', parent).hide();
        jQuery('.area-toggle-cd', parent).show();
        if ( parseInt( button.data('wpcf-custom-logic-change') ) ) {
            wpcfCdCreateSummary(button.data('wpcf-custom-summary'));
        }
    } else {
        button.val(button.data('wpcf-custom-logic-customize'));
        /**
         * turn on future change
         */
        button.data('wpcf-custom-logic-change', 1);
        jQuery('.area-toggle-cd',parent).hide();
        jQuery('.simple-logic',parent).show();
        if (jQuery('.wpcf-cd-entry', parent).length) {
            if (jQuery('.wpcf-cd-entries', parent).length > 1) {
                jQuery('.wpcf-cd-relation', parent).show();
            } else {
                jQuery('.wpcf-cd-relation', parent).hide();
            }
        } else {
            jQuery('.area-toggle-cd', parent).hide();
            jQuery('.wpcf-cd-relation', parent).hide();
        }
    }
    /**
     * handle "Data-dependent display filters" for groups
     */
    if ( 'wpcf-cd-group' == parent.attr('id') ) {
        jQuery('span.count', parent.closest('td')).html( '('+ jQuery('span.count', parent.closest('td')).data('wpcf-custom-logic') +')');
    if ( parseInt(el.val()) ) {
    } else {
        jQuery('span.count', parent.closest('td')).html('('+jQuery('.wpcf-cd-entry', parent).length+')');
    }
    }
}

/**
 * Add New Condition AJAX call
 */
function wpcfCdAddCondition(object, isGroup) {
    var wrapper = isGroup ? object.parents('#wpcf-cd-group') : object.parents('.wpcf-cd-fieldset');
    if (wrapper.find('.wpcf-cd-entry').length > 0) {
        wrapper.find('input.wpcf-cd-display-logic-button').show();
        if (wrapper.find('.wpcf-cd-entry').length > 1) {
            wrapper.find('.wpcf-cd-relation').show();
        } else {
            wrapper.find('.wpcf-cd-relation').hide();
        }
    }
    var url = object.attr('href')+'&count='+wrapper.find('input[type=hidden].wpcf-cd-count').val();
    if (isGroup) {
        url += '&group=1';
    } else {
        url += '&field='+wrapper.attr('id');
    }
    jQuery.get(url, function(data) {
        if (typeof data.output != 'undefined') {
            var condition = jQuery(data.output);
            wrapper.find('.wpcf-cd-entries').append(condition);
            var count = wrapper.find('input[type=hidden].wpcf-cd-count').val();
            wrapper.find('input[type=hidden].wpcf-cd-count').val(parseInt(count)+1);
            wpcfConditionalFormDateToggle(condition.find('.wpcf-cd-field'));
        }
    }, "json");

    /**
     * handle "Data-dependent display filters" for groups
     */
    if ( 'wpcf-cd-group' == wrapper.attr('id') ) {
        jQuery('span.count', wrapper.closest('td')).html('('+(parseInt(jQuery('.wpcf-cd-entry', wrapper).length)+1)+')');
    }

}

/**
 * Remove Condition AJAX call
 */
function wpcfCdRemoveCondition(object) {
    object.parent().fadeOut(function(){
        jQuery(this).remove();
    });
    var count = object.parent().parent().parent().find('input[type=hidden].wpcf-cd-count').val();
    object.parent().parent().parent().find('input[type=hidden].wpcf-cd-count').val(parseInt(count)-1);
    if (object.parent().parent().find('.wpcf-cd-entry').length < 3) {
        var customConditions = object.parent().parent().parent().find('.toggle-cd');
        customConditions.find('.textarea').val('');
    }
    /**
     * handle "Data-dependent display filters" for groups
     */
    var wrapper = object.closest('#wpcf-cd-group');
    if ( 'wpcf-cd-group' == wrapper.attr('id') ) {
        jQuery('span.count', wrapper.closest('td')).html('('+(parseInt(jQuery('.wpcf-cd-entry', wrapper).length)-1)+')');
    }
}

/**
 * Init Date conditional form check.
 */
function wpcfConditionalFormDateInit()
{
    jQuery('#wpcf-form-fields-main').on('change', '.wpcf-cd-field', function(){
        wpcfConditionalFormDateToggle(jQuery(this));
    }).find('.wpcf-cd-field').each(function(){
        wpcfConditionalFormDateToggle(jQuery(this));
    });
}

/**
 * Toggles input textfield to date inputs on Group edit screen.
 */
function wpcfConditionalFormDateToggle(object) {
    var show = object.find(':selected').hasClass('wpcf-conditional-select-date');
    var parent = object.parent();
    var select = parent.find('.wpcf-cd-operation');
    if (show) {
        parent.find('.wpcf-cd-value').hide();
        parent.find('.wpcf-custom-field-date').show();
        select.find("option[value='==='], option[value='!==']").attr('disabled', 'disabled');
        var selected = select.find(':selected').val()
        if (selected == '===') {
            select.val('=').trigger('click');
        } else if (selected == '!==') {
            select.val('<>').trigger('click');
        }
    } else {
        parent.find('.wpcf-cd-value').show();
        parent.find('.wpcf-custom-field-date').hide();
        select.find("option[value='==='], option[value='!=='], option[value='<>']")
        .removeAttr('disabled');
    }
}

/**
 * Checks if Date is valid on Group edit screen.
 */
function wpcfConditionalFormDateCheck() {
    var is_ok = true;
    jQuery('.wpcf-custom-field-date').each(function(index) {
        var field = jQuery(this).parent().find('.wpcf-cd-field :selected');
        if (field.hasClass('wpcf-conditional-select-date')) {
            var month = jQuery(this).children(':first');
            var mm = month.val();
            var jj = month.next().val();
            var aa = month.next().next().val();
            var newD = new Date( aa, mm - 1, jj);

            if ( newD.getFullYear() != aa || (1 + newD.getMonth()) != mm || newD.getDate() != jj) {
                jQuery(this).parent().find('.wpcf_custom_field_invalid_date').show();
                jQuery(this).parents('fieldset').children('.fieldset-wrapper').slideDown();
                is_ok = false;
            } else {
                jQuery(this).parent().find('.wpcf_custom_field_invalid_date').hide();
            }
        }
    });
    return is_ok;
}

/*
 * TODO Not used?
 */
window.wpcfConditional = new Array();
window.wpcfConditionalPassed = new Array();
window.wpcfConditionalHiddenFailed = new Array();
/*
 * Conditional JS.
 */
jQuery(document).ready(function(){
    // Trigger main func
    wpcfConditionalInit();
    // Form edit screen
    wpcfConditionalFormDateInit();
});

