<?php

/**
 * Incassoos Order Functions
 *
 * @package Incassoos
 * @subpackage Main
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Post Type *****************************************************************/

/**
 * Return the Order post type
 *
 * @since 1.0.0
 *
 * @return string Post type name
 */
function incassoos_get_order_post_type() {
	return incassoos()->order_post_type;
}

/**
 * Return the labels for the Order post type
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_order_post_type_labels'
 * @return array Order post type labels
 */
function incassoos_get_order_post_type_labels() {
	return apply_filters( 'incassoos_get_order_post_type_labels', array(
		'name'                  => __( 'Incassoos Orders',          'incassoos' ),
		'menu_name'             => __( 'Orders',                    'incassoos' ),
		'singular_name'         => __( 'Order',                     'incassoos' ),
		'all_items'             => __( 'All Orders',                'incassoos' ),
		'add_new'               => __( 'New Order',                 'incassoos' ),
		'add_new_item'          => __( 'Create New Order',          'incassoos' ),
		'edit'                  => __( 'Edit',                      'incassoos' ),
		'edit_item'             => __( 'Edit Order',                'incassoos' ),
		'new_item'              => __( 'New Order',                 'incassoos' ),
		'view'                  => __( 'View Order',                'incassoos' ),
		'view_item'             => __( 'View Order',                'incassoos' ),
		'view_items'            => __( 'View Orders',               'incassoos' ), // Since WP 4.7
		'search_items'          => __( 'Search Orders',             'incassoos' ),
		'not_found'             => __( 'No orders found.',          'incassoos' ),
		'not_found_in_trash'    => __( 'No orders found in Trash.', 'incassoos' ),
		'insert_into_item'      => __( 'Insert into order',         'incassoos' ),
		'uploaded_to_this_item' => __( 'Uploaded to this order',    'incassoos' ),
		'filter_items_list'     => __( 'Filter orders list',        'incassoos' ),
		'items_list_navigation' => __( 'Orders list navigation',    'incassoos' ),
		'items_list'            => __( 'Orders list',               'incassoos' ),
	) );
}

/**
 * Act when the Order post type has been registered
 *
 * @since 1.0.0
 */
function incassoos_registered_order_post_type() {
	$post_type = incassoos_get_order_post_type();

	add_filter( "rest_pre_insert_{$post_type}", 'incassoos_rest_pre_insert_order', 10, 2 );
}

/**
 * Return an array of features the Order post type supports
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_order_post_type_supports'
 * @return array|false Order post type support or False for no support.
 */
function incassoos_get_order_post_type_supports() {
	return apply_filters( 'incassoos_get_order_post_type_supports', false );
}

/** Template ******************************************************************/

/**
 * Return the Order
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $item Optional. Post object or ID. Defaults to the current post.
 * @return WP_Post|bool Order post object or False when not found.
 */
function incassoos_get_order( $post = 0 ) {

	// Get the post
	$post = get_post( $post );

	// Return false when this is not an Order
	if ( ! $post || incassoos_get_order_post_type() !== $post->post_type ) {
		$post = false;
	}

	return $post;
}

/**
 * Output the Order's title
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_order_title( $post = 0 ) {
	echo incassoos_get_order_title( $post );
}

/**
 * Return the Order's title
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_order_title'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Order title
 */
function incassoos_get_order_title( $post = 0 ) {
	$post  = incassoos_get_order( $post );
	$title = incassoos_get_order_consumer_title( $post );

	return apply_filters( 'incassoos_get_order_title', $title, $post );
}

/**
 * Output the Order's url
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_order_url( $post = 0 ) {
	echo incassoos_get_order_url( $post );
}

/**
 * Return the Order's url
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_order_url'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Order url
 */
function incassoos_get_order_url( $post = 0 ) {
	$post = incassoos_get_order( $post );
	$url  = $post ? add_query_arg( array( 'post' => $post->ID, 'action' => 'edit' ), admin_url( 'post.php' ) ) : '';

	return apply_filters( 'incassoos_get_order_url', $url, $post );
}

/**
 * Output the Order's link
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_order_link( $post = 0 ) {
	echo incassoos_get_order_link( $post );
}

/**
 * Return the Order's link
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_order_link'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Order link
 */
function incassoos_get_order_link( $post = 0 ) {
	$post = incassoos_get_order( $post );
	$link = $post ? sprintf( '<a href="%s">%s</a>', esc_url( incassoos_get_order_url( $post ) ), incassoos_get_order_title( $post ) ) : '';

	return apply_filters( 'incassoos_get_order_link', $link, $post );
}

/**
 * Output the Order's consumer identifier
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_order_consumer( $post = 0 ) {
	echo incassoos_get_order_consumer( $post );
}

/**
 * Return the Order's consumer identifier
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return int|bool Order consumer ID or False when not found.
 */
function incassoos_get_order_consumer( $post = 0 ) {
	$post     = incassoos_get_order( $post );
	$consumer = incassoos_get_order_consumer_id( $post );

	if ( ! $consumer ) {
		$consumer = incassoos_get_order_consumer_type( $post );
	}

	return $consumer;
}

/**
 * Output the Order's consumer ID
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_order_consumer_id( $post = 0 ) {
	echo incassoos_get_order_consumer_id( $post );
}

/**
 * Return the Order's consumer ID
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_order_consumer_id'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return int|bool Order consumer ID or False when not found.
 */
function incassoos_get_order_consumer_id( $post = 0 ) {
	$post    = incassoos_get_order( $post );
	$user_id = get_post_meta( $post ? $post->ID : 0, 'consumer', true );

	if ( is_numeric( $user_id ) ) {
		$user_id = absint( $user_id );
	}

	return apply_filters( 'incassoos_get_order_consumer_id', $user_id, $post );
}

/**
 * Output the Order's consumer type
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_order_consumer_type( $post = 0 ) {
	echo incassoos_get_order_consumer_type( $post );
}

/**
 * Return the Order's consumer type
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_order_consumer_type'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Order consumer type.
 */
function incassoos_get_order_consumer_type( $post = 0 ) {
	$post = incassoos_get_order( $post );
	$type = get_post_meta( $post ? $post->ID : 0, 'consumer_type', true );

	// Default to type 'user' or empty string
	if ( ! $type ) {
		$type = incassoos_get_order_consumer_id( $post ) ? 'user' : '';
	}

	return apply_filters( 'incassoos_get_order_consumer_type', $type, $post );
}

/**
 * Return whether the Order has a custom consumer type
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Does the order have a custom consumer type?
 */
function incassoos_has_order_consumer_type( $post = 0 ) {
	return 'user' !== incassoos_get_order_consumer_type( $post );
}

/**
 * Output the Order's consumer title
 *
 * @since 1.0.0
 *
 * @param int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_order_consumer_title( $post = 0 ) {
	echo incassoos_get_order_consumer_title( $post );
}

/**
 * Return the Order's consumer title
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_order_consumer_title'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Order consumer title
 */
