<?php
/*
 * WP Post Editor class.
 */

/**
 * WP Post Editor class.
 *
 * @since Types 1.3.2
 * @package Types
 * @subpackage Classes
 * @version 0.1
 * @category Field
 * @author srdjan <srdjan@icanlocalize.com>
 */
class WPCF_Editor
{

    /**
     * Settings.
     * @var type 
     */
    private $_settings = array();

    /**
     * Active Field.
     * @var type
     */
    var $field = array();

    /**
     * Context (postmeta|usermeta).
     * @var type
     */
    private $_meta_type = 'post';

    /**
     * Post object.
     * @var type
     */
    private $_post;

    /**
     * Collected data.
     * @var type
     */
    private $_data = array();

    /**
     * Construct function.
     */
    function __construct() {
        wp_register_script( 'types-editor',
                WPCF_EMBEDDED_RES_RELPATH . '/js/editor.js',
                array('jquery', 'types-knockout'), WPCF_VERSION, true );
        wp_register_style( 'types-editor',
                WPCF_EMBEDDED_RES_RELPATH . '/css/editor.css',
                array('admin-bar', 'wp-admin', 'buttons', 'media-views', 'toolset-font-awesome'),
                WPCF_VERSION );
        wp_register_style( 'types-editor-cloned',
                WPCF_EMBEDDED_RES_RELPATH . '/css/editor-cloned.css', array(),
                WPCF_VERSION );
    }

