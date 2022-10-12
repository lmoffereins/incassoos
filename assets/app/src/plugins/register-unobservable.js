/**
 * Vue Register-Unobservable plugin
 *
 * Registers a helper method for running unobserve callbacks in a component's
 * `beforeDestroy()` lifecycle hook. This removes the need for using hacks to
 * make the unobserve callback manually available on component destruction.
 *
 * @author Laurens Offereins
 * @since 2019-05-16
 */
define([], function() {

	/**
	 * Add unobserver to the list of unobservers
	 *
	 * @param {Function} callback Unobserver callback
	 * @return {Void}
	 */
	var registerUnobservable = function( callback ) {
		this._unobservers.push(callback);
	};

	return {
		/**
		 * Plugin installer
		 *
		 * This plugin has no options.
		 *
		 * @param  {Object} Vue The Vue instance
		 * @return {Void}
		 */
		install: function( Vue ) {
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

					// Run the registered unobserver callbacks
					this._unobservers.forEach( function( callback ) {
						("function" === typeof callback) && callback();
					});
				}
			});
		}
	};
});
