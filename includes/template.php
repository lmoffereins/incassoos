<?php

/**
 * Incassoos Template Functions
 * 
 * @package Incassoos
 * @subpackage Main
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Query *********************************************************************/

/**
 * Add checks for plugin conditions to parse_query action
 *
 * @since 1.0.0
 *
 * @param WP_Query $posts_query
 */
function incassoos_parse_query( $posts_query ) {

	// Bail when this is not the main loop
	if ( ! $posts_query->is_main_query() )
		return;

	// Bail when filters are suppressed on this query
	if ( true === $posts_query->get( 'suppress_filters' ) )
		return;

	// Bail when in admin
	if ( is_admin() )
		return;

	// Get plugin
	$plugin = incassoos();

	// Get query variable(s)
	$is_app = $posts_query->get( incassoos_get_app_rewrite_id() );

	// App page
	if ( $is_app ) {

		// Direct to login form when not logged in
		if ( ! is_user_logged_in() ) {
			wp_redirect( wp_login_url( incassoos_get_app_url() ) );
			exit;
		}

		// 404 and bail when the user has no access.
		if ( ! current_user_can( 'edit_incassoos_orders' ) ) {
			$posts_query->set_404();
			return;
		}

		// Looking at the app page
		$posts_query->incassoos_is_app = true;

		// Make sure 404 is not set
		$posts_query->is_404 = false;

		// Correct is_home variable
		$posts_query->is_home = false;

		// Define query result
		$posts_query->found_posts   = 1;
		$posts_query->max_num_pages = 1;
	}
}

/**
 * Handle custom query vars at parse_query action
 *
 * @since 1.0.0
 *
 * @param WP_Query $posts_query
 */
function incassoos_parse_query_vars( $posts_query ) {
	global $wpdb;

	// Bail when filters are suppressed on this query
	if ( true === $posts_query->get( 'suppress_filters' ) )
		return;

	// Get query details
	$post_type = (array) $posts_query->get( 'post_type' );

	// Order query
	if ( incassoos_get_order_post_type() === reset( $post_type ) ) {

		// Search query
		if ( $posts_query->get( 's' ) ) {

			// Get users that match search terms
			$user_ids = incassoos_get_users( array(
				'fields' => 'ID',
				// User search accepts only a flat string. Asterisks are required
				'search' =>  '*' . trim( $posts_query->get( 's' ), '*' ) . '*'
			) );

			// Ensure no results when no users were found
			if ( ! $user_ids ) {
				$user_ids = array( 0 );
			}

			// Query orders by consumer user.
			$posts_query->set( 'incassoos_consumer', $user_ids );
		}

		// Query by consumer
		if ( $consumer = $posts_query->get( 'incassoos_consumer' ) ) {
			$meta_query = (array) $posts_query->get( 'meta_query', array() );
			$meta_query[] = array(
				'key'     => 'consumer',
				'value'   => wp_parse_id_list( $consumer ),
				'compare' => 'IN'
			);
			$posts_query->set( 'meta_query', $meta_query );
		}

		// Query by consumer type
		if ( $consumer_type = $posts_query->get( 'incassoos_consumer_type' ) ) {
			$meta_query = (array) $posts_query->get( 'meta_query', array() );
			$meta_query[] = array(
				'key'     => 'consumer_type',
				'value'   => wp_parse_slug_list( $consumer_type ),
				'compare' => 'IN'
			);
			$posts_query->set( 'meta_query', $meta_query );
		}
	}

	// Product query
	if ( incassoos_get_product_post_type() === reset( $post_type ) ) {

		// Default to ordering by page number in menu_order, then title.
		// REST calls default ordering to 'date', so their ordering should
		// be handled in the client.
		if ( ! $posts_query->get( 'orderby' ) ) {
			$posts_query->set( 'orderby', array( 'menu_order' => 'ASC', 'title' => 'ASC' ) );
		}
	}

	// Filter (not) empty posts
	if ( null !== $posts_query->get( 'incassoos_empty', null ) ) {
		$meta_query = (array) $posts_query->get( 'meta_query', array() );
		$meta_query[] = array(
			'key'     => 'total',
			'value'   => 0,
			'compare' => $posts_query->get( 'incassoos_empty' ) ? '=' : '>'
		);
		$posts_query->set( 'meta_query', $meta_query );
	}
}

