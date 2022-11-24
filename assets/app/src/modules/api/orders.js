/**
 * Incassoos API endpoints for Orders
 *
 * @package Incassoos
 * @subpackage App/API
 */
define([
	"he",
	"util"
], function( he, util ) {
	/**
	 * Get the order from the request item
	 *
	 * @param  {Object} resp Response item
	 * @return {Object} Single order
	 */
	var getOrderFromResponse = function( resp ) {
		resp = resp || { products: [] };

		var item = {
			id: resp.id,
			date: new Date(resp.date_gmt),
			modified: new Date(resp.modified_gmt),
			consumer: resp.consumer,
			consumerData: {
				id: resp.consumer,
				name: he.decode(resp.consumer_name)
			},
			occasion: resp.parent,
			items: resp.products.map( function( prod ) {
				return {
					id: prod.id,
					title: he.decode(prod.name),
					price: util.sanitizePrice(prod.price),
					quantity: parseInt(prod.amount)
				};
			})
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
	 * Prepare the request with data for a single order
	 *
	 * @param  {Object} request Request construct
	 * @param  {Object} payload Parameters for the request
	 * @return {Object} Order request construct
	 */
	preSingleOrderRequest = function( request, payload ) {

		// Define the order's consumer
		if (payload.consumer) {
			request.data.consumer = payload.consumer;
		}

		// Define the order's parent
		if (payload.occasion) {
			request.data.parent = payload.occasion;
		}

		// Define the order's products
		if (payload.items) {
			request.data.products = payload.items.map( function( i ) {
				return {
					id: i.id,
					price: i.price,

					// Server uses 'amount' and 'name'
					amount: i.quantity,
					name: i.title
				};
			});
		}

		return request;
	},

	/**
	 * Holds endpoint configuration for the product resource
	 *
	 * @return {Object}
	 */
	endpoints = [{
		requireAuth: true,
		usePagination: true,

		/**
		 * Modify the request parameters before performing the call
		 *
		 * @param  {Object} request Request construct
		 * @param  {Object} payload Parameters for the request
		 * @return {Object} Request construct
		 */
		pre: function( request, payload ) {

			// Make sure to only request a single occasion's orders
			request.params.parent = payload.occasion || payload.id || 0;

			// Increase size of the returned set
			request.params.per_page = payload.per_page || 25;

			return request;
		},

		/**
		 * Modify the response to define the list of orders
		 *
		 * @param  {Array} resp Response data
		 * @return {Array} List of orders
		 */
		post: function( resp ) {
			return resp.map(getOrderFromResponse);
		}
	}, {
		alias: "create",
		method: "POST",
		pre: preSingleOrderRequest,
		post: getOrderFromResponse
	}, {
		alias: "update",
		method: "PUT",

		/**
		 * Modify the request parameters before performing the call
		 *
		 * @param  {Object} request Request construct
		 * @param  {Object} payload Parameters for the request
		 * @return {Object} Request construct
		 */
		pre: function( request, payload ) {

			// Point to a single order
			request.url = request.baseUrl.concat("/", payload.id);

			return preSingleOrderRequest(request, payload);
		},
		post: getOrderFromResponse
	}];

	return {
		endpoints: endpoints
	};
});
