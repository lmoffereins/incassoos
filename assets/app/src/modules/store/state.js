/**
 * Store Root State
 *
 * NB: state mutation methods are defined in {@see ./mutations.js}.
 *
 * @package Incassoos
 * @subpackage App/Store
 */
define([
	"vue",
	"fsm",
	"services"
], function( Vue, fsm, services ) {
	/**
	 * Holds the initial root store state
	 *
	 * @type {Object}
	 */
	var state = {

		// Application status
		isBootstrapped: false,
		isLoaded: false,

		// Global state machine
		// fsm: fsm, // Used by `v-fsm` directive
		fsmState: fsm.state,
		isSettings: false,
	};

	/**
	 * Reactive listener for the main store's `isBootstrapped` state data
	 *
	 * @return {Boolean} Is the application bootstrapped?
	 */
	Object.defineProperty(Vue.prototype, "$isBootstrapped", {
		get: function() {
			return this.$store.state.isBootstrapped;
		}
	});

	/**
	 * Reactive listener for the main store's `isLoaded` state data
	 *
	 * @return {Boolean} Is the application data loaded?
	 */
	Object.defineProperty(Vue.prototype, "$isLoaded", {
		get: function() {
			return this.$store.state.isLoaded;
		}
	});

	/**
	 * Reactive version of `fsm.is()` to check the state
	 *
	 * @param  {String|Array} state States to check against
	 * @return {Boolean} Is this (one of) the checked state(s)?
	 */
	Object.defineProperty(Vue.prototype, "$fsmIs", {
		value: function( state ) {
			return Array.isArray(state) ? (-1 !== state.indexOf(this.$fsmState)) : (this.$fsmState === state);
		}
	});

	/**
	 * Reactive version of `fsm.seek()` to check the available transition
	 *
	 * @param  {String|Array} transition Transitions to check against
	 * @return {Boolean} Is/are the the checked transition(s) available?
	 */
	Object.defineProperty(Vue.prototype, "$fsmSeek", {
		value: function( transition ) {

			// Assist fsm.seek() here with a reference to $fsmState to make it effectively reactive
			return this.$fsmState && fsm.seek(transition);
		}
	});

	/**
	 * Reactive listener for the main store's fsm state data
	 *
	 * @return {String} FSM state name
	 */
	Object.defineProperty(Vue.prototype, "$fsmState", {
		get: function() {
			return this.$store.state.fsmState;
		}
	});

	/**
	 * Reactive listener for the main store's isSettings state data
	 * 
	 * @return {Boolean} Are we in the SETTINGS zone?
	 */
	Object.defineProperty(Vue.prototype, "$isSettings", {
		get: function() {
			return this.$store.state.isSettings;
		}
	});

	// Define state for services
	services.defineStoreState(state);

	return state;
});
