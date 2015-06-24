<?php
/*
 * Post relationship code.
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/embedded/includes/post-relationship.php $
 * $LastChangedDate: 2015-04-01 14:15:17 +0000 (Wed, 01 Apr 2015) $
 * $LastChangedRevision: 1125405 $
 * $LastChangedBy: iworks $
 *
 */
add_action( 'wpcf_admin_post_init', 'wpcf_pr_admin_post_init_action', 10, 4 );
add_action( 'save_post', 'wpcf_pr_admin_save_post_hook', 20, 2 ); // Trigger afer main hook

/**
 * Init function.
 *
 * Enqueues styles and scripts on post edit page.
 *
 * @param type $post_type
 * @param type $post
 * @param type $groups
 * @param type $wpcf_active
 */
function wpcf_pr_admin_post_init_action( $post_type, $post, $groups, $wpcf_active )
{
    // See if any data
    $has = wpcf_pr_admin_get_has( $post_type );
    $belongs = wpcf_pr_admin_get_belongs( $post_type );

    /*
     * Enqueue styles and scripts
     */
    if ( !empty( $has ) || !empty( $belongs ) ) {

        $output = wpcf_pr_admin_post_meta_box_output( $post, array('post_type' => $post_type, 'has' => $has, 'belongs' => $belongs) );
        add_meta_box( 'wpcf-post-relationship', __( 'Fields table', 'wpcf' ),
                'wpcf_pr_admin_post_meta_box', $post_type, 'normal', 'default',
                array('output' => $output) );
        if ( !empty( $output ) ) {
            wp_enqueue_script( 'wpcf-post-relationship',
                    WPCF_EMBEDDED_RELPATH . '/resources/js/post-relationship.js',
                    array('jquery'), WPCF_VERSION );
            wp_enqueue_style( 'wpcf-post-relationship',
                    WPCF_EMBEDDED_RELPATH . '/resources/css/post-relationship.css',
                    array(), WPCF_VERSION );
            if ( !$wpcf_active ) {
                wpcf_enqueue_scripts();
                wp_enqueue_style( 'wpcf-pr-post',
                        WPCF_EMBEDDED_RES_RELPATH . '/css/fields-post.css',
                        array(), WPCF_VERSION );
                wp_enqueue_script( 'wpcf-form-validation',
                        WPCF_EMBEDDED_RES_RELPATH . '/js/'
                        . 'jquery-form-validation/jquery.validate.min.js',
                        array('jquery'), WPCF_VERSION );
                wp_enqueue_script( 'wpcf-form-validation-additional',
                        WPCF_EMBEDDED_RES_RELPATH . '/js/'
                        . 'jquery-form-validation/additional-methods.min.js',
                        array('jquery'), WPCF_VERSION );
            }
            wpcf_admin_add_js_settings( 'wpcf_pr_del_warning',
                    '\'' . __( 'Are you sure about deleting this post?', 'wpcf' ) . '\'' );
            wpcf_admin_add_js_settings( 'wpcf_pr_pagination_warning',
                    '\'' . __( 'If you continue without saving your changes, they might get lost.',
                            'wpcf' ) . '\'' );
        }
    }
}

/**
 * Gets post types that belong to current post type.
 *
 * @param type $post_type
 * @return type
 */
function wpcf_pr_admin_get_has( $post_type ) {
    static $cache = array();
    if ( isset( $cache[$post_type] ) ) {
        return $cache[$post_type];
    }
    $relationships = get_option( 'wpcf_post_relationship', array() );
    if ( empty( $relationships[$post_type] ) ) {
        return false;
    }
    // See if enabled
    foreach ( $relationships[$post_type] as $temp_post_type => $temp_post_type_data ) {
        $active = get_post_type_object( $temp_post_type );
        if ( !$active ) {
            unset( $relationships[$post_type][$temp_post_type] );
        }
    }
    $cache[$post_type] = !empty( $relationships[$post_type] ) ? $relationships[$post_type] : false;
    return $cache[$post_type];
}

/**
 * Gets post types that current post type belongs to.
 *
 * @param type $post_type
 * @return type
 */
