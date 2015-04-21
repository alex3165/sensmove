<?php
/**
 * Types API
 * 
 * API should be call to /classes/ wrapper.
 * Use global $wpcf_api to add new objects.
 * 
 * @since Types 1.2
 */

/**
 * Gets field.
 *
 * @param string $field
 * @param string $meta_type
 * @return array
 */
function types_get_field( $field, $meta_type = 'postmeta' ) {
    static $cache = array();
    $cache_key = md5( strval( $field ) . strval( $meta_type ) );
    if ( isset( $cache[$cache_key] ) ) {
        return $cache[$cache_key];
    }
    WPCF_Loader::loadInclude( 'fields' );
    $meta_type = $meta_type == 'usermeta' ? 'wpcf-usermeta' : 'wpcf-fields';
    $cache[$cache_key] = wpcf_admin_fields_get_field( strval( $field ), false,
            false, false, $meta_type );
    return $cache[$cache_key];
}

/**
 * Get fields.
 * 
 * @global type $wpcf
 * @param mixed $args Various args
 * @param string $toolset Useful in hooks
 * @return All filtered fields
 */
function types_get_fields( $args = array(), $toolset = 'types' ){
    static $cache = array();
    $cache_key = md5( serialize( func_get_args() ) );
    if ( isset( $cache[$cache_key] ) ) {
        return $cache[$cache_key];
    }
    $fields = WPCF_Fields::getFields( $args, $toolset );
    $cache[$cache_key] = $fields;
    return $cache[$cache_key];
}

/**
 * Gets field meta value.
 * 
 * @global type $wpcf
 * @param type $field
 * @param type $post_id
 * @return type 
 */
function types_get_field_meta_value( $field, $post_id = null, $raw = false ) {
    return wpcf_api_field_meta_value( $field, $post_id, $raw );
}

/**
 * Gets field meta value.
 * 
 * @global type $wpcf
 * @param type $field
 * @param type $post_id
 * @return type 
 */
function wpcf_api_field_meta_value( $field, $post_id = null, $raw = false ) {

    static $cache = array();
    $cache_key = md5( serialize( func_get_args() ) );
    if ( isset( $cache[$cache_key] ) ) {
        return $cache[$cache_key];
    }

    global $wpcf;

    // Get field
    if ( !is_array( $field ) ) {
        $field = types_get_field( $field );
        if ( empty( $field ) ) {
            return NULL;
        }
    }

    // See if repetitive
    if ( wpcf_admin_is_repetitive( $field ) ) {
        return wpcf_api_field_meta_value_repetitive( $field, $post_id );
    }

    // Set post
    if ( !is_null( $post_id ) ) {
        if ( !$post_id = absint( $post_id ) ) {
            return NULL;
        }
        $post = get_post( $post_id );
    } else {
        global $post;
    }
    if ( empty( $post->ID ) ) {
        return NULL;
    }

    // Set field
    $wpcf->field->set( $post, $field );
    $value = $raw ? $wpcf->field->__meta : $wpcf->field->meta;
    $cache[$cache_key] = $value;

    return $value;
}

/**
 * Gets field meta value.
 * 
 * @global type $wpcf
 * @param type $field
 * @param type $post_id
 * @return type 
 */
function types_field_meta_value( $field, $post_id = null, $raw = false ) {
    return wpcf_api_field_meta_value( $field, $post_id, $raw );
}

/**
 * Gets repetitive meta value.
 * 
 * @global type $wpcf
 * @param type $field
 * @param type $post_id
 * @param type $meta_id
 * @return type 
 */
function types_field_get_meta_value_repetitive( $field, $post_id = null,
        $meta_id = null ) {
    return wpcf_api_field_meta_value_repetitive( $field, $post_id, $meta_id );
}

/**
 * Check if field is repetitive.
 * 
 * @param type $type
 * @return type 
 */
function types_is_repetitive( $field ) {

    // Get field
    if ( !is_array( $field ) ) {
        $field = types_get_field( $field );
        if ( empty( $field ) ) {
            return NULL;
        }
    }

    return wpcf_admin_is_repetitive( $field );
}

/**
 * Get fields by group.
 * 
 * Returns array of fields and their values:
 * array( 'myfield' => 'some text' )
 * 
 * @global type $wpcf
 * @staticvar array $cache
 * @param type $group_id
 * @param type $active
 * @return array
 */
