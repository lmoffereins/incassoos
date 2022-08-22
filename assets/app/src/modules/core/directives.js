/**
 * Custom Vue Directives
 *
 * @package Incassoos
 * @subpackage App/Core
 */
define([
	"vue",
	"lodash",
	"services"
], function( Vue, _, services ) {
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
	},

	/**
	 * Unrender the element
	 *
	 * Emulates effect of the v-if directive for custom directives.
	 *
	 * @link https://stackoverflow.com/a/43543814/3601434
	 *
	 * @param  {Object} el    Element data
	 * @param  {Object} vNode Virtual element node
	 * @return {Void}
	 */
	unrender = function( el, vNode ) {

		// Create the placeholder element
		// NOTE: using document object for web
		var comment = document.createComment("");
		Object.defineProperty(comment, "setAttribute", {
			value: function() {}
		});

		// Set vNode properties
		vNode.elm = comment;
		vNode.text = "";
		vNode.isComment = true;
		// vNode.context = {}; // Keep context.$store available?
		vNode.tag = undefined;
		vNode.data.directives = undefined;

		// Replace custom component
		if (vNode.componentInstance) {
			vNode.componentInstance.$el = comment;
		}

		// When already attached to a parent
		if (el.parentNode) {
			el.parentNode.replaceChild(comment, el);
		}
	};

	/**
	 * Enable conditional rendering based on the state machine's (fsm) state
	 *
	 * Shorthand for `v-if="fsm.is(state)"`, where the element component's fsm is used
	 * when present. Defaults to using the application's global fsm.
	 *
	 * Use either a value or modifiers as the state names. The following examples
	 * have the same result:
	 *
	 *    <element v-if-fsm.stateName></element>
	 *    <element v-if-fsm="'stateName'"></element>
	 *    <element v-if-fsm="expressionReturningStateName"></element>
	 *
	 * @param {String} stateNames State names, array or comma-separated
	 */
	Vue.directive("if-fsm", function( el, binding, vNode ) {
		var _fsm = vNode.context.fsm || vNode.context.$store.state.fsm,
		    withModifiers = ! _.isEmpty(binding.modifiers),
		    stateNames;

		// Bail when not using a real state machine
		if (! _fsm || "function" !== typeof _fsm.is) {
			return;
		}

		// Try modifiers first, else use the directive's calculated value
		stateNames = withModifiers ? _.keys(binding.modifiers) : (Array.isArray(binding.value) ? binding.value : (binding.value ? binding.value : binding.expression).split(","));

		// Default to the application's global fsm, when the state is not recognized
		if (-1 === _fsm.allStates().indexOf(stateNames[0])) {
			/**
			 * @see modules/store/main.js
			 */
			_fsm = vNode.context.$store.state.fsm;
		}

		// Unrender element when the state does not match
		if (! _fsm.is(stateNames)) {
			unrender(el, vNode);
		}
	});

	/**
	 * Enable conditional rendering based on user capabilities
	 *
	 * Shorthand for `v-if="authService.userCan(capability)"`.
	 *
	 * Use either a value or modifiers as the capability. The following examples
	 * have the same result:
	 *
	 *    <element v-if-user-can.capability></element>
	 *    <element v-if-user-can="'capability'"></element>
	 *    <element v-if-user-can="expressionReturningCapability"></element>
	 *
	 * @param {String} capability Capability name
	 */
	Vue.directive("if-user-can", function( el, binding, vNode ) {
		var withModifiers = ! _.isEmpty(binding.modifiers),
		    capability = withModifiers ? _.keys(binding.modifiers)[0] : binding.value;

		if (! services.get("auth").userCan(capability)) {
			unrender(el, vNode);
		}
	});

	/**
	 * Conditionally render the element when it is not empty
	 *
	 * Use without parameters:
	 *
	 *    <element v-if-not-empty></element>
	 */
	Vue.directive("if-not-empty", function( el, binding, vNode ) {

		// Unrender element when it has no children with valid
		// tags (i.e. only whitespace or comments)
		if (! vNode.children.filter(i => i.tag).length) {
			unrender(el, vNode);
		}
	});

	/**
	 * Autofocus the element
	 *
	 * Use without parameters:
	 *
	 *    <element v-focus></element>
	 */
	Vue.directive("focus", {
		inserted: function( el, binding, vNode ) {
			el.focus();
		}
	});

	/**
	 * Declare element as a single focus group
	 *
	 * A focus group is a single tabbable item with multiple focussable children.
	 *
	 * NOTE: to ensure the directive's element does update when their children update,
	 * add an arbitrary parameter that changes along to force the directive's update cycle.
	 *
	 * Use with parameters:
	 *
	 *    <element v-focus-group="'selector'"></element>
	 *    <element v-focus-group="{ 'selector': <selector> }"></element>
	 *    <element v-focus-group="expressionReturningParameters"></element>
	 */
	Vue.directive("focus-group", {
		/**
		 * Return the list of focussable items, convert nodelist to array
		 *
		 * @param  {Element} el Directive element
		 * @return {Array} Focussable items
		 */
		getItems: function( el ) {
			return Array.prototype.slice.call(el.querySelectorAll(el.dataset.focusGroupSelector));
		},

		/**
		 * Update tabindex attributes and set focus
		 *
		 * @param  {Element} el Directive element
		 * @param  {Boolean} noFocus Optional. Whether to not apply focus on update. Defaults to false.
		 * @return {Void}
		 */
		updateTabindices: function( el, noFocus ) {
			var focusIx = el.dataset.focusGroupFocusIx;

			focusIx = focusIx ? parseInt(focusIx) : 0;

			this.getItems(el).forEach( function( i, ix ) {

				// Set tabindex to ignore tabbing except the focussed item
				i.setAttribute("tabindex", ix === focusIx ? (noFocus || i.focus(), 0) : -1);
			});
		},

		/**
		 * Register global event listeners
		 *
		 * @return {Void}
		 */
		bind: function( el, binding ) {
			/**
			 * Set properties to share across hooks
			 * 
			 * - initial focus index
			 * - item selector. Assume non-mutating initial value
			 */
			el.dataset.focusGroupFocusIx = 0;
			el.dataset.focusGroupSelector = binding.value.selector || binding.value;

			/**
			 * Navigation for arrow keys to focus elements
			 *
			 * Define event listener on the element itself:
			 * - the listener is only triggered when focus is on/in the element
			 * - the listener has the correct context regardless of element updates
			 * - the listener is automatically removed when the element is destroyed
			 *
			 * @param {Event} event Event details
			 * @return {Void}
			 */
			el.addEventListener("keyup", function vFocusGroupOnKeyup( event ) {
				var key = event.which || event.keyCode || 0, items, focusIx, currIx, newIx;

				// Bail when not an arrow key
				if (! keyArrowMap.hasOwnProperty(event.keyCode)) {
					return;
				}

				// Get the current items
				items = binding.def.getItems(this);
				focusIx = parseInt(this.dataset.focusGroupFocusIx);
				currIx = binding.def.getItems(el).findIndex( function( i ) {
					return i === document.activeElement;
				});

				// Previous item
				if (-1 !== ["up"].indexOf(keyArrowMap[key])) {
					newIx = (currIx > 0)
						? currIx - 1
						: Math.max(currIx - 1, 0);
				}

				// Next item
				if (-1 !== ["down"].indexOf(keyArrowMap[key])) {
					newIx = (currIx < items.length - 1)
						? currIx + 1
						: Math.min(items.length - 1, currIx + 1);
				}

				// Update tabindex attributes and set focus
				if (focusIx !== currIx || currIx !== newIx) {
					this.dataset.focusGroupFocusIx = newIx;
					binding.def.updateTabindices(this);
				}
			});
		},

		/**
		 * Set tabindex attributes and initiate focus
		 *
		 * @return {Void}
		 */
		inserted: function( el, binding ) {
			binding.def.updateTabindices(el);
		},

		/**
		 * Update tabindex attributes and initiate focus
		 *
		 * @return {Void}
		 */
		update: function( el, binding ) {
			var currIx, noFocus = true;

			// Consider the active element when inside here
			// TODO: first selection by click does not reset focus ix?!
			if (el.contains(document.activeElement)) {
				noFocus = false;

				currIx = binding.def.getItems(el).findIndex( function( i ) {
					return i === document.activeElement;
				});

				// Reset current tabitem in case the focus was shifted (click)
				if (-1 !== currIx) {
					el.dataset.focusGroupFocusIx = currIx;
				}
			}

			binding.def.updateTabindices(el, noFocus);
		}
	});

	/**
	 * Toggle a boolean on click
	 *
	 * The event listener is automatically removed on element destroy.
	 *
	 * Use with parameter:
	 *
	 *    <element v-toggle="<dataProp>"></element>
	 */
	Vue.directive("toggle", {
		bind: function( el, binding, vNode ) {
			el.addEventListener("click", function() {
				vNode.context[binding.expression] = ! vNode.context[binding.expression];
			});
		}
	});
});
