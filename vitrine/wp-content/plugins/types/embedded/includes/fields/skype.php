<?php
/**
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/embedded/includes/fields/skype.php $
 * $LastChangedDate: 2015-02-18 14:28:53 +0000 (Wed, 18 Feb 2015) $
 * $LastChangedRevision: 1093394 $
 * $LastChangedBy: iworks $
 *
 */

/**
 * Register data (called automatically).
 *
 * @return type
 */
function wpcf_fields_skype() {
    return array(
        'id' => 'wpcf-skype',
        'title' => __( 'Skype', 'wpcf' ),
        'description' => __( 'Skype', 'wpcf' ),
        'validate' => array(
            'required',
            'skype',
            'maxlength' => array('value' => 32),
            'minlength' => array('value' => 6)
        ),
    );
}

add_filter( 'wpcf_pr_fields_type_skype_value_save',
        'wpcf_pr_fields_type_skype_value_save_filter', 10, 3 );
add_filter( 'wpcf_repetitive_field', 'wpcf_field_skype_repetitive', 10, 4 );

// Add filter when using wpv_condition()
add_filter( 'wpv_condition', 'wpcf_fields_skype_wpv_conditional_trigger' );
add_filter( 'wpv_condition_end', 'wpcf_fields_skype_wpv_conditional_trigger_end' );

/**
 * Form data for post edit page.
 *
 * @param type $field
 */
function wpcf_fields_skype_meta_box_form( $field ) {
    add_thickbox();
    if ( isset( $field['value'] ) ) {
        $field['value'] = maybe_unserialize( $field['value'] );
    }
    $form = array();
    add_filter( 'wpcf_fields_shortcode_slug_' . $field['slug'], 'wpcf_fields_skype_shortcode_filter', 10, 2 );
    $rand = wpcf_unique_id( serialize( $field ) );
    $form['skypename'] = array(
        '#type' => 'textfield',
        '#value' => isset( $field['value']['skypename'] ) ? $field['value']['skypename'] : '',
        '#name' => 'wpcf[' . $field['slug'] . '][skypename]',
        '#id' => 'wpcf-fields-skype-' . $field['slug'] . '-' . $rand . '-skypename',
        '#inline' => true,
        '#suffix' => '&nbsp;' . __( 'Skype name', 'wpcf' ),
        '#description' => '',
        '#prefix' => !empty( $field['description'] ) ? wpcf_translate( 'field ' . $field['id'] . ' description',
                        $field['description'] )
                . '<br /><br />' : '',
        '#attributes' => array('style' => 'width:60%;'),
        '#_validate_this' => true,
        '#before' => '<div class="wpcf-skype">',
    );

    $form['style'] = array(
        '#type' => 'hidden',
        '#value' => isset( $field['value']['style'] ) ? $field['value']['style'] : 'btn2',
        '#name' => 'wpcf[' . $field['slug'] . '][style]',
        '#id' => 'wpcf-fields-skype-' . $field['slug'] . '-' . $rand . '-style',
    );

    $preview_skypename = !empty( $field['value']['skypename'] ) ? $field['value']['skypename'] : '--not--';
    $preview_style = !empty( $field['value']['style'] ) ? $field['value']['style'] : 'btn2';
    $preview = wpcf_fields_skype_get_button_image( $preview_skypename,
            $preview_style );

    // Set button
    // TODO WPML move
    if ( isset( $field['disable'] ) || wpcf_wpml_field_is_copied( $field ) ) {
        $edit_button = '';
    } else {
        $edit_button = ''
                . '<a href="'
                . admin_url( 'admin-ajax.php?action=wpcf_ajax&amp;'
                        . 'wpcf_action=insert_skype_button&amp;_wpnonce='
                        . wp_create_nonce( 'insert_skype_button' )
                        . '&amp;update=wpcf-fields-skype-'
                        . $field['slug'] . '-' . $rand . '&amp;skypename=' . $preview_skypename
                        . '&amp;button_style=' . $preview_style
                        . '&amp;keepThis=true&amp;TB_iframe=true&amp;width=500&amp;height=500' )
                . '"'
                . ' class="thickbox wpcf-fields-skype button-secondary"'
                . ' title="' . __( 'Edit Skype button', 'wpcf' ) . '"'
                . '>'
                . __( 'Edit Skype button', 'wpcf' ) . '</a>';
    }

    $form['markup'] = array(
        '#type' => 'markup',
        '#markup' => '<div class="wpcf-form-item">'
        . '<div id="wpcf-fields-skype-'
        . $field['slug'] . '-' . $rand . '-preview">' . $preview . '</div>'
        . $edit_button . '</div>',
    );
    $form['markup-close'] = array(
        '#type' => 'markup',
        '#markup' => '</div>',
    );
    return $form;
}

