<?php

/**
 * Incassoos Members Extension
 *
 * @package Incassoos
 * @subpackage Members
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Incassoos_Members' ) ) :
/**
 * The Incassoos Members class
 *
 * @since 1.0.0
 */
class Incassoos_Members {

	/**
	 * Setup this class
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->setup_actions();
	}

	/**
	 * Define default actions and filters
	 *
	 * @since 1.0.0
	 */
	private function setup_actions() {
		add_action( 'members_register_cap_groups', array( $this, 'register_cap_groups' ) );
		add_action( 'members_register_caps',       array( $this, 'register_caps'       ) );
	}

	/** Public methods **************************************************/

	/**
	 * Register plugin cap groups
	 *
	 * @since 1.0.0
	 * @since Members 2.0.0
	 */
	public function register_cap_groups() {

		// Add main plugin cap group
		members_register_cap_group( 'incassoos', array(
			'label'    => __( 'Incassoos', 'incassoos' ),
			'icon'     => 'dashicons-forms',
			'priority' => 50
		) );

		// Remove default post type cap groups
		foreach ( array(
			incassoos_get_collection_post_type(),
			incassoos_get_activity_post_type(),
			incassoos_get_occasion_post_type(),
			incassoos_get_order_post_type(),
			incassoos_get_product_post_type()
		) as $post_type ) {
			members_unregister_cap_group( "type-{$post_type}" );
		}

		// Remove plugin caps from taxonomy cap group
		$taxonomy = members_cap_group_registry()->get( 'taxonomy' );
		$taxonomy->caps = array_diff( $taxonomy->caps, array_keys( incassoos_get_cap_translations() ) );

		members_unregister_cap_group( 'taxonomy' );
		members_register_cap_group( 'taxonomy', get_object_vars( $taxonomy ) );

	}

	/**
	 * Register plugin caps
	 *
	 * @since 1.0.0
	 * @since Members 2.0.0
	 */
	public function register_caps() {

		// Register all caps
		foreach ( incassoos_get_cap_translations() as $cap => $label ) {

			// Register with plugin group
			members_register_cap( $cap, array(
				'label' => $label,
				'group' => 'incassoos'
			) );
		}
	}
}

/**
 * Setup the extension logic for Members
 *
 * @since 1.0.0
 *
 * @uses Incassoos_Members
 */
function incassoos_members() {
	incassoos()->extend->members = new Incassoos_Members;
}

endif; // class_exists
