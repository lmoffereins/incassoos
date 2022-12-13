/**
 * Panels Component
 *
 * @package Incassoos
 * @subpackage App/Components
 */
define([
	"lodash",
	"fsm",
	"services",
	"util",
	"./consumer",
	"./installation",
	"./login",
	"./occasions",
	"./product",
	"./../templates/panels.html"
], function( _, fsm, services, util, consumer, installation, login, occasions, product, tmpl ) {
	/**
	 * Holds a reference to the delay service
	 *
	 * @type {Object}
	 */
	var delayService = services.get("delay"),

	/**
	 * Holds the list of panels
	 *
	 * @type {Object}
	 */
	panels = {
		installation: {
			activeState: [
				fsm.st.INSTALLATION
			],
			component: installation,
		},
		login: {
			activeState: [
				fsm.st.LOGIN
			],
			component: login
		},
		occasions: {
			activeState: [
				fsm.st.OCCASIONS,
				fsm.st.VIEW_OCCASION,
				fsm.st.EDIT_OCCASION,
				fsm.st.DELETE_OCCASION
			],
			component: occasions
		},
		product: {
			activeState: [
				fsm.st.VIEW_PRODUCT,
				fsm.st.EDIT_PRODUCT,
				fsm.st.CREATE_PRODUCT,
				fsm.st.DELETE_PRODUCT
			],
			withContext: true,
			component: product
		},
		consumer: {
			activeState: [
				fsm.st.VIEW_CONSUMER,
				fsm.st.EDIT_CONSUMER
			],
			withContext: true,
			component: consumer
		}
	},

	/**
	 * Update state data on state change
	 *
	 * @return {Void}
	 */
	onEnterState = function( lifecycle ) {
		var self = this;

		// Set the active panel
		this.panel = _.values(panels).find( function( i ) {
			return self.$fsmIs(i.activeState);
		});
	};

	return {
		template: tmpl,
		data: function() {
			return {
				panel: null
			};
		},
		computed: {
			/**
			 * Return whether any panel is active
			 *
			 * @return {Boolean} Is any panel active?
			 */
			isActive: function() {
				return !! this.panel;
			},

			/**
			 * Return whether the panel context is relevant
			 *
			 * @return {Boolean} Show panel with context?
			 */
			withContext: function() {
				return this.panel && !! this.panel.withContext;
			}
		},
		methods: {
			/**
			 * Act on the panel when it is mounted
			 *
			 * @return {Void}
			 */
			onPanelMounted: function() {
				var self = this;

				this.$refs.wrapper.classList.remove("is-mounted");

				// Delayed signal in the classname that the panel is mounted
				delayService(100).then( function() {
					self.$refs.wrapper.classList.add("is-mounted");
				});
			}
		},
		watch: {
			/**
			 * Act when the active panel is changed
			 *
			 * @return {Void}
			 */
			panel: function() {

				// Communicate whether a panel is active
				this.$root.$emit("panels/is-panel-active", !! this.panel);
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

			// Before component creation, the panel state may already have been loaded
			onEnterState.apply(this);
		}
	};
});