/**
 * Editor callback form.
 */
function wpcf_fields_skype_editor_callback( $field, $settings, $meta_type, $post ) {
    // Get saved button style if any
    if ( $meta_type == 'usermeta' ) {
        global $current_user;
        $_field = new WPCF_Usermeta_Field;
        $_field->set( $current_user->ID, $field );
    } else {
        $_field = new WPCF_Field;
        $_field->set( $post, $field );
    }
    $settings['button_style'] = isset( $_field->meta['style'] ) ? $_field->meta['style'] : 'btn2';
    return array(
        'supports' => array('styling'),
        'tabs' => array(
            'display' => array(
                'title' => __( 'Display', 'wpcf' ),
                'menu_title' => __( 'Display', 'wpcf' ),
                'content' => WPCF_Loader::template( 'skype-select-button',
                        $settings ),
            ),
        ),
    );
}

/**
 * Editor submit.
 */
function wpcf_fields_skype_editor_submit( $data, $field, $context ) {
    $add = '';
    if ( !empty( $data['button_style'] ) ) {
        $add .= ' button_style="' . strval( $data['button_style'] ) . '"';
    }

    if ( $context == 'usermeta' ) {
        $add .= wpcf_get_usermeta_form_addon_submit();
        $shortcode = wpcf_usermeta_get_shortcode( $field, $add );
    } else {
        $shortcode = wpcf_fields_get_shortcode( $field, $add );
    }

    return $shortcode;
}

/**
 * Shortcode filter.
 *
 * @param type $shortcode
 * @param type $field
 * @return type
 */
function wpcf_fields_skype_shortcode_filter( $shortcode, $field ) {
    return $shortcode;
    $add = '';
    $add .= isset( $field['value']['skypename'] ) ? ' skypename="' . $field['value']['skypename'] . '"' : '';
//    $add .= isset($field['value']['style']) ? ' style="' . $field['value']['style'] . '"' : '';
    return str_replace( ']', $add . ']', $shortcode );
}

/**
 * Edit Skype button submit.
 */
function wpcf_fields_skype_meta_box_submit() {
    $update = esc_attr( $_GET['update'] );
    $preview = wpcf_fields_skype_get_button_image( esc_attr( $_POST['skypename'] ),
            esc_attr( $_POST['button_style'] ) );

    ?>
    <script type="text/javascript">
        //<![CDATA[
        jQuery(document).ready(function(){
            window.parent.jQuery('#<?php echo $update; ?>-skypename').val('<?php echo esc_js( $_POST['skypename'] ); ?>');
            window.parent.jQuery('#<?php echo $update; ?>-style').val('<?php echo esc_js( $_POST['button_style'] ); ?>');
            window.parent.jQuery('#<?php echo $update; ?>-preview').html('<?php echo $preview; ?>');
            window.parent.jQuery('#TB_closeWindowButton').trigger('click');
        });
        //]]>
    </script>
    <?php
}

/**
 * Edit Skype button AJAX call.
 */
function wpcf_fields_skype_meta_box_ajax() {
    if ( isset( $_POST['_wpnonce_wpcf_form'] ) && wp_verify_nonce( $_POST['_wpnonce_wpcf_form'],
                    'wpcf-form' ) ) {
        add_action( 'admin_head_wpcf_ajax', 'wpcf_fields_skype_meta_box_submit' );
    }
    wp_enqueue_script( 'jquery' );
    wpcf_admin_ajax_head( __( 'Insert skype button', 'wpcf' ) );

    ?>
    <form method="post" action="">
        <h2><?php
    _e( 'Enter your Skype Name', 'wpcf' );

    ?></h2>
        <p>
            <input id="btn-skypename" name="skypename" value="<?php esc_attr_e($_GET['skypename']); ?>" type="text" />
        </p>
        <?php
        echo WPCF_Loader::template( 'skype-select-button', $_GET );

        ?>
        <?php
        wp_nonce_field( 'wpcf-form', '_wpnonce_wpcf_form' );

        ?>
        <br /><br />
        <input type="submit" class="button-primary" value="<?php
    _e( 'Insert skype button', 'wpcf' );

        ?>" />
    </form>
    <?php
    $update = esc_attr( $_GET['update'] );

    ?>
    <script type="text/javascript">
        //<![CDATA[
        jQuery(document).ready(function(){
            jQuery('#btn-skypename').val(window.parent.jQuery('#<?php echo $update; ?>-skypename').val());
        });
        //]]>
    </script>
    <?php
    wpcf_admin_ajax_footer();
}

