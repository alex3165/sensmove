<?php
/**
 *
 * Custom types form
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/includes/custom-types-form.php $
 * $LastChangedDate: 2015-04-01 14:15:17 +0000 (Wed, 01 Apr 2015) $
 * $LastChangedRevision: 1125405 $
 * $LastChangedBy: iworks $
 *
 */

/**
 * Add/edit form
 */
function wpcf_admin_custom_types_form()
{
    global $wpcf;

    include_once dirname(__FILE__).'/common-functions.php';
    include_once dirname(__FILE__).'/fields.php';

    $ct = array();
    $id = false;
    $update = false;

    if ( isset( $_GET['wpcf-post-type'] ) ) {
        $id = sanitize_text_field( $_GET['wpcf-post-type'] );
    } elseif ( isset( $_POST['wpcf-post-type'] ) ) {
        $id = sanitize_text_field( $_POST['wpcf-post-type'] );
    }

    if ( $id ) {
        $custom_types = get_option( 'wpcf-custom-types', array() );
        if ( isset( $custom_types[$id] ) ) {
            $ct = $custom_types[$id];
            $update = true;
            // Set rewrite if needed
            if ( isset( $_GET['wpcf-rewrite'] ) ) {
                flush_rewrite_rules();
            }
        } else {
            wpcf_admin_message( __( 'Wrong custom post type specified', 'wpcf' ), 'error' );
            return false;
        }
    } else {
        $ct = wpcf_custom_types_default();
    }

    $form = array();
    /**
     * postbox-controll
     */
    $markup = wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false, false );
    $markup.= wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false, false );
    $form['postbox-controll'] = array(
        '#type' => 'markup',
        '#markup' => $markup,
    );

    /**
     * form setup
     */
    $form['#form']['callback'] = 'wpcf_admin_custom_types_form_submit';
    $form['#form']['redirection'] = false;

    if ( $update ) {
        $form['id'] = array(
            '#type' => 'hidden',
            '#value' => $id,
            '#name' => 'ct[wpcf-post-type]',
        );
        /**
         * update taxonomy too
         */
        $custom_taxonomies = get_option( 'wpcf-custom-taxonomies', array() );
        foreach( $custom_taxonomies as $slug => $data ) {
            if ( !array_key_exists('supports', $data)) {
                continue;
            }
            if ( !array_key_exists($id, $data['supports']) ) {
                continue;
            }
            if (
                array_key_exists('taxonomies', $ct)
                && array_key_exists($slug, $ct['taxonomies'])
            ) {
                continue;
            }
            unset($custom_taxonomies[$slug]['supports'][$id]);
        }
        update_option( 'wpcf-custom-taxonomies', $custom_taxonomies);
    }

    /**
     * WP control for meta boxes
     */
    include_once ABSPATH.'/wp-admin/includes/meta-boxes.php';
    wp_enqueue_script( 'post' );

    $form['form-open'] = array(
        '#type' => 'markup',
        '#markup' => '<div id="poststuff">',
    );

    $form['form-metabox-holder-columns-2-open'] = array(
        '#type' => 'markup',
        '#markup' => '<div id="post-body" class="metabox-holder columns-2">',
    );

    $form['post-body-content-open'] = array(
        '#type' => 'markup',
        '#markup' => '<div id="post-body-content">',
    );

    $form['table-1-open'] = array(
        '#type' => 'markup',
        '#markup' => '<table id="wpcf-types-form-name-table" class="wpcf-types-form-table widefat"><thead><tr><th colspan="2">' . __( 'Name and description',
                'wpcf' ) . '</th></tr></thead><tbody>',
    );
    $table_row = '<tr><td><LABEL></td><td><ERROR><ELEMENT></td></tr>';
    $form['name'] = array(
        '#type' => 'textfield',
        '#name' => 'ct[labels][name]',
        '#title' => __( 'Custom post type name plural', 'wpcf' ) . ' (<strong>' . __( 'required',
                'wpcf' ) . '</strong>)',
        '#description' => '<strong>' . __( 'Enter in plural!', 'wpcf' )
        . '.',
        '#value' => isset( $ct['labels']['name'] ) ? $ct['labels']['name'] : '',
        '#validate' => array(
            'required' => array('value' => 'true'),
        ),
        '#pattern' => $table_row,
        '#inline' => true,
        '#id' => 'name-plural',
        '#attributes' => array(
            'data-wpcf_warning_same_as_slug' => $wpcf->post_types->message( 'warning_singular_plural_match' ),
            'data-wpcf_warning_same_as_slug_ignore' => $wpcf->post_types->message( 'warning_singular_plural_match_ignore' ),
            'placeholder' => __('Enter post type name plural', 'wpcf' ),
        ),
    );
    $form['name-singular'] = array(
        '#type' => 'textfield',
        '#name' => 'ct[labels][singular_name]',
        '#title' => __( 'Custom post type name singular', 'wpcf' ) . ' (<strong>' . __( 'required',
                'wpcf' ) . '</strong>)',
        '#description' => '<strong>' . __( 'Enter in singular!', 'wpcf' )
        . '</strong><br />'
        . '.',
        '#value' => isset( $ct['labels']['singular_name'] ) ? $ct['labels']['singular_name'] : '',
        '#validate' => array(
            'required' => array('value' => 'true'),
        ),
        '#pattern' => $table_row,
        '#inline' => true,
        '#id' => 'name-singular',
        '#attributes' => array(
            'placeholder' => __('Enter post type name singular', 'wpcf' ),
        ),
    );

    /**
     * IF isset $_POST['slug'] it means form is not submitted
     */
    $attributes = array();
    if ( !empty( $_POST['ct']['slug'] ) ) {
        $reserved = wpcf_is_reserved_name( sanitize_text_field( $_POST['ct']['slug'] ), 'post_type' );
        if ( is_wp_error( $reserved ) ) {
            $attributes = array(
                'class' => 'wpcf-form-error',
                'onclick' => 'jQuery(this).removeClass(\'wpcf-form-error\');'
            );
        }
    }

    $form['slug'] = array(
        '#type' => 'textfield',
        '#name' => 'ct[slug]',
        '#title' => __( 'Slug', 'wpcf' ) . ' (<strong>' . __( 'required', 'wpcf' ) . '</strong>)',
        '#value' => isset( $ct['slug'] ) ? $ct['slug'] : '',
        '#pattern' => $table_row,
        '#inline' => true,
        '#validate' => array(
            'required' => array('value' => 'true'),
            'nospecialchars' => array('value' => 'true'),
            'maxlength' => array('value' => '20'),
        ),
        '#attributes' => $attributes + array(
            'maxlength' => '20',
            'placeholder' => __('Enter post type slug', 'wpcf' ),
            ),
        '#id' => 'slug',
    );
    $form['description'] = array(
        '#type' => 'textarea',
        '#name' => 'ct[description]',
        '#title' => __( 'Description', 'wpcf' ),
        '#value' => isset( $ct['description'] ) ? $ct['description'] : '',
        '#attributes' => array(
            'rows' => 4,
            'cols' => 60,
            'placeholder' => __('Enter post type description', 'wpcf' ),
        ),
        '#pattern' => $table_row,
        '#inline' => true,
    );
    /**
     * icons only for version 3.8 up
     */
    global $wp_version;
    if ( version_compare( '3.8', $wp_version ) < 1 ) {
        $form['icon'] = array(
            '#type' => 'select',
            '#name' => 'ct[icon]',
            '#title' => __( 'Icon', 'wpcf' ),
            '#default_value' => isset( $ct['icon'] ) ? $ct['icon'] : 'admin-post',
            '#pattern' => $table_row,
            '#inline' => true,
            '#id' => 'wpcf-types-icon',
            '#options' => array(
                'admin appearance' => 'admin-appearance',
                'admin collapse' => 'admin-collapse',
                'admin comments' => 'admin-comments',
                'admin generic' => 'admin-generic',
                'admin home' => 'admin-home',
                'admin links' => 'admin-links',
                'admin media' => 'admin-media',
                'admin network' => 'admin-network',
                'admin page' => 'admin-page',
                'admin plugins' => 'admin-plugins',
                'admin post' => 'admin-post',
                'admin settings' => 'admin-settings',
                'admin site' => 'admin-site',
                'admin tools' => 'admin-tools',
                'admin users' => 'admin-users',
                'align center' => 'align-center',
                'align left' => 'align-left',
                'align none' => 'align-none',
                'align right' => 'align-right',
                'analytics' => 'analytics',
                'archive' => 'archive',
                'arrow down' => 'arrow-down',
                'arrow down alt' => 'arrow-down-alt',
                'arrow down alt2' => 'arrow-down-alt2',
                'arrow left' => 'arrow-left',
                'arrow left alt' => 'arrow-left-alt',
                'arrow left alt2' => 'arrow-left-alt2',
                'arrow right' => 'arrow-right',
                'arrow right alt' => 'arrow-right-alt',
                'arrow right alt2' => 'arrow-right-alt2',
                'arrow up' => 'arrow-up',
                'arrow up alt' => 'arrow-up-alt',
                'arrow up alt2' => 'arrow-up-alt2',
                'art' => 'art',
                'awards' => 'awards',
                'backup' => 'backup',
                'book' => 'book',
                'book alt' => 'book-alt',
                'businessman' => 'businessman',
                'calendar' => 'calendar',
                'camera' => 'camera',
                'cart' => 'cart',
                'category' => 'category',
                'chart area' => 'chart-area',
                'chart bar' => 'chart-bar',
                'chart line' => 'chart-line',
                'chart pie' => 'chart-pie',
                'clipboard' => 'clipboard',
                'clock' => 'clock',
                'cloud' => 'cloud',
                'dashboard' => 'dashboard',
                'desktop' => 'desktop',
                'dismiss' => 'dismiss',
                'download' => 'download',
                'edit' => 'edit',
                'editor aligncenter' => 'editor-aligncenter',
                'editor alignleft' => 'editor-alignleft',
                'editor alignright' => 'editor-alignright',
                'editor bold' => 'editor-bold',
                'editor break' => 'editor-break',
                'editor code' => 'editor-code',
                'editor contract' => 'editor-contract',
                'editor customchar' => 'editor-customchar',
                'editor expand' => 'editor-expand',
                'editor help' => 'editor-help',
                'editor indent' => 'editor-indent',
                'editor insertmore' => 'editor-insertmore',
                'editor italic' => 'editor-italic',
                'editor justify' => 'editor-justify',
                'editor kitchensink' => 'editor-kitchensink',
                'editor ol' => 'editor-ol',
                'editor outdent' => 'editor-outdent',
                'editor paragraph' => 'editor-paragraph',
                'editor paste text' => 'editor-paste-text',
                'editor paste word' => 'editor-paste-word',
                'editor quote' => 'editor-quote',
                'editor removeformatting' => 'editor-removeformatting',
                'editor rtl' => 'editor-rtl',
                'editor spellcheck' => 'editor-spellcheck',
                'editor strikethrough' => 'editor-strikethrough',
                'editor textcolor' => 'editor-textcolor',
                'editor ul' => 'editor-ul',
                'editor underline' => 'editor-underline',
                'editor unlink' => 'editor-unlink',
                'editor video' => 'editor-video',
                'email' => 'email',
                'email alt' => 'email-alt',
                'exerpt view' => 'exerpt-view',
                'external' => 'external',
                'facebook' => 'facebook',
                'facebook alt' => 'facebook-alt',
                'feedback' => 'feedback',
                'flag' => 'flag',
                'format aside' => 'format-aside',
                'format audio' => 'format-audio',
                'format chat' => 'format-chat',
                'format gallery' => 'format-gallery',
                'format image' => 'format-image',
                'format quote' => 'format-quote',
                'format status' => 'format-status',
                'format video' => 'format-video',
                'forms' => 'forms',
                'googleplus' => 'googleplus',
                'groups' => 'groups',
                'hammer' => 'hammer',
                'heart' => 'heart',
                'id' => 'id',
                'id alt' => 'id-alt',
                'image crop' => 'image-crop',
                'image flip horizontal' => 'image-flip-horizontal',
                'image flip vertical' => 'image-flip-vertical',
                'image rotate left' => 'image-rotate-left',
                'image rotate right' => 'image-rotate-right',
                'images alt' => 'images-alt',
                'images alt2' => 'images-alt2',
                'info' => 'info',
                'leftright' => 'leftright',
                'lightbulb' => 'lightbulb',
                'list view' => 'list-view',
                'location' => 'location',
                'location alt' => 'location-alt',
                'lock' => 'lock',
                'marker' => 'marker',
                'media archive' => 'media-archive',
                'media audio' => 'media-audio',
                'media code' => 'media-code',
                'media default' => 'media-default',
                'media document' => 'media-document',
                'media interactive' => 'media-interactive',
                'media spreadsheet' => 'media-spreadsheet',
                'media text' => 'media-text',
                'media video' => 'media-video',
                'megaphone' => 'megaphone',
                'menu' => 'menu',
                'microphone' => 'microphone',
                'migrate' => 'migrate',
                'minus' => 'minus',
                'nametag' => 'nametag',
                'networking' => 'networking',
                'no' => 'no',
                'no alt' => 'no-alt',
                'performance' => 'performance',
                'playlist audio' => 'playlist-audio',
                'playlist video' => 'playlist-video',
                'plus' => 'plus',
                'plus alt' => 'plus-alt',
                'portfolio' => 'portfolio',
                'post status' => 'post-status',
                'pressthis' => 'pressthis',
                'products' => 'products',
                'randomize' => 'randomize',
                'redo' => 'redo',
                'rss' => 'rss',
                'schedule' => 'schedule',
                'screenoptions' => 'screenoptions',
                'search' => 'search',
                'share' => 'share',
                'share alt' => 'share-alt',
                'share alt2' => 'share-alt2',
                'shield' => 'shield',
                'shield alt' => 'shield-alt',
                'slides' => 'slides',
                'smartphone' => 'smartphone',
                'smiley' => 'smiley',
                'sort' => 'sort',
                'sos' => 'sos',
                'star empty' => 'star-empty',
                'star filled' => 'star-filled',
                'star half' => 'star-half',
                'tablet' => 'tablet',
                'tag' => 'tag',
                'tagcloud' => 'tagcloud',
                'testimonial' => 'testimonial',
                'text' => 'text',
                'tickets' => 'tickets',
                'translation' => 'translation',
                'trash' => 'trash',
                'twitter' => 'twitter',
                'undo' => 'undo',
                'universal access' => 'universal-access',
                'universal access alt' => 'universal-access-alt',
                'update' => 'update',
                'upload' => 'upload',
                'vault' => 'vault',
                'video alt' => 'video-alt',
                'video alt2' => 'video-alt2',
                'video alt3' => 'video-alt3',
                'visibility' => 'visibility',
                'welcome add page' => 'welcome-add-page',
                'welcome comments' => 'welcome-comments',
                'welcome learn more' => 'welcome-learn-more',
                'welcome view site' => 'welcome-view-site',
                'welcome widgets menus' => 'welcome-widgets-menus',
                'welcome write blog' => 'welcome-write-blog',
                'wordpress' => 'wordpress',
                'wordpress alt' => 'wordpress-alt',
                'yes' => 'yes',
            ),
        );
    }

    $form['table-1-close'] = array(
        '#type' => 'markup',
        '#markup' => '</tbody></table>',
    );

    global $sitepress;
    if (
        $update && isset( $sitepress )
        && version_compare( ICL_SITEPRESS_VERSION, '2.6.2', '>=' )
        && function_exists( 'wpml_custom_post_translation_options' )
    ) {
        $form['table-1-close']['#markup'] .= wpml_custom_post_translation_options( $ct['slug'] );
    }

    $form['post-body-content-close'] = array(
        '#type' => 'markup',
        '#markup' => '</div>',
    );

    /**
     * get box order
     */
    $meta_box_order_defaults = apply_filters(
        'wpcf_meta_box_order_defaults',
        array(
            'side' => array('submitdiv', 'wpcf_visibility', 'taxonomies'),
            'normal' => array('labels', 'display_sections', 'options'),
        ),
        'post_type'
    );
    $screen = get_current_screen();
    if ( false == ( $meta_box_order = get_user_option( 'meta-box-order_'.$screen->id) )) {
        $meta_box_order = $meta_box_order_defaults;
    } else {
        if ( isset($meta_box_order[0]) && !isset($meta_box_order['normal']) ) {
            $meta_box_order['normal'] = $meta_box_order[0];
        }
    }

    $meta_boxes = array();
    foreach( $meta_box_order_defaults as $key => $value ) {
        foreach($value as $meta_box_key) {
            $meta_boxes[$meta_box_key] = $ct;
        }
    }
    $meta_boxes[ 'submitdiv'] = false;

    foreach ( $meta_box_order as $key => $value ) {
        if ( is_array($value) ) {
            continue;
        }
        $meta_box_order[$key] = explode(',', $value);
    }

    /**
     * postbox-container-1
     */

    $form['postbox-container-1-open'] = array(
        '#type' => 'markup',
        '#markup' => '<div id="postbox-container-1" class="postbox-container"><div class="meta-box-sortables ui-sortable" id="side-sortables">',
    );
    foreach( $meta_box_order['side'] as $key ) {
        $function = sprintf('wpcf_admin_metabox_%s', $key);
        if ( is_callable($function) ) {
            $form += $function($meta_boxes[$key]);
            unset($meta_boxes[$key]);
        }
    }
    /* close side container */
    $form['postbox-container-1-close'] = array(
        '#type' => 'markup',
        '#markup' => '</div></div>',
    );

    /**
     * normal container
     */

    $form['postbox-container-2-open'] = array(
        '#type' => 'markup',
        '#markup' => '<div id="postbox-container-2" class="postbox-container"><div class="meta-box-sortables ui-sortable">',
    );
    foreach( $meta_box_order['normal'] as $key ) {
        $function = sprintf('wpcf_admin_metabox_%s', $key);
        if ( is_callable($function) ) {
            $form += $function($meta_boxes[$key]);
            unset($meta_boxes[$key]);
        }
    }
    /**
     * grab missing meta-boxes
     */
    foreach( array_keys($meta_boxes) as $key ) {
        $function = sprintf('wpcf_admin_metabox_%s', $key);
        if ( is_callable($function) ) {
            $form += $function($meta_boxes[$key]);
        }
    }

    /**
     * filter wpcf_post_type_form
     */

    $form = $form + apply_filters( 'wpcf_post_type_form', array(), $ct );

    /**
     * container-2 close
     */
    $form['postbox-container-2-close'] = array(
        '#type' => 'markup',
        '#markup' => '</div></div>',
    );

    $form['form-close'] = array(
        '#type' => 'markup',
        '#markup' => '</div></div>',
    );

    return $form;
}

