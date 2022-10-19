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
	 * Holds a reference to the shortcuts service
	 *
	 * @type {Object}
	 */
	var shortcutsService = services.get("shortcuts"),

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
			},

			/**
			 * Return whether the edited order has patches
			 *
			 * @return {Boolean} Has the order patches?
			 */
			hasPatches: function() {
				return false;
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
			 * Signal to set the active section
			 *
			 * @return {Void}
			 */
			setActiveConsumersSection: function( event ) {

				// Prevent `v-toggle` on the .item-list-header parent
				event && event.stopPropagation && event.stopPropagation();

				// Switch active section
				this.$root.$emit("receipt/set-active-section", "consumers");
			},

			/**
			 * Signal to select another active order
			 *
			 * @param  {String} direction Selection direction
			 * @return {Void}
			 */
			selectOrder: function( direction ) {
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
				var self = this;

				if (this.isActive) {
					if (! this.shortcutsOff) {

						// Register global keyboard event listeners
						this.shortcutsOff = shortcutsService.on({
							"enter": function receiptSubmitOnEnter() {
								self.submit();
							},
							"escape": function receiptTransitionCancelOnEscape() {
								self.cancel();
							},
							"left": function receiptTransitionSelectOrderOnLeft() {
								self.selectOrder("previous");
							},
							"right": function receiptTransitionSelectOrderOnRight() {
								self.selectOrder("next");
							}
						});
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
			 * Act when the screen is resized to small
			 *
			 * @return {Void}
			 */
			onSectionsResizeSmall = function() {

				// Reset attributes after any other updates to sections
				self.$nextTick( function() {
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

			// Subscribe to the 'sections/resize-small' event
			this.$root.$on("sections/resize-small", onSectionsResizeSmall);
			this.$registerUnobservable( function() {
				self.$root.$off("sections/resize-small", onSectionsResizeSmall);
			});

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

				// Swipe rtl
				if (event.deltaX < 0) {
					self.selectOrder("next");

				// Swipe ltr
				} else {
					self.selectOrder("previous");
				}
			};

			// Register touch event listeners
			hammer.on("swipe", onReceiptSwipe);
			this.$registerUnobservable( function() {
				hammer.off("swipe", onReceiptSwipe);
			});
		}
	};
});
