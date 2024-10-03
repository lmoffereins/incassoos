<?php

/**
 * Incassoos Capability Functions
 * 
 * @package Incassoos
 * @subpackage Main
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Collections *********************************************************/

/**
 * Return the capability mappings for the Collection post type
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_post_type_caps'
 * @return array Collection post type caps
 */
function incassoos_get_collection_post_type_caps() {
	return apply_filters( 'incassoos_get_collection_post_type_caps', array(
		'view_post'           => 'view_incassoos_collection',
		'view_posts'          => 'view_incassoos_collections',
		'edit_post'           => 'edit_incassoos_collection',
		'edit_posts'          => 'edit_incassoos_collections',
		'edit_others_posts'   => 'edit_incassoos_collections',
		'publish_posts'       => 'publish_incassoos_collections',
		'read_private_posts'  => 'edit_incassoos_collections',
		'delete_post'         => 'delete_incassoos_collection',
		'delete_posts'        => 'delete_incassoos_collections',
		'delete_others_posts' => 'delete_incassoos_collections'
	) );
}

/** Activities **********************************************************/

/**
 * Return the capability mappings for the Activity post type
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_activity_post_type_caps'
 * @return array Activity post type caps
 */
function incassoos_get_activity_post_type_caps() {
	return apply_filters( 'incassoos_get_activity_post_type_caps', array(
		'view_post'           => 'view_incassoos_activity',
		'view_posts'          => 'view_incassoos_activities',
		'edit_post'           => 'edit_incassoos_activity',
		'edit_posts'          => 'edit_incassoos_activities',
		'edit_others_posts'   => 'edit_incassoos_activities',
		'publish_posts'       => 'publish_incassoos_activities',
		'read_private_posts'  => 'edit_incassoos_activities',
		'delete_post'         => 'delete_incassoos_activity',
		'delete_posts'        => 'delete_incassoos_activities',
		'delete_others_posts' => 'delete_incassoos_activities'
	) );
}

/**
 * Return the capability mappings for the Activity Category taxonomy
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_activity_cat_tax_caps'
 * @return array Activity Category taxonomy caps
 */
function incassoos_get_activity_cat_tax_caps() {
	return apply_filters( 'incassoos_get_activity_cat_tax_caps', array(
		'manage_terms' => 'manage_incassoos_activity_cats',
		'edit_terms'   => 'manage_incassoos_activity_cats',
		'delete_terms' => 'manage_incassoos_activity_cats',
		'assign_terms' => 'manage_incassoos_activity_cats'
	) );
}

/** Occasion ************************************************************/

/**
 * Return the capability mappings for the Occasion post type
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_occasion_post_type_caps'
 * @return array Occasion post type caps
 */
function incassoos_get_occasion_post_type_caps() {
	return apply_filters( 'incassoos_get_occasion_post_type_caps', array(
		'view_post'           => 'view_incassoos_occasion',
		'view_posts'          => 'view_incassoos_occasions',
		'edit_post'           => 'edit_incassoos_occasion',
		'edit_posts'          => 'edit_incassoos_occasions',
		'edit_others_posts'   => 'edit_incassoos_occasions',
		'publish_posts'       => 'publish_incassoos_occasions',
		'read_private_posts'  => 'edit_incassoos_occasions',
		'delete_post'         => 'delete_incassoos_occasion',
		'delete_posts'        => 'delete_incassoos_occasions',
		'delete_others_posts' => 'delete_incassoos_occasions'
	) );
}

/**
 * Return the capability mappings for the Occasion Type taxonomy
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_occasion_type_tax_caps'
 * @return array Occasion Type taxonomy caps
 */
function incassoos_get_occasion_type_tax_caps() {
	return apply_filters( 'incassoos_get_occasion_type_tax_caps', array(
		'manage_terms' => 'manage_incassoos_occasion_types',
		'edit_terms'   => 'manage_incassoos_occasion_types',
		'delete_terms' => 'manage_incassoos_occasion_types',
		'assign_terms' => 'manage_incassoos_occasion_types'
	) );
}

/** Orders ********************************************************/

/**
 * Return the capability mappings for the Order post type
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_order_post_type_caps'
 * @return array Order post type caps
 */
function incassoos_get_order_post_type_caps() {
	return apply_filters( 'incassoos_get_order_post_type_caps', array(
		'view_post'           => 'view_incassoos_order',
		'view_posts'          => 'view_incassoos_orders',
		'edit_post'           => 'edit_incassoos_order',
		'edit_posts'          => 'edit_incassoos_orders',
		'edit_others_posts'   => 'edit_incassoos_orders',
		'publish_posts'       => 'publish_incassoos_orders',
		'read_private_posts'  => 'edit_incassoos_orders',
		'delete_post'         => 'delete_incassoos_order',
		'delete_posts'        => 'delete_incassoos_orders',
		'delete_others_posts' => 'delete_incassoos_orders'
	) );
}

/** Products ************************************************************/

/**
 * Return the capability mappings for the Product post type
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_product_post_type_caps'
 * @return array Product post type caps
 */
