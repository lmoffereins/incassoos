/**
 * List helpers for a Store module
 *
 * @package Incassoos
 * @subpackage App/Store/Util
 */
define([
	"fsm",
	"util"
], function( fsm, util ) {
	/**
	 * Set of default list getter methods
	 *
	 * @param {Object} getters Getter extensions
	 * @param {Object} options Optional. Additional settings
	 * @return {Object} Getter methods
	 */
	var getters = function( getters, options ) {
		options = options || {};
		options.validators = options.validators || {};
		options.comparators = options.comparators || {};

		return util.clone({
			/**
			 * Return all items in the list
			 *
			 * May be overwritten in a new object to enhance the returned
			 * items. By default returns just the plain items.
			 *
			 * @return {Array} List items
			 */
			getItems: function ( state ) {
				return state.all;
			},

			/**
			 * Return a single item in the list by matching id
			 *
			 * @param {Number} id The item id to find in the list
			 * @return {Object} List item
			 */
			getItemById: function( state, getters ) {
				return function( id ) {

					// Find item in list
					return getters.getItems.find( function( i ) {
						return i.id === id;
					});
				};
			},

			/**
			 * Return whether this is the active item
			 *
			 * @param {Number} id The item id to find in the list
			 * @return {Boolean} Is this the active item?
			 */
			isActiveItem: function( state, getters ) {
				return function( id ) {
					return state.active && state.active.id === id;
				}
			},

			/**
			 * Return the contents of a new item
			 *
			 * To be redefined per module.
			 *
			 * @return {Object} New item
			 */
			getNewItem: function( state ) {
				return {};
			},

			/**
			 * Return the patches on the active item
			 *
			 * @type {Object} Active patches
			 */
			getActivePatches: function( state, getters ) {
				var patches = {}, item, i;

				// Bail early when there is no active item
				if (! state.active) {
					return patches;
				}

				// Get the original active item or a sample of a new item
				item = getters["getItemById"](state.active.id) || getters["getNewItem"];

				for (i in options.validators) {

					// Custom patch comparison
					if (options.comparators.hasOwnProperty(i)) {
						if (options.comparators[i](state.active[i], item)) {
							patches[i] = state.active[i];
						}

					// Default comparison
					} else if (state.active[i] !== item[i]) {
						patches[i] = state.active[i];
					}
				}

				return patches;
			},

			/**
			 * Return the list's active search query
			 *
			 * @return {String} Search query
			 */
			getSearchQuery: function( state ) {
				return state.searchQuery || "";
			},

			/**
			 * Return the feedback list
			 *
			 * @return {Array} Feedback list
			 */
			getFeedback: function( state ) {
				return state.__feedback || [];
			},

			/**
			 * Return if the feedback list has any errors
			 *
			 * Use `state.__feedback` to trigger state watchers.
			 *
			 * @return {Boolean} Feedback list has errors
			 */
			hasFeedbackErrors: function( state ) {
				return state.__feedback && options.feedback.hasErrors();
			}
		}, getters || {});
	},

	/**
	 * Set of default list mutation methods
	 *
	 * @param {Object} mutations Mutation extensions
	 * @param {Object} options Optional. Additional settings
	 * @return {Object} Mutation methods
	 */
	mutations = function( mutations, options ) {
		options = options || {};

		return util.clone({
			/**
			 * Set the list of items
			 *
			 * @param {Object} payload Containing the new list in `items`.
			 * @return {Void}
			 */
			setListItems: function( state, payload ) {
				state.all = payload.items;
			},

			/**
			 * Add single item to the list
			 *
			 * @param {Object} payload The item to add to the list
			 * @return {Void}
			 */
			addItemToList: function( state, payload ) {
				if (payload.id) {
					state.all.push(payload);
				}
			},

			/**
			 * Update single item in the list
			 *
			 * @param {Object} payload The item to update in the list
			 * @return {Void}
			 */
			setItemInList: function( state, payload ) {

				// Accept item property as the id
				payload.item && (payload.id = payload.item);

				// Find item in list
				var item = state.all.find( function( i ) {
					return i.id === payload.id;
				});

				// Update details
				item && Object.assign(item, payload);
			},

			/**
			 * Remove single item from the list
			 *
			 * @param {Object} payload The item id to remove from the list
			 * @return {Void}
			 */
			removeItemFromList: function( state, payload ) {

				// Accept payload as just the id
				payload.id || (payload = {id: payload});

				// Return list without found item
				state.all = state.all.filter( function( i ) {
					return i.id !== payload.id;
				});
			},

			/**
			 * Unset the list of items
			 *
			 * @return {Void}
			 */
			clearList: function( state ) {
				state.all = [];
			},

			/**
			 * Set or clear the active list search query
			 *
			 * @param {String} payload Optional. Search query. Leave empty to clear.
			 * @return {Void}
			 */
			setSearchQuery: function( state, payload ) {
				state.searchQuery = (payload || "").toString().trim();
			},

			/**
			 * Set the active selected item
			 *
			 * @param {Object} payload The item id to select
			 * @return {Void}
			 */
			setActive: function( state, payload ) {

				// Accept payload as just the id
				payload.id || (payload = {id: payload});

				// Find item in list
				// TODO: `util.copy` does string conversion, losing objects like `Date`
				state.active = util.copy(state.all.find( function( i ) {
					return i.id === payload.id;
				}) || payload || null);
			},

			/**
			 * Set a new item as the active item
			 *
			 * @return {Void}
			 */
			setNewActive: function( state, payload ) {
				state.active = payload;
			},

			/**
			 * Unset the active selected item
			 *
			 * @return {Void}
			 */
			clearActive: function( state ) {
				state.active = null;
			},

			/**
			 * Add a feedback item
			 *
			 * @param {Object} payload Feedback data
			 * @return {Number} Item index
			 */
			addFeedback: function( state, payload ) {

				// Add item to feedback list
				var index = options.feedback.add(payload);

				// Use list copy for state update
				state.__feedback = options.feedback.getList().slice();

				return index;
			},

			/**
			 * Remove a feedback item
			 *
			 * @param {Object} payload Feedback id or index
			 * @return {Void}
			 */
			removeFeedback: function( state, payload ) {

				// Remove item from feedback list
				options.feedback.remove(payload);

				// Use list copy for state update
				state.__feedback = options.feedback.getList().slice();
			},

			/**
			 * Reset the list feedback
			 *
			 * @return {Void}
			 */
			clearFeedback: function( state ) {

				// Clear the feedback list
				options.feedback.clear();

				// Reset state
				state.__feedback = [];
			}
		}, mutations || {});
	},

	/**
	 * Set of default list action methods
	 *
	 * @param {Object} actions Mutation extensions
	 * @param {Object} options Optional. Additional settings
	 * @return {Object} Mutation methods
	 */
	actions = function( actions, options ) {
		options = options || {};

		return util.clone({
			/**
			 * Default initializer
			 *
			 * @return {Void}
			 */
			init: function() {},

			/**
			 * Default loader
			 *
			 * @return {Void}
			 */
			load: function() {},

			/**
			 * Apply a search query
			 *
			 * @param  {String} payload Search query
			 * @return {Void}
			 */
			search: function( context, payload ) {
				context.commit("setSearchQuery", payload);
			},

			/**
			 * Register feedback listeners
			 *
			 * @param  {Object} payload Set of listeners
			 * @return {Void}
			 */
			onFeedback: function( context, payload ) {
				options.feedback && options.feedback.on(payload);
			},

			/**
			 * Update the active item and its equivalent in the list
			 *
			 * @param {Object} payload The item to update in the list
			 * @return {Void}
			 */
			setActiveItemInList: function( context, payload ) {
				context.commit("setItemInList", payload);
				context.commit("setActive", payload);
			},

			/**
			 * Remove the active item and its equivalent from the list
			 *
			 * @param {Object} payload The item to remove from the list
			 * @return {Void}
			 */
			removeActiveItemFromList: function( context, payload ) {
				context.commit("removeItemFromList", payload);
				context.commit("clearActive");
			}
		}, actions || {});
	},

	/**
	 * Return getter function for checking if the item is submittable
	 *
	 * Checks for input validation errors and whether the item has any patches.
	 *
	 * TODO: apply auth check here?
	 *
	 * @param  {Array|String} submittableStates List of or single submittable FSM state(s)
	 * @return {Function} Is the list submittable?
	 */
	isSubmittable = function( submittableStates ) {
		return function( state, getters ) {
			var hasNoErrors = ! getters["hasFeedbackErrors"],
			    patches = getters["getActivePatches"];

			// Checks pass and FSM states match
			return hasNoErrors && _.keys(patches).length && fsm.is(submittableStates);
		};
	},

	/**
	 * Return mutation function for patching the active list item
	 *
	 * Use this function to apply patches to the active item
	 *
	 * @param  {Function|Object} sanitize Sanitization function or set of sanitizer functions
	 * @param  {Function|Object} validate Validation function or set of validator functions
	 * @param  {Object} feedback Feedback handler
	 * @return {Function} Mutate the active list item
	 */
	patchActive = function( sanitize, validate, feedback ) {

		// Default to a standard sanitization function
		if ("function" !== typeof sanitize) {
			sanitize = util.sanitization(sanitize || {});
		}

		// Default to a standard validation function
		if ("function" !== typeof validate) {
			validate = util.validation(validate || {});
		}

		return function( state, payload ) {
			if (state.active) {

				// Sanitize joint payload to include previous input
				var sanitizedPayload = sanitize(util.clone(state.active, payload));

				// Validate the sanitized data. Assign new value to trigger state watchers
				state.__feedback = validate(sanitizedPayload, feedback).slice();

				// Update active item. Assign new value to trigger state watchers
				state.active = util.clone(state.active, sanitizedPayload);
			}
		};
	};

	return {
		actions: actions,
		getters: getters,
		mutations: mutations,
		isSubmittable: isSubmittable,
		patchActive: patchActive
	};
});
