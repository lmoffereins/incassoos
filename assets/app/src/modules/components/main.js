/**
 * All Components
 *
 * @package Incassoos
 * @subpackage App/Components
 */
define([
	"./dialog-container",
	"./feedback",
	"./global-cancel-toggle",
	"./global-edit-toggle",
	"./global-leave-button",
	"./global-login-toggle",
	"./offline-status",
	"./page-title",
	"./panels",
	"./sections"
], function(
	dialogContainer,
	feedback,
	globalCancelToggle,
	globalEditToggle,
	globalLeaveButton,
	globalLoginToggle,
	offlineStatus,
	pageTitle,
	panels,
	sections
) {
	return {
		dialogContainer: dialogContainer,
		feedback: feedback,
		globalCancelToggle: globalCancelToggle,
		globalEditToggle: globalEditToggle,
		globalLeaveButton: globalLeaveButton,
		globalLoginToggle: globalLoginToggle,
		offlineStatus: offlineStatus,
		pageTitle: pageTitle,
		panels: panels,
		sections: sections
	};
});