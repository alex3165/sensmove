<?php
/*
 * @since Types 1.2
 *
 * All WPML specific functions should be moved here.
 *
 * Mind wpml_action parameter for field.
 * Values:
 * 0 nothing (ignore), 1 copy, 2 translate
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/embedded/includes/wpml.php $
 * $LastChangedDate: 2015-03-25 12:38:40 +0000 (Wed, 25 Mar 2015) $
 * $LastChangedRevision: 1120400 $
 * $LastChangedBy: iworks $
 *
 */

add_action( 'wpcf_after_init', 'wpcf_wpml_init' );
add_action( 'init', 'wpcf_wpml_warnings_init', PHP_INT_MAX );

// Only when WPML active
if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {

    add_filter( 'get_post_metadata',
            'wpcf_wpml_relationship_meta_belongs_filter', 10, 4 );

    // Relationship filter get_children query
    add_filter( 'wpcf_relationship_get_children_query',
            'wpcf_wpml_relationship_get_children_query', 10, 5 );

    add_filter( 'types_fields', 'wpcf_wpml_fields_filter', 10, 3 );
    add_filter( 'types_post_type', 'wpcf_wpml_post_types_translate', 10, 3 );
    add_filter( 'types_taxonomy', 'wpcf_wpml_taxonomy_translate', 10, 3 );

    /*
     *
     * Filter terms used when:
     * - Displaying Group form
     * - Filtering Group on post edit screen
     */
    add_filter( 'wpcf_group_form_filter_terms',
            'wpcf_wpml_group_form_filter_terms_filter' );
    add_filter( 'wpcf_post_group_filter_settings',
            'wpcf_wpml_post_group_filter_taxonomies', 10, 4 );
    /*
     * Add untranslated IDs to form
     */
    add_filter( 'wpcf_form_fields', 'wpcf_wpml_group_filter_add_missing_terms',
            10, 2 );

    // Sync
    add_action( 'wpcf_post_type_renamed', 'wpcf_wpml_post_type_renamed', 10, 2 );
    add_action( 'wpcf_taxonomy_renamed', 'wpcf_wpml_taxonomy_renamed', 10, 2 );

    // Relationship save child language
    add_action( 'wpcf_relationship_save_child',
            'wpcf_wpml_relationship_save_child', 10, 2 );

    // Dissallow WPML to delete fields in translated posts
    add_action( 'wpcf_postmeta_before_delete',
            'wpcf_wpml_remove_delete_postmeta_hook_remove', 10, 2 );
    add_action( 'wpcf_postmeta_after_delete',
            'wpcf_wpml_remove_delete_postmeta_hook_add', 10, 2 );
    add_action( 'wpcf_postmeta_before_delete_repetitive',
            'wpcf_wpml_remove_delete_postmeta_hook_remove', 10, 2 );
    add_action( 'wpcf_postmeta_after_delete_repetitive',
            'wpcf_wpml_remove_delete_postmeta_hook_add', 10, 2 );

    // Fix saving repetitive
    add_action( 'wpcf_postmeta_before_add_repetitive',
            'wpcf_wpml_sync_postmeta_hook_remove', 10, 2 );
    add_action( 'wpcf_postmeta_before_add_last_repetitive',
            'wpcf_wpml_sync_postmeta_hook_add', 10, 2 );
    add_action( 'wpcf_postmeta_after_add_repetitive',
            'wpcf_wpml_sync_postmeta_hook_add', 10, 2 );

    // Fix to set correct parent and children for duplicated posts
    add_action( 'icl_make_duplicate', 'wpcf_wpml_duplicated_post_relationships',
            20, 4);
}

/**
 * Adds wpml_action property.
 *
 * @param type $fields
 * @return array
 */
function wpcf_wpml_fields_filter( $fields ) {
    foreach ( $fields as &$field ) {
        $field = wpcf_wpml_field_filter( $field );
    }
    return $fields;
}

/**
 * Adds wpml_action property.
 *
 * @global type $iclTranslationManagement
 * @param type $field
 * @return array
 */
function wpcf_wpml_field_filter( $field ) {

    global $iclTranslationManagement;
    $actions = array(
        'ignore' => 0,
        'copy' => 1,
        'translate' => 2,
    );

    $action = isset( $field['wpml_action'] ) ? $field['wpml_action'] : null;
    // Always use TM settings if available
    if ( defined( 'WPML_TM_VERSION' ) && !empty( $iclTranslationManagement ) ) {
        if ( isset( $iclTranslationManagement->settings['custom_fields_translation'][$field['meta_key']] ) ) {
            $action = intval( $iclTranslationManagement->settings['custom_fields_translation'][$field['meta_key']] );
        }
    }
    if ( is_null( $action ) || !is_numeric( $action ) ) {
        if ( isset( $actions[strval( $action )] ) ) {
            $action = intval( $actions[strval( $action )] );
        } else if ( isset( $field['type'] ) ) {
            $action = wpcf_wpml_get_action_by_type( $field['type'] );
        }
    }

    $field['wpml_action'] = intval( $action );
    return $field;
}

/**
 * Returns WPML action by field type.
 *
 * @param type $type
 * @return type
 */
function wpcf_wpml_get_action_by_type( $type ) {
    return in_array( $type,
                    array('date', 'skype', 'numeric', 'phone', 'image', 'file', 'email',
                'url') ) ? 1 : 2;
}

function wpcf_wpml_init() {
    global $wpcf;
    // Init object
    $wpcf->wpml = new stdClass();
    // Holds all processed terms in current form
    $wpcf->wpml->group_form_filter_taxonomies_filtered = array();
    // Holds all translated terms if on language other than default
    $wpcf->wpml->group_form_filter_taxonomies_translated = array();
}

