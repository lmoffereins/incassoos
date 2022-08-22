/**
 * Final State Machine
 *
 * @package Incassoos
 * @subpackage App/FSM
 */
define([
	"api",
	"services",
	"util",
	"./machine",
	"./lifecycle",
	"./xstate.json"
], function( api, services, util, StateMachine, lifecycler, xstate ) {
	/**
	 * Holds a reference to the feedback service
	 * 
	 * @type {Object}
	 */
	var feedbackService = services.get("feedback"),

	/**
	 * Get lifecycle data from the xstate JSON structure
	 * 
	 * @type {Object}
	 */
	lifecycle = lifecycler(xstate),

	/**
	 * Define listener construct for the main fsm
	 *
	 * Events triggered in this domain are:
	 *  - TRANSITION/to (destination, step)
	 * 
	 * @type {Object}
	 */
	listeners = util.createListeners("fsm/main"),

	/**
	 * Holds the name of the additional transition to open the LOGIN state from any other state
	 *
	 * @type {String}
	 */
	openLoginTransition = "OPEN_LOGIN";

	/**
	 * Add available transition for opening the LOGIN state from any other state
	 */
	lifecycle.steps.push({
		name: openLoginTransition,
		from: "*",
		to: lifecycle.states.LOGIN
	});

	/**
	 * Add additional transition to base list of transitions
	 */
	lifecycle.transitions[openLoginTransition] = openLoginTransition;

	/**
	 * Enable filtering the transition destination
	 */
	lifecycle.steps.forEach( function( step ) {
		var _step = util.clone(step);

		/**
		 * Return the filtered transition destination
		 *
		 * Defaults to the original destination name.
		 *
		 * @return {String} Transition destination
		 */
		step.to = function() {
			/**
			 * Trigger event listeners when the transition's destination is determined
			 *
			 * The dynamic part of the event name is the transition's name.
			 *
			 * @param {String} to Original transition destination
			 * @param {Object} step Original transition
			 * @return {String} Transition destination
			 */
			return listeners.filter(step.name.concat("/to"), _step.to, _step) || _step.to;
		};
	});

	/**
	 * Define the application's final state machine (FSM)
	 *
	 * This contains all handlers and checks for states and transitions between
	 * states. Refer to the `./xstate.json` file for the actual machine structure.
	 *
	 * @type {StateMachine}
	 */
	var fsm = new StateMachine({
		name: "main",
		init: lifecycle.init,
		transitions: lifecycle.steps,
		data: {
			/**
			 * Define shortcut references to state/transition lifecylce event names
			 *
			 * @return {Object} List of event names per event type
			 */
			on: (function() {
				var i, before = {}, leave = {}, enter = {}, after = {};

				// Create dynamic on<STATE> lifecycle names
				for (i in lifecycle.states) {
					leave[i] = util.camelCase.prepended("onLeave", lifecycle.states[i]);
					enter[i] = util.camelCase.prepended("onEnter", lifecycle.states[i]);
				}

				// Create dynamic on<TRANSITION> lifecycle names
				for (i in lifecycle.transitions) {
					before[i] = util.camelCase.prepended("onBefore", lifecycle.transitions[i]);
					after[i]  = util.camelCase.prepended("onAfter", lifecycle.transitions[i]);
				}

				return {
					before: before, // Before starting the transition
					leave: leave,   // When leaving the current state
					enter: enter,   // When entering the new state
					after: after    // After the transition is done
				};
			})(),

			/**
			 * Provide filter option for event listeners
			 *
			 * @type {Function}
			 */
			filter: listeners.on,

			/**
			 * Provide initial state at the root object
			 *
			 * @type {String}
			 */
			initialState: lifecycle.init,

			/**
			 * Provide lists of states at the root object
			 *
			 * @type {Object}
			 */
			st: lifecycle.states,

			/**
			 * Provide lists of transitions at the root object
			 *
			 * @type {Object}
			 */
			tr: lifecycle.transitions,

			/**
			 * Register transition errors in the global feedback
			 *
			 * @param  {String|Object} error Error message or data
			 * @return {Void}
			 */
			onTransitionError: function( error, transition ) {

				// Ignore intended errors
				if (true !== error) {
					console.error("fsm > " + this.name + " [" + transition + ":" + this.state + "->error]", error);
				}

				// Publish global feedback
				feedbackService.add(api.getErrorItem(error));

				// Run post error callback
				if (error && error.onAfterError) {
					error.onAfterError();
				}
			}
		}
	});

	window.mainFsm = fsm;

	return fsm;
});
