/**
 * Products Component
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
	"util",
	"./form/input-dropdown",
	"./form/input-search",
	"./../templates/products.html"
], function( Vuex, _, fsm, services, settings, util, inputDropdown, inputSearch, tmpl ) {
	/**
	 * Holds a reference to the shortcuts service
	 *
	 * @type {Object}
	 */
	var shortcutsService = services.get("shortcuts"),

	/**
	 * When entering the `IDLE` state
	 *
	 * @return {Void}
	 */
	getProductCategories = function() {
		var categories = {};

		// When categories are used, get cats from settings and prepend all-option
		if (_.keys(settings.product.productCategory.items).length) {
			categories = Object.assign({ "0": "Product.AllCategoriesOption" }, settings.product.productCategory.items);
		}

		return categories;
	},

	/**
	 * When entering the `IDLE` state
	 *
	 * @return {Void}
	 */
	onEnterIdle = function() {

		// Reset the category filter
		this.filterProductCategory = "0";

		// Reset the search query
		this.q = "";
	},

	/**
	 * Reset trashed products toggle when entering settings mode
	 *
	 * @return {Void}
	 */
	onBeforeToggleSettings = function() {

		// Reset showing trashed products
		this.showTrashedProducts = false;
	};

	return {
		template: tmpl,
		components: {
			inputDropdown: inputDropdown,
			inputSearch: inputSearch
		},
		data: function() {
			var options = {};

			// Use custom ordering options
			if (settings.product.orderByOptions) {
				options = settings.product.orderByOptions;
			} else {
				options = {
					menuOrder: "Product.OrderByMenuOrder",
					title: "Product.OrderByTitle"
				};
			}

			return {
				focusGroupKey: 0,
				q: "",
				orderBy: _.keys(options)[0], // TODO: store in/get from user preferences?
				orderByOptions: options,
				filterProductCategory: "0",
				productCategories: getProductCategories(),
				showTrashedProducts: false
			};
		},
		computed: Object.assign({
			/**
			 * Return whether this is a state that enables creating products
			 *
			 * @return {Boolean} Can a product be created?
			 */
			creatable: function() {
				return this.$fsmSeek(fsm.tr.CREATE_PRODUCT);
			},

			/**
			 * Return the conditional select label
			 *
			 * @return {String} Select label
			 */
			selectLabel: function() {
				return function( title ) {
					return [this.$isSettings ? "Product.Select" : "Product.SelectMore", title];
				};
			},

			/**
			 * Return whether the component's search is active
			 *
			 * @return {Boolean} Is search active?
			 */
			isSearching: function() {
				return !! this.q.trim().length;
			},

			/**
			 * Return whether the component's search has no results
			 *
			 * @return {Boolean} Does search no results?
			 */
			noSearchResults: function() {
				return this.isSearching && ! this.products.length;
			},

			/**
			 * Return the conditional label for toggling trashed items
			 *
			 * @return {String} Toggle label for trashed items
			 */
			toggleTrashedItemsLabel: function() {
				return this.$isSettings && this.showTrashedProducts ? "Common.Back" : "Product.ShowTrashedItems";
			}
		}, Vuex.mapState("products", {
			/**
			 * Return ordered products, enhanced with receipt data
			 *
			 * @return {Array} Product items
			 */
			products: function( state ) {
				var self = this;

				// Get items. Filter for trashed products
				return _.orderBy(state.all.filter( function( i ) {
					return (self.$isSettings && self.showTrashedProducts ? "trash" : "publish") === i.status;

				// Filter for product category
				}).filter( function( i ) {
					return self.filterProductCategory === "0" || self.filterProductCategory === i.productCategory.toString();

				// Filter for searched items by title
				}).filter( function( i ) {
					return util.matchSearchQuery(i.title, state.searchQuery);

				// Modify item
				}).map( function( i ) {
					var product = self.$store.getters["receipt/getItemById"](i.id);

					// Add quantity data from receipt
					return util.clone(i, {
						quantity: product ? product.quantity : 0
					});
				}), this.orderBy);
			},

			/**
			 * Return whether we have trashed products
			 *
			 * @return {Boolean} Do we have trashed products?
			 */
			haveTrashedProducts: function( state ) {
				return !! state.all.filter( function( i ) {
					return "trash" === i.status;
				}).length;
			}
		})),
		methods: Object.assign({
			/**
			 * Emit that the products section should be the active section
			 *
			 * @return {Void}
			 */
			setActiveSection: function() {
				this.$emit("activeSection");
			}
		}, Vuex.mapActions("products", {
			/**
			 * Increment the item's quantity or select the active item
			 *
			 * Dispatch the action without returning the promise.
			 *
			 * @param  {Function} dispatch Dispatch method
			 * @param  {Number} payload Product id
			 * @return {Void}
			 */
			select: function( dispatch, payload ) {
				dispatch("select", payload);
			},

			/**
			 * Decrement the item's quantity
			 *
			 * Dispatch the action without returning the promise.
			 *
			 * @param  {Function} dispatch Dispatch method
			 * @param  {Number} payload Product id
			 * @return {Void}
			 */
			decrement: function( dispatch, payload ) {
				dispatch("decrement", payload);
			},

			/**
			 * Increment the item's quantity by 10
			 *
			 * @param  {Function} dispatch Dispatch method
			 * @param  {Number} payload Product id
			 * @return {Void}
			 */
			incrementByTen: function( dispatch, payload ) {
				dispatch("select", { id: payload, quantity: 10 });
			},

			/**
			 * Decrement the item's quantity by 10
			 *
			 * @param  {Function} dispatch Dispatch method
			 * @param  {Number} payload Product id
			 * @return {Void}
			 */
			decrementByTen: function( dispatch, payload ) {
				dispatch("decrement", { id: payload, quantity: 10 });
			},

			/**
			 * Start creating a new item
			 *
			 * Dispatch the action without returning the promise.
			 *
			 * @param  {Function} dispatch Dispatch method
			 * @return {Void}
			 */
			create: function( dispatch ) {
				dispatch("create");
			}
		})),
		watch: Object.assign({
			/**
			 * Close the trash can when there are no products trashed
			 *
			 * @param  {Boolean} have Do we have trashed products?
			 * @return {Void}
			 */
			haveTrashedProducts: function( have ) {
				if (! have) {
					this.showTrashedProducts = false;
				}
			},

			/**
			 * Act when the list of items changes
			 *
			 * @return {Void}
			 */
			products: function() {

				// Update focus group key
				this.focusGroupKey++;
			}
		}, Vuex.mapActions("products", {
			/**
			 * Act when the search query is changed
			 *
			 * @param  {Function} dispatch Dispatch method
			 * @return {Void}
			 */
			q: function( dispatch ) {

				// Apply search
				dispatch("search", this.q);
			},
		})),

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
			fsmObservers[fsm.on.enter.IDLE] = onEnterIdle;
			fsmObservers[fsm.on.before.TOGGLE_SETTINGS] = onBeforeToggleSettings;

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

						// Reset the search query
						self.q = "";
					}
				})
			);

			// Update values in component when settings are updated
			this.$registerUnobservable(
				settings.$onUpdate( function() {
					if (settings.product.orderByOptions) {
						var orderByValues = _.keys(settings.product.orderByOptions);

						self.orderByOptions = settings.product.orderByOptions;
						if (-1 === orderByValues.indexOf(self.orderBy)) {
							self.orderBy = orderByValues[0];
						}
					}

					self.productCategories = getProductCategories();
				})
			);
		}
	};
});
