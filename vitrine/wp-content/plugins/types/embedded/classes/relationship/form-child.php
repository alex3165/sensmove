<?php
/*
 * Relationship form class.
 *
 * Used to render child forms
 */
require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields-post.php';

/**
 * Relationship form class.
 *
 * Used on post edit page to show children rows
 */
class WPCF_Relationship_Child_Form
{

    /**
     * Current post.
     *
     * @var type object
     */
    var $post;

    /**
     * Field object.
     *
     * @var type array
     */
    var $cf = array();

    /**
     * Saved data.
     *
     * @var type array
     */
    var $data = array();

    /**
     * Child post object.
     *
     * @var type
     */
    var $child_post_type_object;
    var $parent;
    var $parent_post_type;
    var $child_post_type;
    var $model;
    var $children;
    var $headers = array();
    var $_dummy_post = false;
    private $__params = array('page', '_wpcf_relationship_items_per_page', 'sort',
            'field');
    private $__urlParams = array();

    /**
     * post type configuration
     */
    private $child_supports = array(
        'title' => false,
        'editor' => false,
        'comments' => false,
        'trackbacks' => false,
        'revisions' => false,
        'author' => false,
        'excerpt' => false,
        'thumbnail' => false,
        'custom-fields' => false,
        'page-attributes' => false,
        'post-formats' => false,
    );

    /**
     * Construct function.
     */
    function __construct( $parent_post, $child_post_type, $data ) {
        WPCF_Loader::loadModel( 'relationship' );
        $this->parent = $parent_post;
        $this->parent_post_type = $parent_post->post_type;
        $this->child_post_type = $child_post_type;
        $this->data = $data;
// Clean data
        if ( empty( $this->data['fields_setting'] ) ) {
            $this->data['fields_setting'] = 'all_cf';
        }
        $this->cf = new WPCF_Field();
        $this->cf->context = 'relationship';
        $this->children = WPCF_Relationship_Model::getChildrenByPostType(
            $this->parent,
            $this->child_post_type,
            $this->data,
            $_GET
        );

        // If no children - use dummy post
        if ( empty( $this->children ) ) {
            $_dummy_post = get_default_post_to_edit( $this->child_post_type,
                    false );
            $this->children = array($_dummy_post);
            $this->_dummy_post = true;
        }
        $this->child_post_type_object = get_post_type_object( $this->child_post_type );

        // Collect params from request
        foreach ( $this->__params as $__param ) {
            if ( isset( $_GET[$__param] ) ) {
                $this->__urlParams[$__param] = $_GET[$__param];
            }
        }
        /**
         * build-in types
         */
        if ( in_array($child_post_type, array('page', 'post', 'attachment', 'revision', 'nav_menu_item') ) ) {
            foreach( array_keys($this->child_supports) as $key ) {
                $this->child_supports[$key] = post_type_supports($child_post_type, $key);
            }
            return;
        }
        /**
         * custom post types
         */
        $post_types = get_option( 'wpcf-custom-types', array() );
        if (
            array_key_exists($child_post_type, $post_types )
            && array_key_exists('supports', $post_types[$child_post_type] )
        ) {
            foreach(  $post_types[$child_post_type]['supports'] as $key => $value ) {
                $this->child_supports[$key] = (boolean)$value;
            }
        }
        unset($post_types);
    }

    function getParamsQuery() {
        return count( $this->__urlParams ) ? '&amp;' . http_build_query( $this->__urlParams,
                        '', '&amp;' ) : '';
    }

    /**
     * Sets form.
     *
     * @param type $o
     */
    function _set( $child ) {
        $this->child = $child;
    }

