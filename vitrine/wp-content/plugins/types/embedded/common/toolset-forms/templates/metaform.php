<?php
/**
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/embedded/common/toolset-forms/templates/metaform.php $
 * $LastChangedDate: 2014-11-18 06:47:25 +0000 (Tue, 18 Nov 2014) $
 * $LastChangedRevision: 1027712 $
 * $LastChangedBy: iworks $
 *
 */

if ( is_admin() ) {
    $child_div_classes = array( 'js-wpt-field-items' );
    if ( $cfg['use_bootstrap'] && in_array( $cfg['type'], array( 'date', 'select' ) ) ) {
        $child_div_classes[] = 'form-inline';
    }
    ?><div class="js-wpt-field wpt-field js-wpt-<?php echo $cfg['type']; ?> wpt-<?php echo $cfg['type']; ?><?php if ( @$cfg['repetitive'] ) echo ' js-wpt-repetitive wpt-repetitive'; ?><?php do_action('wptoolset_field_class', $cfg); ?>" data-wpt-type="<?php echo $cfg['type']; ?>" data-wpt-id="<?php echo $cfg['id']; ?>">
        <div class="<?php echo implode( ' ', $child_div_classes ); ?>">
	<?php foreach ( $html as $out ):
		include 'metaform-item.php';
	endforeach; ?>
    <?php if ( @$cfg['repetitive'] ): ?>
        <a href="#" class="js-wpt-repadd wpt-repadd button button-small button-primary-toolset" data-wpt-type="<?php echo $cfg['type']; ?>" data-wpt-id="<?php echo $cfg['id']; ?>"><?php echo apply_filters( 'toolset_button_add_repetition_text', sprintf(__('Add new %s', 'wpv-views'), $cfg['title']), $cfg); ?></a>
	<?php endif; ?>
		</div>
	</div>
<?php
} else {
	// CHeck if we need a wrapper
	$types_without_wrapper = array( 'submit', 'hidden' );
	$needs_wrapper = true;
	if ( isset( $cfg['type'] ) && in_array( $cfg['type'], $types_without_wrapper ) ) {
		$needs_wrapper = false;
	}
	// Adjust the data-initial-conditional
	ob_start();
	do_action('wptoolset_field_class', $cfg);
	$conditional_classes = ob_get_clean();
	if (strpos($conditional_classes, 'wpt-hidden') === false) {
		$conditional_classes = '';
	} else {
		$conditional_classes = 'true';
	}
	// Adjust classnames for container and buttons
	$button_extra_classnames = '';
	$container_classes = '';
	if ( array_key_exists( 'use_bootstrap', $cfg ) && $cfg['use_bootstrap'] ) {
		$button_extra_classnames .= ' btn btn-default btn-sm';
		$container_classes .= ' form-group';
	}
	if ( array_key_exists( 'repetitive', $cfg ) ) {
		$container_classes .= ' js-wpt-repetitive wpt-repetitive';
	}
	// Render
	if ( $needs_wrapper) {
        $identifier = $cfg['type'] . '-' . $cfg['name'];
		echo '<div class="js-wpt-field-items' . $container_classes . '" data-initial-conditional="' . $conditional_classes . '" data-item_name="' . $identifier .'">';
	}
    foreach ( $html as $out ) {
        include 'metaform-item.php';
    }
	if ( $cfg['repetitive'] ) {
		echo '<input type="button" class="js-wpt-repadd wpt-repadd' . $button_extra_classnames . '" data-wpt-type="' . $cfg['type'] . '" data-wpt-id="' . $cfg['id'] . '" value="';
		echo apply_filters( 'toolset_button_add_repetition_text', esc_attr( sprintf( __( 'Add new %s', 'wpv-views' ), $cfg['title'] ) ), $cfg );
		echo '" />';
	}
	if ( $needs_wrapper) {
		echo '</div>';
	}
}

