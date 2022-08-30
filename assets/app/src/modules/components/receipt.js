/**
 * Receipt Component
 *
 * @package Incassoos
 * @subpackage App/Components
 */
define([
	"vuex",
	"fsm",
	"services",
	"settings",
	"util",
	"./feedback",
	"./util/close-button",
	"./../templates/receipt.html"
], function( Vuex, fsm, services, settings, util, feedback, closeButton, tmpl ) {
	/**
	 * Holds a reference to the shortcuts service
	 *
	 * @type {Object}
	 */
	var shortcutsService = services.get("shortcuts");

	return {
		template: tmpl,
		components: {
			closeButton: closeButton,
			feedback: feedback
		},
		data: function() {
			return {
				defaultAvatarUrl: settings.consumer.defaultAvatarUrl
			};
		},
		computed: Object.assign({
			/**
			 * Return whether the receipt is active
			 *
			 * @return {Boolean} Is the receipt active?
			 */
			isActive: function() {
				return this.$fsmIs([
					fsm.st.RECEIPT,
					fsm.st.RECEIPT_SETTINGS,
					fsm.st.VIEW_ORDER,
					fsm.st.EDIT_ORDER
				]);
			},

			/**
			 * Return whether we're in the order viewing state
			 *
			 * @return {Boolean} Is the order being viewed?
			 */
			isViewing: function() {
				return this.$fsmIs(fsm.st.VIEW_ORDER);
			},

			/**
			 * Return whether we're in the order creating state
			 *
			 * @return {Boolean} Is the order being created?
			 */
			isCreating: function() {
				return this.$fsmIs(fsm.st.RECEIPT);
			},

			/**
			 * Return whether we're in the order editing state
			 *
			 * @return {Boolean} Is the order being edited?
			 */
			isEditing: function() {
				return this.$fsmIs([
					fsm.st.RECEIPT,
					fsm.st.EDIT_ORDER
				]);
			},

			/**
			 * Return the submit label
			 *
			 * @return {String} Label
			 */
			labelSubmit: function() {
				return this.$fsmIs(fsm.st.EDIT_ORDER) ? 'Common.Update' : 'Common.Checkout';
			},

			/**
			 * Return whether the edited order has patches
			 *
			 * @return {Boolean} Has the order patches?
			 */
			hasPatches: function() {
				return false;
			}
		}, Vuex.mapGetters("receipt", {
			"feedback": "getFeedback",
			"totalPrice": "getTotalPrice",
			"editable": "isEditable",
			"submittable": "isSubmittable"
		}), Vuex.mapState("receipt", {
			/**
			 * Return the receipt items in reverse
			 *
			 * @return {Array} Receipt items
			 */
			receipt: function( state, getters ) {
				var items = getters.getItems.slice();

				// Reverse the items
				items.reverse();

				return items;
			}
		}), Vuex.mapState("consumers", {
			/**
			 * Return the active consumer
			 *
			 * @return {Object} Consumer object
			 */
			consumer: function( state ) {
				return state.active || { name: "Receipt.SelectAConsumer", avatarUrl: this.defaultAvatarUrl, group: {} };
			}
		}), Vuex.mapState("orders", {
			/**
			 * Return the active consumer
			 *
			 * @return {Object} Consumer object
			 */
			order: function( state ) {
				return state.active;
			}
		})),
		methods: Object.assign(Vuex.mapMutations("receipt", {
			"clear": "clearList",
			"clearFeedback": "clearFeedback"
		}), Vuex.mapActions("orders", {
			/**
			 * Edit the active item
			 *
			 * Dispatch the action without returning the promise.
			 *
			 * @param  {Function} dispatch Dispatch method
			 * @return {Void}
			 */
			edit: function( dispatch ) {
				dispatch("edit");
			}
		}), Vuex.mapActions("receipt", {
			/**
			 * Create new order or save changes for the active item
			 *
			 * Dispatch the action without returning the promise.
			 *
			 * @param  {Function} dispatch Dispatch method
			 * @return {Void}
			 */
			submit: function( dispatch ) {
				dispatch("submit");
			},

			/**
			 * Cancel the current state
			 *
			 * Dispatch the action without returning the promise.
			 *
			 * @param  {Function} dispatch Dispatch method
			 * @return {Void}
			 */
			cancel: function( dispatch ) {
				dispatch("cancel");
			}
		})),
		watch: Object.assign({
			/**
			 * Act when the receipt's total price changed
			 *
			 * @return {Void}
			 */
			totalPrice: function() {
				util.triggerElementChanged(this.$el);
			}
		}, Vuex.mapActions("receipt", {
			/**
			 * Act when the receipt's list is modified
			 *
			 * @param  {Function} dispatch Dispatch method
			 * @param  {Array} items Current list value
			 * @return {Void}
			 */
			receipt: function( dispatch, items ) {

				// Start the receipt when it contains items
				if (items.length) {
					dispatch("start");

				// Stop the receipt when there is no active consumer
				} else if (! this.consumer.id) {
					dispatch("cancel");

				// Clear feedback when the list is empty
				} else {
					this.clearFeedback();
				}
			}
		})),

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
					"escape": function() {
						self.cancel();
					}
				})
			);

			// Update values in component when settings are updated
			this.$registerUnobservable(
				settings.$onUpdate( function() {
					self.defaultAvatarUrl = settings.consumer.defaultAvatarUrl;
				})
			);
		}
	};
});
