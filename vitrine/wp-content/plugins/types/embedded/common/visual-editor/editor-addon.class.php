<?php
if ( file_exists( dirname(__FILE__) . '/editor-addon-generic.class.php') && !class_exists( 'Editor_addon' )  ) {

    require_once( dirname(__FILE__) . '/editor-addon-generic.class.php' );


    class Editor_addon extends Editor_addon_generic
    {

        function get_fields_list() {
            return apply_filters( 'toolset_editor_addon_post_fields_list', $this->items );
        }

        /**
         * Adding a "V" button to the menu
         * @param string $context
         * @param string $text_area
         * @param boolean $standard_v is this a standard V button
         */
        function add_form_button( $context, $text_area = '', $standard_v = true, $add_views = false, $codemirror_button = false )
        {
            /**
             * turn off button
             */
            if ( !apply_filters('toolset_editor_add_form_buttons', true) ) {
                return;
            }

            global $wp_version;

            if ( empty($context) &&  $text_area == '' ){
                return;
            }
            // WP 3.3 changes ($context arg is actually a editor ID now)
            if ( version_compare( $wp_version, '3.1.4', '>' ) && !empty( $context ) ) {
                $text_area = $context;
            }

            // Apply filters
            $this->items = apply_filters( 'editor_addon_items_' . $this->name,
                    $this->items );

            // add_filter('editor_addon_parent_items', array($this, 'wpv_add_parent_items'), 10, $this->items);
            // Apply filter parent items
            //apply_filters('editor_addon_parent_items', $this->items);
            // sort the items into menu levels.

            $menus = array();
            $sub_menus = array();

            if( $this->items )
            foreach ( $this->items as $item ) {
                $parts = explode( '-!-', $item[2] );
                $menu_level = &$menus;
                foreach ( $parts as $part ) {
                    if ( $part != '' ) {
                        if ( !array_key_exists( $part, $menu_level ) ) {
                            $menu_level[$part] = array();
                        }
                        $menu_level = &$menu_level[$part];
                    }
                }
                $menu_level[$item[0]] = $item;
            }

            // Apply filters
            $menus = apply_filters( 'editor_addon_menus_' . $this->name, $menus );

            // add View Template links to the "Add Field" button
            if ( !$standard_v ) {
                $this->add_view_type( $menus, 'view-template',
                        __( 'View templates', 'wpv-views' ) );
                $this->add_view_type( $menus, 'view',
                        __( 'Post View', 'wpv-views' ) );
                $this->add_view_type( $menus, 'view',
                        __( 'Taxonomy View', 'wpv-views' ) );
                $this->add_view_type( $menus, 'view',
                        __( 'User View', 'wpv-views' ) );
            }

            if ( $standard_v && $add_views ) {
                $this->add_view_type( $menus, 'view',
                        __( 'Post View', 'wpv-views' ) );
                $this->add_view_type( $menus, 'view',
                        __( 'Taxonomy View', 'wpv-views' ) );
                $this->add_view_type( $menus, 'view',
                        __( 'User View', 'wpv-views' ) );
            }

            // Sort menus
            if ( is_array( $menus ) ) {
                $menus = $this->sort_menus_alphabetically( $menus );
            }

            $this->_media_menu_direct_links = array();
            $menus_output = $this->_output_media_menu( $menus, $text_area, $standard_v );

            $direct_links = implode( ' ', $this->_media_menu_direct_links );
            $dropdown_class = 'js-editor_addon_dropdown-'.$this->name;
            $icon_class = 'js-wpv-shortcode-post-icon-'.$this->name;
            if ( $this->name == 'wpv-views' ) {
                $button_label = __( 'Fields and Views', 'wpv-views' );
            } else if ( $this->name == 'types' ) {
                $button_label = __( 'Types', 'wpv-views' );
            } else {
                $button_label = '';
            }

            if( '' !== $this->media_button_image )
            {
                $addon_button = '<span class="button wpv-shortcode-post-icon '. $icon_class .'"><img src="' . $this->media_button_image . '" />' . $button_label . '</span>';
            }
            else if( '' !== $this->icon_class ){

                $addon_button = '<span class="button wpv-shortcode-post-icon '. $icon_class .'"><i class="'.$this->icon_class.'"></i><span class="button-label">' . $button_label . '</span></span>';
            }

            if ( !$standard_v ) {

                if( '' !== $this->media_button_image )
                {
                    $addon_button = '<span class="button vicon wpv-shortcode-post-icon '. $icon_class .'"><img src="' . $this->media_button_image . '" />' . $button_label . '</span>';
                }
                else if( '' !== $this->icon_class )
                {
                    $addon_button = '<span class="button vicon wpv-shortcode-post-icon '. $icon_class .'"><i class="'.$this->icon_class.'"></i><span class="button-label">' . $button_label . '</span></span>';
                }
            }
            // Codemirror (new layout) button
            if ( $codemirror_button ){
                 $addon_button = '<button class="js-code-editor-toolbar-button js-code-editor-toolbar-button-v-icon button-secondary">'.
                        '<i class="icon-views-logo ont-icon-18"></i><span class="button-label">'. __('Fields and Views', 'wpv-views') .'</span></button>';
            }
            // add search box
            $searchbar = $this->get_search_bar();

            // generate output content
            $out = '' .
            $addon_button . '
            <div class="editor_addon_dropdown '. $dropdown_class .'" id="editor_addon_dropdown_' . rand() . '">
                <h3 class="title">' . $this->button_text . '</h3>
                <div class="close">&nbsp;</div>
                <div class="editor_addon_dropdown_content">
                        ' . apply_filters( 'editor_addon_dropdown_top_message_' . $this->name,
                                        '' ) . '
                        <p class="direct-links-desc">'. __('Go to','wpv-views') .': </p>
                        <ul class="direct-links">' . $direct_links . '</ul>
                        ' . $searchbar . '
                        ' . $menus_output . '
                        ' . apply_filters( 'editor_addon_dropdown_bottom_message' . $this->name,
                                        '' ) .
                        '
                </div>
            </div>';

            // WP 3.3 changes
            if ( version_compare( $wp_version, '3.1.4', '>' ) ) {
                echo apply_filters( 'wpv_add_media_buttons', $out );
            } else {
                return apply_filters( 'wpv_add_media_buttons', $context . $out );
            }
        }

        /**
         * Adding a "V" button to the menu (for user fields)
         *
         * @global object $wpdb
         *
         * @param string $context
         * @param string $text_area
         * @param boolean $standard_v is this a standard V button
         */
        function add_users_form_button( $context, $text_area = 'textarea#content', $codemirror_button = false ) {
            global $wp_version, $sitepress, $wpdb, $WP_Views;
            $standard_v = true;
            // WP 3.3 changes ($context arg is actually a editor ID now)
            if ( version_compare( $wp_version, '3.1.4', '>' ) && !empty( $context ) ) {
                $text_area = $context;
            }
            //print_r($this->items);exit;
            $this->items = array();

            $unused_field = array('comment_shortcuts','managenav-menuscolumnshidden','dismissed_wp_pointers','meta-box-order_dashboard','nav_menu_recently_edited',
            'primary_blog','rich_editing','source_domain','use_ssl','user_level','user-settings-time'
            ,'user-settings','dashboard_quick_press_last_post_id','capabilities','new_date','show_admin_bar_front','show_welcome_panel','show_highlight','admin_color'
            ,'language_pairs','first_name','last_name','name','nickname','description','yim','jabber','aim');
            $exclude_these_hidden_var = '/('.implode('|', $unused_field).')/';
            $this->items = array(
				array(__('User ID', 'wpv-views'), 'wpv-user field="ID"',__('Basic', 'wpv-views'),''),
                array(__('User Email', 'wpv-views'), 'wpv-user field="user_email"',__('Basic', 'wpv-views'),''),
                array(__('User Login', 'wpv-views'), 'wpv-user field="user_login"',__('Basic', 'wpv-views'),''),
                array(__('First Name', 'wpv-views'), 'wpv-user field="user_firstname"',__('Basic', 'wpv-views'),''),
                array(__('Last Name', 'wpv-views'), 'wpv-user field="user_lastname"',__('Basic', 'wpv-views'),''),
                array(__('Nickname', 'wpv-views'), 'wpv-user field="nickname"',__('Basic', 'wpv-views'),''),
                array(__('Display Name', 'wpv-views'), 'wpv-user field="display_name"',__('Basic', 'wpv-views'),''),
                array(__('Description', 'wpv-views'), 'wpv-user field="description"',__('Basic', 'wpv-views'),''),
                array(__('Yahoo IM', 'wpv-views'), 'wpv-user field="yim"',__('Basic', 'wpv-views'),''),
                array(__('Jabber', 'wpv-views'), 'wpv-user field="jabber"',__('Basic', 'wpv-views'),''),
                array(__('AIM', 'wpv-views'), 'wpv-user field="aim"',__('Basic', 'wpv-views'),''),
                array(__('User Url', 'wpv-views'), 'wpv-user field="user_url"',__('Basic', 'wpv-views'),''),
                array(__('Registration Date', 'wpv-views'), 'wpv-user field="user_registered"',__('Basic', 'wpv-views'),''),
                array(__('User Status', 'wpv-views'), 'wpv-user field="user_status"',__('Basic', 'wpv-views'),''),
                array(__('User Spam Status', 'wpv-views'), 'wpv-user field="spam"',__('Basic', 'wpv-views'),'')
                );

            if ( isset( $sitepress ) && function_exists( 'wpml_string_shortcode' ) ) {
				$nonce = wp_create_nonce('wpv_editor_callback');
				$this->items[] = array(__('Translatable string', 'wpv-views'), 'wpml-string',__('Basic', 'wpv-views'),'WPViews.shortcodes_gui.wpv_insert_translatable_string_popup(\'' . $nonce . '\')');
			}



            $meta_keys = get_user_meta_keys();
            $all_types_fields = get_option( 'wpcf-fields', array() );
            foreach ($meta_keys as $key) {
                $key_nicename = '';
                if ( function_exists('wpcf_init') ){
                    if (stripos($key, 'wpcf-') === 0) {
                        //
                    }
                    else {
                        if ( preg_match($exclude_these_hidden_var , $key) ){
                            continue;
                        }
                        $this->items[] = array($key,
                            'wpv-user field="'.$key.'"',
                            __('Users fields', 'wpv-views'),'');
                    }
                }
                else{
                    if ( preg_match($exclude_these_hidden_var , $key) ){
                            continue;
                    }
                    $this->items[] = array($key,
                          'wpv-user field="'.$key.'"',
                          __('User fields', 'wpv-views'),'');
                }

            }

            if ( function_exists('wpcf_init') ){
                //Get types groups and fields
                $groups = wpcf_admin_fields_get_groups( 'wp-types-user-group' );
                $user_id = wpcf_usermeta_get_user();
                $add = array();
                if ( !empty( $groups ) ) {
                    foreach ( $groups as $group_id => $group ) {
                        if ( empty( $group['is_active'] ) ) {
                            continue;
                        }
                        $fields = wpcf_admin_fields_get_fields_by_group( $group['id'],
                                'slug', true, false, true, 'wp-types-user-group',
                                'wpcf-usermeta' );

                        if ( !empty( $fields ) ) {
                            foreach ( $fields as $field_id => $field ) {
                                $add[] = $field['meta_key'];
                                $callback = 'wpcfFieldsEditorCallback(\'' . $field['id'] . '\', \'views-usermeta\', -1)';
                                $this->items[] = array($field['name'],
                                  'types usermeta="'.$field['meta_key'].'"][/types',
                                  $group['name'],$callback);


                            }
                        }
                    }
                }

                //Get unused types fields
                $cf_types = wpcf_admin_fields_get_fields( true, true, false, 'wpcf-usermeta' );
                foreach ( $cf_types as $cf_id => $cf ) {
                     if ( !in_array( $cf['meta_key'], $add) ){
                         $callback = 'wpcfFieldsEditorCallback(\'' . $cf['id'] . '\', \'views-usermeta\', -1)';
                                $this->items[] = array($cf['name'],
                                  'types usermeta="'.$cf['meta_key'].'"][/types',
                                  __('Types fields', 'wpv-views'),$callback);

                     }
                }
             }

		$view_available = $wpdb->get_results("SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type='view' AND post_status in ('publish')");
		foreach($view_available as $view) {

			$view_settings = get_post_meta($view->ID, '_wpv_settings', true);
			if (isset($view_settings['query_type'][0]) && $view_settings['query_type'][0] == 'posts' && !$WP_Views->is_archive_view($view->ID)) {

				$this->items[] = array($view->post_title,
					$view->post_title,
					__('Post View', 'wpv-views'),
					''
				);
			}
		}

            $out = array();

            $menus = array();
            $sub_menus = array();

            if( $this->items )
            foreach ( $this->items as $item ) {
                $parts = explode( '-!-', $item[2] );
                $menu_level = &$menus;
                foreach ( $parts as $part ) {
                    if ( $part != '' ) {
                        if ( !array_key_exists( $part, $menu_level ) ) {
                            $menu_level[$part] = array();
                        }
                        $menu_level = &$menu_level[$part];
                    }
                }
                $menu_level[$item[0]] = $item;
            }




            // Sort menus
            if ( is_array( $menus ) ) {
                $menus = $this->sort_menus_alphabetically( $menus );
            }


            $this->_media_menu_direct_links = array();
            $menus_output = $this->_output_media_menu( $menus, $text_area,
                    $standard_v );

            $direct_links = implode( ' ', $this->_media_menu_direct_links );
            $dropdown_class = 'js-editor_addon_dropdown-'.$this->name;
            $icon_class = 'js-wpv-shortcode-post-icon-'.$this->name;
            if ( $this->name == 'wpv-views' ) {
				$button_label = __( 'Fields and Views', 'wpv-views' );
			} else if ( $this->name == 'types' ) {
				$button_label = __( 'Types', 'wpv-views' );
			} else {
				$button_label = '';
			}
            $addon_button = '<span class="button wpv-shortcode-post-icon '. $icon_class .'"><img src="' . $this->media_button_image . '" />' . $button_label . '</span>';
            if ( !$standard_v ) {
                $addon_button = '<span class="button vicon wpv-shortcode-post-icon '. $icon_class .'"><img src="' . $this->media_button_image . '" />' . $button_label . '</span>';
                // $addon_button = '<input id="addingbutton" alt="#TB_inline?inlineId=add_field_popup" class="thickbox wpv_add_fields_button button-primary field_adder" type="button" value="'. __('Add field', 'wpv-views') .'" name="" />';
                //$addon_button = '<span class="wpv_add_fields_button button-primary field_adder">'. __('Add field', 'wpv-views') .'</span>';
            }
            // Codemirrir (new layout) button
            if ( $codemirror_button ){
                 $addon_button = '<button class="js-code-editor-toolbar-button js-code-editor-toolbar-button-v-icon button-secondary">'.
                        '<i class="icon-views-logo ont-icon-18"></i><span class="button-label">'. __('Fields and Views', 'wpv-views') .'</span></button>';
            }
            // add search box
            $searchbar = $this->get_search_bar();

            // generate output content
            $out = '' .
            $addon_button . '
            <div class="editor_addon_dropdown '. $dropdown_class .'" id="editor_addon_dropdown_' . rand() . '">
                <h3 class="title">' . $this->button_text . '</h3>
                <div class="close">&nbsp;</div>
                <div class="editor_addon_dropdown_content">
                        ' . apply_filters( 'editor_addon_dropdown_top_message_' . $this->name,
                                        '' ) . '
                        <p class="direct-links-desc">'. __('Go to','wpv-views') .': </p>
                        <ul class="direct-links">' . $direct_links . '</ul>
                        ' . $searchbar . '
                        ' . $menus_output . '
                        ' . apply_filters( 'editor_addon_dropdown_bottom_message' . $this->name,
                                        '' ) .
                        '
                </div>
            </div>';
            // WP 3.3 changes
            if ( version_compare( $wp_version, '3.1.4', '>' ) ) {
                echo apply_filters( 'wpv_add_media_buttons', $out );
            } else {
                return apply_filters( 'wpv_add_media_buttons', $context . $out );
            }

        }

        /**
         * Output a single menu item
         * @param string $menu
         * @param string $text_area
         * @param boolean $standard_v
         * @return string media menu
         */
        function _output_media_menu( $menu, $text_area, $standard_v ) {
            $all_post_types = implode( ' ',
                    get_post_types( array('public' => true) ) );

            $out = '';

            if ( is_array( $menu ) ) {
                foreach ( $menu as $key => $menu_item ) {
                    if ( isset( $menu_item[0] ) && !is_array( $menu_item[0] ) ) {
                        if ( !isset( $menu_item[3] ) ) {
                            break;
                        }
                        if ( $menu_item[3] != '' ) {
                            if ( !($key == 'css') ) { // hide unnecessary elements from the V popup
                                if ( !$standard_v && (strpos( $menu_item[3],
                                                'wpcfFieldsEditorCallback' ) !== false ||
                                        strpos( $menu_item[3],
                                                'wpcfFieldsEmailEditorCallback' ) !== false ||
                                        strpos( $menu_item[3],
                                                'wpv_insert_view_form_popup' ) !== false) ) {
                                    $out .= $this->wpv_parse_menu_item_from_addfield( $menu_item );
                                } else {
                                    $out .= '<li class="item" onclick="' . $menu_item[3] . '; return false;">' . $menu_item[0] . "</li>\n";
                                }
                            }
                        } else {
                            if ( $standard_v ) {
                                $short_code = $menu_item[1];
                                $link_text = $menu_item[0];

                                if ( $menu_item[2] == __( 'Post View',
                                                'wpv-views' ) || $menu_item[2] == __( 'Taxonomy View',
                                                'wpv-views' ) || $menu_item[2] == __( 'User View',
                                                'wpv-views' ) ) {
                                    $short_code = 'wpv-view name="' . $short_code . '"';
                                    $link_text = str_replace( ' - ' . __( 'Post View' ),
                                            '', $link_text );
                                    $link_text = str_replace( ' - ' . __( 'Taxonomy View' ),
                                            '', $link_text );
                                    $link_text = str_replace( ' - ' . __( 'User View' ),
                                            '', $link_text );
                                }
                                $short_code = '[' . $short_code . ']';
                                $short_code = base64_encode( $short_code );

                                $out .= '<li class="item" onclick="insert_b64_shortcode_to_editor(\'' . $short_code . '\', \'' . $text_area . '\'); return false;">' . $link_text . "</li>\n";
                            } else {
                                $out .= $this->wpv_parse_menu_item_from_addfield( $menu_item );
                            }
                        }
                    } else {
            if ( 'wpcf' != $key && 'views' != $key ) {  // for some reason it displays a group wpcf on sites with WPLANG different from ''
                        // a sum menu.
                        /*
                         * SRDJAN
                         * Avoid using all classes.
                         * It will add generic classes that can messup our code.
                         */
                        $css_classes = '';
//                        $css_classes = isset($menu_item['css']) ? $menu_item['css'] : '';
//                        if($key == __('Taxonomy', 'wpv-views') || $key == __('Basic', 'wpv-views')) {
//                          $css_classes = $all_post_types;
//                        }
                        $this->_media_menu_direct_links[] = '<li data-id="' . md5( $key ) .'" class="editor-addon-top-link" data-editor_addon_target="editor-addon-link-' . md5( $key ) . '">' . $key . ' </li>';
                        /*
                         * SRDJAN
                         * Hmmmm, multiple IDs
                         * Changed ID to class
                         */
//                        $out .= '<div class="group '. $css_classes .'"><div class="group-title" id="editor-addon-link-' . md5($key) . '-target">' . $key . "&nbsp;&nbsp;\n</div>\n";
                        $out .= '<div class="group ' . $css_classes . '"><h4 data-id="'.md5( $key ).'" class="group-title  editor-addon-link-' . md5( $key ) . '-target">' . $key . "</h4>";
                        $out .=     '<ul>';
                        $out .=         $this->_output_media_menu( $menu_item, $text_area, $standard_v );
                        $out .=     "</ul>";
                        $out .= "</div>";
                        }
                    }
                }
            }

            return $out;
        }

        /**
         * Parser for menu items in the add-field
         * @param unknown_type $key
         * @param unknown_type $menu_item
         * @return string
         */
        function wpv_parse_menu_item_from_addfield( $menu_item ) {
            $param1 = '';
            $slug = $menu_item[1];

            // search for wpv- starting fields first
            if ( strpos( $slug, 'wpv-' ) !== false ) {
                $menuitem_parts = explode( ' ', $slug );
                $slug = $menuitem_parts[0];
            }
            // find types fields
            else if ( (strpos( $menu_item[3], 'wpcfFieldsEditorCallback' ) !== false)
                    || (strpos( $menu_item[3], 'wpcfFieldsEmailEditorCallback' ) !== false)
                    || (strpos( $menu_item[3], 'wpv_insert_view_form_popup' ) !== false) ) {
                return '<li class="item" onclick="on_add_field_wpv_types_callback(\'' . esc_js( $menu_item[3] ) . '\', \'' . esc_js( $menu_item[0] ) . '\'); return false;">' . $menu_item[0] . "</li>\n";
            } else if ( (preg_match( '/types field="(.+)"/', $slug, $matches ) > 0)
                    || (preg_match( '/type="(.+)"/', $slug, $matches ) > 0) ) {
                $types_slug = $matches[1];
                $types_slug = str_replace( '" class="" style="', '', $types_slug );
                // convert Types fields to Views fields
                $slug = $types_slug;
                $param1 = 'Types-!-wpcf';
            } else if ( preg_match( '/type="(.+)"/', $slug, $matches ) > 0 ) {
                $types_slug = $matches[1];
                $types_slug = str_replace( '" class="" style="', '', $types_slug );
                // convert field to Views field
                $slug = $types_slug;
                $param1 = 'Types-!-wpcf';

                // apply_filters() for Types shortcodes
            }
            // for Basic group fields
            if ( $menu_item[2] == __( 'Basic', 'wpv-views' ) ) {
                // don't use slug here, just field name.
                $slug = $menu_item[0];
            }
            // View Templates here
            if ( $menu_item[2] == __( 'View templates', 'wpv-views' ) ) {
                $param1 = 'View template';
            }
            if ( $menu_item[2] == __( 'Post View', 'wpv-views' ) || $menu_item[2] == __( 'Taxonomy View',
                            'wpv-views' ) || $menu_item[2] == __( 'User View', 'wpv-views' ) ) {
                $param1 = 'Child View';
            }
            if ( strpos( $slug, 'wpv-post-field' ) !== false ) {
                $param1 = __('Field', 'wpv-views');
                $slug = $menu_item[0];
            }
            // Taxonomies
            if ( strpos( $menu_item[1], 'wpv-post-taxonomy' ) !== false ) {
                $slug = $menu_item[1];
                $param1 = 'Taxonomy';
                if ( preg_match( '/wpv-post-taxonomy type="([^"]*)"/', $slug,
                                $matches ) > 0 ) {
                    $slug = 'wpvtax-' . $matches[1]; // split up and pass text only
                } else {
                    $slug = esc_html( $menu_item[1] );
                    $slug = str_replace( 'wpv-post-taxonomy', 'wpv-taxonomy',
                            $slug );
                }
                /* $slug = esc_html($menu_item[1]);
                  $slug = str_replace('wpv-post-taxonomy', 'wpv-taxonomy', $slug); */
            }

            $link_text = $menu_item[0];
            if ( $param1 == 'Child View' ) {
                $link_text = str_replace( ' - ' . __( 'Post View' ), '',
                        $link_text );
                $link_text = str_replace( ' - ' . __( 'Taxonomy View' ), '',
                        $link_text );
                $link_text = str_replace( ' - ' . __( 'User View' ), '',
                        $link_text );
            }
            return '<li class="item" onclick="on_add_field_wpv(\'' . $param1 . '\', \'' . esc_js( $slug ) . '\', \'' . base64_encode( $menu_item[0] ) . '\')">' . $link_text . "</li>\n";
        }

        // add parent items for Views and View Templates
        function wpv_add_parent_items( $items ) {
            global $post, $pagenow;

            if ( $pagenow == 'post-new.php' && isset( $_GET['post_type'] ) && $_GET['post_type'] == 'view-template' ) {
                $this->add_view_template_parent_groups( $items );
            }
            if ( $pagenow == 'post-new.php' && isset( $_GET['post_type'] ) && $_GET['post_type'] == 'view' ) {

            } else if ( $pagenow == 'post.php' && isset( $_GET['action'] ) && $_GET['action'] == 'edit' ) {
                $post_type = $post->post_type;

                if ( $post_type == 'view' ) {
                    $items = $this->add_view_parent_groups( $items );
                } else if ( $post_type == 'view-template' ) {
                    $items = $this->add_view_template_parent_groups( $items );
                }
            }

            return $items;
        }

        function add_view_parent_groups( $items ) {

        }

        // add parent groups for vew templates
        function add_view_template_parent_groups( $items ) {
            global $post, $WPV_settings;
            // get current View ID
            $view_template_id = $post->ID;

            // get all view templates attached in the Settings page for single view
            $view_template_relations = $WPV_settings->get_view_template_settings();

            // find view template groups and get their parents
            $current_types = array();
            $parent_types = array();
            foreach ( $view_template_relations as $relation => $value ) {
                if ( $value == $view_template_id ) {
                    $current_types[] = $relation;
                    if ( function_exists( 'wpcf_pr_get_belongs' ) ) {
                        $parent_types[] = wpcf_pr_get_belongs( $relation );
                    }
                }
            }

            // get parent groups
            $all_parent_groups = array();
            foreach ( $parent_types as $type ) {
                foreach ( $type as $typename => $typeval ) {
                    $parent_groups = wpcf_admin_get_groups_by_post_type( $typename );
                }
            }


        }

        /**
         *
         * Sort menus (and menu content) in an alphabetical order
         *
         * Still, keep Basic and Taxonomy on the top and Other Fields at the bottom
         *
         * @param array $menu menu reference
         */
        function sort_menus_alphabetically( $menus ) {
            // keep main references if set (not set on every screen)
            $menu_temp = array();
            $menu_names = array(
                __( 'Taxonomy View', 'wpv-views' ),
                __( 'User View', 'wpv-views' ),
                __( 'Post View', 'wpv-views' ),
                __( 'View', 'wpv-views' ),
                __( 'View templates', 'wpv-views' ),
                __( 'Taxonomy', 'wpv-views' ),
                __( 'Basic', 'wpv-views' ),
                __( 'Other Fields', 'wpv-views' )
            );

            foreach ( $menu_names as $name ) {
                $menu_temp[$name] = isset( $menus[$name] ) ? $menus[$name] : array();
            }

            // remove them to preserve correct listing
            foreach ( $menu_names as $name ) {
                unset( $menus[$name] );
            }

            // sort all elements by key
            ksort( $menus );

            // add main elements in the correct order
            foreach ( $menu_names as $name ) {
                $menus = !empty( $menu_temp[$name] ) ? array_merge( array($name => $menu_temp[$name]),
                                $menus ) : $menus;
            }

            // sort inner elements in the submenus
            foreach ( $menus as $key => $menu_group ) {
                if ( is_array( $menu_group ) ) {
                    ksort( $menu_group );
                }
            }

            return $menus;
        }

        function get_search_bar() {
            $searchbar  = '<p class="searchbar">';
            $searchbar .=   '<label for="searchbar-input">' . __( 'Search', 'wpv-views' ) . ': </label>';
            $searchbar .=   '<input id="searchbar-input" type="text" class="search_field" onkeyup="wpv_on_search_filter(this)" />';
            $searchbar .=   '<input type="button" class="button-secondary search_clear" value="' . __( 'Clear','wpv-views' ) . '" onclick="wpv_search_clear(this)" style="display: none;" />';
            $searchbar .= '</p>';
            return $searchbar;
        }

        /**
         *
         * @global object $wpdb
         *
         */
        function add_view_type( &$menus, $post_type, $post_name ) {
            global $wpdb;
            $all_post_types = implode( ' ',
                    get_post_types( array('public' => true) ) );

            $view_templates_available = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT ID, post_title, post_name FROM {$wpdb->posts} 
					WHERE post_type = %s 
					AND post_status in (%s)",
                    $post_type,
                    'publish'
                )
            );
            $menus[$post_name] = array();
            $menus[$post_name]['css'] = $all_post_types;

            $vtemplate_index = 0;
            foreach ( $view_templates_available as $vtemplate ) {

                $title = $vtemplate->post_title;

                if ( $post_type == 'view' ) {
                    $view_settings = get_post_meta( $vtemplate->ID,
                            '_wpv_settings', true );
                    $title = $vtemplate->post_title . ' - ' . __( 'Post View',
                                    'wpv-views' );
                    if ( isset( $view_settings['query_type'] ) && isset( $view_settings['query_type'][0] ) && $view_settings['query_type'][0] == 'taxonomy' ) {
                        $title = $vtemplate->post_title . ' - ' . __( 'Taxonomy View',
                                        'wpv-views' );
                        if ( $post_name == __( 'Post View', 'wpv-views' ) || $post_name == __( 'User View', 'wpv-views' ) ) {
                            continue;
                        }
                    } elseif ( isset( $view_settings['query_type'] ) && isset( $view_settings['query_type'][0] ) && $view_settings['query_type'][0] == 'users' ) {
                        $title = $vtemplate->post_title . ' - ' . __( 'User View',
                                        'wpv-views' );
                        if ( $post_name == __( 'Post View', 'wpv-views' ) || $post_name == __( 'Taxonomy View', 'wpv-views' ) ) {
                            continue;
                        }
                    } else {
                        if ( $post_name == __( 'Taxonomy View', 'wpv-views' ) || $post_name == __( 'User View', 'wpv-views' ) ) {
                            continue;
                        }
                    }
                    if ( isset( $view_settings['view-query-mode'] ) && $view_settings['view-query-mode'] =='archive' ){
                        continue;
                    }
                }

                $menus[$post_name][$vtemplate_index] = array();
                $menus[$post_name][$vtemplate_index][] = $title;
                $menus[$post_name][$vtemplate_index][] = $vtemplate->post_name;
                $menus[$post_name][$vtemplate_index][] = $post_name;
                $menus[$post_name][$vtemplate_index][] = '';
                $vtemplate_index++;
            }
        }
    }

