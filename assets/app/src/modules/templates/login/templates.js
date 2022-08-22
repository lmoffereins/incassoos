/**
 * Login Part Templates
 *
 * @package Incassoos
 * @subpackage App/Templates/Login
 */
define([
	"fsm/login",
	"./login-new-login.html",
	"./login-pin-login.html",
	"./login-pin-register.html",
	"./login-pin-register-confirm.html",
	"./login-pin-verify.html",
	"./login-idle.html",
	"./login-information.html"
], function(
	fsm,
	loginNewLoginTmpl,
	loginPinLoginTmpl,
	loginPinRegisterTmpl,
	loginPinRegisterConfirmTmpl,
	loginPinVerifyTmpl,
	loginIdleTmpl,
	loginInformationTmpl
) {
	var templates = {};

	// Set templates per state name
	templates[fsm.st.NEW_LOGIN] = loginNewLoginTmpl;
	templates[fsm.st.PIN_LOGIN] = loginPinLoginTmpl;
	templates[fsm.st.PIN_REGISTER] = loginPinRegisterTmpl;
	templates[fsm.st.PIN_REGISTER_CONFIRM] = loginPinRegisterConfirmTmpl;
	templates[fsm.st.PIN_VERIFY] = loginPinVerifyTmpl;
	templates[fsm.st.IDLE] = loginIdleTmpl;
	templates[fsm.st.INFORMATION] = loginInformationTmpl;

	return templates;
});