<?php
/*
 * Fields and groups form functions.
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/includes/usermeta-form.php $
 * $LastChangedDate: 2015-03-25 12:38:40 +0000 (Wed, 25 Mar 2015) $
 * $LastChangedRevision: 1120400 $
 * $LastChangedBy: iworks $
 *
 */
require_once WPCF_EMBEDDED_ABSPATH . '/classes/validate.php';
require_once WPCF_ABSPATH . '/includes/conditional-display.php';

global $wp_version;
$wpcf_button_style = '';
$wpcf_button_style30 = '';

if (version_compare($wp_version, '3.5', '<')) {
    $wpcf_button_style = 'style="line-height: 35px;"';
    $wpcf_button_style30 = 'style="line-height: 30px;"';
}


/**
 * Saves user fields and groups.
 *
 * If field name is changed in specific group - new one will be created,
 * otherwise old one will be updated and will appear in that way in other grups.
 *
 * @return type
 */
function wpcf_admin_save_usermeta_groups_submit($form) {
    if (
           !isset($_POST['wpcf'])
        || !isset($_POST['wpcf']['group'])
        || !isset($_POST['wpcf']['group']['name'])
    ) {
        return false;
    }

    $_POST['wpcf']['group']['name'] = trim(strip_tags($_POST['wpcf']['group']['name']));

    $_POST['wpcf']['group'] = apply_filters('wpcf_group_pre_save', $_POST['wpcf']['group']);

    if ( empty($_POST['wpcf']['group']['name']) ) {
        $form->triggerError();
        wpcf_admin_message( __( 'Group name can not be empty.', 'wpcf' ), 'error');
        return $form;
    }

    $new_group = false;

    $group_slug = $_POST['wpcf']['group']['slug'] = sanitize_title($_POST['wpcf']['group']['name']);

    // Basic check


    if (isset($_REQUEST['group_id'])) {
        // Check if group exists
        $post = get_post(intval($_REQUEST['group_id']));
        // Name changed
        if (strtolower($_POST['wpcf']['group']['name']) != strtolower($post->post_title)) {
            // Check if already exists
            $exists = get_page_by_title($_POST['wpcf']['group']['name'], 'OBJECT', 'wp-types-user-group');
            if (!empty($exists)) {
                $form->triggerError();
                wpcf_admin_message(
                    sprintf(
                        __("A group by name <em>%s</em> already exists. Please use a different name and save again.", 'wpcf'),
                        htmlspecialchars($_POST['wpcf']['group']['name'])
                    ),
                    'error'
                );
                return $form;
            }
        }
        if (empty($post) || $post->post_type != 'wp-types-user-group') {
            $form->triggerError();
            wpcf_admin_message(sprintf(__("Wrong group ID %d", 'wpcf'), intval($_REQUEST['group_id'])), 'error');
            return $form;
        }
        $group_id = $post->ID;

    } else {
        $new_group = true;
        // Check if already exists
        $exists = get_page_by_title($_POST['wpcf']['group']['name'], 'OBJECT',
                'wp-types-user-group');
        if (!empty($exists)) {
            $form->triggerError();
            wpcf_admin_message(
                sprintf(
                    __("A group by name <em>%s</em> already exists. Please use a different name and save again.", 'wpcf'),
                    htmlspecialchars($_POST['wpcf']['group']['name'])
                ),
                'error'
            );
            return $form;
        }
    }

    // Save fields for future use
    $fields = array();
    if (!empty($_POST['wpcf']['fields'])) {
        // Before anything - search unallowed characters
        foreach ($_POST['wpcf']['fields'] as $key => $field) {
            if ((empty($field['slug']) && preg_match('#[^a-zA-Z0-9\s\_\-]#',
                            $field['name']))
                    || (!empty($field['slug']) && preg_match('#[^a-zA-Z0-9\s\_\-]#',
                            $field['slug']))) {
                $form->triggerError();
                wpcf_admin_message(sprintf(__('Field slugs cannot contain non-English characters. Please edit this field name %s and save again.',
                                        'wpcf'), $field['name']), 'error');
                return $form;
            }
        }

        foreach ($_POST['wpcf']['fields'] as $key => $field) {
            $field = apply_filters('wpcf_field_pre_save', $field);
            if (!empty($field['is_new'])) {
                // Check name and slug
                if (wpcf_types_cf_under_control('check_exists',
                                sanitize_title($field['name']), 'wp-types-user-group', 'wpcf-usermeta')) {
                    $form->triggerError();
                    wpcf_admin_message(sprintf(__('Field with name "%s" already exists',
                                            'wpcf'), $field['name']), 'error');
                    return $form;
                }
                if (isset($field['slug']) && wpcf_types_cf_under_control('check_exists',
                                sanitize_title($field['slug']), 'wp-types-user-group', 'wpcf-usermeta')) {
                    $form->triggerError();
                    wpcf_admin_message(sprintf(__('Field with slug "%s" already exists',
                                            'wpcf'), $field['slug']), 'error');
                    return $form;
                }
            }
            // Field ID and slug are same thing
            $field_id = wpcf_admin_fields_save_field( $field, 'wp-types-user-group', 'wpcf-usermeta' );
            if (!empty($field_id)) {
                $fields[] = $field_id;
            }

        }
    }

    // Save group
    $roles = isset($_POST['wpcf']['group']['supports']) ? $_POST['wpcf']['group']['supports'] : array();
    $admin_style = $_POST['wpcf']['group']['admin_styles'];
    // Rename if needed
    if (isset($_REQUEST['group_id'])) {
        $_POST['wpcf']['group']['id'] = intval($_REQUEST['group_id']);
    }

    $group_id = wpcf_admin_fields_save_group($_POST['wpcf']['group'], 'wp-types-user-group');
    $_POST['wpcf']['group']['id'] = $group_id;

    // Set open fieldsets
    if ($new_group && !empty($group_id)) {
        $open_fieldsets = get_user_meta(get_current_user_id(),
                'wpcf-group-form-toggle', true);
        if (isset($open_fieldsets[-1])) {
            $open_fieldsets[$group_id] = $open_fieldsets[-1];
            unset($open_fieldsets[-1]);
            update_user_meta(get_current_user_id(), 'wpcf-group-form-toggle',
                    $open_fieldsets);
        }
    }


    // Rest of processes
    if (!empty($group_id)) {
        wpcf_admin_fields_save_group_fields($group_id, $fields, false, 'wp-types-user-group');
        wpcf_admin_fields_save_group_showfor($group_id, $roles);
        wpcf_admin_fields_save_group_admin_styles($group_id, $admin_style);

        $_POST['wpcf']['group']['fields'] = isset($_POST['wpcf']['fields']) ? $_POST['wpcf']['fields'] : array();
        do_action('wpcf_save_group', $_POST['wpcf']['group']);
        wpcf_admin_message_store(apply_filters('types_message_usermeta_saved',
                    __('Group saved', 'wpcf'), $_POST['wpcf']['group']['name'], $new_group ? false : true),
            'custom');
        wp_redirect(admin_url('admin.php?page=wpcf-edit-usermeta&group_id=' . $group_id));
        die();
    } else {
        wpcf_admin_message_store(__('Error saving group', 'wpcf'), 'error');
    }


}


