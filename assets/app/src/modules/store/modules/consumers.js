/**
 * Consumers Store Module
 *
 * @package Incassoos
 * @subpackage App/Store
 */
define([
	"q",
	"lodash",
	"api",
	"fsm",
	"services",
	"util",
	"./util/list"
], function( Q, _, api, fsm, services, util, list ) {
	/**
	 * The module state data
	 *
	 * @type {Object}
	 */
	var state = {
		all: [],
		types: [],
		searchQuery: "",
		active: null,
		__feedback: []
	},

	/**
	 * Holds a reference to the localization service
	 *
	 * @type {Object}
	 */
	l10nService = services.get("l10n"),

	/**
	 * Holds a reference to the feedback service
	 *
	 * @type {Object}
	 */
	feedbackService = services.get("feedback"),

	/**
	 * Holds the feedback handler
	 *
	 * @type {Object}
	 */
	feedback = util.createFeedback({
		name: "consumers",
		persistent: true
	}),

	/**
	 * Define sanitization function for consumers
	 *
	 * @type {Function} Consumer sanitization
	 */
	sanitize = util.sanitization({
		/**
		 * Sanitization of the `limit` consumer property
		 *
		 * @param  {String} input Input value
		 * @return {Float} Sanitized value
		 */
		limit: util.sanitizePrice,

		/**
		 * Sanitization of the `show` consumer property
		 *
		 * @param  {Mixed} input Input value
		 * @return {Boolean} Sanitized value
		 */
		show: function( input ) {
			return !! input;
		}
	}),

	/**
	 * Holds validation functions for editable properties
	 *
	 * @type {Object} Item property validators
	 */
	validators = {
		/**
		 * Validation of the `spendingLimit` consumer property
		 *
		 * @param  {String} input Sanitized input value
		 * @return {Boolean|String} Validation success or error code
		 */
		spendingLimit: function( input ) {
			var validated = true;

			// Value should be a number
			if (! _.isNumber(input)) {
				validated = false;

			// Value should be 0 or higher
			} else if (0 > input) {
				validated = "Consumer.Error.SpendingLimitShouldBeZeroOrHigher";
			}

			return validated;
		},

		/**
		 * Validation of the `show` consumer property
		 *
		 * @param  {Boolean} input Sanitized input value
		 * @return {Boolean} Validation success
		 */
		show: _.isBoolean
	},

	/**
	 * Define validation function for consumers
	 *
	 * @type {Function} Consumer validation
	 */
	validate = util.validation(validators, function( id ) {
		return state.all.find( function( i ) {
			return i.id === id;
		});
	}),

	/**
	 * The module getter methods
	 *
	 * @type {Object}
	 */
	getters = list.getters({
		/**
		 * Return all items in the list
		 *
		 * Collection contains the list items and consumer types.
		 *
		 * @return {Array} List items and consumer types
		 */
		getItems: function ( state ) {
			return state.all.concat(state.types);
		},

		/**
		 * Return just the items in the list
		 *
		 * @return {Array} List items
		 */
		getConsumers: function( state ) {
			return state.all;
		},

		/**
		 * Return just the consumer types
		 *
		 * @return {Array} Types
		 */
		getTypes: function ( state ) {
			return state.types;
		},

		/**
		 * Return whether the active item is editable
		 *
		 * @return {Boolean} Is the active item editable?
		 */
		isEditable: function( state ) {
			return state.active && ! state.active.isConsumerType;
		},

		/**
		 * Return whether the active item is submittable
		 *
		 * Provide app states that allow submitting.
		 *
		 * @return {Boolean} Is the active item submittable?
		 */
		isSubmittable: list.isSubmittable([
			fsm.st.EDIT_CONSUMER
		]),

		/**
		 * Return the order count of the active consumer in the context
		 * of the active occasion.
		 *
		 * @return {Function} Getter method
		 */
		getOrderCount: function( state, getters, rootState, rootGetters ) {
			/**
			 * Return the order count of the active consumer
			 *
			 * @param {Number} id Consumer id. Defaults to the active consumer.
			 * @return {Float} Consumer order count
			 */
			return function( id ) {

				// Calculate order count
				return rootGetters["orders/getItemsByConsumer"](id).length;
			};
		},

		/**
		 * Return the total count of the active consumer in the context of
		 * the active occasion.
		 *
		 * @return {Function} Getter method
		 */
		getTotalProductQuantity: function( state, getters, rootState, rootGetters ) {
			/**
			 * Return the total count of the active consumer
			 *
			 * @param {Number} id Consumer id. Defaults to the active consumer.
			 * @return {Float} Consumer total count
			 */
			return function( id ) {

				// Calculate total count
				return rootGetters["orders/getItemsByConsumer"](id).reduce( function( total, i ) {
					return total + i.totalQuantity;
				}, 0);
			};
		},

		/**
		 * Return the total consumed value of the active consumer in the context
		 * of the active occasion.
		 *
		 * @return {Function} Getter method
		 */
		getTotalConsumedValue: function( state, getters, rootState, rootGetters ) {
			/**
			 * Return the total consumed value of the active consumer
			 *
			 * @param {Number} id Consumer id. Defaults to the active consumer.
			 * @return {Float} Consumer total consumed value
			 */
			return function( id ) {

				// Calculate total price
				return rootGetters["orders/getItemsByConsumer"](id).reduce( function( total, i ) {
					return total + i.totalPrice;
				}, 0);
			};
		},

		/**
		 * Return whether the active consumer is within their spending limit in the
		 * context of the active occasion.
		 *
		 * @return {Function} Getter method
		 */
		isWithinSpendingLimit: function( state, getters ) {
			/**
			 * Return whether the active consumer is within their spending limit
			 *
			 * @param {Number} id Optional. Consumer id or object. Defaults to the active consumer.
			 * @param {Number} nextOrderValue Optional. The expected next order to incorporate
			 * @return {Boolean} Is the spending limit safe?
			 */
			return function( id, nextOrderValue ) {

				// Accept payload as consumer object, default to the active consumer
				var consumer = (id && id.id) ? id : (id ? getters["getItemById"](id) : state.active),
				    isWithinLimit = true;

				// When available check limit against total and expected consumed values
				if (consumer && consumer.spendingLimit) {
					isWithinLimit = (getters["getTotalConsumedValue"](consumer) + Number(nextOrderValue || 0)) <= consumer.spendingLimit;
				}

				return isWithinLimit;
			};
		}
	}, {
		validators: validators,
		feedback: feedback
	}),

	/**
	 * The module mutation methods
	 *
	 * @type {Object}
	 */
	mutations = list.mutations({
		/**
		 * Set the active active item.
		 *
		 * This handles also consumer types.
		 *
		 * @param {Object} payload The item id to select
		 * @return {Void}
		 */
		setActive: function( state, payload ) {

			// Accept payload as just the id
			payload.id || (payload = {id: payload});

			// Find item in list
			state.active = util.copy(state.all.concat(state.types).find( function( i ) {
				return i.id === payload.id;
			}) || payload || null);
		},

		/**
		 * Set the types
		 *
		 * @param {Object} payload Containing the new types in `types`.
		 * @return {Void}
		 */
		setTypes: function( state, payload ) {
			state.types = payload.types;
		},

		/**
		 * Modify the active consumer
		 *
		 * @param  {Object} payload Consumer data
		 * @return {Void}
		 */
		patchActive: list.patchActive(sanitize, validate, feedback),

		/**
		 * Toggle the show status of the active item
		 *
		 * @param {Object|Number} payload Consumer data or consumer id
		 * @return {Void}
		 */
		toggleShow: function( state, payload ) {
			var item;

			// Accept payload as just the id
			payload.id || (payload = { id: payload });

			// Find item in list
			item = state.all.find( function( i ) {
				return i.id === payload.id;
			});

			// Toggle show status
			item.show = (! item.show);
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
		 * Setup consumer state listeners and setup initial collection
		 *
		 * @return {Promise} Load of consumers
		 */
		init: function( context ) {
			/**
			 * When selecting a consumer, set the active consumer
			 *
			 * @param  {Object} payload Consumer id
			 * @return {Void}
			 */
			fsm.observe(
				fsm.on.after.SELECT_CONSUMER,
				function( lifecycle, payload ) {

					// Make consumer active
					context.commit("setActive", payload);
				}
			);

			/**
			 * When saving a consumer, update it
			 *
			 * @return {Promise} Update success
			 */
			fsm.observe(
				fsm.on.before.SAVE_CONSUMER,
				function( lifecycle, payload ) {

					// Bail when the payload contains errors
					if (validate(payload).length) {
						return Q.reject();
					}

					// Update the user
					return api.consumers.update(payload).then( function( resp ) {

						// Report success message
						feedbackService.add({
							message: "Consumer.UpdatedConsumer",
							data: {
								args: [resp.name]
							}
						});

						// Update existing item in list
						context.dispatch("setActiveItemInList", resp);
					});
				}
			);

			/**
			 * When cancelling the consumer edit, undo edits by restoring the
			 * active consumer
			 *
			 * @return {Void}
			 */
			fsm.observe(
				fsm.on.before.CANCEL_EDIT,
				function( lifecycle ) {
					if (fsm.st.EDIT_CONSUMER === lifecycle.from) {

						// Reset active consumer, removing applied edits
						context.commit("setActive", { id: context.state.active.id });

						// Clear list feedback
						context.commit("clearFeedback");
					}
				}
			);

			/**
			 * When entering the SETTINGS state, clear the active consumer.
			 *
			 * @return {Void}
			 */
			fsm.observe(
				fsm.on.enter.SETTINGS,
				function( lifecycle ) {

					// Clear active consumer
					context.commit("clearActive");

					// Clear list feedback
					context.commit("clearFeedback");
				}
			);

			/**
			 * When entering the IDLE state, clear the active consumer.
			 *
			 * @return {Void}
			 */
			fsm.observe(
				fsm.on.enter.IDLE,
				function( lifecycle, payload ) {
					var order;

					// Ignore close-and-open-order transitions when they concern the same consumer
					if (fsm.st.VIEW_ORDER === lifecycle.from) {
						order = payload && context.rootGetters["orders/getItemById"](payload);

						// Bail when the consumer stays the same
						if (order && (order.consumer.id === context.state.active.id)) {
							return;
						}
					}

					// Clear active consumer
					context.commit("clearActive");
				}
			);

			/**
			 * When selecting an order, set consumer data from active order
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

					// Register new active consumer
					context.commit("setActive", { id: order.consumer, name: order.consumerData.name });
				}
			);
		},

		/**
		 * Load the list of the consumer collection
		 *
		 * @return {Promise} Was the data loaded?
		 */
		load: function( context ) {

			// Request consumer types and consumers, list the items
			return Q.all([
				api.consumerTypes.get( function( resp ) {

					// Continuously update set of types on stream
					context.commit("setTypes", { types: resp });
				}),
				api.consumers.get( function( resp ) {

					// Continuously update set of list items on stream
					context.commit("setListItems", { items: resp });
				})
			]).then( function( data ) {

				// Bail when consumer data is not an array
				if (! Array.isArray(data[1])) {
					return data[1];
				}

				// Register full set of types
				context.commit("setTypes", { types: data[0] });

				// Register full set of list items
				context.commit("setListItems", { items: data[1] });
			});
		},

		/**
		 * Transition when selecting a consumer OR set the active consumer
		 *
		 * When already in the CONSUMER state, do not close the active item first.
		 *
		 * @param {Object} payload Consumer id
		 * @return {Promise} Transition success
		 */
		select: function( context, payload ) {

			// When ...
			if (fsm.is([
				fsm.st.IDLE,      // ... starting the receipt
				fsm.st.RECEIPT,   // ... setting up the receipt
				fsm.st.EDIT_ORDER // ... editing an order
			])) {

				// First open the receipt when not open already
				return Q.Promisify((! fsm.is(fsm.st.IDLE)) || fsm.do(fsm.tr.START_RECEIPT)).then( function() {
					return fsm.do(fsm.tr.SELECT_CONSUMER, payload);
				});

			// Select a consumer
			} else {
				return fsm.do(fsm.tr.SELECT_CONSUMER, payload);
			}
		},

		/**
		 * Transition when editing the active item
		 *
		 * @return {Promise} Transition success
		 */
		edit: function() {
			return fsm.do(fsm.tr.EDIT_ITEM);
		},

		/**
		 * Patch the active item
		 *
		 * @param  {Object} payload Property patches
		 * @return {Void}
		 */
		patch: function( context, payload ) {
			context.commit("patchActive", payload);
		},

		/**
		 * Transition when saving the active item
		 *
		 * @return {Promise} Transition success
		 */
		update: function( context ) {
			return fsm.do(fsm.tr.SAVE_CONSUMER, context.state.active);
		},

		/**
		 * Transition when cancelling the current action
		 *
		 * @return {Promise} Transition success
		 */
		cancel: function() {
			return fsm.do([
				fsm.tr.CANCEL_EDIT,   // Cancel consumer edit 
				fsm.tr.CLOSE_ITEM,    // Close the active item
				fsm.tr.CANCEL_RECEIPT // TODO: sure to clear the whole receipt when cancelling the consumer?
			]);
		},

		/**
		 * Transition when closing the active item
		 *
		 * @return {Promise} Transition success
		 */
		close: function() {
			return fsm.do(fsm.tr.CLOSE_ITEM);
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
