<?php
/*
 * Migration functions
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/includes/migration.php $
 * $LastChangedDate: 2015-04-01 14:15:17 +0000 (Wed, 01 Apr 2015) $
 * $LastChangedRevision: 1125405 $
 * $LastChangedBy: iworks $
 *
 */

/**
 * Migration form.
 *
 * @global object $wpdb
 *
 * @return array
 */
function wpcf_admin_migration_form()
{
    global $wpdb;
    $wpcf_types = get_option('wpcf-custom-types', array());
    $wpcf_taxonomies = get_option('wpcf-custom-taxonomies', array());
    $wpcf_types_defaults = wpcf_custom_types_default();
    $wpcf_taxonomies_defaults = wpcf_custom_taxonomies_default();

    $form = array();
    $form['#form']['callback'] = 'wpcf_admin_migration_form_submit';

    $cfui_types = get_option('cpt_custom_post_types', array());
    $cfui_types_migrated = array();

    $cfui_taxonomies = get_option('cpt_custom_tax_types', array());
    $cfui_tax_migrated = array();

    if (!empty($cfui_types)) {
        $form['types_title'] = array(
            '#type' => 'markup',
            '#markup' => '<h3>' . __('Custom Types UI Post Types', 'wpcf') . '</h3>',
        );

        foreach ($cfui_types as $key => $cfui_type) {
            $exists = array_key_exists(sanitize_title($cfui_type['name']),
                    $wpcf_types);
            if ($exists) {
                $attributes = array('readonly' => 'readonly', 'disabled' => 'disabled');
                $add = __('(exists)', 'wpcf');
            } else {
                $attributes = array();
                $add = '';
            }
            $slug = $id = sanitize_title($cfui_type['name']);
            $form['types-' . $slug] = array(
                '#type' => 'checkbox',
                '#name' => 'cfui[types][]',
                '#value' => $slug,
                '#title' => !empty($cfui_type['label']) ? $cfui_type['label'] . ' ' . $add : $slug . ' ' . $add,
                '#inline' => true,
                '#after' => '&nbsp;&nbsp;',
                '#default_value' => $exists ? 0 : 1,
                '#attributes' => $attributes,
            );
        }
    }

    if (!empty($cfui_taxonomies)) {
        $form['tax_titles'] = array(
            '#type' => 'markup',
            '#markup' => '<h3>' . __('Custom Types UI Taxonomies') . '</h3>',
        );

        foreach ($cfui_taxonomies as $key => $cfui_tax) {
            $title = !empty($cfui_tax['label']) ? $cfui_tax['label'] : $slug;
            $exists = array_key_exists(sanitize_title($cfui_tax['name']),
                    $wpcf_taxonomies);
            if ($exists) {
                $attributes = array('readonly' => 'readonly', 'disabled' => 'disabled');
                $add = __('(exists)', 'wpcf');
            } else {
                $attributes = array();
                $add = '';
            }
            $slug = $id = sanitize_title($cfui_tax['name']);
            $form['types-' . $slug] = array(
                '#type' => 'checkbox',
                '#name' => 'cfui[tax][]',
                '#value' => $slug,
                '#title' => $title . ' ' . $add,
                '#inline' => true,
                '#after' => '&nbsp;&nbsp;',
                '#default_value' => $exists ? 0 : 1,
                '#attributes' => $attributes,
            );
        }
    }

    if (!empty($cfui_types) || !empty($cfui_taxonomies)) {
        $form['deactivate-cfui'] = array(
            '#type' => 'checkbox',
            '#name' => 'deactivate-cfui',
            '#before' => '<br /><br />',
            '#default_value' => 1,
            '#title' => __('Disable Custom Types UI after importing the configuration (leave this checked to avoid defining custom types twice)',
                    'wpcf'),
        );
    };

    // ACF

    $acf_groups = get_posts('post_type=acf&status=publish&numberposts=-1');
    if (!empty($acf_groups)) {
        $wpcf_types = wpcf_admin_fields_get_available_types();
        $wpcf_types_options = array();
        foreach ($wpcf_types as $type => $data) {
            $wpcf_types_options[$type] = array(
                '#title' => $data['title'],
                '#value' => $type,
            );
        }
        $acf_types = array(
            'text' => 'textfield',
            'textarea' => 'textarea',
            'wysiwyg' => 'wysiwyg',
            'image' => 'image',
            'file' => 'file',
            'select' => 'select',
            'checkbox' => 'checkbox',
            'radio' => 'radio',
            'true_false' => 'radio',
            'page_link' => 'textfield',
            'post_object' => false,
            'relationship' => 'textfield',
            'date_picker' => 'date',
            'color_picker' => false,
            'repeater' => false,
        );

        if (!empty($acf_groups)) {
            $form['acf_title'] = array(
                '#type' => 'markup',
                '#markup' => '<h3>' . __('Advanced Custom Fields') . '</h3>',
            );
        }

        foreach ($acf_groups as $acf_key => $acf_post) {
            $group_id = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT ID FROM $wpdb->posts WHERE post_title = %s AND post_type='wp-types-group'",
                    $acf_post->post_title
                )
            );
            if (empty($group_id)) {
                $add = __('Group will be created', 'wpcf');
            } else {
                $add = __('Group will be updated', 'wpcf');
            }
            $form[$acf_post->ID . '_post'] = array(
                '#type' => 'checkbox',
                '#title' => $acf_post->post_title . ' (' . $add . ')',
                '#value' => $acf_post->ID,
                '#default_value' => 1,
                '#name' => 'acf_posts[migrate_groups][]',
                '#inline' => true,
                '#after' => '<br />',
                '#attributes' => array('onclick' => 'if (jQuery(this).is(\':checked\')) { jQuery(this).parent().find(\'table .checkbox\').attr(\'checked\',\'checked\'); } else { jQuery(this).parent().find(\'table .checkbox\').removeAttr(\'checked\'); }'),
            );
            $form[$acf_post->ID . '_post_title'] = array(
                '#type' => 'hidden',
                '#name' => 'acf_posts[' . $acf_post->ID . '][post_title]',
                '#value' => $acf_post->post_title,
            );
            $form[$acf_post->ID . '_post_content'] = array(
                '#type' => 'hidden',
                '#name' => 'acf_posts[' . $acf_post->ID . '][post_content]',
                '#value' => addslashes($acf_post->post_content),
            );
            $form[$acf_post->ID . '_fields_table'] = array(
                '#type' => 'markup',
                '#markup' => '<table style="margin-bottom: 40px;">',
            );
            $metas = get_post_custom($acf_post->ID);
            $acf_fields = array();
            foreach ($metas as $meta_name => $meta) {
                if (strpos($meta_name, 'field_') === 0) {
                    $data = unserialize($meta[0]);
                    $exists = wpcf_types_cf_under_control('check_exists',
                            $data['name']);
                    $outsider = wpcf_types_cf_under_control('check_outsider',
                            $data['name']);
                    $supported = !empty($acf_types[$data['type']]);
                    if (!$supported) {
//                        wpcf_admin_message(sprintf(__("Field %s will not be imported because field type is not currently supported by Types",
//                                                'wpcf'), $data['label']),
//                                'error');
                        $attributes = array('style' => 'margin-left: 20px;', 'readonly' => 'readonly', 'disabled' => 'disabled');
                        $add = __('Field conversion not supported by Types',
                                'wpcf');
                    } else if ($exists && !$outsider) {
                        $attributes = array('style' => 'margin-left: 20px;', 'readonly' => 'readonly', 'disabled' => 'disabled');
                        $add = __('Field with same name is already controlled by Types',
                                'wpcf');
                    } else if ($exists && $outsider) {
                        $attributes = array('style' => 'margin-left: 20px;');
                        $add = __('Field will be updated', 'wpcf');
                    } else {
                        $attributes = array('style' => 'margin-left: 20px;');
                        $add = __('Field will be created', 'wpcf');
                    }
                    $form[$acf_post->ID . '_acf_field_' . $meta_name . '_checkbox'] = array(
                        '#type' => 'checkbox',
                        '#title' => $data['name'] . ' (' . $add . ')',
                        '#value' => $meta_name,
                        '#default_value' => (($exists && !$outsider) || !$supported) ? 0 : 1,
                        '#name' => 'acf_posts[' . $acf_post->ID . '][migrate_fields][]',
                        '#inline' => true,
                        '#attributes' => $attributes,
                        '#before' => '<tr><td>',
                    );
                    $form[$acf_post->ID . '_acf_field_' . $meta_name . '_details_description'] = array(
                        '#type' => 'hidden',
                        '#name' => 'acf_posts[' . $acf_post->ID . '][fields][' . $meta_name . '][description]',
                        '#value' => esc_attr($data['instructions']),
                        '#inline' => true,
                    );
                    $form[$acf_post->ID . '_acf_field_' . $meta_name . '_details_name'] = array(
                        '#type' => 'hidden',
                        '#name' => 'acf_posts[' . $acf_post->ID . '][fields][' . $meta_name . '][name]',
                        '#value' => esc_attr($data['label']),
                    );
                    $form[$acf_post->ID . '_acf_field_' . $meta_name . '_details_slug'] = array(
                        '#type' => 'hidden',
                        '#name' => 'acf_posts[' . $acf_post->ID . '][fields][' . $meta_name . '][slug]',
                        '#value' => esc_attr($data['name']),
                    );
                    // Add options for radios and select
                    if (in_array($data['type'], array('radio', 'select'))
                            && !empty($data['choices'])) {
                        foreach ($data['choices'] as $option_value => $option_title) {
                            if (strpos($option_value, ':') !== false) {
                                $temp = explode(':', $option_value);
                                $option_value = trim($temp[0]);
                                $option_title = trim($temp[1]);
                            } else if (strpos($option_title, ':') !== false) {
                                $temp = explode(':', $option_title);
                                $option_value = trim($temp[0]);
                                $option_title = trim($temp[1]);
                            }

                            $_key = sanitize_title($option_value);

                            $form[$acf_post->ID . '_acf_field_' . $meta_name . '_option_' . $_key . '_value'] = array(
                                '#type' => 'hidden',
                                '#name' => 'acf_posts[' . $acf_post->ID . '][fields][' . $meta_name . '][options][' . $_key . '][value]',
                                '#value' => esc_attr($option_value),
                            );
                            $form[$acf_post->ID . '_acf_field_' . $meta_name . '_option_' . $_key . '_title'] = array(
                                '#type' => 'hidden',
                                '#name' => 'acf_posts[' . $acf_post->ID . '][fields][' . $meta_name . '][options][' . $_key . '][title]',
                                '#value' => esc_attr($option_title),
                            );
                        }
                        if (!empty($data['default_value'])) {
                            $form[$acf_post->ID . '_acf_field_' . $meta_name . '_option_default'] = array(
                                '#type' => 'hidden',
                                '#name' => 'acf_posts[' . $acf_post->ID . '][fields][' . $meta_name . '][options][default]',
                                '#value' => esc_attr($data['default_value']),
                            );
                        }
                    }
                    if (($exists && !$outsider) || !$supported) {
                        $attributes = array('disabled' => 'disabled');
                        if ($exists) {
                        }
                    } else {
                        $attributes = array();
                    }
                    $default_value = isset($acf_types[$data['type']]) && !empty($acf_types[$data['type']]) ? $acf_types[$data['type']] : 'textfield';
                    $form[$acf_post->ID . '_acf_field_' . $meta_name . '_details_type'] = array(
                        '#type' => 'select',
                        '#name' => 'acf_posts[' . $acf_post->ID . '][fields][' . $meta_name . '][type]',
                        '#options' => $wpcf_types_options,
                        '#default_value' => $default_value,
                        '#inline' => true,
                        '#attributes' => $attributes,
                        '#before' => '</td><td>',
                        '#after' => '</td></tr>',
                    );
                }
            }
            $acf_groups[$acf_key] = $acf_post;
            $form[$acf_post->ID . '_fields_table_close'] = array(
                '#type' => 'markup',
                '#markup' => '</table>',
            );
        }
    }

    $form['submit'] = array(
        '#type' => 'submit',
        '#name' => 'submit',
        '#value' => __('Import custom field settings', 'wpcf'),
        '#attributes' => array('class' => 'button-primary'),
    );

    return $form;
}

