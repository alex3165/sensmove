/**
 *
 * Embedded JS.
 * For now full and embedded version use this script.
 * Before moving full-version-only code - make sure it's not needed here.
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/embedded/resources/js/basic.js $
 * $LastChangedDate: 2015-03-10 06:46:08 +0000 (Tue, 10 Mar 2015) $
 * $LastChangedRevision: 1109249 $
 * $LastChangedBy: iworks $
 *
 */

var wpcfFormGroupsSupportPostTypeState = new Array();
var wpcfFormGroupsSupportTaxState = new Array();
var wpcfFormGroupsSupportTemplatesState = new Array();

// TODO document this
var wpcfFieldsEditorCallback_redirect = null;

jQuery(document).ready(function(){
    /**
     * modal advertising
     */
    if(jQuery.isFunction(jQuery.fn.types_modal_box)) {
        jQuery('.wpcf-disabled-on-submit').types_modal_box();
    }
    jQuery('.wpcf-notif-description a').on('click', function() {
        jQuery(this).attr('target', '_blank');
    });
    //user suggestion
    if(jQuery.isFunction(jQuery.suggest)) {
        jQuery('.input').suggest("admin-ajax.php?action=wpcf_types_suggest_user&tax=post_tag", {
            multiple:false,
            multipleSep: ","
        });
    }
    // Only for adding group
    jQuery('.wpcf-fields-add-ajax-link').click(function(){
        jQuery.ajax({
            url: jQuery(this).attr('href'),
            cache: false,
            beforeSend: function() {
                jQuery('#wpcf-fields-under-title').hide();
                jQuery('#wpcf-ajax-response').addClass('wpcf-ajax-loading');
            },
            success: function(data) {
                jQuery('#wpcf-ajax-response').removeClass('wpcf-ajax-loading');
                jQuery('#wpcf-fields-sortable').append(data);
                jQuery('#wpcf-fields-sortable .ui-draggable:last').find('input:first').focus().select();
                var scrollToHeight = jQuery('#wpcf-fields-sortable .ui-draggable:last').offset();
                window.scrollTo(0, scrollToHeight.top);
                /**
                 * bind logic button if it is possible
                 */
                if ('function' == typeof(wpcfConditionalLogiButtonsBindClick)) {
                    wpcfConditionalLogiButtonsBindClick();
                }
            }
        });
        return false;
    });
    /*
     * Moved to fields-form.js
     */
    //    // Sort and Drag
    //    jQuery('#wpcf-fields-sortable').sortable({
    //        revert: true,
    //        handle: 'img.wpcf-fields-form-move-field',
    //        containment: 'parent'
    //    });
    //    jQuery('.wpcf-fields-radio-sortable').sortable({
    //        revert: true,
    //        handle: 'img.wpcf-fields-form-radio-move-field',
    //        containment: 'parent'
    //    });
    //    jQuery('.wpcf-fields-checkboxes-sortable').sortable({
    //        revert: true,
    //        handle: 'img.wpcf-fields-form-checkboxes-move-field',
    //        containment: 'parent'
    //    });
    //    jQuery('.wpcf-fields-select-sortable').sortable({
    //        revert: true,
    //        handle: 'img.wpcf-fields-form-select-move-field',
    //        containment: 'parent'
    //    });

    jQuery(".wpcf-form-fieldset legend").live('click', function() {
        jQuery(this).parent().children(".collapsible").slideToggle("fast", function() {
            var toggle = '';
            if (jQuery(this).is(":visible")) {
                jQuery(this).parent().children("legend").removeClass("legend-collapsed").addClass("legend-expanded");
                toggle = 'open';
            } else {
                jQuery(this).parent().children("legend").removeClass("legend-expanded").addClass("legend-collapsed");
                toggle = 'close';
            }
            // Save collapsed state
            // Get fieldset id
            var collapsed = jQuery(this).parent().attr('id');

            // For group form save fieldset toggle per group
            if (jQuery(this).parents('form').hasClass('wpcf-fields-form')) {
                // Get group id
                var group_id = false;
                if (jQuery('input[name="group_id"]').length > 0) {
                    group_id = jQuery('input[name="group_id"]').val();
                } else {
                    group_id = -1;
                }
                jQuery.ajax({
                    url: ajaxurl,
                    cache: false,
                    type: 'get',
                    data: 'action=wpcf_ajax&wpcf_action=group_form_collapsed&id='+collapsed+'&toggle='+toggle+'&group_id='+group_id+'&_wpnonce='+wpcf_nonce_toggle_group
                });
            } else {
                jQuery.ajax({
                    url: ajaxurl,
                    cache: false,
                    type: 'get',
                    data: 'action=wpcf_ajax&wpcf_action=form_fieldset_toggle&id='+collapsed+'&toggle='+toggle+'&_wpnonce'+wpcf_nonce_toggle_fieldset
                });
            }
        });
    });
    jQuery('.wpcf-forms-set-legend').live('keyup', function(){
        jQuery(this).parents('fieldset').find('.wpcf-legend-update').html(jQuery(this).val());
    });
    jQuery('.wpcf-form-groups-radio-update-title-display-value').live('keyup', function(){
        jQuery('#'+jQuery(this).attr('id')+'-display-value').prev('label').html(jQuery(this).val());
    });
    jQuery('.form-error').parents('.collapsed').slideDown();
    jQuery('.wpcf-form input').live('focus', function(){
        jQuery(this).parents('.collapsed').slideDown();
    });

    // Delete AJAX added element
    jQuery('.wpcf-form-fields-delete').live('click', function(){
        if (jQuery(this).attr('href') == 'javascript:void(0);') {
            jQuery(this).parent().fadeOut(function(){
                jQuery(this).remove();
            });
        }
    });

    // Check radio and select if same values
    // Check checkbox has a value to store
    jQuery('.wpcf-fields-form').submit(function(){
        wpcfLoadingButton();
        var passed = true;
        var checkedArr = new Array();
        jQuery('.wpcf-compare-unique-value-wrapper').each(function(index){
            var childID = jQuery(this).attr('id');
            checkedArr[childID] = new Array();
            jQuery(this).find('.wpcf-compare-unique-value').each(function(index, value){
                var parentID = jQuery(this).parents('.wpcf-compare-unique-value-wrapper').first().attr('id');
                var currentValue = jQuery(this).val();
                if (currentValue != ''
                    && jQuery.inArray(currentValue, checkedArr[parentID]) > -1) {

                    passed = false;
                    jQuery('#'+parentID).children('.wpcf-form-error-unique-value').remove();
                    jQuery('#'+parentID).append('<div class="wpcf-form-error-unique-value wpcf-form-error">'+wpcfFormUniqueValuesCheckText+'</div>');
                    jQuery(this).parents('fieldset').children('.fieldset-wrapper').slideDown();
                    jQuery(this).focus();
                }

                checkedArr[parentID].push(currentValue);
            });
        });
        if (passed == false) {
            // Bind message fade out
            jQuery('.wpcf-compare-unique-value').live('keyup', function(){
                jQuery(this).parents('.wpcf-compare-unique-value-wrapper').find('.wpcf-form-error-unique-value').fadeOut(function(){
                    jQuery(this).remove();
                });
            });
            wpcfLoadingButtonStop();
            return false;
        }
        // Check field names unique
        passed = true;
        checkedArr = new Array();
        jQuery('.wpcf-forms-field-name').each(function(index){
            var currentValue = jQuery(this).val().toLowerCase();
            if (currentValue != ''
                && jQuery.inArray(currentValue, checkedArr) > -1) {
                passed = false;
                if (!jQuery(this).hasClass('wpcf-name-checked-error')) {
                    jQuery(this).before('<div class="wpcf-form-error-unique-value wpcf-form-error">'+wpcfFormUniqueNamesCheckText+'</div>').addClass('wpcf-name-checked-error');
                }
                jQuery(this).parents('fieldset').children('.fieldset-wrapper').slideDown();
                jQuery(this).focus();

            }
            checkedArr.push(currentValue);
        });
        if (passed == false) {
            // Bind message fade out
            jQuery('.wpcf-forms-field-name').live('keyup', function(){
                jQuery(this).removeClass('wpcf-name-checked-error').prev('.wpcf-form-error-unique-value').fadeOut(function(){
                    jQuery(this).remove();
                });
            });
            wpcfLoadingButtonStop();
            return false;
        }

        // Check field slugs unique
        passed = true;
        checkedArr = new Array();
        /**
         * first fill array with defined, but unused fields
         */
        jQuery('#wpcf-form-groups-user-fields .wpcf-fields-add-ajax-link:visible').each(function(){
            checkedArr.push(jQuery(this).data('slug'));
        });
        jQuery('.wpcf-forms-field-slug').each(function(index){
            var currentValue = jQuery(this).val().toLowerCase();
            if (currentValue != ''
                && jQuery.inArray(currentValue, checkedArr) > -1) {
                passed = false;
                if (!jQuery(this).hasClass('wpcf-slug-checked-error')) {
                    jQuery(this).before('<div class="wpcf-form-error-unique-value wpcf-form-error">'+wpcfFormUniqueSlugsCheckText+'</div>').addClass('wpcf-slug-checked-error');
                }
                jQuery(this).parents('fieldset').children('.fieldset-wrapper').slideDown();
                jQuery(this).focus();

            }
            checkedArr.push(currentValue);
        });

        // Conditional check
        if (wpcfConditionalFormDateCheck() == false) {
            wpcfLoadingButtonStop();
            return false;
        }

        // check to make sure checkboxes have a value to save.
        jQuery('[data-wpcf-type=checkbox],[data-wpcf-type=checkboxes]').each(function () {
            if (wpcf_checkbox_value_zero(this)) {
                passed = false;
            }
        });

        if (passed == false) {
            // Bind message fade out
            jQuery('.wpcf-forms-field-slug').live('keyup', function(){
                jQuery(this).removeClass('wpcf-slug-checked-error').prev('.wpcf-form-error-unique-value').fadeOut(function(){
                    jQuery(this).remove();
                });
            });
            wpcfLoadingButtonStop();
            return false;
        }
    });

    /*
     * Generic AJAX call (link). Parameters can be used.
     */
    jQuery('.wpcf-ajax-link').live('click', function(){
        var callback = wpcfGetParameterByName('wpcf_ajax_callback', jQuery(this).attr('href'));
        var update = wpcfGetParameterByName('wpcf_ajax_update', jQuery(this).attr('href'));
        var updateAdd = wpcfGetParameterByName('wpcf_ajax_update_add', jQuery(this).attr('href'));
        var warning = wpcfGetParameterByName('wpcf_warning', jQuery(this).attr('href'));
        var thisObject = jQuery(this);
        var thisObjectTR = jQuery(this).closest('tr');
        if (warning != false) {
            var answer = confirm(warning);
            if (answer == false) {
                return false;
            }
        }
        jQuery.ajax({
            url: jQuery(this).attr('href'),
            type: 'get',
            dataType: 'json',
            cache: false,
            beforeSend: function() {
                if (update != false) {
                    jQuery('#'+update).html('').show().addClass('wpcf-ajax-loading-small');
                }
            },
            success: function(data) {
                if (data != null) {
                    if (typeof data.output != 'undefined') {
                        if (update != false) {
                            jQuery('#'+update).removeClass('wpcf-ajax-loading-small').html(data.output);
                        }
                        if (updateAdd != false) {
                            if (data.output.length < 1) {
                                jQuery('#'+updateAdd).fadeOut();
                            }
                            jQuery('#'+updateAdd).append(data.output);
                        }
                    }
                    if (typeof data.execute != 'undefined'
                        && (typeof data.wpcf_nonce_ajax_callback != 'undefined'
                            && data.wpcf_nonce_ajax_callback == wpcf_nonce_ajax_callback)) {
                        eval(data.execute);
                    }
                    if (typeof data.status != 'undefined' ) {
                        if ( 'inactive' == data.status ) {
                            thisObjectTR.addClass('status-inactive');

                        } else {
                            thisObjectTR.removeClass('status-inactive');
                        }
                    }
                    if (typeof data.status_label != 'undefined' ) {
                        jQuery('td.status', thisObjectTR).html(data.status_label);
                    }
                }
                if (callback != false) {
                    eval(callback+'(data, thisObject)');
                }
            }
        });
        wpcfLoadingButtonStop();
        return false;
    });

    jQuery('.wpcf-form-groups-support-post-type').each(function(){
        if (jQuery(this).is(':checked')) {
            window.wpcfFormGroupsSupportPostTypeState.push(jQuery(this).attr('id'));
        }
    });

    jQuery('.wpcf-form-groups-support-tax').each(function(){
        if (jQuery(this).is(':checked')) {
            window.wpcfFormGroupsSupportTaxState.push(jQuery(this).attr('id'));
        }
    });

    jQuery('.wpcf-form-groups-support-templates input').each(function(){
        if (jQuery(this).is(':checked')) {
            window.wpcfFormGroupsSupportTemplatesState.push(jQuery(this).attr('id'));
        }
    });

    // Add scroll to user created fieldset if necessary
    if (jQuery('#wpcf-form-groups-user-fields').length > 0) {
        var wpcfFormGroupsUserCreatedFieldsHeight = Math.round(jQuery('#wpcf-form-groups-user-fields').height());
        var wpcfScreenHeight = Math.round(jQuery(window).height());
        var wpcfFormGroupsUserCreatedFieldsOffset = jQuery('#wpcf-form-groups-user-fields').offset();
        /**
         * use jScrollPane only when have enough space
         */
        if ( wpcfScreenHeight -wpcfFormGroupsUserCreatedFieldsOffset.top > 100 ) {
            if (wpcfFormGroupsUserCreatedFieldsHeight+wpcfFormGroupsUserCreatedFieldsOffset.top > wpcfScreenHeight) {
                var wpcfFormGroupsUserCreatedFieldsHeightResize = Math.round(wpcfScreenHeight-wpcfFormGroupsUserCreatedFieldsOffset.top-40);
                jQuery('#wpcf-form-groups-user-fields').height(wpcfFormGroupsUserCreatedFieldsHeightResize);
                jQuery('#wpcf-form-groups-user-fields .fieldset-wrapper').height(wpcfFormGroupsUserCreatedFieldsHeightResize-15);
                jQuery('#wpcf-form-groups-user-fields .fieldset-wrapper').jScrollPane();
            }
            jQuery('.wpcf-form-fields-align-right').css('position', 'fixed');
        } else {
            jQuery('#wpcf-form-groups-user-fields').closest('.wpcf-form-fields-align-right').css('position', 'absolute' );
        }
    }

    // Types form
    jQuery('input[name="ct[public]"]').change(function(){
        if (jQuery(this).val() == 'public') {
            jQuery('#wpcf-types-form-visiblity-toggle').slideDown();
        } else {
            jQuery('#wpcf-types-form-visiblity-toggle').slideUp();
        }
    });
    jQuery('input[name="ct[rewrite][custom]"]').change(function(){
        if (jQuery(this).val() == 'custom') {
            jQuery('#wpcf-types-form-rewrite-toggle').slideDown();
        } else {
            jQuery('#wpcf-types-form-rewrite-toggle').slideUp();
        }
    });
    jQuery('.wpcf-tax-form input[name="ct[rewrite][enabled]"]').change(function(){
        if (jQuery(this).is(':checked')) {
            jQuery('#wpcf-types-form-rewrite-toggle').slideDown();
        } else {
            jQuery('#wpcf-types-form-rewrite-toggle').slideUp();
        }
    });
    /**
     * meta_box_cb
     */
    jQuery('.wpcf-tax-form input[name="ct[meta_box_cb][disabled]"]').change(function(){
        if (jQuery(this).is(':checked')) {
            jQuery('#wpcf-types-form-meta_box_cb-toggle').slideUp();
        } else {
            jQuery('#wpcf-types-form-meta_box_cb-toggle').slideDown();
        }
    });
    jQuery('input[name="ct[show_in_menu]"]').change(function(){
        if (jQuery(this).is(':checked')) {
            jQuery('#wpcf-types-form-showinmenu-toggle').slideDown();
        } else {
            jQuery('#wpcf-types-form-showinmenu-toggle').slideUp();
        }
    });
    jQuery('input[name="ct[query_var_enabled]"]').change(function(){
        if (jQuery(this).is(':checked')) {
            jQuery('#wpcf-types-form-queryvar-toggle').slideDown();
        } else {
            jQuery('#wpcf-types-form-queryvar-toggle').slideUp();
        }
    });

    jQuery('.wpcf-groups-form-ajax-update-custom_taxonomies-ok, .wpcf-groups-form-ajax-update-custom_post_types-ok, .wpcf-groups-form-ajax-update-templates-ok').click(function(){
        var count = 0;
        if (jQuery('.wpcf-groups-form-ajax-update-custom_taxonomies-ok').parent().find("input:checked").length > 0) {
            count += 1;
        }
        if (jQuery('.wpcf-groups-form-ajax-update-custom_post_types-ok').parent().find("input:checked").length > 0) {
            count += 1;
        }
        if (jQuery('.wpcf-groups-form-ajax-update-templates-ok').parent().find("input:checked").length > 0) {
            count += 1;
        }
        if (count > 1) {
            jQuery('#wpcf-fields-form-filters-association-form').show();
        } else {
            jQuery('#wpcf-fields-form-filters-association-form').hide();
        }
        wpcfFieldsFormFiltersSummary();
    });

    // Loading submit button
    jQuery('.wpcf-tax-form, .wpcf-types-form').submit(function(){
        wpcfLoadingButton();
    });
});