    /**
     * Renders Thickbox content.
     *
     * Field should provide callback function
     * that will be called automatically.
     *
     * Function should be named like:
     * 'wpcf_fields_' . $field_type . '_editor_callback'
     * e.g. 'wpcf_fields_checkbox__editor_callback'
     *
     * Function should return array with elements:
     * 'supports' - parameters or other feature supported, e.g. 'styling' will
     *     enable 'Styling' options
     *
     * Tabs is array with elements:
     * 'menu_title' - used for menu title
     * 'title' - used for main title
     * 'content' - HTML content of tab
     *
     * @param type $field
     * @param type $meta_type
     * @param type $post_id
     * @param string $shortcode
     */
    function frame( $field, $meta_type = 'postmeta', $post_id = -1,
            $shortcode = null, $callback = false, $views_usermeta = false ) {

        global $wp_version, $wpcf;

        // Queue rendering JS settings
        add_action( 'admin_print_footer_scripts',
                array($this, 'renderTedSettings'), 1 );

        wp_enqueue_script( 'types' );
        wp_enqueue_script( 'types-knockout' );
        wp_enqueue_script( 'types-editor' );
        wp_enqueue_script( 'wp-pointer' );
        wp_enqueue_style( 'types-editor' );
        wp_enqueue_style( 'wp-pointer' );
        wp_enqueue_style( 'toolset-font-awesome' );

        // Load cloned WP Media Modal CSS
        if ( version_compare( $wp_version, '3.5', '<' ) ) {
            wp_enqueue_style( 'types-editor-cloned' );
        }

        $this->field = $field;
        $this->_meta_type = $meta_type;
        $this->_post = get_post( $post_id );
        $this->_settings = is_null( $shortcode ) ? array() : $this->shortcodeToParameters( $shortcode );
        $this->callback = $callback;
        $this->_data = array(
            'meta_type' => $meta_type,
            'field' => $field,
            'field_type_data' => WPCF_Fields::getFieldTypeData( $field['type'] ),
            'settings' => array(),
            'tabs' => array(),
            'supports' => array(),
            'post' => $this->_post,
            'post_types' => get_post_types( array('show_ui' => true) ),
            'style' => isset( $this->_settings['style'] ) ? $this->_settings['style'] : '',
            'class' => isset( $this->_settings['class'] ) ? $this->_settings['class'] : '',
            'output' => 'html',
            'user_form' => '',
        );

        // Set title if updated
        if ( !is_null( $shortcode ) ) {
            $this->_data['title'] = sprintf( __( 'Update %s', 'wpcf' ),
                    $this->_data['field_type_data']['title'] );
            $this->_data['submit_button_title'] = __( 'Update shortcode', 'wpcf' );
        }

        // Exclude post types
        foreach ( $wpcf->excluded_post_types as $_post_type ) {
            unset( $this->_data['post_types'][$_post_type] );
        }

        /*
         * Callback
         */
        $function = 'wpcf_fields_' . $field['type'] . '_editor_callback';
        if ( function_exists( $function ) ) {
            // Main callback
            $callback = call_user_func( $function, $field, $this->_settings,
                    $this->_meta_type, $this->_post );
            // Add supports
            if ( !empty( $callback['supports'] ) && is_array( $callback['supports'] ) ) {
                $this->_data['supports'] = $callback['supports'];
            }
            // Add tabs
            if ( !empty( $callback['tabs'] ) && is_array( $callback['tabs'] ) ) {
                $this->_data['tabs'] = $callback['tabs'];
            }
            // Unify settings
            if ( !empty( $callback['settings'] ) && is_array( $callback['settings'] ) ) {
                $this->_settings = array_merge( $this->_settings,
                        self::sanitizeParams( $callback['settings'], 'array' ) );
            }
        }

        // If no tabs
        if ( empty( $this->_data['tabs'] ) ) {
            $this->_data['tabs']['display'] = array(
                'menu_title' => __( 'Display', 'wpcf' ),
                'title' => __( 'Display', 'wpcf' ),
                'content' => sprintf( __( 'There are no additional display options for the %s field.',
                                'wpcf' ),
                        $this->_data['field_type_data']['title'] ),
            );
        }

        // Add User ID form
        if ( $this->_meta_type == 'usermeta' ) {
            if ( !$views_usermeta ){    
                $this->_data['user_form'] = wpcf_form_simple( wpcf_get_usermeta_form_addon( $this->_settings ) );
                $this->_data['supports'][] = 'user_id';
            }
           
        } else {
            // Add Post ID form
            $this->_data['supports'][] = 'post_id';
        }

        // Get parents
        if ( !empty( $this->_post->ID ) ) {
            $this->_data['parents'] = WPCF_Relationship::get_parents( $this->_post );
        }

        // Set icons
        $icons = array(
            'audio' => 'icon-music',
            'checkbox' => 'icon-check',
            'checkboxes' => 'icon-checkboxes',
            'colorpicker' => 'icon-tint',
            'date' => 'icon-calendar',
            'email' => 'icon-envelope-alt',
            'embed' => 'icon-youtube-play',
            'file' => 'icon-file-alt',
            'image' => 'icon-picture',
            'map' => 'icon-map-marker',
            'numeric' => 'icon-numeric',
            'phone' => 'icon-phone',
            'radio' => 'icon-radio-button',
            'select' => 'icon-select-box',
            'skype' => 'icon-skype',
            'textarea' => 'icon-text-area',
            'textfield' => 'icon-text-field',
            'url' => 'icon-link',
            'video' => 'icon-film',
            'wysiwyg' => 'icon-wysiwyg',
        );
        $this->_data['icon_class'] = isset( $icons[$field['type']] ) ? $icons[$field['type']] : 'icon-text-field';

        // Is repetitive
        $this->_data['is_repetitive'] = (bool) types_is_repetitive( $field );
        if ( $this->_data['is_repetitive'] ) {
            $this->_data['supports'][] = 'separator';
        }

        // Render header
        wpcf_admin_ajax_head();

        // Check if submitted
        $this->_thickbox_check_submit();

        // Render form
        echo '<form method="post" action="" id="types-editor-modal-form">';
        echo WPCF_Loader::view( 'editor-modal-window', $this->_data );
        wp_nonce_field( 'types_editor_frame', '__types_editor_nonce' );
        echo '</form>';

        // Render footer
        wpcf_admin_ajax_footer();
    }

    /**
     * Renders JS settings queued after editor.js
     */
    function renderTedSettings() {
        $_field = $this->field;
        echo "\r\n" . "\r\n" . '<script type="text/javascript">' . "\r\n"
        . '//<![CDATA[' . "\r\n"
        . 'var ted = {' . "\r\n"
        . '     fieldID: ' . json_encode( sanitize_title( $_field['id'] ) ) . ',' . "\r\n"
        . '     fieldType: ' . json_encode( sanitize_title( $_field['type'] ) ) . ',' . "\r\n"
        . '     fieldTitle: ' . json_encode( strval( self::sanitizeParams( $_field['name'] ) ) ) . ',' . "\r\n"
        . '     params: ' . json_encode( $this->_settings ) . ',' . "\r\n"
        . '     repetitive: ' . json_encode( $this->_data['is_repetitive'] ) . ',' . "\r\n"
        . '     metaType: ' . json_encode( $this->_meta_type ) . ',' . "\r\n"
        . '     postID: ' . json_encode( !empty( $this->_post->ID ) ? (int) $this->_post->ID : -1  ) . ',' . "\r\n"
        . '     callback: ' . json_encode( $this->callback ) . ',' . "\r\n"
        . '     supports: ' . json_encode( $this->_data['supports'] ) . '' . "\r\n"
        . '};' . "\r\n"
        . '//]]>' . "\r\n"
        . '</script>' . "\r\n" . "\r\n";
    }

