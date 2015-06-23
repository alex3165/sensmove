<?php

if ( !class_exists('Types_Data_Installer') ) {

    class Types_Data_Installer
    {
        private $data = array();
        private $args = array();
        private $fields = array();
        private $user_fields = array();
        private $reset_toolset_edit_last_list = array();

        public function __construct($data, $args)
        {
            $this->data = $data;
            $this->args = $args;
        }

        /**
         * Create data for import.
         *
         * Populate and check data for import from Importer
         *
         * @since 1.6.6
         *
         * @return object data for import
         */
        public function wpvdemo()
        {
            $data = new stdClass;
            foreach(array('groups', 'user_groups', 'fields', 'user_fields', 'taxonomies', 'types') as $key) {
                $data->$key = new stdClass;
            }

            $data->groups->group = $this->get_data_by_group_name('wp-types-group');
            $data->user_groups->group = $this->get_data_by_group_name('wp-types-user-group');

            $data->fields->field = $this->get_data_by_field_type('fields');
            $data->user_fields->field = $this->get_data_by_field_type('user_fields');

            $data->types->type = $this->get_data_by_type('wpcf-custom-types');
            $data->taxonomies->taxonomy = $this->get_data_by_type('wpcf-custom-taxonomies');

            return $data;
        }

        /**
         * Helper for group.
         *
         * Helper for importing group.
         *
         * @since 1.6.6
         * @access private
         *
         * @param string $group_name Group name
         *
         * @return object data for import
         */
        private function get_data_by_group_name($group_name)
        {
            $group = 'groups';
            $element = 'group';
            if ( 'wp-types-user-group' == $group_name ) {
                $group = 'user_groups';
            }

            if (
                isset($this->data->$group)
                && isset($this->data->$group->$element)
            ) {
                return $this->get_data($group_name, $this->data->$group->$element);
            }
            return;
        }

        /**
         * Build data for group.
         *
         * Build data to importing group.
         *
         * @since 1.6.6
         * @access private
         *
         * @param string $group_name Group name
         * @param object $group Group data
         *
         * @return object data for import
         */
        private function get_data($group_name, $groups)
        {
            $this->reset_toolset_edit_last_list[$group_name] = array();
            $date = date('Y-m-d H:i');
            $new = array();
            foreach($groups as $group ) {
                if (
                    isset($this->args['force_skip_post_name'][$group_name])
                    && in_array($group->__types_id, $this->args['force_skip_post_name'][$group_name])
                ) {
                    continue;
                }
                if (
                    isset($this->args['force_import_post_name'][$group_name])
                    && in_array($group->__types_id, $this->args['force_import_post_name'][$group_name])
                ) {
                    $new[] = $group;
                    switch($group_name) {
                    case 'wp-types-group':
                        $this->fields += explode(',', $group->meta->_wp_types_group_fields);
                        break;
                    case 'wp-types-user-group':
                        $this->user_fields += explode(',', $group->meta->_wp_types_group_fields);
                        break;
                    }
                    $this->reset_toolset_edit_last_list[$group_name][] = $group->__types_id;
                    continue;
                }
                if (
                    isset($this->args['force_duplicate_post_name'][$group_name])
                    && in_array($group->__types_id, $this->args['force_duplicate_post_name'][$group_name])
                ) {
                    $one = $group;
                    $one->__types_id = wp_unique_post_slug( sanitize_title_with_dashes($group->post_title, null, 'save'), null, 'publish', 'wp-types-group', null);
                    $one->__types_title = $one->post_title = sprintf('%s %s', $group->post_title, $date);
                    $new[$one->__types_id] = $one;
                    continue;
                }
            }
            return $new;
        }

        /**
         * Helper for CPT & CT.
         *
         * Helper for importing custom post types and custom taxonomies.
         *
         * @since 1.6.6
         * @access private
         *
         * @param string $group_name Group name
         *
         * @return object data for import
         */
        private function get_data_by_field_type($type)
        {
            if (
                isset($this->data->fields)
                && isset($this->data->fields->field)
            ) {
                $fields = $this->data->fields->field;
            } else {
                return;
            }
            $new = array();
            foreach($fields as $field ) {
                if ( in_array($field->id, $this->$type) )
                {
                    $new[] = $field;
                }
            }
            return $new;
        }

        /**
         * Build data for CPT & CT
         *
         * Build data to importing custom post types and custom taxonomies.
         *
         * @since 1.6.6
         * @access private
         *
         * @param string $group_name Group name
         * @param object $group Group data
         *
         * @return object data for import
         */
        private function get_data_by_type($group_name)
        {
            $group = 'types';
            $element = 'type';
            if ( 'wpcf-custom-taxonomies' == $group_name ) {
                $group = 'taxonomies';
                $element = 'taxonomy';
            }
            if (
                isset($this->data->$group)
                && isset($this->data->$group->$element)
            ) {
                $groups = $this->data->$group->$element;
            } else {
                return;
            }

            $this->reset_toolset_edit_last_list[$group_name] = array();

            $date = date('Y-m-d H:i');
            $new = array();
            $data = get_option($group_name, array());

            foreach ( $groups as $group) {
                $slug = $group->slug->__toString();
                if ( isset($data[$slug]) && !isset($data[$slug][TOOLSET_EDIT_LAST]) ) {
                    continue;
                }
                $this->reset_toolset_edit_last_list[$group_name][] = $slug;
                if (
                    isset($this->args['force_skip_post_name'][$group_name])
                    && in_array($group->__types_id, $this->args['force_skip_post_name'][$group_name])
                ) {
                    continue;
                }
                if (
                    isset($this->args['force_import_post_name'][$group_name])
                    && in_array($group->__types_id, $this->args['force_import_post_name'][$group_name])
                ) {
                    $this->reset_toolset_edit_last_list[$group_name][] = (int)$group->ID;
                    $group->addChild('add', false);
                    $group->addChild('update', true);
                    $new[] = $group;
                    continue;
                }
            }

            return $new;
        }

        /**
         * Reset TOOLSET_EDIT_LAST.
         *
         * Function to reset TOOLSET_EDIT_LAST - last edit timestamp in Types 
         * definitions.
         *
         * @since 1.6.6
         *
         */
        public function reset_toolset_edit_last()
        {
            if (!empty($this->reset_toolset_edit_last_list)) {
                foreach( $this->reset_toolset_edit_last_list as $group => $data) {
                    switch( $group ) {
                    case 'wp-types-group':
                    case 'wp-types-user-group':
                        foreach( $data as $slug) {
                            $post = get_page_by_path($slug, OBJECT, $group);
                            if ( $post ) {
                                delete_post_meta( $post->ID, TOOLSET_EDIT_LAST);
                            }
                        }
                        break;
                    case 'wpcf-custom-types':
                    case 'wpcf-custom-taxonomies':
                        $options = get_option( $group, array() );
                        $new = array();
                        foreach ( $options as $one ) {
                            if ( in_array($one['slug'], $data ) ) {
                                if ( isset( $one[TOOLSET_EDIT_LAST]) ) {
                                    unset($one[TOOLSET_EDIT_LAST]);
                                }
                            }
                            $new[$one['slug']] = $one;
                        }
                        update_option($group, $new);
                        break;
                    }
                }
            }
        }
    }
}