/**
 * Searches for parameter inside string ('arg', 'edit.php?arg=first&arg2=sec')
 */
function wpcfGetParameterByName(name, string){
    name = name.replace(/[\[]/,"\\\[").replace(/[\]]/,"\\\]");
    var regexS = "[\\?&]"+name+"=([^&#]*)";
    var regex = new RegExp( regexS );
    var results = regex.exec(string);
    if (results == null) {
        return false;
    } else {
        return decodeURIComponent(results[1].replace(/\+/g, " "));
    }
}

/**
 * AJAX delete elements from group form callback.
 */
function wpcfFieldsFormDeleteElement(data, element) {
    element.parent().fadeOut(function(){
        element.parent().remove();
    });
}

/**
 * Set count for options
 */
function wpcfFieldsFormCountOptions(obj) {
    var count = wpcfGetParameterByName('count', obj.attr('href'));
    count++;
    obj.attr('href',  obj.attr('href').replace(/count=.*/, 'count='+count));
}

function wpcfRefresh() {
    window.location.reload();
}

// Migrate checkboxes
function wpcfCbSaveEmptyMigrate(object, field_slug, total, wpnonce, action, metaType) {
    jQuery.ajax({
        url: ajaxurl+'?action=wpcf_ajax&wpcf_action=cb_save_empty_migrate&field='+field_slug+'&subaction='+action+'&total='+total+'&_wpnonce='+wpnonce+'&meta_type='+metaType,
        type: 'get',
        dataType: 'json',
        //            data: ,
        cache: false,
        beforeSend: function() {
            object.parent().parent().find('.wpcf-cb-save-empty-migrate-response').html('').show().addClass('wpcf-ajax-loading-small');
        },
        success: function(data) {
            if (data != null) {
                if (typeof data.output != 'undefined') {
                    object.parent().parent().find('.wpcf-cb-save-empty-migrate-response').removeClass('wpcf-ajax-loading-small').html(data.output);
                }
            }
        }
    });
}

