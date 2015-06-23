<?php

/*
 * - Checks conditionals when form is displayed and values changed
 * - Checks simple conditionals using JS
 * - Checks custom conditinals via AJAX/PHP
 * - PHP simple and custom checks available using class methods
 * 
 * Simple conditionals
 * 
 * Data
 * [id] - Trigger ID to match data-wpt-id
 * [type] - field type (trigger)
 * [operator] - operator
 * [args] - array(value, value2...)
 * 
 * Example
 * $config['conditional'] = array(
 *  'relation' => 'OR'|'AND',
 *  'conditions' => array(
 *      array(
 *          'id' => 'wpcf-text',
 *          'type' => 'textfield',
 *          'operator' => '==',
 *          'args' => array('show')
 *      ),
 *      array(
 *          'id' => 'wpcf-date',
 *          'type' => 'date',
 *          'operator' => 'beetween',
 *          'args' => array('21/01/2014', '24/01/2014') // Accepts timestamps or string date
 *      )
 *  ),
 * );
 * 
 * Custom conditionals
 * 
 * Variable name should match trigger ID - data-wpt-id
 * Example
 * $config['conditional'] = array(
 *  'custom' => '($wpcf-text = show) OR ($wpcf-date > '21-01-2014')'
 * );
 */
if (!defined('ICL_COMMON_FUNCTIONS')) {
    require_once WPTOOLSET_COMMON_PATH . '/functions.php';
}
if (!function_exists('wpv_filter_parse_date')) {
    require_once WPTOOLSET_COMMON_PATH . '/wpv-filter-date-embedded.php';
}

require_once WPTOOLSET_COMMON_PATH . '/expression-parser/parser.php';

/**
 * Class description
 *
 * @todo BUG common function wpv_condition has some flaws
 *      (dashed names, mixed checks for string and numeric values causes failure)
 *
 * @author Srdjan
 */
class WPToolset_Forms_Conditional {

    private $__formID;
    protected $_collected = array(), $_triggers = array(), $_fields = array(), $_custom_triggers = array(), $_custom_fields = array();

    /**
     * Register and enqueue scripts and actions.
     *
     * @param type $formID
     */
    public function __construct($formID) {
        $this->__formID = trim($formID, '#');
        // Register and enqueue
        wp_register_script('wptoolset-form-conditional', WPTOOLSET_FORMS_RELPATH . '/js/conditional.js', array('jquery', 'jquery-effects-scale'), WPTOOLSET_FORMS_VERSION, true);
        wp_enqueue_script('wptoolset-form-conditional');
        $js_data = array(
            'ajaxurl' => admin_url('admin-ajax.php', null),
        );
        wp_localize_script('wptoolset-form-conditional', 'wptConditional', $js_data);

        wp_register_script('wptoolset-parser', icl_get_file_relpath(dirname(dirname(__FILE__))) . '/expression-parser/js/parser.js', array('jquery'), WPTOOLSET_FORMS_VERSION, true);
        wp_enqueue_script('wptoolset-parser');
        $js_data = array(
            'ajaxurl' => admin_url('admin-ajax.php', null),
        );
        // Render settings
        add_action('admin_print_footer_scripts', array($this, 'renderJsonData'), 30);
        add_action('wp_footer', array($this, 'renderJsonData'), 30);
        // Check conditional and hide field
        add_action('wptoolset_field_class', array($this, 'actionFieldClass'));
    }

    /**
     * Collects data.
     *
     * Called from form_factory.
     *
     * @param type $config
     */
    public function add($config) {
        if (!empty($config['conditional'])) {
            $this->_collected[$config['id']] = $config['conditional'];
            return;
        }
    }

