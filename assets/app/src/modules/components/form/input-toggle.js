/**
 * Input Toggle Component
 *
 * @package Incassoos
 * @subpackage App/Components/Form
 */
define([
	"./../../templates/form/input-toggle.html",
], function( tmpl ) {
	return {
		props: {
			value: {
				required: true
			},
			customStyle: {
				default: function() {
					return false;
				}
			}
		},
		template: tmpl,
		methods: {
			/**
			 * Emit the input's value
			 *
			 * @return {Void}
			 */
			input: function() {
				this.$emit("input", ! this.value);
			}
		}
	};
});
