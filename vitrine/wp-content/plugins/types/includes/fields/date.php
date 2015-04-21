<?php
/**
 * Types-field: Date
 *
 * Description: Displays a datepicker to the user with optional
 * 'hour' and 'minute' selection.
 *
 * Rendering: Date is stored in seconds (time()) but displayed as date
 * formatted. Additional data like 'hour' and 'minute' is stored in separate
 * meta value.
 * 
 * Parameters:
 * 'raw' => 'true'|'false' (display raw data stored in DB, default false)
 * 'output' => 'html' (wrap data in HTML, optional)
 * 'show_name' => 'true' (show field name before value e.g. My date: $value)
 * 'style' => 'text'|'calendar' (display text or WP calendar)
 * 'format' => defaults to WP date format settings, can be any valid date format
 *     e.g. "j/n/Y" or "j/n/Y H:i"
 *
 * Example usage:
 * With a short code use [types field="my-date"]
 * Display only hour and minute [types field="my-date" format="H:i"]
 * In a theme use types_render_field("my-date", $parameters)
 * 
 */

/**
 * Form data for group form.
 * 
 * @return type 
 */
function wpcf_fields_date_insert_form( $form_data ) {
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
    $form['date_and_time'] = array(
        '#type' => 'radios',
        '#name' => 'date_and_time',
        '#default_value' => isset( $form_data['data']['date_and_time'] ) ? strval( $form_data['data']['date_and_time'] ) : 'date',
        '#options' => array(
            'date' => array(
                '#title' => __( 'Input only the date', 'wpcf' ),
                '#value' => 'date',
            ),
            'and_time' => array(
                '#title' => __( 'Input date and time', 'wpcf' ),
                '#value' => 'and_time',
            ),
        ),
    );

    return $form;
}