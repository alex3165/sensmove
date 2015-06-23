<?php
/**
 * Types-field: Checkboxes
 *
 * Description: Displays a checkbox to the user. Checkboxes can be
 * used to get binary, yes/no responses from a user.
 *
 * Rendering: The "Value to stored" for the checkbox the front end
 * if the checkbox is checked or 'Selected'|'Not selected' HTML
 * will be rendered. If 'Selected'|'Not selected' HTML is not specified then
 * nothing is rendered.
 * 
 * Parameters:
 * 'raw' => 'true'|'false' (display raw data stored in DB, default false)
 * 'output' => 'html' (wrap data in HTML, optional)
 * 'show_name' => 'true' (show field name before value e.g. My checkbox: $value)
 *
 * Example usage:
 * With a short code use [types field="my-checkboxes"]
 * In a theme use types_render_field("my-checkboxes", $parameters)
 * 
 */

/**
 * Form data for group form.
 * 
 * @return type 
 */
function wpcf_fields_checkboxes_insert_form( $form_data, $parent_name = '' ) {
    $meta_type = isset($_GET['page']) && $_GET['page'] != 'wpcf-edit' ? 'usermeta' : 'postmeta';
    $id = 'wpcf-fields-checkboxes-' . wpcf_unique_id( serialize( $form_data ) . $parent_name );
    $form = array();
    $form['name'] = array(
        '#type' => 'textfield',
        '#title' => __( 'Name of custom field', 'wpcf' ),
        '#description' => __( 'Under this name field will be stored in DB (sanitized)',
                'wpcf' ),
        '#name' => 'name',
        '#attributes' => array('class' => 'wpcf-forms-set-legend'),
        '#validate' => array('required' => array('value' => true)),
    );
    $form['description'] = array(
        '#type' => 'textarea',
        '#title' => __( 'Description', 'wpcf' ),
        '#description' => __( 'Text that describes function to user', 'wpcf' ),
        '#name' => 'description',
        '#attributes' => array('rows' => 5, 'cols' => 1),
    );
    $cb_migrate_save = !empty( $form_data['slug'] ) ? 'wpcfCbSaveEmptyMigrate(jQuery(this), \'' . $form_data['slug'] . '\', \'\', \'' . wp_create_nonce( 'cb_save_empty_migrate' ) . '\', \'save_check\', \'' . $meta_type . '\');' : '';
    $cb_migrate_do_not_save = !empty( $form_data['slug'] ) ? 'wpcfCbSaveEmptyMigrate(jQuery(this), \'' . $form_data['slug'] . '\', \'\', \'' . wp_create_nonce( 'cb_save_empty_migrate' ) . '\', \'do_not_save_check\', \'' . $meta_type . '\');' : '';
    $update_response = !empty( $form_data['slug'] ) ? '<div id="wpcf-cb-save-empty-migrate-response-'
            . $form_data['slug'] . '" class="wpcf-cb-save-empty-migrate-response"></div>' : '<div class="wpcf-cb-save-empty-migrate-response"></div>';
    $form['save_empty'] = array(
        '#type' => 'radios',
        '#name' => 'save_empty',
        '#default_value' => !empty( $form_data['data']['save_empty'] ) ? $form_data['data']['save_empty'] : 'no',
        '#options' => array(
            'yes' => array(
                '#title' => __( 'When unchecked, save 0 to the database', 'wpcf' ),
                '#value' => 'yes',
                '#attributes' => array('class' => 'wpcf-cb-save-empty-migrate', 'onclick' => $cb_migrate_save),
            ),
            'no' => array(
                '#title' => __( "When unchecked, don't save anything to the database",
                        'wpcf' ),
                '#value' => 'no',
                '#attributes' => array('class' => 'wpcf-cb-save-empty-migrate', 'onclick' => $cb_migrate_do_not_save),
            ),
        ),
        '#after' => $update_response,
    );
    $form['options-markup-open'] = array(
        '#type' => 'markup',
        '#markup' => '<strong>' . __( 'Checkboxes', 'wpcf' )
        . '</strong><br /><br /><div id="' . $id . '-sortable"'
        . ' class="wpcf-fields-checkboxes-sortable wpcf-compare-unique-value-wrapper">',
    );
    $existing_options = array();
    $options = !empty( $form_data['options'] ) ? $form_data['options'] : array();
    $options = !empty( $form_data['data']['options'] ) ? $form_data['data']['options'] : $options;
    if ( !empty( $options ) ) {
        foreach ( $options as $option_key => $option ) {
            if ( $option_key == 'default' ) {
                continue;
            }
            $option['key'] = $option_key;
            $option['default'] = isset( $options['default'] ) ? $options['default'] : null;
            $form_option = wpcf_fields_checkboxes_get_option( $parent_name,
                    $option, $form_data );
            $existing_options[array_shift( $form_option )] = $option;
            $form = $form + $form_option;
        }
    } else {
        $form_option = wpcf_fields_checkboxes_get_option( $parent_name, array(), $form_data );
        $existing_options[array_shift( $form_option )] = array();
        $form = $form + $form_option;
    }
    $form['options-markup-close'] = array(
        '#type' => 'markup',
        '#markup' => '</div>',
    );

    if ( !empty( $options ) ) {
        $count = count( $options );
    } else {
        $count = 1;
    }

    $form['options-add-option'] = array(
        '#type' => 'markup',
        '#markup' => '<br /><a href="'
        . admin_url( 'admin-ajax.php?action=wpcf_ajax&amp;wpcf_action=add_checkboxes_option&amp;_wpnonce='
                . wp_create_nonce( 'add_checkboxes_option' ) . '&amp;wpcf_ajax_update_add='
                . $id . '-sortable&amp;parent_name=' . urlencode( $parent_name )
                . '&amp;page='. sanitize_text_field( $_GET['page'] )
                . '&amp;count=' . $count)
        . '" onclick="wpcfFieldsFormCountOptions(jQuery(this));"'
        . ' class="button-secondary wpcf-ajax-link">'
        . __( 'Add option', 'wpcf' ) . '</a>',
    );
    $form['options-close'] = array(
        '#type' => 'markup',
        '#markup' => '<br /><br />',
    );
    return $form;
}

