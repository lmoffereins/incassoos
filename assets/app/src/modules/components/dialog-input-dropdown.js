/**
 * Dialog for Dropdown Input Component
 *
 * @package Incassoos
 * @subpackage App/Components
 */
define([
	"q",
	"lodash",
	"services",
	"./util/basic-dialog",
	"./../templates/dialog-input-dropdown.html"
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
		data: function() {
			return {
				selected: this.dialog.selected
			};
		},
		computed: {
			/**
			 * Return whether this is the selected value
			 *
			 * @param  {String}  id Value to check
			 * @return {Boolean} Is this the selected value?
			 */
			isSelected: function() {
				var self = this;
				return function( id ) {
					return self.selected && id === self.selected.toString();
				};
			}
		},
		methods: {
			/**
			 * Make this the selected value
			 *
			 * @return {Void}
			 */
			select: function( id ) {
				this.selected = id;
			},

			/**
			 * Confirm and close the dialog
			 *
			 * Runs the onConfirm callback, then removes the dialog.
			 *
			 * @return {Void}
			 */
			confirm: function() {
				var self = this;

				Q.Promisify(this.dialog.onConfirm(this.selected)).then( function() {
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

			// Reset options
			this.dialog = _.defaults(this.dialog, {
				title: "Generic.SelectValue",
				content: "",
				onConfirm: _.noop
			});

			// Register global keyboard event listeners
			this.$registerUnobservable(
				shortcutsService.on({

					// Confirm the dialog
					"enter": function inputDropdownConfirmOnEnter() {
						self.confirm();
					}
				})
			);
		},

		/**
		 * Act when the component is mounted
		 *
		 * @return {Void}
		 */
		mounted: function() {

			// Open with focus on the first dropdown option
			this.$el.querySelector(".dropdown-option:first-child").focus();
		}
	};
});
