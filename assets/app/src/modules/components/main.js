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
		pageTitle: pageTitle,
		panels: panels,
		sections: sections
	};
});