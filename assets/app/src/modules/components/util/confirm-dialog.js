/**
 * Confirm Dialog Utility Component
 *
 * @package Incassoos
 * @subpackage App/Components/Util
 */
define([
	"q",
	"lodash",
	"services",
	"./basic-dialog",
	"./../../templates/util/confirm-dialog.html"
], function( Q, _, services, basicDialog, tmpl ) {
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
		methods: {
			/**
			 * Confirm and close the dialog
			 *
			 * Runs the onConfirm callback, then removes the dialog.
			 *
			 * @return {Void}
			 */
			confirm: function() {
				var self = this;

				Q.Promisify(this.dialog.onConfirm).then( function() {
					self.$destroy();
				});
			},

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

			// Parse default options
			this.dialog = _.defaults(this.dialog, {
				title: "Generic.Dialog.AreYouSureTitle",
				content: "Generic.Dialog.AreYouSureContent",
				onConfirm: _.noop
			});

			// Register global keyboard event listeners
			this.$registerUnobservable(
				shortcutsService.on({

					// Confirm the dialog
					"enter": function confirmDialogConfirmOnEnter() {
						self.confirm();
					}
				})
			);
		}
	};
});
