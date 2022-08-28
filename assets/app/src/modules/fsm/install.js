/**
 * Install State Machine
 *
 * @package Incassoos
 * @subpackage App/FSM
 */
define([
	"q",
	"lodash",
	"fsm",
	"fsm/login",
	"api",
	"services",
	"util",
	"./machine"
], function( Q, _, mainFsm, loginFsm, api, services, util, StateMachine ) {
	/**
	 * Holds a reference to the localization service
	 *
	 * @type {Object}
	 */
	var l10nService = services.get("l10n"),

	/**
	 * Holds a reference to the feedback service
	 *
	 * @type {Object}
	 */
	feedbackService = services.get("feedback"),

	/**
	 * Define listener construct for the login fsm
	 *
	 * Events triggered in this domain are:
	 *  - ? (?)
	 *
	 * @type {Object}
	 */
	listeners = util.createListeners("fsm/installation"),

	/**
	 * Return transition destination for the previous step
	 *
	 * Transitions (from functions) are defined before the transition lifecycle is
	 * started. This means that any logic applied in lifecycle event listeners are
	 * always run _after_ the destination state is determined. Consequently there is
	 * no determining state based on changes made within lifecycle event listeners.
	 *
	 * @return {String} State name to transition to
	 */
	nextStep = function() {
		var states = _.map(steps, "state");

		// Get the following state per next index
		return states[states.indexOf(this.state) + 1];
	},

	/**
	 * Return transition destination for the next step
	 *
	 * Transitions (from functions) are defined before the transition lifecycle is
	 * started. This means that any logic applied in lifecycle event listeners are
	 * always run _after_ the destination state is determined. Consequently there is
	 * no determining state based on changes made within lifecycle event listeners.
	 *
	 * @return {String} State name to transition to
	 */
	prevStep = function() {
		var states = _.map(steps, "state");

		// Get the preceding state per pervious index
		return states[states.indexOf(this.state) - 1];
	},

	/**
	 * Holds the installation steps
	 *
	 * @type {Array}
	 */
	steps = [{
		state: "start",
		title: "Installation.Start",
		description: "Installation.StartDescription"
	}, {
		title: "Installation.Connect",
		description: "Installation.ConnectDescription",
		domain: "",
		namespace: "incassoos/v1",
		isSecure: true,
		domainPlaceholder: "my.incassoos.com",
		nextLabel: "Installation.ConnectNextLabel",

		/**
		 * Transition handler before the next step
		 *
		 * Handles verfiying the provided domain parameters. The API
		 * connects with the domain and fetches basic settings.
		 *
		 * @return {Promise} Transition success
		 */
		onBeforeNext: function() {
			var payload = {
				domain: this.domain,
				namespace: this.namespace,
				isSecure: this.isSecure
			};

			// Try to connect to the domain
			return api.connect(payload).catch( function( error ) {

				// Default the error message
				if ("string" !== typeof error) {
					error = "API.Error.IncorrectDomain";
				}

				return Q.reject(error);
			});
		}
	}, {
		title: "Installation.Login",
		description: "Installation.LoginDescription",
		nextLabel: "Installation.LoginNextLabel",
		loginStepsDfd: Q.defer(),

		/**
		 * When stepping back, check the login fsm's navigation options
		 *
		 * @return {Promise} Transition success
		 */
		onBeforePrev: function() {
			var promise;

			// Allow stepping back when the login fsm supports it
			if (loginFsm.can(loginFsm.tr.CANCEL)) {
				loginFsm.resetFormFields();
				promise = Q.resolve();

			// Step back within the login fsm, then block silently
			} else if (loginFsm.can(loginFsm.prevSteps)) {
				promise = loginFsm.do(loginFsm.prevSteps).then( function() {
					return util.maybeReject();
				});

			// Otherwise, block silently
			} else {
				promise = util.maybeReject();
			}

			return promise;
		},

		/**
		 * Transition handler before the next step
		 *
		 * Handles checking whether the login was successfull.
		 *
		 * @return {Promise} Transition success
		 */
		onBeforeNext: function() {
			var self = this;

			// Continue in the login steps instead. Ignore login's default transition error handler
			return loginFsm.do(loginFsm.nextSteps, { onTransitionError: false }).then( function() {

				// When the login is done, return its result
				if ("pending" !== self.loginStepsDfd.promise.inspect().state) {
					return self.loginStepsDfd.promise;

				// Otherwise block the transition
				} else {
					return util.maybeReject();
				}
			});
		},

		/**
		 * When leaving the state, reset the state
		 *
		 * @return {Void}
		 */
		onAfterNext: function() {
			this.loginStepsDfd = Q.defer();
		}
	}, {
		state: "finish",
		title: "Installation.Finish",
		description: "Installation.FinishDescription",

		/**
		 * Transition handler before resetting the steps
		 *
		 * @return {Void}
		 */
		onBeforeReset: function() {

			// Close the installation
			mainFsm.do(mainFsm.tr.CLOSE_INSTALLATION);
		}
	}].map( function( step, index ) {

		// Provide a state name for the in-between steps
		if ("undefined" === typeof step.state) {
			step.state = "step".concat(index);
		}

		return step;
	}),

	/**
	 * Transitions for the state machine
	 *
	 * @type {Array}
	 */
	transitions = [
		{ name: "next",  from: _.difference(_.map(steps, "state"), ["finish"]), to: nextStep },
		{ name: "prev",  from: _.difference(_.map(steps, "state"), ["start", "finish"]),  to: prevStep },
		{ name: "reset", from: "finish", to: "start" }
	],

	/**
	 * Only allow any next step when in the installation main state
	 *
	 * @return {Boolean} Is the transaction allowed?
	 */
	onBeforeNext = function() {
		return util.maybeReject(mainFsm.is(mainFsm.st.INSTALLATION));
	},

	/**
	 * Remove feedback messages when entering a new state
	 *
	 * @return {Void}
	 */
	onEnterState = function() {
		feedbackService.clear();
	},

	/**
	 * Define the login's state machine
	 *
	 * @type {StateMachine}
	 */
	fsm = new StateMachine({
		name: "install",
		init: "start",
		transitions: transitions,
		methods: {
			onBeforeNext: onBeforeNext,
			onEnterState: onEnterState
		},
		data: {
			on: listeners.on,
			off: listeners.off,
			steps: steps,

			/**
			 * Register transition errors in the global feedback
			 *
			 * @param  {String|Object} error Error message or data
			 * @return {Void}
			 */
			onTransitionError: function( error ) {

				// Ignore intended silent errors
				if (error && error.silent) {
					return;
				}

				feedbackService.add(api.getErrorItem(error));
			}
		}
	}),

	/**
	 * Return a step's transition observer method
	 *
	 * @param  {Object} step The step
	 * @param  {String} callbackName The callback's property name
	 * @return {Function} Step observer method
	 */
	stepObserver = function( step, callbackName ) {
		/**
		 * Process the step's transition observer
		 *
		 * @return {Mixed} Transition result
		 */
		return function( lifecycle ) {
			var args = Array.prototype.slice.call(arguments);

			if (step.state === lifecycle.from) {
				return step[callbackName].apply(step, args);
			}
		};
	};

	/**
	 * Register state transition observers
	 */
	steps.forEach( function( step ) {
		var i;

		for (i in step) {
			if (step.hasOwnProperty(i) && "function" === typeof step[i]) {
				fsm.observe(i, stepObserver(step, i));
			}
		}
	});

	/**
	 * When the new user's pin is registered, signal end of login flow
	 */
	loginFsm.on(loginFsm.tr.PIN_REGISTER_SAVE, function() {
		var loginStep = steps.find( function( step ) {
			return step.hasOwnProperty("loginStepsDfd");
		});

		// Signal that the login's steps are done
		loginStep.loginStepsDfd.resolve();

		// Also, try to continue fsm when login is auto-applied
		fsm.do("next");
	});

	return fsm;
});
