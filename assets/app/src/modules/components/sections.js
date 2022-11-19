/**
 * Sections Component
 *
 * @package Incassoos
 * @subpackage App/Components
 */
define([
	"hammerjs",
	"util",
	"services",
	"./consumers",
	"./occasion",
	"./orders",
	"./products",
	"./receipt",
	"./../templates/sections.html"
], function( Hammer, util, services, consumers, occasion, orders, products, receipt, tmpl ) {
	/**
	 * Holds a reference to the resize service
	 *
	 * @type {Object}
	 */
	var resizeService = services.get("resize"),

	/**
	 * Holds a reference to the shortcuts service
	 *
	 * @type {Object}
	 */
	shortcutsService = services.get("shortcuts"),

	/**
	 * Holds the screen sizes
	 *
	 * @type {Object}
	 */
	SCREEN_SIZES = {
		SMALL: "small",
		MEDIUM: "medium",
		LARGE: "large"
	},

	/**
	 * Holds the section identifiers
	 *
	 * @type {Object}
	 */
	SECTIONS = {
		CONSUMERS: "consumers",
		PRODUCTS: "products",
		ORDER_PANEL: "order-panel"
	},

	/**
	 * Holds the list of section identifiers
	 *
	 * @type {Array}
	 */
	SECTIONS_ALL = [SECTIONS.CONSUMERS, SECTIONS.PRODUCTS, SECTIONS.ORDER_PANEL],

	/**
	 * Holds the primary section identifier
	 *
	 * @type {String}
	 */
	primarySection = SECTIONS.CONSUMERS;

	// Register screen sizes
	resizeService.set([{
		name: SCREEN_SIZES.SMALL,
		size: 0
	},{
		name: SCREEN_SIZES.MEDIUM,
		size: 580
	},{
		name: SCREEN_SIZES.LARGE,
		size: 850
	}])

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
				primarySection: "",
				activeSection: SECTIONS.CONSUMERS
			};
		},
		computed: {
			/**
			 * Return whether the consumers section is active
			 *
			 * @return {Boolean} Is the consumers section active?
			 */
			isConsumersActive: function() {
				return -1 !== [this.activeSection, this.primarySection].indexOf(SECTIONS.CONSUMERS);
			},

			/**
			 * Return whether the products section is active
			 *
			 * @return {Boolean} Is the products section active?
			 */
			isProductsActive: function() {
				return -1 !== [this.activeSection, this.primarySection].indexOf(SECTIONS.PRODUCTS);
			},

			/**
			 * Return whether the order panel section is active
			 *
			 * @return {Boolean} Is the order panel section active?
			 */
			isOrderPanelActive: function() {
				return -1 !== [this.activeSection, this.primarySection].indexOf(SECTIONS.ORDER_PANEL);
			},

			/**
			 * Return whether the screen size is large
			 *
			 * @return {Boolean} Is the screen size large?
			 */
			isScreenLarge: function() {
				return this.$screenSize === SCREEN_SIZES.LARGE;
			},

			/**
			 * Return whether the screen size is medium
			 *
			 * @return {Boolean} Is the screen size medium?
			 */
			isScreenMedium: function() {
				return this.$screenSize === SCREEN_SIZES.MEDIUM;
			},

			/**
			 * Return whether the screen size is small
			 *
			 * @return {Boolean} Is the screen size small?
			 */
			isScreenSmall: function() {
				return this.$screenSize === SCREEN_SIZES.SMALL;
			}
		},
		methods: {
			/**
			 * Set the consumers section as the active section
			 *
			 * @return {Void}
			 */
			setConsumersActive: function() {
				this.activeSection = SECTIONS.CONSUMERS;
			},

			/**
			 * Set the products section as the active section
			 *
			 * @return {Void}
			 */
			setProductsActive: function() {
				this.activeSection = SECTIONS.PRODUCTS;
			},

			/**
			 * Set the order panel section as the active section
			 *
			 * @return {Void}
			 */
			setOrderPanelActive: function() {
				this.activeSection = SECTIONS.ORDER_PANEL;
			},

			/**
			 * Set the receipt in the fixed position for medium screens
			 *
			 * @return {Void}
			 */
			setFixedReceiptMedium: function() {
				if (this.isOrderPanelActive) {
					this.resetFixedReceipt();
				} else {

					// Check if elements are rendered
					if (this.$refs.products && ! this.$refs.products.contains(this.$refs.receipt)) {
						this.$refs.products.append(this.$refs.receipt);
					}
				}
			},

			/**
			 * Set the receipt in the fixed position for small screens
			 *
			 * @return {Void}
			 */
			setFixedReceiptSmall: function() {

				// Check if elements are rendered
				if (this.$refs.sections && (this.$refs.products.contains(this.$refs.receipt) || this.$refs.orderPanel.contains(this.$refs.receipt))) {
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
			},

			/**
			 * Return the element's section identifier
			 *
			 * @param  {Element} element Target element
			 * @return {String} Section
			 */
			getElementSection: function( element ) {
				var self = this;

				return SECTIONS_ALL.find( function( section ) {
					return self.$el.querySelector("#".concat(section)).contains(element);
				});
			},

			/**
			 * Restructure sections and reposition the receipt
			 *
			 * @param  {String} size Optional. Screen size when called from the resize event handler.
			 * @return {Void}
			 */
			resetSections: function( size ) {

				// Large screen
				if (this.isScreenLarge) {
					this.resetFixedReceipt();

					// Enable additional active primary section
					this.primarySection = primarySection;

					// Reset the active section to products
					this.isOrderPanelActive && this.setProductsActive();

				// Medium screen
				} else if (this.isScreenMedium) {
					this.setFixedReceiptMedium();

					// Enable additional active primary section
					this.primarySection = primarySection;

					// Reset the active section to products
					! this.isOrderPanelActive && this.setProductsActive();

				// Small screen
				} else if (this.isScreenSmall) {
					this.setFixedReceiptSmall();

					// Disable additional active primary section
					this.primarySection = "";

					// When resized, reset the active section to the primary section
					size && (this.activeSection = primarySection);
				}
			}
		},
		watch: {
			/**
			 * Act when the active section is changed
			 *
			 * @return {Void}
			 */
			activeSection: function() {

				// Focus the section's heading button on small/medium screens
				// so that the focus flow is reset to the top
				if (! this.isScreenLarge && this.$el.querySelector) {
					this.$el.querySelector("#".concat(this.activeSection, " .set-active-section")).focus();
				}

				// Communicate new active section
				this.$root.$emit("sections/active-section", this.activeSection);
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
				if (-1 !== SECTIONS_ALL.indexOf(section)) {
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

			// Register global keyboard event listeners
			this.$registerUnobservable(shortcutsService.on({
				"left": function sectionsNavigateSectionOnLeft() {

					// Bail when focussing an input field
					if (util.isActiveInputNode(document.activeElement)) {
						return;
					}

					// Consider the section
					switch (self.activeSection) {
						case SECTIONS.PRODUCTS:

							// On small screens
							if (self.isScreenSmall) {
								self.setConsumersActive();
							}

							break;
						case SECTIONS.ORDER_PANEL:

							// On all screens
							self.setProductsActive();

							break;
					}
				},
				"right": function sectionsNavigateSectionOnRight() {

					// Bail when focussing an input field
					if (util.isActiveInputNode(document.activeElement)) {
						return;
					}

					// Consider the section
					switch (self.activeSection) {
						case SECTIONS.CONSUMERS:

							// On small screens
							if (self.isScreenSmall) {
								self.setProductsActive();
							}

							break;
						case SECTIONS.PRODUCTS:

							// On all screens
							self.setOrderPanelActive();

							break;
					}
				},

				// Search
				"ctrl+F": function sectionsFocusSearchOnCtrlF( event ) {
					// NOTE: using document for web
					var section = self.getElementSection(document.activeElement) || self.isScreenSmall ? self.activeSection : primarySection,
					    searchInput = self.$el.querySelector("#".concat(section)).querySelector(".search-open");

					// Trigger click on the input search button
					if (searchInput) {

						// Stop browser search
						event.preventDefault();

						// Open the search bar
						util.emitEvent(searchInput, "click");
					}
				}
			}));

			// Register resize event listener
			this.$registerUnobservable(resizeService.on("change", this.resetSections));
		},

		/**
		 * Register listeners when the component is mounted
		 *
		 * @return {Void}
		 */
		mounted: function() {
			var self = this,

			/**
			 * Act when the sections are swiped
			 *
			 * @return {Void}
			 */
			onSectionsSwipe = function( event ) {

				// Consider the section
				switch (self.getElementSection(event.target)) {
					case SECTIONS.CONSUMERS:

						// Swipe rtl on all screens
						if (event.deltaX < 0) {
							self.setProductsActive();
						}

						break;
					case SECTIONS.PRODUCTS:

						// Swipe ltr on all screens
						if (event.deltaX > 0) {
							self.setConsumersActive();
						}

						// Swipe rtl on small screens
						if (event.deltaX < 0 && self.isScreenSmall) {
							self.setOrderPanelActive();
						}

						break;
					case SECTIONS.ORDER_PANEL:

						// Swipe ltr on small screens
						if (event.deltaX > 0 && self.isScreenSmall) {
							self.setProductsActive();
						}

						break;
				}
			};

			/**
			 * Construct element instance for touch events
			 *
			 * @type {Hammer}
			 */
			this.hammer = new Hammer(this.$el);

			// Register touch event listeners
			this.hammer.on("swipe", onSectionsSwipe);
			this.$registerUnobservable( function() {
				self.hammer.off("swipe", onSectionsSwipe);
			});

			// Setup sections
			this.resetSections();
		},

		/**
		 * Register listeners when the component is updated
		 *
		 * @return {Void}
		 */
		updated: function() {

			// Update hammer target element
			this.hammer.element = this.$el;
			this.hammer.input.element = this.$el;
			this.hammer.set({ inputTarget: this.$el });

			// Setup sections
			this.resetSections();
		}
	};
});
