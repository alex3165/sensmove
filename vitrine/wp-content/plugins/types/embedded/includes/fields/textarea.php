<?php
/**
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/embedded/includes/fields/textarea.php $
 * $LastChangedDate: 2014-11-18 06:47:25 +0000 (Tue, 18 Nov 2014) $
 * $LastChangedRevision: 1027712 $
 * $LastChangedBy: iworks $
 *
 */
/**
 * Register data (called automatically).
 *
 * @return type
 */
function wpcf_fields_textarea()
{
    return array(
        'id' => 'wpcf-textarea',
        'title' => __('Multiple lines', 'wpcf'),
        'description' => __('Textarea', 'wpcf'),
        'validate' => array('required'),
    );
}

/**
 * Meta box form.
 *
 * @param type $field
 * @return string
 */
function wpcf_fields_textarea_meta_box_form($field)
{
    $form = array();
    $form['name'] = array(
        '#type' => 'textarea',
        '#name' => 'wpcf[' . $field['slug'] . ']',
    );
    return $form;
}

/**
 * Formats display data.
 */
function wpcf_fields_textarea_view($params)
{

    $value = $params['field_value'];

    // see if it's already wrapped in <p> ... </p>
    $wrapped_in_p = false;
    if (!empty($value) && strpos($value, '<p>') === 0 && strrpos($value, "</p>\n") == strlen($value) - 5) {
        $wrapped_in_p = true;
    }

    // use wpautop for converting line feeds to <br />, etc
    $value = wpautop($value);

    if (!$wrapped_in_p) {
        // If it wasn't wrapped then remove the wrapping wpautop has added.
        if (!empty($value) && strpos($value, '<p>') === 0 && strrpos($value, "</p>\n") == strlen($value) - 5) {
            // unwrapp the <p> ..... </p> if is no <p> inside, to avoid remove <p> if is nessary
            if ( strpos($value, '<p>', 1 ) === false ) {
                $value = substr($value, 3, -5);
            }
        }
    }

    return $value;
}
