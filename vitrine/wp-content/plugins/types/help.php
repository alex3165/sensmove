<?php
/*
 * Plugin contextual help
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/help.php $
 * $LastChangedDate: 2014-05-29 08:44:10 +0000 (Thu, 29 May 2014) $
 * $LastChangedRevision: 922956 $
 * $LastChangedBy: iworks $
 *
 */

/**
 * Returns contextual help.
 * 
 * @param type $page
 * @param type $contextual_help 
 */
function wpcf_admin_help($page, $contextual_help) {
    $help = '';
    switch ($page) {
        // Custom Fields (list)
        case 'custom_fields':
            $help .= ''
                    . ''
                    . __("Types plugin organizes custom fields in groups. Once you create a group, you can add the fields to it and control to what content it belongs.",
                            'wpcf')
                    . '<br /><br />'
                    . sprintf(__('You can read more about Custom Fields in this tutorial: %s.', 
                            'wpcf'),
                              '<a href="http://wp-types.com/user-guides/using-custom-fields/" target="_blank">http://wp-types.com/user-guides/using-custom-fields/ &raquo;</a>')
                    . '<br /><br />'
                    . __("On this page you can see your current custom field groups, as well as information about which post types and taxonomies they are attached to, and whether they are active or not.",
                            'wpcf')
                    . '<br /><br />'
                    . __('You have the following options:', 'wpcf')
                    . '<ul><li>'
                    . __('<strong>Add a Custom Fields Group:</strong> Use this to add a new custom fields group which can be attached to a post type',
                            'wpcf')
                    . '</li><li>'
                    . __('<strong>Edit:</strong> Click to edit the custom field group',
                            'wpcf')
                    . '</li><li>'
                    . __('<strong>Deactivate:</strong> Click to deactivate a custom field group (this can be re-activated at a later date)',
                            'wpcf')
                    . '</li><li>'
                    . __('<strong>Delete Permanently:</strong> Click to delete a custom field group. <strong>Warning: This cannot be undone.</strong>',
                            'wpcf')
                    . '</li></ul>';
            break;

        // Custom Types and Taxonomies (list)
        case 'custom_types_and_taxonomies':
            $help .= __('Custom Post Types are user-defined content types. Custom Taxonomies are used to categorize your content.',
                            'wpcf')
                    . ' ' . sprintf(__('You can read more about Custom Post Types and Taxonomies in this tutorial. %s',
                            'wpcf'), '<a href="http://wp-types.com/learn/custom-post-types/" target="_blank">http://wp-types.com/learn/custom-post-types/ &raquo;</a>')
                    . '<br /><br />'
                    . __('This is the main admin page for your Custom Post Types and Taxonomies. It provides you with an overview of your data.',
                            'wpcf')
                    . '<br />'
                    . __('On this page you have the following options:', 'wpcf')
                    . '<ul><li>'
                    . __('<strong>Add Custom Post Type:</strong> Use to create a new Custom Post Type',
                            'wpcf')
                    . '</li><li>'
                    . __('<strong>Add Custom Taxonomy:</strong> Use to create a new Custom Taxonomy',
                            'wpcf')
                    . '</li><li>'
                    . __('<strong>Edit:</strong> Click to edit the settings of a Custom Post Type or Taxonomy',
                            'wpcf')
                    . '</li><li>'
                    . __('<strong>Deactivate:</strong> Click to deactivate a Custom Post Type or Taxonomy (this can be reactivated at a later date)',
                            'wpcf')
                    . '</li><li>'
                    . __('<strong>Delete:</strong> Click to delete a Custom Post Type or Taxonomy. <strong> Warning: This cannot be undone</strong> ',
                            'wpcf')
                    . '</li></ul>';
            break;

        // Import/Export page
        case 'import_export':
            $help .= __('Use this page to import and export custom post types, taxonomies and custom fields to and from Types.',
                            'wpcf')
                    . '<br /><br />'
                    . __('This is the main admin page for your Custom Post Types and Taxonomies. It provides you with an overview of your data.',
                            'wpcf')
                    . '<br />'
                    . __('On this page you have the following options:', 'wpcf')
                    . '<h3>' . __('Import', 'wpcf') . '</h3>'
                    . '<ul style="list-style-type:none;"><li style="list-style-type:none;">'
                    . __('<strong>Step 1:</strong> Upload an XML file or paste XML content directly into the text area.',
                            'wpcf')
                    . '</li><li style="list-style-type:none;">'
                    . __('<strong>Step 2:</strong> Select which custom post types, taxonomies and custom fields should be imported.',
                            'wpcf')
                    . '</li></ul>'
                    . '<h3>' . __('Export', 'wpcf') . '</h3>'
                    . __('Click Export to export data from Types as an XML file.',
                            'wpcf');
            break;

        // Add/Edit group form page
        case 'edit_group':
            $help .= __('This is the edit page for your Custom Fields Groups.',
                            'wpcf')
                    . '<br />'
                    . sprintf(__('You can read more about creating a Custom Fields Group here: %s',
                            'wpcf'), '<a href="http://wp-types.com/user-guides/using-custom-fields/" target="_blank">http://wp-types.com/user-guides/using-custom-fields/ &raquo;</a>')
                    . '<br /><br />'
                    . __('On this page you can create and edit your groups. To create a group, do the following:',
                            'wpcf')
                    . '<ol style="list-style-type:decimal;"><li style="list-style-type:decimal;">'
                    . __('Add a Title', 'wpcf')
                    . '</li><li style="list-style-type:decimal;">'
                    . __('Choose where to display your group. You can attach this to both default WordPress post types and Custom Post Types. (nb: you can also associate taxonomy terms with Custom Field Groups)',
                            'wpcf')
                    . '</li><li style="list-style-type:decimal;">'
                    . __('To add a field click on the field you desire under “Available Fields” on the right hand side of your screen. This will be added to your Custom Field Group',
                            'wpcf')
                    . '</li><li style="list-style-type:decimal;">'
                    . __('Add information about your Custom Field', 'wpcf')
                    . '</li></ol>'
                    . '<h3>' . __('Tips', 'wpcf') . '</h3>'
                    . '<ul><li>'
                    . __('To ensure a user completes a field, check the box for validation required',
                            'wpcf')
                    . '</li><li>'
                    . __('Once you have created a field it will be saved for future use under "User created fields"',
                            'wpcf')
                    . '</li><li>'
                    . __('You can drag and drop the order of your custom fields using the blue icon',
                            'wpcf')
                    . '</li></ul>';
            break;

        // Add/Edit custom type form page
        case 'edit_type':
            $help .= __('Use this page to create a WordPress post type. If you’d like to learn more about Custom Post Types you can read our detailed guide: <a href="http://wp-types.com/learn/custom-post-types/" target="_blank">http://wp-types.com/learn/custom-post-types/</a> or check out our tutorial on creating them with Types: <a href="http://wp-types.com/user-guides/create-a-custom-post-type/" target="_blank">http://wp-types.com/user-guides/create-a-custom-post-type/ &raquo;</a>',
                            'wpcf')
                    . '<br /><br />'
                    . '<strong>' . __('Name and Description', 'wpcf') . '</strong>'
                    . '<br />'
                    . __('Add a singular and plural name for your post type. You should also add a slug. This will be created from the post type name if none is added.',
                            'wpcf')
                    . '<br /><br />'
                    . '<strong>' . __('Visibility', 'wpcf') . '</strong>'
                    . '<br /><br />'
                    . __('Determine whether your post type will be visible on the admin menu to your users.',
                            'wpcf')
                    . '<br /><br />'
                    . __('You can also adjust the menu position. The default position is 20, which means your post type will appear under “Pages”. You can find more information about menu positioning in the WordPress Codex. <a href="http://codex.wordpress.org/Function_Reference/register_post_type#Parameters" target="_blank">http://codex.wordpress.org/Function_Reference/register_post_type#Parameters</a>',
                            'wpcf')
                    . '<br /><br />'
                    . __('The default post type icon is the pushpin icon that appears beside WordPress posts. You can change this by adding your own icon of 16px x 16px.',
                            'wpcf')
                    . '<br /><br />'
                    . '<strong>' . __('Select Taxonomies', 'wpcf') . '</strong>'
                    . '<br /><br />'
                    . __('Choose which taxonomies are to be associated with this post type.',
                            'wpcf')
                    . '<br /><br />'
                    . '<strong>' . __('Labels', 'wpcf') . '</strong>'
                    . '<br /><br />'
                    . __('Labels are the text that is attached to your custom post type name. Examples of them in use are “Add New Post” (where “Add New” is the label”) and “Edit Post” (where “Edit” is the label). In normal circumstances the defaults will suffice.',
                            'wpcf')
                    . '<br /><br />'
                    . '<strong>' . __('Display Sections', 'wpcf') . '</strong>'
                    . '<br /><br />'
                    . __('Choose which sections to display on your “Add New” page.',
                            'wpcf')
                    . '<br /><br />'
                    . '<strong>' . __('Advanced Settings', 'wpcf') . '</strong>'
                    . '<br /><br />'
                    . __('Advanced settings give you even more control over your custom post type. You can read in detail what all of these settings do on our tutorial.',
                            'wpcf');
            break;

        // Add/Edit custom taxonomy form page
        case 'edit_tax':
            $help .= __('You can use Custom Taxonomies to categorize your content. Read more about what they are on our website: <a href="http://wp-types.com/learn/custom-post-types/" target="_blank">http://wp-types.com/learn/custom-post-types/ &raquo;</a> or you can read our guide about how to set them up: <a href="http://wp-types.com/user-guides/create-custom-taxonomies/" target="_blank">http://wp-types.com/user-guides/create-custom-taxonomies/</a>',
                            'wpcf')
                    . '<br /><br />'
                    . '<strong>' . __('Name and Description', 'wpcf') . '</strong>'
                    . '<br /><br />'
                    . __('Add a singular and plural name for your taxonomy. You should also add a slug. This will be created from the taxonomy name if none is added.',
                            'wpcf')
                    . '<br /><br />'
                    . '<strong>' . __('Visibility', 'wpcf') . '</strong>'
                    . '<br /><br />'
                    . __('Determine whether your post type will be visible on the admin menu to your users.',
                            'wpcf')
                    . '<br /><br />'
                    . '<strong>' . __('Select Post Types', 'wpcf') . '</strong>'
                    . '<br /><br />'
                    . __('Choose which post types this taxonomy should be associated with.',
                            'wpcf')
                    . '<br /><br />'
                    . '<strong>' . __('Labels', 'wpcf') . '</strong>'
                    . '<br /><br />'
                    . __('Labels are the text that is attached to your custom post type name. Examples of them in use are “Add New Post” (where “Add New” is the label”) and “Edit Post” (where “Edit” is the label). In normal circumstances the defaults will suffice.',
                            'wpcf')
                    . '<br /><br />'
                    . '<strong>' . __('Display Sections', 'wpcf') . '</strong>'
                    . '<br /><br />'
                    . __('Choose which sections to display on your “Add New” page.',
                            'wpcf')
                    . '<br /><br />'
                    . '<strong>' . __('Advanced Settings', 'wpcf') . '</strong>'
                    . '<br /><br />'
                    . __('Advanced settings give you even more control over your custom post type. You can read in detail what all of these settings do on our tutorial.',
                            'wpcf')

            ;
            break;
    }
    return wpautop($help);
}