    /**
     * Process if submitted.
     *
     * Field should provide callback function
     * that will be called automatically.
     *
     * Function should be named like:
     * 'wpcf_fields_' . $field_type . '_editor_submit'
     * e.g. 'wpcf_fields_checkbox_editor_submit'
     *
     * Function should return shortcode string.
     */
    function _thickbox_check_submit() {
        if ( !empty( $_POST['__types_editor_nonce'] )
                && wp_verify_nonce( $_POST['__types_editor_nonce'],
                        'types_editor_frame' ) ) {

            $function = 'wpcf_fields_' . strtolower( $this->field['type'] )
                    . '_editor_submit';

            if ( function_exists( $function ) ) {
                /*
                 * Callback
                 */
                $shortcode = call_user_func( $function, $_POST, $this->field, $this->_meta_type );
            } else {
                /*
                 * Generic
                 */
                if ( $this->_meta_type == 'usermeta' ) {
                    $add = wpcf_get_usermeta_form_addon_submit();
                    $shortcode = wpcf_usermeta_get_shortcode( $this->field, $add );
                } else {
                    $shortcode = wpcf_fields_get_shortcode( $this->field );
                }
            }

            if ( !empty( $shortcode ) ) {
                /**
                 * remove <script> tag from all data
                 * remove not allowed tags from shortcode using wp_kses_post
                 */
                $shortcode = preg_replace( '@</?script[^>]*>@im', '', wp_kses_post($shortcode) );
                // Add additional parameters if required
                $shortcode = $this->_add_parameters_to_shortcode( $shortcode, $_POST );
                // Insert shortcode
                echo '<script type="text/javascript">jQuery(function(){tedFrame.close(\''
                . $shortcode . '\', \'' . esc_js( $shortcode ) . '\');});</script>';
            } else {
                echo '<div class="message error"><p>'
                . __( 'Shortcode generation failed', 'wpcf' ) . '</p></div>';
            }

            wpcf_admin_ajax_footer();
            die();
        }
    }

    /**
     * Adds additional parameters if required.
     *
     * @param type $shortcode
     * @param type $data
     * @return type
     */
    function _add_parameters_to_shortcode( $shortcode, $data ) {
        $raw_mode = isset( $data['raw_mode'] ) && $data['raw_mode'] == '1';
        if ( !$raw_mode ) {
            if ( isset( $data['class'] ) && $data['class'] != '' ) {
                $shortcode = preg_replace( '/\[types([^\]]*)/',
                        '$0 class="' . esc_attr(strip_tags($data['class'])) . '"', $shortcode );
            }
            if ( $this->supports( 'style' ) && isset( $data['style'] ) && $data['style'] != '' ) {
                $shortcode = preg_replace( '/\[types([^\]]*)/',
                        '$0 style="' . esc_attr(strip_tags($data['style'])) . '"', $shortcode );
            }
            if ( isset( $data['output'] ) && $data['output'] == 'html' ) {
                $shortcode = preg_replace( '/\[types([^\]]*)/',
                        '$0 output="html"', $shortcode );
            }
        }
        if ( !empty( $data['separator'] ) ) {
            if ( $data['separator'] == 'custom' ) {
                $data['separator'] = isset( $data['separator_custom'] ) ? wp_kses_post($data['separator_custom']) : '';
            }
            $shortcode = preg_replace( '/\[types([^\]]*)/',
                    '$0 separator="'
                    . htmlentities( $data['separator'] ) . '"', $shortcode );
        }
        if ( isset( $data['show_name'] ) && $data['show_name'] == '1' ) {
            $shortcode = preg_replace( '/\[types([^\]]*)/',
                    '$0 show_name="true"', $shortcode );
        }
        if ( isset( $data['raw_mode'] ) && $data['raw_mode'] == '1' ) {
            $shortcode = preg_replace( '/\[types([^\]]*)/', '$0 output="raw"',
                    $shortcode );
        }
        if ( isset( $data['post_id'] ) && $data['post_id'] != 'current' ) {
            $post_id = 'id=';
            if ( $data['post_id'] == 'post_id' ) {
                $post_id .= '"' . preg_replace( '/[^\d]+/', '', $data['specific_post_id'] ) . '"';
            } else if ( $data['post_id'] == 'parent' ) {
                $post_id .= '"$parent"';
            } else if ( $data['post_id'] == 'related' ) {
                $post_id .= '"$' . esc_attr(trim( strval( $data['related_post'] ) )) . '"';
            } else {
                $post_id .= '"' . preg_replace( '/[^\d]+/', '', $data['post_id'] ) . '"';
            }
            $shortcode = preg_replace( '/\[types([^\]]*)/', '$0 ' . $post_id,
                    $shortcode );
        }
        return $shortcode;
    }