/**
 * WPML translate call.
 *
 * @param type $name
 * @param type $string
 * @return type
 */
function wpcf_translate( $name, $string, $context = 'plugin Types' ) {
    if ( !function_exists( 'icl_t' ) || !is_string($string) || empty($string) ) {
        return $string;
    }
    return icl_t( $context, $name, stripslashes( $string ) );
}

/**
 * Registers WPML translation string.
 *
 * @param type $context
 * @param type $name
 * @param type $value
 */
function wpcf_translate_register_string( $context, $name, $value,
        $allow_empty_value = false ) {
    if ( function_exists( 'icl_register_string' ) ) {
        icl_register_string( $context, $name, stripslashes( $value ),
                $allow_empty_value );
    }
}

/**
 * Relationship filter get_children query.
 *
 * @param type $_query string for get_posts()
 * @param type $parent Parent
 * @param type $post_type Children post type
 * @param type $data Saved data
 * @param type $field Ordering field (optional)
 */
function wpcf_wpml_relationship_get_children_query( $query, $parent, $post_type,
        $data, $field = null ) {

    global $sitepress;

    // Check if children post type is translatable
    if ( !$sitepress->is_translated_post_type( $post_type ) ) {
        $query['lang'] = 'all';
    }

    return $query;
}

/**
 * WPML editor filter
 *
 * @param type $cf_name
 * @return type
 */
function wpcf_icl_editor_cf_name_filter( $cf_name ) {
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
    $fields = wpcf_admin_fields_get_fields();
    if ( empty( $fields ) ) {
        return $cf_name;
    }
    $cf_name = substr( $cf_name, 6 );
    if ( strpos( $cf_name, WPCF_META_PREFIX ) == 0 ) {
        $cf_name = str_replace( WPCF_META_PREFIX, '', $cf_name );
    }
    if ( isset( $fields[$cf_name]['name'] ) ) {
        $cf_name = wpcf_translate( 'field ' . $fields[$cf_name]['id'] . ' name',
                $fields[$cf_name]['name'] );
    }
    return $cf_name;
}

/**
 * WPML editor filter
 *
 * @param type $cf_name
 * @param type $description
 * @return type
 */
function wpcf_icl_editor_cf_description_filter( $description, $cf_name ) {
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
    $fields = wpcf_admin_fields_get_fields();
    if ( empty( $fields ) ) {
        return $description;
    }
    $cf_name = substr( $cf_name, 6 );
    if ( strpos( $cf_name, WPCF_META_PREFIX ) == 0 ) {
        $cf_name = str_replace( WPCF_META_PREFIX, '', $cf_name );
    }
    if ( isset( $fields[$cf_name]['description'] ) ) {
        $description = wpcf_translate( 'field ' . $fields[$cf_name]['id'] . ' description',
                $fields[$cf_name]['description'] );
    }

    return $description;
}

/**
 * WPML editor filter
 *
 * @param type $cf_name
 * @param type $style
 * @return type
 */
function wpcf_icl_editor_cf_style_filter( $style, $cf_name ) {
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
    $fields = wpcf_admin_fields_get_fields();

    if ( empty( $fields ) ) {
        return $style;
    }

    $cf_name = substr( $cf_name, 6 );

    if ( strpos( $cf_name, WPCF_META_PREFIX ) == 0 ) {
        $cf_name = str_replace( WPCF_META_PREFIX, '', $cf_name );
    }
    if ( isset( $fields[$cf_name]['type'] ) && $fields[$cf_name]['type'] == 'textarea' ) {
        $style = 1;
    }
    if ( isset( $fields[$cf_name]['type'] ) && $fields[$cf_name]['type'] == 'wysiwyg' ) {
        $style = 2;
    }
    return $style;
}

/**
 * Bulk translation.
 */
function wpcf_admin_bulk_string_translation() {
    if ( !function_exists( 'icl_register_string' ) ) {
        return false;
    }
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/custom-types.php';
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/custom-taxonomies.php';

    // Register groups
    $groups = wpcf_admin_fields_get_groups();
    foreach ( $groups as $group_id => $group ) {
        wpcf_translate_register_string( 'plugin Types',
                'group ' . $group_id . ' name', $group['name'] );
        if ( isset( $group['description'] ) ) {
            wpcf_translate_register_string( 'plugin Types',
                    'group ' . $group_id . ' description', $group['description'] );
        }
    }

    // Register fields
    $fields = wpcf_admin_fields_get_fields();
    foreach ( $fields as $field_id => $field ) {
        wpcf_translate_register_string( 'plugin Types',
                'field ' . $field_id . ' name', $field['name'] );
        if ( isset( $field['description'] ) ) {
            wpcf_translate_register_string( 'plugin Types',
                    'field ' . $field_id . ' description', $field['description'] );
        }

        // For radios or select
        if ( !empty( $field['data']['options'] ) ) {
            foreach ( $field['data']['options'] as $name => $option ) {
                if ( $name == 'default' ) {
                    continue;
                }
                if ( isset( $option['title'] ) ) {
                    wpcf_translate_register_string( 'plugin Types',
                            'field ' . $field_id . ' option ' . $name . ' title',
                            $option['title'] );
                }
                if ( wpcf_wpml_field_is_translated( $field ) ) {
                    if ( isset( $option['value'] ) ) {
                        wpcf_translate_register_string( 'plugin Types',
                                'field ' . $field_id . ' option ' . $name . ' value',
                                $option['value'] );
                    }
                }
                if ( isset( $option['display_value'] ) ) {
                    wpcf_translate_register_string( 'plugin Types',
                            'field ' . $field_id . ' option ' . $name . ' display value',
                            $option['display_value'] );
                }
            }
        }

        if ( $field['type'] == 'checkbox' && (isset( $field['set_value'] ) && $field['set_value'] != '1') ) {
            // we need to translate the check box value to store
            wpcf_translate_register_string( 'plugin Types',
                    'field ' . $field_id . ' checkbox value',
                    $field['set_value'] );
        }

        if ( $field['type'] == 'checkbox' && !empty( $field['display_value_selected'] ) ) {
            // we need to translate the check box value to store
            wpcf_translate_register_string( 'plugin Types',
                    'field ' . $field_id . ' checkbox value selected',
                    $field['display_value_selected'] );
        }

        if ( $field['type'] == 'checkbox' && !empty( $field['display_value_not_selected'] ) ) {
            // we need to translate the check box value to store
            wpcf_translate_register_string( 'plugin Types',
                    'field ' . $field_id . ' checkbox value not selected',
                    $field['display_value_not_selected'] );
        }

        // Validation message
        if ( !empty( $field['data']['validate'] ) ) {
            foreach ( $field['data']['validate'] as $method => $validation ) {
                if ( !empty( $validation['message'] ) ) {
                    // Skip if it's same as default
                    $default_message = wpcf_admin_validation_messages( $method );
                    if ( $validation['message'] != $default_message ) {
                        wpcf_translate_register_string( 'plugin Types',
                                'field ' . $field_id . ' validation message ' . $method,
                                $validation['message'] );
                    }
                }
            }
        }
    }

    // Register types
    $custom_types = get_option( 'wpcf-custom-types', array() );
    foreach ( $custom_types as $post_type => $data ) {
        wpcf_custom_types_register_translation( $post_type, $data );
    }

    // Register taxonomies
    $custom_taxonomies = get_option( 'wpcf-custom-taxonomies', array() );
    foreach ( $custom_taxonomies as $taxonomy => $data ) {
        wpcf_custom_taxonimies_register_translation( $taxonomy, $data );
    }
}

