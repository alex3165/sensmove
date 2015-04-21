<?php
/*
 * Edit post page functions
 *
 * Core file with stable and working functions.
 * Please add hooks if adjustment needed, do not add any more new code here.
 *
 * Consider this file half-locked since Types 1.1.4
 */

// Include conditional field code
require_once WPCF_EMBEDDED_ABSPATH . '/includes/conditional-display.php';

/**
 * Init functions for User profile edit pages.
*/
function wpcf_admin_userprofile_init($user_id){
	global $wpcf;
	if ( !is_object($user_id) ){
		$user_id = new stdClass();
		$user_id->ID = 0;
	}
	$user_role = isset($user_id->roles) ? array_shift($user_id->roles) : 'subscriber';
	$groups = wpcf_admin_usermeta_get_groups_fields();
	$wpcf_active = false;
	$profile_only_preview = '';


    foreach ( $groups as $group ) {
        if ( !empty( $group['fields'] ) ) {
            $wpcf_active = true;
			$for_users = wpcf_admin_get_groups_showfor_by_group($group['id']);
			$profile_only_preview = '';
			if ( count($for_users) != 0){
				if ( !in_array($user_role,$for_users)){
					continue;
				}
				else{
					//If Access plugin activated
					if (function_exists('wpcf_access_register_caps')){

						//If user can't view own profile fields
						if (!current_user_can('view_own_in_profile_' . $group['slug'])){
							continue;
						}
						//If user can modify current group in own profile
						if (!current_user_can('modify_own_' . $group['slug'])){
							$profile_only_preview = 1;
						}


					}
				}
			}
            else{
                 if (function_exists('wpcf_access_register_caps')){
                     if (!current_user_can('view_own_in_profile_' . $group['slug'])){
                       continue;
                     }
                     if (!current_user_can('modify_own_' . $group['slug'])){
                        $profile_only_preview = 1;
                     }
                  }
            }

            // Process fields
			if ( empty($profile_only_preview) ){
                if ( defined( 'WPTOOLSET_FORMS_VERSION' ) ) {
                    $errors = get_user_meta( $user_id->ID, '__wpcf-invalid-fields',
                            true );
                    // OLD
                    delete_post_meta( $user_id->ID, 'wpcf-invalid-fields' );
                    delete_post_meta( $user_id->ID, '__wpcf-invalid-fields' );
                    if ( empty( $group['fields'] ) ) continue;

                    $output = '<div class="wpcf-group-area wpcf-group-area_'
                    . $group['slug'] . '">' . "\n\n" . '<h3>'
                    . wpcf_translate( 'group ' . $group['id'] . ' name',
                            $group['name'] ) . '</h3>' . "\n\n";

                    if ( !empty( $group['description'] ) ) {
                        $output .= '<span>' . wpautop( wpcf_translate( 'group ' . $group['id'] . ' description', $group['description'] ) )
                                . '</span>' . "\n\n";
                    }

                    $output .= '<div class="wpcf-profile-field-line">' . "\n\n";

                    foreach ( $group['fields'] as $field ) {
                        $config = wptoolset_form_filter_types_field( $field,
                                $user_id->ID );

                        $config = array_map( 'fix_fields_config_output_for_display', $config);

                        $meta = get_user_meta( $user_id->ID, $field['meta_key'] );
                        if ( $errors ) {
                            $config['validate'] = true;
                        }
                        if ( isset( $config['validation']['required'] ) ) {
                            $config['title'] .= '&#42;';
                        }
                        $config['_title'] = $config['title'];
                        $output .= '
<div class="wpcf-profile-field-line">
	<div class="wpcf-profile-line-left">
        ' . $config['title'] . '
    </div>
	<div class="wpcf-profile-line-right">
    ';
                        $description = false;
                        if ( !empty($config['description'])) {
                            $description = sprintf(
                                '<span class="description">%s</span>',
                                $config['description']
                            );
                        }
                        $config['title'] = $config['description'] = '';
                        $form_name = $user_id->ID? 'your-profile':'createuser';
                        $output .= wptoolset_form_field( $form_name, $config, $meta );
                        if ( $description ) {
                            $output .= $description;
                        }
                        $output .= '
    </div>
</div>';
                    }

                    $output .= '</div></div>';
                    echo $output;
                } else {
                    $group['fields'] = wpcf_admin_usermeta_process_fields( $user_id,
                            $group['fields'], true );
                    wpcf_admin_render_fields( $group, $user_id );
                }
			}
			else{
				// Render profile fields (text only)
				wpcf_usermeta_preview_profile( $user_id, $group );
			}
        }

	}



    // Activate scripts
    if ( $wpcf_active ) {
		wp_enqueue_script( 'wpcf-fields-post',
                WPCF_EMBEDDED_RES_RELPATH . '/js/fields-post.js',
                array('jquery'), WPCF_VERSION );
        wp_enqueue_script( 'wpcf-form-validation',
                WPCF_EMBEDDED_RES_RELPATH . '/js/'
                . 'jquery-form-validation/jquery.validate.min.js',
                array('jquery'), WPCF_VERSION );
        wp_enqueue_script( 'wpcf-form-validation-additional',
                WPCF_EMBEDDED_RES_RELPATH . '/js/'
                . 'jquery-form-validation/additional-methods.min.js',
                array('jquery'), WPCF_VERSION );
        wp_enqueue_style( 'wpcf-fields-basic',
                WPCF_EMBEDDED_RES_RELPATH . '/css/basic.css', array(),
                WPCF_VERSION );
        wp_enqueue_style( 'wpcf-fields-post',
                WPCF_EMBEDDED_RES_RELPATH . '/css/fields-post.css',
                array('wpcf-fields-basic'), WPCF_VERSION );
		wp_enqueue_style( 'wpcf-usermeta',
                WPCF_EMBEDDED_RES_RELPATH . '/css/usermeta.css',
                array('wpcf-fields-basic'), WPCF_VERSION );
        wpcf_enqueue_scripts();
		wpcf_field_enqueue_scripts( 'date' );
		wpcf_field_enqueue_scripts( 'image' );
		wpcf_field_enqueue_scripts( 'file' );
		wpcf_field_enqueue_scripts( 'skype' );
		wpcf_field_enqueue_scripts( 'numeric' );
        add_action( 'admin_footer', 'wpcf_admin_profile_js_validation' );
    }
}