    /**
     * Sets JSON data to be used with conditional.js
     */
    protected function _parseData() {
        foreach ($this->_collected as $id => $config) {
            if (!empty($config['custom'])) {

                $evaluate = $config['custom'];
                //###############################################################################################
                //https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/193583580/comments
                //Fix REGEX conditions that contains \ that is stripped out
                if (strpos($evaluate, "REGEX") === false) {
                    $evaluate = wpv_filter_parse_date($evaluate);
                    $evaluate = self::handle_user_function($evaluate);                    
                }   
                //###############################################################################################
                $fields = self::extractFields($evaluate);

                foreach ($fields as $field) {
                    $this->_custom_fields[$id]['custom'] = $evaluate;
                    $this->_custom_fields[$id]['triggers'][] = $field;
                    $this->_custom_triggers[$field][] = $id;
                }
            } else {
                if (isset($config) && isset($config['conditions'])) {
                    if (isset($config) && isset($config['relation']))
                        $this->_fields[$id]['relation'] = $config['relation'];

                    foreach ($config['conditions'] as &$c) {
                        /*
                         * $c[id] - field id
                         * $c[type] - field type
                         * $c[operator] - operator
                         * $c[args] - array(value, [value2]...)
                         */
                        if (!isset($this->_triggers[$c['id']]))
                            $this->_triggers[$c['id']] = array();
                        $c['args'] = apply_filters('wptoolset_conditional_args_js', $c['args'], $c['type']);
                        $this->_fields[$id]['conditions'][] = $c;
                        if (!in_array($id, $this->_triggers[$c['id']]))
                            $this->_triggers[$c['id']][] = $id;
                    }
                }
            }
        }
    }

    /**
     * Renders JSON data in footer to be used with conditional.js
     */
    public function renderJsonData() {
        $this->_parseData();
        if (!empty($this->_triggers)) {
            echo '<script type="text/javascript">wptCondTriggers["#'
            . $this->__formID . '"] = ' . json_encode($this->_triggers) . ';</script>';
        }
        if (!empty($this->_fields)) {
            echo '<script type="text/javascript">wptCondFields["#'
            . $this->__formID . '"] = ' . json_encode($this->_fields) . ';</script>';
        }
        if (!empty($this->_custom_triggers)) {
            echo '<script type="text/javascript">wptCondCustomTriggers["#'
            . $this->__formID . '"] = ' . json_encode($this->_custom_triggers) . ';</script>';
        }
        if (!empty($this->_custom_fields)) {
            echo '<script type="text/javascript">wptCondCustomFields["#'
            . $this->__formID . '"] = ' . json_encode($this->_custom_fields) . ';</script>';
        }
    }

    /**
     * Compares values.
     *
     * @param array $config
     * @param array $values
     * @return type
     */
    public static function evaluate($config) {
        // Custom conditional
        if (!empty($config['custom'])) {
            return self::evaluateCustom($config['custom'], $config['values']);
        }

        /**
         * check conditions
         */
        if (!array_key_exists('conditions', $config)) {
            return true;
        }

        $passedOne = false;
        $passedAll = true;
        $relation = $config['relation'];

        foreach ($config['conditions'] as $c) {
            // Add filters
            wptoolset_form_field_add_filters($c['type']);
            $c['args'] = apply_filters('wptoolset_conditional_args_php', $c['args'], $c['type']);
            $value = isset($config['values'][$c['id']]) ? $config['values'][$c['id']] : null;
            $value = apply_filters('wptoolset_conditional_value_php', $value, $c['type']);
            $compare = $c['args'][0];
            switch ($c['operator']) {
                case '=':
                case '==':
                    $passed = $value == $compare;
                    break;

                case '>':
                    $passed = floatval($value) > floatval($compare);
                    break;

                case '>=':
                    $passed = floatval($value) >= floatval($compare);
                    break;

                case '<':
                    $passed = floatval($value) < floatval($compare);
                    break;

                case '<=':
                    $passed = floatval($value) <= floatval($compare);
                    break;

                case '===':
                    $passed = $value === $compare;
                    break;

                case '!==':
                    $passed = $value !== $compare;
                    break;

                case '<>':
                    $passed = $value <> $compare;
                    break;

                case 'between':
                    $passed = floatval($value) > floatval($compare) && floatval($value) < floatval($c['args'][1]);
                    break;

                default:
                    $passed = false;
                    break;
            }
            if (!$passed) {
                $passedAll = false;
            } else {
                $passedOne = true;
            }
        }
        if ($relation == 'AND' && $passedAll) {
            return true;
        }
        if ($relation == 'OR' && $passedOne) {
            return true;
        }
        return false;
    }

