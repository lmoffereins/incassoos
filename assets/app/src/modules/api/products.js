/**
 * Incassoos API endpoints for Products
 *
 * @package Incassoos
 * @subpackage App/API
 */
define([
	"he",
	"services",
	"settings"
], function( he, services, settings ) {
	/**
	 * Holds a reference to the cache service
	 *
	 * @type {Object}
	 */
	var cacheService = services.get("cache"),

	/**
	 * Get the product from the request item
	 *
	 * @param {Object} resp Response item
	 * @return {Object} Single product
	 */
	getProductFromResponse = function( resp ) {
		resp = resp || { title: {} };

		var item = {
			id: resp.id,
			title: he.decode(resp.title.rendered),
			date: new Date(resp.date),
			modified: new Date(resp.modified),
			price: parseFloat(resp.price),
			status: resp.status || "publish",
			productCategory: resp[settings.product.productCategory.taxonomyId][0] || 0,
			menuOrder: parseInt(resp.menu_order)
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
	 * Prepare the request with data for a single product
	 *
	 * @param {Object} request Request construct
	 * @param {Object} payload Parameters for the request
	 * @return {Object} Order request construct
	 */
	preSingleProductRequest = function( request, payload ) {

		// Define the product's name
		if (payload.title) {
			request.data.title = payload.title;
		}

		// Define the product's price
		if (payload.price) {
			request.data.price = payload.price;
		}

		// Define product category at the properly named taxonomy parameter
		if (payload.productCategory) {
			request.data[settings.product.productCategory.taxonomyId] = payload.productCategory;
		}

		// Ensure the product is published
		request.data.status = "publish";

		return request;
	},

	/**
	 * Return the parameters for updating a single product in cache
	 *
	 * @param {Object} request Request details
	 * @param {Mixed} value Request return value
	 * @return {Promise} Product cache was updated
	 */
	updateProductInCache = function( request, value ) {

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

			// Replace in or othwerise add to cache list
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
	 * Holds endpoint configuration for the product resource
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
		 * @param {Object} request Request construct
		 * @param {Object} payload Parameters for the request
		 * @return {Object} Request construct
		 */
		pre: function( request, payload ) {

			// Define payload defaults
			request.params = _.defaults(payload, {

				// Increase size of the returned set. Default is 10.
				per_page: 25
			});

			// Make sure to request both published and trashed products
			request.params.status = "publish,trash";

			return request;
		},

		/**
		 * Modify the response to define the list of products
		 *
		 * @param {Array} resp Response data
		 * @return {Array} List of products
		 */
		post: function( resp ) {
			return resp.map(getProductFromResponse);
		}
	}, {
		alias: "create",
		method: "POST",
		enableCache: { save: updateProductInCache },
		pre: preSingleProductRequest,
		post: getProductFromResponse
	}, {
		alias: "update",
		method: "PUT",
		enableCache: { save: updateProductInCache },

		/**
		 * Modify the request parameters before performing the call
		 *
		 * @param {Object} request Request construct
		 * @param {Object} payload Parameters for the request
		 * @return {Object} Request construct
		 */
		pre: function( request, payload ) {

			// Point to a single product
			request.url = request.baseUrl.concat("/", payload.id);

			return preSingleProductRequest(request, payload);
		},
		post: getProductFromResponse
	}, {
		alias: "trash",
		method: "DELETE",
		enableCache: { save: updateProductInCache },

		/**
		 * Modify the request parameters before performing the call
		 *
		 * @param {Object} request Request construct
		 * @param {Object} payload Parameters for the request
		 * @return {Object} Request construct
		 */
		pre: function( request, payload ) {

			// Point to a single product
			request.url = request.baseUrl.concat("/", payload.id);

			return request;
		},
		post: getProductFromResponse
	}, {
		alias: "untrash",
		method: "PUT",
		enableCache: { save: updateProductInCache },

		/**
		 * Modify the request parameters before performing the call
		 *
		 * @param {Object} request Request construct
		 * @param {Object} payload Parameters for the request
		 * @return {Object} Request construct
		 */
		pre: function( request, payload ) {

			// Point to a single product's untrash action
			request.url = request.baseUrl.concat("/", payload.id, "/untrash");

			return request;
		},
		post: getProductFromResponse
	}];

	return {
		endpoints: endpoints
	};
});
