<?php
/*
 * Fields and groups form functions.
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/includes/fields-form.php $
 * $LastChangedDate: 2015-03-25 12:38:40 +0000 (Wed, 25 Mar 2015) $
 * $LastChangedRevision: 1120400 $
 * $LastChangedBy: iworks $
 *
 */
require_once WPCF_EMBEDDED_ABSPATH . '/classes/validate.php';
require_once WPCF_ABSPATH . '/includes/conditional-display.php';

global $wp_version;
$wpcf_button_style = '';
$wpcf_button_style30 = '';

if ( version_compare( $wp_version, '3.5', '<' ) ) {
    $wpcf_button_style = 'style="line-height: 35px;"';
    $wpcf_button_style30 = 'style="line-height: 30px;"';
}

/**
 * Saves fields and groups.
 *
 * If field name is changed in specific group - new one will be created,
 * otherwise old one will be updated and will appear in that way in other grups.
 *
 * @return type
 */
function wpcf_admin_save_fields_groups_submit( $form )
{
    if (
           !isset( $_POST['wpcf'] )
        || !isset( $_POST['wpcf']['group'] )
        || !isset( $_POST['wpcf']['group']['name'] )
    ) {
        return false;
    }
    // @todo maybe sanitize_text_field this too
    $_POST['wpcf']['group']['name'] = trim(strip_tags($_POST['wpcf']['group']['name']));

    $_POST['wpcf']['group'] = apply_filters( 'wpcf_group_pre_save', $_POST['wpcf']['group'] );

    if ( empty($_POST['wpcf']['group']['name']) ) {
        $form->triggerError();
        wpcf_admin_message( __( 'Group name can not be empty.', 'wpcf' ), 'error');
        return $form;
    }

    $new_group = false;

    $group_slug = $_POST['wpcf']['group']['slug'] = sanitize_title( $_POST['wpcf']['group']['name'] );

    // Basic check
    if ( isset( $_REQUEST['group_id'] ) ) {
        // Check if group exists
        $post = get_post( intval($_REQUEST['group_id']) );
        // Name changed
        if ( strtolower( $_POST['wpcf']['group']['name'] ) != strtolower( $post->post_title ) ) {
            // Check if already exists
            $exists = get_page_by_title( $_POST['wpcf']['group']['name'], 'OBJECT', 'wp-types-group' );
            if ( !empty( $exists ) ) {
                $form->triggerError();
                wpcf_admin_message(
                    sprintf(
                        __( "A group by name <em>%s</em> already exists. Please use a different name and save again.", 'wpcf' ),
                        htmlspecialchars($_POST['wpcf']['group']['name'])
                    ),
                    'error'
                );
                return $form;
            }
        }
        if ( empty( $post ) || $post->post_type != 'wp-types-group' ) {
            $form->triggerError();
            wpcf_admin_message(
                sprintf( __( "Wrong group ID %d", 'wpcf' ), intval( $_REQUEST['group_id'] ) ),
                'error'
            );
            return $form;
        }
        $group_id = $post->ID;
    } else {
        $new_group = true;
        // Check if already exists
        $exists = get_page_by_title( $_POST['wpcf']['group']['name'], 'OBJECT', 'wp-types-group' );
        if ( !empty( $exists ) ) {
            $form->triggerError();
            wpcf_admin_message(
                sprintf(
                    __( "A group by name <em>%s</em> already exists. Please use a different name and save again.",                                    'wpcf' ),
                    htmlspecialchars($_POST['wpcf']['group']['name'])
                ),
                'error'
            );
            return $form;
        }
    }

    // Save fields for future use
    $fields = array();
    if ( !empty( $_POST['wpcf']['fields'] ) ) {
        // Before anything - search unallowed characters
        foreach ( $_POST['wpcf']['fields'] as $key => $field ) {
            if ( (empty( $field['slug'] ) && preg_match( '#[^a-zA-Z0-9\s\_\-]#',
                            $field['name'] ))
                    || (!empty( $field['slug'] ) && preg_match( '#[^a-zA-Z0-9\s\_\-]#',
                            $field['slug'] )) ) {
                $form->triggerError();
                wpcf_admin_message( sprintf( __( 'Field slugs cannot contain non-English characters. Please edit this field name %s and save again.',
                                        'wpcf' ), $field['name'] ), 'error' );
                return $form;
            }
            if ( (!empty( $field['name'] ) && is_numeric($field['name'] ))
                    || (!empty( $field['slug'] ) && is_numeric($field['slug'] )) ) {
                $form->triggerError();
                wpcf_admin_message( sprintf( __( 'Field names or slugs cannot contain only numbers.',
                                        'wpcf' ), $field['name'] ), 'error' );
                return $form;
            }
        }
        // First check all fields
        foreach ( $_POST['wpcf']['fields'] as $key => $field ) {
            $field = apply_filters( 'wpcf_field_pre_save', $field );
            if ( !empty( $field['is_new'] ) ) {
                // Check name and slug
                if ( wpcf_types_cf_under_control( 'check_exists',
                                sanitize_title( $field['name'] ) ) ) {
                    $form->triggerError();
                    wpcf_admin_message( sprintf( __( 'Field with name "%s" already exists',
                                            'wpcf' ), $field['name'] ), 'error' );
                    return $form;
                }
                if ( isset( $field['slug'] ) && wpcf_types_cf_under_control( 'check_exists',
                                sanitize_title( $field['slug'] ) ) ) {
                    $form->triggerError();
                    wpcf_admin_message( sprintf( __( 'Field with slug "%s" already exists',
                                            'wpcf' ), $field['slug'] ), 'error' );
                    return $form;
                }
            }
            // Field ID and slug are same thing
            $field_id = wpcf_admin_fields_save_field( $field );
            if ( is_wp_error( $field_id ) ) {
                $form->triggerError();
                wpcf_admin_message( $field_id->get_error_message(), 'error' );
                return $form;
            }
            if ( !empty( $field_id ) ) {
                $fields[] = $field_id;
            }
            // WPML
            if ( function_exists( 'wpml_cf_translation_preferences_store' ) ) {
                $wpml_save_cf = wpml_cf_translation_preferences_store( $key,
                        wpcf_types_get_meta_prefix( wpcf_admin_fields_get_field( $field_id ) ) . $field_id );
            }
        }
    }

    // Save group
    $post_types = isset( $_POST['wpcf']['group']['supports'] ) ? $_POST['wpcf']['group']['supports'] : array();
    $taxonomies_post = isset( $_POST['wpcf']['group']['taxonomies'] ) ? $_POST['wpcf']['group']['taxonomies'] : array();
    $admin_style = $_POST['wpcf']['group']['admin_styles'];
    $terms = array();
    foreach ( $taxonomies_post as $taxonomy ) {
        foreach ( $taxonomy as $tax => $term ) {
            $terms[] = $term;
        }
    }
    // Rename if needed
    if ( isset( $_REQUEST['group_id'] ) ) {
        $_POST['wpcf']['group']['id'] = intval($_REQUEST['group_id']);
    }

    $group_id = wpcf_admin_fields_save_group( $_POST['wpcf']['group'] );
    $_POST['wpcf']['group']['id'] = $group_id;

    // Set open fieldsets
    if ( $new_group && !empty( $group_id ) ) {
        $open_fieldsets = get_user_meta( get_current_user_id(),
                'wpcf-group-form-toggle', true );
        if ( isset( $open_fieldsets[-1] ) ) {
            $open_fieldsets[$group_id] = $open_fieldsets[-1];
            unset( $open_fieldsets[-1] );
            update_user_meta( get_current_user_id(), 'wpcf-group-form-toggle',
                    $open_fieldsets );
        }
    }

    // Rest of processes
    if ( !empty( $group_id ) ) {
        wpcf_admin_fields_save_group_fields( $group_id, $fields );
        wpcf_admin_fields_save_group_post_types( $group_id, $post_types );
        wpcf_admin_fields_save_group_terms( $group_id, $terms );
        wpcf_admin_fields_save_group_admin_styles( $group_id, $admin_style );
        if ( empty( $_POST['wpcf']['group']['templates'] ) ) {
            $_POST['wpcf']['group']['templates'] = array();
        }
        wpcf_admin_fields_save_group_templates( $group_id,
                $_POST['wpcf']['group']['templates'] );
        $_POST['wpcf']['group']['fields'] = isset( $_POST['wpcf']['fields'] ) ? $_POST['wpcf']['fields'] : array();
        do_action( 'wpcf_save_group', $_POST['wpcf']['group'] );
        wpcf_admin_message_store( apply_filters( 'types_message_custom_fields_saved',
                        __( 'Group saved', 'wpcf' ),
                        $_POST['wpcf']['group']['name'],
                        $new_group ? false : true  ), 'custom' );
        wp_redirect( admin_url( 'admin.php?page=wpcf-edit&group_id=' . $group_id ) );
        die();
    } else {
        wpcf_admin_message_store( __( 'Error saving group', 'wpcf' ), 'error' );
    }
}

