<?php
/*
 * Various functions for Date Field.
 */

/**
 * Calculate time
 * 
 * @param array $value Full data
 */
function wpcf_fields_date_calculate_time( $value ) {

    extract( $value );

    // Empty is only on new post
    if ( empty( $timestamp ) ) {
        return null;
    }

    // Fix hour and minute
    if ( empty( $hour ) || strval( $hour ) == '00' ) {
        $hour = 0;
    }
    if ( empty( $minute ) || strval( $minute ) == '00' ) {
        $minute = 0;
    }
	
	$timestamp_date = adodb_date( 'dmY', $timestamp );
	$date = adodb_mktime( intval( $hour ), intval( $minute ), 0, substr( $timestamp_date, 2, 2 ), substr( $timestamp_date, 0, 2 ), substr( $timestamp_date, 4, 4 ) );
	$timestamp = $date;

    // Add Hour and minute
    //$timestamp = $timestamp + (60 * 60 * intval( $hour )) + (60 * intval( $minute ));

    return $timestamp;
}

/**
 * Converts date to time on post saving.
 * 
 * @param array $value Use 'datepicker' to convert to timestamp.
 * @return int timestamp 
 */
function wpcf_fields_date_value_save_filter( $value, $field, $field_object ) {

    if ( defined( 'WPTOOLSET_FORMS_VERSION' ) ) {
        if ( empty( $value ) || empty( $value['datepicker'] ) ) {
            return false;
        }
		if ( is_numeric( $value['datepicker'] ) ) {
			$timestamp = $value['datepicker'];
		} else {
			$timestamp = wptoolset_strtotime( $value['datepicker'] );
		}
        // Append Hour and Minute
        if ( $timestamp !== false && isset( $value['hour'] ) && isset( $value['minute'] ) ) {
            $timestamp_date = adodb_date( 'dmY', $timestamp );
			$date = adodb_mktime( intval( $value['hour'] ), intval( $value['minute'] ), 0, substr( $timestamp_date, 2, 2 ), substr( $timestamp_date, 0, 2 ), substr( $timestamp_date, 4, 4 ) );
			$timestamp = $date;
			/*
			$date = new DateTime( $value['datepicker'] );
            try {
                $date->add( new DateInterval( 'PT' . intval( $value['hour'] ) . 'H' . intval( $value['minute'] ) . 'M' ) );
            } catch (Exception $e) {
                return $timestamp;
            }
            $timestamp = $date->format( "U" );
			*/
        }
        return $timestamp;
    }
	// I understand this below is not executed anymore?
    global $wpcf;

    // Remove additional meta if any
    if ( isset( $field_object->post->ID ) ) {
        delete_post_meta(
                $field_object->post->ID,
                '_wpcf_' . $field_object->cf['id'] . '_hour_and_minute' );
    }

    if ( empty( $value ) || empty( $value['datepicker'] ) ) {
        return false;
    }

    $value['timestamp'] = wpcf_fields_date_convert_datepicker_to_timestamp( $value['datepicker'] );

    if ( !wpcf_fields_date_timestamp_is_valid( $value['timestamp'] ) ) {
        $wpcf->debug->errors['date_save_failed'][] = array(
            'value' => $value,
            'field' => $field,
        );
        return false;
    }

    // Append Hour and Minute
    if ( isset( $value['hour'] ) && isset( $value['minute'] ) ) {
        $value['timestamp'] = wpcf_fields_date_calculate_time( $value );
    }

    return $value['timestamp'];
}

/**
 * Filters conditional edited field value for built-in Types Conditinal check.
 * 
 * @param type $value
 * @param type $field
 * @param type $operation
 * @param type $conditional_field
 * @param type $post
 * @return type 
 */
function wpcf_fields_date_conditional_value_filter( $value, $field, $operation,
        $field_compared, $post ) {

    global $wpcf;

    $field = wpcf_admin_fields_get_field( $wpcf->field->__get_slug_no_prefix( $field ) );
    if ( !empty( $field ) && isset( $field['type'] ) && $field['type'] == 'date' && isset($value['datepicker']) ) {
        $value['timestamp'] = wpcf_fields_date_convert_datepicker_to_timestamp( $value['datepicker'] );
        $value = wpcf_fields_date_calculate_time( $value );
    }
    return $value;
}

