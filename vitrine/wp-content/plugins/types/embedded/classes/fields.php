<?php
/**
 * Fields class.
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/embedded/classes/fields.php $
 * $LastChangedDate: 2014-05-07 06:56:23 +0000 (Wed, 07 May 2014) $
 * $LastChangedRevision: 909470 $
 * $LastChangedBy: iworks $
 *
 */
require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';

/**
 * Fields class.
 * 
 * @since Types 1.2
 * @package Types
 * @subpackage Classes
 * @version 0.1
 * @category core
 * @author srdjan <srdjan@icanlocalize.com>
 */
class WPCF_Fields
{

    /**
     * Holds all available field types and their config data.
     * 
     * @var type 
     */
    static $fieldTypesData = null;

    /**
     * Returns array of available (registered) field types
     * and paths to config files.
     * 
     * @return type
     */
    public static function getFieldsTypes() {
        $fields = array(
            'audio' => WPCF_EMBEDDED_INC_ABSPATH . '/fields/audio.php',
            'checkbox' => WPCF_EMBEDDED_INC_ABSPATH . '/fields/checkbox.php',
            'checkboxes' => WPCF_EMBEDDED_INC_ABSPATH . '/fields/checkboxes.php',
            'colorpicker' => WPCF_EMBEDDED_INC_ABSPATH . '/fields/colorpicker.php',
            'date' => WPCF_EMBEDDED_INC_ABSPATH . '/fields/date.php',
            'email' => WPCF_EMBEDDED_INC_ABSPATH . '/fields/email.php',
            'embed' => WPCF_EMBEDDED_INC_ABSPATH . '/fields/embed.php',
            'file' => WPCF_EMBEDDED_INC_ABSPATH . '/fields/file.php',
            'image' => WPCF_EMBEDDED_INC_ABSPATH . '/fields/image.php',
            'map' => WPCF_EMBEDDED_INC_ABSPATH . '/fields/map.php',
            'numeric' => WPCF_EMBEDDED_INC_ABSPATH . '/fields/numeric.php',
            'phone' => WPCF_EMBEDDED_INC_ABSPATH . '/fields/phone.php',
            'radio' => WPCF_EMBEDDED_INC_ABSPATH . '/fields/radio.php',
            'select' => WPCF_EMBEDDED_INC_ABSPATH . '/fields/select.php',
            'skype' => WPCF_EMBEDDED_INC_ABSPATH . '/fields/skype.php',
            'textarea' => WPCF_EMBEDDED_INC_ABSPATH . '/fields/textarea.php',
            'textfield' => WPCF_EMBEDDED_INC_ABSPATH . '/fields/textfield.php',
            'twitter' => WPCF_EMBEDDED_INC_ABSPATH . '/fields/twitter.php',
            'url' => WPCF_EMBEDDED_INC_ABSPATH . '/fields/url.php',
            'video' => WPCF_EMBEDDED_INC_ABSPATH . '/fields/video.php',
            'wysiwyg' => WPCF_EMBEDDED_INC_ABSPATH . '/fields/wysiwyg.php',
        );
        return apply_filters( 'types_register_fields', $fields );
    }

    /**
     * Returns array of available (registered) field types
     * and their config data.
     * 
     * @return type
     */
    public static function getFieldsTypesData() {
        if ( !is_null( self::$fieldTypesData ) ) {
            return self::$fieldTypesData;
        }
        self::$fieldTypesData = self::getFieldsTypes();
        foreach ( self::$fieldTypesData as $type => $path ) {
            $data = self::getFieldTypeConfig( $path );
            if ( !empty( $data ) ) {
                self::$fieldTypesData[$type] = $data;
            } else {
                unset( self::$fieldTypesData[$type] );
            }
            if ( isset($data['wp_version'])
                    && wpcf_compare_wp_version( $data['wp_version'], '<' ) ) {
                unset( self::$fieldTypesData[$type] );
            }
        }
        return self::$fieldTypesData;
    }

    /**
     * Get field type data.
     * 
     * @param type $type
     * @return type
     */
    public static function getFieldTypeData( $type ) {
        $fields = self::getFieldsTypes();
        return isset( $fields[$type] ) ? self::getFieldTypeConfig( $fields[$type] ) : array();
    }

    /**
     * Returns data for certain field type.
     * 
     * @param type $type
     * @return type
     */
    public static function getFieldTypeConfig( $path ) {
        if ( !is_string( $path ) ) {
            return array();
        }
        if ( file_exists( $path ) ) {
            require_once $path;
            if ( function_exists( 'wpcf_fields_' . basename( $path, '.php' ) ) ) {
                return call_user_func( 'wpcf_fields_' . basename( $path, '.php' ) );
            }
        }
        return array();
    }

    /**
     * Get fields.
     * 
     * Parameters for
     * wpcf_admin_fields_get_fields()
     * 
     * $only_active = false
     * $disabled_by_type = false
     * $strictly_active = false
     * 
     * @return \stdClass 
     */
    public static function getFields( $args = array(), $toolset = 'types' ) {
        $active = isset( $args['active'] ) ? (bool) $args['active'] : true;
        return wpcf_admin_fields_get_fields( $active );
    }

    /**
     * Checks if field is under control.
     * 
     * @param type $field_key
     * @return type
     */
    public static function isUnderControl( $field_key ) {
        $fields = self::getFields();
        return !empty( $fields[$field_key] );
    }

    /**
     * Enqueue all files from config
     */
    public static function enqueueScript( $field ) {
        $config = (object) self::getFieldTypeData( $field );
        if ( !empty( $config->meta_box_js ) ) {
            foreach ( $config->meta_box_js as $handle => $data_script ) {
                if ( isset( $data_script['inline'] ) ) {
                    add_action( 'admin_footer', $data_script['inline'] );
                    continue;
                }
                if ( !isset( $data_script['src'] ) ) {
                    continue;
                }
                $deps = !empty( $data_script['deps'] ) ? $data_script['deps'] : array();
                wp_enqueue_script( $handle, $data_script['src'], $deps,
                        WPCF_VERSION );
            }
        }
    }

    /**
     * Enqueue all files from config
     */
    public static function enqueueStyle( $field ) {
        $config = (object) self::getFieldTypeData( $field );
        if ( !empty( $config->meta_box_css ) ) {
            foreach ( $config->meta_box_css as $handle => $data_script ) {
                $deps = !empty( $data_script['deps'] ) ? $data_script['deps'] : array();
                if ( isset( $data_script['inline'] ) ) {
                    add_action( 'admin_head', $data_script['inline'] );
                    continue;
                }
                if ( !isset( $data_script['src'] ) ) {
                    continue;
                }
                wp_enqueue_style( $handle, $data_script['src'], $deps,
                        WPCF_VERSION );
            }
        }
    }

}
