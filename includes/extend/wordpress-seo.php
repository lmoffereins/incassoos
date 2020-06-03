<?php

/**
 * Incassoos WP SEO Functions
 *
 * @package Incassoos
 * @subpackage WP SEO
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Incassoos_WPSEO' ) ) :
/**
 * The Incassoos WP SEO class
 *
 * @since 1.0.0
 */
class Incassoos_WPSEO {

	/**
	 * Setup this class
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Bail when WP SEO is not active. Checking the constant,
		// because the plugin has no init sub-action of its own.
		if ( ! defined( 'WPSEO_VERSION' ) )
			return;

		$this->setup_actions();
	}

	/**
	 * Define default actions and filters
	 *
	 * @since 1.0.0
	 */
	private function setup_actions() {

		// Plugin objects
		$collection  = incassoos_get_collection_post_type();
		$activity    = incassoos_get_activity_post_type();
		$order = incassoos_get_order_post_type();
		$product     = incassoos_get_product_post_type();
		$act_cat     = incassoos_get_activity_cat_tax_id();
		$prd_cat     = incassoos_get_product_cat_tax_id();

		// Admin
		add_filter( "manage_{$collection}_posts_columns",  array( $this, 'admin_remove_columns'   ), 99    );
		add_filter( "manage_{$activity}_posts_columns",    array( $this, 'admin_remove_columns'   ), 99    );
		add_filter( "manage_{$order}_posts_columns", array( $this, 'admin_remove_columns'   ), 99    );
		add_filter( "manage_{$product}_posts_columns",     array( $this, 'admin_remove_columns'   ), 99    );
		add_filter( "manage_edit-{$act_cat}_columns",      array( $this, 'admin_remove_columns'   ), 99    );
		add_filter( "manage_edit-{$prd_cat}_columns",      array( $this, 'admin_remove_columns'   ), 99    );
		add_action( 'option_wpseo_titles',                 array( $this, 'admin_remove_metaboxes' ), 10, 2 );
		add_action( 'site_option_wpseo_titles',            array( $this, 'admin_remove_metaboxes' ), 10, 2 );
	}

	/** Public methods **************************************************/

	/**
	 * Modify the admin list table columns
	 *
	 * @since 1.0.0
	 *
	 * @param array $columns Admin columns
	 * @return array Admin columns
	 */
	public function admin_remove_columns( $columns ) {

		// Walk registered columns
		foreach ( $columns as $column => $label ) {

			// Remove WP SEO column
			if ( false !== strpos( $column, 'wpseo' ) ) {
				unset( $columns[ $column ] );
			}
		}

		return $columns;
	}

	/**
	 * Modify the wpseo_titles option value
	 *
	 * @since 1.0.0
	 *
	 * @param array $value Option value
	 * @param string $option Option name
	 * @return array Option value
	 */
	public function admin_remove_metaboxes( $value, $option ) {

		// Plugin objects
		$collection  = incassoos_get_collection_post_type();
		$activity    = incassoos_get_activity_post_type();
		$order = incassoos_get_order_post_type();
		$product     = incassoos_get_product_post_type();
		$act_cat     = incassoos_get_activity_cat_tax_id();
		$prd_cat     = incassoos_get_product_cat_tax_id();

		// Override metabox setting
		$value["hideeditbox-{$collection}"]  = true;
		$value["hideeditbox-{$activity}"]    = true;
		$value["hideeditbox-{$order}"] = true;
		$value["hideeditbox-{$product}"]     = true;
		$value["hideeditbox-tax-{$act_cat}"] = true;
		$value["hideeditbox-tax-{$prd_cat}"] = true;

		return $value;
	}
}

/**
 * Setup the extension logic for WP SEO
 *
 * @since 1.0.0
 *
 * @uses Incassoos_WPSEO
 */
function incassoos_wpseo() {
	incassoos()->extend->wpseo = new Incassoos_WPSEO;
}

endif; // class_exists