/**
 * Adds JS validation script.
 */
function wpcf_admin_types_form_js_validation()
{
    wpcf_form_render_js_validation();
}

/**
 * Submit function
 *
 * @global object $wpdb
 *
 */
function wpcf_admin_custom_types_form_submit($form)
{
    global $wpcf;

    if ( !isset( $_POST['ct'] ) ) {
        return false;
    }
    $data = $_POST['ct'];
    $update = false;

    // Sanitize data
    if ( isset( $data['wpcf-post-type'] ) ) {
        $update = true;
        $data['wpcf-post-type'] = sanitize_title( $data['wpcf-post-type'] );
    } else {
        $data['wpcf-post-type'] = null;
    }
    if ( isset( $data['slug'] ) ) {
        $data['slug'] = sanitize_title( $data['slug'] );
    } else {
        $data['slug'] = null;
    }
    if ( isset( $data['rewrite']['slug'] ) ) {
        $data['rewrite']['slug'] = remove_accents( $data['rewrite']['slug'] );
        $data['rewrite']['slug'] = strtolower( $data['rewrite']['slug'] );
        $data['rewrite']['slug'] = trim( $data['rewrite']['slug'] );
    }

    // Set post type name
    $post_type = null;
    if ( !empty( $data['slug'] ) ) {
        $post_type = $data['slug'];
    } elseif ( !empty( $data['wpcf-post-type'] ) ) {
        $post_type = $data['wpcf-post-type'];
    } elseif ( !empty( $data['labels']['singular_name'] ) ) {
        $post_type = sanitize_title( $data['labels']['singular_name'] );
    }

    if ( empty( $post_type ) ) {
        wpcf_admin_message( __( 'Please set post type name', 'wpcf' ), 'error' );
        return false;
    }

    $data['slug'] = $post_type;
    $custom_types = get_option( 'wpcf-custom-types', array() );

    // Check reserved name
    $reserved = wpcf_is_reserved_name( $post_type, 'post_type' );
    if ( is_wp_error( $reserved ) ) {
        wpcf_admin_message( $reserved->get_error_message(), 'error' );
        return false;
    }

    // Check overwriting
    if ( ( !array_key_exists( 'wpcf-post-type', $data ) || $data['wpcf-post-type'] != $post_type ) && array_key_exists( $post_type, $custom_types ) ) {
        wpcf_admin_message( __( 'Custom post type already exists', 'wpcf' ), 'error' );
        return false;
    }

    /*
     * Since Types 1.2
     * We do not allow plural and singular names to be same.
     */
    if ( $wpcf->post_types->check_singular_plural_match( $data ) ) {
        wpcf_admin_message( $wpcf->post_types->message( 'warning_singular_plural_match' ), 'error' );
        return false;
    }

    // Check if renaming then rename all post entries and delete old type
    if ( !empty( $data['wpcf-post-type'] )
            && $data['wpcf-post-type'] != $post_type ) {
        global $wpdb;
        $wpdb->update( $wpdb->posts, array('post_type' => $post_type),
                array('post_type' => $data['wpcf-post-type']), array('%s'),
                array('%s')
            );

        /**
         * update post meta "_wp_types_group_post_types"
         */
        $sql = $wpdb->prepare(
            sprintf(
                'select meta_id, meta_value from %s where meta_key = %%s',
                $wpdb->postmeta
            ),
            '_wp_types_group_post_types'
        );
        $all_meta = $wpdb->get_results($sql, OBJECT_K);
        $re = sprintf( '/,%s,/', $data['wpcf-post-type'] );
        foreach( $all_meta as $meta ) {
            if ( !preg_match( $re, $meta->meta_value ) ) {
                continue;
            }
            $wpdb->update(
                $wpdb->postmeta,
                array(
                    'meta_value' => preg_replace( $re, ','.$post_type.',', $meta->meta_value ),
                ),
                array(
                    'meta_id' => $meta->meta_id,
                ),
                array( '%s' ),
                array( '%d' )
            );
        }

        /**
         * update _wpcf_belongs_{$data['wpcf-post-type']}_id
         */
        $wpdb->update(
            $wpdb->postmeta,
            array(
                'meta_key' => sprintf( '_wpcf_belongs_%s_id', $post_type ),
            ),
            array(
                'meta_key' => sprintf( '_wpcf_belongs_%s_id', $data['wpcf-post-type'] ),
            ),
            array( '%s' ),
            array( '%s' )
        );

        /**
         * update options "wpv_options"
         */
        $wpv_options = get_option( 'wpv_options', true );
        if ( is_array( $wpv_options ) ) {
            $re = sprintf( '/(views_template_(archive_)?for_)%s/', $data['wpcf-post-type'] );
            foreach( $wpv_options as $key => $value ) {
                if ( !preg_match( $re, $key ) ) {
                    continue;
                }
                unset($wpv_options[$key]);
                $key = preg_replace( $re, "$1".$post_type, $key );
                $wpv_options[$key] = $value;
            }
            update_option( 'wpv_options', $wpv_options );
        }

        /**
         * update option "wpcf-custom-taxonomies"
         */
        $wpcf_custom_taxonomies = get_option( 'wpcf-custom-taxonomies', true );
        if ( is_array( $wpcf_custom_taxonomies ) ) {
            $update_wpcf_custom_taxonomies = false;
            foreach( $wpcf_custom_taxonomies as $key => $value ) {
                if ( array_key_exists( 'supports', $value ) && array_key_exists( $data['wpcf-post-type'], $value['supports'] ) ) {
                    unset( $wpcf_custom_taxonomies[$key]['supports'][$data['wpcf-post-type']] );
                    $update_wpcf_custom_taxonomies = true;
                }
            }
            if ( $update_wpcf_custom_taxonomies ) {
                update_option( 'wpcf-custom-taxonomies', $wpcf_custom_taxonomies );
            }
        }

        // Sync action
        do_action( 'wpcf_post_type_renamed', $post_type, $data['wpcf-post-type'] );

        // Set protected data
        $protected_data_check = $custom_types[$data['wpcf-post-type']];
        // Delete old type
        unset( $custom_types[$data['wpcf-post-type']] );
        $data['wpcf-post-type'] = $post_type;
    } else {
        // Set protected data
        $protected_data_check = !empty( $custom_types[$post_type] ) ? $custom_types[$post_type] : array();
    }

    // Check if active
    if ( isset( $custom_types[$post_type]['disabled'] ) ) {
        $data['disabled'] = $custom_types[$post_type]['disabled'];
    }

    // Sync taxes with custom taxes
    if ( !empty( $data['taxonomies'] ) ) {
        $taxes = get_option( 'wpcf-custom-taxonomies', array() );
        foreach ( $taxes as $id => $tax ) {
            if ( array_key_exists( $id, $data['taxonomies'] ) ) {
                $taxes[$id]['supports'][$data['slug']] = 1;
            } else {
                unset( $taxes[$id]['supports'][$data['slug']] );
            }
        }
        update_option( 'wpcf-custom-taxonomies', $taxes );
    }

    // Preserve protected data
    foreach ( $protected_data_check as $key => $value ) {
        if ( strpos( $key, '_' ) !== 0 ) {
            unset( $protected_data_check[$key] );
        }
    }

    /**
     * set last edit time
     */
    $data[TOOLSET_EDIT_LAST] = time();

    // Merging protected data
    $custom_types[$post_type] = array_merge( $protected_data_check, $data );

    update_option( 'wpcf-custom-types', $custom_types );

    // WPML register strings
    wpcf_custom_types_register_translation( $post_type, $data );

    wpcf_admin_message_store(
            apply_filters( 'types_message_custom_post_type_saved',
                    __( 'Custom post type saved', 'wpcf' ), $data, $update ),
            'custom'
    );

    // Flush rewrite rules
    flush_rewrite_rules();

    do_action( 'wpcf_custom_types_save', $data );

    // Redirect
    wp_redirect(
        add_query_arg(
            array(
                'page' => 'wpcf-edit-type',
                'wpcf-post-type' => $post_type,
                'wpcf-rewrite' => 1,
                'wpcf-message' => 'view',
            ),
            admin_url( 'admin.php' )
        )
    );
    die();
}

