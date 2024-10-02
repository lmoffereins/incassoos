<?php

/**
 * Incassoos Sub-action Functions
 *
 * @package Incassoos
 * @subpackage Main
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Run dedicated activation hook for this plugin
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'incassoos_activation'
 */
function incassoos_activation() {
	do_action( 'incassoos_activation' );
}

/**
 * Run dedicated deactivation hook for this plugin
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'incassoos_deactivation'
 */
function incassoos_deactivation() {
	do_action( 'incassoos_deactivation' );
}

/**
 * Run dedicated loaded hook for this plugin
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'incassoos_loaded'
 */
function incassoos_loaded() {
	do_action( 'incassoos_loaded' );
}

/**
 * Run dedicated early registration hook for this plugin
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'incassoos_register'
 */
function incassoos_register() {
	do_action( 'incassoos_register' );
}

/**
 * Run dedicated init hook for this plugin
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'incassoos_init'
 */
function incassoos_init() {
	do_action( 'incassoos_init' );
}

/**
 * Initialize roles
 *
 * @since 1.0.0
 *
 * @param WP_Roles $wp_roles The list of WP_Role objects that was initialized
 */
function incassoos_roles_init( $wp_roles ) {
	do_action( 'incassoos_roles_init', $wp_roles );
}

/**
 * Run dedicated REST API init hook for this plugin
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'incassoos_rest_api_init'
 */
function incassoos_rest_api_init() {
	do_action( 'incassoos_rest_api_init' );
}

/**
 * Run dedicated after post type registration for this plugin
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'incassoos_registered_{$type}_post_type'
 *
 * @param string $post_type
 */
function incassoos_registered_post_type( $post_type ) {

	// The plugin object type
	if ( incassoos_is_plugin_post_type( $post_type ) && $type = incassoos_get_object_type( $post_type ) ) {

		// Define hook name
		$hook = "incassoos_registered_{$type}_post_type";

		// Run setup function
		if ( is_callable( $hook ) ) {
			call_user_func( $hook );
		}

		do_action( $hook );
	}
}

/**
 * Run dedicated after taxonomy registration for this plugin
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'incassoos_registered_{$tax}_taxonomy'
 *
 * @param string $taxonomy
 */
function incassoos_registered_taxonomy( $taxonomy ) {

	// The plugin object type
	if ( incassoos_is_plugin_taxonomy( $taxonomy ) && $type = incassoos_get_object_type( $taxonomy ) ) {

		// Define hook name
		$hook = "incassoos_registered_{$type}_taxonomy";

		// Run setup function
		if ( is_callable( $hook ) ) {
			call_user_func( $hook );
		}

		do_action( $hook );
	}
}

/**
 * Run dedicated widgets hook for this plugin
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'incassoos_widgets_init'
 */
function incassoos_widgets_init() {
	do_action( 'incassoos_widgets_init' );
}

/**
 * Run dedicated hook after theme setup for this plugin
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'incassoos_after_setup_theme'
 */
function incassoos_after_setup_theme() {
	do_action( 'incassoos_after_setup_theme' );
}

/**
 * Run dedicated map meta caps filter for this plugin
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_map_meta_caps'
 *
 * @param array $caps Mapped caps
 * @param string $cap Required capability name
 * @param int $user_id User ID
 * @param array $args Additional arguments
 * @return array Mapped caps
 */
function incassoos_map_meta_caps( $caps = array(), $cap = '', $user_id = 0, $args = array() ) {
	return apply_filters( 'incassoos_map_meta_caps', $caps, $cap, $user_id, $args );
}

/**
 * Run dedicated post class filter for this plugin
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_post_class'
 *
 * @param array $classes Post class names
 * @param string $class Added class names
 * @param int $post_id Post ID
 * @return array Post class names
 */
function incassoos_post_class( $classes = array(), $class = '', $post_id = 0 ) {
	return apply_filters( 'incassoos_post_class', $classes, $class, $post_id );
}

/**
 * Run dedicated hook for the app page's head
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'incassoos_app_head'
 */
function incassoos_app_head() {
	do_action( 'incassoos_app_head' );
}

/**
 * Run dedicated hook for the app page's footer
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'incassoos_app_footer'
 */
function incassoos_app_footer() {
	do_action( 'incassoos_app_footer' );
}
