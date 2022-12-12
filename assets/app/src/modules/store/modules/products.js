/**
 * Products Store Module
 *
 * @package Incassoos
 * @subpackage App/Store
 */
define([
	"q",
	"lodash",
	"api",
	"fsm",
	"services",
	"settings",
	"util",
	"./util/list"
], function( Q, _, api, fsm, services, settings, util, list ) {
	/**
	 * The module state data
	 *
	 * @type {Object}
	 */
	var state = {
		all: [],
		searchQuery: "",
		active: null,
		__feedback: []
	},

	/**
	 * Holds a reference to the localization service
	 *
	 * @type {Object}
	 */
	l10nService = services.get("l10n"),

	/**
	 * Holds a reference to the authorization service
	 *
	 * @type {Object}
	 */
	authService = services.get("auth"),

	/**
	 * Holds a reference to the global feedback service
	 *
	 * @type {Object}
	 */
	feedbackService = services.get("feedback"),

	/**
	 * Holds the feedback handler
	 *
	 * @type {Object}
	 */
	feedback = util.createFeedback({
		name: "products",
		persistent: true
	}),

	/**
	 * Define sanitization function for products
	 *
	 * @type {Function} Product sanitization
	 */
	sanitize = util.sanitization({
		/**
		 * Sanitization of the `titleRaw` item property
		 *
		 * @param  {String} input Input value
		 * @return {String} Sanitized value
		 */
		titleRaw: function( input ) {
			return input.trim();
		},

		/**
		 * Sanitization of the `price` item property
		 *
		 * @param  {String} input Input value
		 * @return {Float} Sanitized value
		 */
		price: util.sanitizePrice,

		/**
		 * Sanitization of the `productCategory` item property
		 *
		 * @param  {Number|String} input Input value
		 * @return {Float} Sanitized value
		 */
		productCategory: function( input ) {
			return isNaN(input) ? 0 : parseInt(input);
		}
	}),

	/**
	 * Holds validation functions for editable properties
	 *
	 * @type {Object} Item property validators
	 */
	validators = {
		/**
		 * Validation of the `titleRaw` item property
		 *
		 * @param  {String} input Sanitized input value
		 * @return {Boolean|String} Validation success or error code
		 */
		titleRaw: function( input ) {
			var id = this.id, all, validated = true;

			// Get all existing titles
			all = state.all.filter( function( product ) {
				return product.id !== id;
			}).map( function( product ) {
				return util.removeAccents(product.titleRaw.toLowerCase());
			});

			// Value should not already be in use. Compare lowercase, without accents
			if (-1 !== all.indexOf(util.removeAccents(input.toLowerCase()))) {
				validated = "Product.Error.TitleIsAlreadyInUse";

			// Value should contain characters
			} else if (0 === input.length) {
				validated = "Product.Error.TitleIsEmpty";
			}

			return validated;
		},

		/**
		 * Validation of the `price` item property
		 *
		 * TODO: use setting to allow for negative values
		 *
		 * @param  {String} input Sanitized input value
		 * @return {Boolean|String} Validation success or error code
		 */
		price: function( input ) {
			var validated = true;

			// Value should be a number
			if (! _.isNumber(input)) {
				validated = false;

			// Value should be greater than 0
			} else if (0 >= input) {
				validated = "Product.Error.PriceShouldBeGreaterThanZero";
			}

			return validated;
		},

		/**
		 * Validation of the `productCategory` item property
		 *
		 * @param  {String} input Sanitized input value
		 * @return {Boolean|String} Validation success or error code
		 */
		productCategory: function( input ) {
			var validated = true;

			// Only required when categories are defined
			if (_.keys(settings.product.productCategory.items).length) {

				// Value should be a number larger than 0
				if (0 >= input) {
					validated = "Product.Error.NoProductCategory";

				// Value should be an available term id
				} else if (! settings.product.productCategory.items.hasOwnProperty(input)) {
					validated = "Product.Error.InvalidProductCategory";
				}
			}

			return validated;
		}
	},

	/**
	 * Holds patch comparison functions for editable properties
	 *
	 * @type {Object} Item property patch comparators
	 */
	comparators = {
		/**
		 * Comparison of the `titleRaw` item property
		 *
		 * @param  {Mixed} input Value to compare
		 * @param  {Object} item Original list item
		 * @return {Boolean} Change is detected
		 */
		titleRaw: function( input, item ) {
			return input !== item.titleRaw && input !== item.title;
		}
	},

	/**
	 * Define validation function for products
	 *
	 * @type {Function} Product validation
	 */
	validate = util.validation(validators, function( id ) {
		return state.all.find( function( i ) {
			return i.id === id;
		});
	}),

	/**
	 * The module getter methods
	 *
	 * @type {Object}
	 */
	getters = list.getters({
		/**
		 * Return a new active product
		 *
		 * @return {Void}
		 */
		getNewItem: function() {
			return {
				title: "",
				price: 0,
				productCategory: settings.product.productCategory.defaultValue
			};
		},

		/**
		 * Return whether the active item is submittable
		 *
		 * Provide app states that allow submitting.
		 *
		 * @return {Boolean} Is the product submittable?
		 */
		isSubmittable: list.isSubmittable([
			fsm.st.CREATE_PRODUCT,
			fsm.st.EDIT_PRODUCT
		])
	}, {
		validators: validators,
		comparators: comparators,
		feedback: feedback
	}),

	/**
	 * The module mutation methods
	 *
	 * @type {Object}
	 */
	mutations = list.mutations({
		/**
		 * Modify the active product
		 *
		 * @param  {Object} payload Product data
		 * @return {Void}
		 */
		patchActive: list.patchActive(sanitize, validate, feedback)
	}, {
		feedback: feedback
	}),

	/**
	 * The module action methods
	 *
	 * @type {Object}
	 */
	actions = list.actions({
		/**
		 * Setup product state listeners and setup initial collection
		 *
		 * @return {Promise} Load of products
		 */
		init: function( context ) {
			/**
			 * When selecting a product for view/edit, set the active product
			 *
			 * @param  {Object} payload Product id
			 * @return {Void}
			 */
			fsm.observe(
				fsm.on.before.SELECT_PRODUCT,
				function( lifecycle, payload ) {

					// Register new active product
					context.commit("setActive", payload);
				}
			);

			/**
			 * When going to create a new product, set the active product
			 *
			 * @return {Promise} Transition success
			 */
			fsm.observe(
				fsm.on.before.CREATE_PRODUCT,
				function() {

					// Reject transition when the user cannot create products
					if (! authService.userCan("create_products")) {
						return Q.reject("Generic.Error.NotAllowed");
					}

					// Register new active product
					context.commit("setNewActive", getters.getNewItem());
				}
			);

			/**
			 * When going to edit a product
			 *
			 * @return {Promise} Transition success
			 */
			fsm.observe(
				fsm.on.before.EDIT_ITEM,
				function( lifecycle ) {

					// Reject transition when the user cannot edit products
					// if (fsm.st.EDIT_PRODUCT === lifecycle.to && ! authService.userCan("edit_products")) {
					// 	return Q.reject("Generic.Error.NotAllowed");
					// }
				}
			);

			/**
			 * When saving a product, create or update it
			 *
			 * @param {Object} payload Product (creation) data
			 * @return {Promise} Create/Update success
			 */
			fsm.observe(
				fsm.on.before.SAVE_PRODUCT,
				function( lifecycle, payload ) {
					var creating;

					// Bail when the payload contains errors
					if (validate(payload).length) {
						return Q.reject();
					}

					// Determine whether we're creating a product
					creating = lifecycle.from === fsm.st.CREATE_PRODUCT;

					return api.products[creating ? "create" : "update"](payload).then( function( resp ) {

						// Report success message
						feedbackService.add({
							message: creating ? "Product.CreatedNewProduct" : "Product.UpdatedProduct",
							data: {
								args: [resp.title]
							}
						});

						// Register new item or update existing item in list
						if (creating) {
							context.commit("addItemToList", resp);
						} else {
							context.dispatch("setActiveItemInList", resp);
						}
					});
				}
			);

			/**
			 * When going to delete a product
			 *
			 * @return {Promise} Transition success
			 */
			fsm.observe(
				fsm.on.before.DELETE_ITEM,
				function( lifecycle ) {

					// Reject transition when the user cannot delete products
					// if (fsm.st.DELETE_PRODUCT === lifecycle.to && ! authService.userCan("delete_products")) {
					// 	return Q.reject("Generic.Error.NotAllowed");
					// }
				}
			);

			/**
			 * When deleting a product, delete it
			 *
			 * @param {Object} payload Product id
			 * @return {Promise} Delete success
			 */
			fsm.observe(
				fsm.on.before.DELETE_PRODUCT,
				function( lifecycle, payload ) {
					return api.products.trash(payload).then( function( resp ) {

						// Report success message
						feedbackService.add({
							message: "Product.DeletedProduct",
							data: {
								args: [payload.title]
							}
						});

						// Modify item in list as trashed
						context.dispatch("setActiveItemInList", resp);

					}).catch( function( error ) {
						/**
						 * Move away from delete context after the error
						 *
						 * @return {Void}
						 */
						error.onAfterError = function() {
							context.dispatch("cancel");
						}

						return Q.reject(error);
					});
				}
			);

			/**
			 * When untrashing a product, untrash it
			 *
			 * @param {Object} payload Product id
			 * @return {Promise} Untrash success
			 */
			fsm.observe(
				fsm.on.before.UNTRASH_PRODUCT,
				function( lifecycle, payload ) {

					// Reject transition when the user cannot delete products
					// if (! authService.userCan("delete_products")) {
					// 	return Q.reject("Generic.Error.NotAllowed");
					// }

					return api.products.untrash(payload).then( function( resp ) {

						// Report success message
						feedbackService.add({
							message: "Product.UntrashedProduct",
							data: {
								args: [payload.title]
							}
						});

						// Modify item in list as untrashed
						context.dispatch("setActiveItemInList", resp);
					});
				}
			);

			/**
			 * When cancelling the product edit, undo edits by restoring the
			 * active product
			 *
			 * @return {Void}
			 */
			fsm.observe([
				fsm.on.before.CANCEL_EDIT,
				fsm.on.before.CLOSE_ITEM
			], function( lifecycle ) {
					if (fsm.st.EDIT_PRODUCT === lifecycle.from) {

						// Reset active product, removing applied edits
						context.commit("setActive", { id: context.state.active.id });

						// Clear list feedback
						context.commit("clearFeedback");
					}
				}
			);

			/**
			 * When entering the SETTINGS state, clear product data
			 *
			 * @return {Void}
			 */
			fsm.observe(
				fsm.on.enter.SETTINGS,
				function() {

					// Clear active product
					context.commit("clearActive");

					// Clear the list feedback
					context.commit("clearFeedback");
				}
			);
		},

		/**
		 * Load the list of products
		 *
		 * @return {Promise} Was the data loaded?
		 */
		load: function( context ) {

			// Request products, list the items
			return api.products.get().then( function( resp ) {

				// Bail when payload is not an array
				if (! _.isArray(resp)) {
					return resp;
				}

				// Register new set of list items
				context.commit("setListItems", { items: resp });
			});
		},

		/**
		 * Transition when selecting a product OR increment the receipt's item
		 *
		 * When already in the PRODUCT state, do not close the current item first.
		 *
		 * @param {Object} payload Product id
		 * @return {Promise} Transition success
		 */
		select: function( context, payload ) {

			// When editing, prepare the product for edit
			if (fsm.is([
				fsm.st.SETTINGS,
				fsm.st.VIEW_PRODUCT
			])) {
				return fsm.do(fsm.tr.SELECT_PRODUCT, payload);

			// Increment receipt's product
			} else {

				// First open the receipt when not open already
				return Q.Promisify(fsm.is(fsm.st.RECEIPT) || fsm.do(fsm.tr.START_RECEIPT)).then( function() {
					return fsm.do(fsm.tr.INCREMENT_PRODUCT, payload);
				});
			}
		},

		/**
		 * Transition when selecting to decrement a product
		 *
		 * @param {Object} payload Product id
		 * @return {Promise} Transition success
		 */
		decrement: function( context, payload ) {

			// First open the receipt when not open already
			return Q.Promisify(fsm.is(fsm.st.RECEIPT) || fsm.do(fsm.tr.START_RECEIPT)).then( function() {
				return fsm.do(fsm.tr.DECREMENT_PRODUCT, payload);
			});
		},

		/**
		 * Transition when starting to create a product
		 *
		 * @return {Promise} Transition success
		 */
		create: function() {
			return fsm.do(fsm.tr.CREATE_PRODUCT);
		},

		/**
		 * Transition when editing the active item
		 *
		 * @return {Promise} Transition success
		 */
		edit: function() {
			return fsm.do(fsm.tr.EDIT_ITEM);
		},

		/**
		 * Patch the active item
		 *
		 * @param  {Object} payload Property patches
		 * @return {Void}
		 */
		patch: function( context, payload ) {
			context.commit("patchActive", payload);
		},

		/**
		 * Transition when saving the active item
		 *
		 * @return {Promise} Transition success
		 */
		update: function( context ) {
			return fsm.do(fsm.tr.SAVE_PRODUCT, context.state.active);
		},

		/**
		 * Transition when maybe deleting the active item
		 *
		 * @return {Promise} Transition success
		 */
		maybeDelete: function( context ) {
			return fsm.do(fsm.tr.DELETE_ITEM, context.state.active);
		},

		/**
		 * Transition when deleting the active item
		 *
		 * @return {Promise} Transition success
		 */
		delete: function( context ) {
			return fsm.do(fsm.tr.DELETE_PRODUCT, context.state.active);
		},

		/**
		 * Transition when untrashing the active item
		 *
		 * @return {Promise} Transition success
		 */
		untrash: function( context ) {
			return fsm.do(fsm.tr.UNTRASH_PRODUCT, context.state.active);
		},

		/**
		 * Transition when cancelling the product edit OR closing the active item
		 *
		 * @return {Boolean} Transition success
		 */
		cancel: function( context ) {
			return fsm.do([
				fsm.tr.CANCEL_DELETE,
				fsm.tr.CANCEL_EDIT,
				fsm.tr.CLOSE_ITEM
			]);
		},

		/**
		 * Transition when closing the current item
		 *
		 * @return {Promise} Transition success
		 */
		close: function() {
			return fsm.do(fsm.tr.CLOSE_ITEM);
		}
	}, {
		feedback: feedback
	});

	return {
		namespaced: true,
		state: state,
		getters: getters,
		mutations: mutations,
		actions: actions
	};
});
