/**
 * System Service Functions
 * 
 * @package Incassoos
 * @subpackage App/Services
 */
define([], function() {
	/**
	 * Return whether the application is used locally
	 *
	 * @return {Boolean} Is the application used locally?
	 */
	var isLocal = !! ("undefined" !== typeof incassoosL10n && incassoosL10n.isLocal);

	return {
		isLocal: isLocal
	};
});