    /**
     * Returns HTML formatted form.
     *
     * Renders children per row.
     *
     * @todo move all here
     *
     * @return type string (HTML formatted)
     */
    function render() {
        static $count = false;
        if ( !$count ) {
            $count = 1;
        }

        /*
         * Pagination will slice children
         */
        $this->pagination();
        $rows = $this->rows();
        $headers = $this->headers();

        // Capture template output
        ob_start();
        include WPCF_EMBEDDED_INC_ABSPATH . '/relationship/child-table.php';
        $table = ob_get_contents();
        ob_end_clean();

        $count++;
        return $table;
    }

    /**
     * Pagination
     */
    function pagination() {

        global $wpcf;

        // Pagination
        $total_items = count( $this->children );
        $per_page = $wpcf->relationship->get_items_per_page( $this->parent_post_type,
                $this->child_post_type );
        $page = isset( $_GET['page'] ) ? intval( $_GET['page'] ) : 1;
        $numberposts = $page == 1 ? 1 : ($page - 1) * $per_page;
        $slice = $page == 1 ? 0 : ($page - 1) * $per_page;
        $next = count( $this->children ) > $numberposts + $per_page;
        $prev = $page == 1 ? false : true;
        if ( $total_items > $per_page ) {
            $this->children = array_splice( $this->children, $slice, $per_page );
        }

        $this->pagination_top = wpcf_pr_admin_has_pagination( $this->parent,
                $this->child_post_type, $page, $prev, $next, $per_page,
                $total_items );
        /*
         *
         *
         * Add pagination bottom
         */
        $options = array(__( 'All', 'wpcf' ) => 'all', 5 => 5, 10 => 10, 15 => 15);
// Add sorting
        $add_data = isset( $_GET['sort'] ) && isset( $_GET['field'] ) ? '&sort=' . sanitize_text_field( $_GET['sort'] ) . '&field='
                . sanitize_text_field( $_GET['field'] ) : '';
        if ( isset( $_GET['post_type_sort_parent'] ) ) {
            $add_data .= '&post_type_sort_parent=' . sanitize_text_field( $_GET['post_type_sort_parent'] );
        }
        $this->pagination_bottom = wpcf_form_simple( array(
            'pagination' => array(
                '#type' => 'select',
                '#before' => __( 'Show', 'wpcf' ),
                '#after' => $this->child_post_type_object->labels->name,
                '#id' => 'wpcf_relationship_num_' . wpcf_unique_id( serialize( $this->children ) ),
                '#name' => $wpcf->relationship->items_per_page_option_name,
                '#options' => $options,
                '#default_value' => $per_page,
                '#attributes' => array(
                    'class' => 'wpcf-relationship-items-per-page',
                    'data-action' => 'action=wpcf_ajax&wpcf_action=pr_pagination'
                    . '&post_id=' . $this->parent->ID . '&post_type='
                    . $this->child_post_type
                    . '&_wpnonce=' . wp_create_nonce( 'pr_pagination' ) . $add_data,
                ),
            ),
                ) );
    }

    /**
     * Returns rows.
     *
     * @return type
     */
    function rows() {
        $rows = array();
        foreach ( $this->children as $child ) {
            $this->_set( $child );
            $rows[$child->ID] = $this->row();
        }
        return $rows;
    }