/**
 * Returns HTML formatted skype button.
 *
 * @param type $skypename
 * @param type $template
 * @param type $class
 * @return type
 */
function wpcf_fields_skype_get_button( $skypename, $template = '',
        $class = false ) {

    if ( empty( $skypename ) ) {
        return '';
    }

    $class = !empty( $class ) ? ' class="' . strval( $class ) . '"' : '';

    switch ( $template ) {

        case 'btn1':
// Call me big drawn
            $output = '<script type="text/javascript" src="http://download.skype.com/share/skypebuttons/js/skypeCheck.js"></script>
<a href="skype:' . $skypename . '?call"><img src="http://download.skype.com/share/skypebuttons/buttons/call_green_white_153x63.png" style="border: none;" width="153" height="63" alt="Skype Me™!"' . $class . ' /></a>';
            break;

        case 'btn4':
// Call me small
            $output = '<script type="text/javascript" src="http://download.skype.com/share/skypebuttons/js/skypeCheck.js"></script>
<a href="skype:' . $skypename . '?call"><img src="http://download.skype.com/share/skypebuttons/buttons/call_blue_transparent_34x34.png" style="border: none;" width="34" height="34" alt="Skype Me™!"' . $class . ' /></a>';
            break;

        case 'btn3':
// Call me small drawn
            $output = '<script type="text/javascript" src="http://download.skype.com/share/skypebuttons/js/skypeCheck.js"></script>
<a href="skype:' . $skypename . '?call"><img src="http://download.skype.com/share/skypebuttons/buttons/call_green_white_92x82.png" style="border: none;" width="92" height="82" alt="Skype Me™!"' . $class . ' /></a>';
            break;

        case 'btn6':
// Status
            $output = '<script type="text/javascript" src="http://download.skype.com/share/skypebuttons/js/skypeCheck.js"></script>
<a href="skype:' . $skypename . '?call"><img src="http://mystatus.skype.com/bigclassic/' . $skypename . '" style="border: none;" width="182" height="44" alt="My status"' . $class . ' /></a>';
            break;

        case 'btn5':
// Status drawn
            $output = '<script type="text/javascript" src="http://download.skype.com/share/skypebuttons/js/skypeCheck.js"></script>
<a href="skype:' . $skypename . '?call"><img src="http://mystatus.skype.com/balloon/' . $skypename . '" style="border: none;" width="150" height="60" alt="My status"' . $class . ' /></a>';
            break;

        default:
// Call me big
            $output = '<script type="text/javascript" src="http://download.skype.com/share/skypebuttons/js/skypeCheck.js"></script>
<a href="skype:' . $skypename . '?call"><img src="http://download.skype.com/share/skypebuttons/buttons/call_blue_white_124x52.png" style="border: none;" width="124" height="52" alt="Skype Me™!"' . $class . ' /></a>';
            break;
    }

    return $output;
}

/**
 * Returns HTML formatted skype button image.
 *
 * @param type $skypename
 * @param type $template
 * @return type
 */
function wpcf_fields_skype_get_button_image( $skypename = '', $template = '' ) {

    if ( empty( $skypename ) ) {
        $skypename = '--not--';
    }

    switch ( $template ) {

        case 'btn1':
// Call me big drawn
            $output = '<img src="http://download.skype.com/share/skypebuttons/buttons/call_green_white_153x63.png" style="border: none;" width="153" height="63" alt="Skype Me™!" />';
            break;

        case 'btn4':
// Call me small
            $output = '<img src="http://download.skype.com/share/skypebuttons/buttons/call_blue_transparent_34x34.png" style="border: none;" width="34" height="34" alt="Skype Me™!" />';
            break;

        case 'btn3':
// Call me small drawn
            $output = '<img src="http://download.skype.com/share/skypebuttons/buttons/call_green_white_92x82.png" style="border: none;" width="92" height="82" alt="Skype Me™!" />';
            break;

        case 'btn6':
// Status
            $output = '<img src="http://mystatus.skype.com/bigclassic/' . $skypename . '" style="border: none;" width="182" height="44" alt="My status" />';
            break;

        case 'btn5':
// Status drawn
            $output = '<img src="http://mystatus.skype.com/balloon/' . $skypename . '" style="border: none;" width="150" height="60" alt="My status" />';
            break;

        default:
// Call me big
            $output = '<img src="http://download.skype.com/share/skypebuttons/buttons/call_blue_white_124x52.png" style="border: none;" width="124" height="52" alt="Skype Me™!" />';
            break;
    }

    return $output;
}

