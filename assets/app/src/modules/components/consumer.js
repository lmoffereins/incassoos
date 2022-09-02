/**
 * Single Consumer Component
 *
 * @package Incassoos
 * @subpackage App/Components
 */
define([
	"vuex",
	"lodash",
	"fsm",
	"services",
	"./feedback",
	"./form/input-price",
	"./form/input-toggle",
	"./util/close-button",
	"./../templates/consumer.html"
], function( Vuex, _, fsm, services, feedback, inputPrice, inputToggle, closeButton, tmpl ) {

	/**
	 * Holds a reference to the shortcuts service
	 *
	 * @type {Object}
	 */
	var shortcutsService = services.get("shortcuts"),

	/**
	 * Set form fields for the consumer context
	 *
	 * @return {Void}
	 */
	onEnterViewConsumer = function() {
		var payload = this.$store.state.consumers.active;

		this.name = payload.name;
		this.consumerShow = payload.show;
		this.spendingLimit = payload.spendingLimit;
	},

	/**
	 * Reset form fields when the consumer context is lost
	 *
	 * @return {Void}
	 */
	onEnterSettings = function() {
		this.name = "";
		this.consumerShow = true;
		this.spendingLimit = 0;
	},

	/**
	 * Provide watcher to patch the prop on the active item
	 *
	 * @param  {String} prop Item's property name to patch
	 * @return {Function} Patcher
	 */
	watchPatch = function( prop ) {
		return _.debounce(function( dispatch, value ) {

			// Only watch when editing
			if (! this.isViewing) {

				// Patch the active item
				dispatch("patch", { [prop]: value });
			}
		}, 300);
	};

	return {
		template: tmpl,
		components: {
			closeButton: closeButton,
			feedback: feedback,
			inputPrice: inputPrice,
			inputToggle: inputToggle
		},
		data: function() {
			return {

				// Form fields
				name: "",
				consumerShow: true,
				spendingLimit: 0,
			}
		},
		computed: Object.assign({
			/**
			 * Return whether this is the consumer viewing state
			 *
			 * @return {Boolean} Is the consumer being viewed?
			 */
			isViewing: function() {
				return this.$fsmIs(fsm.st.VIEW_CONSUMER);
			}
		}, Vuex.mapGetters("consumers", {
			"feedback": "getFeedback",
			"editable": "isEditable",
			"submittable": "isSubmittable"
		}), Vuex.mapState("consumers", {
			"active": "active",

			/**
			 * Return the active item's order count
			 *
			 * @return {Number} Order count
			 */
			orderCount: function( state, getters ) {
				return getters.getOrderCount();
			},

			/**
			 * Return the active item's total product quantity
			 *
			 * @return {Number} Total product quantity
			 */
			totalProductQuantity: function( state, getters ) {
				return getters.getTotalProductQuantity();
			},

			/**
			 * Return the active item's total consumed value
			 *
			 * @return {Number} Total consumed value
			 */
			totalConsumedValue: function( state, getters ) {
				return getters.getTotalConsumedValue();
			},

			/**
			 * Return whether the active item has been spending within their limit
			 *
			 * @return {Boolean} Has the consumer been spending within their limit?
			 */
			isWithinSpendingLimit: function( state, getters ) {
				return getters.isWithinSpendingLimit();
			}
		})),
		methods: Vuex.mapActions("consumers", {
			/**
			 * Edit the active item
			 *
			 * Dispatch the action without returning the promise.
			 *
			 * @param  {Function} dispatch Dispatch method
			 * @return {Void}
			 */
			edit: function( dispatch ) {
				dispatch("edit");
			},

			/**
			 * Save changes for the active item
			 *
			 * Dispatch the action without returning the promise.
			 *
			 * @param  {Function} dispatch Dispatch method
			 * @return {Void}
			 */
			update: function( dispatch ) {
				dispatch("update");
			},

			/**
			 * Cancel the current state
			 *
			 * Dispatch the action without returning the promise.
			 *
			 * @param  {Function} dispatch Dispatch method
			 * @return {Void}
			 */
			cancel: function( dispatch ) {
				dispatch("cancel");
			},

			/**
			 * Close the panel
			 *
			 * Dispatch the action without returning the promise.
			 *
			 * @param  {Function} dispatch Dispatch method
			 * @return {Void}
			 */
			close: function( dispatch ) {
				dispatch("close");
			}
		}),

		watch: Vuex.mapActions("consumers", {
			consumerShow:  watchPatch("show"),
			spendingLimit: watchPatch("spendingLimit")
		}),

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
			fsmObservers = {};
			fsmObservers[fsm.on.enter.VIEW_CONSUMER] = onEnterViewConsumer;
			fsmObservers[fsm.on.after.SELECT_CONSUMER] = onEnterViewConsumer;
			fsmObservers[fsm.on.enter.SETTINGS] = onEnterSettings;

			// Register observers, bind the component's context
			for (i in fsmObservers) {
				this.$registerUnobservable(
					fsm.observe(i, fsmObservers[i].bind(this))
				);
			}

			// Register global keyboard event listeners
			this.$registerUnobservable(
				shortcutsService.on({
					"escape": function consumerTransitionCancelOnEscape() {
						self.cancel();
					}
				})
			);
		},

		/**
		 * Register listeners when the component is mounted
		 *
		 * @return {Void}
		 */
		mounted: function() {

			// On initial creation, this observer is not triggered yet
			onEnterViewConsumer.call(this);
		}
	};
});
