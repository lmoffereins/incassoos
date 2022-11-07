/**
 * Clock Service Functions
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
	 *  - tick
	 *
	 * @type {Object}
	 */
	var listeners = util.createListeners("service/clock"),

	/**
	 * Holds the current time of the clock
	 *
	 * @type {String}
	 */
	clock = "",

	/**
	 * Initialization of the clock service
	 *
	 * @return {Promise} Is the service initialized?
	 */
	init = function() {

		// Start ticking
		tick();

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
				state.clock = clock;
			},

			/**
			 * Modify service related methods in the main store's mutations
			 *
			 * @param  {Object} mutations Store mutations
			 * @return {Void}
			 */
			defineStoreMutations: function( mutations ) {
				/**
				 * Update reactive properties for the clock
				 *
				 * @return {Void}
				 */
				mutations.setClock = function( state ) {
					state.clock = clock;
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
				 * When ticking to the next minute, update the main store's clock data
				 *
				 * @return {Function} Deregistration method
				 */
				listeners.on("tick", function() {

					// Mutate the reactive clock
					context.commit("setClock");
				});
			}
		};
	},

	/**
	 * Execute callback after each passed minute
	 *
	 * @param  {Function} callback Callback to run after each minute
	 * @return {Void}
	 */
	tick = function() {
		clock = getClock();

		/**
		 * Trigger event listeners for the new minute
		 */
		listeners.trigger("tick");

		// Get the remaining time in seconds with a 5ms margin
		var interval = (60 - (new Date()).getSeconds()) * 1000 + 5;

		// Set timer for the next tick
		setTimeout(tick, interval);
	},

	/**
	 * Return the current time in hh:mm
	 *
	 * @return {String} Time
	 */
	getClock = function() {
		var now = new Date();

		return now.getHours().toString().padStart(2, "0").concat(":", now.getMinutes().toString().padStart(2, "0"));
	};

	/**
	 * Make the clock available at Vue's root
	 *
	 * @return {String} Clock
	 */
	Object.defineProperty(Vue.prototype, "$clock", {
		get: function() {
			return this.$store.state.clock;
		}
	});

	return {
		init: init,
		off: listeners.off,
		on: listeners.on,
		storeDefinition: storeDefinition
	};
});