function wpcf_post_relationship_set_translated_children( $parent_post_id ) {
    // WPML check if it's translation of a child
    // Fix up the parent if it's the child of a related post and it doesn't yet have a parent
    if ( function_exists( 'icl_object_id' ) ) {

        $post = get_post( $parent_post_id );

        global $sitepress;
        $ulanguage = $sitepress->get_language_for_element( $parent_post_id,
                'post_' . $post->post_type );

        remove_filter( 'get_post_metadata',
                'wpcf_wpml_relationship_meta_belongs_filter', 10, 4 );

        $original_post_id = icl_object_id( $parent_post_id, $post->post_type,
                false );
        if ( !empty( $original_post_id ) ) {
            // it has a translation
            $original_post = get_post( $original_post_id );
            if ( !empty( $original_post ) ) {

                // look for _wpcf_belongs_xxxx_id fields.

                $meta_key = '_wpcf_belongs_' . $original_post->post_type . '_id';

                global $wpdb;

                $query = sprintf(
                    'SELECT post_id FROM %s WHERE meta_key= %%s AND meta_value= %%d',
                    $wpdb->postmeta
                );
                $original_children = $wpdb->get_col( $wpdb->prepare( $query, $meta_key, $original_post_id ) );

                foreach ( $original_children as $child_id ) {

                    $child_post = get_post( $child_id );

                    // set if the child is tranlated
                    $translated_child_id = icl_object_id( $child_id,
                            $child_post->post_type, false, $ulanguage );
                    if ( $translated_child_id ) {
                        // Set the parent to be the translated parent
                        update_post_meta( $translated_child_id, $meta_key,
                                $parent_post_id );
                    }
                }
            }
        }

        add_filter( 'get_post_metadata',
                'wpcf_wpml_relationship_meta_belongs_filter', 10, 4 );
    }
}

function wpcf_post_relationship_set_translated_parent( $child_post_id ) {
    // WPML check if it's translation of a child
    // Fix up the parent if it's the child of a related post and it doesn't yet have a parent
    if ( function_exists( 'icl_object_id' ) ) {

        remove_filter( 'get_post_metadata',
                'wpcf_wpml_relationship_meta_belongs_filter', 10, 4 );

        $post = get_post( $child_post_id );
        $original_post_id = icl_object_id( $child_post_id, $post->post_type,
                false );
        if ( !empty( $original_post_id ) ) {
            // it has a translation
            $original_post = get_post( $original_post_id );
            if ( !empty( $original_post ) ) {

                // look for _wpcf_belongs_xxxx_id fields.

                $metas = get_post_custom( $original_post->ID );
                foreach ( $metas as $meta_key => $meta ) {
                    if ( strpos( $meta_key, '_wpcf_belongs_' ) !== false ) {
                        $meta_post = get_post( $meta[0] );
                        if ( !empty( $meta_post ) ) {
                            global $sitepress;
                            $ulanguage = $sitepress->get_language_for_element( $child_post_id,
                                    'post_' . $post->post_type );
                            $meta_translated_id = icl_object_id( $meta_post->ID,
                                    $meta_post->post_type, false, $ulanguage );
                            if ( $meta_translated_id ) {
                                // Set the parent to be the translated parent
                                update_post_meta( $child_post_id, $meta_key,
                                        $meta_translated_id );
                            }
                        }
                    }
                }
            }
        }

        add_filter( 'get_post_metadata',
                'wpcf_wpml_relationship_meta_belongs_filter', 10, 4 );
    }
}

/**
 * Returns translated '_wpcf_belongs_XXX_id' if any.
 *
 * @global type $sitepress
 * @param type $value
 * @param type $object_id
 * @param type $meta_key
 * @param type $single
 * @return type
 */
