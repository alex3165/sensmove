//Sets the header image height
jQuery(function($) {
	if ( $(window).width() > 1024 ) {
		var height = $(window).height(); 
		$('.has-banner, .overlay').css('height', height);
		$(window).resize(function() {
			var height = $(window).height(); 
			$('.has-banner, .overlay').css('height', height);
		});
	}
	$(window).resize(function(){	
		if ($(".header-image").css("display") == "none" ){
			var height = $(window).height(); 
			$('.has-banner, .overlay').css('height', height);
		} else {
			$('.has-banner, .overlay').css('height', 'auto');
		}
	});
});

//Page loader
jQuery(document).ready(function($) {
	$("#page").show();
});

//Menu dropdown animation
jQuery(function($) {
	$('.sub-menu').hide();
	$('.main-navigation .children').hide();
	$('.menu-item').hover( 
		function() {
			$(this).children('.sub-menu').slideDown();
		}, 
		function() {
			$(this).children('.sub-menu').hide();
		}
	);
	$('.main-navigation li').hover( 
		function() {
			$(this).children('.main-navigation .children').slideDown();
		}, 
		function() {
			$(this).children('.main-navigation .children').hide();
		}
	);	
});

//Fit Vids
jQuery(function($) {
  
  $(document).ready(function(){
    $("body").fitVids();
  });
  
});

//Waypoints
jQuery(function($) {
	$('.fact').waypoint(function(down) {
		$('.fact').each(function () {
			var $this = $(this);
			$({ Counter: 0 }).animate({ Counter: $this.attr('id') }, {
				duration: 1000,
				easing: 'swing',
				step: function () {
				    $this.text(Math.ceil(this.Counter));
				}
			});
		});
	},	
	{
	  offset: '90%',
	  triggerOnce: true
	});
	$('.skill-bar').waypoint(function(down) {
		$('.skill-bar').each(function() { 
			var bar = $(this);
			var max = $(this).attr('id');
			var progressBarWidth = max * bar.width() / 100;
			bar.find('div').animate({ width: progressBarWidth }, 1000).html(max + "%&nbsp;");
		});
	},	
	{
	  offset: '90%',
	  triggerOnce: true
	});			
});	

//Make the menu sticky
jQuery(function($) {
	$(window).bind("load", function() {
		$(".top-bar").sticky();
	});	
});

//Better support for third party widgets
jQuery(function($) {
    $('.so-panel.widget, .panel.widget').addClass('container');
}); 

//Open social links in a new tab
jQuery(function($) {
     $( '.social-area li a, .social-widget a' ).attr( 'target','_blank' );
});

//Smooth scrolling
jQuery(function($) {
	$('a[href*=#]:not([href=#])').click(function() {
		if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
			var target = $(this.hash);
			var topbar = $('.top-bar').height();
			target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
			if ( this.hash != '#site-navigation' && $(window).width() > 1024 ) {
				if (target.length) {
				$('html,body').animate({
				scrollTop: target.offset().top - topbar + 40
				}, 800);
				return false;
				}
			} else {
				if (target.length) {
				$('html,body').animate({
				scrollTop: target.offset().top
				}, 800);
				return false;
				}				
			}
		}
	});
});

//Search form
jQuery(function($) {
	$('.nav-search').click(function() {
		$('.nav-search-box').addClass('search-visible');
	});
	$('.search-close').click(function() {
		$('.nav-search-box').removeClass('search-visible');
	});			
});