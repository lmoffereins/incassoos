<?php

/**
 * Incassoos Product Functions
 *
 * @package Incassoos
 * @subpackage Main
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Post Type *****************************************************************/

/**
 * Return the Product post type
 *
 * @since 1.0.0
 *
 * @return string Post type name
 */
function incassoos_get_product_post_type() {
	return incassoos()->product_post_type;
}

/**
 * Return the labels for the Product post type
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_product_post_type_labels'
 * @return array Product post type labels
 */
function incassoos_get_product_post_type_labels() {
	return apply_filters( 'incassoos_get_product_post_type_labels', array(
		'name'                  => __( 'Incassoos Products',         'incassoos' ),
		'menu_name'             => __( 'Products',                   'incassoos' ),
		'singular_name'         => __( 'Product',                    'incassoos' ),
		'all_items'             => __( 'All Products',               'incassoos' ),
		'add_new'               => __( 'New Product',                'incassoos' ),
		'add_new_item'          => __( 'Create New Product',         'incassoos' ),
		'edit'                  => __( 'Edit',                       'incassoos' ),
		'edit_item'             => __( 'Edit Product',               'incassoos' ),
		'new_item'              => __( 'New Product',                'incassoos' ),
		'view'                  => __( 'View Product',               'incassoos' ),
		'view_item'             => __( 'View Product',               'incassoos' ),
		'view_items'            => __( 'View Products',              'incassoos' ), // Since WP 4.7
		'search_items'          => __( 'Search Products',            'incassoos' ),
		'not_found'             => __( 'No products found',          'incassoos' ),
		'not_found_in_trash'    => __( 'No products found in Trash', 'incassoos' ),
		'insert_into_item'      => __( 'Insert into product',        'incassoos' ),
		'uploaded_to_this_item' => __( 'Uploaded to this product',   'incassoos' ),
		'filter_items_list'     => __( 'Filter products list',       'incassoos' ),
		'items_list_navigation' => __( 'Products list navigation',   'incassoos' ),
		'items_list'            => __( 'Products list',              'incassoos' ),
	) );
}

/**
 * Return an array of features the Product post type supports
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_product_post_type_supports'
 * @return array Product post type support
 */
function incassoos_get_product_post_type_supports() {
	return apply_filters( 'incassoos_get_product_post_type_supports', array(
		'title',
		'thumbnail',
		'page-attributes',
		'incassoos-notes'
	) );
}

/** Taxonomy: Product Category ************************************************/

/**
 * Return the Product Category taxonomy
 *
 * @since 1.0.0
 *
 * @return string Taxonomy name
 */
function incassoos_get_product_cat_tax_id() {
	return incassoos()->product_cat_tax_id;
}

/**
 * Return the labels for the Product Category taxonomy
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_product_cat_tax_labels'
 * @return array Product Category taxonomy labels
 */
function incassoos_get_product_cat_tax_labels() {
	return apply_filters( 'incassoos_get_product_cat_tax_labels', array(
		'name'                       => __( 'Incassoos Product Categories',         'incassoos' ),
		'menu_name'                  => __( 'Categories',                           'incassoos' ),
		'singular_name'              => __( 'Product Category',                     'incassoos' ),
		'search_items'               => __( 'Search Product Categories',            'incassoos' ),
		'popular_items'              => null, // Disable tagcloud
		'all_items'                  => __( 'All Product Categories',               'incassoos' ),
		'no_items'                   => __( 'No Product Category',                  'incassoos' ),
		'edit_item'                  => __( 'Edit Product Category',                'incassoos' ),
		'update_item'                => __( 'Update Product Category',              'incassoos' ),
		'add_new_item'               => __( 'Add New Product Category',             'incassoos' ),
		'new_item_name'              => __( 'New Product Category Name',            'incassoos' ),
		'view_item'                  => __( 'View Product Category',                'incassoos' ),

		'separate_items_with_commas' => __( 'Separate categories with commas',      'incassoos' ),
		'add_or_remove_items'        => __( 'Add or remove categories',             'incassoos' ),
		'choose_from_most_used'      => __( 'Choose from the most used categories', 'incassoos' ),
		'not_found'                  => __( 'No categories found.',                 'incassoos' ),
		'no_terms'                   => __( 'No categories',                        'incassoos' ),
		'items_list_navigation'      => __( 'Product categories list navigation',   'incassoos' ),
		'items_list'                 => __( 'Product categories list',              'incassoos' ),
	) );
}