function incassoos_get_product_post_type_caps() {
	return apply_filters( 'incassoos_get_product_post_type_caps', array(
		'read_post'           => 'view_incassoos_product',
		'view_post'           => 'view_incassoos_product',
		'view_posts'          => 'view_incassoos_products',
		'edit_post'           => 'edit_incassoos_product',
		'edit_posts'          => 'edit_incassoos_products',
		'edit_others_posts'   => 'edit_incassoos_products',
		'publish_posts'       => 'publish_incassoos_products',
		'read_private_posts'  => 'edit_incassoos_products',
		'delete_post'         => 'delete_incassoos_product',
		'delete_posts'        => 'delete_incassoos_products',
		'delete_others_posts' => 'delete_incassoos_products'
	) );
}

/**
 * Return the capability mappings for the Product Category taxonomy
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_product_cat_tax_caps'
 * @return array Product Category taxonomy caps
 */
function incassoos_get_product_cat_tax_caps() {
	return apply_filters( 'incassoos_get_product_cat_tax_caps', array(
		'manage_terms' => 'manage_incassoos_product_cats',
		'edit_terms'   => 'manage_incassoos_product_cats',
		'delete_terms' => 'manage_incassoos_product_cats',
		'assign_terms' => 'manage_incassoos_product_cats'
	) );
}

/** Consumers ***********************************************************/

/**
 * Return the capability mappings for the Consumer Type taxonomy
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_consumer_type_tax_caps'
 * @return array Consumer Type taxonomy caps
 */
function incassoos_get_consumer_type_tax_caps() {
	return apply_filters( 'incassoos_get_consumer_type_tax_caps', array(
		'manage_terms' => 'manage_incassoos_consumer_types',
		'edit_terms'   => 'manage_incassoos_consumer_types',
		'delete_terms' => 'manage_incassoos_consumer_types',
		'assign_terms' => 'manage_incassoos_consumer_types'
	) );
}

/** Mapping *************************************************************/

/**
 * Map capabilties for Collection posts and related terms
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_map_collection_caps'
 *
 * @param array $caps Mapped caps
 * @param string $cap Required capability name
 * @param int $user_id User ID
 * @param array $args Additional arguments
 * @return array Mapped caps
 */
function incassoos_map_collection_caps( $caps = array(), $cap = '', $user_id = 0, $args = array() ) {

	switch ( $cap ) {

		/** Viewing *****************************************************/

		case 'view_incassoos_collection' :

			// Defer to viewing caps
			$caps = array( 'view_incassoos_collections' );

			break;

		/** Editing *****************************************************/

		case 'edit_incassoos_collection' :

			$post = incassoos_get_collection( $args[0] );
			if ( $post ) {

				// Prevent editing locked collections
				if ( incassoos_is_collection_locked( $post ) ) {
					$caps = array( 'do_not_allow' );

				// Defer to editing caps
				} else {
					$caps = array( 'edit_incassoos_collections' );
				}
			}

			break;

		/** Collecting **************************************************/

		case 'stage_incassoos_collection' :

			$post = incassoos_get_collection( $args[0] );
			if ( $post ) {

				// Prevent collecting when there are no assets or it is already locked or it contains negative totals
				if ( ! incassoos_collection_has_assets( $post )
					|| incassoos_is_collection_locked( $post )
					|| incassoos_collection_has_consumer_with_negative_total( $post )
				) {
					$caps = array( 'do_not_allow' );

				// Defer to collecting caps
				} else {
					$caps = array( 'collect_incassoos_collections' );
				}
			}

			break;

		case 'unstage_incassoos_collection' :

			$post = incassoos_get_collection( $args[0] );
			if ( $post ) {

				// Prevent uncollecting when it is not staged yet
				if ( ! incassoos_is_collection_staged( $post ) ) {
					$caps = array( 'do_not_allow' );

				// Defer to collecting caps
				} else {
					$caps = array( 'collect_incassoos_collections' );
				}
			}

			break;

		case 'collect_incassoos_collection' :

			$post = incassoos_get_collection( $args[0] );
			if ( $post ) {

				// Prevent collecting when it is not staged yet
				if ( ! incassoos_is_collection_staged( $post ) ) {
					$caps = array( 'do_not_allow' );

				// Defer to collecting caps
				} else {
					$caps = array( 'collect_incassoos_collections' );
				}
			}

			break;

		case 'distribute_incassoos_collection' :

			$post = incassoos_get_collection( $args[0] );
			if ( $post ) {

				// Prevent collecting when it is not staged yet
				if ( ! incassoos_is_collection_collected( $post ) ) {
					$caps = array( 'do_not_allow' );

				// Defer to collecting caps
				} else {
					$caps = array( 'collect_incassoos_collections' );
				}
			}

			break;

		/** Exporting ***************************************************/

		case 'export_incassoos_collection' :

			$post = incassoos_get_collection( $args[0] );
			if ( $post ) {

				// Prevent exporting uncollected collections
				if ( ! incassoos_is_collection_collected( $post ) ) {
					$caps = array( 'do_not_allow' );

				// Defer to collecting caps
				} else {
					$caps = array( 'collect_incassoos_collections' );
				}
			}

			break;

		/** Deleting ****************************************************/

		case 'delete_incassoos_collection' :

			$post = incassoos_get_collection( $args[0] );
			if ( $post ) {

				// Prevent deleting locked collections
				if ( incassoos_is_collection_locked( $post ) ) {
					$caps = array( 'do_not_allow' );

				// Defer to deleting caps
				} else {
					$caps = array( 'delete_incassoos_collections' );
				}
			}

			break;
	}

	return apply_filters( 'incassoos_map_collection_caps', $caps, $cap, $user_id, $args );
}

