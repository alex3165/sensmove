<?php
/*
 * Date editor form.
 */

if ( !defined( 'ABSPATH' ) ) {
    die( 'Security check' );
}

if ( !isset( $data ) ) {
    $data = array();
}

$data = array_merge( array(
    'date_formats' => array(),
    'custom' => get_option( 'date_format' ),
        ), (array) $data );
?>

<div data-bind="template: {name:'tpl-types-modal-date'}"></div>

<!--TYPES MODAL DATE-->
<script id="tpl-types-modal-date" type="text/html">
<div class="fieldset form-inline">
	<ul>
		<li>
			<input id="date-calendar" type="radio" name="style" value="calendar" data-bind="checked: date_mode, click: dateStyling" />
			<label for="date-calendar"><?php _e( 'Show as calendar ', 'wpcf' ); ?></label>
		</li>
		<li>
			<input id="date-text" type="radio" name="style" value="text" data-bind="checked: date_mode, click: dateStyling" />
			<label for="date-text"><?php _e( 'Show as text', 'wpcf' ); ?></label>

			<div id="date-formats" class="group-nested" data-bind="visible: date_mode() == 'text'">
				<ul>
					<?php foreach ( $data['date_formats'] as $format ): ?>

						<li>
							<input id="date-format-<?php echo $format['id']; ?>" type="radio" name="format" value="<?php echo $format['format']; ?>"<?php echo $format['default'] ? ' checked="checked"' : ''; ?> />
							<label for="date-format-<?php echo $format['id']; ?>"><?php echo $format['title']; ?></label>
						</li>

					<?php endforeach; ?>

						<li>
							<input id="date-custom" type="radio" name="format" value="custom"<?php echo $data['default'] == 'custom' ? ' checked="checked"' : ''; ?>/>
							<label for="date-custom"><?php _e( 'Custom', 'wpcf' ); ?></label>
							<input id="date-custom-format" type="text" name="custom" value="<?php echo $data['custom']; ?>" />
							<p>
								<a href="http://codex.wordpress.org/Formatting_Date_and_Time" target="_blank"><i class="icon-book"></i><?php _e( 'Documentation on date and time formatting', 'wpcf' ); ?></a>
							</p>
						</li>
				</ul>
			</div>

		</li>
	</ul>
</div>

</script><!--END TYPES MODAL DATE-->