function fix_fields_config_output_for_display($match)
{
    if( gettype($match) === 'string' )
    {
        $match = stripcslashes( $match );
    }
    return $match;
}

/*
* Show user fields values in profile
* $user_id = array, $group = array
*/
function wpcf_usermeta_preview_profile( $user_id, $group, $echo = ''){
	if ( is_object($user_id) ){
		$user_id = $user_id->ID;
	}
	require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
	require_once WPCF_EMBEDDED_ABSPATH . '/frontend.php';
	global $wpcf;
	//print_r($group);exit;
	$fields = $group['fields'];
	$group_output = '<div class="wpcf-group-area wpcf-group-area-' . $group['slug'] . '">' . "\n\n";
	$group_output .=  '<h3 class="wpcf-group-header-'. $group['slug'] .'">'.wpcf_translate( 'group ' . $group['id'] . ' name', $group['name']).'</h3>'. "\n\n";


	foreach ( $fields as $field ) {
		$html = '';
		$params['post_type'] = 'wp-types-user-group';
		$params['option_name'] = 'wpcf-usermeta';
		$params['separator'] = ', ';
		if ( wpcf_admin_is_repetitive( $field ) ) {
        $wpcf->usermeta_repeater->set( $user_id, $field );
        $_meta = $wpcf->usermeta_repeater->_get_meta();
        if ( isset( $_meta['custom_order'] )){
			$meta = $_meta['custom_order'];
		}
		else{
			$meta = array();
		}
		$content = $code = '';
		// Sometimes if meta is empty - array(0 => '') is returned
        if ( (count( $meta ) == 1 ) ) {
            $meta_id = key( $meta );
            $_temp = array_shift( $meta );
            if (!is_array($_temp) && strval( $_temp ) == '' ) {

            } else {
                $params['field_value'] = $_temp;
                if ( !empty($params['field_value']) ){
				$html = types_render_field_single( $field, $params, $content,
                                $code, $meta_id );
				}
            }
        } else if ( !empty( $meta ) ) {
            $output = '';

            if ( isset( $params['index'] ) ) {
                $index = $params['index'];
            } else {
                $index = '';
            }

            // Allow wpv-for-each shortcode to set the index
            $index = apply_filters( 'wpv-for-each-index', $index );

            if ( $index === '' ) {
                $output = array();
                foreach ( $meta as $temp_key => $temp_value ) {
                    $params['field_value'] = $temp_value;
                    if ( !empty($params['field_value']) ){
						$temp_output = types_render_field_single( $field, $params,
								$content, $code, $temp_key );
					}
                    if ( !empty( $temp_output ) ) {
                        $output[] = $temp_output;
                    }
                }
                if ( !empty( $output ) && isset( $params['separator'] ) ) {
                    $output = implode( html_entity_decode( $params['separator'] ),
                            $output );
                } else if ( !empty( $output ) ) {
                    $output = implode( ' ', $output );
                }
            } else {
                // Make sure indexed right
                $_index = 0;
                foreach ( $meta as $temp_key => $temp_value ) {
                    if ( $_index == $index ) {
                        $params['field_value'] = $temp_value;
						if ( !empty($params['field_value']) ){
                        $output = types_render_field_single( $field, $params,
                                        $content, $code, $temp_key );
						}
                    }
                    $_index++;
                }
            }
            $html = $output;
        }
		} else {

			$params['field_value'] = get_user_meta( $user_id,
					wpcf_types_get_meta_prefix( $field ) . $field['slug'], true );

			if ( !empty($params['field_value']) && $field['type'] != 'date' ){
				$html = types_render_field_single( $field, $params );
			}
			if ( $field['type'] == 'date' && !empty($params['field_value']) ){
				$html = types_render_field_single( $field, $params );
				if ($field['data']['date_and_time'] == 'and_time'){
					$html .= ' ' . date("H", $params['field_value']) . ':' . date("i", $params['field_value']);
				}
			}
		}

		// API filter
		$wpcf->usermeta_field->set( $user_id, $field );
		$field_value = $wpcf->usermeta_field->html( $html, $params );
$group_output .= '<div class="wpcf-profile-field-line wpcf-profile-field-line-'. $field['slug'] .'">
		<div class="wpcf-profile-line-left">
		<b>'. $field['name'] .'</b>
		</div>
		<div class="wpcf-profile-line-right">
		'. $field_value .'
		</div>
</div>' . "\n\n";


	}
	$group_output .= "\n\n</div>";
	if ( empty($echo) ){
		echo $group_output;
	}else{
		return $group_output;
	}

}