function wpcf_wpml_relationship_meta_belongs_filter( $value, $object_id,
        $meta_key, $single ) {
    // WPML check if it's translation of a child
    // Only force if meta is not already set
    if ( empty( $value ) && function_exists( 'icl_object_id' ) && strpos( $meta_key,
                    '_wpcf_belongs_' ) !== false ) {
        $post = get_post( $object_id );
        $original_post_id = icl_object_id( $object_id, $post->post_type, false );
        if ( !empty( $original_post_id ) ) {
            remove_filter( 'get_post_metadata',
                    'wpcf_wpml_relationship_meta_belongs_filter', 10, 4 );
            $original_post_meta = get_post_meta( $original_post_id, $meta_key,
                    true );
            add_filter( 'get_post_metadata',
                    'wpcf_wpml_relationship_meta_belongs_filter', 10, 4 );
            if ( !empty( $original_post_meta ) ) {
                $meta_post = get_post( $original_post_meta );
                if ( !empty( $meta_post ) ) {
                    global $sitepress;
                    $ulanguage = $sitepress->get_language_for_element( $object_id,
                            'post_' . $post->post_type );
                    $meta_translated_id = icl_object_id( $meta_post->ID,
                            $meta_post->post_type, false, $ulanguage );
                    if ( !empty( $meta_translated_id ) ) {
                        if ($single) {
                            $value = $meta_translated_id;
                        } else {
                            $value = array($meta_translated_id);
                        }

                    }
                }
            }
        }
    }

    return $value;
}

/**
 * Adjust translated IDs.
 *
 * @global type $sitepress
 * @param type $parent_post_id
 */
function wpcf_wpml_relationship_save_post_hook( $parent_post_id ){
    // WPML check if it's translation of a child
    // Fix up the parent if it's the child of a related post and it doesn't yet have a parent
    if ( function_exists( 'icl_object_id' ) ) {

        remove_filter( 'get_post_metadata',
                'wpcf_wpml_relationship_meta_belongs_filter', 10, 4 );

        $post = get_post( $parent_post_id );
        $original_post_id = icl_object_id( $parent_post_id, $post->post_type,
                false );
        if ( !empty( $original_post_id ) ) {
            // it has a translation
            $original_post = get_post( $original_post_id );
            if ( !empty( $original_post ) ) {

                // look for _wpcf_belongs_xxxx_id fields.

                $metas = get_post_custom( $original_post->ID );
                foreach ( $metas as $meta_key => $meta ) {
                    if ( strpos( $meta_key, '_wpcf_belongs_' ) !== false ) {
                        $meta_post = get_post( $meta[0] );
                        $exists = get_post_meta( $parent_post_id, $meta_key,
                                true );
                        if ( !empty( $meta_post ) && empty( $exists ) ) {
                            global $sitepress;
                            $ulanguage = $sitepress->get_language_for_element( $parent_post_id,
                                    'post_' . $post->post_type );
                            $meta_translated_id = icl_object_id( $meta_post->ID,
                                    $meta_post->post_type, false, $ulanguage );
                            // Only force if meta is not already set
                            if ( !empty( $meta_translated_id ) ) {
                                update_post_meta( $parent_post_id, $meta_key,
                                        $meta_translated_id );
                            }
                        }
                    }
                }
            }
        }

        add_filter( 'get_post_metadata',
                'wpcf_wpml_relationship_meta_belongs_filter', 10, 4 );
    }
}

/**
 * Registers translation data.
 *
 * @param type $post_type
 * @param type $data
 */
function wpcf_custom_types_register_translation( $post_type, $data ) {
    if ( !function_exists( 'icl_register_string' ) ) {
        return $data;
    }
    if ( isset( $data['description'] ) ) {
        wpcf_translate_register_string( 'Types-CPT',
                $post_type . ' description', $data['description'] );
    }
    wpcf_wpml_register_labels( $post_type, $data, 'post_type' );
}

/**
 * Registers translation data.
 *
 * @param type $post_type
 * @param type $data
 */
function wpcf_custom_taxonimies_register_translation( $taxonomy, $data ) {
    if ( !function_exists( 'icl_register_string' ) ) {
        return $data;
    }
    if ( isset( $data['description'] ) ) {
        wpcf_translate_register_string( 'Types-TAX', $taxonomy . ' description',
                $data['description'] );
    }
    wpcf_wpml_register_labels( $taxonomy, $data, 'taxonomy' );
}

/**
 * Registers labels.
 *
 * @param type $prefix
 * @param type $data
 * @param type $context
 */
function wpcf_wpml_register_labels( $prefix, $data, $context = 'post' ) {
    foreach ( $data['labels'] as $label => $string ) {
        switch ( $context ) {
            case 'taxonomies':
            case 'taxonomy':
            case 'tax':
                $default = wpcf_custom_taxonomies_default();
                if ( $label == 'name' || $label == 'singular_name' ) {
                    wpcf_translate_register_string( 'Types-TAX',
                            $prefix . ' ' . $label, $string );
                    continue;
                }
                if ( isset( $default['labels'][$label] ) && $string == $default['labels'][$label] ) {
                    wpcf_translate_register_string( 'Types-TAX', $label, $string );
                } else {
                    wpcf_translate_register_string( 'Types-TAX',
                            $prefix . ' ' . $label, $string );
                }
                break;

            default:
                $default = wpcf_custom_types_default();

                // Name and singular_name
                if ( $label == 'name' || $label == 'singular_name' ) {
                    wpcf_translate_register_string( 'Types-CPT',
                            $prefix . ' ' . $label, $string );
                    continue;
                }

                // Check others for defaults
                if ( isset( $default['labels'][$label] ) && $string == $default['labels'][$label] ) {
                    // Register default translation
                    wpcf_translate_register_string( 'Types-CPT', $label, $string );
                } else {
                    wpcf_translate_register_string( 'Types-CPT',
                            $prefix . ' ' . $label, $string );
                }
                break;
        }
    }
}

/**
 * Translates data.
 *
 * @param type $post_type
 * @param type $data
 */