/**
 * Filter clause for the query's search part
 *
 * @since 1.0.0
 *
 * @param  string $search
 * @param  WP_Query $posts_query
 * @return string Search WHERE statement
 */
function incassoos_posts_search( $search, $posts_query ) {

	// Bail when filters are suppressed on this query
	if ( true === $posts_query->get( 'suppress_filters' ) )
		return;

	// Bail when there is no search
	if ( empty( $search ) ) {
		return $search;
	}

	// Get query details
	$post_type = (array) $posts_query->get( 'post_type' );

	// Order query
	if ( incassoos_get_order_post_type() === reset( $post_type ) ) {

		// Since they have no searchable post columns, stop default
		// search handling for Order posts. Search is handled in
		// `incassoos_parse_query_vars()`.
		$search = '';
	}

	return $search;
}

/**
 * Filter WHERE clause for the posts query
 *
 * @since 1.0.0
 *
 * @param  string $where
 * @param  WP_Query $posts_query
 * @return string WHERE query clause
 */
function incassoos_posts_where_paged( $where, $posts_query ) {
	global $wpdb;

	// Bail when filters are suppressed on this query
	if ( true === $posts_query->get( 'suppress_filters' ) )
		return;

	// Filter (un)collected posts
	if ( null !== $posts_query->get( 'incassoos_collected', null ) ) {
		$op     = $posts_query->get( 'incassoos_collected' ) ? '=' : '<>';
		$where .= $wpdb->prepare( " AND {$wpdb->posts}.post_status $op %s", incassoos_get_collected_status_id() );
	}

	return $where;
}

/**
 * Overwrite the main WordPress query
 *
 * @since 1.0.0
 *
 * @param string $request SQL query
 * @param WP_Query $query Query object
 * @return string SQL query
 */
function incassoos_filter_wp_query( $request, $query ) {
	global $wpdb;

	// Bail when this is not the main query
	if ( ! $query->is_main_query() )
		return $request;

	// Bail when not displaying custom query results
	if ( ! incassoos_is_app() )
		return $request;

	// Query for nothing and your chicks for free
	$request = "SELECT 1 FROM {$wpdb->posts} WHERE 0=1";

	return $request;
}

/**
 * Stop WordPress performing a DB query for its main loop
 *
 * @since 1.0.0
 *
 * @param null $retval Current return value
 * @param WP_Query $query Query object
 * @return null|array
 */
function incassoos_bypass_wp_query( $retval, $query ) {

	// Bail when this is not the main query
	if ( ! $query->is_main_query() )
		return $retval;

	// Bail when not displaying custom query results
	if ( ! incassoos_is_app() )
		return $retval;

	// Return something other than a null value to bypass WP_Query
	return array();
}

/** Is_* **********************************************************************/

/**
 * Check if current page is the App page
 *
 * @since 1.0.0
 *
 * @global WP_Query $wp_query To check if WP_Query::incassoos_is_app is true
 * @return bool Is it the App page?
 */
function incassoos_is_app() {
	global $wp_query;

	// Assume false
	$retval = false;

	// Check query
	if ( ! empty( $wp_query->incassoos_is_app ) && ( true === $wp_query->incassoos_is_app ) ) {
		$retval = true;
	}

	return (bool) $retval;
}

/** Theme *********************************************************************/

/**
 * Filter the theme's template for supporting themes
 *
 * @since 1.0.0
 *
 * @param string $template Path to template file
 * @return string Path to template file
 */
function incassoos_template_include_theme_supports( $template = '' ) {

	// App page
	if ( incassoos_is_app() ) {
		$template = incassoos_get_app_template();

		// Provide dummy post global
		incassoos_theme_compat_reset_post();
	}

	return $template;
}

/**
 * Retreive path to a template
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_{$type}_template'
 *
 * @param string $type Filename without extension.
 * @param array $templates Optional. Template candidates.
 * @return string Path to template file
 */
function incassoos_get_query_template( $type, $templates = array() ) {
	$type = preg_replace( '|[^a-z0-9-]+|', '', $type );

	// Fallback file
	if ( empty( $templates ) ) {
		$templates = array( "{$type}.php" );
	}

	// Locate template file
	$template = incassoos_locate_template( $templates );

	return apply_filters( "incassoos_{$type}_template", $template );
}

