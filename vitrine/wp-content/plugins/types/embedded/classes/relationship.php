<?php
/*
 * Post relationship class.
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/embedded/classes/relationship.php $
 * $LastChangedDate: 2015-03-25 12:38:40 +0000 (Wed, 25 Mar 2015) $
 * $LastChangedRevision: 1120400 $
 * $LastChangedBy: iworks $
 *
 */

/**
 * Post relationship class
 *
 * @since Types 1.2
 * @package Types
 * @subpackage Classes
 * @version 0.1
 * @category Relationship
 * @author srdjan <srdjan@icanlocalize.com>
 *
 */
class WPCF_Relationship
{
    /**
     * Custom field
     *
     * @var type
     */
    var $cf = array();
    var $data = array();

    /**
     * Settings
     *
     * @var type
     */
    var $settings = array();
    var $items_per_page = 5;
    var $items_per_page_option_name = '_wpcf_relationship_items_per_page';
    var $child_form = null;

    /**
     * Construct function.
     */
    function __construct()
    {
        $this->cf = new WPCF_Field;
        $this->settings = get_option( 'wpcf_post_relationship', array() );
        add_action( 'wp_ajax_add-types_reltax_add',
                array($this, 'ajaxAddTax') );
    }

    /**
     * Sets current data.
     *
     * @param type $parent
     * @param type $child
     * @param type $field
     * @param type $data
     */
    function set( $parent, $child, $data = array() )
    {
        return $this->_set( $parent, $child, $data );
    }

    /**
     * Sets current data.
     *
     * @param type $parent
     * @param type $child
     * @param type $field
     * @param type $data
     */
    function _set( $parent, $child, $data = array() )
    {
        $this->parent = $parent;
        $this->child = $child;
        $this->cf = new WPCF_Field;
        // TODO Revise usage
        $this->data = $data;
    }

    /**
     * Meta box form on post edit page.
     *
     * @param type $parent Parent post
     * @param type $post_type Child post type
     * @return type string HTML formatted form table
     */
    function child_meta_form($parent, $post_type)
    {
        if ( is_integer( $parent ) ) {
            $parent = get_post( $parent );
        }
        $output = '';
        require_once dirname( __FILE__ ) . '/relationship/form-child.php';
        $this->child_form = new WPCF_Relationship_Child_Form(
                        $parent,
                        $post_type,
                        $this->settings( $parent->post_type, $post_type )
        );
        $output .= $this->child_form->render();

        return $output;
    }

    /**
     * Child row rendered on AJAX 'Add New Child' call.
     *
     * @param type $parent_id
     * @param type $child_id
     * @return type
     */
    function child_row($parent_id, $child_id)
    {
        $parent = get_post( intval( $parent_id ) );
        $child = get_post( intval( $child_id ) );
        if ( empty( $parent ) || empty( $child ) ) {
            return new WP_Error( 'wpcf-relationship-save-child', 'no parent/child post' );
        }
        $output = '';
        $this->child_form = $this->_get_child_form( $parent, $child );
        $output .= $this->child_form->child_row( $child );

        return $output;
    }

    /**
     * Returns HTML formatted form.
     *
     * @param type $parent
     * @param type $child
     * @return \WPCF_Relationship_Child_Form
     */
    function _get_child_form($parent, $child)
    {
        require_once dirname( __FILE__ ) . '/relationship/form-child.php';
        return new WPCF_Relationship_Child_Form(
                        $parent,
                        $child->post_type,
                        $this->settings( $parent->post_type, $child->post_type )
        );
    }

    function get_child()
    {
        $r = $this->child;
        $r->parent = $this->parent;
        $r->form = $this->_get_child_form( $r->parent, $this->child );
        return $r;
    }

    /**
     * Save items_per_page settings.
     *
     * @param type $parent
     * @param type $child
     * @param int $num
     */
    function save_items_per_page($parent, $child, $num)
    {
        if ( post_type_exists( $parent ) && post_type_exists( $child ) ) {
            $option_name = $this->items_per_page_option_name . '_' . $parent . '_' . $child;
            if ( $num == 'all' ) {
                $num = 9999999999999999;
            }
            update_option( $option_name, intval( $num ) );
        }
    }