function types_get_fields_by_group( $group, $only_active = 'only_active' ){
    static $cache = array();
    $cache_key = md5( serialize( func_get_args() ) );
    if ( isset( $cache[$cache_key] ) ) {
        return $cache[$cache_key];
    }
    $results = array();
    $only_active = $only_active === 'only_active' || $only_active === true ? true : false;
    $group = wpcf_admin_fields_get_group( $group );
    if ( !empty( $group['id'] ) ) {
        if ( $only_active && $group['is_active'] ) {
            $fields = wpcf_admin_fields_get_fields_by_group( $group['id'],
                    'slug' );
            foreach ( $fields as $field ) {
                $results[$field['id']] = wpcf_api_field_meta_value( $field );
            }
        }
    }
    $cache[$cache_key] = $results;
    return $cache[$cache_key];
}

/**
 * Gets posts that belongs to current post.
 * 
 * @global type $post
 * @param type $post_type
 * @param type $args
 * @return type 
 */
function types_child_posts( $post_type, $args = array() ) {

    static $cache = array();
    
    if ( isset( $args['post_id'] ) ) {
        $post = $args['post_id'] != '0' ? get_post( $args['post_id'] ) : null;
    } else {
        global $post;
    }

    if ( empty( $post->ID ) ) {
        return array();
    }
    
    $cache_key = md5( $post->ID . serialize( func_get_args() ) );
    if ( isset( $cache[$cache_key] ) ) {
        return $cache[$cache_key];
    }

    global $wp_post_types;

    // WP allows querying inactive post types
    if ( !isset( $wp_post_types[$post_type] )
            || !$wp_post_types[$post_type]->publicly_queryable ) {
        return array();
    }

    $defaults = array('post_status' => array('publish'));
    $args = wp_parse_args( $args, $defaults );

    WPCF_Loader::loadModel( 'relationship' );
    WPCF_Loader::loadInclude( 'fields-post' );
    $child_posts = WPCF_Relationship_Model::getChildrenByPostType( $post,
                    $post_type, array(), array(), $args );

    foreach ( $child_posts as $child_post_key => $child_post ) {
        $child_posts[$child_post_key]->fields = array();
        $groups = wpcf_admin_post_get_post_groups_fields( $child_post );
        foreach ( $groups as $group ) {
            if ( !empty( $group['fields'] ) ) {
                // Process fields
                foreach ( $group['fields'] as $k => $field ) {
                    $data = null;
                    if ( types_is_repetitive( $field ) ) {
                        $data = wpcf_get_post_meta( $child_post->ID,
                                wpcf_types_get_meta_prefix( $field ) . $field['slug'],
                                false ); // get all field instances
                    } else {
                        $data = wpcf_get_post_meta( $child_post->ID,
                                wpcf_types_get_meta_prefix( $field ) . $field['slug'],
                                true ); // get single field instance
                        // handle checkboxes which are one value serialized
                        if ( $field['type'] == 'checkboxes' && !empty( $data ) ) {
                            $data = maybe_unserialize( $data );
                        }
                    }
                    if ( !is_null( $data ) ) {
                        $child_posts[$child_post_key]->fields[$k] = $data;
                    }
                }
            }
        }
    }
    $cache[$cache_key] = $child_posts;

    return $child_posts;
}

/**
 * Used for processing conditional statements.
 * 
 * Wrapper for wpcf_cd_post_edit_field_filter()
 * core function.
 * 
 * @param type $element
 * @param type $field
 * @param type $post_id
 * @param string $context
 * @return type 
 */
function types_conditional_evaluate( $field, $post_id = null ) {
    return wpcf_conditional_evaluate( $post_id, $field );
}

/**
 * Create post relationship.
 * 
 * @param integer $post_id
 * @param array $posts array( $post_type => $post_id )
 * @param string $action set_child | set_parent
 */
function types_create_relationship( $post_id, $posts = array(), $error = false ) {
    $updated = wpcf_pr_admin_update_belongs( $post_id, $posts );
    if ( is_wp_error( $updated ) ) {
        return $error? $updated : FALSE;
    }
    return TRUE;
}

