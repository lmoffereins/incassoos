/**
 * Translations loader
 *
 * @package Incassoos
 * @subpackage App/Translations
 */
define([
	"q",
	"dayjs",
	"dayjs/plugin/utc",

	// Translation files
	"./en_US.json",
	"./nl_NL.json",

	// Dayjs locales
	"dayjs/locale/en",
	"dayjs/locale/nl"
], function( Q, dayjs, dayjs_utc, en_US, nl_NL ) {
	/**
	 * Holds the list of available languages and their translations
	 *
	 * en_US is considered the default language.
	 *
	 * @type {Object}
	 */
	var availableLanguages = {
		"en_US": {
			label: "English",
			default: true,
			translation: en_US,
			alias: {
				dayjs: "en",
				easepick: "en-US"
			}
		},
		"nl_NL": {
			label: "Nederlands",
			translation: nl_NL,
			alias: {
				dayjs: "nl",
				easepick: "nl-NL"
			}
		}
	};

	// Reset locale in dayjs after loading locales
	dayjs.locale("en");

	// Use UTC date parsing
	dayjs.extend(dayjs_utc);

	/**
	 * Load the available translations
	 *
	 * @return {Promise} Loaded translations
	 */
	return function translationsLoader() {
		return Q.resolve(availableLanguages);
	};
});