/**
 * Generates form data.
 */
function wpcf_admin_fields_form() {
    wpcf_admin_add_js_settings( 'wpcf_nonce_toggle_group',
            '\'' . wp_create_nonce( 'group_form_collapsed' ) . '\'' );
    wpcf_admin_add_js_settings( 'wpcf_nonce_toggle_fieldset',
            '\'' . wp_create_nonce( 'form_fieldset_toggle' ) . '\'' );
    $default = array();

    global $wpcf_button_style;
    global $wpcf_button_style30;

    global $wpcf;

    // If it's update, get data
    $update = false;
    if ( isset( $_REQUEST['group_id'] ) ) {
        $update = wpcf_admin_fields_get_group( intval( $_REQUEST['group_id'] ) );
        if ( empty( $update ) ) {
            $update = false;
            wpcf_admin_message( sprintf( __( "Group with ID %d do not exist",
                                    'wpcf' ), intval( $_REQUEST['group_id'] ) ) );
        } else {
            $update['fields'] = wpcf_admin_fields_get_fields_by_group( sanitize_text_field( $_REQUEST['group_id'] ), 'slug', false, true );
            $update['post_types'] = wpcf_admin_get_post_types_by_group( sanitize_text_field( $_REQUEST['group_id'] ) );
            $update['taxonomies'] = wpcf_admin_get_taxonomies_by_group( sanitize_text_field( $_REQUEST['group_id'] ) );
            $update['templates'] = wpcf_admin_get_templates_by_group( sanitize_text_field( $_REQUEST['group_id'] ) );
            $update['admin_styles'] = wpcf_admin_get_groups_admin_styles_by_group( sanitize_text_field( $_REQUEST['group_id'] ) );
        }
    }

    $form = array();
    $form['#form']['callback'] = array('wpcf_admin_save_fields_groups_submit');

    // Form sidebars

    $form['open-sidebar'] = array(
        '#type' => 'markup',
        '#markup' => '<div class="wpcf-form-fields-align-right">',
    );
    // Set help icon
    $form['help-icon'] = array(
        '#type' => 'markup',
        '#markup' => '<div class="wpcf-admin-fields-help"><img src="' . WPCF_EMBEDDED_RELPATH
        . '/common/res/images/question.png" style="position:relative;top:2px;" />&nbsp;<a href="http://wp-types.com/documentation/user-guides/using-custom-fields/?utm_source=typesplugin&utm_medium=help&utm_term=fields-help&utm_content=fields-editor&utm_campaign=types" target="_blank">'
        . __( 'Custom fields help', 'wpcf' ) . '</a></div>',
    );
    $form['submit2'] = array(
        '#type' => 'submit',
        '#name' => 'save',
        '#value' => __( 'Save', 'wpcf' ),
        '#attributes' => array('class' => 'button-primary wpcf-disabled-on-submit'),
    );
    $form['fields'] = array(
        '#type' => 'fieldset',
        '#title' => __( 'Available fields', 'wpcf' ),
    );

    // Get field types
    $fields_registered = wpcf_admin_fields_get_available_types();
    foreach ( $fields_registered as $filename => $data ) {
        $form['fields'][basename( $filename, '.php' )] = array(
            '#type' => 'markup',
            '#markup' => '<a href="' . admin_url( 'admin-ajax.php'
                    . '?action=wpcf_ajax&amp;wpcf_action=fields_insert'
                    . '&amp;field=' . basename( $filename, '.php' )
                    . '&amp;page=wpcf-edit' )
            . '&amp;_wpnonce=' . wp_create_nonce( 'fields_insert' ) . '" '
            . 'class="wpcf-fields-add-ajax-link button-secondary">' . $data['title'] . '</a> ',
        );
        // Process JS
        if ( !empty( $data['group_form_js'] ) ) {
            foreach ( $data['group_form_js'] as $handle => $script ) {
                if ( isset( $script['inline'] ) ) {
                    add_action( 'admin_footer', $script['inline'] );
                    continue;
                }
                $deps = !empty( $script['deps'] ) ? $script['deps'] : array();
                $in_footer = !empty( $script['in_footer'] ) ? $script['in_footer'] : false;
                wp_register_script( $handle, $script['src'], $deps,
                        WPCF_VERSION, $in_footer );
                wp_enqueue_script( $handle );
            }
        }

        // Process CSS
        if ( !empty( $data['group_form_css'] ) ) {
            foreach ( $data['group_form_css'] as $handle => $script ) {
                if ( isset( $script['src'] ) ) {
                    $deps = !empty( $script['deps'] ) ? $script['deps'] : array();
                    wp_enqueue_style( $handle, $script['src'], $deps,
                            WPCF_VERSION );
                } else if ( isset( $script['inline'] ) ) {
                    add_action( 'admin_head', $script['inline'] );
                }
            }
        }
    }

    // Get fields created by user
    $fields = wpcf_admin_fields_get_fields( true, true );
    if ( !empty( $fields ) ) {
        $form['fields-existing'] = array(
            '#type' => 'fieldset',
            '#title' => __( 'User created fields', 'wpcf' ),
            '#id' => 'wpcf-form-groups-user-fields',
        );
        foreach ( $fields as $key => $field ) {
            if ( isset( $update['fields'] ) && array_key_exists( $key,
                            $update['fields'] ) ) {
                continue;
            }
            if ( !empty( $field['data']['removed_from_history'] ) ) {
                continue;
            }
            $form['fields-existing'][$key] = array(
                '#type' => 'markup',
                '#markup' => '<div id="wpcf-user-created-fields-wrapper-' . $field['id'] . '" style="float:left; margin-right: 10px;"><a href="' . admin_url( 'admin-ajax.php'
                        . '?action=wpcf_ajax'
                        . '&amp;wpcf_action=fields_insert_existing'
                        . '&amp;page=wpcf-edit'
                        . '&amp;field=' . $field['id'] ) . '&amp;_wpnonce='
                . wp_create_nonce( 'fields_insert_existing' ) . '" '
                . 'class="wpcf-fields-add-ajax-link button-secondary" onclick="jQuery(this).parent().fadeOut();" '
                . ' data-slug="' . $field['id'] . '">'
                . htmlspecialchars( stripslashes( $field['name'] ) ) . '</a>'
                . '<a href="' . admin_url( 'admin-ajax.php'
                        . '?action=wpcf_ajax'
                        . '&amp;wpcf_action=remove_from_history'
                        . '&amp;field_id=' . $field['id'] ) . '&amp;_wpnonce='
                . wp_create_nonce( 'remove_from_history' ) . '&amp;wpcf_warning='
                . sprintf( __( 'Are you sure that you want to remove field %s from history?',
                                'wpcf' ),
                        htmlspecialchars( stripslashes( $field['name'] ) ) )
                . '&amp;wpcf_ajax_update=wpcf-user-created-fields-wrapper-'
                . $field['id'] . '" title="'
                . sprintf( __( 'Remove field %s', 'wpcf' ),
                        htmlspecialchars( stripslashes( $field['name'] ) ) )
                . '" class="wpcf-ajax-link"><img src="'
                . WPCF_RES_RELPATH
                . '/images/delete-2.png" style="postion:absolute;margin-top:5px;margin-left:-4px;" /></a></div>',
            );
        }
    }
    $form['close-sidebar'] = array(
        '#type' => 'markup',
        '#markup' => '</div>',
    );

    // Group data

    $form['open-main'] = array(
        '#type' => 'markup',
        '#markup' => '<div id="wpcf-form-fields-main">',
    );

    $form['title'] = array(
        '#type' => 'textfield',
        '#name' => 'wpcf[group][name]',
        '#id' => 'wpcf-group-name',
        '#value' => $update ? $update['name']:'',
        '#inline' => true,
        '#attributes' => array(
            'style' => 'width:100%;margin-bottom:10px;',
            'placeholder' => __( 'Enter group title', 'wpcf' ),
        ),
        '#validate' => array(
            'required' => array(
                'value' => true,
            ),
        )
    );
    $form['description'] = array(
        '#type' => 'textarea',
        '#id' => 'wpcf-group-description',
        '#name' => 'wpcf[group][description]',
        '#value' => $update ? $update['description']:'',
        '#attributes' => array(
            'placeholder' =>  __( 'Enter a description for this group', 'wpcf' ),
        ),
    );

    /**
     *
     * FILTER BOX
     * Since Types 1.2 we moved JS to /embedded/resources/js/custom-fields-form-filter.js
     *
     */
    // Support post types and taxonomies

    $post_types = get_post_types( '', 'objects' );
    $options = array();
    $post_types_currently_supported = array();
    $form_types = array();

    foreach ( $post_types as $post_type_slug => $post_type ) {
        if ( in_array( $post_type_slug, $wpcf->excluded_post_types )
                || !$post_type->show_ui ) {
            continue;
        }
        $options[$post_type_slug]['#name'] = 'wpcf[group][supports][' . $post_type_slug . ']';
        $options[$post_type_slug]['#title'] = $post_type->label;
        $options[$post_type_slug]['#default_value'] = ($update && !empty( $update['post_types'] ) && in_array( $post_type_slug, $update['post_types'] )) ? 1 : 0;
        $options[$post_type_slug]['#value'] = $post_type_slug;
        $options[$post_type_slug]['#inline'] = TRUE;
        $options[$post_type_slug]['#suffix'] = '<br />';
        $options[$post_type_slug]['#id'] = 'wpcf-form-groups-support-post-type-' . $post_type_slug;
        $options[$post_type_slug]['#attributes'] = array('class' => 'wpcf-form-groups-support-post-type');
        if ( $update && !empty( $update['post_types'] ) && in_array( $post_type_slug, $update['post_types'] ) ) {
            $post_types_currently_supported[] = $post_type->label;
        }
    }

    if ( empty( $post_types_currently_supported ) ) {
        $post_types_currently_supported[] = __( 'Displayed on all content types', 'wpcf' );
    }


    /**
     * POST TYPE FILTER
     */
    $temp = array(
        '#type' => 'checkboxes',
        '#options' => $options,
        '#name' => 'wpcf[group][supports]',
        '#inline' => true,
    );
    /**
     * Here we use unique function for all filters
     * Since Types 1.2
     */
    $form_types = _wpcf_filter_wrap( 'custom_post_types',
            __( 'Post Types:', 'wpcf' ),
            implode( ',', $post_types_currently_supported ),
            __( 'Displayed on all content types', 'wpcf' ), $temp );

    /**
     * TAXONOMIES FILTER QUERY
     */
    $taxonomies = apply_filters( 'wpcf_group_form_filter_taxonomies', get_taxonomies( '', 'objects' ) );
    $options = array();
    $tax_currently_supported = array();
    $form_tax = array();
    $form_tax_single = array();


    /**
     * Filter toxonomies
     */
    foreach ( $taxonomies as $category_slug => $category ) {
        if ( $category_slug == 'nav_menu' || $category_slug == 'link_category'
                || $category_slug == 'post_format' ) {
            continue;
        }
        $terms = apply_filters( 'wpcf_group_form_filter_terms',
                get_terms( $category_slug, array('hide_empty' => false) ) );
        if ( !empty( $terms ) ) {
            $options = array();
            $add_title = '<div class="taxonomy-title">' . $category->labels->name . '</div>';
            $title = '';

            foreach ( $terms as $term ) {
                $checked = 0;
                if ( $update && !empty( $update['taxonomies'] )
                        && array_key_exists( $category_slug,
                                $update['taxonomies'] ) ) {
                    if ( array_key_exists( $term->term_taxonomy_id,
                                    $update['taxonomies'][$category_slug] ) ) {
                        $checked = 1;
                        $tax_currently_supported[$term->term_taxonomy_id] = $term->name;
                        $title = '';
                    }
                }
                $options[$term->term_taxonomy_id]['#name'] = 'wpcf[group][taxonomies]['
                        . $category_slug . '][' . $term->term_taxonomy_id . ']';
                $options[$term->term_taxonomy_id]['#title'] = $term->name;
                $options[$term->term_taxonomy_id]['#default_value'] = $checked;
                $options[$term->term_taxonomy_id]['#value'] = $term->term_taxonomy_id;
                $options[$term->term_taxonomy_id]['#inline'] = true;
                $options[$term->term_taxonomy_id]['#prefix'] = $add_title;
                $options[$term->term_taxonomy_id]['#suffix'] = '<br />';
                $options[$term->term_taxonomy_id]['#id'] = 'wpcf-form-groups-support-tax-' . $term->term_taxonomy_id;
                $options[$term->term_taxonomy_id]['#attributes'] = array('class' => 'wpcf-form-groups-support-tax');
                $add_title = '';
            }
            $form_tax_single['taxonomies-' . $category_slug] = array(
                '#type' => 'checkboxes',
                '#options' => $options,
                '#name' => 'wpcf[group][taxonomies][' . $category_slug . ']',
                '#suffix' => '<br />',
                '#inline' => true,
            );
        }
    }

    if ( empty( $tax_currently_supported ) ) {
        $tax_currently_supported[] = __( 'Not Selected', 'wpcf' );
    }

    /**
     * Since Types 1.2 we use unique function
     */
    $form_tax = _wpcf_filter_wrap( 'custom_taxonomies', __( 'Terms:', 'wpcf' ),
            implode( ', ', array_values( $tax_currently_supported ) ),
            __( 'Not Selected', 'wpcf' ), $form_tax_single );



    /**
     * TEMPLATES
     */
    // Choose templates
    $templates = get_page_templates();
    $templates_views = get_posts( 'post_type=view-template&numberposts=-1&status=publish' );

    $options = array();
    $options['default-template'] = array(
        '#title' => __( 'Default Template' ),
        '#default_value' => !empty( $update['templates'] ) && in_array( 'default', $update['templates'] ),
        '#name' => 'wpcf[group][templates][]',
        '#value' => 'default',
        '#inline' => true,
        '#after' => '<br />',
    );
    foreach ( $templates as $template_name => $template_filename ) {
        $options[$template_filename] = array(
            '#title' => $template_name,
            '#default_value' => !empty( $update['templates'] ) && in_array( $template_filename, $update['templates'] ),
            '#name' => 'wpcf[group][templates][]',
            '#value' => $template_filename,
            '#inline' => true,
            '#after' => '<br />',
        );
    }
    foreach ( $templates_views as $template_view ) {
        $options[$template_view->post_name] = array(
            '#title' => 'View Template ' . $template_view->post_title,
            '#default_value' => !empty( $update['templates'] ) && in_array( $template_view->ID, $update['templates'] ),
            '#name' => 'wpcf[group][templates][]',
            '#value' => $template_view->ID,
            '#inline' => true,
            '#after' => '<br />',
        );
        $templates_view_list_text[$template_view->ID] = $template_view->post_title;
    }
    $text = '';
    if ( !empty( $update['templates'] ) ) {
        $text = array();
        $templates = array_flip( $templates );
        foreach ( $update['templates'] as $template ) {
            if ( $template == 'default' ) {
                $template = __( 'Default Template' );
            } else if ( strpos( $template, '.php' ) !== false ) {
                $template = $templates[$template];
            } else {
                $template = 'View Template ' . $templates_view_list_text[$template];
            }
            $text[] = $template;
        }
        $text = implode( ', ', $text );
    } else {
        $text = __( 'Not Selected', 'wpcf' );
    }

    // Add class
    foreach ( $options as $_k => $_option ) {
        $options[$_k]['#attributes'] = array('class' => 'wpcf-form-groups-support-templates');
    }

    $form_templates = array(
        '#type' => 'checkboxes',
        '#name' => 'wpcf[group][templates]',
        '#options' => $options,
        '#inline' => true,
    );

    /**
     * Since Types 1.2 we use unique function
     */
    $form_templates = _wpcf_filter_wrap(
        'templates',
        __( 'Templates:', 'wpcf' ),
        $text,
        __( 'Not Selected', 'wpcf' ),
        $form_templates
    );

    /**
     * Now starting form
     */
    $form['supports-table-open'] = array(
        '#type' => 'markup',
        '#markup' => '<table class="widefat"><thead><tr><th>'
        . __( 'Where to display this group', 'wpcf' )
        . '</th></tr></thead><tbody><tr><td>'
        . '<p>'
        . __( 'Each custom fields group can display on different content types or different taxonomy.',
                'wpcf' )
        . '</p>',
    );

    /**
     * Join filter forms
     */
    // Types
    $form['p_wrap_1_' . wpcf_unique_id( serialize( $form_types ) )] = array(
        '#type' => 'markup',
        '#markup' => '<p class="wpcf-filter-wrap">',
    );
    $form = $form + $form_types;

    // Terms
    $form['p_wrap_2_' . wpcf_unique_id( serialize( $form_tax ) )] = array(
        '#type' => 'markup',
        '#markup' => '</p><p class="wpcf-filter-wrap">',
    );
    $form = $form + $form_tax;

    // Templates
    $form['p_wrap_3_' . wpcf_unique_id( serialize( $form_templates ) )] = array(
        '#type' => 'markup',
        '#markup' => '</p><p class="wpcf-filter-wrap">',
    );
    $form = $form + $form_templates;
    $form['p_wrap_4_' . wpcf_unique_id( serialize( $form_templates ) )] = array(
        '#type' => 'markup',
        '#markup' => '</p>',
    );

    /**
     * TODO Code from now on should be revised
     */

    $count = 0;
    $count +=!empty( $update['post_types'] ) ? 1 : 0;
    $count +=!empty( $update['taxonomies'] ) ? 1 : 0;
    $count +=!empty( $update['templates'] ) ? 1 : 0;
    $display = $count > 1 ? '' : ' style="display:none;"';
    $form['filters_association'] = array(
        '#type' => 'radios',
        '#name' => 'wpcf[group][filters_association]',
        '#id' => 'wpcf-fields-form-filters-association',
        '#options' => array(
            __( 'Display this group when ANY of the above conditions is met',
                    'wpcf' ) => 'any',
            __( 'Display this group when ALL the above conditions is met',
                    'wpcf' ) => 'all',
        ),
        '#default_value' => !empty( $update['filters_association'] ) ? $update['filters_association'] : 'any',
        '#inline' => true,
        '#before' => '<div id="wpcf-fields-form-filters-association-form"' . $display . '>',
        '#after' => '<div id="wpcf-fields-form-filters-association-summary" style="margin-top:10px;font-style:italic;margin-bottom:15px;"></div></div>',
    );
    wpcf_admin_add_js_settings( 'wpcf_filters_association_or',
            '\'' . __( 'This group will appear on %pt% edit pages where content belongs to taxonomy: %tx% or View Template is: %vt%',
                    'wpcf' ) . '\'' );
    wpcf_admin_add_js_settings( 'wpcf_filters_association_and',
            '\'' . __( 'This group will appear on %pt% edit pages where content belongs to taxonomy: %tx% and View Template is: %vt%',
                    'wpcf' ) . '\'' );
    wpcf_admin_add_js_settings( 'wpcf_filters_association_all_pages',
            '\'' . __( 'all', 'wpcf' ) . '\'' );
    wpcf_admin_add_js_settings( 'wpcf_filters_association_all_taxonomies',
            '\'' . __( 'any', 'wpcf' ) . '\'' );
    wpcf_admin_add_js_settings( 'wpcf_filters_association_all_templates',
            '\'' . __( 'any', 'wpcf' ) . '\'' );

    $additional_filters = apply_filters( 'wpcf_fields_form_additional_filters', array(), $update );
    $form = $form + $additional_filters;

    $form['supports-table-close'] = array(
        '#type' => 'markup',
        '#markup' => '</td></tr></tbody></table><br />',
    );

    /** Admin styles* */
    $form['adminstyles-table-open'] = array(
        '#type' => 'markup',
        '#markup' => '<table class="widefat" id="wpcf-admin-styles-box"><thead><tr><th>'
        . __( 'Styling Editor', 'wpcf' )
        . '</th></tr></thead><tbody><tr><td>'
        . '<p>'
        . __( 'Customize Fields for admin panel.', 'wpcf' )
        . '</p>',
    );

    $admin_styles_value = $preview_profile = $edit_profile = '';
    if ( isset( $update['admin_styles'] ) ) {
        $admin_styles_value = $update['admin_styles'];
    }
    $temp = '';

    if ( $update ) {
        require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
        require_once WPCF_EMBEDDED_INC_ABSPATH . '/usermeta.php';
        require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields-post.php';
        require_once WPCF_EMBEDDED_INC_ABSPATH . '/usermeta-post.php';
        //Get sample post
        $post = query_posts( 'posts_per_page=1' );


        if ( !empty( $post ) && count( $post ) != '' ) {
            $post = $post[0];
        }
        $preview_profile = wpcf_admin_post_meta_box_preview( $post, $update, 1 );
        $group = $update;
        $group['fields'] = wpcf_admin_post_process_fields( $post, $group['fields'], true, false );
        $edit_profile = wpcf_admin_post_meta_box( $post, $group, 1, true );
        add_action( 'admin_enqueue_scripts', 'wpcf_admin_fields_form_fix_styles', PHP_INT_MAX  );
    }

    $temp[] = array(
        '#type' => 'radio',
        '#suffix' => '<br />',
        '#value' => 'edit_mode',
        '#title' => 'Edit mode',
        '#name' => 'wpcf[group][preview]', '#default_value' => '',
        '#before' => '<div class="wpcf-admin-css-preview-style-edit">',
        '#inline' => true,
        '#attributes' => array('onclick' => 'changePreviewHtml(\'editmode\')', 'checked' => 'checked')
    );

    $temp[] = array(
        '#type' => 'radio',
        '#title' => 'Read Only',
        '#name' => 'wpcf[group][preview]', '#default_value' => '',
        '#after' => '</div>',
        '#inline' => true,
        '#attributes' => array('onclick' => 'changePreviewHtml(\'readonly\')')
    );

    $temp[] = array(
        '#type' => 'textarea',
        '#name' => 'wpcf[group][admin_html_preview]',
        '#inline' => true,
        '#id' => 'wpcf-form-groups-admin-html-preview',
        '#before' => '<h3>Field group HTML</h3>'
    );

    $temp[] = array(
        '#type' => 'textarea',
        '#name' => 'wpcf[group][admin_styles]',
        '#inline' => true,
        '#value' => $admin_styles_value,
        '#default_value' => '',
        '#id' => 'wpcf-form-groups-css-fields-editor',
        '#after' => '
        <div class="wpcf-update-preview-btn"><input type="button" value="Update preview" onclick="wpcfPreviewHtml()" style="float:right;" class="button-secondary"></div>
        <h3>Field group preview</h3>
        <div id="wpcf-update-preview-div">Preview here</div>
        <script type="text/javascript">
var wpcfReadOnly = ' . json_encode( $preview_profile ) . ';
var wpcfEditMode = ' . json_encode( $edit_profile ) . ';
var wpcfDefaultCss = ' . json_encode( $admin_styles_value ) . ';
        </script>
        ',
        '#before' => '<h3>Your CSS</h3>'
    );

    $admin_styles = _wpcf_filter_wrap( 'admin_styles',
            __( 'Admin styles for fields:', 'wpcf' ), '', '', $temp,
            __( 'Open style editor', 'wpcf' ) );
    $form['p_wrap_1_' . wpcf_unique_id( serialize( $admin_styles ) )] = array(
        '#type' => 'markup',
        '#markup' => '<p class="wpcf-filter-wrap">',
    );
    $form = $form + $admin_styles;
    $form['adminstyles-table-close'] = array(
        '#type' => 'markup',
        '#markup' => '</td></tr></tbody></table><br />',
    );
    /** End admin Styles * */
    // Group fields

    $form['fields_title'] = array(
        '#type' => 'markup',
        '#markup' => '<h2>' . __( 'Fields', 'wpcf' ) . '</h2>',
    );
    $show_under_title = true;

    $form['ajax-response-open'] = array(
        '#type' => 'markup',
        '#markup' => '<div id="wpcf-fields-sortable" class="ui-sortable">',
    );

    // If it's update, display existing fields
    $existing_fields = array();
    if ( $update && isset( $update['fields'] ) ) {
        foreach ( $update['fields'] as $slug => $field ) {
            $field['submitted_key'] = $slug;
            $field['group_id'] = $update['id'];
            $form_field = wpcf_fields_get_field_form_data( $field['type'],
                    $field );
            if ( is_array( $form_field ) ) {
                $form['draggable-open-' . rand()] = array(
                    '#type' => 'markup',
                    '#markup' => '<div class="ui-draggable">'
                );
                $form = $form + $form_field;
                $form['draggable-close-' . rand()] = array(
                    '#type' => 'markup',
                    '#markup' => '</div>'
                );
            }
            $existing_fields[] = $slug;
            $show_under_title = false;
        }
    }
    // Any new fields submitted but failed? (Don't double it)
    if ( !empty( $_POST['wpcf']['fields'] ) ) {
        foreach ( $_POST['wpcf']['fields'] as $key => $field ) {
            if ( in_array( $key, $existing_fields ) ) {
                continue;
            }
            $field['submitted_key'] = $key;
            $form_field = wpcf_fields_get_field_form_data( $field['type'],
                    $field );
            if ( is_array( $form_field ) ) {
                $form['draggable-open-' . rand()] = array(
                    '#type' => 'markup',
                    '#markup' => '<div class="ui-draggable">'
                );
                $form = $form + $form_field;
                $form['draggable-close-' . rand()] = array(
                    '#type' => 'markup',
                    '#markup' => '</div>'
                );
            }
        }
        $show_under_title = false;
    }
    $form['ajax-response-close'] = array(
        '#type' => 'markup',
        '#markup' => '</div>' . '<div id="wpcf-ajax-response"></div>',
    );

    if ( $show_under_title ) {
        $form['fields_title']['#markup'] = $form['fields_title']['#markup']
                . '<div id="wpcf-fields-under-title">'
                . __( 'There are no fields in this group. To add a field, click on the field buttons at the right.',
                        'wpcf' )
                . '</div>';
    }

    // If update, create ID field
    if ( $update ) {
        $form['group_id'] = array(
            '#type' => 'hidden',
            '#name' => 'group_id',
            '#value' => $update['id'],
            '#forced_value' => true,
        );
    }

    $form['submit'] = array(
        '#type' => 'submit',
        '#name' => 'save',
        '#value' => __( 'Save', 'wpcf' ),
        '#attributes' => array('class' => 'button-primary wpcf-disabled-on-submit'),
    );

    // Close main div
    $form['close-sidebar'] = array(
        '#type' => 'markup',
        '#markup' => '</div>',
    );

    $form = apply_filters( 'wpcf_form_fields', $form, $update );

    // Add JS settings
    wpcf_admin_add_js_settings( 'wpcfFormUniqueValuesCheckText',
            '\'' . __( 'Warning: same values selected', 'wpcf' ) . '\'' );
    wpcf_admin_add_js_settings( 'wpcfFormUniqueNamesCheckText',
            '\'' . __( 'Warning: field name already used', 'wpcf' ) . '\'' );
    wpcf_admin_add_js_settings( 'wpcfFormUniqueSlugsCheckText',
            '\'' . __( 'Warning: field slug already used', 'wpcf' ) . '\'' );

    wpcf_admin_add_js_settings( 'wpcfFormAlertOnlyPreview', sprintf( "'%s'", __( 'Sorry, but this is only preview!', 'wpcf' ) ) );

    return $form;
}

