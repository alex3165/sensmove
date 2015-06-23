<?php
/*

  This is example how to add simple Google Maps field type.
  Most of functions are called automatically from Types plugin.
  Functions naming conventions are:

  - For basic type data (required) callback
  wpcf_fields_$myfieldname()

  Optional

  - Group form data callback
  wpcf_fields_$myfieldname_insert_form()

  - Post edit page form callback
  wpcf_fields_$myfieldname_meta_box_form()

  - Editor popup callback
  wpcf_fields_$myfieldname_editor_callback()

  - View function callback
  wpcf_fields_$myfieldname_view()

 */

// Add registration hook
add_filter( 'types_register_fields', 'my_types' );

/**
 * Register custom post type on 'types_register_fields' hook.
 * 
 * @param array $fields
 * @return type
 */
function my_types( $fields ) {
    $fields['google_map'] = __FILE__;
    return $fields;
}

/**
 * Define field.
 * 
 * @return type
 */
function wpcf_fields_google_map() {
    return array(
        'path' => __FILE__, // This is deprecated but should be tested for safe removal
        'id' => 'google_map',
        'title' => __( 'Google Map', 'wpcf' ),
        'description' => __( 'This is additional field', 'wpcf' ),
        /*
         * Validation
         * 
         * TODO Elaborate on this
         * Add examples for various usage (review needed)
         */
        'validate' => array('required'),
        /*
         * 
         * 
         * 
         * 
         * 
         * 
         * Possible (optional) parameters
         */
        // Additional JS on post edit page
        'meta_box_js' => array(// Add JS when field is active on post edit page
            'wpcf-jquery-fields-my-field' => array(
                'inline' => 'wpcf_fields_google_map_meta_box_js_inline', // This calls function that renders JS
                'deps' => array('jquery'), // (optional) Same as WP's enqueue_script() param
                'in_footer' => true, // (optional) Same as WP's enqueue_script() param
            ),
            /**
             * example how to add javascript file
             *
            'wpcf-jquery-fields-my-field' => array(
                'src' => get_stylesheet_directory_uri() . '/js/my-field.js', // This will load JS file
            ),
             */
        ),
        // Additional CSS on post edit page
        'meta_box_css' => array(
            'wpcf-jquery-fields-my-field' => array(
                'src' => get_stylesheet_directory_uri() . '/css/my-field.css', // or inline function 'inline' => $funcname
                'deps' => array('somecss'), // (optional) Same as WP's enqueue_style() param
            ),
        ),
        // Additional JS on group edit page
        'group_form_js' => array(// Add JS when field is active on post edit page
            /**
             * example how to add javascript file wit callback fundtion
             *
            'wpcf-jquery-fields-my-field' => array(
                'inline' => 'wpcf_fields_google_map_group_form_js_inline', // This calls function that renders JS
                'deps' => array('jquery'), // (optional) Same as WP's enqueue_script() param
                'in_footer' => true, // (optional) Same as WP's enqueue_script() param
            ),
             */
            /**
             * example how to add javascript file
             *
            'wpcf-jquery-fields-my-field' => array(
                'src' => get_stylesheet_directory_uri() . '/js/my-field.js', // This will load JS file
            ),
             */
        ),
        // Additional CSS on post edit page
        'group_form_css' => array(
            'wpcf-jquery-fields-my-field' => array(
                'src' => get_stylesheet_directory_uri() . '/css/my-field.css', // or inline function 'inline' => $funcname
                'deps' => array('somecss'), // (optional) Same as WP's enqueue_style() param
            ),
        ),
        // override editor popup link (you must then load JS function that will process it)
//        'editor_callback' => 'wpcfFieldsMyFieldEditorCallback(\'%s\')', // %s will inject field ID
        // meta key type
        'meta_key_type' => 'INT',
        // Required WP version check
        'wp_version' => '3.3',
    );
}

/**
 * Types Group edit screen form.
 * 
 * Here you can specify all additional group form data if nedded,
 * it will be auto saved to field 'data' property.
 * 
 * @return string
 */
