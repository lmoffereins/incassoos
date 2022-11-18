/**
 * Localization (l10n) Service Functions
 * 
 * @package Incassoos
 * @subpackage App/Services
 */
define([
	"vue",
	"q",
	"lodash",
	"dayjs",
	"util",
	"translations",
	"./storage-service",
	"./auth-service",
	"./feedback-service",
	"./shortcuts-service"
], function( Vue, Q, _, dayjs, util, translations, storageService, authService, feedbackService, shortcutsService ) {
	/**
	 * Holds the list of domains with translated strings
	 *
	 * @type {Object}
	 */
	var l10n = {},

	/**
	 * Define listener construct for the service
	 *
	 * Events triggered in this domain are:
	 *  - set (key)
	 *
	 * @type {Object}
	 */
	listeners = util.createListeners("service/l10n"),

	/**
	 * Holds the default language key
	 *
	 * @type {String}
	 */
	defaultLanguage = _.keys(translations).find(key => true === translations[key].default),

	/**
	 * Holds the currently active language key
	 *
	 * @type {String}
	 */
	activeLanguage = _.keys(translations).find(key => true === translations[key].initial),

	/**
	 * Holds the list of available languages and their labels
	 *
	 * @type {Object}
	 */
	availableLanguages = (function() {
		var list = {}, i;

		// Define translations collection
		for (i in translations) {

			// Parse defaults for each non-default translation
			l10n[i] = _.defaultsDeep(
				translations[i].translation,
				translations[defaultLanguage].translation
			);

			// Define available languages
			list[i] = {
				label: translations[i].label || i,
				alias: translations[i].alias || {}
			};
		}

		return list;
	})(),

	/**
	 * Initialization of the localization service
	 *
	 * @return {Promise} Is the service initialized?
	 */
	init = function() {

		// When the language is set, define the locale for date formatting
		listeners.on("set", function( language ) {

			// Dayjs does its own locale interpretation of the `language` format
			var alias = availableLanguages[language] && availableLanguages[language].alias.dayjs;
			alias && dayjs.locale(alias);
		});

		// When the active user is defined, make their language the active language
		authService.on("active", function( id ) {

			// Fetch the user's data
			authService.getUser(id).then( function( data ) {

				// Declare the active language
				setActiveLanguage((data && data.language) || defaultLanguage);
			});
		});

		// Register global keyboard event listeners
		shortcutsService.on({

			// Switch active language to the next available language
			"shift+alt+L": {
				label: "Administration.SwitchLanguage",
				callback: function l10nServiceSwitchLanguageOnShiftAltL() {
					var keys = _.keys(availableLanguages),
					    currKey = keys.findIndex(key => key === getLanguage().key),
					    nextKey = currKey === keys.length - 1 ? keys[0] : keys[currKey + 1];

					// Switch to the next language
					setActiveLanguage(nextKey);

					// Notify user of change
					feedbackService.add(["Administration.SwitchedLanguageTo", getLanguage(nextKey).label]);
				}
			}
		});

		// Define the initial active language from the installation language
		return storageService.get("language").then(setActiveLanguage);
	},

	/**
	 * Definition of the service's store logic
	 *
	 * @return {Object} Service store methods
	 */
	storeDefinition = function() {
		return {
			/**
			 * Modify service related properties in the main store's state
			 *
			 * @param  {Object} state Store state
			 * @return {Void}
			 */
			defineStoreState: function( state ) {
				state.l10nLanguage = activeLanguage || defaultLanguage;
			},

			/**
			 * Modify service related methods in the main store's mutations
			 *
			 * @param  {Object} mutations Store mutations
			 * @return {Void}
			 */
			defineStoreMutations: function( mutations ) {
				/**
				 * Update reactive property for l10nService's `l10n` property
				 *
				 * @return {Void}
				 */
				mutations.l10nSetLanguage = function( state ) {
					state.l10nLanguage = activeLanguage || defaultLanguage;
				};
			},

			/**
			 * Trigger service related methods in the main store's context
			 *
			 * @param  {Object} context Store context
			 * @return {Void}
			 */
			defineStoreContextUsage: function( context ) {
				/**
				 * When changing the active language, update the main store's `l10n` data
				 *
				 * @return {Function} Deregistration method
				 */
				listeners.on("set", function() {

					// Mutate the reactive `l10n` data
					context.commit("l10nSetLanguage");
				});
			}
		};
	},

	/**
	 * Return the translation of the given path
	 *
	 * @param {String} path Object path of the translated text
	 * @param {Array|String} args Optional. Text parameters to parse
	 * @return {Object|String} Translated set or text
	 */
	get = function( path, args ) {

		// Make path an array
		Array.isArray(path) || (path = path.split("."));

		// Get the translated path
		var translated = (util.path(l10n[activeLanguage], path) || path.join("."));

		// Optionally parse text parameters
		if (args && args.length && "string" === typeof translated) {
			translated = translated.sprintf(args);
		}

		return translated;
	},

	/**
	 * Return the set of available languages/translations
	 *
	 * @return {Array} List of available languages
	 */
	getLanguages = function() {
		return availableLanguages;
	},

	/**
	 * Return the active language
	 *
	 * @return {Object} Active language details
	 */
	getLanguage = function() {
		return {
			key: activeLanguage,
			label: availableLanguages[activeLanguage].label || activeLanguage
		};
	},

	/**
	 * Return the alias's language key for the active language
	 *
	 * @param  {String} alias Alias key
	 * @return {String} Alias language key
	 */
	getLanguageAlias = function( alias ) {
		if (alias && availableLanguages[activeLanguage].alias.hasOwnProperty(alias)) {
			return availableLanguages[activeLanguage].alias[alias];
		}

		return false;
	},

	/**
	 * Declare the currently active language
	 *
	 * @param {String} language Language key
	 * @return {Void}
	 */
	setActiveLanguage = function( language ) {
		if (availableLanguages.hasOwnProperty(language) && activeLanguage !== language) {
			activeLanguage = language;

			/**
			 * Trigger event listeners for any change in the currently active language.
			 *
			 * @param {String} language Active language key
			 */
			listeners.trigger("set", language);
		}
	},

	/**
	 * Save the language to the user's preferences
	 *
	 * @param  {String} language Language key
	 * @return {Promise} Was the language saved?
	 */
	setLanguage = function( language ) {
		var promise;

		// User is logged-in
		if (authService.isUserLoggedIn()) {
			promise = authService.saveUser({
				language: language
			});

		// Generic setting (i.e. on installation)
		} else {
			promise = storageService.save("language", language);
		}

		return promise.then( function() {
			setActiveLanguage(language);
		});
	},

	/**
	 * Save the default language to the global settings
	 *
	 * @param {String} language Language key
	 * @return {Promise} Was the language saved?
	 */
	setDefaultLanguage = function( language ) {
		return storageService.save("language", language);
	},

	/**
	 * Return the translation for the directive's value
	 *
	 * Use either a value or modifiers as the path to the translated text. The
	 * following examples have the same result:
	 *
	 *    <element v-l10n.path.to.nested.text></element>
	 *    <element v-l10n="'path.to.nested.text'"></element>
	 *    <element v-l10n="expressionForPathToNestedText"></element>
	 *
	 * Optionally you can provide string arguments for parsing with `String.sprintf()`:
	 *
	 *    <element v-l10n.path.to.nested.text="expressionForArg1"></element>
	 *    <element v-l10n.path.to.nested.text="'arg1,arg2'"></element>
	 *    <element v-l10n.path.to.nested.text="[expressionForArg1, expressionForArg2]"></element>
	 *    <element v-l10n="['path.to.nested.text', expressionForArg1, expressionForArg2]"></element>
	 *    <element v-l10n="[expressionForPathToNestedText, expressionForArg1, expressionForArg2]"></element>
	 *
	 * The modifier notation takes precedence over the value notation.
	 *
	 * @param  {Object} binding Binding data
	 * @param  {Object} vNode   The element's Vue node data
	 * @return {String} The parsed translation
	 */
	parseTranslationForDirective = function( binding, vNode ) {
		var withModifiers = ! _.isEmpty(binding.modifiers),

			// Try using modifiers first, else use the directive's calculated value
		    path = withModifiers ? _.keys(binding.modifiers) : (Array.isArray(binding.value) ? binding.value[0] : binding.value) || "",
		    args = withModifiers ? util.makeArray(binding.value) : (Array.isArray(binding.value) ? binding.value.slice(1) : []);

		// Make path an array
	    Array.isArray(path) || (path = path.split("."));

		// Return the translated text found at the path's location.
		// If the path is not defined, return the path itself.
		return (util.path(vNode.context.$l10n, path) || path.join(".")).sprintf(args);
	};

	/**
	 * Define `l10n` directive as a shorthand for `v-text="$l10n.path.to.nested.text"`
	 *
	 * See description of `parseTranslationForDirective()` for available
	 * notation options.
	 *
	 * The modifier notation takes precedence over the value notation.
	 */
	Vue.directive("l10n", function( el, binding, vNode ) {
		el.textContent = parseTranslationForDirective(binding, vNode);
	});

	/**
	 * Shorthand for translated title attribute
	 *
	 * See description of `parseTranslationForDirective()` for available
	 * notation options.
	 */
	Vue.directive("l10n-title", function( el, binding, vNode ) {
		el.setAttribute("title", parseTranslationForDirective(binding, vNode));
	});

	/**
	 * Shorthand for translated alt text attribute
	 *
	 * See description of `parseTranslationForDirective()` for available
	 * notation options.
	 */
	Vue.directive("l10n-alt", function( el, binding, vNode ) {
		el.setAttribute("alt", parseTranslationForDirective(binding, vNode));
	});

	/**
	 * Define `l10n` filter as a shorthand for `$l10n.get()`
	 *
	 * @param {Array} args Optional. String parameters to parse.
	 * @return {String} Localized text
	 */
	Vue.filter("l10n", function( value, args ) {
		return get(value, args);
	});

	/**
	 * Alias of the `l10n` filter
	 *
	 * @param {Array} args Optional. String parameters to parse.
	 * @return {String} Localized text
	 */
	Vue.filter("translate", function( value, args ) {
		return Vue.filter("l10n")(value, args);
	});

	/**
	 * Make the `l10n` data available at Vue's root
	 *
	 * @return {Object} L10n
	 */
	Object.defineProperty(Vue.prototype, "$l10n", {
		get: function() {
			return l10n[this.$store.state.l10nLanguage] || l10n[activeLanguage] || l10n[defaultLanguage];
		}
	});

	return {
		init: init,
		get: get,
		getLanguage: getLanguage,
		getLanguages: getLanguages,
		getLanguageAlias: getLanguageAlias,
		setLanguage: setLanguage,
		setDefaultLanguage: setDefaultLanguage,
		off: listeners.off,
		on: listeners.on,
		storeDefinition: storeDefinition
	};
});
