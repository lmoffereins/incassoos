/**
 * Global Leave Button Component
 *
 * @package Incassoos
 * @subpackage App/Components
 */
define([
	"settings"
], function( settings ) {
	return {
		template: '<button type="button" v-if="! $isLoggedIn" @click="leave" class="icon-button" v-l10n-title.Administration.GoToAdminLabel><i class="dashicons dashicons-exit"></i><span class="screen-reader-text" v-l10n.Administration.GoToAdminLabel></span></button>',
		methods: {
			/**
			 * Leave handler
			 *
			 * @return {Void}
			 */
			leave: function() {
				window.location = settings.adminUrl;
			}
		}
	};
});
