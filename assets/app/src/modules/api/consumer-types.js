/**
 * Incassoos API endpoints for Consumer Types
 *
 * @package Incassoos
 * @subpackage App/API
 */
define([
	"he",
	"lodash",
	"settings"
], function( he, _, settings ) {
	/**
	 * Get the consumer type from the request item
	 *
	 * @param  {Object} resp Response item
	 * @return {Object} Single consumer type
	 */
	var getConsumerTypeFromResponse = function( resp ) {
		var item = {
			id: resp.id,
			name: he.decode(resp.name),
			avatarUrl: resp.avatarUrl || settings.consumer.defaultAvatarUrl,
			show: true,
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
	}];

	return {
		endpoints: endpoints
	};
});
