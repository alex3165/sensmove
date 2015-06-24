
jQuery(document).ready(function(){
    
    // EXPAND/COLLAPSE (NOT USED)
    jQuery('.wpcf-access-edit-type').click(function(){
        jQuery(this).hide().parent().find('.wpcf-access-mode').slideToggle();
    });
    jQuery('.wpcf-access-edit-type-done').click(function(){
        jQuery(this).parents('.wpcf-access-mode').slideToggle().parent().find('.wpcf-access-edit-type').show();
    });
    
    // TOGGLE MODES DIVS
    jQuery('.wpcf-access-type-item .not-managed').click(function(){
        if (jQuery(this).is(':checked')
            && jQuery(this).parents('.wpcf-access-mode').find('.follow').is(':checked') == false) {
            wpcfAccessEnableInputs(jQuery(this), true);
        } else {
            wpcfAccessEnableInputs(jQuery(this), false);
        }
        
    });
    jQuery('.wpcf-access-type-item .follow').click(function(){
        if (jQuery(this).is(':checked') == false
            && jQuery(this).parents('.wpcf-access-mode').find('.not-managed').is(':checked')) {
            wpcfAccessEnableInputs(jQuery(this), true);
        } else {
            wpcfAccessEnableInputs(jQuery(this), false);
        }
    });
    jQuery('.wpcf-access-type-item .follow').each(function(){
        if (jQuery(this).is(':checked') == false
            && jQuery(this).parents('.wpcf-access-mode').find('.not-managed').is(':checked')) {
            wpcfAccessEnableInputs(jQuery(this), true);
        } else {
            wpcfAccessEnableInputs(jQuery(this), false);
        }
    });
    jQuery('.wpcf-access-type-item .not-managed').each(function(){
        if (jQuery(this).is(':checked')
            && jQuery(this).parents('.wpcf-access-mode').find('.follow').is(':checked') == false) {
            wpcfAccessEnableInputs(jQuery(this), true);
        } else {
            wpcfAccessEnableInputs(jQuery(this), false);
        }
        
    });
    
    
    jQuery('select[name^="wpcf_access_bulk_set"]').change(function(){
        var value = jQuery(this).val();
        if (value != '0') {
            jQuery(this).parent().find('select').each(function(){
                jQuery(this).val(value);
            });
        }
    });
    
    // ASSIGN LEVELS
    jQuery('.wpcf-access-change-level').live('click', function(){
        jQuery(this).hide().parent().find('.wpcf-access-custom-roles-select-wrapper').slideDown();
    });
    jQuery('.wpcf-access-change-level-cancel').live('click', function(){
        jQuery(this).parent().slideUp().parent().find('.wpcf-access-change-level').show();
    });
    jQuery('.wpcf-access-change-level-apply').live('click', function(){
        wpcfAccessApplyLevels(jQuery(this));
    });
    
    // SAVE SETTINGS
    jQuery('#wpcf_access_admin_form .wpcf-access-submit').click(function(){
        var object = jQuery(this);
        var img = jQuery(this).next();
        jQuery('#wpcf_access_admin_form').find('.dep-message').hide();
        img.css('visibility', 'visible').animate({
            opacity: 1
        }, 0);
        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            //            dataType: 'json',
            data: jQuery('#wpcf_access_admin_form').serialize(),
            cache: false,
            beforeSend: function() {},
            success: function(data) {
                img.animate({
                    opacity: 0
                }, 200);
                object.parents('.wpcf-access-type-item').css("background-color", "#FFFF9C")
                .animate({
                    backgroundColor: "##F7F7F7"
                }, 1500);
            }
        });
        return false;
    });
    
    
    // NEW ROLE
    jQuery('#wpcf-access-new-role .button').click(function(){
        jQuery('#wpcf-access-new-role .toggle').show().find('.input').val('').focus();
        jQuery('#wpcf-access-new-role .ajax-response').html('');
    });
    jQuery('#wpcf-access-new-role .cancel').click(function(){
        jQuery('#wpcf-access-new-role .confirm').attr('disabled', 'disabled');
        jQuery('#wpcf-access-new-role .toggle').hide().find('.input').val('');
        jQuery('#wpcf-access-new-role .ajax-response').html('');
    });
    jQuery('#wpcf-access-new-role .confirm').click(function(){
        if (jQuery(this).attr('disabled')) {
            return false;
        }
        jQuery(this).attr('disabled', 'disabled');
        jQuery('#wpcf-access-new-role .img-waiting').show();
        jQuery('#wpcf-access-new-role .ajax-response').html('');
        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            dataType: 'json',
            data: 'action=wpcf_access_add_role&role='+jQuery('#wpcf-access-new-role .input').val(),
            cache: false,
            beforeSend: function() {},
            success: function(data) {
                jQuery('#wpcf-access-new-role .img-waiting').hide();
                if (data.error == 'false') {
                    jQuery('#wpcf-access-new-role .input').val('');
                    jQuery('#wpcf-access-custom-roles-wrapper').html(data.output);
                } else {
                    jQuery('#wpcf-access-new-role .ajax-response').html(data.output);
                }
            }
        });
    });
    jQuery('#wpcf-access-new-role .input').keyup(function(){
        jQuery('#wpcf-access-new-role .ajax-response').html('');
        if (jQuery(this).val().length > 4) {
            jQuery('#wpcf-access-new-role .confirm').removeAttr('disabled');
        } else {
            jQuery('#wpcf-access-new-role .confirm').attr('disabled', 'disabled');
        }
    });
    
    // DELETE ROLE
    jQuery('#wpcf-access-delete-role').live('click', function() {
        jQuery(this).next().show();
    });
    jQuery('.wpcf-access-reassign-role-popup .confirm').live('click', function() {
        if (jQuery(this).attr('disabled')) {
            return false;
        }
        jQuery('.wpcf-access-reassign-role-popup .img-waiting').show();
        jQuery.ajax({
            url: ajaxurl,
            type: 'post',
            dataType: 'json',
            data: 'action=wpcf_access_delete_role&'+jQuery(this).parents('.wpcf-access-reassign-role-popup').find(':input').serialize(),
            cache: false,
            beforeSend: function() {},
            success: function(data) {
                jQuery('.wpcf-access-reassign-role-popup .img-waiting').hide();
                if (data.error == 'false') {
                    tb_remove();
                    jQuery('#wpcf-access-custom-roles-wrapper').html(data.output);
                } else {
                    jQuery('.wpcf-access-reassign-role-popup .ajax-response').html(data.output);
                }
            }
        });
    });
    jQuery('.wpcf-access-reassign-role-popup select').change(function(){
        jQuery(this).parents('.wpcf-access-reassign-role-popup').find('.confirm').removeAttr('disabled');
    });
    
    // ADD DEPENDENCY MESSAGE
    jQuery('.wpcf-access-type-item').find('.wpcf-access-mode').prepend('<div class="dep-message" style="display:none;"></div>');
    
    // Disable admin checkboxes
    jQuery(':checkbox[value="administrator"]').attr('disabled', 'disabled').attr('readonly', 'readonly').attr('checked', 'checked');
});

