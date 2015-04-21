<?php
/**
 * Register data (called automatically).
 * 
 * @return type 
 */
function wpcf_fields_select() {
    return array(
        'id' => 'wpcf-select',
        'title' => __('Select', 'wpcf'),
        'description' => __('Select', 'wpcf'),
        'validate' => array('required'),
    );
}

/**
 * Form data for post edit page.
 * 
 * @param type $field 
 */
function wpcf_fields_select_meta_box_form($field) {
    $options = array();
    $default_value = null;

    if (!empty($field['data']['options'])) {
        foreach ($field['data']['options'] as $option_key => $option) {
            // Skip default value record
            if ($option_key == 'default') {
                continue;
            }
            // Set default value
            if (!empty($field['data']['options']['default'])
                    && $option_key == $field['data']['options']['default']) {
                $default_value = $option['value'];
            }
            $options[$option['title']] = array(
                '#value' => $option['value'],
                '#title' => wpcf_translate('field ' . $field['id'] . ' option '
                        . $option_key . ' title', $option['title']),
            );
        }
    }

    if (!empty($field['value'])
            || ($field['value'] === 0 || $field['value'] === '0')) {
        $default_value = $field['value'];
    }

    $element = array(
        '#type' => 'select',
        '#default_value' => $default_value,
        '#options' => $options,
    );

    return $element;
}

/**
 * View function.
 * 
 * @param type $params 
 */
function wpcf_fields_select_view($params) {
    if ( isset($params['usermeta']) && !empty($params['usermeta']) ){
		$field = wpcf_fields_get_field_by_slug( $params['field']['slug'] , 'wpcf-usermeta');
	}
	else{
		$field = wpcf_fields_get_field_by_slug( $params['field']['slug'] );
	}
    $output = '';
    if (!empty($field['data']['options'])) {
        $field_value = $params['field_value'];
        foreach ($field['data']['options'] as $option_key => $option) {
            if (isset($option['value'])
                    && $option['value'] == $params['field_value']) {
                $field_value = wpcf_translate('field ' . $params['field']['id'] . ' option '
                        . $option_key . ' title', $option['title']);
            }
        }
        $output = $field_value;
    }
    return $output;
}