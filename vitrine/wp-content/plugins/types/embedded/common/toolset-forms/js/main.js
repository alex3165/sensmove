var toolsetForms = toolsetForms || {};


var wptCallbacks = {};
wptCallbacks.validationInit = jQuery.Callbacks('unique');
wptCallbacks.addRepetitive = jQuery.Callbacks('unique');
wptCallbacks.removeRepetitive = jQuery.Callbacks('unique');
wptCallbacks.conditionalCheck = jQuery.Callbacks('unique');
wptCallbacks.reset = jQuery.Callbacks('unique');

jQuery(document).ready(function () {
    if (typeof wptValidation !== 'undefined') {
        wptCallbacks.validationInit.add(function () {
            wptValidation.init();
        });
    }
    if (typeof wptCond !== 'undefined') {
        wptCond.init();
    } else {
        wptCallbacks.validationInit.fire();
    }
    /**
     * check taxonmies on submitted forms
     */
    jQuery('.cred-taxonomy', jQuery('form.is_submitted')).each(function () {
        parent = jQuery(this);
        setTimeout(function () {
            jQuery('input.wpt-taxonomy-add-new', parent).click();
        }, 50);
    });
});


var wptFilters = {};
function add_filter(name, callback, priority, args_num) {
    var args = _.defaults(arguments, ['', '', 10, 2]);
    if (typeof wptFilters[name] === 'undefined')
        wptFilters[name] = {};
    if (typeof wptFilters[name][args[2]] === 'undefined')
        wptFilters[name][args[2]] = [];
    wptFilters[name][args[2]].push([callback, args[3]]);
}
function apply_filters(name, val) {
    if (typeof wptFilters[name] === 'undefined')
        return val;
    var args = _.rest(_.toArray(arguments));
    _.each(wptFilters[name], function (funcs, priority) {
        _.each(funcs, function ($callback) {
            var _args = args.slice(0, $callback[1]);
            args[0] = $callback[0].apply(null, _args);
        });
    });
    return args[0];
}
function add_action(name, callback, priority, args_num) {
    add_filter.apply(null, arguments);
}
function do_action(name) {
    if (typeof wptFilters[name] === 'undefined')
        return false;
    var args = _.rest(_.toArray(arguments));
    _.each(wptFilters[name], function (funcs, priority) {
        _.each(funcs, function ($callback) {
            var _args = args.slice(0, $callback[1]);
            $callback[0].apply(null, _args);
        });
    });
    return true;
}

/**
 * flat taxonomies functions
 */
function showHideMostPopularButton(taxonomy, form)
{
    var $button = jQuery('[name="sh_' + taxonomy + '"]', form);
    var $taxonomy_box = jQuery('.shmpt-' + taxonomy, form);
    var $tag_list = $taxonomy_box.find('.js-wpt-taxonomy-popular-add');

    if (!$button.hasClass('js-wpt-taxonomy-popular-show-hide'))
        return true;

    if ($tag_list.length > 0)
    {
        $button.show();
        return true;
    } else {
        $button.hide();
        return false;
    }
}

jQuery(document).on('click', '.js-wpt-taxonomy-popular-show-hide', function () {
    showHideMostPopularTaxonomy(this);
});

function showHideMostPopularTaxonomy(el)
{
    var taxonomy = jQuery(el).data('taxonomy');
    var form = jQuery(el).closest('form');
    jQuery('.shmpt-' + taxonomy, form).toggle();
    var curr = jQuery(el).val();
    if (curr == jQuery(el).data('show-popular-text')) {
        jQuery(el).val(jQuery(el).data('hide-popular-text'), form).addClass('btn-cancel');
    } else {
        jQuery(el).val(jQuery(el).data('show-popular-text'), form).removeClass('btn-cancel');
    }
}

jQuery(document).on('click', '.js-wpt-taxonomy-popular-add', function () {
    var thiz = jQuery(this);
    var taxonomy = thiz.data('taxonomy');
    var slug = thiz.data('slug');
    var _name = thiz.data('name');
    addTaxonomy(_name, taxonomy, this);
    return false;
});

