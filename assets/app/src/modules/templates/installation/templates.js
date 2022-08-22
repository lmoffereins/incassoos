/**
 * Installation Step Templates
 *
 * @package Incassoos
 * @subpackage App/Templates/Installation
 */
define([
	"./installation-start.html",
	"./installation-step-1.html",
	"./installation-step-2.html",
	"./installation-finish.html"
], function(
	installationStartTmpl,
	installationStep1Tmpl,
	installationStep2Tmpl,
	installationFinishTmpl
) {
	return {
		start: installationStartTmpl,
		step1: installationStep1Tmpl,
		step2: installationStep2Tmpl,
		finish: installationFinishTmpl
	};
});