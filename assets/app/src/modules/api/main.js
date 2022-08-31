/**
 * Incassoos API
 *
 * @package Incassoos
 * @subpackage App/API
 */
define("api", [
	"lodash",
	"services",
	"settings",
	"./api",
	"./root",
	"./authorization",
	"./consumer-types",
	"./consumers",
	"./occasions",
	"./orders",
	"./products",
], function(
	_,
	services,
	settings,
	createEndpoints,
	rootAPI,
	authorizationAPI,
	consumerTypesAPI,
	consumersAPI,
	occasionsAPI,
	ordersAPI,
	productsAPI
) {
	/**
	 * Holds the map of Incassoos REST API error messages
	 *
	 * @type {Object}
	 */
	var errorMessageMap = {

		// Login
		"incassoos_rest_auth-empty_username": "Login.Error.EmptyUsername",
		"incassoos_rest_auth-empty_password": "Login.Error.EmptyPassword",
		"incassoos_rest_auth-invalid_username": "Login.Error.InvalidCredentials",
		"incassoos_rest_auth-invalid_email": "Login.Error.InvalidCredentials",
		"incassoos_rest_auth-incorrect_password": "Login.Error.InvalidCredentials",
		"incassoos_rest_auth-spammer_account": "Login.Error.AccountMarkedAsSpammer",
		"incassoos_rest_forbidden": "Login.Error.AccessNotAllowed",

		// Authorization
		"incassoos_rest_auth_no_header": "API.Error.InvalidTokenHeader",
		"incassoos_rest_auth_bad_header": "API.Error.InvalidTokenHeader",
		"incassoos_rest_auth_bad_config": "API.Error.InvalidTokenHeader",
		"incassoos_rest_auth_invalid_iss": "API.Error.InvalidTokenHeader",
		"incassoos_rest_auth_invalid_user": "API.Error.InvalidTokenHeader",
		"incassoos_rest_auth_invalid_token": "API.Error.InvalidTokenHeader",

		// Generic
		"rest_forbidden_context": "API.Error.AccessNotAllowed",
		"rest_cannot_edit": "API.Error.EditNotAllowed",
		"rest_cannot_delete": "API.Error.DeleteNotAllowed",
		"rest_post_invalid_page_number": "API.Error.InvalidPageNumber",
		"rest_user_invalid_id": "API.Error.InvalidUserId",

		// Assets
		"incassoos_empty_title": "API.Error.EmptyTitle",
		"incassoos_rest_invalid_order_consumer_field": "API.Error.InvalidConsumerField",
		"incassoos_rest_invalid_order_products_field": "API.Error.InvalidProductsField",
		"incassoos_rest_invalid_date_field": "API.Error.InvalidDateField",
		"incassoos_order_locked_occasion": "Order.Error.OccasionClosed",
		"incassoos_rest_invalid_product_price_field": "API.Error.InvalidPriceField",

		// Occasion
		"incassoos_rest_occasion_cannot_close_post": "Occasion.Error.CloseNotAllowed",
		"incassoos_rest_is_locked": "Occasion.Error.IsLocked",
		"incassoos_rest_cannot_close": "Occasion.Error.CannotClose",
		"incassoos_rest_occasion_cannot_reopen_post": "Occasion.Error.ReopenNotAllowed",
		"incassoos_rest_is_not_closed": "Occasion.Error.IsNotClosed",
		"incassoos_rest_cannot_reopen": "Occasion.Error.CannotReopen"
	},

	/**
	 * Holds the available API methods
	 *
	 * @type {Object}
	 */
	methods = {
		/**
		 * Shortcut route for setting up the basic API connection
		 *
		 * @param  {Object} payload Query parameters
		 * @return {Promise} Request result
		 */
		connect: function( payload ) {
			return methods.root.get(payload);
		},

		/**
		 * Initial version of the root route
		 *
		 * This route is used before any other route to fetch settings.
		 *
		 * @type {Object}
		 */
		root: createEndpoints("root", rootAPI.endpoints),

		/**
		 * Return the error message, parsed for API error codes
		 *
		 * @param  {String|Object} error Error message or data
		 * @return {Object} Error data
		 */
		getErrorItem: function( error ) {

			// When error was not already defined
			if ("undefined" === typeof error.isError) {
				var message, data = {};

				// When dealing with an error object
				if (_.isPlainObject(error)) {
					message = "Common.Error.Unknown";

					// This is a REST API error
					if (error.data && error.data.code) {
						message = errorMessageMap[error.data.code] || message;
						data.isRestError = !! errorMessageMap[error.data.code];
						data.args = Array.isArray(error.data.args) ? error.data.args : [error.data.args];

					// Generic error
					} else {
						data.args = [error.toString()];
					}

				// Use the error's text
				} else {
					message = error.toString();
				}

				// Construct error
				error = {
					isError: true,
					message: message,
					data: data
				};
			}

			return error;
		}
	},

	/**
	 * Create or update the API endpoints
	 *
	 * @return {Void}
	 */
	updateEndpoints = function() {
		var parsedMethods, i;

		// Parse new API settings
		// Note that parsing does not modify `methods` directly. This is to accomodate for the
		// prioritization of the renewed values when using the defaulter. Modifying `methods` instead
		// of overwriting is relevant for the sake of keeping the same single object reference.
		parsedMethods = _.defaults({
			root:          createEndpoints("root",          rootAPI.endpoints),
			auth:          createEndpoints("authorization", authorizationAPI.endpoints),
			consumerTypes: createEndpoints("consumerTypes", consumerTypesAPI.endpoints),
			consumers:     createEndpoints("consumers",     consumersAPI.endpoints),
			occasions:     createEndpoints("occasions",     occasionsAPI.endpoints),
			orders:        createEndpoints("orders",        ordersAPI.endpoints),
			products:      createEndpoints("products",      productsAPI.endpoints)
		}, methods);

		// Update the methods data
		for (i in parsedMethods) {
			methods[i] = parsedMethods[i];
		}
	};

	// When services are initialized, try to fetch initial settings
	services.on("init", function() {
		return methods.connect(settings.api);
	});

	// When settings are updated, update API methods
	settings.$onUpdate(updateEndpoints);

	return methods;
});
