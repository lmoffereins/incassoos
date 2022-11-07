/**
 * Debug Service Functions
 * 
 * @package Incassoos
 * @subpackage App/Services
 */
define([
	"vue",
	"q",
	"util",
	"./shortcuts-service"
], function( Vue, Q, util, shortcutsService ) {
	/**
	 * Define listener construct for the service
	 *
	 * Events triggered in this domain are:
	 *  - set (debugmode)
	 *
	 * @type {Object}
	 */
	var listeners = util.createListeners("service/debug"),

	/**
	 * Holds the available debug modes
	 *
	 * NB: Potential for more detailed debug modes in the future.
	 *
	 * @type {Object}
	 */
	availableModes = {
		0: "Common.Off",
		1: "Common.On"
	},

	/**
	 * Holds the id for the active debug mode
	 *
	 * @type {Number}
	 */
	debugmode = 0,

	/**
	 * Initialization of the debug service
	 *
	 * @return {Promise} Is the service initialized?
	 */
	init = function() {

		// Register global keyboard event listeners
		shortcutsService.on({

			// Toggle the debug mode
			"shift+alt+D": {
				label: "Administration.ToggleDebugModeLabel",
				callback: function debugServiceToggleDebugModeOnShiftAltD() {
					set();
				}
			},

			// Emit custom event: "online"
			"shift+alt+1": function debugServiceEmitOnlineEventOnShiftAlt1() {
				debugmode && util.emitEvent(window, "online");
			},

			// Emit custom event: "offline"
			"shift+alt+2": function debugServiceEmitOfflineEventOnShiftAlt2() {
				debugmode && util.emitEvent(window, "offline");
			}
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
				state.debugmode = debugmode;
			},

			/**
			 * Modify service related methods in the main store's mutations
			 *
			 * @param  {Object} mutations Store mutations
			 * @return {Void}
			 */
			defineStoreMutations: function( mutations ) {
				/**
				 * Update reactive property for debugService's `mode` property
				 *
				 * @return {Void}
				 */
				mutations.debugSetState = function( state ) {
					state.debugmode = debugmode;
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
				 * When changing the debug mode, update the main store's `mode` data
				 *
				 * @return {Function} Deregistration method
				 */
				listeners.on("set", function() {

					// Mutate the reactive `debug` data
					context.commit("debugSetState");
				});
			}
		};
	},

	/**
	 * Set the active debug mode
	 *
	 * @param  {Number} debug Optional. Debug mode identifier
	 * @return {Void}
	 */
	set = function( debug ) {

		// Set the debug mode
		if ("undefined" !== typeof debug && availableModes.hasOwnProperty(debug)) {
			debugmode = parseInt(debug);

		// Toggle basic mode
		} else {
			debugmode = debugmode ? 0 : 1;
		}

		/**
		 * Trigger event listeners for when the application changed debug
		 */
		listeners.trigger("set", debugmode);
	},

	/**
	 * Return the available modes
	 *
	 * @return {Object} Available modes
	 */
	getAvailableModes = function() {
		return availableModes;
	};

	/**
	 * Reactive listener for the debug mode
	 *
	 * @return {Number} Debug mode identifier
	 */
	Object.defineProperty(Vue.prototype, "$debugmode", {
		get: function() {
			return this.$store.state.debugmode;
		}
	});

	return {
		init: init,
		getAvailableModes: getAvailableModes,

		/**
		 * Return whether debug mode is active
		 *
		 * @return {Number} Debug mode identifier
		 */
		isDebugmode: function() {
			return !! debugmode;
		},
		on: listeners.on,
		off: listeners.off,
		set: set,
		storeDefinition: storeDefinition
	};
});