/**
 * Filters conditional condition value - converts dates to timestamps.
 * 
 * @param type $value
 * @param type $field
 * @param type $operation
 * @param type $field_compared
 * @param type $post
 */
function wpcf_fields_date_conditional_condition_filter( $value, $field,
        $operation, $field_compared, $post ) {
    global $wpcf;

    $field = wpcf_admin_fields_get_field( $wpcf->field->__get_slug_no_prefix( $field ) );
    if ( !empty( $field ) && isset( $field['type'] ) && $field['type'] == 'date' ) {
        $_value = wpcf_fields_date_convert_datepicker_to_timestamp( $value );
        if ( $_value ) {
            $value = $_value;
        } else {
            $value = wpcf_fields_date_value_get_filter( $value, $field,
                    'timestamp' );
        }
    }
    return $value;
}

/**
 * Add post meta hook if Custom Conditinal Statement used.
 */
function wpcf_fields_custom_conditional_statement_hook() {
    // Enqueue after first filters in evaluate.php
    add_filter( 'get_post_metadata',
            'wpcf_fields_date_custom_conditional_statement_filter', 20, 4 );
}

/**
 * Custom Conditinal Statement hook returns timestamp if array.
 * 
 * NOTE that $null is already filtered to use $_POST values
 * at priority 10.
 * 
 * @param type $null
 * @param type $object_id
 * @param type $meta_key
 * @param type $single
 * @return mixed timestamp or $null
 */
function wpcf_fields_date_custom_conditional_statement_filter( $null,
        $object_id, $meta_key, $single ) {

    global $wpcf;

    $field = wpcf_admin_fields_get_field( $wpcf->field->__get_slug_no_prefix( $meta_key ) );

    if ( !empty( $null ) && !empty( $field ) && isset( $field['type'] ) && $field['type'] == 'date' ) {
        if ( is_array( $null ) && !isset( $null['datepicker'] ) ) {
            $null = array_shift( $null );
        }
        $null = wpcf_fields_date_value_get_filter( $null, $field, 'timestamp' );
        if ( !is_numeric( $null ) ) {
            $null = -1;
        }
        /**
         * be sure do not return string if array is expected!
         */
        if ( !$single && !is_array($null) ) {
            return array($null);
        }
    }
    return $null;
}

/**
 * Returns most suitable date format.
 * 
 * @global type $supported_date_formats
 * @return string
 */
function wpcf_get_date_format() {
    global $supported_date_formats;

    $date_format = get_option( 'date_format' );
    if ( !in_array( $date_format, $supported_date_formats ) ) {
        // Choose the Month day, Year fromat
        $date_format = 'F j, Y';
    }

    return $date_format;
}

/*
 * 
 * 
 *  TODO DOCUMENT
 */

function wpcf_get_date_format_text() {
    global $supported_date_formats, $supported_date_formats_text;

    $date_format = get_option( 'date_format' );
    if ( !in_array( $date_format, $supported_date_formats ) ) {
        // Choose the Month day, Year fromat
        $date_format = 'F j, Y';
    }

    return $supported_date_formats_text[$date_format];
}

function _wpcf_date_convert_wp_to_js( $date_format ) {
    $date_format = str_replace( 'd', 'dd', $date_format );
    $date_format = str_replace( 'j', 'd', $date_format );
    $date_format = str_replace( 'l', 'DD', $date_format );
    $date_format = str_replace( 'm', 'mm', $date_format );
    $date_format = str_replace( 'n', 'm', $date_format );
    $date_format = str_replace( 'F', 'MM', $date_format );
    $date_format = str_replace( 'Y', 'yy', $date_format );

    return $date_format;
}

/**
 *
 * Convert a format from date() to strftime() format
 *
 */
function wpcf_date_to_strftime( $format ) {

    $format = str_replace( 'd', '%d', $format );
    $format = str_replace( 'D', '%a', $format );
    $format = str_replace( 'j', '%e', $format );
    $format = str_replace( 'l', '%A', $format );
    $format = str_replace( 'N', '%u', $format );
    $format = str_replace( 'w', '%w', $format );

    $format = str_replace( 'W', '%W', $format );

    $format = str_replace( 'F', '%B', $format );
    $format = str_replace( 'm', '%m', $format );
    $format = str_replace( 'M', '%b', $format );
    $format = str_replace( 'n', '%m', $format );

    $format = str_replace( 'o', '%g', $format );
    $format = str_replace( 'Y', '%Y', $format );
    $format = str_replace( 'y', '%y', $format );

    return $format;
}

