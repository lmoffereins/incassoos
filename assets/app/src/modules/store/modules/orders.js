/**
 * Orders Store Module
 *
 * @package Incassoos
 * @subpackage App/Store
 */
define([
	"q",
	"dayjs",
	"lodash",
	"api",
	"fsm",
	"services",
	"settings",
	"util",
	"./util/list"
], function( Q, dayjs, _, api, fsm, services, settings, util, list ) {
	/**
	 * The module state data
	 *
	 * @type {Object}
	 */
	var state = {
		all: [],
		searchQuery: "",
		active: null
	},

	/**
	 * Holds a reference to the localization service
	 *
	 * @type {Object}
	 */
	l10nService = services.get("l10n"),

	/**
	 * Holds a reference to the global feedback service
	 *
	 * @type {Object}
	 */
	feedbackService = services.get("feedback"),

	/**
	 * The module getter methods
	 *
	 * @type {Object}
	 */
	getters = list.getters({
		/**
		 * Return enhanced order items from the list
		 *
		 * @return {Array} List items
		 */
		getItems: function( state, getters, rootState, rootGetters ) {
			return state.all.map( function( i ) {

				// Get the consumer or default to empty object
				var consumer = rootGetters["consumers/getItemById"](i.consumer) || _.defaults(i.consumerData, {
					id: i.consumer,
					name: l10nService.get("Consumer.UnknownName")
				}),

				// Calculate total count
				totalQuantity = i.items.reduce( function( total, i ) {
					return total + i.quantity;
				}, 0),

				// Calculate total price
				totalPrice = i.items.reduce( function( total, i ) {
					return total + (i.price * i.quantity);
				}, 0);

				// Construct item
				return {
					id: i.id,
					date: i.date,
					consumer: consumer,
					items: i.items,
					totalQuantity: totalQuantity,
					totalPrice: totalPrice
				};
			});
		},

		/**
		 * Return a new active order
		 *
		 * @return {Void}
		 */
		getNewItem: function() {
			return {
				consumer: {
					id: 0
				},
				items: []
			};
		},

		/**
		 * Return all items of the consumer
		 *
		 * @return {Function} Getter method
		 */
		getItemsByConsumer: function( state, getters, rootState ) {
			/**
			 * Return all items of the consumer
			 *
			 * @param  {Object|Number} id Consumer object or id. Defaults to the active consumer.
			 * @return {Array} List items of the consumer
			 */
			return function( id ) {

				// Default to the active consumer
				if (! id && rootState.consumers.active) {
					id = rootState.consumers.active;
				}

				// Bail when no user was provided
				if (! id) {
					return [];
				}

				// Accept payload as consumer object
				id.id && (id = id.id);

				// Find item in list
				return getters.getItems.filter( function( i ) {
					return i.consumer.id
						? i.consumer.id === id
						: i.consumer === id;
				});
			};
		},

		/**
		 * Return the total price of the list items
		 *
		 * @return {Number} Total price
		 */
		getTotalPrice: function( state, getters ) {
			return getters.getItems.reduce( function( total, i ) {
				return total + i.totalPrice;
			}, 0);
		},

		/**
		 * Return the total count of list items
		 *
		 * @return {Number} Total count
		 */
		getTotalQuantity: function ( state, getters ) {
			return getters.getItems.reduce( function( total, i ) {
				return total + i.totalQuantity;
			}, 0);
		},

		/**
		 * Return whether the item is locked
		 *
		 * @return {Function} Getter method
		 */
		isItemLocked: function( state, getters ) {
			/**
			 * Return all items of the consumer
			 *
			 * @param  {Object|Number} id Order object or id. Defaults to the active order.
			 * @return {Boolean} Is the item locked?
			 */
			return function( id ) {

				// Bail when no order was provided or when the time lock is disabled
				if (! id || ! settings.order.orderTimeLock) {
					return false;
				}

				// Accept payload as order object or find item in list
				id.id || (id = getters.getItems.find( function( i ) {
					return i.id === id;
				}));

				// Compare dates. Consider locked when now is beyond the order date + time lock
				return dayjs(id.date).utc(true).add(settings.order.orderTimeLock, "minute").isBefore(dayjs()); 
			}
		},

		/**
		 * Return whether the active item is locked
		 *
		 * @return {Boolean} Is active item locked?
		 */
		isActiveItemLocked: function( state, getters ) {
			return !! state.active && getters.isItemLocked(state.active);
		}
	}),

	/**
	 * The module mutation methods
	 *
	 * @type {Object}
	 */
	mutations = list.mutations(),

	/**
	 * The module action methods
	 *
	 * @type {Object}
	 */
	actions = list.actions({
		/**
		 * Setup order state listeners
		 *
		 * @return {Void}
		 */
		init: function( context ) {
			/**
			 * When selecting an order, set the active order
			 *
			 * Act on BEFORE observer, so that other assets can read the active data in AFTER observers.
			 *
			 * @param {Object} payload Order id
			 * @return {Void}
			 */
			fsm.observe(
				fsm.on.before.SELECT_ORDER,
				function( lifecycle, payload ) {

					// Register new active order
					context.commit("setActive", payload);
				}
			);

			/**
			 * When going to edit an order
			 *
			 * @return {Promise} Transition success
			 */
			fsm.observe(
				fsm.on.before.EDIT_ITEM,
				function( lifecycle ) {

					// Reject transition when the occasion is closed
					if (fsm.st.EDIT_ORDER === lifecycle.to && context.rootState.occasions.active.closed) {
						return Q.reject("Order.Error.OccasionClosedEditing");
					}

					// Reject transition when the order is locked
					if (fsm.st.EDIT_ORDER === lifecycle.to && context.getters.isActiveItemLocked) {
						return Q.reject("Order.Error.TimeLocked");
					}

					// Reject transition when the user cannot edit orders
					// if (fsm.st.EDIT_ORDER === lifecycle.to && ! authService.userCan("edit_orders")) {
					// 	return Q.reject("Generic.Error.NotAllowed");
					// }
				}
			);

			/**
			 * When saving an order, update it
			 *
			 * @param {Object} payload Order details
			 * @return {Promise} Update success
			 */
			fsm.observe(
				fsm.on.before.SAVE_ORDER,
				function( lifecycle, payload ) {

					// Update the order
					return api.orders.update(payload).then( function( resp ) {

						// Report success message
						feedbackService.add({
							message: "Order.UpdatedOrder",
							data: {
								args: [resp.consumerData.name]
							}
						});

						// Update existing item in list
						context.dispatch("setActiveItemInList", resp);
					});
				}
			);

			/**
			 * When submitting the receipt, create the order
			 *
			 * @param  {Object} payload Receipt data from `getters["receipt/getReceipt"]`
			 * @return {Promise} Create success
			 */
			fsm.observe(
				fsm.on.before.SUBMIT_RECEIPT,
				function( lifecycle, payload ) {

					// Create new order
					return api.orders.create(payload).then( function( resp ) {

						// Report success message
						feedbackService.add({
							message: "Order.CreatedOrder",
							data: {
								args: [resp.consumerData.name]
							}
						});

						// Register new item in list
						context.commit("addItemToList", resp);
					});
				}
			);

			/**
			 * When cancelling the order edit, undo edits by restoring the
			 * active order
			 *
			 * @return {Void}
			 */
			fsm.observe([
				fsm.on.before.CANCEL_EDIT,
				fsm.on.before.CLOSE_ITEM
			], function( lifecycle ) {
					if (fsm.st.EDIT_ORDER === lifecycle.from) {

						// Reset active order, removing applied edits
						context.commit("setActive", { id: context.state.active.id });
					}
				}
			);

			/**
			 * When entering the IDLE state, clear the active order.
			 *
			 * @return {Void}
			 */
			fsm.observe(
				fsm.on.enter.IDLE,
				function() {

					// Clear active order
					context.commit("clearActive");
				}
			);
		},

		/**
		 * Load collection
		 *
		 * When the payload already contains items, use instead of an API call.
		 *
		 * @param {Object} payload Occasion data to get orders from.
		 * @return {Promise} Load of orders
		 */
		load: function( context, payload ) {
			return Q.Promisify(

				// When items are not provided, load them from the API
				payload.hasOwnProperty("items") ? payload.items : api.orders.get(payload, function( resp ) {

					// Register new set of list items continuously
					context.commit("setListItems", { items: resp });
				})

			).then( function( items ) {

				// Register new set of list items
				context.commit("setListItems", { items: items });
			});
		},

		/**
		 * Transition when selecting an order
		 *
		 * When already in the ORDER state, do not close the current item first.
		 *
		 * @param {Object} payload Order id
		 * @return {Promise} Transition success
		 */
		select: function( context, payload ) {
			return fsm.do(fsm.tr.SELECT_ORDER, payload);
		},

		/**
		 * Transition when editing an order
		 *
		 * @return {Promise} Transition success
		 */
		edit: function( context ) {
			return fsm.do(fsm.tr.EDIT_ITEM);
		},

		/**
		 * Transition when cancelling an order
		 *
		 * @return {Promise} Transition success
		 */
		cancel: function() {
			return fsm.do(fsm.tr.CLOSE_ITEM);
		}
	});

	return {
		namespaced: true,
		state: state,
		getters: getters,
		mutations: mutations,
		actions: actions
	};
});
