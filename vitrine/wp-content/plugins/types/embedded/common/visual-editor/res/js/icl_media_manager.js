/**
 * Thanks to Thomas Griffin for his super useful example on Github
 *
 * https://github.com/thomasgriffin/New-Media-Image-Uploader
 */
jQuery(document).ready(function($){
    
	// Prepare the variable that holds our custom media manager.
	var wpv_media_frame;
	// var wp_media_post_id = wp.media.model.settings.post.id; // Store the old id
    var toolset_edit_data = jQuery( '#toolset-edit-data' ),
    set_to_post_id = toolset_edit_data.val(),
    toolset_edit_plugin = toolset_edit_data.data( 'plugin' );    
    
	// Bind to our click event in order to open up the new media experience.
	$(document.body).on('click', '.js-wpv-media-manager', function(e){ //mojo-open-media is the class of our form button
		// Prevent the default action from occuring.
		e.preventDefault(toolset_edit_data);
		
		var referred_id = $(this).attr('data-id');
		if (typeof referred_id !== 'undefined' && referred_id !== false) {
			set_to_post_id = referred_id;
		}
	
		var active_textarea = $(this).data('content');
		window.wpcfActiveEditor = active_textarea;
					// If the frame already exists, re-open it.
		if ( wpv_media_frame ) {
			wpv_media_frame.uploader.uploader.param( 'post_id', set_to_post_id );
			wpv_media_frame.open();
			return;
		} else {
			// Set the wp.media post id so the uploader grabs the ID we want when initialised
			wp.media.model.settings.post.id = set_to_post_id;
		}
		wpv_media_frame = wp.media.frames.wpv_media_frame = wp.media({
			//Create our media frame
			className: 'media-frame mojo-media-frame js-wpv-media-frame',
			frame: 'post',
			multiple: false, //Disallow Mulitple selections
			library: {
				type: 'image' //Only allow images
			}
		});
		
		
		wpv_media_frame.on('open', function(event){
			var media_button_insert = $('.media-button-insert'),
			media_frame = $('.js-wpv-media-frame');
			$('li.selected').removeClass('selected').find('a.check').trigger('click');
			media_button_insert.addClass('button-secondary').removeClass('button-primary');
			media_frame.find('.media-menu').html('');
			media_button_insert.live("attributeChanged", function(event, args, val ){
				
				if( args == 'disabled' && val == true )
				{
					$(event.target).addClass('button-secondary').removeClass('button-primary');
				}
				else if( args == 'disabled' && val == false )
				{
					$(event.target).removeClass('button-secondary').addClass('button-primary');
				}
			});
			$('.clear-selection').on('click', function() {
				media_button_insert.parent().find('.js-wpv-media-type-not-insertable').remove();
				media_button_insert.addClass('button-secondary').removeClass('button-primary').show();
			});
		}); 
		
		wpv_media_frame.on('insert', function(){
			// Watch changes in wp-includes/js/media-editor.js
			var media_attachment = wpv_media_frame.state().get('selection').first().toJSON(),
			filetype = media_attachment.type;
			if ( filetype == 'image' ) {
				var size = $('.attachment-display-settings .size').val(),// WARNING size might be undefined for some image types, like BMP or TIFF, that do not generate thumbnails
				only_img_src_allowed_here = [
					'wpv-pagination-spinner-image',
					'wpv-dps-spinner-image',
					'wpv_filter_meta_html_css',
					'wpv_filter_meta_html_js',
					'wpv_layout_meta_html_css',
					'wpv_layout_meta_html_js'
				],
				shortcode,
				code,
				options,
				classes,
				align,
				target_url;
				if ( $.inArray( window.wpcfActiveEditor, only_img_src_allowed_here ) !== -1 ) {
					if ( size ) {
						code = media_attachment.sizes[size].url;
					} else {
						code = media_attachment.url;
					}
					$('.js-' + window.wpcfActiveEditor).val('');
					$('.js-' + window.wpcfActiveEditor + '-preview').attr("src",code).show();
				} else {
					// Basic img tag options
					if ( size ) {
						options = {
							tag:'img',
							attrs: {
								src: media_attachment.sizes[size].url
							},
							single: true
						};
					} else {
						options = {
							tag:'img',
							attrs: {
								src: media_attachment.url
							},
							single: true
						};
					}
					if ( media_attachment.hasOwnProperty( 'alt' ) && media_attachment.alt ) {
						options.attrs.alt = media_attachment.alt;
					}
					if ( size ) {
						options.attrs.width = media_attachment.sizes[size].width;
						options.attrs.height = media_attachment.sizes[size].height;
					} else {
						options.attrs.width = 1;
					}
					classes = [];
					align = $('.alignment').val();
					if ( align == 'none' ) {
						align = false;
					}
					// Only assign the align class to the image if we're not printing a caption, since the alignment is sent to the shortcode.
					if ( align && ! media_attachment.caption ) {
						classes.push( 'align' + align );
					}
					if ( size ) {
						classes.push( 'size-' + size );
					}
					options.attrs['class'] = _.compact( classes ).join(' ');
					// Generate the `a` element options, if they exist.
					if ( $('select.link-to').val() == 'file' ) {
						target_url = media_attachment.url;
					} else if ( $('select.link-to').val() == 'custom' ) {
						target_url = $('.link-to-custom').val();
					} else {
						target_url = false;
					}
					if ( target_url ) {
						options = {
							tag: 'a',
							attrs: {
								href: target_url
							},
							content: options
						};
					}
					code = wp.html.string( options );
					// Generate the caption shortcode if needed
					if ( media_attachment.caption ) {
						shortcode = {};
						if (size ) {
							if ( media_attachment.sizes[size].width ) {
								shortcode.width = media_attachment.sizes[size].width;
							}
						} else {
							shortcode.width = 1;
						}
						if ( align ) {
							shortcode.align = 'align' + align;
						}
						code = wp.shortcode.string({
							tag: 'caption',
							attrs: shortcode,
							content: code + ' ' + media_attachment.caption
						});
					}
				}
				icl_editor.insert(code);
				if ( $.inArray( window.wpcfActiveEditor, only_img_src_allowed_here ) !== -1 ) {
					$('.js-' + window.wpcfActiveEditor).trigger('keyup');
				}
			} else {
				var options,
				media_shrtcode = '';
				if ( $('select.link-to').val() == 'embed' ) {
					options = {
						tag: filetype,
						attrs: {
							src: media_attachment.url
						},
						type: true,
						content: ''
					};
					if ( media_attachment.hasOwnProperty( 'caption' ) && media_attachment.caption ) {
						options.attrs.caption = media_attachment.caption;
					}
					media_shrtcode = wp.shortcode.string( options );
				} else {
					options = {
						tag: 'a',
						attrs: {
							href: media_attachment.url
						},
						content: media_attachment.title
					};
					media_shrtcode = wp.html.string( options );
					/*
					media_shrtcode = '<a href="' + media_attachment.url + '">' + media_attachment.title + '</a>';
					*/
				}
				icl_editor.insert(media_shrtcode);
			}
		});
		
		var _AttachmentDisplay = wp.media.view.Settings.AttachmentDisplay;
		wp.media.view.Settings.AttachmentDisplay = _AttachmentDisplay.extend({
			render: function() {
				_AttachmentDisplay.prototype.render.apply(this, arguments);
				var attachment = this.options.attachment,
				attach_type = '',
				insert_button = $('.media-button-insert').show(),
				only_img_src_allowed_here = [
					'wpv-pagination-spinner-image',
					'wpv-dps-spinner-image',
					'wpv_filter_meta_html_css',
					'wpv_filter_meta_html_js',
					'wpv_layout_meta_html_css',
					'wpv_layout_meta_html_js'
				];
				insert_button.parent().find('.js-wpv-media-type-not-insertable').remove();
				if ( attachment ) {
					attach_type = attachment.get('type');
				}
				if ( attach_type == 'image' && $.inArray( window.wpcfActiveEditor, only_img_src_allowed_here ) !== -1 ) {
					this.$el.find('select.link-to').parent().remove();
					this.model.set('link', 'none');
					this.$el.find('select.alignment').parent().remove();
				} else {
					this.$el.find('select.link-to').find('option[value="post"]').remove();
					if ( $.inArray( window.wpcfActiveEditor, only_img_src_allowed_here ) !== -1 ) {
						insert_button.hide().parent().append('<button disabled="disabled" class="media-button button-large button-secondary js-wpv-media-type-not-insertable">' + icl_media_manager.only_img_allowed_here + '</button>');
					}
				}
				this.updateLinkTo();
			}
		});
	
	// Now that everything has been set, let's open up the frame.
	wpv_media_frame.open();
	});
});


jQuery(document).on("DOMNodeInserted", function(){
    var toolset_edit_plugin = jQuery( '#toolset-edit-data' ).data( 'plugin' );
	if ( toolset_edit_plugin === 'views' ){
        // Lock uploads to "Uploaded to this post"
        jQuery('select.attachment-filters [value="uploaded"]').attr( 'selected', true ).parent().trigger('change');
        jQuery('.attachments-browser .media-toolbar-secondary .attachment-filters').addClass('hidden');
    }
});