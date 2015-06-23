var WPV_Toolset = WPV_Toolset  || {};
if ( typeof WPV_Toolset.CodeMirror_instance === "undefined" ) {
	WPV_Toolset.CodeMirror_instance = [];
}

if ( WPV_Toolset.add_qt_editor_buttons !== 'function' ) {
    WPV_Toolset.add_qt_editor_buttons = function( qt_instance, editor_instance ) {
        QTags._buttonsInit();
		WPV_Toolset.CodeMirror_instance[qt_instance.id] = editor_instance;
		
        for ( var button_name in qt_instance.theButtons ) {
			if ( qt_instance.theButtons.hasOwnProperty( button_name ) ) {
				qt_instance.theButtons[button_name].old_callback = qt_instance.theButtons[button_name].callback;
                if ( qt_instance.theButtons[button_name].id == 'img' ){
                    qt_instance.theButtons[button_name].callback = function( element, canvas, ed ) {
                    var t = this,
                    id = jQuery( canvas ).attr( 'id' ),
                    selection = WPV_Toolset.CodeMirror_instance[id].getSelection(),
                    e = "http://",
                    g = prompt( quicktagsL10n.enterImageURL, e ),
                    f = prompt( quicktagsL10n.enterImageDescription, "" );
                    t.tagStart = '<img src="'+g+'" alt="'+f+'" />';
                    selection = t.tagStart;
                    t.closeTag( element, ed );
                    WPV_Toolset.CodeMirror_instance[id].replaceSelection( selection, 'end' );
                    WPV_Toolset.CodeMirror_instance[id].focus();
                    }
                } else if ( qt_instance.theButtons[button_name].id == 'close' ) {
                    
                } else if ( qt_instance.theButtons[button_name].id == 'link' ) {
					var t = this;
					qt_instance.theButtons[button_name].callback = 
                        function ( b, c, d, e ) {
							activeUrlEditor = c;var f,g=this;return"undefined"!=typeof wpLink?void wpLink.open(d.id):(e||(e="http://"),void(g.isOpen(d)===!1?(f=prompt(quicktagsL10n.enterURL,e),f&&(g.tagStart='<a href="'+f+'">',a.TagButton.prototype.callback.call(g,b,c,d))):a.TagButton.prototype.callback.call(g,b,c,d)))
						} 
					;
					jQuery( '#wp-link-submit' ).off();
					jQuery( '#wp-link-submit' ).on( 'click', function() {
						var id = jQuery( activeUrlEditor ).attr('id'),
						selection = WPV_Toolset.CodeMirror_instance[id].getSelection(),
						target = '';
						if ( jQuery( '#link-target-checkbox' ).prop('checked') ) {
						  target = '_blank';
						}
						html = '<a href="' + jQuery('#url-field').val() + '"';
						title = '';
						if ( jQuery( '#link-title-field' ).val() ) {
							title = jQuery( '#link-title-field' ).val().replace( /</g, '&lt;' ).replace( />/g, '&gt;' ).replace( /"/g, '&quot;' );
							html += ' title="' + title + '"';
						}
						if ( target ) {
							html += ' target="' + target + '"';
						}
						html += '>';
						if ( selection === '' ) {
							html += title;
						} else {
							html += selection;
						}
						html += '</a>';
						t.tagStart = html;
						selection = t.tagStart;
						WPV_Toolset.CodeMirror_instance[id].replaceSelection( selection, 'end' );
						WPV_Toolset.CodeMirror_instance[id].focus();
						jQuery( '#wp-link-backdrop,#wp-link-wrap' ).hide();
						jQuery( document.body ).removeClass( 'modal-open' );
						return false;
                    });
                } else {
                    qt_instance.theButtons[button_name].callback = function( element, canvas, ed ) {                    
                        var id = jQuery( canvas ).attr( 'id' ),
                        t = this,
                        selection = WPV_Toolset.CodeMirror_instance[id].getSelection();
						if ( selection.length > 0 ) { 
							if ( !t.tagEnd ) {
								selection = selection + t.tagStart;
							} else {
								selection = t.tagStart + selection + t.tagEnd;
							}
						} else {
							if ( !t.tagEnd ) {
								selection = t.tagStart;
							} else if ( t.isOpen( ed ) === false ) {
								selection = t.tagStart;
								t.openTag( element, ed );
							} else {
								selection = t.tagEnd;
								t.closeTag( element, ed );
							}
						}
                        WPV_Toolset.CodeMirror_instance[id].replaceSelection(selection, 'end');
                        WPV_Toolset.CodeMirror_instance[id].focus();
                    }
                }
			}
		}
    }
}

var iclEditorWidth = 550;
var iclEditorWidthMin = 195;
var iclEditorHeight = 420;
var iclEditorHeightMin = 195;
var iclCodemirror = new Array();

jQuery(document).ready(function(){
    /*
     * Set active editor
     * Important when switching between editor instances.
     *
     * Used on WP editor, Types WYSIWYG, Views Filter Meta HTML,
     * Views Layout Meta HTML, CRED form.
     */
    window.wpcfActiveEditor = 'content';
    jQuery('.wp-media-buttons a, .wpcf-wysiwyg .editor_addon_dropdown .item, .wpt-wysiwyg .editor_addon_dropdown .item, #postdivrich .editor_addon_dropdown .item, #wpv_filter_meta_html_admin_edit .item, #wpv_layout_meta_html_admin_edit .item').on('click', function(){
        window.wpcfActiveEditor = jQuery(this).parents('.wpt-wysiwyg, .wpcf-wysiwyg, #postdivrich, #wpv_layout_meta_html_admin, #wpv_filter_meta_html_admin')
        .find('textarea#content, textarea.wpcf-wysiwyg, textarea.wpt-wysiwyg, textarea#wpv_layout_meta_html_content, textarea#wpv_filter_meta_html_content').attr('id');

    /*
         *
         * TODO 1.3 Why we do not have saving cookie in common?
         */
    //        document.cookie = "wpcfActiveEditor="+window.wpcfActiveEditor+"; expires=Monday, 31-Dec-2020 23:59:59 GMT; path="+wpcf_cookiepath+"; domain="+wpcf_cookiedomain+";";
    });
    // CRED notifications V icon - set active editor - needed for notifications V icons
    jQuery(document).on('click','input[id^="credmailsubject"]', function(){
	    window.wpcfActiveEditor = jQuery(this).attr('id');
    });
    jQuery(document).on('click', 'div[id^="wp-credmailbody"] .editor_addon_dropdown img', function(){
	    window.wpcfActiveEditor = jQuery(this).parents('div[id^="wp-credmailbody"]').find('textarea[id^="credmailbody"]').attr('id');
    });
    /*
     * Handle the "Add Field" boxes - some layout changes.
     */
    jQuery('.wpv_add_fields_button').on('click', function(e) {


        // Set dropdown
        var dropdown_list = jQuery('#add_field_popup .editor_addon_dropdown');

        if (dropdown_list.css('visibility') == 'hidden') {

            /*
             * Specific for 'Add Field'
             * Make changes before setting popup
             */
            jQuery('#add_field_popup .editor_addon_dropdown .vicon').css('display', 'none');
            jQuery('#add_field_popup').show();

            // Place it above button
            dropdown_list.css('margin', '-25px 0 0 -15px');
	    	dropdown_list.css('right', '0'); // needed for RTL
            var pos = jQuery('.wpv_add_fields_button').position();
            dropdown_list.css('top', pos.top + jQuery('.wpv_add_fields_button').height() - iclEditorHeight + 'px');
            dropdown_list.css('left', pos.left + jQuery('.wpv_add_fields_button').width() + 'px');

            // Toggle
            icl_editor_popup(dropdown_list);
			jQuery(dropdown_list).find('.search_field').focus();

        } else {
            dropdown_list.css('visibility', 'hidden');
        }
    });


    /*
     *
     * This manages clicking on dropdown icon
     */
    jQuery('#post').on('click', '.editor_addon_dropdown img', function(e){

        // Set dropdown
        var drop_down = jQuery(this).parent().find('.editor_addon_dropdown');

        if (drop_down.css('visibility') == 'hidden') {

            // Hide top links if div too small
            wpv_hide_top_groups(jQuery(this).parent());

            // Popup
            icl_editor_popup(drop_down);
			jQuery(drop_down).find('.search_field').focus();

        } else {
            // Hide all
			icl_editor_hide_popup();
        }


        // Bind close on iFrame click (it's loaded now)
        /*
         *
         * TODO Check and document this
         * SRDJAN I do not understand this one...
         */
        jQuery('#content_ifr').contents().bind('click', function(e) {
			icl_editor_hide_popup();
		});


        // Bind Escape
        jQuery(document).bind('keyup', function(e) {
            if (e.keyCode == 27) {
				icl_editor_hide_popup();
				jQuery(this).unbind(e);
            }
        });


    });


     /*
     *
     * This manages clicking on dropdown V icon on views edit screen
     */
    jQuery(document).on('click', '.js-code-editor-toolbar-button-v-icon', function(e){

		// find which text area we are inserting into
		var code_editor = jQuery(this).parents('.code-editor');
		if (code_editor.length == 0) {
			// Could be a content template
			code_editor = jQuery(this).parents('.wpv-ct-inline-edit');
		}
		var text_area_id = code_editor.find('textarea').attr('id');
		// Set the active editor so that Types shortcodes can be inserted.
		window.wpcfActiveEditor = text_area_id;


        // Set dropdown
        var drop_down = jQuery(this).parent().find('.editor_addon_dropdown');

        if ( drop_down.css('visibility') === 'hidden' ) {

            // Hide top links if div too small
            wpv_hide_top_groups(jQuery(this).parent());

            // Popup
            icl_editor_popup(drop_down);

			jQuery(drop_down).find('.search_field').focus();
			
			// Make sure the dialog fits on the screen when used in
			// Layouts for the Post Content dialog
			if (jQuery(drop_down).closest('#ddl-default-edit').length > 0) {
				var dialog_bottom = jQuery(drop_down).offset().top + jQuery(drop_down).height();
				dialog_bottom -= jQuery(window).scrollTop();
				var window_height = jQuery(window).height();
				
				if (dialog_bottom > window_height) {
					var new_top = window_height - jQuery(drop_down).height() - 80;
					if (new_top < 0) {
						new_top = 0;
					}
					jQuery(drop_down).animate({top : new_top}, 200);
				}
			}
        }

        // Bind close on iFrame click (it's loaded now)
        /*
         *
         * TODO Check and document this
         * SRDJAN I do not understand this one...
         */
        jQuery('#content_ifr').contents().bind('click', function(e) {
			icl_editor_hide_popup();
        });


        // Bind Escape
        jQuery(document).bind('keyup', function(e) {
            if (e.keyCode == 27) {
				icl_editor_hide_popup();
                jQuery(this).unbind(e);
            }
        });


    });

    /*
     *
     * This manages clicking on dropdown V icon
     */
    jQuery(document).on('click', '.js-wpv-shortcode-post-icon-wpv-views', function(e){

        // Set dropdown
        var drop_down = jQuery(this).parent().find('.js-editor_addon_dropdown-wpv-views');
        if ( drop_down.css('visibility') === 'hidden' ) {
            // Hide top links if div too small
            wpv_hide_top_groups(jQuery(this).parent());
            // Popup
            icl_editor_popup(drop_down);

			jQuery(drop_down).find('.search_field').focus();

        }

        // Bind close on iFrame click (it's loaded now)
        /*
         *
         * TODO Check and document this
         * SRDJAN I do not understand this one...
         */
        jQuery('#content_ifr').contents().bind('click', function(e) {
			icl_editor_hide_popup();
        });


        // Bind Escape
        jQuery(document).bind('keyup', function(e) {
            if (e.keyCode == 27) {
				icl_editor_hide_popup();
                jQuery(this).unbind(e);
            }
        });


    });

    /*
     *
     * This manages clicking on dropdown T icon
     */
    jQuery(document).on('click', '.js-wpv-shortcode-post-icon-types', function(e){
        // Set dropdown
       // console.log('js-wpv-shortcode-post-icon-types');
        var drop_down = jQuery(this).parent().find('.js-editor_addon_dropdown-types');

        if ( drop_down.css('visibility') === 'hidden' ) {
            // Hide top links if div too small
            wpv_hide_top_groups(jQuery(this).parent());
            // Popup
            icl_editor_popup(drop_down);

			jQuery(drop_down).find('.search_field').focus();

        }

        // Bind close on iFrame click (it's loaded now)
        /*
         *
         * TODO Check and document this
         * SRDJAN I do not understand this one...
         */
        jQuery('#content_ifr').contents().bind('click', function(e) {
			icl_editor_hide_popup();
        });


        // Bind Escape
        jQuery(document).bind('keyup', function(e) {
            if (e.keyCode == 27) {
				icl_editor_hide_popup();
                jQuery(this).unbind(e);
            }
        });


    });


    /*
     *
     *
     * Trigger close action
     */
    jQuery(document).on('click', '.editor_addon_dropdown .item, .editor_addon_dropdown .close', function(e){
		icl_editor_hide_popup();
    });

    /*
     *
     * Direct links
     */

    jQuery(document).on('click','.editor-addon-top-link', function() {
        var scrollTargetDiv = jQuery(this).parents('.editor_addon_dropdown_content');
        var target = jQuery(this).closest('.editor_addon_dropdown_content').find('.'+jQuery(this).data('editor_addon_target')+'-target');
        var position = target.position();
        var scrollTo = position.top;

        // Do scroll.
        scrollTargetDiv.animate({
            scrollTop:Math.round(scrollTo)
        }, 'fast');

    });
});

/**
 *
 * Main popup function
 */
function icl_editor_popup(e) {

    // Toggle
    icl_editor_toggle(e);

    // Set popup
    //icl_editor_resize_popup(e);

    // @Srdjan.
    // I have commented out the line above. I don't want the width and height to be calculated.

    // Bind window click to auto-hide
    icl_editor_bind_auto_close();
}

// TODO Document this
jQuery.expr.filters.icl_offscreen = function(el) {
    var t = jQuery(el).offset();
    return (
        (el.offsetLeft + el.offsetWidth) < 0
        || (el.offsetTop + el.offsetHeight) < 0
        || (el.offsetLeft > window.innerWidth || el.offsetTop > window.innerHeight)
        );
};

/**
 * Toggles popups.
 *
 * @todo We have multiple calls here.
 */
function icl_editor_toggle(element) {

    // Hide all except current
    // jQuery('.editor_addon_dropdown').each(function(){
    //     if (element.attr('id') != jQuery(this).attr('id')) {
    //         jQuery(this).css('visibility', 'hidden')
    //         .css('display', 'inline');
    //     } else {
    //         if (jQuery(this).css('visibility') == 'visible') {
    //             jQuery(this).css('visibility', 'hidden');
    //         } else {
    //             jQuery(this).css('visibility', 'visible').css('display', 'inline');
    //         }
    //     }
    // });

    var $popupContent = jQuery(element).find('.editor_addon_dropdown_content');
    var $directLinks = $popupContent.find('.direct-links, .direct-links-desc');
    $directLinks.hide();

    // Hide All editors
    jQuery('.editor_addon_dropdown').css({
        'visibility': 'hidden'
        //'display': 'inline'
    });

    // Show target editor
    element.css({
        'visibility': 'visible'
        //'display': 'block'
    });

    if ( $popupContent.height() >= 400 ) {
        $popupContent.find('.direct-links, .direct-links-desc').show();
    }

}

/**
 * Resizing Toolset editor dropdowns.
 *
 * Mind there are multiple instances on same screen.
 * @see .editor_addon_dropdown
 */
function icl_editor_resize_popup(element) {

    /*
     * First hide elements that should not be taken into account
     * Important: this is where we show shortuts in popup
     */
    jQuery(element).find('.direct-links').hide();
    jQuery(element).find('.editor-addon-link-to-top').hide();

    // Initial state
    // If hidden will be 0
    var heightInitial = jQuery(element).height();

    /*
     * Resize
     *
     * We'll take main editor width
     */
    var editorWidth = Math.round( jQuery('#post-body-content').width() + 20 );
    //Width for Views Edit Page
    if ( editorWidth == 20 ){
    	editorWidth = Math.round( jQuery('.CodeMirror').width() - 20 );
    }
    var editorOffset = element.offset();
    var windowsize = jQuery(window).width();
    if ((editorWidth + editorOffset.left) > windowsize) {
	    editorWidth = windowsize - (editorOffset.left + 20);
    }
    icl_editor_resize_popup_width(element, editorWidth);

    /*
     * Adjust size.
     */
    if (heightInitial > iclEditorHeight) {
        /*
         *
         * Important: this is where we show shortuts in popup
         */
        jQuery(element).find('.direct-links').show();
        jQuery(element).find('.editor-addon-link-to-top').show();
        icl_editor_resize_popup_height(element, iclEditorHeight);
    }
    if (heightInitial < iclEditorHeightMin) {
        icl_editor_resize_popup_height(element, iclEditorHeight);
    }

    /*
     * Set CSS
     */
    jQuery(element).css('overflow', 'auto');
    jQuery(element).css('padding', '0px');
}

/**
 * Sets element width.
 */
function icl_editor_resize_popup_width(element, width) {
    jQuery(element).width(width).css('width', width + 'px');
}

/**
 * Sets element height.
 */
function icl_editor_resize_popup_height(element, height) {
    jQuery(element).height(height).css('height', height + 'px');
}


var keyStr = "ABCDEFGHIJKLMNOP" +
"QRSTUVWXYZabcdef" +
"ghijklmnopqrstuv" +
"wxyz0123456789+/" +
"=";

function editor_decode64(input) {
    var output = "";
    var chr1, chr2, chr3 = "";
    var enc1, enc2, enc3, enc4 = "";
    var i = 0;

    // remove all characters that are not A-Z, a-z, 0-9, +, /, or =
    var base64test = /[^A-Za-z0-9\+\/\=]/g;
    if (base64test.exec(input)) {
        alert("There were invalid base64 characters in the input text.\n" +
            "Valid base64 characters are A-Z, a-z, 0-9, '+', '/',and '='\n" +
            "Expect errors in decoding.");
    }
    input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

    do {
        enc1 = keyStr.indexOf(input.charAt(i++));
        enc2 = keyStr.indexOf(input.charAt(i++));
        enc3 = keyStr.indexOf(input.charAt(i++));
        enc4 = keyStr.indexOf(input.charAt(i++));

        chr1 = (enc1 << 2) | (enc2 >> 4);
        chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
        chr3 = ((enc3 & 3) << 6) | enc4;

        output = output + String.fromCharCode(chr1);

        if (enc3 != 64) {
            output = output + String.fromCharCode(chr2);
        }
        if (enc4 != 64) {
            output = output + String.fromCharCode(chr3);
        }

        chr1 = chr2 = chr3 = "";
        enc1 = enc2 = enc3 = enc4 = "";

    } while (i < input.length);

    return unescape(editor_utf8_decode(output));
}

function editor_utf8_decode(utftext) {
    var string = "";
    var i = 0;
    var c = c1 = c2 = 0;

    while ( i < utftext.length ) {

        c = utftext.charCodeAt(i);

        if (c < 128) {
            string += String.fromCharCode(c);
            i++;
        }
        else if((c > 191) && (c < 224)) {
            c2 = utftext.charCodeAt(i+1);
            string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
            i += 2;
        }
        else {
            c2 = utftext.charCodeAt(i+1);
            c3 = utftext.charCodeAt(i+2);
            string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
            i += 3;
        }

    }

    return string;
}

function insert_b64_shortcode_to_editor(b64_shortcode, text_area) {
    var shortcode = editor_decode64(b64_shortcode);
    if(shortcode.indexOf('[types') == 0 && shortcode.indexOf('[/types') === false) {
        shortcode += '[/types]';
    }
    window.wpcfActiveEditor = text_area;
    
    icl_editor.insert(shortcode);
}

/**
 * Filtering elements from search boxes with JS
 */
function wpv_on_search_filter(el) {
    // get search text
    var searchText = jQuery(el).val();

    // get parent on DOM to find items and hide/show Search
    var parent = el.parentNode.parentNode;
    var searchItems = jQuery(parent).find('.group .item');

    jQuery(parent).find('.search_clear').css('display', (searchText == '') ? 'none' : 'inline');

    // iterate items and search
    jQuery(searchItems).each(function() {
        if(searchText == '' || jQuery(this).text().search(new RegExp(searchText, 'i')) > -1) {
            // alert(jQuery(this).text());
            jQuery(this).css('display', 'inline-block');
        }
        else {
            jQuery(this).css('display', 'none');
        }
    });

    // iterate group titles and check if they have items (otherwise hide them)

    wpv_hide_top_groups(parent);
}

/**
 * @TODO Document this
 */
function wpv_hide_top_groups(parent) {
    var groupTitles = jQuery(parent).find('.group-title');
    jQuery(groupTitles).each(function() {
        var parentOfGroup = jQuery(this).parent();
        // by default we assume that there are no children to show
        var visibleGroup = false;
        jQuery(parentOfGroup).find('.item').each(function() {
            if(jQuery(this).css('display') === 'inline-block') {
                visibleGroup = true;
                return false;
            }
        });
        var id = jQuery(this).data('id');
        if(!visibleGroup) {
            jQuery(this).hide();
            jQuery(this).closest('.group').hide();
            jQuery('.editor-addon-top-link[data-id="'+id+'"]').hide();
        } else {
            jQuery(this).show();
            jQuery(this).closest('.group').show();
            jQuery('.editor-addon-top-link[data-id="'+id+'"]').show();
        }
    });
}

// clear search input
function wpv_search_clear(el) {
    var parent = el.parentNode.parentNode;
    var searchbox = jQuery(parent).find('.search_field');
    searchbox.val('');
    wpv_on_search_filter(searchbox[0]);
}

function icl_editor_hide_popup () {
	jQuery('.editor_addon_dropdown').css({
		'visibility': 'hidden'
	});
	jQuery(document).off('click.icl_editor');
}

/**
 * Bind window click to auto-hide
 *
 * This should be generic close.
 * It's used in few places
 */
function icl_editor_bind_auto_close() {
    /*
     * jQuery executes 'bind' immediatelly on click
     */

    jQuery(document).on('click.icl_editor',function(e){

        var dropdownAddField = jQuery('#add_field_popup .editor_addon_dropdown');

        // Exception for 'Add field' button
        var $target = jQuery(e.target);

        // if we click anything but Toolset buttons
        if ( ( $target.closest('.wpv_add_fields_button,' +
								'.js-code-editor-toolbar-button-v-icon,' +
								'.js-code-editor-toolbar-button-v-icon,' +
								'.js-wpv-shortcode-post-icon-wpv-views,' +
								'.js-wpv-shortcode-post-icon-types,' +
								'.js-wpcf-access-editor-button').length === 0) ) {

            // if we click outside the popup
            if ( $target.parents('.editor_addon_dropdown').length === 0 ) {

                // Hide all
                jQuery('.editor_addon_dropdown').css({
                    'visibility': 'hidden'
                    //'display': 'inline'
                });

                // Unbind Add field dropdown
                dropdownAddField.removeClass('icl_editor_click_binded');

                jQuery(this).unbind(e);
            }

        }

    });
}


/**
 *
 * Inserts content into active editor.
 */
var icl_editor = (function(window, $){

    function isTinyMce($textarea)
    {
        var editor, ed=$textarea.attr('id');
        if (ed && ed.charAt(0)=='#') ed=ed.substring(1);

        // if tinyMCE
        if (
            window.tinyMCE && ed &&
            null != (editor=window.tinyMCE.get(ed)) &&
            false == editor.isHidden()
                )
            return editor;
        return false;
    };

	function codeMirrorCursorIsWithin( area, tStart, tEnd )
	{
		var codemirror = isCodeMirror(area);

		if( !codemirror )
		{
			return false;
		}
		//let's scope it to our own instance do not bother window
		this.wpcfActiveEditor = area;

		var current_cursor = codemirror.getCursor(true)
	 	, text_before = codemirror.getRange({line:0,ch:0}, current_cursor)
		, text_after = codemirror.getRange(current_cursor, {line:codemirror.lastLine(),ch:null})
		, regexStart
		, regexEnd
		, tagStart = tStart ? tStart : ''
		, tagEnd = tEnd ? tEnd : '';

		try
		{
			regexStart = new RegExp("\\["+tagStart+".*?\]");
			regexEnd = new RegExp('\\['+tagEnd+'.*?\]');

		//	console.log( text_before.match(regexStart), text_after.match(regexEnd) );

			return text_before.search(regexStart) != -1 && text_after.search(regexEnd) != -1;
		}
		catch( e )
		{
			console.log( "There are problems with your RegExp.", e.message );
		}

		return false;
	};

    function isCodeMirror($textarea)
    {        
        if ( ! $textarea.is('textarea') ) {
			return false;
		}
		var textareaNext = $textarea[0].nextSibling;
		if ( typeof textareaNext === 'undefined' ) {
			return false;
		}
		if ( textareaNext ) {
			//Usual way before WordPress 4.1
			if (
				textareaNext.CodeMirror 
				&& $textarea[0] == textareaNext.CodeMirror.getTextArea()
			) {
				return textareaNext.CodeMirror;
			}
			// Juan: CodeMirror panels wrap the CodeMirror div and themselves into a div.
			// Depending on the panels position, the CodeMirror div becomes the first or last child of that wrapper.
			// We need to check if the relevant node contains the right CodeMirror div as a child node.
			// Note that we will do the same below, so we can have CodeMirror panels in main editors too.
			var textareaNextHasPanels = isCodeMirrorWithPanels( $textarea, textareaNext );
			if ( textareaNextHasPanels ) {
				return textareaNextHasPanels;
			}
			// Emerson: WordPress 4.0+ introduces 'content-textarea-clone' div which in some instances is loaded after our textarea and before the CodeMirror div.
			// This core feature in WP is used in their auto-resize editor and distraction free writing.
			// This is particularly found in pages and post affecting syntax highlighting in main editors.
			// Let's skip that node and check if the nextsibling is really the CodeMirror div.
			var textareaNextNext = textareaNext.nextSibling;
			if ( textareaNextNext ) {
				if (
					textareaNextNext.CodeMirror
					&& $textarea[0] == textareaNextNext.CodeMirror.getTextArea()
				) {
					return textareaNextNext.CodeMirror;
				}
				var textareaNextNextHasPanels = isCodeMirrorWithPanels( $textarea, textareaNextNext );
				if ( textareaNextNextHasPanels ) {
					return textareaNextNextHasPanels;
				}
			}
		}
        return false;
    };
	
	function isCodeMirrorWithPanels( $textarea, candidateNode ) {
		if ( ! $textarea.is('textarea') ) {
			return false;
		}
		if ( typeof candidateNode === 'undefined' ) {
			return false;
		}
		if ( candidateNode ) {
			var candidateNodeFirstChild = candidateNode.firstChild,
			candidateNodeLastChiild = candidateNode.lastChild;
			if ( 
				candidateNodeFirstChild 
				&& candidateNodeFirstChild.CodeMirror
				&& $textarea[0] == candidateNodeFirstChild.CodeMirror.getTextArea()
			) {
				return candidateNodeFirstChild.CodeMirror;
			} else if ( 
				candidateNodeLastChiild 
				&& candidateNodeLastChiild.CodeMirror
				&& $textarea[0] == candidateNodeLastChiild.CodeMirror.getTextArea()
			) {
				return candidateNodeLastChiild.CodeMirror;
			}
		}
		return false;
	}

    function getContent($area)
    {
        if (!$area) $area=$('#content');
        //var tinymce=aux.isTinyMce($area);
        var codemirror=isCodeMirror($area);
        if (codemirror)
            return codemirror.getValue();
        return $area.val();
    };

    function InsertAtCursor(myField, myValue1, myValue2)
    {
        var $myField=myField;
        var tinymce=isTinyMce($myField);
        var codemirror=isCodeMirror($myField);
        //EMERSON: Check code mirror first before tinymce
        //because tinymce instance would still exist even if code mirror is activated

        // if codemirror
        if (codemirror)
        {
            //            alert('codemirror');
            codemirror.focus();

            if (!codemirror.somethingSelected())
            {
                // set at current cursor
                var current_cursor=codemirror.getCursor(true);
                codemirror.setSelection(current_cursor, current_cursor);
            }
            if (typeof(myValue2)!='undefined' && myValue2) { // wrap
                codemirror.replaceSelection(myValue1 + codemirror.getSelection() + myValue2, 'end');
            } else {
                codemirror.replaceSelection(myValue1, 'end');
            }

        }
        // else if tinymce
        else if (tinymce)
        {
            //            alert('tinymce');
            tinymce.focus();
            if (typeof(myValue2)!='undefined' && myValue2) // wrap
                tinymce.execCommand("mceReplaceContent",false, myValue1 + tinymce.selection.getContent({
                    format : 'raw'
                }) + myValue2);
            else
                tinymce.execCommand("mceInsertContent",false, myValue1);
        }
        // else other text fields
        else
        {
            //            alert('other');
            myField=$myField[0]; //$(myField)[0];
            myField.focus();
            if (document.selection)
            {
                sel = document.selection.createRange();
                if (typeof(myValue2)!='undefined' && myValue2) // wrap
                    sel.text = myValue1 + sel.text + myValue2;
                else
                    sel.text = myValue1;
            }
            else if ((myField.selectionStart != null) && (myField.selectionStart != undefined)/* == 0 || myField.selectionStart == '0'*/)
            {
                var startPos = parseInt(myField.selectionStart);
                var endPos = parseInt(myField.selectionEnd);
                if (typeof(myValue2)!='undefined' && myValue2) // wrap
                {
                    var sel = myField.value.substring(startPos, endPos);
                    myField.value = myField.value.substring(0, startPos) + myValue1 + sel + myValue2 +
                    myField.value.substring(endPos, myField.value.length);
                }
                else
                    myField.value = myField.value.substring(0, startPos) + myValue1 +
                    myField.value.substring(endPos, myField.value.length);
            }
            else
            {
                if (typeof(myValue2)!='undefined' && myValue2) // wrap
                    myField.value += myValue1 + myValue2;
                else
                    myField.value += myValue1;
            }
        }
    //        $myField.trigger('paste');
    };

    function insertContent(content)
    {
        //        alert(window.wpcfActiveEditor);
        InsertAtCursor($('#'+window.wpcfActiveEditor), content);
    }

    /**
     * Toggles Codemirror on textarea (#ID, toggle).
     *
     * We could record mouse position on Codemirror to restore it.
     */
    function toggleCodeMirror(textarea, on, mode)
    {
	mode = (typeof mode === "undefined") ? "myshortcodes" : mode;
        // if codemirror activated, enable syntax highlight
        if (window.CodeMirror)
        {
            if (!on && window.iclCodemirror[textarea])
            {
                window.iclCodemirror[textarea].toTextArea();
                window.iclCodemirror[textarea] = false;
                jQuery('#'+textarea).focus();
                return !on;
            }
            else if (on && !window.iclCodemirror[textarea])
            {
//                CodeMirror.defineMode("myshortcodes", codemirror_shortcodes_overlay);

                var $_metabox=$('#'+textarea).closest('.postbox'),
                _metabox_closed=false,
                _metabox_display=false;

                if ($_metabox.hasClass('closed') || 'none'==$_metabox.css('display'))
                {
                    _metabox_closed=true;
                    $_metabox.removeClass('closed');
                }
                if ('none'==$_metabox.css('display'))
                {
                    _metabox_display='none';
                    $_metabox.css('display','block');
                }
                window.iclCodemirror[textarea] = CodeMirror.fromTextArea(document.getElementById(textarea), {
                    mode: mode,
                    tabMode: "indent",
                    lineWrapping: true,
                    lineNumbers : true
                 //   autofocus: true // test that this breaks nothing
                });

                // TODO is resizing needed?
                // needed for scrolling
//                var height=Math.min(5000, Math.max(50, 200));
//                $('#'+textarea).css('resize', 'none').height( height + 'px' );
//                window.iclCodemirror[textarea].setSize( $('#'+textarea).width(), height );

                if ('none'==_metabox_display)
                {
                    $_metabox.css('display','none');
                }
                if (_metabox_closed)
                {
                    $_metabox.addClass('closed');
                }

                jQuery('#'+textarea).focus();

                return window.iclCodemirror[textarea];
            }
        }
        return false;
    };

    return {
        isTinyMce : isTinyMce,
        isCodeMirror : isCodeMirror,
        getContent : getContent,
        InsertAtCursor : InsertAtCursor,
        toggleCodeMirror : toggleCodeMirror,
		cursorWithin : codeMirrorCursorIsWithin,
        insert : function(text) {
            insertContent(text);
        },
        codemirror : function(textarea, on, mode) {
		mode = (typeof mode === "undefined") ? "myshortcodes" : mode;
		return toggleCodeMirror(textarea, on, mode);
        },
        codemirrorGet : function(textarea) {
            return window.iclCodemirror[textarea];
        }
    };
   

})(window, jQuery, undefined);
