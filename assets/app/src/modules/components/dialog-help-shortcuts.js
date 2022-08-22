/**
 * Dialog for Shortcuts Help Component
 *
 * @package Incassoos
 * @subpackage App/Components
 */
define([
	"lodash",
	"services",
	"./util/basic-dialog",
	"./../templates/dialog-help-shortcuts.html"
], function( _, services, basicDialog, tmpl ) {
	/**
	 * Holds a reference to the shortcuts service
	 *
	 * @type {Object}
	 */
	var shortcutsService = services.get("shortcuts");

	return {
		props: {
			dialog: {
				type: Object,
				default: function() {
					return {};
				}
			}
		},
		template: tmpl,
		components: {
			basicDialog: basicDialog
		},
		computed: {
			/**
			 * Return the list of shortcuts
			 *
			 * @return {Array} Shortcuts
			 */
			shortcuts: function() {
				return shortcutsService.getShortcuts().map( function( i ) {
					return {
						label: i.label,
						keys: i.shortcut.split("+").map(_.capitalize)
					};
				});
			}
		},
		methods: {
			/**
			 * Close the dialog
			 *
			 * Triggering destroy callbacks is done in the basicDialog component.
			 *
			 * @return {Void}
			 */
			close: function() {
				this.$destroy();
			}
		},

		/**
		 * Register listeners when the component is created
		 *
		 * @return {Void}
		 */
		created: function() {
			var self = this;

			// Reset options
			this.dialog = _.defaults(this.dialog, {
				title: "Generic.Dialog.HelpShortcutsTitle",
				content: ""
			});
		}
	};
});
