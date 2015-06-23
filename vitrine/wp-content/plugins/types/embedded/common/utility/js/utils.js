if( typeof WPV_Toolset == 'undefined' )
{
	var WPV_Toolset = {};
	WPV_Toolset.message = {};
	WPV_Toolset.message.container = null;
}

if( typeof WPV_Toolset.Utils == 'undefined' ) WPV_Toolset.Utils = {};

WPV_Toolset.Utils.eventDispatcher = _.extend({}, Backbone.Events);

WPV_Toolset.Utils.do_ajax_post = function( params, callback_object )
{
	jQuery.post(ajaxurl, params, function (response) {

		if ( (typeof(response) !== 'undefined') && response !== null && ( response.message || response.Data )  ) {

			if( callback_object && callback_object.success && typeof callback_object.success == 'function'  )
				callback_object.success.call( this, response, params );
				WPV_Toolset.Utils.eventDispatcher.trigger('on_ajax_success_'+params.action, response, params);
		}
		else if( (typeof(response) !== 'undefined') && response !== null && response.error )
		{

			if( callback_object && callback_object.error && typeof callback_object.error == 'function'  )
				callback_object.error.call(this);
				WPV_Toolset.Utils.eventDispatcher.trigger('on_ajax_error_'+params.action, response, params);
		}
	}, 'json')
		.fail(function (jqXHR, textStatus, errorThrown) {
			console.log('Ajax call failed', textStatus, errorThrown)
			if( callback_object && callback_object.fail && typeof callback_object.fail == 'function'  )
				callback_object.fail.call(this, errorThrown);
				WPV_Toolset.Utils.eventDispatcher.trigger('on_ajax_fail_'+params.action,  textStatus, errorThrown, params );
		})
		.always(function () {
			//console.log( arguments );
			WPV_Toolset.Utils.eventDispatcher.trigger('on_ajax_complete_'+params.action, arguments, params);
		});
};

