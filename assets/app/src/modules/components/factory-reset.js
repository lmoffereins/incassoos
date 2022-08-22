/**
 * Reset Component
 *
 * @package Incassoos
 * @subpackage App/Components
 */
define([
	"services"
], function( services ) {
	return {
		template: '<button type="button" @click="reset" v-l10n.Common.Reset></button>',
		methods: {
			/**
			 * Reset handler
			 *
			 * @return {Void}
			 */
			reset: function() {
				services.reset().then( function() {

					// Reload the page
					window.location.reload(false);
				});
			}
		}
	};
});
