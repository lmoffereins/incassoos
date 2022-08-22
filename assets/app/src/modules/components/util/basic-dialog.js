/**
 * Basic Dialog Utility Component
 *
 * @package Incassoos
 * @subpackage App/Components/Util
 */
define([
	"q",
	"lodash",
	"services",
	"./close-button",
	"./../../templates/util/basic-dialog.html"
], function( Q, _, services, closeButton, tmpl ) {
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
			closeButton: closeButton
		},
		computed: {
			/**
			 * Return whether the footer slot was provided non-empty
			 *
			 * Checks whether the slot was provided and if so, whether it has content.
			 * This is used to determine whether to render the slot's wrapper element.
			 *
			 * @return {Boolean} Is the slot used?
			 */
			slotFooter: function() {
				var name = "footer";
				return (! this.$slots.hasOwnProperty(name)) || !! this.$slots[name];
			}
		},
		methods: {
			/**
			 * Close the dialog
			 *
			 * Runs the onClose callback, then removes the dialog.
			 *
			 * @return {Void}
			 */
			close: function() {
				var self = this;

				Q.Promisify(this.dialog.onClose).then( function() {
					self.$destroy();
				});
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
				title: "",
				content: "",
				onClose: _.noop,
				closeOnEscape: true
			});

			// Register global keyboard event listeners
			this.$registerUnobservable(
				shortcutsService.on({
					"escape": function() {
						if (self.dialog.closeOnEscape) {
							self.close();
						}
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
			var self = this;

			// Run callback on mount
			if (this.dialog && "function" === typeof this.dialog.onMounted) {
				this.dialog.onMounted();
			}
		},

		/**
		 * Act when the component is created
		 *
		 * @return {Void}
		 */
		beforeDestroy: function() {
			var self = this;

			// Run callback on destroy
			if (this.dialog && "function" === typeof this.dialog.onDestroy) {
				this.dialog.onDestroy();
			}
		}
	};
});