;(function ( $, window, document, undefined ) {

	// Create the defaults once
	var pluginName = "wpvToolsetMessage",
		dataPlugin = "plugin_" + pluginName,
		defaults = {
			text : "Enter a customized text to be displayed",
			type: '',
			inline: false,
			position : "after",
			header: false,
			headerText: false,
			close: false,
			use_this: true,
			fadeIn: 100,
			fadeOut: 100,
			stay: false,
			onClose: false,
			onOpen: false,
			onDestroy:false,
			args:[],
			referTo: null,
			offestX: -20,
			offsetY: 0,
			classname: '',
			stay_for: 1200, // Ignored when 'msPerCharacter is given.
			msPerCharacter: 50 // Ignered when 'stay_for' is given. This value is multiplied by the number of defaults.text characters count.
		},
		has_stay = false,
		is_open = false,
		prev = null,
		prev_text = '';

	// The actual plugin constructor
	function Plugin(element, options) {
		var self = this;

		self.container = $(element);

		self.prms = $.extend({}, defaults, options);
		self._defaults = defaults;
		self._name = pluginName;

		self.box = null;
		self.header = null;
		self.remove = null;
		self.tag = self.prms.inline ? 'span' : 'p';
		self.bool = false;

		if ( typeof (options.stay_for) === 'undefined' && typeof(self.prms.msPerCharacter) === 'number' ) { // If stay_for parameter wasn't passes when the plugin wass called AND msPerCharacter has correct type
			self.prms.stay_for = self.prms.text.length * self.prms.msPerCharacter;
		}

	}

	Plugin.prototype = {
		init: function () {
			var self = this;

			if( self.container.data('has_message' )  )
			{
				self.destroy();
			}

			if( self.container.children().length > 0 )
			{
				self.container.children().each(function(i){
					if( $(this).text() == self.prms.text )
					{
						self.bool = true;
					}
				});
			}

			if( self.bool ) return;

			if( has_stay )
			{
				if( prev )
				{
					var rem = prev;
					prev = null;
					has_stay = false;
					is_open = false;
					rem.fadeTo( 0, 0, function(){
						rem.remove();
						rem = null;
					});
				}
			}

			if( self.prms.header && self.prms.headerText )
			{
				self.box = $('<div class="toolset-alert toolset-alert-'+self.prms.type+' '+self.prms.classname+'" />');
				self.header = $('<h2 class="toolset-alert-self.header" />');
				self.box.append(self.header);
				self.header.text(self.prms.headerText);
				self.box.append('<'+self.tag+'></'+self.tag+'>');
				self.box.find(self.tag).html( self.prms.text );
			}
			else
			{
				self.box = $('<'+self.tag+' class="toolset-alert toolset-alert-'+self.prms.type+' '+self.prms.classname+'" />');
				self.box.html( self.prms.text );
			}

			if( self.prms.close ){
				self.remove = $('<i class="toolset-alert-close icon-remove-sign js-icon-remove-sign"></i>');
				self.box.append( self.remove );
				self.remove.on('click', function(event){
					self.wpvMessageRemove();
				});
			}


			//if( is_open ) self.wpvMessageRemove();
			if ( self.prms.position == 'before' ) {
					self.container.prepend( self.box );
				} else {
					self.container.append( self.box );
				}
			self.container.data('has_message', true );
			self.box.hide();

			if( null !== self.prms.referTo )
			{
				self.box.css({
					"position":"absolute",
					"z-index":10000,
					"top": self.prms.referTo.position().top + self.prms.offestY + "px",
					"left": self.prms.referTo.position().left + self.prms.referTo.width() + self.prms.offestX + "px"
				});
			}

			self.container.data( 'message-box', self.box );

			self.box.fadeTo( null != prev ? 0 : self.prms.fadeIn, 1, function(){
                $(this).trigger('wpv-message-open');
				prev = $(this);
				prev_text = self.prms.text;
				is_open = true;
				if( self.prms.onOpen && typeof self.prms.onOpen == 'function' )
				{
					self.prms.onOpen.apply( self, self.prms.args );
				}
				if( self.prms.stay ){
					has_stay = true;
				}
				else
				{
					var remove_message = _.bind(self.wpvMessageRemove, self);
					_.delay( remove_message, self.prms.stay_for );
					//self.wpvMessageRemove();
				}
			});

			return self;
		},
		wpvMessageRemove: function () {

			var self = this;

			if( self.box || self.container.data( 'message-box') )
			{
				var box = self.box || self.container.data( 'message-box');

				box.fadeTo( self.prms.fadeOut, 0, function(){
                    $(this).trigger('wpv-message-remove');
					is_open = false;
					prev = null;
					prev_text = '';
					has_stay = false;
					if( self.prms.onClose && typeof self.prms.onClose == 'function' )
					{
						self.prms.onClose.apply( self, self.prms.args );
					}

					$( this ).remove();
                    self.container.data('has_message', false );
					self.container.data( 'message-box', null );
					self.box = null;
				});
			}

			return self;
		},
		destroy:function()
		{
            $(this).trigger('wpv-message-remove');
			this.container.empty();
			if( this.prms.onDestroy && typeof this.prms.onDestroy == 'function' )
			{
				this.prms.onDestroy.apply( this, this.prms.args );
			}
			this.box = null;
			this.container.data( 'message-box', null );
			this.container.data('has_message', false );
		},
		has_message:function(){
			return this.container.data('has_message');
		}
	};


	$.fn[ pluginName ] = function ( arg ) {

		return this.each(function(){
			var args, instance;

			if ( !( $(this).data( dataPlugin ) instanceof Plugin ) ) {
				// if no instance, create one
				$(this).data( dataPlugin, new Plugin( $(this), arg ) );
			}
			// do not use this one if you want the plugin to be a singleton bound to the DOM element
			else
			{
				// if instance delete reference and do another one
				$(this).data( dataPlugin, null );
				$(this).data( dataPlugin, new Plugin( $(this), arg ) );
			}

			instance = $(this).data( dataPlugin );

			instance.element = $(this);

			// call Plugin.init( arg )
			if (typeof arg === 'undefined' || typeof arg === 'object') {

				if ( typeof instance['init'] === 'function' ) {
					instance.init( arg );
				}

				// checks that the requested public method exists
			} else if ( typeof arg === 'string' && typeof instance[arg] === 'function' ) {

				// copy arguments & remove function name
				args = Array.prototype.slice.call( arguments, 1 );

				// call the method
				return instance[arg].apply( instance, args );

			} else {

				$.error('Method ' + arg + ' does not exist on jQuery.' + pluginName);

			}
		});
	};
})( jQuery, window, document );

