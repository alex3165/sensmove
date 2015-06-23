<?php
/*
 * Import/export data.
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/embedded/includes/import-export.php $
 * $LastChangedDate: 2015-03-25 12:38:40 +0000 (Wed, 25 Mar 2015) $
 * $LastChangedRevision: 1120400 $
 * $LastChangedBy: iworks $
 *
 */

/**
 * Imports data from XML.
 *
 * @global object $wpdb
 *
 */
function wpcf_admin_import_data($data = '', $redirect = true, $context = 'types', $args = array() )
{
    global $wpdb;
    $data_installer = false;

    libxml_use_internal_errors( true );
    $data = simplexml_load_string( $data );
    if ( !$data ) {
        echo '<div class="message error"><p>' . __( 'Error parsing XML', 'wpcf' ) . '</p></div>';
        foreach ( libxml_get_errors() as $error ) {
            echo '<div class="message error"><p>' . $error->message . '</p></div>';
        }
        libxml_clear_errors();
        return false;
    }
    $overwrite_settings = isset( $_POST['overwrite-settings'] );
    $overwrite_groups = isset( $_POST['overwrite-groups'] );
    $overwrite_fields = isset( $_POST['overwrite-fields'] );
    $overwrite_types = isset( $_POST['overwrite-types'] );
    $overwrite_tax = isset( $_POST['overwrite-tax'] );
    $delete_groups = isset( $_POST['delete-groups'] );
    $delete_fields = isset( $_POST['delete-fields'] );
    $delete_types = isset( $_POST['delete-types'] );
    $delete_tax = isset( $_POST['delete-tax'] );

    if ('wpvdemo' == $context && !empty($args)) {
        /**
         * allow overwrite
         */
        $overwrite_groups = true;
        $overwrite_fields = true;
        $overwrite_types = true;
        $overwrite_tax = true;

        include_once dirname(__FILE__).'/classes/class.types.data.installer.php';
        $data_installer = new Types_Data_Installer($data, $args);
        $data = $data_installer->wpvdemo();
    }

    /**
     * process settings
     */
    if ( $overwrite_settings && isset( $data->settings ) ) {
        $wpcf_settings = wpcf_get_settings();
        foreach( wpcf_admin_import_export_simplexml2array( $data->settings ) as $key => $value ) {
            $wpcf_settings[$key] =  $value;
        }
        wpcf_save_settings( $wpcf_settings );
        wpcf_admin_message_store( __( 'Settings are updated.', 'wpcf' ) );
    }

    // Process groups

    $groups_check = array();
    if ( !empty( $data->groups ) ) {
        $groups = array();
        // Set insert data from XML
        foreach ( $data->groups->group as $group ) {
            $group = wpcf_admin_import_export_simplexml2array( $group );
            $groups[$group['ID']] = $group;
        }

        // Set insert data from POST
        if ( !empty( $_POST['groups'] ) ) {
            foreach ( $_POST['groups'] as $group_id => $group ) {
                if ( empty( $groups[$group_id] ) ) {
                    continue;
                }
                $groups[$group_id]['add'] = !empty( $group['add'] );
                $groups[$group_id]['update'] = (isset( $group['update'] ) && $group['update'] == 'update') ? true : false;
            }
        } else {
            foreach ( $groups as $group_id => $group ) {
                $groups[$group_id]['add'] = true;
                $groups[$group_id]['update'] = false;
            }
        }

        // Insert groups
        $show_import_fail_version_message = true;
        foreach ( $groups as $group_id => $group ) {
            $post = array(
                'post_status' => $group['post_status'],
                'post_type' => 'wp-types-group',
                'post_title' => $group['post_title'],
                'post_content' => !empty( $group['post_content'] ) ? $group['post_content'] : '',
            );
            /**
             * preserve slug
             */
            if ( array_key_exists( '__types_id', $group ) ) {
                $post['post_name'] = $group['__types_id'];
            }
            if ( (isset( $group['add'] ) && $group['add'] ) ) {
                $post_to_update = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type = %s",
                        $group['post_title'],
                        'wp-types-group'
                    )
                );
                // Update (may be forced by bulk action)
                if ( $group['update'] || ($overwrite_groups && !empty( $post_to_update )) ) {
                    if ( !empty( $post_to_update ) ) {
                        $post['ID'] = $post_to_update;
                        $group_wp_id = wp_update_post( $post );
                        if ( !$group_wp_id ) {
                            wpcf_admin_message_store( sprintf( __( 'Group "%s" update failed',
                                                    'wpcf' ),
                                            $group['post_title'] ), 'error' );
                        } else {
                            wpcf_admin_message_store( sprintf( __( 'Group "%s" updated',
                                                    'wpcf' ),
                                            $group['post_title'] ) );
                        }
                    } else {
                        wpcf_admin_message_store( sprintf( __( 'Group "%s" update failed',
                                                'wpcf' ), $group['post_title'] ),
                                'error' );
                    }
                } else { // Insert
                    $group_wp_id = wp_insert_post( $post, true );
                    if ( is_wp_error( $group_wp_id ) ) {
                        wpcf_admin_message_store( sprintf( __( 'Group "%s" insert failed',
                                                'wpcf' ), $group['post_title'] ),
                                'error' );
                    } else {
                        wpcf_admin_message_store( sprintf( __( 'Group "%s" added',
                                                'wpcf' ), $group['post_title'] ) );
                    }
                }
                // Update meta
                if ( !empty( $group['meta'] ) ) {
                    foreach ( $group['meta'] as $meta_key => $meta_value ) {
                        if ( '_wpcf_conditional_display' == $meta_key ) {
                            if ( !empty($meta_value) ) {
                                $meta_value = wpcf_admin_import_export_simplexml2array( $meta_value );
                                if ( !is_array($meta_value) ) {
                                    $meta_value = array();
                                    if ( $show_import_fail_version_message ) {
                                        $show_import_fail_version_message = false;
                                        wpcf_admin_message_store( __( 'The Types settings were not fully imported because it contained unsecured data. You should re-export your Types settings using the latest version of Types', 'wpcf' ), 'error' );
                                    }
                                }
                            }
                        }
                        update_post_meta( $group_wp_id, $meta_key, $meta_value);
                    }
                }
                $groups_check[] = $group_wp_id;
                if ( !empty( $post_to_update ) ) {
                    $groups_check[] = $post_to_update;
                }
            }
        }
        // Delete groups (forced, set in bulk actions)
    }

        if ( $delete_groups ) {
            $groups_to_delete = get_posts(
                array(
                    'post_type' => 'wp-types-group',
                    'post_status' => 'any',
                    'posts_per_page' => -1,
                )
            );
            if ( !empty( $groups_to_delete ) ) {
                foreach ( $groups_to_delete as $group_to_delete ) {
                    if ( !in_array( $group_to_delete->ID, $groups_check ) ) {
                        $deleted = wp_delete_post( $group_to_delete->ID, true );
                        if ( !$deleted ) {
                            wpcf_admin_message_store( sprintf( __( 'Group "%s" delete failed',
                                                    'wpcf' ),
                                            $group_to_delete->post_title ),
                                    'error' );
                        } else {
                            wpcf_admin_message_store( sprintf( __( 'Group "%s" deleted',
                                                    'wpcf' ),
                                            $group_to_delete->post_title ) );
                        }
                    }
                }
            }
        } else { // If not forced, look in POST
            if ( !empty( $_POST['groups-to-be-deleted'] ) ) {
                foreach ( $_POST['groups-to-be-deleted'] as $group_to_delete ) {
                    $group_to_delete_post = get_post( $group_to_delete );
                    if ( !empty( $group_to_delete_post ) && $group_to_delete_post->post_type == 'wp-types-group' ) {
                        $deleted = wp_delete_post( $group_to_delete, true );
                        if ( !$deleted ) {
                            wpcf_admin_message_store( sprintf( __( 'Group "%s" delete failed',
                                                    'wpcf' ),
                                            $group_to_delete_post->post_title ),
                                    'error' );
                        } else {
                            wpcf_admin_message_store( sprintf( __( 'Group "%s" deleted',
                                                    'wpcf' ),
                                            $group_to_delete_post->post_title ) );
                        }
                    } else {
                        wpcf_admin_message_store( sprintf( __( 'Group "%s" delete failed',
                                                'wpcf' ), $group_to_delete ),
                                'error' );
                    }
                }
            }
        }

    // Process fields

    $fields_check = array();
    $fields_existing = wpcf_admin_fields_get_fields();
    if ( !empty( $data->fields ) ) {
        $fields = array();
        // Set insert data from XML
        foreach ( $data->fields->field as $field ) {
            $field = wpcf_admin_import_export_simplexml2array( $field );
            // Set if submitted in 'types' context
            if ( $context == 'types' ) {
                // Process only if marked
                if ( isset( $_POST['fields'][$field['id']] ) ) {
                    $fields[$field['id']] = $field;
                }
            } else {
                $fields[$field['id']] = $field;
            }
        }
        // Set insert data from POST
        if ( !empty( $_POST['fields'] ) ) {
            foreach ( $_POST['fields'] as $field_id => $field ) {
                if ( empty( $fields[$field_id] ) ) {
                    continue;
                }
                $fields[$field_id]['add'] = !empty( $field['add'] );
                $fields[$field_id]['update'] = (isset( $field['update'] ) && $field['update'] == 'update') ? true : false;
            }
        }
        // Insert fields
        foreach ( $fields as $field_id => $field ) {
            if ( (isset( $field['add'] ) && !$field['add']) && !$overwrite_fields ) {
                continue;
            }
            if ( empty( $field['id'] ) || empty( $field['name'] ) || empty( $field['slug'] ) ) {
                continue;
            }
            $field_data = array();
            $field_data['description'] = isset( $field['description'] ) ? $field['description'] : '';
            $field_data['data'] = (isset( $field['data'] ) && is_array( $field['data'] )) ? $field['data'] : array();

            foreach( array( 'id', 'name', 'type', 'slug', 'meta_key', 'meta_type' ) as $key ) {
                if ( array_key_exists( $key, $field ) ) {
                    $field_data[$key] = $field[$key];
                }
            }

            $fields_existing[$field_id] = $field_data;
            $fields_check[] = $field_id;

            // WPML
            global $iclTranslationManagement;
            if ( !empty( $iclTranslationManagement ) && isset( $field['wpml_action'] ) ) {
                $iclTranslationManagement->settings['custom_fields_translation'][wpcf_types_get_meta_prefix( $field ) . $field_id] = $field['wpml_action'];
                $iclTranslationManagement->save_settings();
            }

            wpcf_admin_message_store( sprintf( __( 'Field "%s" added/updated',
                                    'wpcf' ), $field['name'] ) );
        }
    }
        // Delete fields
        if ( $delete_fields ) {
            foreach ( $fields_existing as $k => $v ) {
                if ( !empty( $v['data']['controlled'] ) ) {
                    continue;
                }
                if ( !in_array( $k, $fields_check ) ) {
                    wpcf_admin_message_store( sprintf( __( 'Field "%s" deleted',
                                            'wpcf' ),
                                    $fields_existing[$k]['name'] ) );
                    unset( $fields_existing[$k] );
                }
            }
        } else {
            if ( !empty( $_POST['fields-to-be-deleted'] ) ) {
                foreach ( $_POST['fields-to-be-deleted'] as $field_to_delete ) {
                    wpcf_admin_message_store( sprintf( __( 'Field "%s" deleted',
                                            'wpcf' ),
                                    $fields_existing[$field_to_delete]['name'] ) );
                    unset( $fields_existing[$field_to_delete] );
                }
            }
        }
        update_option( 'wpcf-fields', $fields_existing );

    // Process user groups
    //print_r($data->user_groups);exit;
        $groups_check = array();
    if ( !empty( $data->user_groups ) && isset( $data->user_groups->group) ) {
        $groups = array();
        // Set insert data from XML
        foreach ( $data->user_groups->group as $group ) {
            $group = wpcf_admin_import_export_simplexml2array( $group );
            $groups[$group['ID']] = $group;
        }
        // Set insert data from POST
        if ( !empty( $_POST['user_groups'] ) ) {
            foreach ( $_POST['user_groups'] as $group_id => $group ) {
                if ( empty( $groups[$group_id] ) ) {
                    continue;
                }
                $groups[$group_id]['add'] = !empty( $group['add'] );
                $groups[$group_id]['update'] = (isset( $group['update'] ) && $group['update'] == 'update') ? true : false;
            }
        } else {
            foreach ( $groups as $group_id => $group ) {
                $groups[$group_id]['add'] = true;
                $groups[$group_id]['update'] = false;
            }
        }

        // Insert groups
        foreach ( $groups as $group_id => $group ) {
            $post = array(
                'post_status' => $group['post_status'],
                'post_type' => 'wp-types-user-group',
                'post_title' => $group['post_title'],
                'post_content' => !empty( $group['post_content'] ) ? $group['post_content'] : '',
            );
            if ( (isset( $group['add'] ) && $group['add'] ) ) {
                $post_to_update = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type = %s",
                        $group['post_title'],
                        'wp-types-user-group'
                    )
                );

                // Update (may be forced by bulk action)
                if ( $group['update'] || ($overwrite_groups && !empty( $post_to_update )) ) {
                    if ( !empty( $post_to_update ) ) {
                        $post['ID'] = $post_to_update;

                        $group_wp_id = wp_update_post( $post );
                        if ( !$group_wp_id ) {
                            wpcf_admin_message_store( sprintf( __( 'User group "%s" update failed',
                                                    'wpcf' ),
                                            $group['post_title'] ), 'error' );
                        } else {
                            wpcf_admin_message_store( sprintf( __( 'User group "%s" updated',
                                                    'wpcf' ),
                                            $group['post_title'] ) );
                        }
                    } else {
                        wpcf_admin_message_store( sprintf( __( 'User group "%s" update failed',
                                                'wpcf' ), $group['post_title'] ),
                                'error' );
                    }
                } else { // Insert
                    $group_wp_id = wp_insert_post( $post, true );
                    if ( is_wp_error( $group_wp_id ) ) {
                        wpcf_admin_message_store( sprintf( __( 'User group "%s" insert failed',
                                                'wpcf' ), $group['post_title'] ),
                                'error' );
                    } else {
                        wpcf_admin_message_store( sprintf( __( 'User group "%s" added',
                                                'wpcf' ), $group['post_title'] ) );
                    }
                }
                // Update meta
                if ( !empty( $group['meta'] ) ) {
                    foreach ( $group['meta'] as $meta_key => $meta_value ) {
                        update_post_meta( $group_wp_id, $meta_key, wpcf_admin_import_export_simplexml2array( $meta_value ) );
                    }
                }
                $groups_check[] = $group_wp_id;
                if ( !empty( $post_to_update ) ) {
                    $groups_check[] = $post_to_update;
                }
            }
        }
    }
        // Delete groups (forced, set in bulk actions)
        if ( $delete_groups ) {
            $groups_to_delete = get_posts(
                array(
                    'post_type' => 'wp-types-user-group',
                    'post_status' => 'any',
                    'posts_per_page' => -1,
                )
            );
            if ( !empty( $groups_to_delete ) ) {
                foreach ( $groups_to_delete as $group_to_delete ) {
                    if ( !in_array( $group_to_delete->ID, $groups_check ) ) {
                        $deleted = wp_delete_post( $group_to_delete->ID, true );
                        if ( !$deleted ) {
                            wpcf_admin_message_store( sprintf( __( 'User group "%s" delete failed',
                                                    'wpcf' ),
                                            $group_to_delete->post_title ),
                                    'error' );
                        } else {
                            wpcf_admin_message_store( sprintf( __( 'User group "%s" deleted',
                                                    'wpcf' ),
                                            $group_to_delete->post_title ) );
                        }
                    }
                }
            }
        } else { // If not forced, look in POST
            if ( !empty( $_POST['user-groups-to-be-deleted'] ) ) {
                foreach ( $_POST['user-groups-to-be-deleted'] as
                            $group_to_delete ) {
                    $group_to_delete_post = get_post( $group_to_delete );
                    if ( !empty( $group_to_delete_post ) && $group_to_delete_post->post_type == 'wp-types-user-group' ) {
                        $deleted = wp_delete_post( $group_to_delete, true );
                        if ( !$deleted ) {
                            wpcf_admin_message_store( sprintf( __( 'User group "%s" delete failed',
                                                    'wpcf' ),
                                            $group_to_delete_post->post_title ),
                                    'error' );
                        } else {
                            wpcf_admin_message_store( sprintf( __( 'User group "%s" deleted',
                                                    'wpcf' ),
                                            $group_to_delete_post->post_title ) );
                        }
                    } else {
                        wpcf_admin_message_store( sprintf( __( 'User group "%s" delete failed',
                                                'wpcf' ), $group_to_delete ),
                                'error' );
                    }
                }
            }
        }

    // Process fields
    $fields_existing = wpcf_admin_fields_get_fields( false, false, false, 'wpcf-usermeta' );
    $fields_check = array();
    if ( !empty( $data->user_fields ) ) {
        $fields = array();
        // Set insert data from XML
        foreach ( $data->user_fields->field as $field ) {
            $field = wpcf_admin_import_export_simplexml2array( $field );
            // Set if submitted in 'types' context
            if ( $context == 'types' ) {
                // Process only if marked
                if ( isset( $_POST['user_fields'][$field['id']] ) ) {
                    $fields[$field['id']] = $field;
                }
            } else {
                $fields[$field['id']] = $field;
            }
        }
        // Set insert data from POST
        if ( !empty( $_POST['user_fields'] ) ) {
            foreach ( $_POST['user_fields'] as $field_id => $field ) {
                if ( empty( $fields[$field_id] ) ) {
                    continue;
                }
                $fields[$field_id]['add'] = !empty( $field['add'] );
                $fields[$field_id]['update'] = (isset( $field['update'] ) && $field['update'] == 'update') ? true : false;
            }
        }
        // Insert fields
        foreach ( $fields as $field_id => $field ) {
            if ( (isset( $field['add'] ) && !$field['add']) && !$overwrite_fields ) {
                continue;
            }
            if ( empty( $field['id'] ) || empty( $field['name'] ) || empty( $field['slug'] ) ) {
                continue;
            }
            $field_data = array();
            $field_data['id'] = $field['id'];
            $field_data['name'] = $field['name'];
            $field_data['description'] = isset( $field['description'] ) ? $field['description'] : '';
            $field_data['type'] = $field['type'];
            $field_data['slug'] = $field['slug'];
            $field_data['data'] = (isset( $field['data'] ) && is_array( $field['data'] )) ? $field['data'] : array();
            $fields_existing[$field_id] = $field_data;
            $fields_check[] = $field_id;

            // WPML
            global $iclTranslationManagement;
            if ( !empty( $iclTranslationManagement ) && isset( $field['wpml_action'] ) ) {
                $iclTranslationManagement->settings['custom_fields_translation'][wpcf_types_get_meta_prefix( $field ) . $field_id] = $field['wpml_action'];
                $iclTranslationManagement->save_settings();
            }

            wpcf_admin_message_store( sprintf( __( 'User field "%s" added/updated',
                                    'wpcf' ), $field['name'] ) );
        }
    }
        // Delete fields
        if ( $delete_fields ) {
            foreach ( $fields_existing as $k => $v ) {
                if ( !empty( $v['data']['controlled'] ) ) {
                    continue;
                }
                if ( !in_array( $k, $fields_check ) ) {
                    wpcf_admin_message_store( sprintf( __( 'User field "%s" deleted',
                                            'wpcf' ),
                                    $fields_existing[$k]['name'] ) );
                    unset( $fields_existing[$k] );
                }
            }
        } else {
            if ( !empty( $_POST['user-fields-to-be-deleted'] ) ) {
                foreach ( $_POST['user-fields-to-be-deleted'] as
                            $field_to_delete ) {
                    wpcf_admin_message_store( sprintf( __( 'User field "%s" deleted',
                                            'wpcf' ),
                                    $fields_existing[$field_to_delete]['name'] ) );
                    unset( $fields_existing[$field_to_delete] );
                }
            }
        }
        update_option( 'wpcf-usermeta', $fields_existing );

    // Process types

        $types_existing = get_option( 'wpcf-custom-types', array() );
        $types_check = array();
    if ( !empty($data->types) && isset($data->types->type) ) {
        $types = array();
        // Set insert data from XML
        foreach ( $data->types->type as $type ) {
            $type = wpcf_admin_import_export_simplexml2array( $type );
            // Set if submitted in 'types' context
            if ( $context == 'types' ) {
                if ( isset( $_POST['types'][$type['id']] ) ) {
                    $types[$type['id']] = $type;
                }
            } else {
                $types[$type['id']] = $type;
            }
        }
        // Set insert data from POST
        if ( !empty( $_POST['types'] ) ) {
            foreach ( $_POST['types'] as $type_id => $type ) {
                if ( empty( $types[$type_id] ) ) {
                    continue;
                }
                $types[$type_id]['add'] = !empty( $type['add'] );
                $types[$type_id]['update'] = (isset( $type['update'] ) && $type['update'] == 'update') ? true : false;
            }
        }
        // Insert types
        foreach ( $types as $type_id => $type ) {
            if ( (isset( $type['add'] ) && !$type['add']) && !$overwrite_types ) {
                continue;
            }
            unset( $type['add'], $type['update'] );
            $types_existing[$type_id] = $type;
            $types_check[] = $type_id;
            wpcf_admin_message_store( sprintf( __( 'Custom post type "%s" added/updated',
                                    'wpcf' ), $type_id ) );
        }
    }
        // Delete types
        if ( $delete_types ) {
            foreach ( $types_existing as $k => $v ) {
                if ( !in_array( $k, $types_check ) ) {
                    unset( $types_existing[$k] );
                    wpcf_admin_message_store( sprintf( __( 'Custom post type "%s" deleted',
                                            'wpcf' ), esc_html( $k ) ) );
                }
            }
        } else {
            if ( !empty( $_POST['types-to-be-deleted'] ) ) {
                foreach ( $_POST['types-to-be-deleted'] as $type_to_delete ) {
                    wpcf_admin_message_store( sprintf( __( 'Custom post type "%s" deleted',
                                            'wpcf' ),
                                    $types_existing[$type_to_delete]['labels']['name'] ) );
                    unset( $types_existing[$type_to_delete] );
                }
            }
        }
        update_option( 'wpcf-custom-types', $types_existing );

    // Process taxonomies

        $taxonomies_existing = get_option( 'wpcf-custom-taxonomies', array() );
        $taxonomies_check = array();
    if ( !empty( $data->taxonomies ) && isset($data->taxonomies->taxonomy)) {
        $taxonomies = array();
        // Set insert data from XML
        foreach ( $data->taxonomies->taxonomy as $taxonomy ) {
            $taxonomy = wpcf_admin_import_export_simplexml2array( $taxonomy );
            // Set if submitted in 'types' context
            if ( $context == 'types' ) {
                if ( isset( $_POST['taxonomies'][$taxonomy['id']] ) ) {
                    $taxonomies[$taxonomy['id']] = $taxonomy;
                }
            } else {
                $taxonomies[$taxonomy['id']] = $taxonomy;
            }
        }
        // Set insert data from POST
        if ( !empty( $_POST['taxonomies'] ) ) {
            foreach ( $_POST['taxonomies'] as $taxonomy_id => $taxonomy ) {
                if ( empty( $taxonomies[$taxonomy_id] ) ) {
                    continue;
                }
                $taxonomies[$taxonomy_id]['add'] = !empty( $taxonomy['add'] );
                $taxonomies[$taxonomy_id]['update'] = (isset( $taxonomy['update'] ) && $taxonomy['update'] == 'update') ? true : false;
            }
        }
        // Insert taxonomies
        foreach ( $taxonomies as $taxonomy_id => $taxonomy ) {
            if ( (isset( $taxonomy['add'] ) && !$taxonomy['add']) && !$overwrite_tax ) {
                continue;
            }
            unset( $taxonomy['add'], $taxonomy['update'] );
            $taxonomies_existing[$taxonomy_id] = $taxonomy;
            $taxonomies_check[] = $taxonomy_id;
            wpcf_admin_message_store( sprintf( __( 'Custom taxonomy "%s" added/updated',
                                    'wpcf' ), $taxonomy_id ) );
        }
    }


        /**
         * reset TOOLSET_EDIT_LAST
         */
        if ( $data_installer ) {
            $data_installer->reset_toolset_edit_last();
        }

        // Delete taxonomies
        if ( $delete_tax ) {
            foreach ( $taxonomies_existing as $k => $v ) {
                if ( !in_array( $k, $taxonomies_check ) ) {
                    unset( $taxonomies_existing[$k] );
                    wpcf_admin_message_store( sprintf( __( 'Custom taxonomy "%s" deleted', 'wpcf' ), $k ) );
                }
            }
        } else {
            if ( !empty( $_POST['taxonomies-to-be-deleted'] ) ) {
                foreach ( $_POST['taxonomies-to-be-deleted'] as
                            $taxonomy_to_delete ) {
                    wpcf_admin_message_store( sprintf( __( 'Custom taxonomy "%s" deleted', 'wpcf' ),
                                    $taxonomies_existing[$taxonomy_to_delete]['labels']['name'] ) );
                    unset( $taxonomies_existing[$taxonomy_to_delete] );
                }
            }
        }
        update_option( 'wpcf-custom-taxonomies', $taxonomies_existing );

    // Add relationships
    if ( !empty( $data->post_relationships ) && !empty( $_POST['post_relationship'] ) ) {
        $relationship_existing = get_option( 'wpcf_post_relationship', array() );
        /**
         * be sure, $relationship_existing is a array!
         */
        if ( !is_array( $relationship_existing ) ) {
            $relationship_existing = array();
        }
        $relationship = json_decode( $data->post_relationships->data, true );
        if ( is_array( $relationship ) ) {
            $relationship = array_merge( $relationship_existing, $relationship );
            update_option( 'wpcf_post_relationship', $relationship );
            wpcf_admin_message_store( __( 'Post relationships created', 'wpcf' ) );
        } else {
            wpcf_admin_message_store( __( 'Post relationships settings were not imported because it contained unsecured data. You should re-export your Types settings using the latest version of Types', 'wpcf' ), 'error' );
        }
    }

    // WPML bulk registration
    if ( wpcf_get_settings( 'register_translations_on_import' ) ) {
        wpcf_admin_bulk_string_translation();
    }

    // Flush rewrite rules
    wpcf_init_custom_types_taxonomies();
    flush_rewrite_rules();

    if ( $redirect ) {
        echo '<script type="text/javascript">
<!--
window.location = "' . admin_url( 'admin.php?page=wpcf-import-export' ) . '"
//-->
</script>';
        die();
    }
}

