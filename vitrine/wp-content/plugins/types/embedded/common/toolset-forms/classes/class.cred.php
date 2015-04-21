<?php
/*
 * Types fields specific
 */
require_once 'class.types.php';
require_once 'class.conditional.php';

/**
 * Class description
 * 
 * @author Srdjan
 */
class WPToolset_Cred
{

    /**
     * Filters validation.
     * 
     * Loop over validation settings and create array of validation rules.
     * array( $rule => array( 'args' => array, 'message' => string ), ... )
     * 
     * @param array|string $field settings array (as stored in DB) or field ID
     * @return array array( $rule => array( 'args' => array, 'message' => string ), ... )
     */
    public static function filterValidation( $config ){
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
                    $validation[$rule] = array(
                        'args' => isset( $settings['args'] ) ? array_unshift( $value,
                                        $settings['args'] ) : array($value, true),
                        'message' => $settings['message']
                    );
                }
            }
        }
        return $validation;
    }

    /**
     * Filters conditional.
     * 
     * We'll just handle this as a custom conditional
     * 
     * Custom conditional
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
    public static function filterConditional( $if, $post_id ){
        
        $data = WPToolset_Types::getCustomConditional($if, '', WPToolset_Types::getConditionalValues($post_id));
        return $data;
    }

}
