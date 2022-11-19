/**
 * Resize Service Functions
 * 
 * @package Incassoos
 * @subpackage App/Services
 */
define([
	"vue",
	"q",
	"lodash",
	"util"
], function( Vue, Q, _, util ) {
	/**
	 * Define listener construct for the service
	 *
	 * Events triggered in this domain are:
	 *  - change
	 *  - {size}
	 *
	 * @type {Object}
	 */
	var listeners = util.createListeners("service/resize"),

	/**
	 * Holds the active screen size
	 *
	 * @type {String}
	 */
	size = "",

	/**
	 * Holds the available screen sizes
	 *
	 * @type {Array}
	 */
	availableSizes = [],

	/**
	 * Define the screen size when the window is resized
	 *
	 * @param {Object} event Event data or MatchQueryListEvent data
	 * @return {Void}
	 */
	setScreenSize = function setScreenSizeOnResize( event ) {
		var width = document.body.clientWidth,
		    event = event || { matches: true }, i;

		// Inside of range
		if (window.matchMedia && ! event.matches) {

			// Walk items in any order
			for (i = 0; i < availableSizes.length; i++) {

				// Check for matching media query
				if (event.media === "(max-width: ".concat(availableSizes[i].size, "px)")) {
					size = availableSizes[i].name;
				}
			};

		// Outside of range
		} else {

			// Walk items in descending order
			for (i = 0; i < availableSizes.length; i++) {
				if (width > availableSizes[i].size) {
					size = availableSizes[i].name;
					break;
				}
			}
		}

		/**
		 * Trigger event listeners for when the application was resized to a specific size
		 */
		listeners.trigger(size);

		/**
		 * Trigger event listeners for when the application was resized
		 */
		listeners.trigger("change", size);
	},

	/**
	 * Register the callback to listen for the resize event
	 *
	 * Prefer to use `window.matchMedia()` as it only triggers on passing the threshold.
	 *
	 * @return {Function} Deregistration callback
	 */
	registerListeners = function() {
		var matches = {}, i;

		// Remove previous listeners
		removeListeners();

		// Set initial screen size
		setScreenSize();

		// Register listeners
		if (window.matchMedia) {
			for (i = 0; i < availableSizes.length; i++) {
				matches[i] = window.matchMedia("(max-width: ".concat(availableSizes[i].size, "px)"));
				matches[i].addEventListener("change", setScreenSize);
			}
		} else {
			window.addEventListener("resize", setScreenSize);
		}

		/**
		 * Deregister the registered listener
		 *
		 * @return {Void}
		 */
		return function removeResizeListeners() {
			if (! _.isEmpty(matches)) {
				for (i in matches) {
					matches[i].removeListener(setScreenSize);
				}
			} else {
				window.removeEventListener("resize", setScreenSize);
			}
		}
	},

	/**
	 * Remove listeners
	 *
	 * @type {Function}
	 */
	removeListeners = function() {},

	/**
	 * Initialization of the resize service
	 *
	 * @return {Promise} Is the service initialized?
	 */
	init = function() {
		setScreenSize();

		return Q.resolve();
	},

	/**
	 * Remove listeners when resetting the service
	 *
	 * @return {Void}
	 */
	reset = function() {
		removeListeners();
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
				state.screenSize = size;
			},

			/**
			 * Modify service related methods in the main store's mutations
			 *
			 * @param  {Object} mutations Store mutations
			 * @return {Void}
			 */
			defineStoreMutations: function( mutations ) {
				/**
				 * Update reactive property for resizeService's data property
				 *
				 * @return {Void}
				 */
				mutations.setScreenSize = function( state ) {
					state.screenSize = size;
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
				 * When changing the screen size, update the main store's data
				 *
				 * @return {Function} Deregistration method
				 */
				listeners.on("change", function() {

					// Mutate the reactive `screenSize` data
					context.commit("setScreenSize");
				});
			}
		};
	},

	/**
	 * Set the available screen sizes
	 *
	 * @param {Object|Array} newSizes New screen sizes
	 * @return {Void}
	 */
	set = function( newSizes ) {
		var sizes = [], s, i;

		// Bail when no sizes were provided
		if (! newSizes) {
			return;
		}

		// Handle object
		if (_.isPlainObject(newSizes)) {
			for (i in newSizes) {
				if (newSizes.hasOwnProperty(i) && "string" === typeof i && "number" === typeof newSizes[i]) {
					sizes.push({
						name: i,
						size: newSizes[i]
					});
				}
			}

		// Handle array
		} else if (Array.isArray(newSizes)) {
			newSizes.forEach( function( item ) {
				if ("string" === typeof item.name && "number" === typeof item.size) {
					sizes.push(item);
				}
			});
		}

		// Ensure presence of smallest size 0
		if (! sizes.find(item => 0 === item.size)) {
			sizes.push({
				name: "_small",
				size: 0
			});
		}

		// Set the new sizes, ordered
		availableSizes = _.orderBy(sizes, "size", "desc");

		// Redefine listeners
		removeListeners = registerListeners();
	},

	/**
	 * Return the active screen size
	 *
	 * @return {String} The active screen size
	 */
	get = function() {
		return size;
	},

	/**
	 * Return the names of the available screen sizes
	 *
	 * @return {Array} The names of the available screen sizes
	 */
	getAvailableSizeNames = function() {
		var sizes = {}, i;

		for (i = 0; i < availableSizes.length; i++) {
			sizes[availableSizes[i].name.toUpperCase()] = availableSizes[i].name;
		}

		return sizes;
	};

	/**
	 * Reactive listener for the active screen size
	 *
	 * @return {String} The active screen size
	 */
	Object.defineProperty(Vue.prototype, "$screenSize", {
		get: function() {
			return this.$store.state.screenSize;
		}
	});

	return {
		get: get,
		getAvailableSizeNames: getAvailableSizeNames,
		init: init,
		on: listeners.on,
		off: listeners.off,
		reset: reset,
		set: set,
		storeDefinition: storeDefinition
	};
});
