<?php
/*
 * Add user screen functions.
 * Included on add_action('load-user-new.php') hook.
 */
add_action( 'in_admin_footer', 'wpcf_usermeta_add_user_templates' );
add_action( 'user_register', 'wpcf_usermets_add_user_submit' );

/**
 * Renders templates on bottom of screen.
 */
function wpcf_usermeta_add_user_templates() {

    ?>
    <script type="text/html" id="tpl-wpcf-usermeta-add-user">
        <?php wpcf_admin_userprofile_init( -1 ); ?>
    </script>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#createuser .submit').prepend($('#tpl-wpcf-usermeta-add-user').html());
        });
    </script>
    <?php
    wpcf_form_render_js_validation( '#createuser' );
}

/**
 * Hooks to 'user_register'
 * @param type $user_id
 */
function wpcf_usermets_add_user_submit( $user_id ) {
    if ( isset( $_POST['wpcf'] ) ) {
        wpcf_admin_userprofilesave_init( $user_id );
    }
}

/**
 * Init function.
 */
function wpcf_usermeta_add_user_screen_init() {
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/usermeta-post.php';
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/usermeta.php';
    require_once WPCF_EMBEDDED_INC_ABSPATH . '/fields-post.php';
    remove_action( 'admin_footer', 'wpcf_admin_profile_js_validation' );
}