function incassoos_get_order_consumer_title( $post = 0 ) {
	$post     = incassoos_get_order( $post );
	$consumer = incassoos_get_order_consumer( $post );
	$title    = '';

	// Get user's display name
	if ( is_numeric( $consumer ) ) {
		$user = get_userdata( $consumer );

		if ( $user && $user->exists() ) {
			$title = $user->display_name;

		// Default to registered ID
		} else {
			$title = "[ID: $consumer]";
		}

	// Get consumer type title
	} else {
		$title = incassoos_get_consumer_type_title( $consumer );
	}

	return apply_filters( 'incassoos_get_order_consumer_title', $title, $post, $consumer );
}

/**
 * Return the Order's products
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_order_products'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return array Order products
 */
function incassoos_get_order_products( $post = 0 ) {
	$post      = incassoos_get_order( $post );
	$_products = get_post_meta( $post ? $post->ID : 0, 'products', true ); // Single meta value
	$products  = array();

	if ( empty( $_products ) ) {
		$_products = array();
	}

	// Parse default values
	foreach ( $_products as $product ) {
		$product = wp_parse_args( (array) $product, array(
			'id'     => 0,
			'name'   => _x( 'Unknown', 'Product', 'incassoos' ),
			'price'  => 0,
			'amount' => 0
		) );

		// Parse value types
		$products[ $product['id'] ] = array(
			'id'     => (int) $product['id'],
			'name'   => esc_html( $product['name'] ),
			'price'  => (float) $product['price'],
			'amount' => (int) $product['amount']
		);
	}

	return (array) apply_filters( 'incassoos_get_order_products', $products, $post );
}

/**
 * Output the Order's product count
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_order_product_count( $post = 0 ) {
	echo incassoos_get_order_product_count( $post );
}

/**
 * Return the Order's product count
 *
 * Counts negative amounts as actual absolute product counts.
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_order_product_count'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return int Order product count
 */
function incassoos_get_order_product_count( $post = 0 ) {
	$post     = incassoos_get_order( $post );
	$products = incassoos_get_order_products( $post );
	$count    = 0;

	foreach ( $products as $product ) {

		// Handle negative amounts as actual absolute product counts
		$count += absint( $product['amount'] );
	}

	return (int) apply_filters( 'incassoos_get_order_product_count', $count, $post );
}

/**
 * Output the Order's product list
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  string $sep Optional. List separator.
 */
function incassoos_the_order_product_list( $post = 0, $sep = ', ' ) {
	echo incassoos_get_order_product_list( $post, $sep );
}

/**
 * Return the Order's product list
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_order_product_list'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  string $sep Optional. List separator.
 * @return string Order product list
 */
function incassoos_get_order_product_list( $post = 0, $sep = ', ' ) {
	$post     = incassoos_get_order( $post );
	$products = incassoos_get_order_products( $post );
	$list     = array();

	foreach ( $products as $product ) {
		$list[] = $product['name'] . ' (' . $product['amount'] . ')';
	}

	return apply_filters( 'incassoos_get_order_product_list', implode( $sep, $list ), $list, $post, $sep );
}

/**
 * Output the Order's total price
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  bool|array|null $num_format Optional. Whether to apply currency format. Pass array as format args. Pass
 *                                     null to skip format parsing. Defaults to false.
 */
function incassoos_the_order_total( $post = 0, $num_format = false ) {
	echo incassoos_get_order_total( $post, $num_format );
}

/**
 * Return the Order's total price
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_order_total'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  bool|array|null $num_format Optional. Whether to apply currency format. Pass array as format args. Pass
 *                                     null to skip format parsing. Defaults to false.
 * @return string|float Order total price.
 */
function incassoos_get_order_total( $post = 0, $num_format = false ) {
	$post  = incassoos_get_order( $post );
	$total = get_post_meta( $post ? $post->ID : 0, 'total', true );

	// Get total from raw calculation
	if ( false === $total && $post ) {
		$total = incassoos_get_order_total_raw( $post );
		update_post_meta( $post->ID, 'total', $total );
	}

	$total = (float) apply_filters( 'incassoos_get_order_total', (float) $total, $post );

	// Apply currency format
	if ( null !== $num_format ) {
		$total = incassoos_parse_currency( $total, $num_format );
	}

	return $total;
}

/**
 * Return the Order's raw total price
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_order_total_raw'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return float Order raw total price.
 */
function incassoos_get_order_total_raw( $post = 0 ) {
	$post     = incassoos_get_order( $post );
	$products = incassoos_get_order_products( $post );
	$total    = incassoos_get_total_from_products( $products );

	return (float) apply_filters( 'incassoos_get_order_total_raw', $total, $post );
}

/**
 * Output the Order's author name
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_order_author( $post = 0 ) {
	echo incassoos_get_order_author( $post );
}

/**
 * Return the Order's author name
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_order_author'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Order author name or False when not found.
 */
function incassoos_get_order_author( $post = 0 ) {
	$post   = incassoos_get_order( $post );
	$author = get_userdata( $post ? $post->post_author : 0 );

	if ( $author && $author->exists() ) {
		$author = $author->display_name;
	} else {
		$author = '';
	}

	return apply_filters( 'incassoos_get_order_author', $author, $post );
}

/**
 * Output the Order's created date
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  string      $date_format Optional. Timestamp's date format to return.
 */
function incassoos_the_order_created( $post = 0, $date_format = '' ) {
	echo incassoos_get_order_created( $post, $date_format );
}

/**
 * Return the Order's created date
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_order_created'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  string      $date_format Optional. Timestamp's date format to return.
 * @return string Order created date.
 */
function incassoos_get_order_created( $post = 0, $date_format = '' ) {
	$post = incassoos_get_order( $post );
	$date = $post ? $post->post_date : '';

	// Default to the registered date format
	if ( empty( $date_format ) ) {
		$date_format = get_option( 'date_format' );
	}

	if ( $date_format ) {
		$date = mysql2date( $date_format, $date );
	}

	return apply_filters( 'incassoos_get_order_created', $date, $post, $date_format );
}

/**
 * Return whether the Order is closed
 *
 * An Order can be locked because of the time lock or when its parent Occasion is closed.
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_is_order_closed'
 *
 * @param int|WP_Post $post Post object or ID
 * @return bool Is the Order closed?
 */
function incassoos_is_order_closed( $post = 0 ) {
	$post      = incassoos_get_order( $post );
	$time_lock = incassoos_get_order_time_lock();
	$closed    = incassoos_is_order_occasion_closed( $post );

	if ( ! $closed && $post && $time_lock ) {

		// Handle undefined gmt dates
		if ( '0000-00-00 00:00:00' === $post->post_date_gmt ) {
			$post_date_gmt = get_gmt_from_date( $post->post_date );
		} else {
			$post_date_gmt = $post->post_date_gmt;
		}

		// `time()` always returns UTC, so compare with GMT date
		$closed = time() > (strtotime( $post_date_gmt ) + ( 60 * $time_lock ));
	}

	return (bool) apply_filters( 'incassoos_is_order_closed', $closed, $post );
}

