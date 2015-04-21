<?php
/*
 * Import/export data.
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/includes/import-export.php $
 * $LastChangedDate: 2015-03-25 12:38:40 +0000 (Wed, 25 Mar 2015) $
 * $LastChangedRevision: 1120400 $
 * $LastChangedBy: iworks $
 *
 */
require_once WPCF_EMBEDDED_INC_ABSPATH . '/import-export.php';

/**
 * Import/Export form data.
 *
 * @return type
 */
function wpcf_admin_import_export_form()
{
    $form = array();
    $form['wpnonce'] = array(
        '#type' => 'hidden',
        '#name' => '_wpnonce',
        '#value' => wp_create_nonce( 'wpcf_import' ),
    );
    $form_base = $form;
    $show_first_screen = true;
    if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'wpcf_import' ) ) {
        $show_first_screen = false;
        if ( isset( $_POST['import-final'] ) ) {
            if ( $_POST['mode'] == 'file' && !empty( $_POST['file'] ) ) {
                $file = get_transient( sanitize_text_field( $_POST['file'] ) );
                if ( file_exists($file) ) {
                    $info = pathinfo($file);
                    $is_zip = $info['extension'] == 'zip' ? true : false;
                    if ( $is_zip ) {
                        $zip = zip_open($file);
                        if ( is_resource( $zip ) ) {
                            while ( ($zip_entry = zip_read( $zip )) !== false ) {
                                if ( zip_entry_name( $zip_entry ) == 'settings.xml' ) {
                                    $data = @zip_entry_read( $zip_entry,
                                        zip_entry_filesize( $zip_entry ) );
                                }
                            }
                        } else {
                            echo '<div class="message error"><p>'
                                . __( 'Unable to open zip file', 'wpcf' )
                                . '</p></div>';
                            return array();
                        }
                    } else {
                        $data = @file_get_contents( $file );
                    }

                    @unlink($file);

                    if ( $data ) {
                        wpcf_admin_import_data( $data );
                    } else {
                        echo '<div class="message error"><p>'
                            . __( 'Unable to process file', 'wpcf' )
                            . '</p></div>';
                        return array();
                    }
                } else {
                    echo '<div class="message error"><p>'
                        . __( 'Unable to process file', 'wpcf' )
                        . '</p></div>';
                    return array();
                }
            }
            if ( $_POST['mode'] == 'text' && !empty( $_POST['text'] ) ) {
                $charset = !empty( $_POST['text-encoding'] ) ? sanitize_text_field( $_POST['text-encoding'] ) : get_option( 'blog_charset' );
                wpcf_admin_import_data( stripslashes( html_entity_decode( $_POST['text'],
                                        ENT_QUOTES, $charset ) ) );
            }
        } elseif ( isset( $_POST['step'] ) ) {
            $mode = 'none';
            $data = '';
            if ( !empty( $_POST['import-file'] ) && !empty( $_FILES['file']['tmp_name'] ) ) {
                if ( $_FILES['file']['type'] == 'text/xml' ) {
                    $_FILES['file']['name'] .= '.txt';
                }
                /*
                 *
                 * We need to move uploaded file manually
                 */
                if ( !empty( $_FILES['file']['error'] ) ) {
                    echo '<div class="message error"><p>'
                    . __( 'Error uploading file', 'wpcf' )
                    . '</p></div>';
                    return array();
                }
                $wp_upload_dir = wp_upload_dir();
                $new_file = $wp_upload_dir['basedir'] . '/' . $_FILES['file']['name'];
                $move = move_uploaded_file( $_FILES['file']['tmp_name'],
                        $new_file );
                if ( !$move ) {
                    echo '<div class="message error"><p>'
                    . __( 'Error moving uploaded file', 'wpcf' )
                    . '</p></div>';
                    return array();
                }

                $uploaded_file = array(
                    'file' => $new_file
                );
                $info = pathinfo( $uploaded_file['file'] );
                $is_zip = $info['extension'] == 'zip' ? true : false;
                if ( $is_zip ) {
                    $zip = zip_open( $uploaded_file['file'] );
                    if ( is_resource( $zip ) ) {
                        while ( ($zip_entry = zip_read( $zip )) !== false ) {
                            if ( zip_entry_name( $zip_entry ) == 'settings.xml' ) {
                                $data = @zip_entry_read( $zip_entry,
                                                zip_entry_filesize( $zip_entry ) );
                            }
                        }
                    } else {
                        echo '<div class="message error"><p>'
                        . __( 'Unable to open zip file', 'wpcf' )
                        . '</p></div>';
                        return array();
                    }
                } else {
                    $data = @file_get_contents( $uploaded_file['file'] );
                }
                /**
                 * use Transients API to store file fullpath
                 */
                $current_user = wp_get_current_user();
                $cache_key = md5($current_user->user_email.$uploaded_file['file']);
                set_transient( $cache_key, $uploaded_file['file'], 60*60 );
                $form['file'] = array(
                    '#type' => 'hidden',
                    '#name' => 'file',
                    '#value' => $cache_key,
                );
                $mode = 'file';
            } elseif ( !empty( $_POST['import-text'] ) && !empty( $_POST['text'] ) ) {
                $data = stripslashes( $_POST['text'] );
                if ( preg_match( '/encoding=("[^"]*"|\'[^\']*\')/s', $data,
                                $match ) ) {
                    $charset = trim( $match[1], '"' );
                } else {
                    $charset = !empty( $_POST['text-encoding'] ) ? sanitize_text_field( $_POST['text-encoding'] ) : get_option( 'blog_charset' );
                }
                $form['text'] = array(
                    '#type' => 'hidden',
                    '#name' => 'text',
                    '#value' => htmlentities( stripslashes( $_POST['text'] ),
                            ENT_QUOTES, $charset ),
                );
                $form['text-encoding'] = array(
                    '#type' => 'hidden',
                    '#name' => 'text-encoding',
                    '#value' => $charset,
                );
                $mode = 'text';
            }
            if ( empty( $data ) ) {
                echo '<div class="message error"><p>'
                . __( 'Data not valid', 'wpcf' )
                . '</p></div>';
                $show_first_screen = true;
            } else {
                $data = wpcf_admin_import_export_settings( $data );
                if ( empty( $data ) ) {
                    echo '<div class="message error"><p>'
                        . __( 'Data not valid', 'wpcf' )
                        . '</p></div>';
                    $show_first_screen = true;
                } else {
                    $form = array_merge( $form, $data );
                    $form['mode'] = array(
                        '#type' => 'hidden',
                        '#name' => 'mode',
                        '#value' => $mode,
                    );
                    $form['import-final'] = array(
                        '#type' => 'hidden',
                        '#name' => 'import-final',
                        '#value' => 1,
                    );
                    $form['submit'] = array(
                        '#type' => 'submit',
                        '#name' => 'import',
                        '#value' => __( 'Import', 'wpcf' ),
                        '#attributes' => array('class' => 'button-primary'),
                    );
                }
            }
        }
    }
    if ( $show_first_screen ) {
        $form = $form_base;
        $form['embedded-settings'] = array(
            '#type' => 'radios',
            '#name' => 'embedded-settings',
            '#title' => __( 'When importing to theme:', 'wpcf' ),
            '#options' => array(
                __( 'ask user for approval', 'wpcf' ) => 'ask',
                __( 'import automatically', 'wpcf' ) => 'auto',
            ),
            '#inline' => true,
            '#before' => '<h2>' . __( 'Export Types data', 'wpcf' ) . '</h2>'
            . __( 'Download all custom fields, custom post types and taxonomies created by Types plugin.',
                'wpcf' ) . '<br /><br />',
            );
        $form['submit'] = array(
            '#type' => 'submit',
            '#name' => 'export',
            '#value' => __( 'Export', 'wpcf' ),
            '#attributes' => array('class' => 'button-primary'),
            '#after' => '<br /><br />',
        );
        /**
         * check is temp folder available?
         */
        $temp = wpcf_get_temporary_directory();
        if ( empty($temp) ) {
            unset($form['submit']);
            $form['embedded-settings']['#disable'] = true;
            $form['embedded-settings']['#after'] = sprintf(
                '<p class="error-message"><b>%s</b> %s</p>',
                __( 'Temporary directory is not found or there is not enough disk space.', 'wpcf' ),
                __('Please check server settings or contact your server administrator.', 'wpcf' )
            );

        }
        if ( extension_loaded( 'simplexml' ) ) {
            $attributes = !wpcf_admin_import_dir() ? array('disabled' => 'disabled') : array();
            $form['file'] = array(
                '#type' => 'file',
                '#name' => 'file',
                '#prefix' => __( 'Upload XML file', 'wpcf' ) . '<br />',
                '#before' => '<h2>' . __( 'Import Types data file', 'wpcf' ) . '</h2>',
                '#inline' => true,
                '#attributes' => $attributes,
            );
            $form['submit-file'] = array(
                '#type' => 'submit',
                '#name' => 'import-file',
                '#value' => __( 'Import file', 'wpcf' ),
                '#attributes' => array_merge(
                    $attributes,
                    array(
                        'class' => 'button-primary',
                        'disabled' => 'disabled',
                    )
                ),
                '#prefix' => '<br />',
                '#suffix' => '<br /><br />',
            );
            $form['text'] = array(
                '#type' => 'textarea',
                '#title' => __( 'Paste code here', 'wpcf' ),
                '#name' => 'text',
                '#attributes' => array('rows' => 20),
                '#before' => '<h2>' . __( 'Import Types data text input', 'wpcf' ) . '</h2>',
            );
            $form['text-encoding'] = array(
                '#type' => 'textfield',
                '#title' => __( 'Encoding', 'wpcf' ),
                '#name' => 'text-encoding',
                '#value' => get_option( 'blog_charset' ),
                '#description' => __( 'If encoding is set in text input, it will override this setting.',
                        'wpcf' ),
            );
            $form['submit-text'] = array(
                '#type' => 'submit',
                '#name' => 'import-text',
                '#value' => __( 'Import text', 'wpcf' ),
                '#attributes' => array('class' => 'button-primary'),
            );
            $form['step'] = array(
                '#type' => 'hidden',
                '#name' => 'step',
                '#value' => 1,
            );
        } else {
            echo '<div class="message error"><p>'
            . __( 'PHP SimpleXML extension not loaded: Importing not available',
                    'wpcf' )
            . '</p></div>';
        }
    }

    return $form;
}

