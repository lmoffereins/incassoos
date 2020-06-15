<?php

/**
 * Incassoos VGSR Capability Functions
 *
 * @package Incassoos
 * @subpackage VGSR
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Mapping *************************************************************/

/**
 * Modify the capabilities for the requested role
 *
 * @since 1.0.0
 *
 * @param  string $role Optional. Role to get the caps for. Defaults to none.
 * @return array Role caps.
 */
function incassoos_vgsr_filter_caps_for_role( $caps, $role ) {

	// Which role is requested?
	switch ( $role ) {

		// Controller
		case incassoos_vgsr_get_controller_role() :
			$caps = array(

				// Admin
				'view_incassoos_dashboard'        => true,
				'edit_incassoos_consumers'        => false,
				'edit_incassoos_settings'         => false,

				// REST API
				'access_incassoos_rest_api'       => true,

				// Collection
				'view_incassoos_collections'      => true,
				'edit_incassoos_collections'      => false,
				'publish_incassoos_collections'   => false,
				'delete_incassoos_collections'    => false,
				'collect_incassoos_collections'   => false,

				// Activity
				'view_incassoos_activities'       => true,
				'edit_incassoos_activities'       => false,
				'publish_incassoos_activities'    => false,
				'delete_incassoos_activities'     => false,

				// Activity Category
				'manage_incassoos_activity_cats'  => false,

				// Occasion
				'view_incassoos_occasions'        => true,
				'edit_incassoos_occasions'        => false,
				'publish_incassoos_occasions'     => false,
				'delete_incassoos_occasions'      => false,

				// Occasion Type
				'manage_incassoos_occasion_types' => false,

				// Order
				'view_incassoos_orders'           => true,
				'edit_incassoos_orders'           => false,
				'publish_incassoos_orders'        => false,
				'delete_incassoos_orders'         => false,

				// Product
				'view_incassoos_products'         => true,
				'edit_incassoos_products'         => false,
				'publish_incassoos_products'      => false,
				'delete_incassoos_products'       => false,

				// Product Category
				'manage_incassoos_product_cats'   => false,
			);

			break;
	}

	return $caps;
}

/** Roles ***************************************************************/

/**
 * Modify the list of plugin roles that the current user is
 * allowed to have.
 *
 * @since 1.0.0
 *
 * @param  array $roles Plugin roles
 * @return array Plugin roles
 */
function incassoos_vgsr_filter_dynamic_roles( $roles ) {

	// Controller
	$roles[ incassoos_vgsr_get_controller_role() ] = array(
		'name'         => 'Controller',
		'capabilities' => incassoos_get_caps_for_role( incassoos_vgsr_get_controller_role() )
	);

	return $roles;
}

/**
 * Return the Controller role
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_vgsr_get_controller_role'
 * @return string Role name
 */
function incassoos_vgsr_get_controller_role() {
	return apply_filters( 'incassoos_vgsr_get_controller_role', 'inc_vgsr_controller' );
}