/**
 * Return whether the Order is collected
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_is_order_collected'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Order is collected
 */
function incassoos_is_order_collected( $post = 0 ) {
	$post      = incassoos_get_order( $post );
	$collected = $post && ( incassoos_get_collected_status_id() === $post->post_status );

	return (bool) apply_filters( 'incassoos_is_order_collected', $collected, $post );
}

/**
 * Return whether the Order is locked
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_is_order_locked'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Order is locked
 */
function incassoos_is_order_locked( $post = 0 ) {
	$post   = incassoos_get_order( $post );
	$locked = $post && ( incassoos_is_order_closed( $post ) || incassoos_is_order_collected( $post ) );

	return (bool) apply_filters( 'incassoos_is_order_locked', $locked, $post );
}

/**
 * Return whether the Order is collectable
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_is_order_collectable'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Order is collectable
 */
function incassoos_is_order_collectable( $post = 0 ) {
	$post        = incassoos_get_order( $post );
	$collectable = $post && ! incassoos_is_order_collected( $post );

	return (bool) apply_filters( 'incassoos_is_order_collectable', $collectable, $post );	
}

/**
 * Query and return the Orders
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_orders'
 *
 * @param  array $query_args Optional. Additional query arguments for {@see WP_Query}.
 * @return array Orders.
 */
function incassoos_get_orders( $query_args = array() ) {

	// Parse query arguments
	$query_args = wp_parse_args( $query_args, array(
		'fields'         => 'ids',
		'post_type'      => incassoos_get_order_post_type(),
		'posts_per_page' => -1
	) );

	$query = new WP_Query( $query_args );
	$posts = $query->posts;

	// Default to empty array
	if ( ! $posts ) {
		$posts = array();
	}

	return apply_filters( 'incassoos_get_orders', $posts, $query_args );
}

/**
 * Return the uncollected Orders
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_uncollected_orders'
 *
 * @param  array $query_args Optional. Additional query arguments for {@see WP_Query}.
 * @return array Uncollected Orders
 */
function incassoos_get_uncollected_orders( $query_args = array() ) {

	// Define query arguments
	$query_args['incassoos_collected'] = false;

	// Query posts
	$posts = incassoos_get_orders( $query_args );

	return apply_filters( 'incassoos_get_uncollected_orders', $posts, $query_args );
}

/** Filters *******************************************************************/

/**
 * Modify the Order's post title
 *
 * @since 1.0.0
 *
 * @param  string $title   Post title
 * @param  int    $post_id Post ID
 * @return string          Post title
 */
function incassoos_filter_order_title( $title, $post_id ) {

	// When this is an Order
	if ( incassoos_get_order( $post_id ) ) {
		$title = incassoos_get_order_title( $post_id );
	}

	return $title;
}

/**
 * Modify the Order's post class
 *
 * @since 1.0.0
 *
 * @param  array       $classes Post class names
 * @param  string      $class   Added class names
 * @param  int|WP_Post $post_id Post ID
 * @return array       Post class names
 */
function incassoos_filter_order_class( $classes, $class, $post_id ) {
	$post = incassoos_get_order( $post_id );

	// When this is an Order
	if ( $post ) {

		// Order is closed
		if ( incassoos_is_order_closed( $post ) ) {
			$classes[] = 'order-closed';
		}

		// Order is collected
		if ( incassoos_is_order_collected( $post ) ) {
			$classes[] = 'order-collected';
		}

		// Order is time locked
		if ( incassoos_is_order_locked( $post ) ) {
			$classes[] = 'order-locked';
		}
	}

	return $classes;
}

/** Occasion *******************************************************************/

/**
 * Return the Order's Occasion post
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return WP_Post|false Occasion post object or False when not found.
 */
function incassoos_get_order_occasion( $post = 0 ) {
	$post     = incassoos_get_order( $post );
	$occasion = incassoos_get_order_occasion_id( $post );

	return incassoos_get_occasion( $occasion );
}

/**
 * Output the Order's Occasion ID
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_order_occasion_id( $post = 0 ) {
	echo incassoos_get_order_occasion_id( $post );
}

/**
 * Return the Order's Occasion ID
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_order_occasion_id'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return int Order Occasion ID.
 */
function incassoos_get_order_occasion_id( $post = 0 ) {
	$post    = incassoos_get_order( $post );
	$post_id = $post ? $post->post_parent : 0;

	return (int) apply_filters( 'incassoos_get_order_occasion_id', $post_id, $post );
}

/**
 * Return whether the Order has an Occasion
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_order_has_occasion'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Does Order have an Occasion?
 */
function incassoos_order_has_occasion( $post = 0 ) {
	$post     = incassoos_get_order( $post );
	$occasion = incassoos_get_order_occasion( $post );

	return (bool) apply_filters( 'incassoos_order_has_occasion', (bool) $occasion, $post );
}

/**
 * Output the Order's Occasion title
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_order_occasion_title( $post = 0 ) {
	echo incassoos_get_order_occasion_title( $post );
}

/**
 * Return the Order's Occasion title
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Order Occasion title.
 */
function incassoos_get_order_occasion_title( $post = 0 ) {
	$occasion = incassoos_get_order_occasion( $post );
	$title    = $occasion ? incassoos_get_occasion_title( $occasion ) : '';

	return $title;
}

/**
 * Output the Order's Occasion date
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  string      $date_format Optional. Timestamp's date format to return. Defaults to the `date_format` option.
 */
function incassoos_the_order_occasion_date( $post = 0, $date_format = '' ) {
	echo incassoos_get_order_occasion_date( $post, $date_format );
}

/**
 * Return the Order's Occasion date
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  string      $date_format Optional. Timestamp's date format to return. Defaults to the `date_format` option.
 * @return string Order Occasion date.
 */
function incassoos_get_order_occasion_date( $post = 0, $date_format = '' ) {
	$occasion = incassoos_get_order_occasion( $post );
	$date     = $occasion ? incassoos_get_occasion_date( $occasion, $date_format ) : '';

	return $date;
}

/**
 * Output the Order's Occasion url
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_order_occasion_url( $post = 0 ) {
	echo incassoos_get_order_occasion_url( $post );
}

/**
 * Return the Order's Occasion url
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Order Occasion url.
 */
function incassoos_get_order_occasion_url( $post = 0 ) {
	$occasion = incassoos_get_order_occasion( $post );
	$url      = $occasion ? incassoos_get_occasion_url( $occasion ) : '';

	return $url;
}

/**
 * Output the Order's Occasion link
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_order_occasion_link( $post = 0 ) {
	echo incassoos_get_order_occasion_link( $post );
}

/**
 * Return the Order's Occasion link
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Order Occasion link.
 */
function incassoos_get_order_occasion_link( $post = 0 ) {
	$occasion = incassoos_get_order_occasion( $post );
	$link     = $occasion ? incassoos_get_occasion_link( $occasion ) : '';

	return $link;
}

/**
 * Return whether the Order's Occasion is closed
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Is the Order's Occasion closed?
 */
