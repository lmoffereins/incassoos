/**
 * Feedback Component
 *
 * @package Incassoos
 * @subpackage App/Components
 */
define([
	"services",
	"./../templates/feedback.html"
], function( services, tmpl ) {
	/**
	 * Holds a reference to the feedback service
	 *
	 * @type {Object}
	 */
	var feedbackService = services.get("feedback");

	return {
		props: {
			name: {
				type: String,
				required: true
			},
			items: {
				type: Array
			},
			title: {
				type: String,
				default: function() {
					return "";
				}
			},
			reverse: {
				type: Boolean,
				default: function() {
					return false;
				}
			},
			enableRemove: {
				// No default value
			},
			autoRemove: {
				type: Boolean,
				default: function() {
					return false;
				}
			}
		},
		template: tmpl,
		data: function() {
			return {
				_showAll: true
			};
		},
		computed: {
			/**
			 * Return the list's id value
			 *
			 * @return {String} List id value
			 */
			listId: function() {
				return "feedback-".concat(this.name);
			},

			/**
			 * Return whether this component contains the global feedback list
			 *
			 * @return {Boolean} Is this a global list?
			 */
			isGlobal: function() {
				return ! this.items;
			},

			/**
			 * Return whether to show all feedback items
			 *
			 * @return {Boolean} Show all items?
			 */
			showAll: function() {
				return this.isGlobal ? this._showAll : true;
			},

			/**
			 * Return whether list items can be removed
			 *
			 * Defaults to whether this is the global list.
			 *
			 * @return {Boolean} Are items removable?
			 */
			removable: function() {
				return "undefined" !== typeof this.enableRemove
					? !! this.enableRemove
					: this.isGlobal && this.$debugmode;
			},

			/**
			 * List of feedback items
			 *
			 * Defaults to the global `$feedback` Vue property which is defined
			 * within the feedback service.
			 *
			 * @return {Array} List items
			 */
			feedback: function() {
				var self = this,

				// Take a copy of the items
				items = (this.isGlobal ? this.$feedback : this.items).slice();

				// Maybe reverse the items
				if (this.reverse) {
					items.reverse();
				}

				return items.map( function( item ) {
					item.data = item.data || {};
					item.data.args = item.data.args || [];

					// Wrap action callback
					if (item.action) {
						item.action.do = function() {

							// Run action callback
							item.action.callback();

							// Remove the item
							self.remove(item.$id);
						}
					}

					return item;
				});
			}
		},
		methods: {
			/**
			 * Remove the selected feedback item
			 *
			 * @param  {String} id The item's id
			 * @return {Void}
			 */
			remove: function( id ) {

				// When using the global feedback, remove the item globally
				if (this.isGlobal) {
					feedbackService.remove(id);
				}

				// Emit the `remove` event on this component
				this.$emit("remove", id);
			}
		}
	};
});