(function ($) {
	$.fn.insertAtIndex = function(index,selector){
		var opts = $.extend({
			index: 0,
			selector: '<div/>'
		}, {index: index, selector: selector});
		return this.each(function() {
			var p = $(this);
			var i = ($.isNumeric(opts.index) ? parseInt(opts.index,10) : 0);
			if (i <= 0)
				p.prepend(opts.selector);
			else if( i > p.children().length-1 )
				p.append(opts.selector);
			else
				p.children().eq(i).before(opts.selector);
		});
	};
})( jQuery );

(function ($) {

	$.fn.loaderOverlay = function( action,options )
	// action: 'show'|'hide' attributes are optional.
	// options: fadeInSpeed, fadeOutSpeed, displayOverlay, class. attributes are optional
	{

		var defaults = {
			fadeInSpeed : 'fast',
			fadeOutSpeed : 'fast',
			displayLoader: true,
			class: null
		};

		var prms = $.extend( defaults, options );
		var $overlayContainer = this;
		var $overlayEl = $('<div class="loader-overlay" />');

		var showOverlay = function() {
			if ( ! $overlayContainer.data('has-overlay') ) {
				$overlayEl
					.appendTo($overlayContainer)
					.hide()
					.fadeIn(prms.fadeInSpeed, function() {
						$overlayContainer.data('has-overlay', true);
						$overlayContainer.data('overlay-el', $overlayEl);
					} );
			}
		};

		var hideOverlay = function() {
			if ( $overlayContainer.data('has-overlay') ) {
				$overlayContainer.data('overlay-el')
					.fadeOut(prms.fadeOutSpeed, function() {
						$overlayEl.remove();
						$overlayContainer.data('has-overlay', false);
				} );
			}
		};

		if ( prms.class !== null ) {
			$overlayEl.addClass(prms.class);
		}
		if ( prms.displayLoader ) {
			$('<div class="preloader" />').appendTo($overlayEl);
		}

		if ( typeof(action) !== 'undefined' ) { // When 'action' parameter is given

			if ( action === 'show' ) {
				showOverlay();
			}
			else if ( action === 'hide' ) {
				hideOverlay();
			}

		}
		else { // when the method is called without 'action' parameter

			if ( $overlayContainer.data('has-overlay') ) { // hide overlay if it's displayed
				hideOverlay();
			}
			else { // show overlay if not
				showOverlay();
			}

		}

		return this;
	};

})( jQuery );

(function ($) {
	/*
	Basic usage:
	$element.ddlWpPointer(); // will show a pointer if it's hidden OR hide a pointer if it's shown

	1. $element have to be valid jQuery selector
	2. data-toolipt-header HTML attribute is required to display the header
	3. data-tooltip-content HTML attribute is required to display the content

	Customization:
	$element.ddlWpPointer('action', // action: 'show' | 'hide'
	{
		content: $element // $element have to be valid jQuery selector content element should contain H3 for the header and P for the content. Example: <div><h3>Header</h3><p>Content</p></div>
		edge: 'left' // 'left' | 'right' | 'top' | 'bottom'
		align: 'center' // 'center' | 'right' | 'left'
		offset: 'x y' // example: '0 15'
	})

	 */
	$.fn.ddlWpPointer = function( action, options )
	{
		var $el = this;

		//$.jStorage.flush();

		var defaults = {
			headerText: function() {
				var header = $el.data('tooltip-header');
				if ( header ) {
					return header;
				}
				else {
					return 'use <b>data-tooltip-header="header text"</b> attribute to create a header';
				}
			},
			contentText : function() {
				var content = $el.data('tooltip-content');
				if ( content ) {
					return content;
				}
				else {
					return 'use <b>data-tooltip-content="content text"</b> attribute to create a content';
				}
			},
			content: function() { // returns string by default (data-tooltip-header and data-tooltip-content attibutes), but can be overridden by jQuery obj
				return '<h3>'+ defaults.headerText() +'</h3><p>'+ defaults.contentText() +'</p>';
			},
			edge : 'left',
			align : 'center',
			offset: '0 0',
			stay_hidden: false
		};

		var prms = $.extend( defaults, options );

		var showPointer = function() {

			if ( ! $el.data('has-wppointer') ) {
				$el
					.pointer({
						content: function() {
							return prms.content;
						},
						position: {
							edge: prms.edge,
							align: prms.align,
							offset: prms.offset
						},
						close: function() {

							$el.data('has-wppointer', false);
							$el.trigger('help_tooltip_closes', options );
						}
					})
					.pointer('open');

				$el.data('has-wppointer', true);
			}
		};

		var hidePointer = function() {

			if ( $el.data('has-wppointer') ) {

				$el.pointer('close');
				$el.data('has-wppointer', false);

			}

		};

		if ( typeof(action) !== 'undefined' ) { // When 'action' parameter is given

			if ( action === 'show' && prms.stay_hidden !== true ) {
				showPointer();
			}
			else if ( action === 'hide' ) {
				hidePointer();
			}

		}
		else { // when the method is called without 'action' parameter

			if ( $el.data('has-wppointer') ) { // hide pointer if it's displayed
				hidePointer();
			}
			else if( prms.stay_hidden !== true ) { // show it if not
				showPointer();
			}

		}

		return this;
	};

})( jQuery );

