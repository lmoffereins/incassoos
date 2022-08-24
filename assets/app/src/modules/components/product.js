/**
 * Single Product Component
 *
 * @package Incassoos
 * @subpackage App/Components
 */
define([
	"vuex",
	"lodash",
	"fsm",
	"services",
	"settings",
	"./feedback",
	"./form/input-dropdown",
	"./form/input-price",
	"./util/close-button",
	"./../templates/product.html"
], function( Vuex, _, fsm, services, settings, feedback, inputDropdown, inputPrice, closeButton, tmpl ) {
	/**
	 * Holds a reference to the dialog service
	 *
	 * @type {Object}
	 */
	var dialogService = services.get("dialog"),

	/**
	 * Holds a reference to the shortcuts service
	 *
	 * @type {Object}
	 */
	shortcutsService = services.get("shortcuts"),

	/**
	 * Return the product category's label
	 *
	 * @param  {Number|String} categoryId Category id
	 * @return {String} Product category label
	 */
	getProductCategoryLabel = function( categoryId ) {
		return settings.product.productCategory.items && settings.product.productCategory.items[categoryId];
	},

	/**
	 * Set form fields for the product context
	 *
	 * @return {Void}
	 */
	onEnterViewProduct = function() {
		var payload = this.$store.state.products.active;

		this.title = payload.title;
		this.price = payload.price;
		this.productCategory = payload.productCategory;
		this.productCategoryLabel = getProductCategoryLabel(payload.productCategory);
	},

	/**
	 * Reset form fields after the product context
	 *
	 * @return {Void}
	 */
	onEnterSettings = function() {
		this.title = "";
		this.price = 0;
		this.productCategory = settings.product.productCategory.defaultValue;
		this.productCategoryLabel = getProductCategoryLabel(settings.product.productCategory.defaultValue);
	},

	/**
	 * Request confirmation when deleting the product
	 *
	 * @return {Void}
	 */
	onEnterDeleteProduct = function( lifecycle, payload ) {
		var self = this;

		// Request the user to confirm the action
		dialogService.confirm({
			id: "delete-product",
			content: ["Product.AreYouSureDelete", payload.title],

			/**
			 * Start product delete when the dialog is confirmed
			 *
			 * @return {Void}
			 */
			onConfirm: function() {
				self.$store.dispatch("products/delete");
			},

			/**
			 * Close the delete state when the dialog is cancelled
			 *
			 * @return {Void}
			 */
			onClose: function() {
				self.$store.dispatch("products/cancel");
			}
		});
	},

	/**
	 * Provide watcher to patch the prop on the active item
	 *
	 * @param  {String} prop Item's property name to patch
	 * @return {Function} Patcher
	 */
	watchPatch = function( prop ) {
		return _.debounce(function( dispatch, value ) {

			// Only watch when editing
			if (! this.isViewing) {

				// Patch the active item
				dispatch("patch", { [prop]: value });
			}
		}, 600);
	};

	return {
		template: tmpl,
		components: {
			closeButton: closeButton,
			feedback: feedback,
			inputPrice: inputPrice,
			inputDropdown: inputDropdown
		},
		data: function() {
			return {
				availableProductCategories: settings.product.productCategory.items,

				// Form fields
				title: "",
				price: 0,
				productCategory: settings.product.productCategory.defaultValue,
				productCategoryLabel: getProductCategoryLabel(settings.product.productCategory.defaultValue)
			};
		},
		computed: Object.assign({
			/**
			 * Return whether we're in the product viewing state
			 *
			 * @return {Boolean} Is the product being viewed?
			 */
			isViewing: function() {
				return this.$fsmIs(fsm.st.VIEW_PRODUCT);
			},

			/**
			 * Return whether we're in the product creating state
			 *
			 * @return {Boolean} Is the product being created?
			 */
			isCreating: function() {
				return this.$fsmIs(fsm.st.CREATE_PRODUCT);
			},

			/**
			 * Return the label path for the submit button
			 *
			 * @return {String} Button label
			 */
			submitLabel: function() {
				return this.isCreating ? "Common.Create" : "Common.Save";
			},

			/**
			 * Return the active product's order count
			 *
			 * @return {Number} Order count
			 */
			orderCount: function() {
				return this.orders.length;
			},

			/**
			 * Return the active product's total quantity
			 *
			 * @return {Number} Total quantity
			 */
			totalProductQuantity: function() {
				return this.orders.reduce( function( count, i ) {
					return count + i[0].quantity;
				}, 0);
			},

			/**
			 * Return the active product's total consumed value
			 *
			 * @return {Number} Total consumed value
			 */
			totalConsumedValue: function() {
				return this.orders.reduce( function( sum, i ) {
					return sum + (i[0].quantity * i[0].price);
				}, 0);
			},

			/**
			 * Return whether we have any product categories
			 *
			 * @return {Boolean} Do we have product categories?
			 */
			haveProductCategories: function() {
				return !! _.keys(this.availableProductCategories).length;
			}
		}, Vuex.mapState("products", {
			/**
			 * Return the active component title
			 *
			 * @return {String} Component title
			 */
			componentTitle: function( state ) {

				// When creating
				if (this.isCreating) {
					return "Product.CreateProduct";

				// When viewing, editing or otherwise
				} else {
					return state.active && state.active.title;
				}
			},

			/**
			 * Return whether the active product can be trashed
			 *
			 * @return {Boolean} Can the product be trashed?
			 */
			deletable: function( state ) {
				return state.active && "publish" === state.active.status;
			},

			/**
			 * Return whether the active product can be untrashed
			 *
			 * @return {Boolean} Can the product be untrashed?
			 */
			untrashable: function( state ) {
				return state.active && "trash" === state.active.status;
			}
		}), Vuex.mapGetters("products", {
			"feedback": "getFeedback",
			"submittable": "isSubmittable"
		}), Vuex.mapState("orders", {
			/**
			 * Return the active orders that contain the active product
			 *
			 * @return {Array} Orders containing the active product
			 */
			orders: function( state ) {
				var product = this.$store.state.products.active;

				return product ? state.all.map( function( i ) {
					return i.items.filter( function( j ) {
						return j.id === product.id;
					});
				}).filter( function( i ) {
					return i.length;
				}) : [];
			}
		})),
		methods: Vuex.mapActions("products", {
			/**
			 * Edit the active item
			 *
			 * Dispatch the action without returning the promise.
			 *
			 * @param  {Function} dispatch Dispatch method
			 * @return {Void}
			 */
			edit: function( dispatch ) {
				dispatch("edit");
			},

			/**
			 * Save changes for the active item
			 *
			 * Dispatch the action without returning the promise.
			 *
			 * @param  {Function} dispatch Dispatch method
			 * @return {Void}
			 */
			update: function( dispatch ) {
				dispatch("update");
			},

			/**
			 * Transition to untrash or confirm for deletion
			 *
			 * Avoid using Javascript keyword `delete` as property name.
			 *
			 * Dispatch the action without returning the promise.
			 *
			 * @param  {Function} dispatch Dispatch method
			 * @return {Void}
			 */
			maybeDelete: function( dispatch ) {
				dispatch("maybeDelete");
			},

			/**
			 * Untrash the active item
			 *
			 * Dispatch the action without returning the promise.
			 *
			 * @param  {Function} dispatch Dispatch method
			 * @return {Void}
			 */
			untrash: function( dispatch ) {
				dispatch("untrash");
			},

			/**
			 * Cancel the current state
			 *
			 * Dispatch the action without returning the promise.
			 *
			 * @param  {Function} dispatch Dispatch method
			 * @return {Void}
			 */
			cancel: function( dispatch ) {
				dispatch("cancel");
			},

			/**
			 * Close the panel
			 *
			 * Dispatch the action without returning the promise.
			 *
			 * @param  {Function} dispatch Dispatch method
			 * @return {Void}
			 */
			close: function( dispatch ) {
				dispatch("close");
			}
		}),

		watch: Vuex.mapActions("products", {
			title:           watchPatch("title"),
			price:           watchPatch("price"),
			productCategory: watchPatch("productCategory")
		}),

		/**
		 * Register listeners when the component is created
		 *
		 * @return {Void}
		 */
		created: function() {
			var self = this, i,

			/**
			 * Collection of fsm observers
			 *
			 * @type {Object}
			 */
			fsmObservers = {};
			fsmObservers[fsm.on.enter.VIEW_PRODUCT] = onEnterViewProduct;
			fsmObservers[fsm.on.after.SELECT_PRODUCT] = onEnterViewProduct;
			fsmObservers[fsm.on.enter.SETTINGS] = onEnterSettings;
			fsmObservers[fsm.on.enter.DELETE_PRODUCT] = onEnterDeleteProduct;

			// Register observers, bind the component's context
			for (i in fsmObservers) {
				this.$registerUnobservable(
					fsm.observe(i, fsmObservers[i].bind(this))
				);
			}

			// Register global keyboard event listeners
			this.$registerUnobservable(
				shortcutsService.on({
					"escape": function() {
						self.cancel();
					}
				})
			);

			// Update values in component when settings are updated
			this.$registerUnobservable(
				settings.$onUpdate( function() {
					self.availableProductCategories = settings.product.productCategory.items;
				})
			);
		},

		/**
		 * Register listeners when the component is mounted
		 *
		 * @return {Void}
		 */
		mounted: function() {

			// On initial creation, this observer is not triggered yet
			onEnterViewProduct.call(this);
		}
	};
});
