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
