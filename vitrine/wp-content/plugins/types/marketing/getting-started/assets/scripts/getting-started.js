/**
 *
 */
jQuery( document ).ready(function($) {

    if ( 'undefined' != typeof marketing_getting_started ) {
        $('[name="'+marketing_getting_started.id+'"]').on('change', function() {
            $('#wcpf-getting-started-button').removeClass('disabled');
        });
        $('#wcpf-getting-started-button').on('click', function() {
            $(this).closest('form').submit();
        });
    }

    if ( 'undefined' != typeof types_activate ) {
        var html = '<div>';
        html += fill_with_tag('h2',types_activate.header);
        html += fill_with_tag('p',types_activate.text);
        var buttons = fill_with_tag('a', types_activate.button_primary_text, 'button button-primary');
        buttons += ' ';
        buttons += fill_with_tag('a', types_activate.button_dismiss_text, 'button-dismiss');
        html += fill_with_tag('p', buttons, 'buttons');
        html += '</div>';
        html += fill_with_tag('span', fill_with_tag('span', '', 'icon-types-logo'), 'logo');
        var parent = $('#message').html(html).removeClass('updated').addClass('toolset-message-after-activate');
        $('.button-primary', parent).on('click', function() {
            document.location = types_activate.button_primary_url;
            return false;
        });
        $('.button-dismiss', parent).on('click', function() {
            $('#message').detach();
            return false;
        });

    }

    function fill_with_tag(tag, text, css_class) {
        var html = '<'+tag;
        if ( css_class ) {
            html += ' class="'+css_class+'"';
        }
        html += '>'+text+'</'+tag+'>';
        return html;
    }
});