function addTaxonomy(slug, taxonomy, el)
{
    var form = jQuery(el).closest('form');
    var curr = jQuery('input[name=tmp_' + taxonomy + ']', form).val().trim();
    if ('' == curr) {
        jQuery('input[name=tmp_' + taxonomy + ']', form).val(slug);
        setTaxonomy(taxonomy, el);
    } else {
        if (curr.indexOf(slug) == -1) {
            jQuery('input[name=tmp_' + taxonomy + ']', form).val(curr + ',' + slug);
            setTaxonomy(taxonomy, el);
        }
    }
    jQuery('input[name=tmp_' + taxonomy + ']', form).val('');
}

jQuery(document).on('click', '.js-wpt-taxonomy-add-new', function () {
    var thiz = jQuery(this),
            taxonomy = thiz.data('taxonomy');
    setTaxonomy(taxonomy, this);
});

jQuery(document).on('keypress', '.js-wpt-new-taxonomy-title', function (e) {
    if (13 === e.keyCode) {
        e.preventDefault();
        var thiz = jQuery(this),
                taxonomy = thiz.data('taxonomy'),
                taxtype = thiz.data('taxtype');
        if (taxtype == 'hierarchical') {
            toolsetForms.cred_tax.add_taxonomy(taxonomy, this);
        } else {
            setTaxonomy(taxonomy, this);
        }
    }
});

function setTaxonomy(taxonomy, el)
{
    var form = jQuery(el).closest('form');
    var tmp_tax = jQuery('input[name=tmp_' + taxonomy + ']', form).val();
    if (tmp_tax.trim() == '')
        return;
    var tax = jQuery('input[name=' + taxonomy + ']', form).val();
    var arr = tax.split(',');
    if (jQuery.inArray(tmp_tax, arr) !== -1)
        return;
    var toadd = (tax == '') ? tmp_tax : tax + ',' + tmp_tax;
    jQuery('input[name=' + taxonomy + ']', form).val(toadd);
    jQuery('input[name=tmp_' + taxonomy + ']', form).val('');
    updateTaxonomies(taxonomy, form);
}

function updateTaxonomies(taxonomy, form)
{
    var taxonomies = jQuery('input[name=' + taxonomy + ']', form).val();
    jQuery('div.tagchecklist-' + taxonomy, form).html('');
    if (!taxonomies || (taxonomies && taxonomies.trim() == ''))
        return;
    var toshow = taxonomies.split(',');
    var str = '';
    for (var i = 0; i < toshow.length; i++) {
        var sh = toshow[i].trim();
        str += '<span><a href="#" class=\'ntdelbutton\' data-wpcf-i=\'' + i + '\' id=\'post_tag-check-num-' + i + '\'>X</a>&nbsp;' + sh + '</span>';
    }
    jQuery('div.tagchecklist-' + taxonomy, form).html(str);
    jQuery('div.tagchecklist-' + taxonomy + ' a', form).bind('click', function () {
        jQuery('input[name=' + taxonomy + ']', form).val('');
        del = jQuery(this).data('wpcf-i');
        var values = '';
        for (i = 0; i < toshow.length; i++) {
            if (del == i) {
                continue;
            }
            if (values) {
                values += ',';
            }
            values += toshow[i];
        }
        jQuery('input[name=' + taxonomy + ']', form).val(values);
        updateTaxonomies(taxonomy, form);

        return false;
    });

}

function initTaxonomies(values, taxonomy, url, fieldId)
{
    form = jQuery('#' + fieldId.replace(/_field_\d+$/, '')).closest('form');
    jQuery('div.tagchecklist-' + taxonomy).html(values);

    jQuery('input[name=' + taxonomy + ']').val(values);
    updateTaxonomies(taxonomy, form);
    jQuery('input[name=tmp_' + taxonomy + ']').suggest( 
		wptoolset_forms_local.ajaxurl + '?action=wpt_suggest_taxonomy_term&taxonomy=' + taxonomy ,
		{
			resultsClass: 'wpt-suggest-taxonomy-term',
			selectClass: 'wpt-suggest-taxonomy-term-select'
		}
	);
    //https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/195723133/comments#308689055
    if (jQuery('input[name=tmp_' + taxonomy + ']').val() != "")
        jQuery("input[name='new_tax_button_" + taxonomy + "']").trigger("click");
}