/*
      Add the wpv_views button to the toolbar.
     */
    function wpv_mce_add_button( $buttons )
    {
        array_push( $buttons, "separator", str_replace( '-', '_', $this->name ) );
        return $buttons;
    }

    /*

      Register this plugin as a mce 'addon'
      Tell the mce editor the url of the javascript file.
     */
    if( !function_exists('wpv_mce_register') )
    {
        function wpv_mce_register( $plugin_array )
        {
            $plugin_array[str_replace( '-', '_', $this->name )] = $this->plugin_js_url;
            return $plugin_array;
        }
    }

    /**
     * Renders JS for inserting shortcode from thickbox popup to editor.
     *
     * @param type $shortcode
     */
    if( !function_exists('editor_admin_popup_insert_shortcode_js') )
    {
        function editor_admin_popup_insert_shortcode_js( $shortcode ) { // Types now uses ColorBox, it's not used in Views anymore. Maybe DEPRECATED

            ?>
            <script type="text/javascript">
                //<![CDATA[

                // Close popup
                window.parent.jQuery('#TB_closeWindowButton').trigger('click');

                // Check if there is custom handler
                if (window.parent.wpcfFieldsEditorCallback_redirect) {
                    eval(window.parent.wpcfFieldsEditorCallback_redirect['function'] + '(\'<?php echo esc_js( $shortcode ); ?>\', window.parent.wpcfFieldsEditorCallback_redirect[\'params\'])');
                } else {
                    // Use default handler
                    window.parent.icl_editor.insert('<?php echo $shortcode; ?>');
                }

                //]]>
            </script>
            <?php
        }
    }

}

