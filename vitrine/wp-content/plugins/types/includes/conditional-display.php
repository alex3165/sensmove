<?php
/*
 * Conditional display code.
 */
require_once WPCF_EMBEDDED_ABSPATH . '/includes/conditional-display.php';

add_filter( 'wpcf_form_field', 'wpcf_cd_form_field_filter', 10, 2 );
add_filter( 'wpcf_field_pre_save', 'wpcf_cd_field_pre_save_filter' );
add_filter( 'wpcf_group_pre_save', 'wpcf_cd_group_pre_save_filter' );
add_filter( 'wpcf_fields_form_additional_filters', 'wpcf_cd_fields_form_additional_filters', 10, 2 );
add_action( 'wpcf_save_group', 'wpcf_cd_save_group_action' );

global $wp_version;
$wpcf_button_style = '';
$wpcf_button_style30 = '';

if ( version_compare( $wp_version, '3.5', '<' ) ) {
    $wpcf_button_style = 'style="line-height: 35px;"';
    $wpcf_button_style30 = 'style="line-height: 30px;"';
}

/**
 * Filters group field form.
 *
 * @param type $form
 * @param type $data
 * @return type
 */
function wpcf_cd_form_field_filter( $form, $data ) {
    if ( defined( 'DOING_AJAX' ) && isset( $_SERVER['HTTP_REFERER'] ) ) {
        parse_str( $_SERVER['HTTP_REFERER'], $vars );
    } else if ( isset( $_GET['group_id'] ) ) {
        $vars = array();
        $vars['group_id'] = sanitize_text_field( $_GET['group_id'] );
    }
    if ( !isset( $vars['group_id'] ) ) {
        return $form + array(
            'cd_not_available' => array(
                '#type' => 'markup',
                '#markup' => '<p>' . __( 'You will be able to set conditional field display once this group is saved.',
                        'wpcf' ) . '</p>',
            ),
        );
    }
    $form = $form + wpcf_cd_admin_form_filter( $data );
    return $form;
}

/**
 * Group pre-save filter.
 *
 * @param array $data
 * @return array
 */
function wpcf_cd_group_pre_save_filter( $data ) {
    return wpcf_cd_field_pre_save_filter( $data );
}

/**
 * Field pre-save filter.
 *
 * @param array $data
 * @return array
 */
function wpcf_cd_field_pre_save_filter( $data ) {
    if ( empty( $data['conditional_display'] ) ) {
        $data['conditional_display'] = array();
    } else if ( !empty( $data['conditional_display']['conditions'] ) ) {
        foreach ( $data['conditional_display']['conditions'] as $k => $condition ) {
            if ( !array_key_exists( 'field', $condition ) ) {
                continue;
            }
            $field = wpcf_admin_fields_get_field( $condition['field'] );
            if ( !empty( $field ) ) {
                // Date conversions
                if ( $field['type'] == 'date'
                        && isset( $condition['date'] )
                        && isset( $condition['month'] )
                        && isset( $condition['year'] )
                ) {
                    $time = adodb_mktime( 0, 0, 0, $condition['month'],
                            $condition['date'], $condition['year'] );
					if ( wpcf_fields_date_timestamp_is_valid( $time ) ) {
						$condition['value'] = $time;
					}
					/*
                    $date = date( wpcf_get_date_format(), $time );
                    if ( $date !== false ) {
                        $condition['value'] = $date;
                    }
					*/
                }
                if ( isset( $condition['date'] ) && isset( $condition['month'] )
                        && isset( $condition['year'] )
                ) {
                    unset( $condition['date'], $condition['month'],
                            $condition['year'] );
                }
                $data['conditional_display']['conditions'][$k] = $condition;
            }
        }
    }
    return $data;
}

/**
 * Conditional display form.
 *
 * @param type $data
 * @param type $group
 * @return type
 */
