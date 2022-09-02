/**
 * Login State Machine
 *
 * @package Incassoos
 * @subpackage App/FSM
 */
define([
	"q",
	"lodash",
	"api",
	"fsm",
	"services",
	"settings",
	"util",
	"./machine"
], function( Q, _, api, mainFsm, services, settings, util, StateMachine ) {
	/**
	 * Holds a reference to the authorization service
	 *
	 * @type {Object}
	 */
	var authService = services.get("auth"),

	/**
	 * Holds a reference to the delay service
	 *
	 * @type {Object}
	 */
	delayService = services.get("delay"),

	/**
	 * Holds a reference to the dialog service
	 *
	 * @type {Object}
	 */
	dialogService = services.get("dialog"),

	/**
	 * Holds a reference to the feedback service
	 *
	 * @type {Object}
	 */
	feedbackService = services.get("feedback"),

	/**
	 * Holds a reference to the installation service
	 *
	 * @type {Object}
	 */
	installService = services.get("install"),

	/**
	 * Holds a reference to the shortcuts service
	 *
	 * @type {Object}
	 */
	shortcutsService = services.get("shortcuts"),

	/**
	 * Define listener construct for the login fsm
	 *
	 * Events triggered in this domain are:
	 *  - login-attempts (loginAttemptsAvailable)
	 *
	 * @type {Object}
	 */
	listeners = util.createListeners("fsm/login"),

	/**
	 * Holds the artificial duration in milliseconds for processing pin actions
	 *
	 * @type {Number}
	 */
	pinDuration = 350,

	/**
	 * Holds the available state names
	 *
	 * @type {Object}
	 */
	STATES = {
		IDLE: "IDLE",
		NEW_LOGIN: "NEW_LOGIN",
		PIN_LOGIN: "PIN_LOGIN",
		PIN_REGISTER: "PIN_REGISTER",
		PIN_REGISTER_CONFIRM: "PIN_REGISTER_CONFIRM",
		PIN_VERIFY: "PIN_VERIFY",
		INFORMATION: "INFORMATION"
	},

	/**
	 * Holds the available transition names
	 *
	 * @type {Object}
	 */
	STEPS = {
		INIT: "INIT",
		AUTHENTICATE: "AUTHENTICATE",
		PIN_REGISTER_FIRST: "FIRST_PIN",
		PIN_REGISTER_UNDO: "UNDO_PIN",
		PIN_REGISTER_SAVE: "SAVE_PIN",
		PIN_LOGIN: "USE_PIN_LOGIN",
		LOCK: "LOCK",
		LOGOUT: "LOGOUT",
		PIN_REGISTER: "PIN_REGISTER",
		PIN_RENEW: "PIN_RENEW",
		SWITCH_LOGIN: "SWITCH_LOGIN",
		REQUEST_VERIFY: "REQUEST_VERIFY",
		VERIFY_LOGIN: "VERIFY",
		INFORMATION_PREV: "PREV_INFO",
		INFORMATION_NEXT: "NEXT_INFO",
		CANCEL: "CANCEL_STATE"
	},

	/**
	 * Return transition destination when attempting to login
	 *
	 * @return {String} State name to transition to
	 */
	getLoginState = function() {
		var isLoggedIn = authService.isUserLoggedIn(),
		    userCount = authService.getUsers().length;

		if (installService.isLocal) {
			return authService.hasPin() ? STATES.PIN_LOGIN : STATES.IDLE;

		// Require new login when no other users are registered
		} else if ((! isLoggedIn && ! userCount) || (isLoggedIn && 1 === userCount)) {
			return STATES.NEW_LOGIN;
		} else {
			return STATES.PIN_LOGIN;
		}
	},

	/**
	 * Return transition destination depending on the defined destination
	 *
	 * @param {String} defaultState Optional. The default destination name. Defaults to `idle`.
	 * @return {Function} Destination handler
	 */
	getDynamicDestination = function( defaultState ) {
		defaultState = defaultState || STATES.IDLE;

		/**
		 * Return transition destination for the current state
		 *
		 * @return {String} State name to transition to
		 */
		return function() {
			var self = this, step = steps.find( function( step ) {
				return self.state === step.state;
			}),

			// Get destination state from step parameter
			destination = "function" === typeof step.destination ? step.destination() : step.destination;

			return destination || defaultState;
		};
	},

	/**
	 * Transitions for the state machine
	 *
	 * Transitions (from functions) are defined before the transition lifecycle is
	 * started. This means that any logic applied in lifecycle event listeners are
	 * always run _after_ the destination state is determined. Therefore there is
	 * no determining state based on changes made within lifecycle event listeners.
	 *
	 * @type {Array}
	 */
	transitions = [

		// Initialization scenario
		{ name: STEPS.INIT,               from: STATES.IDLE,                 to: getLoginState },

		// New login scenario
		{ name: STEPS.AUTHENTICATE,       from: STATES.NEW_LOGIN,            to: STATES.PIN_REGISTER },
		{ name: STEPS.PIN_REGISTER_FIRST, from: STATES.PIN_REGISTER,         to: STATES.PIN_REGISTER_CONFIRM },
		{ name: STEPS.PIN_REGISTER_UNDO,  from: STATES.PIN_REGISTER_CONFIRM, to: STATES.PIN_REGISTER },
		{ name: STEPS.PIN_REGISTER_SAVE,  from: STATES.PIN_REGISTER_CONFIRM, to: getDynamicDestination() },

		// Pin login scenario
		{ name: STEPS.PIN_LOGIN,          from: STATES.PIN_LOGIN,            to: STATES.IDLE },

		// Lock
		{ name: STEPS.LOCK,               from: STATES.IDLE,                 to: STATES.PIN_LOGIN },

		// Logout: force new login
		{ name: STEPS.LOGOUT,             from: STATES.IDLE,                 to: getLoginState },
		{ name: STEPS.LOGOUT,             from: STATES.PIN_LOGIN,            to: getLoginState },

		// Register/edit pin
		{ name: STEPS.PIN_REGISTER,       from: STATES.IDLE,                 to: STATES.PIN_REGISTER },
		{ name: STEPS.PIN_RENEW,          from: STATES.IDLE,                 to: STATES.PIN_VERIFY },

		// Toggle pin <> login
		{ name: STEPS.SWITCH_LOGIN,       from: STATES.NEW_LOGIN,            to: getLoginState },
		{ name: STEPS.SWITCH_LOGIN,       from: STATES.PIN_LOGIN,            to: STATES.NEW_LOGIN },

		// Verify login
		{ name: STEPS.REQUEST_VERIFY,     from: STATES.IDLE,                 to: STATES.PIN_VERIFY },
		{ name: STEPS.VERIFY_LOGIN,       from: STATES.PIN_VERIFY,           to: getDynamicDestination() },

		// Information scenario
		{ name: STEPS.INFORMATION_PREV,   from: STATES.INFORMATION,          to: STATES.INFORMATION },
		{ name: STEPS.INFORMATION_NEXT,   from: STATES.INFORMATION,          to: getDynamicDestination() },

		// Cancel
		// TODO: fix cancel in single-user vs multi-user and installation
		{
			name: STEPS.CANCEL,
			from: [
				STATES.NEW_LOGIN,
				// STATES.PIN_LOGIN,         // Not on sinle-user
				STATES.PIN_REGISTER,         // Not on multi-user
				STATES.PIN_REGISTER_CONFIRM, // Not on multi-user
				STATES.PIN_VERIFY,
				STATES.INFORMATION
			],
			to: STATES.IDLE
		}
	],

	/**
	 * The login step configurations
	 *
	 * @type {Array}
	 */
	steps = [{
		state: STATES.NEW_LOGIN,
		title: "Login.NewLoginTitle",
		prevLabel: "Common.Switch",
		nextLabel: "Common.Login",
		userid: "",
		password: "",
		isLoading: false,
		observers: [{
			/**
			 * When submitting the login form, try to authenticate with the
			 * provided credentials
			 *
			 * @return {Promise} API request result
			 */
			method: util.camelCase.prepended("onBefore", STEPS.AUTHENTICATE),
			callback: function() {
				var self = this;

				// Bail when no login attempts are left
				if (! fsm.loginAttemptsAvailable) {
					return rejectLoginAttempt();
				}

				// Indicate loading state
				this.isLoading = true;

				/**
				 * Make an API login request with the credentials
				 */
				return api.auth.login({
					username: this.userid,
					password: this.password
				}).catch( function( error ) {

					// Clear some form fields
					self.password = "";

					// Stop loading state
					self.isLoading = false;

					// Record the failed login attempt
					registerFailedLoginAttempt();

					return Q.reject(error);
				});
			}
		}, {
			/**
			 * When leaving the state, reset the form fields
			 *
			 * @return {Void}
			 */
			method: util.camelCase.prepended("onLeave", STATES.NEW_LOGIN),
			callback: function() {
				this.userid = "";
				this.password = "";
				this.isLoading = false;
			}
		}]
	}, {
		state: STATES.PIN_REGISTER,
		title: "Login.PinRegisterTitle",
		description: "Login.PinRegisterDescription",
		pin: "",
		observers: [{
			/**
			 * When submitting the first pin, check whether the pin was valid
			 *
			 * @return {Promise} Transition success
			 */
			method: util.camelCase.prepended("onBefore", STEPS.PIN_REGISTER_FIRST),
			callback: function() {
				var dfd = Q.defer();

				// Is a valiid pin provided?
				if (authService.isValidPin(this.pin)) {

					// Store the first pin registration
					fsm.pinRegistrationFirstPin = this.pin;

					// Continue
					dfd.resolve();
				} else {
					dfd.reject("Login.Error.InvalidPin");
				}

				return dfd.promise;
			}
		}, {
			/**
			 * When leaving the state, reset the form fields
			 *
			 * @return {Void}
			 */
			method: util.camelCase.prepended("onLeave", STATES.PIN_REGISTER),
			callback: function() {
				this.pin = "";
			}
		}]
	}, {
		state: STATES.PIN_REGISTER_CONFIRM,
		title: "Login.PinRegisterTitle",
		description: "Login.PinRegisterConfirmDescription",
		prevLabel: "Common.Undo",
		pin: "",
		isLoading: false,
		observers: [{
			/**
			 * When saving the pin, check whether the pin was saved
			 *
			 * @return {Promise} Transition success
			 */
			method: util.camelCase.prepended("onBefore", STEPS.PIN_REGISTER_SAVE),
			callback: function() {
				var self = this;

				// Do the provided pins match?
				if (this.pin.length && this.pin === fsm.pinRegistrationFirstPin) {
					var dfd = Q.defer();

					// Indicate loading state
					this.isLoading = true;

					// Save the pin. Require minimum duration
					Q.all([
						authService.savePin(self.pin),
						delayService(pinDuration)
					]).then(dfd.resolve).catch( function( error ) {

						// Stop loading state
						self.isLoading = false;

						// Notify error
						dfd.reject(["Login.Error.SavePinFailed", error]);
					});

					return dfd.promise.then( function() {
						feedbackService.add("Login.PinRegistered");

						/**
						 * Trigger event listeners for when the pin was registered
						 */
						listeners.trigger("registered-pin");

						// Close login when not stepping further
						if (! self.destination) {
							mainFsm.do(mainFsm.tr.CLOSE_LOGIN);
						}
					});
				} else {

					// Clear form fields
					this.pin = "";

					return Q.reject("Login.Error.PinDoesNotMatch");
				}
			}
		}, {
			/**
			 * When leaving the state, reset the form fields
			 *
			 * @return {Void}
			 */
			method: util.camelCase.prepended("onLeave", STATES.PIN_REGISTER_CONFIRM),
			callback: function() {
				this.pin = "";
				this.destination = "";
				this.isLoading = false;
				fsm.pinRegistrationFirstPin = "";
			}
		}]
	}, {
		state: STATES.PIN_LOGIN,
		title: "Login.PinLoginTitle",
		prevLabel: "Common.Switch",
		users: [],
		userid: "",
		pin: "",
		isLoading: false,
		observers: [{
			/**
			 * When entering the state, reset the form fields
			 *
			 * @return {Void}
			 */
			method: util.camelCase.prepended("onEnter", STATES.PIN_LOGIN),
			callback: function() {
				this.userid = authService.getActiveUser() || authService.getPrevActiveUser() || (this.users.length && this.users[0].id);
				this.pin = "";
				this.isLoading = false;
			}
		}, {
			/**
			 * When submitting the pin, check whether the user can be logged-in
			 *
			 * @return {Promise} Transition success
			 */
			method: util.camelCase.prepended("onBefore", STEPS.PIN_LOGIN),
			callback: function() {
				var dfd = Q.defer(), self = this;

				// Bail when no login attempts are left
				if (! fsm.loginAttemptsAvailable) {
					return rejectLoginAttempt();
				}

				// Bail when no user is selected
				if (! this.userid.length) {
					return Q.reject("Login.Error.NoUserSelected");
				}

				// Indicate loading state
				this.isLoading = true;

				// Match the pin. Require minimum duration
				delayService(pinDuration).then( function() {
					if (authService.matchPin(self.pin, self.userid)) {

						// Set the active user
						authService.setActiveUser(self.userid).then(dfd.resolve)

						// When not running locally, validate user in the background
						.then(installService.isLocal ? _.noop : api.auth.validate)

						// When the user does not validate
						.catch( function() {

							// Stop loading state
							self.isLoading = false;

							// Notify invalidated account
							feedbackService.add("Login.Error.InvalidAccount");

							// Lock the session immediately
							// TODO: check whether this is correct behavior
							fsm.do(STEPS.LOGOUT);
						});
					} else {

						// Record the failed login attempt
						registerFailedLoginAttempt();

						// Clear form fields
						self.pin = "";

						// Stop loading state
						self.isLoading = false;

						dfd.reject("Login.Error.IncorrectPin");
					}
				});

				return dfd.promise.then( function() {

					// Close login when validated
					mainFsm.do(mainFsm.tr.CLOSE_LOGIN);
				});
			}
		}]
	}, {
		state: STATES.PIN_VERIFY,
		title: "Login.PinVerifyTitle",
		pin: "",
		destination: "",
		isLoading: false,
		observers: [{
			/**
			 * When entering the state, reset the form fields
			 *
			 * @return {Void}
			 */
			method: util.camelCase.prepended("onEnter", STATES.PIN_VERIFY),
			callback: function() {
				this.pin = "";
				this.isLoading = false;
			}
		}, {
			/**
			 * When the pin was renewed, decide on the step's destination
			 *
			 * @return {Void}
			 */
			method: util.camelCase.prepended("onAfter", STEPS.PIN_RENEW),
			callback: function() {

				// Set the destination after verification
				this.destination = STATES.PIN_REGISTER;

				// Get the pin registration confirmation step
				var step = steps.find( function( step ) {
					return STATES.PIN_REGISTER_CONFIRM === step.state;
				}),

				// Get the `information` step
				information = steps.find( function( step ) {
					return STATES.INFORMATION === step.state;
				});

				// Set the destination after registration confirmation
				step.destination = STATES.INFORMATION;

				// Set the payload after registration
				information.payload = function() {
					return [{
						title: "Login.PinRenewed",
						description: "Login.PinRenewedDescription",
					},{
						title: "Second info page",
						description: "Another description",
					},{
						title: "Third info page",
						description: "You may go now",
						nextLabel: "Common.Close"
					}];
				};
			}
		}, {
			/**
			 * When requesting user verification, set the step's payload
			 *
			 * @param {Function} payload Callback to run after verification
			 * @return {Void}
			 */
			method: util.camelCase.prepended("onAfter", STEPS.REQUEST_VERIFY),
			callback: function( lifecycle, payload ) {
				this.payload = payload;
			}
		}, {
			/**
			 * When submitting the pin, check whether it is correct for the current user
			 *
			 * @return {Promise} Does the pin match?
			 */
			method: util.camelCase.prepended("onBefore", STEPS.VERIFY_LOGIN),
			callback: function() {
				var dfd = Q.defer(), self = this;

				// Indicate loading state
				this.isLoading = true;

				// Require minimum duration
				delayService(pinDuration).then( function() {
					if (authService.matchPin(self.pin)) {
						dfd.resolve();
					} else {

						// Clear form field
						self.pin = "";

						// Stop loading state
						self.isLoading = false;

						// Block transition
						dfd.reject("Login.Error.PinDoesNotMatch");
					}
				});

				return dfd.promise;
			}
		}, {
			/**
			 * When leaving the state, reset the destination
			 *
			 * @return {Void}
			 */
			method: util.camelCase.prepended("onLeave", STATES.PIN_VERIFY),
			callback: function() {
				this.destination = "";
			}
		}, {
			/**
			 * When the pin was verified, activate payload callback
			 *
			 * @return {Void}
			 */
			method: util.camelCase.prepended("onAfter", STEPS.VERIFY_LOGIN),
			callback: function() {

				// Run payload when available
				if ("function" === typeof this.payload) {
					this.payload();
				}

				// Clear payload
				this.payload = undefined;
			}
		}]
	}, {
		state: STATES.IDLE,
		title: ["Login.Idle", ""],

		/**
		 * Close the login mode
		 *
		 * @return {Void}
		 */
		close: function() {
			mainFsm.do(mainFsm.tr.CLOSE_LOGIN);
		},

		/**
		 * Return whether the active user can lock the application
		 *
		 * @return {Boolean} Can the user lock?
		 */
		canLock: function() {
			return authService.hasPin();
		},

		/**
		 * Start the `LOCK` transition
		 *
		 * @return {Void}
		 */
		lock: function() {
			fsm.do(STEPS.LOCK);
		},

		/**
		 * Return whether the active user can logout
		 *
		 * @return {Boolean} Can the user logout?
		 */
		canLogout: function() {
			return fsm.can(STEPS.LOGOUT);
		},

		/**
		 * Start the `LOGOUT` transition
		 *
		 * @return {Void}
		 */
		logout: function() {
			fsm.do(STEPS.LOGOUT);
		},

		/**
		 * Return whether the user can register a pin
		 *
		 * @return {Boolean} Can the user register a pin?
		 */
		canRegisterPin: function() {
			return fsm.can(STEPS.PIN_REGISTER);
		},

		/**
		 * Start the `PIN_REGISTER` transition
		 *
		 * @return {Void}
		 */
		registerPin: function() {
			fsm.do(STEPS.PIN_REGISTER);
		},

		/**
		 * Return whether the user can remove their pin
		 *
		 * @return {Boolean} Can the user remove their pin?
		 */
		canRemovePin: function() {
			return authService.hasPin() && authService.isSingleUser();
		},

		/**
		 * Request confirmation before removing the user's pin
		 *
		 * @return {Void}
		 */
		removePin: function() {
			dialogService.confirm({
				id: "login-remove-pin",
				content: "Login.AreYouSureRemovePin",

				/**
				 * Remove the user's pin when confirmed
				 *
				 * @return {Void}
				 */
				onConfirm: function() {
					authService.removePin()
						.then( function() {
							feedbackService.add("Login.PinRemoved");
						}).catch( function( error ) {
							feedbackService.add(["Login.PinRemovedFailed", error]);
						});
				}
			});
		},

		/**
		 * Return whether the user can renew their pin
		 *
		 * @return {Boolean} Can the user renew their pin?
		 */
		canRenewPin: function() {
			return fsm.can(STEPS.PIN_RENEW);
		},

		/**
		 * Start the `RENEW-PIN` transition
		 *
		 * @return {Void}
		 */
		renewPin: function() {
			fsm.do(STEPS.PIN_RENEW);
		}
	}, {
		state: STATES.INFORMATION,
		title: "",
		description: "",
		iterator: 0,
		observers: [{
			/**
			 * When entering the state, setup the context
			 *
			 * @return {Void}
			 */
			method: util.camelCase.prepended("onEnter", STATES.INFORMATION),
			callback: function() {
				this._setupContext();
			}
		}, {
			/**
			 * When iterating the state, setup the context
			 *
			 * @return {Void}
			 */
			method: util.camelCase.prepended("onBefore", STEPS.INFORMATION_PREV),
			callback: function() {
				if (this.iterator) {
					this.iterator--;
					this._setupContext();
				}
			}
		}, {
			/**
			 * When iterating the state, setup the context
			 *
			 * @return {Void}
			 */
			method: util.camelCase.prepended("onBefore", STEPS.INFORMATION_NEXT),
			callback: function() {
				if (this.iterator + 1 < this.steps.length) {
					this.iterator++;
					this._setupContext();
				}
			}
		}, {
			/**
			 * When leaving the state, reset the state fields
			 *
			 * @return {Void}
			 */
			method: util.camelCase.prepended("onLeave", STATES.INFORMATION),
			callback: function() {
				var context = {}, i;

				// Run payload when available
				if ("function" === typeof this.payload) {
					context = this.payload();
				}

				// Reset payload fields
				for (i in context) {
					if (context.hasOwnProperty(i) && "state" !== i) {
						this[i] = undefined;
					}
				}

				// Clear the payload
				this.payload = undefined;
			}
		}],

		/**
		 * Start the `cancel` transition
		 *
		 * @return {Void}
		 */
		cancel: function() {
			fsm.do(STEPS.CANCEL);
		},

		/**
		 * Setup the state's context parameters
		 *
		 * @return {Void}
		 */
		_setupContext: function() {
			var context = {}, i;

			// Run payload when available
			if ("function" === typeof this.payload) {
				context = this.payload();
			}

			// Consider multi-step information
			if (Array.isArray(context)) {
				this.steps = context;
				context = this.steps[this.iterator];

				// When steps are available, direct the next step
				this.destination = (this.iterator + 1 < this.steps.length) && STATES.INFORMATION;
			}

			// Apply the step's parameters
			for (i in context) {
				if (context.hasOwnProperty(i) && -1 === ["state", "steps", "iterator", "cancel"].indexOf(i)) {
					this[i] = context[i];
				}
			}
		}
	}],

	/**
	 * Return the requirements for state transitions
	 *
	 * @return {Object} List of requirement methods
	 */
	setupRequirements = function() {
		var requirements = {};

		/**
		 * Enable pin registration when the active user has no pin
		 */
		requirements[STEPS.PIN_REGISTER] = function() {
			return ! authService.hasPin();
		};

		/**
		 * Disable pin renewal when the active user has no pin
		 */
		requirements[STEPS.PIN_RENEW] = function() {
			return authService.hasPin();
		};

		/**
		 * Disable locking when the active user has no pin
		 */
		requirements[STEPS.LOCK] = function() {
			return authService.hasPin();
		};

		return requirements;
	},

	/**
	 * Disable any next step when not in one of the main states
	 *
	 * @return {Boolean} Transition success
	 */
	onBeforeTransition = function() {
		return util.maybeReject(mainFsm.is([
			mainFsm.st.VOID,
			mainFsm.st.INIT,
			mainFsm.st.INSTALLATION,
			mainFsm.st.LOGIN
		]));
	},

	/**
	 * When initiating, unset the active user when using a pin
	 *
	 * @return {Promise} Transition success
	 */
	onAfterInit = function() {

		// Unset the active user when using a pin
		if (authService.hasPin()) {
			return authService.unsetActiveUser();
		}
	},

	/**
	 * When locking, unset the active user
	 *
	 * @return {Promise} Transition success
	 */
	onAfterLock = function() {

		// Unset the active user
		return authService.unsetActiveUser();
	},

	/**
	 * When logging out, logout the active user
	 *
	 * @return {Promise} API request result
	 */
	onBeforeLogout = function() {

		// When running locally
		if (installService.isLocal) {

			// Navigate to the site's logout page
			window.location = incassoosL10n.auth.logoutUrl;

			return delayService(5000).then( function() {
				return util.maybeReject();
			});
		} else {
			return api.auth.logout();
		}
	},

	/**
	 * When cancelling, do not allow when the application is locked
	 *
	 * @return {Boolean} Transition success
	 */
	onBeforeCancel = function( lifecycle ) {
		var allowTransition = true;

		// Block transition silently when there is no active user
		if (-1 !== [STATES.NEW_LOGIN, STATES.PIN_LOGIN].indexOf(lifecycle.from)) {
			allowTransition = util.maybeReject(authService.isUserLoggedIn());
		}

		return allowTransition;
	},

	/**
	 * Holds the login lock timer
	 *
	 * @type {Timeout}
	 */
	loginLockTimer = null,

	/**
	 * Define the login's state machine
	 *
	 * @type {StateMachine}
	 */
	fsm = new StateMachine({
		name: "login",
		init: STATES.IDLE,
		transitions: transitions,
		methods: {
			// onBeforeTransition: onBeforeTransition,
			onAfterInit: onAfterInit,
			onAfterLock: onAfterLock,
			onBeforeLogout: onBeforeLogout,
			onBeforeCancel: onBeforeCancel
		},
		data: {
			/**
			 * Holds the first pin registration
			 *
			 * @type {String}
			 */
			pinRegistrationFirstPin: "",

			/**
			 * Holds the remaining available login attempts
			 *
			 * Will be set to `settings.login.loginAttemptsAllowed` on services.init.
			 *
			 * @type {Number}
			 */
			loginAttemptsAvailable: 0,

			/**
			 * Holds the transition names that are considered 'previous' steps
			 *
			 * @type {Array}
			 */
			prevSteps: [
				STEPS.PIN_REGISTER_UNDO,
				STEPS.SWITCH_LOGIN,
				STEPS.INFORMATION_PREV
			],

			/**
			 * Holds the transition names that are considered 'next' steps
			 *
			 * @type {Array}
			 */
			nextSteps: [
				STEPS.AUTHENTICATE,
				STEPS.PIN_REGISTER_FIRST,
				STEPS.PIN_REGISTER_SAVE,
				STEPS.PIN_LOGIN,
				STEPS.VERIFY_LOGIN,
				STEPS.INFORMATION_NEXT
			],

			/**
			 * Holds the state names that contain pin interaction
			 *
			 * @type {Array}
			 */
			pinStates: [
				STATES.PIN_LOGIN,
				STATES.PIN_REGISTER,
				STATES.PIN_REGISTER_CONFIRM,
				STATES.PIN_VERIFY
			],

			/**
			 * Handler for transitioning to the previous state
			 *
			 * @return {Promise} Transition success
			 */
			prev: function() {
				return fsm.do(fsm.prevSteps);
			},

			/**
			 * Handler for transitioning to the next state
			 *
			 * @return {Promise} Transition success
			 */
			next: function() {
				return fsm.do(fsm.nextSteps);
			},

			/**
			 * Reset form fields on all steps
			 *
			 * @return {Void}
			 */
			resetFormFields: function() {
				this.steps.forEach( function( step ) {
					["userid", "password", "pin"].forEach( function( field ) {
						if (step.hasOwnProperty(field)) {
							step[field] = "";
						}
					});
				});
			},

			on: listeners.on,
			off: listeners.off,
			st: STATES,
			tr: STEPS,
			steps: steps,
			requirements: setupRequirements(),

			/**
			 * Handler for switching between login states
			 *
			 * @return {Promise} Transition success
			 */
			toggle: function() {
				return fsm.do(fsm.tr.SWITCH_LOGIN);
			},

			/**
			 * Handler for canceling states
			 *
			 * @return {Promise} Transition success
			 */
			cancel: function() {
				return fsm.do(fsm.tr.CANCEL);
			},

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
	 * Record a failed login attempt
	 *
	 * @return {Void}
	 */
	registerFailedLoginAttempt = function() {

		// Bail when no login attempts are left
		if (! fsm.loginAttemptsAvailable) {
			return false;
		}

		// Decrease the remaining login attempts
		fsm.loginAttemptsAvailable--;

		// Trigger login lock when no login attempts remain
		if (! fsm.loginAttemptsAvailable) {

			// Reset the remaining login attempts
			delayService(1000 * settings.login.loginAttemptsTimeout).then(resetLoginAttempts);

			// Register login lock start time
			fsm.loginAttemptsTimer = new Date().getTime();
		}
	},

	/**
	 * Reset the value for the available login attempts
	 *
	 * @return {Void}
	 */
	resetLoginAttempts = function() {
		fsm.loginAttemptsAvailable = settings.login.loginAttemptsAllowed;
	},

	/**
	 * Return a rejection when login attempts are not available
	 *
	 * @return {Promise} Rejected login attempt
	 */
	rejectLoginAttempt = function() {
		var remainingTimeInSecs = settings.login.loginAttemptsTimeout - Math.ceil((new Date().getTime() - fsm.loginAttemptsTimer) / 1000),
		    showInSeconds = 2 === Math.max(remainingTimeInSecs / 60, 2);

		return Q.reject({
			isError: true,
			message: showInSeconds ? "Login.Error.LockedInSeconds" : "Login.Error.LockedInMinutes",
			data: {
				args: Math.ceil(remainingTimeInSecs / (showInSeconds ? 1 : 60))
			}
		});
	},

	/**
	 * Return a step's transition observer method
	 *
	 * @param  {Object} step The step
	 * @param  {Function} callback The observer callback
	 * @return {Function} Step observer method
	 */
	stepObserver = function( step, callback ) {
		/**
		 * Process the step transition observer with the step's context
		 *
		 * @return {Mixed} Transition result
		 */
		return function( lifecycle ) {
			var args = Array.prototype.slice.call(arguments);

			if (-1 !== [lifecycle.from, lifecycle.to].indexOf(step.state)) {
				return callback.apply(step, args);
			}
		};
	};

	/**
	 * Register the step state transition observers
	 */
	steps.forEach( function( step ) {
		(step.observers || []).forEach( function( observer ) {
			fsm.observe(observer.method, stepObserver(step, observer.callback));
		});
	});

	/**
	 * When the settings are updated, update machine properties.
	 */
	settings.$onUpdate( function() {

		// Renew remaining login attempts
		fsm.loginAttemptsAvailable = settings.login.loginAttemptsAllowed;
	});

	/**
	 * Act on events from the authorization service
	 */
	authService.on({

		// When a login was successfull
		active: function() {

			// Reset login attempts
			resetLoginAttempts();

			// Get the `idle` step
			var step = steps.find( function( step ) {
				return STATES.IDLE === step.state;
			});

			// Update `idle` step's title parameters
			step.title = ["Login.Idle", authService.getActiveUser()];

			// Clear the login feedback
			feedbackService.clear();
		},

		// When no user is active
		inactive: function() {

			// Go back to LOGIN
			mainFsm.do(mainFsm.tr.OPEN_LOGIN);
		},

		// When the users list is changed
		users: function( users ) {

			// Collect users in object
			var userList = users.reduce( function( list, user ) {
				list[user.id] = user.userName;
				return list;
			}, {});

			// Update steps that utilize users
			_.filter(steps, "users").forEach( function( step ) {
				step.users = userList;
			});

			// Switch to new-login when no users are selectable
			if (fsm.is(STATES.PIN_LOGIN) && ! fsm.busy && ! users.length) {
				fsm.do(STEPS.SWITCH_LOGIN);
			}
		}
	});

	/**
	 * Act on events from the idle service
	 */
	services.get("idle").on({

		// When the application is considered idle
		idle: function() {

			/**
			 * Auto-lock the application
			 *
			 * Although the `lock` transition only effectively executes once,
			 * on subsequent calls its Promise still resolves on every call.
			 * To ensure the feedback message only shows once, let's check when
			 * the lock will actually execute.
			 *
			 * Check whether the application should be locked on idle:
			 *  - when the LOCK transition is available
			 *  - when the current receipt is empty, so when not in the RECEIPT main state
			 */
			if (fsm.can(STEPS.LOCK) && ! mainFsm.is(mainFsm.st.RECEIPT)) {

				// Lock the application
				fsm.do(STEPS.LOCK).then( function() {

					// Let them know why
					feedbackService.add({
						message: "Login.IdleTimeLock",
						autoRemove: false
					});
				});
			}
		}
	});

	/**
	 * Register keyboard shortcuts
	 *
	 * By using state transitions on shortcuts, actual interaction will only materialize
	 * when the machine's current state allows for the transition. This makes it possible
	 * to have identical shortcuts in different state machines and machine states.
	 */
	shortcutsService.on({

		// Lock the application
		"ctrl+L": {
			label: "Login.LockApplication",
			callback: function loginFsmLockApplicationOnCtrlL( e ) {

				// Browsers may default to focussing the address bar
				e.preventDefault();

				// Lock the application
				fsm.do(STEPS.LOCK);
			}
		},

		// Switch login mode
		"alt+S": {
			label: "Login.SwitchLoginMode",
			keyUp: true, // Prevents continued switching when holding the keys
			callback: function loginFsmSwitchLoginModeOnAltS() {
				fsm.do(STEPS.SWITCH_LOGIN);
			}
		},

		// Cancel the login state
		"escape": function loginFsmTransitionCancelOnEscape() {
			fsm.do(STEPS.CANCEL);
		}
	});

	/**
	 * When the application gets initialized, initiate the login mode
	 */
	mainFsm.observe(
		mainFsm.on.leave.INIT,
		function( lifecycle ) {

			// Collect users in object
			var userList = authService.getUsers().reduce( function( list, user ) {
				list[user.id] = user.userName;
				return list;
			}, {});

			// Update steps that utilize users
			_.filter(steps, "users").forEach( function( step ) {
				step.users = userList;
			});

			// Initialize login
			fsm.do(STEPS.INIT);
		}
	);

	/**
	 * Block transitions of the main fsm early when the application is locked
	 *
	 * @return {Boolean} Transition success
	 */
	mainFsm.observe(
		"onBeforeTransition",
		function( lifecycle ) {

			// Only check transitions beyond the initial state(s)
			if (-1 === [mainFsm.initialState, mainFsm.st.INIT].indexOf(lifecycle.from)) {

				// Require the user to be logged-in, apart from entering the login state
				if (! (authService.isUserLoggedIn() || lifecycle.transition === mainFsm.tr.OPEN_LOGIN)) {
					return Q.reject("Login.Error.RequiresAuthentication");
				}
			}
		}
	);

	return fsm;
});
