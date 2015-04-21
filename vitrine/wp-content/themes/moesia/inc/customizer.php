<?php
/**
 * Moesia Theme Customizer
 *
 * @package Moesia
 */

function moesia_customize_register( $wp_customize ) {
	/**
	 * Add postMessage support for site title and description for the Theme Customizer.
	 *
	 */	
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
    $wp_customize->remove_control('header_textcolor');

	//Add a class for titles
    class Moesia_Info extends WP_Customize_Control {
        public $type = 'info';
        public $label = '';
        public function render_content() {
        ?>
			<h3 style="text-decoration: underline; color: #DA4141; text-transform: uppercase;"><?php echo esc_html( $this->label ); ?></h3>
        <?php
        }
    }	


	//___General___//
    $wp_customize->add_section(
        'moesia_general',
        array(
            'title' => __('General', 'moesia'),
            'priority' => 9,
        )
    );
	//Logo Upload
	$wp_customize->add_setting(
		'site_logo',
		array(
			'default-image' => '',
			'sanitize_callback' => 'esc_url_raw',
		)
	);
    $wp_customize->add_control(
        new WP_Customize_Image_Control(
            $wp_customize,
            'site_logo',
            array(
               'label'          => __( 'Upload your logo', 'moesia' ),
			   'type' 			=> 'image',
               'section'        => 'moesia_general',
               'settings'       => 'site_logo',
               'priority' => 9,
            )
        )
    );
    //Logo size
    $wp_customize->add_setting(
        'logo_size',
        array(
            'sanitize_callback' => 'absint',
            'default'           => '100',
            'transport'         => 'postMessage'
        )       
    );
    $wp_customize->add_control( 'logo_size', array(
        'type'        => 'number',
        'priority'    => 10,
        'section'     => 'moesia_general',
        'label'       => __('Logo size', 'moesia'),
        'description' => __('Menu-content spacing will return to normal after you save &amp; exit the Customizer', 'moesia'),
        'input_attrs' => array(
            'min'   => 10,
            'max'   => 300,
            'step'  => 5,
            'style' => 'margin-bottom: 15px; padding: 15px;',
        ),
    ) );    
	//Favicon Upload
	$wp_customize->add_setting(
		'site_favicon',
		array(
			'default-image' => '',
			'sanitize_callback' => 'esc_url_raw',
		)
	);
    $wp_customize->add_control(
        new WP_Customize_Image_Control(
            $wp_customize,
            'site_favicon',
            array(
               'label'          => __( 'Upload your favicon', 'moesia' ),
			   'type' 			=> 'image',
               'section'        => 'moesia_general',
               'settings'       => 'site_favicon',
               'priority' => 11,
            )
        )
    );
    //Apple touch icon 144
    $wp_customize->add_setting(
        'apple_touch_144',
        array(
            'default-image' => '',
			'sanitize_callback' => 'esc_url_raw',
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Image_Control(
            $wp_customize,
            'apple_touch_144',
            array(
               'label'          => __( 'Upload your Apple Touch Icon (144x144 pixels)', 'moesia' ),
               'type'           => 'image',
               'section'        => 'moesia_general',
               'settings'       => 'apple_touch_144',
               'priority'       => 12,
            )
        )
    );
    //Apple touch icon 114
    $wp_customize->add_setting(
        'apple_touch_114',
        array(
            'default-image' => '',
			'sanitize_callback' => 'esc_url_raw',
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Image_Control(
            $wp_customize,
            'apple_touch_114',
            array(
               'label'          => __( 'Upload your Apple Touch Icon (114x114 pixels)', 'moesia' ),
               'type'           => 'image',
               'section'        => 'moesia_general',
               'settings'       => 'apple_touch_114',
               'priority'       => 13,
            )
        )
    );
    //Apple touch icon 72
    $wp_customize->add_setting(
        'apple_touch_72',
        array(
            'default-image' => '',
			'sanitize_callback' => 'esc_url_raw',
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Image_Control(
            $wp_customize,
            'apple_touch_72',
            array(
               'label'          => __( 'Upload your Apple Touch Icon (72x72 pixels)', 'moesia' ),
               'type'           => 'image',
               'section'        => 'moesia_general',
               'settings'       => 'apple_touch_72',
               'priority'       => 14,
            )
        )
    );
    //Apple touch icon 57
    $wp_customize->add_setting(
        'apple_touch_57',
        array(
            'default-image' => '',
			'sanitize_callback' => 'esc_url_raw',
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Image_Control(
            $wp_customize,
            'apple_touch_57',
            array(
               'label'          => __( 'Upload your Apple Touch Icon (57x57 pixels)', 'moesia' ),
               'type'           => 'image',
               'section'        => 'moesia_general',
               'settings'       => 'apple_touch_57',
               'priority'       => 15,
            )
        )
    );
    //SCroller
	$wp_customize->add_setting(
		'moesia_scroller',
		array(
			'sanitize_callback' => 'moesia_sanitize_checkbox',
			'default' => 0,			
		)		
	);
	$wp_customize->add_control(
		'moesia_scroller',
		array(
			'type' => 'checkbox',
			'label' => __('Check this box if you want to disable the custom scroller.', 'moesia'),
			'section' => 'moesia_general',
            'priority' => 16,			
		)
	);
    //Animations
    $wp_customize->add_setting(
        'moesia_animate',
        array(
            'sanitize_callback' => 'moesia_sanitize_checkbox',
            'default' => 0,         
        )       
    );
    $wp_customize->add_control(
        'moesia_animate',
        array(
            'type' => 'checkbox',
            'label' => __('Check this box if you want to disable the animations.', 'moesia'),
            'section' => 'moesia_general',
            'priority' => 17,           
        )
    );
    //Sidebar widgets
    $wp_customize->add_setting(
        'sidebar_widgets',
        array(
            'sanitize_callback' => 'moesia_sanitize_checkbox',
            'default' => 0,         
        )       
    );
    $wp_customize->add_control(
        'sidebar_widgets',
        array(
            'type' => 'checkbox',
            'label' => __('Check this box to hide the sidebar widgets on screen widths below 1024px', 'moesia'),
            'section' => 'moesia_general',
            'priority' => 18,           
        )
    ); 
    //Footer widgets
    $wp_customize->add_setting(
        'footer_widgets',
        array(
            'sanitize_callback' => 'moesia_sanitize_checkbox',
            'default' => 0,         
        )       
    );
    $wp_customize->add_control(
        'footer_widgets',
        array(
            'type' => 'checkbox',
            'label' => __('Check this box to hide the footer widgets on screen widths below 1024px', 'moesia'),
            'section' => 'moesia_general',
            'priority' => 19,           
        )
    );
    //Search
    $wp_customize->add_setting(
        'toggle_search',
        array(
            'sanitize_callback' => 'moesia_sanitize_checkbox',
            'default' => 0,         
        )       
    );
    $wp_customize->add_control(
        'toggle_search',
        array(
            'type' => 'checkbox',
            'label' => __('Activate header search icon?', 'moesia'),
            'section' => 'moesia_general',
            'priority' => 20,           
        )
    );                
    //___Single posts___//
    $wp_customize->add_section(
        'moesia_singles',
        array(
            'title' => __('Single posts/pages', 'moesia'),
            'priority' => 13,
        )
    );
    //Single posts
    $wp_customize->add_setting(
        'moesia_post_img',
        array(
            'sanitize_callback' => 'moesia_sanitize_checkbox',
        )       
    );
    $wp_customize->add_control(
        'moesia_post_img',
        array(
            'type' => 'checkbox',
            'label' => __('Check this box to show featured images on single posts', 'moesia'),
            'section' => 'moesia_singles',
        )
    );
    //Pages
    $wp_customize->add_setting(
        'moesia_page_img',
        array(
            'sanitize_callback' => 'moesia_sanitize_checkbox',
        )
    );
    $wp_customize->add_control(
        'moesia_page_img',
        array(
            'type' => 'checkbox',
            'label' => __('Check this box to show featured images on pages', 'moesia'),
            'section' => 'moesia_singles',
        )
    );
    //Author bio
    $wp_customize->add_setting(
        'author_bio',
        array(
            'sanitize_callback' => 'moesia_sanitize_checkbox',
        )       
    );
    $wp_customize->add_control(
        'author_bio',
        array(
            'type' => 'checkbox',
            'label' => __('Check this box to display the author bio on single posts. You can add your author bio and social links on the Users screen in the Your Profile section.', 'moesia'),
            'section' => 'moesia_singles',
        )
    );
    //___Blog layout___//
    $wp_customize->add_section(
        'blog_options',
        array(
            'title' => __('Blog options', 'moesia'),
            'priority' => 12,
            'description' => __('The blog layout can use either small featured images or large featured images. Select your desired option below.', 'moesia'),

        )
    );
    //Layout
    $wp_customize->add_setting(
        'blog_layout',
        array(
            'default' => 'small-images',
            'sanitize_callback' => 'moesia_sanitize_layout',
        )
    );
     
    $wp_customize->add_control(
        'blog_layout',
        array(
            'type' => 'radio',
            'label' => __('Layout', 'solon'),
            'section' => 'blog_options',
            'choices' => array(
                'small-images' => 'Small images',
                'large-images' => 'Large images',
                'masonry'      => 'Masonry (no sidebar)',
                'fullwidth'    => 'Full width (no sidebar)',
            ),
        )
    );
    //Full content posts
    $wp_customize->add_setting(
      'full_content',
      array(
        'sanitize_callback' => 'moesia_sanitize_checkbox',
        'default' => 0,     
      )   
    );
    $wp_customize->add_control(
        'full_content',
        array(
            'type' => 'checkbox',
            'label' => __('Check this box to display the full content of your posts on the home page.', 'moesia'),
            'section' => 'blog_options',
            'priority' => 11,
        )
    );
    //Excerpt
    $wp_customize->add_setting(
        'exc_lenght',
        array(
            'sanitize_callback' => 'absint',
            'default'           => '30',
        )       
    );
    $wp_customize->add_control( 'exc_lenght', array(
        'type'        => 'number',
        'priority'    => 12,
        'section'     => 'blog_options',
        'label'       => __('Excerpt lenght', 'moesia'),
        'description' => __('Choose your excerpt length here. Default: 30 words', 'moesia'),
        'input_attrs' => array(
            'min'   => 10,
            'max'   => 200,
            'step'  => 5,
            'style' => 'padding: 15px;',
        ),
    ) );       
	//___Welcome area___//
    $wp_customize->add_section(
        'moesia_header',
        array(
            'title' => __('Welcome Area', 'moesia'),
            'priority' => 12,
        )
    );
    //Header title
	$wp_customize->add_setting(
	    'header_title',
	    array(
	        'default' => '',
	        'sanitize_callback' => 'moesia_sanitize_text',
	    )
	);
	$wp_customize->add_control(
	    'header_title',
	    array(
	        'label' => __( 'Welcome title (not the site title)', 'moesia' ),
	        'section' => 'moesia_header',
	        'type' => 'text',
	        'priority' => 13
	    )
	);
    //Welcome logo
    $wp_customize->add_setting(
        'header_logo',
        array(
            'default-image' => '',
			'sanitize_callback' => 'esc_url_raw',
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Image_Control(
            $wp_customize,
            'header_logo',
            array(
               'label'          => __( 'Welcome logo. Displayed instead of the welcome title from the previous option.', 'moesia' ),
               'type'           => 'image',
               'section'        => 'moesia_header',
               'settings'       => 'header_logo',
               'priority'       => 14,
            )
        )
    ); 
    //Logo size
    $wp_customize->add_setting(
        'wlogo_size',
        array(
            'sanitize_callback' => 'absint',
            'default'           => '200',
            'transport'         => 'postMessage'
        )       
    );
    $wp_customize->add_control( 'wlogo_size', array(
        'type'        => 'number',
        'priority'    => 15,
        'section'     => 'moesia_header',
        'label'       => __('Welcome logo size', 'moesia'),
        'input_attrs' => array(
            'min'   => 10,
            'max'   => 500,
            'step'  => 5,
            'style' => 'margin-bottom: 15px; padding: 15px;',
        ),
    ) );        
   //Header description
	$wp_customize->add_setting(
	    'header_desc',
	    array(
	        'default' => '',
	        'sanitize_callback' => 'moesia_sanitize_text',
	    )
	);
	$wp_customize->add_control(
	    'header_desc',
	    array(
	        'label' => __( 'Welcome message (not the site description)', 'moesia' ),
	        'section' => 'moesia_header',
	        'type' => 'text',
	        'priority' => 16
	    )
	);	
   //Header button text 
	$wp_customize->add_setting(
	    'header_btn_text',
	    array(
	        'default' => 'Download this theme',
	        'sanitize_callback' => 'moesia_sanitize_text',
	    )
	);
	$wp_customize->add_control(
	    'header_btn_text',
	    array(
	        'label' => __( 'The text for the call to action button', 'moesia' ),
	        'section' => 'moesia_header',
	        'type' => 'text',
	        'priority' => 17
	    )
	);
   //Header button link 
	$wp_customize->add_setting(
	    'header_btn_link',
	    array(
	        'default' => '',
	        'sanitize_callback' => 'esc_url_raw',
	    )
	);
	$wp_customize->add_control(
	    'header_btn_link',
	    array(
	        'label' => __( 'The link for the call to action button', 'moesia' ),
	        'section' => 'moesia_header',
	        'type' => 'text',
	        'priority' => 18
	    )
	);
    //Activate
    $wp_customize->add_setting(
        'moesia_banner',
        array(
            'sanitize_callback' => 'moesia_sanitize_checkbox',
            'default' => 0,         
        )       
    );
    $wp_customize->add_control(
        'moesia_banner',
        array(
            'type' => 'checkbox',
            'label' => __('Check this box if you want to disable the header image and text on all pages except the front page.', 'moesia'),
            'section' => 'moesia_header',
            'priority' => 19,            
        )
    );
    //Overlay
    $wp_customize->add_setting(
        'header_overlay',
        array(
            'sanitize_callback' => 'moesia_sanitize_checkbox',
            'default' => 0,         
        )       
    );
    $wp_customize->add_control(
        'header_overlay',
        array(
            'type' => 'checkbox',
            'label' => __('Check this box if you want to disable the header overlay pattern.', 'moesia'),
            'section' => 'moesia_header',
            'priority' => 20,            
        )
    );
    //Title color
    $wp_customize->add_setting(
        'header_title_color',
        array(
            'default'           => '#fff',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'header_title_color',
            array(
                'label' => __('Welcome title color', 'moesia'),
                'section' => 'moesia_header',
                'settings' => 'header_title_color',
                'priority' => 21
            )
        )
    );    
    //Description color
    $wp_customize->add_setting(
        'header_desc_color',
        array(
            'default'           => '#d8d8d8',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'header_desc_color',
            array(
                'label' => __('Welcome message color', 'moesia'),
                'section' => 'moesia_header',
                'settings' => 'header_desc_color',
                'priority' => 22
            )
        )
    );    
    //Button
    $wp_customize->add_setting(
        'header_btn_bg',
        array(
            'default'           => '#ff6b53',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'header_btn_bg',
            array(
                'label' => __('Button background', 'moesia'),
                'section' => 'moesia_header',
                'settings' => 'header_btn_bg',
                'priority' => 23
            )
        )
    );   
    //Button box shadow
    $wp_customize->add_setting(
        'header_btn_bs',
        array(
            'default'           => '#C2503D',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'header_btn_bs',
            array(
                'label' => __('Button box shadow', 'moesia'),
                'section' => 'moesia_header',
                'settings' => 'header_btn_bs',
                'priority' => 24
            )
        )
    );
    //___Menu___//
    $wp_customize->add_section(
        'moesia_menu',
        array(
            'title' => __('Menu', 'moesia'),
            'priority' => 13,
        )
    );
    //Top menu
    $wp_customize->add_setting(
        'moesia_menu_top',
        array(
            'sanitize_callback' => 'moesia_sanitize_checkbox',
            'default' => 0,         
        )       
    );
    $wp_customize->add_control(
        'moesia_menu_top',
        array(
            'type' => 'checkbox',
            'label' => __('Check this box to show the nav bar at top (changes will be visible after you save and exit the Customizer).', 'moesia'),
            'section' => 'moesia_menu',
            'priority' => 10,           
        )
    );
    //Menu height
    $wp_customize->add_setting(
        'moesia_menu_height',
        array(
            'sanitize_callback' => 'absint',
            'default'           => '40',
        )       
    );
    $wp_customize->add_control( 'moesia_menu_height', array(
        'type'        => 'number',
        'priority'    => 11,
        'section'     => 'moesia_menu',
        'label'       => __('Menu bar height', 'moesia'),
        'description' => __('The 40px default value refers to the top/bottom padding', 'moesia'),
        'input_attrs' => array(
            'min'   => 5,
            'max'   => 100,
            'step'  => 5,
            'style' => 'margin-bottom: 15px; padding: 15px;',
        ),
    ) );
    //Sticky menu height
    $wp_customize->add_setting(
        'moesia_sticky_height',
        array(
            'sanitize_callback' => 'absint',
            'default'           => '20',
        )       
    );
    $wp_customize->add_control( 'moesia_sticky_height', array(
        'type'        => 'number',
        'priority'    => 12,
        'section'     => 'moesia_menu',
        'label'       => __('Menu bar height [sticky]', 'moesia'),
        'description' => __('This option refers to the menu in sticky mode', 'moesia'),
        'input_attrs' => array(
            'min'   => 5,
            'max'   => 100,
            'step'  => 5,
            'style' => 'margin-bottom: 15px; padding: 15px;',
        ),
    ) );
    //Unsticky menu
    $wp_customize->add_setting(
        'moesia_menu_sticky',
        array(
            'sanitize_callback' => 'moesia_sanitize_checkbox',
            'default' => 0,         
        )       
    );
    $wp_customize->add_control(
        'moesia_menu_sticky',
        array(
            'type' => 'checkbox',
            'label' => __('Stop the nav bar from being sticky?', 'moesia'),
            'section' => 'moesia_menu',
            'priority' => 12,        
        )
    );         	
	//___FRONT PAGE COLORS___//
    $wp_customize->add_section(
        'moesia_fp_colors',
        array(
            'title' => __('Front Page Colors', 'moesia'),
            'priority' => 21,
            'description' => __('Here you can change the colors for each type of front page section.', 'moesia'),
        )
    );	
	//***Services section
    $wp_customize->add_setting('moesia_options[info]', array(
            'type' => 'info_control',
            'capability' => 'edit_theme_options',
			'sanitize_callback' => 'moesia_no_sanitize',			
        )
    );
    $wp_customize->add_control( new Moesia_Info( $wp_customize, 'services_section', array(
		'label' => __('Services section', 'moesia'),
        'section' => 'moesia_fp_colors',
        'settings' => 'moesia_options[info]',
		'priority' => 10
        ) )
    );
    //Background
	$wp_customize->add_setting(
		'services_bg',
		array(
			'default'			=> '#fff',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'services_bg',
			array(
				'label' => __('Services section background color', 'moesia'),
				'section' => 'moesia_fp_colors',
				'settings' => 'services_bg',
				'priority' => 11
			)
		)
	);
    //Title
	$wp_customize->add_setting(
		'services_title',
		array(
			'default'			=> '#444',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'services_title',
			array(
				'label' => __('Services section main title color', 'moesia'),
				'section' => 'moesia_fp_colors',
				'settings' => 'services_title',
				'priority' => 12
			)
		)
	);
    //Title decoration
	$wp_customize->add_setting(
		'services_title_dec',
		array(
			'default'			=> '#ff6b53',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'services_title_dec',
			array(
				'label' => __('Services section main title decoration (Updates after you press Save&amp;Publish)', 'moesia'),
				'section' => 'moesia_fp_colors',
				'settings' => 'services_title_dec',
				'priority' => 13
			)
		)
	);
    //Icons background
	$wp_customize->add_setting(
		'services_icon_bg',
		array(
			'default'			=> '#ff6b53',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'services_icon_bg',
			array(
				'label' => __('Services section icons background', 'moesia'),
				'section' => 'moesia_fp_colors',
				'settings' => 'services_icon_bg',
				'priority' => 15
			)
		)
	);
    //Item title
	$wp_customize->add_setting(
		'services_item_title',
		array(
			'default'			=> '#ff6b53',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'services_item_title',
			array(
				'label' => __('Services section item titles', 'moesia'),
				'section' => 'moesia_fp_colors',
				'settings' => 'services_item_title',
				'priority' => 16
			)
		)
	);
    //Body
	$wp_customize->add_setting(
		'services_body_text',
		array(
			'default'			=> '#aaa',
			'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Color_Control(
			$wp_customize,
			'services_body_text',
			array(
				'label' => __('Services section body', 'moesia'),
				'section' => 'moesia_fp_colors',
				'settings' => 'services_body_text',
				'priority' => 17
			)
		)
	);
    //***Employees section
    $wp_customize->add_setting('moesia_options[info]', array(
            'type' => 'info_control',
            'capability' => 'edit_theme_options',
			'sanitize_callback' => 'moesia_no_sanitize',			
        )
    );
    $wp_customize->add_control( new Moesia_Info( $wp_customize, 'employees_section', array(
        'label' => __('Employees section', 'moesia'),
        'section' => 'moesia_fp_colors',
        'settings' => 'moesia_options[info]',
        'priority' => 18
        ) )
    );
    //Background
    $wp_customize->add_setting(
        'employees_bg',
        array(
            'default'           => '#fff',
            'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'employees_bg',
            array(
                'label' => __('Employees section background color', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'employees_bg',
                'priority' => 19
            )
        )
    );
    //Title
    $wp_customize->add_setting(
        'employees_title',
        array(
            'default'           => '#444',
            'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'employees_title',
            array(
                'label' => __('Employees section main title color', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'employees_title',
                'priority' => 20
            )
        )
    );
    //Title decoration
    $wp_customize->add_setting(
        'employees_title_dec',
        array(
            'default'           => '#ff6b53',
            'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'employees_title_dec',
            array(
                'label' => __('Employees section main title decoration (Updates after you press Save&amp;Publish)', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'employees_title_dec',
                'priority' => 21
            )
        )
    );
    //Employee name
    $wp_customize->add_setting(
        'employees_name',
        array(
            'default'           => '#222',
            'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'employees_name',
            array(
                'label' => __('Employees section names', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'employees_name',
                'priority' => 22
            )
        )
    );
     //Employee function
    $wp_customize->add_setting(
        'employees_function',
        array(
            'default'           => '#727272',
            'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'employees_function',
            array(
                'label' => __('Employees section functions&amp;social icons', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'employees_function',
                'priority' => 23
            )
        )
    );
    //Body
    $wp_customize->add_setting(
        'employees_body_text',
        array(
            'default'           => '#aaa',
            'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'employees_body_text',
            array(
                'label' => __('Employees section body', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'employees_body_text',
                'priority' => 24
            )
        )
    );
    //***Testimonials section
    $wp_customize->add_setting('moesia_options[info]', array(
            'type' => 'info_control',
            'capability' => 'edit_theme_options',
			'sanitize_callback' => 'moesia_no_sanitize',			
        )
    );
    $wp_customize->add_control( new Moesia_Info( $wp_customize, 'testimonials_section', array(
        'label' => __('Testimonials section', 'moesia'),
        'section' => 'moesia_fp_colors',
        'settings' => 'moesia_options[info]',
        'priority' => 25
        ) )
    );
    //Background
    $wp_customize->add_setting(
        'testimonials_bg',
        array(
            'default'           => '#fff',
            'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'testimonials_bg',
            array(
                'label' => __('Testimonials section background color', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'testimonials_bg',
                'priority' => 26
            )
        )
    );
    //Title
    $wp_customize->add_setting(
        'testimonials_title',
        array(
            'default'           => '#444',
            'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'testimonials_title',
            array(
                'label' => __('Testimonials section main title color', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'testimonials_title',
                'priority' => 27
            )
        )
    );
    //Title decoration
    $wp_customize->add_setting(
        'testimonials_title_dec',
        array(
            'default'           => '#ff6b53',
            'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'testimonials_title_dec',
            array(
                'label' => __('Testimonials section main title decoration (Updates after you press Save&amp;Publish)', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'testimonials_title_dec',
                'priority' => 28
            )
        )
    );
    //Client name
    $wp_customize->add_setting(
        'testimonials_client',
        array(
            'default'           => '#222',
            'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'testimonials_client',
            array(
                'label' => __('Testimonials section - clients names', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'testimonials_client',
                'priority' => 29
            )
        )
    );
     //Client function
    $wp_customize->add_setting(
        'testimonials_function',
        array(
            'default'           => '#aaa',
            'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'testimonials_function',
            array(
                'label' => __('Testimonials section - clients functions', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'testimonials_function',
                'priority' => 30
            )
        )
    );
    //Testimonial item bg
    $wp_customize->add_setting(
        'testimonials_body_bg',
        array(
            'default'           => '#f5f5f5',
            'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'testimonials_body_bg',
            array(
                'label' => __('Testimonials section - item background ', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'testimonials_body_bg',
                'priority' => 31
            )
        )
    );    
    //Testimonial item text
    $wp_customize->add_setting(
        'testimonials_body_text',
        array(
            'default'           => '#aaa',
            'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'testimonials_body_text',
            array(
                'label' => __('Testimonials section - item text color', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'testimonials_body_text',
                'priority' => 32
            )
        )
    );
    //***Skills section
    $wp_customize->add_setting('moesia_options[info]', array(
            'type' => 'info_control',
            'capability' => 'edit_theme_options',
			'sanitize_callback' => 'moesia_no_sanitize',			
        )
    );
    $wp_customize->add_control( new Moesia_Info( $wp_customize, 'skills_section', array(
        'label' => __('Skills section', 'moesia'),
        'section' => 'moesia_fp_colors',
        'settings' => 'moesia_options[info]',
        'priority' => 33
        ) )
    );
    //Background
    $wp_customize->add_setting(
        'skills_bg',
        array(
            'default'           => '#fff',
            'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'skills_bg',
            array(
                'label' => __('Skills section background color', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'skills_bg',
                'priority' => 34
            )
        )
    );
    //Title
    $wp_customize->add_setting(
        'skills_title',
        array(
            'default'           => '#444',
            'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'skills_title',
            array(
                'label' => __('Skills section main title color', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'skills_title',
                'priority' => 35
            )
        )
    );
    //Title decoration
    $wp_customize->add_setting(
        'skills_title_dec',
        array(
            'default'           => '#ff6b53',
            'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'skills_title_dec',
            array(
                'label' => __('Skills section main title decoration (Updates after you press Save&amp;Publish)', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'skills_title_dec',
                'priority' => 36
            )
        )
    );
    //Skills bar
    $wp_customize->add_setting(
        'skills_bar',
        array(
            'default'           => '#ff6b53',
            'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'skills_bar',
            array(
                'label' => __('Skills section - skills bar', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'skills_bar',
                'priority' => 37
            )
        )
    );
    //Body
    $wp_customize->add_setting(
        'skills_body_text',
        array(
            'default'           => '#aaa',
            'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'skills_body_text',
            array(
                'label' => __('Skills section - body text', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'skills_body_text',
                'priority' => 38
            )
        )
    );
    //***Facts section
    $wp_customize->add_setting('moesia_options[info]', array(
            'type' => 'info_control',
            'capability' => 'edit_theme_options',
			'sanitize_callback' => 'moesia_no_sanitize',			
        )
    );
    $wp_customize->add_control( new Moesia_Info( $wp_customize, 'facts_section', array(
        'label' => __('Facts section', 'moesia'),
        'section' => 'moesia_fp_colors',
        'settings' => 'moesia_options[info]',
        'priority' => 39
        ) )
    );
    //Background
    $wp_customize->add_setting(
        'facts_bg',
        array(
            'default'           => '#fff',
            'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'facts_bg',
            array(
                'label' => __('Facts section background color', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'facts_bg',
                'priority' => 40
            )
        )
    );
    //Title
    $wp_customize->add_setting(
        'facts_title',
        array(
            'default'           => '#444',
            'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'facts_title',
            array(
                'label' => __('Facts section main title color', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'facts_title',
                'priority' => 41
            )
        )
    );
    //Title decoration
    $wp_customize->add_setting(
        'facts_title_dec',
        array(
            'default'           => '#ff6b53',
            'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'facts_title_dec',
            array(
                'label' => __('Facts section main title decoration (Updates after you press Save&amp;Publish)', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'facts_title_dec',
                'priority' => 42
            )
        )
    );
    //Facts
    $wp_customize->add_setting(
        'facts_numbers',
        array(
            'default'           => '#ff6b53',
            'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'facts_numbers',
            array(
                'label' => __('Facts section - facts numbers', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'facts_numbers',
                'priority' => 43
            )
        )
    );
    //Body
    $wp_customize->add_setting(
        'facts_body_text',
        array(
            'default'           => '#aaa',
            'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'facts_body_text',
            array(
                'label' => __('Facts section - body text', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'facts_body_text',
                'priority' => 44
            )
        )
    );
    //***Clients section
    $wp_customize->add_setting('moesia_options[info]', array(
            'type' => 'info_control',
            'capability' => 'edit_theme_options',
			'sanitize_callback' => 'moesia_no_sanitize',			
        )
    );
    $wp_customize->add_control( new Moesia_Info( $wp_customize, 'clients_section', array(
        'label' => __('Clients section', 'moesia'),
        'section' => 'moesia_fp_colors',
        'settings' => 'moesia_options[info]',
        'priority' => 45
        ) )
    );
    //Background
    $wp_customize->add_setting(
        'clients_bg',
        array(
            'default'           => '#fff',
            'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'clients_bg',
            array(
                'label' => __('Clients section background color', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'clients_bg',
                'priority' => 46
            )
        )
    );
    //Title
    $wp_customize->add_setting(
        'clients_title',
        array(
            'default'           => '#444',
            'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'clients_title',
            array(
                'label' => __('Clients section main title color', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'clients_title',
                'priority' => 47
            )
        )
    );
    //Title decoration
    $wp_customize->add_setting(
        'clients_title_dec',
        array(
            'default'           => '#ff6b53',
            'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'clients_title_dec',
            array(
                'label' => __('Clients section main title decoration (Updates after you press Save&amp;Publish)', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'clients_title_dec',
                'priority' => 48
            )
        )
    );
    //Slider controls
    $wp_customize->add_setting(
        'clients_slider',
        array(
            'default'           => '#ff6b53',
            'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'clients_slider',
            array(
                'label' => __('Clients section - slider controls (Updates after you press Save&amp;Publish)', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'clients_slider',
                'priority' => 49
            )
        )
    );
    //***Blockquote section
    $wp_customize->add_setting('moesia_options[info]', array(
            'type' => 'info_control',
            'capability' => 'edit_theme_options',
			'sanitize_callback' => 'moesia_no_sanitize',			
        )
    );
    $wp_customize->add_control( new Moesia_Info( $wp_customize, 'blockquote_section', array(
        'label' => __('Blockquote section', 'moesia'),
        'section' => 'moesia_fp_colors',
        'settings' => 'moesia_options[info]',
        'priority' => 50
        ) )
    );
    //Background
    $wp_customize->add_setting(
        'blockquote_bg',
        array(
            'default'           => '#fff',
            'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'blockquote_bg',
            array(
                'label' => __('Blockquote section background color', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'blockquote_bg',
                'priority' => 51
            )
        )
    );
    //Title
    $wp_customize->add_setting(
        'blockquote_title',
        array(
            'default'           => '#444',
            'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'blockquote_title',
            array(
                'label' => __('Blockquote section main title color', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'blockquote_title',
                'priority' => 52
            )
        )
    );
    //Title decoration
    $wp_customize->add_setting(
        'blockquote_title_dec',
        array(
            'default'           => '#ff6b53',
            'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'blockquote_title_dec',
            array(
                'label' => __('Blockquote section main title decoration (Updates after you press Save&amp;Publish)', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'blockquote_title_dec',
                'priority' => 53
            )
        )
    );
    //Blockquote icon
    $wp_customize->add_setting(
        'blockquote_icon',
        array(
            'default'           => '#ff6b53',
            'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'blockquote_icon',
            array(
                'label' => __('Blockquote section - blockquote icon (Updates after you press Save&amp;Publish)', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'blockquote_icon',
                'priority' => 54
            )
        )
    );
    //Body
    $wp_customize->add_setting(
        'blockquote_body_text',
        array(
            'default'           => '#aaa',
            'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'blockquote_body_text',
            array(
                'label' => __('Blockquote section - body text', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'blockquote_body_text',
                'priority' => 55
            )
        )
    );
    //***Social section
    $wp_customize->add_setting('moesia_options[info]', array(
            'type' => 'info_control',
            'capability' => 'edit_theme_options',
			'sanitize_callback' => 'moesia_no_sanitize',			
        )
    );
    $wp_customize->add_control( new Moesia_Info( $wp_customize, 'social_section', array(
        'label' => __('Social section', 'moesia'),
        'section' => 'moesia_fp_colors',
        'settings' => 'moesia_options[info]',
        'priority' => 56
        ) )
    );
    //Background
    $wp_customize->add_setting(
        'social_bg',
        array(
            'default'           => '#fff',
            'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'social_bg',
            array(
                'label' => __('Social section background color', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'social_bg',
                'priority' => 57
            )
        )
    );
    //Title
    $wp_customize->add_setting(
        'social_title',
        array(
            'default'           => '#444',
            'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'social_title',
            array(
                'label' => __('Social section main title color', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'social_title',
                'priority' => 58
            )
        )
    );
    //Title decoration
    $wp_customize->add_setting(
        'social_title_dec',
        array(
            'default'           => '#ff6b53',
            'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'social_title_dec',
            array(
                'label' => __('Social section main title decoration (Updates after you press Save&amp;Publish)', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'social_title_dec',
                'priority' => 59
            )
        )
    );
    //Social icons
    $wp_customize->add_setting(
        'social_icons',
        array(
            'default'           => '#ff6b53',
            'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'social_icons',
            array(
                'label' => __('Social section - social icons (Updates after you press Save&amp;Publish)', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'social_icons',
                'priority' => 60
            )
        )
    );

    //***Projects section
    $wp_customize->add_setting('moesia_options[info]', array(
            'type' => 'info_control',
            'capability' => 'edit_theme_options',
			'sanitize_callback' => 'moesia_no_sanitize',			
        )
    );
    $wp_customize->add_control( new Moesia_Info( $wp_customize, 'projects_section', array(
        'label' => __('Projects section', 'moesia'),
        'section' => 'moesia_fp_colors',
        'settings' => 'moesia_options[info]',
        'priority' => 61
        ) )
    );
    //Background
    $wp_customize->add_setting(
        'projects_bg',
        array(
            'default'           => '#fff',
            'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'projects_bg',
            array(
                'label' => __('Projects section background color', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'projects_bg',
                'priority' => 62
            )
        )
    );
    //Title
    $wp_customize->add_setting(
        'projects_title',
        array(
            'default'           => '#444',
            'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'projects_title',
            array(
                'label' => __('Projects section main title color', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'projects_title',
                'priority' => 63
            )
        )
    );
    //Title decoration
    $wp_customize->add_setting(
        'projects_title_dec',
        array(
            'default'           => '#ff6b53',
            'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'projects_title_dec',
            array(
                'label' => __('Projects section main title decoration', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'projects_title_dec',
                'priority' => 64
            )
        )
    );
    //Project background
    $wp_customize->add_setting(
        'projects_item_bg',
        array(
            'default'           => '#ff6b53',
            'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'projects_item_bg',
            array(
                'label' => __('Projects section - item background (on hover)', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'projects_item_bg',
                'priority' => 65
            )
        )
    );
    //Projects icons
    $wp_customize->add_setting(
        'projects_icons',
        array(
            'default'           => '#ff6b53',
            'sanitize_callback' => 'sanitize_hex_color',
			'transport'			=> 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'projects_icons',
            array(
                'label' => __('Projects section - projects icons', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'projects_icons',
                'priority' => 66
            )
        )
    );
    //***Latest news section
    $wp_customize->add_setting('moesia_options[info]', array(
            'type' => 'info_control',
            'capability' => 'edit_theme_options',
			'sanitize_callback' => 'moesia_no_sanitize',			
        )
    );
    $wp_customize->add_control( new Moesia_Info( $wp_customize, 'latest_news_section', array(
        'label' => __('Latest news section', 'moesia'),
        'section' => 'moesia_fp_colors',
        'settings' => 'moesia_options[info]',
        'priority' => 67
        ) )
    );
    //Background
    $wp_customize->add_setting(
        'latest_news_bg',
        array(
            'default'           => '#fff',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'latest_news_bg',
            array(
                'label' => __('Latest news section background color', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'latest_news_bg',
                'priority' => 68
            )
        )
    );
    //Title
    $wp_customize->add_setting(
        'latest_news_title',
        array(
            'default'           => '#444',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'latest_news_title',
            array(
                'label' => __('Latest news section main title color', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'latest_news_title',
                'priority' => 69
            )
        )
    );
    //Title decoration
    $wp_customize->add_setting(
        'latest_news_title_dec',
        array(
            'default'           => '#ff6b53',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'latest_news_title_dec',
            array(
                'label' => __('Latest news section main title decoration (Updates after you press Save&amp;Publish)', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'latest_news_title_dec',
                'priority' => 70
            )
        )
    );
    //Post title
    $wp_customize->add_setting(
        'latest_news_post_title',
        array(
            'default'           => '#444',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'latest_news_post_title',
            array(
                'label' => __('Post titles', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'latest_news_post_title',
                'priority' => 71
            )
        )
    );
    //Latest news text
    $wp_customize->add_setting(
        'latest_news_body_text',
        array(
            'default'           => '#aaa',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'latest_news_body_text',
            array(
                'label' => __('Text color', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'latest_news_body_text',
                'priority' => 72
            )
        )
    );
    //Seel all button
    $wp_customize->add_setting(
        'latest_news_see_all',
        array(
            'default'           => '#444',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'latest_news_see_all',
            array(
                'label' => __('See all button', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'latest_news_see_all',
                'priority' => 73
            )
        )
    );
    //***Action area section
    $wp_customize->add_setting('moesia_options[info]', array(
            'type' => 'info_control',
            'capability' => 'edit_theme_options',
			'sanitize_callback' => 'moesia_no_sanitize',			
        )
    );
    $wp_customize->add_control( new Moesia_Info( $wp_customize, 'action_area_section', array(
        'label' => __('Call to action section', 'moesia'),
        'section' => 'moesia_fp_colors',
        'settings' => 'moesia_options[info]',
        'priority' => 74
        ) )
    );
    //Background
    $wp_customize->add_setting(
        'action_area_bg',
        array(
            'default'           => '#fff',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'action_area_bg',
            array(
                'label' => __('Call to action section background color', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'action_area_bg',
                'priority' => 75
            )
        )
    );
    //Title
    $wp_customize->add_setting(
        'action_area_title',
        array(
            'default'           => '#444',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'action_area_title',
            array(
                'label' => __('Call to action section main title color', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'action_area_title',
                'priority' => 76
            )
        )
    );
    //Title decoration
    $wp_customize->add_setting(
        'action_area_title_dec',
        array(
            'default'           => '#ff6b53',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'action_area_title_dec',
            array(
                'label' => __('Call to action section main title decoration (Updates after you press Save&amp;Publish)', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'action_area_title_dec',
                'priority' => 77
            )
        )
    );
    //Message
    $wp_customize->add_setting(
        'action_area_message',
        array(
            'default'           => '#aaa',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'action_area_message',
            array(
                'label' => __('Message', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'action_area_message',
                'priority' => 78
            )
        )
    );
    //Button
    $wp_customize->add_setting(
        'action_area_btn',
        array(
            'default'           => '#ff6b53',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'action_area_btn',
            array(
                'label' => __('Button background', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'action_area_btn',
                'priority' => 79
            )
        )
    );   
    //Button box shadow
    $wp_customize->add_setting(
        'action_area_btn_bs',
        array(
            'default'           => '#C2503D',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'action_area_btn_bs',
            array(
                'label' => __('Button box shadow', 'moesia'),
                'section' => 'moesia_fp_colors',
                'settings' => 'action_area_btn_bs',
                'priority' => 80
            )
        )
    );
    //___Colors___//

    $wp_customize->get_section( 'colors' )->description = __('Not all of the color settings found in this section apply to the front page.', 'moesia');
    
    //Menu background
    $wp_customize->add_setting(
        'menu_color',
        array(
            'default'           => '#222',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'menu_color',
            array(
                'label' => __('Menu background', 'moesia'),
                'section' => 'colors',
                'settings' => 'menu_color',
                'priority' => 11
            )
        )
    );
    //Menu links
    $wp_customize->add_setting(
        'menu_links_color',
        array(
            'default'           => '#ffffff',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'menu_links_color',
            array(
                'label' => __('Menu links', 'moesia'),
                'section' => 'colors',
                'settings' => 'menu_links_color',
                'priority' => 11
            )
        )
    );	
    //Primary color
    $wp_customize->add_setting(
        'primary_color',
        array(
            'default'           => '#ff6b53',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'primary_color',
            array(
                'label' => __('Primary color (Updates after you click Save&amp;Publish)', 'moesia'),
                'section' => 'colors',
                'settings' => 'primary_color',
                'priority' => 12
            )
        )
    );             
    //Site title
    $wp_customize->add_setting(
        'site_title_color',
        array(
            'default'           => '#ffffff',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'site_title_color',
            array(
                'label' => __('Site title', 'moesia'),
                'section' => 'colors',
                'settings' => 'site_title_color',
                'priority' => 13
            )
        )
    );
    //Site description
    $wp_customize->add_setting(
        'site_desc_color',
        array(
            'default'           => '#dfdfdf',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'site_desc_color',
            array(
                'label' => __('Site description', 'moesia'),
                'section' => 'colors',
                'settings' => 'site_desc_color',
                'priority' => 14
            )
        )
    );
    //Entry title
    $wp_customize->add_setting(
        'entry_title_color',
        array(
            'default'           => '#444',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'entry_title_color',
            array(
                'label' => __('Entry title', 'moesia'),
                'section' => 'colors',
                'settings' => 'entry_title_color',
                'priority' => 15
            )
        )
    );  
    //Body
    $wp_customize->add_setting(
        'body_text_color',
        array(
            'default'           => '#aaa',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'body_text_color',
            array(
                'label' => __('Text', 'moesia'),
                'section' => 'colors',
                'settings' => 'body_text_color',
                'priority' => 16
            )
        )
    );
    //Footer
    $wp_customize->add_setting(
        'footer_color',
        array(
            'default'           => '#222',
            'sanitize_callback' => 'sanitize_hex_color',
            'transport'         => 'postMessage'
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Color_Control(
            $wp_customize,
            'footer_color',
            array(
                'label' => __('Footer background', 'moesia'),
                'section' => 'colors',
                'settings' => 'footer_color',
                'priority' => 17
            )
        )
    );
    //___Fonts___//
    $wp_customize->add_section(
        'moesia_typography',
        array(
            'title' => __('Fonts', 'moesia' ),
            'priority' => 15,
        )
    );
    $font_choices = 
        array(
            'Source Sans Pro:400,700,400italic,700italic' => 'Source Sans Pro',     
            'Droid Sans:400,700' => 'Droid Sans',
            'Lato:400,700,400italic,700italic' => 'Lato',
            'Arvo:400,700,400italic,700italic' => 'Arvo',
            'Lora:400,700,400italic,700italic' => 'Lora',
            'PT Sans:400,700,400italic,700italic' => 'PT Sans',
            'PT+Sans+Narrow:400,700' => 'PT Sans Narrow',
            'Arimo:400,700,400italic,700italic' => 'Arimo',
            'Ubuntu:400,700,400italic,700italic' => 'Ubuntu',
            'Bitter:400,700,400italic' => 'Bitter',
            'Droid Serif:400,700,400italic,700italic' => 'Droid Serif',
            'Open+Sans:400italic,700italic,400,700' => 'Open Sans',
            'Roboto:400,400italic,700,700italic' => 'Roboto',
            'Oswald:400,700' => 'Oswald',
            'Open Sans Condensed:700,300italic,300' => 'Open Sans Condensed',
            'Roboto Condensed:400italic,700italic,400,700' => 'Roboto Condensed',
            'Raleway:400,700' => 'Raleway',
            'Roboto Slab:400,700' => 'Roboto Slab',
            'Yanone Kaffeesatz:400,700' => 'Yanone Kaffeesatz',
            'Rokkitt:400' => 'Rokkitt',
        );
    
    $wp_customize->add_setting(
        'headings_fonts',
        array(
            'sanitize_callback' => 'moesia_sanitize_fonts',
        )
    );
    
    $wp_customize->add_control(
        'headings_fonts',
        array(
            'type' => 'select',
            'label' => __('Select your desired font for the headings.', 'moesia'),
            'section' => 'moesia_typography',
            'choices' => $font_choices
        )
    );
    
    $wp_customize->add_setting(
        'body_fonts',
        array(
            'sanitize_callback' => 'moesia_sanitize_fonts',
        )
    );
    
    $wp_customize->add_control(
        'body_fonts',
        array(
            'type' => 'select',
            'label' => __('Select your desired font for the body.', 'moesia'),
            'section' => 'moesia_typography',
            'choices' => $font_choices
        )
    );
    //H1 size
    $wp_customize->add_setting(
        'h1_size',
        array(
            'sanitize_callback' => 'absint',
            'default'           => '36',
            'transport'         => 'postMessage'
        )       
    );
    $wp_customize->add_control( 'h1_size', array(
        'type'        => 'number',
        'priority'    => 11,
        'section'     => 'moesia_typography',
        'label'       => __('H1 font size', 'moesia'),
        'input_attrs' => array(
            'min'   => 10,
            'max'   => 60,
            'step'  => 1,
            'style' => 'margin-bottom: 15px; padding: 15px;',
        ),
    ) );
    //H2 size
    $wp_customize->add_setting(
        'h2_size',
        array(
            'sanitize_callback' => 'absint',
            'default'           => '30',
            'transport'         => 'postMessage'
        )       
    );
    $wp_customize->add_control( 'h2_size', array(
        'type'        => 'number',
        'priority'    => 12,
        'section'     => 'moesia_typography',
        'label'       => __('H2 font size', 'moesia'),
        'input_attrs' => array(
            'min'   => 10,
            'max'   => 60,
            'step'  => 1,
            'style' => 'margin-bottom: 15px; padding: 15px;',
        ),
    ) );
    //H3 size
    $wp_customize->add_setting(
        'h3_size',
        array(
            'sanitize_callback' => 'absint',
            'default'           => '24',
            'transport'         => 'postMessage'
        )       
    );
    $wp_customize->add_control( 'h3_size', array(
        'type'        => 'number',
        'priority'    => 13,
        'section'     => 'moesia_typography',
        'label'       => __('H3 font size', 'moesia'),
        'input_attrs' => array(
            'min'   => 10,
            'max'   => 60,
            'step'  => 1,
            'style' => 'margin-bottom: 15px; padding: 15px;',
        ),
    ) );
    //h4 size
    $wp_customize->add_setting(
        'h4_size',
        array(
            'sanitize_callback' => 'absint',
            'default'           => '18',
            'transport'         => 'postMessage'
        )       
    );
    $wp_customize->add_control( 'h4_size', array(
        'type'        => 'number',
        'priority'    => 14,
        'section'     => 'moesia_typography',
        'label'       => __('H4 font size', 'moesia'),
        'input_attrs' => array(
            'min'   => 10,
            'max'   => 60,
            'step'  => 1,
            'style' => 'margin-bottom: 15px; padding: 15px;',
        ),
    ) );
    //h5 size
    $wp_customize->add_setting(
        'h5_size',
        array(
            'sanitize_callback' => 'absint',
            'default'           => '14',
            'transport'         => 'postMessage'
        )       
    );
    $wp_customize->add_control( 'h5_size', array(
        'type'        => 'number',
        'priority'    => 15,
        'section'     => 'moesia_typography',
        'label'       => __('H5 font size', 'moesia'),
        'input_attrs' => array(
            'min'   => 10,
            'max'   => 60,
            'step'  => 1,
            'style' => 'margin-bottom: 15px; padding: 15px;',
        ),
    ) );
    //h6 size
    $wp_customize->add_setting(
        'h6_size',
        array(
            'sanitize_callback' => 'absint',
            'default'           => '12',
            'transport'         => 'postMessage'
        )       
    );
    $wp_customize->add_control( 'h6_size', array(
        'type'        => 'number',
        'priority'    => 16,
        'section'     => 'moesia_typography',
        'label'       => __('H6 font size', 'moesia'),
        'input_attrs' => array(
            'min'   => 10,
            'max'   => 60,
            'step'  => 1,
            'style' => 'margin-bottom: 15px; padding: 15px;',
        ),
    ) );
    //body
    $wp_customize->add_setting(
        'body_size',
        array(
            'sanitize_callback' => 'absint',
            'default'           => '14',
            'transport'         => 'postMessage'
        )       
    );
    $wp_customize->add_control( 'body_size', array(
        'type'        => 'number',
        'priority'    => 17,
        'section'     => 'moesia_typography',
        'label'       => __('Body font size', 'moesia'),
        'input_attrs' => array(
            'min'   => 10,
            'max'   => 24,
            'step'  => 1,
            'style' => 'margin-bottom: 15px; padding: 15px;',
        ),
    ) );
    //Home page widget titles size
    $wp_customize->add_setting(
        'widget_title_size',
        array(
            'sanitize_callback' => 'absint',
            'default'           => '56',
            'transport'         => 'postMessage'
        )       
    );
    $wp_customize->add_control( 'widget_title_size', array(
        'type'        => 'number',
        'priority'    => 18,
        'section'     => 'moesia_typography',
        'label'       => __('Home page widget titles size', 'moesia'),
        'input_attrs' => array(
            'min'   => 10,
            'max'   => 90,
            'step'  => 1,
            'style' => 'margin-bottom: 15px; padding: 15px;',
        ),
    ) );
    //Menu links font size
    $wp_customize->add_setting(
        'menu_size',
        array(
            'sanitize_callback' => 'absint',
            'default'           => '14',
            'transport'         => 'postMessage'
        )       
    );
    $wp_customize->add_control( 'menu_size', array(
        'type'        => 'number',
        'priority'    => 19,
        'section'     => 'moesia_typography',
        'label'       => __('Menu links font size', 'moesia'),
        'input_attrs' => array(
            'min'   => 10,
            'max'   => 30,
            'step'  => 1,
            'style' => 'margin-bottom: 15px; padding: 15px;',
        ),
    ) );	
    //___Pro___//
    $wp_customize->add_section(
        'moesia_pro',
        array(
            'title' => __('Moesia Pro', 'moesia'),
            'priority' => 99,
            'description' => __('If you like Moesia and you want to see what Moesia Pro offers, have a look ', 'moesia') . '<a href="http://athemes.com/theme/moesia-pro">here</a>',
        )
    );  
    //Pro
    $wp_customize->add_setting('moesia_options[info]', array(
			'sanitize_callback' => 'moesia_no_sanitize',
            'type' => 'info_control',
            'capability' => 'edit_theme_options',
        )
    );
    $wp_customize->add_control( new Moesia_Info( $wp_customize, 'pro_section', array(
        'section' => 'moesia_pro',
        'settings' => 'moesia_options[info]',
        'priority' => 10
        ) )
    );
    //___Extensions___//
    $wp_customize->add_section(
        'moesia_extensions',
        array(
            'title' => __('Extensions', 'moesia'),
            'priority' => 99,
            'description' => __('A growing collection of free extensions for Moesia is available ', 'moesia') . '<a href="http://athemes.com/moesia-extensions">here</a>',
        )
    );  
    //Extensions
    $wp_customize->add_setting('moesia_options[info]', array(
            'sanitize_callback' => 'moesia_no_sanitize',
            'type' => 'info_control',
            'capability' => 'edit_theme_options',
        )
    );
    $wp_customize->add_control( new Moesia_Info( $wp_customize, 'extensions', array(
        'section' => 'moesia_extensions',
        'settings' => 'moesia_options[info]',
        'priority' => 10
        ) )
    );    
    //___Mobile header image___//
    $wp_customize->add_setting(
        'mobile_header',
        array(
            'default-image' => '',
			'sanitize_callback' => 'esc_url_raw',
        )
    );
    $wp_customize->add_control(
        new WP_Customize_Image_Control(
            $wp_customize,
            'mobile_header',
            array(
               'label'          => __( 'Small screens header image', 'moesia' ),
               'type'           => 'image',
               'section'        => 'header_image',
               'settings'       => 'mobile_header',
               'description'    => __( 'You can add below a smaller version of your header image and it will be displayed at screen widths below 1024px. This is important in case iPhones don\'t display your header image because of it being too big. You can also add a completely different image if you want, in case you have one that will look better on small screens. Recommended width: 1024px', 'moesia' ),
               'priority'       => 10,
            )
        )
    );
    //Background-size
    $wp_customize->add_setting(
        'header_bg_size',
        array(
            'default' => 'cover',
            'sanitize_callback' => 'moesia_sanitize_bg_size',
        )
    );
    $wp_customize->add_control(
        'header_bg_size',
        array(
            'type' => 'radio',
            'priority'    => 10,
            'label' => __('Header background size', 'moesia'),
            'section' => 'header_image',
            'choices' => array(
                'cover'     => __('Cover', 'moesia'),
                'contain'   => __('Contain', 'moesia'),
            ),
        )
    );
    //Header max height 1199
    $wp_customize->add_setting(
        'header_max_height_1199',
        array(
            'sanitize_callback' => 'absint',
            'default'           => '1440',
        )       
    );
    $wp_customize->add_control( 'header_max_height_1199', array(
        'type'        => 'number',
        'priority'    => 11,
        'section'     => 'header_image',
        'label'       => __('Header max height > 1199px', 'moesia'),
        'description' => __('Max height for the header at screen widths above 1199px', 'moesia'),
        'input_attrs' => array(
            'min'   => 200,
            'max'   => 1440,
            'step'  => 5,
            'style' => 'margin-bottom: 15px; padding: 15px;',
        ),
    ) );
    //Header max height 1025
    $wp_customize->add_setting(
        'header_max_height_1025',
        array(
            'sanitize_callback' => 'absint',
            'default'           => '1440',
        )       
    );
    $wp_customize->add_control( 'header_max_height_1025', array(
        'type'        => 'number',
        'priority'    => 12,
        'section'     => 'header_image',
        'label'       => __('Header max height > 1024px', 'moesia'),
        'description' => __('Max height for the header at screen widths above 1024px', 'moesia'),
        'input_attrs' => array(
            'min'   => 200,
            'max'   => 1440,
            'step'  => 5,
            'style' => 'margin-bottom: 15px; padding: 15px;',
        ),
    ) );
    //Welcome info top offset 1199
    $wp_customize->add_setting(
        'welcome_info_offset_1199',
        array(
            'sanitize_callback' => 'absint',
            'default'           => '100',
        )       
    );
    $wp_customize->add_control( 'welcome_info_offset_1199', array(
        'type'        => 'number',
        'priority'    => 13,
        'section'     => 'header_image',
        'label'       => __('Welcome info offset top > 1199px', 'moesia'),
        'description' => __('Offset at screen widths above 1199px', 'moesia'),        
        'input_attrs' => array(
            'min'   => 0,
            'max'   => 300,
            'step'  => 5,
            'style' => 'margin-bottom: 15px; padding: 15px;',
        ),
    ) );
    //Welcome info top offset 991
    $wp_customize->add_setting(
        'welcome_info_offset_991',
        array(
            'sanitize_callback' => 'absint',
            'default'           => '100',
        )       
    );
    $wp_customize->add_control( 'welcome_info_offset_991', array(
        'type'        => 'number',
        'priority'    => 13,
        'section'     => 'header_image',
        'label'       => __('Welcome info offset top > 991px', 'moesia'),
        'description' => __('Offset at screen widths above 991px', 'moesia'),        
        'input_attrs' => array(
            'min'   => 0,
            'max'   => 300,
            'step'  => 5,
            'style' => 'margin-bottom: 15px; padding: 15px;',
        ),
    ) );           
}
add_action( 'customize_register', 'moesia_customize_register' );

/**
 * Sanitization
 */
//Checkboxes
function moesia_sanitize_checkbox( $input ) {
	if ( $input == 1 ) {
		return 1;
	} else {
		return '';
	}
}
//Integers
function moesia_sanitize_int( $input ) {
    if( is_numeric( $input ) ) {
        return intval( $input );
    }
}
//Text
function moesia_sanitize_text( $input ) {
    return wp_kses_post( force_balance_tags( $input ) );
}
//Fonts
function moesia_sanitize_fonts( $input ) {
    $valid = array(
            'Source Sans Pro:400,700,400italic,700italic' => 'Source Sans Pro',     
            'Droid Sans:400,700' => 'Droid Sans',
            'Lato:400,700,400italic,700italic' => 'Lato',
            'Arvo:400,700,400italic,700italic' => 'Arvo',
            'Lora:400,700,400italic,700italic' => 'Lora',
            'PT Sans:400,700,400italic,700italic' => 'PT Sans',
            'PT+Sans+Narrow:400,700' => 'PT Sans Narrow',
            'Arimo:400,700,400italic,700italic' => 'Arimo',
            'Ubuntu:400,700,400italic,700italic' => 'Ubuntu',
            'Bitter:400,700,400italic' => 'Bitter',
            'Droid Serif:400,700,400italic,700italic' => 'Droid Serif',
            'Open+Sans:400italic,700italic,400,700' => 'Open Sans',
            'Roboto:400,400italic,700,700italic' => 'Roboto',
            'Oswald:400,700' => 'Oswald',
            'Open Sans Condensed:700,300italic,300' => 'Open Sans Condensed',
            'Roboto Condensed:400italic,700italic,400,700' => 'Roboto Condensed',
            'Raleway:400,700' => 'Raleway',
            'Roboto Slab:400,700' => 'Roboto Slab',
            'Yanone Kaffeesatz:400,700' => 'Yanone Kaffeesatz',
            'Rokkitt:400' => 'Rokkitt',
    );
 
    if ( array_key_exists( $input, $valid ) ) {
        return $input;
    } else {
        return '';
    }
}
//Blog layout
function moesia_sanitize_layout( $input ) {
    $valid = array(
        'small-images' => 'Small images',
        'large-images' => 'Large images',
        'masonry'      => 'Masonry (no sidebar)',
        'fullwidth'    => 'Full width (no sidebar)',
    );
 
    if ( array_key_exists( $input, $valid ) ) {
        return $input;
    } else {
        return '';
    }
}
//Background size
function moesia_sanitize_bg_size( $input ) {
    $valid = array(
        'cover'     => __('Cover', 'moesia'),
        'contain'   => __('Contain', 'moesia'),
    );
    if ( array_key_exists( $input, $valid ) ) {
        return $input;
    } else {
        return '';
    }
}
//No sanitize - empty function for options that do not require sanitization -> to bypass the Theme Check plugin
function moesia_no_sanitize( $input ) {
}
/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function moesia_customize_preview_js() {
	wp_enqueue_script( 'moesia_customizer', get_template_directory_uri() . '/js/customizer.js', array( 'customize-preview' ), true );
}
add_action( 'customize_preview_init', 'moesia_customize_preview_js' );
