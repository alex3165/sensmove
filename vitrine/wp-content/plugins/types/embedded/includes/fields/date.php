<?php
/*
 * 
 * Date Field
 */

// Set date formats
require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields/date/date-formats.php';

// Include helper functions
require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields/date/functions.php';

// Include calendar
require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields/date/calendar.php';

// Include JS
require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields/date/js.php';

// Parsing date function
if ( !function_exists( 'wpv_filter_parse_date' ) ) {
    require_once WPCF_EMBEDDED_ABSPATH . '/common/wpv-filter-date-embedded.php';
}

/*
 * 
 * 
 * 
 * 
 * 
 * Filters
 */
/*
 * 
 * 
 * This one is called to convert stored timestamp to array,
 * appending Hour and Minute data too.
 * 
 * Called from WPCF_Field:: _get_meta()
 * 
 * Returns array(
 *  'timestamp' => 14435346,
 *  'datepicker' => 'April 9, 2012',
 *  'hour' => 8,
 *  'minute' => 9
 */
add_filter( 'wpcf_fields_type_date_value_get',
        'wpcf_fields_date_value_get_filter', 10, 4 );

/*
 * 
 * Used to convert submitted data (array) to timestamp before saving field.
 * Called from WPCF_Field::_filter_save_value()
 * 
 * Returns timestamp
 */
add_filter( 'wpcf_fields_type_date_value_save',
        'wpcf_fields_date_value_save_filter', 10, 3 );

/*
 * 
 * Built-in Types Conditinal check hook.
 * Used for Conditional value.
 * If array - convert to timestamp.
 * 
 * Returns timestamp
 */
add_filter( 'wpcf_conditional_display_compare_condition_value',
        'wpcf_fields_date_conditional_condition_filter', 10, 5 );
/*
 * This only applied when Checking in AJAX call
 */
if ( defined( 'DOING_AJAX' ) ) {
    add_filter( 'wpcf_conditional_display_compare_meta_value',
            'wpcf_fields_date_conditional_value_filter', 10, 5 );
}

/*
 * 
 * This is added for Custom Conditional Statement.
 * Use more specific hook in evaluate.php
 */
//add_action( 'types_custom_conditional_statement',
//        'wpcf_fields_custom_conditional_statement_hook' );
/*
 * 1.3 Changed to use 'wpv_condition' hook.
 */
add_action( 'wpv_condition', 'wpcf_fields_custom_conditional_statement_hook' );

/**
 * Register data (called automatically).
 * 
 * @return type 
 */
function wpcf_fields_date() {
    $settings = array(
        'id' => 'wpcf-date',
        'title' => __( 'Date', 'wpcf' ),
        'description' => __( 'Date', 'wpcf' ),
        'validate' => array('required', 'date'),
        'meta_key_type' => 'TIME',
        'version' => '1.2',
    );

    if ( !defined( 'WPTOOLSET_FORMS_ABSPATH' ) ) {
        // Allow localized Datepicker if date format does not need translating
        $localized_date_formats = array(
            'Y/m/d', // 2011/12/23
            'm/d/Y', // 12/23/2011
            'd/m/Y', // 23/22/2011
            'd/m/y', // 23/22/11
        );

        $date_format = wpcf_get_date_format();
        $localized_js = array();

        if ( in_array( $date_format, $localized_date_formats ) ) {
            $locale = str_replace( '_', '-', strtolower( get_locale() ) );
            $localized_js = array(
                'src' => file_exists( WPCF_EMBEDDED_RES_ABSPATH . '/js/i18n/jquery.ui.datepicker-'
                        . $locale . '.js' ) ? WPCF_EMBEDDED_RES_RELPATH . '/js/i18n/jquery.ui.datepicker-'
                        . $locale . '.js' : '',
                'deps' => array('jquery-ui-core'),
            );
        }
        $settings['meta_box_js'] = array(
            'wpcf-jquery-fields-date' => array(
                'src' => WPCF_EMBEDDED_RES_RELPATH . '/js/jquery.ui.datepicker.min.js',
                'deps' => array('jquery-ui-core'),
            ),
            'wpcf-jquery-fields-date-inline' => array(
                'inline' => 'wpcf_fields_date_meta_box_js_inline',
            ),
            'wpcf-jquery-fields-date-localization' => $localized_js,
        );
        $settings['meta_box_css'] = array(
            'wpcf-jquery-ui' => array(
                'src' => WPCF_EMBEDDED_RES_RELPATH
                . '/css/jquery-ui/jquery-ui-1.9.2.custom.min.css',
            ),
        );
    }
    return $settings;
}