/**
 * Map capabilties for Activity posts and related terms
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_map_activity_caps'
 *
 * @param array $caps Mapped caps
 * @param string $cap Required capability name
 * @param int $user_id User ID
 * @param array $args Additional arguments
 * @return array Mapped caps
 */
function incassoos_map_activity_caps( $caps = array(), $cap = '', $user_id = 0, $args = array() ) {

	switch ( $cap ) {

		/** Viewing *****************************************************/

		case 'view_incassoos_activity' :

			// Defer to viewing caps
			$caps = array( 'view_incassoos_activities' );

			break;

		/** Editing *****************************************************/

		case 'edit_incassoos_activity' :

			$post = incassoos_get_activity( $args[0] );
			if ( $post ) {

				// Prevent editing collected activities
				if ( incassoos_is_activity_collected( $post ) ) {
					$caps = array( 'do_not_allow' );

				// Defer to editing caps
				} else {
					$caps = array( 'edit_incassoos_activities' );
				}
			}

			break;

		/** Exporting ***************************************************/

		case 'export_incassoos_activity' :

			$post = incassoos_get_activity( $args[0] );
			if ( $post ) {

				// Defer to editing caps
				$caps = array( 'edit_incassoos_activities' );
			}

			break;

		/** Deleting ****************************************************/

		case 'delete_incassoos_activity' :

			$post = incassoos_get_activity( $args[0] );
			if ( $post ) {

				// Prevent deleting collected activities
				if ( incassoos_is_activity_collected( $post ) ) {
					$caps = array( 'do_not_allow' );

				// Defer to deleting caps
				} else {
					$caps = array( 'delete_incassoos_activities' );
				}
			}

			break;
	}

	return apply_filters( 'incassoos_map_activity_caps', $caps, $cap, $user_id, $args );
}

/**
 * Map capabilties for Occasion posts
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_map_occasion_caps'
 *
 * @param array $caps Mapped caps
 * @param string $cap Required capability name
 * @param int $user_id User ID
 * @param array $args Additional arguments
 * @return array Mapped caps
 */
function incassoos_map_occasion_caps( $caps = array(), $cap = '', $user_id = 0, $args = array() ) {

	switch ( $cap ) {

		/** Viewing *****************************************************/

		case 'view_incassoos_occasion' :

			// Defer to viewing caps
			$caps = array( 'view_incassoos_occasions' );

			break;

		/** Editing *****************************************************/

		case 'edit_incassoos_occasion' :

			$post = incassoos_get_occasion( $args[0] );
			if ( $post ) {

				// Prevent editing collected occasions
				if ( incassoos_is_occasion_collected( $post ) ) {
					$caps = array( 'do_not_allow' );

				// Prevent editing closed occasions
				} else if ( ! apply_filters( 'incassoos_allow_edit_closed_occasions', false, $post ) && incassoos_is_occasion_closed( $post ) ) {
					$caps = array( 'do_not_allow' );

				// Defer to editing caps
				} else {
					$caps = array( 'edit_incassoos_occasions' );
				}
			}

			break;

		case 'close_incassoos_occasion' :

			$post = incassoos_get_occasion( $args[0] );
			if ( $post ) {

				// Prevent closing locked or unpublished occasions
				if ( incassoos_is_occasion_locked( $post ) || 'publish' !== $post->post_status ) {
					$caps = array( 'do_not_allow' );

				// Defer to editing caps
				} else {
					$caps = array( 'edit_incassoos_occasions' );
				}
			}

			break;

		case 'reopen_incassoos_occasion' :

			$post = incassoos_get_occasion( $args[0] );
			if ( $post ) {

				// Prevent opening not-closed occasions
				if ( ! incassoos_is_occasion_closed( $post ) ) {
					$caps = array( 'do_not_allow' );

				// Defer to editing caps
				} else {
					$caps = array( 'edit_incassoos_occasions' );
				}
			}

			break;

		/** Exporting ***************************************************/

		case 'export_incassoos_occasion' :

			$post = incassoos_get_occasion( $args[0] );
			if ( $post ) {

				// Defer to editing caps
				$caps = array( 'edit_incassoos_occasions' );
			}

			break;

		/** Deleting ****************************************************/

		case 'delete_incassoos_occasion' :

			$post = incassoos_get_occasion( $args[0] );
			if ( $post ) {

				// Prevent deleting locked occasions
				if ( incassoos_is_occasion_locked( $post ) ) {
					$caps = array( 'do_not_allow' );

				// Prevent deleting occasions with orders
				} else if ( ! apply_filters( 'incassoos_allow_delete_occasions_with_orders', false, $post ) && incassoos_get_occasion_order_count( $post ) ) {
					$caps = array( 'do_not_allow' );

				// Defer to deleting caps
				} else {
					$caps = array( 'delete_incassoos_occasions' );
				}
			}

			break;

		/** REST API ****************************************************/

		case 'access_incassoos_rest_occasions' :

			// Defer to rest caps
			$caps = array( 'access_incassoos_rest_api' );

			break;
	}

	return apply_filters( 'incassoos_map_occasion_caps', $caps, $cap, $user_id, $args );
}

