/**
 * Input Dropdown Component
 *
 * @package Incassoos
 * @subpackage App/Components/Form
 */
define([
	"services",
	"util",
	"./../../templates/form/input-dropdown.html",
], function( services, util, tmpl ) {
	/**
	 * Holds a reference to the dialog service
	 *
	 * @type {Object}
	 */
	var dialogService = services.get("dialog");

	return {
		props: {
			options: {
				type: Object,
				required: true
			},
			value: {
				required: true
			},
			title: {
				type: String
			},
			dialog: {
				default: function() {
					return false;
				}
			}
		},
		template: tmpl,
		data: function() {
			return {
				isOpen: false
			};
		},
		computed: {
			/**
			 * Return options as list of objects
			 *
			 * @return {Array} Dropdown options
			 */
			items: function() {
				var items = [], i;

				for (i in this.options) {
					if (this.options.hasOwnProperty(i)) {
						items.push(_.isPlainObject(this.options[i])
							? Object.assign({ id: i }, this.options[i])
							: {
								id: i,
								label: this.options[i]
							}
						);
					}
				}

				return items;
			},

			/**
			 * Return whether this is the selected value
			 *
			 * @param  {String}  id Value to check
			 * @return {Boolean} Is this the selected value?
			 */
			isSelected: function() {
				var self = this;

				return function( id ) {
					return self.value && id === self.value.toString();
				};
			},

			/**
			 * Return the selected item
			 *
			 * @return {Object} Selected item
			 */
			getItem: function() {
				var self = this;

				return this.items.find( function( i ) {
					return i.id === self.value.toString();
				});
			},

			/**
			 * Return the dropdown title
			 *
			 * Defaults to the selected value.
			 *
			 * @return {String} Dropdown title
			 */
			getTitle: function() {
				var item = this.getItem;

				return this.title || (item && item.label) || "Generic.SelectValue";
			},

			/**
			 * Return the selected item icon
			 *
			 * @return {String} Selected item icon
			 */
			getIcon: function() {
				var item = this.getItem;

				return item && item.icon;
			},

			/**
			 * Return the selected item icon title
			 *
			 * @return {String} Selected item icon title
			 */
			getIconTitle: function() {
				var item = this.getItem;

				return item && item.iconTitle;
			}
		},
		methods: {
			/**
			 * Open or close the options
			 *
			 * @return {Void}
			 */
			toggle: function() {
				var self = this;

				this.isOpen = ! this.isOpen;

				// Using dialog mode
				if (this.dialog) {

					/**
					 * Open a dialog for the dropdown options
					 */
					dialogService.open({
						id: "input-dropdown",
						type: "input/dropdown",
						title: this.title || ("string" === typeof this.dialog && this.dialog) || "Generic.SelectValue",
						selected: this.value,
						items: this.items,

						/**
						 * Select the value when the dialog is confirmed
						 *
						 * @param  {String} id The selected value
						 * @return {Void}
						 */
						onConfirm: function( id ) {
							self.select(id);
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
				}
			},

			/**
			 * Make this the selected value
			 *
			 * @param  {String} id The selected value
			 * @return {Void}
			 */
			select: function( id ) {
				this.isOpen = false;
				this.$emit("input", id);
				this.$el.focus();
			}
		},

		/**
		 * Register listeners when the component is mounted
		 *
		 * @return {Void}
		 */
		mounted: function() {
			var self = this;

			// Close dropdown on outside focus
			this.$registerUnobservable(
				util.onOuterFocus(this.$el, function() {
					if (! self.dialog) {
						self.isOpen = false;
					}
				})
			);
		}
	};
});
