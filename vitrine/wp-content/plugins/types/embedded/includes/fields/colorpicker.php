<?php

/**
 * Register data (called automatically).
 * 
 * @return type 
 */
function wpcf_fields_colorpicker() {
    return array(
        'id' => 'wpcf-colorpicker',
        'title' => __( 'Colorpicker', 'wpcf' ),
        'description' => __( 'Colorpicker', 'wpcf' ),
        'validate' => array('required'),
        'meta_box_js' => array(
            'wpcf-jquery-fields-colorpicker' => array(
                'inline' => 'wpcf_fields_colorpicker_render_js',
            ),
        ),
    );
}

/**
 * Meta box form.
 * 
 * @param type $field
 * @return string 
 */
function wpcf_fields_colorpicker_meta_box_form( $field ) {
    $form = array();
    $form['name'] = array(
        '#type' => 'textfield',
        '#name' => 'wpcf[' . $field['slug'] . ']',
        '#attributes' => array('class' => 'js-types-colorpicker', 'style' => 'width:100px;'),
        '#after' => '',
    );
    wpcf_fields_colorpicker_enqueue_scripts();
	//By Gen: changed minimal version from 3.4 to 3.5, because colorbox not works in 3.4.2
    if ( wpcf_compare_wp_version( '3.5', '<' ) ) {
        $form['name']['#after'] .= '<a href="#" class="button-secondary js-types-pickcolor">' . __( 'Pick color',
                        'wpcf' ) . '</a><div class="js-types-cp-preview types-cp-preview" style="background-color:' . $field['value'] . '"></div>';
        wp_enqueue_script( 'farbtastic' );
        wp_enqueue_style( 'farbtastic' );
    } else {
        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_style( 'wp-color-picker' );
        if ( defined( 'DOING_AJAX' ) ) {
            $form['name']['#after'] .= '<script type="text/javascript">typesPickColor.init();</script>';
        }
    }
    return $form;
}

function wpcf_fields_colorpicker_enqueue_scripts() {
	//By Gen: changed minimal version from 3.4 to 3.5, because colorbox not works in 3.4.2
    if ( wpcf_compare_wp_version( '3.5', '<' ) ) {
        wp_enqueue_script( 'farbtastic' );
        wp_enqueue_style( 'farbtastic' );
    } else {
        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_style( 'wp-color-picker' );
    }
}

function wpcf_fields_colorpicker_render_js() {
	//By Gen: changed minimal version from 3.4 to 3.5, because colorbox not works in 3.4.2
    if ( wpcf_compare_wp_version( '3.5', '<' ) ) {
        wpcf_fields_colorpicker_js_farbtastic();
    } else {
        wpcf_fields_colorpicker_js();
    }
}

/**
 * Colorpicker JS.
 */
function wpcf_fields_colorpicker_js() {

    ?>
    <script type="text/javascript">
        /* <![CDATA[ */
        var typesPickColor = (function($) {
            function init() {
                if ($.isFunction($.fn.wpColorPicker)) {
                    $('.js-types-colorpicker').each(function(){
                        $(this).not(':disabled').wpColorPicker();
                    });
                }
            }
            return {init: init}
        })(jQuery);
        (function($) {
            $(document).ready(function() {
                typesPickColor.init();
            });
        })(jQuery);
        /* ]]> */
    </script>
    <?php
}

/**
 * Pre WP 3.5 JS.
 */
function wpcf_fields_colorpicker_js_farbtastic() {

    ?>
    <div id="types-color-picker" style="display:none; background-color: #FFF; width:220px; padding: 10px;"></div>
    <script type="text/javascript">
        /* <![CDATA[ */
        var farbtasticTypes;
        var typesPickColor = (function($) {
            var el;
            function set(color) {
                el.parent().find('.js-types-cp-preview').css('background-color', color)
                        .parent().find('.js-types-colorpicker').val(color);
                toggleButton();
            }
            function show(element) {
                el = element;
                var offset = el.offset();
                farbtasticTypes.setColor(el.parent().find('.js-types-colorpicker').val());
                $('#types-color-picker').toggle().offset({left: offset.left, top: Math.round(offset.top + 25)});
                toggleButton();
            }
            function toggleButton() {
                $('.js-types-pickcolor').text('<?php echo esc_js( __( 'Pick color', 'wpcf' ) ); ?>');
                el.text($('#types-color-picker').is(':visible') ? '<?php echo esc_js( __( 'Done', 'wpcf' ) ); ?>' : '<?php echo esc_js( __( 'Pick color', 'wpcf' ) ); ?>');
            }
            return {set: set, show: show}
        })(jQuery);
        (function($) {
            if ($.isFunction($.fn.farbtastic)) {
            $(document).ready(function() {
                $('#post').on('click', '.js-types-pickcolor', function(e) {
                    e.preventDefault();
                    typesPickColor.show($(this));
                    return false;
                });
                farbtasticTypes = $.farbtastic('#types-color-picker', typesPickColor.set);
            });
            }
        })(jQuery);
        /* ]]> */
    </script>
    <?php
}

/**
 * View function
 * 
 * @param type $params
 * @return string
 */
function wpcf_fields_colorpicker_view( $params ) {
    if ( empty( $params['field_value'] ) || strpos( $params['field_value'], '#' ) !== 0 || !( strlen( $params['field_value'] ) == 4 || strlen( $params['field_value'] ) == 7 ) ) {
        return '__wpcf_skip_empty';
    }
    return $params['field_value'];
}