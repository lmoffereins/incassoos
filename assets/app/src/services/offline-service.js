/**
 * Offline Service Functions
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
	 *  - up
	 *  - down
	 *
	 * @type {Object}
	 */
	var listeners = util.createListeners("service/offline"),

	/**
	 * Holds the online/offline status
	 *
	 * @type {Boolean}
	 */
	online = window.navigator && window.navigator.onLine || false,

	/**
	 * Initialization of the offline service
	 *
	 * @return {Promise} Is the service initialized?
	 */
	init = function() {

		// Setup listener for the `online` event
		window.addEventListener("online", function offlineServiceOnOnline() {
			online = true;

			/**
			 * Trigger event listeners for when the application went online
			 */
			listeners.trigger("up");
		});

		// Setup listener for the `offline` event
		window.addEventListener("offline", function offlineServiceOnOffline() {
			online = false;

			/**
			 * Trigger event listeners for when the application went offline
			 */
			listeners.trigger("down");
		});

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
				state.offlineStatus = ! online;
			},

			/**
			 * Modify service related methods in the main store's mutations
			 *
			 * @param  {Object} mutations Store mutations
			 * @return {Void}
			 */
			defineStoreMutations: function( mutations ) {
				/**
				 * Update reactive property for offlineService's `status` property
				 *
				 * @return {Void}
				 */
				mutations.offlineSetStatus = function( state ) {
					state.offlineStatus = ! online;
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
				 * When changing the online status, update the main store's `status` data
				 *
				 * @return {Function} Deregistration method
				 */
				listeners.on(["up", "down"], function() {

					// Mutate the reactive `offline` data
					context.commit("offlineSetStatus");
				});
			}
		};
	};

	/**
	 * Reactive listener for the offline status
	 *
	 * @return {Boolean} Is the application offline?
	 */
	Object.defineProperty(Vue.prototype, "$offline", {
		get: function() {
			return this.$store.state.offlineStatus;
		}
	});

	return {
		init: init,

		/**
		 * Return whether the application is online
		 *
		 * @return {Boolean} Is the connection up?
		 */
		isUp: function() {
			return online;
		},

		/**
		 * Return whether the application is offline
		 *
		 * @return {Boolean} Is the connection down?
		 */
		isDown: function() {
			return ! online;
		},
		on: listeners.on,
		off: listeners.off,
		storeDefinition: storeDefinition
	};
});