    /**
     * Return items_per_page settings
     *
     * @param type $parent
     * @param type $child
     * @return type
     */
    function get_items_per_page($parent, $child)
    {
        $per_page = get_option( $this->items_per_page_option_name . '_' . $parent . '_' . $child,
                $this->items_per_page );
        return empty( $per_page ) ? $this->items_per_page : $per_page;
    }

    /**
     * Adjusts post name when saving.
     *
     * @todo Revise (not used?)
     * @param type $post
     * @return type
     */
    function get_insert_post_name($post)
    {
        if ( empty( $post->post_title ) ) {
            return $post->post_type . '-' . $post->ID;
        }
        return $post->post_title;
    }

    /**
     * Bulk saving children.
     *
     * @param int $parent_id
     * @param array $children Array $child_id => $fields. For details about $fields see save_child().
     */
    function save_children($parent_id, $children)
    {
        foreach ( $children as $child_id => $fields ) {
            $this->save_child( $parent_id, $child_id, $fields );
        }
    }

    /**
     * Unified save child function.
     *
     * @param int $parent_id
     * @param int $child_id
     * @param array $save_fields
     * @return bool|WP_Error
     */
    function save_child( $parent_id, $child_id, $save_fields = array() )
    {
        $parent = get_post( intval( $parent_id ) );
        $child = get_post( intval( $child_id ) );
        $post_data = array();

        if ( empty( $parent ) || empty( $child ) ) {
            return new WP_Error( 'wpcf-relationship-save-child', 'no parent/child post' );
        }

        // Save relationship
        update_post_meta( $child->ID,
                '_wpcf_belongs_' . $parent->post_type . '_id', $parent->ID );

        // Check if added via AJAX
        $check = get_post_meta( $child->ID, '_wpcf_relationship_new', true );
        $new = !empty( $check );
        delete_post_meta( $child->ID, '_wpcf_relationship_new' );

        // Set post data
        $post_data['ID'] = $child->ID;

        // Title needs to be checked if submitted at all
        if ( !isset( $save_fields['_wp_title'] ) ) {
            // If not submitted that means it is not offered to be edited
            if ( !empty( $child->post_title ) ) {
                $post_title = $child->post_title;
            } else {
                // DO NOT LET IT BE EMPTY
                $post_title = $child->post_type . ' ' . $child->ID;
            }
        } else {
            $post_title = $save_fields['_wp_title'];
        }


        $post_data['post_title'] = $post_title;
        $post_data['post_content'] = isset( $save_fields['_wp_body'] ) ? $save_fields['_wp_body'] : $child->post_content;
        $post_data['post_type'] = $child->post_type;

        // Check post status - if new, convert to 'publish' else keep remaining
        if ( $new ) {
            $post_data['post_status'] =  'publish';
        } else {
            $post_data['post_status'] =  get_post_status( $child->ID );
        }

        /*
         *
         *
         *
         *
         *
         *
         * UPDATE POST
         */

        $cf = new WPCF_Field;
        if (
            isset( $_POST['wpcf_post_relationship'][$parent_id])
            && isset( $_POST['wpcf_post_relationship'][$parent_id][$child_id] )
        ) {
            $_POST['wpcf'] = array();
            foreach( $_POST['wpcf_post_relationship'][$parent_id][$child_id] as $slug => $value ) {
                $_POST['wpcf'][$cf->__get_slug_no_prefix( $slug )] = $value;
                $_POST['wpcf'][$slug] = $value;
            }
        }
        unset($cf);

        /**
         * avoid filters for children
         * /
        global $wp_filter;
        $save_post = $wp_filter['save_post'];
        $wp_filter['save_post'] = array();
         */
        $updated_id = wp_update_post( $post_data );
        /*
            $wp_filter['save_post'] = $save_post;
         */
        unset($save_post);
        if ( empty( $updated_id ) ) {
            return new WP_Error( 'relationship-update-post-failed', 'Updating post failed' );
        }

        // Save parents
        if ( !empty( $save_fields['parents'] ) ) {
            foreach ( $save_fields['parents'] as $parent_post_type => $parent_post_id ) {
                update_post_meta( $child->ID,
                        '_wpcf_belongs_' . $parent_post_type . '_id',
                        $parent_post_id );
            }
        }

        // Update taxonomies
        if ( !empty( $save_fields['taxonomies'] ) && is_array( $save_fields['taxonomies'] ) ) {
            $_save_data = array();
            foreach ( $save_fields['taxonomies'] as $taxonomy => $t ) {
                if ( !is_taxonomy_hierarchical( $taxonomy ) ) {
                    $_save_data[$taxonomy] = strval( $t );
                    continue;
                }
                foreach ( $t as $term_id ) {
                    if ( $term_id != '-1' ) {
                        $term = get_term( $term_id, $taxonomy );
                        if ( empty( $term ) ) {
                            continue;
                        }
                        $_save_data[$taxonomy][] = $term_id;
                    }
                }
            }
            wp_delete_object_term_relationships( $child->ID,
                    array_keys( $save_fields['taxonomies'] ) );
            foreach ( $_save_data as $_taxonomy => $_terms ) {
                wp_set_post_terms( $child->ID, $_terms, $_taxonomy,
                        $append = false );
            }
        }

        // Unset non-types
        unset( $save_fields['_wp_title'], $save_fields['_wp_body'],
                $save_fields['parents'], $save_fields['taxonomies'] );
        /*
         *
         *
         *
         *
         *
         *
         * UPDATE Loop over fields
         */
        foreach ( $save_fields as $slug => $value ) {
            if ( defined( 'WPTOOLSET_FORMS_VERSION' ) ) {
                // Get field by slug
                $field = wpcf_fields_get_field_by_slug( str_replace( WPCF_META_PREFIX,
                                '', $slug ) );
                if ( empty( $field ) ) {
                    continue;
                }
                // Set config
                $config = wptoolset_form_filter_types_field( $field, $child->ID );
                // Check if valid
                $valid = wptoolset_form_validate_field( 'post', $config, $value );
                if ( is_wp_error( $valid ) ) {
                    $errors = $valid->get_error_data();
                    $msg = sprintf( __( 'Child post "%s" field "%s" not updated:',
                                    'wpcf' ), $child->post_title, $field['name'] );
                    wpcf_admin_message_store( $msg . ' ' . implode( ', ',
                                    $errors ), 'error' );
                    continue;
                }
            }
            $this->cf->set( $child, $field );
            $this->cf->context = 'post_relationship';
            $this->cf->save( $value );
        }

        do_action( 'wpcf_relationship_save_child', $child, $parent );

        clean_post_cache( $parent->ID );
        clean_post_cache( $child->ID );
        // Added because of caching meta 1.5.4
        wp_cache_flush();

        return true;
    }

