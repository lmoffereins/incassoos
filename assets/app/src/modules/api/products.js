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
			titleRaw: he.decode(resp.title.raw),
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

		// Set the title from raw title
		if (payload.titleRaw) {
			request.data.title = payload.titleRaw;
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
		enableCache: { save: cacheService.updateItemInListFromRequest },
		pre: preSingleProductRequest,
		post: getProductFromResponse
	}, {
		alias: "update",
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

			// Point to a single product
			request.url = request.baseUrl.concat("/", payload.id);

			return preSingleProductRequest(request, payload);
		},
		post: getProductFromResponse
	}, {
		alias: "trash",
		method: "DELETE",
		enableCache: { save: cacheService.updateItemInListFromRequest },

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
		enableCache: { save: cacheService.updateItemInListFromRequest },

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
