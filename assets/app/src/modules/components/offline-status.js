/**
 * Offline Status Component
 *
 * @package Incassoos
 * @subpackage App/Components
 */
define([], function() {
	return {
		template: '<div id="offline-status" v-if="$offline"><h2 class="status-title" v-l10n.Generic.Offline.StatusTitle></h2><span class="status-description" v-l10n.Generic.Offline.StatusDescription></span></div>'
	};
});