/**
 * Map capabilties for Order posts and related terms
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_map_order_caps'
 *
 * @param array $caps Mapped caps
 * @param string $cap Required capability name
 * @param int $user_id User ID
 * @param array $args Additional arguments
 * @return array Mapped caps
 */
function incassoos_map_order_caps( $caps = array(), $cap = '', $user_id = 0, $args = array() ) {

	switch ( $cap ) {

		/** Viewing *****************************************************/

		case 'view_incassoos_order' :

			// Defer to viewing caps
			$caps = array( 'view_incassoos_orders' );

			break;

		/** Editing *****************************************************/

		case 'edit_incassoos_order' :

			$post = incassoos_get_order( $args[0] );
			if ( $post ) {

				// Auto-draft post statuses to check against
				$autodrafts = array( $post->post_status );
				if ( ! empty( $_REQUEST['original_post_status'] ) ) {
					$autodrafts[] = $_REQUEST['original_post_status'];
				}

				// Prevent editing non-auto-draft orders when locked
				if ( ! in_array( 'auto-draft', $autodrafts ) && incassoos_is_order_locked( $post ) ) {
					$caps = array( 'do_not_allow' );

				// Defer to editing caps
				} else {
					$caps = array( 'edit_incassoos_orders' );
				}
			}

			break;

		/** Exporting ***************************************************/

		case 'export_incassoos_order' :

			$post = incassoos_get_order( $args[0] );
			if ( $post ) {

				// Defer to editing caps
				$caps = array( 'edit_incassoos_orders' );
			}

			break;

		/** Deleting ****************************************************/

		case 'delete_incassoos_order' :

			$post = incassoos_get_order( $args[0] );
			if ( $post ) {

				// Prevent deleting locked orders
				if ( incassoos_is_order_locked( $post ) ) {
					$caps = array( 'do_not_allow' );

				// Defer to deleting caps
				} else {
					$caps = array( 'delete_incassoos_orders' );
				}
			}

			break;

		/** REST API ****************************************************/

		case 'access_incassoos_rest_orders' :

			// Defer to rest caps
			$caps = array( 'access_incassoos_rest_api' );

			break;
	}

	return apply_filters( 'incassoos_map_order_caps', $caps, $cap, $user_id, $args );
}

/**
 * Map capabilties for Product posts and related terms
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_map_product_caps'
 *
 * @param array $caps Mapped caps
 * @param string $cap Required capability name
 * @param int $user_id User ID
 * @param array $args Additional arguments
 * @return array Mapped caps
 */
function incassoos_map_product_caps( $caps = array(), $cap = '', $user_id = 0, $args = array() ) {

	switch ( $cap ) {

		/** Viewing *****************************************************/

		case 'view_incassoos_product' :

			// Defer to viewing caps
			$caps = array( 'view_incassoos_products' );

			break;

		/** Editing *****************************************************/

		case 'edit_incassoos_product' :

			$post = incassoos_get_product( $args[0] );
			if ( $post ) {

				// Defer to editing caps
				$caps = array( 'edit_incassoos_products' );
			}

			break;

		/** Deleting ****************************************************/

		case 'delete_incassoos_product' :

			$post = incassoos_get_product( $args[0] );
			if ( $post ) {

				// Defer to deleting caps
				$caps = array( 'delete_incassoos_products' );
			}

			break;

		/** REST API ****************************************************/

		case 'access_incassoos_rest_products' :

			// Defer to rest caps
			$caps = array( 'access_incassoos_rest_api' );

			break;
	}

	return apply_filters( 'incassoos_map_product_caps', $caps, $cap, $user_id, $args );
}

/**
 * Map capabilties for generic plugin actions
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_map_generic_caps'
 *
 * @param array $caps Mapped caps
 * @param string $cap Required capability name
 * @param int $user_id User ID
 * @param array $args Additional arguments
 * @return array Mapped caps
 */
function incassoos_map_generic_caps( $caps = array(), $cap = '', $user_id = 0, $args = array() ) {

	switch ( $cap ) {

		/** Consumers/Users *********************************************/

		case 'edit_incassoos_consumers' :

			// Allow REST users
			if ( incassoos_doing_rest() && user_can( $user_id, 'access_incassoos_rest_api' ) ) {
				$caps = array( 'access_incassoos_rest_api' );

			// Defer to editing caps
			} else {
				$caps = array( 'edit_incassoos_consumers' );
			}

			break;

		case 'archive_incassoos_consumer' :
		case 'unarchive_incassoos_consumer' :
		case 'edit_incassoos_consumer' :

			// Block all non-assigned
			if ( ! user_can( $user_id, 'edit_incassoos_consumers' ) ) {
				$caps = array( 'do_not_allow' );

			// Defer to editing caps
			} else {
				$caps = array( 'edit_incassoos_consumers' );
			}

			break;

		case 'export_incassoos_consumers' :

			// Block all non-assigned
			if ( ! user_can( $user_id, 'edit_incassoos_consumers' ) ) {
				$caps = array( 'do_not_allow' );

			// Defer to editing caps
			} else {
				$caps = array( 'edit_incassoos_consumers' );
			}

			break;

		/** Application *************************************************/

		case 'view_incassoos_application' :

			// Defer to editing caps
			$caps = array( 'edit_incassoos_orders' );

			break;

		/** REST API ****************************************************/

		case 'access_incassoos_rest_consumer_types' :
		case 'access_incassoos_rest_consumers' :

			// Defer to basic rest caps
			$caps = array( 'access_incassoos_rest_api' );

			break;

		case 'access_incassoos_rest_settings' :

			// Treat as a public endpoint
			$caps = array( 'exist' );

			break;
	}

	return apply_filters( 'incassoos_map_generic_caps', $caps, $cap, $user_id, $args );
}

