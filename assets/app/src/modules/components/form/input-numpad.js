/**
 * Login Numpad Component
 *
 * @package Incassoos
 * @subpackage App/Components
 */
define([
	"services",
	"util",
	"./../../templates/form/input-numpad.html"
], function( services, util, tmpl ) {
	/**
	 * Holds a reference to the auth service
	 *
	 * @type {Object}
	 */
	var authService = services.get("auth"),

	/**
	 * Holds a reference to the focus service
	 *
	 * @type {Object}
	 */
	focusService = services.get("focus"),

	/**
	 * Holds the map of keyboard codes to numbers
	 *
	 * @type {Object}
	 */
	keyNumMap = {
		48: 0,
		49: 1,
		50: 2,
		51: 3,
		52: 4,
		53: 5,
		54: 6,
		55: 7,
		56: 8,
		57: 9,
		96: 0,
		97: 1,
		98: 2,
		99: 3,
		100: 4,
		101: 5,
		102: 6,
		103: 7,
		104: 8,
		105: 9,
	},

	/**
	 * Holds the map of keyboard codes to arrow names
	 *
	 * @type {Object}
	 */
	keyArrowMap = {
		37: "left",
		38: "up",
		39: "right",
		40: "down"
	},

	/**
	 * Holds the map of numbers to navigation directions and their targets
	 *
	 * @type {Object}
	 */
	keyArrowNavMap = {
		0: {
			"left": "left",
			"up": 8,
			"right": "right"
		},
		1: {
			"left": "right",
			"right": 2,
			"down": 4
		},
		2: {
			"left": 1,
			"right": 3,
			"down": 5
		},
		3: {
			"left": 2,
			"right": 4,
			"down": 6
		},
		4: {
			"left": 3,
			"up": 1,
			"right": 5,
			"down": 7
		},
		5: {
			"left": 4,
			"up": 2,
			"right": 6,
			"down": 8
		},
		6: {
			"left": 5,
			"up": 3,
			"right": 7,
			"down": 9
		},
		7: {
			"left": 6,
			"up": 4,
			"right": 8,
			"down": "left"
		},
		8: {
			"left": 7,
			"up": 5,
			"right": 9,
			"down": 0
		},
		9: {
			"left": 8,
			"up": 6,
			"right": "left",
			"down": "right"
		},
		"left": {
			"left": 9,
			"up": 7,
			"right": 0
		},
		"right": {
			"left": 0,
			"up": 9,
			"right": 1
		}
	},

	/**
	 * Holds the map of navigation destinations when skipping the left block
	 *
	 * @type {Object}
	 */
	skipLeftBlockArrowNavMap = {
		"left": 9,
		"up": 0,
		"right": 0,
		"down": 0
	},

	/**
	 * Holds the 'longpress' timer
	 *
	 * @type {Timer}
	 */
	longPressTimer = null;

	return {
		props: {
			value: {
				type: String,
				required: true
			},
			length: {
				type: Number,
				default: function() {
					return authService.requiredPinLength;
				}
			},
			disabled: {
				type: Boolean,
				default: function() {
					return false;
				}
			}
		},
		template: tmpl,
		data: function() {
			return {
				nums: [1,2,3,4,5,6,7,8,9], // Dial numbers
				skipLeftBlock: false
			};
		},
		computed: {
			/**
			 * Return set of button elements by ref within the component
			 *
			 * @return {Object} Button elements
			 */
			buttons: function() {
				var list = {}, i;

				// Walk all referenced elements, get each button
				for (i in this.$refs) {
					list[i] = this.refEl(i).getElementsByTagName("button")[0];
				}

				return list;
			},

			/**
			 * Return the element for the available back button
			 *
			 * @return {Element} Back button element
			 */
			elBack: function() {
				return this.skipLeftBlock ? this.$refs.right : this.$el.getElementsByClassName("numpad-back")[0];
			}
		},
		methods: {
			/**
			 * Return the element that is associated with the ref key
			 *
			 * Simplifies getting a single button's ref, for num 1-9 are registered in
			 * an array due to applying `:ref` in v-for.
			 *
			 * @param  {String|Number} key Reference key
			 * @return {Element} Ref element
			 */
			refEl: function( key ) {
				return Array.isArray(this.$refs[key]) ? this.$refs[key][0] : this.$refs[key];
			},

			/**
			 * Add number to the bound value
			 *
			 * @param {Number} num Number to add
			 * @return {Void}
			 */
			add: function( num ) {
				this.disabled || this.$emit("input", (this.value.concat(num.toString())).substring(0, this.length));
			},

			/**
			 * Remove number from the bound value
			 *
			 * @return {Void}
			 */
			back: function() {
				this.disabled || this.$emit("input", this.value.substr(0, this.value.length - 1));
			},

			/**
			 * Clear the bound value
			 *
			 * @return {Void}
			 */
			clear: function() {
				this.disabled || this.$emit("input", "");
			},

			/**
			 * Support keyboard input by handling keyup events
			 *
			 * @param  {Object} event Event data
			 * @return {Void}
			 */
			keyup: function inputNumpadOnKeyup( event ) {
				var self = this,
				    key = event.which || event.keyCode || 0,
				    fromRef = false,
				    toRef = false;

				// Bail when the component is disabled
				if (this.disabled) {
					return;
				}

				// Number, remove class for button-press
				if (keyNumMap.hasOwnProperty(key)) {
					this.refEl(keyNumMap[key]).classList.remove("is-active");
					this.add(keyNumMap[key]);

				// Support arrow navigation for button focus
				} else if (keyArrowMap.hasOwnProperty(key)) {

					// Get the focussed element
					for (var i in self.buttons) {

						// Compare with .numpad-grid-cell's button of focussed element
						// NOTE: using document object for web
						if (self.buttons[i] === document.activeElement) {
							fromRef = i;
						}
					}

					// Find the `ref` corresponding with the navigation
					toRef = fromRef && keyArrowNavMap.hasOwnProperty(fromRef) && keyArrowNavMap[fromRef][keyArrowMap[key]];

					// Handle skipping the left block
					if (this.skipLeftBlock && "left" === toRef) {
						toRef = skipLeftBlockArrowNavMap[keyArrowMap[key]];
					}

					// Shift focus according to currently focussed element within the component
					if (this.$refs[toRef]) {
						this.refEl(toRef).getElementsByTagName("button")[0].focus();
					}

				// Backspace or Delete
				} else if (8 === key || 46 === key) {
					this.elBack.classList.remove("is-active");
					this.back();

					// Remove timer
					if (8 === key) {
						longPressTimer && clearTimeout(longPressTimer);
					}
				}
			},

			/**
			 * Support keyboard input by handling keydown events
			 *
			 * @param  {Object} event Event data
			 * @return {Void}
			 */
			keydown: function inputNumpadOnKeydown( event ) {
				var self = this,
				    key = event.which || event.keyCode || 0;

				// Bail when the component is disabled
				if (this.disabled) {
					return;
				}

				// Number, add class for button-press
				if (keyNumMap.hasOwnProperty(key)) {
					this.refEl(keyNumMap[key]).classList.add("is-active");

				// Backspace, prevent browser navigation
				} else if ((8 === key || 46 === key) && ! util.isActiveInputNode(event.srcElement || event.target)) {
					event.preventDefault();
					this.elBack.classList.add("is-active");

					/**
					 * Mimic 'longpress' event from keyboard
					 *
					 * @see polyfills/event.tap-longpress.js
					 */
					longPressTimer = setTimeout( function() {
						self.clear();
						longPressTimer = null;
					}, 800);
				}
			},

			/**
			 * Reset active states on all elements
			 *
			 * @return {Void}
			 */
			resetActive: function() {
				for (var i in this.$refs) {
					this.refEl(i).classList.remove("is-active");
				}
			}
		},

		/**
		 * Register event listeners when the component is created
		 *
		 * @return {Void}
		 */
		created: function() {
			window.addEventListener("keyup", this.keyup);
			window.addEventListener("keydown", this.keydown);

			// Reset the active button when focus is lost
			this.$registerUnobservable(
				focusService.on("blur", this.resetActive)
			);
		},

		/**
		 * Act when the component is mounted
		 *
		 * @return {Void}
		 */
		mounted: function() {

			// Remove the back button from the left block when there's more then one
			if (this.$el.getElementsByClassName("numpad-back").length > 1) {

				// We're skipping the left block
				this.skipLeftBlock = true;

				// Remove the inner elements of the left block
				this.$refs.left.innerHTML = "";
			}
		},

		/**
		 * Remove event listeners before the component is destroyed
		 *
		 * @return {Void}
		 */
		beforeDestroy: function() {
			window.removeEventListener("keyup", this.keyup);
			window.removeEventListener("keydown", this.keydown);
		}
	};
});