/**
 * components
 */

/**
 * save button
 */
function wpcf_admin_metabox_submitdiv($cf)
{
    $button_text = __( 'Save Custom Post', 'wpcf' );
    return wpcf_admin_common_metabox_save($cf, $button_text);
}

/**
 * Visibility
 */
function wpcf_admin_metabox_wpcf_visibility($ct)
{
    $form = array();
    $form['table-2-open'] = wpcf_admin_metabox_begin(__( 'Visibility', 'wpcf' ), 'wpcf_visibility', 'wpcf-types-form-visibility-table', false);
    $form['public'] = array(
        '#type' => 'radios',
        '#name' => 'ct[public]',
        '#options' => array(
            __( 'Make this type public (will appear in the WordPress Admin menu)', 'wpcf' ) => 'public',
            __( 'Hidden - users cannot directly edit data in this type', 'wpcf' ) => 'hidden',
        ),
        '#default_value' => (isset( $ct['public'] ) && strval( $ct['public'] ) == 'hidden') ? 'hidden' : 'public',
        '#inline' => true,
    );
    $hidden = (isset( $ct['public'] ) && strval( $ct['public'] ) == 'hidden') ? ' class="hidden"' : '';
    $form['menu_position'] = array(
        '#type' => 'textfield',
        '#name' => 'ct[menu_position]',
        '#title' => __( 'Menu position', 'wpcf' ),
        '#value' => isset( $ct['menu_position'] ) ? $ct['menu_position'] : '',
        '#validate' => array('number' => array('value' => true)),
        '#inline' => true,
        '#pattern' => '<BEFORE><p><LABEL><ELEMENT><ERROR></p><AFTER>',
        '#before' => '<div' . $hidden . ' id="wpcf-types-form-visiblity-toggle">',
    );
    $form['menu_icon'] = array(
        '#type' => 'textfield',
        '#name' => 'ct[menu_icon]',
        '#title' => __( 'Menu icon', 'wpcf' ),
        '#description' => __( 'The url to the icon to be used for this menu. Default: null - defaults to the posts icon.', 'wpcf' ),
        '#value' => isset( $ct['menu_icon'] ) ? $ct['menu_icon'] : '',
        '#inline' => true,
        '#pattern' => '<BEFORE><p><LABEL><ELEMENT><ERROR></p><AFTER>',
        '#after' => '</div>',
    );
    /**
     * dashboard glance option to show counters on admin dashbord widget
     */
    $form['dashboard_glance'] = array(
        '#type' => 'checkbox',
        '#before' => sprintf('<h4>%s</h4>', __( 'Show in Right Now', 'wpcf' )),
        '#name' => 'ct[dashboard_glance]',
        '#title' => __( 'Show number of entries on "At a Glance" admin widget.', 'wpcf' ),
        '#default_value' => !empty( $ct['dashboard_glance'] ),
    );
    $form['table-2-close'] = wpcf_admin_metabox_end();
    return $form;
}

    /**
     * Taxonomies
     */