/**
 * Return the capabilities for the requested role
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_caps_for_role'
 *
 * @param  string $role Optional. Role to get the caps for. Defaults to none.
 * @return array Role caps.
 */
function incassoos_get_caps_for_role( $role = '' ) {

	// Define return value
	$caps = array();

	// Which role is requested?
	switch ( $role ) {

		// Collector
		case incassoos_get_collector_role() :
			$caps = array(

				// Admin
				'view_incassoos_dashboard'        => true,
				'edit_incassoos_consumers'        => true,
				'edit_incassoos_settings'         => true,

				// Encryption
				'decrypt_incassoos_data'          => true,

				// REST API
				'access_incassoos_rest_api'       => true,

				// Collection
				'view_incassoos_collections'      => true,
				'edit_incassoos_collections'      => true,
				'publish_incassoos_collections'   => true,
				'delete_incassoos_collections'    => true,
				'collect_incassoos_collections'   => true,

				// Activity
				'view_incassoos_activities'       => true,
				'edit_incassoos_activities'       => true,
				'publish_incassoos_activities'    => true,
				'delete_incassoos_activities'     => true,

				// Activity Category
				'manage_incassoos_activity_cats'  => true,

				// Occasion
				'view_incassoos_occasions'        => true,
				'edit_incassoos_occasions'        => false,
				'publish_incassoos_occasions'     => false,
				'delete_incassoos_occasions'      => false,

				// Occasion Type
				'manage_incassoos_occasion_types' => false,

				// Order
				'view_incassoos_orders'           => false,
				'edit_incassoos_orders'           => false,
				'publish_incassoos_orders'        => false,
				'delete_incassoos_orders'         => false,

				// Product
				'view_incassoos_products'         => false,
				'edit_incassoos_products'         => false,
				'publish_incassoos_products'      => false,
				'delete_incassoos_products'       => false,

				// Product Category
				'manage_incassoos_product_cats'   => false,

				// Consumer Type
				'manage_incassoos_consumer_types' => true,
			);

			break;

		// Supervisor
		case incassoos_get_supervisor_role() :
			$caps = array(

				// Admin
				'view_incassoos_dashboard'        => true,
				'edit_incassoos_consumers'        => true,
				'edit_incassoos_settings'         => true,

				// Encryption
				'decrypt_incassoos_data'          => false,

				// REST API
				'access_incassoos_rest_api'       => true,

				// Collection
				'view_incassoos_collections'      => false,
				'edit_incassoos_collections'      => false,
				'publish_incassoos_collections'   => false,
				'delete_incassoos_collections'    => false,
				'collect_incassoos_collections'   => false,

				// Activity
				'view_incassoos_activities'       => false,
				'edit_incassoos_activities'       => false,
				'publish_incassoos_activities'    => false,
				'delete_incassoos_activities'     => false,

				// Activity Category
				'manage_incassoos_activity_cats'  => false,

				// Occasion
				'view_incassoos_occasions'        => true,
				'edit_incassoos_occasions'        => true,
				'publish_incassoos_occasions'     => true,
				'delete_incassoos_occasions'      => true,

				// Occasion Type
				'manage_incassoos_occasion_types' => true,

				// Order
				'view_incassoos_orders'           => true,
				'edit_incassoos_orders'           => true,
				'publish_incassoos_orders'        => true,
				'delete_incassoos_orders'         => true,

				// Product
				'view_incassoos_products'         => true,
				'edit_incassoos_products'         => true,
				'publish_incassoos_products'      => true,
				'delete_incassoos_products'       => true,

				// Product Category
				'manage_incassoos_product_cats'   => true,

				// Consumer Type
				'manage_incassoos_consumer_types' => true,
			);

			break;

		// Registrant
		case incassoos_get_registrant_role() :
			$caps = array(

				// Admin
				'view_incassoos_dashboard'        => false,
				'edit_incassoos_consumers'        => false,
				'edit_incassoos_settings'         => false,

				// Encryption
				'decrypt_incassoos_data'          => false,

				// REST API
				'access_incassoos_rest_api'       => true,

				// Collection
				'view_incassoos_collections'      => false,
				'edit_incassoos_collections'      => false,
				'publish_incassoos_collections'   => false,
				'delete_incassoos_collections'    => false,
				'collect_incassoos_collections'   => false,

				// Activity
				'view_incassoos_activities'       => false,
				'edit_incassoos_activities'       => false,
				'publish_incassoos_activities'    => false,
				'delete_incassoos_activities'     => false,

				// Activity Category
				'manage_incassoos_activity_cats'  => false,

				// Occasion
				'view_incassoos_occasions'        => true,
				'edit_incassoos_occasions'        => true,
				'publish_incassoos_occasions'     => true,
				'delete_incassoos_occasions'      => false,

				// Occasion Type
				'manage_incassoos_occasion_types' => false,

				// Order
				'view_incassoos_orders'           => true,
				'edit_incassoos_orders'           => true,
				'publish_incassoos_orders'        => true,
				'delete_incassoos_orders'         => false,

				// Product
				'view_incassoos_products'         => true,
				'edit_incassoos_products'         => false,
				'publish_incassoos_products'      => false,
				'delete_incassoos_products'       => false,

				// Product Category
				'manage_incassoos_product_cats'   => false,

				// Consumer Type
				'manage_incassoos_consumer_types' => false,
			);

			break;
	}

	return apply_filters( 'incassoos_get_caps_for_role', $caps, $role );
}

