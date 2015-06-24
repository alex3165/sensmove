<?php
/**
 * Types fields specific
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/embedded/common/toolset-forms/classes/class.types.php $
 * $LastChangedDate: 2015-03-02 10:49:00 +0000 (Mon, 02 Mar 2015) $
 * $LastChangedRevision: 1103173 $
 * $LastChangedBy: iworks $
 *
 */

/**
 * Class description
 *
 * @author Srdjan
 */
class WPToolset_Types
{

    /**
     * Filters Types field to match data structure needed for shared code.
     *
     * @global type $pagenow
     * @staticvar array $cache
     * @param type $field array|string $field settings array (as stored in DB) or field ID
     * @param type $post_id Post or user ID used for conditional
     * @return array
     */
    static function filterField($field, $post_id = null, $_post_wpcf = array())
    {
        // Get field settings (as when using get_option('wpcf-fields') from DB)
        $field = self::getConfig( $field );
        if ( is_null( $field ) ) return array();

        // Caching
        static $cache = array();
        $cache_key = md5( serialize( $field ) . $post_id );
        if ( isset( $cache[$cache_key] ) ) {
            return $cache[$cache_key];
        }

        // Prefix - used to construct ID of field @see at bottom 'General field settings'
        $prefix = self::getPrefix( $field );

        /* Suffix - used to construct ID of this field and other fields connected
         * to it via conditionals.
         *
         * @see at bottom 'General field settings'
         *
         * Reason to use it - Types have child posts forms inside same form as
         * main fields. It's like having same sets of fields inside same form.
         * Main post fields do not have suffix.
         *
         * Example main field:
         * ID: wpcf-text
         * conditional: '$wpcf-date > DATE(01,02,2014)'
         *
         * Example child field:
         * ID: wpcf-text-123
         * conditional: '$wpcf-date-123 > DATE(01,02,2014)'
         * Suffix is child post ID (wpcf-text-$child_post_id).
         *
         * That way right triggers and conditional fields are mapped.
         */
        $suffix = isset( $field['suffix'] ) ? $field['suffix'] : '';

        /* General field settings
         *
         * Main settings that are returned.
         */

        $_field = array(
            'id' => $prefix . $field['id'] . $suffix, // Used as main ID (raw date wpt-id), used to connect conditional relations
            'meta_key' => $prefix . $field['id'], // Used by Types (meta key of field saved to DB)
            'meta_type' => isset( $field['meta_type'] ) ? $field['meta_type'] : 'postmeta', // Used by Types (postmeta|usermeta)
            'type' => $field['type'], // Type of field (textfield, skype, email etc)
            'slug' => $field['id'], // Not sure if used by Types TODO REVISE
            'title' => self::translate( "field {$field['id']} name",
                    $field['name'] ), // Translated title
            'description' => !empty( $field['description'] ) ? self::translate( "field {$field['id']} description",
                    $field['description'] ) : '', // Translated description
            'name' => isset( $field['form_name'] ) ? $field['form_name'] : "wpcf[{$field['id']}]", // Form element name, default wpcf[$no-prefix-slug]
            'repetitive' => self::isRepetitive( $field ), // Is repetitive?
            'validation' => self::filterValidation( $field ), // Validation settings
            'conditional' => self::filterConditional( $field, $post_id, $_post_wpcf ), // Conditional settings
            'placeholder' => isset($field['data']) && isset($field['data']['placeholder'])? $field['data']['placeholder']:null, // HTML5 placeholder
        );

        /* Specific field settings
         *
         * There are some field types that needs extra data or adjusted data
         * for readibility.
         */
        switch( $field['type'] ) {
            // Checkbox set default_value
        case 'checkbox':
            $_field['default_value'] = $field['data']['set_value'];
            $_field['checked'] = array_key_exists( 'checked', $field['data'] )? $field['data']['checked']:false;
            break;
            // Checkboxes set field [options]
        case 'checkboxes':
            if ( !empty( $field['data']['options'] ) ) {
                global $pagenow;
                foreach ( $field['data']['options'] as $option_key => $option ) {
                    // Set value
                    $_field['options'][$option_key] = array(
                        'value' => $option['set_value'],
                        'title' => self::translate( 'field ' . $field['id'] . ' option '
                        . $option_key . ' title', $option['title'] ),
                        'name' => 'wpcf[' . $field['id'] . '][' . $option_key . ']',
                    );
                    if ( $pagenow == 'post-new.php' && !empty( $option['checked'] ) ) {
                        $_field['options'][$option_key]['checked'] = true;
                    }
                }
            }
            break;
        case 'date':
            $_field['add_time'] = !empty( $field['data']['date_and_time'] ) && $field['data']['date_and_time'] == 'and_time';
            break;
            // Radio and Select set field [options]
        case 'radio':
        case 'select':
            if ( !empty( $field['data']['options'] ) ) {
                foreach ( $field['data']['options'] as $k => $option ) {
                    if ( $k == 'default' ) {
                        continue;
                    }
                    if ( array_key_exists( 'default', $field['data']['options'] ) && $field['data']['options']['default'] == $k ) {
                        $_field['default_value'] = $option['value'];
                    }
                    $_field['options'][] = array(
                        'value' => $option['value'],
                        'title' => self::translate( 'field ' . $field['id'] . ' option '
                        . $k . ' title', $option['title'] ),
                    );
                }
            }
            break;
        }
        // Radio adjust type name because form_factory class name contains 'Radios'
        if ( $field['type'] == 'radio' ) {
            $_field['type'] = 'radios';
        }

        return $cache[$cache_key] = $_field;
    }