    /**
     * Checks if feature is supported.
     * 
     * @param type $feature
     * @return type
     */
    function supports( $feature ) {
        return in_array( $feature, $this->_data['supports'] );
    }

    /**
     * Converts shortcode string to array of parameters.
     * 
     * @param type $shortcode
     */
    function shortcodeToParameters( $shortcode ) {

        if ( !is_string( $shortcode ) ) {
            return is_array( $shortcode ) ? $shortcode : array();
        }

        $params = array();
        $pattern = get_shortcode_regex();
        preg_match_all( "/$pattern/s", stripslashes( $shortcode ), $matches );

        if ( !empty( $matches[3] ) ) {
            $options = array();
            foreach ( $matches[3] as $index => $match ) {
                $_params = shortcode_parse_atts( trim( $match, '[]' ) );
                switch ( $this->field['type'] ) {
                    case 'checkbox':
                        $_params['mode'] = 'db';
                        if ( isset( $_params['state'] ) ) {
                            $_params['mode'] = 'value';
                            if ( $_params['state'] == 'checked' ) {
                                $_params['selected'] = $matches[5][$index];
                            } else if ( $_params['state'] == 'unchecked' ) {
                                $_params['not_selected'] = $matches[5][$index];
                            }
                        }
                        $params = array_merge( $params, $_params );
                        break;

                    case 'checkboxes':
                        $_params['mode'] = isset( $_params['separator'] ) ? 'display_all' : 'db';
                        if ( isset( $_params['option'] ) ) {
                            $_option = $_params['option'];
                            $_params['mode'] = 'value';
                            $_params['state'] = isset( $_params['state'] ) ? $_params['state'] : 'checked';
                            if ( $_params['state'] == 'unchecked' ) {
                                $options[$_option]['not_selected'] = $matches[5][$index];
                            } else {
                                $options[$_option]['selected'] = $matches[5][$index];
                            }
                            unset( $_params['option'], $_params['state'] );
                        }
                        $params = array_merge( $params, $_params );
                        break;

                    case 'radio':
                        $_params['mode'] = 'db';
                        if ( isset( $_params['option'] ) ) {
                            $_option = $_params['option'];
                            $_params['mode'] = 'value';
                            $options[$_option] = $matches[5][$index];
                            unset( $_params['option'] );
                        }
                        $params = array_merge( $params, $_params );
                        break;

                    case 'image':
                        if ( isset( $_params['width'] ) || isset( $_params['height'] ) ) {
                            $_params['image_size'] = 'wpcf-custom';
                        }
                        $params = array_merge( $params, $_params );
                        break;

                    default:
                        $params = shortcode_parse_atts( $matches[3][0] );
                        break;
                }
            }
            if ( !empty( $options ) ) {
                $params['options'] = $options;
            }
        }

        if ( !is_array( $params ) ) {
            return array();
        }

        while ( $next = next( $params ) ) {
            $param = key( $params );
            $value = $params[$param];
            switch ( $param ) {
                case 'id':
                    if ( is_numeric( $value ) ) {
                        $params['post_id'] = 'post_id';
                        $params['specific_post_id'] = intval( $value );
                    } else {
                        if ( $value === '$parent' ) {
                            $params['post_id'] = 'parent';
                        } else if ( $value === 'current' ) {
                            $params['post_id'] = self::sanitizeParams( $value );
                        } else {
                            $params['post_id'] = 'related';
                            $params['related_post'] = trim( self::sanitizeParams( $value ),
                                    '$' );
                        }
                    }
                    $params[$param] = self::sanitizeParams( $value );
                    break;

                case 'options':
                    $params[$param] = self::sanitizeParams( $value, 'array' );
                    break;

                default:
                    $params[$param] = self::sanitizeParams( $value );
                    break;
            }
        }
        return $params;
    }

    /**
     * Sanitize value before writing to JS.
     * 
     * @param type $value
     * @return type
     */
    public static function sanitizeParams( $value, $is_array = false ) {
        if ( $is_array === 'array' && is_array( $value ) ) {
            foreach ( $value as $k => $v ) {
                $value[$k] = self::sanitizeParams( $v, 'array' );
            }
            return $value;
        }
        return htmlentities( stripslashes( strval( $value ) ), ENT_QUOTES );
    }

}
