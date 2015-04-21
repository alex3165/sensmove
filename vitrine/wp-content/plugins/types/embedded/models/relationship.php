<?php
/*
 * Relationship model.
 */

class WPCF_Relationship_Model
{

    /**
     * Fetch children by post type.
     *
     * @global object $wpdb
     *
     * @param type $post
     * @param type $post_type
     * @param string $data
     * @param array $params
     * @param array $user_query - Override default query
     * @return type
     */
    public static function getChildrenByPostType( $post, $post_type, $data, $params = array(), $user_query = array() )
    {

        if ( empty( $post->ID ) ) {
            return array();
        }

        global $wpdb;

        $items = array();

        // Merge with user query
        $query = wp_parse_args(
            $user_query,
            array(
                'post_type' => $post_type,
                'numberposts' => -1,
                'post_status' => array('publish', 'pending', 'draft', 'future', 'private'),
                'meta_key' => '_wpcf_belongs_' . $post->post_type . '_id',
                'meta_value' => $post->ID,
                'suppress_filters' => 0,
            )
        );

        // Cleanup data
        if ( empty( $data['fields_setting'] ) ) {
            $data['fields_setting'] = 'all_cf';
        }

        // List items
        if ( isset( $params['sort'] ) && isset( $params['field'] ) ) {

            // Set sorting
            $query['order'] = esc_attr( strtoupper( strval( $params['sort'] ) ) );
            if ( !preg_match('/^(A|DE)SC$/', $query['order']) ) {
                $query['order'] = 'ASC';
            }

            /*
             *
             * Order by title
             */
            if ( $params['field'] == '_wp_title' ) {
                $query['orderby'] = 'title';
                $query = apply_filters( 'wpcf_relationship_get_children_query',
                        $query, $post, $post_type, $data,
                        esc_attr( $params['field'] ) );
                $items = get_posts( $query );
                /*
                 *
                 * Order by parents
                 */
            } else if ( $params['field'] == '_wpcf_pr_parent' ) {
                $query = apply_filters( 'wpcf_relationship_get_children_query',
                        $query, $post, $post_type, $data,
                        esc_attr( $params['field'] ) );
                $items = get_posts( $query );
                if ( !empty( $items ) ) {
                    $include = array();
                    $additional = array();
                    foreach ( $items as $item ) {
                        $meta = wpcf_get_post_meta( $item->ID,
                                '_wpcf_belongs_'
                                . esc_attr( $params['post_type_sort_parent'] )
                                . '_id', true );
                        if ( empty( $meta ) ) {
                            $additional[] = $item;
                            continue;
                        }
                        $include[intval( $meta )][] = $item;
                    }
                    if ( !empty( $include ) ) {
                        ksort( $include, SORT_NUMERIC );
                        if ( $query['order'] == 'DESC' ) {
                            $include = array_reverse( $include );
                        }
                        $sorted = array();
                        foreach ( $include as $meta_value => $posts ) {
                            foreach ( $posts as $post ) {
                                $sorted[] = $post;
                            }
                        }
                        $items = array_merge( $sorted, $additional );
                    }
                }
                /*
                 *
                 * Order by body
                 */
            } else if ( $params['field'] == '_wp_body' ) {
                $_query = "
                    SELECT p.ID
                    FROM $wpdb->posts p
                    WHERE p.post_type = %s
                    AND p.post_status NOT IN ('auto-draft', 'trash', 'inherit')
                    ORDER BY p.post_content " . ( ( 'ASC' == $query['order'] ) ? 'ASC' : 'DESC' );
                $sorted = $wpdb->get_results( $wpdb->prepare( $_query, $post_type ) );
                if ( !empty( $sorted ) ) {
                    $query['orderby'] = 'post__in';
                    foreach ( $sorted as $key => $value ) {
                        $query['post__in'][] = $value->ID;
                    }
                }
                $query = apply_filters( 'wpcf_relationship_get_children_query',
                        $query, $post, $post_type, $data,
                        esc_attr( $params['field'] ) );
                $items = get_posts( $query );
                /*
                 *
                 *
                 * Order by custom field
                 */
            } else {
                $field = wpcf_admin_fields_get_field( str_replace( 'wpcf-', '',
                                $params['field'] ) );
                if ( !empty( $field ) ) {
                    $query['orderby'] = isset( $field['type'] ) && in_array( $field['type'],
                                    array('numeric', 'date') ) ? 'meta_value_num' : 'meta_value';
                }
                $query = apply_filters( 'wpcf_relationship_get_children_query',
                        $query, $post, $post_type, $data,
                        esc_attr( $params['field'] ) );
                $items = get_posts( $query );
                if ( !empty( $items ) ) {
                    $include = array();
                    $additional = array();
                    foreach ( $items as $item ) {
                        $meta = wpcf_get_post_meta( $item->ID,
                                'wpcf-' . $field['slug'], true );
                        if ( empty( $meta ) ) {
                            $additional[] = $item;
                            continue;
                        }
                        $check = wpcf_cd_post_edit_field_filter( array(),
                                $field, $item, 'post-relationship-sort' );
                        if ( isset( $check['__wpcf_cd_status'] )
                                && $check['__wpcf_cd_status'] == 'failed' ) {
                            $additional[] = $item;
                            continue;
                        }
                        $key = $query['orderby'] == 'meta_value_num' ? intval( $meta ) : strval( $meta );
                        $include[$key][] = $item;
                    }
                    if ( !empty( $include ) ) {
                        if ( $query['orderby'] == 'meta_value_num' ) {
                            ksort( $include, SORT_NUMERIC );
                        } else {
                            ksort( $include, SORT_STRING );
                        }
                        if ( $query['order'] == 'DESC' ) {
                            $include = array_reverse( $include );
                        }
                        $sorted = array();
                        foreach ( $include as $meta_value => $posts ) {
                            foreach ( $posts as $post ) {
                                $sorted[] = $post;
                            }
                        }
                        $items = array_merge( $sorted, $additional );
                    }
                }
            }
            /**
             *
             * Regular
             *
             */
        } else {
            $query = apply_filters( 'wpcf_relationship_get_children_query', $query, $post, $post_type, $data );
            $items = get_posts( $query );
        }
        return $items;
    }
}