/**
 * Adds 3rd party created types, taxonomies and fields
 */
function wpcf_admin_migration_form_submit() {
    $cfui_types = get_option('cpt_custom_post_types', array());
    $cfui_taxonomies = get_option('cpt_custom_tax_types', array());
    $wpcf_types = get_option('wpcf-custom-types', array());
    $wpcf_taxonomies = get_option('wpcf-custom-taxonomies', array());
    $redirect_page = 'wpcf-ctt';

    if (!empty($_POST['cfui']['types'])) {
        $data = array();
        foreach ($_POST['cfui']['types'] as $key => $types_slug) {
            if (array_key_exists(sanitize_title($types_slug), $wpcf_types)) {
                continue;
            }
            foreach ($cfui_types as $cfui_type) {
                if (sanitize_title($cfui_type['name']) == $types_slug) {
                    $data[$types_slug] = wpcf_admin_migrate_get_cfui_type_data($cfui_type);
                    wpcf_admin_message_store(
                        sprintf(__("Post Type %s added", 'wpcf'),
                        '<em>' . $cfui_type['name'] . '</em>')
                    );
                }
            }
        }
        $wpcf_types = array_merge($wpcf_types, $data);
    }
    if (!empty($_POST['cfui']['tax'])) {
        $data = array();
        foreach ($_POST['cfui']['tax'] as $key => $tax_slug) {
            if (array_key_exists(sanitize_title($tax_slug), $wpcf_taxonomies)) {
                continue;
            }
            foreach ($cfui_taxonomies as $cfui_tax) {
                if (sanitize_title($cfui_tax['name']) == $tax_slug) {
                    $data[$tax_slug] = wpcf_admin_migrate_get_cfui_tax_data($cfui_tax);
                    wpcf_admin_message_store(
                        sprintf(__("Taxonomy %s added", 'wpcf'),
                        '<em>' . $cfui_tax['name'] . '</em>')
                    );
                    if (
                        array_key_exists(1,$cfui_tax)
                        && !empty($cfui_tax[1])
                        && is_array($cfui_tax[1])
                    ) {
                        foreach( $cfui_tax[1] as $key) {
                            $types_slug = sanitize_title($key);
                            if ( array_key_exists($types_slug, $wpcf_types) ) {
                                if ( !array_key_exists('taxonomies', $wpcf_types[$types_slug] )) {
                                    $wpcf_types[$types_slug]['taxonomies'] = array();
                                }
                                $wpcf_types[$types_slug]['taxonomies'][$tax_slug] = 1;
                                $wpcf_types[$types_slug][TOOLSET_EDIT_LAST] = time();
                            }
                        }

                    }
                }
            }
        }
        $wpcf_taxonomies = array_merge($wpcf_taxonomies, $data);
        update_option('wpcf-custom-taxonomies', $wpcf_taxonomies);
    }
    update_option('wpcf-custom-types', $wpcf_types);

    // ACF

    if (!empty($_POST['acf_posts']['migrate_groups'])) {
        foreach ($_POST['acf_posts']['migrate_groups'] as $acf_group_id) {
            if (empty($_POST['acf_posts'][$acf_group_id])) {
                continue;
            }
            global $wpdb;
            $group = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT ID, post_title FROM $wpdb->posts WHERE post_title = %s AND post_type='wp-types-group'",
                    $_POST['acf_posts'][$acf_group_id]['post_title']
                )
            );
            if (empty($group)) {
				// @todo Maybe sanitize here
                $group = array();
                $group['name'] = $_POST['acf_posts'][$acf_group_id]['post_title'];
                $group['description'] = $_POST['acf_posts'][$acf_group_id]['post_content'];
                $group_id = wpcf_admin_fields_save_group($group);
                wpcf_admin_message_store(sprintf(__("Group %s added", 'wpcf'),
                                '<em>' . $group['name'] . '</em>'));
            } else {
                $group_id = $group->ID;
                wpcf_admin_message_store(sprintf(__("Group %s updated", 'wpcf'),
                                '<em>' . $group->post_title . '</em>'));
            }
            $fields_to_add = array();
            if ($group_id && !empty($_POST['acf_posts'][$acf_group_id]['fields'])) {
                foreach ($_POST['acf_posts'][$acf_group_id]['fields'] as $field_id => $field) {
                    if (!in_array($field_id,
                                    $_POST['acf_posts'][$acf_group_id]['migrate_fields'])) {
                        continue;
                    }
                    if (!wpcf_types_cf_under_control('check_exists',
                                    $field['slug'])
                            || wpcf_types_cf_under_control('check_outsider',
                                    $field['slug'])) {
                        // save field
                        $field['controlled'] = 1;
                        $temp = wpcf_admin_fields_save_field($field);
                        $fields_to_add[] = $temp;
                        wpcf_admin_message_store(sprintf(__("Field %s added",
                                                'wpcf'),
                                        '<em>' . $temp . '</em>'));
                    }
                }
                wpcf_admin_fields_save_group_fields($group_id, $fields_to_add,
                        false);
            }
            wpcf_admin_fields_save_group_post_types($group_id, array());
            wpcf_admin_fields_save_group_terms($group_id, array());
        }
        $redirect_page = 'wpcf';
    }
    flush_rewrite_rules();

    // Deactivate plugins
    if (!empty($_POST['deactivate-cfui'])) {
        $active_plugins = get_option('active_plugins', array());
        foreach ($active_plugins as $key => $file) {
            if (strpos($file, 'custom-post-type-ui.php') !== false) {
                unset($active_plugins[$key]);
            }
        }
        update_option('active_plugins', array_values($active_plugins));
    }
    wp_redirect(admin_url('admin.php?page=' . $redirect_page));
    die();
}