function wpcf_admin_metabox_taxonomies($ct)
{
    $form = array();
    $taxonomies = get_taxonomies( '', 'objects' );
    $options = array();

    foreach ( $taxonomies as $category_slug => $category ) {
        if ( $category_slug == 'nav_menu' || $category_slug == 'link_category'
                || $category_slug == 'post_format' ) {
            continue;
        }
        $options[$category_slug]['#name'] = 'ct[taxonomies][' . $category_slug . ']';
        $options[$category_slug]['#title'] = $category->labels->name;
        $options[$category_slug]['#default_value'] = !empty( $ct['taxonomies'][$category_slug] );
        $options[$category_slug]['#inline'] = true;
        $options[$category_slug]['#before'] = '<li>';
        $options[$category_slug]['#after'] = '</li>';
        if ( is_rtl() ) {
            $options[$category_slug]['#before'] = '<div style="float:right;margin-left:10px;">';
            $options[$category_slug]['#after'] .= '</div>';
        }
    }

    $form['table-3-open'] = wpcf_admin_metabox_begin(__( 'Select Taxonomies', 'wpcf' ), 'taxonomies', 'wpcf-types-form-taxonomies-table', false);
    $form['taxonomies'] = array(
        '#type' => 'checkboxes',
        '#options' => $options,
        '#description' => __( 'Registered taxonomies that will be used with this post type.', 'wpcf' ),
        '#name' => 'ct[taxonomies]',
        '#inline' => true,
        '#before' => '<ul>',
        '#after' => '</ul>',
    );
    $form['table-3-close'] = wpcf_admin_metabox_end(false);
    return $form;
}

    /**
     * Labels
     */