    /**
     * Saves new child.
     *
     * @param int $parent_id
     * @param string $post_type
     * @return int|WP_Error
     */
    function add_new_child($parent_id, $post_type)
    {
        global $wpdb;
        $parent = get_post( $parent_id );
        if ( empty( $parent ) ) {
            return new WP_Error( 'wpcf-relationship-no-parent', 'No parent' );
        }
        $new_post = array(
            'post_title' => __('New'). ': '.$post_type,
            'post_type' => $post_type,
            'post_status' => 'draft',
        );
        $id = wp_insert_post( $new_post, true );
        /**
         * return wp_error
         */
        if ( is_wp_error( $id ) ) {
            return $id;
        }
        /**
         * Mark that it is new post
         */
        update_post_meta( $id, '_wpcf_relationship_new', 1 );
        /**
         * Save relationship
         */
        update_post_meta( $id, '_wpcf_belongs_' . $parent->post_type . '_id', $parent->ID );
        /**
         * Fix title
         */
        $wpdb->update(
            $wpdb->posts,
            array('post_title' => $post_type . ' ' . $id),
            array('ID' => $id), array('%s'), array('%d')
        );
        do_action( 'wpcf_relationship_add_child', get_post( $id ), $parent );
        wp_cache_flush();
        return $id;
    }

