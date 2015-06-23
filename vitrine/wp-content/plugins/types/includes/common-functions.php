<?php
/**
 *
 * Custom types form - common functions
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/includes/common-functions.php $
 * $LastChangedDate: 2015-01-16 14:28:15 +0000 (Fri, 16 Jan 2015) $
 * $LastChangedRevision: 1069430 $
 * $LastChangedBy: iworks $
 *
 */

function wpcf_admin_metabox_end($table_show = true)
{
    $markup = '';
    if ( $table_show ) {
        $markup .= '</td></tr></tbody></table>';
    }
    $markup .= '</div></div>';
    return array(
        '#type' => 'markup',
        '#markup' => $markup,
    );
}

function wpcf_admin_metabox_begin($title, $id = false, $table_id = false, $table_show = true)
{
    $screen = get_current_screen();
    $markup = sprintf(
        '<div class="postbox %s" %s><div title="%s" class="handlediv"><br></div><h3 class="hndle">%s</h3><div class="inside">',
        postbox_classes($id, $screen->id),
        $id? sprintf('id="%s"', $id):'',
        __('Click to toggle'),
        $title
    );
    if ( $table_show ) {
        $markup .= sprintf(
            '<table %s class="wpcf-types-form-table widefat"><tbody><tr><td>',
            $table_id? sprintf( 'id="%s"', $table_id):''
        );
    }
    return array(
        '#type' => 'markup',
        '#markup' => $markup,
    );
}

function wpcf_admin_common_metabox_save($cf, $button_text)
{
    $form = array();
    $form['submit-open'] = wpcf_admin_metabox_begin(__( 'Save', 'wpcf' ), 'submitdiv', false, false);
    $form['submit-div-open'] = array(
        '#type' => 'markup',
        '#markup' => '<div class="submitbox" id="submitpost"><div id="major-publishing-actions"><div id="publishing-action"><span class="spinner"></span>',
    );
    $form['submit'] = array(
        '#type' => 'submit',
        '#name' => 'submit',
        '#value' => $button_text,
        '#attributes' => array('class' => 'button-primary wpcf-disabled-on-submit'),
    );
    $form['submit-div-close'] = array(
        '#type' => 'markup',
        '#markup' => '</div><div class="clear"></div></div></div>',
    );
    $form['submit-close'] = wpcf_admin_metabox_end();
    return $form;
}
