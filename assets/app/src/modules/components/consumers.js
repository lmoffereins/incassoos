/**
 * Consumers Component
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
	"./form/input-toggle",
	"./../templates/consumers.html"
], function( Vuex, _, fsm, services, settings, util, inputDropdown, inputSearch, inputToggle, tmpl ) {
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
	onEnterIdle = function() {

		// Reset the search query
		this.q = "";
	},

	/**
	 * Return callback to sort sanitized values for the given property
	 *
	 * @param  {String} prop Sorting property name
	 * @return {Function} Order callback
	 */
	orderBySanitized = function( prop ) {
		return function( i ) {
			return "string" === typeof i[prop] ? util.removeAccents(i[prop]).toLowerCase() : i[prop];
		};
	};

	return {
		template: tmpl,
		components: {
			inputDropdown: inputDropdown,
			inputSearch: inputSearch,
			inputToggle: inputToggle
		},
		data: function() {
			var options = {};

			// Use custom ordering options
			if (settings.consumer.orderByOptions) {
				options = settings.consumer.orderByOptions;
			} else {
				options = {
					name: "Consumer.OrderByName",
					id: "ID"
				};
			}

			return {
				isLoading: false,
				focusGroupKey: 0,
				q: "",
				shortcutsOff: false,
				orderBy: _.keys(options)[0], // TODO: store in/get from user preferences?
				orderByOptions: options,
				oneGroupExpanded: false,
				allGroupsExpanded: true
			};
		},
		computed: Object.assign({
			/**
			 * Return ordered groups
			 *
			 * @return {Array} Ordered groups
			 */
			groups: function() {
				return _.orderBy(
					_.uniqBy(this.consumers.map( function( i ) {
						// Return group data
						return i.group;
					}), "id"),
					"order"
				);
			},

			/**
			 * Return items filtered for the given group
			 *
			 * @return {Array} Ordered and filtered group items
			 */
			groupConsumers: function() {
				var self = this;

				return function( groupId ) {
					return _.orderBy(
						self.consumers.filter( function( i ) {
							return i.group.id === groupId;
						}),
						["group.order", orderBySanitized(self.orderBy)] // TODO: order with/without groups?
					);
				};
			},

			/**
			 * Return whether to show the sublist within a group
			 *
			 * @return {Boolean} Show the group's sublist?
			 */
			showGroupSubList: function() {
				var self = this;

				return function( groupId ) {
					return self.isSearching || self.allGroupsExpanded || self.oneGroupExpanded === groupId;
				};
			},

			/**
			 * Return whether this is any settings state
			 *
			 * @return {Boolean} Is this any settings state?
			 */
			isSettings: function() {
				return this.$isSettings || this.$fsmIs(fsm.st.RECEIPT_SETTINGS);
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
				return this.isSearching && ! this.consumers.length;
			},

			/**
			 * Return label for expanding a group
			 *
			 * @param {Number|String} groupdId Group identifier
			 * @return {String} Expand group label
			 */
			expandGroupLabel: function() {
				var self = this;

				return function( groupId ) {
					return self.allGroupsExpanded
						? ""
						: self.oneGroupExpanded === groupId
							? "Consumer.CollapseGroup"
							: "Consumer.ExpandGroup";
				}
			},

			/**
			 * Return the consumer's avatar
			 *
			 * @param {Object} item Consumer data
			 * @return {String} Consumer avatar url
			 */
			consumerAvatarUrl: function() {
				return function( item ) {
					return item.avatarUrl || settings.consumer.defaultAvatarUrl;
				}
			},

			/**
			 * Return the first consumer id
			 *
			 * @return {Number} Consumer id
			 */
			firstConsumerId: function() {
				return this.consumers[0].id;
			},

			/**
			 * Return the previous consumer id
			 *
			 * @return {Number} Consumer id
			 */
			previousConsumerId: function() {
				var currIx = this.activeConsumerIx;

				return -1 !== currIx && currIx < this.consumers.length - 1 ? this.consumers[currIx + 1].id : false;
			},

			/**
			 * Return the next consumer id
			 *
			 * @return {Number} Consumer id
			 */
			nextConsumerId: function() {
				var currIx = this.activeConsumerIx;

				return -1 !== currIx && currIx > 0 ? this.consumers[currIx - 1].id : false;
			},

			/**
			 * Return the last consumer id
			 *
			 * @return {Number} Consumer id
			 */
			lastConsumerId: function() {
				return this.consumers[this.consumers.length - 1].id ;
			}
		}, Vuex.mapState("consumers", {
			"active": "active",

			/**
			 * Return all or searched items
			 *
			 * @return {Array} All or searched items
			 */
			consumers: function( state, getters ) {
				var self = this;

				// Get items. Filter for hidden consumers
				return _.orderBy(
					getters.getConsumers.filter( function( i ) {
						return i.show || getters.isActiveItem(i.id) || self.isSettings;

					// Filter for searched items by name or group name
					}).filter( function( i ) {
						return util.matchSearchQuery(i.name, state.searchQuery)
							|| util.matchSearchQuery(i.group.name, state.searchQuery);
					}),
					["group.order", orderBySanitized(this.orderBy)] // TODO: order with/without groups?
				);
			},

			/**
			 * Return the index of the active consumer
			 *
			 * @return {Number} Active consumer index
			 */
			activeConsumerIx: function( state ) {
				return this.consumers.findIndex( function( i ) {
					return state.active && i.id === state.active.id;
				});
			}
		}), Vuex.mapGetters("consumers", {
			"isSelected": "isActiveItem",
			"types": "getTypes"
		}), Vuex.mapState("receipt", {
			/**
			 * Return whether the receipt has active product items
			 *
			 * @return {Boolean} Does the receipt have items?
			 */
			receiptHasItems: function( state, getters ) {
				return !! getters.getItems.length;
			}
		})),
		methods: Object.assign({
			/**
			 * Emit that the consumers section should be the active section
			 *
			 * @return {Void}
			 */
			setActiveSection: function() {
				this.$emit("activeSection");
			},

			/**
			 * Toggle all groups to expand or collapse
			 *
			 * @return {Void}
			 */
			expandAllGroups: function() {
				this.allGroupsExpanded = ! this.allGroupsExpanded;

				// When closing all groups, keep the active consumer's group open
				if (! this.allGroupsExpanded && this.active) {
					this.oneGroupExpanded = this.active.group.id;
				}
			},

			/**
			 * Toggle a single group to expand or collapse
			 *
			 * This is blocked when all groups are expanded.
			 *
			 * @param  {Number|String} id Group identifier. Defaults to false to remove the filter.
			 * @return {Void}
			 */
			expandOneGroup: function( id ) {
				this.oneGroupExpanded = this.allGroupsExpanded
					? false
					: this.oneGroupExpanded === id
						? false
						: id || false;
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
		}, Vuex.mapMutations("consumers", {
			"toggleShowConsumer": "toggleShow"
		}), Vuex.mapActions("consumers", {
			/**
			 * Select the active item
			 *
			 * Dispatch the action without returning the promise.
			 *
			 * @param  {Function} dispatch Dispatch method
			 * @param  {Number} payload Consumer identifier
			 * @return {Void}
			 */
			select: function( dispatch, payload ) {

				// Toggle active item when it was already selected and
				// the receipt has no active items
				if (this.active && this.active.id === payload && ! this.receiptHasItems) {
					dispatch("cancel");
				} else {
					dispatch("select", payload);
				}
			},

			/**
			 * Reload the list of the consumer collection
			 *
			 * Dispatch the action without returning the promise.
			 *
			 * @return {Void}
			 */
			reload: function( dispatch ) {
				var self = this;

				// Bail when already reloading
				if (this.isLoading) {
					return;
				}

				// Indicate loading status and start reload
				this.isLoading = true;
				dispatch("reload").finally( function() {
					self.isLoading = false;
				});
			}
		})),
		watch: Object.assign({
			/**
			 * Act when the list of items changes
			 *
			 * @return {Void}
			 */
			consumers: function() {

				// Update focus group key
				this.focusGroupKey++;
			}
		}, Vuex.mapActions("receipt", {
			/**
			 * Act when the active item is changed
			 *
			 * @param  {Function} dispatch Dispatch method
			 * @return {Void}
			 */
			active: function( dispatch ) {

				// When the active consumer is defined, start the receipt
				if (this.active) {
					dispatch("start");

					// When all groups are closed, keep the active consumer's group open
					// This applies when the consumer was not directly selected, i.e. when
					// viewing a single order.
					if (! this.allGroupsExpanded) {
						this.oneGroupExpanded = this.active.group.id;
					}
				}
			},
		}), Vuex.mapActions("consumers", {
			/**
			 * Act when the search query is changed
			 *
			 * @param  {Function} dispatch Dispatch method
			 * @return {Void}
			 */
			q: function( dispatch ) {
				var self = this;

				if (this.q) {
					if (! this.shortcutsOff) {

						// Register global keyboard event listeners
						this.shortcutsOff = shortcutsService.on({
							"escape": function consumersResetSearchQueryOnEscape() {

								// Reset the search query
								self.q = "";
							},
							"enter": function consumersSelectFirstSearchResultOnEnter() {

								// When focusing the search input with any search result
								if (self.$refs.search.$el.contains(document.activeElement) && self.consumers.length) {

									// Select the first consumer of the first group after sorting
									self.select(self.groupConsumers(self.groups[0].id)[0].id);

									// Reset the search query
									self.q = "";
								}
							}
						});
					}
				} else {
					this.unregisterShortcuts();
				}

				// Remove any group expansion
				this.oneGroupExpanded = false;

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
			 * Set the active consumer from the first consumer
			 *
			 * @return {Void}
			 */
			onSelectFirstConsumer = function () {
				self.nextConsumerId && self.select(self.firstConsumerId);
			},

			/**
			 * Set the active consumer from the previous consumer
			 *
			 * @return {Void}
			 */
			onSelectPreviousConsumer = function () {
				self.previousConsumerId && self.select(self.previousConsumerId);
			},

			/**
			 * Set the active consumer from the next consumer
			 *
			 * @return {Void}
			 */
			onSelectNextConsumer = function () {
				self.nextConsumerId && self.select(self.nextConsumerId);
			},

			/**
			 * Set the active consumer from the last consumer
			 *
			 * @return {Void}
			 */
			onSelectLastConsumer = function () {
				self.previousConsumerId && self.select(self.lastConsumerId);
			},

			/**
			 * Collection of fsm observers
			 *
			 * @type {Object}
			 */
			fsmObservers = {};
			fsmObservers[fsm.on.enter.IDLE] = onEnterIdle;

			// Register observers, bind the component's context
			for (i in fsmObservers) {
				this.$registerUnobservable(
					fsm.observe(i, fsmObservers[i].bind(this))
				);
			}

			// Unregister global keyboard event listeners
			this.$registerUnobservable( function() {
				self.unregisterShortcuts();
			});

			// Update values in component when settings are updated
			this.$registerUnobservable(
				settings.$onUpdate( function() {
					if (settings.consumer.orderByOptions) {
						var orderByValues = _.keys(settings.consumer.orderByOptions);

						self.orderByOptions = settings.consumer.orderByOptions;
						if (-1 === orderByValues.indexOf(self.orderBy)) {
							self.orderBy = orderByValues[0];
						}
					}
				})
			);

			// Subscribe to external events
			this.$root.$on("consumer/select-first-consumer", onSelectFirstConsumer);
			this.$root.$on("consumer/select-previous-consumer", onSelectPreviousConsumer);
			this.$root.$on("consumer/select-next-consumer", onSelectNextConsumer);
			this.$root.$on("consumer/select-last-consumer", onSelectLastConsumer);
			this.$registerUnobservable( function() {
				self.$root.$off("consumer/select-first-consumer", onSelectFirstConsumer);
				self.$root.$off("consumer/select-previous-consumer", onSelectPreviousConsumer);
				self.$root.$off("consumer/select-next-consumer", onSelectNextConsumer);
				self.$root.$off("consumer/select-last-consumer", onSelectLastConsumer);
			});
		}
	};
});
