<?php
/*
 * Post relationship code.
 *
 */
require_once WPCF_EMBEDDED_ABSPATH . '/includes/post-relationship.php';
add_action( 'wpcf_custom_types_save', 'wpcf_pr_custom_types_save_action' );

/**
 * Init funtion.
 */
function wpcf_post_relationship_init() {
    add_thickbox();
    wp_enqueue_script( 'wpcf-post-relationship',
            WPCF_EMBEDDED_RELPATH . '/resources/js/post-relationship.js',
            array('jquery'), WPCF_VERSION );
    add_filter('wpcf_meta_box_order_defaults', 'wpcf_post_relationship_add_metabox', 10, 2);
}

/**
 * add metabox relationship to list
 */

function wpcf_post_relationship_add_metabox($meta_boxes, $type )
{
    if ( 'post_type' == $type ) {
        $key = 'relationship';
        if ( !in_array($key, $meta_boxes['side']) && !in_array($key, $meta_boxes['normal'])) {
            $meta_boxes['normal'][] = $key;
        }
    }
    return $meta_boxes;
}

/**
 * Saves relationships.
 *
 * @param type $data
 */
function wpcf_pr_custom_types_save_action( $data ) {
    $relationships = get_option( 'wpcf_post_relationship', array() );
    $save_has_data = array();
    // Reset has
    if ( !empty( $relationships[$data['slug']] ) ) {
        foreach ( $relationships[$data['slug']] as $post_type_has => $rel_data ) {
            if ( !isset( $data['post_relationship']['has'][$post_type_has] ) ) {
                unset( $relationships[$data['slug']][$post_type_has] );
            }
        }
    }
    if ( !empty( $data['post_relationship']['has'] ) ) {
        foreach ( $data['post_relationship']['has'] as $post_type => $true ) {
            if ( empty( $relationships[$data['slug']][$post_type] ) ) {
                $save_has_data[$data['slug']][$post_type] = array();
            } else {
                $save_has_data[$data['slug']][$post_type] = $relationships[$data['slug']][$post_type];
            }
        }
        $relationships[$data['slug']] = $save_has_data[$data['slug']];
    }
    // Reset belongs
    foreach ( $relationships as $post_type => $rel_data ) {
        if ( empty( $data['post_relationship']['belongs'] )
                || !array_key_exists( $post_type, $data['post_relationship']['belongs'] ) ) {
            unset( $relationships[$post_type][$data['slug']] );
        }
    }
    if ( !empty( $data['post_relationship']['belongs'] ) ) {
        foreach ( $data['post_relationship']['belongs'] as $post_type => $true ) {
            if ( empty( $relationships[$post_type][$data['slug']] )
                    && !isset( $relationships[$data['slug']][$post_type] ) ) {
                // Check that can't exist same belongs and has
                $relationships[$post_type][$data['slug']] = array();
            }
        }
    }
    update_option( 'wpcf_post_relationship', $relationships );
}

/**
 * Edit fields form.
 *
 * @param type $parent
 * @param type $child
 */