    /**
     * Saved relationship settings.
     *
     * @param type $parent
     * @param type $child
     * @return type
     */
    function settings($parent, $child)
    {
        return isset( $this->settings[$parent][$child] ) ? $this->settings[$parent][$child] : array();
    }

    /**
     * Fetches submitted data.
     *
     * @param type $parent_id
     * @param type $child_id
     * @return type
     */
    function get_submitted_data($parent_id, $child_id, $field)
    {
        if ( !is_string( $field ) ) {
            $_field_slug = $field->slug;
        } else {
            $_field_slug = $field;
        }
        return isset( $_POST['wpcf_post_relationship'][$parent_id][$child_id][$_field_slug] ) ? $_POST['wpcf_post_relationship'][$parent_id][$child_id][$_field_slug] : null;
    }

    /**
     * Gets all parents per post type.
     *
     * @param type $child
     * @return type
     */
    public static function get_parents($child)
    {
        $parents = array();
        $item_parents = wpcf_pr_admin_get_belongs( $child->post_type );
        if ( $item_parents ) {
            foreach ( $item_parents as $post_type => $data ) {

                // Get parent ID
                $meta = wpcf_get_post_meta( $child->ID,
                        '_wpcf_belongs_' . $post_type . '_id', true );

                if ( !empty( $meta ) ) {

                    $parent_post = get_post( $meta );

                    if ( !empty( $parent_post ) ) {
                        $parents[$parent_post->post_type] = $parent_post;
                    }
                }
            }
        }
        return $parents;
    }

    /**
     * Gets post parent by post type.
     *
     * @param type $post_id
     * @param type $parent_post_type
     * @return type
     */
    public static function get_parent($post_id, $parent_post_type)
    {
        return wpcf_get_post_meta( $post_id,
                        '_wpcf_belongs_' . $parent_post_type . '_id', true );
    }

    /**
     * AJAX adding taxonomies
     */
    public function ajaxAddTax()
    {
        if ( isset( $_POST['types_reltax'] ) ) {
            $data = array_shift( $_POST['types_reltax'] );
            $tax = key( $data );
            $val = array_shift( $data );
            $__nonce = array_shift( $_POST['types_reltax_nonce'] );
            $nonce = array_shift( $__nonce );
            $_POST['action'] = 'add-' . $tax;
            $_POST['post_category'][$tax] = $val;
            $_POST['tax_input'][$tax] = $val;
            $_POST['new'.$tax] = $val;
            $_REQUEST["_ajax_nonce-add-{$tax}"] = $nonce;
            _wp_ajax_add_hierarchical_term();
        }
        die();
    }

    /**
     * Meta box form on post edit page.
     *
     * @param type $parent Parent post
     * @param type $post_type Child post type
     * @return type string HTML formatted list
     */
    function child_list($parent, $post_type)
    {
        if ( is_integer( $parent ) ) {
            $parent = get_post( $parent );
        }
        $output = '';
        require_once dirname( __FILE__ ) . '/relationship/form-child.php';
        $this->child_form = new WPCF_Relationship_Child_Form(
                        $parent,
                        $post_type,
                        $this->settings( $parent->post_type, $post_type )
                    );
        foreach($this->child_form->children as $child) {
            $output .= sprintf(
                '<li>%s</li>',
                apply_filters('post_title', $child->post_title)
            );
        }
        if ( $output ) {
            $output = sprintf(
                '<ul>%s</ul>',
                $output
            );
        } else {
            $output = sprintf(
                '<p class="info">%s</p>',
                $this->child_form->child_post_type_object->labels->not_found
            );
        }

        return $output;
    }


}
