/**
 * Occasion Component
 *
 * @package Incassoos
 * @subpackage App/Components
 */
define([
	"vuex",
	"fsm",
	"services",
	"./../templates/occasion.html"
], function( Vuex, fsm, services, tmpl ) {
	/**
	 * Holds a reference to the shortcuts service
	 *
	 * @type {Object}
	 */
	var shortcutsService = services.get("shortcuts");

	return {
		template: tmpl,
		computed: Object.assign(Vuex.mapState("occasions", {
			/**
			 * Return whether the active occasion is closed
			 *
			 * @return {Boolean} Is the active occasion closed?
			 */
			isClosed: function( state ) {
				return state.active && !! state.active.closed;
			},

			/**
			 * Return whether the occasion panel is disabled
			 *
			 * @return {Boolean} Is the occasion button disabled?
			 */
			isDisabled: function() {
				return ! this.$fsmSeek([
					fsm.tr.START_OCCASION,
					fsm.tr.SELECT_OCCASION
				]);
			}
		}), Vuex.mapGetters("occasions", {
			"title": "getTitle"
		})),
		methods: Vuex.mapActions("occasions", {
			/**
			 * Open the occasion panel
			 *
			 * Dispatch the action without returning the promise
			 *
			 * @param  {Function} dispatch Dispatch method
			 * @return {Void}
			 */
			open: function( dispatch ) {
				dispatch("start");
			}
		}),

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
					"shift+alt+O": {
						label: "Occasion.OpenPanel",
						callback: function() {
							self.open();
						}
					}
				})
			);
		}
	};
});