/**
 * Dynamically adds new field on AJAX call.
 *
 * @param type $form_data
 */
function wpcf_fields_insert_ajax( $form_data = array() ) {
    echo wpcf_fields_get_field_form( sanitize_text_field( $_GET['field'] ) );
}

/**
 * Dynamically adds existing field on AJAX call.
 *
 * @param type $form_data
 */
function wpcf_fields_insert_existing_ajax() {
    $field = wpcf_admin_fields_get_field( sanitize_text_field( $_GET['field'] ), false, true );
    if ( !empty( $field ) ) {
        echo wpcf_fields_get_field_form( $field['type'], $field );
    } else {
        echo '<div>' . __( "Requested field don't exist", 'wpcf' ) . '</div>';
    }
}

/**
 * Returns HTML formatted field form (draggable).
 *
 * @param type $type
 * @param type $form_data
 * @return type
 */
function wpcf_fields_get_field_form( $type, $form_data = array() ) {
    $form = wpcf_fields_get_field_form_data( $type, $form_data );
    if ( $form ) {
        $return = '<div class="ui-draggable">'
                . wpcf_form_simple( $form )
                . '</div>';

        /**
         * add extra condition check if this is checkbox
         */
        foreach( $form as $key => $value ) {
            if (
                !array_key_exists('value', $value )
                || !array_key_exists('#attributes', $value['value'] )
                || !array_key_exists('data-wpcf-type', $value['value']['#attributes'] )
                || 'checkbox' != $value['value']['#attributes']['data-wpcf-type']
            ) {
                continue;
            }
            echo '<script type="text/javascript">';
            printf('jQuery(document).ready(function($){wpcf_checkbox_value_zero(jQuery(\'[name="%s"]\'));});', $value['value']['#name'] );
            echo '</script>';
        }

        return $return;
    }
    return '<div>' . __( 'Wrong field requested', 'wpcf' ) . '</div>';
}

