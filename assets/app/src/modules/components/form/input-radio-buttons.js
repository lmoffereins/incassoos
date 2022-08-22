/**
 * Input Radio Buttons Component
 *
 * @package Incassoos
 * @subpackage App/Components/Form
 */
define([
	"./../../templates/form/input-radio-buttons.html",
], function( tmpl ) {
	/**
	 * Holds the map of keyboard codes to arrow names
	 *
	 * @type {Object}
	 */
	var keyArrowMap = {
		37: "left",
		38: "up",
		39: "right",
		40: "down"
	};

	return {
		props: {
			items: {
				type: Object,
				required: true
			},
			value: {
				required: true
			}
		},
		template: tmpl,
		data: function() {
			return {
				tabitem: _.keys(this.items)[0]
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
					return self.value && id === self.value.toString();
				};
			},

			/**
			 * Return tabindex key resulting in one tabbable item per component
			 *
			 * @return {Number} Tabindex
			 */
			tabindex: function() {
				var self = this;
				return function( id ) {
					return id === self.tabitem ? 0 : -1;
				}
			}
		},
		methods: {
			/**
			 * Make this the selected value
			 *
			 * @param  {String} id The selected value
			 * @return {Void}
			 */
			select: function( id ) {
				this.$emit("input", id);

				// Set focussed element
				this.tabitem = id;
			},

			/**
			 * Return the item id that corresponds with the element
			 *
			 * @param  {Object} element HTML Element
			 * @return {String} Item id
			 */
			getElementItemId: function( element ) {
				var id;

				// Bail when the element is not a child
				if (! this.$el.contains(element)) {
					return "";
				}

				// Loop for all items
				for (id in this.items) {
					if (this.items.hasOwnProperty(id) && this.$refs[id][0] === element) {
						return id;
					}
				}
			}
		},

		/**
		 * Register listeners when the component is created
		 *
		 * @return {Void}
		 */
		created: function() {
			var self = this,

			/**
			 * Navigation for arrow keys to focus elements
			 *
			 * @param {Event} event Keyup event
			 * @type {Void}
			 */
			onKeyup = function inputRadioButtonsOnKeyup( event ) {
				var key = event.which || event.keyCode || 0, ids, currIx;

				// Continue only when component has focus
				// NOTE: using document object for web
				if (keyArrowMap.hasOwnProperty(event.keyCode) && self.$el.contains(document.activeElement)) {

					// When focus is shifted, reset current tabitem
					if (document.activeElement !== self.$refs[self.tabitem][0]) {
						self.tabitem = self.getElementItemId(document.activeElement);
					}

					ids    = _.keys(self.items);
					currIx = ids.indexOf(self.tabitem);

					// Previous item
					if (-1 !== ["left"].indexOf(keyArrowMap[key])) {
						self.tabitem = (currIx > 0)
							? ids[currIx - 1]
							: ids[Math.max(currIx - 1, 0)];
					}

					// Next item
					if (-1 !== ["right"].indexOf(keyArrowMap[key])) {
						self.tabitem = (currIx < ids.length - 1)
							? ids[currIx + 1]
							: ids[Math.min(ids.length - 1, currIx + 1)];
					}

					// Set focus on the tabbed item
					self.$refs[self.tabitem][0].focus();
				}
			};

			// Register global keyboard event listeners
			window.addEventListener("keyup", onKeyup);
			this.$registerUnobservable(
				function() {
					window.removeEventListener("keyup", onKeyup);
				}
			);
		}
	};
});