function wpcfCbMigrateStep(total, offset, field_slug, wpnonce, metaType) {
    jQuery.ajax({
        url: ajaxurl+'?action=wpcf_ajax&wpcf_action=cb_save_empty_migrate&field='+field_slug+'&subaction=save&total='+total+'&offset='+offset+'&_wpnonce='+wpnonce+'&meta_type='+metaType,
        type: 'get',
        dataType: 'json',
        //            data: ,
        cache: false,
        beforeSend: function() {
        //            jQuery('#wpcf-cb-save-empty-migrate-response-'+field_slug).html(total+'/'+offset);
        },
        success: function(data) {
            if (data != null) {
                if (typeof data.output != 'undefined') {
                    jQuery('#wpcf-cb-save-empty-migrate-response-'+field_slug).html(data.output);
                }
            }
        }
    });
}

function wpcfCdCheckDateCustomized(object) {
    var show = false;
    object.parents('.fieldset-wrapper').find('.wpcf-cd-field option:selected').each(function(){
        if (jQuery(this).hasClass('wpcf-conditional-select-date')) {
            show = true;
        }
    });
    if (show) {
        object.parent().find('.wpcf-cd-notice-date').show();
    } else {
        object.parent().find('.wpcf-cd-notice-date').show();
    }
}

/**
 * Adds spinner graphics and disable button.
 */
