/**
 * Input Pin Entries Component
 *
 * @package Incassoos
 * @subpackage App/Components/Form
 */
define([
	"services",
	"./../../templates/form/input-pin-entries.html",
], function( services, tmpl ) {
	return {
		props: {
			value: {
				type: String,
				required: true
			},
			length: {
				type: Number,
				default: function() {
					return services.get("auth").requiredPinLength;
				}
			}
		},
		template: tmpl,
		computed: {
			/**
			 * Return the length of the provided pin value
			 *
			 * @return {Number} Input length
			 */
			input: function() {
				return Math.min(this.value.length, this.length);
			},

			/**
			 * Return the empty length
			 *
			 * @return {Number} Empty length
			 */
			empty: function() {
				return Math.max(this.length - this.value.length, 0);
			}
		}
	};
});