/*
* Set fomr ID to JS validation
*/
function wpcf_admin_profile_js_validation(){
    wpcf_form_render_js_validation( '#your-profile' );
}


/*
* Save user profile custom fields
*/
function wpcf_admin_userprofilesave_init($user_id){

    if ( defined( 'WPTOOLSET_FORMS_VERSION' ) ) {

        global $wpcf;
        $errors = false;

        /**
         * check checkbox type fields to delete or save empty if needed
         */
        $groups = wpcf_admin_usermeta_get_groups_fields();
        foreach ( $groups as $group ) {
            if ( !array_key_exists( 'fields', $group ) || empty( $group['fields'] ) ) {
                continue;
            }
            foreach( $group['fields'] as $field ) {
                switch ( $field['type'] ) {
                case 'checkboxes':
                    if (
                        !array_key_exists('wpcf', $_POST)
                        || !array_key_exists( $field['slug'], $_POST['wpcf'] )
                    ) {
                        delete_user_meta($user_id, $field['meta_key']);
                    }
                    break;
                case 'checkbox':
                    if (
                        !array_key_exists('wpcf', $_POST)
                        || !array_key_exists( $field['slug'], $_POST['wpcf'] )
                    ) {
                        if ( 'yes' == $field['data']['save_empty'] ) {
                            $_POST['wpcf'][$field['slug']] = 0;
                        } else {
                            delete_user_meta($user_id, $field['meta_key']);
                        }
                    }
                    break;
                }
            }
        }

        // Save meta fields
        if ( !empty( $_POST['wpcf'] ) ) {
            foreach ( $_POST['wpcf'] as $field_slug => $field_value ) {
                // Get field by slug
                $field = wpcf_fields_get_field_by_slug( $field_slug, 'wpcf-usermeta' );
                if ( empty( $field ) ) {
                    continue;
                }
                // Skip copied fields
                if ( isset( $_POST['wpcf_repetitive_copy'][$field['slug']] ) ) {
                    continue;
                }
                $_field_value = !types_is_repetitive( $field ) ? array($field_value) : $field_value;
                // Set config
                $config = wptoolset_form_filter_types_field( $field, $user_id );
                foreach ( $_field_value as $_k => $_val ) {
                    // Check if valid
                    $valid = wptoolset_form_validate_field( 'your-profile', $config,
                            $_val );
                    if ( is_wp_error( $valid ) ) {
                        $errors = true;
                        $_errors = $valid->get_error_data();
                        $_msg = sprintf( __( 'Field "%s" not updated:', 'wpcf' ),
                                $field['name'] );
                        wpcf_admin_message_store( $_msg . ' ' . implode( ', ',
                                        $_errors ), 'error' );
                        if ( types_is_repetitive( $field ) ) {
                            unset( $field_value[$_k] );
                        } else {
                            break;
                        }
                    }
                }
                // Save field
                if ( types_is_repetitive( $field ) ) {
                    $wpcf->usermeta_repeater->set( $user_id, $field );
                    $wpcf->usermeta_repeater->save( $field_value );
                } else {
                    $wpcf->usermeta_field->set( $user_id, $field );
                    $wpcf->usermeta_field->usermeta_save( $field_value );
                }

                do_action( 'wpcf_user_field_saved', $user_id, $field );

                // TODO Move to checkboxes

                if ( $field['type'] == 'checkboxes' ) {
                    $field_data = wpcf_admin_fields_get_field( $field['id'], false, false, false, 'wpcf-usermeta' );
                    if ( !empty( $field_data['data']['options'] ) ) {
                        $update_data = array();
                        foreach ( $field_data['data']['options'] as $option_id => $option_data ) {
                            if ( !isset( $_POST['wpcf'][$field['id']][$option_id] ) ) {
                                if ( isset( $field_data['data']['save_empty'] ) && $field_data['data']['save_empty'] == 'yes' ) {
                                    $update_data[$option_id] = 0;
                                }
                            } else {
                                $update_data[$option_id] = $_POST['wpcf'][$field['id']][$option_id];
                            }
                        }
                        update_user_meta( $user_id, $field['meta_key'], $update_data );
                    }
                }
            }
        }
        if ( $errors ) {
            update_post_meta( $user_id, '__wpcf-invalid-fields', true );
        }
        do_action( 'wpcf_user_saved', $user_id );
        return;
    }

	global $wpcf;

	$all_fields = array();
	$_not_valid = array();
	$_error = false;
	$error = '';

	$groups = $groups = wpcf_admin_usermeta_get_groups_fields();
    if ( empty( $groups ) ) {
        return false;
    }

	foreach ( $groups as $group ) {
        // Process fields

        $fields = wpcf_admin_usermeta_process_fields( $user_id , $group['fields'], true,
                false, 'validation' );
        // Validate fields
        $form = wpcf_form_simple_validate( $fields );

        $all_fields = $all_fields + $fields;

        // Collect all not valid fields
        if ( $form->isError() ) {
            $_error = true; // Set error only to true
            $_not_valid = array_merge( $_not_valid,
                    (array) $form->get_not_valid() );
        }
    }

	// Set fields
    foreach ( $all_fields as $k => $v ) {
        // only Types field
        if ( empty( $v['wpcf-id'] ) ) {
            continue;
        }
        $_temp = new WPCF_Usermeta_Field();
        $_temp->set( $user_id, $v['wpcf-id'] );
        $all_fields[$k]['_field'] = $_temp;
    }
	foreach ( $_not_valid as $k => $v ) {
        // only Types field
        if ( empty( $v['wpcf-id'] ) ) {
            continue;
        }
        $_temp = new WPCF_Usermeta_Field();
        $_temp->set( $user_id, $v['wpcf-id'] );
        $_not_valid[$k]['_field'] = $_temp;
    }

    $not_valid = apply_filters( 'wpcf_post_form_not_valid', $_not_valid,
            $_error, $all_fields );


    // Notify user about error
    if ( $error ) {
        wpcf_admin_message_store(
                __( 'Please check your input data', 'wpcf' ), 'error' );
    }

    /*
     * Save invalid elements so user can be informed after redirect.
     */
    if ( !empty( $not_valid ) ) {
        update_user_meta( $user_id, 'wpcf-invalid-fields', $not_valid );
    }


	if ( !empty( $_POST['wpcf'] ) ) {
        foreach ( $_POST['wpcf'] as $field_slug => $field_value ) {

			$field = wpcf_fields_get_field_by_slug( $field_slug, 'wpcf-usermeta' );
			if ( empty( $field ) ) {
                continue;
            }


			$wpcf->usermeta_field->set( $user_id, $field );
			if ( isset( $_POST['wpcf_repetitive_copy'][$field['slug']] ) ) {
                continue;
            }

			if ( isset( $_POST['__wpcf_repetitive'][$wpcf->usermeta_field->slug] ) ) {
                 $wpcf->usermeta_repeater->set( $user_id, $field );
                $wpcf->usermeta_repeater->save();
            } else {
                 $wpcf->usermeta_field->usermeta_save();
            }

            do_action('wpcf_post_field_saved', '', $field);



		}//end foreach

	}//end if

	foreach ( $all_fields as $field ) {
		if ( !isset( $field['#type'] ) ) {
            continue;
        }
		if ( $field['#type'] == 'checkbox') {
            $field_data = wpcf_admin_fields_get_field( $field['wpcf-id'], false,
                    false, false, 'wpcf-usermeta' );
			if ( !isset( $_POST['wpcf'][$field['wpcf-slug']] ) ){
				if ( isset( $field_data['data']['save_empty'] )
                    && $field_data['data']['save_empty'] == 'yes' ) {
						update_user_meta($user_id, wpcf_types_get_meta_prefix( $field ) . $field['wpcf-slug'], 0);
				}
				else{
					delete_user_meta($user_id, wpcf_types_get_meta_prefix( $field ) . $field['wpcf-slug']);
				}
			}
		}
        if ( $field['#type'] == 'checkboxes' ) {
            $field_data = wpcf_admin_fields_get_field( $field['wpcf-id'], false,
                    false, false, 'wpcf-usermeta' );
            if ( !empty( $field_data['data']['options'] ) ) {
                $update_data = array();
                foreach ( $field_data['data']['options'] as $option_id => $option_data ) {
                    if ( !isset( $_POST['wpcf'][$field['wpcf-slug']][$option_id] ) ) {
                        if ( isset( $field_data['data']['save_empty'] ) && $field_data['data']['save_empty'] == 'yes' ) {
                            $update_data[$option_id] = 0;
                        }
                    } else {
                        $update_data[$option_id] = $_POST['wpcf'][$field['wpcf-slug']][$option_id];
                    }
                }
                update_user_meta( $user_id,
                        wpcf_types_get_meta_prefix( $field ) . $field['wpcf-slug'],
                        $update_data );
            }
        }
	}


}