WPV_Toolset.Utils.Loader = function()
{
	//fake comment
	var self = this;

	self.loading = false; self.el = null;

	self.loader = jQuery('<div class="ajax-loader spinner"></div>');

	self.loadShow = function( el, after )
	{
		self.el = el;
		self.loading = true;

        if( typeof after === 'undefined' )
        {
            self.loader.prependTo( self.el ).show();
        }
        else{
            self.loader.insertAfter( self.el ).show();
        }

        return self.loader;
	};
	self.loadHide = function()
	{
		self.loader.fadeOut(400, function(){

			self.loading = false;
			jQuery(this).remove();
		});

        return self.loader;
	};
};

if( typeof _ != 'undefined' )
{
	WPV_Toolset.Utils.flatten = function(x, result, prefix) {
		if(_.isObject(x)) {
			_.each(x, function(v, k) {
				WPV_Toolset.Utils.flatten(v, result, prefix ? prefix + '_' + k : k)
			})
		} else {
			result[prefix] = x
		}
		return result
	};
	WPV_Toolset.Utils.flatten_filter_by_key = function( x, result, prefix, filter )
	{
		var res = [],
		find = WPV_Toolset.Utils.flatten( x, result, prefix );

		if ( !filter ) return _.values( find );

		_.each(find, function( element, index, list ){
			if( index.indexOf( prefix ? prefix + '_'+filter : filter ) !== -1 || filter === index )
				res.push( element );
		});

		return res;
	}
	WPV_Toolset.Utils.containsObject = function (obj, list) {
		var res = _.find(list, function(val){
			return _.isEqual(obj, val);
		});
		return (_.isObject(res))? true:false;
	};
};



(function($) {
	$.fn.textWidth = function() {
		var text = this.html() || this.text() || this.val();
		return( $.textWidth( text ) );
	};
	$.textWidth = function(text) {
		var div = $('#textWidth');
		if (div.length === 0)
			div = $('<div id="textWidth" style="display: none;"></div>').appendTo($('body'));
		div.html(text);
		return(div.width());
	};
})(jQuery);

//Courtesy from http://stackoverflow.com/questions/24816/escaping-html-strings-with-jquery
WPV_Toolset.Utils.escapeHtml = function(str) {
	if (typeof(str) == "string"){
		try{
			var newStr = "";
			var nextCode = 0;
			for (var i = 0;i < str.length;i++){
				nextCode = str.charCodeAt(i);
				if (nextCode > 0 && nextCode < 128){
					newStr += "&#"+nextCode+";";
				}
				else{
					newStr += "?";
				}
			}
			return newStr;
		}
		catch(err){
		}
	}
	else{
		return str;
	}
};

