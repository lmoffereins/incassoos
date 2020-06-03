<?php

/**
 * Incassoos Admin Sub-action Functions
 *
 * @package Incassoos
 * @subpackage Administration
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Run dedicated admin init hook for this plugin
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'incassoos_admin_init'
 */
function incassoos_admin_init() {
	do_action( 'incassoos_admin_init' );
}

/**
 * Run dedicated admin menu hook for this plugin
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'incassoos_admin_menu'
 */
function incassoos_admin_menu() {
	do_action( 'incassoos_admin_menu' );
}

/**
 * Run dedicated admin head hook for this plugin
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'incassoos_admin_head'
 */
function incassoos_admin_head() {
	do_action( 'incassoos_admin_head' );
}

/**
 * Run dedicated admin head hook for this plugin
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'incassoos_admin_notices'
 */
function incassoos_admin_notices() {
	do_action( 'incassoos_admin_notices' );
}