function incassoos_is_order_occasion_closed( $post = 0 ) {
	$occasion = incassoos_get_order_occasion( $post );
	$closed   = $occasion ? incassoos_is_occasion_closed( $occasion ) : '';

	return $closed;
}

/**
 * Return whether the Order's Occasion is collected
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Is the Order's Occasion collected?
 */
function incassoos_is_order_occasion_collected( $post = 0 ) {
	$occasion  = incassoos_get_order_occasion( $post );
	$collected = $occasion ? incassoos_is_occasion_collected( $occasion ) : '';

	return $collected;
}

/**
 * Return whether the Order's Occasion is locked
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Is the Order's Occasion locked?
 */
function incassoos_is_order_occasion_locked( $post = 0 ) {
	$occasion = incassoos_get_order_occasion( $post );
	$locked   = $occasion ? incassoos_is_occasion_locked( $occasion ) : '';

	return $locked;
}

/** Collection ****************************************************************/

/**
 * Return the Order's Collection post
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return WP_Post|false Collection post object or False when not found.
 */
function incassoos_get_order_collection( $post = 0 ) {
	$post       = incassoos_get_order( $post );
	$collection = incassoos_get_order_collection_id( $post );

	return incassoos_get_collection( $collection );
}

/**
 * Output the Order's collection ID
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_order_collection_id( $post = 0 ) {
	echo incassoos_get_order_collection_id( $post );
}

/**
 * Return the Order's Collection ID
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_order_collection_id'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return int Order's Collection ID
 */
function incassoos_get_order_collection_id( $post = 0 ) {
	$post        = incassoos_get_order( $post );
	$occasion_id = $post ? incassoos_get_order_occasion_id( $post ) : 0;
	$post_id     = $occasion_id ? incassoos_get_occasion_collection_id( $occasion_id ) : 0;

	return (int) apply_filters( 'incassoos_get_order_collection_id', $post_id, $post );
}

/**
 * Output the Order's Collection title
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_order_collection_title( $post = 0 ) {
	echo incassoos_get_order_collection_title( $post );
}

/**
 * Return the Order's Collection title
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Order's Collection title
 */
function incassoos_get_order_collection_title( $post = 0 ) {
	$collection = incassoos_get_order_collection( $post );
	$title      = $collection ? incassoos_get_collection_title( $collection ) : '';

	return $title;
}

/**
 * Output the Order's Collection collected date
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  string      $date_format Optional. Timestamp's date format to return. Defaults to the `date_format` option.
 */
function incassoos_the_order_collection_date( $post = 0, $date_format = '' ) {
	echo incassoos_get_order_collection_date( $post, $date_format );
}

/**
 * Return the Order's Collection collected date
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  string      $date_format Optional. Timestamp's date format to return. Defaults to the `date_format` option.
 * @return string Order's Collection collected date
 */
function incassoos_get_order_collection_date( $post = 0, $date_format = '' ) {
	$collection = incassoos_get_order_collection( $post );
	$date       = $collection ? incassoos_get_collection_date( $collection, $date_format ) : '';

	return $date;
}

/**
 * Output the Order's Collection url
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_order_collection_url( $post = 0 ) {
	echo incassoos_get_order_collection_url( $post );
}

/**
 * Return the Order's Collection url
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Order's Collection url
 */
function incassoos_get_order_collection_url( $post = 0 ) {
	$collection = incassoos_get_order_collection( $post );
	$url        = $collection ? incassoos_get_collection_url( $collection ) : '';

	return $url;
}

/**
 * Output the Order's Collection link
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_order_collection_link( $post = 0 ) {
	echo incassoos_get_order_collection_link( $post );
}

/**
 * Return the Order's Collection link
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Order's Collection link
 */
function incassoos_get_order_collection_link( $post = 0 ) {
	$collection = incassoos_get_order_collection( $post );
	$link       = $collection ? incassoos_get_collection_link( $collection ) : '';

	return $link;
}

/**
 * Output the Order's Collection hint
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_order_collection_hint( $post = 0 ) {
	echo incassoos_get_order_collection_hint( $post );
}

/**
 * Return the Order's Collection hint
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Order's Collection hint
 */
function incassoos_get_order_collection_hint( $post = 0 ) {
	$collection = incassoos_get_order_collection( $post );
	$hint       = incassoos_get_collection_hint( $collection ); // Provides default value

	return $hint;
}

/**
 * Return whether the Order's Collection is collected
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Order's Collection is collected
 */
function incassoos_is_order_collection_collected( $post = 0 ) {
	$collection = incassoos_get_order_collection( $post );
	$collected  = $collection ? incassoos_is_collection_collected( $collection ) : false;

	return $collected;
}

/** Update ********************************************************************/

/**
 * Update the Order's consumer
 *
 * @since 1.0.0
 *
 * @param  int|string $consumer Consumer user ID or consumer type.
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Update success.
 */
function incassoos_update_order_consumer( $consumer, $post = 0 ) {
	$post    = incassoos_get_order( $post );
	$success = false;

	if ( $post ) {
		$_consumer = is_numeric( $consumer ) ? get_userdata( $consumer ) : false;

		// Save consumer
		if ( $_consumer && $_consumer->exists() ) {
			$prev_value = incassoos_get_order_consumer_id( $post );

			// Bail when the stored value is identical to avoid update_metadata() returning false.
			if ( $_consumer->ID === $prev_value ) {
				return true;
			}

			$success = update_post_meta( $post->ID, 'consumer', $_consumer->ID );

			// Remove any previous consumer type
			if ( $success ) {
				delete_post_meta( $post->ID, 'consumer_type' );
			}

		// Save consumer type
		} elseif ( $consumer_type = incassoos_get_consumer_type( $consumer ) ) {
			$prev_value = incassoos_get_order_consumer_type( $post );

			// Bail when the stored value is identical to avoid update_metadata() returning false.
			if ( $consumer_type->ID === $prev_value ) {
				return true;
			}

			$success = update_post_meta( $post->ID, 'consumer_type', $consumer_type->id );

			// Remove any previous consumer ID
			if ( $success ) {
				delete_post_meta( $post->ID, 'consumer' );
			}
		}
	}

	return $success;
}

/**
 * Sanitize products for an Order
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_sanitize_order_products'
 *
 * @param  array $products Product details
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return array Sanitized product details
 */
function incassoos_sanitize_order_products( $products, $post = 0 ) {
	$post      = incassoos_get_order( $post );
	$sanitized = array();

	// Parse products
	foreach ( (array) $products as $input ) {
		$input = wp_parse_args( $input, array(
			'id'     => 0,
			'name'   => false,
			'price'  => 0,
			'amount' => 0
		) );

		// Skip when missing data
		if ( ( empty( $input['id'] ) && empty( $input['name'] ) ) || empty( $input['amount'] ) ) {
			continue;
		}

		// Fill the defaults when the product is found
		if ( ! empty( $input['id'] ) && $product = incassoos_get_product( $input['id'] ) ) {

			// Default the product name
			if ( empty( $input['name'] ) ) {
				$input['name'] = incassoos_get_product_title( $product );
			}

			// Default the product price
			if ( empty( $input['price'] ) ) {
				$input['price'] = incassoos_get_product_price( $product );
			}

		// When the product is not known
		} else {

			// Name fallback
			if ( empty( $input['name'] ) ) {
				$input['name'] = _x( 'Unknown', 'Product', 'incassoos' );
			}
		}

		// Parse types
		$input['id']     = (int) $input['id'];
		$input['price']  = (float) $input['price'];
		$input['amount'] = (int) $input['amount'];

		$sanitized[] = $input;
	}

	return apply_filters( 'incassoos_sanitize_order_products', $sanitized, $products, $post );
}

