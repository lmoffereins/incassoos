/**
 * Orders Component
 *
 * @package Incassoos
 * @subpackage App/Components
 */
define([
	"vuex",
	"lodash",
	"fsm",
	"./../templates/orders.html"
], function( Vuex, _, fsm, tmpl ) {
	return {
		template: tmpl,
		data: function() {
			return {
				focusGroupKey: 0
			};
		},
		computed: Object.assign({
			/**
			 * Return arguments for the `Consumer.ConsumerConsumedAtDate` translator
			 *
			 * @param  {Object} item Order item
			 * @return {Array} Text arguments
			 */
			consumerConsumedAtDateArgs: function() {
				return function( item ) {
					return [
						item.consumer.name,
						this.$options.filters.money(item.totalPrice),
						this.$options.filters.date(item.date)
					];
				};
			},

			/**
			 * Return whether an order is being viewed
			 *
			 * @return {Boolean} Is an order being viewed?
			 */
			isViewOrder: function() {
				return fsm.is(fsm.st.VIEW_ORDER);
			}
		}, Vuex.mapState("orders", {
			/**
			 * Return all order items
			 *
			 * @return {Array} Order items
			 */
			orders: function( state, getters ) {
				return _.orderBy(getters.getItems, "date", "desc");
			},

			/**
			 * Return the index of the active order
			 *
			 * @return {Number} Active order index
			 */
			activeOrderIx: function( state ) {
				return this.orders.findIndex( function( i ) {
					return state.active && i.id === state.active.id;
				});
			},

			/**
			 * Return the previous order id
			 *
			 * @return {Number} Order id
			 */
			previousOrderId: function( state, getters ) {
				var currIx = this.activeOrderIx;

				return -1 !== currIx && currIx < this.orders.length - 1 ? this.orders[currIx + 1].id : false;
			},

			/**
			 * Return the next order id
			 *
			 * @return {Number} Order id
			 */
			nextOrderId: function( state, getters ) {
				var currIx = this.activeOrderIx;

				return -1 !== currIx && currIx > 0 ? this.orders[currIx - 1].id : false;
			},

			/**
			 * Return whether the order is selected
			 *
			 * @param  {Number}  id Order identifier
			 * @return {Boolean} Is the order selected?
			 */
			isSelected: function( state ) {
				return function( id ) {
					return state.active && state.active.id === id;
				}
			},

			/**
			 * Return whether any orders are listed
			 *
			 * @return {Boolean} Are any orders listed?
			 */
			noOrders: function( state, getters ) {
				return this.noOccasion ? false : ! getters.getItems.length;
			}
		}), Vuex.mapState("occasions", {
			/**
			 * Return whether an occasion is selected
			 *
			 * @return {Boolean} Is an occasion selected?
			 */
			noOccasion: function( state ) {
				return ! (state.active && state.active.id);
			},

			/**
			 * Return whether the active occasion is closed
			 *
			 * @return {Boolean} Is the active occasion closed?
			 */
			isOccasionClosed: function( state ) {
				return state.active && !! state.active.closed;
			}
		})),
		methods: Vuex.mapActions("orders", {
			/**
			 * Dispatch the action without returning the promise
			 *
			 * @param  {Function} dispatch Dispatch method
			 * @param  {Number} payload Order identifier
			 * @return {Void}
			 */
			select: function( dispatch, payload ) {

				// Apply toggle when order is already selected
				if (this.isSelected(payload)) {
					dispatch("cancel");

				// When in settings, view the order's consumer
				} else if (fsm.is(fsm.st.SETTINGS)) {
					var order = this.$store.getters["orders/getItemById"](payload);

					if (order) {
						this.$store.dispatch("consumers/select", order.consumer.id);
					}

				// Open the order
				} else {
					dispatch("select", payload);
				}
			},

			/**
			 * Dispatch the action without returning the promise
			 *
			 * @param  {Function} dispatch Dispatch method
			 * @return {Void}
			 */
			cancel: function( dispatch ) {
				dispatch("cancel");
			}
		}),
		watch: {
			/**
			 * Act when the list of items changes
			 *
			 * @return {Void}
			 */
			orders: function() {

				// Update focus group key
				this.focusGroupKey++;
			}
		},

		/**
		 * Register listeners when the component is created
		 *
		 * @return {Void}
		 */
		created: function() {
			var self = this,

			/**
			 * Set the active order from the previous order
			 *
			 * @return {Void}
			 */
			onSelectPreviousOrder = function () {
				self.previousOrderId && self.select(self.previousOrderId);
			},

			/**
			 * Set the active order from the next order
			 *
			 * @return {Void}
			 */
			onSelectNextOrder = function () {
				self.nextOrderId && self.select(self.nextOrderId);
			};

			// Subscribe to external events
			this.$root.$on("receipt/select-previous-order", onSelectPreviousOrder);
			this.$root.$on("receipt/select-next-order", onSelectNextOrder);
			this.$registerUnobservable( function() {
				self.$root.$off("receipt/select-previous-order", onSelectPreviousOrder);
				self.$root.$off("receipt/select-next-order", onSelectNextOrder);
			});
		}
	};
});
