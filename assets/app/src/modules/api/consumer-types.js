/**
 * Incassoos API endpoints for Consumer Types
 *
 * @package Incassoos
 * @subpackage App/API
 */
define([
	"lodash",
	"settings"
], function( _, settings ) {
	/**
	 * Get the consumer type from the request item
	 *
	 * @param  {Object} resp Response item
	 * @return {Object} Single consumer type
	 */
	var getConsumerTypeFromResponse = function( resp ) {
		return {
			id: resp.id,
			name: resp.name,
			avatarUrl: settings.consumer.defaultAvatarUrl,
			show: true,
			isConsumerType: true,
			group: {
				id: 0,
				name: "Consumer.TypesGroupName",
				order: 0
			},
			editable: []
		};
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