/**
 * Return whether the value is a valid order products set
 *
 * @since 1.0.0
 *
 * @param  mixed $value Value to validate
 * @return mixed|WP_Error Validated order products or error when invalid
 */
function incassoos_validate_order_products( $value ) {
	$products = incassoos_sanitize_order_products( $value );

	if ( empty( $products ) ) {
		return new WP_Error( 'incassoos_order_invalid_products', __( 'Invalid order products.', 'incassoos' ) );
	}

	return $value;
}

/**
 * Update the Order's products
 *
 * @since 1.0.0
 *
 * @param  array $products Product details
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Update success.
 */
function incassoos_update_order_products( $products, $post = 0 ) {
	$post    = incassoos_get_order( $post );
	$success = false;

	if ( $post ) {
		$products   = incassoos_sanitize_order_products( $products );
		$prev_value = get_post_meta( $post->ID, 'products', true );

		// Bail when the stored value is identical to avoid update_metadata() returning false.
		if ( json_encode( $products ) === json_encode( $prev_value ) ) {
			return true;
		}

		$success = update_post_meta( $post->ID, 'products', $products );

		// Update order total
		update_post_meta( $post->ID, 'total', incassoos_get_order_total_raw( $post ) );
	}

	return $success;
}

/**
 * Return whether the provided data is valid for an Order
 *
 * @since 1.0.0
 *
 * @param  array|object $args Order attributes to update
 * @return WP_Error|bool Error object on invalidation, true when validated
 */
function incassoos_validate_order( $args = array() ) {

	// Array-fy when an object was provided
	if ( ! is_array( $args ) ) {
		$args = (array) $args;
	}

	$update = isset( $args['ID'] ) && ! empty( $args['ID'] );

	// Parse defaults
	$args = wp_parse_args( $args, array(
		'post_parent'   => 0,
		'consumer_id'   => $update ? incassoos_get_order_consumer_id( $args['ID'] ) : 0,
		'consumer_type' => $update ? incassoos_get_order_consumer_type( $args['ID'] ) : '',
		'consumer'      => $update ? incassoos_get_order_consumer( $args['ID'] ) : 0,
		'products'      => $update ? incassoos_get_order_products( $args['ID'] ) : array()
	) );

	// Parent is not an Occasion
	if ( ! $parent = incassoos_get_occasion( $args['post_parent'] ) ) {
		return new WP_Error(
			'incassoos_order_invalid_parent',
			__( 'Invalid occasion.', 'incassoos' )
		);
	}

	// Parent Occasion is locked
	if ( incassoos_is_occasion_locked( $parent ) ) {
		return new WP_Error(
			'incassoos_order_locked_occasion',
			__( 'The occasion is closed for new orders.', 'incassoos' )
		);
	}

	// Process alternative consumer inputs. Prefer ids over types.
	if ( ! empty( $args['consumer_id'] ) ) {
		$args['consumer'] = $args['consumer_id'];

	// Try provided type
	} elseif ( ! empty( $args['consumer_type'] ) ) {
		$args['consumer'] = $args['consumer_type'];
	}

	// Validate consumer
	$consumer = incassoos_validate_consumer_id( $args['consumer'] );
	if ( is_wp_error( $consumer ) ) {
		return $consumer;
	}

	// Validate products
	$products = incassoos_validate_order_products( $args['products'] );
	if ( is_wp_error( $products ) ) {
		return $products;
	}

	// Validate spending limit
	if ( ! incassoos_is_consumption_within_limit_for_occasion( $products, $consumer, $parent ) ) {
		return new WP_Error(
			'incassoos_spending_limited',
			__( "The consumer's spending limit has been reached.", 'incassoos' )
		);
	}

	return true;
}

/**
 * Modify and validate an Order before it is inserted via the REST API
 *
 * @since 1.0.0
 */
function incassoos_rest_pre_insert_order( $post, $request ) {

	/**
	 * The order will be validated twice: here specifically in the REST API, and
	 * before the post is inserted in the database. This requires metadata on the
	 * post object.
	 *
	 * @see incassoos_prevent_insert_post()
	 */

	// Consumer
	if ( isset( $request['consumer'] ) ) {
		$post->consumer = $request['consumer'];
	}

	// Products
	if ( isset( $request['products'] ) ) {
		$post->products = $request['products'];
	}

	// Validate order
	$validated = incassoos_validate_order( $post );
	if ( is_wp_error( $validated ) ) {
		$validated->add_data( array( 'status' => 400 ) );
		return $validated;
	}

	return $post;
}

/** Helpers *******************************************************************/

/**
 * Return whether the consumption of products is within a consumer's spending limit
 * for the occasion.
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_is_consumption_within_limit_for_occasion'
 *
 * @param  array $products Products list
 * @param  int|WP_User $user User ID or object
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Is the consumption within limit?
 */
function incassoos_is_consumption_within_limit_for_occasion( $products, $user, $post = 0 ) {
	$spending_limit = incassoos_get_user_spending_limit( $user );
	$within_limit   = true;

	// Only continue when spending limit applies
	if ( $spending_limit ) {
		$current_total  = incassoos_get_occasion_consumer_total( $user, incassoos_get_order_occasion( $post ), null );
		$products_total = incassoos_get_total_from_products( $products );
		$within_limit   = ( $current_total + $products_total ) < $spending_limit;
	}

	return (bool) apply_filters( 'incassoos_is_consumption_within_limit_for_occasion', $within_limit, $products, $user, $post );
}

/**
 * Return whether the consumption of products is within a consumer's spending limit
 * for the time window.
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_is_consumption_within_limit_for_time_window'
 * @uses apply_filters() Calls 'incassoos_default_spending_limit_time_window'
 *
 * @param  array $products Products list
 * @param  int|WP_User $user User ID or object
 * @param  int $time_window Optional. Time window in seconds. Defaults to 4 hours.
 * @return bool Is the consumption within limit?
 */
