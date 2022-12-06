/**
 * Receipt Component
 *
 * @package Incassoos
 * @subpackage App/Components
 */
define([
	"vuex",
	"hammerjs",
	"fsm",
	"services",
	"settings",
	"util",
	"./feedback",
	"./util/close-button",
	"./../templates/receipt.html"
], function( Vuex, Hammer, fsm, services, settings, util, feedback, closeButton, tmpl ) {
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
	 * Reset attributes when closing a receipt
	 *
	 * @return {Void}
	 */
	onEnterIdle = function() {
		this.isExpanded = false;
	},

	/**
	 * Set attributes when viewing an order
	 *
	 * @return {Void}
	 */
	onEnterViewOrder = function() {
		this.isExpanded = true;
	};

	return {
		template: tmpl,
		components: {
			closeButton: closeButton,
			feedback: feedback
		},
		data: function() {
			return {
				shortcutsOff: false,
				isExpanded: false,
				defaultAvatarUrl: settings.consumer.defaultAvatarUrl
			};
		},
		computed: Object.assign({
			/**
			 * Return whether the receipt is active
			 *
			 * @return {Boolean} Is the receipt active?
			 */
			isActive: function() {
				return this.$fsmIs([
					fsm.st.RECEIPT,
					fsm.st.RECEIPT_SETTINGS,
					fsm.st.VIEW_ORDER,
					fsm.st.EDIT_ORDER
				]);
			},

			/**
			 * Return whether we're in the order viewing state
			 *
			 * @return {Boolean} Is the order being viewed?
			 */
			isViewing: function() {
				return this.$fsmIs(fsm.st.VIEW_ORDER);
			},

			/**
			 * Return whether we're in the order creating state
			 *
			 * @return {Boolean} Is the order being created?
			 */
			isCreating: function() {
				return this.$fsmIs(fsm.st.RECEIPT);
			},

			/**
			 * Return whether we're in the order editing state
			 *
			 * @return {Boolean} Is the order being edited?
			 */
			isEditing: function() {
				return this.$fsmIs([
					fsm.st.RECEIPT,
					fsm.st.EDIT_ORDER
				]);
			},

			/**
			 * Return whether this is the receipt settings state
			 *
			 * @return {Boolean} Is this is the settings state?
			 */
			isSettings: function() {
				return this.$fsmIs(fsm.st.RECEIPT_SETTINGS);
			},

			/**
			 * Return the submit label
			 *
			 * @return {String} Label
			 */
			labelSubmit: function() {
				return this.$fsmIs(fsm.st.EDIT_ORDER) ? 'Common.Update' : 'Common.Checkout';
			}
		}, Vuex.mapGetters("receipt", {
			"feedback": "getFeedback",
			"totalPrice": "getTotalPrice",
			"editable": "isEditable",
			"submittable": "isSubmittable"
		}), Vuex.mapState("receipt", {
			/**
			 * Return the receipt items in reverse
			 *
			 * @return {Array} Receipt items
			 */
			receipt: function( state, getters ) {
				var items = getters.getItems.slice();

				// Reverse the items
				items.reverse();

				return items;
			}
		}), Vuex.mapState("consumers", {
			/**
			 * Return the active consumer
			 *
			 * Use `this.consumer.id` to check for an active selected consumer.
			 *
			 * @return {Object} Consumer object
			 */
			consumer: function( state ) {
				return state.active || { name: "Receipt.SelectAConsumer", avatarUrl: this.defaultAvatarUrl, group: {} };
			}
		}), Vuex.mapState("orders", {
			/**
			 * Return the active consumer
			 *
			 * @return {Object} Consumer object
			 */
			order: function( state ) {
				return state.active;
			}
		})),
		methods: Object.assign({
			/**
			 * Signal to set the active consumers section
			 *
			 * @param {Object} $event Event data
			 * @return {Void}
			 */
			setActiveConsumersSection: function( $event ) {

				// Switch active section when there is no active consumer
				if (! this.consumer.id) {
					this.$root.$emit("receipt/set-active-section", "consumers");

					// Prevent triggering subsequent click listeners
					$event.stopPropagation();

					// Collapse the receipt - when already in the right section
					this.isExpanded = false;
				}
			},

			/**
			 * Signal to set the active products section
			 *
			 * @return {Void}
			 */
			setActiveProductsSection: function() {

				// Switch active section when there are no active products
				if (! this.receipt.length) {
					this.$root.$emit("receipt/set-active-section", "products");

					// Collapse the receipt - when already in the right section
					this.isExpanded = false;
				}
			},

			/**
			 * Signal to select an item
			 *
			 * @param  {String} direction Optional. Selection direction. Defaults to the next item.
			 * @return {Void}
			 */
			selectItem: function( direction ) {
				direction = direction || "next";

				// Communicate to select the order
				this.$root.$emit("receipt/select-".concat(direction, "-order"));
			},

			/**
			 * Unregister global keyboard event listeners
			 *
			 * @return {Void}
			 */
			unregisterShortcuts: function() {
				if (this.shortcutsOff) {
					this.shortcutsOff();
					this.shortcutsOff = false;
				}
			}
		}, Vuex.mapMutations("receipt", {
			"clear": "clearList",
			"clearFeedback": "clearFeedback"
		}), Vuex.mapActions("orders", {
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
			}
		}), Vuex.mapActions("products", {
			/**
			 * Increment the product's quantity
			 *
			 * Dispatch the action without returning the promise.
			 *
			 * @param  {Function} dispatch Dispatch method
			 * @param  {Number} payload Product id
			 * @return {Void}
			 */
			increment: function( dispatch, payload ) {
				dispatch("select", payload);
			},

			/**
			 * Decrement the product's quantity
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
			 * Increment the product's quantity by 10
			 *
			 * @param  {Function} dispatch Dispatch method
			 * @param  {Number} payload Product id
			 * @return {Void}
			 */
			incrementByTen: function( dispatch, payload ) {
				dispatch("select", { id: payload, quantity: 10 });
			},

			/**
			 * Decrement the product's quantity by 10
			 *
			 * @param  {Function} dispatch Dispatch method
			 * @param  {Number} payload Product id
			 * @return {Void}
			 */
			decrementByTen: function( dispatch, payload ) {
				dispatch("decrement", { id: payload, quantity: 10 });
			}
		}), Vuex.mapActions("receipt", {
			/**
			 * Toggle receipt settings
			 *
			 * Dispatch the action without returning the promise.
			 *
			 * @param  {Function} dispatch Dispatch method
			 * @return {Void}
			 */
			toggleSettings: function( dispatch ) {
				dispatch("toggleSettings");
			},

			/**
			 * Create new order or save changes for the active item
			 *
			 * Dispatch the action without returning the promise.
			 *
			 * @param  {Function} dispatch Dispatch method
			 * @return {Void}
			 */
			submit: function( dispatch ) {

				// Bail when the receipt is not submittable
				if (! this.submittable) {
					return;
				}

				// Create or update order
				dispatch("submit");
			},

			/**
			 * Close the active item
			 *
			 * Dispatch the action without returning the promise.
			 *
			 * @param  {Function} dispatch Dispatch method
			 * @return {Void}
			 */
			close: function( dispatch ) {
				dispatch("cancel", { close: true });
			},

			/**
			 * Cancel the current action
			 *
			 * Dispatch the action without returning the promise.
			 *
			 * @param  {Function} dispatch Dispatch method
			 * @return {Void}
			 */
			cancel: function( dispatch ) {
				dispatch("cancel");
			}
		})),
		watch: Object.assign({
			/**
			 * Act when the consumer is changed
			 *
			 * @return {Void}
			 */
			consumer: function() {

				// Switch to the products section when selecting a consumer,
				// but not when in settings mode
				if (this.consumer.id && ! this.$isSettings) {
					this.setActiveProductsSection();
				}
			},

			/**
			 * Act when the receipt is toggled expanded or not
			 *
			 * @return {Void}
			 */
			isExpanded: function() {
				var self = this;

				this.$nextTick(function() {
					util.triggerElementChanged(self.$el);
				});
			},

			/**
			 * Act when the receipt's total price changed
			 *
			 * @return {Void}
			 */
			totalPrice: function() {
				util.triggerElementChanged(this.$el);
			},

			/**
			 * Act when the receipt changes active state
			 *
			 * @return {Void}
			 */
			isActive: function() {
				var self = this, shortcuts;

				if (this.isActive) {
					if (! this.shortcutsOff) {

						// Basic shortcuts
						shortcuts = {
							"enter": function receiptSubmitOnEnter() {
								self.submit();
							},
							"escape": function receiptTransitionCancelOnEscape() {
								self.cancel();
							}
						};

						// Shortcuts for viewing a single order
						if (this.isViewing) {
							shortcuts["home"] = function receiptTransitionSelectOrderOnHome() {
								self.selectItem("first");
							};
							shortcuts["left"] = function receiptTransitionSelectOrderOnLeft() {
								self.selectItem("previous");
							};
							shortcuts["right"] = function receiptTransitionSelectOrderOnRight() {
								self.selectItem("next");
							};
							shortcuts["end"] = function receiptTransitionSelectOrderOnEnd() {
								self.selectItem("last");
							};
						}

						// Register global keyboard event listeners
						this.shortcutsOff = shortcutsService.on(shortcuts);
					}
				} else {
					this.unregisterShortcuts();
				}
			}
		}, Vuex.mapActions("receipt", {
			/**
			 * Act when the receipt's list is modified
			 *
			 * @param  {Function} dispatch Dispatch method
			 * @param  {Array} items Current list value
			 * @return {Void}
			 */
			receipt: function( dispatch, items ) {

				// Start the receipt when it contains items
				if (items.length) {
					dispatch("start");

				// Stop the receipt when there is no active consumer
				} else if (! this.consumer.id) {
					dispatch("cancel");

				// Clear feedback when the list is empty
				} else {
					this.clearFeedback();
				}
			}
		})),

		/**
		 * Register listeners when the component is created
		 *
		 * @return {Void}
		 */
		created: function() {
			var self = this,

			/**
			 * Act when the sections are updated
			 *
			 * @return {Void}
			 */
			onSectionsUpdate = function() {

				// Reset attributes after any other updates to sections
				self.$nextTick( function() {

					// Collapse receipt when resizing
					self.isExpanded = false;
				});
			},

			/**
			 * Collection of fsm observers
			 *
			 * @type {Object}
			 */
			fsmObservers = {};
			fsmObservers[fsm.on.enter.IDLE] = onEnterIdle;
			fsmObservers[fsm.on.enter.VIEW_ORDER] = onEnterViewOrder;

			// Register observers, bind the component's context
			for (i in fsmObservers) {
				this.$registerUnobservable(
					fsm.observe(i, fsmObservers[i].bind(this))
				);
			}

			// Subscribe to the 'sections/active-section' event
			this.$root.$on("sections/active-section", onSectionsUpdate);
			this.$registerUnobservable( function() {
				self.$root.$off("sections/active-section", onSectionsUpdate);
			});

			// Register resize event listener
			this.$registerUnobservable(resizeService.on("change", onSectionsUpdate));

			// Unregister global keyboard event listeners
			this.$registerUnobservable( function() {
				self.unregisterShortcuts();
			});

			// Update values in component when settings are updated
			this.$registerUnobservable(
				settings.$onUpdate( function() {
					self.defaultAvatarUrl = settings.consumer.defaultAvatarUrl;
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
			 * Act when the receipt is swiped
			 *
			 * @return {Void}
			 */
			onReceiptSwipe = function( event ) {

				// Horizontal swipe
				if (Math.abs(event.deltaX) > Math.abs(event.deltaY)) {

					// Swipe rtl
					if (event.deltaX < 0) {
						self.selectItem("next");

					// Swipe ltr
					} else {
						self.selectItem("previous");
					}

				// Vertical swipe
				} else {

					// Swipe down
					if (event.deltaY > 0) {
						self.isExpanded = false;
					}
				}
			};

			// Detect horizontal and vertical swipes
			hammer.get("swipe").set({
				direction: Hammer.DIRECTION_ALL
			});

			// Register touch event listeners
			hammer.on("swipe", onReceiptSwipe);
			this.$registerUnobservable( function() {
				hammer.off("swipe", onReceiptSwipe);
			});

			// Collapse receipt on outside click
			this.$registerUnobservable(
				util.onOuterClick(this.$el, function( event ) {

					// Not when clicked inside a list body which
					// might cause the receipt to (stay) open
					if (! event.target.closest(".item-list-body")) {
						self.isExpanded = false;
					}
				})
			);

			// Collapse receipt on outside focus
			this.$registerUnobservable(
				util.onOuterFocus(this.$el, function() {
					self.isExpanded = false;
				})
			);
		}
	};
});