/*
* Render user profile form fields
*/
function wpcf_admin_render_fields( $group, $user_id, $echo = '') {

	global $wpcf;
	$output = '<div class="wpcf-group-area wpcf-group-area_' . $group['slug'] . '">' . "\n\n";
	$output .= '<h3>'.wpcf_translate( 'group ' . $group['id'] . ' name', $group['name']).'</h3>' . "\n\n";
	if ( !empty( $group['fields'] ) ) {
        // Display description
        if ( !empty( $group['description'] ) ) {
            $output .= '<span>'
            . wpautop( wpcf_translate( 'group ' . $group['id'] . ' description',
                            $group['description'] ) ) . '</span>' . "\n\n";
        }

		$output .=  '<div class="wpcf-profile-field-line">' . "\n\n";
        foreach ( $group['fields'] as $field_slug => $field ) {
            if ( empty( $field ) || !is_array( $field ) ) {
                continue;
            }
			$field = $wpcf->usermeta_field->_parse_cf_form_element( $field );

            if ( !isset( $field['#id'] ) ) {
                $field['#id'] = wpcf_unique_id( serialize( $field ) );
            }
			if ( isset( $field['wpcf-type'] ) ) { // May be ignored
                $field = apply_filters( 'wpcf_fields_' . $field['wpcf-type'] . '_meta_box_form_value_display', $field );
            }
            // Render form elements
            if ( wpcf_compare_wp_version() && $field['#type'] == 'wysiwyg' ) {
				$field['#editor_settings']['media_buttons'] = '';
				if ( !empty($echo) ){
					$field['#editor_settings']['wpautop'] = true;
				}
                // Especially for WYSIWYG
                $output .=  "\n".'<div class="wpcf-profile-field-line">' . "\n\n";
				$output .= '<div class="wpcf-wysiwyg">' . "\n\n";
                $output .=  '<div id="wpcf-textarea-textarea-wrapper" class="form-item form-item-textarea wpcf-form-item wpcf-form-item-textarea">' . "\n\n";
                $output .=  isset( $field['#before'] ) ? $field['#before'] : '';
                $output .=  '<label class="wpcf-form-label wpcf-form-textarea-label">' . $field['#title'] . '</label>' . "\n\n";
                $output .=  '<div class="description wpcf-form-description wpcf-form-description-textarea description-textarea">' . "\n\n" .
					 wpautop( $field['#description'] ) . '</div>' . "\n\n";
                ob_start();
				wp_editor( $field['#value'], $field['#id'],
                        $field['#editor_settings'] );
				$output .= ob_get_clean() . "\n\n";
                $field['slug'] = str_replace( WPCF_META_PREFIX . 'wysiwyg-', '',
                        $field_slug );
                $field['type'] = 'wysiwyg';
                $output .=  '</div>' . "\n\n";
                $output .=  isset( $field['#after'] ) ? $field['#after'] : '';
                $output .=  '</div>' . "\n\n";
				$output .= '</div>' . "\n\n";
            }
			else {
                if ( $field['#type'] == 'wysiwyg' ) {
                    $field['#type'] = 'textarea';
                }
				$field['#pattern'] = "\n".'<div class="wpcf-profile-field-line">
	<div class="wpcf-profile-line-left">
		<LABEL><DESCRIPTION>
	</div>
	<div class="wpcf-profile-line-right"><BEFORE><ERROR><PREFIX><ELEMENT><SUFFIX><AFTER></div>
</div>' . "\n\n";

				if ( isset( $field['#name'] ) && ( strpos($field['#name'], '[hour]') !== false || strpos($field['#name'], '[minute]') !== false ) ){
					if ( isset($field['#attributes']) && $field['#attributes']['class'] == 'wpcf-repetitive'){
						$field['#pattern'] = (strpos($field['#name'], '[hour]') !== false)?__( 'Hour', 'wpcf' ):__( 'Minute', 'wpcf' );
						$field['#pattern'] .= '<LABEL><DESCRIPTION><ERROR><PREFIX><ELEMENT><SUFFIX><AFTER>' . "\n\n";
					}
					else{
						if (strpos($field['#name'],'[hour]')!== false){
							$field['#pattern'] = "\n".'<div class="wpcf-profile-field-line">
	<div class="wpcf-profile-line-left">&nbsp;&nbsp;&nbsp;&nbsp;'.__( 'Time', 'wpcf' ).'</div>
	<div class="wpcf-profile-line-right">
	<LABEL><DESCRIPTION><ERROR><PREFIX><ELEMENT><SUFFIX><AFTER>' . "\n";
						}
						else{
							$field['#pattern'] = "\n".'
	<LABEL><DESCRIPTION><ERROR><PREFIX><ELEMENT><SUFFIX><AFTER></div>
</div>' . "\n\n";
						}

					}

				}

				if ( !empty($echo) ){
					$field['#validate'] = '';
				}
                $output .=  wpcf_form_simple( array($field['#id'] => $field) );

            }


        }
		$output .=  '</div>';
    }

    /*
     * TODO Move to Conditional code
     *
     * This is already checked. Use hook to add wrapper DIVS and apply CSS.
     */
    if ( !empty( $group['_conditional_display'] ) ) {
        $output .=  '</div>';
    }
	$output .= "\n\n" . '</div>';
	if ( !empty($echo) ){
		return $output;
	}
	else{
		echo $output;
	}
}