/**
 * Processes field form data.
 *
 * @param type $type
 * @param type $form_data
 * @return type
 */
function wpcf_fields_get_field_form_data( $type, $form_data = array() ) {

    // Get field type data
    $field_data = wpcf_fields_type_action( $type );

    if ( !empty( $field_data ) ) {
        $form = array();

        // Set right ID if existing field
        if ( isset( $form_data['submitted_key'] ) ) {
            $id = $form_data['submitted_key'];
        } else {
            $id = $type . '-' . rand();
        }

        // Sanitize
        $form_data = wpcf_sanitize_field( $form_data );

        // Set remove link
        $remove_link = isset( $form_data['group_id'] ) ? admin_url( 'admin-ajax.php?'
                        . 'wpcf_ajax_callback=wpcfFieldsFormDeleteElement&amp;wpcf_warning='
                        . __( 'Are you sure?', 'wpcf' )
                        . '&amp;action=wpcf_ajax&amp;wpcf_action=remove_field_from_group'
                        . '&amp;group_id=' . intval( $form_data['group_id'] )
                        . '&amp;field_id=' . $form_data['id'] )
                . '&amp;_wpnonce=' . wp_create_nonce( 'remove_field_from_group' ) : admin_url( 'admin-ajax.php?'
                        . 'wpcf_ajax_callback=wpcfFieldsFormDeleteElement&amp;wpcf_warning='
                        . __( 'Are you sure?', 'wpcf' )
                        . '&amp;action=wpcf_ajax&amp;wpcf_action=remove_field_from_group' )
                . '&amp;_wpnonce=' . wp_create_nonce( 'remove_field_from_group' );

        // Set move button
        $form['wpcf-' . $id . '-control'] = array(
            '#type' => 'markup',
            '#markup' => '<img src="' . WPCF_RES_RELPATH
            . '/images/move.png" class="wpcf-fields-form-move-field" alt="'
            . __( 'Move this field', 'wpcf' ) . '" /><a href="'
            . $remove_link . '" '
            . 'class="wpcf-form-fields-delete wpcf-ajax-link">'
            . '<img src="' . WPCF_RES_RELPATH . '/images/delete-2.png" alt="'
            . __( 'Delete this field', 'wpcf' ) . '" /></a>',
        );

        // Set fieldset

        $collapsed = wpcf_admin_fields_form_fieldset_is_collapsed( 'fieldset-' . $id );
        // Set collapsed on AJAX call (insert)
        $collapsed = defined( 'DOING_AJAX' ) ? false : $collapsed;

        // Set title
        $title = !empty( $form_data['name'] ) ? $form_data['name'] : __( 'Untitled',
                        'wpcf' );
        $title = '<span class="wpcf-legend-update">' . $title . '</span> - '
                . sprintf( __( '%s field', 'wpcf' ), $field_data['title'] );

        // Do not display on Usermeta Group edit screen
        if ( !isset( $_GET['page'] ) || $_GET['page'] != 'wpcf-edit-usermeta' ) {
            if ( !empty( $form_data['data']['conditional_display']['conditions'] ) ) {
                $title .= ' ' . __( '(conditional)', 'wpcf' );
            }
        }

        $form['wpcf-' . $id] = array(
            '#type' => 'fieldset',
            '#title' => $title,
            '#id' => 'fieldset-' . $id,
            '#collapsible' => true,
            '#collapsed' => $collapsed,
        );

        // Get init data
        $field_init_data = wpcf_fields_type_action( $type );

        // See if field inherits some other
        $inherited_field_data = false;
        if ( isset( $field_init_data['inherited_field_type'] ) ) {
            $inherited_field_data = wpcf_fields_type_action( $field_init_data['inherited_field_type'] );
        }

        $form_field = array();

        // Force name and description
        $form_field['name'] = array(
            '#type' => 'textfield',
            '#name' => 'name',
            '#attributes' => array(
                'class' => 'wpcf-forms-set-legend wpcf-forms-field-name',
                'style' => 'width:100%;margin:10px 0 10px 0;',
                'placeholder' => __( 'Enter field name', 'wpcf' ),
            ),
            '#validate' => array('required' => array('value' => true)),
            '#inline' => true,
        );
        $form_field['slug'] = array(
            '#type' => 'textfield',
            '#name' => 'slug',
            '#attributes' => array(
                'class' => 'wpcf-forms-field-slug',
                'style' => 'width:100%;margin:0 0 10px 0;',
                'maxlength' => 255,
                'placeholder' => __( 'Enter field slug', 'wpcf' ),
            ),
            '#validate' => array('nospecialchars' => array('value' => true)),
            '#inline' => true,
        );

        // If insert form callback is not provided, use generic form data
        if ( function_exists( 'wpcf_fields_' . $type . '_insert_form' ) ) {
            $form_field_temp = call_user_func( 'wpcf_fields_' . $type
                    . '_insert_form', $form_data,
                    'wpcf[fields]['
                    . $id . ']' );
            if ( is_array( $form_field_temp ) ) {
                unset( $form_field_temp['name'], $form_field_temp['slug'] );
                $form_field = $form_field + $form_field_temp;
            }
        }

        $form_field['description'] = array(
            '#type' => 'textarea',
            '#name' => 'description',
            '#attributes' => array(
                'rows' => 5,
                'cols' => 1,
                'style' => 'margin:0 0 10px 0;',
                'placeholder' => __( 'Describe this field', 'wpcf' ),
            ),
            '#inline' => true,
        );

        /**
         * add placeholder field
         */
            switch($type)
            {
            case 'audio':
            case 'colorpicker':
            case 'date':
            case 'email':
            case 'embed':
            case 'file':
            case 'image':
            case 'numeric':
            case 'phone':
            case 'skype':
            case 'textarea':
            case 'textfield':
            case 'url':
            case 'video':
                $form_field['placeholder'] = array(
                    '#type' => 'textfield',
                    '#name' => 'placeholder',
                    '#inline' => true,
                    '#title' => __( 'Placeholder', 'wpcf' ),
                    '#attributes' => array(
                        'style' => 'width:100%;margin:0 0 10px 0;',
                        'placeholder' =>  __('Enter placeholder', 'wpcf'),
                    ),
                );
                break;
            }

        if ( wpcf_admin_can_be_repetitive( $type ) ) {
            $temp_warning_message = '';
            $form_field['repetitive'] = array(
                '#type' => 'radios',
                '#name' => 'repetitive',
                '#title' => __( 'Single or repeating field?', 'wpcf' ),
                '#options' => array(
                    'repeat' => array(
                        '#title' => __( 'Allow multiple-instances of this field',
                                'wpcf' ),
                        '#value' => '1',
                        '#attributes' => array('onclick' => 'jQuery(this).parent().parent().find(\'.wpcf-cd-warning\').hide(); jQuery(this).parent().find(\'.wpcf-cd-repetitive-warning\').show();'),
                    ),
                    'norepeat' => array(
                        '#title' => __( 'This field can have only one value',
                                'wpcf' ),
                        '#value' => '0',
                        '#attributes' => array('onclick' => 'jQuery(this).parent().parent().find(\'.wpcf-cd-warning\').show(); jQuery(this).parent().find(\'.wpcf-cd-repetitive-warning\').hide();'),
                    ),
                ),
                '#default_value' => isset( $form_data['data']['repetitive'] ) ? $form_data['data']['repetitive'] : '0',
                '#after' => wpcf_admin_is_repetitive( $form_data ) ? '<div class="wpcf-message wpcf-cd-warning wpcf-error" style="display:none;"><p>' . __( "There may be multiple instances of this field already. When you switch back to single-field mode, all values of this field will be updated when it's edited.",
                                'wpcf' ) . '</p></div>' . $temp_warning_message : $temp_warning_message,
            );
        }

        // Process all form fields
        foreach ( $form_field as $k => $field ) {
            $form['wpcf-' . $id][$k] = $field;
            // Check if nested
            if ( isset( $field['#name'] ) && strpos( $field['#name'], '[' ) === false ) {
                $form['wpcf-' . $id][$k]['#name'] = 'wpcf[fields]['
                        . $id . '][' . $field['#name'] . ']';
            } else if ( isset( $field['#name'] ) ) {
                $form['wpcf-' . $id][$k]['#name'] = 'wpcf[fields]['
                        . $id . ']' . $field['#name'];
            }
            if ( !isset( $field['#id'] ) ) {
                $form['wpcf-' . $id][$k]['#id'] = $type . '-'
                        . $field['#type'] . '-' . rand();
            }
            if ( isset( $field['#name'] ) && isset( $form_data[$field['#name']] ) ) {
                $form['wpcf-'
                        . $id][$k]['#value'] = $form_data[$field['#name']];
                $form['wpcf-'
                        . $id][$k]['#default_value'] = $form_data[$field['#name']];
                // Check if it's in 'data'
            } else if ( isset( $field['#name'] ) && isset( $form_data['data'][$field['#name']] ) ) {
                $form['wpcf-'
                        . $id][$k]['#value'] = $form_data['data'][$field['#name']];
                $form['wpcf-'
                        . $id][$k]['#default_value'] = $form_data['data'][$field['#name']];
            }
        }

        // Set type
        $form['wpcf-' . $id]['type'] = array(
            '#type' => 'hidden',
            '#name' => 'wpcf[fields][' . $id . '][type]',
            '#value' => $type,
            '#id' => $id . '-type',
        );

        // Add validation box
        $form_validate = wpcf_admin_fields_form_validation( 'wpcf[fields]['
                . $id . '][validate]', call_user_func( 'wpcf_fields_' . $type ),
                $form_data );
        foreach ( $form_validate as $k => $v ) {
            $form['wpcf-' . $id][$k] = $v;
        }

        // WPML Translation Preferences
        if ( function_exists( 'wpml_cf_translation_preferences' ) ) {
            $custom_field = !empty( $form_data['slug'] ) ? wpcf_types_get_meta_prefix( $form_data ) . $form_data['slug'] : false;
            $suppress_errors = $custom_field == false ? true : false;
            $translatable = array('textfield', 'textarea', 'wysiwyg');
            $action = in_array( $type, $translatable ) ? 'translate' : 'copy';
            $form['wpcf-' . $id]['wpml-preferences'] = array(
                '#type' => 'fieldset',
                '#title' => __( 'Translation preferences', 'wpcf' ),
                '#collapsed' => true,
            );
            $wpml_prefs = wpml_cf_translation_preferences( $id,
                        $custom_field, 'wpcf', false, $action, false,
                        $suppress_errors );
            $wpml_prefs = str_replace('<span style="color:#FF0000;">', '<span class="wpcf-form-error">', $wpml_prefs);
            $form['wpcf-' . $id]['wpml-preferences']['form'] = array(
                '#type' => 'markup',
                '#markup' => $wpml_prefs,
            );
        }

        if ( empty( $form_data ) || isset( $form_data['is_new'] ) ) {
            $form['wpcf-' . $id]['is_new'] = array(
                '#type' => 'hidden',
                '#name' => 'wpcf[fields][' . $id . '][is_new]',
                '#value' => '1',
                '#attributes' => array(
                    'class' => 'wpcf-is-new',
                ),
            );
        }
        $form_data['id'] = $id;
        $form['wpcf-' . $id] = apply_filters( 'wpcf_form_field',
                $form['wpcf-' . $id], $form_data );
        return $form;
    }
    return false;
}

