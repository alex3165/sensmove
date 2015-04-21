<?php
if (!function_exists('adodb_mktime')) {
	require_once WPTOOLSET_FORMS_ABSPATH . '/lib/adodb-time.inc.php';
}

class WPToolset_Field_Date_Scripts
{

    public static $_supported_date_formats = array(
        'F j, Y', //December 23, 2011
        'Y/m/d', // 2011/12/23
        'm/d/Y', // 12/23/2011
        'd/m/Y', // 23/22/2011
        'd/m/y', // 23/22/11
    );

    public $_supported_date_formats_text = array(
        'F j, Y' => 'Month dd, yyyy',
        'Y/m/d' => 'yyyy/mm/dd',
        'm/d/Y' => 'mm/dd/yyyy',
        'd/m/Y' => 'dd/mm/yyyy',
        'd/m/y' => 'dd/mm/yy',
    );

    // 15/10/1582 00:00 - 31/12/3000 23:59
    protected static $_mintimestamp = -12219292800;
    protected static $_maxtimestamp =  32535215940;

    public function __construct()
    {
        global $pagenow;
		if ( 
		//for front-end
		!is_admin() ||
		//for edit group pages 
		( ( isset($_GET['page']) && ($_GET['page'] == 'wpcf-edit-usermeta' || $_GET['page'] == 'wpcf-edit') ) ||
		//for edit pages including profile pages
		($pagenow == 'profile.php' || $pagenow == 'post-new.php' || $pagenow == 'user-edit.php' || $pagenow == 'user-new.php' || $pagenow == 'post.php' || $pagenow == 'admin-ajax.php') && is_admin() )  ){	
        add_action( 'admin_enqueue_scripts', array( $this,'date_enqueue_scripts' ) );
            if ( defined('CRED_FE_VERSION')) {
                add_action( 'wp_enqueue_scripts', array( $this, 'date_enqueue_scripts' ) );
            }
		}
		$this->localization_slug = false;
    }

