<?php
/**
 *
 * Post Types Class
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/embedded/classes/class.wpcf-post-types.php $
 * $LastChangedDate: 2015-03-16 12:03:31 +0000 (Mon, 16 Mar 2015) $
 * $LastChangedRevision: 1113864 $
 * $LastChangedBy: iworks $
 *
 */

require_once WPCF_EMBEDDED_INC_ABSPATH . '/custom-types.php';

/**
 * Post Types Class
 *
 * @since Types 1.2
 * @package Types
 * @subpackage Classes
 * @version 0.1
 * @category Post Type
 * @author srdjan <srdjan@icanlocalize.com>
 */
class WPCF_Post_Types
{

    var $data;
    var $settings;
    var $messages = null;

    function __construct()
    {
        add_action('admin_init', array($this, 'admin_init'));
        add_action('admin_head-nav-menus.php', array($this, 'add_filters'));
        add_filter('wp_setup_nav_menu_item',  array( $this, 'setup_archive_item'));
        add_filter('wp_nav_menu_objects', array( $this, 'maybe_make_current'));
    }
    /**
     * Check has some custom fields to display.
     *
     * Check custom post type for custom fields to display on custom post edit 
     * screen.
     *
     * @since 1.6.6
     * @access (for functions: only use if private)
     *
     * @return bool It has some fields?
     */
    private function check_has_custom_fields($data)
    {
        return
            isset($data['custom_fields'])
            && is_array($data['custom_fields'])
            && !empty($data['custom_fields']);
    }

    /**
     * Admin init.
     *
     * Admin init function used to add columns..
     *
     * @since 1.6.6
     */
    public function admin_init()
    {
        $custom_post_types = wpcf_get_active_custom_types();
        foreach( $custom_post_types as $post_type => $data ) {
            if ( $this->check_has_custom_fields($data)) {
                $hook = sprintf('manage_edit-%s_columns', $post_type);
                add_filter($hook, array($this, 'manage_posts_columns'));
                $hook = sprintf('manage_%s_posts_custom_column', $post_type);
                add_action($hook, array($this, 'manage_custom_columns'), 10, 2);
            }
        }
    }

    /**
     * Add custom fields as a columns.
     *
     * Add custom fields as a columns on custom post admin list
     *
     * @since 1.6.6
     *
     * @param array $columns Hashtable of columns;
     * @return array Hashtable of columns;
     */
    public function manage_posts_columns($columns)
    {
        $screen = get_current_screen();
        if ( !isset( $screen->post_type) ) {
            return $columns;
        }
        $custom_post_types = wpcf_get_active_custom_types();
        if(
            !isset($custom_post_types[$screen->post_type])
            || !$this->check_has_custom_fields($custom_post_types[$screen->post_type])
        ) {
            return $columns;
        }
        $fields = wpcf_admin_fields_get_fields();
        ksort($fields);
        foreach( $fields as $key => $data ) {
            if ( !isset($data['meta_key']) ) {
                continue;
            }
            if ( in_array($data['meta_key'], $custom_post_types[$screen->post_type]['custom_fields']) ) {
                $columns[$data['meta_key']] = $data['name'];
            }
        }
        return $columns;
    }

    /**
     * Show value of custom field.
     *
     * Show value of custom field.
     *
     * @since 1.6.6
     *
     * @param string $column Column name,
     * @param int $var Current post ID.
     */
    public function manage_custom_columns($column, $post_id)
    {
        $value = get_post_meta($post_id, $column, true);
        if ( empty($value) ) {
            return;
        }
        $field = wpcf_admin_fields_get_field_by_meta_key($column);
        if ( isset( $field['type'] ) ) {
            switch( $field['type'] ) {
            case 'image':
                $value = sprintf(
                    '<img src="%s" width="120" />',
                    $value
                );
                break;
            case 'skype':
                $value = isset($value['skypename'])? $value['skypename']:'';
                break;
            case 'date':
                require_once WPTOOLSET_FORMS_ABSPATH . '/classes/class.date.php';
                $value = WPToolset_Field_Date::timetodate($value);
                break;
            }
        }
        if ( is_string($value ) ) {
            echo $value;
        }
    }