function wpcf_cd_admin_form_filter( $data, $group = false ) {
    global $wpcf_button_style30;

    if ( $group ) {
        $name = 'wpcf[group][conditional_display]';
    } else {
        $name = 'wpcf[fields][' . $data['id'] . '][conditional_display]';
    }
    $form = array();

    // Count
    if ( !empty( $data['data']['conditional_display']['conditions'] ) ) {
        $conditions = $data['data']['conditional_display']['conditions'];
        $count = count( $conditions );
        $_count_txt = $count;
    } else {
        $_count_txt = '';
        $count = 1;
    }

    /**
     * state of conditional display custom use
     */
    $use_custom_logic= 0;
    if ( 1
        && array_key_exists( 'data', $data )
        && is_array( $data['data'] )
        && array_key_exists( 'conditional_display', $data['data'] )
        && is_array( $data['data']['conditional_display'] )
        && array_key_exists( 'custom_use', $data['data']['conditional_display'] )
        && !empty( $data['data']['conditional_display']['custom_use'] )
    ) {
        $use_custom_logic = 1;
    }

    if ( !$group ) {
        $form['cd'] = array(
            '#type' => 'fieldset',
            '#title' => __( 'Conditional display', 'wpcf' ),
            '#collapsed' => true,
            '#id' => $data['id'] . '_conditional_display',
            '#attributes' => array('class' => 'wpcf-cd-fieldset'),
        );
    } else {
        $count_text = sprintf(
            '<span class="count" data-wpcf-custom-logic="%s">(%s)</span>',
            esc_attr__('custom logic', 'wpcf'),
            $use_custom_logic? __('custom logic', 'wpcf'):$_count_txt
        );
        $form['cd']['wrap'] = array(
            '#type' => 'markup',
            '#markup' => '<strong>' . sprintf( __( 'Data-dependent display filters %s',
                            'wpcf' ), $count_text ) . '</strong><br />'
            . __( "Specify additional filters that control this group's display, based on values of custom fields.",
                    'wpcf' )
            . '<br /><a class="button-secondary" id="conditional-logic-button-open" onclick="jQuery(this).css(\'display\',\'none\').next().slideToggle();" ' . $wpcf_button_style30 . '  href="javascript:void(0);">'
            . __( 'Edit', 'wpcf' ) . '</a><div id="wpcf-cd-group" class="wpcf-cd-fieldset" style="display:none;">',
        );
    }

    $form['cd']['custom_use'] = array(
        '#type' => 'hidden',
        '#name' => $name . '[custom_use]',
        '#default_value' => isset( $data['data']['conditional_display']['custom_use'] ),
        '#attributes' => array(
            'class' => 'conditional-display-custom-use',
        ),
        '#value' => $use_custom_logic,
    );

    // Stop for Usermeta Group edit screen
    if ( isset( $_GET['page'] ) && $_GET['page'] == 'wpcf-edit-usermeta' ) {
        $form['cd']['message'] = array(
            '#type' => 'markup',
            '#markup' => '<p>' . __( 'Conditional display is not supported yet for Usermeta fields.',
                    'wpcf' ) . '</p>',
        );
        return $form;
    }

    $add = $group ? 'true' : 'false';

    // Set button ID
    $_add_id = 'wpcf_conditional_add_condition_';
    $_add_id .= $group ? 'group' : 'field_' . $data['id'];

    // Set link param
    $_temp_group_id = isset( $_GET['group_id'] ) ? '&group_id=' . sanitize_text_field( $_GET['group_id'] ) : '';
    $_url = admin_url( 'admin-ajax.php?action=wpcf_ajax&wpcf_action=add_condition'
            . $_temp_group_id . '&_wpnonce='
            . wp_create_nonce( 'add_condition' ) );

    $form['cd']['add'] = array(
        '#type' => 'markup',
        '#markup' => '<br /><a id="' . $_add_id
        . '" class="button-secondary simple-logic"'
        . ' onclick="wpcfCdAddCondition(jQuery(this),' . $add . '); return false;"'
        . ' href="' . $_url . '">' . __( 'Add condition', 'wpcf' ) . '</a>'
        . '<div class="wpcf-cd-entries simple-logic">',
    );

    /*
     * Sanitize conditions
     */
    if ( !empty( $data['data']['conditional_display'] ) ) {
        // There may be some unserilized leftovers from previoius versions
        $_conditions = maybe_unserialize( $data['data']['conditional_display'] );
        if ( !empty( $_conditions['conditions'] )
                && is_array( $_conditions['conditions'] ) ) {
            foreach ( $_conditions['conditions'] as $key => $condition ) {
                $form['cd'] += wpcf_cd_admin_form_single_filter( $data,
                        $condition, $key, $group );
            }
        }
    }

    $form['cd']['add_close'] = array(
        '#type' => 'markup',
        '#markup' => '</div>',
    );
    $form['cd']['relation'] = array(
        '#type' => 'radios',
        '#name' => $name . '[relation]',
        '#options' => array(
            'AND' => array(
                '#title' => 'AND',
                '#attributes' => array('onclick' => 'wpcfCdCreateSummary(\'' . md5( $data['id'] ) . '_cd_summary\')'),
                '#inline' => true,
                '#value' => 'AND',
                '#after' => '<br />',
            ),
            'OR' => array(
                '#title' => 'OR',
                '#attributes' => array('onclick' => 'wpcfCdCreateSummary(\'' . md5( $data['id'] ) . '_cd_summary\')'),
                '#inline' => true,
                '#value' => 'OR'
            ),
        ),
        '#default_value' => isset( $data['data']['conditional_display']['relation'] ) ? $data['data']['conditional_display']['relation'] : 'AND',
        '#inline' => true,
        '#before' => '<div class="wpcf-cd-relation simple-logic" style="display:none;">',
        '#after' => '</div>',
    );
    /**
     * logic form
     */
    $form['cd']['toggle_open'] = array(
        '#type' => 'markup',
        '#markup' => '<div class="toggle-cd">',
    );

    $form['cd']['display_logic_button'] = array(
        '#name' => $name.'[display-logic-button]',
        '#type' => 'button',
        '#value' => '',
        '#patern' => '<ELEMENT>',
        '#attributes' => array(
            'data-wpcf-custom-logic-simple' => esc_attr__( 'Go back to simple logic', 'wpcf' ),
            'data-wpcf-custom-logic-customize' => esc_attr__( 'Customize the display logic', 'wpcf' ),
            'data-wpcf-custom-logic' => '',
            'data-wpcf-custom-logic-change' => !$use_custom_logic,
            'data-wpcf-custom-summary' => md5( $data['id'] ) . '_cd_summary',
            'class' => 'wpcf-cd-display-logic-button',
        ),
    );

    $form['cd']['toggle_open_area'] = array(
        '#type' => 'markup',
        '#markup' => '<div class="area-toggle-cd" style="margin-top:10px;display:none;">',
    );
    $form['cd']['custom'] = array(
        '#type' => 'textarea',
        '#name' => $name . '[custom]',
        '#title' => __( 'Customize conditions', 'wpcf' ),
        '#id' => md5( $data['id'] ) . '_cd_summary',
        '#inline' => true,
        '#value' => isset( $data['data']['conditional_display']['custom'] ) ? $data['data']['conditional_display']['custom'] : '',
    );
    $form['cd']['date_notice'] = array(
        '#type' => 'markup',
        '#markup' => '<div style="display:none; margin-top:15px;" class="wpcf-cd-notice-date">'
        . sprintf( __( '%sDates can be entered using the date filters &raquo;%s',
            'wpcf' ),
        '<a href="http://wp-types.com/documentation/user-guides/date-filters/" target="_blank">',
        '</a>' ) . '</div>',
    );
    $form['cd']['toggle_close'] = array(
        '#type' => 'markup',
        '#markup' => '</div>',
    );
    $form['cd']['toggle_close_area'] = array(
        '#type' => 'markup',
        '#markup' => '</div>',
    );
    $form['cd']['count'] = array(
        '#type' => 'hidden',
        '#name' => '_wpcf_cd_count_' . $data['id'],
        '#value' => $count,
        '#attributes' => array(
            'class' => 'wpcf-cd-count',
        ),
    );
    if ( $group ) {
        $form['cd']['wrap_close_button'] = array(
            '#name' => 'button',
            '#type' => 'button',
            '#value' => __('OK', 'wpcf'),
            '#id' => 'conditional-logic-button-ok',
            '#attributes' => array(
                'class' => 'button-primary',
                'href' => '#',
            ),
            '#before' => '<br />',

        );
        $form['cd']['wrap_close'] = array(
            '#type' => 'markup',
            '#markup' => '</div>',
        );
    }
    return $group ? $form['cd'] : $form;
}