/**
 * Act when the Product Category taxonomy has been registered
 *
 * @since 1.0.0
 */
function incassoos_registered_product_cat_taxonomy() {
	add_action( 'incassoos_rest_api_init', 'incassoos_register_product_cat_rest_fields' );
}

/**
 * Register REST fields for the Product Category taxonomy
 *
 * @since 1.0.0
 */
function incassoos_register_product_cat_rest_fields() {

	// Get assets
	$product = incassoos_get_product_post_type();

	// Add categories to Product
	register_rest_field(
		$product,
		'categories',
		array(
			'get_callback' => 'incassoos_get_product_rest_categories'
		)
	);
}

/**
 * Return the value for the 'categories' product REST API field
 *
 * @since 1.0.0
 *
 * @param array $object Request object
 * @param string $field_name Request field name
 * @param WP_REST_Request $request Current REST request
 * @return array Location term(s)
 */
function incassoos_get_product_rest_categories( $object, $field_name, $request ) {
	return wp_get_object_terms( $object['id'], incassoos_get_product_cat_tax_id() );
}

/**
 * Return whether the given post has any or the given Product Category
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  int|WP_Term $term Optional. Term object or ID. Defaults to any term.
 * @return bool Object has a/the Product Category
 */
function incassoos_product_has_category( $post = 0, $term = 0 ) {
	return has_term( $term, incassoos_get_product_cat_tax_id(), $post );
}

/**
 * Output the Product's category
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_product_category( $post = 0 ) {
	the_terms( $post, incassoos_get_product_cat_tax_id() );
}

/** Template ******************************************************************/

/**
 * Return the Product
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $item Optional. Post object or ID. Defaults to the current post.
 * @return WP_Post|bool Product post object or False when not found.
 */
function incassoos_get_product( $post = 0 ) {

	// Get the post
	$post = get_post( $post );

	// Return false when this is not a Product
	if ( ! $post || incassoos_get_product_post_type() !== $post->post_type ) {
		$post = false;
	}

	return $post;
}

/**
 * Output the Product's title
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_product_title( $post = 0 ) {
	echo incassoos_get_product_title( $post );
}

/**
 * Return the Product's title
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_product_title'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Product title.
 */
function incassoos_get_product_title( $post = 0 ) {
	$post  = incassoos_get_product( $post );
	$title = $post ? get_the_title( $post ) : '';

	return apply_filters( 'incassoos_get_product_title', $title, $post );
}

/**
 * Output the Product's created date
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  string      $format Optional. Timestamp's date format to return.
 */
function incassoos_the_product_created( $post = 0, $format = '' ) {
	echo incassoos_get_product_created( $post, $format );
}

/**
 * Return the Product's created date
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_product_created'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  string      $format Optional. Timestamp's date format to return.
 * @return string Product created date.
 */
function incassoos_get_product_created( $post = 0, $format = '' ) {
	$post = incassoos_get_order( $post );
	$date = $post ? $post->post_date : '';

	if ( $format ) {
		$date = mysql2date( $format, $date );
	}

	return apply_filters( 'incassoos_get_product_created', $date, $post, $format );
}

/**
 * Output the Product's menu order
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_product_menu_order( $post = 0 ) {
	echo incassoos_get_product_menu_order( $post );
}

/**
 * Return the Product's menu order
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_product_menu_order'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return int Product menu order.
 */
function incassoos_get_product_menu_order( $post = 0 ) {
	$post       = incassoos_get_product( $post );
	$menu_order = $post ? $post->menu_order : 0;

	return (int) apply_filters( 'incassoos_get_product_menu_order', $menu_order, $post );
}

/**
 * Output the Product's price
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  bool|array|null $format Optional. Whether to apply currency format. Pass array as format args. Pass
 *                                  null to skip format parsing. Defaults to false.
 */
function incassoos_the_product_price( $post = 0, $format = false ) {
	echo incassoos_get_product_price( $post, $format );
}

/**
 * Return the Product's price
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_product_price'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  bool|array|null $format Optional. Whether to apply currency format. Pass array as format args. Pass
 *                                  null to skip format parsing. Defaults to false.
 * @return string|float Product price.
 */
