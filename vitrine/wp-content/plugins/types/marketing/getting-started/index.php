<?php
/**
 *
 *
 */
include_once WPCF_ABSPATH.'/classes/class.wpcf-marketing-messages.php';
$marketing = new WPCF_Types_Marketing_Messages();
$marketing->update_options();
$content = $marketing->get_content();

if (
    empty($content)
    || ( isset($_GET['kind']) && !isset($_POST[$marketing->get_option_name()]) )
) {
    $marketing->delete_option_kind();
?>
<div class="wrap wp-types select-kind">
    <h2><?php _e('What kind of site are you building?', 'wcpf') ?></h2>
    <?php settings_errors(); ?>
    <p><?php _e('Types plugin includes a lot of features and there are many possibilities. By selecting what kind of site you are building, you allow Types to advise you about what features are needed and how to use them.', 'wcpf'); ?></p>
    <form method="post">
        <?php wp_nonce_field('update', 'marketing'); ?>
        <?php $marketing->kind_list(); ?>
        <a href="#" id="wcpf-getting-started-button" class="button"><?php _e('Continue', 'wcpf'); ?></a>
    </form>
</div>
<?php } else {
    echo '<div class="wrap wp-types about-wrap">';
    echo $content;
    echo '</div>';
}
?>
