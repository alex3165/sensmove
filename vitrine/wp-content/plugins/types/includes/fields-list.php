<?php
/*
 * Fields and groups list functions
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/includes/fields-list.php $
 * $LastChangedDate: 2015-03-02 10:49:00 +0000 (Mon, 02 Mar 2015) $
 * $LastChangedRevision: 1103173 $
 * $LastChangedBy: iworks $
 *
 */

/**
 * Renders 'widefat' table.
 */
function wpcf_admin_fields_list()
{
    include_once dirname(__FILE__).'/classes/class.wpcf.custom.fields.list.table.php';
    //Create an instance of our package class...
    $listTable = new WPCF_Custom_Fields_List_Table();
    //Fetch, prepare, sort, and filter our data...
    $listTable->prepare_items();
    ?>
        <!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
        <form id="cf-filter" method="post">
            <!-- For plugins, we also need to ensure that the form posts back to our current page -->
            <input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']); ?>" />
            <?php $listTable->search_box(__('Search custom fields', 'wcpf'), 'search_id'); ?>
            <!-- Now we can render the completed list table -->
            <?php $listTable->display() ?>
        </form>
    <?php
    do_action('wpcf_groups_list_table_after');
}

