/**
 * Custom generic Vue Filters
 *
 * @package Incassoos
 * @subpackage App/Core
 */
define([
	"vue",
	"dayjs",
	"services",
	"util"
], function( Vue, dayjs, services, util ) {
	/**
	 * Holds a reference to the l10n service
	 *
	 * @type {Object}
	 */
	var l10nService = services.get("l10n");

	/**
	 * Format a boolean value to Yes/No
	 *
	 * @return {String} Yes or No
	 */
	Vue.filter("boolean", function( value ) {
		return l10nService.get(value ? "Common.Yes" : "Common.No");
	});

	/**
	 * Format a date value
	 *
	 * @param {String} format Optional. Date format definition. Defaults to format from l10n.
	 * @param {Boolean} isUTC Optional. Whether the date is set in UTC. Defaults to True.
	 * @return {String} Formatted date
	 */
	Vue.filter("date", function( value, format, isUTC ) {
		format = format || l10nService.get("Generic.DateFormat");
		isUTC = "undefined" === typeof isUTC ? true : isUTC;

		// Return formatted date
		return dayjs(value).utc(isUTC).local().format(format);
	});

	/**
	 * Format a datetime value
	 *
	 * @param {String} format Optional. Date format definition. Defaults to format from l10n.
	 * @param {Boolean} isUTC Optional. Whether the date is set in UTC. Defaults to True.
	 * @return {String} Formatted date
	 */
	Vue.filter("datetime", function( value, format, isUTC ) {
		format = format || l10nService.get("Generic.DateTimeFormat");
		isUTC = "undefined" === typeof isUTC ? true : isUTC;

		// Return formatted date
		return dayjs(value).utc(isUTC).local().format(format);
	});

	/**
	 * Format a datetime value to a comparison to now
	 *
	 * @param {String} format Optional. Date format definition. Defaults to format from l10n.
	 * @param {Boolean} isUTC Optional. Whether the date is set in UTC. Defaults to True.
	 * @return {String} Formatted date
	 */
	Vue.filter("recent", function( value, format, isUTC ) {
		format = format || l10nService.get("Generic.DateFormat");
		isUTC = "undefined" === typeof isUTC ? true : isUTC;

		var date = dayjs(value).utc(isUTC).local(), now = dayjs();

		// When less than a day ago
		if (now.diff(date, "hour") < 24) {
			return date.format("HH:mm");

		// When less than a year ago (no month mix-up possible)
		} else if (now.diff(date, "year", true) < 1) {
			return date.format(l10nService.get("Generic.DateShortFormat"));

		// Longer than a year ago
		} else {
			return date.format(format);
		}
	});

	/**
	 * Format a number value
	 *
	 * @param {String} format Optional. Number format definition. Defaults to format from l10n.
	 * @return {String} Formatted number
	 */
	Vue.filter("number", function( value, format, options ) {
		format = format || l10nService.get("Generic.NumFormat");
		options = options || {};
		options.decSep = options.decSep || l10nService.get("Generic.DecimalSeparator");
		options.thouSep = options.thouSep || l10nService.get("Generic.ThousandSeparator");

		// Handling of negative numbers
		if (-1 !== format.indexOf(";") && value < 0) {
			format = format.split(";")[1];
			value = Math.abs(value);

		// Default to positive format
		} else {
			format = format.split(";")[0];
		}

		// Return formatted number
		return util.numberFormat(value, format, options);
	});

	/**
	 * Format a money value
	 *
	 * TODO: apply appropriate valuta sign --> valuta should not be related to l10n
	 *
	 * @param {String} format Optional. Money format definition. Defaults to format from l10n.
	 * @return {String} Formatted money
	 */
	Vue.filter("money", function( value, format ) {
		format = format || l10nService.get("Generic.MoneyFormat");

		// Make it a number
		value = util.sanitizePrice(value);

		// Return formatted number
		return Vue.filter("number")(value, format);
	});
});