function wpcfAccessReset(object) {
    jQuery('#wpcf_access_admin_form').find('.dep-message').hide();
    jQuery.ajax({
        url: object.attr('href')+'&button_id='+object.attr('id'),
        type: 'get',
        dataType: 'json',
        //            data: ,
        cache: false,
        beforeSend: function() {},
        success: function(data) {
            if (data != null) {
                if (typeof data.output != 'undefined' && typeof data.button_id != 'undefined') {
                    var parent = jQuery('#'+data.button_id).parent();
                    jQuery.each(data.output, function(index, value) {
                        object = parent.find('input[id*="_permissions_'+index+'_'+value+'_role"]');
                        object.trigger('click').attr('checked', 'checked');
                    });
                }
            }
        }
    });
    return false;
}

function wpcfAccessApplyLevels(object) {
    jQuery.ajax({
        url: ajaxurl,
        type: 'post',
        dataType: 'json',
        data: object.parent().find('.wpcf-access-custom-roles-select').serialize()+'&_wpnonce='+wpcf_nonce_ajax_callback+'&action=wpcf_access_ajax_set_level',
        cache: false,
        beforeSend: function() {
            jQuery('#wpcf-access-custom-roles-table-wrapper').css('opacity', 0.5);
        },
        success: function(data) {
            if (data != null) {
                if (typeof data.output != 'undefined') {
                    //                    jQuery('#wpcf-access-custom-roles-wrapper').css('opacity', 1).replaceWith(data.output);
                    window.location = 'admin.php?page=wpcf-access#custom-roles';
                    window.location.reload(true);
                }
            }
        }
    });
    return false;
}

function wpcfAccessEnable(object) {
    if ((object.is('input[type="checkbox"]') && object.is(':checked')) || (object.is('input[type="radio"]') && object.val() != 'not_managed')) {
        wpcfAccessEnableInputs(object, true);
    } else {
        wpcfAccessEnableInputs(object, false);
    }
}

function wpcfAccessEnableInputs(object, check) {
    if (check) {
        object.parent().find('.wpcf-enable-set').val(object.val());
        object.parent().parent().parent().find('table input, .wpcf-access-submit, .wpcf-access-reset').not(':checkbox[value="administrator"]').removeAttr('readonly').removeAttr('disabled');
        object.parent().parent().parent().find('.warning-fallback').hide();
    } else {
        object.parent().find('.wpcf-enable-set').val('not_managed');
        object.parent().parent().parent().find('table input, .wpcf-access-reset').attr('readonly', 'readonly').attr('disabled', 'disabled');
        object.parent().parent().parent().find('.warning-fallback').show();
    }
}

