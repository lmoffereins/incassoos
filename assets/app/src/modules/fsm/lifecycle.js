/**
 * State Machine Lifecycle
 *
 * @package Incassoos
 * @subpackage App/FSM
 */
define([], function() {
	/**
	 * Return the lifecycle data of an xstate JSON structure
	 *
	 * @param  {String} xstate Xstate JSON structure
	 * @return {Object} Lifecycle data
	 */
	return function lifecycler( xstate ) {
		var steps = [], states = {}, transitions = {}, from, to, transition;

		// Walk each state
		for (from in xstate.states) {

			// Walk each state's transitions
			for (transition in xstate.states[from].on) {

				// Get state transition's target state
				to = xstate.states[from].on[transition];

				// Setup step data for this transition
				steps.push({
					name: transition,
					from: from,
					to: to
				});

				/**
				 * Collect states and transitions
				 *
				 * State and transition names are used by the StateMachine/FSM for
				 * constructing additional structures. Here we collect the names to
				 * expose them for further use throughout the application.
				 */
				states[from] = from;
				states[to] = to;
				transitions[transition] = transition;
			}
		}

		return {
			init: states[xstate.initial],
			states: states,
			steps: steps,
			transitions: transitions
		};
	};
});