    /**
     * Assign menu item the appropriate url
     * @param  object $menu_item
     * @return object $menu_item
     */
    public function setup_archive_item( $menu_item ) {
        if ( $menu_item->type !== 'post_type_archive' ) {
            return $menu_item;
        }
        $post_type = $menu_item->object;
        if (post_type_exists( $post_type )) {
            $data = get_post_type_object( $post_type );
            $menu_item->type_label = sprintf( __( 'Archive for %s', 'wpcf' ), $data->labels->name);
            $menu_item->url = get_post_type_archive_link( $post_type );
        }
        return $menu_item;
    }

    public function add_filters()
    {
        $custom_post_types = wpcf_get_active_custom_types();
        if ( empty($custom_post_types) ) {
            return;
        }
        foreach ( $custom_post_types as $slug => $data ) {
            add_filter( 'nav_menu_items_' . $slug, array( $this, 'add_archive_checkbox' ), null, 3 );
        }
    }

    public function add_archive_checkbox( $posts, $args, $post_type )
    {
        global $_nav_menu_placeholder, $wp_rewrite;
        $_nav_menu_placeholder = ( 0 > $_nav_menu_placeholder ) ? intval($_nav_menu_placeholder) - 1 : -1;

        array_unshift( $posts, (object) array(
            'ID' => 0,
            'object_id' => $_nav_menu_placeholder,
            'post_title' => $post_type['args']->labels->all_items,
            'post_type' => 'nav_menu_item',
            'type' => 'post_type_archive',
            'object' => $post_type['args']->slug,
        ) );

        return $posts;
    }

    /**
     * Make post type archive link 'current'
     * @uses   Post_Type_Archive_Links :: get_item_ancestors()
     * @param  array $items
     * @return array $items
     */
    public function maybe_make_current( $items ) {
        foreach ( $items as $item ) {
            if ( 'post_type_archive' !== $item->type ) {
                continue;
            }
            $post_type = $item->object;
            if (
                ! is_post_type_archive( $post_type )
                AND ! is_singular( $post_type )
            )
            continue;

            // Make item current
            $item->current = true;
            $item->classes[] = 'current-menu-item';

            // Loop through ancestors and give them 'parent' or 'ancestor' class
            $active_anc_item_ids = $this->get_item_ancestors( $item );
            foreach ( $items as $key => $parent_item ) {
                $classes = (array) $parent_item->classes;

                // If menu item is the parent
                if ( $parent_item->db_id == $item->menu_item_parent ) {
                    $classes[] = 'current-menu-parent';
                    $items[ $key ]->current_item_parent = true;
                }

                // If menu item is an ancestor
                if ( in_array( intval( $parent_item->db_id ), $active_anc_item_ids ) ) {
                    $classes[] = 'current-menu-ancestor';
                    $items[ $key ]->current_item_ancestor = true;
                }

                $items[ $key ]->classes = array_unique( $classes );
            }
        }

        return $items;
    }

    /**
     * Get menu item's ancestors
     * @param  object $item
     * @return array  $active_anc_item_ids
     */
    public function get_item_ancestors( $item ) {
        $anc_id = absint( $item->db_id );

        $active_anc_item_ids = array();
        while (
            $anc_id = get_post_meta( $anc_id, '_menu_item_menu_item_parent', true )
            AND ! in_array( $anc_id, $active_anc_item_ids )
        )
        $active_anc_item_ids[] = $anc_id;

        return $active_anc_item_ids;
    }

    function set($post_type, $settings = null)
    {
        $data = get_post_type_object( $post_type );
        if ( empty( $data ) ) {

        }
        $this->data = $data;
        $this->settings = is_null( $settings ) ? $this->get_settings( $post_type ) : (array) $settings;
    }

    function _get_labels($data)
    {
        $data = (array) $data;
        return isset( $data['labels'] ) ? (object) $data['labels'] : new stdClass();
    }

    function check_singular_plural_match($data = null)
    {
        if ( is_null( $data ) ) {
            $data = $this->data;
        }
        $labels = $this->_get_labels( $data );
        if ( array_key_exists( 'ignore', $labels ) && 'on' == $labels->ignore ) {
            return false;
        }
        return strtolower( $labels->singular_name ) == strtolower( $labels->name );
    }

    function message($message_id)
    {
        $this->_set_messenger();
        return isset( $this->messages[$message_id] ) ? $this->messages[$message_id] : 'Howdy!';
    }

    function _set_messenger()
    {
        if ( is_null( $this->messages ) ) {
            include dirname( __FILE__ ) . '/post-types/messages.php';
            $this->messages = $messages;
        }
    }

    function get_settings($post_type)
    {
        return wpcf_get_custom_post_type_settings( $post_type );
    }

}