/**
 * From data for post edit page.
 * 
 * @param type $field 
 * @param type $data
 * @param type $field_object Field instance 
 */
function wpcf_fields_date_meta_box_form( $field, $field_object = null ) {

    /*
     * Added extra fields 'hour' and 'minute'.
     * 
     * If value is not array it is assumed that DB entry is timestamp()
     * and data is converted to array.
     */
    $value = $field['value'] = wpcf_fields_date_value_get_filter( $field['value'],
            $field_object );

    // TODO WPML Set disable_in_form or similar to true, use hook for WPML
    if ( wpcf_wpml_field_is_copied( $field ) ) {
        $attributes = array('style' => 'width:150px;');
    } else {
        $attributes = array('class' => 'wpcf-datepicker', 'style' => 'width:150px;');
    }

    /*
     * 
     * Do not forget to trigger datepicker script
     * Only trigger on AJAX call (inserting new)
     */
    $js_trigger = defined( 'DOING_AJAX' ) ? '<script type="text/javascript">wpcfFieldsDateInit(\'\');</script>' : '';

    /*
     * 
     * 
     * Set Form
     */
    $unique_id = wpcf_unique_id( serialize( $field ) );
    $form = array();
    $form[$unique_id . '-datepicker'] = array(
        '#type' => 'textfield',
        '#title' => '&nbsp;' . $field['name'],
        '#attributes' => $attributes,
        '#name' => 'wpcf[' . $field['slug'] . '][datepicker]',
        '#id' => 'wpcf-date-' . $field['slug'] . '-datepicker-' . $unique_id,
        '#value' => $value['datepicker'],
//        '#inline' => true,
        '#after' => '' . $js_trigger, // Append JS trigger
        '#_validate_this' => true, // Important when H and M are used too
    );

    // Add warning about supported timestamp
    if ( !fields_date_timestamp_neg_supported() ) {
        $_visible = !empty( $value['datepicker'] )
                && intval( $value['timestamp'] ) < 0 ? '' : ' style="display:none;"';
        $form[$unique_id . '-warning'] = array(
            '#type' => 'markup',
            '#markup' => '<div class="wpcf-form-error"' . $_visible
            . '><p>' . __( 'Please enter a date after 1 January 1970', 'wpcf' )
            . '</p></div>',
        );
    }

    /*
     * 
     * If set 'date_and_time' add time
     */
    if ( !empty( $field['data']['date_and_time'] )
            && $field['data']['date_and_time'] == 'and_time' ) {

        // Set parent CSS inline
        $form[$unique_id . '-datepicker']['#inline'] = true;

        $hours = 24;
        $minutes = 60;
        $options = array();

        // Hour
        for ( $index = 0; $index < $hours; $index++ ) {
            $prefix = $index < 10 ? '0' : '';
            $options[$index] = array(
                '#title' => $prefix . strval( $index ),
                '#value' => $index,
            );
        }
        $form[$unique_id . 'time_hour'] = array(
            '#type' => 'select',
            '#title' => __( 'Hour', 'wpcf' ),
            '#inline' => true,
            '#before' => '<br />',
            '#after' => '&nbsp;&nbsp;',
            '#options' => $options,
            '#default_value' => $value['hour'],
            '#name' => 'wpcf[' . $field['slug'] . '][hour]',
            '#id' => 'wpcf-date-' . $field['slug'] . '-select-hour-'
            . $unique_id,
            '#inline' => true,
        );

        // Minutes
        for ( $index = 1; $index < $minutes; $index++ ) {
            $prefix = $index < 10 ? '0' : '';
            $options[$index] = array(
                '#title' => $prefix . strval( $index ),
                '#value' => $index,
            );
        }
        $form[$unique_id . 'time_minute'] = array(
            '#type' => 'select',
            '#title' => __( 'Minute', 'wpcf' ),
            '#after' => '<br /><br />',
            '#inline' => true,
            '#options' => $options,
            '#default_value' => $value['minute'],
            '#name' => 'wpcf[' . $field['slug'] . '][minute]',
            '#id' => 'wpcf-date-' . $field['slug'] . '-minute-'
            . $unique_id,
        );
    }

    return $form;
}

