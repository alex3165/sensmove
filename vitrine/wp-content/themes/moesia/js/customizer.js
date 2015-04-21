/**
 * Theme Customizer enhancements for a better user experience.
 *
 * Contains handlers to make Theme Customizer preview reload changes asynchronously.
 */

( function( $ ) {
	// Site title and description.
	wp.customize( 'blogname', function( value ) {
		value.bind( function( to ) {
			$( '.site-title a' ).text( to );
		} );
	} );
	wp.customize( 'blogdescription', function( value ) {
		value.bind( function( to ) {
			$( '.site-description' ).text( to );
		} );
	} );
	// Header text color.
	wp.customize( 'header_textcolor', function( value ) {
		value.bind( function( to ) {
			if ( 'blank' === to ) {
				$( '.site-title, .site-description' ).css( {
					'clip': 'rect(1px, 1px, 1px, 1px)',
					'position': 'absolute'
				} );
			} else {
				$( '.site-title, .site-description' ).css( {
					'clip': 'auto',
					'color': to,
					'position': 'relative'
				} );
			}
		} );
	} );
	//--FRONT PAGE COLORS
	//Services section
	wp.customize('services_bg',function( value ) {
		value.bind( function( newval ) {
			$('.services-area').css('background-color', newval );
		} );
	});
	wp.customize('services_title',function( value ) {
		value.bind( function( newval ) {
			$('.services-area .widget-title').css('color', newval );
		} );
	});
	wp.customize('services_icon_bg',function( value ) {
		value.bind( function( newval ) {
			$('.service-icon').css('background-color', newval );
		} );
	});	
	wp.customize('services_item_title',function( value ) {
		value.bind( function( newval ) {
			$('.service-title, .service-title a').css('color', newval );
		} );
	});
	wp.customize('services_body_text',function( value ) {
		value.bind( function( newval ) {
			$('.service-desc').css('color', newval );
		} );
	});
	//Employees section
	wp.customize('employees_bg',function( value ) {
		value.bind( function( newval ) {
			$('.employees-area').css('background-color', newval );
		} );
	});
	wp.customize('employees_title',function( value ) {
		value.bind( function( newval ) {
			$('.employees-area .widget-title').css('color', newval );
		} );
	});
	wp.customize('employees_name',function( value ) {
		value.bind( function( newval ) {
			$('.employee-name').css('color', newval );
		} );
	});
	wp.customize('employees_function',function( value ) {
		value.bind( function( newval ) {
			$('.employee-position, .employee-social a').css('color', newval );
		} );
	});
	wp.customize('employees_body_text',function( value ) {
		value.bind( function( newval ) {
			$('.employee-desc').css('color', newval );
		} );
	});
	//Testimonials section
	wp.customize('testimonials_bg',function( value ) {
		value.bind( function( newval ) {
			$('.testimonials-area').css('background-color', newval );
		} );
	});
	wp.customize('testimonials_title',function( value ) {
		value.bind( function( newval ) {
			$('.testimonials-area .widget-title').css('color', newval );
		} );
	});
	wp.customize('testimonials_client',function( value ) {
		value.bind( function( newval ) {
			$('.client-name').css('color', newval );
		} );
	});	
	wp.customize('testimonials_function',function( value ) {
		value.bind( function( newval ) {
			$('.client-function').css('color', newval );
		} );
	});
	wp.customize('testimonials_body_bg',function( value ) {
		value.bind( function( newval ) {
			$('.testimonial-body').css('background-color', newval );
		} );
	});	
	wp.customize('testimonials_body_text',function( value ) {
		value.bind( function( newval ) {
			$('.testimonial-body').css('color', newval );
		} );
	});
	//Skills section
	wp.customize('skills_bg',function( value ) {
		value.bind( function( newval ) {
			$('.skills-area').css('background-color', newval );
		} );
	});
	wp.customize('skills_title',function( value ) {
		value.bind( function( newval ) {
			$('.skills-area .widget-title').css('color', newval );
		} );
	});
	wp.customize('skills_bar',function( value ) {
		value.bind( function( newval ) {
			$('.skill-bar div').css('background-color', newval );
		} );
	});
	wp.customize('skills_body_text',function( value ) {
		value.bind( function( newval ) {
			$('.skills-desc, .skills-list').css('color', newval );
		} );
	});
	//Facts section
	wp.customize('facts_bg',function( value ) {
		value.bind( function( newval ) {
			$('.facts-area').css('background-color', newval );
		} );
	});
	wp.customize('facts_title',function( value ) {
		value.bind( function( newval ) {
			$('.facts-area .widget-title').css('color', newval );
		} );
	});
	wp.customize('facts_numbers',function( value ) {
		value.bind( function( newval ) {
			$('.fact').css('color', newval );
		} );
	});
	wp.customize('facts_body_text',function( value ) {
		value.bind( function( newval ) {
			$('.fact-name').css('color', newval );
		} );
	});				
	//Clients section
	wp.customize('clients_bg',function( value ) {
		value.bind( function( newval ) {
			$('.clients-area').css('background-color', newval );
		} );
	});
	wp.customize('clients_title',function( value ) {
		value.bind( function( newval ) {
			$('.clients-area .widget-title').css('color', newval );
		} );
	});
	//Blockquote section
	wp.customize('blockquote_bg',function( value ) {
		value.bind( function( newval ) {
			$('.blockquote-area').css('background-color', newval );
		} );
	});
	wp.customize('blockquote_title',function( value ) {
		value.bind( function( newval ) {
			$('.blockquote-area .widget-title').css('color', newval );
		} );
	});
	wp.customize('blockquote_body_text',function( value ) {
		value.bind( function( newval ) {
			$('.blockquote-area blockquote').css('color', newval );
		} );
	});
	//Social section
	wp.customize('social_bg',function( value ) {
		value.bind( function( newval ) {
			$('.social-area').css('background-color', newval );
		} );
	});
	wp.customize('social_title',function( value ) {
		value.bind( function( newval ) {
			$('.social-area .widget-title').css('color', newval );
		} );
	});
	//Projects section
	wp.customize('projects_bg',function( value ) {
		value.bind( function( newval ) {
			$('.projects-area').css('background-color', newval );
		} );
	});
	wp.customize('projects_title',function( value ) {
		value.bind( function( newval ) {
			$('.projects-area .widget-title').css('color', newval );
		} );
	});
	wp.customize('projects_item_bg',function( value ) {
		value.bind( function( newval ) {
			$('.project-image').css('background-color', newval );
		} );
	});
	wp.customize('projects_icons',function( value ) {
		value.bind( function( newval ) {
			$('.link-icon, .pp-icon').css('color', newval );
		} );
	});
	//Latest news section
	wp.customize('latest_news_bg',function( value ) {
		value.bind( function( newval ) {
			$('.latest-news-area').css('background-color', newval );
		} );
	});
	wp.customize('latest_news_title',function( value ) {
		value.bind( function( newval ) {
			$('.latest-news-area .widget-title').css('color', newval );
		} );
	});
	wp.customize('latest_news_post_title',function( value ) {
		value.bind( function( newval ) {
			$('.latest-news-area .entry-title a').css('color', newval );
		} );
	});
	wp.customize('latest_news_body_text',function( value ) {
		value.bind( function( newval ) {
			$('.blog-post').css('color', newval );
		} );
	});
	wp.customize('latest_news_see_all',function( value ) {
		value.bind( function( newval ) {
			$('.all-news').css('color', newval );
			$('.all-news').css('border-color', newval );
		} );
	});
	//Call to action section
	wp.customize('action_area_bg',function( value ) {
		value.bind( function( newval ) {
			$('.action-area').css('background-color', newval );
		} );
	});
	wp.customize('action_area_title',function( value ) {
		value.bind( function( newval ) {
			$('.action-area .widget-title').css('color', newval );
		} );
	});	
	wp.customize('action_area_message',function( value ) {
		value.bind( function( newval ) {
			$('.action-text').css('color', newval );
		} );
	});
	wp.customize('action_area_btn',function( value ) {
		value.bind( function( newval ) {
			$('.call-to-action').css('background-color', newval );
		} );
	});
	wp.customize('action_area_btn_bs',function( value ) {
		value.bind( function( newval ) {
			$('.call-to-action').css('box-shadow', '0 5px 0' + newval );
		} );
	});	
	//Welcome area
	wp.customize('header_title_color',function( value ) {
		value.bind( function( newval ) {
			$('.welcome-title').css('color', newval );
		} );
	});
	wp.customize('header_desc_color',function( value ) {
		value.bind( function( newval ) {
			$('.welcome-desc').css('color', newval );
		} );
	});		
	wp.customize('header_btn_bg',function( value ) {
		value.bind( function( newval ) {
			$('.welcome-button').css('background-color', newval );
		} );
	});
	wp.customize('header_btn_bs',function( value ) {
		value.bind( function( newval ) {
			$('.welcome-button').css('box-shadow', '0 5px 0' + newval );
		} );
	});
	// Menu background
	wp.customize('menu_color',function( value ) {
		value.bind( function( newval ) {
			$('.top-bar').css('background-color', newval );
		} );
	});
	// Menu Links
	wp.customize('menu_links_color',function( value ) {
		value.bind( function( newval ) {
			$('.main-navigation a').css('color', newval );
		} );
	});		
	// Site title
	wp.customize('site_title_color',function( value ) {
		value.bind( function( newval ) {
			$('.site-title a').css('color', newval );
		} );
	});
	// Site description
	wp.customize('site_desc_color',function( value ) {
		value.bind( function( newval ) {
			$('.site-description').css('color', newval );
		} );
	});
	// Entry title
	wp.customize('entry_title_color',function( value ) {
		value.bind( function( newval ) {
			$('.hentry .entry-title, .hentry .entry-title a').css('color', newval );
		} );
	});
	// Body text color
	wp.customize('body_text_color',function( value ) {
		value.bind( function( newval ) {
			$('body').css('color', newval );
		} );
	});
	// Footer background
	wp.customize('footer_color',function( value ) {
		value.bind( function( newval ) {
			$('.footer-widget-area, .site-footer').css('background-color', newval );
		} );
	});		
	// Font sizes
	wp.customize('h1_size',function( value ) {
		value.bind( function( newval ) {
			$('h1').css('font-size', newval + 'px' );
		} );
	});	
    wp.customize('h2_size',function( value ) {
        value.bind( function( newval ) {
            $('h2').css('font-size', newval + 'px' );
        } );
    });	
    wp.customize('h3_size',function( value ) {
        value.bind( function( newval ) {
            $('h3').css('font-size', newval + 'px' );
        } );
    });
    wp.customize('h4_size',function( value ) {
        value.bind( function( newval ) {
            $('h4').css('font-size', newval + 'px' );
        } );
    });
    wp.customize('h5_size',function( value ) {
        value.bind( function( newval ) {
            $('h5').css('font-size', newval + 'px' );
        } );
    });
    wp.customize('h6_size',function( value ) {
        value.bind( function( newval ) {
            $('h6').css('font-size', newval + 'px' );
        } );
    });
    wp.customize('body_size',function( value ) {
        value.bind( function( newval ) {
            $('body').css('font-size', newval + 'px' );
        } );
    });
    wp.customize('widget_title_size',function( value ) {
        value.bind( function( newval ) {
            $('section .widget-title, .panel.widget .widget-title').css('font-size', newval + 'px' );
        } );
    });
    wp.customize('menu_size',function( value ) {
        value.bind( function( newval ) {
            $('.main-navigation li').css('font-size', newval + 'px' );
        } );
    });	
	//Logos
	wp.customize('logo_size',function( value ) {
		value.bind( function( newval ) {
			$('.site-logo').css('max-width', newval + 'px' );
		} );
	});
	//Logos
	wp.customize('wlogo_size',function( value ) {
		value.bind( function( newval ) {
			$('.welcome-logo').css('max-width', newval + 'px' );
		} );
	}); 	  
} )( jQuery );