    /**
     * Returns HTML formatted row
     *
     * While generating rows we collect headers too.
     *
     * @return type
     */
    function row() {
        /*
         * Start output.
         * Output is returned as array - each element is <td> content.
         */
        $row = array();

        /*
         * LOOP over fields
         * Custom settings (specific)
         */
        if ( $this->data['fields_setting'] == 'specific'
                && !empty( $this->data['fields'] ) ) {
            // Set title
            if ( isset( $this->data['fields']['_wp_title'] ) ) {
                $this->headers[] = '_wp_title';
                $row[] = $this->title();
            }
            // Set body
            if ( isset( $this->data['fields']['_wp_body'] ) ) {
                $this->headers[] = '_wp_body';
                $row[] = $this->body();
            }
            // Loop over Types fields
            foreach ( $this->data['fields'] as $field_key => $true ) {
                // If field belongs only to disabled group - remove it.
                $groups = wpcf_admin_fields_get_groups_by_field( $this->cf->__get_slug_no_prefix( $field_key ) );
                if ( empty($groups ) ) {
                    continue;
                }
                $_continue = false;
                // If at least one active - proceed
                foreach ( $groups as $group ) {
                    if ( $group['is_active'] ) {
                        $_continue = true;
                    }
                }
                if ( !$_continue ) {
                    continue;
                }
                // Skip parents
                if ( in_array( $field_key,
                                array('_wp_title', '_wp_body', '_wpcf_pr_parents', '_wpcf_pr_taxonomies') ) ) {
                    continue;
                } else {
                    /*
                     * Set field
                     */
//                    $field_key = $this->cf->__get_slug_no_prefix( $field_key );
                    $this->cf->set( $this->child, $field_key );
                    $row[] = $this->field_form();
                    $this->_field_triggers();
                    // Add to header
//                    $this->headers[] = WPCF_META_PREFIX . $field_key;
                    $this->headers[] = $field_key;
                }
            }
            // Add parent forms
            if ( !empty( $this->data['fields']['_wpcf_pr_parents'] ) ) {
                $_temp = (array) $this->data['fields']['_wpcf_pr_parents'];
                foreach ( $_temp as $_parent => $_true ) {
                    $row[] = $this->_parent_form( $_parent );
                    // Add to header
                    $this->headers['__parents'][$_parent] = $_true;
                }
            }
            // Add taxonomies forms
            if ( !empty( $this->data['fields']['_wpcf_pr_taxonomies'] ) ) {
                $_temp = (array) $this->data['fields']['_wpcf_pr_taxonomies'];
                foreach ( $_temp as $taxonomy => $_true ) {
                    $_taxonomy = get_taxonomy($taxonomy);
                    if ( !empty( $_taxonomy ) ) {
                        $row[] = $this->taxonomy_form( $_taxonomy );
                        // Add to header
                        $this->headers['__taxonomies'][$taxonomy] = $_taxonomy->label;
                    }
                }
            }
            /*
             *
             *
             *
             *
             * DEFAULT SETTINGS
             */
        } else {
            // Set title
            $row[] = $this->title();
            $this->headers[] = '_wp_title';

            // Set body if needed
            if ( $this->data['fields_setting'] == 'all_cf_standard' ) {
                $this->headers[] = '_wp_body';
                $row[] = $this->body();
            }
            /*
             * Loop over groups and fields
             */
            // Get groups
            $groups = wpcf_admin_post_get_post_groups_fields( $this->child,
                    'post_relationships' );
            foreach ( $groups as $group ) {
                if ( empty( $group['fields'] ) ) {
                    continue;
                }
                /*
                 * Loop fields
                 */
                foreach ( $group['fields'] as $field_key => $field ) {
                    /*
                     * Set field
                     */
                    $field_key = $this->cf->__get_slug_no_prefix( $field_key );
                    $this->cf->set( $this->child, $field_key );
                    $row[] = $this->field_form();
                    $this->_field_triggers();
                    // Add to header{
                    $this->headers[] = WPCF_META_PREFIX . $field_key;
                }
            }

            // Add parent forms
            if ( $this->data['fields_setting'] == 'all_cf' ) {
                $this->data['fields']['_wpcf_pr_parents'] = wpcf_pr_admin_get_belongs( $this->child_post_type );
                if ( !empty( $this->data['fields']['_wpcf_pr_parents'] ) ) {
                    $_temp = (array) $this->data['fields']['_wpcf_pr_parents'];
                    foreach ( $_temp as $_parent => $_true ) {
                        if ( $_parent == $this->parent_post_type ) {
                            continue;
                        }
                        $row[] = $this->_parent_form( $_parent );
                        // Add to header
                        $this->headers['__parents'][$_parent] = $_true;
                    }
                }
            }
        }
        return $row;
    }

