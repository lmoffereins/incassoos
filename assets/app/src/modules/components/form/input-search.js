/**
 * Input Search Component
 *
 * @package Incassoos
 * @subpackage App/Components/Form
 */
define([
	"services",
	"util",
	"./../../templates/form/input-search.html",
], function( services, util, tmpl ) {
	/**
	 * Holds a reference to the delay service
	 *
	 * @type {Object}
	 */
	var delayService = services.get("delay");

	return {
		props: {
			value: {
				required: true
			}
		},
		template: tmpl,
		data: function() {
			return {
				isOpen: false
			};
		},
		methods: {
			/**
			 * Open or close the searchbar
			 *
			 * @return {Void}
			 */
			toggle: function() {
				var self = this;

				this.isOpen = ! this.isOpen;

				if (this.isOpen) {
					delayService(0).then( function() {
						self.$refs.search.focus();
					});
				} else {
					this.cancel();
				}
			},

			/**
			 * Emit the input event
			 *
			 * @return {Void}
			 */
			input: function() {
				this.$emit("input", this.$refs.search.value);
			},

			/**
			 * Clear the search
			 *
			 * @return {Void}
			 */
			cancel: function( event ) {

				// Stop propagation when a search was present
				if (this.$refs.search.value.length) {
					event.stopPropagation();
				}

				this.isOpen = false;
				this.$emit("input", "");
				this.$emit("cancel");
			}
		},
		watch: {
			/**
			 * Close toggled searchbar when search query was cleared from outside
			 *
			 * @return {Void}
			 */
			value: function() {
				// Close only when component does not have focus
				// NOTE: using document object for web
				if ("" === this.value && ! this.$el.contains(document.activeElement)) {
					this.isOpen = false;
				}
			}
		},

		/**
		 * Register listeners when the component is mounted
		 *
		 * @return {Void}
		 */
		mounted: function() {
			var self = this;

			// Close searchbar on outside focus when empty
			this.$registerUnobservable(
				util.onOuterFocus(this.$el, function() {
					if ("" === self.value) {
						self.isOpen = false;
					}
				})
			);
		}
	};
});