function wpcf_pr_admin_get_belongs( $post_type ) {
    static $cache = array();
    if ( isset( $cache[$post_type] ) ) {
        return $cache[$post_type];
    }
    $relationships = get_option( 'wpcf_post_relationship', array() );
    $results = array();
    if ( is_array( $relationships ) ) {
        foreach ( $relationships as $has => $belongs ) {
            // See if enabled
            $active = get_post_type_object( $has );
            if ( !$active ) {
                continue;
            }
            if ( array_key_exists( $post_type, $belongs ) ) {
                $results[$has] = $belongs[$post_type];
            }
        }
    }
    $cache[$post_type] = !empty( $results ) ? $results : false;
    return $cache[$post_type];
}

/**
 * Meta boxes contents.
 *
 * @param type $post
 * @param type $args
 */
function wpcf_pr_admin_post_meta_box( $post, $args )
{
    if ( !empty( $args['args']['output'] ) ) {
        echo $args['args']['output'];
    } else {
        $wpcf_pr_admin_belongs = wpcf_pr_admin_get_belongs( $post->post_type );
        if ( empty( $wpcf_pr_admin_belongs ) ) {
            _e( 'You will be able to manage child posts after saving this post.', 'wpcf' );
        } else {
            _e( 'You will be able to add parent posts after saving this post.', 'wpcf' );
        }
    }
}

function wpcf_admin_notice_post_locked_no_parent() {
    if ( ! $post = get_post() ) {
        return;
    }
    $parent_type = wpcf_pr_admin_get_belongs( $post->post_type );
    if ( is_array( $parent_type ) && count( $parent_type ) ) {
        $parent_type = array_shift( array_keys( $parent_type ) );
        $parent_type = get_post_type_object( $parent_type );
    } else {
        return;
    }

    if ( ( $sendback = wp_get_referer() ) && false === strpos( $sendback, 'post.php' ) && false === strpos( $sendback, 'post-new.php' ) ) {
        $sendback_text = __('Go back');
    } else {
        $sendback = admin_url( 'edit.php' );
        if ( 'post' != $post->post_type ) {
            $sendback = add_query_arg( 'post_type', $post->post_type, $sendback );
        }
        $sendback_text = get_post_type_object( $post->post_type )->labels->all_items;
    }
?>
<div id="post-lock-dialog" class="notification-dialog-wrap">
    <div class="notification-dialog-background"></div>
        <div class="notification-dialog">
            <div class="post-locked-message">
                <p>
<?php
    if ( 'auto-draft' == $post->post_status ) {
        printf(
            __( 'You will be able to add child posts after saving at least one <b>%s</b>.', 'wpcf' ),
            $parent_type->labels->singular_name
        );
    } else {
        printf(
            __( 'You will be able to edit child posts after saving at least one <b>%s</b>.', 'wpcf' ),
            $parent_type->labels->singular_name
        );
    }
?>
                </p>
                <p><a class="button button-primary wp-tab-last" href="<?php echo esc_url( $sendback ); ?>"><?php echo $sendback_text; ?></a></p>
            </div>
        </div>
    </div>
</div>
<?php
}
/**
 * Meta boxes contents output.
 *
 * @param type $post
 * @param type $args
 */