    /**
     * Evaluates conditions using custom conditional statement.
     *
     * @uses wpv_condition()
     *
     * @param type $post
     * @param type $evaluate
     * @return boolean
     */
    public static function evaluateCustom($evaluate, $values) {       
        //###############################################################################################
        //https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/193583580/comments
        //Fix REGEX conditions that contains \ that is stripped out
        if (strpos($evaluate, "REGEX") === false) {            
            $evaluate = trim(stripslashes($evaluate));
            // Check dates
            $evaluate = wpv_filter_parse_date($evaluate);
            $evaluate = self::handle_user_function($evaluate);
        }

        $fields = self::extractFields($evaluate);
        $evaluate = self::_update_values_in_expression($evaluate, $fields, $values);

        $check = false;
        try {
            $parser = new Toolset_Parser($evaluate);
            $parser->parse();
            $check = $parser->evaluate();
        } catch (Exception $e) {
            $check = false;
        }
        return $check;
    }

    static function sortByLength($a, $b) {
        return strlen($b) - strlen($a);
    }

    private static function _update_values_in_expression($evaluate, $fields, $values) {

        // use string replace to replace any fields with their values.
        // Sort by length just in case a field name contians a shorter version of another field name.
        // eg.  $my-field and $my-field-2

        $keys = array_keys($fields);
        usort($keys, 'WPToolset_Forms_Conditional::sortByLength');

        foreach ($keys as $key) {
            $value = isset($values[$fields[$key]]) ? $values[$fields[$key]] : '';
            if ($value == '') {
                $value = "''";
            }
            if (is_numeric($value)) {
                $value = '\'' . $value . '\'';
            }

            if ('array' === gettype($value)) {
                // workaround for datepicker data to cover all cases
                if (array_key_exists('timestamp', $value)) {
                    if (is_numeric($value['timestamp'])) {
                        $value = $value['timestamp'];
                    } else if (is_array($value['timestamp'])) {
                        $value = implode(',', array_values($value['timestamp']));
                    }
                } else if (array_key_exists('datepicker', $value)) {
                    if (is_numeric($value['datepicker'])) {
                        $value = $value['datepicker'];
                    } else if (is_array($value['datepicker'])) {
                        $value = implode(',', array_values($value['datepicker']));
                    }
                } else {
                    $value = implode(',', array_values($value));
                }
            }


            // First replace the $(field_name) format
            $evaluate = str_replace('$(' . $fields[$key] . ')', $value, $evaluate);
            // next replace the $field_name format
            $evaluate = str_replace('$' . $fields[$key], $value, $evaluate);
        }

        return $evaluate;
    }

    /**
     * Extracts fields from custom conditional statement.
     *
     * @param type $evaluate
     * @return type
     */
    public static function extractFields($evaluate) {        
        //###############################################################################################
        //https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/193583580/comments
        //Fix REGEX conditions that contains \ that is stripped out
        if (strpos($evaluate, "REGEX") === false) {   
            $evaluate = trim(stripslashes($evaluate));
            // Check dates
            $evaluate = wpv_filter_parse_date($evaluate);
            $evaluate = self::handle_user_function($evaluate);
        }

        // Add quotes = > < >= <= === <> !==
        $strings_count = preg_match_all('/[=|==|===|<=|<==|<===|>=|>==|>===|\!===|\!==|\!=|<>]\s(?!\$)(\w*)[\)|\$|\W]/', $evaluate, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $temp_match) {
                $temp_replace = is_numeric($temp_match) ? $temp_match : '\'' . $temp_match . '\'';
                $evaluate = str_replace(' ' . $temp_match . ')', ' ' . $temp_replace . ')', $evaluate);
            }
        }
        // if new version $(field-value) use this regex
        if (preg_match('/\$\(([^()]+)\)/', $evaluate)) {
            preg_match_all('/\$\(([^()]+)\)/', $evaluate, $matches);
        }
        // if old version $field-value use this other
        else {
            preg_match_all('/\$([^\s]*)/', $evaluate, $matches);
        }


