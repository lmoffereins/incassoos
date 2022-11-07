/**
 * Store Root Actions
 *
 * @package Incassoos
 * @subpackage App/Store
 */
define([
	"fsm",
	"services"
], function( fsm, services ) {
	/**
	 * Holds a reference to the installation service
	 *
	 * @type {Object}
	 */
	var installService = services.get("install"),

	/**
	 * Bootstrap the application
	 *
	 * @return {Promise} Transition success
	 */
	bootstrap = function( context ) {
		/**
		 * When entering any state, update the main store's fsm state data
		 *
		 * @return {Void}
		 */
		fsm.observe(
			"onEnterState",
			function( lifecycle ) {

				// Mutate the reactive state data
				context.commit("setState", lifecycle.to);
			}
		);

		/**
		 * Transition to bootstrap the application environment
		 */
		return fsm.do(fsm.tr.BOOTSTRAP, context).then( function() {

			// Set loading status
			context.commit("setAppLoadingStatus", "Loading.StartingApp");

			// After bootstrap, continue initialization
			return context.dispatch("init");
		});
	},

	/**
	 * Initialize the application logic by continuing the state machine
	 *
	 * @return {Promise} Transition success
	 */
	init = function( context ) {
		/**
		 * Mind the context:
		 * - start the login process when the application is installed (ie. settings are present)
		 * - start the installation process otherwise
		 */
		return installService.isInstalled().then( function( installed ) {
			return fsm.do(installed ? fsm.tr.START_LOGIN : fsm.tr.START_INSTALLATION);
		});
	},

	/**
	 * Load the application data by continuing the state machine
	 *
	 * @return {Promise} Transition success
	 */
	load = function( context ) {
		/**
		 * Transition to load the application data:
		 * - after finishing the installation process
		 * - after closing the login process
		 */
		return fsm.do([
			fsm.tr.CLOSE_INSTALLATION,
			fsm.tr.CLOSE_LOGIN
		]);
	};

	return {
		bootstrap: bootstrap,
		init: init,
		load: load
	};
});
