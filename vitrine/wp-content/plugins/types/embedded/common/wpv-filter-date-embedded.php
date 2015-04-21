<?php
if (!function_exists('wpv_filter_parse_date')) {


    if (!function_exists('adodb_mktime')) {
        require_once(dirname(__FILE__) . "/toolset-forms/lib/adodb-time.inc.php");
    }
    /**
     * Helper function for parsing dates.
     *
     * Possible inputs:
     *
     * NOW()
     * TODAY()    (time at 00:00 today)
     * FUTURE_DAY(1)
     * PAST_DAY(1)
     * THIS_MONTH()   (time at 00:00 on first day of this month)
     * FUTURE_MONTH(1)
     * PAST_MONTH(1)
     * THIS_YEAR()   (time at 00:00 on first day of this year)
     * FUTURE_YEAR(1)
     * PAST_YEAR(1)
     * SECONDS_FROM_NOW(1)
     * MONTHS_FROM_NOW(1)
     * YEARS_FROM_NOW(1)
     * DATE(dd,mm,yyyy)
     * DATE(dd,mm,yyyy)    as per Views
     * DATE('dd/mm/yyyy', 'd/m/Y')
     * DATE('mm/dd/yyyy', 'm/d/Y')
     *
     * @param int timestamp $date_format
     */
    function wpv_filter_parse_date($date_format)
    {
        $date_format = stripcslashes($date_format);
        $occurences = preg_match_all('/(\\w+)\(([^\)]*)\)/', $date_format, $matches);

        if ($occurences > 0) {
            for ($i = 0; $i < $occurences; $i++) {
                $date_func = $matches[1][$i];
                // remove comma at the end of date value in case is left there
                $date_value = isset( $matches[2] ) ? rtrim( $matches[2][$i], ',' ) : '';
                $resulting_date = false;

                switch (strtoupper($date_func)) {
                    case "NOW":
                        $resulting_date = current_time('timestamp');
                        break;
                    case "TODAY":
                        $resulting_date = adodb_mktime(0, 0, 0, date_i18n('m'), date_i18n('d'), date_i18n('Y'));
                        break;
                    case "FUTURE_DAY":
                        $resulting_date = adodb_mktime(0, 0, 0, date_i18n('m'), date_i18n('d') + $date_value, date_i18n('Y'));
                        break;
                    case "PAST_DAY":
                        $resulting_date = adodb_mktime(0, 0, 0, date_i18n('m'), date_i18n('d') - $date_value, date_i18n('Y'));
                        break;
                    case "THIS_MONTH":
                        $resulting_date = adodb_mktime(0, 0, 0, date_i18n('m'), 1, date_i18n('Y'));
                        break;
                    case "FUTURE_MONTH":
                        $resulting_date = adodb_mktime(0, 0, 0, date_i18n('m') + $date_value, 1, date_i18n('Y'));
                        break;
                    case "PAST_MONTH":
                        $resulting_date = adodb_mktime(0, 0, 0, date_i18n('m') - $date_value, 1, date_i18n('Y'));
                        break;
                    case "THIS_YEAR":
                        $resulting_date = adodb_mktime(0, 0, 0, 1, 1, date_i18n('Y'));
                        break;
                    case "FUTURE_YEAR":
                        $resulting_date = adodb_mktime(0, 0, 0, 1, 1, date_i18n('Y') + $date_value);
                        break;
                    case "PAST_YEAR":
                        $resulting_date = adodb_mktime(0, 0, 0, 1, 1, date_i18n('Y') - $date_value);
                        break;
                    case "SECONDS_FROM_NOW":
                        $resulting_date = current_time('timestamp') + $date_value;
                        break;
                    case "MONTHS_FROM_NOW":
                        $resulting_date = adodb_mktime(0, 0, 0, date_i18n('m') + $date_value, date_i18n('d'), date_i18n('Y'));
                        break;
                    case "YEARS_FROM_NOW":
                        $resulting_date = adodb_mktime(0, 0, 0, date_i18n('m'), date_i18n('d'), date_i18n('Y') + $date_value);
                        break;
                    case "DATE":
                        $date_object = wpv_filter_get_date_and_format($date_value);
                        $date_value = $date_object->date;
                        $format = $date_object->format;
                        $resulting_date = wpv_filter_parse_date_get_resulting_date( $date_value, $format );
                        break;

                }
                if ($resulting_date != false) {
                    $date_format = str_replace($matches[0][$i], $resulting_date, $date_format);
                }
            }
        }

        return $date_format;
    }

    if( !function_exists('wpv_filter_parse_date_get_resulting_date') )
    {
        function wpv_filter_parse_date_get_resulting_date( $date_value, $format )
        {
            $date_value = (string) $date_value;

            if( !$format && strpos($date_value, ',') !== false ){

                $date_parts = explode(',', $date_value);
                $ret = adodb_mktime(0, 0, 0, $date_parts[1], $date_parts[0], $date_parts[2]);
                return $ret;
            }
            else
            {
                // just in case the Parser is not loaded yet
                if( class_exists('Toolset_DateParser') === false )
                {
                    require_once(dirname(__FILE__) . "/expression-parser/parser.php");
                }

                $date_string = trim( trim( str_replace(',', '/', $date_value), "'" ) );

                $date = Toolset_DateParser::parseDate( $date_string, $format );
                if( is_object($date) && method_exists( $date, 'getTimestamp' ) )
                {
                    $timestamp = $date->getTimestamp();// NOTE this timestamp construction should be compatible with the adodb_xxx functions
                    return $timestamp;
                }

                return $date_value;
            }
        }
    }

    if( !function_exists('wpv_filter_get_date_format') )
    {
        function wpv_filter_get_date_and_format($date_value)
        {

            $date_value = str_replace("'", '', $date_value);

            $ret = new stdClass();
            $ret->date = $date_value;
            $ret->format = false;

            $last = strrpos( $date_value, ',' );

            if( $last === false ) return $ret;

            $temp = trim( trim( substr($date_value, $last ), ',' ) );

            if( is_numeric( $temp ) )
            {
                return $ret;
            }
            else{
                $ret->date = trim( substr($date_value, 0, $last ) );
                $ret->format = trim(  trim( $temp, ',') );

                return $ret;
            }

            return $date_value;
        }
    }
}