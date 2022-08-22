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
	 * @return {Void}
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
		fsm.do(fsm.tr.BOOTSTRAP, context).then( function() {

			// After bootstrap, continue initialization
			context.dispatch("init");
		});
	},

	/**
	 * Initialize the application logic by continuing the state machine
	 *
	 * @return {Void}
	 */
	init = function( context ) {
		console.log("actions/init");
		/**
		 * Mind the context:
		 * - start installation when no settings are present
		 * - start login process otherwise
		 */
		installService.isInstalled().then( function( installed ) {
			fsm.do(installed ? fsm.tr.START_LOGIN : fsm.tr.START_INSTALLATION);
		});
	},

	/**
	 * Load the application data by continuing the state machine
	 *
	 * @return {Void}
	 */
	load = function( context ) {
		console.log("actions/load");
		/**
		 * Transition to load the application data
		 */
		fsm.do([fsm.tr.FINISH_INSTALLATION, fsm.tr.CLOSE_LOGIN]);
	};

	return {
		bootstrap: bootstrap,
		init: init,
		load: load
	};
});
