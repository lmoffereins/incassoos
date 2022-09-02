/**
 * Login Component
 *
 * @package Incassoos
 * @subpackage App/Components
 */
define([
	"q",
	"fsm",
	"fsm/login",
	"services",
	"./administration",
	"./form/input-numpad",
	"./form/input-pin-entries",
	"./form/input-radio-buttons",
	"./util/close-button",
	"./../templates/login.html",
	"./../templates/login/templates"
], function( Q, mainFsm, fsm, services, administration, inputNumpad, inputPinEntries, inputRadioButtons, closeButton, tmpl, stepTemplates ) {
	/**
	 * Holds a reference to the authorization service
	 *
	 * This service is used for listing registered users and showing the current user.
	 *
	 * @type {Object}
	 */
	var authService = services.get("auth"),

	/**
	 * Holds a reference to the shortcuts service
	 *
	 * @type {Object}
	 */
	shortcutsService = services.get("shortcuts"),

	/**
	 * Watch the pin value and automatically attempt a transition
	 *
	 * @param  {String} value Pin value
	 * @return {Void}
	 */
	autoTransitionWithPin = function( value ) {
		var self = this;

		// When the required length is reached, try to auto-attempt a transition
		if (value.length === authService.requiredPinLength) {
			fsm.next().catch( function( error ) {

				// Failed to transition, clear form fields
				self.step.pin = "";

				return Q.reject(error);
			});
		}
	},

	/**
	 * Holds the watchers for specified login steps
	 *
	 * @type {Object}
	 */
	watchers = (function() {
		var watch = {};

		// Listen for changes on the pin form fields
		fsm.pinStates.forEach( function( i ) {
			watch[i] = { "step.pin": autoTransitionWithPin };
		});

		return watch;
	})(),

	/**
	 * Holds the enhanced set of login steps
	 *
	 * Defines step labels, step component definitions.
	 *
	 * @return {Array} Installation steps
	 */
	steps = fsm.steps.map( function( step ) {

		// Default step labels
		step.prevLabel = step.prevLabel || "Common.Previous";
		step.nextLabel = step.nextLabel || "Common.Next";

		// Define the step's on-the-fly component
		step.component = {
			props: {
				step: {
					type: Object,
					required: true
				},
				disabled: {
					type: Boolean,
					value: function() {
						return false;
					}
				}
			},
			template: stepTemplates[step.state],
			components: {
				administration: administration,
				inputNumpad: inputNumpad,
				inputPinEntries: inputPinEntries,
				inputRadioButtons: inputRadioButtons
			},
			computed: {
				/**
				 * Provide access to the login fsm within a component
				 *
				 * @return {Object} Login fsm
				 */
				fsm: function() {
					return fsm;
				}
			},
			methods: {
				/**
				 * In-component method for stepping through the login steps
				 *
				 * @return {Void}
				 */
				next: function() {
					fsm.next();
				},

				/**
				 * In-component method for switching between login steps
				 *
				 * @return {Void}
				 */
				toggle: function() {
					fsm.toggle();
				}
			},
			watch: watchers[step.state] || {}
		};

		return step;
	}),

	/**
	 * Update state value on state change
	 *
	 * @return {Void}
	 */
	onEnterState = function( lifecycle ) {

		// Set the current state
		this.fsmState = lifecycle.to;

		// Set the current installation step
		this.step = steps.find( function( i ) {
			return i.state === lifecycle.to;
		});

		// Set navigation handlers
		this.canCancel    = fsm.seek(fsm.tr.CANCEL) || fsm.is(fsm.st.IDLE);
		this.canPrev      = fsm.seek(fsm.prevSteps);
		this.canNext      = fsm.seek(fsm.nextSteps);
		this.showControls = ! fsm.is(fsm.pinStates);

		// For embedded logins, provide step details in a custom event
		this.$emit("enterState", {
			step:    this.step,
			canPrev: this.canPrev || false,
			canNext: this.canNext || false
		});
	};

	return {
		props: {
			isEmbedded: {
				type: Boolean,
				default: function() {
					return false;
				}
			},
			controls: {
				type: Boolean,
				default: function() {
					return ! fsm.is(fsm.pinStates);
				}
			},
			disabled: {
				type: Boolean,
				default: function() {
					return false;
				}
			}
		},
		data: function() {
			return {
				fsmState: fsm.state,
				step: steps.find( function( i ) {
					return i.state === fsm.state;
				}),
				updateComponent: 0,

				// Navigation
				canCancel: fsm.seek(fsm.tr.CANCEL) || fsm.is(fsm.st.IDLE),
				canPrev: fsm.seek(fsm.prevSteps),
				canNext: fsm.seek(fsm.nextSteps),
				showControls: this.controls
			};
		},
		template: tmpl,
		components: {
			closeButton: closeButton,
		},
		computed: {
			/**
			 * Return the class name for the current login state
			 *
			 * @return {String} Login state class name
			 */
			loginStateClass: function() {
				return "login-state-".concat(this.fsmState.toLowerCase());
			},

			/**
			 * Return whether the component inputs are disabled
			 *
			 * Use property when provided, otherwise default to requiring the LOGIN main state.
			 *
			 * @return {Boolean} Is component disabled?
			 */
			isDisabled: function() {
				return this.$options.propsData.hasOwnProperty("disabled") ? this.disabled : ! this.$fsmIs(mainFsm.st.LOGIN);
			}
		},
		methods: {
			/**
			 * Navigate to the previous login step
			 *
			 * @return {Void}
			 */
			prev: function() {
				fsm.prev();
			},

			/**
			 * Navigate to the next login step
			 *
			 * @return {Void}
			 */
			next: function() {
				fsm.next();
			},

			/**
			 * Transition away from the current login step
			 *
			 * @return {Void}
			 */
			cancel: function() {
				this.step.close && this.step.close() || fsm.cancel();
			}
		},

		/**
		 * Register listeners when the component is created
		 *
		 * @return {Void}
		 */
		created: function() {
			var self = this, i,

			/**
			 * Collection of fsm observers
			 *
			 * @type {Object}
			 */
			fsmObservers = {
				onEnterState: onEnterState
			};

			// Register observers, bind the component's context
			for (i in fsmObservers) {
				this.$registerUnobservable(
					fsm.observe(i, fsmObservers[i].bind(this))
				);
			}

			// Force update the component when anything changed in the users set
			this.$registerUnobservable(
				authService.on("users", function() {
					self.updateComponent++;
				})
			);

			// Register global keyboard event listeners
			this.$registerUnobservable(
				shortcutsService.on({
					"escape": function loginTransitionCancelOnEscape() {
						self.cancel();
					}
				})
			);
		}
	};
});
