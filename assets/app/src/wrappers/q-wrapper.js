/**
 * Wrapper for Q library
 *
 * @package Incassoos
 * @subpackage App/Core
 */
define([
	"../../../../node_modules/q"
], function( Q ) {
	/**
	 * Promise wrapper to ensure a promise is returned. Accepts both
	 * functional callbacks and callbacks that already return a promise.
	 *
	 * Will return False as a rejected promise.
	 *
	 * @param {Mixed} input Parameter to wrap in a promise. When a callback, its outcome will be wrapped.
	 * @return {Promise} Input success
	 */
	Q.Promisify = function( input ) {
		var result, dfd = Q.defer();

		// Input is already a promise
		if (input && input.then && "function" === typeof input.then) {
			input.then(dfd.resolve).catch(dfd.reject);

		// Input is a function
		} else if ("function" === typeof input) {
			result = input();

			// Function resulted in a promise
			if (result && result.then && "function" === typeof result.then) {
				result.then(dfd.resolve).catch(dfd.reject);

			// Function resulted in someting else
			} else {
				(result || "undefined" === typeof result) ? dfd.resolve(result || true) : dfd.reject(result);
			}

		// Input is something else
		} else {
			(input || "undefined" === typeof input) ? dfd.resolve(input || true) : dfd.reject(input);
		}

		return dfd.promise;
	};

	return Q;
});
