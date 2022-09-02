/**
 * Bootstrap functions
 *
 * @package Incassoos
 * @subpackage App
 */
define([
	"q",
	"fsm",
	"services"
], function( Q, fsm, services ) {

	/**
	 * Define global store and event listeners
	 *
	 * @param {Object} context Store action context
	 * @return {Void}
	 */
	var setupListeners = function( context ) {
		/**
		 * When leaving init, initialize store parts
		 * 
		 * When any of the actions fail (Promise is rejected), the transition is cancelled.
		 *
		 * @return {Promise} Initialize success
		 */
		fsm.observe(
			fsm.on.leave.INIT,
			function( lifecycle ) {
				return Q.all([

					// These register store listeners
					context.dispatch("consumers/init"),
					context.dispatch("occasions/init"),
					context.dispatch("orders/init"),
					context.dispatch("products/init"),
					context.dispatch("receipt/init")

				]).catch( function( error ) {

					// TODO: register in log?
					console.error("Bootstrap: Failed intializing the store parts", error);

					return Q.reject(error);
				});
			}
		);

		/**
		 * When the user is logged-in, maybe skip the login state
		 *
		 * @return {Void}
		 */
		fsm.observe(
			fsm.on.done.START_LOGIN,
			function( lifecycle ) {

				// Close login when user is already logged-in (local installation)
				if (services.get("auth").isUserLoggedIn()) {
					fsm.do(fsm.tr.CLOSE_LOGIN);
				}
			}
		);

		/**
		 * When the user is logged-in, load store data
		 *
		 * TODO: the 'action' event is triggered for both new-login and pin-login.
		 *
		 * @return {Promise} Load success
		 */
		services.get("auth").on("active", function( lifecycle ) {
			return Q.all([

				// These require an authenticated user
				context.dispatch("consumers/load"),
				context.dispatch("occasions/load"),
				context.dispatch("products/load")

			]).then( function() {

				// Declare that application data is loaded
				context.commit("setLoaded");

			}).catch( function( error ) {

				// TODO: register in log? Use global feedback?
				console.error("Bootstrap: failed loading the remote store data", error);

				return Q.reject(error);
			});
		});

		/**
		 * When the idle state is entered, declare the application ready
		 *
		 * @return {Void}
		 */
		fsm.observe(
			fsm.on.enter.IDLE,
			function( lifecycle ) {

				// Declare that the application is ready
				context.commit("setReady");
			}
		);

		/**
		 * When toggling settings, update the main store's settings flag
		 *
		 * @return {Void}
		 */
		fsm.observe([
			fsm.on.after.TOGGLE_SETTINGS,
			fsm.on.after.CLOSE_SETTINGS
		], function( lifecycle ) {

				// Mutate the reactive settings flag
				context.commit("toggleSettings", lifecycle.to === fsm.st.SETTINGS);
			}
		);

		// Register service context usage
		services.defineStoreContextUsage(context);
	};

	/**
	 * When bootstrapping, setup application environment
	 *
	 * @return {Promise} Bootstrap success
	 */
	fsm.observe(
		fsm.on.before.BOOTSTRAP,
		function( lifecycle, context ) {
			return Q.all([

				// Initialize services (auth, l10n, etc.)
				services.init(),

				// Setup event/store listeners
				Q.Promisify(setupListeners(context))

			]).catch( function( error ) {

				// TODO: register in log?
				console.error("Bootstrap: an error occurred when bootstrapping the application.", error);

				return Q.reject(error);
			});
		}
	);

	/**
	 * When bootstrapped
	 *
	 * @return {Void}
	 */
	fsm.observe(
		fsm.on.after.BOOTSTRAP,
		function( lifecycle, context ) {

			// Declare that application bootstrap was successful
			context.commit("setBootstrapped");

			// When running locally, set active user
			return services.get("auth").setActiveLocalUser();
		}
	);

	/**
	 * Trigger the boostrap process by firing the 'bootstrap' store action
	 *
	 * @param  {Object} vm The Vue instance on which the store is defined
	 * @return {Void}
	 */
	return function( vm ) {
		vm && vm.$store && vm.$store.dispatch("bootstrap");
	};
});
