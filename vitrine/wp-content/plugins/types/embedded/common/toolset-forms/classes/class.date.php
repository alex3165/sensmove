<?php

require_once 'class.field_factory.php';
if (!function_exists('adodb_mktime')) {
    require_once WPTOOLSET_FORMS_ABSPATH . '/lib/adodb-time.inc.php';
}

/**
 * Description of class
 *
 * @author Srdjan
 */
class WPToolset_Field_Date extends FieldFactory
{
    // 15/10/1582 00:00 - 31/12/3000 23:59
    protected static $_mintimestamp = -12219292800, $_maxtimestamp = 32535215940;

    public function init()
    {
        $this->set_placeholder_as_attribute();
    }

    public static function registerScripts()
    {
    }

    public static function registerStyles()
    {
    }

    public static function addFilters()
    {
        if (has_filter('wptoolset_validation_value_date', array('WPToolset_Field_Date', 'filterValidationValue'))) {
            return;
        }
        // Filter validation
        add_filter('wptoolset_validation_value_date', array('WPToolset_Field_Date', 'filterValidationValue'));
        add_filter('wptoolset_validation_rule_js', array('WPToolset_Field_Date', 'filterValidationRuleJs'));
        add_filter('wptoolset_validation_args_php', array('WPToolset_Field_Date', 'filterValidationArgsPhp'), 10, 2);
        // Filter conditional
        add_filter('wptoolset_conditional_args_php', array('WPToolset_Field_Date', 'filterConditionalArgsPhp'), 10, 2);
        add_filter('wptoolset_conditional_value_php', array('WPToolset_Field_Date', 'filterConditionalValuePhp'), 10, 2);
        add_filter('wptoolset_conditional_args_js', array('WPToolset_Field_Date', 'filterConditionalArgsJs'), 10, 2);
    }

    public function enqueueScripts()
    {
    }

    public function enqueueStyles()
    {
    }

