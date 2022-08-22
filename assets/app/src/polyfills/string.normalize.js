/**
 * Addition of `normalize` to the String prototype
 *
 * Provides a graceful fallback.
 */
if ("function" !== typeof String.prototype.normalize) {

	// Must be writable: true, enumerable: false, configurable: true
	Object.defineProperty(String.prototype, "normalize", {
		value: function() {
			return this;
		},
		writable: true,
		configurable: true
	});
}
