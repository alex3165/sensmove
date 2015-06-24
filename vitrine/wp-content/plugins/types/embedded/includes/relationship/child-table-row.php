<?php
/*
 * Child table row
 */

?>

<tr id="types-child-row-<?php echo $child_id; ?>">
    <?php
    foreach ( $row as $td ) {

        ?>
        <td><?php echo $td; ?></td>
        <?php
    }

    ?>
    <td class="actions">

        <!--SAVE-->
        <a href="<?php
    echo admin_url( 'admin-ajax.php?action=wpcf_ajax&amp;'
            . 'wpcf_action=pr_save_child_post&amp;post_type_parent='
            . $this->parent_post_type
            . '&amp;post_id=' . $child_id
            . '&amp;parent_id=' . $this->parent->ID
            . '&amp;post_type_child='
            . $this->child_post_type . '&_wpnonce=' . wp_create_nonce( 'pr_save_child_post' ) );

    ?>" class="wpcf-pr-save-ajax button-secondary"><?php
           echo __( 'Save', 'wpcf' );

    ?></a>
        <!--EDIT-->
        <?php
        if ( strpos( $this->child->ID, 'new_' ) === false ):

            ?><a href="<?php echo get_edit_post_link( $child_id ); ?>" class="button-secondary"><?php
        echo __( 'Edit', 'wpcf' );

            ?></a>
            <a href="<?php
            echo admin_url( 'admin-ajax.php?action=wpcf_ajax&amp;'
                    . 'wpcf_action=pr_delete_child_post'
                    . '&amp;post_id=' . $child_id
                    . '&amp;parent_id=' . $this->parent->ID
                    . '&_wpnonce=' . wp_create_nonce( 'pr_delete_child_post' ) );

            ?>" class="wpcf-pr-delete-ajax button-secondary"><?php
           echo __( 'Delete', 'wpcf' );

            ?></a>
            <?php
        endif;

        // Trigger Conditional
        // TODO Move to conditional.php
        if ( defined( 'DOING_AJAX' ) && !defined( 'WPTOOLSET_FORMS_VERSION' ) ):

            ?>
            <script type="text/javascript">
                //<![CDATA[
                jQuery(document).ready(function(){
                    wpcfConditionalInit('#types-child-row-<?php echo $child_id; ?>');
                });
                //]]>
            </script>
            <?php
        endif;

        ?>
    </td>
</tr>
