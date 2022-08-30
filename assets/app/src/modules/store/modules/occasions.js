/**
 * Occasions Store Module
 *
 * @package Incassoos
 * @subpackage App/Store
 */
define([
	"q",
	"dayjs",
	"api",
	"fsm",
	"services",
	"util",
	"./util/list"
], function( Q, dayjs, api, fsm, services, util, list ) {
	/**
	 * The module state data
	 *
	 * @type {Object}
	 */
	var state = {
		all: [],
		searchQuery: "",
		active: null,
		__feedback: []
	},

	/**
	 * Holds a reference to the global delay service
	 *
	 * @type {Object}
	 */
	delayService = services.get("delay"),

	/**
	 * Holds a reference to the global feedback service
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
		name: "occasions",
		persistent: true
	}),

	/**
	 * Define sanitization function for occasions
	 *
	 * @type {Function} Product sanitization
	 */
	sanitize = util.sanitization({
		/**
		 * Sanitization of the `title` item property
		 *
		 * @param  {String} input Input value
		 * @return {String} Sanitized value
		 */
		title: function( input ) {
			return input.trim();
		},

		/**
		 * Sanitization of the `occasionType` item property
		 *
		 * @param  {Number|String} input Input value
		 * @return {Float} Sanitized value
		 */
		occasionType: function( input ) {
			return isNaN(input) ? 0 : parseInt(input);
		}
	}),

	/**
	 * Holds validation functions for editable properties
	 *
	 * @type {Object} Item property validators
	 */
	validators = {
		/**
		 * Validation of the `title` item property
		 *
		 * @param  {String} input Sanitized input value
		 * @return {Boolean|String} Validation success or error code
		 */
		title: function( input ) {
			var validated = true;

			// Value should contain characters
			if (0 === input.length) {
				validated = "Occasion.Error.TitleIsEmpty";
			}

			return validated;
		},

		/**
		 * Validation of the `occasionDate` item property
		 *
		 * @param  {String} input Sanitized input value
		 * @return {Boolean|String} Validation success or error code
		 */
		occasionDate: function( input ) {
			var validated = true;

			// Value should be a valid date object
			if (! dayjs(input).isValid()) {
				validated = "Occasions.Error.InvalidOccasionDate";
			}

			return validated;
		},

		/**
		 * Validation of the `occasionType` item property
		 *
		 * @param  {String} input Sanitized input value
		 * @return {Boolean|String} Validation success or error code
		 */
		occasionType: function( input ) {
			var validated = true;

			// Value should be a number larger than 0
			if (0 >= input) {
				validated = "Occasion.Error.NoOccasionType";

			// Value should be an available term id
			} else if (! settings.occasion.occasionType.items.hasOwnProperty(input)) {
				validated = "Occasion.Error.UnavailableOccasionType";
			}

			return validated;
		}
	},

	/**
	 * Define validation function for occasions
	 *
	 * @type {Function} Occasion validation
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
		 * Return the title of the active item
		 *
		 * @return {String} Item title
		 */
		getTitle: function( state ) {
			return state.active && state.active.title;
		},

		/**
		 * Return whether the occasion can be edited
		 *
		 * TODO: require `! state.active.closed`?
		 *
		 * @return {Boolean} Can occasion be edited?
		 */
		isEditable: function( state ) {
			return state.active;
		},

		/**
		 * Return whether the occasion can be updated
		 *
		 * Submittable for occasions is only defined for editing an occasion, not for
		 * creating an occasion, because the active occasion remains the active item
		 * during the creation process.
		 *
		 * @return {Boolean} Can occasion be updated?
		 */
		isSubmittable: list.isSubmittable([
			fsm.st.EDIT_OCCASION
		], _.keys(validators)),

		/**
		 * Return whether the occasion can be deleted
		 *
		 * TODO: check `state.active.status` as well - not present in item yet?
		 *
		 * @return {Boolean} Can occasion be deleted?
		 */
		isDeletable: function( state, getters, rootState ) {
			return state.active
				&& ! state.active.closed
				&& ! rootState.orders.all.length;
		},

		/**
		 * Return whether the occasion can be closed
		 *
		 * @return {Boolean} Can occasion be closed?
		 */
		isClosable: function( state, getters, rootState ) {
			return state.active
				&& ! state.active.closed
				&& !! rootState.orders.all.length;
		},

		/**
		 * Return whether the occasion can be reopened
		 *
		 * @return {Boolean} Can occasion be reopened?
		 */
		isReopenable: function( state ) {
			return state.active && !! state.active.closed;
		}
	}, {
		feedback: feedback
	}),

	/**
	 * The module mutation methods
	 *
	 * @type {Object}
	 */
	mutations = list.mutations({
		/**
		 * Modify the active product
		 *
		 * @param  {Object} payload Product data
		 * @return {Void}
		 */
		patchActive: list.patchActive(sanitize, validate, feedback)
	}, {
		feedback: feedback
	}),

	/**
	 * Create an occasion
	 *
	 * @param  {Object} context Module context
	 * @param  {Object} payload Occasion creation data
	 * @return {Promise} Create and load success
	 */
	create = function( context, payload ) {

		// Create occasion, add occasion to list, load item
		return api.occasions.create(payload).then( function( resp ) {

			// Report success message
			feedbackService.add({
				message: "Occasion.CreatedOccasion",
				data: {
					args: [resp.title]
				}
			});

			// Add item to the list
			context.commit("addItemToList", resp);

			// Load item instantly, provide empty list of items to skip an API call
			return load(context, { id: resp.id, items: [] });
		});
	},

	/**
	 * Load a single occasion's orders
	 *
	 * @param  {Object} context Module context
	 * @param  {Object} payload Occasion data
	 * @return {Promise} Load success
	 */
	load = function( context, payload ) {

		// Accept payload as just the id
		payload.id || (payload = { id: payload });

		// Load the occasion's orders
		return Q.all([
			context.dispatch("orders/load", payload, { root: true }),

			// At least run for a few seconds
			delayService(2500)
		]).then( function() {

			// Register active item
			context.commit("setActive", payload);

			// Report success message
			feedbackService.add({
				message: "Occasion.LoadedOccasion",
				data: {
					args: [context.state.active.title]
				}
			});

		}).catch( function( e ) {
			console.warn("occasions/load error", e);

			return Q.reject(e);
		});
	},

	/**
	 * The module action methods
	 *
	 * @type {Object}
	 */
	actions = list.actions({
		/**
		 * Setup occasion state listeners and setup initial collection
		 *
		 * @return {Void}
		 */
		init: function( context ) {
			/*
			 * Modify the destination for the `CLOSE_LOGIN` transition
			 *
			 * @return {String} Transition destination
			 */
			fsm.filter(
				fsm.tr.CLOSE_LOGIN.concat("/to"),
				function( to, step ) {

					// Route to IDLE when an occasion is already selected
					if (context.state.active) {
						to = fsm.st.IDLE;
					}

					return to;
				}
			);

			/**
			 * When getting an occasion, load or create it
			 *
			 * @param {Object} payload Occasion data (with id) or creation data
			 * @return {Promise} Load/create success
			 */
			fsm.observe(
				fsm.on.before.GET_OCCASION,
				function( lifecycle, payload ) {
					var withId = !! payload.id;

					return (withId ? load : create)(context, withId ? { id: payload.id } : payload);
				}
			);

			/**
			 * When editing an occasion, check for the selected item
			 *
			 * @return {Promise} Transition success
			 */
			fsm.observe(
				fsm.on.before.EDIT_ITEM,
				function( lifecycle ) {

					// Reject transition when no occasion is selected
					if (lifecycle.from === fsm.st.VIEW_OCCASION && ! context.state.active) {
						return Q.reject("Occasion.Error.NoOccasion");
					}
				}
			);

			/**
			 * When saving an occasion, update it
			 *
			 * @param {Object} payload Occasion details
			 * @return {Promise} Update success
			 */
			fsm.observe(
				fsm.on.before.SAVE_OCCASION,
				function( lifecycle, payload ) {

					// Reject transition when no occasion is selected
					if (! context.state.active) {
						return Q.reject("Occasion.Error.NoOccasion");
					}

					// Reject transition when the user cannot update occasions
					// if (! authService.userCan("delete_products")) {
					// 	return Q.reject("Generic.Error.NotAllowed");
					// }

					// Update the active occasion
					payload.id = state.active.id;

					return api.occasions.update(payload).then( function( resp ) {

						// Report success message
						feedbackService.add({
							message: "Occasion.UpdatedOccasion",
							data: {
								args: [resp.title]
							}
						});

						// Modify active item in list as updated
						context.dispatch("setActiveItemInList", resp);
					});
				}
			);

			/**
			 * When going to delete an occasion
			 *
			 * @return {Promise} Transition success
			 */
			fsm.observe(
				fsm.on.before.DELETE_ITEM,
				function( lifecycle ) {

					// Reject transition when the user cannot delete occasions
					// if (fsm.st.DELETE_OCCASION === lifecycle.to && ! authService.userCan("delete_occasions")) {
					// 	return Q.reject("Generic.Error.NotAllowed");
					// }
				}
			);

			/**
			 * When deleting an occasion, delete it
			 *
			 * @param {Object} payload Occasion id
			 * @return {Promise} Delete success
			 */
			fsm.observe(
				fsm.on.before.DELETE_OCCASION,
				function( lifecycle, payload ) {
					return api.occasions.trash(payload).then( function( resp ) {

						// Report success message
						feedbackService.add({
							message: "Occasion.DeletedOccasion",
							data: {
								args: [payload.title]
							}
						});

						// Remove item from list
						context.dispatch("removeActiveItemFromList", resp);

					}).catch( function( error ) {
						/**
						 * Move away from delete context after the error
						 *
						 * @return {Void}
						 */
						error.onAfterError = function() {
							context.dispatch("cancel");
						}

						return Q.reject(error);
					});
				}
			);

			/**
			 * When closing an occasion, close it
			 *
			 * @param {Object} payload Occasion id
			 * @return {Promise} Close success
			 */
			fsm.observe(
				fsm.on.before.CLOSE_OCCASION,
				function( lifecycle, payload ) {

					// Reject transition when no occasion is selected
					if (! payload) {
						return Q.reject("Occasion.Error.NoOccasion");
					}

					// Reject transition when the user cannot close occasions
					// if (! authService.userCan("close_occasions")) {
					// 	return Q.reject("Generic.Error.NotAllowed");
					// }

					return api.occasions.close(payload).then( function( resp ) {

						// Report success message
						feedbackService.add({
							message: "Occasion.ClosedOccasion",
							data: {
								args: [resp.title]
							}
						});

						// Modify active item in list as closed
						context.dispatch("setActiveItemInList", resp);
					});
				}
			);

			/**
			 * When reopening an occasion, reopen it
			 *
			 * @param {Object} payload Occasion id
			 * @return {Promise} Reopen success
			 */
			fsm.observe(
				fsm.on.before.REOPEN_OCCASION,
				function( lifecycle, payload ) {

					// Reject transition when no occasion is selected
					if (! payload) {
						return Q.reject("Occasion.Error.NoOccasion");
					}

					// Reject transition when the user cannot reopen occasions
					// if (! authService.userCan("delete_products")) {
					// 	return Q.reject("Generic.Error.NotAllowed");
					// }

					return api.occasions.reopen(payload).then( function( resp ) {

						// Report success message
						feedbackService.add({
							message: "Occasion.ReopenedOccasion",
							data: {
								args: [resp.title]
							}
						});

						// Modify active item in list as reopened
						context.dispatch("setActiveItemInList", resp);
					});
				}
			);

			/**
			 * When cancelling the product edit, undo edits by restoring the
			 * active product
			 *
			 * @return {Void}
			 */
			fsm.observe(
				fsm.on.before.CANCEL_EDIT,
				function( lifecycle ) {
					if (fsm.st.EDIT_OCCASION === lifecycle.from) {

						// Reset active product, removing applied edits
						context.commit("setActive", { id: context.state.active.id });

						// Clear the list feedback
						context.commit("clearFeedback");
					}
				}
			);

			/**
			 * When entering the OCCASIONS state, clear feedback data
			 *
			 * @return {Void}
			 */
			fsm.observe(
				fsm.on.enter.OCCASIONS,
				function() {

					// Clear the list feedback
					context.commit("clearFeedback");
				}
			);
		},

		/**
		 * Load the list of occasions
		 *
		 * @return {Promise} Was the data loaded?
		 */
		load: function( context ) {

			// Request occasions, list the items
			return api.occasions.get().then( function( resp ) {

				// Bail when payload is not an array
				if (! Array.isArray(resp)) {
					return resp;
				}

				// Register new set of list items
				context.commit("setListItems", { items: resp });
			});
		},

		/**
		 * Transition when starting the occasion selection
		 * 
		 * Either explicitly navigate to START_OCCASION or do so when there's no active item.
		 *
		 * @param {Object} payload Start parameters
		 * @return {Boolean} Transition success
		 */
		start: function( context, payload ) {
			return payload && payload.start || ! context.state.active
				? fsm.do(fsm.tr.START_OCCASION)
				: fsm.do(fsm.tr.SELECT_OCCASION);
		},

		/**
		 * Transition when selecting an occasion
		 *
		 * Getting the occasion (load or create) determines success of the transition.
		 *
		 * @param {Object} payload Occasion details
		 * @return {Promise} Transition success
		 */
		get: function( context, payload ) {
			return fsm.do(fsm.tr.GET_OCCASION, payload);
		},

		/**
		 * Transition when editing the selected occasion
		 *
		 * @return {Promise} Transition success
		 */
		edit: function() {
			return fsm.do(fsm.tr.EDIT_ITEM);
		},

		/**
		 * Transition when updating the selected occasion
		 *
		 * @param {Object} payload Occasion details
		 * @return {Promise} Transition success
		 */
		update: function( context, payload ) {
			return fsm.do(fsm.tr.SAVE_OCCASION, payload);
		},

		/**
		 * Transition when maybe deleting the selected occasion
		 *
		 * @return {Promise} Transition success
		 */
		maybeDelete: function( context ) {
			return fsm.do(fsm.tr.DELETE_ITEM, context.state.active);
		},

		/**
		 * Transition when deleting the selected occasion
		 *
		 * @return {Promise} Transition success
		 */
		delete: function( context ) {
			return fsm.do(fsm.tr.DELETE_OCCASION, context.state.active);
		},

		/**
		 * Transition when closing the selected occasion
		 *
		 * @return {Promise} Transition success
		 */
		close: function( context ) {
			return fsm.do(fsm.tr.CLOSE_OCCASION, context.state.active);
		},

		/**
		 * Transition when reopening the selected occasion
		 *
		 * @return {Promise} Transition success
		 */
		reopen: function( context ) {
			return fsm.do(fsm.tr.REOPEN_OCCASION, context.state.active);
		},

		/**
		 * Transition when cancelling the occasion context
		 *
		 * @param {Object} payload Cancel parameters
		 * @return {Boolean} Transition success
		 */
		cancel: function( context, payload ) {
			return payload && payload.close
				? fsm.do([
					fsm.tr.CLOSE_ITEM,
					fsm.tr.CANCEL_OCCASION
				])
				: fsm.do([
					fsm.tr.CANCEL_DELETE,
					fsm.tr.CANCEL_EDIT,
					fsm.tr.START_OCCASION
				]);
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
