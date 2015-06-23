<?php
/*
 * Add here marketing messages
 * 
 * Hooks used
 */

add_action('wpcf_admin_page_init', 'wpcf_marketing_init');

/**
 * Enqueue styles and scripts
 */
function wpcf_marketing_init() {
    wp_enqueue_style('wpcf-marketing-congrats',
            WPCF_RELPATH . '/marketing/congrats-post-types/style.css', array(),
            WPCF_VERSION);
    wp_enqueue_script('wpcf-marketing-congrats',
            WPCF_RELPATH . '/marketing/congrats-post-types/js/jquery.wpcfnotif.js',
            array('jquery'), WPCF_VERSION);
}

add_filter('types_message_custom_post_type_saved',
        'types_marketing_message_custom_post_type_saved', 10, 3);

add_filter('types_message_custom_taxonomy_saved',
        'types_marketing_message_custom_taxonomy_saved', 10, 3);

add_filter('types_message_custom_fields_saved',
        'types_marketing_message_custom_fields_saved', 10, 3);
		
add_filter('types_message_usermeta_saved',
        'types_marketing_message_usermeta_saved', 10, 3);			

/*
 * 
 * 
 * 
 * Hooks per page
 */

function types_marketing_message_custom_post_type_saved($message, $data, $update) {
    $title = $data['labels']['name'];
    $type = 'post_type';
    ob_start();
    include WPCF_ABSPATH . '/marketing/congrats-post-types/index.php';
    $message = ob_get_contents();
    ob_end_clean();
    return $message;
}

function types_marketing_message_custom_taxonomy_saved($message, $data, $update) {
    $title = $data['labels']['singular_name'];
    $type = 'taxonomy';
    ob_start();
    include WPCF_ABSPATH . '/marketing/congrats-post-types/index.php';
    $message = ob_get_contents();
    ob_end_clean();
    return $message;
}

function types_marketing_message_custom_fields_saved($message, $title, $update) {
    $type = 'fields';
    ob_start();
    include WPCF_ABSPATH . '/marketing/congrats-post-types/index.php';
    $message = ob_get_contents();
    ob_end_clean();
    return $message;
}

function types_marketing_message_usermeta_saved($message, $title, $update) {
    $type = 'usermeta';
    ob_start();
    include WPCF_ABSPATH . '/marketing/congrats-post-types/index.php';
    $message = ob_get_contents();
    ob_end_clean();
    return $message;
}