/**
 * Single condition form elements.
 *
 * @param type $data
 * @param type $condition
 * @param type $key
 * @return string
 */
function wpcf_cd_admin_form_single_filter( $data, $condition, $key = null,
        $group = false, $force_multi = false ) {

    global $wpcf;

    if ( $group ) {
        $name = 'wpcf[group][conditional_display]';
    } else {
        $name = 'wpcf[fields][' . $data['id'] . '][conditional_display]';
    }
    $group_id = isset( $_GET['group_id'] ) ? intval( $_GET['group_id'] ) : false;

    /*
     *
     *
     * TODO Review this allowing fields from same group as conditional (self loop)
     * I do not remember allowing fields from same group as conditional (self loop)
     * on Group Fields edit screen.
     */
//    if ( $group_id && !$group ) {// Allow group to use other fields
//        $fields = wpcf_admin_fields_get_fields_by_group( $group_id );
//    } else {
    $fields = wpcf_admin_fields_get_fields(true, false, true);
    ksort( $fields, SORT_STRING );
//    }

    if ( $group ) {
        $_distinct = wpcf_admin_fields_get_fields_by_group( $group_id );

        foreach ( $_distinct as $_field_id => $_field ) {
            if ( isset( $fields[$_field_id] ) ) {
                unset( $fields[$_field_id] );
            }
        }
    }
    $options = array();

    $ignore_field_type_array = array(
        'audio',
        'checkboxes',
        'embed',
        'file',
        'image',
        'video',
        'wysiwyg',
    );

    $flag_repetitive = false;
    foreach ( $fields as $field_id => $field ) {
        if ( !$group && $data['id'] == $field_id ) {
            continue;
        }
        // WE DO NOT ALLOW repetitive fields to be compared.
        if ( wpcf_admin_is_repetitive( $field ) ) {
            $flag_repetitive = true;
            continue;
        }
        /**
         * Skip some files
         */
        if ( in_array( $field['type'], $ignore_field_type_array ) ) {
            continue;
        }
        /**
         * build options
         */
        $options[$field_id] = array(
            '#value' => $field_id,
            '#title' => stripslashes( $field['name'] ),
            '#attributes' => array('class' => 'wpcf-conditional-select-' . $field['type']),
        );
    }
    /*
     * Special case
     * https://icanlocalize.basecamphq.com/projects/7393061-wp-views/todo_items/153565054/comments
     *
     * When field is new and only one diff field in list - that
     * means one field is saved but other not yet.
     */
    $is_new = isset( $data['id'] ) && isset( $fields[$data['id']] ) ? false : true;
    $special_stop = false;
    if ( $is_new ) {
        if ( count( $options ) == 1 ) {
            $special_stop = true;
        }
    }
    /*
     *
     * This means all fields are repetitive and no one left to compare with.
     * WE DO NOT ALLOW repetitive fields to be compared.
     */
    if ( !$group && empty( $options ) && $flag_repetitive ) {
        return array(
            'cd' => array(
                '#type' => 'markup',
                '#markup' => '<p class="wpcf-error">' . __( 'Conditional display is only working based on non-repeating fields. All fields in this group are repeating, so you cannot set their display based on other fields.',
                        'wpcf' ) . '</p>' . wpcf_conditional_disable_add_js( $data['id'] ),
            )
        );
    } else {
        if ( !$group && ( empty( $options ) || $special_stop ) ) {
            return array(
                'cd' => array(
                    '#type' => 'markup',
                    '#markup' => '<p>' . __( 'You will be able to set conditional field display when you save more fields.',
                            'wpcf' ) . '</p>',
                )
            );
        }
    }
    $id = !is_null( $key ) ? $key : strval( 'condition_' . wpcf_unique_id( serialize( $data ) . serialize( $condition ) . $key . $group ) );
    $form = array();
    $before = '<div class="wpcf-cd-entry"><br />';
    $form['cd']['field_' . $id] = array(
        '#type' => 'select',
        '#name' => $name . '[conditions][' . $id . '][field]',
        '#options' => $options,
        '#inline' => true,
        '#before' => $before,
        '#default_value' => isset( $condition['field'] ) ? $condition['field'] : null,
        '#attributes' => array('class' => 'wpcf-cd-field'),
    );
    $form['cd']['operation_' . $id] = array(
        '#type' => 'select',
        '#name' => $name . '[conditions][' . $id . '][operation]',
        '#options' => array_flip( wpcf_cd_admin_operations() ),
        '#inline' => true,
        '#default_value' => isset( $condition['operation'] ) ? $condition['operation'] : null,
        '#attributes' => array('class' => 'wpcf-cd-operation'),
    );
    $form['cd']['value_' . $id] = array(
        '#type' => 'textfield',
        '#name' => $name . '[conditions][' . $id . '][value]',
        '#inline' => true,
        '#value' => isset( $condition['value'] ) ? $condition['value'] : '',
        '#attributes' => array('class' => 'wpcf-cd-value'),
    );
    /*
     *
     * Adjust for date
     */
    if ( !empty( $condition['value'] ) ) {
        WPCF_Loader::loadInclude( 'fields/date/functions.php' );
        $timestamp = wpcf_fields_date_convert_datepicker_to_timestamp( $condition['value'] );
        if ( $timestamp !== false ) {
            $date_value = adodb_date( 'd', $timestamp ) . ',' . adodb_date( 'm', $timestamp ) . ',' . adodb_date( 'Y',
                            $timestamp );
            $date_function = 'date';
        } else if ( wpcf_fields_date_timestamp_is_valid( $condition['value'] ) ) {
			$date_value = adodb_date( 'd', $condition['value'] ) . ',' . adodb_date( 'm', $condition['value'] ) . ',' . adodb_date( 'Y',
                            $condition['value'] );
            $date_function = 'date';
		}
    }
    if ( empty( $date_value ) ) {
        $date_value = '';
        $date_function = false;
    }
    $form['cd']['value_date_' . $id] = array(
        '#type' => 'markup',
        '#markup' => '<br />' . wpcf_conditional_add_date_controls( $date_function,
                $date_value, $name . '[conditions][' . $id . ']' ),
        '#attributes' => array('class' => 'wpcf-cd-value-date'),
    );
    $form['cd']['remove_' . $id] = array(
        '#type' => 'button',
        '#name' => 'remove',
        '#value' => __( 'Remove condition', 'wpcf' ),
        '#attributes' => array('onclick' => 'wpcfCdRemoveCondition(jQuery(this));', 'class' => 'wpcf-add-condition'),
        '#after' => '</div><br />',
    );
    return $form['cd'];
        }