/**
 * File upload error handler.
 *
 * @param type $file
 * @param type $error_msg
 */
function wpcf_admin_import_export_file_upload_error($file, $error_msg)
{
    echo '<div class="message error"><p>' . $error_msg . '</p></div>';
}

/**
 * Import settings.
 *
 * @global object $wpdb
 * @param SimpleXMLElement $data
 * @return string
 */
function wpcf_admin_import_export_settings($data)
{
    global $wpdb;
    $form = array();
    $form['title'] = array(
        '#type' => 'markup',
        '#markup' => '<h2>' . __( 'General Settings', 'wpcf' ) . '</h2>',
    );
    $form['overwrite-settings'] = array(
        '#type' => 'checkbox',
        '#title' => __( 'Overwrite settings', 'wpcf' ),
        '#name' => 'overwrite-settings',
        '#inline' => true,
        '#after' => '<br />',
    );
    $form['overwrite-or-add-groups'] = array(
        '#type' => 'checkbox',
        '#title' => __( 'Bulk overwrite groups if exist', 'wpcf' ),
        '#name' => 'overwrite-groups',
        '#inline' => true,
        '#after' => '<br />',
    );
    $form['delete-groups'] = array(
        '#type' => 'checkbox',
        '#title' => __( "Delete group if don't exist", 'wpcf' ),
        '#name' => 'delete-groups',
        '#inline' => true,
        '#after' => '<br />',
    );
    $form['delete-fields'] = array(
        '#type' => 'checkbox',
        '#title' => __( "Delete field if don't exist", 'wpcf' ),
        '#name' => 'delete-fields',
        '#inline' => true,
        '#after' => '<br />',
    );
    $form['delete-types'] = array(
        '#type' => 'checkbox',
        '#title' => __( "Delete custom post type if don't exist", 'wpcf' ),
        '#name' => 'delete-types',
        '#inline' => true,
        '#after' => '<br />',
    );
    $form['delete-tax'] = array(
        '#type' => 'checkbox',
        '#title' => __( "Delete custom taxonomy if don't exist", 'wpcf' ),
        '#name' => 'delete-tax',
        '#inline' => true,
        '#after' => '<br />',
    );
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
//    $data = new SimpleXMLElement($data);
    // Check groups
    if ( !empty( $data->groups ) ) {
        $form['title-1'] = array(
            '#type' => 'markup',
            '#markup' => '<h2>' . __( 'Groups to be added/updated', 'wpcf' ) . '</h2>',
        );
        $groups_check = array();
        foreach ( $data->groups->group as $group ) {
            $group = (array) $group;
            $form['group-add-' . $group['ID']] = array(
                '#type' => 'checkbox',
                '#name' => 'groups[' . $group['ID'] . '][add]',
                '#default_value' => true,
                '#title' => '<strong>' . esc_html( $group['post_title'] ) . '</strong>',
                '#inline' => true,
                '#after' => '<br />',
            );
            $post = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type = %s",
                    $group['post_title'],
                    $group['post_type']
                )
            );
            if ( !empty( $post ) ) {
                $form['group-add-' . $group['ID']]['#after'] = wpcf_form_simple(
                        array('group-add-update-' . $group['ID'] => array(
                                '#type' => 'radios',
                                '#name' => 'groups[' . $group['ID'] . '][update]',
                                '#inline' => true,
                                '#options' => array(
                                    __( 'Update', 'wpcf' ) => 'update',
                                    __( 'Create new', 'wpcf' ) => 'add'
                                ),
                                '#default_value' => 'update',
                                '#before' => '<br />',
                                '#after' => '<br />',
                            )
                        )
                );
            }
            $groups_check[] = $group['post_title'];
        }
        $groups_existing = get_posts( 'post_type=wp-types-group&post_status=null' );
        if ( !empty( $groups_existing ) ) {
            $groups_to_be_deleted = array();
            foreach ( $groups_existing as $post ) {
                if ( !in_array( $post->post_title, $groups_check ) ) {
                    $groups_to_be_deleted['<strong>' . $post->post_title . '</strong>'] = $post->ID;
                }
            }
            if ( !empty( $groups_to_be_deleted ) ) {
                $form['title-groups-deleted'] = array(
                    '#type' => 'markup',
                    '#markup' => '<h2>' . __( 'Groups to be deleted', 'wpcf' ) . '</h2>',
                );
                $form['groups-deleted'] = array(
                    '#type' => 'checkboxes',
                    '#name' => 'groups-to-be-deleted',
                    '#options' => $groups_to_be_deleted,
                );
            }
        }
    }

    // Check fields
    if ( !empty( $data->fields ) ) {
        $form['title-fields'] = array(
            '#type' => 'markup',
            '#markup' => '<h2>' . __( 'Fields to be added/updated', 'wpcf' ) . '</h2>',
        );
        $fields_existing = wpcf_admin_fields_get_fields();
        $fields_check = array();
        $fields_to_be_deleted = array();
        foreach ( $data->fields->field as $field ) {
            $field = (array) $field;
            if ( empty( $field['id'] ) || empty( $field['name'] ) ) {
                continue;
            }
            $form['field-add-' . $field['id']] = array(
                '#type' => 'checkbox',
                '#name' => 'fields[' . $field['id'] . '][add]',
                '#default_value' => true,
                '#title' => '<strong>' . $field['name'] . '</strong>',
                '#inline' => true,
                '#after' => '<br />',
            );
            $fields_check[] = $field['id'];
        }

        foreach ( $fields_existing as $field_id => $field ) {
            if ( !in_array( $field_id, $fields_check ) ) {
                $fields_to_be_deleted['<strong>' . $field['name'] . '</strong>'] = $field['id'];
            }
        }

        if ( !empty( $fields_to_be_deleted ) ) {
            $form['title-fields-deleted'] = array(
                '#type' => 'markup',
                '#markup' => '<h2>' . __( 'Fields to be deleted', 'wpcf' ) . '</h2>',
            );
            $form['fields-deleted'] = array(
                '#type' => 'checkboxes',
                '#name' => 'fields-to-be-deleted',
                '#options' => $fields_to_be_deleted,
            );
        }
    }

    // Check user groups
    if ( !empty( $data->user_groups ) ) {
        $form['title-users'] = array(
            '#type' => 'markup',
            '#markup' => '<h2>' . __( 'User Groups to be added/updated', 'wpcf' ) . '</h2>',
        );
        $groups_check = array();
        foreach ( $data->user_groups->group as $group ) {
            $group = (array) $group;
            $form['user-group-add-' . $group['ID']] = array(
                '#type' => 'checkbox',
                '#name' => 'user_groups[' . $group['ID'] . '][add]',
                '#default_value' => true,
                '#title' => '<strong>' . esc_html( $group['post_title'] ) . '</strong>',
                '#inline' => true,
                '#after' => '<br /><br />',
            );
            $post = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type = %s",
                    $group['post_title'],
                    $group['post_type']
                )
            );
            if ( !empty( $post ) ) {
                $form['user-group-add-' . $group['ID']]['#after'] = wpcf_form_simple(
                        array('user-group-add-update-' . $group['ID'] => array(
                                '#type' => 'radios',
                                '#name' => 'user_groups[' . $group['ID'] . '][update]',
                                '#inline' => true,
                                '#options' => array(
                                    __( 'Update', 'wpcf' ) => 'update',
                                    __( 'Create new', 'wpcf' ) => 'add'
                                ),
                                '#default_value' => 'update',
                                '#before' => '<br />',
                                '#after' => '<br />',
                            )
                        )
                );
            }
            $groups_check[] = $group['post_title'];
        }
        $groups_existing = get_posts( 'post_type=wp-types-user-group&post_status=null' );
        if ( !empty( $groups_existing ) ) {
            $groups_to_be_deleted = array();
            foreach ( $groups_existing as $post ) {
                if ( !in_array( $post->post_title, $groups_check ) ) {
                    $groups_to_be_deleted['<strong>' . $post->post_title . '</strong>'] = $post->ID;
                }
            }
            if ( !empty( $groups_to_be_deleted ) ) {
                $form['title-groups-deleted'] = array(
                    '#type' => 'markup',
                    '#markup' => '<h2>' . __( 'Groups to be deleted', 'wpcf' ) . '</h2>',
                );
                $form['user-groups-deleted'] = array(
                    '#type' => 'checkboxes',
                    '#name' => 'user-groups-to-be-deleted',
                    '#options' => $groups_to_be_deleted,
                );
            }
        }
    }

    // Check user fields
    if ( !empty( $data->user_fields ) ) {
        $form['user-title-fields'] = array(
            '#type' => 'markup',
            '#markup' => '<h2>' . __( 'User fields to be added/updated', 'wpcf' ) . '</h2>',
        );
        $fields_existing = wpcf_admin_fields_get_fields( false, false, false, 'wpcf-usermeta' );
        $fields_check = array();
        $fields_to_be_deleted = array();
        foreach ( $data->user_fields->field as $field ) {
            $field = (array) $field;
            if ( empty( $field['id'] ) || empty( $field['name'] ) ) {
                continue;
            }
            $form['user-field-add-' . $field['id']] = array(
                '#type' => 'checkbox',
                '#name' => 'user_fields[' . $field['id'] . '][add]',
                '#default_value' => true,
                '#title' => '<strong>' . $field['name'] . '</strong>',
                '#inline' => true,
                '#after' => '<br />',
            );
            $fields_check[] = $field['id'];
        }

        foreach ( $fields_existing as $field_id => $field ) {
            if ( !in_array( $field_id, $fields_check ) ) {
                $fields_to_be_deleted['<strong>' . $field['name'] . '</strong>'] = $field['id'];
            }
        }

        if ( !empty( $fields_to_be_deleted ) ) {
            $form['user-title-fields-deleted'] = array(
                '#type' => 'markup',
                '#markup' => '<h2>' . __( 'Fields to be deleted', 'wpcf' ) . '</h2>',
            );
            $form['user-fields-deleted'] = array(
                '#type' => 'checkboxes',
                '#name' => 'user-fields-to-be-deleted',
                '#options' => $fields_to_be_deleted,
            );
        }
    }

    // Check types
    if ( !empty( $data->types ) ) {
        $form['title-types'] = array(
            '#type' => 'markup',
            '#markup' => '<h2>' . __( 'Custom post types to be added/updated',
                    'wpcf' ) . '</h2>',
        );
        $types_existing = get_option( 'wpcf-custom-types', array() );
        $types_check = array();
        $types_to_be_deleted = array();
        foreach ( $data->types->type as $type ) {
            $type = (array) $type;
            $form['type-add-' . $type['id']] = array(
                '#type' => 'checkbox',
                '#name' => 'types[' . $type['id'] . '][add]',
                '#default_value' => true,
                '#title' => '<strong>' . $type['labels']->name . '</strong>',
                '#inline' => true,
                '#after' => '<br />',
            );
            $types_check[] = $type['id'];
        }

        foreach ( $types_existing as $type_id => $type ) {
            if ( !in_array( $type_id, $types_check ) ) {
                $types_to_be_deleted['<strong>' . $type['labels']['name'] . '</strong>'] = $type_id;
            }
        }

        if ( !empty( $types_to_be_deleted ) ) {
            $form['title-types-deleted'] = array(
                '#type' => 'markup',
                '#markup' => '<h2>' . __( 'Custom post types to be deleted',
                        'wpcf' ) . '</h2>',
            );
            $form['types-deleted'] = array(
                '#type' => 'checkboxes',
                '#name' => 'types-to-be-deleted',
                '#options' => $types_to_be_deleted,
            );
        }
    }

    // Check taxonomies
    if ( !empty( $data->taxonomies ) ) {
        $form['title-tax'] = array(
            '#type' => 'markup',
            '#markup' => '<h2>' . __( 'Custom taxonomies to be added/updated',
                    'wpcf' ) . '</h2>',
        );
        $taxonomies_existing = get_option( 'wpcf-custom-taxonomies', array() );
        $taxonomies_check = array();
        $taxonomies_to_be_deleted = array();
        foreach ( $data->taxonomies->taxonomy as $taxonomy ) {
            $taxonomy = (array) $taxonomy;
            $form['taxonomy-add-' . $taxonomy['id']] = array(
                '#type' => 'checkbox',
                '#name' => 'taxonomies[' . $taxonomy['id'] . '][add]',
                '#default_value' => true,
                '#title' => '<strong>' . $taxonomy['labels']->name . '</strong>',
                '#inline' => true,
                '#after' => '<br />',
            );
            $taxonomies_check[] = $taxonomy['id'];
        }

        foreach ( $taxonomies_existing as $taxonomy_id => $taxonomy ) {
            if ( !in_array( $taxonomy_id, $taxonomies_check ) ) {
                $taxonomies_to_be_deleted['<strong>' . $taxonomy['labels']['name'] . '</strong>'] = $taxonomy_id;
            }
        }

        if ( !empty( $taxonomies_to_be_deleted ) ) {
            $form['title-taxonomies-deleted'] = array(
                '#type' => 'markup',
                '#markup' => '<h2>' . __( 'Custom taxonomies to be deleted',
                        'wpcf' ) . '</h2>',
            );
            $form['taxonomies-deleted'] = array(
                '#type' => 'checkboxes',
                '#name' => 'taxonomies-to-be-deleted',
                '#options' => $taxonomies_to_be_deleted,
            );
        }
    }

    // Check post relationships
    if ( !empty( $data->post_relationships ) ) {
        $form['title-post-relationships'] = array(
            '#type' => 'markup',
            '#markup' => '<h2>' . __( 'Post relationship', 'wpcf' ) . '</h2>',
        );
        $form['pr-add'] = array(
            '#type' => 'checkbox',
            '#name' => 'post_relationship',
            '#default_value' => true,
            '#title' => '<strong>' . __( 'Create relationships', 'wpcf' ) . '</strong>',
            '#inline' => true,
            '#after' => '<br />',
        );
    }

    return $form;
}

/**
 * Exports data to XML.
 */
function wpcf_admin_export_data($download = true)
{
    /**
     *
     * Since Types 1.2
     * Merged function with Module Manager
     * /embedded/includes/module-manager.php
     * wpcf_admin_export_selected_data( array $items, $_type = 'all', $return = 'download' )
     *
     */
    $return = $download ? 'download' : 'xml';
    return wpcf_admin_export_selected_data( array(), 'all', $return );
}

/**
 * Check upload dir.
 *
 * @return type
 */
function wpcf_admin_import_dir()
{
    $dir = get_temp_dir();
    return is_writable( $dir );
}
