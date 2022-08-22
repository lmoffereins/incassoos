/**
 * Dark Mode Service Functions
 * 
 * @package Incassoos
 * @subpackage App/Services
 */
define([
	"q",
	"util",
	"./storage-service",
	"./shortcuts-service"
], function( Q, util, storageService, shortcutsService ) {
	/**
	 * Define listener construct for the service
	 *
	 * Events triggered in this domain are:
	 *  - set (id)
	 *
	 * @type {Object}
	 */
	var listeners = util.createListeners("service/darkmode"),

	/**
	 * Holds the available dark modes
	 *
	 * The 'auto' mode defers to the indicated scheme of the current system.
	 *
	 * @type {Array}
	 */
	availableModes = {
		auto: "Administration.DarkModeAuto",
		light: "Administration.DarkModeLight",
		dark: "Administration.DarkModeDark"
	},

	/**
	 * Holds the id for the active dark mode setting
	 *
	 * @type {String}
	 */
	darkmode = "auto",

	/**
	 * Initialization of the dark mode service
	 *
	 * @param {Object} Vue The Vue instance
	 * @return {Promise} Is the service initialized?
	 */
	init = function( Vue ) {
		/**
		 * Make the dark mode available at Vue's root
		 *
		 * @return {String} Mode id
		 */
		Object.defineProperty(Vue.prototype, "$darkmode", {
			get: function() {
				return this.$store.state.darkmode;
			}
		});

		/**
		 * Make the dark mode setting available at Vue's root
		 *
		 * @return {String} Mode id
		 */
		Object.defineProperty(Vue.prototype, "$darkmodeSetting", {
			get: function() {
				return this.$store.state.darkmodeSetting;
			}
		});

		// Register global keyboard event listeners
		shortcutsService.on({
			/**
			 * Toggle between light and dark mode
			 */
			"shift+alt+M": {
				label: "Administration.ToggleDarkMode",
				callback: function() {
					isDarkmode() ? set("light") : set("dark");
				}
			}
		});

		// Maybe get the active mode from storage
		return storageService.get("darkmode").then( function( mode ) {
			mode && set(mode, { preventSave: false });
		});
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
				state.darkmode = isDarkmode();
				state.darkmodeSetting = darkmode;
			},

			/**
			 * Modify service related methods in the main store's mutations
			 *
			 * @param  {Object} mutations Store mutations
			 * @return {Void}
			 */
			defineStoreMutations: function( mutations ) {
				/**
				 * Update reactive properties for the dark mode
				 *
				 * @return {Void}
				 */
				mutations.setDarkmode = function( state ) {
					state.darkmode = isDarkmode();
					state.darkmodeSetting = darkmode;
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
				 * When changing the active dark mode, update the main store's dark mode data
				 *
				 * @return {Function} Deregistration method
				 */
				listeners.on("set", function() {

					// Mutate the reactive dark mode data
					context.commit("setDarkmode");
				});

				if (window.matchMedia) {
					/**
					 * When the system preference changes, update the main store's dark mode data
					 */
					window.matchMedia("(prefers-color-scheme: dark)").addListener( function() {
						context.commit("setDarkmode");
					});
				}
			}
		};
	},

	/**
	 * Return the actual mode
	 *
	 * @return {String} Mode id
	 */
	isDarkmode = function() {
		return "dark" === ("auto" === darkmode ? getSystemScheme() : darkmode);
	},

	/**
	 * Set the active mode and save the mode to the global settings
	 *
	 * @param {String} mode Mode id
	 * @param {Object} options Setting options
	 * @return {Promise} Was the mode saved?
	 */
	set = function( mode, options ) {
		options = options || {};
		options.preventSave = options.preventSave || false;

		// Set the new mode
		if (-1 !== availableModes.hasOwnProperty(mode) && darkmode !== mode) {
			darkmode = mode;

			return Q.Promisify(options.preventSave || storageService.save("darkmode", mode)).then( function() {
				/**
				 * Trigger event listeners for any change in the currently active mode.
				 *
				 * @param {String} mode Active mode id
				 */
				listeners.trigger("set", mode);
			});
		} else {
			return Q.resolve();
		}
	},

	/**
	 * Return the available modes
	 *
	 * @return {Object} Available modes
	 */
	getAvailableModes = function() {
		return availableModes;
	},

	/**
	 * Return the mode setting from the current system (css: prefers-color-scheme)
	 *
	 * @return {String} Mode id
	 */
	getSystemScheme = function() {
		return window.matchMedia && window.matchMedia("(prefers-color-scheme:dark)").matches ? "dark" : "light";
	};

	return {
		init: init,
		getAvailableModes: getAvailableModes,

		/**
		 * Return whether dark mode is active
		 *
		 * @return {Boolean} Is dark mode active?
		 */
		isDarkmode: function() {
			return isDarkmode();
		},
		set: set,
		off: listeners.off,
		on: listeners.on,
		storeDefinition: storeDefinition
	};
});
