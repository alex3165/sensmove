<?php
/*
 *
 * Calendar view.
 */

/**
 * Calendar view.
 *
 * @global object $wpdb
 * @global type $m
 * @global type $wp_locale
 * @global type $posts
 * @param type $params
 * @param type $initial
 * @param type $echo
 * @return type
 */
function wpcf_fields_date_get_calendar( $params, $initial = true, $echo = true ) {

    global $wpdb, $m, $wp_locale, $posts;

    // wpcf Set our own date
    $monthnum = adodb_date( 'n', $params['field_value'] );
    $year = adodb_date( 'Y', $params['field_value'] );
    $wpcf_date = adodb_date( 'j', $params['field_value'] );

    $cache = array();
    $key = md5( $params['field']['slug'] . $wpcf_date );
    if ( $cache = wp_cache_get( 'get_calendar', 'calendar' ) ) {
        if ( is_array( $cache ) && isset( $cache[$key] ) ) {
            if ( $echo ) {
                echo apply_filters( 'get_calendar', $cache[$key] );
                return;
            } else {
                return apply_filters( 'get_calendar', $cache[$key] );
            }
        }
    }

    if ( !is_array( $cache ) )
        $cache = array();

    if ( isset( $_GET['w'] ) )
        $w = '' . intval( $_GET['w'] );

    // week_begins = 0 stands for Sunday
    $week_begins = intval( get_option( 'start_of_week' ) );

    // Let's figure out when we are
    if ( !empty( $monthnum ) && !empty( $year ) ) {
        $thismonth = '' . zeroise( intval( $monthnum ), 2 );
        $thisyear = '' . intval( $year );
    } elseif ( !empty( $w ) ) {
        // We need to get the month from MySQL
        $thisyear = '' . intval( substr( $m, 0, 4 ) );
        $d = (($w - 1) * 7) + 6; //it seems MySQL's weeks disagree with PHP's
        $thismonth = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT DATE_FORMAT((DATE_ADD(%s, INTERVAL %d DAY) ), '%%m')",
                sprintf('%d0101', $thisyear),
                $d
            )
        );
    } elseif ( !empty( $m ) ) {
        $thisyear = '' . intval( substr( $m, 0, 4 ) );
        if ( strlen( $m ) < 6 )
            $thismonth = '01';
        else
            $thismonth = '' . zeroise( intval( substr( $m, 4, 2 ) ), 2 );
    } else {
        $thisyear = adodb_gmdate( 'Y', current_time( 'timestamp' ) );
        $thismonth = adodb_gmdate( 'm', current_time( 'timestamp' ) );
    }

    $unixmonth = adodb_mktime( 0, 0, 0, $thismonth, 1, $thisyear );
    $last_day = adodb_date( 't', $unixmonth );

    $class = !empty( $params['class'] ) ? ' class="' . $params['class'] . '"' : '';

    /* translators: Calendar caption: 1: month name, 2: 4-digit year */
    $calendar_caption = _x( '%1$s %2$s', 'calendar caption' );
    $calendar_output = '<table id="wp-calendar-' . md5( serialize( func_get_args() ) )
            . '" summary="' . esc_attr__( 'Calendar' ) . '"' . $class . '>
	<caption>' . sprintf( $calendar_caption,
                    $wp_locale->get_month( $thismonth ), adodb_date( 'Y', $unixmonth ) ) . '</caption>
	<thead>
	<tr>';

    $myweek = array();

    for ( $wdcount = 0; $wdcount <= 6; $wdcount++ ) {
        $myweek[] = $wp_locale->get_weekday( ($wdcount + $week_begins) % 7 );
    }

    foreach ( $myweek as $wd ) {
        $day_name = (true == $initial) ? $wp_locale->get_weekday_initial( $wd ) : $wp_locale->get_weekday_abbrev( $wd );
        $wd = esc_attr( $wd );
        $calendar_output .= "\n\t\t<th scope=\"col\" title=\"$wd\">$day_name</th>";
    }

    $calendar_output .= '
	</tr>
	</thead>

	<tfoot>
	<tr>';

    $calendar_output .= '
	</tr>
	</tfoot>

	<tbody>
	<tr>';

    // See how much we should pad in the beginning
    $pad = calendar_week_mod( adodb_date( 'w', $unixmonth ) - $week_begins );
    if ( 0 != $pad )
        $calendar_output .= "\n\t\t" . '<td colspan="' . esc_attr( $pad ) . '" class="pad">&nbsp;</td>';

    $daysinmonth = intval( adodb_date( 't', $unixmonth ) );
    for ( $day = 1; $day <= $daysinmonth; ++$day ) {
        if ( isset( $newrow ) && $newrow )
            $calendar_output .= "\n\t</tr>\n\t<tr>\n\t\t";
        $newrow = false;

        if ( $day == gmdate( 'j', current_time( 'timestamp' ) ) && $thismonth == gmdate( 'm',
                        current_time( 'timestamp' ) ) && $thisyear == gmdate( 'Y',
                        current_time( 'timestamp' ) ) )
            $calendar_output .= '<td id="today">';
        else
            $calendar_output .= '<td>';

        // wpcf
        if ( $wpcf_date == $day ) {
            $calendar_output .= '<a href="javascript:void(0);">' . $day . '</a>';
        } else {
            $calendar_output .= $day;
        }

        $calendar_output .= '</td>';

        if ( 6 == calendar_week_mod( adodb_date( 'w',
                                adodb_mktime( 0, 0, 0, $thismonth, $day, $thisyear ) ) - $week_begins ) )
            $newrow = true;
    }

    $pad = 7 - calendar_week_mod( adodb_date( 'w',
                            adodb_mktime( 0, 0, 0, $thismonth, $day, $thisyear ) ) - $week_begins );
    if ( $pad != 0 && $pad != 7 )
        $calendar_output .= "\n\t\t" . '<td class="pad" colspan="' . esc_attr( $pad ) . '">&nbsp;</td>';

    $calendar_output .= "\n\t</tr>\n\t</tbody>\n\t</table>";

    $cache[$key] = $calendar_output;
    wp_cache_set( 'get_calendar', $cache, 'calendar' );

    if ( $echo )
        echo apply_filters( 'get_calendar', $calendar_output );
    else
        return apply_filters( 'get_calendar', $calendar_output );
}
