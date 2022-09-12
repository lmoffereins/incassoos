/**
 * Sections Component
 *
 * @package Incassoos
 * @subpackage App/Components
 */
define([
	"./consumers",
	"./occasion",
	"./orders",
	"./products",
	"./receipt",
	"./../templates/sections.html"
], function( consumers, occasion, orders, products, receipt, tmpl ) {
	/**
	 * Register the callback to listen for the resize event
	 *
	 * Prefer to use `window.matchMedia()` as it only triggers on passing the threshold.
	 *
	 * @param {Function} callback Listener callback
	 * @return {Fucntion} Deregistration callback
	 */
	var addResizeListener = function( callback ) {
		var matches;

		if (window.matchMedia) {
			matches = window.matchMedia("(max-width: 580px)");
			matches.addListener(callback);
		} else {
			window.addEventListener("resize", callback);
		}

		/**
		 * Deregister the registered listener
		 *
		 * @return {Void}
		 */
		return function removeResizeListener() {
			if (matches) {
				matches.removeListener(callback);
			} else {
				window.removeEventListener("resize", callback);
			}
		}
	},

	/**
	 * Reset the active section when the window is resized above the threshold
	 *
	 * @return {Void}
	 */
	onResize = function sectionOnResize() {
		var width = document.body.clientWidth;

		// Reset the active section to products
		if (width > 580) {
			this.isOrderPanelActive && this.setProductsActive();
			this.resetFixedReceipt();
			this.$root.$emit("sections/resize-large");
		} else {
			this.setFixedReceipt();
			this.$root.$emit("sections/resize-small");
		}
	};

	return {
		template: tmpl,
		components: {
			consumers: consumers,
			occasion: occasion,
			orders: orders,
			products: products,
			receipt: receipt
		},
		data: function() {
			return {
				isPanelActive: false,
				activeSection: "products"
			};
		},
		computed: {
			/**
			 * Return whether the consumers section is active
			 *
			 * @return {Boolean} Is the consumers section active?
			 */
			isConsumersActive: function() {
				return this.activeSection === "consumers";
			},

			/**
			 * Return whether the products section is active
			 *
			 * @return {Boolean} Is the products section active?
			 */
			isProductsActive: function() {
				return this.activeSection === "products";
			},

			/**
			 * Return whether the order panel section is active
			 *
			 * @return {Boolean} Is the order panel section active?
			 */
			isOrderPanelActive: function() {
				return this.activeSection === "order-panel";
			}
		},
		methods: {
			/**
			 * Set the consumers section as the active section
			 *
			 * @return {Void}
			 */
			setConsumersActive: function() {
				this.activeSection = "consumers";
			},

			/**
			 * Set the products section as the active section
			 *
			 * @return {Void}
			 */
			setProductsActive: function() {
				this.activeSection = "products";
			},

			/**
			 * Set the order panel section as the active section
			 *
			 * @return {Void}
			 */
			setOrderPanelActive: function() {
				this.activeSection = "order-panel";
			},

			/**
			 * Set the receipt in the fixed position
			 *
			 * @return {Void}
			 */
			setFixedReceipt: function() {

				// Check if elements are rendered
				if (this.$refs.sections && this.$refs.orderPanel.contains(this.$refs.receipt)) {
					this.$refs.sections.append(this.$refs.receipt);
				}
			},

			/**
			 * Reset the receipt from the fixed position
			 *
			 * @return {Void}
			 */
			resetFixedReceipt: function() {

				// Check if elements are rendered
				if (this.$refs.orderPanel && ! this.$refs.orderPanel.contains(this.$refs.receipt)) {
					this.$refs.orderPanel.append(this.$refs.receipt);
				}
			}
		},

		/**
		 * Register listeners when the component is created
		 *
		 * @return {Void}
		 */
		created: function() {
			var self = this,

			/**
			 * Set whether the panel is active
			 *
			 * @param  {Boolean} active A panel is active
			 * @return {Void}
			 */
			onPanelActive = function ( active ) {
				self.isPanelActive = active;
			},

			/**
			 * Set the active section from the receipt
			 *
			 * @param  {String} section The section to set as active
			 * @return {Void}
			 */
			onSetActiveSection = function ( section ) {
				if (-1 !== ["consumers", "products", "order-panel"].indexOf(section)) {
					self.activeSection = section;
				}
			};

			// Subscribe to external events
			this.$root.$on("panels/is-panel-active", onPanelActive);
			this.$root.$on("receipt/set-active-section", onSetActiveSection);
			this.$registerUnobservable( function() {
				self.$root.$off("panels/is-panel-active", onPanelActive);
				self.$root.$off("receipt/set-active-section", onSetActiveSection);
			});

			// Register resize event listener
			this.$registerUnobservable(
				addResizeListener(onResize.bind(this))
			);
		},

		/**
		 * Register listeners when the component is updated
		 *
		 * @return {Void}
		 */
		updated: function() {

			// Run resize handler when the component is rendered
			onResize.apply(this);
		}
	};
});