function wpcf_wpml_post_types_translate( $data, $post_type ) {
    if ( !function_exists( 'icl_t' ) ) {
        return $data;
    }
    $default = wpcf_custom_types_default();
    if ( !empty( $data['description'] ) ) {
        $data['description'] = wpcf_translate( $post_type . ' description',
                $data['description'], 'Types-CPT' );
    }
    foreach ( $data['labels'] as $label => $string ) {
        if ( $label == 'name' || $label == 'singular_name' ) {
            $data['labels'][$label] = wpcf_translate( $post_type . ' ' . $label,
                    $string, 'Types-CPT' );
            continue;
        }
        if ( !isset( $default['labels'][$label] ) || $string !== $default['labels'][$label] ) {
            $data['labels'][$label] = wpcf_translate( $post_type . ' ' . $label,
                    $string, 'Types-CPT' );
        } else {
            $data['labels'][$label] = wpcf_translate( $label, $string,
                    'Types-CPT' );
        }
    }
    return $data;
}

/**
 * Translates data.
 *
 * @param type $taxonomy
 * @param type $data
 */
function wpcf_wpml_taxonomy_translate( $data, $taxonomy ) {
    if ( !function_exists( 'icl_t' ) ) {
        return $data;
    }
    $default = wpcf_custom_taxonomies_default();
    if ( !empty( $data['description'] ) ) {
        $data['description'] = wpcf_translate( $taxonomy . ' description',
                $data['description'], 'Types-TAX' );
    }
    foreach ( $data['labels'] as $label => $string ) {
        if ( $label == 'name' || $label == 'singular_name' ) {
            $data['labels'][$label] = wpcf_translate( $taxonomy . ' ' . $label,
                    $string, 'Types-TAX' );
            continue;
        }
        if ( !isset( $default['labels'][$label] ) || $string !== $default['labels'][$label] ) {
            $data['labels'][$label] = wpcf_translate( $taxonomy . ' ' . $label,
                    $string, 'Types-TAX' );
        } else {
            $data['labels'][$label] = wpcf_translate( $label, $string,
                    'Types-TAX' );
        }
    }
    return $data;
}

/**
 * Filters terms on Group form filter.
 *
 * Do not complicate this, just replace term ID
 * with corresponding original ID.
 *
 * @param array $terms array of terms objects.
 * @return array
 */
function wpcf_wpml_group_form_filter_terms_filter( $terms ) {

    global $sitepress, $wpdb, $wpcf;

    foreach ( $terms as $k => $term ) {

        // Mark it
        $wpcf->wpml->group_form_filter_taxonomies_filtered[$term->term_taxonomy_id] = $term->term_taxonomy_id;

        // Only on other language
        if ( $sitepress->get_default_language() != $sitepress->get_current_language() ) {

            // Get original term
            $original_term_id = icl_object_id( $term->term_id, $term->taxonomy,
                    true, $sitepress->get_default_language() );

            // Only if translation found
            if ( $term->term_id != $original_term_id ) {
                /*
                 *
                 * Note that WPML will return term_id.
                 */
                $_term = $wpdb->get_row( $wpdb->prepare( "SELECT t.*, tt.* FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy = %s AND t.term_id = %d LIMIT 1",
                                $term->taxonomy, $original_term_id ) );


                if ( !empty( $_term ) ) {

                    // Mark it
                    $wpcf->wpml->group_form_filter_taxonomies_translated[$term->term_taxonomy_id] = array(
                        'original_term_taxonomy_id' => $term->term_taxonomy_id,
                        'term_taxonomy_id' => $_term->term_taxonomy_id,
                        'term_id' => $_term->term_id,
                    );

                    // Adjust Ids
                    $term->term_taxonomy_id = $_term->term_taxonomy_id;
                    $term->term_id = $_term->term_id;
                } else {
                    $wpcf->debug->errors['wpml']['missing_original_term'][] = $term;
                }
            }

            $terms[$k] = $term;
        }
    }

    return $terms;
}

/**
 * Adjusts term_id in group filter in post edit screen.
 *
 * @global type $sitepress
 * @global object $wpdb
 * @param type $group
 * @return type
 */
function wpcf_wpml_post_group_filter_taxonomies( $group, $post, $context,
        $post_terms ) {

    global $sitepress, $wpdb;

    if ( empty( $post->ID ) ) {
        return $group;
    }

    $post_language = $sitepress->get_language_for_element( $post->ID,
            'post_' . $post->post_type );

    // Only on other language
    if ( empty( $post_language ) || $sitepress->get_default_language() == $post_language ) {
        return $group;
    }

    if ( !empty( $group['_wp_types_group_terms'] ) ) {
        foreach ( $group['_wp_types_group_terms'] as $key => $term ) {

            // Skip 'all' setting
            if ( strval( $term ) === 'all' ) {
                continue;
            }

            // Get term
            $_term = $wpdb->get_row( $wpdb->prepare( "SELECT t.*, tt.* FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.term_taxonomy_id = %d LIMIT 1",
                            $term ) );
            if ( !empty( $_term ) ) {
                // Get translated term
                $translated_term_id = icl_object_id( $_term->term_id,
                        $_term->taxonomy, true, $post_language );

                if ( $translated_term_id != $_term->term_id ) {
                    $translated_term = $wpdb->get_row( $wpdb->prepare( "SELECT t.*, tt.* FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id WHERE t.term_id = %d LIMIT 1",
                                    $translated_term_id ) );
                    $group['_wp_types_group_terms'][$key] = $translated_term->term_taxonomy_id;
                }
            }
        }
    }

    return $group;
}

/**
 * Append missing terms from saved filter.
 *
 * If terms are added in another language and still valid,
 * append them as 'hidden' fields. That way we keep terms in sync if swithing
 * languages in admin area.
 *
 * @global type $wpcf
 * @param type $form
 * @param type $settings
 * @return string
 */