function wpcfAccessAutoThick(object, cap, name) {
    var thick = new Array();
    var thickOff = new Array();
    var active = object.is(':checked');
    var role = object.val();
    var cap_active = 'wpcf_access_dep_true_'+cap;
    var cap_inactive = 'wpcf_access_dep_false_'+cap;
    var message = new Array();
    
    if (active) {
        if (typeof window[cap_active] != 'undefined') {
            thick = thick.concat(window[cap_active]);
        }
    } else {
        if (typeof window[cap_inactive] != 'undefined') {
            thickOff = thickOff.concat(window[cap_inactive]);
        }
    }
    
    // FIND DEPENDABLES
    //
    // Check ONs
    jQuery.each(thick, function(index, value){
        object.parents('table').find(':checkbox').each(function(){
            if (jQuery(this).attr('id') != object.attr('id')) {
                if (jQuery(this).val() == role
                    && jQuery(this).hasClass('wpcf-access-'+value)) {
                    // Mark for message
                    if (jQuery(this).is(':checked') == false) {
                        message.push(jQuery(this).data('wpcfaccesscap'));
                    }
                    // Set element form name
                    jQuery(this).attr('checked', 'checked')
                    .attr('name', jQuery(this).data('wpcfaccessname'));
                    wpcfAccessThickTd(jQuery(this), 'prev', true);
                }
            }
        });
    });
    // Check OFFs
    jQuery.each(thickOff, function(index, value){
        object.parents('table').find(':checkbox').each(function(){
            if (jQuery(this).attr('id') != object.attr('id')) {
                if (jQuery(this).val() == role
                    && jQuery(this).hasClass('wpcf-access-'+value)) {
                    // Mark for message
                    if (jQuery(this).is(':checked')) {
                        message.push(jQuery(this).data('wpcfaccesscap'));
                    }
                    jQuery(this).removeAttr('checked').attr('name', 'dummy');
                    // Set element form name
                    var prevSet = jQuery(this).parent().prev().find(':checkbox');
                    if (prevSet.is(':checked')) {
                        prevSet.attr('checked', 'checked').attr('name', prevSet.data('wpcfaccessname'));
                    }
                    wpcfAccessThickTd(jQuery(this), 'next', false);
                }
            }
        });
    });
    
    // Thick all checkboxes
    wpcfAccessThickTd(object, 'next', false);
    wpcfAccessThickTd(object, 'prev', true);
    
    // SET NAME
    // 
    // Find previous if switched off
    if (object.is(':checked')) {
        object.attr('name', name);
    } else {
        object.attr('name', 'dummy');
        object.parent().prev().find(':checkbox').attr('checked', 'checked').attr('name', name);
    }
    // Set true if admnistrator
    if (object.val() == 'administrator') {
        object.attr('name', name).attr('checked', 'checked');
    }
    
    // Alert
    wpcfAccessDependencyMessageShow(object, cap, message, active);
}

function wpcfAccessThickTd(object, direction, checked) {
    if (direction == 'next') {
        var cbs = object.parent().nextAll('td').find(':checkbox');
    } else {
        var cbs = object.parent().prevAll('td').find(':checkbox');
    }
    
    if (checked) {
        cbs.each(function(){
            jQuery(this).attr('checked', 'checked').attr('name', 'dummy');
        });
    } else {
        cbs.each(function(){
            jQuery(this).removeAttr('checked').attr('name', 'dummy');
        });
    }
}

function wpcfAccessDependencyMessageShow(object, cap, caps, active) {
    var update_message = wpcfAccessDependencyMessage(cap, caps, active);
    var update = object.parents('.wpcf-access-type-item').find('.dep-message');
    update.hide().html('');
    if (update_message != false) {
        update.html(update_message).show();
    }
}

function wpcfAccessDependencyMessage(cap, caps, active) {
    var active_pattern_singular = window['wpcf_access_dep_active_messages_pattern_singular'];
    var active_pattern_plural = window['wpcf_access_dep_active_messages_pattern_plural'];
    var inactive_pattern_singular = window['wpcf_access_dep_inactive_messages_pattern_singular'];
    var inactive_pattern_plural = window['wpcf_access_dep_inactive_messages_pattern_singular'];
    var caps_titles = new Array();
    var update_message = false;
    
    jQuery.each(caps, function(index, value){
        if (active) {
            var key = window['wpcf_access_dep_true_'+cap].indexOf(value);
            caps_titles.push(window['wpcf_access_dep_true_'+cap+'_message'][key]);
        } else {
            var key = window['wpcf_access_dep_false_'+cap].indexOf(value);
            caps_titles.push(window['wpcf_access_dep_false_'+cap+'_message'][key]);
        }
    });

    if (caps.length > 0) {
        if (active) {
            if (caps.length < 2) {
                var update_message = active_pattern_singular.replace('%cap', window['wpcf_access_dep_'+cap+'_title']);
            } else {
                var update_message = active_pattern_plural.replace('%cap', window['wpcf_access_dep_'+cap+'_title']);
            }
        } else {
            if (caps.length < 2) {
                var update_message = inactive_pattern_singular.replace('%cap', window['wpcf_access_dep_'+cap+'_title']);
            } else {
                var update_message = inactive_pattern_plural.replace('%cap', window['wpcf_access_dep_'+cap+'_title']);
            }
        }
        update_message = update_message.replace('%dcaps', caps_titles.join('\', \''));
    }
    return update_message;
}