<?php

/**
 * All AJAX calls go here.
 *
 * @global object $wpdb
 *
 */
function wpcf_ajax() {
    if (!current_user_can('manage_options')
            || (!isset($_REQUEST['_wpnonce'])
            || !wp_verify_nonce($_REQUEST['_wpnonce'], $_REQUEST['wpcf_action']))) {
        die();
    }
    switch ($_REQUEST['wpcf_action']) {
        /* User meta actions*/
		case 'user_fields_control_bulk':
            require_once WPCF_INC_ABSPATH . '/fields.php';
            require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
            require_once WPCF_INC_ABSPATH . '/fields-control.php';
			require_once WPCF_INC_ABSPATH . '/usermeta-control.php';
            wpcf_admin_user_fields_control_bulk_ajax();
            break;

        case 'usermeta_delete':
        case 'delete_usermeta':
            require_once WPCF_INC_ABSPATH . '/fields.php';
            if (isset($_GET['field_id'])) {
				$field_id = sanitize_text_field( $_GET['field_id'] );
                wpcf_admin_fields_delete_field($field_id,'wp-types-user-group','wpcf-usermeta');
            }
            if (isset($_GET['field'])) {
				$field = sanitize_text_field( $_GET['field'] );
                wpcf_admin_fields_delete_field($field,'wp-types-user-group','wpcf-usermeta');
            }
            echo json_encode(array(
                'output' => ''
            ));
            break;

		case 'remove_from_history2':
            require_once WPCF_INC_ABSPATH . '/fields.php';
            $fields = wpcf_admin_fields_get_fields( true, true,false,'wpcf-usermeta');
            if (isset($_GET['field_id']) && isset($fields[$_GET['field_id']])) {
                $fields[$_GET['field_id']]['data']['removed_from_history'] = 1;
                wpcf_admin_fields_save_fields($fields, true, 'wpcf-usermeta');
            }
            echo json_encode(array(
                'output' => ''
            ));
            break;

		case 'deactivate_user_group':
            require_once WPCF_INC_ABSPATH . '/fields.php';
			require_once WPCF_INC_ABSPATH . '/usermeta.php';
	        $success = wpcf_admin_fields_deactivate_group(intval($_GET['group_id']), 'wp-types-user-group');
            if ($success) {
                echo json_encode(array(
                    'output' => __('Group deactivated', 'wpcf'),
                    'execute' => 'jQuery("#wpcf-list-activate-'
                    . intval($_GET['group_id']) . '").replaceWith(\''
                    . wpcf_admin_usermeta_get_ajax_activation_link(intval($_GET['group_id']))
                    . '\');jQuery(".wpcf-table-column-active-'
                    . intval($_GET['group_id']) . '").html("' . __('No', 'wpcf') . '");',
                    'wpcf_nonce_ajax_callback' => wp_create_nonce('execute'),
                    'status' => 'inactive',
                    'status_label' => __('No', 'wpcf'),
                ));
            } else {
                echo json_encode(array(
                    'output' => __('Error occured', 'wpcf')
                ));
            }
            break;

		case 'activate_user_group':
            require_once WPCF_INC_ABSPATH . '/fields.php';
			require_once WPCF_INC_ABSPATH . '/usermeta.php';
            $success = wpcf_admin_fields_activate_group(intval($_GET['group_id']), 'wp-types-user-group');
            if ($success) {
                echo json_encode(array(
                    'output' => __('Group activated', 'wpcf'),
                    'execute' => 'jQuery("#wpcf-list-activate-'
                    . intval($_GET['group_id']) . '").replaceWith(\''
                    . wpcf_admin_usermeta_get_ajax_deactivation_link(intval($_GET['group_id']))
                    . '\');jQuery(".wpcf-table-column-active-'
                    . intval($_GET['group_id']) . '").html("' . __('Yes', 'wpcf') . '");',
                    'wpcf_nonce_ajax_callback' => wp_create_nonce('execute'),
                    'status' => 'active',
                    'status_label' => __('Yes', 'wpcf'),
                ));
            } else {
                echo json_encode(array(
                    'output' => __('Error occured', 'wpcf')
                ));
            }
            break;

		case 'delete_usermeta_group':
            require_once WPCF_INC_ABSPATH . '/fields.php';
			require_once WPCF_INC_ABSPATH . '/usermeta.php';
            wpcf_admin_fields_delete_group(intval($_GET['group_id']), 'wp-types-user-group');
            echo json_encode(array(
                'output' => '',
                'execute' => 'jQuery("#wpcf-list-activate-'
                . intval($_GET['group_id'])
                . '").parents("tr").css("background-color", "#FF0000").fadeOut();',
                'wpcf_nonce_ajax_callback' => wp_create_nonce('execute'),
            ));
            break;

		case 'usermeta_insert_existing':
			require_once WPCF_INC_ABSPATH . '/fields.php';
            require_once WPCF_INC_ABSPATH . '/fields-form.php';
			require_once WPCF_INC_ABSPATH . '/usermeta-form.php';
            wpcf_usermeta_insert_existing_ajax();
            wpcf_form_render_js_validation();
            break;
		/* End Usertmeta actions*/




		case 'fields_insert':
            require_once WPCF_INC_ABSPATH . '/fields.php';
            require_once WPCF_INC_ABSPATH . '/fields-form.php';
            wpcf_fields_insert_ajax();
            wpcf_form_render_js_validation();
            break;

        case 'fields_insert_existing':
            require_once WPCF_INC_ABSPATH . '/fields.php';
            require_once WPCF_INC_ABSPATH . '/fields-form.php';
            wpcf_fields_insert_existing_ajax();
            wpcf_form_render_js_validation();
            break;

        case 'remove_field_from_group':
            require_once WPCF_INC_ABSPATH . '/fields.php';
            if (isset($_GET['group_id']) && isset($_GET['field_id'])) {
                wpcf_admin_fields_remove_field_from_group(intval($_GET['group_id']),
                        sanitize_text_field($_GET['field_id']));
            }
            break;

        case 'deactivate_group':
            require_once WPCF_INC_ABSPATH . '/fields.php';
            $success = wpcf_admin_fields_deactivate_group(intval($_GET['group_id']));
            if ($success) {
                echo json_encode(array(
                    'output' => __('Group deactivated', 'wpcf'),
                    'execute' => 'jQuery("#wpcf-list-activate-'
                    . intval($_GET['group_id']) . '").replaceWith(\''
                    . wpcf_admin_fields_get_ajax_activation_link(intval($_GET['group_id']))
                    . '\');jQuery(".wpcf-table-column-active-'
                    . intval($_GET['group_id']) . '").html("' . __('No', 'wpcf') . '");',
                    'wpcf_nonce_ajax_callback' => wp_create_nonce('execute'),
                    'status' => 'inactive',
                    'status_label' => __('No', 'wpcf'),
                ));
            } else {
                echo json_encode(array(
                    'output' => __('Error occured', 'wpcf')
                ));
            }
            break;

        case 'activate_group':
            require_once WPCF_INC_ABSPATH . '/fields.php';
            $success = wpcf_admin_fields_activate_group(intval($_GET['group_id']));
            if ($success) {
                echo json_encode(array(
                    'output' => __('Group activated', 'wpcf'),
                    'execute' => 'jQuery("#wpcf-list-activate-'
                    . intval($_GET['group_id']) . '").replaceWith(\''
                    . wpcf_admin_fields_get_ajax_deactivation_link(intval($_GET['group_id']))
                    . '\');jQuery(".wpcf-table-column-active-'
                    . intval($_GET['group_id']) . '").html("' . __('Yes', 'wpcf') . '");',
                    'wpcf_nonce_ajax_callback' => wp_create_nonce('execute'),
                    'status' => 'active',
                    'status_label' => __('Yes', 'wpcf'),
                ));
            } else {
                echo json_encode(array(
                    'output' => __('Error occured', 'wpcf')
                ));
            }
            break;

        case 'delete_group':
            require_once WPCF_INC_ABSPATH . '/fields.php';
            wpcf_admin_fields_delete_group(intval($_GET['group_id']));
            echo json_encode(array(
                'output' => '',
                'execute' => 'jQuery("#wpcf-list-activate-'
                . intval($_GET['group_id'])
                . '").parents("tr").css("background-color", "#FF0000").fadeOut();',
                'wpcf_nonce_ajax_callback' => wp_create_nonce('execute'),
            ));
            break;

        case 'deactivate_post_type':
            if (!isset($_GET['wpcf-post-type'])) {
                die();
            }
            require_once WPCF_INC_ABSPATH . '/custom-types.php';
            $custom_types = get_option('wpcf-custom-types', array());
            if (isset($custom_types[$_GET['wpcf-post-type']])) {
                $custom_types[$_GET['wpcf-post-type']]['disabled'] = 1;
                $custom_types[$_GET['wpcf-post-type']][TOOLSET_EDIT_LAST] = time();
                update_option('wpcf-custom-types', $custom_types);
                echo json_encode(array(
                    'output' => __('Post type deactivated', 'wpcf'),
                    'execute' => 'jQuery("#wpcf-list-activate-'
                    . sanitize_text_field($_GET['wpcf-post-type']) . '").replaceWith(\''
                    . wpcf_admin_custom_types_get_ajax_activation_link(esc_attr(
                                    $_GET['wpcf-post-type']))
                    . '\');jQuery(".wpcf-table-column-active-'
                    . sanitize_text_field($_GET['wpcf-post-type']) . '").html("' . __('No', 'wpcf') . '");',
                    'wpcf_nonce_ajax_callback' => wp_create_nonce('execute'),
                ));
            } else {
                echo json_encode(array(
                    'output' => __('Error occured', 'wpcf')
                ));
            }
            break;

        case 'activate_post_type':
            if (!isset($_GET['wpcf-post-type'])) {
                die();
            }
            require_once WPCF_INC_ABSPATH . '/custom-types.php';
            $custom_types = get_option('wpcf-custom-types', array());
            if (isset($custom_types[$_GET['wpcf-post-type']])) {
                $custom_types[$_GET['wpcf-post-type']]['disabled'] = 0;
                $custom_types[$_GET['wpcf-post-type']][TOOLSET_EDIT_LAST] = time();
                update_option('wpcf-custom-types', $custom_types);
                echo json_encode(array(
                    'output' => __('Post type activated', 'wpcf'),
                    'execute' => 'jQuery("#wpcf-list-activate-'
                    . sanitize_text_field($_GET['wpcf-post-type']) . '").replaceWith(\''
                    . wpcf_admin_custom_types_get_ajax_deactivation_link(sanitize_text_field($_GET['wpcf-post-type']))
                    . '\');jQuery(".wpcf-table-column-active-'
                    . sanitize_text_field($_GET['wpcf-post-type']) . '").html("' . __('Yes', 'wpcf') . '");',
                    'wpcf_nonce_ajax_callback' => wp_create_nonce('execute'),
                ));
            } else {
                echo json_encode(array(
                    'output' => __('Error occured', 'wpcf')
                ));
            }
            break;

        case 'delete_post_type':
            if (!isset($_GET['wpcf-post-type'])) {
                die();
            }
            $custom_types = get_option('wpcf-custom-types', array());
            $custom_type = strval($_GET['wpcf-post-type']);

            /**
             * Delete relation between custom posts types
             *
             * Filter allow to delete all custom fields used to make
             * a relation between posts.
             *
             * @since 1.6.4
             *
             * @param bool   $delete True or false flag to delete relationships.
             * @param string $var Currently deleted custom post type.
             */
            if ( apply_filters('wpcf_delete_relation_meta', false, $custom_type) ) {
                global $wpdb;
                $wpdb->delete(
                    $wpdb->postmeta,
                    array( 'meta_key' => sprintf( '_wpcf_belongs_%s_id', $custom_type ) ),
                    array( '%s' )
                );
            }

            unset($custom_types[$custom_type]);
            /**
             * remove post relation
             */
            foreach ( array_keys($custom_types) as $post_type ) {
                if ( array_key_exists( 'post_relationship', $custom_types[$post_type] ) ) {
                    /**
                     * remove "has" relation
                     */
                    if (
                        array_key_exists( 'has', $custom_types[$post_type]['post_relationship'] )
                        && array_key_exists( $custom_type, $custom_types[$post_type]['post_relationship']['has'] )
                    ) {
                        unset($custom_types[$post_type]['post_relationship']['has'][$custom_type]);
                        $custom_types[$post_type][TOOLSET_EDIT_LAST] = time();
                    }
                    /**
                     * remove "belongs" relation
                     */
                    if (
                        array_key_exists( 'belongs', $custom_types[$post_type]['post_relationship'] )
                        && array_key_exists( $custom_type, $custom_types[$post_type]['post_relationship']['belongs'] )
                    ) {
                        unset($custom_types[$post_type]['post_relationship']['belongs'][$custom_type]);
                        $custom_types[$post_type][TOOLSET_EDIT_LAST] = time();
                    }
                }
            }
            update_option('wpcf-custom-types', $custom_types);
            wpcf_admin_deactivate_content('post_type', $custom_type);
            echo json_encode(array(
                'output' => '',
                'execute' => 'jQuery("#wpcf-list-activate-'
                . sanitize_text_field($_GET['wpcf-post-type'])
                . '").parents("tr").css("background-color", "#FF0000").fadeOut();',
                'wpcf_nonce_ajax_callback' => wp_create_nonce('execute'),
            ));
            break;

        case 'deactivate_taxonomy':
            if (!isset($_GET['wpcf-tax'])) {
                die();
            }
            require_once WPCF_INC_ABSPATH . '/custom-taxonomies.php';
            $custom_taxonomies = get_option('wpcf-custom-taxonomies', array());
            if (isset($custom_taxonomies[$_GET['wpcf-tax']])) {
                $custom_taxonomies[$_GET['wpcf-tax']]['disabled'] = 1;
                $custom_taxonomies[$_GET['wpcf-tax']][TOOLSET_EDIT_LAST] = time();
                update_option('wpcf-custom-taxonomies', $custom_taxonomies);
                echo json_encode(array(
                    'output' => __('Taxonomy deactivated', 'wpcf'),
                    'execute' => 'jQuery("#wpcf-list-activate-'
                    . sanitize_text_field($_GET['wpcf-tax']) . '").replaceWith(\''
                    . wpcf_admin_custom_taxonomies_get_ajax_activation_link(sanitize_text_field($_GET['wpcf-tax']))
                    . '\');jQuery(".wpcf-table-column-active-'
                    . sanitize_text_field($_GET['wpcf-tax']) . '").html("' . __('No', 'wpcf') . '");',
                    'wpcf_nonce_ajax_callback' => wp_create_nonce('execute'),
                ));
            } else {
                echo json_encode(array(
                    'output' => __('Error occured', 'wpcf')
                ));
            }
            break;

        case 'activate_taxonomy':
            if (!isset($_GET['wpcf-tax'])) {
                die();
            }
            require_once WPCF_INC_ABSPATH . '/custom-taxonomies.php';
            $custom_taxonomies = get_option('wpcf-custom-taxonomies', array());
            if (isset($custom_taxonomies[$_GET['wpcf-tax']])) {
                $custom_taxonomies[$_GET['wpcf-tax']]['disabled'] = 0;
                $custom_taxonomies[$_GET['wpcf-tax']][TOOLSET_EDIT_LAST] = time();
                update_option('wpcf-custom-taxonomies', $custom_taxonomies);
                echo json_encode(array(
                    'output' => __('Taxonomy activated', 'wpcf'),
                    'execute' => 'jQuery("#wpcf-list-activate-'
                    . sanitize_text_field($_GET['wpcf-tax']) . '").replaceWith(\''
                    . wpcf_admin_custom_taxonomies_get_ajax_deactivation_link(sanitize_text_field($_GET['wpcf-tax']))
                    . '\');jQuery(".wpcf-table-column-active-'
                    . sanitize_text_field($_GET['wpcf-tax']) . '").html("' . __('Yes', 'wpcf') . '");',
                    'wpcf_nonce_ajax_callback' => wp_create_nonce('execute'),
                ));
            } else {
                echo json_encode(array(
                    'output' => __('Error occured', 'wpcf')
                ));
            }
            break;

        case 'delete_taxonomy':
            if (!isset($_GET['wpcf-tax'])) {
                die();
            }
            $custom_taxonomies = get_option('wpcf-custom-taxonomies', array());
            $custom_taxonomy = strval($_GET['wpcf-tax']);
            unset($custom_taxonomies[$custom_taxonomy]);
            update_option('wpcf-custom-taxonomies', $custom_taxonomies);
            wpcf_admin_deactivate_content('taxonomy', $custom_taxonomy);
            echo json_encode(array(
                'output' => '',
                'execute' => 'jQuery("#wpcf-list-activate-'
                . sanitize_text_field($_GET['wpcf-tax'])
                . '").parents("tr").css("background-color", "#FF0000").fadeOut();',
                'wpcf_nonce_ajax_callback' => wp_create_nonce('execute'),
            ));
            break;

        case 'add_radio_option':
            require_once WPCF_INC_ABSPATH . '/fields/radio.php';
            $element = wpcf_fields_radio_get_option(
                    urldecode($_GET['parent_name']));
            $id = array_shift($element);
            $element_txt = wpcf_fields_radio_get_option_alt_text($id,
                    urldecode($_GET['parent_name']));
            echo json_encode(array(
                'output' => wpcf_form_simple($element),
                'execute' => 'jQuery("#wpcf-form-groups-radio-ajax-response-'
                . urldecode($_GET['wpcf_ajax_update_add']) . '").append(\''
                . trim(str_replace("\r\n", '', wpcf_form_simple($element_txt))) . '\');',
                'wpcf_nonce_ajax_callback' => wp_create_nonce('execute'),
            ));
            break;

        case 'add_select_option':
            require_once WPCF_INC_ABSPATH . '/fields/select.php';
            $element = wpcf_fields_select_get_option(
                    urldecode($_GET['parent_name']));
            echo json_encode(array(
                'output' => wpcf_form_simple($element)
            ));
            break;

        case 'add_checkboxes_option':
            require_once WPCF_INC_ABSPATH . '/fields/checkboxes.php';
            $element = wpcf_fields_checkboxes_get_option(
                    urldecode($_GET['parent_name']));
            $id = array_shift($element);
            $element_txt = wpcf_fields_checkboxes_get_option_alt_text($id,
                    urldecode($_GET['parent_name']));
            echo json_encode(array(
                'output' => wpcf_form_simple($element),
//                'execute' => 'jQuery("#wpcf-form-groups-checkboxes-ajax-response-'
//                . urldecode($_GET['wpcf_ajax_update_add']) . '").append(\''
//                . trim(str_replace("\r\n", '', wpcf_form_simple($element_txt))) . '\');',
                'wpcf_nonce_ajax_callback' => wp_create_nonce('execute'),
            ));
            break;

        case 'group_form_collapsed':
            require_once WPCF_INC_ABSPATH . '/fields-form.php';
            $group_id = sanitize_text_field($_GET['group_id']);
            $action = sanitize_text_field($_GET['toggle']);
            $fieldset = sanitize_text_field($_GET['id']);
            wpcf_admin_fields_form_save_open_fieldset($action, $fieldset,
                    $group_id);
            break;

        case 'form_fieldset_toggle':
            $action = sanitize_text_field($_GET['toggle']);
            $fieldset = sanitize_text_field($_GET['id']);
            wpcf_admin_form_fieldset_save_toggle($action, $fieldset);
            break;

        case 'group_update_post_types':
            require_once WPCF_INC_ABSPATH . '/fields.php';
			// @todo Sanitize this $post_types
            $post_types = empty($_GET['wpcf']['group']['supports']) ? array() : $_GET['wpcf']['group']['supports'];
            if (!empty($_GET['group_id'])) {
                wpcf_admin_fields_save_group_post_types(sanitize_text_field($_GET['group_id']),
                        $post_types);
                $output = array();
                foreach ($post_types as $post_type) {
                    $post_type = get_post_type_object($post_type);
                    if (!empty($post_type->label)) {
                        $output[] = $post_type->label;
                    }
                }
                if (empty($post_types)) {
                    $output[] = __('No post types associated', 'wpcf');
                }
                $output = implode(', ', $output);
            } else {
                $output = __('No post types associated', 'wpcf');
            }
            echo json_encode(array(
                'output' => $output
            ));
            break;

        case 'group_update_taxonomies':
            require_once WPCF_INC_ABSPATH . '/fields.php';
            $taxonomies_post = empty($_GET['wpcf']['group']['taxonomies']) ? array() : $_GET['wpcf']['group']['taxonomies'];
            $terms = array();
            foreach ($taxonomies_post as $taxonomy) {
                foreach ($taxonomy as $tax => $term) {
                    $terms[] = $term;
                }
            }
            if (!empty($_GET['group_id'])) {
                wpcf_admin_fields_save_group_terms(sanitize_text_field($_GET['group_id']), $terms);
                $output = array();
                foreach ($taxonomies_post as $taxonomy => $terms) {
                    $taxonomy = get_taxonomy($taxonomy);
                    if (!empty($taxonomy)) {
                        $title = $taxonomy->label . ': ';
                        foreach ($terms as $term_id) {
                            $term = get_term($term_id, $taxonomy->name);
                            $output[] = $title . $term->name;
                            $title = '';
                        }
                    }
                }
                if (empty($output)) {
                    $output[] = __('No taxonomies associated', 'wpcf');
                }
                $output = implode(', ', $output);
            } else {
                $output = __('No taxonomies associated', 'wpcf');
            }
            echo json_encode(array(
                'output' => $output
            ));
            break;

        case 'custom_fields_control_bulk':
            require_once WPCF_INC_ABSPATH . '/fields.php';
            require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
            require_once WPCF_INC_ABSPATH . '/fields-control.php';
            wpcf_admin_custom_fields_control_bulk_ajax();
            break;

        case 'fields_delete':
        case 'delete_field':
            require_once WPCF_INC_ABSPATH . '/fields.php';
            if (isset($_GET['field_id'])) {
                wpcf_admin_fields_delete_field(sanitize_text_field($_GET['field_id']));
            }
            if (isset($_GET['field'])) {
                wpcf_admin_fields_delete_field(sanitize_text_field($_GET['field']));
            }
            echo json_encode(array(
                'output' => ''
            ));
            break;

        case 'remove_from_history':
            require_once WPCF_INC_ABSPATH . '/fields.php';
            $fields = wpcf_admin_fields_get_fields();
            if (isset($_GET['field_id']) && isset($fields[$_GET['field_id']])) {
                $fields[$_GET['field_id']]['data']['removed_from_history'] = 1;
                wpcf_admin_fields_save_fields($fields, true);
            }
            echo json_encode(array(
                'output' => ''
            ));
            break;

        case 'add_condition':
            require_once WPCF_INC_ABSPATH . '/fields.php';
            require_once WPCF_ABSPATH . '/includes/conditional-display.php';
            if (!empty($_GET['field']) || !empty($_GET['group'])) {
                $data = array();
                if (isset($_GET['group'])) {
                    $output = wpcf_form_simple(wpcf_cd_admin_form_single_filter(array(),
                                    array(), null, true));
                    echo json_encode(array(
                        'output' => $output,
                    ));
                } else {
                    $data['id'] = str_replace('_conditional_display', '',
                            sanitize_text_field($_GET['field']));
                    $output = wpcf_form_simple(wpcf_cd_admin_form_single_filter($data,
                                    array(), null, false));
                    if (!empty($data['id'])) {
                        echo json_encode(array(
                            'output' => $output,
                        ));
                    } else {
                        echo json_encode(array(
                            'output' => __('Error occured', 'wpcf'),
                        ));
                    }
                }
            } else {
                echo json_encode(array(
                    'output' => __('Error occured', 'wpcf'),
                ));
            }
            break;

        case 'pt_edit_fields':
            if (!empty($_GET['parent']) && !empty($_GET['child'])) {
                require_once WPCF_INC_ABSPATH . '/fields.php';
                require_once WPCF_INC_ABSPATH . '/post-relationship.php';
                wpcf_pr_admin_edit_fields(sanitize_text_field($_GET['parent']), sanitize_text_field($_GET['child']));
            }
            break;

        case 'toggle':
            $option = get_option('wpcf_toggle', array());
            $hidden = isset($_GET['hidden']) ? (bool) $_GET['hidden'] : 1;
            $_GET['div'] = strval($_GET['div']);
            if (!$hidden) {
                unset($option[$_GET['div']]);
            } else {
                $option[$_GET['div']] = 1;
            }
            update_option('wpcf_toggle', $option);
            break;

        case 'cb_save_empty_migrate':
            $output = sprintf(
                '<span style="color:red;">%s</div>',
                __('Migration process is not yet finished - please save group first, then change settings of this field.', 'wpcf')
            );
            if (isset($_GET['field']) && isset($_GET['subaction'])) {
                require_once WPCF_INC_ABSPATH . '/fields.php';
                $option = $_GET['meta_type'] == 'usermeta' ? 'wpcf-usermeta' : 'wpcf-fields';
                $meta_type = sanitize_text_field($_GET['meta_type']);
                $field = wpcf_admin_fields_get_field(sanitize_text_field($_GET['field']), false, false,
                        false, $option);

                $_txt_updates = $meta_type == 'usermeta' ? __( '%d users require update',
                                'wpcf' ) : __( '%d posts require update', 'wpcf' );
                $_txt_no_updates = $meta_type == 'usermeta' ? __('No users require update', 'wpcf') : __('No posts require update', 'wpcf');
                $_txt_updated = $meta_type == 'usermeta' ? __('Users updated', 'wpcf') : __('Posts updated', 'wpcf');

                if (!empty($field)) {
                    if ($_GET['subaction'] == 'save_check'
                            || $_GET['subaction'] == 'do_not_save_check') {
                        if ($field['type'] == 'checkbox') {
                            $posts = wpcf_admin_fields_checkbox_migrate_empty_check($field,
                                    $_GET['subaction']);
                        } else if ($field['type'] == 'checkboxes') {
                            $posts = wpcf_admin_fields_checkboxes_migrate_empty_check($field,
                                    $_GET['subaction']);
                        }
                        if (!empty($posts)) {
                            $output = '<div class="message updated"><p>'
                                    . sprintf($_txt_updates, count($posts)) . '&nbsp;'
                                    . '<a href="javascript:void(0);" class="button-primary" onclick="'
                                    . 'wpcfCbSaveEmptyMigrate(jQuery(this).parent().parent().parent(), \''
                                    . sanitize_text_field($_GET['field']) . '\', '
                                    . count($posts) . ', \''
                                    . wp_create_nonce('cb_save_empty_migrate') . '\', \'';
                            $output .= $_GET['subaction'] == 'save_check' ? 'save' : 'do_not_save';
                            $output .= '\', \'' . $meta_type . '\');'
                                    . '">'
                                    . __('Update') . '</a>' . '</p></div>';
                        } else {
                            $output = '<div class="message updated"><p><em>'
                                    . $_txt_no_updates . '</em></p></div>';
                        }
                    } else if ($_GET['subaction'] == 'save'
                            || $_GET['subaction'] == 'do_not_save') {
                        if ($field['type'] == 'checkbox') {
                            $posts = wpcf_admin_fields_checkbox_migrate_empty($field,
                                    $_GET['subaction']);
                        } else if ($field['type'] == 'checkboxes') {
                            $posts = wpcf_admin_fields_checkboxes_migrate_empty($field,
                                    $_GET['subaction']);
                        }
                        if (isset($posts['offset'])) {
                            if (!isset($_GET['total'])) {
                                $output = '<span style="color:red;">'
                                        . __('Error occured', 'wpcf') . '</div>';
                            } else {
                                $output = '<script type="text/javascript">wpcfCbMigrateStep('
                                        . intval($_GET['total']) . ','
                                        . $posts['offset'] . ','
                                        . '\'' . sanitize_text_field($_GET['field']) . '\','
                                        . '\'' . wp_create_nonce('cb_save_empty_migrate')
                                        . '\', \'' . $meta_type . '\');</script>'
                                        . number_format($posts['offset'])
                                        . '/' . number_format(intval($_GET['total']))
                                        . '<div class="wpcf-ajax-loading-small"></div>';
                            }
                        } else {
                            $output = '<div class="message updated"><p>'
                                    . $_txt_updated . '</p></div>';
                        }
                    }
                }
            }
            echo json_encode(array(
                'output' => $output,
            ));
            break;

        default:
            break;
    }
    die();
}
