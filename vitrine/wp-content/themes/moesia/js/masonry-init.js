
//Masonry init
jQuery(function($) {
	var $container = $('.home-wrapper');
	$container.imagesLoaded( function() {
		$container.masonry({
			itemSelector: '.hentry',
	        isAnimated: true,
			isFitWidth: true,
			animationOptions: {
				duration: 500,
				easing: 'linear',
			}
	    });
	});
});