/**
 *
 * View function.
 *
 * @param type $params
 *
 * @return string
 *
 */
function wpcf_fields_skype_view( $params )
{
    if ( empty( $params['field_value']['skypename'] ) ) {
        return '__wpcf_skip_empty';
    }
    // Button style
    $button_style = 'default';
    // First check if passed by parameter
    if ( array_key_exists( 'button_style', $params ) && $params['button_style'] ) {
        $button_style = $params['button_style'];
        // Otherwise use saved value
    } else if ( array_key_exists( 'style', $params['field_value'] ) && $params['field_value']['style'] ) {
        $button_style = $params['field_value']['style'];
    } else if ( array_key_exists( 'button_style', $params['field_value'] ) && $params['field_value']['button_style'] ) {
        $button_style = $params['field_value']['button_style'];
    }
    // Style can be overrided by params (shortcode)
    if ( !isset( $params['field_value']['style'] ) ) {
        $params['field_value']['style'] = '';
    }
    $class = empty( $params['class'] ) ? false : $params['class'];
    $content = wpcf_fields_skype_get_button( $params['field_value']['skypename'], $button_style, $class );
    return $content;
}

/**
 * Filters post relationship save data.
 *
 * @param type $data
 * @param type $meta_key
 * @param type $post_id
 * @return type
 */
function wpcf_pr_fields_type_skype_value_save_filter( $data, $meta_key = null,
        $post_id = null ) {
    $meta = (array) get_post_meta( $post_id, $meta_key, true );
    $meta['skypename'] = $data;
    $data = $meta;
    return $data;
}

/**
 * Processes repetitive Skype fields.
 *
 * Each form element is sent separately.
 * Determine which is which and process it.
 *
 * @staticvar array $repetitive_started
 * @staticvar array $repetitive_index
 * @param type $post
 * @param string $field
 * @param type $skype_element
 * @return string
 */
function wpcf_field_skype_repetitive( $element, $post, $field, $array_key ) {

    global $wpcf;

    if ( $field['type'] != 'skype' ) {
        return $element;
    }


    switch ( $array_key ) {
        case 'skypename':
            // TODO WPML move
            if ( wpcf_wpml_field_is_copied( $field ) ) {
                $element['#after'] .= '<input type="hidden" name="wpcf_repetitive_copy['
                        . $field['id'] . '][' . $wpcf->repeater->index
                        . ']" value="1" />';
            }

            /*
             *
             * If added via AJAX set value
             */
            if ( defined( 'DOING_AJAX' ) ) {
                $field['value'] = '__wpcf_repetitive_new_field';
                $element['#value'] = '';
            }
            break;


        default:
            break;
    }

    return $element;
}

/**
 * Triggers post_meta filter.
 *
 * @param type $post
 * @return type
 */
function wpcf_fields_skype_wpv_conditional_trigger( $post ) {
    add_filter( 'get_post_metadata',
            'wpcf_fields_skype_conditional_filter_post_meta', 10, 4 );
}

/**
 * Returns 'skypename' if available.
 *
 * @global type $wpcf
 * @param type $null
 * @param type $object_id
 * @param type $meta_key
 * @param type $single
 * @return type
 */
function wpcf_fields_skype_conditional_filter_post_meta( $null, $object_id,
        $meta_key, $single ) {

    global $wpcf;
    $field = wpcf_admin_fields_get_field( $wpcf->field->__get_slug_no_prefix( $meta_key ) );
    if ( !empty( $field ) && $field['type'] == 'skype' ) {
        $_meta = maybe_unserialize( wpcf_get_post_meta( $object_id, $meta_key,
                        $single ) );
        if ( is_array( $_meta ) ) {
            $null = isset( $_meta['skypename'] ) ? $_meta['skypename'] : '';
        }
        /**
         * be sure do not return string if array is expected!
         */
        if ( !$single && !is_array($null) ) {
            return array($null);
        }
    }
    return $null;
}

/**
 * Removes trigger post_meta filter.
 *
 * @param type $evaluate
 * @return type
 */
function wpcf_fields_skype_wpv_conditional_trigger_end( $post ) {
    remove_filter( 'get_post_metadata',
            'wpcf_fields_skype_conditional_filter_post_meta', 10, 4 );
}
