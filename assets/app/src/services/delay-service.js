/**
 * Delay Service Functions
 *
 * @package Incassoos
 * @subpackage App/Services
 */
define([
	"q"
], function( Q ) {
	/**
	 * Holds the default load time of mocked delays
	 *
	 * @type {Number}
	 */
	var defaultDelay = 600,

	/**
	 * Delay the execution of a callback
	 *
	 * @param  {Number} delay Optional. Delay to apply in milliseconds.
	 * @return {Promise}
	 */
	delay = function( delay ) {
		var dfd = Q.defer();

		setTimeout(dfd.resolve, ! isNaN(delay) ? delay : defaultDelay);

		return dfd.promise;
	};

	return delay;
});