WPV_Toolset.Utils.editor_decode64 = function(input) {
    var  output = "",
         chr1, chr2, chr3 = "",
         enc1, enc2, enc3, enc4 = "",
         i = 0,
         keyStr = "ABCDEFGHIJKLMNOP" +
            "QRSTUVWXYZabcdef" +
            "ghijklmnopqrstuv" +
            "wxyz0123456789+/" +
            "=";

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

    return WPV_Toolset.Utils.editor_utf8_decode( output );
};

WPV_Toolset.Utils.editor_utf8_decode = function (utftext) {
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
};

// convert unicode character to its corrsponding numeric entity
WPV_Toolset.Utils.fixedCharCodeAt = function  (str, idx) {
    // ex. fixedCharCodeAt ('\uD800\uDC00', 0); // 65536
    // ex. fixedCharCodeAt ('\uD800\uDC00', 1); // 65536
    idx = idx || 0;
    var code = str.charCodeAt(idx);
    var hi, low;
    if (0xD800 <= code && code <= 0xDBFF) { // High surrogate (could change last hex to 0xDB7F to treat high private surrogates as single characters)
        hi = code;
        low = str.charCodeAt(idx+1);
        if (isNaN(low)) {
            throw 'High surrogate not followed by low surrogate in fixedCharCodeAt()';
        }
        return ((hi - 0xD800) * 0x400) + (low - 0xDC00) + 0x10000;
    }
    if (0xDC00 <= code && code <= 0xDFFF) { // Low surrogate
        // We return false to allow loops to skip this iteration since should have already handled high surrogate above in the previous iteration
        return false;
        /*hi = str.charCodeAt(idx-1);
         low = code;
         return ((hi - 0xD800) * 0x400) + (low - 0xDC00) + 0x10000;*/
    }
    return code;
};

WPV_Toolset.replace_unicode_characters = function(string)
{
    // remove accents, swap ñ for n, etc
    var from = "ãàáäâẽèéëêìíïîõòóöôùúüûñç·/_,:;",
        to   = "aaaaaeeeeeiiiiooooouuuunc------";
    for (var i=0, l=from.length ; i<l ; i++) {
        string = string.replace( new RegExp(from.charAt(i), 'g'), to.charAt(i) );
    }

    var unicode = '!£$%&()=?^|#§';

    for( var i=0; i<unicode.length; i++ )
    {
        string = string.replace( new RegExp(unicode.charAt(i).regexEscape(), 'g'), WPV_Toolset.Utils.fixedCharCodeAt( unicode.charAt(i) ) );
    }

    return string;
};

