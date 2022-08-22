/**
 * Dialog Container Component
 *
 * @package Incassoos
 * @subpackage App/Components
 */
define([
	"lodash",
	"services",
	"./util/basic-dialog",
	"./util/confirm-dialog",
	"./dialog-help-shortcuts",
	"./dialog-input-datepicker",
	"./dialog-input-dropdown",
	"./../templates/dialog-container.html"
], function( _, services, basicDialog, confirmDialog, dialogHelpShortcuts, dialogInputDatepicker, dialogInputDropdown, tmpl ) {
	/**
	 * Holds a reference to the dialog service
	 *
	 * @type {Object}
	 */
	var dialogService = services.get("dialog"),

	/**
	 * Holds the list of available dialog components
	 *
	 * @type {Object}
	 */
	dialogComponentMap = {
		"confirm": confirmDialog,
		"help/shortcuts": dialogHelpShortcuts,
		"input/datepicker": dialogInputDatepicker,
		"input/dropdown": dialogInputDropdown
	};

	return {
		template: tmpl,
		data: function() {
			return {
				activeDialogs: [],
				dialogs: []
			};
		},
		computed: {
			/**
			 * Dynamic checker to return whether the dialog is active
			 *
			 * @return {Boolean} Is the dialog active?
			 */
			isActive: function() {
				var self = this;
				return function( id ) {
					return -1 !== self.activeDialogs.indexOf(id);
				};
			}
		},
		methods: {
			/**
			 * Remove the dialog when it is closed
			 *
			 * @param  {String} id Dialog identifier
			 * @return {Void}
			 */
			remove: function( id ) {
				dialogService.remove(id);
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
				dialogService.on({

					// Dialog to add
					add: function( options ) {

						// Set the dialog's component
						options.component = dialogComponentMap[options.type] || basicDialog;

						// Add dialog to collection
						self.dialogs.push(options);
					},

					// Dialog to remove
					remove: function( id ) {

						// Remove from active dialogs
						self.activeDialogs = self.activeDialogs.filter( function( i ) {
							return i.id !== id;
						});

						// Remove from registered dialogs
						self.dialogs = self.dialogs.filter( function( i ) {
							return i.id !== id;
						});
					},

					// Dialog to show
					show: function( id ) {
						if (-1 === self.activeDialogs.indexOf(id)) {
							self.activeDialogs.push(id);
						}
					}
				})
			);
		}
	};
});
