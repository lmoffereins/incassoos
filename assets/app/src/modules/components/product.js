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
	 * Return the available product categories
	 *
	 * @return {Object} Product categories
	 */
	getAvailableProductCategories = function() {
		var items = settings.product.productCategory.items, cats = {}, i;

		for (i in items) {
			if (items.hasOwnProperty(i)) {
				cats[i] = { label: items[i] };

				// Identify hidden categories
				if (-1 !== settings.product.productCategory.hiddenItems.indexOf(parseInt(i))) {
					cats[i].icon      = "hidden";
					cats[i].iconTitle = "Product.HiddenProductCategory";
				}
			}
		}

		return cats;
	},

	/**
	 * Return the product category's attribute
	 *
	 * @param  {Number|String} categoryId Category id
	 * @return {String} Product category attribute
	 */
	getProductCategory = function( categoryId ) {
		var item = {
			id: categoryId,
			label: settings.product.productCategory.items[categoryId]
		};

		// Identify hidden category
		if (-1 !== settings.product.productCategory.hiddenItems.indexOf(parseInt(categoryId))) {
			item.icon = "hidden";
			item.iconTitle = "Product.HiddenProductCategory";
		}

		return item;
	},

	/**
	 * Set form fields for the product context
	 *
	 * @return {Void}
	 */
	onEnterViewProduct = function() {
		var payload = this.$store.state.products.active;

		this.editTitle = payload.title;
		this.title = payload.title;
		this.price = payload.price;
		this.productCategory = payload.productCategory;
		this.productCategoryItem = getProductCategory(payload.productCategory);
	},

	/**
	 * Reset form fields after the product context
	 *
	 * @return {Void}
	 */
	onEnterSettings = function() {
		this.editTitle = "";
		this.title = "";
		this.price = 0;
		this.productCategory = settings.product.productCategory.defaultValue;
		this.productCategoryItem = getProductCategory(settings.product.productCategory.defaultValue);
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
		}, 300);
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
				availableProductCategories: getAvailableProductCategories(),

				// Form fields
				editTitle: "",
				title: "",
				price: 0,
				productCategory: settings.product.productCategory.defaultValue,
				productCategoryItem: getProductCategory(settings.product.productCategory.defaultValue)
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
			 * Return whether we're in the product editing state
			 *
			 * @return {Boolean} Is the product being created?
			 */
			isEditing: function() {
				return this.$fsmIs(fsm.st.EDIT_PRODUCT);
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

				// When editing
				} else if (this.isEditing) {
					return ["Product.EditProduct", this.editTitle];

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
		}), Vuex.mapState("occasions", {
			"occasion": "active"
		})),
		methods: Object.assign({
			/**
			 * Signal to select an item
			 *
			 * @param  {String} direction Optional. Selection direction. Defaults to the next item.
			 * @return {Void}
			 */
			selectItem: function( direction ) {
				direction = direction || "next";

				// Communicate to select the product
				this.$root.$emit("product/select-".concat(direction, "-product"));
			}
		}, Vuex.mapActions("products", {
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
		})),

		watch: Vuex.mapActions("products", {
			title: watchPatch("titleRaw"),
			price: watchPatch("price"),
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
					"escape": function productTransitionCancelOnEscape() {
						self.cancel();
					},
					"home": function productTransitionSelectProductOnHome() {
						self.selectItem("first");
					},
					"left": function productTransitionSelectProductOnLeft() {
						self.selectItem("previous");
					},
					"right": function productTransitionSelectProductOnRight() {
						self.selectItem("next");
					},
					"end": function productTransitionSelectProductOnEnd() {
						self.selectItem("last");
					}
				})
			);

			// Update values in component when settings are updated
			this.$registerUnobservable(
				settings.$onUpdate( function() {
					self.availableProductCategories = getAvailableProductCategories();
				})
			);
		},

		/**
		 * Register listeners when the component is mounted
		 *
		 * @return {Void}
		 */
		mounted: function() {
			var self = this,

			/**
			 * Construct element instance for touch events
			 *
			 * @type {Hammer}
			 */
			hammer = new Hammer(this.$el),

			/**
			 * Act when the product is swiped
			 *
			 * @return {Void}
			 */
			onProductSwipe = function( event ) {

				// Swipe rtl
				if (event.deltaX < 0) {
					self.selectItem("next");

				// Swipe ltr
				} else {
					self.selectItem("previous");
				}
			};

			// Register touch event listeners
			hammer.on("swipe", onProductSwipe);
			this.$registerUnobservable( function() {
				hammer.off("swipe", onProductSwipe);
			});

			// On initial creation, this observer is not triggered yet
			onEnterViewProduct.call(this);
		}
	};
});
