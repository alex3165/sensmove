<?php

class WPCF_WPViews
{

    /**
     * Init called from WPCF_Loader.
     */
    public static function init() {
        add_action( 'views_edit_screen', array('WPCF_WPViews', 'editScreenInit') );
        add_action( 'layouts_edit_screen', array('WPCF_WPViews', 'editScreenInit') );
        add_action( 'views_ct_inline_editor',
                array('WPCF_WPViews', 'addEditorDropdownFilter') );
    }

    /**
     * Actions for Views edit screens.
     */
    public static function editScreenInit() {
        wp_enqueue_script( 'types' );
        wp_enqueue_script( 'types-wp-views' );
        wp_enqueue_script( 'toolset-colorbox' );
        wp_enqueue_style( 'toolset-colorbox' );
        self::addEditorDropdownFilter();
    }

    /**
     * Adds filtering editor dropdown items.
     */
    public static function addEditorDropdownFilter() {
        add_filter( 'editor_addon_menus_wpv-views',
                array('WPCF_WPViews', 'editorDropdownFilter') );
        add_filter( 'editor_addon_menus_wpv-views',
                'wpcf_admin_post_add_usermeta_to_editor_js' );
    }

    /**
     * Adds items to view dropdown.
     * 
     * @param type $items
     * @return type 
     */
    public static function editorDropdownFilter( $items ) {
        $post = wpcf_admin_get_edited_post();
        if ( empty( $post ) ) {
            $post = (object) array('ID' => -1);
        }
        $groups = wpcf_admin_fields_get_groups( 'wp-types-group', 'group_active' );
        $all_post_types = implode( ' ',
                get_post_types( array('public' => true) ) );
        $add = array();
        if ( !empty( $groups ) ) {
            // $group_id is blank therefore not equal to $group['id']
            // use array for item key and CSS class
            $item_styles = array();

            foreach ( $groups as $group ) {
                $fields = wpcf_admin_fields_get_fields_by_group( $group['id'],
                        'slug', true, false, true );
                if ( !empty( $fields ) ) {
                    // code from Types used here without breaking the flow
                    // get post types list for every group or apply all
                    $post_types = get_post_meta( $group['id'],
                            '_wp_types_group_post_types', true );
                    if ( $post_types == 'all' ) {
                        $post_types = $all_post_types;
                    }
                    $post_types = trim( str_replace( ',', ' ', $post_types ) );
                    $item_styles[$group['name']] = $post_types;

                    foreach ( $fields as $field ) {
                        $callback = 'wpcfFieldsEditorCallback(\'' . $field['id']
                                . '\', \'postmeta\', ' . $post->ID . ')';
                        $add[$group['name']][stripslashes( $field['name'] )] = array(stripslashes( $field['name'] ), trim( wpcf_fields_get_shortcode( $field ),
                                    '[]' ), $group['name'], $callback);
                        // TODO Remove - it's not post edit screen (meta box JS and CSS)
                        WPCF_Fields::enqueueScript( $field['type'] );
                        WPCF_Fields::enqueueStyle( $field['type'] );
                    }
                }
            }
        }

        $search_key = '';

        // Iterate all items to be displayed in the "V" menu
        foreach ( $items as $key => $item ) {
            if ( $key == __( 'Basic', 'wpv-views' ) ) {
                $search_key = 'found';
                continue;
            }
            if ( $search_key == 'found' ) {
                $search_key = $key;
            }

            if ( $key == __( 'Field', 'wpv-views' ) && isset( $item[trim( wpcf_types_get_meta_prefix(),
                                    '-' )] ) ) {
                unset( $items[$key][trim( wpcf_types_get_meta_prefix(), '-' )] );
            }
        }
        if ( empty( $search_key ) || $search_key == 'found' ) {
            $search_key = count( $items );
        }

        $insert_position = array_search( $search_key, array_keys( $items ) );
        $part_one = array_slice( $items, 0, $insert_position );
        $part_two = array_slice( $items, $insert_position );
        $items = $part_one + $add + $part_two;

        // apply CSS styles to each item based on post types
        foreach ( $items as $key => $value ) {
            if ( isset( $item_styles[$key] ) ) {
                $items[$key]['css'] = $item_styles[$key];
            } else {
                $items[$key]['css'] = $all_post_types;
            }
        }

        return $items;
    }

}