/**
 * Gets types data.
 *
 * @param type $cfui_type
 * @return type
 */
function wpcf_admin_migrate_get_cfui_type_data($cfui_type) {
    $cfui_types_migrated = array();
    $supports = array();
    if (!empty($cfui_type[0])) {
        foreach ($cfui_type[0] as $temp_key => $support) {
            $supports[$support] = 1;
        }
    }

    $taxonomies = array();
    if (!empty($cfui_type[1])) {
        foreach ($cfui_type[1] as $key => $tax) {
            $taxonomies[$tax] = 1;
        }
    }
    $wpcf_types_defaults = wpcf_custom_types_default();
    $slug = $id = sanitize_title($cfui_type['name']);

    // Set labels
    $labels = isset($cfui_type[2]) ? $cfui_type[2] : array();
    $labels['name'] = !empty($cfui_type['label']) ? $cfui_type['label'] : $slug;
    $labels['singular_name'] = !empty($cfui_type['singular_label']) ? $cfui_type['singular_label'] : $slug;
    foreach ($wpcf_types_defaults['labels'] as $label_id => $label_text) {
        if (empty($labels[$label_id])) {
            $labels[$label_id] = $label_text;
        }
    }
    foreach ($labels as $label_id => $label_text) {
        if (!isset($wpcf_types_defaults['labels'][$label_id])) {
            unset($labels[$label_id]);
        }
    }
    // Force menu_name label
    if (empty($labels['menu_name'])) {
        $labels['menu_name'] = $labels['name'];
    }

    // Set rewrite
    $rewrite = empty($cfui_type['rewrite']) ? 0 : array();
    if (is_array($rewrite)) {
        $rewrite = array(
            'enabled' => 1,
            'custom' => !empty($cfui_type['rewrite_slug']) ? 'custom' : 'normal',
            'slug' => !empty($cfui_type['rewrite_slug']) ? $cfui_type['rewrite_slug'] : '',
            'with_front' => 1,
            'feeds' => 1,
            'pages' => 1,
        );
    }

    $cfui_types_migrated[$slug] = array(
        'labels' => $labels,
        'supports' => $supports,
        'slug' => $slug,
        'rewrite' => $rewrite,
        'slug' => $slug,
        'id' => $id,
        'public' => empty($cfui_type['public']) ? 'hidden' : 'public',
        'publicly_queryable' => empty($cfui_type['public']) ? false : true,
        'query_var_enabled' => (bool) $cfui_type['query_var'],
        'query_var' => '',
        'show_in_menu' => (bool) $cfui_type['show_in_menu'],
        'show_in_menu_page' => $cfui_type['show_in_menu_string'],
        'has_archive' => (bool) $cfui_type['has_archive'],
        'taxonomies' => $taxonomies,
        'can_export' => true,
        'show_in_nav_menus' => true,
    );

    unset($cfui_type[0], $cfui_type[1], $cfui_type[2], $cfui_type['public'],
            $cfui_type['rewrite'], $cfui_type['name'], $cfui_type['label'],
            $cfui_type['singular_label'], $cfui_type['capability_type'],
            $cfui_type['rewrite_slug'], $cfui_type['show_in_menu'],
            $cfui_type['show_in_menu_string'], $cfui_type['publicly_queryable'],
            $cfui_type['capabilities'], $cfui_type['has_archive'],
            $cfui_type['show_in_nav_menus']);

    $cfui_types_migrated[$slug] = array_merge($cfui_type,
            $cfui_types_migrated[$slug]);

    return $cfui_types_migrated[$slug];
}

