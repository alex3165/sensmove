<?php
/*
 * Fields and groups list functions
 */

/**
 * Renders 'widefat' table.
 */
function wpcf_admin_usermeta_list()
{
    include_once dirname(__FILE__).'/classes/class.wpcf.user.fields.list.table.php';
    //Create an instance of our package class...
    $listTable = new WPCF_User_Fields_List_Table();
    //Fetch, prepare, sort, and filter our data...
    $listTable->prepare_items();
    ?>
        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form id="usermeta-filter" method="post">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']); ?>" />
            <?php $listTable->search_box(__('Search user fields', 'wcpf'), 'search_id'); ?>
            <!-- Now we can render the completed list table -->
            <?php $listTable->display() ?>
        </form>
    <?php
    do_action('wpcf_groups_list_table_after');
}