/**
 * Return the translated capabilities
 *
 * This is primarily used by the Members plugin, but defined here for
 * easy coupling with capability definitions.
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_cap_translations'
 * @return array Translated caps
 */
function incassoos_get_cap_translations() {
	return apply_filters( 'incassoos_get_cap_translations', array(

		// Admin
		'view_incassoos_dashboard'        => _x( 'View Dashboard',             'Capability name', 'incassoos' ),
		'edit_incassoos_consumers'        => _x( 'Edit Consumers',             'Capability name', 'incassoos' ),
		'edit_incassoos_settings'         => _x( 'Edit Settings',              'Capability name', 'incassoos' ),

		// Encryption
		'decrypt_incassoos_data'          => _x( 'Decrypt data',               'Capability name', 'incassoos' ),

		// REST API
		'access_incassoos_rest_api'       => _x( 'Access REST API',            'Capability name', 'incassoos' ),

		// Collection
		'view_incassoos_collections'      => _x( 'View Collections',           'Capability name', 'incassoos' ),
		'edit_incassoos_collections'      => _x( 'Edit Collections',           'Capability name', 'incassoos' ),
		'publish_incassoos_collections'   => _x( 'Publish Collections',        'Capability name', 'incassoos' ),
		'delete_incassoos_collections'    => _x( 'Delete Collections',         'Capability name', 'incassoos' ),
		'collect_incassoos_collections'   => _x( 'Collect Collections',        'Capability name', 'incassoos' ),

		// Activity
		'view_incassoos_activities'       => _x( 'View Activities',            'Capability name', 'incassoos' ),
		'edit_incassoos_activities'       => _x( 'Edit Activities',            'Capability name', 'incassoos' ),
		'publish_incassoos_activities'    => _x( 'Publish Activities',         'Capability name', 'incassoos' ),
		'delete_incassoos_activities'     => _x( 'Delete Activities',          'Capability name', 'incassoos' ),

		// Activity Category
		'manage_incassoos_activity_cats'  => _x( 'Manage Activity Categories', 'Capability name', 'incassoos' ),

		// Occasion
		'view_incassoos_occasions'        => _x( 'View Occasions',             'Capability name', 'incassoos' ),
		'edit_incassoos_occasions'        => _x( 'Edit Occasions',             'Capability name', 'incassoos' ),
		'publish_incassoos_occasions'     => _x( 'Publish Occasions',          'Capability name', 'incassoos' ),
		'delete_incassoos_occasions'      => _x( 'Delete Occasions',           'Capability name', 'incassoos' ),

		// Occasion Type
		'manage_incassoos_occasion_types' => _x( 'Manage Occasion Types',      'Capability name', 'incassoos' ),

		// Order
		'view_incassoos_orders'           => _x( 'View Orders',                'Capability name', 'incassoos' ),
		'edit_incassoos_orders'           => _x( 'Edit Orders',                'Capability name', 'incassoos' ),
		'publish_incassoos_orders'        => _x( 'Publish Orders',             'Capability name', 'incassoos' ),
		'delete_incassoos_orders'         => _x( 'Delete Orders',              'Capability name', 'incassoos' ),

		// Product
		'view_incassoos_products'         => _x( 'View Products',              'Capability name', 'incassoos' ),
		'edit_incassoos_products'         => _x( 'Edit Products',              'Capability name', 'incassoos' ),
		'publish_incassoos_products'      => _x( 'Publish Products',           'Capability name', 'incassoos' ),
		'delete_incassoos_products'       => _x( 'Delete Products',            'Capability name', 'incassoos' ),

		// Product Category
		'manage_incassoos_product_cats'   => _x( 'Manage Product Categories',  'Capability name', 'incassoos' ),

		// Consumer Type
		'manage_incassoos_consumer_types' => _x( 'Manage Consumer Types',      'Capability name', 'incassoos' ),
	) );
}

/**
 * Adds capabilities to WordPress user roles.
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'incassoos_add_caps'
 */
function incassoos_add_caps() {

	// Loop through available roles and add caps
	foreach ( incassoos_get_wp_roles()->role_objects as $role ) {
		foreach ( incassoos_get_caps_for_role( $role->name ) as $cap => $value ) {
			$role->add_cap( $cap, $value );
		}
	}

	do_action( 'incassoos_add_caps' );
}

/**
 * Removes capabilities from WordPress user roles.
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'incassoos_remove_caps'
 */
function incassoos_remove_caps() {

	// Loop through available roles and remove caps
	foreach ( incassoos_get_wp_roles()->role_objects as $role ) {
		foreach ( array_keys( incassoos_get_caps_for_role( $role->name ) ) as $cap ) {
			$role->remove_cap( $cap );
		}
	}

	do_action( 'incassoos_remove_caps' );
}

