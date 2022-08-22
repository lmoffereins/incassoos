/**
 * Installation Component
 *
 * @package Incassoos
 * @subpackage App/Components
 */
define([
	"fsm",
	"fsm/install",
	"./feedback",
	"./language-switcher",
	"./login",
	"./../templates/installation.html",
	"./../templates/installation/templates"
], function( mainFsm, fsm, feedback, languageSwitcher, login, tmpl, stepTemplates ) {
	/**
	 * Holds the enhanced set of installation steps
	 *
	 * Defines step labels, step component definitions.
	 *
	 * @return {Array} Installation steps
	 */
	var steps = fsm.steps.map( function( step ) {

		// Default step labels
		step.prevLabel = step.prevLabel || "Common.Previous";
		step.nextLabel = step.nextLabel || "Common.Next";

		// Embedded fsm support
		step.embedded = {};

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
						return ! this.$fsmIs(mainFsm.st.INSTALLATION);
					}
				}
			},
			template: stepTemplates[step.state],
			components: {
				languageSwitcher: languageSwitcher,
				login: login
			},
			methods: {
				/**
				 * In-component method for stepping through the installation steps
				 *
				 * @return {Void}
				 */
				next: function() {
					next();
				},

				/**
				 * In-component method for resetting the installation steps
				 *
				 * @return {Void}
				 */
				reset: function() {
					fsm.do("reset");
				},

				/**
				 * When using embedded state machines, emit step details in a custom event
				 *
				 * @param  {Object} data Event data
				 * @return {Void}
				 */
				onEmbeddedEnterState: function( data ) {
					this.$emit("embeddedEnterState", data);
				}
			}
		};

		return step;
	}),

	/**
	 * Handler for transitioning to the next state
	 *
	 * @return {Void}
	 */
	next = function() {
		fsm.do("next");
	},

	/**
	 * Return the step's field computer, but maybe the field is embedded
	 *
	 * @param  {String} field Field name
	 * @return {Function} Field computer
	 */
	maybeEmbeddedField = function( field ) {
		/**
		 * Return the step's field, but maybe it is embedded
		 *
		 * @return {Mixed} Field value
		 */
		return function() {
			return "undefined" !== typeof this.step.embedded[field] ? this.step.embedded[field] : this.step[field] || this[field];
		};
	},

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
		this.seekPrev = fsm.seek("prev");
		this.seekNext = fsm.seek("next");
	};

	return {
		template: tmpl,
		components: {
			feedback: feedback
		},
		data: function() {
			return {
				fsmState: fsm.state,
				step: steps[0],

				// Navigation
				seekPrev: fsm.seek("prev"),
				seekNext: fsm.seek("next")
			};
		},
		computed: {
			title: maybeEmbeddedField("title"),
			description: maybeEmbeddedField("description"),
			canPrev: maybeEmbeddedField("seekPrev"),
			canNext: maybeEmbeddedField("seekNext"),
			prevLabel: maybeEmbeddedField("prevLabel"),
			nextLabel: maybeEmbeddedField("nextLabel"),
		},
		methods: {

			/**
			 * Navigate to the previous installation step
			 *
			 * @return {Void}
			 */
			prev: function() {
				fsm.do("prev");
			},

			/**
			 * Navigate to the next installation step
			 *
			 * @return {Void}
			 */
			next: function() {
				next();
			},

			/**
			 * When using embedded state machines, parse step details
			 *
			 * @param  {Object} data Event data
			 * @return {Void}
			 */
			onEmbeddedEnterState: function( data ) {
				this.step.embedded = {
					title: data.step.title,
					description: data.step.description,
					seekPrev: data.canPrev,
					seekNext: data.canNext,
					prevLabel: data.step.prevLabel,
					nextLabel: data.step.nextLabel
				};
			}
		},

		/**
		 * Register listeners when the component is created
		 *
		 * @return {Void}
		 */
		created: function() {
			var key,

			/**
			 * Collection of fsm observers
			 *
			 * @type {Object}
			 */
			fsmObservers = {
				onEnterState: onEnterState
			};

			// Register observers, bind the component's context
			for (key in fsmObservers) {
				this.$registerUnobservable(
					fsm.observe(key, fsmObservers[key].bind(this))
				);
			}
		}
	};
});