/**
 * Checks if Date value array has all required elements.
 * 
 * It will re-create missing elements if possible.
 * If not possible - will return empty Date.
 * 
 * @param array $a
 * @return null
 */
function wpcf_fields_date_value_check( $a ) {

    $required = array('timestamp', 'datepicker', 'hour', 'minute');
    $empty_date = array(
        'timestamp' => null,
        'hour' => 8,
        'minute' => 0,
        'datepicker' => '',
    );

    // Return empty date if can not be calculated
    if ( empty( $a['timestamp'] ) && empty( $a['datepicker'] ) ) {
        return $empty_date;
    }

    // Loop over and check if some missing or need fix
    foreach ( $required as $key ) {
        switch ( $key ) {
            case 'timestamp':
                // If not set or malformed - create from datepicker
                if ( !isset( $a[$key] ) || !wpcf_fields_date_timestamp_is_valid( $a[$key] ) ) {
                    $_t = wpcf_fields_date_convert_datepicker_to_timestamp( $a['datepicker'] );
                    if ( !$_t ) {
                        // Failed converting
                        return $empty_date;
                    }
                    $a['timestamp'] = $_t;
                }
                $a['timestamp'] = $a['timestamp'];
                break;

            case 'datepicker':
                // If not set - create it from timestamp.
                if ( empty( $a[$key] ) ) {
                    $_d = wpcf_fields_date_convert_timestamp_to_datepicker( $a['timestamp'] );
                    if ( !$_d ) {
                        // Failed converting
                        return $empty_date;
                    }
                    $a['datepicker'] = $_d;
                }
                // Check if valid (already set value)
				/*
                if ( !wpcf_fields_date_datepicker_is_valid( $a['datepicker'] ) ) {
                    return $empty_date;
                }
				*/
                $a['datepicker'] = strval( $a['datepicker'] );
                break;

            case 'hour':
                $_h = adodb_date( 'H', $a['timestamp'] );
                $a['hour'] = $_h;
                break;

            case 'minute':
                $_m = adodb_date( 'i', $a['timestamp'] );
                $a['minute'] = $_m;
                break;

            default:
                break;
        }
    }

    // Final test - make sure timestamp matches Datepicker
    if ( adodb_date( wpcf_get_date_format(), $a['timestamp'] ) != $a['datepicker'] ) {
        // In this case we'll give advantage to timestamp
        $a['datepicker'] = wpcf_fields_date_convert_timestamp_to_datepicker( $a['timestamp'] );
        if ( !$a['timestamp'] ) {
            // Failed converting
            return $empty_date;
        }
    }

    ksort( $a );
    return $a;
}

/**
 * Converts timestamp to Datepicker and checks if valid.
 * 
 * @param type $timestamp
 * @return boolean
 */
function wpcf_fields_date_convert_timestamp_to_datepicker( $timestamp ) {
    // Check if timestamp valid
    if ( !wpcf_fields_date_timestamp_is_valid( $timestamp ) ) {
        return false;
    }
    $_d = adodb_date( wpcf_get_date_format(), $timestamp );
	/*
    if ( !wpcf_fields_date_datepicker_is_valid( $_d ) ) {
        // Failed converting
        return false;
    }
	*/
    return $_d;
}

/**
 * Converts Datepicker to timestamp and checks if valid.
 * @param type $datepicker
 * @return boolean
 */
function wpcf_fields_date_convert_datepicker_to_timestamp( $datepicker ) {
    $date_format = wpcf_get_date_format();
    if ( $date_format == 'd/m/Y' ) {
        // strtotime requires a dash or dot separator to determine dd/mm/yyyy format
        $datepicker = str_replace( '/', '-', $datepicker );
    }
    $_t = strtotime( strval( $datepicker ) );
    if ( $_t == false || !wpcf_fields_date_timestamp_is_valid( $_t ) ) {
        // Failed converting
        return false;
    }
    return $_t;
}