        $fields = array();
        if (!empty($matches)) {
            foreach ($matches[1] as $field_name) {
                $fields[trim($field_name, '()')] = trim($field_name, '()');
            }
        }

        return $fields;
    }

    public static function handle_user_function($evaluate) {
        $evaluate = stripcslashes($evaluate);
        $occurrences = preg_match_all('/(\\w+)\(([^\)]*)\)/', $evaluate, $matches);

        if ($occurrences > 0) {
            for ($i = 0; $i < $occurrences; $i++) {
                $result = false;
                $function = $matches[1][$i];
                $field = isset($matches[2]) ? rtrim($matches[2][$i], ',') : '';

                if ($function === 'USER') {
                    $result = WPV_Handle_Users_Functions::get_user_field($field);
                }

                if ($result) {
                    $evaluate = str_replace($matches[0][$i], $result, $evaluate);
                }
            }
        }

        return $evaluate;
    }

    /**
     * Custom conditional AJAX check (called from bootstrap.php)
     */
    public static function ajaxCustomConditional() {
        $res = array('passed' => array(), 'failed' => array());
        $conditional = stripslashes_deep($_POST['conditions']);
        foreach ($conditional as $k => $c) {
            $post_values = stripslashes_deep($_POST['values']);
            $values = array();
            foreach ($post_values as $fid => $value) {
                if (isset($_POST['field_types'][$fid])) {
                    $field_type = stripslashes_deep($_POST['field_types'][$fid]);
                    wptoolset_form_field_add_filters($field_type);
                    $value = apply_filters('wptoolset_conditional_value_php', $value, $field_type);
                }
                $values[$fid] = $value;
            }
            if ($passed = self::evaluateCustom($c, $values)) {
                $res['passed'][] = $k;
            } else {
                $res['failed'][] = $k;
            }
        }
        echo json_encode($res);
        die();
    }

    /**
     * Checks conditional and hides field.
     *
     * @param type $config
     */
    public function actionFieldClass($config) {
        if (
                !empty($config['conditional']) && array_key_exists('conditions', $config['conditional']) && !self::evaluate($config['conditional'])
        ) {
            echo ' wpt-hidden js-wpt-remove-on-submit js-wpt-validation-ignore';
        }
    }

    /**
     * Returns collected JSON data
     *
     * @return type
     */
    public function getData() {
        $this->_parseData();
        return array(
            'triggers' => $this->_triggers,
            'fields' => $this->_fields,
            'custom_triggers' => $this->_custom_triggers,
            'custom_fields' => $this->_custom_fields,
        );
    }

}

if (!class_exists('WPV_Handle_Users_Functions')) {

    class WPV_Handle_Users_Functions {

        private static $field;

        public static function get_user_field($field) {
            if (!$field)
                return false;

            self::$field = str_replace("'", '', $field);

            $ret = self::get_info();

            if ($ret !== false)
                return "'" . $ret . "'";

            return false;
        }

        private static function get_info()
        {
            if (!is_user_logged_in()) {
                return false;
            }
            global $current_user;

            get_currentuserinfo();

            switch (self::$field) {
                case 'role':
                    return isset($current_user->roles[0]) ? $current_user->roles[0] : 'Subscriber';
                    break;
                case 'login':
                    return $current_user->data->user_login;
                    break;
                case 'name':
                    return $current_user->data->display_name;
                    break;
                case 'id':
                    return $current_user->data->ID;
                    break;
                default:
                    return $current_user->data->ID;
                    break;
            }

            return false;
        }

    }

}