    /**
     * Add here various triggers for field
     */
    function _field_triggers() {
        /*
         * Check if repetitive - add warning
         */
        if ( wpcf_admin_is_repetitive( $this->cf->cf ) ) {
            $this->repetitive_warning = true;
        }
        /*
         * Check if date - trigger it
         * TODO Move to date
         */
        if ( $this->cf->cf['type'] == 'date' ) {
            $this->trigger_date = true;
        }
    }

    /**
     * Returns HTML formatted title field.
     *
     * @param type $post
     * @return type
     */
    function title()
    {
        $title = '';
        $type = 'textfield';
        if ( !$this->child_supports['title']) {
            $type = 'hidden';
            $title .= wpcf_form_simple(
                array(
                    'field' => array(
                        '#type' => 'markup',
                        '#markup' => sprintf('%s id: %d', $this->child_post_type_object->labels->singular_name, $this->child->ID),
                    ),
                )
            );
        }
        $title .= wpcf_form_simple(
            array(
                'field' => array(
                    '#type' =>  $type,
                    '#id' => 'wpcf_post_relationship_'
                    . $this->child->ID . '_wp_title',
                    '#name' => 'wpcf_post_relationship['
                    . $this->parent->ID . ']['
                    . $this->child->ID . '][_wp_title]',
                    '#value' => trim( $this->child->post_title ),
                    '#inline' => true,
                ),
            )
        );
        return $title;
    }

    /**
     * Returns HTML formatted body field.
     *
     * @return type
     */
    function body() {
        return wpcf_form_simple(
                        array('field' => array(
                                '#type' => 'textarea',
                                '#id' => 'wpcf_post_relationship_'
                                . $this->child->ID . '_wp_body',
                                '#name' => 'wpcf_post_relationship['
                                . $this->parent->ID . ']['
                                . $this->child->ID . '][_wp_body]',
                                '#value' => $this->child->post_content,
                                '#attributes' => array('style' => 'width:300px;height:100px;'),
                                '#inline' => true,
                            )
                        )
        );
    }

    /**
     * Returns HTML formatted taxonomy form.
     *
     * @param type $taxonomy
     * @return type
     */
    function taxonomy_form( $taxonomy, $simple = false ) {
        // SIMPLIFIED VERSION
        if ( $simple ) {
            $terms = wp_get_post_terms( $this->child->ID, $taxonomy->name, array() );
            $selected = ( !empty( $terms ) ) ? array_shift($terms)->term_id : -1;
            $output =  wp_dropdown_categories( array(
                'taxonomy' => $taxonomy->name,
                'selected' => $selected,
                'echo' => false,
                'hide_empty' => false,
                'hide_if_empty' => true,
                'show_option_none' => sprintf( __( 'No %s', 'wpcf' ),
                        $taxonomy->name ),
                'name' => 'wpcf_post_relationship['
                . $this->parent->ID . '][' . $this->child->ID
                . '][taxonomies][' . $taxonomy->name . ']',
                'id' => 'wpcf_pr_' . $this->child->ID . '_' . $taxonomy->name,
                'hierarchical' => true,
                'depth' => 9999
                    )
            );

            return empty( $output ) ? sprintf( __( 'No %s', 'wpcf' ),
                    $taxonomy->label ) : $output;
        }

        $data = array(
            'post' => $this->child,
            'taxonomy' => $taxonomy->name,
        );
        if ( $taxonomy->name == 'category' ) {
            $data['_wpcf_name'] = "wpcf_post_relationship[{$this->parent->ID}][{$this->child->ID}][taxonomies][{$taxonomy->name}][]";
            $output = WPCF_Loader::template('child-tax-category', $data);
            // Reduce JS processing
            return str_replace( "name=\"post_category[]",
                    "name=\"{$data['_wpcf_name']}", $output );
        }
        if ( $taxonomy->hierarchical ) {
            $data['_wpcf_name'] = "wpcf_post_relationship[{$this->parent->ID}][{$this->child->ID}][taxonomies][{$taxonomy->name}][]";
            $output = WPCF_Loader::template('child-tax-category', $data);
            // Reduce JS processing
            return str_replace( "name=\"tax_input[{$taxonomy->name}][]",
                    "name=\"{$data['_wpcf_name']}", $output );
        }
        $data['_wpcf_name'] = "wpcf_post_relationship[{$this->parent->ID}][{$this->child->ID}][taxonomies][{$taxonomy->name}]";
        $output = WPCF_Loader::template('child-tax-tag', $data);
        // Reduce JS processing
        return str_replace( "name=\"tax_input[{$taxonomy->name}]",
                "name=\"{$data['_wpcf_name']}", $output );
    }