/**
 * Loops over elements and convert to array or empty string.
 *
 * @param type $element
 * @return string
 */
function wpcf_admin_import_export_simplexml2array($element)
{
    $element = is_string( $element ) ? trim( $element ) : $element;
    if ( !empty( $element ) && is_object( $element ) ) {
        $element = (array) $element;
    }
    if ( !is_array( $element ) && strval( $element ) == '0' ) {
        $element = 0;
    } elseif ( empty( $element ) ) {
        $element = '';
    } elseif ( is_array( $element ) ) {
        foreach ( $element as $k => $v ) {
            $v = is_string( $v ) ? trim( $v ) : $v;
            if ( !is_array( $v ) && strval( $v ) == '0' ) {
                $element[$k] = 0;
            } elseif ( empty( $v ) ) {
                $element[$k] = '';
                continue;
            }
            $add = wpcf_admin_import_export_simplexml2array( $v );
            if ( !is_array( $add ) && strval( $add ) == '0' ) {
                $element[$k] = 0;
            } elseif ( !empty( $add ) ) {
                $element[$k] = $add;
            } else {
                $element[$k] = '';
            }
        }
    }

    if ( !is_array( $element ) && strval( $element ) == '0' ) {
        $element = 0;
    } elseif ( empty( $element ) ) {
        $element = '';
    }

    return $element;
}

