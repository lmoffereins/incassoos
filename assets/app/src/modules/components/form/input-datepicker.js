/**
 * Datepicker Form Component
 *
 * @package Incassoos
 * @subpackage App/Components/Util
 */
define([
	"services",
	"./../../templates/form/input-datepicker.html"
], function( services, tmpl ) {
	/**
	 * Holds a reference to the dialog service
	 *
	 * @type {Object}
	 */
	var dialogService = services.get("dialog");

	return {
		props: {
			value: {
				type: Date,
				default: function() {
					return new Date();
				}
			},
			title: {
				type: String,
				default: function() {
					return "Generic.SelectDate";
				}
			}
		},
		template: tmpl,
		methods: {
			/**
			 * Open the calendar
			 *
			 * @return {Void}
			 */
			open: function() {
				var self = this;

				/**
				 * Open a dialog for the datepicker
				 */
				dialogService.open({
					id: "input-datepicker",
					type: "input/datepicker",
					title: this.title,
					selected: this.value,

					/**
					 * Select the value when the dialog is confirmed
					 *
					 * @param  {Date} value The selected value
					 * @return {Void}
					 */
					onConfirm: function( value ) {
						self.select(value);
					},

					/**
					 * Act when the dialog is destroyed
					 *
					 * @return {Void}
					 */
					onDestroy: function() {

						// Refocus the input element
						self.$refs.input.focus();
					}
				});
			},

			/**
			 * Make this the selected value
			 *
			 * @param  {Date} value The selected value
			 * @return {Void}
			 */
			select: function( value ) {
				this.$emit("input", value);
			}
		}
	};
});
