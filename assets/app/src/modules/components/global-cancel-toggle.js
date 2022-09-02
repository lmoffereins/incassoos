/**
 * Global Cancel/Close Toggle Component
 *
 * @package Incassoos
 * @subpackage App/Components
 */
define([
	"fsm",
	"services"
], function( fsm, services ) {
	/**
	 * Holds a reference to the shortcuts service
	 *
	 * @type {Object}
	 */
	var shortcutsService = services.get("shortcuts"),

	/**
	 * Holds the transitions that cancel the state
	 *
	 * Either: cancel the current state or close an item
	 *
	 * @type {Array}
	 */
	cancelTransitions = [
		fsm.tr.CANCEL_DELETE,
		fsm.tr.CANCEL_EDIT,
		fsm.tr.CLOSE_ITEM,
		fsm.tr.CANCEL_OCCASION,
		fsm.tr.CANCEL_RECEIPT,
		fsm.tr.CLOSE_SETTINGS
	];

	return {
		template: '<button type="button" v-if="cancelable" @click="cancel" class="icon-button" v-l10n-title.Common.Cancel><i class="dashicons dashicons-undo"></i><span class="screen-reader-text" v-l10n.Common.Cancel></span></button>',
		computed: {
			/**
			 * Return whether the current state can be cancelled
			 *
			 * @return {Boolean} Is the current state cancelable?
			 */
			cancelable: function() {
				return this.$fsmSeek(cancelTransitions);
			}
		},
		methods: {
			/**
			 * Transition when cancelling the current state
			 *
			 * @return {Void}
			 */
			cancel: function() {
				fsm.do(cancelTransitions);
			}
		},

		/**
		 * Register listeners when the component is created
		 *
		 * @return {Void}
		 */
		created: function() {
			var self = this;

			// Register global keyboard event listeners
			this.$registerUnobservable(
				shortcutsService.on({
					"escape": function globalCancelToggleOnEscape() {
						self.cancel();
					}
				})
			);
		}
	};
});
