/**
 * Cache Service Functions
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
	 * Holds the storage for the service
	 *
	 * @type {Object}
	 */
	var storage = storageService.create("service/cache"),

	/**
	 * Define listener construct for the service
	 *
	 * Events triggered in this domain are:
	 *  - saved (key, value)
	 *
	 * @type {Object}
	 */
	listeners = util.createListeners("service/cache"),

	/**
	 * Contains the default cache expiry time in hours
	 *
	 * @type {Number}
	 */
	defaultCacheExpires = 24,

	/**
	 * Holds the key for the stored timestamp of the last cache clearing
	 *
	 * @type {String}
	 */
	cacheClearKey = "cacheLastCleared",

	/**
	 * Holds the timeframe of the cache clearing
	 *
	 * @type {Number}
	 */
	cacheClearTimeframe = 3 * 24 * 60 * 60 * 1000,

	/**
	 * API for the checksums of the cached data
	 *
	 * Checksums help maintain the integrity of the stored cache. By
	 * comparing the checksum of a cached value with the previously
	 * stored value, this ensures that the cache wasn't changed.
	 *
	 * @type {Object}
	 */
	_checksums = {
		key: "$checksums",

		/**
		 * Return the checksum for the given cache key
		 *
		 * @param  {String} key Cache key
		 * @return {Promise} Cache checksum
		 */
		get: function( key ) {
			return storage.get(this.key).then( function( list ) {
				return list && list[key.toString()];
			});
		},

		/**
		 * Save the checksum for the given cache key/value pair
		 *
		 * @param  {String} key Cache key
		 * @param  {Mixed} value Cache value
		 * @param  {Object} options Cache options.
		 * @return {Promise} Cache checksum
		 */
		save: function( key, value, options ) {
			var self = this;

			options = options || {};

			// Cache expiry time in hours. Defaults to 24 hours.
			if ("number" !== typeof options.expires) {
				options.expires = defaultCacheExpires;
			}

			return storage.get(self.key).then( function( list ) {
				list = list || {};
				list[key.toString()] = {
					expires: new Date().getTime() + (options.expires * 60 * 60 * 1000),
					checksum: util.hash(value)
				};

				return storage.save(self.key, list);
			});
		},

		/**
		 * Remove the checksum for the given cache key
		 *
		 * @param  {String} key Cache key
		 * @return {Promise} Was the cache checksum removed?
		 */
		remove: function( key )  {
			var self = this;

			return storage.get(self.key).then( function( list ) {
				list = list || {};
				delete list[key];

				return storage.save(self.key, list);
			});
		}
	},

	/**
	 * Initialization of the cache service
	 *
	 * @return {Promise} Is service initialized?
	 */
	init = function() {
		var dfd = Q.defer();

		// Make sure the checksums list is present
		storage.get(_checksums.key).then( function( value ) {
			if ("undefined" === typeof value) {
				storage.save(_checksums.key, {});
			}
		});

		// Clear the cache on a schedule
		storageService.get(cacheClearKey).then( function( value ) {
			var now = new Date().getTime() + cacheClearTimeframe;

			// When this is the first 
			if ("undefined" === typeof value || now > value) {
				clear().then(dfd.resolve);
			} else {
				dfd.resolve();
			}
		});

		// Register shortcuts
		shortcutsService.on({

			// Reset cache when hard-reloading the page
			"ctrl+shift+R": {
				label: "Administration.ClearCacheAndReloadLabel",
				callback: function cacheServiceResetOnCtrlShiftR() {
					reset();
				}
			}
		});

		return dfd.promise;
	},

	/**
	 * Validate the found cache value
	 *
	 * @param  {String} key Cache key
	 * @param  {Mixed} value Optional. Cached value. Defaults to only validating the expiration.
	 * @return {Promise} Is the cache validated?
	 */
	validate = function( key, value ) {
		return _checksums.get(key).then( function( checksum ) {
			var checked = "undefined" === typeof value || checksum.checksum === util.hash(value);

			// Validate expiration and integrity
			if (new Date().getTime() < checksum.expires && checked) {
				return true;

			// When invalid, remove the cache
			} else {
				return remove(key).then( function() {
					return false;
				});
			}
		});
	},

	/**
	 * Return a value from the cache
	 *
	 * @param  {String} key Cache key
	 * @return {Promise} Cache value or rejected when not found or invalid
	 */
	get = function( key ) {
		return storage.get(key).then( function( value ) {
			var dfd = Q.defer();

			// When a value was found, validate it
			if ("undefined" !== typeof value) {
				dfd.resolve(validate(key, value));
			} else {
				dfd.resolve(false);
			}

			return dfd.promise.then( function( validated ) {
				return validated ? Q.resolve(value) : Q.reject(value);
			});
		});
	},

	/**
	 * Save a value to the cache
	 *
	 * @param  {String} key Cache key
	 * @param  {Mixed} value Cache value
	 * @param  {Object} options Cache options
	 * @return {Promise} Cached value
	 */
	save = function( key, value, options ) {
		return storage.save(key, value).then( function() {

			// Store the value's checksum
			return _checksums.save(key, value, options).then( function() {
				/**
				 * Trigger event listeners for a saved cache value.
				 *
				 * @param {String} key Cache key
				 * @param {Mixed} value Cache value
				 */
				return listeners.trigger("save", key, value).then( function() {
					return value;
				});
			});
		});
	},

	/**
	 * Remove the indicated cache
	 *
	 * @param  {String} key Cache key
	 * @return {Promise} Was the cache removed?
	 */
	remove = function( key ) {
		return Q.all([
			storage.remove(key),
			_checksums.remove(key)
		]);
	},

	/**
	 * Clear the cache storage
	 *
	 * @param  {Boolean} forceAll Optional. Whether to force clear all caches. Defaults to false.
	 * @return {Promise} Are the caches cleared?
	 */
	clear = function( forceAll ) {
		var dfd = Q.defer(),
		    now = new Date().getTime();

		// Force remove all caches
		if (forceAll) {
			storage.clear().then( function() {

				// Clear checksums
				storage.save(_checksums.key, {}).then(dfd.resolve);
			});

		// Remove only expired caches
		} else {
			storage.get(_checksums.key).then( function( list ) {
				var key;

				// Expiration validator applies removal policy
				for (key in list) {
					list[key] = validate(key);
				}

				Q.all(list).then(dfd.resolve);
			});
		}

		// Store time reference
		return dfd.promise.then( function() {
			return storageService.save(cacheClearKey, now);
		});
	},

	/**
	 * Reset the cache storage
	 *
	 * @return {Promise} Is the cache reset?
	 */
	reset = function() {
		return clear(true);
	},

	/**
	 * Return the request's cache key
	 *
	 * The key is derived from the relevant query elements.
	 *
	 * @param  {Object} request Request object
	 * @return {String} Cache key
	 */
	getCacheKeyForRequest = function( request ) {
		return util.hash(request.url);
	};

	return {
		clear: clear,
		get: get,
		getCacheKeyForRequest: getCacheKeyForRequest,
		init: init,
		remove: remove,
		off: listeners.off,
		on: listeners.on,
		reset: reset,
		save: save
	};
});