function wpcf_pr_admin_edit_fields( $parent, $child ) {

    $post_type_parent = get_post_type_object( $parent );
    $post_type_child = get_post_type_object( $child );
    if ( empty( $post_type_parent ) || empty( $post_type_child ) ) {
        die( __( 'Wrong post types' ) );
    }
    $relationships = get_option( 'wpcf_post_relationship', array() );
    if ( !isset( $relationships[$parent][$child] ) ) {
        die( __( 'Relationship do not exist' ) );
    }
    $data = $relationships[$parent][$child];
    wp_enqueue_script( 'jquery' );
    wp_enqueue_style( 'types' );
    wpcf_admin_ajax_head( 'Edit fields', 'wpcf' );
    // Process submit
    if ( isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'pt_edit_fields' ) ) {
        $relationships[$parent][$child]['fields_setting'] = sanitize_text_field( $_POST['fields_setting'] );
		// @todo Maybe sanitization here
        $relationships[$parent][$child]['fields'] = isset( $_POST['fields'] ) ? $_POST['fields'] : array();
        update_option( 'wpcf_post_relationship', $relationships );
        ?>
        <script type="text/javascript">
            window.parent.jQuery('#TB_closeWindowButton').trigger('click');
            window.parent.location.reload();
        </script>
        <?php
        die();
    }

    $groups = wpcf_admin_get_groups_by_post_type( $child );
    $options_cf = array();
    $repetitive_warning = false;
    $repetitive_warning_markup = array();
    $repetitive_warning_txt = __( 'Repeating fields should not be used in child posts. Types will update all field values.', 'wpcf' );
    foreach ( $groups as $group ) {
        $fields = wpcf_admin_fields_get_fields_by_group( $group['id'] );
        foreach ( $fields as $key => $cf ) {
            $__key = wpcf_types_cf_under_control( 'check_outsider', $key ) ? $key : WPCF_META_PREFIX . $key;
            $options_cf[$__key] = array();
            $options_cf[$__key]['#title'] = $cf['name'];
            $options_cf[$__key]['#name'] = 'fields[' . $__key . ']';
            $options_cf[$__key]['#default_value'] = isset( $data['fields'][$__key] ) ? 1 : 0;
            // Repetitive warning
            if ( wpcf_admin_is_repetitive( $cf ) ) {
                if ( !$repetitive_warning ) {
                    $repetitive_warning_markup = array(
                        '#type' => 'markup',
                        '#markup' => '<div class="message error" style="display:none;" id="wpcf-repetitive-warning"><p>' . $repetitive_warning_txt . '</p></div>',
                    );
                }
                $repetitive_warning = true;
                $options_cf[$__key]['#after'] = !isset( $data['fields'][$__key] ) ? '<div class="message error" style="display:none;"><p>' : '<div class="message error"><p>';
                $options_cf[$__key]['#after'] .= $repetitive_warning_txt;
                $options_cf[$__key]['#after'] .= '</p></div>';
                $options_cf[$__key]['#attributes'] = array(
                    'onclick' => 'jQuery(this).parent().find(\'.message\').toggle();',
                    'disabled' => 'disabled',
                );
            }
        }
    }

    $form = array();
    $form['repetitive_warning_markup'] = $repetitive_warning_markup;
    $form['select'] = array(
        '#type' => 'radios',
        '#name' => 'fields_setting',
        '#options' => array(
            __( 'Title, all custom fields and parents', 'wpcf' ) => 'all_cf',
            __( 'Do not show management options for this post type', 'wpcf' ) => 'only_list',
            __( 'All fields, including the standard post fields', 'wpcf' ) => 'all_cf_standardll_cf_standard',
            __( 'Specific fields', 'wpcf' ) => 'specific',
        ),
        '#default_value' => empty( $data['fields_setting'] ) ? 'all_cf' : $data['fields_setting'],
    );
    $options = array();
    $options['_wp_title'] = array(
        '#title' => __( 'Post title', 'wpcf' ),
        '#name' => 'fields[_wp_title]',
        '#default_value' => isset( $data['fields']['_wp_title'] ) ? 1 : 0,
    );
    $options['_wp_body'] = array(
        '#title' => __( 'Post body', 'wpcf' ),
        '#name' => 'fields[_wp_body]',
        '#default_value' => isset( $data['fields']['_wp_body'] ) ? 1 : 0,
    );
    $options = $options + $options_cf;
    $temp_belongs = wpcf_pr_admin_get_belongs( $child );
    foreach ( $temp_belongs as $temp_parent => $temp_data ) {
        if ( $temp_parent == $parent ) {
            continue;
        }
        $temp_parent_type = get_post_type_object( $temp_parent );
        $options[$temp_parent] = array();
        $options[$temp_parent]['#title'] = $temp_parent_type->label;
        $options[$temp_parent]['#name'] = 'fields[_wpcf_pr_parents][' . $temp_parent . ']';
        $options[$temp_parent]['#default_value'] = isset( $data['fields']['_wpcf_pr_parents'][$temp_parent] ) ? 1 : 0;
    }
    // Taxonomies
    $taxonomies = get_object_taxonomies( $post_type_child->name, 'objects' );
    if ( !empty( $taxonomies ) ) {
        foreach ( $taxonomies as $tax_id => $taxonomy ) {
            $options[$tax_id] = array();
            $options[$tax_id]['#title'] = sprintf( __('Taxonomy - %s', ''), $taxonomy->label );
            $options[$tax_id]['#name'] = 'fields[_wpcf_pr_taxonomies][' . $tax_id . ']';
            $options[$tax_id]['#default_value'] = isset( $data['fields']['_wpcf_pr_taxonomies'][$tax_id] ) ? 1 : 0;
        }
    }
    $form['specific'] = array(
        '#type' => 'checkboxes',
        '#name' => 'fields',
        '#options' => $options,
        '#default_value' => isset( $data['fields'] ),
        '#before' => '<div id="wpcf-specific" style="display:none;margin:10px 0 0 20px;">',
        '#after' => '</div>',
    );
    $form['submit'] = array(
        '#type' => 'submit',
        '#name' => 'submit',
        '#value' => __( 'Save', 'wpcf' ),
        '#attributes' => array('class' => 'button-primary'),
    );
    echo '<form method="post" action="" class="types-select-child-fields">';
    echo wpcf_form_simple( $form );
    echo wp_nonce_field( 'pt_edit_fields' );
    echo '</form>';
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function(){
            if (jQuery('input[name="fields_setting"]:checked').val() == 'specific') {
                jQuery('#wpcf-specific').show();
            } else {
    <?php if ( $repetitive_warning && 'only_list' != $form['select']['#default_value']) {

?>
                    jQuery('#wpcf-repetitive-warning').show();
        <?php
    }
    ?>
            }
            jQuery('input[name="fields_setting"]').change(function(){
                if (jQuery(this).val() == 'specific') {
                    jQuery('#wpcf-specific').slideDown();
                } else {
                    jQuery('#wpcf-specific').slideUp();
    <?php if ( $repetitive_warning ) { ?>
                    if ( 'only_list' != jQuery('input[name="fields_setting"]:checked').val()) {
                        jQuery('#wpcf-repetitive-warning').show();
                    }
        <?php } ?>
                }
            });
        });
    </script>
    <?php
    wpcf_admin_ajax_footer();
}

