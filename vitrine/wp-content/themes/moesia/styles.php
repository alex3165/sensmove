<?php

//Dynamic styles
function moesia_custom_styles($custom) {

	//***Front page colors
	//Services section
	$services_bg = esc_html(get_theme_mod( 'services_bg' ));
	$services_title = esc_html(get_theme_mod( 'services_title' ));
	$services_title_dec = esc_html(get_theme_mod( 'services_title_dec' ));
	$services_icon_bg = esc_html(get_theme_mod( 'services_icon_bg' ));
	$services_item_title = esc_html(get_theme_mod( 'services_item_title' ));
	$services_body_text = esc_html(get_theme_mod( 'services_body_text' ));

	if ( isset($services_bg) && ( $services_bg != '#ffffff' ) ) {
		$custom = ".services-area { background-color: {$services_bg} !important; }"."\n";
	}
	if ( isset($services_title) && ( $services_title != '#444' ) ) {
		$custom .= ".services-area .widget-title { color: {$services_title}; }"."\n";
	}
	if ( isset($services_title_dec) && ( $services_title_dec != '#ff6b53' ) ) {
		$custom .= ".services-area .widget-title:after { border-color: {$services_title_dec}; }"."\n";
	}	
	if ( isset($services_icon_bg) && ( $services_icon_bg != '#ff6b53' ) ) {
		$custom .= ".service-icon { background-color: {$services_icon_bg}; }"."\n";
	}
	if ( isset($services_item_title) && ( $services_item_title != '#ff6b53' ) ) {
		$custom .= ".service-title, .service-title a { color: {$services_item_title}; }"."\n";
	}
	if ( isset($services_body_text) && ( $services_body_text!= '#aaa' ) ) {
		$custom .= ".service-desc { color: {$services_body_text}; }"."\n";
	}

	//Employees section
	$employees_bg = esc_html(get_theme_mod( 'employees_bg' ));
	$employees_title = esc_html(get_theme_mod( 'employees_title' ));
	$employees_title_dec = esc_html(get_theme_mod( 'employees_title_dec' ));
	$employees_name = esc_html(get_theme_mod( 'employees_name' ));
	$employees_function = esc_html(get_theme_mod( 'employees_function' ));
	$employees_body_text = esc_html(get_theme_mod( 'employees_body_text' ));

	if ( isset($employees_bg) && ( $employees_bg != '#ffffff' ) ) {
		$custom .= ".employees-area { background-color: {$employees_bg} !important; }"."\n";
	}
	if ( isset($employees_title) && ( $employees_title != '#444' ) ) {
		$custom .= ".employees-area .widget-title { color: {$employees_title}; }"."\n";
	}
	if ( isset($employees_title_dec) && ( $employees_title_dec != '#ff6b53' ) ) {
		$custom .= ".employees-area .widget-title:after { border-color: {$employees_title_dec}; }"."\n";
	}
	if ( isset($employees_name) && ( $employees_name != '#444' ) ) {
		$custom .= ".employee-name { color: {$employees_name}; }"."\n";
	}
	if ( isset($employees_function) && ( $employees_function != '#727272' ) ) {
		$custom .= ".employee-position, .employee-social a { color: {$employees_function}; }"."\n";
	}
	if ( isset($employees_body_text) && ( $employees_body_text!= '#aaa' ) ) {
		$custom .= ".employee-desc { color: {$employees_body_text}; }"."\n";
	}
	//Testimonials section
	$testimonials_bg = esc_html(get_theme_mod( 'testimonials_bg' ));
	$testimonials_title = esc_html(get_theme_mod( 'testimonials_title' ));
	$testimonials_title_dec = esc_html(get_theme_mod( 'testimonials_title_dec' ));
	$testimonials_function = esc_html(get_theme_mod( 'testimonials_function' ));
	$testimonials_client = esc_html(get_theme_mod( 'testimonials_client' ));
	$testimonials_body_bg = esc_html(get_theme_mod( 'testimonials_body_bg' ));
	$testimonials_body_text = esc_html(get_theme_mod( 'testimonials_body_text' ));

	if ( isset($testimonials_bg) && ( $testimonials_bg != '#ffffff' ) ) {
		$custom .= ".testimonials-area { background-color: {$testimonials_bg} !important; }"."\n";
	}
	if ( isset($testimonials_title) && ( $testimonials_title != '#444' ) ) {
		$custom .= ".testimonials-area .widget-title { color: {$testimonials_title}; }"."\n";
	}
	if ( isset($testimonials_title_dec) && ( $testimonials_title_dec != '#ff6b53' ) ) {
		$custom .= ".testimonials-area .widget-title:after { border-color: {$testimonials_title_dec}; }"."\n";
	}
	if ( isset($testimonials_function) && ( $testimonials_function != '#aaa' ) ) {
		$custom .= ".client-function { color: {$testimonials_function}; }"."\n";
	}
	if ( isset($testimonials_client) && ( $testimonials_client != '#444' ) ) {
		$custom .= ".client-name { color: {$testimonials_client}; }"."\n";
	}
	if ( isset($testimonials_body_bg) && ( $testimonials_body_bg != '#f5f5f5' ) ) {
		$custom .= ".testimonial-body { background-color: {$testimonials_body_bg}; }"."\n";
		$custom .= ".testimonial-body:after { border-top-color: {$testimonials_body_bg}; }"."\n";
	}	
	if ( isset($testimonials_body_text) && ( $testimonials_body_text!= '#aaa' ) ) {
		$custom .= ".testimonial-body { color: {$testimonials_body_text}; }"."\n";
	}
	//Skills section
	$skills_bg = esc_html(get_theme_mod( 'skills_bg' ));
	$skills_title = esc_html(get_theme_mod( 'skills_title' ));
	$skills_title_dec = esc_html(get_theme_mod( 'skills_title_dec' ));
	$skills_bar = esc_html(get_theme_mod( 'skills_bar' ));
	$skills_body_text = esc_html(get_theme_mod( 'skills_body_text' ));

	if ( isset($skills_bg) && ( $skills_bg != '#ffffff' ) ) {
		$custom .= ".skills-area { background-color: {$skills_bg} !important; }"."\n";
	}
	if ( isset($skills_title) && ( $skills_title != '#444' ) ) {
		$custom .= ".skills-area .widget-title { color: {$skills_title}; }"."\n";
	}
	if ( isset($skills_title_dec) && ( $skills_title_dec != '#ff6b53' ) ) {
		$custom .= ".skills-area .widget-title:after { border-color: {$skills_title_dec}; }"."\n";
	}
	if ( isset($skills_bar) && ( $skills_bar != '#ff6b53' ) ) {
		$custom .= ".skill-bar div { background-color: {$skills_bar}; }"."\n";
	}
	if ( isset($skills_body_text) && ( $skills_body_text!= '#aaa' ) ) {
		$custom .= ".skills-desc, .skills-list { color: {$skills_body_text}; }"."\n";
	}
	//Facts section
	$facts_bg = esc_html(get_theme_mod( 'facts_bg' ));
	$facts_title = esc_html(get_theme_mod( 'facts_title' ));
	$facts_title_dec = esc_html(get_theme_mod( 'facts_title_dec' ));
	$facts_numbers = esc_html(get_theme_mod( 'facts_numbers' ));
	$facts_body_text = esc_html(get_theme_mod( 'facts_body_text' ));

	if ( isset($facts_bg) && ( $facts_bg != '#ffffff' ) ) {
		$custom .= ".facts-area { background-color: {$facts_bg} !important; }"."\n";
	}
	if ( isset($facts_title) && ( $facts_title != '#444' ) ) {
		$custom .= ".facts-area .widget-title { color: {$facts_title}; }"."\n";
	}
	if ( isset($facts_title_dec) && ( $facts_title_dec != '#ff6b53' ) ) {
		$custom .= ".facts-area .widget-title:after { border-color: {$facts_title_dec}; }"."\n";
	}
	if ( isset($facts_numbers) && ( $facts_numbers != '#ff6b53' ) ) {
		$custom .= ".fact { color: {$facts_numbers}; }"."\n";
	}
	if ( isset($facts_body_text) && ( $facts_body_text!= '#aaa' ) ) {
		$custom .= ".fact-name { color: {$facts_body_text}; }"."\n";
	}
	//Clients section
	$clients_bg = esc_html(get_theme_mod( 'clients_bg' ));
	$clients_title = esc_html(get_theme_mod( 'clients_title' ));
	$clients_title_dec = esc_html(get_theme_mod( 'clients_title_dec' ));
	$clients_slider = esc_html(get_theme_mod( 'clients_slider' ));

	if ( isset($clients_bg) && ( $clients_bg != '#ffffff' ) ) {
		$custom .= ".clients-area { background-color: {$clients_bg} !important; }"."\n";
	}
	if ( isset($clients_title) && ( $clients_title != '#444' ) ) {
		$custom .= ".clients-area .widget-title { color: {$clients_title}; }"."\n";
	}
	if ( isset($clients_title_dec) && ( $clients_title_dec != '#ff6b53' ) ) {
		$custom .= ".clients-area .widget-title:after { border-color: {$clients_title_dec}; }"."\n";
	}
	if ( isset($clients_slider) && ( $clients_slider != '#ff6b53' ) ) {
		$custom .= ".slick-prev:before, .slick-next:before { color: {$clients_slider}; }"."\n";
	}
	//Blockquote section
	$blockquote_bg = esc_html(get_theme_mod( 'blockquote_bg' ));
	$blockquote_title = esc_html(get_theme_mod( 'blockquote_title' ));
	$blockquote_title_dec = esc_html(get_theme_mod( 'blockquote_title_dec' ));
	$blockquote_icon = esc_html(get_theme_mod( 'blockquote_icon' ));
	$blockquote_body_text = esc_html(get_theme_mod( 'blockquote_body_text' ));	

	if ( isset($blockquote_bg) && ( $blockquote_bg != '#ffffff' ) ) {
		$custom .= ".blockquote-area { background-color: {$blockquote_bg} !important; }"."\n";
	}
	if ( isset($blockquote_title) && ( $blockquote_title != '#444' ) ) {
		$custom .= ".blockquote-area .widget-title { color: {$blockquote_title}; }"."\n";
	}
	if ( isset($blockquote_title_dec) && ( $blockquote_title_dec != '#ff6b53' ) ) {
		$custom .= ".blockquote-area .widget-title:after { border-color: {$blockquote_title_dec}; }"."\n";
	}
	if ( isset($blockquote_icon) && ( $blockquote_icon != '#ff6b53' ) ) {
		$custom .= ".blockquote-area blockquote:before { color: {$blockquote_icon}; }"."\n";
	}
	if ( isset($blockquote_body_text) && ( $blockquote_body_text != '#aaa' ) ) {
		$custom .= ".blockquote-area blockquote { color: {$blockquote_body_text}; }"."\n";
	}
	//Social section
	$social_bg = esc_html(get_theme_mod( 'social_bg' ));
	$social_title = esc_html(get_theme_mod( 'social_title' ));
	$social_title_dec = esc_html(get_theme_mod( 'social_title_dec' ));
	$social_icons = esc_html(get_theme_mod( 'social_icons' ));
	$social_body_text = esc_html(get_theme_mod( 'social_body_text' ));	

	if ( isset($social_bg) && ( $social_bg != '#ffffff' ) ) {
		$custom .= ".social-area { background-color: {$social_bg} !important; }"."\n";
	}
	if ( isset($social_title) && ( $social_title != '#444' ) ) {
		$custom .= ".social-area .widget-title { color: {$social_title}; }"."\n";
	}
	if ( isset($social_title_dec) && ( $social_title_dec != '#ff6b53' ) ) {
		$custom .= ".social-area .widget-title:after { border-color: {$social_title_dec}; }"."\n";
	}
	if ( isset($social_icons) && ( $social_icons != '#ff6b53' ) ) {
		$custom .= ".social-area a:before { color: {$social_icons}; }"."\n";
	}
	//Projects section
	$projects_bg = esc_html(get_theme_mod( 'projects_bg' ));
	$projects_title = esc_html(get_theme_mod( 'projects_title' ));
	$projects_title_dec = esc_html(get_theme_mod( 'projects_title_dec' ));
	$projects_item_bg = esc_html(get_theme_mod( 'projects_item_bg' ));
	$projects_icons = esc_html(get_theme_mod( 'projects_icons' ));	

	if ( isset($projects_bg) && ( $projects_bg != '#ffffff' ) ) {
		$custom .= ".projects-area { background-color: {$projects_bg} !important; }"."\n";
	}
	if ( isset($projects_title) && ( $projects_title != '#444' ) ) {
		$custom .= ".projects-area .widget-title { color: {$projects_title}; }"."\n";
	}
	if ( isset($projects_title_dec) && ( $projects_title_dec != '#ff6b53' ) ) {
		$custom .= ".projects-area .widget-title:after { border-color: {$projects_title_dec}; }"."\n";
	}
	if ( isset($projects_item_bg) && ( $projects_item_bg != '#ff6b53' ) ) {
		$custom .= ".project-image { background-color: {$projects_item_bg}; }"."\n";
	}
	if ( isset($projects_icons) && ( $projects_icons != '#ffffff' ) ) {
		$custom .= ".link-icon, .pp-icon { color: {$projects_icons}; }"."\n";
	}	
    //Latest news section
    $latest_news_bg = esc_html(get_theme_mod( 'latest_news_bg' ));
    $latest_news_title = esc_html(get_theme_mod( 'latest_news_title' ));
    $latest_news_title_dec = esc_html(get_theme_mod( 'latest_news_title_dec' ));
    $latest_news_post_title = esc_html(get_theme_mod( 'latest_news_post_title' ));
    $latest_news_body_text = esc_html(get_theme_mod( 'latest_news_body_text' ));
    $latest_news_see_all = esc_html(get_theme_mod( 'latest_news_see_all' ));

    if ( isset($latest_news_bg) && ( $latest_news_bg != '#ffffff' ) ) {
        $custom .= ".latest-news-area { background-color: {$latest_news_bg} !important; }"."\n";
    }
    if ( isset($latest_news_title) && ( $latest_news_title != '#444' ) ) {
        $custom .= ".latest-news-area .widget-title { color: {$latest_news_title}; }"."\n";
    }
    if ( isset($latest_news_title_dec) && ( $latest_news_title_dec != '#ff6b53' ) ) {
        $custom .= ".latest-news-area .widget-title:after { border-color: {$latest_news_title_dec}; }"."\n";
    }
    if ( isset($latest_news_post_title) && ( $latest_news_post_title != '#444' ) ) {
        $custom .= ".latest-news-area .entry-title a { color: {$latest_news_post_title}; }"."\n";
    }
    if ( isset($latest_news_body_text) && ( $latest_news_body_text != '#aaa' ) ) {
        $custom .= ".blog-post { color: {$latest_news_body_text}; }"."\n";
    }
    if ( isset($latest_news_see_all) && ( $latest_news_see_all != '#444' ) ) {
        $custom .= ".all-news { color: {$latest_news_see_all}; border-color: {$latest_news_see_all}; }"."\n";
    }
    //Action area section
    $action_area_bg = esc_html(get_theme_mod( 'action_area_bg' ));
    $action_area_title = esc_html(get_theme_mod( 'action_area_title' ));
    $action_area_title_dec = esc_html(get_theme_mod( 'action_area_title_dec' ));
    $action_area_message = esc_html(get_theme_mod( 'action_area_message' ));
    $action_area_btn = esc_html(get_theme_mod( 'action_area_btn' ));
    $action_area_btn_bs = esc_html(get_theme_mod( 'action_area_btn_bs', '#c2503d' ));

    if ( isset($action_area_bg) && ( $action_area_bg != '#ffffff' ) ) {
        $custom .= ".action-area { background-color: {$action_area_bg}; }"."\n";
    }
    if ( isset($action_area_title) && ( $action_area_title != '#444' ) ) {
        $custom .= ".action-area .widget-title { color: {$action_area_title}; }"."\n";
    }
    if ( isset($action_area_title_dec) && ( $action_area_title_dec != '#ff6b53' ) ) {
        $custom .= ".action-area .widget-title:after { border-color: {$action_area_title_dec}; }"."\n";
    }
    if ( isset($action_area_message) && ( $action_area_message != '#444' ) ) {
        $custom .= ".action-text { color: {$action_area_message}; }"."\n";
    }
    if ( isset($action_area_btn) && ( $action_area_btn != '#ff6b53' ) ) {
        $custom .= ".call-to-action { background-color: {$action_area_btn}; }"."\n";
    }
    $custom .= ".call-to-action { box-shadow: 0 5px 0 {$action_area_btn_bs}; }"."\n";

    //Welcome area
    $header_title_color = esc_html(get_theme_mod( 'header_title_color' ));
    $header_desc_color = esc_html(get_theme_mod( 'header_desc_color' ));
    $header_btn_bg = esc_html(get_theme_mod( 'header_btn_bg' ));
    $header_btn_bs = esc_html(get_theme_mod( 'header_btn_bs', '#C2503D' ));
    if ( isset($header_title_color) && ( $header_title_color != '#ffffff' ) ) {
        $custom .= ".welcome-title { color: {$header_title_color}; }"."\n";
    }
    if ( isset($header_desc_color) && ( $header_desc_color != '#d8d8d8' ) ) {
        $custom .= ".welcome-desc { color: {$header_desc_color}; }"."\n";
    }
    if ( isset($header_btn_bg) && ( $header_btn_bg != '#ff6b53' ) ) {
        $custom .= ".welcome-button { background-color: {$header_btn_bg}; }"."\n";
    }    
    if ( isset($header_btn_bs) && ( $header_btn_bs != '#C2503D' ) ) {
        $custom .= ".welcome-button { box-shadow: 0 5px 0 {$header_btn_bs}; }"."\n";
        $custom .= ".welcome-button:active { box-shadow: 0 2px 0 {$header_btn_bs}; }"."\n";
    } else {
        $custom .= ".welcome-button { box-shadow: 0 5px 0 #C2503D; }"."\n";
        $custom .= ".welcome-button:active { box-shadow: 0 2px 0 #C2503D; }"."\n";   	
    }



	//Primary
	$primary_color = esc_html(get_theme_mod( 'primary_color' ));
	if ( isset($primary_color) && ( $primary_color != '#ff6b53' )) {
		$custom .= ".post-navigation .nav-previous, .post-navigation .nav-next, .paging-navigation .nav-previous, .paging-navigation .nav-next, .comment-respond input[type=\"submit\"], ::selection { background-color: {$primary_color}; }"."\n";
		$custom .= ".main-navigation a:hover, .entry-title a:hover, .entry-meta a:hover, .entry-footer a:hover, .social-widget li a::before, .author-social a, .widget a:hover, blockquote:before { color: {$primary_color}; }"."\n";
		$custom .= ".panel.widget .widget-title:after, .so-panel.widget .widget-title:after { border-color: {$primary_color}; }"."\n";	
	}
	//Site title
	$site_title = esc_html(get_theme_mod( 'site_title_color' ));
	if ( isset($site_title) && ( $site_title != '#ffffff' )) {
		$custom .= ".site-title a { color: {$site_title}; }"."\n";
	}
	//Site description
	$site_desc = esc_html(get_theme_mod( 'site_desc_color' ));
	if ( isset($site_desc) && ( $site_desc != '#dfdfdf' )) {
		$custom .= ".site-description { color: {$site_desc}; }"."\n";
	}	
	//Entry title
	$entry_title = esc_html(get_theme_mod( 'entry_title_color' ));
	if ( isset($entry_title) && ( $entry_title != '#222' )) {
		$custom .= ".entry-title, .entry-title a { color: {$entry_title}; }"."\n";
	}
	//Body text
	$body_text = esc_html(get_theme_mod( 'body_text_color' ));
	if ( isset($body_text) && ( $body_text != '#aaa' )) {
		$custom .= "body { color: {$body_text}; }"."\n";
	}
	//Menu background
	$menu_bg = esc_html(get_theme_mod( 'menu_color' ));
	if ( isset($menu_bg) && ( $menu_bg != '#222' )) {
		$custom .= ".top-bar { background-color: {$menu_bg}; }"."\n";
	}
	//Menu links
	$menu_links_color = esc_html(get_theme_mod( 'menu_links_color' ));
	if ( isset($menu_links_color) && ( $menu_links_color != '#ffffff' )) {
		$custom .= ".main-navigation a { color: {$menu_links_color}; }"."\n";
	}	
	//Footer background
	$footer_bg = esc_html(get_theme_mod( 'footer_color' ));
	if ( isset($footer_bg) && ( $footer_bg != '#222' )) {
		$custom .= ".footer-widget-area, .site-footer { background-color: {$footer_bg}; }"."\n";
	}		
	
	//Logos
	$logo_size = get_theme_mod( 'logo_size' );
	if ( get_theme_mod( 'logo_size' )) {
		$custom .= ".site-logo { max-width:" . intval($logo_size) . "px; }"."\n";
	}
	$wlogo_size = get_theme_mod( 'wlogo_size' );
	if ( get_theme_mod( 'wlogo_size' )) {
		$custom .= ".welcome-logo { max-width:" . intval($wlogo_size) . "px; }"."\n";
	}

	if ( ! function_exists('moesia_fonts_extended') ) { //Check that the Moesia Fonts extension is not active	
		//Fonts
		$headings_font = esc_html(get_theme_mod('headings_fonts'));	
		$body_font = esc_html(get_theme_mod('body_fonts'));	
		
		if ( $headings_font ) {
			$font_pieces = explode(":", $headings_font);
			$custom .= "h1, h2, h3, h4, h5, h6, .main-navigation li, .fact, .all-news, .welcome-button, .call-to-action .employee-position, .post-navigation .nav-previous, .post-navigation .nav-next, .paging-navigation .nav-previous, .paging-navigation .nav-next { font-family: {$font_pieces[0]}; }"."\n";
		}
		if ( $body_font ) {
			$font_pieces = explode(":", $body_font);
			$custom .= "body { font-family: {$font_pieces[0]}; }"."\n";
		}
	}
	//H1 size
	$h1_size = get_theme_mod( 'h1_size' );
	if ( get_theme_mod( 'h1_size' )) {
		$custom .= "h1 { font-size:" . intval($h1_size) . "px; }"."\n";
	}
    //H2 size
    $h2_size = get_theme_mod( 'h2_size' );
    if ( get_theme_mod( 'h2_size' )) {
        $custom .= "h2 { font-size:" . intval($h2_size) . "px; }"."\n";
    }
    //H3 size
    $h3_size = get_theme_mod( 'h3_size' );
    if ( get_theme_mod( 'h3_size' )) {
        $custom .= "h3 { font-size:" . intval($h3_size) . "px; }"."\n";
    }
    //H4 size
    $h4_size = get_theme_mod( 'h4_size' );
    if ( get_theme_mod( 'h4_size' )) {
        $custom .= "h4 { font-size:" . intval($h4_size) . "px; }"."\n";
    }
    //H5 size
    $h5_size = get_theme_mod( 'h5_size' );
    if ( get_theme_mod( 'h5_size' )) {
        $custom .= "h5 { font-size:" . intval($h5_size) . "px; }"."\n";
    }
    //H6 size
    $h6_size = get_theme_mod( 'h6_size' );
    if ( get_theme_mod( 'h6_size' )) {
        $custom .= "h6 { font-size:" . intval($h6_size) . "px; }"."\n";
    }
    //Body size
    $body_size = get_theme_mod( 'body_size' );
    if ( get_theme_mod( 'body_size' )) {
        $custom .= "body { font-size:" . intval($body_size) . "px; }"."\n";
    }
    //Widget titles size
    $widget_title_size = get_theme_mod( 'widget_title_size' );
    if ( get_theme_mod( 'widget_title_size' )) {
        $custom .= "@media (min-width: 499px) { section .widget-title, .panel.widget .widget-title { font-size:" . intval($widget_title_size) . "px; } }"."\n";
    }
    //Menu links font size
    $menu_size = get_theme_mod( 'menu_size' );
    if ( get_theme_mod( 'menu_size' )) {
        $custom .= ".main-navigation li { font-size:" . intval($menu_size) . "px; }"."\n";
    }	
	
    //Menu height
    $menu_height = get_theme_mod( 'moesia_menu_height' );
    if ( $menu_height ) {
        $custom .= ".site-branding, .main-navigation li { padding-top:" . intval($menu_height) . "px; padding-bottom:" . intval($menu_height) . "px; }"."\n";
        $custom .= ".menu-toggle { margin:" . intval($menu_height) . "px 0;}"."\n";
    }
    $sticky_menu_height = get_theme_mod( 'moesia_sticky_height' );
    if ( $sticky_menu_height ) {
        $custom .= "@media screen and (min-width: 992px) { .is-sticky .site-branding, .is-sticky .main-navigation li { padding-top:" . intval($sticky_menu_height) . "px; padding-bottom:" . intval($sticky_menu_height) . "px; } }"."\n";
    } 
 
    //Sticky menu
    $sticky_menu = get_theme_mod( 'moesia_menu_sticky', 0 );
    if ( $sticky_menu ) {
        $custom .= ".top-bar { position: relative !important; }"."\n";
    }

    //Widgets display on small screens
    //1. Sidebar
    if ( get_theme_mod( 'sidebar_widgets' ) == 1 ) {
        $custom .= "@media only screen and (max-width: 991px) { .widget-area { display: none; } }"."\n";
    }
    //2. Footer
    if ( get_theme_mod( 'footer_widgets' ) == 1 ) {
        $custom .= "@media only screen and (max-width: 991px) { .footer-widget-area { display: none; } }"."\n";
    }

    $background_img = get_post_meta( get_the_ID(), 'wpcf-page-bg-image', true );
    global $post;
    if ( $background_img ) {
        $custom .= ".page-id-" . $post->ID . " { background-image: url('" . esc_url($background_img) . "') !important; background-attachment: fixed !important; background-repeat: no-repeat !important; background-size: cover;}"."\n";
    }    

    //Header background size
    if (get_theme_mod('header_bg_size', 'cover') == 'contain') {
    	$custom .= ".has-banner::after { background-size: contain; }"."\n";
    }
    //Header max height
    $header_max_height_1025 = get_theme_mod('header_max_height_1025', '1440');
    $custom .= "@media only screen and (min-width: 1025px) { .has-banner,.has-banner::after { max-height:" . intval($header_max_height_1025) . "px; } }"."\n";    
    $header_max_height_1199 = get_theme_mod('header_max_height_1199', '1440');
    $custom .= "@media only screen and (min-width: 1199px) { .has-banner,.has-banner::after { max-height:" . intval($header_max_height_1199) . "px; } }"."\n";
    //Welcome info offset
    $wi_offset_991 = get_theme_mod('welcome_info_offset_991', '100');
    $custom .= "@media only screen and (min-width: 991px) { .welcome-info { top:" . intval($wi_offset_991) . "px; } }"."\n";
    $wi_offset_1199 = get_theme_mod('welcome_info_offset_1199', '100');
    $custom .= "@media only screen and (min-width: 1199px) { .welcome-info { top:" . intval($wi_offset_1199) . "px; } }"."\n";
    
	//Output all the styles
	wp_add_inline_style( 'moesia-style', $custom );	
}
add_action( 'wp_enqueue_scripts', 'moesia_custom_styles' );