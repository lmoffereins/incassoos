/**
 * Close Button Utility Component
 *
 * @package Incassoos
 * @subpackage App/Components/Util
 */
define([
	"./../../templates/util/close-button.html"
], function( tmpl ) {
	return {
		template: tmpl,
		methods: {
			/**
			 * Close with the button
			 *
			 * @return {Void}
			 */
			close: function() {
				this.$emit("click");
			}
		}
	};
});