function incassoos_get_product_price( $post = 0, $format = false ) {
	$post  = incassoos_get_product( $post );
	$price = get_post_meta( $post ? $post->ID : 0, 'price', true );

	if ( ! $price ) {
		$price = 0;
	}

	$price = (float) apply_filters( 'incassoos_get_product_price', (float) $price, $post );

	// Apply currency format
	if ( null !== $format ) {
		$price = incassoos_parse_currency( $price, $format );
	}

	return $price;
}

/**
 * Query and return the Products
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_products'
 *
 * @param  array $args Optional. Additional query arguments for {@see WP_Query}.
 * @return array Products.
 */
function incassoos_get_products( $args = array() ) {

	// Parse query arguments
	$args = wp_parse_args( $args, array(
		'fields'         => 'ids',
		'post_type'      => incassoos_get_product_post_type(),
		'posts_per_page' => -1
	) );

	$query = new WP_Query( $args );
	$posts = $query->posts;

	// Default to empty array
	if ( ! $posts ) {
		$posts = array();
	}

	return apply_filters( 'incassoos_get_products', $posts, $args );
}

/** Update ********************************************************************/

/**
 * Update Product menu orders after post update
 *
 * This will reset and increment all the menu orders of the posts
 * following the current post.
 *
 * @since 1.0.0
 *
 * @global WPDB $wpdb
 *
 * @param  int     $post_id     Post ID
 * @param  WP_Post $post_after  Post object after update
 * @param  WP_Post $post_before Post object before update
 */
function incassoos_update_product_menu_orders( $post_id, $post_after, $post_before ) {
	global $wpdb;

	// Bail when this is not a Product
	if ( ! in_array( incassoos_get_product_post_type(), array( $post_before->post_type, $post_after->post_type ) ) )
		return;

	// Menu order changed
	if ( $post_after->menu_order !== $post_before->menu_order ) {

		// Don't be picky, update the whole set
		$products    = incassoos_get_products( array( 'fields' => 'all' ) );
		$menu_orders = array_combine( wp_list_pluck( $products, 'ID' ), wp_list_pluck( $products, 'menu_order' ) );

		// If post is ordered after a different occurence of the same
		// menu order, move the post before it.
		if ( $post_id !== array_search( $post_after->menu_order, $menu_orders ) ) {
			unset( $menu_orders[ $post_id ] );
			$menu_orders = array( $post_id => $post_after->menu_order ) + array_slice( $menu_orders, array_search( $post_after->menu_order, array_values( $menu_orders ) ), null, true );

		// Get all orderings upward of the post
		} else {
			$menu_orders = array_slice( $menu_orders, array_search( $post_id, array_keys( $menu_orders ) ), null, true );
		}

		// Setup query variable
		$wpdb->query( sprintf( "SELECT @i := %s", $post_after->menu_order - 1 ) );

		// Update all menu orders iterated by 1
		$list = implode( ',', array_keys( $menu_orders ) );
		$wpdb->query( "UPDATE {$wpdb->posts} SET menu_order = ( @i := @i + 1 ) WHERE ID IN ( $list ) ORDER BY FIELD( ID, $list )" );
	}
}

/**
 * Update the Product's price
 *
 * @since 1.0.0
 *
 * @param  float|string $price Price value.
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Update success.
 */
function incassoos_update_product_price( $price, $post = 0 ) {
	$post    = incassoos_get_product( $post );
	$success = false;

	if ( $post ) {
		$success = update_post_meta( $post->ID, 'price', (float) $price );
	}

	return $success;
}

/**
 * Return whether the provided data is valid for a Product
 *
 * @since 1.0.0
 *
 * @param  array $args Product attributes to update
 * @return WP_Error|bool Error object on invalidation, true when validated
 */
function incassoos_validate_product( $args = array() ) {
	$update = isset( $args['ID'] ) && ! empty( $args['ID'] );

	// Parse defaults
	$args = wp_parse_args( $args, array(
		'post_title' => '',
		'price'      => $update ? incassoos_get_product_price( $args['ID'], null ) : 0
	) );

	// Validate title
	$title = incassoos_validate_title( $args['post_title'] );
	if ( is_wp_error( $title ) ) {
		return $title;
	}

	// Validate price
	$price = incassoos_validate_price( $args['price'] );
	if ( is_wp_error( $price ) ) {
		return $price;
	}

	return true;
}
