<?php
/*
 * Custom Types and Taxonomies list functions
 */

function wpcf_admin_ctt_list_header()
{
    $custom_types = get_option('wpcf-custom-types', array());
    $custom_taxonomies = get_option('wpcf-custom-taxonomies', array());

    if (empty($custom_types) && empty($custom_taxonomies)) {
        echo '<p>'
        . __('Custom Post Types are user-defined content types. Custom Taxonomies are used to categorize your content.',
                'wpcf')
        . ' ' . __('You can read more about Custom Post Types and Taxonomies in this tutorial. <a href="http://wp-types.com/learn/custom-post-types/" target="_blank">http://wp-types.com/learn/custom-post-types/ &raquo;</a>',
                'wpcf')
        . '</p>';
    }
}

function wpcf_admin_custom_post_types_list()
{
    include_once dirname(__FILE__).'/classes/class.wpcf.custom.post.types.list.table.php';
    //Create an instance of our package class...
    $listTable = new WPCF_Custom_Post_Types_List_Table();
    //Fetch, prepare, sort, and filter our data...
    $listTable->prepare_items();
    ?>
        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form id="cpt-filter" method="post">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']); ?>" />
            <?php $listTable->search_box(__('Search custom posts', 'wcpf'), 'search_id'); ?>
            <!-- Now we can render the completed list table -->
            <?php $listTable->display() ?>
        </form>
    <?php
}

function wpcf_admin_custom_taxonomies_list()
{
    include_once dirname(__FILE__).'/classes/class.wpcf.custom.taxonomies.list.table.php';
    //Create an instance of our package class...
    $listTable = new WPCF_Custom_Taxonomies_List_Table();
    //Fetch, prepare, sort, and filter our data...
    $listTable->prepare_items();
    ?>
        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form id="ct-filter" method="post">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']); ?>" />
            <?php $listTable->search_box(__('Search custom taxonomies', 'wcpf'), 'search_id'); ?>
            <!-- Now we can render the completed list table -->
            <?php $listTable->display() ?>
        </form>
    <?php
}


