/**
 * Wrapper for StateMachine class
 *
 * @package Incassoos
 * @subpackage App/FSM
 */
define([
	"q",
	"StateMachine",
	"lodash",
	"services",
	"util"
], function( Q, StateMachine, _, services, util ) {
	/**
	 * Holds a reference to the debug service
	 * 
	 * @type {Object}
	 */
	var debugService = services.get("debug"),

	/**
	 * Default transition error handler
	 *
	 * Logs error to the console.
	 *
	 * @param {Object} error Error data
	 * @param {String} transition Transition name
	 * @return {Void}
	 */
	onTransitionError = function( error, transition ) {
		if (debugService.isDebugmode()) {
			console.error("fsm > " + this.name + " [" + transition + ":" + this.state + "->error]", error);
		}
	};

	/**
	 * Create and return an enhanced StateMachine
	 *
	 * @param {String} name Machine name
	 * @param {Object} options Constructor options
	 * @return {Object} StateMachine
	 */
	return function machine( name, options ) {
		options = options || name;

		/**
		 * Define default options data
		 *
		 * @type {Object}
		 */
		options.data = _.defaults(options.data || {}, {
			name: ("string" === typeof name) ? name : options.name || util.generateId("fsm"),
			onTransitionError: onTransitionError,
			requirements: {}
		});

		/**
		 * Create a new state machine
		 *
		 * @type {StateMachine}
		 */
		var machine = new StateMachine(options);

		/**
		 * Define `busy` property as an alias for `machine._fsm.isPending()`
		 *
		 * Returns whether the machine is busy/pending processing current transitions.
		 *
		 * @return {Boolean} Is the machine transitioning?
		 */
		Object.defineProperty(machine, "busy", {
			get: function() {
				return machine._fsm.isPending();
			}
		});

		/**
		 * Run transition when possible. Accepts an array of transitions of which to
		 * run only the first possible one.
		 *
		 * Returns whether any transition was started and whether it was successful.
		 *
		 * @param {String|Array} transition Transition name(s)
		 * @param {Object} options Optional. Transition options.
		 * @return {Promise} Transition success
		 */
		machine.do = function( transition, options ) {
			var self = this,

			// Keep the name of the current state
			from = self.state,

			// Collect any additional arguments
			payload = Array.prototype.slice.call(arguments, 1);

			// Ensure `transition` is an array
			Array.isArray(transition) || (transition = [transition]);

			// Parse options defaults
			options = _.defaults(options || {}, {
				onTransitionError: machine.onTransitionError
			});

			// Get the first possible transition
			transition = _.first(transition.filter( function( tr ) {
				return machine.can(tr);
			}));

			// Run the first possible transition
			return Q.Promisify(
				transition && machine[util.camelCase(transition)].apply(machine, payload)

			// Run custom events after the transition ended
			).then( function( result ) {

				// Did not run the transition when it is empty
				if (! transition) {
					return result;
				}

				// Natively trigger observers for generic onDone and onDone<Transition> events
				return self._fsm.observeEvents([
					self._fsm.observersForEvent("onDone"),
					self._fsm.observersForEvent(util.camelCase.prepended("onDone", transition))
				], [{
					transition: transition,
					from: from,
					to: self.state,
					fsm: self._fsm.context
				}, ...payload]);

			}).catch( function( error ) {

				// Apply custom error handling
				if ("function" === typeof options.onTransitionError) {
					return options.onTransitionError.apply(machine, [error, transition]);
				}

				// Continue rejection to signal that the transition did not run
				return Q.reject(error);
			});
		};

		/**
		 * Returns whether requirements are met for the transition
		 *
		 * Requirement definitions should be defined as synchronous functions
		 * in the `requirements` machine option.
		 *
		 * @param  {String} transition Transition name
		 * @return {Boolean} Are transition requirements met?
		 */
		machine.meet = function( transition ) {
			return "function" === typeof machine.requirements[transition] ? machine.requirements[transition]() : true;
		};

		/**
		 * Modification of `machine.can()` that accepts an array of transitions
		 * and checks for transition requirements.
		 * 
		 * Returns whether any of the transitions is possible.
		 *
		 * @param {String|Array} transition Transition name(s)
		 * @return {Boolean} Is any transition possible?
		 */
		machine.can = function( transition ) {

			// Refer to internal class for using canonical `_fsm.can()`
			var _fsm = this._fsm;

			// Ensure `transition` is an array
			Array.isArray(transition) || (transition = [transition]);

			// For each transition, check if it can be run
			return transition.reduce( function( retval, tr ) {

				// Only check when none of the previous transitions is possible
				return retval || (_fsm.can(tr) && machine.meet(tr));
			}, false);
		};

		/**
 		 * Exposed version of `machine._fsm.seek()` that accepts an array of transitions
		 * 
		 * Returns whether any of the transitions is available for the current state.
		 *
		 * This allows for checking optional transitions, while the current transition is
		 * still pending. Use this only when the current transition has time to resolve.
		 *
		 * @param {String|Array} transition Transition name(s)
		 * @return {Boolean} Is any transition available?
		 */
		machine.seek = function( transition ) {

			// Refer to internal class for using canonical `_fsm.seek()`
			var _fsm = this._fsm;

			// Ensure `transition` is an array
			Array.isArray(transition) || (transition = [transition]);

			// For each transition, check if it can be run
			return transition.reduce( function( retval, tr ) {

				// Only check when none of the previous transitions is available
				return retval || _fsm.seek(tr);
			}, false);
		};

		/**
		 * Modification of `machine.observe()` that supports unobserving
		 *
		 * Returns an unobserver function for the observer callback.
		 *
		 * @param {String|Array} event Event name or names
		 * @param {Function} callback Event listener
		 * @return {Function} Deregistration callback
		 */
		machine.observe = function( event, callback ) {

			// Refer to internal class for using internal `_fsm.observers`
			var _fsm = this._fsm, observer = {};

			// Accept direct object-style listeners
			if ("function" !== typeof callback) {
				observer = event;

			// Accept list of event names
			} else if (Array.isArray(event)) {
				event.forEach( function( i ) {
					observer[i.toString()] = callback;
				});
			} else {
				observer[event] = callback;
			}

			// Add observer to the list of event listeners
			_fsm.observers.push(observer);

			/**
			 * Deregister the registered listener
			 *
			 * @return {Void}
			 */
			return function unobserve() {

				// Remove observer from the list of event listeners
				_.pull(_fsm.observers, observer);
			};
		};

		// Register debug logging
		var logEvents = {
			"onBeforeTransition": "before",
			"onEnterState": "enter",
			"onLeaveState": "leave",
			"onAfterTransition": "after"
		};

		// Monitor events
		machine.observe(_.keys(logEvents), function( lifecycle ) {
			if (debugService.isDebugmode()) {
				console.log("fsm > " + lifecycle.fsm.name + "/" + logEvents[lifecycle.event] + " [" + lifecycle.transition + ":" + lifecycle.from + "->" + lifecycle.to + "]");
			}
		});

		return machine;
	};
});