// escapes regex characters for use in regex constructor
String.prototype.regexEscape = function regexEscape() {
    return this.replace(/[\.\?\+\*\^\$\|\(\{\[\]\\)]/g, '\\$&');
};

// THE TOOLTIP //
;
(function ($, window, document, undefined) {

    // Create the defaults once
    var pluginName = "toolsetTooltip",
        dataPlugin = "plugin_" + pluginName,
        undefined,
        defaults = {
            top: undefined,
            text:'',
            close:null,
            open:null
        };

    // The actual plugin constructor
    function Plugin(element, options) {

        this.$element = element;
        this.settings = $.extend({}, defaults, options);
        this._defaults = defaults;
        this._name = pluginName;
        this._remove_tooltip = null;
    }

    // Avoid Plugin.prototype conflicts
    $.extend(Plugin.prototype, {
        init: function () {
            var self = this;
            this.$element.on('mouseenter', function(event) {
                self.show(event);
            });
            this.$element.on('mouseleave', function(event) {
                self.hide(event);
            });
        },
        show: function (event) {
            var self = this,
                $tooltip = $('<div class="toolset-tooltip" />'),
                offset = self.$element.offset(),
                offset_top = typeof self.settings.top === 'undefined' ? 20 : self.settings.top;

            self._remove_tooltip = $tooltip;

            $tooltip
                .appendTo('body')
                .text( this.settings.text || self.$element.data('tooltip-text') )
                .css({
                    'top': offset.top - $tooltip.height() - offset_top,
                    'left': offset.left - ($tooltip.outerWidth() / 2) + (self.$element.outerWidth() / 2),
                    'zIndex': '9999999'
                })
                .fadeIn(100);

            // Probably $elem doesn't is removed before 'click' event takes place
            // So we need to call _manageCellTooltip( $elem, 'hide') somewhere... but i don't know where ;)

            self.$element.on('mousedown', function () {

                if ( self._remove_tooltip ){
                    self._remove_tooltip.remove();
                    self._remove_tooltip = null;
                }
            });

            if( self.settings.open !== null && self.settings.open instanceof Function ){
                self.settings.open.call(self);
            }

            return $tooltip;
        },
        hide: function (event) {
            var self = this;

            if ( self._remove_tooltip ) {
                if( self.settings.close !== null && self.settings.close instanceof Function ){
                    self.settings.close.call(self);
                }
                self._remove_tooltip.remove();
                self._remove_tooltip = null;
            }
        }
    });

    $.fn[ pluginName ] = function ( arg ) {

        return this.each(function(){
            var args, instance;

            if ( !( $(this).data( dataPlugin ) instanceof Plugin ) ) {
                // if no instance, create one
                $(this).data( dataPlugin, new Plugin( $(this), arg ) );
            }

            instance = $(this).data( dataPlugin );
            instance.element = $(this);

            // call Plugin.init( arg )
            if (typeof arg === 'undefined' || typeof arg === 'object') {

                if ( typeof instance['init'] === 'function' ) {
                    instance.init( arg );
                }

                // checks that the requested public method exists
            } else if ( typeof arg === 'string' && typeof instance[arg] === 'function' ) {

                // copy arguments & remove function name
                args = Array.prototype.slice.call( arguments, 1 );

                // call the method
                return instance[arg].apply( instance, args );

            } else {

                $.error('Method ' + arg + ' does not exist on jQuery.' + pluginName);

            }
        });

        return this;
    };

})(jQuery, window, document);

;(function($, window, document, undefined){
    /*

     highlight v3  !! Modified by Jon Raasch (http://jonraasch.com) to fix IE6 bug !!

     Highlights arbitrary terms.

     <http://johannburkard.de/blog/programming/javascript/highlight-javascript-text-higlighting-jquery-plugin.html>

     MIT license.

     Johann Burkard
     <http://johannburkard.de>
     <mailto:jb@eaio.com>

     */
    var defaults = {
            className:'highlighted'
        },
        options = {

        };

    jQuery.fn.highlight = function(pat, option) {

        options = jQuery.extend( options, defaults, option )

        function innerHighlight(node, pat) {
            var skip = 0;

            if ( node.nodeType == 3  ) {
                var pos = node.data.toUpperCase().indexOf(pat);
                if (pos >= 0) {
                    var spannode = document.createElement('span');
                    spannode.className = options.className;
                    var middlebit = node.splitText(pos);
                    var endbit = middlebit.splitText(pat.length);
                    var middleclone = middlebit.cloneNode(true);
                    spannode.appendChild(middleclone);
                    middlebit.parentNode.replaceChild(spannode, middlebit);
                    skip = 1;
                }
            }
            else if (node.nodeType == 1 && node.childNodes && !/(script|style)/i.test(node.tagName)) {
                for (var i = 0; i < node.childNodes.length; ++i) {
                    i += innerHighlight(node.childNodes[i], pat);
                }
            }
            return skip;
        }
        return this.each(function() {
            innerHighlight(this, pat.toUpperCase());
        });
    };

    jQuery.fn.removeHighlight = function() {
        function newNormalize(node) {
            for (var i = 0, children = node.childNodes, nodeCount = children.length; i < nodeCount; i++) {
                var child = children[i];
                if (child.nodeType == 1) {
                    newNormalize(child);
                    continue;
                }
                if (child.nodeType != 3) { continue; }
                var next = child.nextSibling;
                if (next == null || next.nodeType != 3) { continue; }
                var combined_text = child.nodeValue + next.nodeValue;
                new_node = node.ownerDocument.createTextNode(combined_text);
                node.insertBefore(new_node, child);
                node.removeChild(child);
                node.removeChild(next);
                i--;
                nodeCount--;
            }
        }

        return this.find("span."+options.className).each(function() {
            var thisParent = this.parentNode;
            thisParent.replaceChild(this.firstChild, this);
            newNormalize(thisParent);
        }).end();
    };

}(jQuery, window, document))