/**
 * Authentication and authorization Service Functions
 *
 * @package Incassoos
 * @subpackage App/Services
 */
define([
	"vue",
	"q",
	"lodash",
	"util",
	"./storage-service",
	"./install-service"
], function( Vue, Q, _, util, storageService, installService ) {
	/**
	 * Holds the storage for the service
	 *
	 * @type {Object}
	 */
	var storage = storageService.create("service/auth"),

	/**
	 * Define listener construct for the service
	 *
	 * Events triggered in this domain are:
	 *  - add (userid, data)
	 *  - update (userid, data)
	 *  - remove (userid)
	 *  - users (users)
	 *  - active (userid)
	 *  - inactive (userid)
	 *
	 * @type {Object}
	 */
	listeners = util.createListeners("service/auth"),

	/**
	 * Define the required length of the user's pin
	 *
	 * @type {Number}
	 */
	requiredPinLength = 5,

	/**
	 * Holds the currently active user
	 *
	 * This data is kept synchronous to prevent dealing with asynchronous
	 * logic in Vue component methods.
	 *
	 * @type {String}
	 */
	_activeUserId = "",

	/**
	 * Holds the previously active user
	 *
	 * This data is kept synchronous to prevent dealing with asynchronous
	 * logic in Vue component methods.
	 *
	 * @type {String}
	 */
	_prevActiveUserId = "",

	/**
	 * Holds the registered users
	 *
	 * This data is kept synchronous to prevent dealing with asynchronous
	 * logic in Vue component methods.
	 *
	 * @type {Object}
	 */
	registeredUsers = {},

	/**
	 * Holds a list of pin values that are considered invalid
	 *
	 * @type {Array}
	 */
	invalidPins = (function() {
		var list = [];

		// Range of increments by 1 (01234)
		list.push.apply(list, [0,1,2,3,4,5,6,7,8,9].filter( function( i ) {
			return i + requiredPinLength - 1 < 10;
		}).map( function( i ) {
			return i.toString().repeat(requiredPinLength).split("").map( function( i, ix ) {
				return parseInt(i) + ix;
			}).join("");
		}));

		// Consisting of only identical numbers (00000-99999)
		list.push.apply(list, [0,1,2,3,4,5,6,7,8,9].map( function( i ) {
			return i.toString().repeat(requiredPinLength);
		}));

		return list;
	})(),

	/**
	 * Initialization of the authorization service
	 *
	 * @return {Promise} Is the service initialized?
	 */
	init = function() {
		/**
		 * Update the user reference when adding or updating a user
		 */
		listeners.on(["add", "update"], function authServiceOnAddUpdateUser( id, data ) {
			registeredUsers[id] = data;
		});

		/**
		 * Update the user reference when removing a user
		 */
		listeners.on("remove", function authServiceOnRemoveUser( id ) {
			registeredUsers = registeredUsers.filter( function( i ) {
				return i.id !== id;
			});
		});

		/**
		 * Trigger user list updates when any change in the user list was made
		 */
		listeners.on(["add", "update", "remove", "active", "inactive"], function authServiceOnChangeUserlist() {
			/**
			 * Trigger event listeners for any change in the users list.
			 *
			 * @param {Array} users List of users.
			 */
			listeners.trigger("users", getUsers());
		});

		return Q.resolve();
	},

	/**
	 * Definition of the service's store logic
	 *
	 * @return {Object} Service store methods
	 */
	storeDefinition = function() {
		return {
			/**
			 * Modify service related properties in the main store's state
			 *
			 * @param  {Object} state Store state
			 * @return {Void}
			 */
			defineStoreState: function( state ) {
				state.authIsLoggedIn = false;
			},

			/**
			 * Modify service related methods in the main store's mutations
			 *
			 * @param  {Object} mutations Store mutations
			 * @return {Void}
			 */
			defineStoreMutations: function( mutations ) {
				/**
				 * Update reactive property for authService's `isLoggedIn` property
				 *
				 * @param {Boolean} payload Is a user logged-in?
				 * @return {Void}
				 */
				mutations.authSetIsLoggedIn = function( state, payload ) {
					state.authIsLoggedIn = !! payload;
				};
			},

			/**
			 * Trigger service related methods in the main store's context
			 *
			 * @param  {Object} context Store context
			 * @return {Void}
			 */
			defineStoreContextUsage: function( context ) {
				/**
				 * When switching the active user, update the main store's `isLoggedIn` data
				 *
				 * @return {Void}
				 */
				listeners.on("active", function authServiceOnActiveUser() {

					// Mutate the reactive `isLoggedIn` flag
					context.commit("authSetIsLoggedIn", true);
				});

				/**
				 * When removing the active user, update the main store's `isLoggedIn` data
				 *
				 * @return {Void}
				 */
				listeners.on("inactive", function authServiceOnInactiveUser() {

					// Mutate the reactive `isLoggedIn` flag
					context.commit("authSetIsLoggedIn", false);
				});
			}
		};
	},

	/**
	 * Return the (currently active) authentication data
	 *
	 * @param  {String} id Optional. Auth identifier. Defaults to the currently active user.
	 * @return {Promise} Auth data or False when not registered
	 */
	getAuthData = function( id ) {
		id = id || _activeUserId;

		// Find authentication by id
		return storage.toArray().then( function( items ) {
			return items.find( function( i ) {
				return i.id === id;
			});
		});
	},

	/**
	 * Return the (currently active) user token
	 *
	 * @internal
	 *
	 * @param  {String} id Optional. Auth identifier. Defaults to the currently active user.
	 * @return {Promise} Auth token data or False when not registered
	 */
	getAuthToken = function( id ) {
		return getAuthData(id).then( function( data ) {
			return data && data.token;
		});
	},

	/**
	 * Store a user's authentication data in the registry
	 *
	 * @param  {String} id Auth identifier
	 * @param  {Object} data Authentication data
	 * @return {Promise} Was the action successfull?
	 */
	_saveAuth = function( id, data ) {
		var dfd = Q.defer();

		// Add identifier to the auth data
		data = _.isObject(data) ? data : { token: data };
		data.id = id;
		data.userName = data.userName || id;

		// Require token data
		if (! data.token) {
			dfd.reject("AuthService: token data is missing for id ".concat(id, "."));

		// Get all registered users
		} else {
			storage.get().then( function( users ) {
				var i;

				// Report when token is already in use
				for (i in users) {
					if (i !== id.toString() && users[i].token === data.token) {
						dfd.reject("AuthService: cannot use the same token (".concat(id, ") for multiple users."));
						return;
					}
				}

				// Save (or update) user data
				storage.save(id, data).then( function() {
					/**
					 * Trigger event listeners for an updated or added user.
					 *
					 * @param {String} id Auth identifier
					 * @param {Object} data User data
					 */
					listeners.trigger(!! users[id] ? "update" : "add", id, data)
						.then(dfd.resolve.bind(dfd, id))
						.catch(dfd.reject);
				});
			});
		}

		return dfd.promise;
	},

	/**
	 * Save user data
	 *
	 * @param {String} id Optional. Auth identifier. Defaults to the currently active id.
	 * @param {Object} data User or authentication data
	 * @return {Promise} Was the action successfull?
	 */
	saveUser = function( id, data ) {
		if (! data) {
			data = id;
			id = data.id || _activeUserId;
		}

		return getAuthData(id).then( function( userData ) {
			// Save with parsed existing data
			return _saveAuth(id, _.defaults(data, userData || {}));
		});
	},

	/**
	 * When running locally, set active local user
	 *
	 * @return {Promise} Was the action successfull?
	 */
	setActiveLocalUser = function() {
		var dfd = Q.defer();

		// When running locally setup user
		if (installService.isLocal) {
			saveUserAndSetActive(incassoosL10n.auth).then( function() {

				// Load initial users
				return storage.get().then( function( users ) {

					// Store local reference
					registeredUsers = users;

					// We're done here
					dfd.resolve();
				});
			}).catch(dfd.reject);
		} else {
			dfd.resolve();
		}

		return dfd.promise;
	},

	/**
	 * Save user data and make this the active user
	 *
	 * @param {String} id See {@see `saveUser()`}
	 * @param {Object} data See {@see `saveUser()`}
	 * @return {Promise} Was the action successfull?
	 */
	saveUserAndSetActive = function( id, data ) {
		return saveUser(id, data).then( function( id ) {
			return setActiveUser(id);
		});
	},

	/**
	 * Remove a user's auth data from the registry
	 *
	 * @param  {String} id Auth identifier
	 * @return {Promise} Was the action successfull?
	 */
	removeUser = function( id ) {
		return storage.remove(id).then( function() {

			// When it was, remove the currently active user
			if (id === _activeUserId) {
				unsetActiveUser(id);
			}

			// When it was, remove the previously active user
			if (id === _prevActiveUserId) {
				_prevActiveUserId = "";
			}

			/**
			 * Trigger event listeners for a removed user.
			 *
			 * @param {String} id Auth identifier
			 */
			return listeners.trigger("remove", id);
		});
	},

	/**
	 * Remove all auth data
	 *
	 * @param {Boolean} quick Optional. Whether to remove data without triggering per-user event listeners
	 * @return {Promise} Is all auth data removed?
	 */
	clear = function( quick ) {
		return Q.Promisify( function() {
			if (quick) {
				storage.clear();
				registeredUsers = [];
			} else {
				return storage.get().then( function( users ) {
					for (var i in users) {

						// Remove each users separately
						removeUser(users[i].id);
					}
				});
			}
		}).then(unsetActiveUser).catch(console.error);
	},

	/**
	 * Return all registered users
	 *
	 * This function is made synchronous to prevent hassling with asynchronous
	 * logic in Vue component methods.
	 *
	 * @param {String|Boolean} id Optional. User identifier for returning a single user
	 *                             or True for returning the active user.
	 * @return {Array|Object} User data
	 */
	getUsers = function( id ) {
		var i, list = [];

		// Create array from object
		for (i in registeredUsers) {
			list.push(registeredUsers[i]);
		}

		// Limit return data
		list = list.map( function( j ) {
			return {
				id: j.id,
				userName: j.userName,
				pin: j.pin,
				_active: (_activeUserId === j.id),
				_prevActive : (_prevActiveUserId === j.id)
			};
		});

		// Get single user's data
		if (id) {
			list = list.find( function( j ) {
				return (true === id) ? j._active : j.id === id;
			});
		}

		return list;
	},

	/**
	 * Return the active user
	 *
	 * @return {String} Auth identifier or False when not registered
	 */
	getActiveUser = function() {
		return _activeUserId;
	},

	/**
	 * Return the previously active user
	 *
	 * @return {String} Auth identifier or False when not registered
	 */
	getPrevActiveUser = function() {
		return _prevActiveUserId;
	},

	/**
	 * Return whether there is an active user
	 *
	 * @return {Boolean} Is any user logged-in?
	 */
	isUserLoggedIn = function() {
		return !! getActiveUser();
	},

	/**
	 * Return the active user
	 *
	 * @param {String} id Auth identifier
	 * @return {Boolean} Is this the logged-in user?
	 */
	isActiveUser = function( id ) {
		return id === _activeUserId;
	},

	/**
	 * Return whether this is a single-user installation
	 *
	 * @return {Boolean} Is this a single-user installation?
	 */
	isSingleUser = function() {
		return installService.isLocal;
	},

	/**
	 * Return whether this is a multi-user installation
	 *
	 * @return {Boolean} Is this a multi-user installation?
	 */
	isMultiUser = function() {
		return ! isSingleUser();
	},

	/**
	 * Define the active user
	 *
	 * @param  {String} id Auth identifier
	 * @return {Promise} Was the action successfull?
	 */
	setActiveUser = function( id ) {

		// Check if the user is registered
		return getAuthData(id).then( function( data ) {
			if (data) {

				// Set the active user
				_activeUserId = id;

				/**
				 * Trigger event listeners for setting the active user
				 *
				 * @param {string} id Auth identifier
				 */
				return listeners.trigger("active", id);
			}
		});
	},

	/**
	 * Remove the definition of the active user
	 *
	 * @return {Promise} Was the action successfull?
	 */
	unsetActiveUser = function() {
		var id = _activeUserId;

		// Unset the active user
		_activeUserId = "";

		// Set the previously active user
		_prevActiveUserId = id;

		/**
		 * Trigger event listeners for unsetting the active user
		 *
		 * @param {string} id Auth identifier
		 */
		return listeners.trigger("inactive", id);
	},

	/**
	 * Define authorization data on the request's headers
	 *
	 * @param {Object} request Request parameters
	 * @param {String} id Optional. Auth identifier. Defaults to the currently active id.
	 * @return {Promise} Request headers
	 */
	setAuthHeaders = function( request, id ) {

		// Get the registered user's token
		return getAuthToken(id).then( function( token ) {
			request.headers = request.headers || {};

			// When running locally use nonce token
			if (installService.isLocal) {
				// TODO: works with multi-user?
				request.headers["X-WP-Nonce"] = token;

			// Otherwise use JWT with Bearer token
			// When a token is found for the user, add the Authorization header
			} else if (token) {
				request.headers.Authorization = "Bearer ".concat(token);
			}

			return request;
		});
	},

	/* User Pin */

	/**
	 * Save a user's pin
	 *
	 * @param  {String} pin Pin number
	 * @param  {String} id Optional. Auth identifier. Defaults to the currently active id.
	 * @return {Promise} Action success
	 */
	savePin = function( pin, id ) {
		return saveUser(id || _activeUserId, {
			pin: util.hash(pin)
		});
	},

	/**
	 * Return whether this is a valid pin
	 *
	 * @param  {String}  pin Collection of numbers
	 * @return {Boolean} Is the pin valid?
	 */
	isValidPin = function( pin ) {
		return (requiredPinLength === pin.length) && -1 === invalidPins.indexOf(pin);
	},

	/**
	 * Return whether the user has a pin
	 *
	 * @param  {String} id Optional. Auth identifier. Defaults to the current user.
	 * @return {Boolean} Does the user have a pin?
	 */
	hasPin = function( id ) {
		var user = getUsers(id || _activeUserId);

		return user ? !! user.pin : true;
	},

	/**
	 * Return whether a user's pin matches the provided one
	 *
	 * @param  {String} pin Collection of numbers
	 * @param  {String} id Optional. Auth identifier. Defaults to the current user.
	 * @return {Boolean} Does the pin match?
	 */
	matchPin = function( pin, id ) {
		var user = getUsers(id || _activeUserId);

		return user && user.pin && (util.hash(pin) === user.pin);
	},

	/**
	 * Remove a user's pin
	 *
	 * @param  {String} id Optional. Auth identifier. Defaults to the current user.
	 * @return {Promise} Action success
	 */
	removePin = function( id ) {
		return saveUser(id || _activeUserId, {
			pin: null
		});
	},

	/**
	 * Holds the capabilities for the roles
	 *
	 * Available roles are:
	 *  - inc_collector
	 *  - inc_supervisor
	 *  - inc_registrant
	 *
	 * An `*` indicates access for all user roles.
	 *
	 * @type {Object}
	 */
	capabilities = {
		"create_products": ["inc_supervisor"],
		"edit_products": ["inc_supervisor"],
		"delete_products": ["inc_supervisor"],
		"edit_consumers": ["inc_collector", "inc_supervisor"],
		"edit_consumers:show": ["*"]
	},

	/* User Capabilities */

	/**
	 * Return whether the current user has the capability
	 *
	 * @param  {String} cap Capability name
	 * @return {Boolean} Can the current user do that?
	 */
	userCan = function( cap ) {
		var roles = registeredUsers[_activeUserId] && registeredUsers[_activeUserId].roles || [];

		// TODO: for now allow all
		return true;

		// return isUserLoggedIn() && _.some(roles.concat(["*"]), function( i ) {
		// 	return !! (capabilities[cap] && -1 !== capabilities[cap].indexOf(i));
		// });
	};

	/**
	 * Reactive listener for whether a user is currently active
	 *
	 * @see store/main.js
	 *
	 * @return {Boolean} Is currently a user active?
	 */
	Object.defineProperty(Vue.prototype, "$isLoggedIn", {
		get: function() {
			return this.$store.state.authIsLoggedIn;
		}
	});

	return {
		init: init,
		clear: clear,
		getActiveUser: getActiveUser,
		getPrevActiveUser: getPrevActiveUser,
		getUser: getAuthData,
		getUsers: getUsers,
		hasPin: hasPin,
		isActiveUser: isActiveUser,
		isMultiUser: isMultiUser,
		isSingleUser: isSingleUser,
		isUserLoggedIn: isUserLoggedIn,
		isValidPin: isValidPin,
		matchPin: matchPin,
		on: listeners.on,
		off: listeners.off,
		removePin: removePin,
		removeUser: removeUser,
		requiredPinLength: requiredPinLength,
		savePin: savePin,
		saveUser: saveUser,
		saveUserAndSetActive: saveUserAndSetActive,
		setActiveLocalUser: setActiveLocalUser,
		setActiveUser: setActiveUser,
		setAuthHeaders: setAuthHeaders,
		storeDefinition: storeDefinition,
		unsetActiveUser: unsetActiveUser,
		userCan: userCan
	};
});
