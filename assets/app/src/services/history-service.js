/**
 * History Service Functions
 * 
 * @package Incassoos
 * @subpackage App/Services
 */
define([
	"vue",
	"util",
	"./storage-service"
], function( Vue, util, storageService ) {
	/**
	 * Define listener construct for the service
	 *
	 * Events triggered in this domain are:
	 *  - change
	 *
	 * @type {Object}
	 */
	var listeners = util.createListeners("service/history"),

	/**
	 * Holds the details for the current history state
	 *
	 * @type {Object}
	 */
	historyState = null,

	/**
	 * Initialization of the history service
	 *
	 * @return {Promise} Is the service initialized?
	 */
	init = function() {

		// Listen for history changes in the `popstate` event
		window.addEventListener("popstate", function historyServiceOnPopstate( event ) {

			// TOOD: Chrome does not trigger 'popstate'?
			console.log("popstate", state);
			historyState = event.state;

			// Store the history state to the global settings
			storageService.save("history", historyState).then( function() {
				/**
				 * Trigger listeners for any change in the history's state
				 */
				listeners.trigger("change", historyState);
			});
		});

		// Maybe get the current history state from storage
		return storageService.get("history").then( function( state ) {
			state && replace(state);
		});
	},

	/**
	 * Definition of the service's store logic
	 *
	 * @return {Object} Service store methods
	 */
	storeDefinition = function() {
		return {
			/**
			 * Modify service related properties in the main store's state
			 *
			 * @param  {Object} state Store state
			 * @return {Void}
			 */
			defineStoreState: function( state ) {
				state.historyState = historyState;
			},

			/**
			 * Modify service related methods in the main store's mutations
			 *
			 * @param  {Object} mutations Store mutations
			 * @return {Void}
			 */
			defineStoreMutations: function( mutations ) {
				/**
				 * Update reactive properties for the history state
				 *
				 * @return {Void}
				 */
				mutations.historySetState = function( state ) {
					state.historyState = historyState;
				};
			},

			/**
			 * Trigger service related methods in the main store's context
			 *
			 * @param  {Object} context Store context
			 * @return {Void}
			 */
			defineStoreContextUsage: function( context ) {
				/**
				 * When changing the history state, update the main store's history data
				 *
				 * @return {Function} Deregistration method
				 */
				listeners.on("state", function() {

					// Mutate the reactive history data
					context.commit("historySetState");
				});
			}
		};
	},

	/**
	 * Register a next state in the history
	 *
	 * @param {Object} state State details
	 * @return {Void}
	 */
	add = function( state ) {
		window.history.pushState(state, "");
	},

	/**
	 * Replace the current state in the history
	 *
	 * @param {Object} state State details
	 * @return {Void}
	 */
	replace = function( state ) {
		window.history.replaceState(state, "");
	},

	/**
	 * Navigate to the previous history state
	 *
	 * @return {Void}
	 */
	back = function() {
		window.history.back();
	},

	/**
	 * Navigate to the next history state
	 *
	 * @return {Void}
	 */
	forward = function() {
		window.history.fowrard();
	},

	/**
	 * Return the details of the current history state
	 *
	 * @return {Object} State details
	 */
	getState = function() {
		return historyState;
	};

	/**
	 * Make the history available at Vue's root
	 *
	 * @return {String} Mode id
	 */
	Object.defineProperty(Vue.prototype, "$history", {
		get: function() {
			return this.$store.state.historyState;
		}
	});

	return {
		init: init,
		add: add,
		replace: replace,
		back: back,
		forward: forward,
		getState: getState,
		off: listeners.off,
		on: listeners.on,
		storeDefinition: storeDefinition
	};
});
