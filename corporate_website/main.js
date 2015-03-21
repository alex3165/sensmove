requirejs.config({
    paths: {
          'jquery': 'js/libs/jquery', /* Javascript extension library */
        'parallax': 'js/libs/parallax', /* Parallax.js library to make parallax effect */
      'underscore': 'node_modules/underscore/underscore-min', /* Methods that help to manipulate javascript */
        'backbone': 'node_modules/backbone/backbone-min', /* Javascript MVC framework */
      'marionette': 'node_modules/backbone.marionette/lib/backbone.marionette.min', /* Backbone overlay that make development more easy */
      'handlebars': 'node_modules/handlebars/handlebars.min' /* Library that make data-binding easier */
    }
});

requirejs(['backbone', 'marionette'], function (Backbone, Marionette) {
	
});