/**
 * Checks if timestamp is numeric and within range.
 * 
 * @param type $timestamp
 * @return type
 */
function wpcf_fields_date_timestamp_is_valid( $timestamp ) {
    /*
     * http://php.net/manual/en/function.strtotime.php
     * The valid range of a timestamp is typically
     * from Fri, 13 Dec 1901 20:45:54 UTC
     * to Tue, 19 Jan 2038 03:14:07 UTC.
     * (These are the dates that correspond to the minimum
     * and maximum values for a 32-bit signed integer.)
     * Additionally, not all platforms support negative timestamps,
     * therefore your date range may be limited to no earlier than
     * the Unix epoch.
     * This means that e.g. dates prior to Jan 1, 1970 will not
     * work on Windows, some Linux distributions,
     * and a few other operating systems.
     * PHP 5.1.0 and newer versions overcome this limitation though. 
     */
    // MIN 'Jan 1, 1970' - 0 | Fri, 13 Dec 1901 20:45:54 UTC
    $_min_timestamp = fields_date_timestamp_neg_supported() ? -2147483646 : 0;
    // MAX 'Tue, 19 Jan 2038 03:14:07 UTC' - 2147483647
    $_max_timestamp = 2147483647;
	return WPToolset_Field_Date_Scripts::_isTimestampInRange($timestamp);
    //return is_numeric( $timestamp ) && $_min_timestamp <= $timestamp && $timestamp <= $_max_timestamp;
}

/**
 * Checks if Datepicker is valid by converting it to timestamp.
 * @param type $datepicker
 * @return boolean
 */
function wpcf_fields_date_datepicker_is_valid( $datepicker ) {
    return (bool) wpcf_fields_date_convert_datepicker_to_timestamp( $datepicker );
}

/**
 * Fix due to a bug saving date as array.
 * 
 * BUGS
 * 'timestamp' is saved without Hour and Minute appended.
 * 
 * @param type $value
 * @param type $field
 */
function __wpcf_fields_date_check_leftover( $value, $field, $use_cache = true ) {
    
    if ( empty( $value )) {
        return $value;
    }

    if ( !is_object( $field ) ) {
        $post_id = wpcf_get_post_id();
        $field_id = isset( $field['id'] ) ? $field['id'] : false;
        $meta_id = isset( $field['__meta_id'] ) ? $field['__meta_id'] : false;
    } else {
        $post_id = isset( $field->meta_object->post_id ) ? $field->meta_object->post_id : false;
        $field_id = isset( $field->cf['id'] ) ? $field->cf['id'] : false;
        $meta_id = isset( $field->meta_object->meta_id ) ? $field->meta_object->meta_id : false;
    }

    if ( empty( $post_id ) || empty( $meta_id ) || empty( $field_id ) ) {
        return $value;
    }

    $field_slug = wpcf_types_get_meta_prefix() . $field_id;

    // Check if cached
    static $cache = array();
    $cache_key = $meta_id;

    if ( isset( $cache[$cache_key] ) && $use_cache ) {
        return $cache[$cache_key];
    }

    $_meta = wpcf_get_post_meta( $post_id,
            '_wpcf_' . $field_id . '_hour_and_minute', true );

    /*
     * If meta exists - it's outdated value
     * and Hour and Minute should be appended and removed.
     */
    if ( !empty( $_meta ) && is_array( $_meta ) && isset( $_meta[$meta_id] ) ) {

        $meta = $_meta[$meta_id];

        // Return empty date if can not be calculated
        if ( !empty( $meta['timestamp'] ) || !empty( $meta['datepicker'] ) ) {

            $meta['timestamp'] = wpcf_fields_date_value_get_filter( $meta,
                    $field, 'timestamp', 'check_leftover' );

            // Check if calculation needed
            if ( (isset( $meta['hour'] )
                    && $meta['hour'] != adodb_date( 'H', $meta['timestamp'] ) )
                    || (isset( $meta['minute'] )
                    && $meta['minute'] != adodb_date( 'i', $meta['timestamp'] ) ) ) {

                $value = wpcf_fields_date_calculate_time( $meta );
            }
        }
    }

    // Cache it
    if ( $use_cache ) {
        $cache[$cache_key] = $value;
    }

    return $value;
}