    public function metaform()
    {
        $time_value = $this->getValue();
        $datepicker = $hour = $minute = null;
        $timestamp = null;
        $readonly = false;
        if (is_admin()) {
            if (is_array($time_value) && array_key_exists('timestamp', $time_value) && $time_value ) {
                $timestamp = $time_value['timestamp'];
            }
            $datepicker = self::timetodate($timestamp);
            $hour = self::timetodate($timestamp, 'H');
            $minute = self::timetodate($timestamp, 'i');
        } else {
            // We are on a CRED form, on frontend, so getVAlue returns nothing or a string or an array of the kind array( 'datepicker' =>, 'hour' =>, 'minute' => )
            // Note that even if the array is passed, 'hour' and 'minute' will only be passed if there are any
            if (!empty($time_value)) {
                if (is_array($time_value)) {
                    if (isset($time_value['timestamp']) && is_numeric($time_value['timestamp']) && self::_isTimestampInRange($time_value['timestamp'])) {
                        $timestamp = $time_value['timestamp'];
                        $datepicker = self::timetodate($timestamp);
                    } elseif (isset($time_value['datepicker']) && $time_value['datepicker'] !== false && is_numeric($time_value['datepicker']) && self::_isTimestampInRange($time_value['datepicker'])) {
                        $timestamp = $time_value['datepicker'];
                        $datepicker = self::timetodate($timestamp);
                    }
                    if (isset($time_value['hour']) && is_numeric($time_value['hour'])) {
                        $hour = $time_value['hour'];
                    }
                    if (isset($time_value['minute']) && is_numeric($time_value['minute'])) {
                        $minute = $time_value['minute'];
                    }
                } else {
                    if (is_numeric($time_value) && self::_isTimestampInRange($time_value)) {
                        $timestamp = $time_value;
                        $datepicker = self::timetodate($timestamp);
                    } else {
                        $timestamp = self::strtotime($time_value);
                        $datepicker = $time_value;
                    }
                }
            }
        }
        $data = $this->getData();

        if (!$timestamp) {
            // If there is no timestamp, we need to make it an empty string
            // A false value would render the hidden field with a value of 1
            $timestamp = '';
            $datepicker = null;
        }

        $def_class = 'js-wpt-date';

        $def_class_aux = 'js-wpt-date-auxiliar';

        if (isset($data['attribute']) && isset($data['attribute']['readonly']) && $data['attribute']['readonly'] == 'readonly') {
            $def_class .= ' js-wpv-date-readonly';
            $def_class_aux .= ' js-wpt-date-readonly';
            $readonly = true;
        }

        $form = array();

        $validate = $this->getValidationData();
        $title = $this->getTitle();

        if (isset($validate['required']) && !empty($title)) {
            // Asterisk
            $title .= '&#42;';
        }

        $attr_visible = array(
            'class' => $def_class,
            'style' => 'display:inline;width:150px;position:relative;',
            'readonly' => 'readonly',
            'title' => esc_attr(__('Select', 'wpv-views'))." Date"
        );
        $attr_hidden = array('class' => $def_class_aux, 'data-ts' => $timestamp, 'data-wpt-type' => 'date');

        if (isset($data['attribute']) && isset($data['attribute']['placeholder'])) {
            $attr_visible['placeholder'] = $data['attribute']['placeholder'];
        }

        $form[] = array(
            '#type' => 'textfield',
            '#title' => $title,
            '#description' => $this->getDescription(),
            '#attributes' => $attr_visible,
            '#name' => $this->getName() . '[display-only]',
            '#value' => $datepicker,
            '#inline' => true,
        );
        $form[] = array(
            '#type' => 'hidden',
            '#title' => $title,
            '#attributes' => $attr_hidden,
            '#name' => $this->getName() . '[datepicker]',
            '#value' => $timestamp,
            '#validate' => $validate,
            '#repetitive' => $this->isRepetitive(),
        );

        /*
          // This was the old implementaton
          // We have implemented the above one because we need a hidden field to hold the timestamp
          // And the visible text input field to display the date string to the user
          $form[] = array(
          '#type' => 'textfield',
          '#title' => $this->getTitle(),
          '#attributes' => array('class' => $def_class, 'style' => 'width:150px;'),
          '#name' => $this->getName() . '[datepicker]',
          '#value' => $timestamp,
          '#validate' => $this->getValidationData(),
          '#repetitive' => $this->isRepetitive(),
          );
         */
        if (!empty($data['add_time'])) {
            // Shared attributes
            $attributes_hour_minute = array();
            if ($readonly) {
                $attributes_hour_minute['disabled'] = 'disabled';
            }
            if (array_key_exists('use_bootstrap', $this->_data) && $this->_data['use_bootstrap']) {
                $attributes_hour_minute['style'] = 'display:inline;width:auto;';
            }

            // Hour
            $hours = 24;
            $options = array();
            for ($index = 0; $index < $hours; $index++) {
                $prefix = $index < 10 ? '0' : '';
                $options[$index] = array(
                    '#title' => $prefix . strval($index),
                    '#value' => $index,
                );
            }
            $hour_element = array(
                '#type' => 'select',
                '#before' => '<span class="wpt-form-label">' . __('Hour', 'wpv-views') . '</span>',
                '#options' => $options,
                '#default_value' => $hour,
                '#name' => $this->getName() . '[hour]',
                '#inline' => true,
                '#attributes' => array('title' => esc_attr(__('Select', 'wpv-views'))." Date"),
            );
            if (!empty($attributes_hour_minute)) {
                $hour_element['#attributes'] = $attributes_hour_minute;
            }
            $form[] = $hour_element;
            // Minutes
            $minutes = 60;
            $options = array();
            for ($index = 0; $index < $minutes; $index++) {
                $prefix = $index < 10 ? '0' : '';
                $options[$index] = array(
                    '#title' => $prefix . strval($index),
                    '#value' => $index,
                );
            }
            $minute_element = array(
                '#type' => 'select',
                '#before' => '<span class="wpt-form-label">' . __('Minute', 'wpv-views') . '</span>',
                '#options' => $options,
                '#default_value' => $minute,
                '#name' => $this->getName() . '[minute]',
                '#inline' => true,
                '#attributes' => array('title' => esc_attr(__('Select minute', 'wpv-views'))),
            );
            if (!empty($attributes_hour_minute)) {
                $minute_element['#attributes'] = $attributes_hour_minute;
            }
            $form[] = $minute_element;
        }

        $form[] = array(
            '#type' => 'markup',
            '#inline' => true,
            '#markup' => sprintf(
                '<input type="button" class="button button-secondary js-wpt-date-clear wpt-date-clear" value="%s" %s/>',
                esc_attr(__('Clear', 'wpv-views'))." Date",
                /**
                 * show button if array is empty or timestamp in array is
                 * empty
                 */
                empty($time_value) || 
                        (isset($time_value['timestamp']) && 
                        empty($time_value['timestamp']))? 'style="display:none" ':''
            ),
        );
        return $form;
    }

    public static function getDateFormat()
    {
        return WPToolset_Field_Date_Scripts::getDateFormat();
    }

