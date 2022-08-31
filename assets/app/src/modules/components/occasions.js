/**
 * Occasions Component
 *
 * @package Incassoos
 * @subpackage App/Components
 */
define([
	"vuex",
	"dayjs",
	"fsm",
	"services",
	"settings",
	"./feedback",
	"./form/input-datepicker",
	"./form/input-radio-buttons",
	"./util/close-button",
	"./../templates/occasions.html",
	"./../../images/loader.svg"
], function( Vuex, dayjs, fsm, services, settings, feedback, inputDatepicker, inputRadioButtons, closeButton, tmpl, loaderSvg ) {
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
	 * Return the occasion type's label
	 *
	 * @param  {Number|String} typeId Type id
	 * @return {String} Occasion type label
	 */
	getOccasionTypeLabel = function( typeId ) {
		return settings.occasion.occasionType.items && settings.occasion.occasionType.items[typeId];
	},

	/**
	 * Set form fields for the occasion view
	 *
	 * @return {Void}
	 */
	onEnterViewOccasion = function() {
		var payload = this.$store.state.occasions.active;

		if (payload) {
			this.mode = "active";
			this.title = payload.titleRaw;
			this.occasionDate = new Date(payload.occasionDate);
			this.occasionType = payload.occasionType;
			this.occasionTypeLabel = getOccasionTypeLabel(payload.occasionType);
		}
	},

	/**
	 * Set mode for the occasion edit
	 *
	 * @return {Void}
	 */
	onEnterEditOccasion = function() {
		this.mode = "edit";
	},

	/**
	 * Reset form fields after the occasion edit
	 *
	 * @return {Void}
	 */
	onEnterOccasions = function() {
		this.title = "";
		this.occasionDate = new Date();
		this.occasionType = settings.occasion.occasionType.defaultValue;
		this.occasionTypeLabel = getOccasionTypeLabel(settings.occasion.occasionType.defaultValue);

		if (this.activeMode || this.editMode) {
			this.mode = "get";
		}
	},

	/**
	 * Request confirmation when deleting the occasion
	 *
	 * @return {Void}
	 */
	onEnterDeleteOccasion = function( lifecycle, payload ) {
		var self = this;

		// Request the user to confirm the action
		dialogService.confirm({
			id: "delete-occasion",
			content: ["Occasion.AreYouSureDelete", payload.title],

			/**
			 * Start occasion delete when the dialog is confirmed
			 *
			 * @return {Void}
			 */
			onConfirm: function() {
				self.$store.dispatch("occasions/delete");
			},

			/**
			 * Close the delete state when the dialog is cancelled
			 *
			 * @return {Void}
			 */
			onClose: function() {
				self.$store.dispatch("occasions/cancel");
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
			if (this.editMode) {

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
			inputDatepicker: inputDatepicker,
			inputRadioButtons: inputRadioButtons
		},
		data: function() {
			var types = settings.occasion.occasionType && settings.occasion.occasionType.items || {};

			return {
				mode: "create",
				availableOccasionTypes: types,
				isLoading: false,
				loadingPayload: null,
				loadingImg: loaderSvg,

				// Form fields
				title: "",
				occasionDate: new Date(),
				occasionType: settings.occasion.occasionType.defaultValue,
				occasionTypeLabel: getOccasionTypeLabel(settings.occasion.occasionType.defaultValue)
			};
		},
		computed: Object.assign({
			/**
			 * Return whether the mode is of the given type
			 *
			 * @return {Boolean} Is this the mode?
			 */
			getMode:    function() { return this.mode === "get";    },
			createMode: function() { return this.mode === "create"; },
			activeMode: function() { return this.mode === "active"; },
			editMode:   function() { return this.mode === "edit";   },

			/**
			 * Return whether the occasion can be created
			 *
			 * @return {Boolean} Can occasion be created?
			 */
			creatable: function() {
				return this.$fsmSeek(fsm.tr.GET_OCCASION)
					&& !! this.title
					&& dayjs(this.occasionDate).isValid();
			},

			/**
			 * Return the contents for the loading title
			 *
			 * @return {Array} Loading title content
			 */
			loadingTitle: function() {
				return this.loadingPayload && this.loadingPayload.id
					? ["Occasion.LoadingOccasion", this.loadingPayload.title]
					: ["Occasion.CreatingOccasion", this.title];
			},

			/**
			 * Return whether we have any occasion types
			 *
			 * @return {Boolean} Do we have occasion types?
			 */
			haveOccasionTypes: function() {
				return !! _.keys(this.availableOccasionTypes).length;
			}
		}, Vuex.mapState("occasions", {
			"active": "active",

			/**
			 * Return the active occasion's closed date
			 *
			 * @return {Date|Boolean} Closed date, False if not closed
			 */
			occasionClosedDate: function( state ) {
				return state.active && state.active.closed;
			}
		}), Vuex.mapGetters("occasions", {
			"occasions": "getItems",
			"feedback": "getFeedback",
			"isSelected": "isActiveItem",
			"editable": "isEditable",
			"submittable": "isSubmittable",
			"deletable": "isDeletable",
			"closable": "isClosable",
			"reopenable": "isReopenable"
		}), Vuex.mapState("orders", {
			/**
			 * Return the active occasion's order count
			 *
			 * @return {Number} Order count
			 */
			orderCount: function( state ) {
				return state.all.length;
			}
		}), Vuex.mapGetters("orders", {
			"totalProductQuantity": "getTotalQuantity",
			"totalConsumedValue": "getTotalPrice"
		})),
		methods: Vuex.mapActions("occasions", {
			/**
			 * Get or create the occasion
			 *
			 * @param {Function} dispatch Dispatch method
			 * @param {Number} payload Optional. Occasion identifier. No value results in creating the occasion.
			 * @return {Void}
			 */
			get: function( dispatch, payload ) {
				var self = this;

				this.isLoading = true;
				this.loadingPayload = payload;

				// Setup payload attributes for creating a new item
				if (! payload || ! payload.id) {
					payload = {
						title: this.title,
						occasionDate: this.occasionDate,
						occasionType: this.occasionType
					};
				}

				// Get or create item
				dispatch("get", payload).catch( function() {
					self.isLoading = false;
					self.loadingPayload = null;
				});
			},

			/**
			 * Select to view the active item
			 *
			 * Dispatch the action without returning the promise.
			 *
			 * @param  {Function} dispatch Dispatch method
			 * @return {Void}
			 */
			select: function( dispatch ) {
				dispatch("start");
			},

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
			 * Close the active item
			 *
			 * Dispatch the action without returning the promise.
			 *
			 * @param  {Function} dispatch Dispatch method
			 * @return {Void}
			 */
			closeOccasion: function( dispatch ) {
				dispatch("close");
			},

			/**
			 * Reopen the active item
			 *
			 * Dispatch the action without returning the promise.
			 *
			 * @param  {Function} dispatch Dispatch method
			 * @return {Void}
			 */
			reopenOccasion: function( dispatch ) {
				dispatch("reopen");
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
				dispatch("cancel", { close: true });
			}
		}),
		watch: Object.assign({
			/**
			 * Act when the selection mode is toggled
			 *
			 * @return {Void}
			 */
			mode: function() {

				// Cancel the active edit when moving away
				if (! this.activeMode && ! this.editMode) {
					this.cancel();

				// View occasion when changing to active mode
				} else if (this.activeMode) {
					this.select();

				// Go to create mode when there are no items to select
				} else if (this.getMode && ! this.occasions.length) {
					this.mode = "create";
				}
			}
		}, Vuex.mapActions("occasions", {
			title:        watchPatch("title"),
			occasionDate: watchPatch("occasionDate"),
			occasionType: watchPatch("occasionType")
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
			fsmObservers[fsm.on.enter.VIEW_OCCASION] = onEnterViewOccasion;
			fsmObservers[fsm.on.enter.EDIT_OCCASION] = onEnterEditOccasion;
			fsmObservers[fsm.on.enter.OCCASIONS] = onEnterOccasions;
			fsmObservers[fsm.on.enter.DELETE_OCCASION] = onEnterDeleteOccasion;

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
						if (self.editMode) {
							self.cancel();
						} else {
							self.close();
						}
					}
				})
			);

			// Update values in component when settings are updated
			this.$registerUnobservable(
				settings.$onUpdate( function() {
					self.availableOccasionTypes = settings.occasion.occasionType.items;
					onEnterOccasions.apply(self);
				})
			);
		},

		/**
		 * Register listeners when the component is mounted
		 *
		 * @return {Void}
		 */
		mounted: function() {

			// When starting to select, turn to get mode
			if (this.createMode && this.occasions.length) {
				this.mode = "get";
			}

			// On initial creation, this observer is not triggered yet
			this.active && onEnterViewOccasion.call(this);
		}
	};
});