/**
 * Returns form data for radio.
 * 
 * @param type $parent_name Used for AJAX adding options
 * @param type $form_data
 * @return type 
 */
function wpcf_fields_checkboxes_get_option( $parent_name = '',
        $form_data = array(), $field = array() ) {
    $id = isset( $form_data['key'] ) ? $form_data['key'] : 'wpcf-fields-checkboxes-option-' . wpcf_unique_id( serialize( $form_data ) . $parent_name );
    $form = array();
    $count = isset( $_GET['count'] ) ? intval( $_GET['count'] ) : 1;
    $title = isset( $_GET['count'] ) ? __( 'Checkbox title', 'wpcf' ) . ' ' . intval( $_GET['count'] ) : __( 'Checkbox title',
                    'wpcf' ) . ' 1';
    $title = isset( $form_data['title'] ) ? $form_data['title'] : $title;
    $form[$id . '-id'] = $id;
    $form[$id . '-drag'] = array(
        '#type' => 'markup',
        '#markup' => '<div class="js-types-sortable wpcf-fields-checkboxes-draggable"><div class="wpcf-checkboxes-drag"><img src="'
        . WPCF_RES_RELPATH
        . '/images/move.png" class="js-types-sort-button wpcf-fields-form-checkboxes-move-field" alt="'
        . __( 'Move this option', 'wpcf' ) . '" /><img src="'
        . WPCF_RES_RELPATH . '/images/delete.png"'
        . ' class="wpcf-fields-checkboxes-delete-option wpcf-pointer"'
        . ' onclick="if (confirm(\'' . __( 'Are you sure?', 'wpcf' )
        . '\')) { jQuery(this).parent().fadeOut().next().fadeOut(function(){jQuery(this).remove(); '
        . '}); }"'
        . 'alt="' . __( 'Delete this checkbox', 'wpcf' ) . '" /></div>',
    );
    $form[$id] = array(
        '#type' => 'fieldset',
        '#title' => $title,
        '#collapsed' => isset( $form_data['key'] ) ? true : false,
        '#collapsible' => true,
    );
    $form[$id]['title'] = array(
        '#type' => 'textfield',
        '#title' => __( 'Title', 'wpcf' ),
        '#id' => $id . '-title',
        '#name' => $parent_name . '[options][' . $id . '][title]',
        '#value' => $title,
        '#inline' => true,
        '#attributes' => array(
            'class' => 'wpcf-form-groups-check-update-title-display-value',
        ),
        '#before' => '<br />',
    );
    $form[$id]['value'] = array(
        '#type' => 'textfield',
        '#title' => __( 'Value to store', 'wpcf' ),
        '#name' => $parent_name . '[options][' . $id . '][set_value]',
        '#value' => isset( $form_data['set_value'] ) ? $form_data['set_value'] : 1,
        '#attributes' => array(
            'data-wpcf-type' => 'checkbox',
            'data-required-message-0' => __("This value can't be zero", 'wpcf'),
            'data-required-message' => __("Please enter a value", 'wpcf')
        )

    );
    if ( isset($_GET['page']) && $_GET['page'] == 'wpcf-edit' ) {
        $form[$id]['checked'] = array(
            '#id' => 'checkboxes-' . wpcf_unique_id( serialize( $form_data ) . $parent_name ),
            '#type' => 'checkbox',
            '#title' => __( 'Set checked by default (on new post)?', 'wpcf' ),
            '#name' => $parent_name . '[options][' . $id . '][checked]',
            '#default_value' => !empty( $form_data['checked'] ) ? 1 : 0,
        );
    }
    $form[$id]['display'] = array(
        '#type' => 'radios',
        '#default_value' => !empty( $form_data['display'] ) ? $form_data['display'] : 'db',
        '#name' => $parent_name . '[options][' . $id . '][display]',
        '#options' => array(
            'display_from_db' => array(
                '#title' => __( 'Display the value of this field from the database',
                        'wpcf' ),
                '#name' => $parent_name . '[options][' . $id . '][display]',
                '#value' => 'db',
                '#inline' => true,
                '#after' => '<br />'
            ),
            'display_values' => array(
                '#title' => __( 'Show one of these two values:', 'wpcf' ),
                '#name' => $parent_name . '[options][' . $id . '][display]',
                '#value' => 'value',
            ),
        ),
        '#inline' => true,
    );
    $form[$id]['display-value'] = array(
        '#type' => 'textfield',
        '#title' => __( 'Not selected:', 'wpcf' ),
        '#name' => $parent_name . '[options][' . $id . '][display_value_not_selected]',
        '#value' => isset( $form_data['display_value_not_selected'] ) ? $form_data['display_value_not_selected'] : '',
        '#inline' => true,
        '#attributes' => array(
            'placeholder' => __('Enter not selected value', 'wpcf'),
        ),
    );
    $form[$id]['display-value-2'] = array(
        '#type' => 'textfield',
        '#title' => __( 'Selected:', 'wpcf' ),
        '#name' => $parent_name . '[options][' . $id . '][display_value_selected]',
        '#value' => isset( $form_data['display_value_selected'] ) ? $form_data['display_value_selected'] : '',
        '#attributes' => array(
            'placeholder' => __('Enter selected value', 'wpcf'),
        ),
    );
    $form[$id . 'drag-close'] = array(
        '#type' => 'markup',
        '#markup' => '</div>',
    );
    return $form;
}

/**
 * Returns form data for radio.
 * 
 * @param type $parent_name Used for AJAX adding options
 * @param type $form_data
 * @return type 
 */
function wpcf_fields_checkboxes_get_option_alt_text( $id, $parent_name = '',
        $form_data = array() ) {
    $form = array();
    $title = isset( $_GET['count'] ) ? __( 'Checkbox title', 'wpcf' ) . ' ' . $_GET['count'] : __( 'Checkbox title',
                    'wpcf' ) . ' 1';
    $title = isset( $form_data['title'] ) ? $form_data['title'] : $title;
    $value = isset( $_GET['count'] ) ? intval( $_GET['count'] ) : 1;
    $value = isset( $form_data['value'] ) ? $form_data['value'] : $value;
    $value = isset( $form_data['display_value'] ) ? $form_data['display_value'] : $value;
    $form = array(
        '#type' => 'textfield',
        '#id' => $id . '-title-display-value',
        '#name' => $parent_name . '[options][' . $id . '][display_value]',
        '#title' => $title,
        '#value' => $value,
        '#inline' => true,
        '#before' => '<div id="' . $id . '-title-display-value-wrapper">',
        '#after' => '</div>',
    );
    return $form;
}