function wpcf_admin_metabox_labels($ct)
{
    $form = array();
    $form['table-4-open'] = wpcf_admin_metabox_begin(__( 'Labels', 'wpcf' ), 'labels', 'wpcf-types-form-table');
    $labels = array(
        'add_new' => array('title' => __( 'Add New', 'wpcf' ), 'description' => __( 'The add new text. The default is Add New for both hierarchical and non-hierarchical types.',
                    'wpcf' )),
        'add_new_item' => array('title' => __( 'Add New %s', 'wpcf' ), 'description' => __( 'The add new item text. Default is Add New Post/Add New Page.',
                    'wpcf' )),
//        'edit' => array('title' => __('Edit', 'wpcf'), 'description' => __('The edit item text. Default is Edit Post/Edit Page.', 'wpcf')),
        'edit_item' => array('title' => __( 'Edit %s', 'wpcf' ), 'description' => __( 'The edit item text. Default is Edit Post/Edit Page.',
                    'wpcf' )),
        'new_item' => array('title' => __( 'New %s', 'wpcf' ), 'description' => __( 'The view item text. Default is View Post/View Page.',
                    'wpcf' )),
//        'view' => array('title' => __('View', 'wpcf'), 'description' => __('', 'wpcf')),
        'view_item' => array('title' => __( 'View %s', 'wpcf' ), 'description' => __( 'The view item text. Default is View Post/View Page.',
                    'wpcf' )),
        'search_items' => array('title' => __( 'Search %s', 'wpcf' ), 'description' => __( 'The search items text. Default is Search Posts/Search Pages.',
                    'wpcf' )),
        'not_found' => array('title' => __( 'No %s found', 'wpcf' ), 'description' => __( 'The not found text. Default is No posts found/No pages found.',
                    'wpcf' )),
        'not_found_in_trash' => array('title' => __( 'No %s found in Trash',
                    'wpcf' ), 'description' => __( 'The not found in trash text. Default is No posts found in Trash/No pages found in Trash.',
                    'wpcf' )),
        'parent_item_colon' => array('title' => __( 'Parent text', 'wpcf' ), 'description' => __( "The parent text. This string isn't used on non-hierarchical types. In hierarchical ones the default is Parent Page.",
                    'wpcf' )),
        'all_items' => array('title' => __( 'All items', 'wpcf' ), 'description' => __( 'The all items text used in the menu. Default is the Name label.',
                    'wpcf' )),
    );
    foreach ( $labels as $name => $data ) {
        $form['labels-' . $name] = array(
            '#type' => 'textfield',
            '#name' => 'ct[labels][' . $name . ']',
            '#title' => ucwords( str_replace( '_', ' ', $name ) ),
            '#description' => $data['description'],
            '#value' => empty($ct['slug'])? $data['title']:(isset( $ct['labels'][$name] ) ? $ct['labels'][$name] : ''),
            '#inline' => true,
            '#pattern' => '<tr><td><LABEL></td><td><ELEMENT></td><td><DESCRIPTION></td>',
        );
    }
    $form['table-4-close'] = wpcf_admin_metabox_end();
    return $form;
}

    /**
     * Display Sections
     */