function wpcf_fields_google_map_insert_form() {
    $form['additional'] = array(
        '#type' => 'textfield',
        '#description' => 'Add some comment',
        '#name' => 'comment',
    );
    return $form;
}

/**
 * Overrides form output in meta box on post edit screen.
 */
function wpcf_fields_google_map_meta_box_form( $data ) {
    $form['name'] = array(
        '#name' => 'wpcf[' . $data['slug'] . ']', // Set this to override default output
        '#type' => 'textfield',
        '#title' => __( 'Add Google Map coordinates', 'wpcf' ),
        '#description' => __( 'Your input should look something like "41.934146,12.455821"',
                'wpcf' )
    );
    return $form;
}

/**
 * Adds editor popup callnack.
 * 
 * This form will be showed in editor popup
 */
function wpcf_fields_google_map_editor_callback( $field, $settings ) {
    ob_start();

    ?>
    <label><input type="text" name="width" value="<?php echo isset( $settings['width'] ) ? $settings['width'] : '425'; ?>" />&nbsp;<?php _e( 'Width',
            'wpcf' ); ?></label>
    <br />
    <label><input type="text" name="height" value="<?php echo isset( $settings['height'] ) ? $settings['height'] : '350'; ?>" />&nbsp;<?php _e( 'Height',
            'wpcf' ); ?></label>
    <?php
    $form = ob_get_contents();
    ob_get_clean();
    return array(
        'tabs' => array(
            'display' => array(
                'menu_title' => __( 'Display', 'wpcf' ),
                'title' => __( 'Display', 'wpcf' ),
                'content' => $form,
            )
        )
    );
}

/**
 * Processes editor popup submit
 */
function wpcf_fields_google_map_editor_submit( $data, $field ) {
    $add = '';

    // Add parameters
    if ( !empty( $data['width'] ) ) {
        $add .= ' width="' . strval( $data['width'] ) . '"';
    }
    if ( !empty( $data['height'] ) ) {
        $add .= ' height="' . strval( $data['height'] ) . '"';
    }

    // Generate and return shortcode
    return wpcf_fields_get_shortcode( $field, $add );
}

/**
 * Renders view
 * 
 * Useful $data:
 * $data['field_value'] - Value of custom field
 * 
 * @param array $data
 */
function wpcf_fields_google_map_view( $data ) {
    $data['width'] = !empty( $data['width'] ) ? $data['width'] : 425;
    $data['height'] = !empty( $data['height'] ) ? $data['height'] : 350;
    return '<iframe width="' . $data['width'] . '" height="' . $data['height']
            . '" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="http://maps.google.com/maps?q='
            . $data['field_value']
            . '&amp;num=1&amp;vpsrc=0&amp;hl=en&amp;ie=UTF8&amp;t=m&amp;z=14&amp;ll='
            . $data['field_value']
            . '&amp;output=embed"></iframe><br /><small><a href="http://maps.google.com/maps?q='
            . $data['field_value']
            . '&amp;num=1&amp;vpsrc=0&amp;hl=en&amp;ie=UTF8&amp;t=m&amp;z=14&amp;ll='
            . $data['field_value']
            . '&amp;source=embed" style="color:#0000FF;text-align:left">'
            . __( 'View Larger Map', 'wpcf' )
            . '</a></small><br />';
}

function WPToolset_Field_Google_Map_loader()
{

    if ( class_exists('WPToolset_Field_Google_Map' ) ) {
        return;
    }

    class WPToolset_Field_Google_Map extends FieldFactory
    {
        public function metaform()
        {
            $attributes =  $this->getAttr();

            $metaform = array();
            $metaform[] = array(
                '#type' => 'textfield',
                '#title' => $this->getTitle(),
                '#description' => $this->getDescription(),
                '#name' => $this->getName(),
                '#value' => $this->getValue(),
                '#validate' => $this->getValidationData(),
                '#repetitive' => $this->isRepetitive(),
                '#attributes' => $attributes,
            );
            return $metaform;
        }

    }
}
