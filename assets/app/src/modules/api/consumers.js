/**
 * Incassoos API endpoints for Consumers
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
	 * Get the consumer from the request item
	 *
	 * @param  {Object} resp Response item
	 * @return {Object} Single consumer
	 */
	getConsumerFromResponse = function( resp ) {
		resp = resp || { group: {} };

		return {
			id: resp.id,
			name: he.decode(resp.name),
			avatarUrl: resp.avatarUrl || settings.consumer.defaultAvatarUrl,
			spendingLimit: util.sanitizePrice(resp.spendingLimit) || 0,
			show: !! resp.show,
			customSort: parseInt(resp.customSort),
			group: {
				id: resp.group.id,
				name: he.decode(resp.group.name),
				order: resp.group.order
			}
		};
	},

	/**
	 * Return the parameters for updating a single consumer in cache
	 *
	 * @param {Object} request Request details
	 * @param {Mixed} value Request return value
	 * @return {Promise} Consumer cache was updated
	 */
	updateConsumerInCache = function( request, value ) {

		// Construct cache key for generic GET
		var key = cacheService.getCacheKeyForRequest({
			url: request.baseUrl
		});

		// Get existing item cache
		return cacheService.get(key).then( function( cache ) {

			// Find original item in cache
			var index = cache.findIndex( function( i ) {
				return i.id === value.id;
			});

			// Replace in or otherwise add to cache list
			if (-1 !== index) {
				cache[index] = value;
			} else {
				cache.push(value);
			}

			// Update list in cache
			return cacheService.save(key, cache, { expires: true }).then( function() {
				return value;
			});
		});
	},

	/**
	 * Holds endpoint configuration for the consumers resource
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
				per_page: 25
			});

			return request;
		},

		/**
		 * Modify the response to define the list of consumers
		 *
		 * @param  {Array} resp Response data
		 * @return {Array} List of consumers
		 */
		post: function( resp ) {
			return resp.map(getConsumerFromResponse);
		}
	}, {
		alias: "update",
		method: "PUT",
		enableCache: { save: updateConsumerInCache },

		/**
		 * Modify the request parameters before performing the call
		 *
		 * @param  {Object} request Request construct
		 * @param  {Object} payload Parameters for the request
		 * @return {Object} Request construct
		 */
		pre: function( request, payload ) {

			// Point to a single consumer
			request.url = request.baseUrl.concat("/", payload.id);

			// Set the consumption limit. Can be 0.
			if ("undefined" !== typeof payload.spendingLimit) {
				request.data.spendingLimit = payload.spendingLimit;
			}

			// Set the show parameter for hidden consumer. Can be false.
			if ("undefined" !== typeof payload.show) {
				request.data.show = payload.show;
			}

			return request;
		},
		post: getConsumerFromResponse
	}, {
		alias: "hide",
		method: "PUT",
		enableCache: { save: updateConsumerInCache },

		/**
		 * Modify the request parameters before performing the call
		 *
		 * @param {Object} request Request construct
		 * @param {Object} payload Parameters for the request
		 * @return {Object} Request construct
		 */
		pre: function( request, payload ) {

			// Point to a single consumer's hide action
			request.url = request.baseUrl.concat("/", payload.id, "/hide");

			return request;
		},
		post: getConsumerFromResponse
	}, {
		alias: "show",
		method: "PUT",
		enableCache: { save: updateConsumerInCache },

		/**
		 * Modify the request parameters before performing the call
		 *
		 * @param {Object} request Request construct
		 * @param {Object} payload Parameters for the request
		 * @return {Object} Request construct
		 */
		pre: function( request, payload ) {

			// Point to a single consumer's show action
			request.url = request.baseUrl.concat("/", payload.id, "/show");

			return request;
		},
		post: getConsumerFromResponse
	}];

	return {
		endpoints: endpoints
	};
});
