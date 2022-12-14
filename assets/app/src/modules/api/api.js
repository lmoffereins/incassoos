/**
 * Incassoos API endpoint creators
 *
 * @package Incassoos
 * @subpackage App/API
 */
define([
	"q",
	"rxjs",
	"axios",
	"lodash",
	"services",
	"settings",
	"util"
], function( Q, Rx, axios, _, services, settings, util ) {
	/**
	 * Define an Axios instance with root data presets
	 *
	 * @type {Function} Axios instance
	 */
	var caller,

	/**
	 * Holds a reference to the authorization service
	 *
	 * @type {Object}
	 */
	authService = services.get("auth"),

	/**
	 * Holds a reference to the caching service
	 *
	 * @type {Object}
	 */
	cacheService = services.get("cache"),

	/**
	 * Default callback applied before the request is called
	 *
	 * @param  {Object} request Request parameters
	 * @return {Promise} Request parameters
	 */
	defaultPre = function( request ) {
		return Q.resolve(request);
	},

	/**
	 * Default callback applied after the request is called
	 *
	 * @param  {Object} data Response data
	 * @param  {Object} payload Route payload
	 * @param  {Object} response Response object
	 * @return {Promise} Parsed response
	 */
	defaultPost = function( data, payload, response ) {
		return Q.resolve(data);
	},

	/**
	 * Default callback applied when the request failed
	 *
	 * @param  {Object} error Error data
	 * @param  {Object} request Request parameters
	 * @return {Boolean} Whether the error was catched
	 */
	onError = function( error, request ) {
		console.error(error);
		return false;
	},

	/**
	 * Return the mocked response data object
	 *
	 * @param  {Mixed} data Mock response data value
	 * @return {Object} Response data object
	 */
	getMockedResponse = function( data ) {
		return Q.resolve({
			headers: {},
			data: (true !== data) && data || {},
		});
	},

	/**
	 * Define auth methods for the request
	 *
	 * @param {Boolean} enabled Whether authorization is enabled
	 * @return {Object} Authorization interaction methods
	 */
	createAuthMethods = function( enabled ) {
		return {
			/**
			 * Define authorization headers on the request
			 *
			 * @return {Promise} Request data
			 */
			setHeaders: function( request ) {
				if (enabled) {
					return authService.isUserLoggedIn() ? authService.setAuthHeaders(request) : Q.reject("Login.Error.RequiresAuthentication");
				} else {
					return Q.resolve(request);
				}
			}
		};
	},

	/**
	 * Default callback for getting a request's return value from cache
	 *
	 * @param {Object} request Request details
	 * @return {Promise} Cached value
	 */
	defaultCacheGet = function( request ) {
		return Q.reject();
	},

	/**
	 * Default callback for removing a request's value in cache
	 *
	 * @param {Object} request Request details
	 * @return {Promise} Cache was removed
	 */
	defaultCacheRemove = function( request ) {
		return Q.resolve();
	},

	/**
	 * Default callback for saving a request's return value in cache
	 *
	 * @param {Object} request Request details
	 * @param {Mixed} value Request return value
	 * @return {Promise} Request return value
	 */
	defaultCacheSave = function( request, value ) {
		return Q.resolve(value);
	},

	/**
	 * Return default cache methods
	 *
	 * @param {Boolean|Number} enabled Whether caching is enabled. When a number, used as cache expiry time in hours.
	 * @return {Object} Default cache interaction methods
	 */
	createDefaultCacheMethods = function( enabled ) {
		var options = {
			expires: enabled
		},

		/**
		 * Return the cached endpoint value
		 *
		 * @param {Object} request Request details
		 * @return {Promise} Cached value
		 */
		get = function( request ) {
			var key = cacheService.getCacheKeyForRequest(request);

			return enabled && ! request.clearCache ? cacheService.get(key) : defaultCacheGet(request);
		},

		/**
		 * Remove the cached endpoint value
		 *
		 * @param {Object} request Request details
		 * @return {Promise} Cache was removed
		 */
		remove = function( request ) {
			var key = cacheService.getCacheKeyForRequest(request);

			return enabled ? cacheService.remove(key) : defaultCacheRemove(request);
		},

		/**
		 * Save the cached endpoint value
		 *
		 * @param {Object} request Request details
		 * @param {Mixed} value Request return value
		 * @return {Promise} Request return value
		 */
		save = function( request, value ) {
			var key = cacheService.getCacheKeyForRequest(request);

			return enabled ? cacheService.save(key, value, options) : defaultCacheSave(request, value);
		};

		return {
			get: get,
			remove: remove,
			save: save
		}
	},

	/**
	 * Define cache functions for the endpoint
	 *
	 * @param {Boolean|Number|Object} enabledOrCacheMethods Whether caching is enabled. When a number,
	 *               used as cache expiry time in hours. When an object, used as the caching techniques.
	 * @return {Object} Cache interaction methods
	 */
	createCacheMethods = function( enabledOrCacheMethods ) {

		// Use default cache methods
		if ("boolean" === typeof enabledOrCacheMethods || "number" === typeof enabledOrCacheMethods) {
			return createDefaultCacheMethods(enabledOrCacheMethods);

		// Use custom cache methods
		} else {
			return Object.assign({
				get: defaultCacheGet,
				remove: defaultCacheRemove,
				save: defaultCacheSave
			}, enabledOrCacheMethods);
		}
	},

	/**
	 * Holds the default subscription callbacks
	 *
	 * @type {Object}
	 */
	defaultSubscription = {
		next: _.noop,
		error: _.noop,
		complete: _.noop,
		backgroundProcess: true
	},

	/**
	 * Execute a stream of paginated requests using the Observable pattern
	 *
	 * @param  {Object} request Request data
	 * @param  {Object} options Endpoint options
	 * @param  {Object} payload Optional. Request parameters
	 * @param  {Object} subscription Optional. Request subscription callbacks.
	 * @return {Promise} Request completed
	 */
	streamPaginatedRequest = function( request, options, payload, subscription ) {
		var dfd = Q.defer(), streamPagination$, lastResult;

		payload = payload || {};

		// Parse subscription callbacks
		subscription = _.defaults(subscription || {}, defaultSubscription);

		/**
		 * Setup pagination stream
		 *
		 * The initial stream just emits page numbers. The page numbers are then used
		 * to setup and execute the paginated request. The response of the request is
		 * evaluated to assess whether a new page number should be emitted in the stream.
		 *
		 * The response is further more parsed for post processing, then reduced to a
		 * single list of response data all with the previous responses, and finally
		 * saved into cache, before it is handed to the subscriber(s).
		 */
		streamPagination$ = (new Rx.Subject()).pipe(

			// Promises: execute page request
			Rx.flatMap( function( page ) {

				// Set the pagination parameters
				request.params.page = page;

				// Make Observable for the page request
				return Rx.from(caller(request));
			}),

			// Promises: post processing. Trigger new page request if available.
			Rx.flatMap( function( response ) {
				var page = response.config.params.page,
				    totalPages = response.headers["x-wp-totalpages"] || 1;

				console.log("scan: page " + page + " of " + totalPages + "...");

				// Continue pagination
				if (page < totalPages) {
					streamPagination$.next(page + 1);

				// Pagination is done
				} else {
					streamPagination$.complete();
				}

				// Apply post processing, make Observable for the response
				return Rx.from(request.post(response.data, payload, response));
			}),

			// Combine data of all pages. Merge data with previous data
			Rx.scan( function( retval, data ) {
				return [].concat(retval, data);
			}, []),

			// Update the last result value
			Rx.tap( function( data ) {
				lastResult = data;
			}),

			// Promises: store data in cache
			Rx.flatMap( function( data ) {

				// Make Observable for the cache action
				return Rx.from(options.cache.save(request, data));
			})
		);

		// Register subscription callbacks
		streamPagination$.subscribe(
			// Act when the stream emits another result.
			// The result contains the accumulated data of the paged requests.
			function onSuccess( result ) {
				console.log("paginatedRequest/" + request.url + "/received", result.length);
				subscription.next(result);

				// Resolve the request early when allowing the process to run in the background
				if (subscription.backgroundProcess) {
					dfd.resolve(result);
				}
			},

			// Act when the stream emits an error
			function onError( error ) {
				console.error("paginatedRequest/" + request.url + "/error", error);
				subscription.error(error);
				dfd.reject(error);
			},

			// Act when the stream is completed
			function onComplete() {
				console.log("paginatedRequest/" + request.url + "/complete");
				subscription.complete();
				dfd.resolve(lastResult);
			}
		);

		// Start request with the first page
		streamPagination$.next(1);

		return dfd.promise;
	},

	/**
	 * Create an API callback that will perform a paginated request
	 *
	 * @param  {Object} request Request data
	 * @param  {Object} options Endpoint options
	 * @return {Function} API Callback
	 */
	createPaginatedRequest = function( request, options ) {
		/**
		 * Perform the paginated request
		 *
		 * This method uses the Observable-based request pattern.
		 *
		 * This method supports pagination through the Observable pattern. In case of
		 * pagination, pages are requested in sequence. The subscription methods allow
		 * for parsing cumulative results while pagination continues in the background.
		 *
		 * The request's promise will be resolved when the pagination is completed.
		 *
		 * @param  {Object} payload Optional. Request parameters.
		 * @param  {Object} subscription Optional. Request subscription callbacks.
		 * @return {Promise} Request completed
		 */
		return function( payload, subscription ) {
			var dfd = Q.defer();

			// Bail when caller instance isn't setup yet
			if ("undefined" === typeof caller) {
				return getMockedResponse();
			}

			// Accept subscription without payload
			if ("function" === typeof payload) {
				subscription = payload;
				payload = {};
			}

			payload = payload || {};

			// Allow single subscription callback
			if ("function" === typeof subscription) {
				subscription = {
					next: subscription
				};
			}

			// Parse subscription callbacks
			subscription = _.defaults(subscription || {}, defaultSubscription);

			// Reset request attributes
			request.headers = {};
			request.params = {};
			request.data = {};
			request.clearCache = !! payload.clearCache;

			// Setup request. Maybe apply authorization
			options.auth.setHeaders(request).then( function() {

				// Parse pre-call options
				return request.pre(request, payload).then( function() {

					// Maybe get value from cache
					return options.cache.get(request).then( function( data ) {

						// Pretend to complete the not-created stream
						subscription.complete(data);

						// Short-circuit request with cached data
						dfd.resolve(data);

					// Start new paginated request
					}).catch( function( data ) {

						// // While paginated query starts, use invalidated cached data first
						// if ("undefined" !== typeof data) {
						// 	dfd.resolve(data);
						// }

						return streamPaginatedRequest(request, options, payload, subscription).then(dfd.resolve);
					});
				});
			}).catch(dfd.reject);

			// Handle response errors
			return dfd.promise.catch( function( error ) {
				return options.onError(error, request) || Q.reject(error);
			});
		};
	},

	/**
	 * Create an API callback that will perform a request
	 *
	 * @param  {Object} request Request data
	 * @param  {Object} options Endpoint options
	 * @return {Function} API Callback
	 */
	createRequest = function( request, options ) {
		/**
		 * Perform the request
		 *
		 * This method uses the linear Promise-based request pattern.
		 *
		 * @param  {Object} payload Optional. Request parameters.
		 * @return {Promise} Request success
		 */
		return function( payload ) {
			payload = payload || {};

			// Bail when caller instance isn't setup yet
			if ("undefined" === typeof caller) {
				return getMockedResponse();
			}

			// Reset request attributes
			request.headers = {};
			request.params = {};
			request.data = {};
			request.clearCache = !! payload.clearCache;

			// Maybe apply authorization
			return options.auth.setHeaders(request).then( function() {

				// Parse pre-call options
				return request.pre(request, payload).then( function() {

					// Maybe get value from cache or start new request
					return options.cache.get(request).catch( function() {

						// Execute request
						return caller(request).then( function( response ) {

							// Apply post processing
							return request.post(response.data, payload, response).then( function( data ) {

								// Store data in cache
								return options.cache.save(request, data);
							});
						});
					});

				// Handle response errors
				}).catch( function( error ) {
					return options.onError(error, request) || Q.reject(error);
				});
			});
		};
	},

	/**
	 * Create an API callback that will perform the request
	 *
	 * @param  {String} route Route endpoint url
	 * @param  {Object} options Endpoint options
	 * @return {Function} API Callback
	 */
	createCallback = function( route, options ) {
		var request = {
		    method: options.method.toLowerCase(),
		    baseUrl: route,
		    url: route,
		    params: {},
		    data: {}, // Request body is called `data` in Axios
		    headers: {},

		    /**
		     * Wrap the route's pre-request method in a Promise
		     *
		     * NB: Axios's `transformRequest` is only used for POST/PUT/PATCH methods.
		     * NB: Axios's `transformRequest` cannot handle Promises (?)
		     *
		     * @param  {Object} request Request data
		     * @return {Promise} Request data
		     */
		    pre: function( request, payload ) {
		    	return Q.Promisify(options.pre(request, payload));
		    },

		    /**
		     * Wrap the route's post-request method in a Promise
		     *
		     * NB: Axios's `transformResponse` cannot handle Promises (?)
		     *
		     * @param  {Mixed} data Response data
		     * @param  {Object} payload Route payload
		     * @param  {Object} response Response object
		     * @return {Promise} Response data
		     */
		    post: function( data, payload, response ) {
		    	return Q.Promisify(options.post(data, payload, response));
		    }
		}, callback;

		// Setup authorization methods
		options.auth  = createAuthMethods(options.requireAuth);

		// Setup cache methods
		options.cache = createCacheMethods(options.enableCache);

		// Setup the right request callback. GET requests may use pagination.
		callback = options.usePagination ? createPaginatedRequest(request, options) : createRequest(request, options);

		return callback;
	},

	/**
	 * Create endpoint handlers for the route
	 *
	 * @param  {String} route Route path
	 * @param  {Array} endpoints Set of endpoints to setup
	 * @return {Object} Endpoint handlers
	 */
	createEndpoints = function( route, endpoints ) {
		var api = {}, i, options, alias, endpoint;

		// Define the route
		route = settings.api.namespace.concat("/", (settings.api.routes[route] || route).replace(/^\//, ''));

		// Parse each endpoint on the route
		for (i in endpoints) {

			// Parse defaults for endpoint data
			options = _.defaults(endpoints[i], {
				method: "GET",
				mock: false,
				pre: defaultPre,
				post: defaultPost,
				onError: onError,
				requireAuth: !! (endpoints[i].method && "GET" !== endpoints[i].method),
				enableCache: false,
				usePagination: false,
				subroute: ""
			});

			// Define endpoint alias
			alias = options.alias || util.camelCase(options.method);

			// Define specific endpoint with subroute
			endpoint = route.concat(options.subroute);

			/**
			 * Create the API callback for this route's endpoint
			 *
			 * @return {Promise} Callback result
			 */
			api[alias] = createCallback(endpoint, options);
		}

		return api;
	},

	/**
	 * Setup Axios instance for the main caller construct
	 *
	 * @return {Void}
	 */
	setupCallerInstance = function() {

		// Bail when the root path is unavailable
		if ("undefined" === typeof settings.api.root) {
			return;
		}

		// Renew the Axios instance
		caller = axios.create({
			baseURL: settings.api.root
		});

		/**
		 * Define default response handlers for logging
		 *
		 * TODO: does this overwrite previous response handlers when settings are updated?
		 */
		caller.interceptors.response.use(
			/**
			 * Intercept the response when the request was successfull
			 *
			 * @param  {Mixed} resp Response data
			 * @return {Mixed} Response data
			 */
			function( resp ) {

				// Log API response
				services.get("log").log("API", "Received response data from " + resp.request.responseURL);

				// Doing it wrong in WP
				if (resp.headers.hasOwnProperty("x-wp-doingitwrong")) {
					services.get("feedback").add("x-wp-doingitwrong", resp.headers["x-wp-doingitwrong"]);
				}

				return resp;
			},

			/**
			 * Intercept the error when the request failed
			 *
			 * @param  {Object} resp Response error
			 * @return {Object} Response error
			 */
			function( error ) {
				var wpError;

				// Dissect the received WP_Error object
				if (error.response && error.response.data) {
					wpError = {
						data: {
							code: error.response.data.code,
							args: error.response.data.message,
							url: error.request.responseURL
						}
					};
				}

				return Q.reject(wpError || error);
			}
		);
	};

	// On startup, setup the caller instance
	setupCallerInstance();

	// When the settings are updated, refresh the caller instance
	settings.$onUpdate(setupCallerInstance);

	return createEndpoints;
});