/**
 * Parses date meta.
 * 
 * Use this as main function.
 * 
 * @param int $value timestamp
 * @param type $field Field data
 * $param string $return Specify to return array or specific element of same array
 *          ( timestamp, datepicker, hour, minute )
 * @return mixed array | custom parameter
 */
function wpcf_fields_date_value_get_filter( $value, $field, $return = 'array',
        $context = 'get', $use_cache = true ) {
    global $wpcf;

    /*
     * 
     * Fix for leftover
     */
    if ( $context != 'check_leftover' ) {
        $value = __wpcf_fields_date_check_leftover( $value, $field );
    }

    // Check if cached
    static $cache = array();
    $cache_key = md5( serialize( $value ) );

    if ( isset( $cache[$cache_key] ) && $use_cache ) {
        // Set return data if necessary
        if ( $return != 'array' ) {
            if ( isset( $cache[$cache_key][strval( $return )] ) ) {
                return $cache[$cache_key][strval( $return )];
            }
        } else {
            return $cache[$cache_key];
        }
    }

    $value_cloned = $value;
    $date_format = wpcf_get_date_format();

    if ( empty( $value ) ) {
        $value = array(
            'timestamp' => null,
            'hour' => 8,
            'minute' => 0,
            'datepicker' => '',
        );
    } else if ( is_array( $value ) ) {
        /*
         * Consider this already parsed
         * but check anyway.
         */
        if (
            array_key_exists('datepicker', $value)
            && !array_key_exists('timestamp', $value)
            //Fix date pre-1970 issue
            //https://wp-types.com/forums/topic/pre-1971-dates-throw-error/#post-288989
            //https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/194818870/comments
            && (
                preg_match( '/^\d+$/', $value['datepicker'] )
                || ( 
                 -12219292800 <= $value['datepicker'] 
                 && $value['datepicker'] <= 32535215940
                )
               )
                    ) {
            $value['timestamp'] = $value['datepicker'];
            unset($value['datepicker']);
        }
        $value = wpcf_fields_date_value_check( $value );
    } else {
        $value = array(
            'timestamp' => $value,
            'hour' => adodb_date( 'H', $value ),
            'minute' => adodb_date( 'i', $value ),
            'datepicker' => adodb_date( $date_format, $value ),
        );
        $value = wpcf_fields_date_value_check( $value );
    }

    // Debug
    $wpcf->debug->dates[] = array(
        'original_value' => $value_cloned,
        'value' => $value,
        'field' => $field,
        'context' => $context,
    );

    // Cache it
    if ( $use_cache ) {
        $cache[$cache_key] = $value;
    }

    // Set return data if necessary
    if ( $return != 'array' ) {
        $value = isset( $value[strval( $return )] ) ? $value[strval( $return )] : null;
    }

    return $value;
}

/**
 * View function.
 * 
 * @param type $params 
 */
