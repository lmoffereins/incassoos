/**
 * Global Login Toggle Component
 *
 * @package Incassoos
 * @subpackage App/Components
 */
define([
	"fsm"
], function( fsm ) {
	return {
		template: '<button type="button" @click="toggle" class="icon-button" v-l10n-title.Common.Account><i class="dashicons dashicons-admin-users"></i><span class="screen-reader-text" v-l10n.Common.Account></span></button>',
		methods: {
			/**
			 * Reset handler
			 *
			 * TODO: move logic to fsm
			 *
			 * @return {Void}
			 */
			toggle: function() {
				fsm.is(fsm.st.LOGIN)
					? fsm.do(fsm.tr.CLOSE_LOGIN)
					: fsm.do(fsm.tr.OPEN_LOGIN);
			}
		}
	};
});