    /**
     * Returns element form as array.
     *
     * This is done per field.
     *
     * @param type $key Field key as stored
     * @return array
     */
    function field_form() {
        if ( defined( 'WPTOOLSET_FORMS_VERSION' ) ) {
            $field = $this->cf->cf;
            $meta = get_post_meta( $this->child->ID, $field['meta_key'] );
            $field['suffix'] = "-{$this->child->ID}";
            $config = wptoolset_form_filter_types_field( $field, $this->child->ID );
            // Do not allow repetitive
            $config['repetitive'] = false;
            $config['name'] = $this->cf->alter_form_name( 'wpcf_post_relationship['
                    . $this->parent->ID . ']', $config['name'] );
            if ( !empty( $config['options'] ) ) {
                foreach ( $config['options'] as &$v ) {
                    if ( isset( $v['name'] ) ) {
                        $v['name'] = $this->alter_form_name( $v['name'] );
                    }
                }
            }
            if ( $config['type'] == 'wysiwyg' ) {
                $config['type'] = 'textarea';
            }
            return wptoolset_form_field( 'post', $config, $meta );
        }
        /*
         *
         * Get meta form for field
         */
        $form = $this->cf->_get_meta_form( $this->cf->__meta,
                $this->cf->meta_object->meta_id, false );
        /*
         *
         * Filter form
         */
        $_filtered_form = $this->__filter_meta_form( $form );

        return wpcf_form_simple( apply_filters( 'wpcf_relationship_child_meta_form',
                                $_filtered_form, $this->cf ) );
    }

    /**
     * Filters meta form.
     *
     * IMPORTANT: This is place where look of child form is altered.
     * Try not to spread it over other code.
     *
     * @param string $form
     * @return string
     */
    function __filter_meta_form( $form = array() ) {
        foreach ( $form as $k => &$e ) {
            /*
             *
             * Filter name
             */
            if ( isset( $e['#name'] ) ) {
                $e['#name'] = $this->cf->alter_form_name( 'wpcf_post_relationship['
                        . $this->parent->ID . ']', $e['#name'] );
            }
            /*
             * Some fields have #options and names set there.
             * Loop over them and adjust.
             */
            if ( !empty( $e['#options'] ) ) {
                foreach ( $e['#options'] as $_k => $_v ) {
                    if ( isset( $_v['#name'] ) ) {
                        $e['#options'][$_k]['#name'] = $this->alter_form_name( $_v['#name'] );
                    }
                }
            }
            if ( isset( $e['#title'] ) ) {
                unset( $e['#title'] );
            }
            if ( isset( $e['#description'] ) ) {
                unset( $e['#description'] );
            }
            $e['#inline'] = true;
        }

        return $form;
    }

    function alter_form_name( $name, $parent_id = null ){
        if ( is_null( $parent_id ) ) {
            $parent_id = $this->parent->ID;
        }
        return $this->cf->alter_form_name(
                        'wpcf_post_relationship[' . $parent_id . ']', $name
        );
    }

