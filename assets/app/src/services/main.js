/**
 * Centralized service functions
 *
 * @package Incassoos
 * @subpackage App/Services
 */
define([
	"vue",
	"q",
	"util",
	"./auth-service",
	"./cache-service",
	"./clock-service",
	"./darkmode-service",
	"./debug-service",
	"./delay-service",
	"./dialog-service",
	"./feedback-service",
	"./focus-service",
	"./history-service",
	"./idle-service",
	"./install-service",
	"./l10n-service",
	"./log-service",
	"./offline-service",
	"./shortcuts-service",
	"./storage-service",
	"./visibility-service"
], function(
	Vue,
	Q,
	util,
	authService,
	cacheService,
	clockService,
	darkmodeService,
	debugService,
	delayService,
	dialogService,
	feedbackService,
	focusService,
	historyService,
	idleService,
	installService,
	l10nService,
	logService,
	offlineService,
	shortcutsService,
	storageService,
	visibilityService
) {
	/**
	 * Holds the exposed services
	 *
	 * @type {Object}
	 */
	var services = {
		auth: authService,
		cache: cacheService,
		clock: clockService,
		darkmode: darkmodeService,
		debug: debugService,
		delay: delayService,
		dialog: dialogService,
		feedback: feedbackService,
		focus: focusService,
		history: historyService,
		idle: idleService,
		install: installService,
		l10n: l10nService,
		log: logService,
		offline: offlineService,
		shortcuts: shortcutsService,
		storage: storageService,
		visibility: visibilityService
	},

	/**
	 * Define listener construct for the main services
	 *
	 * Events triggered in this domain are:
	 *  - init
	 *  - reset
	 *
	 * @type {Object}
	 */
	listeners = util.createListeners("services"),

	/**
	 * Return the named methods of each service when present
	 *
	 * @param  {String} name Method name
	 * @return {Array} Service methods
	 */
	getServiceMethods = function( name ) {
		var fns = [], i;

		// Collect named methods from the services
		for (i in services) {
			if (services[i][name] && "function" === typeof services[i][name]) {
				fns.push({
					service: i,
					run: services[i][name]
				});
			}
		}

		return fns;
	},

	/**
	 * Holds the service defintitions of store modifiers for its state, mutations and context
	 *
	 * @type {Array}
	 */
	defineStore = {
		state: [],
		mutations: [],
		context: []
	};

	// Unfold the service store definitions
	getServiceMethods("storeDefinition").forEach( function( storeDefinition ) {
		var methods = storeDefinition.run();

		// Add state modifier
		if (methods.defineStoreState && "function" === typeof methods.defineStoreState) {
			defineStore.state.push(methods.defineStoreState);
		}

		// Add mutations modifier
		if (methods.defineStoreMutations && "function" === typeof methods.defineStoreMutations) {
			defineStore.mutations.push(methods.defineStoreMutations);
		}

		// Add context users
		if (methods.defineStoreContextUsage && "function" === typeof methods.defineStoreContextUsage) {
			defineStore.context.push(methods.defineStoreContextUsage);
		}

		// Remove the `storeDefinition` method from outer API access
		delete services[storeDefinition.service].storeDefinition;
	});

	return {
		/**
		 * Initialize all services
		 *
		 * @param {Object} context Store action context
		 * @return {Promise} Services are initialized
		 */
		init: function( context ) {

			// Do storage service first
			return services.storage.init().then( function() {

				// Then run all other initializations
				return Q.all(getServiceMethods("init").map( function( init ) {

					// Run the `init` method and make sure it returns a Promise
					// The storage service was run previously
					if ("storage" !== init.service) {
						return Q.Promisify(init.run(Vue)).catch(console.error);
					}
				}));
			}).then( function() {
				/**
				 * Trigger event listeners for when all services are initialized
				 *
				 * @param {Object} context Store action context
				 */
				return listeners.trigger("init", context);
			}).catch( function( error ) {

				// TODO: register in log?
				console.error("An error occurred when initializing services.", error);

				return Q.reject(error);
			});
		},

		/**
		 * Return the requested service object
		 *
		 * @param  {String} service Service name
		 * @return {Object} Service object or Null when not found.
		 */
		get: function( service ) {
			return services[service] || null;
		},

		/**
		 * Reset all services
		 *
		 * @return {Promise} Services are reset
		 */
		reset: function() {

			// Do storage service first
			return services.storage.clear().then( function( globalDb ) {

				// Then run all other clears and resets
				return Q.all(getServiceMethods("clear")
					.concat(getServiceMethods("reset"))
					.map( function( reset ) {

						// Run the `reset` method and make sure it returns a Promise
						return Q.Promisify(reset.run());
					})
				);
			}).then( function() {
				/**
				 * Trigger event listeners for when all services are reset
				 */
				return listeners.trigger("reset");
			}).catch( function( error ) {

				// TODO: register in log?
				console.error("An error occurred when resetting services.", error);

				return Q.reject(error);
			});
		},

		// Hook listener methods
		on: listeners.on,
		off: listeners.off,

		/**
		 * Define service related properties in the main store's state
		 *
		 * @param  {Object} state Store state
		 * @return {Void}
		 */
		defineStoreState: function( state ) {
			defineStore.state.forEach( function( callback ) {
				callback(state);
			});
		},

		/**
		 * Define service related methods in the main store's mutations
		 *
		 * @param  {Object} mutations Store mutations
		 * @return {Void}
		 */
		defineStoreMutations: function( mutations ) {
			defineStore.mutations.forEach( function( callback ) {
				callback(mutations);
			});
		},

		/**
		 * Define service related usage of the main store's context
		 *
		 * @param  {Object} context Store context
		 * @return {Void}
		 */
		defineStoreContextUsage: function( context ) {
			defineStore.context.forEach( function( callback ) {
				callback(context);
			});
		}
	};
});
