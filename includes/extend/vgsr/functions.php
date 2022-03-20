<?php

/**
 * Incassoos VGSR Functions
 *
 * @package Incassoos
 * @subpackage VGSR
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Consumer Types ******************************************************/

/**
 * Return the Cash consumer type id
 *
 * @since 1.0.0
 *
 * @return string Cash consumer type id
 */
function incassoos_vgsr_get_cash_consumer_type_id() {
	return incassoos()->extend->vgsr->cash_consumer_type;
}

/**
 * Return the On the House consumer type id
 *
 * @since 1.0.0
 *
 * @return string On the House consumer type id
 */
function incassoos_vgsr_get_on_the_house_consumer_type_id() {
	return incassoos()->extend->vgsr->on_the_house_consumer_type;
}

/**
 * Register VGSR consumer types
 *
 * @since 1.0.0
 */
function incassoos_vgsr_register_consumer_types() {

	// Remove Guest type
	incassoos_unregister_consumer_type( incassoos_get_guest_consumer_type_id() );

	// Cash
	incassoos_register_consumer_type(
		incassoos_vgsr_get_cash_consumer_type_id(),
		array(
			'label'       => _x( 'Cash', 'Consumer type', 'incassoos' ),
			'label_count' => _nx_noop( 'Cash <span class="count">(%s)</span>', 'Cash <span class="count">(%s)</span>', 'Consumer type', 'incassoos' ),
		)
	);

	// On the House
	incassoos_register_consumer_type(
		incassoos_vgsr_get_on_the_house_consumer_type_id(),
		array(
			'label'       => _x( 'On the House', 'Consumer type', 'incassoos' ),
			'label_count' => _nx_noop( 'On the House <span class="count">(%s)</span>', 'On the House <span class="count">(%s)</span>', 'Consumer type', 'incassoos' ),
		)
	);
}

/** Export Types ********************************************************/

/**
 * Return the VGSR SFC export type id
 *
 * @since 1.0.0
 *
 * @return string VGSR SFC export type id
 */
function incassoos_vgsr_get_sfc_export_type_id() {
	return incassoos()->extend->vgsr->sfc_export_type;
}

/**
 * Register VGSR export types
 *
 * @since 1.0.0
 */
function incassoos_vgsr_register_export_types() {

	// Require class
	require_once( incassoos()->extend->vgsr->plugin_dir . 'classes/class-incassoos-vgsr-sfc-file.php' );

	// SFC
	incassoos_register_export_type(
		incassoos_vgsr_get_sfc_export_type_id(),
		array(
			'labels'     => array(
				'name'        => esc_html__( 'SFC file',        'incassoos' ),
				'export_file' => esc_html__( 'Export SFC file', 'incassoos' )
			),
			'class_name' => 'Incassoos_VGSR_SFC_File'
		)
	);
}

/** Users ***************************************************************/

/**
 * Context-aware wrapper for `is_user_vgsr()`
 *
 * @since 1.0.0
 *
 * @param int $user_id User ID. Optional. Defaults to the current user.
 * @return bool The user is VGSR lid
 */
function incassoos_is_user_vgsr( $user_id = 0 ) {
	return ( function_exists( 'vgsr' ) && is_user_vgsr( $user_id ) );
}
