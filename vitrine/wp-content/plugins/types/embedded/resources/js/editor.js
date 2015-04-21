/*
 * Example of ted created by WPCF_Editor::renderTedSettings()
 */
/*var ted = {
    fieldID: null,
    fieldType: null,
    fieldTitle: null,
    params: [],
    repetitive: false,
    metaType: 'postmeta',
    postID: -1,
    supports:[]
};*/

/*
 * Knockout inner bindings for Editor modal form.
 */
var tedForm = {
    cb_mode: ko.observable(ted.params.mode || 'db'),
    cbs_mode: ko.observable(ted.params.mode || 'display_all'),
    date_mode: ko.observable(ted.params.style || 'text'),
    dateStyling: function() {
        var menu = tedFrame.menu();
        if (this.date_mode() == 'calendar') {
            menu.find('#menu-item-styling').show();
            menu.find('#types-modal-css-class').removeAttr('disabled');
        } else {
            menu.find('#menu-item-styling').hide();
            menu.find('#types-modal-css-class').attr('disabled', 'disabled');
        }
        return true;
    },
    imageResize: ko.observable(ted.params.resize != 'stretch' ? (ted.params.resize || 'proportional') : 'proportional'),
    image_size: ko.observable(ted.params.image_size || 'full'),
    imageKeepProportional: ko.observable(ted.params.resize != 'stretch'),
    imagePaddingColor: function() {
        return ted.params.padding_color == 'transparent' ? '#FFFFFF' : ted.params.padding_color;
    },
    imagePaddingTransparent: ko.observable(ted.params.padding_color == 'transparent'),
    imageUrl: ko.observable(ted.params.imageUrl || ''),
    imageUrlDisable: function() {
        var elements = tedFrame.form()
        .find('#image-title, #image-alt, #types-modal-css-class, #types-modal-style, #types-modal-output, #types-modal-showname, #image-alignment, #image-onload');
        if (this.imageUrl()) {
            elements.attr('disabled', 'disabled');
        } else {
            elements.not('.js-raw-disabled').removeAttr('disabled');
        }
        return true;
    },
    output: ko.observable(true),
    radio_mode: ko.observable(ted.params.mode || 'db'),
    radioPostType: ko.observable(ted.params.related_post || 'post'),
    raw: ko.observable(),
    rawDisableAll: function(data, event) {
        if (this.raw()) {
            // Disable enabled inputs and mark them
            tedFrame.form().find('div.js-raw-disable :enabled')
            .not('#types-modal-raw,#__types_nonce')
            .addClass('js-raw-disabled')
            .attr('disabled', 'disabled');
        } else {
            tedFrame.form().find('.js-raw-disabled').removeAttr('disabled')
            .removeClass('js-raw-disabled');
        }
        return true;
    },
    relatedPost: ko.observable(ted.params.post_id || 'current'),
    selectPostType: ko.observableArray([ted.params.related_post || 'post']),
    separator: ko.observable(ted.params.separator || ', '),
    showMenuStyling: function() {
        return ted.fieldType != 'date' || (ted.fieldType == 'date' && ted.params.style == 'calendar');
    },
    specificPostID: ko.observable(ted.params.specific_post_id || ''),
    supports: function(feature) {
        return jQuery.inArray(feature, ted.supports) != -1;
    },
    url_target: ko.observable(ted.params.target || '_self')
};

/*
 * Editor modal window control.
 */
var tedFrame = (function(window, $){

    var modal = $('#types-editor-modal');
    var modalMenu = modal.find('.types-media-menu');
    var modalMenuItems;
    var modalContent = modal.find('.types-media-frame-content');
    var modalContentTabs;
    var modalInsertButton = modal.find('.media-button-insert');
    var modalForm;
    var tabIndex = 0;

    function init()
    {

        modalForm = $('#types-editor-modal-form');

        modalMenuItems = modal.find('.types-media-menu a');
        modalContentTabs = modal.find('.types-media-frame-content .tab');

        // Bind menu tabbing
        bindTabbing();

        modalMenu.find('a:first-child').addClass('active');
        modalContent.find('.tab:eq(0)').show();
        modal.find('.media-modal-close, .media-button-cancel').click(function(){
            if (ted.callback == 'views_wizard') {
                window.parent.typesWPViews.wizardCancel();
                return false;
            }
            window.parent.jQuery.colorbox.close();
            return false;
        });

        // Bind submit
        modalInsertButton.click(function(){
            $('#types-editor-modal-form').trigger('submit');
            return false;
        });

        // Show modal content
        modal.css('visibility', 'visible');

        // Bind click to the Colorbox close button
        jQuery('.js-close-types-popup').on('click',function(){
            parent.jQuery.colorbox.close();
        });
    }

    function bindTabbing()
    {
        modalMenuItems.click(function(){
            modalMenuItems.removeClass('active');
            $(this).addClass('active');
            tabIndex = modalMenuItems.index($(this));
            modalContentTabs.hide();
            modalContent.find('.tab:eq('+tabIndex+')').show();
            return false;
        });
    }

    function resetMenu()
    {
        bindTabbing();
    }

    function insertShortcode( shortcode, esc_shortcode )
    {
        if (ted.callback == 'views_wizard') {
            window.parent.typesWPViews.wizardSendShortcode(shortcode);
            return true;
        }
        // Check if there is custom handler
        if (window.parent.wpcfFieldsEditorCallback_redirect) {
            eval(window.parent.wpcfFieldsEditorCallback_redirect['function'] + '(\''+esc_shortcode+'\', window.parent.wpcfFieldsEditorCallback_redirect[\'params\'])');
            // Reset redirect
            window.parent.wpcfFieldsEditorCallback_redirect = null;
        } else {
            // Use default handler
            
            window.parent.icl_editor.insert(shortcode);
        }
        window.parent.jQuery.colorbox.close();
    }

    return {
        init: init,
        close: insertShortcode,
        container: function() {
            return modal;
        },
        form: function() {
            return modalForm;
        },
        menu: function() {
            return modalMenu;
        }
    };
})(window, jQuery, undefined);

/*
 * WP Tooltip
 */
jQuery(document).ready(function($){


    /* Generic function to display native WP Tooltip */
    $(document).on('click', '.js-show-tooltip', function() {

        var $this = $(this);

        // default options
        var defaults = {
            edge: "left", // on which edge of the element tooltips should be shown: ( right, top, left, bottom )
            align: "middle", // how the pointer should be aligned on this edge, relative to the target (top, bottom, left, right, middle).
            offset: "15 0 " // pointer offset - relative to the edge
        };

        // custom options passed in HTML "data-" attributes
        var custom = {
            edge: $this.data('edge'),
            align: $this.data('align'),
            offset: $this.data('offset')
        };

        $this.pointer({
            content: '<h3>' + $this.data('header') + '</h3>' + '<p>' + $this.data('content') + '</p>',
            position: $.extend(defaults, custom) // merge defaults and custom attributes
        }).pointer('open');

    });
/* Generic function to display native WP Tooltip END */
});

ko.bindingHandlers.tedSupports = {
    init: function(element, valueAccessor, allBindingsAccessor, viewModel, bindingContext) {
        var feature = valueAccessor();
        if (!viewModel.supports(feature)) {
            jQuery(element).remove();
        }
    }
};

ko.applyBindings(tedForm);
jQuery(function(){
    tedFrame.init();
    parent.jQuery.colorbox.resize({
        innerHeight: jQuery('#wpcf-ajax').height()
    });
});
