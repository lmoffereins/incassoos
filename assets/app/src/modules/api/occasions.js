/**
 * Incassoos API endpoints for Occasions
 *
 * @package Incassoos
 * @subpackage App/API
 */
define([
	"dayjs",
	"settings"
], function( dayjs, settings ) {
	/**
	 * Get the occasion from the request item
	 *
	 * @param {Object} resp Response item
	 * @return {Object} Single occasion
	 */
	var getOccasionFromResponse = function( resp ) {
		resp = resp || { title: {} };

		return {
			id: resp.id,
			title: resp.title.rendered,
			titleRaw: resp.title.raw,
			date: new Date(resp.date),
			modified: new Date(resp.modified),
			occasionDate: new Date(resp.occasion_date),
			occasionType: resp[settings.occasion.occasionType.taxonomyId][0] || 0,
			closed: resp.closed || false,
			consumers: resp.consumers || [],
			editable: [
				"title",
				"occasionType",
				"occasionDate"
			]
		};
	},

	/**
	 * Prepare the request with data for a single occasion
	 *
	 * @param {Object} request Request construct
	 * @param {Object} payload Parameters for the request
	 * @return {Object} Order request construct
	 */
	preSingleOccasionRequest = function( request, payload ) {

		// Set the title
		if ("undefined" !== typeof payload.title) {
			request.data.title = payload.title;
		}

		// Parse occasion date into the proper format
		if ("undefined" !== typeof payload.occasionDate) {
			request.data.occasion_date = dayjs(payload.occasionDate).format("YYYY-MM-DD");
		}

		// Define occasion type at the properly named taxonomy parameter
		if ("undefined" !== typeof payload.occasionType) {
			request.data[settings.occasion.occasionType.taxonomyId] = payload.occasionType;
		}

		// Instantly publish the occasion
		request.data.status = "publish";

		return request;
	},

	/**
	 * Holds endpoint configuration for the occasion resource
	 *
	 * @return {Object}
	 */
	endpoints = [{
		requireAuth: true,
		usePagination: true,

		/**
		 * Modify the request parameters before performing the call
		 *
		 * @param {Object} request Request construct
		 * @param {Object} payload Parameters for the request
		 * @return {Object} Request construct
		 */
		pre: function( request, payload ) {

			// Make sure to only request editable occasions. Collected occasions are not editable.
			// Closed occasions have status 'publish' and need to be reopened for adding orders.
			request.params.status = "publish";

			return request;
		},

		/**
		 * Modify the response to define the list of occasions
		 *
		 * @param {Array} resp Response data
		 * @return {Array} List of occasions
		 */
		post: function( resp ) {
			return resp.map(getOccasionFromResponse);
		}
	}, {
		alias: "create",
		method: "POST",
		pre: preSingleOccasionRequest,
		post: getOccasionFromResponse
	}, {
		alias: "update",
		method: "PUT",

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

			return preSingleOccasionRequest(request, payload);
		},
		post: getOccasionFromResponse
	}, {
		alias: "trash",
		method: "DELETE",

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
		post: getOccasionFromResponse
	}, {
		alias: "close",
		method: "PUT",

		/**
		 * Modify the request parameters before performing the call
		 *
		 * @param {Object} request Request construct
		 * @param {Object} payload Parameters for the request
		 * @return {Object} Request construct
		 */
		pre: function( request, payload ) {

			// Point to a single occasion's close action
			request.url = request.baseUrl.concat("/", payload.id, "/close");

			return request;
		},
		post: getOccasionFromResponse
	}, {
		alias: "reopen",
		method: "PUT",

		/**
		 * Modify the request parameters before performing the call
		 *
		 * @param {Object} request Request construct
		 * @param {Object} payload Parameters for the request
		 * @return {Object} Request construct
		 */
		pre: function( request, payload ) {

			// Point to a single occasion's reopen action
			request.url = request.baseUrl.concat("/", payload.id, "/reopen");

			return request;
		},
		post: getOccasionFromResponse
	}];
	
	return {
		endpoints: endpoints
	};
});