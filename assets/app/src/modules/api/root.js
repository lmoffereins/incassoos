/**
 * Incassoos API endpoints for the root
 *
 * @package Incassoos
 * @subpackage App/API
 */
define([
	"q",
	"axios",
	"lodash",
	"services",
	"settings"
], function( Q, axios, _, services, settings ) {
	/**
	 * Holds a reference to the installation service
	 *
	 * @type {Object}
	 */
	var installService = services.get("install"),

	/**
	 * Holds endpoints configuration for the root
	 *
	 * @return {Object}
	 */
	endpoints = [{
		requireAuth: false,

		/**
		 * Modify the request parameters before performing the call
		 *
		 * @param  {Object} request Request consruct
		 * @param  {Object} payload Parameters for the request
		 * @return {Promise} Request construct
		 */
		pre: function( request, payload ) {
			var protocol = (!! payload.isSecure) ? "https://" : "http://";

			// Define payload defaults
			payload = _.defaults(payload, {
				domain: _.get(settings, "api.root"),
				namespace: _.get(settings, "api.namespace")
			});

			// Bail when connection parameters are invalid
			if (! payload.domain.length || ! payload.namespace.length) {
				return Q.reject("API.Error.InvalidRequestParameters");
			}

			// Get url structure from domain. Domain can be user input.
			try {
				var url = new URL(protocol.concat(payload.domain.replace(/https?:\/\//, '')));
				payload.domain = url.host;
			} catch ( error ) {
				return Q.reject("API.Error.IncorrectDomain");
			}

			// Set the base url
			request.url = protocol.concat(payload.domain, "/wp-json/", payload.namespace);

			return request;
		},

		/**
		 * Use the response to define the login status
		 *
		 * @param  {Object} resp Response data
		 * @return {Promise} Response data
		 */
		post: function( resp ) {
			var route, root, stored = [], install = {};

			// When the response contains what is expected
			if (resp._links && resp._links.up) {

				// Define the root or baseUrl
				root = resp._links.up[0].href;

				// Save the API root
				install.root = root;

				// Look for the `settings` route
				for (route in resp.routes) {
					if (resp.routes[route].isSettings) {
						stored.push(

							// Call the settings route (includes the namespace)
							axios.get(root + route.substring(1)).then( function( response ) {

								// Mark with a timestamp
								response.data.$timestamp = new Date().getTime();

								// Add settings to installation details
								install.settings = response.data;

								// Install the API settings
								return installService.install(install);
							})
						);

						break;
					}
				}

				// When no `settings` route was found, just install with root
				if (! stored.length) {
					stored.push(installService.install(install));
				}

				resp = Q.all(stored);
			} else {
				resp = Q.reject("API.Error.IncorrectDomain");
			}

			return resp;
		}
	}, {
		alias: "settings",
		method: "GET",

		/**
		 * Modify the request parameters before performing the call
		 *
		 * @param  {Object} request Request consruct
		 * @return {Promise} Request construct
		 */
		pre: function( request ) {

			// Set the settings url
			request.url = settings.api.namespace.concat("/", settings.api.routes.settings);

			return request;
		},

		/**
		 * Use the response to define the login status
		 *
		 * @param  {Object} resp Response data
		 * @return {Promise} Response data
		 */
		post: function( resp ) {

			// Mark with a timestamp
			resp.$timestamp = new Date().getTime();

			// Save the settings
			return installService.install({ settings: resp }).then( function() {
				return resp;
			});
		}
	}];

	return {
		endpoints: endpoints
	};
});