function incassoos_is_consumption_within_limit_for_time_window( $products, $user, $time_window = 0 ) {
	$spending_limit = incassoos_get_user_spending_limit( $user );
	$within_limit   = true;

	// Default time window to 4 hours
	if ( empty( $time_window ) ) {
		$time_window = (int) apply_filters( 'incassoos_default_spending_limit_time_window', 4 * HOUR_IN_SECONDS, $user );
	}

	// Only continue when spending limit applies
	if ( $spending_limit ) {

		// Find any orders of the user that were created between now and the time window
		$current_total = 0;
		$time_start    = strtotime( "-{$time_window} seconds" );
		$posts         = incassoos_get_orders( array(
			'incassoos_consumer' => incassoos_get_user_id( $user ),
			'date_query'         => array(
				array(
					'column'  => 'post_date',
					'compare' => '>=',
					array(
						'year'   => date( 'Y', $time_start ),
						'month'  => date( 'm', $time_start ),
						'day'    => date( 'd', $time_start ),
						'hour'   => date( 'H', $time_start ),
						'minute' => date( 'i', $time_start ),
						'second' => date( 's', $time_start ),
					)
				)
			)
		) );

		foreach ( $posts as $post_id ) {
			$current_total += incassoos_get_order_total( $post_id );
		}

		$products_total = incassoos_get_total_from_products( $products );
		$within_limit   = ( $current_total + $products_total ) <= $spending_limit;
	}

	return (bool) apply_filters( 'incassoos_is_consumption_within_limit_for_time_window', $within_limit, $products, $user, $time_window );
}

/** Consumer Type *************************************************************/

/**
 * Return the Consumer Type taxonomy
 *
 * @since 1.0.0
 *
 * @return string Taxonomy name
 */
function incassoos_get_consumer_type_tax_id() {
	return incassoos()->consumer_type_tax_id;
}

/**
 * Return the labels for the Consumer Type taxonomy
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_consumer_type_tax_labels'
 * @return array Consumer Type taxonomy labels
 */
function incassoos_get_consumer_type_tax_labels() {
	return apply_filters( 'incassoos_get_consumer_type_tax_labels', array(
		'name'                       => __( 'Incassoos Consumer Types',        'incassoos' ),
		'menu_name'                  => __( 'Consumer Types',                  'incassoos' ),
		'singular_name'              => __( 'Consumer Type',                   'incassoos' ),
		'search_items'               => __( 'Search Consumer Types',           'incassoos' ),
		'popular_items'              => null, // Disable tagcloud
		'all_items'                  => __( 'All Consumer Types',              'incassoos' ),
		'no_items'                   => __( 'No Consumer Type',                'incassoos' ),
		'edit_item'                  => __( 'Edit Consumer Type',              'incassoos' ),
		'update_item'                => __( 'Update Consumer Type',            'incassoos' ),
		'add_new_item'               => __( 'Add New Consumer Type',           'incassoos' ),
		'new_item_name'              => __( 'New Consumer Type Name',          'incassoos' ),
		'view_item'                  => __( 'View Consumer Type',              'incassoos' ),

		'separate_items_with_commas' => __( 'Separate types with commas',      'incassoos' ),
		'add_or_remove_items'        => __( 'Add or remove types',             'incassoos' ),
		'choose_from_most_used'      => __( 'Choose from the most used types', 'incassoos' ),
		'not_found'                  => __( 'No types found.',                 'incassoos' ),
		'no_terms'                   => __( 'No types',                        'incassoos' ),
		'items_list_navigation'      => __( 'Consumer types list navigation',  'incassoos' ),
		'items_list'                 => __( 'Consumer types list',             'incassoos' ),
		'back_to_items'              => __( '&larr; Go to Consumer Types',     'incassoos' ),
	) );
}

/**
 * Act when the Consumer Type taxonomy has been registered
 *
 * @since 1.0.0
 */
function incassoos_registered_consumer_type_taxonomy() {
	$taxonomy = incassoos_get_consumer_type_tax_id();

	// Admin
	add_action( "{$taxonomy}_add_form_fields",  'incassoos_admin_taxonomy_add_form_fields',  10    );
	add_action( "{$taxonomy}_edit_form_fields", 'incassoos_admin_taxonomy_edit_form_fields', 10, 2 );
}

/**
 * Return the base Unknown user consumer type id
 *
 * @since 1.0.0
 *
 * @return string Consumer type id
 */
function incassoos_get_unknown_consumer_type_id_base() {
	return incassoos()->unknown_consumer_type;
}

/**
 * Return the Unknown user consumer type id
 *
 * @since 1.0.0
 *
 * @param int $user_id User ID of the unknown user.
 * @return string Consumer type id
 */
function incassoos_get_unknown_consumer_type_id( $user_id ) {
	$consumer_type = incassoos_get_unknown_consumer_type_id_base();
	$user_id       = is_numeric( $user_id ) ? $user_id : 0;

	// Add user id suffix to type
	$type_id = "{$consumer_type}:{$user_id}";

	return $type_id;
}

/**
 * Return whether the input value is a Unknown user consumer type
 *
 * @since 1.0.0
 *
 * @param string $type_id Consumer type id
 * @return int Unknown user ID
 */
function incassoos_get_user_id_from_unknown_consumer_type( $type_id ) {
	$consumer_type = incassoos_get_unknown_consumer_type_id_base();
	$parts         = is_string( $type_id ) ? explode( ':', $type_id ) : array();
	$user_id       = 0;

	// Check syntax '{consumer_type}:{user_id}'
	if ( $parts && $parts[0] === $consumer_type ) {
		$user_id = isset( $parts[1] ) && is_numeric( $parts[1] ) ? (int) $parts[1] : 0;
	}

	return $user_id;
}

/**
 * Return whether the input value is a Unknown user consumer type
 *
 * @since 1.0.0
 *
 * @param string $type_id Consumer type id
 * @return bool Consumer type is a unknown user
 */
function incassoos_is_unknown_consumer_type_id( $type_id ) {
	return (bool) incassoos_get_user_id_from_unknown_consumer_type( $type_id );
}

/**
 * Return the Guest consumer type id
 *
 * @since 1.0.0
 *
 * @return string Consumer type id
 */
function incassoos_get_guest_consumer_type_id() {
	return incassoos()->guest_consumer_type;
}

/**
 * Register a consumer type
 *
 * @since 1.0.0
 *
 * @uses apply_filters Calls 'incassoos_register_consumer_type'
 *
 * @param  string $type_id Consumer type id.
 * @param  array  $args    Optional. Consumer type parameters.
 * @return bool Registration success.
 */
function incassoos_register_consumer_type( $type_id, $args = array() ) {
	$plugin  = incassoos();
	$type_id = sanitize_title( $type_id );

	// Bail when type param is invalid or type already exists
	if ( empty( $type_id ) || incassoos_get_consumer_type( $type_id ) ) {
		return false;
	}

	// Keep original arguments
	$original_args = $args;

	// Parse defaults
	$args['id'] = $type_id;
	$args = wp_parse_args( $args, array(
		'label'               => ucfirst( $type_id ),
		'description'         => '',
		'avatar_url_callback' => '',
		'avatar_url'          => ''
	) );

	// Allow filtering
	$consumer_type = apply_filters( 'incassoos_register_consumer_type', $args, $type_id, $original_args );

	// Define consumer types collection
	if ( ! isset( $plugin->consumer_types ) ) {
		$plugin->consumer_types = array();
	}

	// Create and add type to collection
	$plugin->consumer_types[ $type_id ] = new Incassoos_Consumer_Type( $consumer_type );

	return true;
}

