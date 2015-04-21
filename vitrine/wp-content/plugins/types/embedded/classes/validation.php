<?php
/*
 * Validation class.
 */

/**
 * Validation class.
 * 
 * @since Types 1.1.5
 * @package Types
 * @subpackage Validation
 * @version 0.1.2
 * @category core
 * @author srdjan <srdjan@icanlocalize.com>
 */
class WPCF_Validation
{

    function __construct() {
        add_filter( 'wpcf_field', array($this, 'filter_field') );
    }

    /**
     * Filters each field.
     * 
     * Uses 'wpcf_field' hook. Filters field settings.
     * 
     * @param type $field
     * @return int
     */
    function filter_field( $field ) {
        if ( $field['type'] == 'date' ) {
            if ( !fields_date_timestamp_neg_supported() ) {
                if ( !isset( $field['data']['validate'] )
                        || !is_array( $field['data']['validate'] ) ) {
                    $field['data']['validate'] = array();
                }
                $field['data']['validate']['negativeTimestamp'] = array(
                    'active' => 1,
                    'value' => 'true',
                    'message' => wpcf_admin_validation_messages( 'negativeTimestamp' ),
                );
            }
        }
        return $field;
    }
    
    /**
     * Returns formatted JSON validation data.
     * 
     * @param type $selector Can be CSS class or element ID
     * @return string
     */
    static function renderJsonData( $selector = '.wpcf-form-validate' ) {

        // Get collected validation rules
        $elements = wpcf_form_add_js_validation( 'get' );

        // Not collected!
        if ( empty( $elements ) ) {
            return '';
        }

        $json = array('selector' => $selector, 'elements' => array());

        foreach ( $elements as $id => $element ) {
            // Basic check or skip read-only
            if ( empty( $element['#validate'] ) || isset( $element['#attributes']['readonly'] ) ) {
                continue;
            }

            $json['elements'][$id] = array('id' => $id);

            // Set selectors
            if ( in_array( $element['#type'], array('radios') ) ) {
                $json['elements'][$id]['selector'] = "input[name=\"{$element['#name']}\"]";
            } else {
                $json['elements'][$id]['selector'] = "#{$id}";
            }

            foreach ( $element['#validate'] as $method => $args ) {
                $args['value'] = !isset( $args['value'] ) ? 'true' : $args['value'];
                if ( empty( $args['message'] ) ) {
                    $args['message'] = wpcf_admin_validation_messages( $method,
                            $args['value'] );
                }
                //Check if wordpress date format is d/m/Y and use ITA validation
                if ( $method == 'date' && isset( $element['wpcf-type'] ) && $element['wpcf-type'] == 'date' && get_option( 'date_format' ) == 'd/m/Y' ) {
                    $method = 'dateITA:true';
                }
                // Set JSON data
                $json['elements'][$id]['rules'][] = array(
                    'method' => $method,
                    'value' => $args['value'],
                    'message' => $args['message'],
                );
            }
        }
        ob_start();

        ?>

        <script type="text/javascript">
            //<![CDATA[
            types.validation.push(<?php echo json_encode( $json ); ?>);
            <?php if ( defined ('DOING_AJAX') ): ?>
            jQuery(document).ready(function($) {
                typesValidation.setRules();
            });
        <?php endif; ?>
            /* ]]> */
        </script>

        <?php
        $output = ob_get_contents();
        ob_get_clean();

        wp_enqueue_script( 'types-validation' );

        return $output;
    }

}