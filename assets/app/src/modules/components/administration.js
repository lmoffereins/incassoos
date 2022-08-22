/**
 * Administration Component
 *
 * @package Incassoos
 * @subpackage App/Components
 */
define([
	"q",
	"lodash",
	"services",
	"settings",
	"./factory-reset",
	"./form/input-datepicker",
	"./form/input-dropdown",
	"./form/input-radio-buttons",
	"./form/input-toggle",
	"./language-switcher",
	"./../templates/administration.html"
], function( Q, _, services, settings, factoryReset, inputDatepicker, inputDropdown, inputRadioButtons, inputToggle, languageSwitcher, tmpl ) {
	/**
	 * Holds a reference to the authorization service
	 *
	 * @type {Object}
	 */
	var authService = services.get("auth"),

	/**
	 * Holds a reference to the cache service
	 *
	 * @type {Object}
	 */
	cacheService = services.get("cache"),

	/**
	 * Holds a reference to the dark mode service
	 *
	 * @type {Object}
	 */
	darkmodeService = services.get("darkmode"),

	/**
	 * Holds a reference to the debug service
	 *
	 * @type {Object}
	 */
	debugService = services.get("debug"),

	/**
	 * Holds a reference to the dialog service
	 *
	 * @type {Object}
	 */
	dialogService = services.get("dialog"),

	/**
	 * Holds a reference to the feedback service
	 *
	 * @type {Object}
	 */
	feedbackService = services.get("feedback"),

	/**
	 * Holds a reference to the shortcuts service
	 *
	 * @type {Object}
	 */
	shortcutsService = services.get("shortcuts"),

	/**
	 * Holds the available administrative confirmable actions
	 *
	 * @type {Object}
	 */
	actionsToConfirm = {
		clearCache: {
			title: "Administration.AreYouSureClearCache",
			callback: function() {
				return cacheService.reset();
			},
			success: "Administration.ClearCacheSuccess",
			failed: "Administration.ClearCacheFailed"
		},
		removeUsers: {
			title: "Administration.AreYouSureRemoveUsers",
			callback: function() {
				return authService.clear(true);
			},
			success: "Administration.RemoveUsersSuccess",
			failed: "Administration.RemoveUsersFailed"
		}
	},

	/**
	 * Return callback for adding an action's feedback
	 *
	 * @param  {String} id Message identifier
	 * @param  {String} message Feedback message
	 * @return {Function} Feedback adder
	 */
	addFeedback = function( id, message ) {
		return function() {
			feedbackService.add(id, message);
		};
	},

	/**
	 * Open the confirm dialog for the action's callback(s)
	 *
	 * @param  {String} action Action name
	 * @return {Void}
	 */
	showConfirmDialog = function( action ) {
		var self = this;

		dialogService.confirm({
			title: actionsToConfirm[action].title,

			/**
			 * Execute the action's callback on dialog confirmed
			 *
			 * Will report feedback on fail or success.
			 *
			 * @return {Void}
			 */
			onConfirm: function() {
				Q.Promisify(actionsToConfirm[action].callback)
					.then(addFeedback(action, actionsToConfirm[action].success))
					.catch(addFeedback(action, actionsToConfirm[action].failed));
			},

			/**
			 * Refocus the action's element when the dialog is done
			 *
			 * @return {Void}
			 */
			onDestroy: function() {
				self.$refs[action] && self.$refs[action].focus();
			}
		});
	};

	return {
		template: tmpl,
		components: {
			factoryReset: factoryReset,
			inputDatepicker: inputDatepicker,
			inputDropdown: inputDropdown,
			inputRadioButtons: inputRadioButtons,
			inputToggle: inputToggle,
			languageSwitcher: languageSwitcher
		},
		data: function() {
			return {
				darkmodeOptions: darkmodeService.getAvailableModes(),
				debugmodeOptions: debugService.getAvailableModes()
			};
		},
		computed: {
			/**
			 * Return whether the user can remove users
			 *
			 * TODO: add capability check?
			 *
			 * @return {Boolean} Can the user remove users?
			 */
			canRemoveUsers: function() {
				return authService.isMultiUser();
			}
		},
		methods: {
			/**
			 * Clear the application's cache
			 *
			 * @return {Void}
			 */
			clearCache: function() {
				showConfirmDialog.call(this, "clearCache");
			},

			/**
			 * Remove the application's registered users
			 *
			 * @return {Void}
			 */
			removeUsers: function() {
				showConfirmDialog.call(this, "removeUsers");
			},

			/**
			 * Open the admin url in a new tab
			 *
			 * @return {Void}
			 */
			openAdmin: function() {
				window.open(settings.adminUrl, "_blank");
			},

			/**
			 * Open the shortcuts dialog
			 *
			 * @return {Void}
			 */
			showShortcuts: function() {
				var self = this;

				shortcutsService.showShortcutsDialog({
					onDestroy: function() {
						self.$refs.showShortcuts.focus();
					}
				});
			},

			/**
			 * Change the dark mode setting
			 *
			 * @param  {String} mode Mode id
			 * @return {Void}
			 */
			setDarkmode: function( mode ) {
				darkmodeService.set(mode);
			},

			/**
			 * Change the debug mode setting
			 *
			 * @param  {Boolean} mode Debug mode
			 * @return {Void}
			 */
			setDebugmode: function( mode ) {
				debugService.set(mode ? 1 : 0);
			}
		}
	};
});