/**
 * Unregister a consumer type
 *
 * @since 1.0.0
 *
 * @param  string $type_id Consumer type id.
 * @return bool Unregistration success.
 */
function incassoos_unregister_consumer_type( $type_id ) {
	unset( incassoos()->consumer_types[ $type_id ] );

	return true;
}

/**
 * Return the consumer type object
 *
 * @since 1.0.0
 *
 * @uses apply_filters Calls 'incassoos_get_consumer_type'
 *
 * @param  Incassoos_Consumer_Type|string|WP_Term $type_id Consumer type, id or label or Term object.
 * @return Incassoos_Consumer_Type|bool Consumer type object or False when not found.
 */
function incassoos_get_consumer_type( $type_id ) {
	if ( empty( $type_id ) ) {
		return false;
	}

	if ( $type_id instanceof Incassoos_Consumer_Type ) {
		$_type = $type_id;
	} else {
		$_type = Incassoos_Consumer_Type::get_instance( $type_id );
	}

	if ( ! $_type ) {
		return false;
	}

	return apply_filters( 'incassoos_get_consumer_type', $_type );
}

/**
 * Return whether the consumer type exists
 *
 * @since 1.0.0
 *
 * @param  string $type_id Consumer type id or label
 * @return bool Does consumer type exist?
 */
function incassoos_consumer_type_exists( $type_id ) {
	return !! incassoos_get_consumer_type( $type_id );
}

/**
 * Return the ids of all defined consumer types
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_consumer_types'
 *
 * @param  array $args Query arguments
 * @return array Consumer type ids
 */
function incassoos_get_consumer_types( $args = array() ) {
	$args  = wp_parse_args( $args, array(
		'builtin'  => null,
		'archived' => null
	) );
	$types = array();

	// Get built-in types
	if ( false !== $args['builtin'] ) {
		foreach( incassoos()->consumer_types as $type_id => $type ) {

			// Skip internal items
			if ( $type->is_internal() ) {
				continue;
			}

			// Consider archived items
			if ( $args['archived'] ) {
				continue;
			}

			// Search in item
			if ( isset( $args['search'] ) && ! $type->find( $args['search'] ) ) {
				continue;
			}

			// List item
			$types[] = $type_id;
		}
	}

	// Get custom types
	if ( true !== $args['builtin'] ) {
		$query_args = wp_parse_args( array(
			'taxonomy'   => incassoos_get_consumer_type_tax_id(),
			'fields'     => 'slugs',
			'hide_empty' => false,

			// Prevent limited queries
			'number'     => '',
			'offset'     => ''
		), $args );

		// Consider archived items
		if ( null !== $args['archived'] ) {
			$meta_query = isset( $query_args['meta_query'] ) ? $query_args['meta_query'] : array();
			$meta_query[] = array(
				array(
					'key'     => '_incassoos_archived',
					'value'   => 1,
					'compare' => $args['archived'] ? '=' : 'NOT EXISTS'
				)
			);
			$query_args['meta_query'] = $meta_query;
		}

		// Query terms
		$terms = get_terms( $query_args );

		// Combine types
		$types = array_merge( $types, $terms );
	}

	// Default sort alphabetically
	sort( $types );

	return (array) apply_filters( 'incassoos_get_consumer_types', $types, $args );
}

/**
 * Query the consumer types
 *
 * @since 1.0.0
 *
 * @uses apply_filters Calls 'incassoos_query_consumer_types_query'
 * @uses apply_filters Calls 'incassoos_query_consumer_types'
 *
 * @param  array $query_args Optional. Query arguments.
 * @return array Queried consumer types
 */
function incassoos_query_consumer_types( $query_args = array() ) {
	$items = array();

	// Sorting
	if ( ! isset( $query_args['orderby'] ) ) {
		$query_args['orderby'] = 'label';
	}

	if ( ! isset( $query_args['order'] ) ) {
		$query_args['order'] = 'ASC';
	}

	// Default to 10 items per page
	if ( ! isset( $query_args['per_page'] ) ) {
		$query_args['per_page'] = 10;
	}

	$query_args = apply_filters( 'incassoos_query_consumer_types_query', $query_args );

	foreach ( incassoos_get_consumer_types( $query_args ) as $type ) {
		$items[] = incassoos_get_consumer_type( $type );
	}

	$items_count = count($items);

	// Handle sorting
	$query_args['order'] = ( 'DESC' === strtoupper( $query_args['order'] ) ) ? 'DESC' : 'ASC';
	if ( 'name' === $query_args['orderby'] ) {
		$query_args['orderby'] = 'label';
	}
	$items = wp_list_sort( $items, $query_args['orderby'], $query_args['order'] );

	// Handle pagination
	if ( (int) $query_args['per_page'] >= 0 ) {
		$query_args['page'] = max( isset( $query_args['page'] ) ? (int) $query_args['page'] : 1, 1 );
		$offset = ($query_args['page'] - 1) * $query_args['per_page'];
		$items = array_slice( $items, $offset, $query_args['per_page'] );
	}

	// Define query result
	$items_query = (object) array(
		'query_result' => $items,
		'total_count'  => $items_count,
		'query_vars'   => $query_args
	);

	return apply_filters( 'incassoos_query_consumer_types', $items_query, $query_args );
}

/**
 * Output the consumer type title
 *
 * @since 1.0.0
 *
 * @param  string $type_id Consumer type id
 */
function incassoos_the_consumer_type_title( $type_id ) {
	echo incassoos_get_consumer_type_title( $type_id );
}

/**
 * Return the consumer type title
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_consumer_type_title'
 *
 * @param  string $type_id Consumer type id
 * @return string Consumer type title
 */
function incassoos_get_consumer_type_title( $type_id ) {
	$consumer_type = incassoos_get_consumer_type( $type_id );
	$title         = ucfirst( $type_id );

	if ( $consumer_type ) {
		$title = $consumer_type->label;
	}

	return apply_filters( 'incassoos_get_consumer_type_title', $title, $consumer_type );
}

/**
 * Output the consumer type description
 *
 * @since 1.0.0
 *
 * @param  string $type_id Consumer type id
 */
function incassoos_the_consumer_type_description( $type_id ) {
	echo incassoos_get_consumer_type_description( $type_id );
}

/**
 * Return the consumer type description
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_consumer_type_description'
 *
 * @param  string $type_id Consumer type id
 * @return string Consumer type description
 */
function incassoos_get_consumer_type_description( $type_id ) {
	$consumer_type = incassoos_get_consumer_type( $type_id );
	$description   = '';

	if ( $consumer_type ) {
		$description = $consumer_type->description;
	}

	return apply_filters( 'incassoos_get_consumer_type_description', $description, $consumer_type );
}