/**
 * Adds validation box.
 *
 * @param type $name
 * @param string $field
 * @param type $form_data
 * @return type
 */
function wpcf_admin_fields_form_validation( $name, $field, $form_data = array() ) {
    $form = array();

    if ( isset( $field['validate'] ) ) {

        $form['validate-table-open'] = array(
            '#type' => 'markup',
            '#markup' => '<table class="wpcf-fields-form-validate-table" '
            . 'cellspacing="0" cellpadding="0"><thead><tr><td>'
            . __( 'Validation', 'wpcf' ) . '</td><td>' . __( 'Error message',
                    'wpcf' )
            . '</td></tr></thead><tbody>',
        );

        // Process methods
        foreach ( $field['validate'] as $k => $method ) {

            // Set additional method data
            if ( is_array( $method ) ) {
                $form_data['data']['validate'][$k]['method_data'] = $method;
                $method = $k;
            }

            if ( !Wpcf_Validate::canValidate( $method )
                    || !Wpcf_Validate::hasForm( $method ) ) {
                continue;
            }

            $form['validate-tr-' . $method] = array(
                '#type' => 'markup',
                '#markup' => '<tr><td>',
            );

            // Get method form data
            if ( Wpcf_Validate::canValidate( $method )
                    && Wpcf_Validate::hasForm( $method ) ) {

                $field['#name'] = $name . '[' . $method . ']';
                $form_validate = call_user_func_array(
                        array('Wpcf_Validate', $method . '_form'),
                        array(
                    $field,
                    isset( $form_data['data']['validate'][$method] ) ? $form_data['data']['validate'][$method] : array()
                        )
                );

                // Set unique IDs
                foreach ( $form_validate as $key => $element ) {
                    if ( isset( $element['#type'] ) ) {
                        $form_validate[$key]['#id'] = $element['#type'] . '-'
                                . wpcf_unique_id( serialize( $element ) );
                    }
                    if ( isset( $element['#name'] ) && strpos( $element['#name'],
                                    '[message]' ) !== FALSE ) {
                        $before = '</td><td>';
                        $after = '</td></tr>';
                        $form_validate[$key]['#before'] = isset( $element['#before'] ) ? $element['#before'] . $before : $before;
                        $form_validate[$key]['#after'] = isset( $element['#after'] ) ? $element['#after'] . $after : $after;
                    }
                }

                // Join
                $form = $form + $form_validate;
            }
        }
        $form['validate-table-close'] = array(
            '#type' => 'markup',
            '#markup' => '</tbody></table>',
        );
    }

    return $form;
}