toolsetForms.CRED_taxonomy = function () {

    var self = this;

    self.init = function () {
        self._new_taxonomy = new Array();
        jQuery(document).ready(self._document_ready);
    }

    self._document_ready = function () {
        self._initialize_taxonomy_buttons();
        self._initialize_hierachical();
    }

    self._initialize_hierachical = function () {
        self._fill_parent_drop_down()
    }

    self._fill_parent_drop_down = function () {
        jQuery('select.js-taxonomy-parent').each(function () {
            var select = jQuery(this);

            // remove all the options
            jQuery(this).find('option').each(function () {
                if (jQuery(this).val() != '-1') {
                    jQuery(this).remove();
                }
            })

            var taxonomy = jQuery(this).data('taxonomy');

            // Copy all the checkbox values if it's checkbox mode
            jQuery('input[name="' + taxonomy + '\[\]"]').each(function () {
                var id = jQuery(this).attr('id');
                var label = jQuery(this).next('label');
                var level = jQuery(this).closest('ul').data('level');
                var prefix = '';
                if (level) {
                    prefix = "\xA0\xA0" + Array(level).join("\xA0\xA0");
                }
                select.append('<option value="' + jQuery(this).val() + '">' + prefix + label.text() + '</option>');
            })

            // Copy all the select option values if it's select mode
            jQuery('select[name="' + taxonomy + '\[\]"]').find('option').each(function () {
                var id = jQuery(this).val();
                var text = jQuery(this).text();
                select.append('<option value="' + id + '">' + text + '</option>');
            })


        });

    }

    self._initialize_taxonomy_buttons = function () {
        // replace the taxonomy button placeholders with the actual buttons.
        jQuery('.js-taxonomy-button-placeholder').each(function () {
            var placeholder = jQuery(this);
            //https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/195150507/comments
            var label = jQuery(this).attr('data-label');
            //###########################################################################################
            var taxonomy = jQuery(this).data('taxonomy');
            var form = jQuery(this).closest('form');
            var buttons = jQuery('[name="sh_' + taxonomy + '"],[name="btn_' + taxonomy + '"]', form);
            var selectors = [];

            if (buttons.length) {

                buttons.each(function () {
                    var button = jQuery(this, form);

                    //https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/195150507/comments
                    if (label)
                    button.val(label);
                    //##########################################################################################

                    placeholder.replaceWith(button);

                    if (button.hasClass('js-wpt-taxonomy-popular-show-hide')) {
                        if (showHideMostPopularButton(taxonomy, form)) {
                            button.show();
                        }
                    } else {
                        button.show();
                    }

                    // move anything else that should be moved with the button
                    //Responsible of the issue https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/188673095/comments
                    //changed selector
                    //var selector = button.data('after-selector');
                    selectors.push(button.data('after-selector'))
                    if (typeof selector !== 'undefined' && selector.length) {
                        var position = button;
                        jQuery('.' + selectors[0]).detach();
                        jQuery('.' + selectors[0]).insertAfter(button);
                        position = jQuery('.' + selectors[0]);
                        selectors.pop();
                    }
                })
            }
        });
    }

    jQuery(document).on('click', '.js-wpt-hierarchical-taxonomy-add-new-show-hide', function () {
        if (jQuery(this).val() == jQuery(this).data('close')) {
            jQuery(this).val(jQuery(this).data('open')).removeClass('btn-cancel');
        } else {
            jQuery(this).val(jQuery(this).data('close')).addClass('btn-cancel');
        }
        var thiz = jQuery(this), taxonomy = thiz.data('taxonomy');
        self.add_new_show_hide(taxonomy, this);
    });

    self.add_new_show_hide = function (taxonomy, button) {
        var form = jQuery(button).closest('form');
        jQuery('.js-wpt-hierarchical-taxonomy-add-new-' + taxonomy, form).toggle();
        self.hide_parent_button_if_no_terms(taxonomy, button);
    }

    jQuery(document).on('click', '.js-wpt-hierarchical-taxonomy-add-new', function () {
        var thiz = jQuery(this),
                taxonomy = thiz.data('taxonomy');
        self.add_taxonomy(taxonomy, this);
    });
    /*
     jQuery(document).on('keypress', '.js-wpt-new-taxonomy-title', function(e) {
     if( 13 === e.keyCode ) {
     e.preventDefault();
     var thiz = jQuery(this),
     taxonomy = thiz.data( 'taxonomy' );
     self.add_taxonomy( taxonomy, this );
     }
     });
     */

    self.terms_exist = function (taxonomy, button)
    {
        var form = jQuery(button).closest('form');
        var build_what = jQuery(button).data('build_what'), parent = jQuery('[name="new_tax_select_' + taxonomy + '"]', form).val();
        if (build_what === 'checkboxes') {
            var first_checkbox = jQuery('input[name="' + taxonomy + '\[\]"][data-parent="' + parent + '"]:first', form);
            return first_checkbox.length > 0;
        } else {
            var first_option = jQuery('select[name="' + taxonomy + '\[\]"]', form).find('option[data-parent="' + parent + '"]:first');
            return first_option.length > 0;
        }

        return false;
    };

    self._add_new_flag = [];
    self.hide_parent_button_if_no_terms = function (taxonomy, button)
    {
        var form = jQuery(button).closest('form');
        var form_id = form.attr('id');
        if ('undefined' == typeof self._add_new_flag[form_id]) {
            self._add_new_flag[form_id] = '';
        }
        self._add_new_flag[form_id] = !self._add_new_flag[form_id];
        if (self._add_new_flag[form_id] === false) {
            jQuery('[name="new_tax_select_' + taxonomy + '"]', form).hide();
        } else {
            jQuery('[name="new_tax_select_' + taxonomy + '"]', form).show();
        }
    };

    self.add_taxonomy = function (taxonomy, button)
    {
        var form = jQuery(button).closest('form');
        var new_taxonomy = jQuery('[name="new_tax_text_' + taxonomy + '"]', form).val();
        var build_what = jQuery(button).data('build_what');
        new_taxonomy = new_taxonomy.trim();

        if (new_taxonomy == '') {
            return;
        }

        // make sure we don't already have a taxonomy with the same name.
        var exists = false;
        jQuery('input[name="' + taxonomy + '\[\]"]').each(function () {
            var id = jQuery(this).attr('id');
            var label = jQuery('label[for="' + id + '"]', form);

            if (new_taxonomy == label.text()) {
                exists = true
                self._flash_it(label);
            }
        });

        jQuery('select[name="' + taxonomy + '\[\]"]', form).find('option').each(function () {
            if (new_taxonomy == jQuery(this).text()) {
                exists = true;
                self._flash_it(jQuery(this));
            }
        });

        if (exists) {
            jQuery('[name="new_tax_text_' + taxonomy + '"]', form).val('');
            return;
        }

        var parent = jQuery('[name="new_tax_select_' + taxonomy + '"]', form).val(),
                add_position = null,
                add_before = true,
                div_fields_wrap = jQuery('div[data-item_name="taxonomyhierarchical-' + taxonomy + '"]', form),
                level = 0;

        if (build_what === 'checkboxes') {
            //Fix add new leaf
            //https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/188589136/comments
            jQuery('div[data-item_name="taxonomyhierarchical-' + taxonomy + '"] li input[type=checkbox]', form).each(function () {
                if (this.value == parent || this.value == new_taxonomy) {
                    div_fields_wrap = jQuery(this).parent();
                }
            });
            //#########################################################################################

            var new_checkbox = '<li><input data-parent="' + parent + '" class="wpt-form-checkbox form-checkbox checkbox" type="checkbox" name="' + taxonomy + '[]" checked="checked" value="' + new_taxonomy + '"></input><label>' + new_taxonomy + '</label></li>';
            // find the first checkbox sharing parent
            var first_checkbox = jQuery('input[name="' + taxonomy + '\[\]"][data-parent="' + parent + '"]:first', form);
            if (first_checkbox.length == 0) {
                // there are no existing brothers
                // so we need to compose the ul wrapper and append to the parent li
                //add_position = jQuery('input[name="' + taxonomy + '\[\]"][value="' + parent + '"]').closest('li');
                level = jQuery('input[name="' + taxonomy + '\[\]"][value="' + parent + '"]', form).closest('ul').data('level');
                level++;
                new_checkbox = '<ul class="wpt-form-set-children" data-level="' + level + '">' + new_checkbox + '</ul>';
                //first_checkbox = ;
                //add_before = false;
                //add_position = jQuery('input[name="' + taxonomy + '\[\]"][value="' + parent + '"]').closest('li');
                jQuery(new_checkbox).appendTo(div_fields_wrap);
            } else {
                // there are brothers
                // so we need to insert before all of them
                add_position = first_checkbox.closest('li');
                jQuery(new_checkbox).insertBefore(add_position);
            }
            jQuery('[name="new_tax_select_' + taxonomy + '"]', form).show();
        } else if (build_what === 'select') {
            // Select control

            jQuery('select[name="' + taxonomy + '\[\]"]').show();

            var indent = '';
            var first_option = jQuery('select[name="' + taxonomy + '\[\]"]').find('option[data-parent="' + parent + '"]:first', form);
            if (first_option.length == 0) {
                // there a no children of this parent
                first_option = jQuery('select[name="' + taxonomy + '\[\]"]').find('option[value="' + parent + '"]:first', form);
                add_before = false;
                var label = first_option.text();
                for (var i = 0; i < label.length; i++) {
                    if (label[i] == '\xA0') {
                        indent += '\xA0';
                    } else {
                        break;
                    }
                }
                indent += '\xA0';
                indent += '\xA0';
                add_position = jQuery('select[name="' + taxonomy + '\[\]"]', form);
            } else {
                add_position = first_option;
                var label = first_option.text();
                for (var i = 0; i < label.length; i++) {
                    if (label[i] == '\xA0') {
                        indent += '\xA0';
                    } else {
                        break;
                    }
                }
            }

            if (add_position) {
                var new_option = '<option value="' + new_taxonomy + '" selected>' + indent + new_taxonomy + '</option>';
                if (add_before) {
                    jQuery(new_option).insertBefore(add_position);
                } else {
                    jQuery(new_option).appendTo(add_position);
                }
            }
            jQuery('[name="new_tax_select_' + taxonomy + '"]', form).show()
        }

        self._update_hierachy(taxonomy, new_taxonomy, form);

        jQuery('[name="new_tax_text_' + taxonomy + '"]', form).val('');

        self._fill_parent_drop_down();

    }

    self._update_hierachy = function (taxonomy, new_taxonomy, form) {
        var new_taxonomy_input = jQuery('input[name="' + taxonomy + '_hierarchy"]', form);
        if (!new_taxonomy_input.length) {
            // add a hidden field for the hierarchy
            jQuery('<input name="' + taxonomy + '_hierarchy" style="display:none" type="hidden">').insertAfter(jQuery('[name="new_tax_text_' + taxonomy + '"]', form));
            new_taxonomy_input = jQuery('input[name="' + taxonomy + '_hierarchy"]', form);
        }

        var parent = jQuery('[name="new_tax_select_' + taxonomy + '"]', form).val();
        self._new_taxonomy.push(parent + ',' + new_taxonomy);

        var value = '';
        for (var i = 0; i < self._new_taxonomy.length; i++) {
            value += '{' + self._new_taxonomy[i] + '}';
        }
        new_taxonomy_input.val(value);

    }

    self._flash_it = function (element) {
        element.fadeOut(300).fadeIn(300).fadeOut(300).fadeIn(300);
    }

    self.init();

}

toolsetForms.cred_tax = new toolsetForms.CRED_taxonomy();

//Fix https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/188725294/comments
//removed return key press
jQuery(function () {
    var keyStop = {
        8: ":not(input:text, textarea,  input:file, input:password)", // stop backspace = back
        13: "input:text, input:password", // stop enter = submit

        end: null
    };
    jQuery(document).bind("keydown", function (event) {
        var thiz_selector = keyStop[event.which],
		thiz_target = jQuery(event.target);
		
		if ( 
			thiz_target.closest( "form.cred-form" ).length 
			&& thiz_selector !== undefined 
			&& thiz_target.is(thiz_selector)
		) {
			event.preventDefault(); //stop event
		}
		
        return true;
    });
});