/**
 * Get the $wp_roles global without needing to declare it everywhere
 *
 * @since 1.0.0
 *
 * @global WP_Roles $wp_roles
 *
 * @return WP_Roles
 */
function incassoos_get_wp_roles() {
	global $wp_roles;

	// Load roles if not set
	if ( ! isset( $wp_roles ) ) {
		$wp_roles = new WP_Roles();
	}

	return $wp_roles;
}

/**
 * Get the available roles minus the plugin's dynamic roles
 *
 * @since 1.0.0
 *
 * @uses incassoos_get_wp_roles() To load and get the $wp_roles global
 * @return array Blog roles
 */
function incassoos_get_blog_roles() {

	// Get WordPress's roles (returns $wp_roles global)
	$wp_roles  = incassoos_get_wp_roles();

	// Apply the WordPress 'editable_roles' filter to let plugins ride along.
	//
	// We use this internally via incassoos_filter_blog_editable_roles() to remove
	// any custom plugin roles that are added to the global.
	$the_roles = isset( $wp_roles->roles ) ? $wp_roles->roles : false;
	$all_roles = apply_filters( 'editable_roles', $the_roles );

	return apply_filters( 'incassoos_get_blog_roles', $all_roles, $wp_roles );
}

/** Roles ***************************************************************/

/**
 * Add the plugin roles to the $wp_roles global.
 *
 * We do this to avoid adding these values to the database.
 *
 * @since 1.0.0
 *
 * @param WP_Roles $wp_roles The list of WP_Role objects that was initialized
 * @return WP_Roles The main $wp_roles global
 */
function incassoos_add_plugin_roles( $wp_roles = null ) {

	// Loop through dynamic roles and add them to the $wp_roles array
	foreach ( incassoos_get_dynamic_roles() as $role_id => $details ) {
		$wp_roles->roles[$role_id]        = $details;
		$wp_roles->role_objects[$role_id] = new WP_Role( $role_id, $details['capabilities'] );
		$wp_roles->role_names[$role_id]   = $details['name'];
	}

	// Return the modified $wp_roles array
	return $wp_roles;
}

/**
 * Helper function to add filter to option_wp_user_roles
 *
 * @see _incassoos_reinit_dynamic_roles()
 *
 * @since 1.0.0
 *
 * @global WPDB $wpdb
 */
function incassoos_filter_user_roles_option() {
	global $wpdb;

	$role_key = $wpdb->prefix . 'user_roles';

	add_filter( 'option_' . $role_key, '_incassoos_reinit_dynamic_roles' );
}

/**
 * This is necessary because in a few places (noted below) WordPress initializes
 * a blog's roles directly from the database option. When this happens, the
 * $wp_roles global gets flushed, causing a user to magically lose any
 * dynamically assigned roles or capabilities when $current_user is refreshed.
 *
 * Because dynamic multiple roles is a new concept in WordPress, we work around
 * it here for now, knowing that improvements will come to WordPress core later.
 *
 * Also note that if using the $wp_user_roles global non-database approach,
 * the plugin does not have an intercept point to add its dynamic roles.
 *
 * @see switch_to_blog()
 * @see restore_current_blog()
 * @see WP_Roles::_init()
 *
 * @since 1.0.0
 *
 * @internal Used by the plugin to reinitialize dynamic roles on blog switch
 *
 * @param array $roles
 * @return array Combined array of database roles and dynamic plugin roles
 */
function _incassoos_reinit_dynamic_roles( $roles = array() ) {
	foreach ( incassoos_get_dynamic_roles() as $role_id => $details ) {
		$roles[$role_id] = $details;
	}

	return $roles;
}

/**
 * Fetch a filtered list of plugin roles that the current user is
 * allowed to have.
 *
 * Simple function who's main purpose is to allow filtering of the
 * list of plugin roles so that plugins can remove inappropriate ones
 * depending on the situation or user making edits.
 *
 * Specifically because without filtering, anyone with the edit_users
 * capability can edit others to be administrators, even if they are
 * only editors or authors. This filter allows admins to delegate
 * user management.
 *
 * @since 1.0.0
 *
 * @return array Plugin roles
 */
function incassoos_get_dynamic_roles() {
	return apply_filters( 'incassoos_get_dynamic_roles', array(

		// Collector
		incassoos_get_collector_role() => array(
			'name'         => 'Collector',
			'capabilities' => incassoos_get_caps_for_role( incassoos_get_collector_role() )
		),

		// Supervisor
		incassoos_get_supervisor_role() => array(
			'name'         => 'Supervisor',
			'capabilities' => incassoos_get_caps_for_role( incassoos_get_supervisor_role() )
		),

		// Registrant
		incassoos_get_registrant_role() => array(
			'name'         => 'Registrant',
			'capabilities' => incassoos_get_caps_for_role( incassoos_get_registrant_role() )
		)
	) );
}

/**
 * Gets a translated role name from a role ID
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_dynamic_role_name'
 *
 * @param string $role_id
 * @return string Translated role name
 */
function incassoos_get_dynamic_role_name( $role_id = '' ) {
	$roles = incassoos_get_dynamic_roles();
	$role  = isset( $roles[ $role_id ] ) ? incassoos_translate_user_role( $roles[ $role_id ]['name'] ) : '';

	return apply_filters( 'incassoos_get_dynamic_role_name', $role, $role_id, $roles );
}