    /**
     * Filters validation.
     *
     * Loop over validation settings and create array of validation rules.
     * array( $rule => array( 'args' => array, 'message' => string ), ... )
     *
     * @param array|string $field settings array (as stored in DB) or field ID
     * @return array array( $rule => array( 'args' => array, 'message' => string ), ... )
     */
    public static function filterValidation($config)
    {
        $config = self::getConfig( $config );
        if ( is_null( $config ) ) return array();
        /* Placeholder for field value '$value'.
         *
         * Used for validation settings.
         * Field value is not processed here, instead string '$value' is used
         * to be replaced with actual value when needed.
         *
         * For example:
         * validation['rangelength'] = array(
         *     'args' => array( '$value', 5, 12 ),
         *     'message' => 'Value length between %s and %s required'
         * );
         * validation['reqiuired'] = array(
         *     'args' => array( '$value', true ),
         *     'message' => 'This field is required'
         * );
         *
         * Types have default and custom messages defined on it's side.
         */
        $value = '$value';
        $validation = array();
        if ( isset( $config['data']['validate'] ) ) {
            foreach ( $config['data']['validate'] as $rule => $settings ) {
                if ( $settings['active'] ) {
                    // Work out the id so we can get the translated message.
                    $id = '';
                    if (isset($config['slug'])) {
                        $id = $config['slug'];
                    } else {
                        // This is on a cred from
                        // try to find an appropriate id
                        $id = $config['name'];
                        if (strpos($id, 'wpcf-') === 0) {
                            $id = substr($id, 5);
                        }
                    }
                    $validation[$rule] = array(
                        'args' => isset( $settings['args'] ) ? array_unshift( $value,
                                        $settings['args'] ) : array($value, true),
                        'message' => self::translate('field ' . $id . ' validation message ' . $rule, stripslashes( $settings['message']))
                    );
                }
            }
        }
        return $validation;
    }