function wpcf_fields_date_view( $params ) {

    global $wp_locale;

    $defaults = array(
        'format' => get_option( 'date_format' ),
        'style' => '' // add default value
    );
    $params = wp_parse_args( $params, $defaults );
    $output = '';

    // Make sure value is right
    $__timestamp = wpcf_fields_date_value_get_filter( $params['field_value'],
            $params['field'], 'timestamp' );
    if ( is_null( $__timestamp ) ) {
        return '';
    } else {
        $params['field_value'] = $__timestamp;
    }

    switch ( $params['style'] ) {
        case 'calendar':
            $output .= wpcf_fields_date_get_calendar( $params, true, false );
            break;

        default:
            $field_name = '';


            // Extract the Full month and Short month from the format.
            // We'll replace with the translated months if possible.
            $format = $params['format'];
            //$format = str_replace( 'F', '#111111#', $format );
            //$format = str_replace( 'M', '#222222#', $format );

            // Same for the Days
            //$format = str_replace( 'D', '#333333#', $format );
            //$format = str_replace( 'l', '#444444#', $format );

            $date_out = adodb_date( $format, $params['field_value'] );

            //$month = adodb_date( 'm', $params['field_value'] );
            //$month_full = $wp_locale->get_month( $month );
            //$date_out = str_replace( '#111111#', $month_full, $date_out );
            //$month_short = $wp_locale->get_month_abbrev( $month_full );
            //$date_out = str_replace( '#222222#', $month_short, $date_out );

            //$day = adodb_date( 'w', $params['field_value'] );
            //$day_full = $wp_locale->get_weekday( $day );
            //$date_out = str_replace( '#444444#', $day_full, $date_out );
            //$day_short = $wp_locale->get_weekday_abbrev( $day_full );
            //$date_out = str_replace( '#333333#', $day_short, $date_out );

            $output .= $date_out;
            break;
    }

    return $output;
}

/**
 * TinyMCE editor form.
 */
function wpcf_fields_date_editor_callback( $field, $settings ) {

    $data = array(
        'date_formats' => array(),
    );
    $date_formats = apply_filters( 'date_formats',
        array(
            __( 'F j, Y' ),
            'Y/m/d',
            'm/d/Y',
            'd/m/Y',
            'd/m/y',
        )
    );

    // Custom format
    $data['custom'] = isset( $settings['custom'] ) ? $settings['custom'] : get_option( 'date_format' );

    $data['default'] = 'custom';

    foreach ( $date_formats as $k => $format ) {
        $title = date( $format, time() );
        $data['date_formats'][$k] = array(
            'id' => sanitize_title( $format ),
            'title' => $title,
            'format' => $format,
            'default' => isset( $settings['format'] ) && $format == $settings['format'] ? true : false,
        );
        if ( $data['date_formats'][$k]['default'] ) {
            $data['default'] = $data['date_formats'][$k]['id'];
        }
    }
    return array(
        'supports' => array('styling'),
        'tabs' => array(
            'display' => array(
                'menu_title' => __( 'Display', 'wpcf' ),
                'title' => __( 'Display', 'wpcf' ),
                'content' => WPCF_Loader::template( 'editor-modal-date', $data ),
            ),
        )
    );
}

/**
 * Inserts shortcode in editor.
 * 
 * @return type 
 */
function wpcf_fields_date_editor_submit( $data, $field, $context ) {

    $add = ' ';
    $raw = !empty( $data['raw_mode'] );
    $format = get_option( 'date_format' );
    $style = isset( $data['style'] ) ? $data['style'] : 'text';

    if ( !$raw ) {
        $add .= 'style="' . $style . '"';
        if ( $style == 'text' ) {
            if ( isset( $data['format'] ) ) {
                if ( $data['format'] == 'custom' && isset( $data['custom'] ) ) {
                    $format = $data['custom'];
                } else {
                    $format = $data['format'];
                }
            }
            $add .= ' format="' . $format . '"';
        }
    }
    if ( $context == 'usermeta' ) {
        $add .= wpcf_get_usermeta_form_addon_submit();
        $shortcode = wpcf_usermeta_get_shortcode( $field, $add );
    } else {
        $shortcode = wpcf_fields_get_shortcode( $field, $add );
    }
    return $shortcode;
}
