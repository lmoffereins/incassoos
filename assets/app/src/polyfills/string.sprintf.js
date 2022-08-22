/**
 * Addition of PHP's `sprintf` to the String prototype
 *
 * Supports insertion of Strings (%s) and Numbers (%d).
 */
if ("function" !== typeof String.prototype.sprintf) {

	// Must be writable: true, enumerable: false, configurable: true
	Object.defineProperty(String.prototype, "sprintf", {
		value: function() { // .length of function is 0
			'use strict';

			var to = String(this),
			    // Accept list of arguments as array
			    args = Array.isArray(arguments[0]) ? arguments[0] : arguments;

			for (var index = 0; index < args.length; index++) {
				var nextArg = args[index],
				    typeIndex = ["string", "number"].indexOf(typeof nextArg);

				// Skip over if undefined or null or not string/number
				if (nextArg !== null && -1 !== typeIndex) {
					var nextKey = ["%s", "%d"][typeIndex],
					    nextIndexedKey = "%" + (index + 1) + "$" + nextKey[1];

					if (-1 !== to.indexOf(nextIndexedKey)) {
						while (-1 !== to.indexOf(nextIndexedKey)) {
							to = to.replace(nextIndexedKey, nextArg);
						}
					} else {
						to = to.replace(nextKey, nextArg);
					}
				}
			}

			return to;
		},
		writable: true,
		configurable: true
	});
}
