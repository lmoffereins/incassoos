/**
 * Wrapper for Vue library
 *
 * @package Incassoos
 * @subpackage App/Core
 */
define([
	"vue/dist/vue.esm",
	"../plugins/register-unobservable"
], function( Vue, registerUnobservable ) {

	// Get default export from ESM module
	Vue = Vue.default;

	// Register handling of unobservables
	Vue.use(registerUnobservable);

	return Vue;
});