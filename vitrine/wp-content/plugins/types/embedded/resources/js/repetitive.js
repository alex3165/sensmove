/* 
 * Repetitive fields JS.
 * 
 * Used on post edit pages.
 * 
 * @since Types 1.2
 */


jQuery(document).ready(function(){
    jQuery('.wpcf-repetitive-add').click(function(){
        
        var field_id = wpcfGetParameterByName('field_id_md5', jQuery(this).attr('href'));
        var query = jQuery(this).attr('href').replace('http://'+window.location.host+window.ajaxurl+'?', '') + '&count='+eval('window.wpcf_repetitive_count_'+field_id);
        var num = eval('window.wpcf_repetitive_count_'+field_id);
        var wrapper = jQuery(this).parents('.wpcf-repetitive-wrapper');
        var update = wrapper.find('.wpcf-repetitive-response');

        jQuery.ajax({
            url: jQuery(this).attr('href'),
            type: 'post',
            dataType: 'json',
            data: query+'&'+jQuery('[name^="wpcf"]').serialize(),
            cache: false,
            beforeSend: function() {
                update.prepend('<div class="wpcf-ajax-loading-small"></div>');
            },
            success: function(data) {
                if (data != null) {
                    wrapper.find('.wpcf-repetitive-sortable-wrapper').append(data.output);
                }
                update.find('.wpcf-ajax-loading-small').fadeOut(function(){
                    jQuery(this).remove();
                });
                
                
                /*
                 *
                 *
                 *
                 * I think we do not need this anymore
                 */
                eval('window.wpcf_repetitive_count_'+field_id+' += 1;');
            }
        });
        return false;
    });
    jQuery('.wpcf-repetitive-delete').live('click', function(){
        
        var wrapper = jQuery(this).parents('.wpcf-repetitive-sortable-wrapper');
        
        // Do not allow all fields to be deleted
        if (wrapper.find('.wpcf-repetitive-drag-and-drop').length < 2) {
            alert(window.wpcf_repetitive_last_warning);
            return false;
        }
        
        var warning = wpcfGetParameterByName('wpcf_warning', jQuery(this).attr('href'));
        if (warning != false) {
            var answer = confirm(warning);
            if (answer == false) {
                return false;
            }
        }
        var update = jQuery(this).parent().parent().find('.wpcf-repetitive-response');
        var object = jQuery(this);
        var vars = jQuery(this).attr('href').replace(window.ajaxurl+'?', '');
        var field_id = wpcfGetParameterByName('field_id_md5', jQuery(this).attr('href'));
        
        // New field
        if (jQuery(this).hasClass('wpcf-repetitive-delete-new')) {
            object.parents('.wpcf-repetitive-drag-and-drop').fadeOut(function(){
                jQuery(this).remove();
            });
        } else {
            jQuery.ajax({
                url: jQuery(this).attr('href'),
                type: 'post',
                dataType: 'json',
                data: vars+'&'+jQuery(this).parent().parent().find(':input').serialize(),
                cache: false,
                beforeSend: function() {
                    update.append('<div class="wpcf-ajax-loading-small"></div>');
                },
                success: function(data) {
                    object.parents('.wpcf-repetitive-drag-and-drop').fadeOut(function(){
                        jQuery(this).remove();
                    });
                }
            });
        }
        return false;
    });
    jQuery('.wpcf-repetitive-sortable-wrapper').sortable({
        revert: true,
        handle: '.wpcf-repetitive-drag',
        //        containment: 'parent'
        axis: "y"
    });
});