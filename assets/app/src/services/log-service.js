/**
 * Logging Service Functions
 * 
 * @package Incassoos
 * @subpackage App/Services
 */
define([
	"lodash",
	"./storage-service",
	"./auth-service"
], function( _, storageService, authService ) {
	/**
	 * Holds the storage for the service
	 *
	 * @type {Object}
	 */
	var storage = storageService.create("service/log"),

	/**
	 * The default logging domain name
	 *
	 * @type {String}
	 */
	defaultDomain = "default",

	/**
	 * Whether logging is enabled
	 *
	 * @type {Boolean}
	 */
	isEnabled = true,

	/**
	 * Enable logging
	 *
	 * @return {Void}
	 */
	enable = function() {
		isEnabled = true;
	},

	/**
	 * Disable logging
	 *
	 * @return {Void}
	 */
	disable = function() {
		isEnabled = false;
	},

	/**
	 * Add a log object to the log registry
	 *
	 * @param {Object} log Logging data
	 * @return {Promise} Was the action successfull?
	 */
	add = function( log ) {
		var logs;

		// Bail when logging is disabled
		if (! isEnabled) {
			return false;
		}

		log = _.isObject(log) ? log : { message: log };

		// Parse defaults
		_.defaults(log, {
			domain: defaultDomain,
			isError: false,
			message: "",
			timestamp: new Date().getTime(),
			user: authService.getActiveUser(),
			context: window.navigator ? window.navigator.userAgent : ""
		});

		// Add log to the log list
		return storage.save(log.timestamp, log);
	},

	/**
	 * Add an error message to the log registry
	 *
	 * @param  {String} domain Log domain
	 * @param  {String} message Log message
	 * @return {Promise} Was the action successfull?
	 */
	error = function( domain, message ) {
		console.error("logService/error", message);

		// Only one argument was provided
		if (1 === arguments.length) {
			message = domain;
			domain = defaultDomain;
		}

		return add({
			domain: domain,
			isError: true,
			message: message
		});
	},

	/**
	 * Add a message to the log registry
	 *
	 * @param  {String} domain Log domain
	 * @param  {String} message Log message
	 * @return {Promise} Was the action successfull?
	 */
	log = function( domain, message ) {

		// Only one argument was provided
		if (1 === arguments.length) {
			message = domain;
			domain = defaultDomain;
		}

		console.log("logService/log", message);

		return add({
			domain: domain,
			message: message
		});
	},

	/**
	 * Return the registered log entries
	 *
	 * @param {Object} options Optional. List of options.
	 * @return {Promise} Registered logs
	 */
	getEntries = function( options ) {
		options = options || {};
		options.since = options.since && new Date(options.since).toString();

		return storage.filter( function( i ) {

			// Filter by domain
			if (options.domain && i.domain !== options.domain) {
				return false;
			}

			// Filter by logging date
			if (options.since && "Invalid Date" !== options.since && i.timestamp < options.since) {
				return false;
			}

			return true;
		});
	};

	return {
		clear: storage.clear,
		enable: enable,
		error: error,
		disable: disable,
		getEntries: getEntries,
		isEnabled: isEnabled,
		log: log
	};
});