function wpcf_pr_admin_post_meta_box_output( $post, $args )
{
    if ( empty($post) || empty( $post->ID ) ) {
        return array();
    }

    global $wpcf;

    $output = '';
    $relationships = $args;
    $post_id = !empty( $post->ID ) ? $post->ID : -1;
    $current_post_type = wpcf_admin_get_edited_post_type( $post );

    /*
     * Render has form (child form)
     */
    if ( !empty( $relationships['has'] ) ) {
        foreach ( $relationships['has'] as $post_type => $data ) {
            if ( isset($data['fields_setting']) && 'only_list' == $data['fields_setting'] ) {
                $output .= $wpcf->relationship->child_list( $post, $post_type, $data );
            } else {
                $output .= $wpcf->relationship->child_meta_form( $post, $post_type, $data );
            }
        }
    }
    /*
     * Render belongs form (parent form)
     */
    if ( !empty( $relationships['belongs'] ) ) {
        $meta = get_post_custom( $post_id );
        $belongs = array('belongs' => array(), 'posts' => array());
        foreach ( $meta as $meta_key => $meta_value ) {
            if ( strpos( $meta_key, '_wpcf_belongs_' ) === 0 ) {
                $temp_post = get_post( $meta_value[0] );
                if ( !empty( $temp_post ) ) {
                    $belongs['posts'][$temp_post->ID] = $temp_post;
                    $belongs['belongs'][$temp_post->post_type] = $temp_post->ID;
                }
            }
        }
        foreach ( $relationships['belongs'] as $post_type => $data ) {
            $output .= '<div style="margin: 20px 0 10px 0">';
            if ( $x = wpcf_form_simple( wpcf_pr_admin_post_meta_box_belongs_form( $post, $post_type, $belongs ) ) ) {
                $output .= sprintf(
                    __( 'This <i>%s</i> belongs to:', 'wpcf' ),
                    get_post_type_object($current_post_type)->labels->singular_name
                );
                $output .= $x;
            } else {
                $output .= get_post_type_object($post_type)->labels->not_found;
            }
            $output .= '</div>';
        }
    }
    return $output;
}

/**
 * AJAX delete child item call.
 *
 * @param int $post_id
 * @return string
 */
function wpcf_pr_admin_delete_child_item( $post_id ) {
    wp_delete_post( $post_id, true );
    return __( 'Post deleted', 'wpcf' );
}

/**
 *
 * Belongs form helper to build correct SQL string to prepare.
 *
 * Belongs form helper to build correct SQL string to $wpdb->prepare - replace 
 * any item by digital placeholder.
 *
 * @param any $item
 * @return string
 *
 */
function wpcf_pr_admin_post_meta_box_belongs_form_items_helper( $item )
{
    return '%d';
}

/**
 * Belongs form.
 *
 * @param type $post
 * @param type $post_type
 * @param type $data
 * @param type $parent_post_type
 */
function wpcf_pr_admin_post_meta_box_belongs_form( $post, $type, $belongs )
{
    global $wpdb;

    $temp_type = get_post_type_object( $type );
    if ( empty( $temp_type ) ) {
        return array();
    }
    $form = array();
    $options = array(
        __( 'Not selected', 'wpcf' ) => 0,
    );

    $args = array(
        'fields' => 'ids',
        'numberposts' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
        'post_status' => apply_filters( 'wpcf_pr_belongs_post_status', array( 'publish', 'private' ) ),
        'post_type' => $type,
        'suppress_filters' => 0,
    );
    $items = get_posts($args);

    if ( empty( $items ) ) {
        return array();
    }
    /**
     * Filter array of parent objects.
     *
     * Allow to change array of parent objects, modyfiy titles, remove
     * or add some data from list of avaiable post parents.
     *
     * @since 1.6.6
     *
     * @param array $args {
     *     Array of objects with all possible parents
     *
     *     @param int    $ID   Post ID
     *     @param string $type Post title.
     * }
     * @param string $type Post Type of parent.
     */
    $_titles = apply_filters(
        'wpcf_pr_belongs_items',
        $wpdb->get_results(
            $wpdb->prepare(
                sprintf(
                    'SELECT ID, post_title FROM %s WHERE ID IN (%s)',
                    $wpdb->posts,
                    implode(', ', array_map( 'wpcf_pr_admin_post_meta_box_belongs_form_items_helper', $items))
                ),
                $items
            ),
            OBJECT_K
        ),
        $type
    );

    foreach ( $items as $temp_post ) {
        if ( !isset( $_titles[$temp_post]->post_title ) ) {
            continue;
        }
        /**
         * Filter single parent data
         *
         * Allow change singla parent data.
         *
         * @since 1.6.6
         *
         * @param array $args Single parent title and id
         * @param string $type Post Type of parent.
         */
        $options[] = apply_filters(
            'wpcf_pr_belongs_item',
            array(
                '#title' => $_titles[$temp_post]->post_title,
                '#value' => $temp_post,
            ),
            $type
        );
    }


    $form[$type] = array(
        '#type' => 'select',
        '#name' => 'wpcf_pr_belongs[' . $post->ID . '][' . $type . ']',
        '#default_value' => isset( $belongs['belongs'][$type] ) ? $belongs['belongs'][$type] : 0,
        '#options' => $options,
        '#prefix' => $temp_type->labels->singular_name . '&nbsp;',
        '#suffix' => '&nbsp;<a href="'
        . admin_url( 'admin-ajax.php?action=wpcf_ajax'
                . '&amp;wpcf_action=pr-update-belongs&amp;_wpnonce='
                . wp_create_nonce( 'pr-update-belongs' )
                . '&amp;post_id=' . $post->ID )
        . '" class="button-secondary wpcf-pr-update-belongs">' . __( 'Update',
                'wpcf' ) . '</a>',
    );
    return $form;
}

