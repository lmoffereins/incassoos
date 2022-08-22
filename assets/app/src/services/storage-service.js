/**
 * Storage Service Functions
 *
 * @package Incassoos
 * @subpackage App/Services
 */
define([
	"q",
	"localforage",
	"util"
], function( Q, localForage, util ) {
	/**
	 * Holds the global store for key-value settings
	 *
	 * @type {Object}
	 */
	var globalDb = null,

	/**
	 * Initialization of storage service
	 *
	 * @return {Promise} Is service initialized?
	 */
	init = function() {

		// When local storage is available
		return localForage.ready().then( function() {

			// Create the global application storage
			if (null === globalDb) {
				globalDb = create("global");

				// Report no support for offline features
				// TODO: document offline features 
				// TODO: handle service error messaging
				if (! isOfflineSupported) {
					return {
						isWarning: true,
						message: "No support for database storage. Offline features are disabled.",
						confirm: true
					};
				}
			}

			return globalDb.ready();
		});
	},

	/**
	 * Shortcut for `globalDb.ready()`
	 *
	 * @return {Promise} Result of `globalDb.ready()`
	 */
	ready = function() {
		return init();
	},

	/**
	 * Shortcut for `globalDb.get()`
	 *
	 * @return {Promise} Result of `globalDb.get()`
	 */
	get = function() {
		var args = Array.prototype.slice.call(arguments);
		return init().then( function() {
			return globalDb.get.apply(globalDb, args);
		});
	},

	/**
	 * Shortcut for `globalDb.save()`
	 *
	 * @return {Promise} Result of `globalDb.save()`
	 */
	save = function() {
		var args = Array.prototype.slice.call(arguments);
		return init().then( function() {
			return globalDb.save.apply(globalDb, args);
		});
	},

	/**
	 * Shortcut for `globalDb.remove()`
	 *
	 * @return {Promise} Result of `globalDb.remove()`
	 */
	remove = function() {
		var args = Array.prototype.slice.call(arguments);
		return init().then( function() {
			return globalDb.remove.apply(globalDb, args);
		});
	},

	/**
	 * Shortcut for `globalDb.clear()`
	 *
	 * @return {Promise} Result of `globalDb.clear()`
	 */
	clear = function() {
		var args = Array.prototype.slice.call(arguments);
		return init().then( function() {
			return globalDb.clear.apply(globalDb, args);
		});
	},

	/**
	 * Return a prefixed version of the input text
	 *
	 * @param  {String} str Text to prefix
	 * @return {String} Prefixed text
	 */
	prefixed = function( str ) {
		return "incassoos/".concat(str);
	},

	/**
	 * Holds the methods for storing larger data in a database
	 *
	 * @type {Object}
	 */
	create = function( name ) {
		/**
		 * Holds the instance of this database
		 *
		 * @type {Object}
		 */
		var database = localForage.createInstance({
			name: prefixed(name)
		}),

		/**
		 * Define listener construct for this database
		 *
		 * Events triggered in this domain are:
		 *  - remove (key)
		 *  - save (key, value)
		 *
		 * @type {Object}
		 */
		listeners = util.createListeners("service/storage/".concat(name)),

		/**
		 * Return all values or a single value from the database storage
		 *
		 * @param {Array|String} key Optional. Storage key or keys of which to return the value. Omit to return all values.
		 * @param {Mixed} fallback Optional. Default return value when requesting a single value.
		 * @return {Promise} Stored value or list of values
		 */
		get = function( key, fallback ) {

			// Return multiple or all items
			if (Array.isArray(key) || ! arguments.length) {
				var items = {};

				return database.iterate( function( value, _key ) {
					if ("undefined" === typeof key || -1 !== key.indexOf(_key)) {
						items[_key] = value;
					}
				}).then( function() {
					return items;
				});

			// Return requested item
			} else {
				return database.getItem(key).then( function( value ) {

					// Use fallback value
					if (null === value || "undefined" === typeof value) {
						value = fallback;
					}

					return value;
				});
			}
		},

		/**
		 * Save a value in the database storage
		 *
		 * @param {String} key Storage key
		 * @param {Mixed} value Storage value
		 * @return {Promise} Storage success
		 */
		_save = function( key, value ) {

			// Make sure the key is a string
			key = key.toString();

			// Save the database value
			return database.setItem(key, value).then( function( value ) {

				/**
				 * Trigger event listeners for when the specific storage value was saved
				 *
				 * @param {Mixed} value The saved value
				 */
				listeners.trigger("save/".concat(key), value);

				/**
				 * Trigger event listeners for when any storage value was saved
				 *
				 * @param {String} key Key of the saved value
				 * @param {Mixed} value The saved value
				 */
				listeners.trigger("save", key, value);
			});
		},

		/**
		 * Start to save a value in the databse storage
		 *
		 * Supports multi-key saving.
		 *
		 * @param  {String|Object} key Storage key or set of key-value pairs to save
		 * @param  {Mixed} value Optional. Storage value when saving a single value
		 * @return {Promise} Storage success
		 */
		save = function( key, value ) {
			var i, saves = [];

			// Assume a list of saveable values
			if ("string" !== typeof key && "undefined" === typeof value) {
				key = key || {};

				for (i in key) {
					if (key.hasOwnProperty(i)) {
						saves.push(_save(i, key[i]));
					}
				}

				return Q.all(saves);

			// Default save method
			} else {
				return _save(key, value);
			}
		},

		/**
		 * Remove a value from the database storage
		 *
		 * @param {String} key Storage key
		 * @return {Promise} Removal success
		 */
		remove = function( key ) {

			// Make sure the key is a string
			key = key.toString();

			// Remove the database value
			return database.removeItem(key).then( function() {

				/**
				 * Trigger event listeners for when the sepcific stored value was removed
				 */
				listeners.trigger("remove/".concat(key));

				/**
				 * Trigger event listeners for when any stored value was removed
				 *
				 * @param {String} key Key of the removed value
				 */
				listeners.trigger("remove", key);
			});
		},

		/**
		 * Return the values from the database storage as an array
		 *
		 * @param {Function} filter Optional. Filter callback
		 * @return {Promise} List of values
		 */
		toArray = function( filter ) {
			var items = [];

			return database.iterate( function( value, key ) {
				if ("function" !== typeof filter || filter(value)) {
					items.push(value);
				}
			}).then( function() {
				return items;
			});
		};

		return {
			clear: function() {
				return database.clear();
			},
			filter: toArray,
			get: get,
			length: function() {
				return database.length;
			},
			on: listeners.on,
			off: listeners.off,
			ready: function() {
				return database.ready();
			},
			remove: remove,
			save: save,
			toArray: toArray
		};
	},

	/** Database storage */

	/**
	 * Holds whether localStorage is supported
	 *
	 * Local storage is required for keeping required details in memory, like
	 * the api domain, application settings, user settings, request caches, etc.
	 *
	 * @type {Boolean}
	 */
	isStorageSupported = localForage.supports(localForage.LOCALSTORAGE),

	/**
	 * Holds whether database storage is supported
	 *
	 * To support offline functionality, larger storage options are required.
	 *
	 * @type {Boolean}
	 */
	isOfflineSupported = localForage.supports(localForage.INDEXEDDB) || localForage.supports(localForage.WEBSQL);

	return {
		clear: clear,
		create: create,
		get: get,
		ready: ready,
		init: init,
		isOfflineSupported: isOfflineSupported,
		isStorageSupported: isStorageSupported,
		remove: remove,
		save: save
	};
});
