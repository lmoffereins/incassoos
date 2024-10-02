/**
 * Settings data
 *
 * @package Incassoos
 * @subpackage App/Core
 */
define([
	"lodash",
	"services",
	"util"
], function( _, services, util ) {
	/**
	 * Holds a reference to the installation service
	 *
	 * @type {Object}
	 */
	var installService = services.get("install"),

	/**
	 * Define listener construct for the settings
	 *
	 * Events triggered in this domain are:
	 *  - update
	 *
	 * @type {Object}
	 */
	listeners = util.createListeners("settings"),

	/**
	 * Holds the settings data
	 *
	 * Starts with a few default values. When running locally use local defaults.
	 *
	 * @type {Object}
	 */
	settings = _.defaultsDeep(_.get(incassoosL10n, "settings") || {}, {

		// API settings
		api: {
			routes: {
				root: "/" // Root path. Used internally; not present in remote or stored settings
			}
		},

		// Main settings
		main: {
			currencyFormatArgs: {
				format: "%s",
				symbol: "",
				decimals: 2,
				decimal_point: util.getNumberFormatSeparator("decimal"),
				thousands_sep: util.getNumberFormatSeparator("thousand")
			}
		},

		// Login settings
		login: {
			loginAttemptsAllowed: 3,
			loginAttemptsTimeout: 180
		},

		// Occasion settings
		occasion: {
			occasionType: {
				items: {},
				defaultValue: 0
			}
		},

		// Consumer settings
		consumer: {},

		// Order settings
		order: {
			orderTimeLock: 0
		},

		// Product settings
		product: {
			productCategory: {
				items: {},
				archivedItems: [],
				defaultValue: 0
			}
		}
	}),

	/**
	 * Update the settings construct with data from the API
	 *
	 * @return {Void}
	 */
	updateSettingsFromStorage = function() {

		// Get the installation details
		installService.get().then( function( values ) {
			var i, j;

			// Get the API root
			if (values.root) {
				settings.api.root = values.root;
			}

			// Parse stored settings
			// Note that parsing does not modify `settings` immediately. This is to accomodate
			// for the prioritization of the stored values when using the defaulter. Modifying
			// `settings` instead of overwriting is required for the sake of keeping the same
			// single object reference.
			j = _.defaultsDeep(
				values.settings || {},
				settings
			);

			// Update the settings data
			for (i in j) {
				settings[i] = j[i];
			}

			/**
			 * Trigger event listeners for when the settings construct is updated
			 *
			 * @param {Object} settings The settings construct
			 */
			listeners.trigger("update", settings);
		});
	};

	/**
	 * Shortcut method for listening on the 'update' event
	 *
	 * @param  {Function} callback Event handler
	 * @return {Function} Listener deregisterer
	 */
	settings.$onUpdate = function( callback ) {
		return listeners.on("update", callback);
	};

	// When the installation is run, update the settings construct
	services.on("init", function( context ) {

		// Set loading status
		context.commit("setAppLoadingStatus", "Loading.LoadingSettings");

		// Update settings
		updateSettingsFromStorage();
	});

	// When the installation is run, update the settings construct
	installService.on("installed", updateSettingsFromStorage);

	return settings;
});
