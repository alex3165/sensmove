/*
 * Group edit page JS
 *
 * This file should be used from now on as dedicated JS for group edit page.
 * Avoid adding new functionalities to basic.js
 *
 * Thanks!
 *
 * @since Types 1.1.5
 * @autor srdjan
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/embedded/resources/js/fields-form.js $
 * $LastChangedDate: 2015-03-25 12:38:40 +0000 (Wed, 25 Mar 2015) $
 * $LastChangedRevision: 1120400 $
 * $LastChangedBy: iworks $
 *
 */

jQuery(document).ready(function($){
    // Invoke drag on mouse enter
    $('#wpcf-fields-sortable').on('mouseenter', '.js-types-sortable', function(){
        if (!$(this).parent().hasClass('ui-sortable')) {
            $(this).parent().sortable({
                revert: true,
                handle: 'img.js-types-sort-button',
                start: function(e, ui){
                        ui.placeholder.height(ui.item.find('.wpcf-form-fieldset').height());
                    }
            });
        }
    });
    // Sort and Drag
    $('#wpcf-fields-sortable').sortable({
        cursor: 'ns-resize',
        axis: 'y',
        handle: 'img.wpcf-fields-form-move-field',
        forcePlaceholderSize: true,
        tolerance: 'pointer',
        start: function(e, ui){
                ui.placeholder.height(ui.item.height() + 23);
            }
    });

    $('.wpcf-fields-radio-sortable,.wpcf-fields-select-sortable').sortable({
        cursor: 'ns-resize',
        axis: 'y',
        handle: 'img.js-types-sort-button',
        start: function(e, ui){
                ui.placeholder.height(ui.item.height() - 2);
            }
    });

    $('.wpcf-fields-checkboxes-sortable').sortable({
        cursor: 'ns-resize',
        axis: 'y',
        handle: 'img.js-types-sort-button',
        start: function(e, ui){
                ui.placeholder.height(ui.item.height() + 13);
            }
    });

    $('[data-wpcf-type="checkbox"],[data-wpcf-type=checkboxes]').each( function() {
        $(this).bind('change', function() {
            wpcf_checkbox_value_zero($(this))
        });
        wpcf_checkbox_value_zero($(this));
    });

    /**
     * confitonal logic button close on group edit screen
     */
    $('#conditional-logic-button-ok').live('click', function(){
        $(this).parent().slideUp('slow', function() {
            $('#conditional-logic-button-open').fadeIn();
        });
        return false;
    });
});

function wpcf_checkbox_value_zero(field) {
    var passed = true;

    if (jQuery(field).hasClass('wpcf-value-store-error-error')) {
        jQuery(field).prev().remove();
        jQuery(field).removeClass('wpcf-value-store-error-error');
    }

    var value = jQuery(field).val();
    if (value === '') {
        passed = false;
        if (!jQuery(field).hasClass('wpcf-value-store-error-error')) {
            jQuery(field).before('<div class="wpcf-form-error">' + jQuery(field).data('required-message') + '</div>').addClass('wpcf-value-store-error-error');
            var legend = jQuery(field).closest('div.ui-draggable').children('fieldset').children('legend');
            if ( legend.hasClass('legend-collapsed') ) {
                legend.click();
            }
            var fieldset = jQuery(field).closest('fieldset');
            if ( jQuery('legend.legend-collapsed', fieldset ) ) {
                jQuery('legend.legend-collapsed', fieldset).click();
            }
        }
        jQuery(field).focus();
    }
    if (value === '0') {
        passed = false;
        if (!jQuery(field).hasClass('wpcf-value-store-error-error')) {
            jQuery(field).before('<div class="wpcf-form-error">' + jQuery(field).data('required-message-0') + '</div>').addClass('wpcf-value-store-error-error');
        }
        jQuery(field).focus();
    }
    return !passed;
}