/**
 * Adds JS validation script.
 */
function wpcf_admin_fields_form_js_validation() {
    wpcf_form_render_js_validation();
}

/**
 * Saves open fieldsets.
 *
 * @param type $action
 * @param type $fieldset
 * @param type $group_id
 */
function wpcf_admin_fields_form_save_open_fieldset( $action, $fieldset,
        $group_id = false ) {
    $data = get_user_meta( get_current_user_id(), 'wpcf-group-form-toggle', true );
    if ( $group_id && $action == 'open' ) {
        $data[intval( $group_id )][$fieldset] = 1;
    } else if ( $group_id && $action == 'close' ) {
        $group_id = intval( $group_id );
        if ( isset( $data[$group_id][$fieldset] ) ) {
            unset( $data[$group_id][$fieldset] );
        }
    } else if ( $action == 'open' ) {
        $data[-1][$fieldset] = 1;
    } else if ( $action == 'close' ) {
        if ( isset( $data[-1][$fieldset] ) ) {
            unset( $data[-1][$fieldset] );
        }
    }
    update_user_meta( get_current_user_id(), 'wpcf-group-form-toggle', $data );
}

/**
 * Saves open fieldsets.
 *
 * @param type $action
 * @param type $fieldset
 * @param type $group_id
 */
function wpcf_admin_fields_form_fieldset_is_collapsed( $fieldset ) {
    if ( isset( $_REQUEST['group_id'] ) ) {
        $group_id = intval( $_REQUEST['group_id'] );
    } else {
        $group_id = -1;
    }
    $data = get_user_meta( get_current_user_id(), 'wpcf-group-form-toggle', true );
    if ( !isset( $data[$group_id] ) ) {
        return true;
    }
    return array_key_exists( $fieldset, $data[$group_id] ) ? false : true;
}