function wpcf_admin_metabox_display_sections($ct)
{
    $form = array();
    $form['table-5-open'] = wpcf_admin_metabox_begin(__( 'Display Sections', 'wpcf' ), 'display_sections', 'wpcf-types-form-supports-table');
    $options = array(
        'title' => array(
            '#name' => 'ct[supports][title]',
            '#default_value' => !empty( $ct['supports']['title'] ),
            '#title' => __( 'Title', 'wpcf' ),
            '#description' => __( 'Text input field to create a post title.',
                    'wpcf' ),
            '#inline' => true,
            '#id' => 'wpcf-supports-title',
        ),
        'editor' => array(
            '#name' => 'ct[supports][editor]',
            '#default_value' => !empty( $ct['supports']['editor'] ),
            '#title' => __( 'Editor', 'wpcf' ),
            '#description' => __( 'Content input box for writing.', 'wpcf' ),
            '#inline' => true,
            '#id' => 'wpcf-supports-editor',
        ),
        'comments' => array(
            '#name' => 'ct[supports][comments]',
            '#default_value' => !empty( $ct['supports']['comments'] ),
            '#title' => __( 'Comments', 'wpcf' ),
            '#description' => __( 'Ability to turn comments on/off.', 'wpcf' ),
            '#inline' => true,
        ),
        'trackbacks' => array(
            '#name' => 'ct[supports][trackbacks]',
            '#default_value' => !empty( $ct['supports']['trackbacks'] ),
            '#title' => __( 'Trackbacks', 'wpcf' ),
            '#description' => __( 'Ability to turn trackbacks and pingbacks on/off.',
                    'wpcf' ),
            '#inline' => true,
        ),
        'revisions' => array(
            '#name' => 'ct[supports][revisions]',
            '#default_value' => !empty( $ct['supports']['revisions'] ),
            '#title' => __( 'Revisions', 'wpcf' ),
            '#description' => __( 'Allows revisions to be made of your post.',
                    'wpcf' ),
            '#inline' => true,
        ),
        'author' => array(
            '#name' => 'ct[supports][author]',
            '#default_value' => !empty( $ct['supports']['author'] ),
            '#title' => __( 'Author', 'wpcf' ),
            '#description' => __( 'Displays a dropdown menu for changing the post author.',
                    'wpcf' ),
            '#inline' => true,
        ),
        'excerpt' => array(
            '#name' => 'ct[supports][excerpt]',
            '#default_value' => !empty( $ct['supports']['excerpt'] ),
            '#title' => __( 'Excerpt', 'wpcf' ),
            '#description' => __( 'A text area for writing a custom excerpt.',
                    'wpcf' ),
            '#inline' => true,
        ),
        'thumbnail' => array(
            '#name' => 'ct[supports][thumbnail]',
            '#default_value' => !empty( $ct['supports']['thumbnail'] ),
            '#title' => __( 'Thumbnail', 'wpcf' ),
            '#description' => __( 'Add a box for uploading a featured image.',
                    'wpcf' ),
            '#inline' => true,
        ),
        'custom-fields' => array(
            '#name' => 'ct[supports][custom-fields]',
            '#default_value' => !empty( $ct['supports']['custom-fields'] ),
            '#title' => __( 'custom-fields', 'wpcf' ),
            '#description' => __( 'Custom fields input area.', 'wpcf' ),
            '#inline' => true,
        ),
        'page-attributes' => array(
            '#name' => 'ct[supports][page-attributes]',
            '#default_value' => !empty( $ct['supports']['page-attributes'] ),
            '#title' => __( 'page-attributes', 'wpcf' ),
            '#description' => __( 'Menu order, hierarchical must be true to show Parent option',
                    'wpcf' ),
            '#inline' => true,
        ),
        'post-formats' => array(
            '#name' => 'ct[supports][post-formats]',
            '#default_value' => !empty( $ct['supports']['post-formats'] ),
            '#title' => __( 'post-formats', 'wpcf' ),
            '#description' => sprintf( __( 'Add post formats, see %sPost Formats%s',
                            'wpcf' ),
                    '<a href="http://codex.wordpress.org/Post_Formats" title="Post Formats" target="_blank">',
                    '</a>' ),
            '#inline' => true,
        ),
    );
    $form['supports'] = array(
        '#type' => 'checkboxes',
        '#options' => $options,
        '#name' => 'ct[supports]',
        '#inline' => true,
    );
    $form['table-5-close'] = wpcf_admin_metabox_end();
    return $form;
}

    /**
     * Options
     */