/**
 * Display or return consumer types dropdown element
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_dropdown_consumer_types_query_args'
 * @uses apply_filters() Calls 'incassoos_dropdown_consumer_types'
 *
 * @param  Incassoos_Consumer_Type|string $consumer_type Consumer type object or id
 * @param  array                          $args Dropdown arguments
 * @return string HTML dropdown list of consumer types
 */
function incassoos_dropdown_consumer_types( $args = array() ) {
	$parsed_args = wp_parse_args( $args, array(
		'echo'              => 1,
		'id'                => 'consumer-type',
		'class'             => '',
		'tab_index'         => 0,
		'selected'          => '',
		'option_none_value' => __( '&mdash; Consumer Type &mdash;', 'incassoos' ),
	) );

	$id    = esc_attr( $parsed_args['id'] );
	$name  = isset( $parsed_args['name'] ) ? esc_attr( $parsed_args['name'] ) : $id;
	$class = ! empty( $parsed_args['class'] ) ? ' class="'. esc_attr( $parsed_args['class'] ) . '"' : '';

	$tab_index = $parsed_args['tab_index'];
	$tab_index_attribute = '';
	if ( (int) $tab_index > 0 ) {
		$tab_index_attribute = " tabindex=\"$tab_index\"";
	}

	$output  = "<select id='$id' name='$name' $class $tab_index_attribute>\n";
	$output .= '<option value="-1">' . esc_html( $parsed_args['option_none_value'] ) . '</option>';

	// Default query all unarchived items
	$query_args = apply_filters( 'incassoos_dropdown_consumer_types_query_args', wp_parse_args( $parsed_args, array(
		'per_page' => -1,
		'archived' => false
	) ), $args );
	$query = incassoos_query_consumer_types( $query_args );

	// Consumer types
	foreach ( $query->query_result as $type ) {
		$output .= '<option value="' . esc_attr( $type->id ) . '"' . selected( $type->id, $parsed_args['selected'], false ) . '>' . esc_html( incassoos_get_consumer_type_title( $type->id ) ) . '</option>';
	}

	$output .= '</select>';
	$output = apply_filters( 'incassoos_dropdown_consumer_types', $output, $parsed_args );

	if ( $parsed_args['echo'] ) {
		echo $output;
	}

	return $output;
}

/**
 * Return the consumer type avatar url
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_consumer_type_avatar_url'
 *
 * @param  string $type_id Consumer type id
 * @param  array $args {
 *     Optional. Arguments to return instead of the default arguments.
 *
 *     @type int $size Height and width of the avatar in pixels. Default 96.
 * }
 * @return string Consumer type avatar url
 */
function incassoos_get_consumer_type_avatar_url( $type_id, $args = array() ) {
	$consumer_type = incassoos_get_consumer_type( $type_id );
	$avatar_url    = '';
	$args          = wp_parse_args( $args, array(
		'size' => 96
	) );

	// Parse defaults
	if ( is_numeric( $args['size'] ) ) {
		$args['size'] = absint( $args['size'] );
		if ( ! $args['size'] ) {
			$args['size'] = 96;
		}
	} else {
		$args['size'] = 96;
	}

	if ( $consumer_type ) {
		if ( is_callable( $consumer_type->avatar_url_callback ) ) {
			$avatar_url = call_user_func_array( $consumer_type->avatar_url_callback, array( $consumer_type, $args ) );
		} else {
			$avatar_url = $consumer_type->avatar_url;
		}
	}

	return apply_filters( 'incassoos_get_consumer_type_avatar_url', $avatar_url, $consumer_type, $args );
}

/**
 * Return the consumer type spending limit
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_consumer_type_spending_limit'
 *
 * @param  string $type_id Consumer type id
 * @return float Consumer type spending limit.
 */
function incassoos_get_consumer_type_spending_limit( $type_id ) {
	$consumer_type = incassoos_get_consumer_type( $type_id );
	$limit         = 0;

	if ( $consumer_type ) {

		// Type is a term
		if ( $consumer_type->is_term() ) {
			$limit = (float) get_term_meta( $consumer_type->term_id, '_incassoos_spending_limit', true );
		}
	}

	return (float) apply_filters( 'incassoos_get_consumer_type_spending_limit', $limit, $consumer_type );
}

/**
 * Return whether the consumer type is archived
 *
 * @since 1.0.0
 *
 * @param  string $type_id Consumer type id
 * @return bool Is the consumer type archived?
 */
function incassoos_is_consumer_type_archived( $type_id ) {
	$consumer_type = incassoos_get_consumer_type( $type_id );
	$archived      = false;

	if ( $consumer_type ) {

		// Type is a term
		if ( $consumer_type->is_term() ) {
			$archived = incassoos_is_term_archived( $consumer_type->term_id );
		}
	}

	return (bool) apply_filters( 'incassoos_is_consumer_type_archived', $archived, $consumer_type );
}

/**
 * Return whether the consumer type is not archived
 *
 * @since 1.0.0
 *
 * @param  string $type_id Consumer type id
 * @return bool Is the consumer type not archived?
 */
function incassoos_is_consumer_type_not_archived( $type_id ) {
	return ! incassoos_is_consumer_type_archived( $type_id );
}

/** Update ********************************************************************/

/**
 * Update the consumer type spending limit
 *
 * @since 1.0.0
 *
 * @param  string $type_id Consumer type id
 * @param  float $limit Optional. Spending limit. Removed when empty.
 * @return bool Update success
 */
function incassoos_update_consumer_type_spending_limit( $type_id, $limit = 0 ) {
	$consumer_type = incassoos_get_consumer_type( $type_id );
	$success       = false;

	if ( $consumer_type ) {

		// Type is term
		if ( $consumer_type->is_term() ) {
			if ( ! $limit ) {
				$success = delete_term_meta( $consumer_type->term_id, '_incassoos_spending_limit' );
			} else {
				$success = update_term_meta( $consumer_type->term_id, '_incassoos_spending_limit', (float) $limit );
			}
		}
	}

	return $success;
}

/**
 * Archive the consumer type
 *
 * @since 1.0.0
 *
 * @param  string $type_id Consumer type id
 * @return bool Update success
 */
function incassoos_archive_consumer_type( $type_id ) {
	$consumer_type = incassoos_get_consumer_type( $type_id );
	$success       = false;

	if ( $consumer_type ) {

		// Type is term
		if ( $consumer_type->is_term() ) {
			$success = update_term_meta( $consumer_type->term_id, '_incassoos_archived', 1 );
		}
	}

	return $success;
}

/**
 * Unarchive the consumer type
 *
 * Effectively removes the '_incassoos_archived' term attribute.
 *
 * @since 1.0.0
 *
 * @param  string $type_id Consumer type id
 * @return bool Update success
 */
function incassoos_unarchive_consumer_type( $type_id ) {
	$consumer_type = incassoos_get_consumer_type( $type_id );
	$success       = false;

	if ( $consumer_type ) {

		// Type is term
		if ( $consumer_type->is_term() ) {
			$success = delete_term_meta( $consumer_type->term_id, '_incassoos_archived' );
		}
	}

	return $success;
}