/**
 * Gets repetitive meta value.
 * 
 * @global type $wpcf
 * @param type $field
 * @param type $post_id
 * @param type $meta_id
 * @return type 
 */
function wpcf_api_field_meta_value_repetitive( $field, $post_id = null,
        $meta_id = null ) {

    static $cache = array();
    $cache_key = md5( serialize( func_get_args() ) );
    if ( isset( $cache[$cache_key] ) ) {
        return $cache[$cache_key];
    }

    global $wpcf;

    // Get field
    if ( !is_array( $field ) ) {
        $field = types_get_field( $field );
        if ( empty( $field ) ) {
            return NULL;
        }
    }

    // Set post
    if ( !is_null( $post_id ) ) {
        if ( !$post_id = absint( $post_id ) ) {
            return NULL;
        }
        $post = get_post( $post_id );
    } else {
        global $post;
    }
    if ( empty( $post->ID ) ) {
        return NULL;
    }

    // Set field
    $wpcf->repeater->set( $post, $field );
    $meta = $wpcf->repeater->meta;
    $values = array();

    // See if single
    if ( !wpcf_admin_is_repetitive( $field ) ) {
        $values = isset( $meta['single'] ) ? $meta['single'] : NULL;
    } else if ( !is_null( $meta_id ) && isset( $meta['by_meta_id'][$meta_id] ) ) {
        // Return single repetitive field value if meta_id specified
        $values = $meta['by_meta_id'][$meta_id];
    } else if ( isset( $wpcf->repeater->meta['custom_order'] ) ) {
        // Return ordered
        $values = $wpcf->repeater->meta['custom_order'];
    } else if ( isset( $wpcf->repeater->meta['by_meta_id'] ) ) {
        // Return by_meta_id
        $values = $wpcf->repeater->meta['by_meta_id'];
    }
    $cache[$cache_key] = $values;

    return $values;
}

/**
 * Gets repetitive meta value.
 * 
 * @global type $wpcf
 * @param type $field
 * @param type $post_id
 * @param type $meta_id
 * @return type 
 */
function types_field_meta_value_repetitive( $field, $post_id = null,
        $meta_id = null ) {
    return wpcf_api_field_meta_value_repetitive( $field, $post_id, $meta_id );
}

/**
 * Used for processing conditional statements.
 * 
 * Wrapper for wpcf_cd_post_edit_field_filter()
 * core function.
 * 
 * @param type $element
 * @param type $field
 * @param type $post
 * @param string $context
 * @return type 
 */
function wpcf_conditional_evaluate( $post = null, $field ) {

    // Set post
    if ( is_null( $post ) ) {
        global $post;
    } else {
        $post = get_post( $post );
    }
    if ( empty( $post->ID ) ) {
        return NULL;
    }

    // Get field
    if ( !is_array( $field ) ) {
        $field = types_get_field( $field );
        if ( empty( $field ) ) {
            return NULL;
        }
    }

    global $wpcf;
    $e = clone $wpcf->conditional;
    $e->set( $post, $field );
    return $e->evaluate();
}

/**
 * Returns meta prefix.
 * 
 * @param array $field
 */
function types_meta_prefix( $field = array() ) {
    return wpcf_types_get_meta_prefix( $field );
}

/*
 * 
 * 
 * 
 * REVIEW NEEDED
 */

/**
 * Saves repeater fields.
 * 
 * Repeater class checks:
 * $_POST['wpcf'][$field_slug_full]
 * e.g. 'wpcf-img'
 *  * If field slug do not exist in $_POST['wpcf'] - won't be saved.
 * 
 * @global type $wpcf_api
 * @param type $post
 * @param type $field
 * @return type 
 */
function wpcf_api_repetitive_save( $post, $field ) {
    global $wpcf;
    $wpcf->repeater->set( $post, $field );
    return $wpcf->repeater->save();
}

/**
 * Fetches saved meta for post.
 * 
 * Please debug output to get familiar with results returned:
 * 'single'
 * 'by_meta_id'
 * 'by_meta_key'
 * 'custom_order' (optional)
 * 
 * @global type $wpcf_api
 * @param type $post
 * @param type $field
 * @return type 
 */
function wpcf_api_repetitive_get_meta( $post, $field ) {
    global $wpcf;
    $wpcf->repeater->set( $post, $field );
    return $wpcf->repeater->_get_meta();
}