/**
 * App Utility Functions
 *
 * @package Incassoos
 * @subpackage App/Core
 */
define([
	"vue",
	"q",
	"lodash",
	"object-hash"
], function( Vue, Q, _, objectHash ) {
	/**
	 * List of valid names for input types
	 *
	 * @type {Object} List of input types
	 */
	var validInputTypes = {
		TEXT: 1,
		PASSWORD: 1,
		FILE: 1,
		EMAIL: 1,
		SEARCH: 1,
		DATE: 1,
		NUMBER: 1,
		MONTH: 1,
		WEEK: 1,
		TIME: 1,
		DATETIME: 1,
		"DATETIME-LOCAL": 1,
		TEL: 1,
		URL: 1
    },

    /**
     * Holds the map of keyboard keys and codes
     *
     * @type {Object}
     */
    keyboardMap = {
		backspace: 8,
		tab: 9,
		enter: 13,
		shift: 16,
		ctrl: 17,
		alt: 18,
		pause: 19,
		"caps-lock": 20,
		escape: 27,
		"page-up": 33,
		"page-down": 34,
		end: 35,
		home: 36,
		left: 37,
		up: 38,
		right: 39,
		down: 40,
		insert: 45,
		delete: 46,
		0: 48,
		1: 49,
		2: 50,
		3: 51,
		4: 52,
		5: 53,
		6: 54,
		7: 55,
		8: 56,
		9: 57,
		A: 65,
		B: 66,
		C: 67,
		D: 68,
		E: 69,
		F: 70,
		G: 71,
		H: 72,
		I: 73,
		J: 74,
		K: 75,
		L: 76,
		M: 77,
		N: 78,
		O: 79,
		P: 80,
		Q: 81,
		R: 82,
		S: 83,
		T: 84,
		U: 85,
		V: 86,
		W: 87,
		X: 88,
		Y: 89,
		Z: 90,
		meta: 91,
		"meta-left": 91,
		"meta-right": 92,
		select: 93,
		"num-0": 96,
		"num-1": 97,
		"num-2": 98,
		"num-3": 99,
		"num-4": 100,
		"num-5": 101,
		"num-6": 102,
		"num-7": 103,
		"num-8": 104,
		"num-9": 105,
		multiply: 106,
		add: 107,
		subtract: 109,
		decimal: 110,
		divide: 111,
		F1: 112,
		F2: 113,
		F3: 114,
		F4: 115,
		F5: 116,
		F6: 117,
		F7: 118,
		F8: 119,
		F9: 120,
		F10: 121,
		F11: 122,
		F12: 123,
		"num-lock": 144,
		"scroll-lock": 145,
		"semi-colon": 186,
		equals: 187,
		comma: 188,
		dash: 189,
		period: 190,
		"forward-slash": 191,
		"grave-accent": 192,
		"open-bracket": 219,
		"back-slash": 220,
		"close-braket": 221,
		"single-quote": 222
    },

    /**
     * List of internal event listeners
     *
     * @type {Object} List of listeners per event
     */
    customListeners = {},

	/**
	 * Return camelcased version of a text
	 *
	 * @param  {String} s Text to camelcase
	 * @return {String} Camelcased text
	 */
	camelCase = function( s ) {
		var i, camelCased, parts = s.split(/[_-]/);

		// Bail when string is empty
		if (0 === s.length) {
			return s;
		}

		// Bail when string is already camelCased
		if (1 === parts.length && parts[0][0].toLowerCase() === parts[0][0]) {
			return s;
		}

		// Start camelCasing
		camelCased = parts[0].toLowerCase();

		// Append remaining Casing parts
		for (i = 1; i < parts.length; i++) {
			camelCased = camelCased.concat(parts[i].charAt(0).toUpperCase(), parts[i].substr(1).toLowerCase());
		}

		return camelCased;
	},

	/**
	 * Return a shallow copy of an object
	 *
	 * Accepts any number of objects as arguments to extend
	 *
	 * @return {Object} Extended object copy
	 */
	clone = function() {
		return Array.prototype.slice.call(arguments).reduce( function( a, i ) {
			return Object.assign(a, i);
		}, {});
	},

	/**
	 * Return a deep copy of enumerable data
	 *
	 * @param  {Mixed} a Data to copy
	 * @return {Mixed} Copied data
	 */
	copy = function( a ) {
		return JSON.parse(JSON.stringify(a));
	},

	/**
	 * Return a feedback data object
	 * 
	 * @param  {Object} options Feedback options
	 * @return {Object} Feedback API
	 */
	createFeedback = function( options ) {
		var list = [], listeners;

		options = options || {};
		options.name = "feedback-".concat(options.name || Date.now());
		options.defaultAttributes = options.defaultAttributes || _.noop;

		/**
		 * Define listener construct for a feedback list
		 *
		 * Events triggered in this domain are:
		 *  - add (item)
		 *  - remove (item)
		 *  - clear
		 *
		 * @type {Object}
		 */
		listeners = createListeners(options.name);

		/**
		 * Add an item to the feedback list
		 *
		 * @param {String} id Optional. Item identifier
		 * @param {Object|String} item Item options or message
		 * @return {Number} Item's list index
		 */
		function add( id, item ) {

			// When no id was provided. Also when applied in Array.prototype.forEach
			// where the second parameter is the item's array index.
			if (! item || "number" === typeof item) {
				item = id;

				if (options.persistent) {
					id = _.isPlainObject(id) ? id.message : id;
				} else {
					id = hash(new Date());
				}
			}

			// Accept just a message or message with parameters
			if ("string" === typeof item || (Array.isArray(item) && "string" === typeof item[0])) {
				item = {
					message: Array.isArray(item) ? item[0] : item,
					data: {
						args: Array.isArray(item) ? item.slice(1) : []
					}
				};
			}

			// When the item was previously registered, remove it or bail on a persistent list
			if (id && exists(id) && options.persistent) {
				return;
			}

			// Add new item to the list. Assign a random id
			var arrayLen = list.push(_.defaults(item, options.defaultAttributes(), {
				$id: id || hash(Math.random()),
				isError: false,
				message: "",
				data: {}
			}));

			/**
			 * Trigger event listeners for when an item is added
			 *
			 * @param {Object} item The added item
			 */
			listeners.trigger("add", list[arrayLen - 1]);

			return arrayLen - 1;
		}

		/**
		 * Return whether an item with the given id is registered
		 *
		 * @param  {String} id Item identifier
		 * @return {Boolean} Does the item exist?
		 */
		function exists( id ) {
			var item = list.find( function( item ) {
				return item.$id === id.toString();
			});

			return !! item;
		}

		/**
		 * Return the feedback list
		 *
		 * @return {Array} Feedback list
		 */
		function getList() {
			return list;
		}

		/**
		 * Remove an item from the feedback list
		 *
		 * @param  {Number|String|Object} idOrIndex Item's identifier or list index
		 * @return {Void}
		 */
		function remove( idOrIndex ) {
			var item = list.find( function( item, index ) {
				return (("string" === typeof idOrIndex) ? item.$id : index) === idOrIndex;
			});

			// Redefine the list without the indicated item
			list = list.filter( function( item, index ) {
				return (("string" === typeof idOrIndex) ? item.$id : index) !== idOrIndex;
			});

			/**
			 * Trigger event listeners for when an item is removed
			 *
			 * @param {Object} item The removed item
			 */
			listeners.trigger("remove", item);
		}

		/**
		 * Clear the feedback list
		 *
		 * @param {Boolean} force Optional. Force clearing the list.
		 * @return {Void}
		 */
		function clear( force ) {

			// Bail when the list is already empty or when not forced on a persistent list
			if (! list.length || (options.persistent && ! force)) {
				return;
			}

			// Get a copy of the list
			var _list = list.slice();

			// Clear the list
			list = [];

			/**
			 * Trigger event listeners for when the list is cleared
			 *
			 * @param {Array} list Cleared list items
			 */
			listeners.trigger("clear", _list);
		}

		/**
		 * Return the number of feedback items
		 *
		 * @return {Number} Feedback list's length
		 */
		function count() {
			return list.length;
		}

		/**
		 * Return the amount of error items
		 *
		 * @return {Number} Error count
		 */
		function errorCount() {
			return list.filter( function( i ) {
				return true === i.isError;
			}).length;
		}

		/**
		 * Return whether the feedback contains errors
		 *
		 * @return {Boolean} Feedback contains errors
		 */
		function hasErrors() {
			return errorCount() > 0;
		}

		return {
			add: add,
			clear: clear,
			count: count,
			exists: exists,
			errorCount: errorCount,
			getList: getList,
			hasErrors: hasErrors,
			on: listeners.on,
			off: listeners.off,
			remove: remove
		};
	},

	/**
	 * Create listener functions for the domain's events
	 *
	 * @param  {String} domain Domain name
	 * @return {Object} Listeners API
	 */
	createListeners = function( domain ) {

		// Define list of domain listeners
		if (! customListeners[domain]) {
			customListeners[domain] = {};
		}

		/**
		 * Trigger custom event listeners
		 *
		 * Passes any additional event arguments along to the listener callbacks.
		 *
		 * @param  {String} event Event name
		 * @return {Promise} Was the trigger successfull?
		 */
		function trigger( event ) {
			console.log("trigger > " + domain + ":" + event);

			// Wrap callbacks in try-catch block to account for possible errors
			try {

				// Collect any additional arguments
				var payload = Array.prototype.slice.call(arguments, 1), promises = [];

				// Check whether listeners are registered for the event
				if (customListeners[domain][event]) {

					// Run listeners for the event
					customListeners[domain][event].forEach( function( callback ) {
						promises.push(callback.apply(callback, payload));
					});
				}

				// Check whether wildcard domain listeners are registered
				if (customListeners[domain]["*"]) {

					// Run wildcard domain listeners
					customListeners[domain]["*"].forEach( function( callback ) {

						// The first parameter is now the event name
						promises.push(callback.apply(callback, [event].concat(payload)));
					});
				}

				return Q.all(promises);
			} catch ( error ) {
				return Q.reject(error);
			}
		}

		/**
		 * Modify a value with custom event listeners
		 *
		 * Passes any additional event arguments along to the listener callbacks.
		 *
		 * @param  {String} event Event name
		 * @param  {Mixed} retval Return value
		 * @return {Mixed} Return value
		 */
		function filter( event, retval ) {

			// Wrap callbacks in try-catch block to account for possible errors
			try {

				// Collect any additional arguments
				var payload = Array.prototype.slice.call(arguments, 2);

				// Check whether listeners are registered for the event
				if (customListeners[domain][event]) {

					// Run listeners for the event
					customListeners[domain][event].forEach( function( callback ) {
						retval = callback.apply(callback, [retval].concat(payload));
					});
				}

				// Check whether wildcard domain listeners are registered
				if (customListeners[domain]["*"]) {

					// Run wildcard domain listeners
					customListeners[domain]["*"].forEach( function( callback ) {

						// The first parameter is now the event name
						retval = callback.apply(callback, [event, retval].concat(payload));
					});
				}

			} catch ( error ) {
				return Q.reject(error);
			}

			return retval;
		}

		/**
		 * Registration callback for the given event
		 *
		 * The returned callback is useful for deregistering anonymous callbacks.
		 *
		 * Supports wildcard (`*`) listeners for any event triggered within the domain. The
		 * first argument then is the event name, followed by the event's own arguments.
		 *
		 * @param  {String|Array|Object} event Event name(s) or object with event:callback items.
		 * @param  {Function} callback Listener callback. Optional when using an event object.
		 * @return {Function} Deregistration callback
		 */
		function on( event, callback ) {

			// When a function is not provided, assume an object
			if ("function" !== typeof callback) {
				var i, offs = [];

				for (i in event) {
					if (event.hasOwnProperty(i) && "function" === typeof event[i]) {
						offs.push(on(i, event[i]));
					}
				}

				/**
				 * Deregister the registered listeners
				 *
				 * @return {Void}
				 */
				return function() {
					_.over(offs)();
				};

			// Make sure event name(s) are an array
			} else {
				Array.isArray(event) || (event = [event]);
			}

			// Walk event list
			event.forEach( function( e ) {

				// Add callback to the set of event listeners
				customListeners[domain][e] || (customListeners[domain][e] = []);
				customListeners[domain][e].push(callback);
			});

			/**
			 * Deregister the registered listener
			 *
			 * @return {Void}
			 */
			return function _off() {
				off(event, callback);
			};
		}

		/**
		 * Deregistration callback for the given event
		 *
		 * @param  {String|Array} event Event name(s)
		 * @param  {Function} callback Listener callback
		 * @return {Void}
		 */
		function off( event, callback ) {

			// Array-fy the event name(s)
			Array.isArray(event) || (event = [event]);

			// Walk event list
			event.forEach( function( e ) {

				// Check whether listerens are registered for the event
				if (customListeners[domain][e]) {

					// Remove the callback from the set of event listeners
					_.pull(customListeners[domain][e], callback);
				}
			});
		}

		return {
			filter: filter,
			on: on,
			off: off,
			trigger: trigger
		};
	},

	/**
	 * Trigger an event on a DOM element
	 *
	 * CustomEvent is polyfilled in polyfills/event.tap-longpress.js
	 *
	 * @param  {Element} el DOM element
	 * @param  {String} name Event name
	 * @param  {Object} data Event data
	 * @return {Void}
	 */
	emitEvent = function( el, name, data ) {
		el.dispatchEvent(new CustomEvent(name, data));
	},

	/**
	 * Return a randomly generated ID
	 *
	 * @param  {String} domain Optional. Domain name to ensure unique values in the context.
	 * @return {Number} Generated ID
	 */
	generateId = function( domain ) {
		return hash(domain || "id").toString().concat(Date.now());
	},

	/**
	 * Return the hashed equivalent of any value
	 *
	 * @link https://github.com/puleos/object-hash
	 * @link https://stackoverflow.com/a/7616484
	 *
	 * @param  {Mixed} val Value to hash
	 * @return {Number} Value hash
	 */
	hash = function( val ) {
		return objectHash(val);
	},

	/**
	 * Return whether the element node exists in the DOM
	 *
	 * @param  {Object}  node Element data
	 * @return {Boolean} Does the element exist in the DOM?
	 */
	isDOMNode = function( node ) {

		// IE does not have contains method on document element, only body   
		var container = node.ownerDocument.contains ? node.ownerDocument : node.ownerDocument.body;

		return container.contains(node);
	},

	/**
	 * Return whether the element node is (in a) contenteditable
	 *
	 * @param  {Object}  node Element data
	 * @param  {Boolean} self Optional. Whether to only check the element node itself. Defaults to false.
	 * @return {Boolean} Is the element node cnotenteditable?
	 */
	isActiveContentEditable = function( node, self ) {
		while (node) {
			if (
				node.getAttribute &&
				node.getAttribute("contenteditable") &&
				node.getAttribute("contenteditable").toUpperCase() === "TRUE"
			) {
				return true;
			}

			node = (!! self) ? false : node.parentNode;
		}

		return false;
	},

	/**
	 * Return whether the node is an active input element
	 *
	 * @link https://github.com/slorber/backspace-disabler
	 *
	 * @param  {Object}  node Element data
	 * @return {Boolean} Is this an active input element?
	 */
	isActiveInputNode = function( node ) {
		var tagName = node.tagName.toUpperCase(),
		    isInput = (tagName === "INPUT" && node.type.toUpperCase() in validInputTypes),
		    isTextarea = (tagName === "TEXTAREA");

		if (isInput || isTextarea) {

			// The element may have been disconnected from the DOM between
			// the event happening and the end of the event chain, which is
			// another case that triggers history changes.
			return !(node.readOnly || node.disabled) && isDOMNode(node);
		} else if (isActiveContentEditable(node)) {
			return isDOMNode(node);
		} else {
			return false;
		}
	},

	/**
	 * Return the associated keyboard key/code
	 *
	 * @param  {String|Number|KeyboardEvent} key Key name or code or event object
	 * @param  {Boolean} reverse Optional. Whether to return key name by code
	 * @return {String|Number} String for key names, Number for key codes.
	 */
	keyboardMapper = function( key, reverse ) {
		var map = "", i;

		// Map from KeyboardEvent object
		if (key instanceof KeyboardEvent) {
			key = key.which || key.keyCode || 0;
			reverse = true;
		}

		// Find key name
		if (! reverse) {
			if ("undefined" !== typeof keyboardMap[key]) {
				map = keyboardMap[key];
			}

		// Find key code
		} else {
			for (i in keyboardMap) {
				if (keyboardMap[i] === key) {
					map = i;
				}
			}
		}

		return map;
	},

	/**
	 * Return the array variant of a value
	 *
	 * @param  {Mixed} value Any value
	 * @return {Array} Array variant of value
	 */
	makeArray = function( value ) {
		var arr, i;

		// Transform object
		if (_.isPlainObject(value)) {
			for (i in value) {
				if (value.hasOwnProperty(i)) {
					arr.push(value[i]);
				}
			}

		// Transform string
		} else if ("string" === typeof value) {
			arr = value.split(",");

		// No actual value
		} else if ("undefined" === typeof value) {
			arr = [];

		// Force array
		} else {
			arr = Array.isArray(value) ? value : [value];
		}

		return arr;
	},

	/**
	 * Return whether the search query is found in the test
	 *
	 * @param  {String} test  Input to test
	 * @param  {String} query Search terms
	 * @return {Boolean} Does the search term match?
	 */
	matchSearchQuery = function( test, query ) {
		var match = false, i;

		// Sanitize the query terms
		query = query.toString().trim();

		// Bail early when the query is empty
		if (! query.length) {
			return true;
		}

		// Sanitize test input
		test = removeAccents(test.toString().toLowerCase());

		// Get multiple terms, apply trim to each
		query = query.toLowerCase().split(" ").map(Function.prototype.call, String.prototype.trim);

		// Require match all
		for (i = 0; i < query.length; i++) {

			// When first matching or continue to match
			if (0 === i || match) {
				match = (-1 !== test.indexOf(query[i]));
			}
		}

		return match;
	},

	/**
	 * Return promise resolved or rejected based on boolean input
	 *
	 * @param {Mixed} input Input value, will be converted to boolean. True will resolve, false will reject.
	 * @param {Mixed} errorMessage Optional. Value for silent rejection. Defaults to silent error.
	 * @return {Promise} Resolved or rejected
	 */
	maybeReject = function( input, errorMessage ) {
		if (!! input) {
			return Q.resolve();
		} else {
			errorMessage = "undefined" !== typeof errorMessage ? errorMessage : false;
			return Q.reject({
				isError: true,
				message: errorMessage,
				data: {},
				silent: ! errorMessage
			});
		}
	},

	/**
	 * Format a number
	 *
	 * @param  {Number} value  Number to parse
	 * @param  {String} format Format to apply
	 * @param  {Object} options Optional. Additional formatting options
	 * @return {String} Formatted number
	 */
	numberFormat = function( value, format, options ) {
		var decSep = ",", thouSep = ".", decimalCount, formatted;

		format = format || "#.###,##";
		options = options || {};
		options.decSep = options.decSep || decSep;
		options.thouSep = options.thouSep || thouSep;

		// Dissect by decimal separator
		formatted = format.split(options.decSep);
		decimalCount = formatted.length > 1 ? (formatted[1].split("#").length - 1) : 0;

		// Parse the integer part. Replace the integers, apply thousand separator.
		formatted[0] = formatted[0].replace(
			"#" + options.thouSep + "###",
			Math[decimalCount ? "floor" : "round"](value).toString().replace(/\B(?=(\d{3})+(?!\d))/g, options.thouSep)
		);

		// Parse the decimal part. Replace the decimals, cut to correct size, round and pad zeros
		if (decimalCount) {
			formatted[1] = formatted[1].replace(
				"".padEnd(decimalCount, "#"),
				Math.round((value - Math.floor(value)) * Math.pow(10, decimalCount)).toString().padStart(decimalCount, "0")
			);
		}

		return formatted.join(options.decSep);
	},

	/**
	 * Register listeners for focussing outside of an element
	 *
	 * @link https://stackoverflow.com/a/38317768/3601434
	 *
	 * @param  {Element}  element  HTML element
	 * @param  {Function} callback Callback on event trigger
	 * @return {Function} Unregister callback
	 */
	onOuterFocus = function( element, callback ) {
		var timeout,

		/**
		 * Immediately trigger callback after timeout tick
		 *
		 * This prevents triggering the callback when we might
		 * effectively be still inside the element's container.
		 * The 'focusin' event triggers just after 'focusout'.
		 *
		 * @return {Void}
		 */
		callbackImmediate = function() {
			timeout = setTimeout(callback, 0);
		},

		/**
		 * Remove timeout
		 *
		 * @return {Void}
		 */
		undoTimer = function() {
			clearTimeout(timeout);
		};

		// Register timeout
		element.addEventListener("focusout", callbackImmediate);
		element.addEventListener("focusin", undoTimer);

		/**
		 * Deregister the registered listeners
		 *
		 * @return {Void}
		 */
		return function offOuterFocus() {
			element.removeEventListener("focusout", callbackImmediate);
			element.removeEventListener("focusin", undoTimer);
		};
	},

	/**
	 * Return a nested property in an object according to a path
	 *
	 * @param  {Object} obj Source object
	 * @param  {String|Array} path Path of property names. Dot-separated string or array.
	 * @return {Mixed} Result or `undefined` when not found
	 */
	path = function( obj, path ) {
		var i, next = obj;

		// Bail when the object is not traversable
		if (! obj || ! obj.hasOwnProperty) {
			return obj;
		}

		// Parse path from string
		path = ("string" === typeof path) ? path.split(".") : path;

		// Find each path step
		for (i in path) {
			if (next.hasOwnProperty(path[i])) {
				next = next[path[i]];
			} else {
				return undefined;
			}
		}

		return next;
	},

	/**
	 * Remove accents from a text
	 *
	 * NOTE: `String.prototype.normalize` is polyfilled for IE.
	 * 
	 * @link https://stackoverflow.com/a/37511463/3601434
	 *
	 * @param  {String} text Text to remove accents from
	 * @return {String} Modified text
	 */
	removeAccents = function( text ) {
		// return text.toString().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
		return _.deburr(text);
	},

	/**
	 * Return sanitization callback for the available items
	 *
	 * For each property sanitizer in `sanitizers` the context will be
	 * set to the original item that the sanitized property belongs to,
	 * so original property values can be found through `this.id` etc.
	 *
	 * @param  {Object} sanitizers Set of property sanitizer callbacks
	 * @param  {Function} getItem Callback for getting an item
	 * @return {Function} Item sanitization callback
	 */
	sanitization = function( sanitizers, getItem ) {
		/**
		 * Sanitize an item's data
		 *
		 * @param  {Object} data An item's data to sanitize
		 * @return {Object} Sanitized item
		 */
		return function sanitize( data ) {
			var prop, item, sanitized = {};

			// Get the current item
			item = ("function" === typeof getItem) && getItem(data);

			// Loop the provided data
			for (prop in data) {

				// Use available sanitizer function
				if (sanitizers.hasOwnProperty(prop) && "function" === typeof sanitizers[prop]) {
					sanitized[prop] = sanitizers[prop].call(item || copy(data), data[prop]);

				// Default to an unsanitized value
				} else {
					sanitized[prop] = data[prop];
				}
			}

			return sanitized;
		};
	},

	/**
	 * Sanitize input value representing a price
	 *
	 * @param  {String} val Input value
	 * @return {Float} Sanitized value
	 */
	sanitizePrice = function( val ) {
		val = ("string" === typeof val) ? parseFloat(val.replace(',', '.')) : val;
		return isNaN( val ) ? undefined : parseFloat(val.toFixed(2));
	},

	/**
	 * Re-add an element's classname to trigger animation styles
	 *
	 * @param  {Element} element HTML element
	 * @param  {String} className Optional. Class name to assign. Defaults to 'is-changed'.
	 * @return {Void}
	 */
	triggerElementChanged = function( element, className ) {
		if (element && element.classList) {
			className = className || "is-changed";

			/**
			 * Trigger animation on value change. To do this, force a reflow in between
			 * toggling the animation class.
			 * 
			 * @link https://css-tricks.com/restart-css-animation/
			 */
			element.classList.remove(className);
			void element.offsetWidth;
			element.classList.add(className);
		}
	},

	/**
	 * Return validation callback for the available items
	 *
	 * For each property validator in `validators` the `this` context will
	 * be set to the original item that the validated property belongs to,
	 * so original property values can be found through `this.id` etc.
	 *
	 * @param  {Object} validators Set of property validator callbacks
	 * @param  {Function} getItem Callback for getting an item
	 * @return {Function} Item sanitization callback
	 */
	validation = function( validators, getItem ) {
		/**
		 * Validate an item's data
		 *
		 * @param  {Object} data An item's data to validate
		 * @param  {Object} feedback Optional. A feedback data object to update
		 * @return {Array} Feedback list items
		 */
		return function validate( data, feedback ) {
			var prop, item, validated, feedbackId;

			// Default to a new feedback data object
			feedback = feedback || createFeedback();

			// Remove previously registered feedback
			feedback.clear();

			// Get the current item
			item = ("function" === typeof getItem) && getItem(data);

			// Loop validators
			for (prop in validators) {
				validated = false;
				feedbackId = "invalid-".concat(prop);

				// Remove any previous feedback
				feedback.remove(feedbackId);

				// Use available validator function
				if (validators.hasOwnProperty(prop) && "function" === typeof validators[prop]) {
					validated = validators[prop].call(item || copy(data), data[prop]);

				// Default to a generic validation
				} else {
					validated = "undefined" !== typeof data[prop];
				}

				// When value was invalidated
				if (true !== validated) {

					// Add feedback
					feedback.add(feedbackId, {
						isError: true,
						message: validated || "Generic.Error.InvalidInputValue",
						data: {
							field: prop,
							value: data[prop]
						}
					});
				}
			}

			return feedback.getList();
		};
	};

	/**
	 * Return a prefixed camelcased text
	 *
	 * @param  {String} prefix Custom prefix
	 * @param  {String} s Text to camelcase
	 * @return {String} Camelcased text
	 */
	camelCase.prepended = function( prefix, s ) {
		s = camelCase(s);
		return prefix.concat(s[0].toUpperCase(), s.substr(1));
	};

	return {
		camelCase: camelCase,
		copy: copy,
		emitEvent: emitEvent,
		generateId: generateId,
		hash: hash,
		clone: clone,
		createFeedback: createFeedback,
		createListeners: createListeners,
		isActiveInputNode: isActiveInputNode,
		keyboardMapper: keyboardMapper,
		makeArray: makeArray,
		matchSearchQuery: matchSearchQuery,
		maybeReject: maybeReject,
		numberFormat: numberFormat,
		onOuterFocus: onOuterFocus,
		path: path,
		removeAccents: removeAccents,
		sanitization: sanitization,
		sanitizePrice: sanitizePrice,
		triggerElementChanged: triggerElementChanged,
		validation: validation
	};
});
