/**
 * Page Title Component
 *
 * @package Incassoos
 * @subpackage App/Components
 */
define([], function() {
	return {
		template: '<h1 class="page-title">{{label}} <slot></slot></h1>',
		computed: {
			/**
			 * Return the occasion or default title
			 *
			 * @return {String} Title
			 */
			label: function() {
				return this.$l10n.Page.DefaultTitle;
			}
		}
	};
});
