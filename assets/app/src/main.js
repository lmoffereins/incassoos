/**
 * Incassoos App main logic
 *
 * @package Incassoos
 * @subpackage App
 */
define([
	"vue",
	"store",
	"components",
	"./modules/bootstrap",
	"./modules/templates/main.html",

	// Load by file
	"./styles/main.scss",
	"./polyfills",
	"./modules/core/directives",
	"./modules/core/filters"
], function( Vue, store, components, bootstrap, tmpl ) {

	// Define main Vue instance
	window.vm = new Vue({
		el: "#root",
		store: store,
		template: tmpl,
		components: components,

		/**
		 * Bootstrap the application logic once the app is created
		 *
		 * @return {Void}
		 */
		created: function() {
			bootstrap(this);
		}
	});
});
