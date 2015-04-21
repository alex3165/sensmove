<?php
/**
 * Dynamic styles for the Page Builder rows
 *
 * @package Moesia
 */
?>
<?php

function moesia_panels_row_style_fields($fields) {

	$fields['color'] = array(
		'name' => __('Color', 'moesia'),
		'type' => 'color',
	);	

	$fields['background'] = array(
		'name' => __('Background Color', 'moesia'),
		'type' => 'color',
	);

	$fields['background_image'] = array(
		'name' => __('Background Image', 'moesia'),
		'type' => 'url',
	);

	return $fields;
}
add_filter('siteorigin_panels_row_style_fields', 'moesia_panels_row_style_fields');
remove_filter('siteorigin_panels_row_style_fields', array('SiteOrigin_Panels_Default_Styling', 'row_style_fields' ) );

function moesia_panels_panels_row_style_attributes($attr, $style) {
	$attr['style'] = '';

	if(!empty($style['background'])) $attr['style'] .= 'background-color: '.$style['background'].'; ';
	if(!empty($style['color'])) $attr['style'] .= 'color: '.$style['color'].'; ';
	if(!empty($style['background_image'])) $attr['style'] .= 'background-image: url('.esc_url($style['background_image']).'); ';

	if(empty($attr['style'])) unset($attr['style']);
	return $attr;
}
add_filter('siteorigin_panels_row_style_attributes', 'moesia_panels_panels_row_style_attributes', 10, 2);

/* Theme widgets */
function moesia_theme_widgets($widgets) {
	$theme_widgets = array(
		'Moesia_Services',
		'Moesia_Employees',
		'Moesia_Fp_Social_Profile',
		'Moesia_Blockquote',
		'Moesia_Skills',
		'Moesia_Facts',
		'Moesia_Testimonials',
		'Moesia_Clients',
		'Moesia_Projects',
		'Moesia_Action',
		'Moesia_Latest_News',
	);
	foreach($theme_widgets as $theme_widget) {
		if( isset( $widgets[$theme_widget] ) ) {
			$widgets[$theme_widget]['groups'] = array('moesia-theme');
			$widgets[$theme_widget]['icon'] = 'dashicons dashicons-schedule';
		}
	}
	return $widgets;
}
add_filter('siteorigin_panels_widgets', 'moesia_theme_widgets');

/* Add a tab for the theme widgets in the page builder */
function moesia_theme_widgets_tab($tabs){
	$tabs[] = array(
		'title' => __('Moesia Theme Widgets', 'moesia'),
		'filter' => array(
			'groups' => array('moesia-theme')
		)
	);
	return $tabs;
}
add_filter('siteorigin_panels_widget_dialog_tabs', 'moesia_theme_widgets_tab', 20);