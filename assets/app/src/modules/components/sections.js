/**
 * Sections Component
 *
 * @package Incassoos
 * @subpackage App/Components
 */
define([
	"underscore",
	"hammerjs",
	"util",
	"services",
	"./consumers",
	"./occasion",
	"./orders",
	"./products",
	"./receipt",
	"./../templates/sections.html"
], function( _, Hammer, util, services, consumers, occasion, orders, products, receipt, tmpl ) {
	/**
	 * Holds a reference to the shortcuts service
	 *
	 * @type {Object}
	 */
	var shortcutsService = services.get("shortcuts"),

	/**
	 * Holds the screen breakpoint values
	 *
	 * @type {Object}
	 */
	BREAKPOINTS = {
		SMALL: 580,
		LARGE: 850
	},

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
	 * Register the callback to listen for the resize event
	 *
	 * Prefer to use `window.matchMedia()` as it only triggers on passing the threshold.
	 *
	 * @param {Function} callback Listener callback
	 * @return {Function} Deregistration callback
	 */
	addResizeListener = function( callback ) {
		var matches = {}, i;

		if (window.matchMedia) {
			for (i in BREAKPOINTS) {
				matches[i] = window.matchMedia("(max-width: ".concat(BREAKPOINTS[i], "px)"));
				matches[i].addEventListener("change", callback);
			}
		} else {
			window.addEventListener("resize", callback);
		}

		/**
		 * Deregister the registered listener
		 *
		 * @return {Void}
		 */
		return function removeResizeListener() {
			if (! _.isEmpty(matches)) {
				for (i in matches) {
					matches[i].removeListener(callback);
				}
			} else {
				window.removeEventListener("resize", callback);
			}
		}
	},

	/**
	 * Define the screen size when the window is resized
	 *
	 * @param {Object} event Event data or MatchQueryListEvent data
	 * @return {Void}
	 */
	onResize = function sectionFindScreenSizeOnResize( event ) {
		var width = document.body.clientWidth;

		if (width >= BREAKPOINTS.LARGE && ! event.matches) {
			this.screenSize = SCREEN_SIZES.LARGE;
		} else if (width > BREAKPOINTS.SMALL) {
			this.screenSize = SCREEN_SIZES.MEDIUM;
		} else {
			this.screenSize = SCREEN_SIZES.SMALL;
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
				screenSize: "",
				isPanelActive: false,
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
				return this.activeSection === SECTIONS.CONSUMERS;
			},

			/**
			 * Return whether the products section is active
			 *
			 * @return {Boolean} Is the products section active?
			 */
			isProductsActive: function() {
				return this.activeSection === SECTIONS.PRODUCTS;
			},

			/**
			 * Return whether the order panel section is active
			 *
			 * @return {Boolean} Is the order panel section active?
			 */
			isOrderPanelActive: function() {
				return this.activeSection === SECTIONS.ORDER_PANEL;
			},

			/**
			 * Return whether the screen size is large
			 *
			 * @return {Boolean} Is the screen size large?
			 */
			isScreenLarge: function() {
				return this.screenSize === SCREEN_SIZES.LARGE;
			},

			/**
			 * Return whether the screen size is medium
			 *
			 * @return {Boolean} Is the screen size medium?
			 */
			isScreenMedium: function() {
				return this.screenSize === SCREEN_SIZES.MEDIUM;
			},

			/**
			 * Return whether the screen size is small
			 *
			 * @return {Boolean} Is the screen size small?
			 */
			isScreenSmall: function() {
				return this.screenSize === SCREEN_SIZES.SMALL;
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
				if (! this.isScreenLarge) {
					this.$el.querySelector("#".concat(this.activeSection)).querySelector(".set-active-section").focus();
				}
			},

			/**
			 * Act when the screen size is changed
			 *
			 * @return {Void}
			 */
			screenSize: function() {

				// Consider the screen size
				switch (this.screenSize) {
					case SCREEN_SIZES.LARGE:

						// Reset the active section to consumers
						this.isOrderPanelActive && this.setConsumersActive();
						this.resetFixedReceipt();

						// Emit screen resize
						this.$root.$emit("sections/resize-large");
						break;

					case SCREEN_SIZES.SMALL:
						this.setFixedReceiptSmall();

						// Emit screen resize
						this.$root.$emit("sections/resize-small");
						break;
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

							// On all screens
							self.setConsumersActive();

							break;
						case SECTIONS.ORDER_PANEL:

							// On small screens
							if (self.isScreenSmall) {
								self.setProductsActive();
							}

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

							// On all screens
							self.setProductsActive();

							break;
						case SECTIONS.PRODUCTS:

							// On small screens
							if (self.isScreenSmall) {
								self.setOrderPanelActive();
							}

							break;
					}
				},

				// Search
				"ctrl+F": function sectionsFocusSearchOnCtrlF( event ) {
					// NOTE: using document for web
					var section = self.getElementSection(document.activeElement) || self.activeSection,
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
			this.$registerUnobservable(
				addResizeListener(onResize.bind(this))
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

			// Run resize handler when the component is rendered
			onResize.apply(this);
		}
	};
});
