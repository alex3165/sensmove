jQuery(document).ready(function(){
    /*
     * 
     * 
     * 
     * This should be triggered in icl_editor_addon_plugin.js
     * TODO Why we do not have saving cookie in common?
     */
    // Set active editor
    //    window.wpcfActiveEditor = false;
    jQuery('.wp-media-buttons a, .wpcf-wysiwyg .editor_addon_wrapper .item, #postdivrich .editor_addon_wrapper .item').click(function(){
        /*
         * Changed to internal var
         * See icl_editor_addon_plugin.js jQuery(document).ready()
         */
        //        window.wpcfActiveEditor = jQuery(this).parents('.wpcf-wysiwyg, #postdivrich').find('textarea').attr('id');
        var wpcfActiveEditor = jQuery(this).parents('.wpcf-wysiwyg, #postdivrich').find('textarea').attr('id');
        document.cookie = "wpcfActiveEditor="+wpcfActiveEditor+"; expires=Monday, 31-Dec-2020 23:59:59 GMT; path="+wpcf_cookiepath+"; domain="+wpcf_cookiedomain+";";
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
            //            data: ,
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
                }
                if (callback != false) {
                    eval(callback+'(data, thisObject)');
                }
            }
        });
        return false;
    });
    
    jQuery('#post').submit(function(){
        
        //
        //
        //
        //
        // TODO Remove
        // Checking unique repetitive values removed
        // Types 1.2
        //
        //        var passed = true;
        //        var checkedArr = new Array();
        //        jQuery('.wpcf-repetitive-wrapper').each(function(){
        //            var parent = jQuery(this);
        //            var parentID = parent.attr('id');
        //            var childParentProcessed = false;
        //            checkedArr[parentID] = new Array();
        //            parent.find('.wpcf-repetitive').each(function(index, value){
        //                var toContinue = true;
        //                if (jQuery(this).hasClass('radio')) {
        //                    var childParent = jQuery(this).parents('.form-item-radios');
        //                    var childParentId = childParent.attr('id');
        //                    if (childParentProcessed != childParentId) {
        //                        var currentValue = childParent.find(':checked').val();
        //                        childParentProcessed = childParentId;
        //                    } else {
        //                        toContinue = false;
        //                    }
        //                } else {
        //                    var currentValue = jQuery(this).val();
        //                }
        //                if (toContinue) {
        //                    if (jQuery.inArray(currentValue, checkedArr[parentID]) > -1) {
        //                        passed = false;
        //                        if (jQuery(this).hasClass('wpcf-repetitive-error') == false) {
        //                            jQuery(this).before('<div class="wpcf-form-error-unique-value wpcf-form-error">'+wpcfFormRepetitiveUniqueValuesCheckText+'</div>').focus();
        //                            jQuery(this).addClass('wpcf-repetitive-error');
        //                        }
        //                    }
        //                    checkedArr[parentID].push(currentValue);
        //                }
        //            });
        //        });
        //        if (passed == false) {
        //            // Bind message fade out
        //            jQuery('.wpcf-repetitive').live('click', function(){
        //                jQuery(this).removeClass('wpcf-repetitive-error');
        //                jQuery(this).parents('.wpcf-repetitive-wrapper').find('.wpcf-form-error-unique-value').fadeOut(function(){
        //                    jQuery(this).remove();
        //                });
        //            });
        //            return false;
        //        }
        jQuery('#post .wpcf-cd-failed, #post .wpcf-cd-group-failed').remove();
    });
    
    jQuery('.wpcf-pr-save-all-link, .wpcf-pr-save-ajax').live('click', function(){
        jQuery(this).parents('.wpcf-pr-has-entries').find('.wpcf-cd-failed').remove();
    });
    
    // Trigger conditinal check
    //
    //First make repetitive wrapper main if any found
    jQuery('.wpcf-repetitive-wrapper').find('.wpcf-wrap').removeClass('wpcf-wrap');
    // Now show/hide wrappers
    jQuery('.wpcf-cd-passed').parents('.wpcf-repetitive-wrapper').show();
    jQuery('.wpcf-cd-failed').parents('.wpcf-repetitive-wrapper').hide();
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

var typesPostScreen = (function($){
    previewWarningMsg = '';
    function bindChange() {
        // Bind actions according to form element type
        $(document).ready(function(){
            $('[name^="wpcf["]').each(function() {
                var $this = $(this);
                if ($this.hasClass('radio') || $this.hasClass('checkbox')) {
                    $this.bind('click', previewWarningShow);
                } else if ($this.hasClass('select')) {
                    $this.bind('change', previewWarningShow);
                } else if ($this.hasClass('wpcf-datepicker')) {
                    $this.bind('wpcfDateBlur', previewWarningShow);
                } else {
                    $this.bind('blur', previewWarningShow);
                }
            });
            $('.js-wpt-repadd,.js-wpt-repdelete,.js-wpt-date-clear').on('click', previewWarningShow);
            $('.js-wpt-repdrag').on('mouseup', previewWarningShow);
        });
    }
    function previewWarning(header, content) {
        $(document).ready(function(){
            $('#post-preview').before('<i class="icon-warning-sign" id="types-preview-warning" data-header="'+header+'" data-content="'+content+'"></i>');
            bindChange();
        });
    }
    function previewWarningShow() {
        $('#types-preview-warning').show().on('click', function() {
                var $this = $(this);
                $this.pointer({
                content: '<h3>' + $this.data('header') + '</h3>' + '<p>' + $this.data('content') + '</p>',
                position: { edge: "right", align: "middle", offset: "0 0"}
            }).pointer('open');
        });
    }
    return {
        previewWarning: previewWarning
    };
})(jQuery);
