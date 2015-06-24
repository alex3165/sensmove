<?php
/**
 * Types-field: Checkbox
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
 * 'state' => 'checked' or 'unchecked' (display the content of the shortcode depending on the state)
 *
 * Example usage:
 * With a short code use [types field="my-checkbox"]
 * In a theme use types_render_field("my-checkbox", $parameters)
 *
 * Link:
 * <a href="http://wp-types.com/documentation/functions/checkbox/">Types checkbox custom field</a>
 * 
 */

/**
 * Form data for group form.
 * 
 * @return type 
 */
function wpcf_fields_checkbox_insert_form( $form_data ) {
    $meta_type = isset($_GET['page']) && $_GET['page'] != 'wpcf-edit' ? 'usermeta' : 'postmeta';
    $form = array();
    $form['name'] = array(
        '#type' => 'textfield',
        '#title' => __( 'Name of custom field', 'wpcf' ),
        '#description' => __( 'Under this name field will be stored in DB (sanitized)',
                'wpcf' ),
        '#name' => 'name',
        '#attributes' => array(
            'class' => 'wpcf-forms-set-legend',
        ),
        '#validate' => array('required' => array('value' => true)),
    );
    $form['description'] = array(
        '#type' => 'textarea',
        '#title' => __( 'Description', 'wpcf' ),
        '#description' => __( 'Text that describes function to user', 'wpcf' ),
        '#name' => 'description',
        '#attributes' => array('rows' => 5, 'cols' => 1),
    );
    $form['value'] = array(
        '#type' => 'textfield',
        '#title' => __( 'Value to store', 'wpcf' ),
        '#name' => 'set_value',
        '#value' => 1,
        '#attributes' => array(
            'data-wpcf-type' => 'checkbox',
            'data-required-message-0' => __("This value can't be zero", 'wpcf'),
            'data-required-message' => __("Please enter a value", 'wpcf')
        )
    );
    $cb_migrate_save = !empty( $form_data['slug'] ) ? "wpcfCbSaveEmptyMigrate(jQuery(this), '{$form_data['slug']}', '', '" . wp_create_nonce( 'cb_save_empty_migrate' ) . "', 'save_check', '{$meta_type}');" : '';
    $cb_migrate_do_not_save = !empty( $form_data['slug'] ) ? "wpcfCbSaveEmptyMigrate(jQuery(this), '{$form_data['slug']}', '', '" . wp_create_nonce( 'cb_save_empty_migrate' ) . "', 'do_not_save_check', '{$meta_type}');" : '';
    $update_response = !empty( $form_data['slug'] ) ? "<div id='wpcf-cb-save-empty-migrate-response-{$form_data['slug']}' class='wpcf-cb-save-empty-migrate-response'></div>" : '<div class="wpcf-cb-save-empty-migrate-response"></div>';
    $form['save_empty'] = array(
        '#type' => 'radios',
        '#name' => 'save_empty',
        '#default_value' => !empty( $form_data['data']['save_empty'] ) ? $form_data['data']['save_empty'] : 'no',
        '#options' => array(
            'yes' => array(
                '#title' => __( 'save 0 to the database', 'wpcf' ),
                '#value' => 'yes',
                '#attributes' => array('class' => 'wpcf-cb-save-empty-migrate', 'onclick' => $cb_migrate_save),
            ),
            'no' => array(
                '#title' => __( "don't save anything to the database", 'wpcf' ),
                '#value' => 'no',
                '#attributes' => array('class' => 'wpcf-cb-save-empty-migrate', 'onclick' => $cb_migrate_do_not_save),
            ),
        ),
        '#description' => '<strong>' . __( 'When unchecked:', 'wpcf' ) . '</strong>',
        '#after' => $update_response,
    );
    if ( isset($_GET['page']) && $_GET['page'] == 'wpcf-edit' ) {
        $form['checked'] = array(
            '#type' => 'checkbox',
            '#title' => __( 'Set checked by default (on new post)?', 'wpcf' ),
            '#name' => 'checked',
            '#default_value' => !empty( $form_data['data']['checked'] ) ? 1 : 0,
        );
    }
    $form['display'] = array(
        '#type' => 'radios',
        '#default_value' => 'db',
        '#name' => 'display',
        '#options' => array(
            'display_from_db' => array(
                '#title' => __( 'Display the value of this field from the database',
                        'wpcf' ),
                '#name' => 'display',
                '#value' => 'db',
                '#inline' => true,
                '#after' => '<br />'
            ),
            'display_values' => array(
                '#title' => __( 'Show one of these two values:', 'wpcf' ),
                '#name' => 'display',
                '#value' => 'value',
            ),
        ),
        '#inline' => true,
    );
    $form['display-value-1'] = array(
        '#type' => 'textfield',
        '#title' => __( 'Not selected:', 'wpcf' ),
        '#name' => 'display_value_not_selected',
        '#value' => '',
        '#inline' => true,
        '#attributes' => array(
            'placeholder' => __('Enter not selected value', 'wpcf'),
        ),
    );
    $form['display-value-2'] = array(
        '#type' => 'textfield',
        '#title' => __( 'Selected:', 'wpcf' ),
        '#name' => 'display_value_selected',
        '#value' => '',
        '#attributes' => array(
            'placeholder' => __('Enter selected value', 'wpcf'),
        ),
    );
    $form['help'] = array(
        '#type' => 'markup',
        '#markup' => '<p style="text-align:right"><a href="http://wp-types.com/documentation/functions/checkbox/" target="_blank">' . __( 'Checkbox help',
                'wpcf' ) . '</a></p>',
    );
    return $form;
}
