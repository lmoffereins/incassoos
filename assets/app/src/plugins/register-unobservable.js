/**
 * Vue Register-Unobserver plugin
 *
 * Unobservers are automatically called on component destruction. This
 * leaves unregistering of event handlers or other listeners to the
 * component, while not having to use hacks in order to make the unregistration
 * function available to the component's `beforeDestroy()` method.
 *
 * @author Laurens Offereins
 * @since 2019-05-16
 */
define([], function() {

	/**
	 * Add unobserver to the list of unobservers
	 *
	 * @param {Function} fn Unobserver function
	 * @return {Void}
	 */
	var registerUnobservable = function( fn ) {
		this._unobservers.push(fn);
	};

	return {
		/**
		 * Plugin installer
		 *
		 * @param  {Object} Vue The Vue instance
		 * @param  {Object} options Optional parameters
		 * @return {Void}
		 */
		install: function( Vue, options ) {
			Vue.mixin({
				/**
				 * Register unobserver handlers when the component is about to be created
				 *
				 * @return {Void}
				 */
				beforeCreate: function() {
					this._unobservers = [];
					this.$registerUnobservable = registerUnobservable.bind(this);
				},

				/**
				 * Deregister observers when the component is about to be destroyed
				 *
				 * @return {Void}
				 */
				beforeDestroy: function() {
					var self = this;

					// Unobserve all registered observers
					this._unobservers.forEach( function( unobserver ) {
						("function" === typeof unobserver) && unobserver();
					});
				}
			});
		}
	};
});