function wpcf_wpml_group_filter_add_missing_terms( $form, $settings ) {

    global $wpcf;

    $add_terms = array();

    // Loop over saved settings and see if omitted from form
    if ( !empty( $settings['taxonomies'] ) ) {
        foreach ( $settings['taxonomies'] as $taxonomy => $terms ) {
            foreach ( $terms as $term_taxonomy_id => $term ) {
                // skip 'Uncategorized' because WPML handles each per language
                if ( $term_taxonomy_id == 1 ) {
                    continue;
                }
                /*
                 * Check if term is filtered out, but existing in saved option
                 * and if it's not translated already.
                 */
                if ( !isset( $wpcf->wpml->group_form_filter_taxonomies_filtered[$term_taxonomy_id] ) ) {
                    $_add = true;
                    foreach ( $wpcf->wpml->group_form_filter_taxonomies_translated as $_translated_term ) {
                        if ( $_translated_term['term_taxonomy_id'] == $term_taxonomy_id ) {
                            $_add = false;
                        }
                    }

                    if ( $_add ) {
                        // Now check if it's still valid
                        $term_ok = get_term_by( 'id', $term_taxonomy_id,
                                $taxonomy );
                        // Add form extension to terms radios
                        if ( !empty( $term_ok ) ) {
                            $add_terms['wpml_add_terms_' . $term_taxonomy_id] = array(
                                '#type' => 'hidden',
                                '#name' => 'wpcf[group][taxonomies]['
                                . $taxonomy . '][' . $term_taxonomy_id . ']',
                                '#value' => $term_taxonomy_id,
                            );
                        }
                    }
                }
            }
        }
    }

    return $form + $add_terms;
}

/**
 * Sync when slug changed.
 *
 * @global type $sitepress
 * @global type $sitepress_settings
 * @param type $new_slug
 * @param type $old_slug
 */
function wpcf_wpml_post_type_renamed( $new_slug, $old_slug ) {
    global $sitepress, $sitepress_settings, $wpdb;
    if ( isset( $sitepress_settings['custom_posts_sync_option'][$old_slug] ) ) {
        $sitepress_settings['custom_posts_sync_option'][$new_slug] = $sitepress_settings['custom_posts_sync_option'][$old_slug];
        unset( $sitepress_settings['custom_posts_sync_option'][$old_slug] );
        $sitepress->save_settings( $sitepress_settings );
        /*
         * Update slug in icl_strings table
         */
        $wpdb->update( $wpdb->prefix . 'icl_strings',
                array(
            'name' => 'URL slug: ' . $new_slug,
            'value' => $new_slug,
                ),
                array(
            'name' => 'URL slug: ' . $old_slug,
            'context' => 'URL slugs - wpcf',
                )
        );
    }
}

/**
 * Sync when slug changed.
 *
 * @global type $sitepress
 * @global type $sitepress_settings
 * @param type $new_slug
 * @param type $old_slug
 */
function wpcf_wpml_taxonomy_renamed( $new_slug, $old_slug ) {
    global $sitepress, $sitepress_settings, $wpdb;
    if ( isset( $sitepress_settings['taxonomies_sync_option'][$old_slug] ) ) {
        $sitepress_settings['taxonomies_sync_option'][$new_slug] = $sitepress_settings['taxonomies_sync_option'][$old_slug];
        unset( $sitepress_settings['taxonomies_sync_option'][$old_slug] );
        $sitepress->save_settings( $sitepress_settings );

        /*
         * Update term in WPML table as used in WPML
         * wpml/menu/troubleshooting.php
         * case 'link_taxonomy':
         */
        $wpdb->update( $wpdb->prefix . 'icl_translations',
                array('element_type' => 'tax_' . $new_slug),
                array('element_type' => 'tax_' . $old_slug) );
    }
}

/**
 * Relationship save child language.
 *
 * @global type $sitepress
 * @param type $child
 * @param type $parent
 */
function wpcf_wpml_relationship_save_child( $child, $parent ) {
    global $sitepress;
    $lang_details = $sitepress->get_element_language_details( $parent->ID,
            'post_' . $parent->post_type );
    if ( $lang_details ) {
        $sitepress->set_element_language_details( $child->ID,
                'post_' . $child->post_type, null, $lang_details->language_code );
    }
}

/**
 * Checks if field is copied on post edit screen.
 *
 * @global type $sitepress
 * @param type $field
 * @param type $post
 * @return boolean
 */
function wpcf_wpml_field_is_copied( $field, $post = null ) {
    if ( defined( 'ICL_SITEPRESS_VERSION' ) && defined( 'WPML_TM_VERSION' ) && !defined( 'DOING_AJAX' ) ) {
        if ( !wpcf_wpml_post_is_original( $post ) ) {
            return wpcf_wpml_have_original( $post ) && wpcf_wpml_field_is_copy( $field );
        }
    }
    return false;
}

/**
 * Checks if field is copied on profile edit screen.
 *
 * @global type $sitepress
 * @param type $field
 * @return boolean
 */
function wpcf_wpml_is_translated_profile_page( $field ) {
    if ( defined( 'ICL_SITEPRESS_VERSION' ) && defined( 'WPML_TM_VERSION' ) && !defined( 'DOING_AJAX' ) ) {
        global $sitepress;
        if ( $sitepress->get_default_language() !== $sitepress->get_current_language() ) {
            return wpcf_wpml_field_is_copy( $field );
        }
    }
    return false;
}

/**
 * Checks if field is copied.
 *
 * @param type $field
 * @return type
 */
