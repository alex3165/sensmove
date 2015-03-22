requirejs.config({
    paths: {
          'jquery': 'js/libs/jquery', /* Javascript extension library */
        'parallax': 'js/libs/parallax', /* Parallax.js library to make parallax effect */
      'underscore': 'node_modules/underscore/underscore-min', /* Methods that help to manipulate javascript */
      'handlebars': 'node_modules/handlebars/handlebars.min' /* Library that make data-binding easier */
    }
});

requirejs(['jquery'], function ($) {

	$(document).ready(function() {

		var layers = $('section');

		$('.nav').on('click', function(){
			
			$('.nav').removeClass('active');
			$(this).addClass('active');

			layers.hide();
			var layerToShow = $(this).attr('href').replace('#', '');
			var elementToShow = layers.filter('.' + layerToShow);
			elementToShow.show();
		});
	});

});