/**
 * Locate and return the App page template
 *
 * @since 1.0.0
 *
 * @return string Path to template file
 */
function incassoos_get_app_template() {
	return incassoos_get_query_template( 'app' );
}

/** Page **********************************************************************/

/**
 * Modify whether to show the admin bar
 *
 * @since 1.0.0
 *
 * @param  Boolean $show Show admin bar
 * @return Boolean Show admin bar
 */
function incassoos_show_admin_bar( $show ) {

	// App page
	if ( incassoos_is_app() ) {
		$show = false;
	}

	return $show;
}

/**
 * Modify the document title parts for plugin pages
 *
 * @since 1.0.0
 *
 * @param array $title Title parts
 * @return array Title parts
 */
function incassoos_document_title_parts( $title = array() ) {

	// App page
	if ( incassoos_is_app() ) {
		$title['title'] = esc_html_x( 'App', 'App page document title', 'incassoos' );
	}

	return $title;
}

/**
 * Enqueue plugin page scripts
 *
 * @since 1.0.0
 */
function incassoos_enqueue_scripts() {
	$inc     = incassoos();
	$version = incassoos_get_version();

	// App page
	if ( incassoos_is_app() ) {

		// Enqueue scripts
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'underscore' );
		wp_enqueue_script( 'angular', $inc->assets_url . 'js/angular.min.js', array( 'jquery' ), '1.6.2', true );
		wp_enqueue_script( 'fold-accents', $inc->assets_url . 'js/fold-accents.js', array(), $version, true );
		wp_enqueue_script( 'incassoos-app', $inc->assets_url . 'js/app.js', array( 'jquery', 'jquery-ui-datepicker', 'underscore', 'angular', 'fold-accents' ), $version, true );

		// Enqueue styles
		wp_enqueue_style( 'bootstrap', $inc->assets_url . 'css/bootstrap.min.css', array(), '3.3.7' );
		wp_enqueue_style( 'stuttter-datepicker', $inc->assets_url . 'css/datepicker.css', array(), '20150908' );
		wp_enqueue_style( 'incassoos-app', $inc->assets_url . 'css/web-app.css', array( 'bootstrap', 'stuttter-datepicker' ), $version );

		// Define consumer types
		$consumer_types = array_combine(
			incassoos_get_consumer_types(),
			array_map( 'incassoos_get_consumer_type_title', incassoos_get_consumer_types() )
		);

		// Define occasion types
		$occasion_types = incassoos_get_occasion_types();
		$occasion_types = array_combine( wp_list_pluck( $occasion_types, 'term_id' ), wp_list_pluck( $occasion_types, 'name' ) );

		// L10n
		wp_localize_script( 'incassoos-app', 'incassoosL10n', apply_filters( 'incassoos_localize_app', array(
			'settings' => array(
				'urls' => array(
					'assets'       => $inc->assets_url,
					'templates'    => $inc->themes_url,
					'consumers'    => esc_url_raw( incassoos_get_rest_url( 'consumer' ) ),
					'occasions'    => esc_url_raw( incassoos_get_rest_url( 'occasion' ) ),
					'orders'       => esc_url_raw( incassoos_get_rest_url( 'order'    ) ),
					'products'     => esc_url_raw( incassoos_get_rest_url( 'product'  ) ),
					'nonce'        => wp_create_nonce( 'wp_rest' ),
				),
				'defaultAvatar'       => 'https://www.gravatar.com/avatar/?d=mm&f=y',
				'consumerTypes'       => $consumer_types,
				'occasionTypes'       => $occasion_types,
				'occasionTypeDefault' => incassoos_get_default_occasion_type(),
				'orderTimeLock'       => incassoos_get_order_time_lock()
			),
			'l10n' => array(
				'selectConsumer'  => esc_html__( 'Select Consumer', 'incassoos' ),
				'newProduct'      => esc_html__( 'New Product',     'incassoos' ),
				'unknownConsumer' => esc_html_x( 'Unknown', 'Consumer name', 'incassoos' ),
				'unknownProduct'  => esc_html_x( 'Unknown', 'Product name',  'incassoos' )
			)
		) ) );
	}
}