/**
 * Updates belongs data.
 *
 * @param int $post_id
 * @param array $data $post_type => $post_id
 * @return string
 */
function wpcf_pr_admin_update_belongs( $post_id, $data ) {

    $errors = array();
    $post = get_post( intval( $post_id ) );
    if ( empty( $post->ID ) ) {
        return new WP_Error(
            'wpcf_update_belongs',
            sprintf(
                __( 'Missing child post ID %d', 'wpcf' ),
                intval( $post_id )
            )
        );
    }

    foreach ( $data as $post_type => $post_owner_id ) {
        // Check if relationship exists
        if ( !wpcf_relationship_is_parent( $post_type, $post->post_type ) ) {
            $errors[] = sprintf(
                __( 'Relationship do not exist %s -> %s', 'wpcf' ),
                strval( $post_type ),
                strval( $post->post_type )
            );
            continue;
        }
        if ( $post_owner_id == '0' ) {
            delete_post_meta( $post_id, "_wpcf_belongs_{$post_type}_id" );
            continue;
        }
        $post_owner = get_post( intval( $post_owner_id ) );
        // Check if owner post exists
        if ( empty( $post_owner->ID ) ) {
            $errors[] = sprintf( __( 'Missing parent post ID %d', 'wpcf' ), intval( $post_owner_id ) );
            continue;
        }
        // Check if owner post type matches required
        if ( $post_owner->post_type != $post_type ) {
            $errors[] = sprintf(
                __( 'Parent post ID %d is not type of %s', 'wpcf' ),
                intval( $post_owner_id ),
                strval( $post_type )
            );
            continue;
        }
        update_post_meta( $post_id, "_wpcf_belongs_{$post_type}_id", $post_owner->ID );
    }

    if ( !empty( $errors ) ) {
        return new WP_Error( 'wpcf_update_belongs', implode( '; ', $errors ) );
    }

    return __( 'Post updated', 'wpcf' );
}

/**
 * Pagination link.
 *
 * @param type $post
 * @param type $post_type
 * @param type $page
 * @param type $prev
 * @param type $next
 * @return string
 */
