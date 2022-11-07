/**
 * App Loading Status Component
 *
 * @package Incassoos
 * @subpackage App/Components
 */
define([], function() {
	return {
		template: '<span class="loading-status" v-l10n="status"></span>',
        computed: {
            /**
             * The status text
             *
             * @return {String}
             */
            status: function() {
                return this.$appLoadingStatus || "Loading.LoadingPage";
            }
        }
	};
});