/**
 * Gets taxonomies data.
 *
 * @param type $cfui_tax
 * @return type
 */
function wpcf_admin_migrate_get_cfui_tax_data($cfui_tax) {
    $cfui_tax_migrated = array();
    $supports = array();
    if (!empty($cfui_tax[1])) {
        foreach ($cfui_tax[1] as $temp_key => $support) {
            $supports[$support] = 1;
        }
    }

    $wpcf_taxonomies_defaults = wpcf_custom_taxonomies_default();
    $slug = $id = sanitize_title($cfui_tax['name']);

    // Set labels
    $labels = isset($cfui_tax[0]) ? $cfui_tax[0] : array();
    $labels['name'] = !empty($cfui_tax['label']) ? $cfui_tax['label'] : $slug;
    $labels['singular_name'] = !empty($cfui_tax['singular_label']) ? $cfui_tax['singular_label'] : $slug;
    foreach ($wpcf_taxonomies_defaults['labels'] as $label_id => $label_text) {
        if (empty($labels[$label_id])) {
            $labels[$label_id] = $label_text;
        }
    }
    foreach ($labels as $label_id => $label_text) {
        if (!isset($wpcf_taxonomies_defaults['labels'][$label_id])) {
            unset($labels[$label_id]);
        }
    }
    // Force menu_name label
    if (empty($labels['menu_name'])) {
        $labels['menu_name'] = $labels['name'];
    }

    // Set rewrite
    $rewrite = empty($cfui_tax['rewrite']) ? 0 : array();
    if (is_array($rewrite)) {
        $rewrite = array(
            'enabled' => 1,
            'slug' => !empty($cfui_tax['rewrite_slug']) ? $cfui_tax['rewrite_slug'] : '',
            'with_front' => 1,
            'hierarchical' => (bool) $cfui_tax['hierarchical'],
        );
    }

    $cfui_tax_migrated[$slug] = array(
        'labels' => $labels,
        'supports' => $supports,
        'slug' => $slug,
        'wpcf-tax' => $slug,
        'rewrite' => $rewrite,
        'slug' => $slug,
        'id' => $id,
        'public' => 'public',
        'query_var_enabled' => (bool) $cfui_tax['query_var'],
        'query_var' => '',
        'hierarchical' => (bool) $cfui_tax['hierarchical'] ? 'hierarchical' : 'flat',
    );

    unset($cfui_tax[0], $cfui_tax[1], $cfui_tax['rewrite'], $cfui_tax['name'],
            $cfui_tax['label'], $cfui_tax['singular_label'],
            $cfui_tax['rewrite_slug'], $cfui_tax['capabilities'],
            $cfui_tax['hierarchical']);

    $cfui_tax_migrated[$slug] = array_merge($wpcf_taxonomies_defaults,
            array_merge($cfui_tax, $cfui_tax_migrated[$slug]));

    return $cfui_tax_migrated[$slug];
}