function wpcf_admin_metabox_options($ct)
{
    $form = array();
    $form['table-6-open'] = wpcf_admin_metabox_begin( __( 'Options', 'wpcf' ), 'options', 'wpcf-types-form-table');
    $form['rewrite-enabled'] = array(
        '#type' => 'checkbox',
        '#title' => __( 'Rewrite', 'wpcf' ),
        '#name' => 'ct[rewrite][enabled]',
        '#description' => __( 'Rewrite permalinks with this format. False to prevent rewrite. Default: true and use post type as slug.',
                'wpcf' ),
        '#default_value' => !empty( $ct['rewrite']['enabled'] ),
        '#inline' => true,
    );
    $form['rewrite-custom'] = array(
        '#type' => 'radios',
        '#name' => 'ct[rewrite][custom]',
        '#options' => array(
            __( 'Use the normal WordPress URL logic', 'wpcf' ) => 'normal',
            __( 'Use a custom URL format', 'wpcf' ) => 'custom',
        ),
        '#default_value' => empty( $ct['rewrite']['custom'] ) || $ct['rewrite']['custom'] != 'custom' ? 'normal' : 'custom',
        '#inline' => true,
        '#after' => '<br />',
    );
    $hidden = empty( $ct['rewrite']['custom'] ) || $ct['rewrite']['custom'] != 'custom' ? ' class="hidden"' : '';
    $form['rewrite-slug'] = array(
        '#type' => 'textfield',
        '#name' => 'ct[rewrite][slug]',
        '#description' => __( 'Optional.', 'wpcf' ) . ' ' . __( "Prepend posts with this slug - defaults to post type's name.",
                'wpcf' ),
        '#value' => isset( $ct['rewrite']['slug'] ) ? $ct['rewrite']['slug'] : '',
        '#inline' => true,
        '#before' => '<div id="wpcf-types-form-rewrite-toggle"' . $hidden . '>',
        '#after' => '</div>',
        '#validate' => array('rewriteslug' => array('value' => 'true')),
    );
    $form['rewrite-with_front'] = array(
        '#type' => 'checkbox',
        '#title' => __( 'Allow permalinks to be prepended with front base',
                'wpcf' ),
        '#name' => 'ct[rewrite][with_front]',
        '#description' => __( 'Example: if your permalink structure is /blog/, then your links will be: false->/news/, true->/blog/news/.',
                'wpcf' ) . ' ' . __( 'Defaults to true.', 'wpcf' ),
        '#default_value' => !empty( $ct['rewrite']['with_front'] ),
        '#inline' => true,
    );
    $form['rewrite-feeds'] = array(
        '#type' => 'checkbox',
        '#name' => 'ct[rewrite][feeds]',
        '#title' => __( 'Feeds', 'wpcf' ),
        '#description' => __( 'Defaults to has_archive value.', 'wpcf' ),
        '#default_value' => !empty( $ct['rewrite']['feeds'] ),
        '#value' => 1,
        '#inline' => true,
    );
    $form['rewrite-pages'] = array(
        '#type' => 'checkbox',
        '#name' => 'ct[rewrite][pages]',
        '#title' => __( 'Pages', 'wpcf' ),
        '#description' => __( 'Defaults to true.', 'wpcf' ),
        '#default_value' => !empty( $ct['rewrite']['pages'] ),
        '#value' => 1,
        '#inline' => true,
    );
    $show_in_menu_page = isset( $ct['show_in_menu_page'] ) ? $ct['show_in_menu_page'] : '';
    $hidden = !empty( $ct['show_in_menu'] ) ? '' : ' class="hidden"';
    $form['vars'] = array(
        '#type' => 'checkboxes',
        '#name' => 'ct[vars]',
        '#inline' => true,
        '#options' => array(
            'has_archive' => array(
                '#name' => 'ct[has_archive]',
                '#default_value' => !empty( $ct['has_archive'] ),
                '#title' => __( 'has_archive', 'wpcf' ),
                '#description' => __( 'Allow custom post type to have index page.',
                        'wpcf' ) . '<br />' . __( 'Default: not set.', 'wpcf' ),
                '#inline' => true,
            ),
            'show_in_menu' => array(
                '#name' => 'ct[show_in_menu]',
                '#default_value' => !empty( $ct['show_in_menu'] ),
                '#title' => __( 'show_in_menu', 'wpcf' ),
                '#description' => __( 'Whether to show the post type in the admin menu and where to show that menu. Note that show_ui must be true.',
                        'wpcf' ) . '<br />' . __( 'Default: null.', 'wpcf' ),
                '#after' => '<div id="wpcf-types-form-showinmenu-toggle"' . $hidden . '><input type="text" name="ct[show_in_menu_page]" style="width:50%;" value="' . $show_in_menu_page . '" /><div class="description wpcf-form-description wpcf-form-description-checkbox description-checkbox">' . __( 'Optional.',
                        'wpcf' ) . ' ' . __( "Top level page like 'tools.php' or 'edit.php?post_type=page'",
                        'wpcf' ) . '</div></div>',
                '#inline' => true,
            ),
            'show_ui' => array(
                '#name' => 'ct[show_ui]',
                '#default_value' => !empty( $ct['show_ui'] ),
                '#title' => __( 'show_ui', 'wpcf' ),
                '#description' => __( 'Generate a default UI for managing this post type.',
                        'wpcf' ) . '<br />' . __( 'Default: value of public argument.',
                        'wpcf' ),
                '#inline' => true,
            ),
            'publicly_queryable' => array(
                '#name' => 'ct[publicly_queryable]',
                '#default_value' => !empty( $ct['publicly_queryable'] ),
                '#title' => __( 'publicly_queryable', 'wpcf' ),
                '#description' => __( 'Whether post_type queries can be performed from the front end.',
                        'wpcf' ) . '<br />' . __( 'Default: value of public argument.',
                        'wpcf' ),
                '#inline' => true,
            ),
            'exclude_from_search' => array(
                '#name' => 'ct[exclude_from_search]',
                '#default_value' => !empty( $ct['exclude_from_search'] ),
                '#title' => __( 'exclude_from_search', 'wpcf' ),
                '#description' => __( 'Whether to exclude posts with this post type from search results.',
                        'wpcf' ) . '<br />' . __( 'Default: value of the opposite of the public argument.',
                        'wpcf' ),
                '#inline' => true,
            ),
            'hierarchical' => array(
                '#name' => 'ct[hierarchical]',
                '#default_value' => !empty( $ct['hierarchical'] ),
                '#title' => __( 'hierarchical', 'wpcf' ),
                '#description' => __( 'Whether the post type is hierarchical. Allows Parent to be specified.',
                        'wpcf' ) . '<br />' . __( 'Default: false.', 'wpcf' ),
                '#inline' => true,
            ),
            'can_export' => array(
                '#name' => 'ct[can_export]',
                '#default_value' => !empty( $ct['can_export'] ),
                '#title' => __( 'can_export', 'wpcf' ),
                '#description' => __( 'Can this post_type be exported.', 'wpcf' ) . '<br />' . __( 'Default: true.',
                        'wpcf' ),
                '#inline' => true,
            ),
            'show_in_nav_menus' => array(
                '#name' => 'ct[show_in_nav_menus]',
                '#default_value' => !empty( $ct['show_in_nav_menus'] ),
                '#title' => __( 'show_in_nav_menus', 'wpcf' ),
                '#description' => __( 'Whether post_type is available for selection in navigation menus.',
                        'wpcf' ) . '<br />' . __( 'Default: value of public argument.',
                        'wpcf' ),
                '#inline' => true,
            ),
        ),
    );
    $query_var = isset( $ct['query_var'] ) ? $ct['query_var'] : '';
    $hidden = !empty( $ct['query_var_enabled'] ) ? '' : ' class="hidden"';
    $form['query_var'] = array(
        '#type' => 'checkbox',
        '#name' => 'ct[query_var_enabled]',
        '#title' => 'query_var',
        '#description' => __( 'False to prevent queries, or string value of the query var to use for this post type.',
                'wpcf' ) . '<br />' . __( 'Default: true - set to $post_type.',
                'wpcf' ),
        '#default_value' => !empty( $ct['query_var_enabled'] ),
        '#after' => '<div id="wpcf-types-form-queryvar-toggle"' . $hidden . '><input type="text" name="ct[query_var]" value="' . $query_var . '" style="width:50%;" /><div class="description wpcf-form-description wpcf-form-description-checkbox description-checkbox">' . __( 'Optional',
                'wpcf' ) . '. ' . __( 'String to customize query var', 'wpcf' ) . '</div></div>',
        '#inline' => true,
    );
    $form['permalink_epmask'] = array(
        '#type' => 'textfield',
        '#name' => 'ct[permalink_epmask]',
        '#title' => __( 'Permalink epmask', 'wpcf' ),
        '#description' => sprintf( __( 'Default value EP_PERMALINK. More info here %s.',
                        'wpcf' ),
                '<a href="http://core.trac.wordpress.org/ticket/12605" target="_blank">link</a>' ),
        '#value' => isset( $ct['permalink_epmask'] ) ? $ct['permalink_epmask'] : '',
        '#inline' => true,
    );
    $form['table-6-close'] = wpcf_admin_metabox_end();
    return $form;
}
