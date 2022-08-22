/**
 * Incassoos API endpoints for authorization
 *
 * @package Incassoos
 * @subpackage App/API
 */
define([
	"q",
	"lodash",
	"services"
], function( Q, _, services ) {
	/**
	 * Holds a reference to the authorization service
	 *
	 * @type {Object}
	 */
	var authService = services.get("auth"),

	/**
	 * Act when the request failed
	 *
	 * TODO: when does this actually happen?
	 *
	 * @param  {Object} error Error data
	 * @param  {Object} request Request parameters
	 * @return {Promise} Whether the error was catched
	 */
	onError = function( error, request ) {

		// Remove the invalidated user's authorization
		return authService.removeUser(request.userid || authService.getActiveUser()).then( function() {

			// Continue the error chain
			return Q.reject(error);
		});
	},

	/**
	 * Holds endpoint configuration for the authorization
	 *
	 * @return {Object}
	 */
	endpoints = [{
		requireAuth: false,
		alias: "login",
		method: "POST",

		/**
		 * Modify the request parameters before performing the call
		 *
		 * @param  {Object} request Request consruct
		 * @param  {Object} payload Parameters for the request
		 * @return {Promise} Request construct
		 */
		pre: function( request, payload ) {

			// Define payload defaults
			payload = _.defaults(payload, {
				username: "",
				password: ""
			});

			// Bail when login credentials are invalid
			if (! payload.username.length) {
				return Q.reject("Login.Error.EmptyUsername");
			}

			// Bail when login credentials are invalid
			if (! payload.password.length) {
				return Q.reject("Login.Error.EmptyPassword");
			}

			// Set the Content-Type
			request.headers["Content-Type"] = "application/json";

			// Set the body username
			request.data.username = payload.username;

			// Set the body password. TODO: Should this be safer?!
			request.data.password = payload.password;

			return request;
		},

		/**
		 * Use the response to define the login status
		 *
		 * @param  {Object} resp Response data
		 * @return {Promise} Response data
		 */
		post: function( resp ) {
			var id = resp.data.user_login;

			// Register user and make this the active user
			return authService.saveUserAndSetActive(id, {
				token: resp.data.token,
				userName: resp.data.user_display_name,
				roles: resp.data.roles
				// expires: Date.now() // TODO: Local expiration of tokens

			// Return response data
			}).then( function() {
				return resp;
			});
		}
	}, {
		subroute: "/validate",
		alias: "validate",
		method: "POST",
		onError: onError,

		/**
		 * Modify the request parameters before performing the call
		 *
		 * @param  {Object} request Request construct
		 * @param  {Object} payload Parameters for the request
		 * @return {Promise} Request construct
		 */
		pre: function( request, payload ) {

			// When provided, define request for the given user
			if (payload.id) {

				// Save specified id in custom parameter
				request.userid = payload.id;

				// Re-set authorization headers for the specified id
				return authService.setAuthHeaders(request, payload.id).then( function() {
					return request;
				});

			// Otherwise, validate the implicated current user. As authorization is
			// required, the endpoint handles setting the correct headers.
			} else {
				return Q.resolve(request);
			}
		},

		/**
		 * Use the response to update the user data
		 *
		 * @param  {Object} resp Response data
		 * @return {Promise} Response data
		 */
		post: function( resp ) {
			var id = resp.data.user_login;

			// Update user
			return authService.saveUser(id, {
				userName: resp.data.user_display_name,
				roles: resp.data.roles
			});
		}
	}, {
		subroute: "/invalidate",
		alias: "logout",
		method: "POST",
		onError: onError,

		/**
		 * Modify the request parameters before performing the call
		 *
		 * @param  {Object} request Request construct
		 * @param  {Object} payload Parameters for the request
		 * @return {Promise} Request construct
		 */
		pre: function( request, payload ) {

			// When provided, define request for the given user
			if (payload.id) {

				// Save specified id in custom parameter
				request.userid = payload.id;

				// Re-set authorization headers for the specified id
				return authService.setAuthHeaders(request, payload.id).then( function() {
					return request;
				});

			// Otherwise, invalidate the implicated current user. As authorization is
			// required, the endpoint handles setting the correct headers.
			} else {
				return Q.resolve(request);
			}
		},

		/**
		 * Use the response to define the logout status
		 *
		 * @param  {Object} resp Response data
		 * @return {Promise} Response data
		 */
		post: function( resp ) {

			// Deregister user
			return authService.removeUser(resp.data.user_login).then( function() {
				return resp;
			});
		}
	}];

	return {
		endpoints: endpoints
	};
});