    /**
     * Filters conditional.
     *
     * There are two types of conditionals:
     * 1. Regular conditionals created using Types GUI
     * 2. Custom onditionals (user entered manually)
     *
     * 1. Regular conditional
     *
     * Main properties:
     * [relation] - AND|OR evaluate as true if all or any condition is TRUE
     * [conditions] - array of conditions
     * [values] - values to check against (used only by PHP), evaluate method
     *      should not be aware if it's checking post meta or user meta,
     *      instead array of needed values (or missing) are passed to method.
     *      Types use filteres get_post_meta() and get_user_meta().
     *
     * [conditions]
     * Each conditions is array with properties:
     * id: ID of trigger field (this field value is checked) to evaluate
     *      this field as TRUE or FALSE (corresponds to main IDs set here)
     * type: type of trigger field. JS and PHP needs at least this information
     *      when processing condtitional evaluation
     * operator: which operation to perform (=|>|<|>=|<=|!=|between)
     * args: arguments passed to checking functions
     *
     * Example of reguar conditional
     *
     * [conditional] => Array(
            [relation] => OR
            [conditions] => Array(
                [0] => Array(
                    [id] => wpcf-my-date
                    [type] => date
                    [operator] => =
                    [args] => Array(
                        [0] => 02/01/2014
                    )
                )
                [1] => Array(
                    [id] => wpcf-my-date
                    [type] => date
                    [operator] => between
                    [args] => Array(
                        [0] => 02/07/2014
                        [1] => 02/10/2014
                    )
                 )
            )
      [values] => Array(
        [wpcf-my-date] => 32508691200
        )
      )
     *
     *
     * 2. Custom conditional
     * Main properties:
     * [custom] - custom statement made by user, note that $xxxx should match
     *      IDs of fields that passed this filter.
     * [values] - same as for regular conditional
     *
     * [conditional] => Array(
            [custom] => ($wpcf-my-date = DATE(01,02,2014)) OR ($wpcf-my-date > DATE(07,02,2014))
            [values] => Array(
                [wpcf-my-date] => 32508691200
            )
      )
     *
     * @param array|string $field settings array (as stored in DB) or field ID
     * @param int $post_id Post or user ID to fetch meta data to check against
     * @return array
     */
    public static function filterConditional($field, $post_id, $_post_wpcf = array())
    {
        $field = self::getConfig( $field );
        if ( is_null( $field ) ) return array();

        // Caching
        static $cache = array();
        $cache_key = md5( serialize( $field ) . $post_id );
        if ( isset( $cache[$cache_key] ) ) {
            return $cache[$cache_key];
        }

        /* Suffix - used to construct ID of this field and other fields connected
         * to it via conditionals.
         *
         * @see at bottom 'General field settings'
         *
         * Reason to use it - Types have child posts forms inside same form as
         * main fields. It's like having same sets of fields inside same form.
         * Main post fields do not have suffix.
         *
         * Example main field:
         * ID: wpcf-text
         * conditional: '$wpcf-date > DATE(01,02,2014)'
         *
         * Example child field:
         * ID: wpcf-text-123
         * conditional: '$wpcf-date-123 > DATE(01,02,2014)'
         * Suffix is child post ID (wpcf-text-$child_post_id).
         *
         * That way right triggers and conditional fields are mapped.
         */
        $suffix = isset( $field['suffix'] ) ? $field['suffix'] : '';
        $cond = array();
        if ( empty( $field['meta_type'] ) ) {
            $field['meta_type'] = 'postmeta';
        }

        // Get [values]
        $cond_values = self::getConditionalValues($post_id, $field['meta_type']);

		if ( function_exists('wpcf_fields_get_field_by_slug') ){
            // Update the conditional values according to what's being saved.
            foreach ( $_post_wpcf as $field_slug => $field_value ) {
                // Get field by slug
                $field = wpcf_fields_get_field_by_slug( $field_slug );
                if ( empty( $field ) ) {
                    continue;
                }

                $field_value = apply_filters( 'wpcf_fields_type_' . $field['type'] . '_value_save', $field_value, $field, null );

                $cond_values[$field['meta_key']] = $field_value;
            }
        }

        // Set custom conditional
        if ( !empty( $field['data']['conditional_display']['custom_use'] ) ) {
            require_once 'class.conditional.php';
            $custom = $field['data']['conditional_display']['custom'];

            // Extract field values ('${$field_name}')
            $cond = self::getCustomConditional($custom, $suffix, $cond_values);

            // Regular conditional
        } elseif ( !empty( $field['data']['conditional_display']['conditions'] ) ) {
            $cond = array(
                'relation' => $field['data']['conditional_display']['relation'],
                'conditions' => array(),
                'values' => array(),
            );

            // Loop over conditions and collect settings
            foreach ( $field['data']['conditional_display']['conditions'] as $d ) {

                // Get field settings
                $c_field = self::getConfig( $d['field'] );

                // If it's Types field
                if ( !empty( $c_field ) ) {
                    $_c = array(
                        'id' => self::getPrefix( $c_field ) . $d['field'] . $suffix,
                        'type' => $c_field['type'],
                        'operator' => $d['operation'],
                        'args' => array($d['value']),
                    );
                    $cond['conditions'][] = $_c;

                    // Apply filters from field (that is why we set 'type' property)
                    wptoolset_form_field_add_filters( $field['type'] );
                    $key = $c_field['meta_key'];
                    if ( isset( $cond_values[$key] ) ) {
                        $cond['values'][$key . $suffix] = apply_filters( 'wptoolset_conditional_value_php',
                                $cond_values[$key], $c_field['type'] );
                    }

                    // Otherwise do nothing add [values]
                    // That allows checking for non-Types field
                    // TODO REVIEW THIS
                } elseif ( isset( $cond_values[$d['field']] ) ) {
                    $cond['values'][$d['field'] . $suffix] = $cond_values[$d['field']];
                }
            }
        }
        unset( $cond_values, $c_values, $c_field );
        return $cache[$cache_key] = $cond;
    }

