/**
 * Feedback Service Functions
 * 
 * @package Incassoos
 * @subpackage App/Services
 */
define([
	"q",
	"util",
	"./debug-service",
	"./delay-service"
], function( Q, util, debugService, delayService ) {
	/**
	 * Holds the global feedback API
	 *
	 * Provides listeners. Events triggered in this domain are:
	 *  - add (item)
	 *  - clear
	 *  - remove (item)
	 *
	 * @type {Object}
	 */
	var feedback = util.createFeedback({
		name: "global",

		/**
		 * Define additional default item attributes when adding the item
		 *
		 * @return {Object} defaults Feedback item defaults
		 */
		defaultAttributes: function() {
			return {
				// Make global items auto-remove, but not when in debug mode
				autoRemove: ! debugService.isDebugmode()
			};
		}
	}),

	/**
	 * Initialization of the feedback service
	 *
	 * @param {Object} Vue The Vue instance
	 * @return {Promise} Is the service initialized?
	 */
	init = function( Vue ) {
		/**
		 * Reactive listener for the feedback list
		 *
		 * @return {Array} The feedback list
		 */
		Object.defineProperty(Vue.prototype, "$feedback", {
			get: function() {
				return this.$store.state.feedbackGlobalList;
			}
		});

		return Q.resolve();
	},

	/**
	 * Add an item to the feedback list
	 *
	 * Enhancement of `feedback.add()` with an implementation for
	 * auto-removing of added items.
	 *
	 * @param {String} id Optional. Item identifier
	 * @param {Object|String} item Item options or message
	 * @return {Number} Item's list index
	 */
	add = function( id, item ) {
		var index = feedback.add(id, item);

		// Get the added item
		item = feedback.getList()[index];

		// Auto-remove the item after a delay
		if (item.autoRemove) {
			delayService(5000).then( function() {
				feedback.remove(item.$id);
			});
		}

		return index;
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

				// Use list copy for initial state
				state.feedbackGlobalList = feedback.getList().slice();
			},

			/**
			 * Modify service related methods in the main store's mutations
			 *
			 * @param  {Object} mutations Store mutations
			 * @return {Void}
			 */
			defineStoreMutations: function( mutations ) {
				/**
				 * Update reactive property for feedbackService's `feedback` property
				 *
				 * @return {Void}
				 */
				mutations.feedbackUpdateGlobalList = function( state ) {

					// Use list copy for state update
					state.feedbackGlobalList = feedback.getList().slice();
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
				 * When changing the global feedback list, update the main store's `feedback` data
				 *
				 * @return {Function} Deregistration method
				 */
				feedback.on(["add", "clear", "remove"], function() {

					// Mutate the reactive `feedback` data
					context.commit("feedbackUpdateGlobalList");
				});
			}
		};
	};

	return {
		add: add,
		clear: feedback.clear,
		count: feedback.count,
		exists: feedback.exists,
		errorCount: feedback.errorCount,
		getList: feedback.getList,
		hasErrors: feedback.hasErrors,
		init: init,
		on: feedback.on,
		off: feedback.off,
		remove: feedback.remove,
		storeDefinition: storeDefinition
	};
});
