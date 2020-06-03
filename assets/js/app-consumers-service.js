/**
 * Angular Online Status service module
 *
 * @since 1.0.0
 *
 * global angular, _, incassoosL10n
 */
(function( angular, _, inc ) {
	var l10n = inc.l10n || {},
		settings = inc.settings || {};

	/**
	 * Setup Incassoos Consumers service
	 */
	angular.module( 'incConsumerService', [] )
		.factory( '$incConsumers', [ '$q', '$http', function( $q, $http ) {
			var consumers = [], currentConsumer;

			/**
			 * Redefine collection's `push` method
			 *
			 * @return {Object} Last added element
			 */
			consumers.push = function() {
				var items = [].slice.call( arguments ).filter( function( i ) {
					// Ignore items that already exist by the same id
					return -1 === consumers.findIndex( function( c ) {
						return c.id === i.id;
					});
				});

				Array.prototype.push.apply( this, _.map( items, parseConsumer ) );

				// Return the last added element
				return consumers[ consumers.length - 1 ];
			};

			/**
			 * Define collection's push method for array of objects
			 *
			 * @param  {Arrat}  array List of objects
			 * @return {Object}       Result of the `push` method
			 */
			consumers.pushArray = function( array ) {
				return this.push.apply( this, array );
			};

			/**
			 * Return a parsed consumer data object
			 *
			 * @param  {Object} data Initial consumer data
			 * @return {Object}      Parsed consumer data
			 */
			function parseConsumer( data ) {
				return {
					id: data.id || 0,
					name: data.name || l10n.unknownConsumer,
					avatar: data.avatar || settings.defaultAvatar,
					show: 'undefined' !== typeof data.show ? !! data.show : false,
					group: data.group || false,
					limit: data.limit > 0 ? data.limit : '',
					isEditable: 'undefined' !== typeof data.isEditable ? !! data.isEditable : false
				};
			}

			/**
			 * Return the default HTTP headers
			 *
			 * @return {Object} Default HTTP headers object
			 */
			function getHttpHeaders() {
				return {
					'X-WP-Nonce': settings.urls.nonce
				};
			}

			/**
			 * Fetch and return the app's consumers
			 *
			 * @return {Promise} Resolves to the Array of consumer objects
			 */
			function getAll() {
				if ( _.isEmpty( consumers ) ) {

					// Parse consumer types
					_.each( settings.consumerTypes, function( type, index ) {
						consumers.push({
							id: index,
							name: type,
							isEditable: false
						});
					});

					return $http.get( settings.urls.consumers, {
						params: {
							avatar_size: 58
						},
						headers: getHttpHeaders()
					}).then( function( resp ) {
						if ( ! resp.data || ! resp.data.length ) {
							return [];
						}

						consumers.pushArray( resp.data );

						return consumers;
					});

				} else {
					return $q.when( consumers );
				}
			}

			/**
			 * Return a single consumer object
			 *
			 * @param  {Mixed}   val  Property value to get the consumer by
			 * @param  {String}  prop Optional. Property name. Default to 'id'
			 * @return {Promise}      Resolves with consumer object
			 */
			function getOne( val, prop ) {
				prop = prop || 'id';

				return getAll().then( function( all ) {
					var i = all.findIndex( function( c ) {
						return c[ prop ] === val;
					});

					return -1 !== i ? all[ i ] : undefined;
				});
			}

			/**
			 * Return the current consumer object
			 *
			 * @return {Promise} Resolves with current consumer data
			 */
			function getCurrent() {
				return getOne( currentConsumer );
			}

			/**
			 * Select the current consumer by a value
			 *
			 * @param  {Mixed}   val  Property value to get the consumer by
			 * @param  {String}  prop Optional. Property name. Default to 'id'
			 * @return {Promise}      Resolves with current consumer id
			 */
			function selectCurrent( val, prop ) {
				return getOne( val, prop ).then( function( c ) {
					c && (currentConsumer = c.id);
					return currentConsumer;
				});
			}

			/**
			 * Deselect the current consumer
			 *
			 * @return {void}
			 */
			function cancelCurrent() {
				currentConsumer = undefined;
			}

			/**
			 * Update a single consumer
			 *
			 * @param  {Number} id   Consumer ID
			 * @param  {Object} data Update details
			 * @return {Promise}     Resolves with update response data
			 */
			function updateConsumer( id, data ) {
				return $http.post( settings.urls.consumers + id, data, {
					headers: getHttpHeaders()
				}).then( function( resp ) {
					// do stuff to store resp into given consumer
					// consumers[]

					return getOne( id );
				});
			}

			// Expose methods
			return {
				all: getAll,
				get: getCurrent,
				select: selectCurrent,
				cancel: cancelCurrent,
				update: updateConsumer
			};
		}]);

})( angular, _, incassoosL10n );