/**
 * Group coditional display filter.
 *
 * @param type $filters
 * @param type $update
 * @return type
 */
function wpcf_cd_fields_form_additional_filters( $filters, $update ) {
    $data = array();
    $data['id'] = !empty( $update ) ? $update['name'] : wpcf_unique_id( serialize( $filters ) );
    if ( $update ) {
        $data['data']['conditional_display'] = maybe_unserialize( get_post_meta( $update['id'],
                        '_wpcf_conditional_display', true ) );
    } else {
        $data['data']['conditional_display'] = array();
    }
    $filters = $filters + wpcf_cd_admin_form_filter( $data, true );
    return $filters;
}

/**
 * Save group action hook.
 *
 * @param type $group
 */
function wpcf_cd_save_group_action( $group ) {
    if ( !empty( $group['conditional_display'] ) ) {
        update_post_meta( $group['id'], '_wpcf_conditional_display',
                $group['conditional_display'] );
    } else {
        update_post_meta( $group['id'], '_wpcf_conditional_display', array() );
    }
}

/**
 * Triggers disabling 'Add Condition' button.
 * @param type $id
 * @return string
 */
function wpcf_conditional_disable_add_js( $id ) {
    $js = '';
    $js .= '<script type="text/javascript">
        jQuery(document).ready(function(){wpcfDisableAddCondition(\''
            . strtolower( $id ) . '\'); });
    </script>
';
    return $js;
}

/**
 * Date select form for Group edit screen.
 *
 * @global type $wp_locale
 * @param type $function
 * @param type $value
 * @param type $name
 * @return string
 *
 */
function wpcf_conditional_add_date_controls( $function, $value, $name ) {

    global $wp_locale;

    if ( $function == 'date' ) {
        $date_parts = explode( ',', $value );
        $time_adj = adodb_mktime( 0, 0, 0, $date_parts[1], $date_parts[0],
                $date_parts[2] );
    } else {
        $time_adj = current_time( 'timestamp' );
    }
    $jj = adodb_gmdate( 'd', $time_adj );
    $mm = adodb_gmdate( 'm', $time_adj );
    $aa = adodb_gmdate( 'Y', $time_adj );

    $output = '<div class="wpcf-custom-field-date">' . "\n";

    $month = "<select name=\"" . $name . '[month]' . "\" >\n";
    for ( $i = 1; $i < 13; $i = $i + 1 ) {
        $monthnum = zeroise( $i, 2 );
        $month .= "\t\t\t" . '<option value="' . $monthnum . '"';
        if ( $i == $mm )
            $month .= ' selected="selected"';
        $month .= '>' . $monthnum . '-'
                . $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) )
                . "</option>\n";
    }
    $month .= '</select>';

    $day = '<input name="' . $name . '[date]" type="text" value="' . $jj . '" size="2" maxlength="2" autocomplete="off" />';
    $year = '<input name="' . $name . '[year]" type="text" value="' . $aa . '" size="4" maxlength="4" autocomplete="off" />';

    $output .= sprintf( __( '%1$s%2$s, %3$s' ), $month, $day, $year );

    $output .= '<div class="wpcf_custom_field_invalid_date wpcf-form-error"><p>' . __( 'Please enter a valid date here',
                    'wpcf' ) . '</p></div>' . "\n";

    $output .= "</div>\n";

    return $output;
}
