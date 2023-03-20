/**
 * Translations loader
 *
 * @package Incassoos
 * @subpackage App/Translations
 */
define([
	"dayjs",
	"dayjs/plugin/utc",

	// Translation files
	"./en_US.json",
	"./nl_NL.json",

	// Dayjs locales
	"dayjs/locale/en",
	"dayjs/locale/nl"
], function( dayjs, dayjs_utc, en_US, nl_NL ) {
	/**
	 * Holds the list of available languages and their translations
	 *
	 * en_US is the default language with full localization coverage.
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
				easepick: "en-US",
				system: "en-US"
			}
		},
		"nl_NL": {
			label: "Nederlands",
			translation: nl_NL,
			alias: {
				dayjs: "nl",
				easepick: "nl-NL",
				system: "nl"
			}
		}
	},

	/**
	 * Holds the initial language key
	 *
	 * Use the browser's language, or else default to en_US.
	 *
	 * @type {String}
	 */
	initialLanguage = _.keys(availableLanguages).find( function( i ) {
		return window.navigator && window.navigator.language === availableLanguages[i].alias.system;
	}) || "en_US";

	// Identify the initial language
	availableLanguages[initialLanguage].initial = true;

	// Set locale in dayjs after loading locales
	dayjs.locale(availableLanguages[initialLanguage].alias.dayjs);

	// Use UTC date parsing
	dayjs.extend(dayjs_utc);

	// Return the available translations
	return availableLanguages;
});