function wpcf_admin_metabox_relationship($post_type)
{
    $form = array();
    $form['table-pr-open'] = wpcf_admin_metabox_begin(__( 'Post Relationship', 'wpcf' ), 'relationship', 'wpcf-types-form-pr-table', false);
    /**
     * belongs/children section
     */
    $has = wpcf_pr_admin_get_has( $post_type['slug'] );
    $belongs = wpcf_pr_admin_get_belongs( $post_type['slug'] );
    $post_types = get_post_types( '', 'objects' );

    if ( empty( $has ) || empty( $post_type['slug'] ) ) {
        $txt_has = __( "Children: None", 'wpcf' );
    } else {
        $txt_has = array();
        foreach ( $has as $pr_key => $pr_data ) {
            $txt_has[] = isset( $post_types[$pr_key] ) ? $post_types[$pr_key]->labels->singular_name : $pr_key;
        }
        $txt_has = sprintf( __( "Children: %s", 'wpcf' ), implode( ', ', $txt_has ) );
    }
    if ( !empty( $belongs ) ) {
        $txt_belongs = array();
        foreach ( $belongs as $pr_key => $pr_data ) {
            $txt_belongs[] = isset( $post_types[$pr_key] ) ? $post_types[$pr_key]->labels->singular_name : $pr_key;
        }
        $txt_belongs = sprintf( __( "Parent: %s", 'wpcf' ),
            implode( ', ', $txt_belongs ) );
    } else {
        $txt_belongs = __( "Parent: None", 'wpcf' );
    }
    // Others belonging to
    if ( !empty( $belongs ) ) {
        $txt_belongs = array();
        foreach ( $belongs as $pr_key => $pr_data ) {
            $txt_belongs[] = isset( $post_types[$pr_key] ) ? $post_types[$pr_key]->labels->singular_name : $pr_key;
        }
        $txt_belongs = sprintf( __( "Parent: %s", 'wpcf' ),
            implode( ', ', $txt_belongs ) );
    } else {
        $txt_belongs = __( "Parent: None", 'wpcf' );
    }
    $form['table-pr-belongs'] = array(
        '#type' => 'markup',
        '#markup' => '<div style="margin: 10px 0 10px 0;"><span class="wpcf-pr-belongs-summary">' . $txt_belongs . '</span>&nbsp;'
        . '<a href="javascript:void(0);" id="wpcf-pr-belongs-edit" class="button-secondary wpcf-pr-edit">'
        . __( 'Edit', 'wpcf' ) . '</a>',
            );
    $options = array();

    /**
     * build excluded post types
     */
    global $wpcf;
    $excluded_post_types = $wpcf->excluded_post_types;
    $excluded_post_types[] = $post_type['slug'];

    foreach ( $post_types as $temp_post_type_slug => $temp_post_type ) {
        if (
            in_array( $temp_post_type_slug, $excluded_post_types )
            || (
                !$temp_post_type->show_ui
                && !apply_filters('wpcf_show_ui_hide_in_relationships', true)
            )
        ) {
            continue;
        }
        // Check if it's in has
        if ( isset( $has[$temp_post_type_slug] ) ) {
            continue;
        }
        $options[$temp_post_type_slug]['#name'] = 'ct[post_relationship][belongs][' . $temp_post_type_slug . ']';
        $options[$temp_post_type_slug]['#title'] = $temp_post_type->labels->singular_name;
        $options[$temp_post_type_slug]['#default_value'] = isset( $belongs[$temp_post_type_slug] );
        $options[$temp_post_type_slug]['#inline'] = true;
        $options[$temp_post_type_slug]['#after'] = '&nbsp;&nbsp;';
        if ( is_rtl() ) {
            $options[$temp_post_type_slug]['#before'] = '<div style="float:right;margin-left:10px;">';
            $options[$temp_post_type_slug]['#after'] .= '</div>';
        }
    }
    $form['table-pr-has-form'] = array(
        '#type' => 'checkboxes',
        '#options' => $options,
        '#name' => 'ct[post_relationship]',
        '#before' => '<div style="display:none; margin: 10px 0 20px 0;">',
        '#after' => '<br /><br /><a href="javascript:void(0);" class="button-primary wpcf-pr-belongs-apply">'
        . __( 'Apply', 'wpcf' ) . '</a>&nbsp;<a href="javascript:void(0);" class="button-secondary wpcf-pr-belongs-cancel">'
        . __( 'Cancel', 'wpcf' ) . '</a></div></div>',
        '#inline' => true,
    );
    $form['table-pr-has'] = array(
        '#type' => 'markup',
        '#markup' => '<div style="margin: 10px 0 5px 0;"><span class="wpcf-pr-has-summary">' . $txt_has . '</span>&nbsp;'
        . '<a href="javascript:void(0);" id="wpcf-pr-has-edit" class="button-secondary wpcf-pr-edit">'
        . __( 'Edit', 'wpcf' ) . '</a>',
    );
    $options = array();
    foreach ( $post_types as $temp_post_type_slug => $temp_post_type ) {
        if (
            in_array( $temp_post_type_slug, $excluded_post_types )
            || (
                !$temp_post_type->show_ui
                && !apply_filters('wpcf_show_ui_hide_in_relationships', true)
            )
        ) {
            continue;
        }
        // Check if it already belongs
        if ( isset( $belongs[$temp_post_type_slug] ) ) {
            continue;
        }
        $options[$temp_post_type_slug]['#name'] = 'ct[post_relationship][has][' . $temp_post_type_slug . ']';
        $options[$temp_post_type_slug]['#title'] = $temp_post_type->labels->singular_name;
        $options[$temp_post_type_slug]['#default_value'] = isset( $has[$temp_post_type_slug] );
        $options[$temp_post_type_slug]['#inline'] = true;
        $options[$temp_post_type_slug]['#after'] = isset( $has[$temp_post_type_slug] ) ? ''
                . '<a href="'
                . admin_url( 'admin-ajax.php?action=wpcf_ajax&wpcf_action=pt_edit_fields&child='
                        . $temp_post_type_slug . '&parent='
                        . $post_type['slug']
                        . '&_wpnonce='
                        . wp_create_nonce( 'pt_edit_fields' )
                        . '&KeepThis=true&TB_iframe=true' )
                . '" class="thickbox" title="'
                . __('Select child fields to be displayed', 'wpcf') . '">('
                . __( 'Edit fields' ) . ')</a>&nbsp;&nbsp;' : ''
                . '<a href="javascript:void(0);" style="color:Gray;" title="'
                . __( 'Please save the page first, before you can edit the child items',
                        'wpcf' ) . '">('
                . __( 'Edit fields' ) . ')</a>&nbsp;&nbsp;';
        if ( is_rtl() ) {
            $options[$temp_post_type_slug]['#before'] = '<div style="float:right;margin-left:10px;">';
            $options[$temp_post_type_slug]['#after'] .= '</div>';
        }
    }
    $form['table-pr-belongs-form'] = array(
        '#type' => 'checkboxes',
        '#options' => $options,
        '#name' => 'ct[post_relationship]',
        '#before' => '<div style="display:none; margin: 10px 0 20px 0;">',
        '#after' => '<br /><br /><a href="javascript:void(0);" class="button-primary wpcf-pr-has-apply">'
        . __( 'Apply', 'wpcf' ) . '</a>&nbsp;<a href="javascript:void(0);" class="button-secondary wpcf-pr-has-cancel">'
        . __( 'Cancel', 'wpcf' ) . '</a></div></div>',
        '#inline' => true,
    );
    $form['table-pr-explanation'] = array(
        '#type' => 'markup',
        '#markup' => '<p>'
        . __( "You can choose which fields will show when editing parent pages.",
                'wpcf' )
        . '<br />' . __( "Click on the 'edit' button to select them for each parent.",
                'wpcf' )
        . '<br />'
        . sprintf( __( 'Learn about %sPost Type Relationships%s', 'wpcf' ),
                '<a href="http://wp-types.com/documentation/user-guides/creating-post-type-relationships/" target="_blank">',
                ' &raquo;</a>' )
        . '</p>',
    );
    /**
     * close form
     */
    $form['table-pr-close'] = wpcf_admin_metabox_end();
    /**
        * additional settings
    */
    wpcf_admin_add_js_settings( 'wpcf_pr_has_empty_txt', '\'' . __( "Children: None", 'wpcf' ) . '\'' );
    wpcf_admin_add_js_settings( 'wpcf_pr_has_txt', '\'' . __( "Children: %s", 'wpcf' ) . '\'' );
    wpcf_admin_add_js_settings( 'wpcf_pr_belongs_empty_txt', '\'' . __( "Parent: None", 'wpcf' ) . '\'' );
    wpcf_admin_add_js_settings( 'wpcf_pr_belongs_txt', '\'' . __( "Parent: %s", 'wpcf' ) . '\'' );
    return $form;
}

