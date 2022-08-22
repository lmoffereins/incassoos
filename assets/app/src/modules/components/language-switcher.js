/**
 * Language Switcher Component
 *
 * TODO: support dropdown and buttons
 *
 * @package Incassoos
 * @subpackage App/Components
 */
define([
	"services",
	"./form/input-dropdown"
], function( services, inputDropdown ) {
	/**
	 * Holds a reference to the localization service
	 *
	 * @type {Object}
	 */
	var l10nService = services.get("l10n"),

	/**
	 * Holds the available languages
	 *
	 * @type {Object}
	 */
	availableLanguages = l10nService.getLanguages();

	return {
		props: {
			dialog: {
				type: Boolean,
				default: function() {
					return false;
				}
			}
		},
		template: '<input-dropdown class="language-switcher" v-model="selected" :items="languages" :dialog="dialogTitle"></input-dropdown>',
		components: {
			inputDropdown: inputDropdown
		},
		data: function() {
			var languages = {}, i;

			for (i in availableLanguages) {
				languages[i] = availableLanguages[i].label;
			}

			return {
				selected: l10nService.getLanguage().key,
				languages: languages
			};
		},
		computed: {
			/**
			 * Return the title for the dialog
			 *
			 * @return {String} Dialog title
			 */
			dialogTitle: function() {
				return this.dialog ? "Administration.SwitchLanguageLabel" : false;
			}
		},
		watch: {
			/**
			 * Make the selected language the active language
			 *
			 * @param {String} val Input value
			 * @return {Void}
			 */
			selected: function( val ) {

				// Save the selected language
				l10nService.setLanguage(val);
			}
		},

		/**
		 * Act when the component is created
		 *
		 * @return {Void}
		 */
		created: function() {
			var self = this;

			// When the language is set outside the component, update the selected value
			this.$registerUnobservable(
				l10nService.on("set", function( language ) {
					self.selected = language;
				})
			);
		}
	};
});