    public static function getConditionalValues($post_id, $meta_type = 'postmeta') {
        $cond_values = array();
        if ( !empty( $post_id ) ) {
            $cond_values = $meta_type == 'usermeta' ? get_user_meta( $post_id ) : get_post_custom( $post_id );
        }

        // Unserialize [values] and do not allow array (take first value from array
        if ( is_array( $cond_values ) ) {
            foreach ( $cond_values as $k => &$v ) {
                $v = maybe_unserialize( $v[0] );
                $v = self::getStringFromArray($v);
            }
        }

        return $cond_values;
    }

    public static function getCustomConditional($custom, $suffix = '', $cond_values = array()) {
        $c_fields = WPToolset_Forms_Conditional::extractFields( $custom );
        $c_values = array();

        // Loop over extracted fields and adjust custom statement
        foreach ( $c_fields as $c_field_id ) {

            // Get field settings
            $c_field = self::getConfig( $c_field_id );

            // If it's Types field
            if ( !empty( $c_field ) ) {

                // Adjust custom statement
                $custom = preg_replace( '/\$\(' . $c_field_id . '\)/',
                        "\$({$c_field['meta_key']}{$suffix})", $custom );
                $custom = preg_replace( '/\$' . $c_field_id . '[\s\)]/',
                        "\${$c_field['meta_key']}{$suffix} ", $custom );

                // Apply filters from field (that is why we set 'type' property)
                wptoolset_form_field_add_filters( $c_field['type'] );
                $c_key = $c_field['meta_key'];
                if ( isset( $cond_values[$c_key] ) ) {
                    $c_values[$c_key . $suffix] = apply_filters( 'wptoolset_conditional_value_php',
                            $cond_values[$c_key], $c_field['type'] );
                }

                // Otherwise do nothing (leave statement as it is and just add [values])
                // That allows checking for non-Types field
            } elseif ( isset( $cond_values[$c_field_id] ) ) {
                $c_values[$c_field_id . $suffix] = $cond_values[$c_field_id];
            }
        }

        // Set conditional setting
        $cond = array(
            'custom' => $custom,
            'values' => $c_values,
        );

        return $cond;
    }

    /**
     * Checks if field is repetitive.
     *
     * @param type $field
     * @return null|boolean
     */
    public static function isRepetitive($config)
    {
        $config = self::getConfig( $config );
        if ( is_null( $config ) ) return null;
        return !empty( $config['data']['repetitive'] );
    }

    /**
     * Returns all fields configurations created by Types.
     *
     * @return array Array of field settings
     */
    public static function getFields()
    {
        return get_option( 'wpcf-fields', array() );
    }

    /**
     * Get specific field configuration created by Types.
     *
     * @param type $field_id
     * @return array|null
     */
    public static function getConfig($field_id)
    {
        if ( !is_string( $field_id ) ) {
            return is_array( $field_id ) ? $field_id : null;
        }
        $fields = self::getFields();
        return isset( $fields[$field_id] ) ? $fields[$field_id] : null;
    }

    /**
     * Returns prefix for specific field meta_key or ID.
     *
     * @param type $field
     * @return string
     */
    public static function getPrefix($config)
    {
        return !empty( $config['data']['controlled'] ) ? '' : 'wpcf-';
    }

    /**
     * Translates various strings connected to Types using WPML icl_t().
     *
     * @param type $name
     * @param type $string
     * @param type $context
     * @return string
     */
    public static function translate($name, $string, $context = 'plugin Types')
    {
        if ( !function_exists( 'icl_t' ) ) {
            return $string;
        }
        return icl_t( $context, $name, stripslashes( $string ) );
    }

    /**
     * Returns field meta key.
     *
     * @param type $config
     * @return type
     */
    public static function getMetakey($config)
    {
        return self::getPrefix( $config ) . $config['id'];
    }

    /**
     * return first string value
     *
     * @param type $string
     * @return string
     */
    private static function getStringFromArray($array)
    {
        if ( is_object( $array ) ) {
            return $array;
        }
        if ( is_array( $array ) ) {
            return self::getStringFromArray(array_shift($array));
        }
        return strval( $array );
    }
}
