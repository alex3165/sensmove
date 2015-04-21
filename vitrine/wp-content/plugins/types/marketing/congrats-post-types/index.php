<?php
$marketing = new WPCF_Types_Marketing_Messages();
$show_documentation_link = false;
?>
<?php
if ( $top = $marketing->show_top($update) ) {
    echo '<div class="wpcf-notif">';
    echo $top;
    echo '</div>';
    } else {

        $message = false;

        switch( $type ) {
        case 'post_type':
            if ( $update ) {
                $message = __( 'Congratulations! Your custom post type %s was successfully updated.', 'wpcf' );
            } else {
                $message = __( 'congratulations! your new custom post type %s was successfully created.', 'wpcf' );
            }
            break;
        case 'fields':
            if ( $update) {
                $message = __( 'Congratulations! Your custom fields group %s was successfully updated.', 'wpcf' );
            } else {
                $message = __( 'Congratulations! Your new custom fields group %s was successfully created.', 'wpcf' );
            }
            break;
        case 'taxonomy':
            if ( $update) {
                $message = __( 'Congratulations! Your custom taxonomy %s was successfully updated.', 'wpcf' );
            } else {
                $message = __( 'Congratulations! Your new custom taxonomy %s was successfully created.', 'wpcf' );
            }
            break;
        case 'usermeta':
            if ( $update) {
                $message = __( 'Congratulations! Your user meta group %s was successfully updated.', 'wpcf' );
            } else {
                $message = __( 'Congratulations! Your new user meta group %s was successfully created.', 'wpcf' );
            }
            break;
        }
        $message = sprintf($message, sprintf('<strong>%s</strong>', $title));
        $marketing->update_message($message);
?>
<?php if ( $show_documentation_link ) { ?>
    <a href="javascript:void(0);" class="wpcf-button show <?php if ( $update ) echo 'wpcf-show'; else echo 'wpcf-hide'; ?>"><?php echo __( 'Show next steps and documentation' ); ?><span class="wpcf-button-arrow show"></span></a>
<?php } ?>
    <?php $class = $update ? ' wpcf-hide' : ' wpcf-show'; ?>
    <div class="wpcf-notif-dropdown<?php echo $class; ?>">
        <span><strong><?php echo __( 'Next, learn how to:' ); ?></strong></span>
        <?php if ( $type == 'post_type' ): ?>
            <ul>
                <li><a target="_blank" href="http://wp-types.com/documentation/user-guides/using-custom-fields/?utm_source=typesplugin&utm_medium=next-steps&utm_term=adding-fields&utm_campaign=types"><?php
        echo __( 'Enrich content using <strong>custom fields</strong>', 'wpcf' );

            ?> &raquo;</a></li>
                <li><a target="_blank" href="http://wp-types.com/documentation/user-guides/create-custom-taxonomies/?utm_source=typesplugin&utm_medium=next-steps&utm_term=using-taxonomy&utm_campaign=types"><?php
                    echo __( 'Organize content using <strong>taxonomy</strong>',
                            'wpcf' );

            ?> &raquo;</a></li>
                <li><a target="_blank" href="http://wp-types.com/documentation/user-guides/many-to-many-post-relationship/?utm_source=typesplugin&utm_medium=next-steps&utm_term=parent-child&utm_campaign=types"><?php
                    echo __( 'Connect post types as <strong>parents and children</strong>',
                            'wpcf' );

            ?> &raquo;</a></li>
                <li><a target="_blank" href="http://wp-types.com/documentation/user-guides/creating-wordpress-custom-post-archives/?utm_source=typesplugin&utm_medium=next-steps&utm_term=custom-post-archives&utm_campaign=types"><?php
                    echo __( 'Display custom post <strong>archives</strong>',
                            'wpcf' );

            ?> &raquo;</a></li>
            </ul>
        <?php elseif ( $type == 'taxonomy' ): ?>
            <ul>
                <li><a target="_blank" href="http://wp-types.com/documentation/user-guides/create-custom-taxonomies/?utm_source=typesplugin&utm_medium=next-steps&utm_term=using-taxonomy&utm_campaign=types"><?php
        echo __( 'Organize content using <strong>taxonomy</strong>', 'wpcf' );

            ?> &raquo;</a></li>
                <li><a target="_blank" href="http://wp-types.com/documentation/user-guides/creating-wordpress-custom-taxonomy-archives/?utm_source=typesplugin&utm_medium=next-steps&utm_term=custom-taxonomy-archives&utm_campaign=types"><?php
                    echo __( 'Display custom taxonomy <strong>archives</strong>',
                            'wpcf' );

            ?> &raquo;</a></li>
            </ul>
        <?php elseif ( $type == 'usermeta' ): ?>   
        	<ul>
                <li><a target="_blank" href="http://wp-types.com/documentation/user-guides/displaying-wordpress-user-fields/?utm_source=typesplugin&utm_medium=next-steps&utm_term=display-user-fields&utm_campaign=types"><?php
        echo __( 'Display user fields', 'wpcf' );

            ?> &raquo;</a></li>
            </ul> 
        <?php else: ?>
            <ul>
                <li><a target="_blank" href="http://wp-types.com/documentation/user-guides/displaying-wordpress-custom-fields/?utm_source=typesplugin&utm_medium=next-steps&utm_term=display-custom-fields&utm_campaign=types"><?php
        echo __( 'Display custom fields', 'wpcf' );

            ?> &raquo;</a></li>
                <li><a target="_blank" href="http://wp-types.com/documentation/user-guides/creating-groups-of-repeating-fields-using-fields-tables/?utm_source=typesplugin&utm_medium=next-steps&utm_term=repeating-fields-group&utm_campaign=types"><?php
                    echo __( 'Create groups of repeating fields', 'wpcf' );

            ?> &raquo;</a></li>
            </ul>
        <?php endif; ?>

        <div class="hr"></div>

        <span><strong><?php _e( 'Build complete sites without coding:' ); ?></strong></span>
        <ul>
            <li><a href="http://wp-types.com/documentation/user-guides/view-templates/?utm_source=typesplugin&utm_medium=next-steps&utm_term=single-pages&utm_campaign=types" target="_blank"><?php
        echo __( 'Design templates for single pages', 'wpcf' );

        ?> &raquo;</a></li>
            <li><a href="http://wp-types.com/documentation/user-guides/views/?utm_source=typesplugin&utm_medium=next-steps&utm_term=query-and-display&utm_campaign=types" target="_blank"><?php
                    echo __( 'Load and display custom content', 'wpfc' );

        ?> &raquo;</a></li>
        </ul>

        <a href="javascript:void(0);" class="wpcf-button hide" style="float:right;"><?php echo __( 'Hide notifications' ); ?><span class="wpcf-button-arrow hide"></span></a>
    </div><!-- END .wpcf-notif-dropdown -->
        <?php } ?>
<!-- END .wpcf-notif -->
