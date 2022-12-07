/**
 * Dialog Service Functions
 * 
 * @package Incassoos
 * @subpackage App/Services
 */
define([
	"q",
	"lodash",
	"util",
	"./debug-service"
], function( Q, _, util, debugService ) {
	/**
	 * Define listener construct for the service
	 *
	 * Events triggered in this domain are:
	 *  - add (options)
	 *  - remove (id)
	 *  - show (id)
	 *
	 * @type {Object}
	 */
	var listeners = util.createListeners("service/dialog", {
		log: function( context, domain, args ) {
			debugService.isDebugmode() && console.log(context.concat(" > ", domain, ":", args[0]));
		}
	}),

	/**
	 * List of identifiers of registered dialog
	 *
	 * @type {Array}
	 */
	dialogs = [],

	/**
	 * Register a dialog
	 *
	 * @param  {Object} options Optional. Dialog options.
	 * @return {Function} Dialog show method.
	 */
	dialog = function( options ) {
		options = options || {};

		// Create dialog identifier
		options.id = options.id || util.generateId("dialog");

		if (-1 === dialogs.indexOf(options.id)) {
			dialogs.push(options.id);

			/**
			 * Trigger event listeners for when a dialog is added.
			 *
			 * @param {Object} options Dialog options
			 */
			listeners.trigger("add", options);

			/**
			 * Show the dialog
			 *
			 * @return {String} Dialog identifier
			 */
			return function() {
				show(options.id);
				return options.id;
			};
		} else {
			return _.noop;
		}
	},

	/**
	 * Shortcut for showing a confirm dialog
	 *
	 * @param  {Object|Function} options Dialog options or confirm callback.
	 * @return {String} Dialog identifier
	 */
	confirm = function( options ) {
		options = options || {};

		// Accept confirm callback
		if ("function" === typeof options) {
			options = {
				onConfirm: options
			};
		}

		// Set the dialog type
		options.type = "confirm";

		return dialog(options)();
	},

	/**
	 * Remove a dialog
	 *
	 * @param  {String} id Dialog identifier
	 * @return {Void}
	 */
	remove = function( id ) {

		// Bail when dialog is not registered
		if (-1 === dialogs.indexOf(id)) {
			return;
		}

		// Remove registered dialog
		dialogs = dialogs.filter( function( i ) {
			return i !== id;
		});

		/**
		 * Trigger event listeners for when a dialog is removed.
		 *
		 * @param {String} id Dialog identifier
		 */
		listeners.trigger("remove", id);
	},

	/**
	 * Show a dialog
	 *
	 * @param  {String} id Dialog identifier
	 * @return {Void}
	 */
	show = function( id ) {

		// Bail when dialog is not registered
		if (-1 === dialogs.indexOf(id)) {
			return;
		}

		/**
		 * Trigger event listeners for when a dialog is shown.
		 *
		 * @param {String} id Dialog identifier
		 */
		listeners.trigger("show", id);
	},

	/**
	 * Create and show a dialog
	 *
	 * @param  {Object|String} optionsOrId Dialog options or dialog id
	 * @return {Void}
	 */
	open = function( optionsOrId ) {
		"string" === typeof optionsOrId ? show(optionsOrId) : dialog(optionsOrId)();
	};

	return {
		confirm: confirm,
		dialog: dialog,
		on: listeners.on,
		off: listeners.off,
		open: open,
		remove: remove
	};
});
