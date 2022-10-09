/**
 * Input Price Component
 *
 * @link https://www.filamentgroup.com/lab/type-number.html
 *
 * @package Incassoos
 * @subpackage App/Components/Form
 */
define([
	"util",
	"./../../templates/form/input-price.html"
], function( util, tmpl ) {
	return {
		props: {
			value: {
				default: function() {
					return 0;
				}
			}
		},
		template: tmpl,
		methods: {
			/**
			 * Emit the input's value, but debounced
			 *
			 * @param  {Number} addValue Value to add to input
			 * @return {Void}
			 */
			_emit: function( addValue ) {
				var value = this.$refs.price.value,
				    str = value.toString(),
				    addValue = addValue || 0;

				// Bail when last character is a decimal separator
				if (-1 !== [".", ","].indexOf(str.charAt(str.length - 1))) {
					return;
				}

				// Strip non-numeric characters, except decimal pointers and negative sign
				value = value.replace(/[^\d,.-]/g, "");
				value = (parseFloat(value.replace(",", ".")) + addValue) || addValue;
				value = util.sanitizePrice(value);

				// Set input sanitized input value
				this.$refs.price.value = value;

				// Emit input value
				this.$emit("input", value);
			},

			/**
			 * Emit the input's value, but debounced
			 *
			 * @return {Void}
			 */
			input: function() {
				this._emit();
			},

			/**
			 * Emit the input's value plus 0.01
			 *
			 * @return {Void}
			 */
			plus001: function() {
				this._emit(0.01);
			},

			/**
			 * Emit the input's value plus 0.1
			 *
			 * @return {Void}
			 */
			plus010: function() {
				this._emit(0.1);
			},

			/**
			 * Emit the input's value plus 1
			 *
			 * @return {Void}
			 */
			plus100: function() {
				this._emit(1);
			},

			/**
			 * Emit the input's value minus 0.01
			 *
			 * @return {Void}
			 */
			minus001: function() {
				this._emit(-0.01);
			},

			/**
			 * Emit the input's value minus 0.1
			 *
			 * @return {Void}
			 */
			minus010: function() {
				this._emit(-0.1);
			},

			/**
			 * Emit the input's value minus 1
			 *
			 * @return {Void}
			 */
			minus100: function() {
				this._emit(-1);
			}
		}
	};
});