    /**
     * Content for choose parent column.
     *
     * @return boolean
     */
    function _parent_form( $post_parent = '' ) {
        $item_parents = wpcf_pr_admin_get_belongs( $this->child_post_type );
        if ( $item_parents ) {
            foreach ( $item_parents as $parent => $temp_data ) {

                // Skip if only current available
                if ( $parent == $this->parent_post_type ) {
                    continue;
                }

                if ( !empty( $post_parent ) && $parent != $post_parent ) {
                    continue;
                }

                // Get parent ID
                $meta = get_post_meta( $this->child->ID,
                        '_wpcf_belongs_' . $parent . '_id', true );
                $meta = empty( $meta ) ? 0 : $meta;

                // Get form
                $belongs_data = array('belongs' => array($parent => $meta));
                $temp_form = wpcf_pr_admin_post_meta_box_belongs_form( $this->child,
                        $parent, $belongs_data );

                if ( empty( $temp_form ) ) {
                    return '<span class="types-small-italic">' . __( 'No parents available',
                                    'wpcf' ) . '</span>';
                }
                unset(
                        $temp_form[$parent]['#suffix'],
                        $temp_form[$parent]['#prefix'],
                        $temp_form[$parent]['#title']
                );
                $temp_form[$parent]['#name'] = 'wpcf_post_relationship['
                        . $this->parent->ID . '][' . $this->child->ID
                        . '][parents][' . $parent . ']';
                // Return HTML formatted output
                return wpcf_form_simple( $temp_form );
            }
        }
        return '<span class="types-small-italic">' . __( 'No parents available',
                        'wpcf' ) . '</span>';
    }

    /**
     * HTML formatted row.
     *
     * @return type
     */
    function child_row( $child ) {
        $child_id = $child->ID;
        $this->_set( $child );
        $row = $this->row();
        ob_start();
        include WPCF_EMBEDDED_INC_ABSPATH . '/relationship/child-table-row.php';
        $o = ob_get_contents();
        ob_end_clean();
        return $o;
    }