/**
 * Gets all groups and fields for post.
 *
 * Core function. Works and stable. Do not move or change.
 * If required, add hooks only.
 *
 * @param type $post_ID
 * @return type
 */
function wpcf_admin_usermeta_get_groups_fields() {
	$post = array();
    // Filter groups
    $groups = array();

    $groups_all =  wpcf_admin_fields_get_groups('wp-types-user-group');

    foreach ( $groups_all as $temp_key => $temp_group ) {
        if ( empty( $temp_group['is_active'] ) ) {
            unset( $groups_all[$temp_key] );
            continue;
        }
        $passed = 1;
        if ( !$passed ) {
            unset( $groups_all[$temp_key] );
        } else {
            $groups_all[$temp_key]['fields'] = wpcf_admin_fields_get_fields_by_group( $temp_group['id'],
                    'slug', true, false, true, 'wp-types-user-group', 'wpcf-usermeta');
        }
    }
    $groups = $groups_all;
    return $groups;
}


/**
 * Creates form elements.
 *
 * Core function. Works and stable. Do not move or change.
 * If required, add hooks only.
 *
 * @param type $post
 * @param type $fields
 * @return type
 */
function wpcf_admin_usermeta_process_fields( $user_id, $fields = array(),
        $use_cache = true, $add_to_editor = true, $context = 'group' ) {

    global $wpcf;

    $wpcf->usermeta_field->use_cache = $use_cache;
    $wpcf->usermeta_field->add_to_editor = $add_to_editor;
    $wpcf->usermeta_repeater->use_cache = $use_cache;
    $wpcf->usermeta_repeater->add_to_editor = $add_to_editor;


	if( is_object( $user_id ) ){
		$user_id = $user_id->ID;
	}

	// Get cached
    static $cache = array();
    $cache_key = !empty( $user_id ) ? $user_id . md5( serialize( $fields ) ) : false;
    if ( $use_cache && $cache_key && isset( $cache[$cache_key] ) ) {
        return $cache[$cache_key];
    }

    $fields_processed = array();
	$invalid_fields = array();



    foreach ( $fields as $field ) {

		if ( !empty( $user_id ) ) {
			$invalid_fields = update_user_meta( $user_id, 'wpcf-invalid-fields', true );
			delete_user_meta( $user_id, 'wpcf-invalid-fields' );
			$wpcf->usermeta_field->invalid_fields = $invalid_fields;
   		}
        // Repetitive fields
        if ( wpcf_admin_is_repetitive( $field ) && $context != 'post_relationship' ) {
            	$wpcf->usermeta_repeater->set( $user_id, $field );
                $fields_processed = $fields_processed + $wpcf->usermeta_repeater->get_fields_form(1);

        } else {


            $wpcf->usermeta_field->set( $user_id, $field );


            /*
             * From Types 1.2 use complete form setup
             */
            $fields_processed = $fields_processed + $wpcf->usermeta_field->_get_meta_form();
        }
    }

    // Cache results
    if ( $cache_key ) {
        $cache[$cache_key] = $fields_processed;
    }

    return $fields_processed;
}