/**
 * Generates form data.
 */
function wpcf_admin_usermeta_form() {
    global $wpcf;
    wpcf_admin_add_js_settings('wpcf_nonce_toggle_group',
            '\'' . wp_create_nonce('group_form_collapsed') . '\'');
    wpcf_admin_add_js_settings('wpcf_nonce_toggle_fieldset',
            '\'' . wp_create_nonce('form_fieldset_toggle') . '\'');
    $default = array();

    global $wpcf_button_style;
    global $wpcf_button_style30;

    // If it's update, get data
    $update = false;
    if (isset($_REQUEST['group_id'])) {
        $update = wpcf_admin_fields_get_group(intval($_REQUEST['group_id']), 'wp-types-user-group');
        if (empty($update)) {
            $update = false;
            wpcf_admin_message(sprintf(__("Group with ID %d do not exist", 'wpcf'), intval($_REQUEST['group_id'])));
        } else {
            $update['fields'] = wpcf_admin_fields_get_fields_by_group( sanitize_text_field( $_REQUEST['group_id'] ), 'slug', false, true, false, 'wp-types-user-group', 'wpcf-usermeta');
            $update['show_for'] = wpcf_admin_get_groups_showfor_by_group( sanitize_text_field( $_REQUEST['group_id'] ) );
            $update['admin_styles'] = wpcf_admin_get_groups_admin_styles_by_group( sanitize_text_field( $_REQUEST['group_id'] ) );
        }
    }

    $form = array();
    $form['#form']['callback'] = array('wpcf_admin_save_usermeta_groups_submit');

    // Form sidebars

    $form['open-sidebar'] = array(
        '#type' => 'markup',
        '#markup' => '<div class="wpcf-form-fields-align-right">',
    );
    // Set help icon
    $form['help-icon'] = array(
        '#type' => 'markup',
        '#markup' => '<div class="wpcf-admin-fields-help"><img src="' . WPCF_EMBEDDED_RELPATH
        . '/common/res/images/question.png" style="position:relative;top:2px;" />&nbsp;<a href="http://wp-types.com/documentation/user-guides/using-custom-fields/" target="_blank">'
        . __('Usermeta help', 'wpcf') . '</a></div>',
    );
    $form['submit2'] = array(
        '#type' => 'submit',
        '#name' => 'save',
        '#value' => __('Save', 'wpcf'),
        '#attributes' => array('class' => 'button-primary wpcf-disabled-on-submit'),
    );
    $form['fields'] = array(
        '#type' => 'fieldset',
        '#title' => __('Available fields', 'wpcf'),
    );

    // Get field types
    $fields_registered = wpcf_admin_fields_get_available_types();
    foreach ($fields_registered as $filename => $data) {
        $form['fields'][basename($filename, '.php')] = array(
            '#type' => 'markup',
            '#markup' => '<a href="' . admin_url('admin-ajax.php'
                    . '?action=wpcf_ajax&amp;wpcf_action=fields_insert'
                    . '&amp;field=' . basename($filename, '.php')
                    . '&amp;page=wpcf-edit-usermeta' )
            . '&amp;_wpnonce=' . wp_create_nonce('fields_insert') . '" '
            . 'class="wpcf-fields-add-ajax-link button-secondary">' . $data['title'] . '</a> ',
        );
        // Process JS
        if (!empty($data['group_form_js'])) {
            foreach ($data['group_form_js'] as $handle => $script) {
                if (isset($script['inline'])) {
                    add_action('admin_footer', $script['inline']);
                    continue;
                }
                $deps = !empty($script['deps']) ? $script['deps'] : array();
                $in_footer = !empty($script['in_footer']) ? $script['in_footer'] : false;
                wp_register_script($handle, $script['src'], $deps, WPCF_VERSION,
                        $in_footer);
                wp_enqueue_script($handle);
            }
        }

        // Process CSS
        if (!empty($data['group_form_css'])) {
            foreach ($data['group_form_css'] as $handle => $script) {
                if (isset($script['src'])) {
                    $deps = !empty($script['deps']) ? $script['deps'] : array();
                    wp_enqueue_style($handle, $script['src'], $deps,
                            WPCF_VERSION);
                } else if (isset($script['inline'])) {
                    add_action('admin_head', $script['inline']);
                }
            }
        }
    }


    // Get fields created by user
    $fields = wpcf_admin_fields_get_fields( true, true, false, 'wpcf-usermeta' );
    if ( !empty( $fields ) ) {
        $form['fields-existing'] = array(
            '#type' => 'fieldset',
            '#title' => __( 'User created fields', 'wpcf' ),
            '#id' => 'wpcf-form-groups-user-fields',
        );
        foreach ( $fields as $key => $field ) {
            if ( isset( $update['fields'] ) && array_key_exists( $key,
                            $update['fields'] ) ) {
                continue;
            }
            if ( !empty( $field['data']['removed_from_history'] ) ) {
                continue;
            }
            $form['fields-existing'][$key] = array(
                '#type' => 'markup',
                '#markup' => '<div id="wpcf-user-created-fields-wrapper-' . $field['id'] . '" style="float:left; margin-right: 10px;"><a href="' . admin_url( 'admin-ajax.php'
                        . '?action=wpcf_ajax'
                        . '&amp;page=wpcf-edit'
                        . '&amp;wpcf_action=usermeta_insert_existing'
                        . '&amp;field=' . $field['id'] ) . '&amp;_wpnonce='
                . wp_create_nonce( 'usermeta_insert_existing' ) . '" '
                . 'class="wpcf-fields-add-ajax-link button-secondary" onclick="jQuery(this).parent().fadeOut();" '
                . 'data-slug="' . $field['id'] . '">'
                . htmlspecialchars( stripslashes( $field['name'] ) ) . '</a>'
                . '<a href="' . admin_url( 'admin-ajax.php'
                        . '?action=wpcf_ajax'
                        . '&amp;wpcf_action=remove_from_history2'
                        . '&amp;field_id=' . $field['id'] ) . '&amp;_wpnonce='
                . wp_create_nonce( 'remove_from_history2' ) . '&amp;wpcf_warning='
                . sprintf( __( 'Are you sure that you want to remove field %s from history?',
                                'wpcf' ),
                        htmlspecialchars( stripslashes( $field['name'] ) ) )
                . '&amp;wpcf_ajax_update=wpcf-user-created-fields-wrapper-'
                . $field['id'] . '" title="'
                . sprintf( __( 'Remove field %s', 'wpcf' ),
                        htmlspecialchars( stripslashes( $field['name'] ) ) )
                . '" class="wpcf-ajax-link"><img src="'
                . WPCF_RES_RELPATH
                . '/images/delete-2.png" style="postion:absolute;margin-top:5px;margin-left:-4px;" /></a></div>',
            );
        }
    }
    $form['close-sidebar'] = array(
        '#type' => 'markup',
        '#markup' => '</div>',
    );

    // Group data

    $form['open-main'] = array(
        '#type' => 'markup',
        '#markup' => '<div id="wpcf-form-fields-main">',
    );

    $form['title'] = array(
        '#type' => 'textfield',
        '#name' => 'wpcf[group][name]',
        '#id' => 'wpcf-group-name',
        '#value' => $update ? $update['name'] : '',
        '#inline' => true,
        '#attributes' => array(
            'style' => 'width:100%;margin-bottom:10px;',
            'placeholder' => __('Enter group title', 'wpcf'),
        ),
        '#validate' => array(
            'required' => array(
                'value' => true,
            ),
        )
    );
    $form['description'] = array(
        '#type' => 'textarea',
        '#id' => 'wpcf-group-description',
        '#name' => 'wpcf[group][description]',
        '#value' => $update ? $update['description'] : '',
        '#attributes' => array(
            'placeholder' => __('Enter a description for this group', 'wpcf'),
        ),
    );

    // Show Fields for
    global $wp_roles;
    $options = array();
    $users_currently_supported = array();
    $form_types = array();
    foreach ( $wp_roles->role_names as $role => $name   ) :
        $options[$role]['#name'] = 'wpcf[group][supports][' . $role . ']';
        $options[$role]['#title'] = ucwords($role);
        $options[$role]['#default_value'] = ($update && !empty($update['show_for']) && in_array($role,
                        $update['show_for'])) ? 1 : 0;
        $options[$role]['#value'] = $role;
        $options[$role]['#inline'] = TRUE;
        $options[$role]['#suffix'] = '<br />';
        $options[$role]['#id'] = 'wpcf-form-groups-show-for-' . $role;
        $options[$role]['#attributes'] = array('class' => 'wpcf-form-groups-support-post-type');
        if ($update && !empty($update['show_for']) && in_array($role,
                        $update['show_for'])) {
            $users_currently_supported[] = ucwords($role);
        }
    endforeach;

    if (empty($users_currently_supported)) {
        $users_currently_supported[] = __('Displayed for all users roles',
                'wpcf');
    }

    /*
     * Show for FILTER
     */
    $temp = array(
        '#type' => 'checkboxes',
        '#options' => $options,
        '#name' => 'wpcf[group][supports]',
        '#inline' => true,
    );
    /*
     *
     * Here we use unique function for all filters
     * Since Types 1.1.4
     */
    $form_users = _wpcf_filter_wrap('custom_post_types',
            __('Show For:', 'wpcf'),
            implode(', ', $users_currently_supported),
            __('Displayed for all users roles', 'wpcf'), $temp);

    /*
     * Now starting form
     */
    $access_notification = '';
    if (function_exists('wpcf_access_register_caps')){
        $access_notification = '<div class="message custom wpcf-notif"><span class="wpcf-notif-congrats">'
        . __('This groups visibility is also controlled by the Access plugin.',
                'wpcf')  .'</span></div>';
    }
    $form['supports-table-open'] = array(
        '#type' => 'markup',
        '#markup' => '<table class="widefat"><thead><tr><th>'
        . __('Where to display this group', 'wpcf')
        . '</th></tr></thead><tbody><tr><td>'
        . '<p>'
        . __('Each usermeta group can display different fields for user roles.',
                'wpcf')
        . $access_notification
        . '</p>',
    );
    /*
     * Join filter forms
     */
    // User Roles
    $form['p_wrap_1_' . wpcf_unique_id(serialize($form_users))] = array(
        '#type' => 'markup',
        '#markup' => '<p class="wpcf-filter-wrap">',
    );
    $form = $form + $form_users;

    $form['supports-table-close'] = array(
        '#type' => 'markup',
        '#markup' => '</td></tr></tbody></table><br />',
    );




    /** Admin styles**/

    $form['adminstyles-table-open'] = array(
        '#type' => 'markup',
        '#markup' => '<table class="widefat" id="wpcf-admin-styles-box"><thead><tr><th>'
        . __('Styling Editor', 'wpcf')
        . '</th></tr></thead><tbody><tr><td>'
        . '<p>'
        . __('Customize Fields for admin panel.',
                'wpcf')
        . '</p>',
    );

    $admin_styles_value = $preview_profile = $edit_profile = '';
    if ( isset ($update['admin_styles']) ){
        $admin_styles_value = $update['admin_styles'];
    }
    $temp = '';
    if ($update){
        require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields.php';
        require_once WPCF_EMBEDDED_INC_ABSPATH . '/usermeta.php';
        require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields-post.php';
        require_once WPCF_EMBEDDED_INC_ABSPATH . '/usermeta-post.php';

        $user_id = wpcf_usermeta_get_user();
        $preview_profile = wpcf_usermeta_preview_profile( $user_id, $update, 1 );
        $group = $update;
        $group['fields'] = wpcf_admin_usermeta_process_fields( $user_id, $group['fields'], true, false );
        $edit_profile = wpcf_admin_render_fields($group, $user_id, 1);
        add_action( 'admin_enqueue_scripts', 'wpcf_admin_fields_form_fix_styles', PHP_INT_MAX  );
    }
    $temp[] = array(
        '#type' => 'radio',
        '#suffix' => '<br />',
        '#value' => 'edit_mode',
        '#title' => 'Edit mode',
        '#name' => 'wpcf[group][preview]','#default_value' => '',
        '#before' => '<div class="wpcf-admin-css-preview-style-edit">',
        '#inline' => true,
        '#attributes' => array('onclick' => 'changePreviewHtml(\'editmode\')','checked'=>'checked')
    );

    $temp[] = array(
        '#type' => 'radio',
        '#title' => 'Read Only',
        '#name' => 'wpcf[group][preview]','#default_value' => '',
        '#after' => '</div>',
        '#inline' => true,
        '#attributes' => array('onclick' => 'changePreviewHtml(\'readonly\')')
    );

    $temp[] = array(
        '#type' => 'textarea',
        '#name' => 'wpcf[group][admin_html_preview]',
        '#inline' => true,
        '#value' => '',
        '#id' => 'wpcf-form-groups-admin-html-preview',
        '#before' => '<h3>Field group HTML</h3>'
    );

    $temp[] = array(
        '#type' => 'textarea',
        '#name' => 'wpcf[group][admin_styles]',
        '#inline' => true,
        '#value' => $admin_styles_value,
        '#default_value' => '',
        '#id' => 'wpcf-form-groups-css-fields-editor',
        '#after' => '
        <div class="wpcf-update-preview-btn"><input type="button" value="Update preview" onclick="wpcfPreviewHtml()" style="float:right;" class="button-secondary"></div>
        <h3>Field group preview</h3>
        <div id="wpcf-update-preview-div">Preview here</div>
        <script type="text/javascript">
            var wpcfReadOnly = ' .  json_encode($preview_profile) . ';
            var wpcfEditMode = ' .  json_encode($edit_profile) . ';
            var wpcfDefaultCss = ' .  json_encode($admin_styles_value) . ';
        </script>
        ',
        '#before' => '<h3>Your CSS</h3>'
    );




    $admin_styles = _wpcf_filter_wrap( 'admin_styles',
            __('Admin styles for fields:', 'wpcf'), '', '', $temp, __( 'Open style editor', 'wpcf' ) );
    $form['p_wrap_1_' . wpcf_unique_id(serialize($admin_styles))] = array(
        '#type' => 'markup',
        '#markup' => '<p class="wpcf-filter-wrap">',
    );
    $form = $form + $admin_styles;
    $form['adminstyles-table-close'] = array(
        '#type' => 'markup',
        '#markup' => '</td></tr></tbody></table><br />',
    );
    /** End admin Styles **/




    // Group fields

    $form['fields_title'] = array(
        '#type' => 'markup',
        '#markup' => '<h2>' . __('Fields', 'wpcf') . '</h2>',
    );
    $show_under_title = true;

    $form['ajax-response-open'] = array(
        '#type' => 'markup',
        '#markup' => '<div id="wpcf-fields-sortable" class="ui-sortable">',
    );

    // If it's update, display existing fields
    $existing_fields = array();
    if ($update && isset($update['fields'])) {
        foreach ($update['fields'] as $slug => $field) {
            $field['submitted_key'] = $slug;
            $field['group_id'] = $update['id'];
            $form_field = wpcf_fields_get_field_form_data($field['type'], $field);
            if (is_array($form_field)) {
                $form['draggable-open-' . rand()] = array(
                    '#type' => 'markup',
                    '#markup' => '<div class="ui-draggable">'
                );
                $form = $form + $form_field;
                $form['draggable-close-' . rand()] = array(
                    '#type' => 'markup',
                    '#markup' => '</div>'
                );
            }
            $existing_fields[] = $slug;
            $show_under_title = false;
        }
    }
    // Any new fields submitted but failed? (Don't double it)
    if (!empty($_POST['wpcf']['fields'])) {
        foreach ($_POST['wpcf']['fields'] as $key => $field) {
            if (in_array($key, $existing_fields)) {
                continue;
            }
            $field['submitted_key'] = $key;
            $form_field = wpcf_fields_get_field_form_data($field['type'], $field);
            if (is_array($form_field)) {
                $form['draggable-open-' . rand()] = array(
                    '#type' => 'markup',
                    '#markup' => '<div class="ui-draggable">'
                );
                $form = $form + $form_field;
                $form['draggable-close-' . rand()] = array(
                    '#type' => 'markup',
                    '#markup' => '</div>'
                );
            }
        }
        $show_under_title = false;
    }
    $form['ajax-response-close'] = array(
        '#type' => 'markup',
        '#markup' => '</div>' . '<div id="wpcf-ajax-response"></div>',
    );

    if ($show_under_title) {
        $form['fields_title']['#markup'] = $form['fields_title']['#markup']
                . '<div id="wpcf-fields-under-title">'
                . __('There are no fields in this group. To add a field, click on the field buttons at the right.',
                        'wpcf')
                . '</div>';
    }

    // If update, create ID field
    if ($update) {
        $form['group_id'] = array(
            '#type' => 'hidden',
            '#name' => 'group_id',
            '#value' => $update['id'],
            '#forced_value' => true,
        );
    }

    $form['submit'] = array(
        '#type' => 'submit',
        '#name' => 'save',
        '#value' => __('Save', 'wpcf'),
        '#attributes' => array('class' => 'button-primary wpcf-disabled-on-submit'),
    );

    // Close main div
    $form['close-sidebar'] = array(
        '#type' => 'markup',
        '#markup' => '</div>',
    );


    wpcf_admin_add_js_settings( 'wpcf_filters_association_or',
            '\'' . __( 'This group will appear on %pt% edit pages where content belongs to taxonomy: %tx% or View Template is: %vt%',
                    'wpcf' ) . '\'' );
    wpcf_admin_add_js_settings( 'wpcf_filters_association_and',
            '\'' . __( 'This group will appear on %pt% edit pages where content belongs to taxonomy: %tx% and View Template is: %vt%',
                    'wpcf' ) . '\'' );
    wpcf_admin_add_js_settings( 'wpcf_filters_association_all_pages',
            '\'' . __( 'all', 'wpcf' ) . '\'' );
    wpcf_admin_add_js_settings( 'wpcf_filters_association_all_taxonomies',
            '\'' . __( 'any', 'wpcf' ) . '\'' );
    wpcf_admin_add_js_settings( 'wpcf_filters_association_all_templates',
            '\'' . __( 'any', 'wpcf' ) . '\'' );
    // Add JS settings
    wpcf_admin_add_js_settings('wpcfFormUniqueValuesCheckText',
            '\'' . __('Warning: same values selected', 'wpcf') . '\'');
    wpcf_admin_add_js_settings('wpcfFormUniqueNamesCheckText',
            '\'' . __('Warning: field name already used', 'wpcf') . '\'');
    wpcf_admin_add_js_settings('wpcfFormUniqueSlugsCheckText',
            '\'' . __('Warning: field slug already used', 'wpcf') . '\'');

    return $form;
}


/**
 * Dynamically adds existing field on AJAX call.
 *
 * @param type $form_data
 */
function wpcf_usermeta_insert_existing_ajax() {
    $field = wpcf_admin_fields_get_field( sanitize_text_field( $_GET['field'] ), false, true, false, 'wpcf-usermeta');

    if ( !empty( $field ) ) {
        echo wpcf_fields_get_field_form( $field['type'], $field );
    } else {
        echo '<div>' . __( "Requested field don't exist", 'wpcf' ) . '</div>';
    }
}