function wpcf_pr_admin_has_pagination( $post, $post_type, $page, $prev, $next,
        $per_page = 20, $count = 20 ) {

    global $wpcf;

    $link = '';
    $add = '';
    if ( isset( $_GET['sort'] ) ) {
        $add .= '&sort=' . sanitize_text_field( $_GET['sort'] );
    }
    if ( isset( $_GET['field'] ) ) {
        $add .= '&field=' . sanitize_text_field( $_GET['field'] );
    }
    if ( isset( $_GET['post_type_sort_parent'] ) ) {
        $add .= '&post_type_sort_parent=' . sanitize_text_field( $_GET['post_type_sort_parent'] );
    }

    /**
     * default for next
     */
    $url_params = array(
        'action' => 'wpcf_ajax',
        'wpcf_action' => 'pr_pagination',
        'page' => $page + 1,
        'dir' => 'next',
        'post_id' => $post->ID,
        'post_type' => $post_type,
        $wpcf->relationship->items_per_page_option_name => $wpcf->relationship->items_per_page,
        '_wpnonce' => wp_create_nonce( 'pr_pagination' ) . $add,
    );
    $url = admin_url('admin-ajax.php');


    if ( $prev ) {
        $url_params['page'] = $page - 1;
        $url_params['dir'] = 'prev';
        $link .= sprintf(
            '<a class="button-secondary wpcf-pr-pagination-link wpcf-pr-prev" href="%s" data-pagination-name="%s">',
            add_query_arg( $url_params, $url),
            esc_attr($wpcf->relationship->items_per_page_option_name)
        );
        $link .= __( 'Prev', 'wpcf' ) . '</a>&nbsp;&nbsp;';
    }
    if ( $per_page < $count ) {
        $total_pages = ceil( $count / $per_page );
        $link .= sprintf(
            '<select class="wpcf-pr-pagination-select" name="wpcf-pr-pagination-select" data-pagination-name="%s">',
            esc_attr($wpcf->relationship->items_per_page_option_name)
        );
        for ( $index = 1; $index <= $total_pages; $index++ ) {
            $link .= '<option';
            if ( ($index) == $page ) {
                $link .= ' selected="selected"';
            }
            $url_params['page'] = $index;

            $link .= sprintf( ' value="%s"', add_query_arg( $url_params, $url));
            $link .= '">' . $index . '</option>';
        }
        $link .= '</select>';
    }
    if ( $next ) {
        $url_params['page'] = $page + 1;
        $link .= sprintf(
            '<a class="button-secondary wpcf-pr-pagination-link wpcf-pr-next" href="%s" data-pagination-name="%s">',
            add_query_arg( $url_params, $url),
            esc_attr($wpcf->relationship->items_per_page_option_name)
        );
        $link .= __( 'Next', 'wpcf' ) . '</a>';
    }
    return !empty( $link ) ? '<div class="wpcf-pagination-top">' . $link . '</div>' : '';
}

/**
 * Save post hook.
 *
 * @param type $parent_post_id
 * @return string
 */
function wpcf_pr_admin_save_post_hook( $parent_post_id ) {

    global $wpcf;
    /*
     * TODO https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/159760120/comments#225005357
     * Problematic This should be done once per save (on saving main post)
     * remove_action( 'save_post', 'wpcf_pr_admin_save_post_hook', 11);
     */
    static $cached = array();
    /*
     *
     * TODO Monitor this
     */
    // Remove main hook?
    // CHECKPOINT We remove temporarily main hook
    if ( !isset( $cached[$parent_post_id] ) ) {
        if ( isset( $_POST['wpcf_post_relationship'][$parent_post_id] ) ) {
            $wpcf->relationship->save_children( $parent_post_id,
                    (array) $_POST['wpcf_post_relationship'][$parent_post_id] );
        }
        // Save belongs if any
        if ( isset( $_POST['wpcf_pr_belongs'][intval( $parent_post_id )] ) ) {
            wpcf_pr_admin_update_belongs( intval( $parent_post_id ),
                    $_POST['wpcf_pr_belongs'][intval( $parent_post_id )] );
        }

        // WPML
        wpcf_wpml_relationship_save_post_hook( $parent_post_id );

        $cached[$parent_post_id] = true;
    }

}

/**
 * Filters AJAX 'cd_verify' action data.
 *
 * @global type $wpcf
 * @param type $posted
 * @param type $field
 * @return type
 */
function wpcf_relationship_ajax_data_filter( $posted, $field ) {

    global $wpcf;

    $value = $wpcf->relationship->get_submitted_data(
        $wpcf->relationship->parent->ID,
        $wpcf->relationship->child->ID,
        $field
    );

    return is_null( $value ) ? $posted : $value;
}

/**
 * Checks if post type is parent
 * @param type $parent_post_type
 * @param type $child_post_type
 * @return type
 */
function wpcf_relationship_is_parent( $parent_post_type, $child_post_type ) {
    $has = wpcf_pr_admin_get_has( $parent_post_type );
    return isset( $has[$child_post_type] );
}