    protected function _dateToStrftime($format)
    {
        $format = str_replace('d', '%d', $format);
        $format = str_replace('D', '%a', $format);
        $format = str_replace('j', '%e', $format);
        $format = str_replace('l', '%A', $format);
        $format = str_replace('N', '%u', $format);
        $format = str_replace('w', '%w', $format);

        $format = str_replace('W', '%W', $format);

        $format = str_replace('F', '%B', $format);
        $format = str_replace('m', '%m', $format);
        $format = str_replace('M', '%b', $format);
        $format = str_replace('n', '%m', $format);

        $format = str_replace('o', '%g', $format);
        $format = str_replace('Y', '%Y', $format);
        $format = str_replace('y', '%y', $format);

        return $format;
    }

    public static function filterValidationValue($value)
    {
        /**
         * validate fimestamp range is possible
         */
        if (isset($value['timestamp'])) {
            return $value['timestamp'];
        }
        if (isset($value['datepicker'])) {
            return $value['datepicker'];
        }
        return $value;
    }

    public static function filterValidationRuleJs($rule)
    {
        if ($rule == 'date') {
            return 'dateADODB_STAMP';
        } else {
            return $rule;
        }
    }

    public static function filterValidationArgsPhp($args, $rule)
    {
        if ($rule == 'date') {
            return array('$value', self::getDateFormat());
        }
        return $args;
    }

    public static function filterConditionalArgsJs($args, $type)
    {
        if ($type == 'date') {
            foreach ($args as &$arg) {
                if (!is_numeric($arg)) {
                    // Well it should be a numeric timestamp indeed
                    $arg = self::strtotime($arg);
                }
            }
        }
        return $args;
    }

    public static function filterConditionalArgsPhp($args, $type)
    {
        if ($type == 'date') {
            foreach ($args as &$arg) {
                $arg = self::filterConditionalValuePhp($arg, $type);
            }
        }
        return $args;
    }

    public static function filterConditionalValuePhp($value, $type)
    {
        if ($type == 'date') {
            if (!is_numeric($value)) {
                // Well it should be a numeric timestamp indeed
                $value = self::strtotime($value);
            }
            // Use timestamp with PHP
            // Convert back/forward to have rounded timestamp (no H and i)
            // TODO review this because we should not play with timestamps generated on adodb_xxx functions
            //$value = self::strtotime( self::timetodate( $value ) );
        }
        return $value;
    }

    // We need to keep this for backwards compatibility
    // Note that this function will only convert dates coming on a string:
    // - in english
    // - inside the valid PHP date range
    // We are only using this when the value being checked is not a timestamp
    // And we have tried to avoid that situation from happening
    // But for old implementation, this happens for date conditions on conditional fields
    public static function strtotime($value, $format = null)
    {
        if (is_null($format)) {
            $format = self::getDateFormat();
        }
        /**
         * add exception to handle short year
         */
        if ('d/m/y' == $format) {
            preg_match_all('/(\d{2})/', $value, $value);
            $value[0][2] += $value[0][2] < 70 ? 2000 : 1900;
            $value = implode('-', $value[0]);
        }
        if (strpos($format, 'd/m/Y') !== false) {
            // strtotime requires a dash or dot separator to determine dd/mm/yyyy format
            preg_match('/\d{2}\/\d{2}\/\d{4}/', $value, $matches);
            if (!empty($matches)) {
                foreach ($matches as $match) {
                    $value = str_replace($match, str_replace('/', '-', $match), $value);
                }
            }
        }
        try {
            $date = new DateTime($value);
        } catch (Exception $e) {
            return false;
        }
        $timestamp = $date->format("U");
        return self::_isTimestampInRange($timestamp) ? $timestamp : false;
    }

    // TODO review this because we should not play with timestamps generated on adodb_xxx functions
    public static function timetodate($timestamp, $format = null)
    {
        return WPToolset_Field_Date_Scripts::timetodate($timestamp, $format);
    }

    protected static function _isTimestampInRange($timestamp)
    {
        return WPToolset_Field_Date_Scripts::_isTimestampInRange($timestamp);
    }

    /**
     * DEPRECATED
     *
     * This is not used anymore
     */
    public static function timeIsValid($time)
    {
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
        $_min_time = self::timeNegativeSupported() ? -2147483646 : 0;
        // MAX 'Tue, 19 Jan 2038 03:14:07 UTC' - 2147483647
        $_max_time = 2147483647;

        return is_numeric($time) && $_min_time <= intval($time) && intval($time) <= $_max_time;
    }

    /**
     * DEPRECATED
     *
     * This is not used anymore
     */
    public static function timeNegativeSupported()
    {
        return strtotime('Fri, 13 Dec 1950 20:45:54 UTC') === -601010046;
    }

}
