/**
 * Visibility Service Functions
 *
 * Relies on the Document object.
 * 
 * @package Incassoos
 * @subpackage App/Services
 */
define([
	"vue",
	"q",
	"util"
], function( Vue, Q, util ) {
	/**
	 * Define listener construct for the service
	 *
	 * Events triggered in this domain are:
	 *  - visible
	 *  - hidden
	 *
	 * @type {Object}
	 */
	var listeners = util.createListeners("service/visibility"),

	/**
	 * Holds the visibility status
	 *
	 * @type {Boolean}
	 */
	visible = document ? "visible" === document.visibilityState : true,

	/**
	 * Initialization of the visibility service
	 *
	 * @return {Promise} Is the service initialized?
	 */
	init = function() {

		// Require the document object
		if (document && document.addEventListener && document.visibilityState) {

			// Setup listener for the `visibilitychange` event
			document.addEventListener("visibilitychange", function() {
				visible = "visible" === document.visibilityState;

				/**
				 * Trigger event listeners for when the application changed visibility
				 */
				listeners.trigger(visible ? "visible" : "hidden");
			});
		}

		return Q.resolve();
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
				state.documentVisible = visible;
			},

			/**
			 * Modify service related methods in the main store's mutations
			 *
			 * @param  {Object} mutations Store mutations
			 * @return {Void}
			 */
			defineStoreMutations: function( mutations ) {
				/**
				 * Update reactive property for visibilityService's `status` property
				 *
				 * @return {Void}
				 */
				mutations.visibilitySetState = function( state ) {
					state.documentVisible = visible;
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
				 * When changing the visible status, update the main store's `status` data
				 *
				 * @return {Function} Deregistration method
				 */
				listeners.on(["visible", "hidden"], function() {

					// Mutate the reactive `visibility` data
					context.commit("visibilitySetState");
				});
			}
		};
	};

	/**
	 * Reactive listener for the visibility status
	 *
	 * @return {Boolean} Is the application visible?
	 */
	Object.defineProperty(Vue.prototype, "$visible", {
		get: function() {
			return this.$store.state.documentVisible;
		}
	});

	return {
		init: init,

		/**
		 * Return whether the application is visible
		 *
		 * @return {Boolean} Is the application visible?
		 */
		isVisible: function() {
			return visible;
		},

		/**
		 * Return whether the application is hidden
		 *
		 * @return {Boolean} Is the application hidden?
		 */
		isHidden: function() {
			return ! visible;
		},
		on: listeners.on,
		off: listeners.off,
		storeDefinition: storeDefinition
	};
});
