/**
 * Incassoos API endpoints for Consumer Types
 *
 * @package Incassoos
 * @subpackage App/API
 */
define([
	"he",
	"lodash",
	"services",
	"settings",
	"util"
], function( he, _, services, settings, util ) {
	/**
	 * Holds a reference to the cache service
	 *
	 * @type {Object}
	 */
	var cacheService = services.get("cache"),

	/**
	 * Get the consumer type from the request item
	 *
	 * @param  {Object} resp Response item
	 * @return {Object} Single consumer type
	 */
	getConsumerTypeFromResponse = function( resp ) {
		var item = {
			id: resp.id,
			name: he.decode(resp.name),
			description: he.decode(resp.description),
			avatarUrl: resp.avatarUrl || settings.consumer.defaultAvatarUrl,
			spendingLimit: util.sanitizePrice(resp.spendingLimit) || 0,
			show: ! resp.archived,
			isBuiltin: resp._builtin,
			isConsumerType: true,
			group: {
				id: "consumer-types",
				name: "Consumer.TypesGroupName",
				order: 0
			}
		}, i;

		/**
		 * Add custom application fields to item
		 *
		 * We cannot assume all additional fields in the response object
		 * are relevant for the item. Adding all fields would create
		 * unnecessary large objects in the application. Only add fields
		 * that are listed in the `_applicationFields` array.
		 */
		if (resp._applicationFields) {
			for (i = 0; i < resp._applicationFields.length; i++) {
				item[resp._applicationFields[i]] = resp[resp._applicationFields[i]];
			}
		}

		return item;
	},

	/**
	 * Holds endpoint configuration for the consumer types resource
	 *
	 * @return {Object}
	 */
	endpoints = [{
		requireAuth: true,
		enableCache: true,
		usePagination: true,

		/**
		 * Modify the request parameters before performing the call
		 *
		 * @param  {Object} request Request construct
		 * @param  {Object} payload Parameters for the request
		 * @return {Object} Request construct
		 */
		pre: function( request, payload ) {

			// Define payload defaults
			request.params = _.defaults(payload, {

				// Increase size of the returned set. Default is 10. Maximum is 100.
				per_page: 50
			});

			return request;
		},

		/**
		 * Modify the response to define the list of consumer types
		 *
		 * @param  {Array} resp Response data
		 * @return {Array} List of consumer types
		 */
		post: function( resp ) {
			return resp.map(getConsumerTypeFromResponse);
		}
	}, {
		alias: "update",
		method: "PUT",
		enableCache: { save: cacheService.updateItemInListFromRequest },

		/**
		 * Modify the request parameters before performing the call
		 *
		 * @param  {Object} request Request construct
		 * @param  {Object} payload Parameters for the request
		 * @return {Object} Request construct
		 */
		pre: function( request, payload ) {

			// Point to a single consumer type
			request.url = request.baseUrl.concat("/", payload.id);

			// Set the consumption limit. Can be 0.
			if ("undefined" !== typeof payload.spendingLimit) {
				request.data.spendingLimit = payload.spendingLimit;
			}

			// Set the archived parameter for hidden consumer type. Can be false.
			if ("undefined" !== typeof payload.show) {
				request.data.archived = ! payload.show;
			}

			return request;
		},
		post: getConsumerTypeFromResponse
	}, {
		alias: "archive",
		method: "PUT",
		enableCache: { save: cacheService.updateItemInListFromRequest },

		/**
		 * Modify the request parameters before performing the call
		 *
		 * @param {Object} request Request construct
		 * @param {Object} payload Parameters for the request
		 * @return {Object} Request construct
		 */
		pre: function( request, payload ) {

			// Point to a single consumer type's archive action
			request.url = request.baseUrl.concat("/", payload.id, "/archive");

			return request;
		},
		post: getConsumerTypeFromResponse
	}, {
		alias: "unarchive",
		method: "PUT",
		enableCache: { save: cacheService.updateItemInListFromRequest },

		/**
		 * Modify the request parameters before performing the call
		 *
		 * @param {Object} request Request construct
		 * @param {Object} payload Parameters for the request
		 * @return {Object} Request construct
		 */
		pre: function( request, payload ) {

			// Point to a single consumer type's unarchive action
			request.url = request.baseUrl.concat("/", payload.id, "/unarchive");

			return request;
		},
		post: getConsumerTypeFromResponse
	}];

	return {
		endpoints: endpoints
	};
});