/**
 * Removes the plugin roles from the editable roles array
 *
 * @since 1.0.0
 *
 * @param array $all_roles All registered roles
 * @return array 
 */
function incassoos_filter_blog_editable_roles( $all_roles = array() ) {

	// Loop through plugin roles
	foreach ( array_keys( incassoos_get_dynamic_roles() ) as $inc_role ) {

		// Loop through WordPress roles
		foreach ( array_keys( $all_roles ) as $wp_role ) {

			// If keys match, unset
			if ( $wp_role === $inc_role ) {
				unset( $all_roles[$wp_role] );
			}
		}
	}

	return $all_roles;
}

/**
 * Return the Collector role
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_collector_role'
 * @return string Role name
 */
function incassoos_get_collector_role() {
	return apply_filters( 'incassoos_get_collector_role', 'inc_collector' );
}

/**
 * Return the Supervisor role
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_supervisor_role'
 * @return string Role name
 */
function incassoos_get_supervisor_role() {
	return apply_filters( 'incassoos_get_supervisor_role', 'inc_supervisor' );
}

/**
 * Return the Registrant role
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_registrant_role'
 * @return string Role name
 */
function incassoos_get_registrant_role() {
	return apply_filters( 'incassoos_get_registrant_role', 'inc_registrant' );
}

/**
 * Return the users that are assigned to each dynamic role
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_users_for_roles'
 * @return array Users for roles
 */
function incassoos_get_users_for_roles() {
	$roles = array();

	foreach ( incassoos_get_dynamic_roles() as $role_id => $args ) {
		$roles[ $role_id ] = get_option( "_incassoos_users_for_role-{$role_id}", array() );
	}

	return apply_filters( 'incassoos_get_users_for_roles', $roles );
}

/**
 * Return the users that are assigned to a specific role
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_users_for_role'
 * @return array Users for roles
 */
function incassoos_get_users_for_role( $role_id = '' ) {
	$roles = incassoos_get_dynamic_roles();
	$users = array();

	if ( isset( $roles[ $role_id ] ) ) {
		$users = array_map( 'intval', get_option( "_incassoos_users_for_role-{$role_id}", array() ) );
	}

	return apply_filters( 'incassoos_get_users_for_role', $users, $role_id );
}

/**
 * Return the dynamic roles for which the user is assigned
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_roles_for_user'
 * @return array Roles of user
 */
function incassoos_get_roles_for_user( $user_id = 0 ) {
	$user_roles = array();

	// Default to the current user
	if ( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	foreach ( incassoos_get_users_for_roles() as $role => $users ) {
		if ( in_array( (int) $user_id, $users, true ) ) {
			$user_roles[] = $role;
		}
	}

	return apply_filters( 'incassoos_get_roles_for_user', $user_roles, $user_id );
}

/**
 * Helper function to add filter to wp_capabilities user meta
 *
 * @see _incassoos_add_dynamic_user_roles()
 *
 * @since 1.0.0
 *
 * @global WPDB $wpdb
 */
function incassoos_filter_capabilities_user_meta() {
	add_filter( 'get_user_metadata', '_incassoos_add_dynamic_user_roles', 10, 4 );
}

/**
 * This is necessary because the list of a user's roles can be directly edited
 * outside of the plugin's realm. When this happens, the user's roles are updated
 * removing the plugin's roles. To enable dynamic roles, the roles are assigned
 * in the plugin's settings and applied when the user's capabilities are retreived
 * from the user metadata.
 *
 * Because dynamic multiple roles is a new concept in WordPress, we work around
 * it here for now, knowing that improvements will come to WordPress core later.
 *
 * @since 1.0.0
 *
 * @internal Used by the plugin to add dynamic roles to a user
 *
 * @param null|array|string $check     The value get_metadata() should return - a single metadata value,
 *                                     or an array of values.
 * @param int               $object_id ID of the object metadata is for.
 * @param string            $meta_key  Metadata key.
 * @param bool              $single    Whether to return only the first value of the specified $meta_key.
 * @return mixed Combined array of database and dynamic plugin user roles or whatever `$check` contains
 */
function _incassoos_add_dynamic_user_roles( $check, $object_id, $meta_key, $single ) {
	global $wpdb;

	// Bail when this is not the right meta
	if ( $wpdb->prefix . 'capabilities' !== $meta_key ) {
		return $check;
	}

	$meta_type = 'user';

	// Get the original value from cache
	$meta_cache = wp_cache_get( $object_id, $meta_type . '_meta' );

	// Generate cache when not done already
	if ( ! $meta_cache ) {
		$meta_cache = update_meta_cache( $meta_type, array( $object_id ) );
		if ( isset( $meta_cache[ $object_id ] ) ) {
			$meta_cache = $meta_cache[ $object_id ];
		} else {
			$meta_cache = null;
		}
	}

	if ( isset( $meta_cache[ $meta_key ] ) ) {

		// Unserialize value. `single` is always true
		$meta_value = maybe_unserialize( $meta_cache[ $meta_key ][0] );
	} else {
		$meta_value = array();
	}

	// Add dynamic user roles
	foreach ( incassoos_get_roles_for_user( $object_id ) as $role ) {
		$meta_value[ $role ] = true;
	}

	// Make sure `get_metadata` finds the value at position 0
	return array( $meta_value );
}