/**
 * Adds 'Edit' and 'Cancel' buttons, expandable div.
 *
 * @todo REMOVE THIS - Since Types 1.2 we do not need it
 *
 * @param type $id
 * @param type $element
 * @param type $title
 * @param type $list
 * @param type $empty_txt
 * @return string
 */
function wpcf_admin_fields_form_nested_elements( $id, $element, $title, $list,
        $empty_txt ) {
    global $wpcf_button_style;
    global $wpcf_button_style30;
    $form = array();
    $form = $element;
    $id = strtolower( strval( $id ) );

    $form['#before'] = '<span id="wpcf-group-form-update-' . $id . '-ajax-response"'
            . ' style="font-style:italic;font-weight:bold;display:inline-block;">'
            . esc_html( $title ) . ' ' . $list . '</span>'
            . '&nbsp;&nbsp;<a href="javascript:void(0);" ' . $wpcf_button_style30 . ' '
            . ' class="button-secondary" onclick="'
            . 'window.wpcf' . ucfirst( $id ) . 'Text = new Array(); window.wpcfFormGroups' . ucfirst( $id ) . 'State = new Array(); '
            . 'jQuery(this).next().slideToggle()'
            . '.find(\'.checkbox\').each(function(index){'
            . 'if (jQuery(this).is(\':checked\')) { '
            . 'window.wpcf' . ucfirst( $id ) . 'Text.push(jQuery(this).next().html()); '
            . 'window.wpcfFormGroups' . ucfirst( $id ) . 'State.push(jQuery(this).attr(\'id\'));'
            . '}'
            . '});'
            . ' jQuery(this).css(\'visibility\', \'hidden\');">'
            . __( 'Edit', 'wpcf' ) . '</a>' . '<div class="hidden" id="wpcf-form-fields-' . $id . '">';

    $form['#after'] = '<a href="javascript:void(0);" ' . $wpcf_button_style . ' '
            . 'class="button-primary wpcf-groups-form-ajax-update-' . $id . '-ok"'
            . ' onclick="">'
            . __( 'OK', 'wpcf' ) . '</a>&nbsp;'
            . '<a href="javascript:void(0);" ' . $wpcf_button_style . ' '
            . 'class="button-secondary wpcf-groups-form-ajax-update-' . $id . '-cancel"'
            . ' onclick="">'
            . __( 'Cancel', 'wpcf' ) . '</a>' . '</div></div>';

    return $form;
}