    /**
     * Header HTML formatted output.
     *
     * Each header <th> is array element. Sortable.
     *
     * @return array 'header_id' => html
     */
    function headers() {

        // Sorting
        $dir = isset( $_GET['sort'] ) && $_GET['sort'] == 'ASC' ? 'DESC' : 'ASC';
        $dir_default = 'ASC';
        $sort_field = isset( $_GET['field'] ) ? sanitize_text_field( $_GET['field'] ) : '';

        // Set values
        $post = $this->parent;
        $post_type = $this->child_post_type;
        $parent_post_type = $this->parent_post_type;
        $data = $this->data;

        $wpcf_fields = wpcf_admin_fields_get_fields( true );
        $headers = array();

        foreach ( $this->headers as $k => $header ) {
            if ( $k === '__parents' || $k === '__taxonomies' ) {
                continue;
            }

            if ( $header == '_wp_title' ) {
                if ( $this->child_supports['title']) {
                    $title_dir = $sort_field == '_wp_title' ? $dir : 'ASC';
                    $headers[$header] = '';
                    $headers[$header] .= $sort_field == '_wp_title' ? '<div class="wpcf-pr-sort-' . $dir . '"></div>' : '';
                    $headers[$header] .= '<a href="' . admin_url( 'admin-ajax.php?action=wpcf_ajax&amp;wpcf_action=pr_sort&amp;field='
                        . '_wp_title&amp;sort=' . $title_dir . '&amp;post_id=' . $post->ID . '&amp;post_type='
                        . $post_type . '&amp;_wpnonce='
                        . wp_create_nonce( 'pr_sort' ) ) . '">' . __( 'Post Title' ) . '</a>';
                } else {
                    $headers[$header] = 'ID';
                }
            } else if ( $header == '_wp_body' ) {
                $body_dir = $sort_field == '_wp_body' ? $dir : $dir_default;
                $headers[$header] = '';
                $headers[$header] .= $sort_field == '_wp_body' ? '<div class="wpcf-pr-sort-' . $dir . '"></div>' : '';
                $headers[$header] .= '<a href="' . admin_url( 'admin-ajax.php?action=wpcf_ajax&amp;wpcf_action=pr_sort&amp;field='
                                . '_wp_body&amp;sort=' . $body_dir . '&amp;post_id=' . $post->ID . '&amp;post_type='
                                . $post_type . '&amp;_wpnonce='
                                . wp_create_nonce( 'pr_sort' ) ) . '">' . __( 'Post Body' ) . '</a>';
            } else if ( strpos( $header, WPCF_META_PREFIX ) === 0
                    && isset( $wpcf_fields[str_replace( WPCF_META_PREFIX, '',
                                    $header )] ) ) {
                wpcf_field_enqueue_scripts( $wpcf_fields[str_replace( WPCF_META_PREFIX,
                                '', $header )]['type'] );
                $field_dir = $sort_field == $header ? $dir : $dir_default;
                $headers[$header] = '';
                $headers[$header] .= $sort_field == $header ? '<div class="wpcf-pr-sort-' . $dir . '"></div>' : '';
                $headers[$header] .= '<a href="' . admin_url( 'admin-ajax.php?action=wpcf_ajax&amp;wpcf_action=pr_sort&amp;field='
                                . $header . '&amp;sort=' . $field_dir . '&amp;post_id=' . $post->ID . '&amp;post_type='
                                . $post_type . '&amp;_wpnonce='
                                . wp_create_nonce( 'pr_sort' ) ) . '">' . stripslashes( $wpcf_fields[str_replace( WPCF_META_PREFIX,
                                        '', $header )]['name'] ) . '</a>';
            } else {
                $field_dir = $sort_field == $header ? $dir : $dir_default;
                $headers[$header] = '';
                $headers[$header] .= $sort_field == $header ? '<div class="wpcf-pr-sort-' . $dir . '"></div>' : '';
                $headers[$header] .= '<a href="' . admin_url( 'admin-ajax.php?action=wpcf_ajax&amp;wpcf_action=pr_sort&amp;field='
                                . $header . '&amp;sort=' . $field_dir . '&amp;post_id=' . $post->ID . '&amp;post_type='
                                . $post_type . '&amp;_wpnonce='
                                . wp_create_nonce( 'pr_sort' ) ) . '">'
                        . stripslashes( $header ) . '</a>';
            }
        }
        if ( !empty( $this->headers['__parents'] ) ) {
            foreach ( $this->headers['__parents'] as $_parent => $data ) {
                if ( $_parent == $parent_post_type ) {
                    continue;
                }
                $temp_parent_type = get_post_type_object( $_parent );
                if ( empty( $temp_parent_type ) ) {
                    continue;
                }
                $parent_dir = $sort_field == '_wpcf_pr_parent' ? $dir : $dir_default;
                $headers['_wpcf_pr_parent_' . $_parent] = $sort_field == '_wpcf_pr_parent' ? '<div class="wpcf-pr-sort-' . $dir . '"></div>' : '';
                $headers['_wpcf_pr_parent_' . $_parent] .= '<a href="' . admin_url( 'admin-ajax.php?action=wpcf_ajax&amp;wpcf_action=pr_sort&amp;field='
                                . '_wpcf_pr_parent&amp;sort='
                                . $parent_dir . '&amp;post_id=' . $post->ID . '&amp;post_type='
                                . $post_type . '&amp;post_type_sort_parent='
                                . $_parent . '&amp;_wpnonce='
                                . wp_create_nonce( 'pr_sort' ) ) . '">' . $temp_parent_type->label . '</a>';
            }
        }
        if ( !empty( $this->headers['__taxonomies'] ) ) {
            foreach ( $this->headers['__taxonomies'] as $tax_id => $taxonomy ) {
                $headers["_wpcf_pr_taxonomy_$tax_id"] = $taxonomy;
            }
        }
        return $headers;
    }

}
