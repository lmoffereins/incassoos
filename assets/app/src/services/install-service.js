/**
 * Installation Service Functions
 * 
 * @package Incassoos
 * @subpackage App/Services
 */
define([
	"q",
	"util",
	"./storage-service"
], function( Q, util, storageService ) {
	/**
	 * Define listener construct for the service
	 *
	 * Events triggered in this domain are:
	 *  - installed
	 *
	 * @type {Object}
	 */
	var listeners = util.createListeners("service/install"),

	/**
	 * Holds the storage key for the API root
	 *
	 * @type {String}
	 */
	storageKeyApiRoot = "apiRoot",

	/**
	 * Holds the storage key for the API settings
	 *
	 * @type {String}
	 */
	storageKeySettings = "settings",

	/**
	 * Return the installation details
	 *
	 * @return {Promise} Installation details
	 */
	get = function() {
		return storageService.get([
			storageKeyApiRoot,
			storageKeySettings
		]).then( function( values ) {
			return {
				root: values[storageKeyApiRoot],
				settings: values[storageKeySettings]
			};
		});
	},

	/**
	 * Store the installation details
	 *
	 * @param  {Object} payload Installation data
	 * @return {Promise} Did the installation run?
	 */
	install = function( payload ) {
		var saves = {};

		payload = payload || {};

		if ("undefined" !== typeof payload.root) {
			saves[storageKeyApiRoot] = payload.root;
		}

		if ("undefined" !== typeof payload.settings) {
			saves[storageKeySettings] = payload.settings;
		}

		return storageService.save(saves).then( function() {
			/**
			 * Trigger event listeners for when the installation is done
			 */
			listeners.trigger("installed");
		});
	},

	/**
	 * Return whether the application is installed
	 *
	 * @param  {Object} options Installation options
	 * @return {Promise} Is the application installed?
	 */
	isInstalled = function() {
		return get().then( function( values ) {
			return !! values.root && !! values.settings;
		});
	},

	/**
	 * Return whether the application is used locally
	 *
	 * @return {Boolean} Is the application used locally?
	 */
	isLocal = !! ("undefined" !== typeof incassoosL10n && incassoosL10n.isLocal);

	return {
		get: get,
		install: install,
		isInstalled: isInstalled,
		isLocal: isLocal,
		on: listeners.on
	};
});