function wpcf_wpml_field_is_copy( $field ) {
    if ( !defined( 'WPML_TM_VERSION' ) ) return false;
    return isset( $field['wpml_action'] ) && $field['wpml_action'] === 1;
}

/**
 * Checks if field is translated.
 *
 * @param type $field
 * @return type
 */
function wpcf_wpml_field_is_translated( $field ) {
    if ( !defined( 'WPML_TM_VERSION' ) ) return false;
    return isset( $field['wpml_action'] ) && $field['wpml_action'] === 2;
}

/**
 * Checks if field is ignored.
 *
 * @param type $field
 * @return type
 */
function wpcf_wpml_field_is_ignored( $field ) {
    if ( !defined( 'WPML_TM_VERSION' ) ) return true;
    return !wpcf_wpml_field_is_copy( $field ) && !wpcf_wpml_field_is_translated( $field );
}

/**
 * Determine if post is in original language.
 *
 * @global type $sitepress
 * @global type $pagenow
 * @param type $field
 * @param type $post
 * @return boolean
 */
function wpcf_wpml_post_is_original( $post = null ) {
    if ( defined( 'ICL_SITEPRESS_VERSION' ) ) {
        global $sitepress, $pagenow;
        // WPML There is no lang on new post
        if ( $pagenow == 'post-new.php' ) {
            $post_type = wpcf_admin_get_edited_post_type();
            $current_lang = isset( $_GET['lang'] ) ? sanitize_text_field( $_GET['lang'] ) : $sitepress->get_current_language();
            if ( in_array( $post_type,
                            array_keys( $sitepress->get_translatable_documents() ) ) ) {
                return $sitepress->get_default_language() == $current_lang;
            }
        } else {
            if ( empty( $post->ID ) ) {
                $post = wpcf_admin_get_edited_post();
            }
            if ( !empty( $post->ID ) ) {
                if ( in_array( $post->post_type,
                                array_keys( $sitepress->get_translatable_documents() ) ) ) {
                    $post_lang = $sitepress->get_element_language_details( $post->ID,
                            'post_' . $post->post_type );
                    // Suggestion from Black Studio
                    // http://wp-types.com/forums/topic/major-bug-on-types-when-setting-fields-to-be-copied-wo-wpml-translated-posts/#post-146182
                    if ( isset( $post_lang->source_language_code ) ) {
                        return $post_lang->source_language_code == null;

//                        return $sitepress->get_default_language() == $post_lang->language_code;
                    }
                }
            }
        }
    }
    return true;
}

function wpcf_wpml_have_original( $post = null ) {
    static $cache = array();
    global $pagenow;
    $res = false;

    // WPML There is no lang on new post
    if ( $pagenow == 'post-new.php' ) {
        return isset( $_GET['trid'] );
    }

    if ( empty( $post->ID ) ) {
        $post = wpcf_admin_get_edited_post();
    }
    if ( !empty( $post->ID ) ) {
        $cache_key = $post->ID;
        if ( isset( $cache[$cache_key] ) ) {
            return $cache[$cache_key];
        }
        global $wpdb, $sitepress;
        $post_lang = $sitepress->get_element_language_details( $post->ID,
                'post_' . $post->post_type );
        if ( isset( $post_lang->language_code ) ) {

            $sql = $wpdb->prepare(
                "SELECT trid FROM {$wpdb->prefix}icl_translations
                WHERE language_code = %s
                AND element_id = %d
                AND element_type = %s
                AND source_language_code IS NOT NULL",
                $post_lang->language_code,
                $post->ID,
                sprintf('post_%s', $post->post_type)
            );
            $res = (bool) $wpdb->get_var( $sql );
        }
        $cache[$cache_key] = $res;
    }
    return $res;
}

/**
 * Removes temporarily hook to avoid deleting custom fields from translated post.
 *
 * @global type $sitepress
 * @param type $post
 * @param type $field
 */
function wpcf_wpml_remove_delete_postmeta_hook_remove( $post, $field ) {
    if ( wpcf_wpml_field_is_translated( $field ) ) {
        global $sitepress;
        remove_action( 'delete_postmeta', array($sitepress, 'delete_post_meta') );
    }
}

/**
 * Re-enables hook.
 *
 * @see wpcf_wpml_remove_delete_postmeta_hook_remove()
 * @global type $sitepress
 * @param type $post
 * @param type $field
 */
function wpcf_wpml_remove_delete_postmeta_hook_add( $post, $field ) {
    if ( wpcf_wpml_field_is_translated( $field ) ) {
        global $sitepress;
        add_action( 'delete_postmeta', array($sitepress, 'delete_post_meta') );
    }
}

/**
 * Removes temporarily hook to fix asving repetitive fields.
 *
 * @global type $sitepress
 * @param type $post
 * @param type $field
 */
function wpcf_wpml_sync_postmeta_hook_remove( $post, $field ) {
    global $sitepress;
    remove_action( 'updated_post_meta', array($sitepress, 'update_post_meta'),
            100, 4 );
    remove_action( 'added_post_meta', array($sitepress, 'update_post_meta'),
            100, 4 );
    remove_action( 'updated_postmeta', array($sitepress, 'update_post_meta'),
            100, 4 ); // ajax
    remove_action( 'added_postmeta', array($sitepress, 'update_post_meta'), 100,
            4 ); // ajax
}

/**
 * Re-enables hook.
 *
 * @see wpcf_wpml_remove_delete_postmeta_hook_remove()
 * @global type $sitepress
 * @param type $post
 * @param type $field
 */
function wpcf_wpml_sync_postmeta_hook_add( $post, $field ) {
    global $sitepress;
    add_action( 'updated_post_meta', array($sitepress, 'update_post_meta'), 100,
            4 );
    add_action( 'added_post_meta', array($sitepress, 'update_post_meta'), 100, 4 );
    add_action( 'updated_postmeta', array($sitepress, 'update_post_meta'), 100,
            4 ); // ajax
    add_action( 'added_postmeta', array($sitepress, 'update_post_meta'), 100, 4 ); // ajax
    add_action( 'delete_postmeta', array($sitepress, 'delete_post_meta') ); // ajax
}

