/**
 * Receipt Store Module
 *
 * @package Incassoos
 * @subpackage App/Store
 */
define([
	"q",
	"lodash",
	"api",
	"fsm",
	"util",
	"./util/list"
], function( Q, _, api, fsm, util, list ) {
	/**
	 * The module state data
	 *
	 * @type {Object}
	 */
	var state = {
		all: [],
		__feedback: []
	},

	/**
	 * Holds the feedback handler
	 *
	 * @type {Object}
	 */
	feedback = util.createFeedback({
		name: "receipt",
		persistent: true
	}),

	/**
	 * The module getter methods
	 *
	 * @type {Object}
	 */
	getters = list.getters({
		/**
		 * Return enhanced product items from the list
		 *
		 * @return {Array} List items
		 */
		getItems: function( state, getters, rootState, rootGetters ) {
			return state.all.map( function( i ) {

				// Get the product or default to the provided data
				var p = _.defaults({}, rootGetters["products/getItemById"](i.id) || {}, i);

				// Construct item
				return {
					id: i.id,
					title: p.title,
					price: p.price,
					quantity: i.quantity
				};
			});
		},

		/**
		 * Return the total price of the list
		 *
		 * @return {Float} Total price
		 */
		getTotalPrice: function( state, getters ) {
			return getters.getItems.reduce( function( total, i ) {
				return total + (i.price * i.quantity);
			}, 0);
		},

		/**
		 * Return the total count of list items
		 *
		 * @return {Number} Total count
		 */
		getTotalQuantity: function( state, getters ) {
			return getters.getItems.reduce( function( total, i ) {
				return total + i.quantity;
			}, 0);
		},

		/**
		 * Return the active details of the receipt
		 *
		 * @return {Object} Receipt data
		 */
		getReceipt: function( state, getters, rootState ) {
			return util.copy({
				consumer: rootState.consumers.active && rootState.consumers.active.id,
				id: rootState.orders.active && rootState.orders.active.id,
				items: getters.getItems,
				occasion: rootState.occasions.active && rootState.occasions.active.id
			});
		},

		/**
		 * Return whether the order in the receipt is editable
		 *
		 * @return {Boolean} Is the receipt editable?
		 */
		isEditable: function( state, getters, rootState, rootGetters ) {

			// Require items, selected consumer, selected occasion is not closed, order is not locked and no errors
			return state.all.length
				&& !! rootState.consumers.active
				&& !! rootState.occasions.active
				&& ! rootState.occasions.active.closed
				&& ! rootGetters["orders/isActiveItemLocked"]
				&& ! feedback.hasErrors();
		},

		/**
		 * Return whether the receipt is submittable
		 *
		 * @return {Boolean} Is the receipt submittable?
		 */
		isSubmittable: function( state, getters, rootState ) {

			// Require items, selected consumer, selected occasion, valid patches and no errors
			return state.all.length
				&& !! rootState.consumers.active
				&& !! rootState.occasions.active
				&& ! feedback.hasErrors();
		},

		/**
		 * Return whether the receipt is cancelable
		 *
		 * @return {Boolean} Is the receipt cancelable?
		 */
		isCancelable: function( state, getters, rootState ) {

			// Require item or selected consumer
			return state.all.length || !! rootState.consumers.active;
		}
	}, {
		feedback: feedback
	}),

	/**
	 * Add product to the list
	 *
	 * @param {Object} state Module state to modify
	 * @param {Object} payload Product id
	 * @return {Void}
	 */
	addItemToReceipt = function( state, payload ) {

		// Accept payload as just the id
		payload.id || (payload = { id: payload });

		// Add item to list
		state.all.push({
			id: payload.id,
			quantity: payload.quantity || 1
		});
	},

	/**
	 * Remove product from the list
	 *
	 * @param {Object} state Module state to modify
	 * @param {Object} payload Product id
	 * @return {Void}
	 */
	removeItemFromReceipt = function( state, payload ) {

		// Accept payload as just the id
		payload.id || (payload = { id: payload });

		// Find not matching items in list
		state.all = state.all.filter( function( i ) {
			return i.id !== payload.id;
		});
	},

	/**
	 * The module mutation methods
	 *
	 * @type {Object}
	 */
	mutations = list.mutations({
		/**
		 * Increment a product in the list
		 *
		 * @param  {Object} payload Product id
		 * @return {Void}
		 */
		incrementItem: function( state, payload ) {

			// Accept payload as just the id
			payload.id || (payload = { id: payload });

			// Default increment quantity to 1
			payload.quantity || (payload.quantity = 1);

			// Find item in list
			var item = state.all.find( function( i ) {
				return i.id === payload.id;
			});

			// Increment quantity
			if (! item) {
				addItemToReceipt(state, payload);
			} else {
				item.quantity += payload.quantity;

				// Remove item when made empty
				if (0 === item.quantity) {
					removeItemFromReceipt(state, payload);
				}
			}
		},

		/**
		 * Decrement a product in the list
		 *
		 * @param  {Object} payload Product id
		 * @return {Void}
		 */
		decrementItem: function( state, payload ) {

			// Accept payload as just the id
			payload.id || (payload = { id: payload });

			// Default decrement quantity to 1
			payload.quantity || (payload.quantity = 1);

			// Find item in list
			var item = state.all.find( function( i ) {
				return i.id === payload.id;
			});

			// Decrement quantity
			if (! item) {
				payload.quantity = -1 * Math.abs(payload.quantity);
				addItemToReceipt(state, payload);
			} else {
				item.quantity -= payload.quantity;

				// Remove item when made empty
				if (0 === item.quantity) {
					removeItemFromReceipt(state, payload);
				}
			}
		},

		/**
		 * Reset the list feedback
		 *
		 * Clears the list and re-evaluates feedback checks.
		 *
		 * @param {Object} context Store context
		 * @return {Void}
		 */
		resetFeedback: function( state, context ) {
			var totalPrice = context.rootGetters["receipt/getTotalPrice"];

			// When the consumer spending limit is passed
			if (! context.rootGetters["consumers/isWithinSpendingLimit"](null, totalPrice)) {
				feedback.add("spending-limit-reached", {
					isError: true,
					message: "Consumer.Error.SpendingLimitReached"
				});
			} else {
				feedback.remove("spending-limit-reached");
			}

			// Use list copy for state update
			state.__feedback = feedback.getList().slice();
		}
	}, {
		feedback: feedback
	}),

	/**
	 * The module action methods
	 *
	 * @type {Object}
	 */
	actions = list.actions({
		/**
		 * Setup receipt state listeners and setup initial receipt data
		 *
		 * @return {Promise} Load receipt data
		 */
		init: function( context ) {
			/**
			 * When modifying the receipt, always require an active occasion
			 *
			 * @return {Boolean} Transition success
			 */
			fsm.observe(
				"onBeforeTransition",
				function( lifecycle ) {

					// When transitioning from/to the receipt
					if (-1 !== [lifecycle.from, lifecycle.to].indexOf(fsm.st.RECEIPT)) {

						// Reject transition when no occasion is selected
						if (! context.rootState.occasions.active) {
							return Q.reject({
								isError: true,
								message: "Occasion.Error.NoOccasion",
								action: {
									label: "Occasion.OpenOccasion",
									callback: function() {
										fsm.do(fsm.tr.START_OCCASION);
									}
								}
							});
						}

						// Reject transition when the occasion is closed
						if (context.rootState.occasions.active.closed) {
							return Q.reject("Order.Error.OccasionClosed");
						}
					}
				}
			);

			/**
			 * When viewing an order, set receipt data from active order
			 *
			 * Consider both selecting an order, fresh or iteratively, and
			 * returning to the order from edit, either after save or cancel.
			 *
			 * @return {Void}
			 */
			fsm.observe([
				fsm.on.after.SELECT_ORDER,
				fsm.on.enter.VIEW_ORDER
			], function() {
					var order = context.rootState.orders.active;

					// Register new set of list items
					context.commit("setListItems", { items: order.items });
				}
			);

			/**
			 * When selecting a consumer, clear the list feedback
			 *
			 * @param  {Object} payload Product id
			 * @return {Void}
			 */
			fsm.observe(
				fsm.on.after.SELECT_CONSUMER,
				function( lifecycle, payload ) {

					// Reset feedback items
					context.commit("resetFeedback", context);
				}
			);

			/**
			 * When selecting a product, increment the receipt's product
			 *
			 * @param  {Object} payload Product id
			 * @return {Void}
			 */
			fsm.observe(
				fsm.on.after.INCREMENT_PRODUCT,
				function( lifecycle, payload ) {

					// Register new active product
					context.commit("incrementItem", payload);

					// Reset feedback items
					context.commit("resetFeedback", context);
				}
			);

			/**
			 * When unselecting a product, decrement the receipt's product
			 *
			 * @param  {Object} payload Product id
			 * @return {Void}
			 */
			fsm.observe(
				fsm.on.after.DECREMENT_PRODUCT,
				function( lifecycle, payload ) {

					// Register new active product
					context.commit("decrementItem", payload);

					// Reset feedback items
					context.commit("resetFeedback", context);
				}
			);

			/**
			 * When entering the IDLE state, clear receipt data
			 *
			 * @return {Promise} Was the receipt loaded?
			 */
			fsm.observe(
				fsm.on.enter.IDLE,
				function() {

					// Clear the receipt list
					context.commit("clearList");

					// Clear the list feedback
					context.commit("clearFeedback");
				}
			);
		},

		/**
		 * Transition when starting the receipt
		 *
		 * @return {Promise} Transition success
		 */
		start: function( context ) {
			return fsm.do(fsm.tr.START_RECEIPT);
		},

		/**
		 * Transition when opening or closing receipt settings
		 *
		 * @return {Promise} Transition success
		 */
		toggleSettings: function( context ) {
			return fsm.do(fsm.tr.TOGGLE_SETTINGS);
		},

		/**
		 * Transition when saving an order OR submitting the receipt
		 *
		 * @return {Promise} Transition success
		 */
		submit: function( context ) {
			return fsm.do([
				fsm.tr.SAVE_ORDER,
				fsm.tr.SUBMIT_RECEIPT
			], context.getters["getReceipt"]);
		},

		/**
		 * Transition when cancelling the order/receipt context
		 *
		 * @param {Object} payload Cancel parameters
		 * @return {Promise} Transition success
		 */
		cancel: function( context, payload ) {
			return payload && payload.close
				? fsm.do([
					fsm.tr.CLOSE_ITEM,
					fsm.tr.CANCEL_RECEIPT
				])
				: fsm.do([
					fsm.tr.CANCEL_EDIT,
					fsm.tr.CLOSE_ITEM,
					fsm.tr.CANCEL_RECEIPT
				]);
		}
	}, {
		feedback: feedback
	});

	return {
		namespaced: true,
		state: state,
		getters: getters,
		mutations: mutations,
		actions: actions
	};
});