    public function date_enqueue_scripts()
    {
        /**
         * prevent load scripts on custom field group edit screen
         */
        if ( is_admin() ) {
            $screen = get_current_screen();
            if ( 'types_page_wpcf-edit' == $screen->id ) {
                return;
            }
        }
        /**
         * styles
         */
        wp_register_style(
            'wptoolset-field-datepicker',
            WPTOOLSET_FORMS_RELPATH . '/css/wpt-jquery-ui/datepicker.css',
            array(),
            WPTOOLSET_FORMS_VERSION
        );
        /**
         * scripts
         */
        wp_register_script(
            'wptoolset-field-date',
            WPTOOLSET_FORMS_RELPATH . '/js/date.js',
            array('jquery-ui-datepicker', 'wptoolset-forms'),
            WPTOOLSET_FORMS_VERSION,
            true
        );
        // Localize datepicker
        if ( in_array( self::getDateFormat(), self::$_supported_date_formats ) ) {
            /*
			$locale = str_replace( '_', '-', strtolower( get_locale() ) );
            $file = WPTOOLSET_FORMS_ABSPATH . '/js/i18n/jquery.ui.datepicker-' . $locale . '.js';
            if ( file_exists( $file ) ) {
                wp_register_script(
                    'wptoolset-field-date-localized',
                    WPTOOLSET_FORMS_RELPATH . '/js/i18n/jquery.ui.datepicker-' . $locale . '.js',
                    array('jquery-ui-datepicker'),
                    WPTOOLSET_FORMS_VERSION,
                    true
                );
            }
			*/
			$lang = get_locale();
			$lang = str_replace('_', '-', $lang);
			// TODO integrate this with WPML lang
			if ( file_exists( WPTOOLSET_FORMS_ABSPATH . '/js/i18n/jquery.ui.datepicker-' . $lang . '.js' ) ) {
				if ( !wp_script_is( 'jquery-ui-datepicker-local-' . $lang, 'registered' ) ) {
					wp_register_script( 'jquery-ui-datepicker-local-' . $lang, WPTOOLSET_FORMS_RELPATH . '/js/i18n/jquery.ui.datepicker-' . $lang . '.js', array('jquery-ui-core', 'jquery', 'jquery-ui-datepicker'), WPTOOLSET_FORMS_VERSION, true );
					$this->localization_slug = $lang;
				}
			} else {
				$lang = substr($lang, 0, 2);
				if ( file_exists( WPTOOLSET_FORMS_ABSPATH . '/js/i18n/jquery.ui.datepicker-' . $lang . '.js' ) ) {
					if ( !wp_script_is( 'jquery-ui-datepicker-local-' . $lang, 'registered' ) ) {
						wp_register_script( 'jquery-ui-datepicker-local-' . $lang, WPTOOLSET_FORMS_RELPATH . '/js/i18n/jquery.ui.datepicker-' . $lang . '.js', array('jquery-ui-core', 'jquery', 'jquery-ui-datepicker'), WPTOOLSET_FORMS_VERSION, true );
						$this->localization_slug = $lang;
					}
				}
			}
        }
        /**
         * styles
         */
        wp_enqueue_style( 'wptoolset-field-datepicker' );
        /**
         * scripts
         */
        wp_enqueue_script( 'wptoolset-field-date' );
        $date_format = self::getDateFormat();
        $js_date_format = $this->_convertPhpToJs( $date_format );
		$calendar_image = WPTOOLSET_FORMS_RELPATH . '/images/calendar.gif';
		$calendar_image = apply_filters( 'wptoolset_filter_wptoolset_calendar_image', $calendar_image );
		$calendar_image_readonly = WPTOOLSET_FORMS_RELPATH . '/images/calendar-readonly.gif';
		$calendar_image_readonly = apply_filters( 'wptoolset_filter_wptoolset_calendar_image_readonly', $calendar_image_readonly );
        $js_data = array(
            'buttonImage' => $calendar_image,
            'buttonText' => __( 'Select date', 'wpv-views' ),
            'dateFormat' => $js_date_format,
            'dateFormatPhp' => $date_format,
            'dateFormatNote' => esc_js( sprintf( __( 'Input format: %s', 'wpv-views' ), $date_format ) ),
            'yearMin' => intval( self::timetodate( self::$_mintimestamp, 'Y' ) ) + 1,
            'yearMax' => self::timetodate( self::$_maxtimestamp, 'Y' ),
			'ajaxurl' => admin_url('admin-ajax.php', null),
			'readonly' => esc_js( __( 'This is a read-only date input', 'wpv-views' ) ),
            'readonly_image' => $calendar_image_readonly,
        );
        wp_localize_script( 'wptoolset-field-date', 'wptDateData', $js_data );
		if ( $this->localization_slug && !wp_script_is( 'jquery-ui-datepicker-local-' . $this->localization_slug ) ) {
			wp_enqueue_script( 'jquery-ui-datepicker-local-' . $this->localization_slug );
		}
    }

    protected function _convertPhpToJs( $date_format )
    {
        $date_format = str_replace( 'd', 'dd', $date_format );
        $date_format = str_replace( 'j', 'd', $date_format );
        $date_format = str_replace( 'l', 'DD', $date_format );
        $date_format = str_replace( 'm', 'mm', $date_format );
        $date_format = str_replace( 'n', 'm', $date_format );
        $date_format = str_replace( 'F', 'MM', $date_format );
        $date_format = str_replace( 'y', 'y', $date_format );
        $date_format = str_replace( 'Y', 'yy', $date_format );

        return $date_format;
    }

    public static function getDateFormat() {
        $date_format = get_option( 'date_format' );
        if ( !in_array( $date_format, self::$_supported_date_formats ) ) {
            $date_format = 'F j, Y';
        }
        return $date_format;
    }

    public static function timetodate( $timestamp, $format = null )
    {
        if ( is_null( $format ) ) {
            $format = self::getDateFormat();
        }
        return self::_isTimestampInRange( $timestamp ) ? @adodb_date( $format, $timestamp ) : false;
    }

    public static function _isTimestampInRange( $timestamp )
    {
        return self::$_mintimestamp <= $timestamp && $timestamp <= self::$_maxtimestamp;
    }
}


