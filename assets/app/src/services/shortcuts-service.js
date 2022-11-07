/**
 * Shortcuts Service Functions
 *
 * @package Incassoos
 * @subpackage App/Services
 */
define([
	"q",
	"lodash",
	"util",
	"./dialog-service"
], function( Q, _, util, dialogService ) {
	/**
	 * Holds pressed keys and their current press status
	 *
	 * @type {Object}
	 */
	var pressedKeys = {},

	/**
	 * List of listeners for the global `keyup` event
	 *
	 * @type {Object}
	 */
	keyUpListeners = {},

	/**
	 * List of listeners for the global `keydown` event
	 *
	 * @type {Object}
	 */
	keyDownListeners = {},

	/**
	 * Initialization of the shortcuts service
	 *
	 * @return {Promise} Is the service initialized?
	 */
	init = function() {

		/**
		 * Register global event listeners
		 */
		window.addEventListener("keyup", keyup);
		window.addEventListener("keydown", keydown);

		// Register generic shortcuts
		registerShortcuts({

			// Open help window for shortcuts
			"shift+alt+H": {
				label: "Administration.OpenShortcutsHelp",
				callback: function shortcutsServiceOpenShortcutsOnShiftAltH() {
					showShortcutsDialog();
				}
			}
		});

		return Q.resolve();
	},

	/**
	 * Return whether the given keys are pressed
	 *
	 * @param  {Array|String}  keys  List of key names
	 * @param  {Boolean}  exact Optional. Whether the keys require an exact match
	 * @return {Boolean} Are the keys pressed?
	 */
	isPressing = function( keys, exact ) {
		var pressed = false;

		// Parse keys into array
		Array.isArray(keys) || (keys = keys.split("+"));

		// Look for each key
		pressed = keys.reduce( function( match, i ) {
			return match && true === pressedKeys[i.toLowerCase()];
		}, true);

		// Check for an exact match
		if (pressed && true === exact) {
			pressed = keys.length !== pressedKeys.length;
		}

		return pressed;
	},

	/**
	 * Call the listener functions iteratively
	 *
	 * @param  {Array} listeners List of listeners
	 * @param  {Object} event Event data object
	 * @return {Void}
	 */
	callListeners = function( listeners, event ) {
		var i;

		// Iterate backwards
		for (i = listeners.length - 1; 0 <= i; i--) {
			if ("function" === typeof listeners[i]) {
				listeners[i](event);

			// Only run when the context is right
			} else if (listeners[i].context()) {
				listeners[i].callback(event);

				// Stop iteration when not propagating
				if (! listeners[i].propagate) {
					break;
				}
			}
		}
	},

	/**
	 * Return whether to ignore the event for the given keys
	 *
	 * @param  {Object} event Event data
	 * @param  {String} keys      Shortcut key combination
	 * @return {Boolean} Ignore this event?
	 */
	ignoreEvent = function( event, keys ){
		keys = keys || "";

		// Is the shortcut simple?
		var simpleKeys = 1 === keys.length,

		// Is an input node used?
		isActiveInputNode = util.isActiveInputNode(event.srcElement || event.target);

		// Ignore only on input nodes for single and simple key listeners
		return simpleKeys && isActiveInputNode;
	},

	/**
	 * Support keyboard input by handling keyup events
	 *
	 * @param  {KeyboardEvent} event Event data
	 * @return {Void}
	 */
	keyup = function shortcutsServiceOnKeyup( event ) {
		var key = event.which || event.keyCode || 0, i;

		// Handle pressed keys while they're still registered as pressed
		for (i in keyUpListeners) {
			if (isPressing(i, keyUpListeners[i].exact) && ! ignoreEvent(event, i)) {
				callListeners(keyUpListeners[i], event);
			}
		}

		// Deregister pressed key
		delete pressedKeys[util.keyboardMapper(key, true).toLowerCase()];
	},

	/**
	 * Support keyboard input by handling keydown events
	 *
	 * @param  {KeyboardEvent} event Event data
	 * @return {Void}
	 */
	keydown = function shortcutsServiceOnKeydown( event ) {
		var key = event.which || event.keyCode || 0, i;

		// Register pressed key
		pressedKeys[util.keyboardMapper(key, true).toLowerCase()] = true;

		// Handle pressed keys
		for (i in keyDownListeners) {
			if (isPressing(i, keyDownListeners[i].exact) && ! ignoreEvent(event, i)) {
				callListeners(keyDownListeners[i], event);
			}
		}
	},

	/**
	 * Register a key event listener
	 *
	 * @param  {String} keys Key combination
	 * @param  {Object|Function} options Options object or event callback.
	 * @return {Function} Deregister function
	 */
	register = function( keys, options ) {
		options = options || {};

		// Only the callback was provided
		if ("function" === typeof options) {
			options = { callback: options };
		}

		// Set `exact` property. Require exact match when combining keys.
		if ("undefined" === typeof options.exact) {
			options.exact = -1 !== keys.indexOf("+");
		}

		// Set `propagate` property. Shortcut events do not propagate by default.
		if ("undefined" === typeof options.propagate) {
			options.propagate = false;
		}

		// Set `context` property. Default context is based on state.
		if ("undefined" === typeof options.context) {
			options.context = function() {
				// TODO: implement state-based context for shortcuts?
				// if (this.state) {
				// 	return fsm.is(this.state);
				// }

				return true;
			};
		}

		// Shortcut may be undocumented
		if ("undefined" === typeof options.label) {
			options.label = false;
		}

		// Register keyup event
		if (options.keyUp) {
			keyUpListeners[keys] = keyUpListeners[keys] || [];
			keyUpListeners[keys].push(options);

		// Default to keydown event
		} else {
			keyDownListeners[keys] = keyDownListeners[keys] || [];
			keyDownListeners[keys].push(options);
		}

		/**
		 * Deregister the key event listener
		 *
		 * @return {Void}
		 */
		return function deregister() {
			_.pull(options.keyUp ? keyUpListeners[keys] : keyDownListeners[keys], options);
		};
	},

	/**
	 * Process key event listener registration
	 *
	 * @param {Object|String} keys List of key combinations or singular key combination
	 * @param {Object|Function} options Optional. Singular options object or event callback.
	 * @return {Function} Deregister function
	 */
	registerShortcuts = function( keys, options ) {
		var i, deregisterers = {};

		// Support multiple key combinations
		if ("string" !== typeof keys) {
			for (i in keys) {
				deregisterers[i] = register(i, keys[i]);
			}

		// Single key combination
		} else {
			deregisterers[keys] = register(keys, options);
		}

		/**
		 * Deregister the event listeners
		 *
		 * @return {Void}
		 */
		return function deregisterShortcuts() {
			for (var i in deregisterers) {
				deregisterers[i]();
			}
		};
	},

	/**
	 * Return the raw list of shortcuts
	 *
	 * @return {Object} Registered shortcuts
	 */
	getAllShortcuts = function() {
		return {
			keyUpListeners: keyUpListeners,
			keyDownListeners: keyDownListeners
		};
	},

	/**
	 * Return a single shortcut's display data
	 *
	 * Helper function to `getShortcuts()`.
	 *
	 * @param  {String} i Shortcut key combination
	 * @return {Function} Shortcut parser
	 */
	getShortcut = function( i ) {
		/**
		 * Return a single shortcut's display data
		 *
		 * @param  {Object} j Shortcut
		 * @return {Object} Shortcut display data
		 */
		return function( j ) {
			return {
				shortcut: i,
				label: j.label
			};
		};
	},

	/**
	 * Return the registered shortcuts for display
	 *
	 * @return {Array} Shortcut display data
	 */
	getShortcuts = function() {
		var shortcuts = [], i;

		for (i in keyUpListeners) {
			Array.prototype.push.apply(shortcuts, _.filter(keyUpListeners[i], "label").map(getShortcut(i)));
		}

		for (i in keyDownListeners) {
			Array.prototype.push.apply(shortcuts, _.filter(keyDownListeners[i], "label").map(getShortcut(i)));
		}

		return shortcuts;
	},

	/**
	 * Open the shortcuts dialog
	 *
	 * @param  {Object} options Optional. Dialog options
	 * @return {Void}
	 */
	showShortcutsDialog = function( options ) {
		dialogService.open(_.defaults({
			id: "help-shortcuts",
			type: "help/shortcuts"
		}, options || {}));
	};

	return {
		getAllShortcuts: getAllShortcuts,
		getShortcuts: getShortcuts,
		init: init,
		on: registerShortcuts,
		showShortcutsDialog: showShortcutsDialog
	};
});
