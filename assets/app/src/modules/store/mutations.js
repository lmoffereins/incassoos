/**
 * Store Root Mutations
 *
 * NB: mutated state properties are defined in {@see ./state.js}.
 *
 * @package Incassoos
 * @subpackage App/Store
 */
define([
	"services"
], function( services ) {
	/**
	 * Holds the initial root store mutations
	 *
	 * @type {Object}
	 */
	var mutations = {
		/**
		 * Update reactive property for whether the application is bootstrapped
		 *
		 * @return {Void}
		 */
		setBootstrapped: function( state ) {
			state.isBootstrapped = true;
		},

		/**
		 * Update reactive property for whether the application data is loaded
		 *
		 * @return {Void}
		 */
		setLoaded: function( state ) {
			state.isLoaded = true;
		},

		/**
		 * Update reactive property for whether the application is ready
		 *
		 * @return {Void}
		 */
		setReady: function( state ) {
			state.isReady = true;
		},

		/**
		 * Update reactive property for the global fsm state
		 *
		 * @param {String} payload State name
		 * @return {Void}
		 */
		setState: function( state, payload ) {
			state.fsmState = payload;
		},

		/**
		 * Update whether we are in the SETTINGS zone
		 *
		 * @param  {Boolean} payload Optional. In the settings zone. Defaults to invertion of current value.
		 * @return {Void}
		 */
		toggleSettings: function( state, payload ) {
			state.isSettings = (undefined === payload) ? (! state.isSettings) : (!! payload);
		}
	};

	// Define mutations for services
	services.defineStoreMutations(mutations);

	return mutations;
});
