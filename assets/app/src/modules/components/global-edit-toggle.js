/**
 * Global Edit/Settings Toggle Component
 *
 * @package Incassoos
 * @subpackage App/Components
 */
define([
	"fsm",
	"services"
], function( fsm, services ) {
	/**
	 * Holds a reference to the shortcuts service
	 *
	 * @type {Object}
	 */
	var shortcutsService = services.get("shortcuts"),

	/**
	 * Holds the settings states
	 *
	 * @type {Array}
	 */
	settingStates = [
		fsm.st.SETTINGS,
		fsm.st.RECEIPT_SETTINGS
	],

	/**
	 * Holds the states that are editable
	 *
	 * @type {Array}
	 */
	editableStates = [
		fsm.st.SETTINGS,
		fsm.st.RECEIPT_SETTINGS,
		fsm.st.EDIT_ORDER,
		fsm.st.EDIT_CONSUMER,
		fsm.st.EDIT_PRODUCT
	],

	/**
	 * Holds the transitions that edit the state
	 *
	 * @type {Array}
	 */
	editTransitions = [
		fsm.tr.TOGGLE_SETTINGS,
		fsm.tr.RECEIPT_SETTINGS,
		fsm.tr.EDIT_ITEM
	];

	return {
		template: '<button type="button" v-if="isEditable" @click="toggle" class="icon-button" v-l10n-title="label"><i :class="dashicons"></i><span class="screen-reader-text" v-l10n="label"></span></button>',
		computed: {
			/**
			 * Return the component's label
			 *
			 * @return {String} Label
			 */
			label: function() {
				var label;

				if (this.$fsmSeek(fsm.tr.TOGGLE_SETTINGS) && ! this.$fsmIs(settingStates)) {
					label = "Settings.OpenSettings";
				} else {
					label = this.$fsmIs(editableStates) ? 'Common.Done' : 'Common.Edit';
				}

				return label;
			},

			/**
			 * Return the dashicons class names
			 *
			 * @return {String} Dashicons class names
			 */
			dashicons: function() {
				var classNames = "dashicons dashicons-";

				if (this.$fsmSeek(fsm.tr.TOGGLE_SETTINGS) && ! this.$fsmIs(settingStates)) {
					classNames = classNames.concat("admin-generic");
				} else {
					classNames = classNames.concat(this.$fsmIs(editableStates) ? "saved" : "edit");
				}

				return classNames;
			},

			/**
			 * Return whether the state can be edited
			 *
			 * @return {Boolean} Is the state editable?
			 */
			isEditable: function() {
				return this.$fsmSeek(editTransitions);
			}
		},
		methods: {
			/**
			 * Transition when toggling the current edit state
			 *
			 * @return {Void}
			 */
			toggle: function() {
				fsm.do(editTransitions);
			}
		},

		/**
		 * Register listeners when the component is created
		 *
		 * @return {Void}
		 */
		created: function() {
			var self = this;

			// Register global keyboard event listeners
			this.$registerUnobservable(
				shortcutsService.on({
					"ctrl+E": {
						label: "Administration.ToggleEditModeLabel",
						callback: function globalEditToggleOnCtrlE( e ) {

							// Browsers may default to focussing the address bar
							e.preventDefault();

							// Toggle the edit state
							self.toggle();
						}
					}
				})
			);
		}
	};
});
