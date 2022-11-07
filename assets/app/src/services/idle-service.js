/**
 * Idle Service Functions
 *
 * Signals whether the application is considered idle due to user inactivity.
 * 
 * @package Incassoos
 * @subpackage App/Services
 */
define([
	"q",
	"util"
], function( Q, util ) {
	/**
	 * Define listener construct for the service
	 *
	 * Events triggered in this domain are:
	 *  - idle
	 *
	 * @type {Object}
	 */
	var listeners = util.createListeners("service/idle"),

	/**
	 * Holds the idle state timer
	 *
	 * @type {Timeout}
	 */
	timer,

	/**
	 * Holds the default idle time in seconds
	 *
	 * @type {Number}
	 */
	idleTime = 120,

	/**
	 * Initialization of the idle service
	 *
	 * @return {Promise} Is the service initialized?
	 */
	init = function() {
		/**
		 * User input events.
		 *
		 * @see https://stackoverflow.com/a/24989958/3601434
		 *
		 * @type {Array}
		 */
		var events = [
			"load",
			"mousemove",
			"mousedown",
			"touchstart",
			"click",
			"keypress",
			"scroll"
		], i;

		// Setup listeners for user input events
		for (i = 0; i < events.length; i++) {
			window.addEventListener(events[i], resetTimer, true);
		}

		return Q.resolve();
	},

	/**
	 * Triger the `idle` event
	 *
	 * @return {Void}
	 */
	triggerIdleEvent = function() {
		/**
		 * Trigger event listeners when the application is considered idle.
		 */
		listeners.trigger("idle");
	},

	/**
	 * Reset the idle timer
	 *
	 * @return {Void}
	 */
	resetTimer = function idleServiceResetTimer() {
		clearTimeout(timer);
		timer = setTimeout(triggerIdleEvent, idleTime * 1000);
	};

	return {
		init: init,
		on: listeners.on,
		off: listeners.off
	};
});