function wpcfLoadingButton() {
    jQuery('.wpcf-disabled-on-submit').attr('disabled', 'disabled').each(function(){
        if ( 'undefined' == typeof(types_modal) ) {
            jQuery(this).after('<div id="'+jQuery(this).attr('id')+'-loading" class="wpcf-loading">&nbsp;</div>');
        }
    });
}
/**
 * Counter loading.
 */
function wpcfLoadingButtonStop() {
    jQuery('.wpcf-disabled-on-submit').removeAttr('disabled');
    jQuery('.wpcf-loading').fadeOut();
    //Fix https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/194177056/comments
    //type modal didnt disappeared
    jQuery('.types_modal_box').remove();
    jQuery('.types_block_page').remove();
}

/**
 * Editor callback func.
 */
function wpcfFieldsEditorCallback(fieldID , metaType, postID) {

    var colorboxWidth = 750 + 'px';

    if ( !( jQuery.browser.msie && parseInt(jQuery.browser.version) < 9 ) ) {
        var documentWidth = jQuery(document).width();
        if ( documentWidth < 750 ) {
            colorboxWidth = 600 + 'px';
        }
    }

    var url = ajaxurl+'?action=wpcf_ajax&wpcf_action=editor_callback&_typesnonce='+types.wpnonce+'&field_id='+fieldID+'&field_type='+metaType+'&post_id='+postID;

    // Check if shortcode passed
    if ( typeof arguments[3] === 'string' ) {
        // urlencode() PHP
        url += '&shortcode='+arguments[3];
    }

    jQuery.colorbox({
        href: url,
        iframe: true,
        inline : false,
        width: colorboxWidth,
        opacity: 0.7,
        closeButton: false
    });
}

/**
 * TODO Document this!
 * 1.1.5
 */
function wpcfFieldsEditorCallback_set_redirect(function_name, params) {
    wpcfFieldsEditorCallback_redirect = {
        'function' : function_name,
        'params' : params
    };
}
//Usermeta shortocde addon
function wpcf_showmore(show){
    if (show){
        jQuery('#specific_user_div').css('display','block');
        jQuery('#display_username_for_author').removeAttr('checked');
    }
    else{
        jQuery('#specific_user_div').css('display','none');
        jQuery('#display_username_for_suser').removeAttr('checked');
    }
}
//Usermeta shortocde addon
function hideControls(control_id1,control_id2){
    control_id1 = '#'+control_id1;
    control_id2 = '#'+control_id2;
    jQuery(control_id1).css('display','none');
    jQuery(control_id2).css('display','inline');
    jQuery(control_id2).focus();
}