function wpcf_wpml_warnings_init()
{
    if(!defined('WPML_ST_PATH') || !class_exists( 'ICL_AdminNotifier' )) return;

    /**
     * check is configuration done?!
     */
    global $sitepress, $sitepress_settings;
    if ( function_exists('icl_get_setting') && icl_get_setting('st') ) {
        return;
    }

    /**
     * do that only when version of WPML is lower then 3.2
     */
    if ( defined('ICL_SITEPRESS_VERSION') && version_compare( ICL_SITEPRESS_VERSION, '3.2', '<' ) ) {
        if (isset($sitepress_settings[ 'st' ]) && $sitepress->get_default_language() != $sitepress_settings[ 'st' ][ 'strings_language' ] ) {
            wp_types_default_language_warning();
        } elseif (isset($sitepress_settings[ 'st' ]) && $sitepress_settings[ 'st' ][ 'strings_language' ] != 'en' ) {
            wp_types_st_language_warning();
        } else {
            ICL_AdminNotifier::removeMessage( 'wp_types_default_language_warning' );
            ICL_AdminNotifier::removeMessage( 'wp_types_st_language_warning' );
        }
    }
}

function wpcf_wpml_warning()
{
	if(!defined('WPML_ST_PATH') || !class_exists( 'ICL_AdminNotifier' )) return;
	ICL_AdminNotifier::displayMessages('wp-types');
}

/**
 * Handle notification messages for WPML String Translation when default language is != 'en'
 */
function wp_types_default_language_warning()
{
	if ( class_exists( 'ICL_AdminNotifier' ) && defined( 'ICL_SITEPRESS_VERSION' ) ) {
		ICL_AdminNotifier::removeMessage( 'wp_types_st_language_warning' );
		static $called = false;
		if ( !$called ) {
			global $sitepress;
			$languages             = $sitepress->get_active_languages();
			$translation_languages = array();
			foreach ( $languages as $language ) {
				if ( $language[ 'code' ] != 'en' ) {
					$translation_languages[ ] = $language[ 'display_name' ];
				}
			}
			$last_translation_language = $translation_languages[ count( $translation_languages ) - 1 ];
			unset( $translation_languages[ count( $translation_languages ) - 1 ] );
			$translation_languages_list = is_Array( $translation_languages ) ? implode( ', ', $translation_languages ) : $translation_languages;

			$message = 'Because your default language is not English, you need to enter all strings in English and translate them to %s and %s.';
			$message .= ' ';
			$message .= '<strong><a href="%s" target="_blank">Read more</a></strong>';

			$message = __( $message, 'Read more string-translation-default-language-not-english', 'wpml-string-translation' );
			$message = sprintf( $message, $translation_languages_list, $last_translation_language, 'http://wpml.org/faq/string-translation-default-language-not-english/' );

			$fallback_message = _( '<a href="%s" target="_blank">How to translate strings when default language is not English</a>' );
			$fallback_message = sprintf( $fallback_message, 'http://wpml.org/faq/string-translation-default-language-not-english/' );

			ICL_AdminNotifier::addMessage( 'wp_types_default_language_warning', $message, 'icl-admin-message icl-admin-message-information', true, $fallback_message, false, 'wp-types' );
			$called = true;
		}
	}
}

/**
 * Handle notification messages for WPML String Translation when default language and ST language is != 'en'
 */
function wp_types_st_language_warning()
{
	global $sitepress, $sitepress_settings;

	if ( class_exists( 'ICL_AdminNotifier' ) && defined( 'ICL_SITEPRESS_VERSION' ) ) {
		ICL_AdminNotifier::removeMessage( 'wp_types_default_language_warning' );
		static $called = false;
		if ( !$called && isset($sitepress_settings[ 'st' ])) {
			$st_language_code = $sitepress_settings[ 'st' ][ 'strings_language' ];
			$st_language = $sitepress->get_display_language_name($st_language_code, $sitepress->get_admin_language());

			$st_page_url = admin_url('admin.php?page='.WPML_ST_FOLDER.'/menu/string-translation.php');

			$message = 'The strings language in your site is set to %s instead of English. ';
			$message .= 'This means that all English texts that are hard-coded in PHP will appear when displaying content in %s.';
			$message .= ' ';
			$message .= '<strong><a href="%s" target="_blank">Read more</a> | ';
			$message .= '<a href="%s#icl_st_sw_form">Change strings language</a></strong>';

			$message = __( $message, 'wpml-string-translation' );
			$message = sprintf( $message, $st_language, $st_language, 'http://wpml.org/faq/string-translation-default-language-not-english/', $st_page_url );

			$fallback_message = _( '<a href="%s" target="_blank">How to translate strings when default language is not English</a>' );
			$fallback_message = sprintf( $fallback_message, 'http://wpml.org/faq/string-translation-default-language-not-english/' );

			ICL_AdminNotifier::addMessage( 'wp_types_st_language_warning', $message, 'icl-admin-message icl-admin-message-warning', true, $fallback_message, false, 'wp-types' );
			$called = true;
		}
	}
}


// Fix to set correct parent and children for duplicated posts
function wpcf_wpml_duplicated_post_relationships( $original_post_id, $lang,
        $postarr, $duplicate_post_id ) {
    require_once WPCF_EMBEDDED_ABSPATH . '/includes/post-relationship.php';
    wpcf_post_relationship_set_translated_parent( $duplicate_post_id );
    wpcf_post_relationship_set_translated_children( $duplicate_post_id );
}
