/**
 * Input Price Component
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
			 * @return {Void}
			 */
			input: function() {
				this.$emit("input", util.sanitizePrice(this.$refs.price.value));
			},

			/**
			 * Emit the input's value plus 0.1
			 *
			 * @return {Void}
			 */
			plus010: function() {
				this.$emit("input", util.sanitizePrice(parseFloat(this.$refs.price.value) + 0.1));
			},

			/**
			 * Emit the input's value plus 1
			 *
			 * @return {Void}
			 */
			plus100: function() {
				this.$emit("input", util.sanitizePrice(parseFloat(this.$refs.price.value) + 1));
			},

			/**
			 * Emit the input's value minus 0.1
			 *
			 * @return {Void}
			 */
			minus010: function() {
				this.$emit("input", util.sanitizePrice(parseFloat(this.$refs.price.value) - 0.1));
			},

			/**
			 * Emit the input's value minus 1
			 *
			 * @return {Void}
			 */
			minus100: function() {
				this.$emit("input", util.sanitizePrice(parseFloat(this.$refs.price.value) - 1));
			}
		}
	};
});