/*
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 *
 * From here add revised code
 */

/**
 *
 * Use this to show filter item
 *
 * @since Types 1.2
 * @global type $wpcf_button_style
 * @global type $wpcf_button_style30
 * @param type $id
 * @param type $txt
 * @param type $txt_empty
 * @param type $e
 * @return string
 */
function _wpcf_filter_wrap( $id, $title, $txt, $txt_empty, $e, $edit_button = '' ) {

    global $wpcf_button_style;
    global $wpcf_button_style30;

    $form = array();
    $unique_id = wpcf_unique_id( serialize( func_get_args() ) );
    $query = 'jQuery(this), \'' . esc_html( $id ) . '\', \'' . esc_html( $title )
            . '\', \'' . esc_html( $txt ) . '\', \'' . esc_html( $txt_empty ) . '\'';

    if ( empty( $edit_button ) ) {
        $edit = __( 'Edit', 'wpcf' );
    } else {
        $edit = $edit_button;
    }
    /*
     *
     * Title and Edit button
     */
    $form['filter_' . $unique_id . '_wrapper'] = array(
        '#type' => 'markup',
        '#markup' => '<span class="wpcf-filter-ajax-response"'
        . ' style="font-style:italic;font-weight:bold;display:inline-block;">'
        . $title . ' ' . $txt . '</span>'
        . '&nbsp;&nbsp;<a href="javascript:void(0);" ' . $wpcf_button_style30 . ' '
        . ' class="button-secondary wpcf-form-filter-edit" onclick="wpcfFilterEditClick('
        . $query . ');">'
        . $edit . '</a><div class="hidden" id="wpcf-form-fields-' . $id . '">',
    );

    /**
     * Form element as param
     * It may be single element or array of elements
     * Simply check if array has #type - indicates it is a form item
     */
    if ( isset( $e['#type'] ) ) {
        $form['filter_' . $unique_id . '_items'] = $e;
    } else {
        /*
         * If array of elements just join
         */
        $form = $form + (array) $e;
    }

    /**
     * OK button
     */
    $form['filter_' . $unique_id . '_ok'] = array(
        '#type' => 'markup',
        '#markup' => '<a href="javascript:void(0);" ' . $wpcf_button_style . ' '
        . 'class="button-primary  wpcf-form-filter-ok wpcf-groups-form-ajax-update-'
        . $id . '-ok"'
        . ' onclick="wpcfFilterOkClick('
        . $query . ');">'
        . __( 'OK', 'wpcf' ) . '</a>&nbsp;',
    );

    /**
     * Cancel button
     */
    $form['filter_' . $unique_id . '_cancel'] = array(
        '#type' => 'markup',
        '#markup' => '<a href="javascript:void(0);" ' . $wpcf_button_style . ' '
        . 'class="button-secondary wpcf-form-filter-cancel wpcf-groups-form-ajax-update-'
        . $id . '-cancel"'
        . ' onclick="wpcfFilterCancelClick('
        . $query . ');">'
        . __( 'Cancel', 'wpcf' ) . '</a>',
    );

    /**
     * Close wrapper
     */
    $form['filter_' . $unique_id . 'wrapper_close'] = array(
        '#type' => 'markup',
        '#markup' => '</div></div>',
    );

    return $form;
}
