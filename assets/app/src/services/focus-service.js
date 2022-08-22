/**
 * Focus Service Functions
 *
 * Relies on the Document object.
 * 
 * @package Incassoos
 * @subpackage App/Services
 */
define([
	"q",
	"util",
	"./visibility-service"
], function( Q, util, visibilityService ) {
	/**
	 * Define listener construct for the service
	 *
	 * Events triggered in this domain are:
	 *  - focus
	 *  - blur
	 *
	 * @type {Object}
	 */
	var listeners = util.createListeners("service/focus"),

	/**
	 * Holds the focus status
	 *
	 * @type {Boolean}
	 */
	focus = document ? document.hasFocus() : true,

	/**
	 * Holds the interval for pinging the focus status
	 *
	 * @type {Number}
	 */
	interval = null,

	/**
	 * Initialization of the focus service
	 *
	 * @param {Object} Vue The Vue instance
	 * @return {Promise} Is the service initialized?
	 */
	init = function( Vue ) {

		// Require the document object
		if (document && document.hasFocus) {

			// Initiate interval ping, because there is no `focuschange` event
			interval = setInterval(set, 300);

			// Consider a hidden page as blurred
			visibilityService.on("hidden", function() {
				set();

				// Pause the interval ping
				clearInterval(interval);
				interval = null;
			});

			// Consider a visible page as focussed
			visibilityService.on("visible", function() {
				set();

				// Restart the interval ping
				interval || (interval = setInterval(set, 300));
			});
		}

		/**
		 * Reactive listener for the focus status
		 *
		 * @return {Boolean} Is the application focussed?
		 */
		Object.defineProperty(Vue.prototype, "$focus", {
			get: function() {
				return this.$store.state.documentFocus;
			}
		});

		return Q.resolve();
	},

	/**
	 * Definition of the service's store logic
	 *
	 * @return {Object} Service store methods
	 */
	storeDefinition = function() {
		return {
			/**
			 * Modify service related properties in the main store's state
			 *
			 * @param  {Object} state Store state
			 * @return {Void}
			 */
			defineStoreState: function( state ) {
				state.documentFocus = focus;
			},

			/**
			 * Modify service related methods in the main store's mutations
			 *
			 * @param  {Object} mutations Store mutations
			 * @return {Void}
			 */
			defineStoreMutations: function( mutations ) {
				/**
				 * Update reactive property for focusService's `status` property
				 *
				 * @return {Void}
				 */
				mutations.focusSetState = function( state ) {
					state.documentFocus = focus;
				};
			},

			/**
			 * Trigger service related methods in the main store's context
			 *
			 * @param  {Object} context Store context
			 * @return {Void}
			 */
			defineStoreContextUsage: function( context ) {
				/**
				 * When changing the focus status, update the main store's `status` data
				 *
				 * @return {Function} Deregistration method
				 */
				listeners.on(["focus", "blur"], function() {

					// Mutate the reactive `focus` data
					context.commit("focusSetState");
				});
			}
		};
	},

	/**
	 * Set the active focus mode
	 *
	 * @return {Void}
	 */
	set = function() {

		// Bail when nothing changed
		if (focus === document.hasFocus()) {
			return;
		}

		focus = ! focus;

		/**
		 * Trigger event listeners for when the application changed focus
		 */
		listeners.trigger(focus ? "focus" : "blur");
	};

	return {
		init: init,

		/**
		 * Return whether the application is focussed
		 *
		 * @return {Boolean} Is the application focussed?
		 */
		isFocus: function() {
			return focus;
		},

		/**
		 * Return whether the application is not focussed
		 *
		 * @return {Boolean} Is the application not focussed?
		 */
		isBlur: function() {
			return ! focus;
		},
		on: listeners.on,
		off: listeners.off,
		storeDefinition: storeDefinition
	};
});
