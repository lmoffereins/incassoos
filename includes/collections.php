<?php

/**
 * Incassoos Collection Functions
 *
 * @package Incassoos
 * @subpackage Main
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Post Type *****************************************************************/

/**
 * Return the Collection post type
 *
 * @since 1.0.0
 *
 * @return string Post type name
 */
function incassoos_get_collection_post_type() {
	return incassoos()->collection_post_type;
}

/**
 * Return the labels for the Collection post type
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_post_type_labels'
 * @return array Collection post type labels
 */
function incassoos_get_collection_post_type_labels() {
	return apply_filters( 'incassoos_get_collection_post_type_labels', array(
		'name'                  => __( 'Incassoos Collections',         'incassoos' ),
		'menu_name'             => __( 'Collections',                   'incassoos' ),
		'singular_name'         => __( 'Collection',                    'incassoos' ),
		'all_items'             => __( 'All Collections',               'incassoos' ),
		'add_new'               => __( 'New Collection',                'incassoos' ),
		'add_new_item'          => __( 'Create New Collection',         'incassoos' ),
		'edit'                  => __( 'Edit',                          'incassoos' ),
		'edit_item'             => __( 'Edit Collection',               'incassoos' ),
		'new_item'              => __( 'New Collection',                'incassoos' ),
		'view'                  => __( 'View Collection',               'incassoos' ),
		'view_item'             => __( 'View Collection',               'incassoos' ),
		'view_items'            => __( 'View Collections',              'incassoos' ), // Since WP 4.7
		'search_items'          => __( 'Search Collections',            'incassoos' ),
		'not_found'             => __( 'No collections found',          'incassoos' ),
		'not_found_in_trash'    => __( 'No collections found in Trash', 'incassoos' ),
		'insert_into_item'      => __( 'Insert into collection',        'incassoos' ),
		'uploaded_to_this_item' => __( 'Uploaded to this collection',   'incassoos' ),
		'filter_items_list'     => __( 'Filter collections list',       'incassoos' ),
		'items_list_navigation' => __( 'Collections list navigation',   'incassoos' ),
		'items_list'            => __( 'Collections list',              'incassoos' ),
	) );
}

/**
 * Return an array of features the Collection post type supports
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_post_type_supports'
 * @return array|false Collection post type support or False for no support.
 */
function incassoos_get_collection_post_type_supports() {
	return apply_filters( 'incassoos_get_collection_post_type_supports', array(
		'title',
		'editor'
	) );
}

/**
 * Return the Staged post status id
 *
 * @since 1.0.0
 *
 * @return string Staged status id
 */
function incassoos_get_staged_status_id() {
	return incassoos()->staged_status_id;
}

/**
 * Return the Collected post status id
 *
 * @since 1.0.0
 *
 * @return string Collected status id
 */
function incassoos_get_collected_status_id() {
	return incassoos()->collected_status_id;
}

/** Template ******************************************************************/

/**
 * Return the Collection
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  null|bool $collected Optional. The required Collection's collected status.
 * @return WP_Post|bool Collection post object or False when not found.
 */
function incassoos_get_collection( $post = 0, $collected = null ) {

	// Get the post
	$post = get_post( $post );

	// Return false when this is not a Collection
	if ( ! $post || incassoos_get_collection_post_type() !== $post->post_type ) {
		$post = false;

	// Check collected status
	} elseif ( null !== $collected ) {

		// Return false when collection status does not match the request
		if ( (bool) $collected !== incassoos_is_collection_collected( $post ) ) {
			$post = false;
		}
	}

	return $post;
}

/**
 * Output the Collection's title
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_collection_title( $post = 0 ) {
	echo incassoos_get_collection_title( $post );
}

/**
 * Return the Collection's title
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_title'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Collection's title
 */
function incassoos_get_collection_title( $post = 0 ) {
	$post  = incassoos_get_collection( $post );
	$title = $post ? get_the_title( $post ) : '';

	return apply_filters( 'incassoos_get_collection_title', $title, $post );
}

/**
 * Output the Collection's content
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_collection_content( $post = 0 ) {
	echo incassoos_get_collection_content( $post );
}

/**
 * Return the Collection's content
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_content'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Collection's content
 */
function incassoos_get_collection_content( $post = 0 ) {
	$post    = incassoos_get_collection( $post );
	$content = $post ? apply_filters( 'the_content', $post->post_content ) : '';

	return apply_filters( 'incassoos_get_collection_content', $content, $post );
}

/**
 * Output the Collection's url
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_collection_url( $post = 0 ) {
	echo incassoos_get_collection_url( $post );
}

/**
 * Return the Collection's url
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_url'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Collection's url
 */
function incassoos_get_collection_url( $post = 0 ) {
	$post = incassoos_get_collection( $post );
	$url  = $post ? add_query_arg( array( 'post' => $post->ID, 'action' => 'edit' ), admin_url( 'post.php' ) ) : '';

	return apply_filters( 'incassoos_get_collection_url', $url, $post );
}

/**
 * Output the Collection's link
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_collection_link( $post = 0 ) {
	echo incassoos_get_collection_link( $post );
}

/**
 * Return the Collection's link
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_link'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Collection's link
 */
function incassoos_get_collection_link( $post = 0 ) {
	$post = incassoos_get_collection( $post );
	$link = $post ? sprintf( '<a href="%s">%s</a>', esc_url( incassoos_get_collection_url( $post ) ), incassoos_get_collection_title( $post ) ) : '';

	return apply_filters( 'incassoos_get_collection_link', $link, $post );
}

/**
 * Output the Collection's author name
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_collection_author( $post = 0 ) {
	echo incassoos_get_collection_author( $post );
}

/**
 * Return the Collection's author name
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_author'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Collection author name or False when not found.
 */
function incassoos_get_collection_author( $post = 0 ) {
	$post   = incassoos_get_collection( $post );
	$author = get_userdata( $post ? $post->post_author : 0 );

	if ( $author && $author->exists() ) {
		$author = $author->display_name;
	} else {
		$author = '';
	}

	return apply_filters( 'incassoos_get_collection_author', $author, $post );
}

/**
 * Output the Collection's created date
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  string      $format Optional. Timestamp's date format to return. Defaults to the `date_format` option.
 */
function incassoos_the_collection_created( $post = 0, $format = false ) {
	echo incassoos_get_collection_created( $post );
}

/**
 * Return the Collection's created date
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_created'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  string      $format Optional. Timestamp's date format to return. Defaults to the `date_format` option.
 * @return string Collection's created date
 */
function incassoos_get_collection_created( $post = 0, $format = false ) {
	$post = incassoos_get_collection( $post );
	$date = $post ? $post->post_date : '';

	// Default to the registered date format
	if ( empty( $format ) ) {
		$format = get_option( 'date_format' );
	}

	if ( $date ) {
		$date = mysql2date( $format, $date );
	}

	return apply_filters( 'incassoos_get_collection_created', $date, $post, $format );
}

/**
 * Output the Collection's staged date
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  string      $format Optional. Timestamp's date format to return. Defaults to the `date_format` option.
 */
function incassoos_the_collection_staged( $post = 0, $format = false ) {
	echo incassoos_get_collection_staged( $post );
}

/**
 * Return the Collection's staged date
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_staged'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  string      $format Optional. Timestamp's date format to return. Defaults to the `date_format` option.
 * @return string Collection's staged date.
 */
function incassoos_get_collection_staged( $post = 0, $format = false ) {
	$post = incassoos_get_collection( $post );
	$date = get_post_meta( $post ? $post->ID : 0, 'staged', true );

	// Default to the registered date format
	if ( empty( $format ) ) {
		$format = get_option( 'date_format' );
	}

	if ( $date ) {
		$date = mysql2date( $format, $date );
	} else {
		$date = '';
	}

	return apply_filters( 'incassoos_get_collection_staged', $date, $post, $format );
}

/**
 * Output the Collection's collected date
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  string      $format Optional. Timestamp's date format to return. Defaults to the `date_format` option.
 */
function incassoos_the_collection_date( $post = 0, $format = false ) {
	echo incassoos_get_collection_date( $post );
}

/**
 * Return the Collection's collected date
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_date'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  string      $format Optional. Timestamp's date format to return. Defaults to the `date_format` option.
 * @return string Collection's collected date.
 */
function incassoos_get_collection_date( $post = 0, $format = false ) {
	$post = incassoos_get_collection( $post );
	$date = get_post_meta( $post ? $post->ID : 0, 'collected', true );

	// Default to the registered date format
	if ( empty( $format ) ) {
		$format = get_option( 'date_format' );
	}

	if ( $date ) {
		$date = mysql2date( $format, $date );
	} else {
		$date = '';
	}

	return apply_filters( 'incassoos_get_collection_date', $date, $post, $format );
}

/**
 * Output the Collection's total value
 * 
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  bool|array|null $format Optional. Whether to apply currency format. Pass array as format args. Pass
 *                                  null to skip format parsing. Defaults to false.
 */
function incassoos_the_collection_total( $post = 0, $format = false ) {
	echo incassoos_get_collection_total( $post, $format );
}

/**
 * Return the Collection's total value
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_total'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  bool|array|null $format Optional. Whether to apply currency format. Pass array as format args. Pass
 *                                  null to skip format parsing. Defaults to false.
 * @return string|float Collection total value.
 */
function incassoos_get_collection_total( $post = 0, $format = false ) {
	$post  = incassoos_get_collection( $post );
	$total = get_post_meta( $post ? $post->ID : 0, 'total', true );

	// Get total from raw calculation
	if ( false === $total && $post ) {
		$total = incassoos_get_collection_total_raw( $post );
		update_post_meta( $post->ID, 'total', $total );
	}

	$total = (float) apply_filters( 'incassoos_get_collection_total', (float) $total, $post );

	// Apply currency format
	if ( null !== $format ) {
		$total = incassoos_parse_currency( $total, $format );
	}

	return $total;
}

/**
 * Return the Collection's raw total value
 *
 * @since 1.0.0
 *
 * @global WPDB $wpdb
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_total_raw'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return float Collection raw total value.
 */
function incassoos_get_collection_total_raw( $post = 0 ) {
	global $wpdb;

	$post  = incassoos_get_collection( $post );
	$total = 0;

	// Get Collection assets
	$activities   = incassoos_get_collection_activities( $post );
	$orders = incassoos_get_collection_orders( $post );

	// Query all total values
	if ( $activities || $orders ) {

		// Define post meta query
		$post_ids = implode( ',', array_merge( $activities, $orders ) );
		$sql      = $wpdb->prepare( "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id IN ($post_ids) AND meta_key = %s", 'total' );

		// Query all totals
		if ( $values = $wpdb->get_col( $sql ) ) {
			$total = array_sum( array_map( 'floatval', $values ) );
		}
	}

	return (float) apply_filters( 'incassoos_get_collection_total_raw', $total, $post );
}

/**
 * Return whether the Collection is published
 *
 * A Collection is considered published when it is either created (status=publish)
 * or fully collected. TODO: What for?
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_is_collection_published'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Collection is published
 */
function incassoos_is_collection_published( $post = 0 ) {
	$post      = incassoos_get_collection( $post );
	$published = $post && in_array( $post->post_status, array( 'publish', incassoos_get_collected_status_id() ) );

	return apply_filters( 'incassoos_is_collection_published', $published, $post );	
}

/**
 * Return whether the Collection is staged
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_is_collection_staged'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Collection is staged
 */
function incassoos_is_collection_staged( $post = 0 ) {
	$post   = incassoos_get_collection( $post );
	$staged = $post && ( incassoos_get_staged_status_id() === $post->post_status );

	return (bool) apply_filters( 'incassoos_is_collection_staged', $staged, $post );	
}

/**
 * Return whether the Collection is collected
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_is_collection_collected'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Collection is collected
 */
function incassoos_is_collection_collected( $post = 0 ) {
	$post      = incassoos_get_collection( $post );
	$collected = $post && ( incassoos_get_collected_status_id() === $post->post_status );

	return (bool) apply_filters( 'incassoos_is_collection_collected', $collected, $post );	
}

/**
 * Return whether the Collection is locked
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_is_collection_locked'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Collection is locked
 */
function incassoos_is_collection_locked( $post = 0 ) {
	$post   = incassoos_get_collection( $post );
	$locked = $post && ( incassoos_is_collection_staged( $post ) || incassoos_is_collection_collected( $post ) );

	return (bool) apply_filters( 'incassoos_is_collection_locked', $locked, $post );	
}

/**
 * Return whether the Collection is collectable
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_is_collection_collectable'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Collection is collectable
 */
function incassoos_is_collection_collectable( $post = 0 ) {
	$post        = incassoos_get_collection( $post );
	$collectable = $post && ( incassoos_collection_has_assets( $post ) && ! incassoos_is_collection_collected( $post ) );

	return (bool) apply_filters( 'incassoos_is_collection_collectable', $collectable, $post );	
}

/** Assets ********************************************************************/

/**
 * Return whether the post can be collected
 * 
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Is the post collectable?
 */
function incassoos_is_post_collectable( $post = 0 ) {
	$post   = get_post( $post );
	$retval = false;

	// Bail when the post is already collected
	if ( incassoos_is_post_collected( $post ) )
		return false;

	// Post is a Collection, Activity, Occasion, or Order
	if ( $post ) {
		switch ( $post->post_type ) {

			// Collection
			case incassoos_get_collection_post_type() :
				$retval = incassoos_is_collection_collectable( $post );
				break;

			// Activity
			case incassoos_get_activity_post_type() :
				$retval = incassoos_is_activity_collectable( $post );
				break;

			// Occasion
			case incassoos_get_occasion_post_type() :
				$retval = incassoos_is_occasion_collectable( $post );
				break;

			// Order
			case incassoos_get_order_post_type() :
				$retval = incassoos_is_order_collectable( $post );
				break;
		}
	}

	return $retval;
}

/**
 * Return whether the post is collected
 * 
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Is the post collected?
 */
function incassoos_is_post_collected( $post = 0 ) {
	$post   = get_post( $post );
	$retval = false;

	// Post is a Collection, Activity, Occasion, or Order
	if ( $post ) {
		switch ( $post->post_type ) {

			// Collection
			case incassoos_get_collection_post_type() :
				$retval = incassoos_is_collection_collected( $post );
				break;

			// Activity
			case incassoos_get_activity_post_type() :
				$retval = incassoos_is_activity_collected( $post );
				break;

			// Occasion
			case incassoos_get_occasion_post_type() :
				$retval = incassoos_is_occasion_collected( $post );
				break;

			// Order
			case incassoos_get_order_post_type() :
				$retval = incassoos_is_order_collected( $post );
				break;
		}
	}

	return $retval;
}

/**
 * Return the Collection's activities
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_activities'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  array       $args Optional. Additional query arguments for {@see WP_Query}.
 * @return array Collection activities
 */
function incassoos_get_collection_activities( $post = 0, $args = array() ) {
	$post  = incassoos_get_collection( $post );
	$posts = array();

	// Query activities
	if ( $post ) {

		// Query by post parent
		$args['post_parent'] = $post->ID;

		// Query posts
		$posts = incassoos_get_activities( $args );
	}

	return (array) apply_filters( 'incassoos_get_collection_activities', $posts, $post, $args );
}

/**
 * Output the Collection's activity count
 * 
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  array       $args Optional. Additional query arguments for {@see WP_Query}.
 */
function incassoos_the_collection_activity_count( $post = 0, $args = array() ) {
	echo incassoos_get_collection_activity_count( $post, $args );
}

/**
 * Return the Collection's activity count
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_activity_count'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  array       $args Optional. Additional query arguments for {@see WP_Query}.
 * @return int Collection activity count
 */
function incassoos_get_collection_activity_count( $post = 0, $args = array() ) {
	$post  = incassoos_get_collection( $post );
	$posts = incassoos_get_collection_activities( $post, $args );
	$count = count( $posts );

	return (int) apply_filters( 'incassoos_get_collection_activity_count', $count, $post, $args );
}

/**
 * Return the Collection's order occasions
 * 
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  array       $args Optional. Additional query arguments for {@see WP_Term_Query}.
 * @return array Collection order occasions
 */
function incassoos_get_collection_occasions( $post = 0, $args = array() ) {
	$post  = incassoos_get_collection( $post );
	$posts = array();

	// Query order occasions
	if ( $post ) {

		// Query by post parent
		$args['post_parent'] = $post->ID;

		// Query posts
		$posts = incassoos_get_occasions( $args );
	}

	return (array) apply_filters( 'incassoos_get_collection_occasions', $posts, $post, $args );
}

/**
 * Output the Collection's order occasion count
 * 
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  array       $args Optional. Additional query arguments for {@see WP_Query}.
 */
function incassoos_the_collection_occasion_count( $post = 0, $args = array() ) {
	echo incassoos_get_collection_occasion_count( $post, $args );
}

/**
 * Return the Collection's order occasion count
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_occasion_count'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  array       $args Optional. Additional query arguments for {@see WP_Query}.
 * @return int Collection order occasion count
 */
function incassoos_get_collection_occasion_count( $post = 0, $args = array() ) {
	$post  = incassoos_get_collection( $post );
	$posts = incassoos_get_collection_occasions( $post, $args );
	$count = count( $posts );

	return (int) apply_filters( 'incassoos_get_collection_occasion_count', $count, $post, $args );
}

/**
 * Return the Collection's orders
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_orders'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  array       $args Optional. Additional query arguments for {@see WP_Query}.
 * @return array Collection orders
 */
function incassoos_get_collection_orders( $post = 0, $args = array() ) {
	$post      = incassoos_get_collection( $post );
	$occasions = incassoos_get_collection_occasions( $post );
	$posts     = array();

	// Query orders
	if ( $post && $occasions ) {

		// Query by post parent
		$args['post_parent__in'] = $occasions;

		// Query posts
		$posts = incassoos_get_orders( $args );
	}

	return (array) apply_filters( 'incassoos_get_collection_orders', $posts, $post, $args );
}

/**
 * Output the Collection's order count
 * 
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  array       $args Optional. Additional query arguments for {@see WP_Query}.
 */
function incassoos_the_collection_order_count( $post = 0, $args = array() ) {
	echo incassoos_get_collection_order_count( $post, $args );
}

/**
 * Return the Collection's order count
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_order_count'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  array       $args Optional. Additional query arguments for {@see WP_Query}.
 * @return int Collection order count
 */
function incassoos_get_collection_order_count( $post = 0, $args = array() ) {
	$post  = incassoos_get_collection( $post );
	$posts = incassoos_get_collection_orders( $post, $args );
	$count = count( $posts );

	return (int) apply_filters( 'incassoos_get_collection_order_count', $count, $post, $args );
}

/**
 * Return whether the Collection has any assets
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_collection_has_assets'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Has the Collection any assets?
 */
function incassoos_collection_has_assets( $post = 0 ) {
	$post   = incassoos_get_collection( $post );
	$assets = (bool) incassoos_get_collection_raw_assets( $post );

	return (bool) apply_filters( 'incassoos_collection_has_assets', $assets, $post );
}

/**
 * Return the Collection's consumers
 * 
 * @since 1.0.0
 *
 * @global WPDB $wpdb
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_consumers'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return array Collection user ids
 */
function incassoos_get_collection_consumers( $post = 0 ) {
	global $wpdb;

	$post      = incassoos_get_collection( $post );
	$consumers = array();

	// Get Collection assets
	if ( $post ) {

		// Define post meta query
		$sql = "SELECT meta_value FROM {$wpdb->postmeta} WHERE 0=1";

		// Query for activities
		if ( $activities = incassoos_get_collection_activities( $post ) ) {
			$post_ids = implode( ',', $activities );
			$sql     .= $wpdb->prepare( " OR ( post_id IN ($post_ids) AND meta_key = %s )", 'participant' );
		}

		// Query for orders
		if ( $orders = incassoos_get_collection_orders( $post ) ) {
			$post_ids = implode( ',', $orders );
			$sql     .= $wpdb->prepare( " OR ( post_id IN ($post_ids) AND meta_key = %s )", 'consumer' );
		}

		// Query all consumers
		if ( $values = $wpdb->get_col( $sql ) ) {
			$consumers = array_map( 'intval', array_unique( array_filter( $values ) ) );
		}
	}

	return (array) apply_filters( 'incassoos_get_collection_consumers', $consumers, $post );
}

/**
 * Return the Collection's consumer users
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_consumer_users'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  array $args Optional. Additional query arguments for {@see WP_User_Query}.
 * @return array Collection consumer user objects
 */
function incassoos_get_collection_consumer_users( $post = 0, $args = array() ) {
	$post      = incassoos_get_collection( $post );
	$consumers = incassoos_get_collection_consumers( $post );
	$users     = array();

	if ( $consumers ) {

		// Query selected users
		$user_ids = ! empty( $args['include'] ) ? array_intersect( (array) $args['include'], $consumers ) : $consumers;
		$args['include'] = array_map( 'intval', array_unique( array_filter( $user_ids ) ) );

		// Query users
		$users = incassoos_get_users( $args );
	}

	return apply_filters( 'incassoos_get_collection_consumer_users', $users, $post, $consumers );
}

/**
 * Output the Collection's consumer count
 * 
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_collection_consumer_count( $post = 0 ) {
	echo incassoos_get_collection_consumer_count( $post );
}

/**
 * Return the Collection's consumer count
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_consumer_count'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return int Collection consumer count.
 */
function incassoos_get_collection_consumer_count( $post = 0 ) {
	$post      = incassoos_get_collection( $post );
	$consumers = incassoos_get_collection_consumers( $post );
	$count     = count( array_unique( $consumers ) );

	return (int) apply_filters( 'incassoos_get_collection_consumer_count', $count, $post );
}

/**
 * Return the Collection's consumer types
 * 
 * @since 1.0.0
 *
 * @global WPDB $wpdb
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_consumer_types'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return array Collection consumer types
 */
function incassoos_get_collection_consumer_types( $post = 0 ) {
	global $wpdb;

	$post   = incassoos_get_collection( $post );
	$orders = incassoos_get_collection_orders( $post );
	$types  = array();

	if ( $post && $orders ) {

		// Define post meta query
		$post_ids = implode( ',', $orders );
		$sql      = $wpdb->prepare( "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id IN ($post_ids) AND meta_key = %s", 'consumer_type' );

		// Query all types
		if ( $values = $wpdb->get_col( $sql ) ) {
			$types = array_unique( array_filter( $values ) );
		}
	}

	return (array) apply_filters( 'incassoos_get_collection_consumer_types', $types, $post );
}

/**
 * Output the Collection's consumer total value
 * 
 * @since 1.0.0
 *
 * @param  int|WP_user|string $consumer Consumer user object or ID or consumer type id.
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  bool|array|null $format Optional. Whether to apply currency format. Pass array as format args. Pass
 *                                  null to skip format parsing. Defaults to false.
 */
function incassoos_the_collection_consumer_total( $consumer, $post = 0, $format = false ) {
	echo incassoos_get_collection_consumer_total( $consumer, $post, $format );
}

/**
 * Return the Collection's consumer total value
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_consumer_total'
 *
 * @param  int|WP_user|string $consumer Consumer user object or ID or consumer type id.
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  bool|array|null $format Optional. Whether to apply currency format. Pass array as format args. Pass
 *                                  null to skip format parsing. Defaults to false.
 * @return string|float Collection consumer total value.
 */
function incassoos_get_collection_consumer_total( $consumer, $post = 0, $format = false ) {
	$post  = incassoos_get_collection( $post );
	$total = 0;

	if ( $post ) {

		// Get collected amount
		if ( incassoos_is_collection_collected( $post ) ) {
			$totals = get_post_meta( $post->ID, 'totals', true );
			$total  = isset( $totals[ $consumer ] ) ? (float) $totals[ $consumer ] : 0;

		// Get calculated amount
		} else {
			$total  = incassoos_get_collection_consumer_total_raw( $consumer, $post );
		}
	}

	$total = (float) apply_filters( 'incassoos_get_collection_consumer_total', (float) $total, $post, $consumer );

	// Apply currency format
	if ( null !== $format ) {
		$total = incassoos_parse_currency( $total, $format );
	}

	return $total;
}

/**
 * Return the Collection's consumer raw total value
 * 
 * @since 1.0.0
 *
 * @global WPDB $wpdb
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_consumer_total_raw'
 *
 * @param  int|WP_user|string $consumer Consumer user object or ID or consumer type id.
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Collection consumer raw total value.
 */
function incassoos_get_collection_consumer_total_raw( $consumer, $post = 0 ) {
	global $wpdb;

	$post   = incassoos_get_collection( $post );
	$assets = incassoos_get_collection_consumer_raw_assets( $consumer, $post );
	$total  = 0;

	if ( $post && $assets ) {

		// Define post meta query
		$post_ids = implode( ',', $assets );
		$sql      = $wpdb->prepare( "SELECT post_id, meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id IN ($post_ids) AND ( meta_key = %s OR meta_key = %s OR meta_key = %s )", 'price', 'prices', 'total' );

		// Query all prices
		if ( $values = $wpdb->get_results( $sql ) ) {
			foreach ( $values as $value ) {

				// Price, Prices, Total
				if ( 'price' === $value->meta_key ) {

					// Find whether a consumer price was defined
					$post_rows = wp_list_filter( $values, array( 'post_id' => $value->post_id ) );
					$prices    = wp_list_filter( $post_rows, array( 'meta_key' => 'prices' ) );

					if ( $prices ) {
						$prices = reset( $prices );
						$prices = maybe_unserialize( $prices->meta_value );
					}

					// Rely on post price
					if ( ! isset( $prices[ $consumer ] ) ) {
						$total += (float) $value->meta_value;
					}

				} elseif ( 'prices' === $value->meta_key ) {
					$prices = maybe_unserialize( $value->meta_value );

					// When a consumer price was defined
					if ( isset( $prices[ $consumer ] ) ) {
						$total += (float) $prices[ $consumer ];
					}

				// Totals for Orders only
				} elseif ( 'total' === $value->meta_key && incassoos_get_order( $value->post_id ) ) {
					$total += (float) $value->meta_value;
				}
			}
		}
	}

	return (float) apply_filters( 'incassoos_get_collection_consumer_total_raw', (float) $total, $post, $consumer );
}

/**
 * Return the Collection's assets
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_assets'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  array       $args Optional. Additional query arguments for {@see WP_Query}.
 * @return array Collection assets
 */
function incassoos_get_collection_assets( $post = 0, $args = array() ) {
	$post  = incassoos_get_collection( $post );
	$posts = array();

	if ( $post ) {

		// Query by post parent
		$defaults                    = array();
		$defaults['fields']          = 'ids';
		$defaults['post_parent__in'] = array( $post->ID );
		$defaults['post_type']       = array( incassoos_get_activity_post_type(), incassoos_get_occasion_post_type() );
		$defaults['posts_per_page']  = -1;

		// Query collected assets when collected
		if ( incassoos_is_collection_collected( $post ) ) {
			$defaults['post_status'] = incassoos_get_collected_status_id();
		}

		$args = apply_filters( 'incassoos_get_collection_assets_args', wp_parse_args( $args, $defaults ) );

		$query = new WP_Query( $args );
		$posts = $query->posts;

		// Default to empty array
		if ( ! $posts ) {
			$posts = array();
		}
	}

	return (array) apply_filters( 'incassoos_get_collection_assets', $posts, $post, $args );
}

/**
 * Return the Collection's consumer assets
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_consumer_assets'
 *
 * @param  int|WP_user|string $consumer Consumer user object or ID or consumer type id.
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  array       $args Optional. Additional query arguments for {@see WP_Query}.
 * @return array Collection consumer assets
 */
function incassoos_get_collection_consumer_assets( $consumer, $post = 0, $args = array() ) {
	$post  = incassoos_get_collection( $post );
	$posts = array();

	if ( $post ) {

		// Raw assets with parent
		$raw_posts = incassoos_get_collection_consumer_raw_assets( $consumer, $post, array( 'fields' => 'id=>parent' ) );
		$occasions = array_unique( wp_list_pluck( wp_list_filter( $raw_posts, array( 'post_parent' => $post->ID ), 'NOT' ), 'post_parent' ) );
		$others    = wp_list_pluck( wp_list_filter( $raw_posts, array( 'post_parent' => $post->ID ) ), 'ID' );

		// Query by post ID
		$args['post_parent__in'] = false;
		$args['post__in'] = array_values( array_merge( $occasions, $others ) );

		// Query assets
		$posts = incassoos_get_collection_assets( $post, $args );
	}

	return (array) apply_filters( 'incassoos_get_collection_consumer_assets', $posts, $post, $consumer );
}

/**
 * Return the Collection's raw assets
 *
 * Raw assets contain the lowest level objects with registered consumer prices. This
 * excludes Occasions, since they are only collections of Orders.
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_raw_assets'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  array       $args Optional. Additional query arguments for {@see WP_Query}.
 * @return array Collection raw assets
 */
function incassoos_get_collection_raw_assets( $post = 0, $args = array() ) {
	$post  = incassoos_get_collection( $post );
	$posts = array();

	if ( $post ) {
		$parents   = incassoos_get_collection_occasions( $post );
		$parents[] = $post->ID;

		// Query by post parent
		$defaults                    = array();
		$defaults['fields']          = 'ids';
		$defaults['post_parent__in'] = $parents;
		$defaults['post_type']       = array( incassoos_get_activity_post_type(), incassoos_get_order_post_type() );
		$defaults['posts_per_page']  = -1;

		// Query collected assets when collected
		if ( incassoos_is_collection_collected( $post ) ) {
			$defaults['post_status'] = incassoos_get_collected_status_id();
		}

		$args = apply_filters( 'incassoos_get_collection_raw_assets_args', wp_parse_args( $args, $defaults ) );

		$query = new WP_Query( $args );
		$posts = $query->posts;

		// Default to empty array
		if ( ! $posts ) {
			$posts = array();
		}
	}

	return (array) apply_filters( 'incassoos_get_collection_raw_assets', $posts, $post, $args );
}

/**
 * Return the Collection's consumer raw assets
 *
 * Raw assets contain the lowest level objects with registered consumer prices. This
 * excludes Occasions, since these are only collections of Orders.
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_consumer_raw_assets'
 *
 * @param  int|WP_user|string $consumer Consumer user object or ID or consumer type id.
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  array       $args Optional. Additional query arguments for {@see WP_Query}.
 * @return array Collection consumer raw assets
 */
function incassoos_get_collection_consumer_raw_assets( $consumer, $post = 0, $args = array() ) {
	$post  = incassoos_get_collection( $post );
	$posts = array();

	if ( $post ) {

		// Define post meta query
		$meta_query = isset( $args['meta_query'] ) ? $args['meta_query'] : array();
		$meta_query[] = array(
			'relation' => 'OR',
			array(
				'key'   => 'participant',
				'value' => $consumer
			),
			array(
				'key'   => 'consumer',
				'value' => $consumer
			),
			array(
				'key'   => 'consumer_type',
				'value' => $consumer
			)
		);
		$args['meta_query'] = $meta_query;

		// Query posts
		$posts = incassoos_get_collection_raw_assets( $post, $args );
	}

	return (array) apply_filters( 'incassoos_get_collection_consumer_raw_assets', $posts, $post, $consumer, $args );
}

/**
 * Return the Collection's consumer total values by asset
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_consumer_total_by_asset'
 *
 * @param  int|WP_user|string $consumer Consumer user object or ID or consumer type id.
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  bool|array|null $format Optional. Whether to apply currency format. Pass array as format args. Pass
 *                                  null to skip format parsing. Defaults to false.
 * @return array Collection consumer total values by asset. Value is a string when formatting is applied.
 */
function incassoos_get_collection_consumer_total_by_asset( $consumer, $post = 0, $format = false ) {
	$post  = incassoos_get_collection( $post );
	$posts = array();

	if ( $post ) {

		$assets = incassoos_get_collection_consumer_raw_assets( $consumer, $post, array( 'fields' => 'id=>parent' ) );
		if ( $assets ) {
		}
	}

	return $posts;
}

/** Filters *******************************************************************/

/**
 * Modify the Collection's post class
 *
 * @since 1.0.0
 *
 * @param  array       $classes Post class names
 * @param  string      $class   Added class names
 * @param  int}WP_Post $post_id Post ID
 * @return array       Post class names
 */
function incassoos_filter_collection_class( $classes, $class, $post_id ) {
	$post = incassoos_get_collection( $post_id );

	// When this is an Collection
	if ( $post ) {

		// Collection is staged
		if ( incassoos_is_collection_staged( $post ) ) {
			$classes[] = 'collection-staged';
		}

		// Collection is collected
		if ( incassoos_is_collection_collected( $post ) ) {
			$classes[] = 'collection-collected';
		}

		// Collection is locked
		if ( incassoos_is_collection_locked( $post ) ) {
			$classes[] = 'collection-locked';
		}
	}

	// Post is collected
	if ( incassoos_is_post_collected( $post_id ) ) {
		$classes[] = 'post-collected';

	// Post is collectable
	} elseif ( incassoos_is_post_collectable( $post_id ) ) {
		$classes[] = 'post-collectable';
	}

	return $classes;
}

/** Assets ********************************************************************/

/**
 * An asset is a post type-agnostic approach of plugin post objects. This logic is
 * usually applied to fetch post details when there is a benefit of not knowing the
 * post's post type per se.
 */

/**
 * Output the asset's title
 * 
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_asset_title( $post = 0 ) {
	echo incassoos_get_asset_title( $post );
}

/**
 * Return the asset's title
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_asset_title'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Asset title.
 */
function incassoos_get_asset_title( $post = 0 ) {
	$title = '';

	// Collection
	if ( $_post = incassoos_get_collection( $post ) ) {
		$title = incassoos_get_collection_title( $_post );

	// Activity
	} elseif ( $_post = incassoos_get_activity( $post ) ) {
		$title = incassoos_get_activity_title( $_post );

	// Occasion
	} elseif ( $_post = incassoos_get_occasion( $post ) ) {
		$title = incassoos_get_occasion_title( $_post );

	// Custom asset
	} else {
		$title = apply_filters( 'incassoos_get_asset_title', $title, $post );
	}

	return $title;
}

/**
 * Output the asset's date
 * 
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  string      $format Optional. Timestamp's date format to return. Defaults to the `date_format` option.
 */
function incassoos_the_asset_date( $post = 0, $format = false ) {
	echo incassoos_get_asset_date( $post, $format );
}

/**
 * Return the asset's date
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_asset_date'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  string      $format Optional. Timestamp's date format to return. Defaults to the `date_format` option.
 * @return string Asset date.
 */
function incassoos_get_asset_date( $post = 0, $format = false ) {
	$date = '';

	// Collection
	if ( $_post = incassoos_get_collection( $post ) ) {
		$date = incassoos_get_collection_date( $_post, $format );

	// Activity
	} elseif ( $_post = incassoos_get_activity( $post ) ) {
		$date = incassoos_get_activity_date( $_post, $format );

	// Occasion
	} elseif ( $_post = incassoos_get_occasion( $post ) ) {
		$date = incassoos_get_occasion_date( $_post, $format );

	// Custom asset
	} else {

		// Default to the registered date format
		if ( empty( $format ) ) {
			$format = get_option( 'date_format' );
		}

		$date = apply_filters( 'incassoos_get_asset_date', $date, $post, $format );
	}

	return $date;
}

/**
 * Output the asset's url
 * 
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_asset_url( $post = 0 ) {
	echo incassoos_get_asset_url( $post );
}

/**
 * Return the asset's url
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_asset_url'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Asset url.
 */
function incassoos_get_asset_url( $post = 0 ) {
	$url = '';

	// Collection
	if ( $_post = incassoos_get_collection( $post ) ) {
		$url = incassoos_get_collection_url( $_post );

	// Activity
	} elseif ( $_post = incassoos_get_activity( $post ) ) {
		$url = incassoos_get_activity_url( $_post );

	// Occasion
	} elseif ( $_post = incassoos_get_occasion( $post ) ) {
		$url = incassoos_get_occasion_url( $_post );

	// Custom asset
	} else {
		$url = apply_filters( 'incassoos_get_asset_url', $url, $post );
	}

	return $url;
}

/**
 * Output the asset's link
 * 
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_asset_link( $post = 0 ) {
	echo incassoos_get_asset_link( $post );
}

/**
 * Return the asset's link
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_asset_link'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Asset link.
 */
function incassoos_get_asset_link( $post = 0 ) {
	$link = '';

	// Collection
	if ( $_post = incassoos_get_collection( $post ) ) {
		$link = incassoos_get_collection_link( $_post );

	// Activity
	} elseif ( $_post = incassoos_get_activity( $post ) ) {
		$link = incassoos_get_activity_link( $_post );

	// Occasion
	} elseif ( $_post = incassoos_get_occasion( $post ) ) {
		$link = incassoos_get_occasion_link( $_post );

	// Custom asset
	} else {
		$link = apply_filters( 'incassoos_get_asset_link', $link, $post );
	}

	return $link;
}

/**
 * Output the asset's consumer total value
 * 
 * @since 1.0.0
 *
 * @param  int|WP_user|string $consumer Consumer user object or ID or consumer type id.
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  bool|array|null $format Optional. Whether to apply currency format. Pass array as format args. Pass
 *                                  null to skip format parsing. Defaults to false.
 */
function incassoos_the_asset_consumer_total( $consumer, $post = 0, $format = false ) {
	echo incassoos_get_asset_consumer_total( $consumer, $post, $format );
}

/**
 * Return the asset's consumer total value
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_asset_consumer_total'
 *
 * @param  int|WP_user|string $consumer Consumer user object or ID or consumer type id.
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  bool|array|null $format Optional. Whether to apply currency format. Pass array as format args. Pass
 *                                  null to skip format parsing. Defaults to false.
 * @return string|float Asset consumer total value.
 */
function incassoos_get_asset_consumer_total( $consumer, $post = 0, $format = false ) {
	$total = 0;

	// Collection
	if ( $_post = incassoos_get_collection( $post ) ) {
		$total = incassoos_get_collection_consumer_total( $consumer, $_post, $format );

	// Activity
	} elseif ( $_post = incassoos_get_activity( $post ) ) {
		$total = incassoos_get_activity_participant_price( $consumer, $_post, $format );

	// Occasion
	} elseif ( $_post = incassoos_get_occasion( $post ) ) {
		$total = incassoos_get_occasion_consumer_total( $consumer, $_post, $format );

	// Custom asset
	} else {
		$total = apply_filters( 'incassoos_get_asset_consumer_total', $total, $post, $consumer, $format );

		// Apply currency format
		if ( ! is_string( $total ) && null !== $format ) {
			$total = incassoos_get_format_currency( $total, $format );
		}
	}

	return $total;
}

/** Update ********************************************************************/

/**
 * Update the Collection's consumer totals
 *
 * Since the list of totals is already stored as a serialized string, the consumer
 * totals are parsed as strings. This shortens storage space in case of long floats
 * that don't need to be precise beyond the defined decimal length.
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Update success.
 */
function incassoos_update_collection_consumer_totals( $post = 0 ) {
	$post    = incassoos_get_collection( $post );
	$totals  = array();
	$success = false;

	if ( $post ) {
		$consumers      = incassoos_get_collection_consumers( $post );
		$consumer_types = incassoos_get_collection_consumer_types( $post );

		// Consumers
		foreach ( $consumers as $consumer ) {
			$totals[ $consumer ] = incassoos_parse_currency( incassoos_get_collection_consumer_total_raw( $consumer, $post ) );
		}

		// Consumer types
		foreach ( $consumer_types as $consumer_type ) {
			$totals[ $consumer_type ] = incassoos_parse_currency( incassoos_get_collection_consumer_total_raw( $consumer_type, $post ) );
		}

		// Update post meta
		$success = update_post_meta( $post->ID, 'totals', $totals );
	}

	return $success;
}

/**
 * Stage the Collection
 * 
 * @since 1.0.0
 *
 * @global WPDB $wpdb
 *
 * @uses do_action() Calls 'incassoos_stage_collection'
 * @uses do_action() Calls 'incassoos_staged_collection'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Staging success.
 */
function incassoos_stage_collection( $post = 0 ) {
	global $wpdb;

	$post = incassoos_get_collection( $post, false );

	// Bail when the Collection wasn't found
	if ( ! $post )
		return false;

	// Bail when the Collection is already staged or it is collected
	if ( incassoos_is_collection_staged( $post ) || incassoos_is_collection_collected( $post ) )
		return false;

	// Run action before staging
	do_action( 'incassoos_stage_collection', $post );

	// Consolidate Collection consumer totals
	incassoos_update_collection_consumer_totals( $post );

	// Update collection status
	wp_update_post( array( 'ID' => $post->ID, 'post_status' => incassoos_get_staged_status_id() ) );

	// Get assets to update statuses for
	$updating = array_merge(
		incassoos_get_collection_activities( $post ),
		incassoos_get_collection_occasions( $post ),
		incassoos_get_collection_orders( $post )
	);

	// Update post status
	$post_ids = implode( ',', $updating );
	$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET post_status = %s WHERE ID IN ($post_ids)", incassoos_get_collected_status_id() ) );

	// Update staged date
	update_post_meta( $post->ID, 'staged', wp_date( 'Y-m-d H:i:s' ) );

	// Run action after staging
	do_action( 'incassoos_staged_collection', $post );

	return true;
}

/**
 * Unstage the Collection
 * 
 * @since 1.0.0
 *
 * @global WPDB $wpdb
 *
 * @uses do_action() Calls 'incassoos_unstage_collection'
 * @uses do_action() Calls 'incassoos_unstaged_collection'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Unstaging success.
 */
function incassoos_unstage_collection( $post = 0 ) {
	global $wpdb;

	$post = incassoos_get_collection( $post, false );

	// Bail when the Collection wasn't found
	if ( ! $post )
		return false;

	// Bail when the Collection is not staged
	if ( ! incassoos_is_collection_staged( $post ) )
		return false;

	// Run action before unstaging
	do_action( 'incassoos_unstage_collection', $post );

	// Update collection status
	wp_update_post( array( 'ID' => $post->ID, 'post_status' => 'publish' ) );

	// Get assets to update statuses for
	$updating = array_merge(
		incassoos_get_collection_activities( $post ),
		incassoos_get_collection_occasions( $post ),
		incassoos_get_collection_orders( $post )
	);

	// Update post status
	$post_ids = implode( ',', $updating );
	$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} SET post_status = %s WHERE ID IN ($post_ids)", 'publish' ) );

	// Delete staged date
	delete_post_meta( $post->ID, 'staged' );

	// Run action after unstaging
	do_action( 'incassoos_unstaged_collection', $post );

	return true;
}

/**
 * Collect the Collection
 * 
 * @since 1.0.0
 *
 * @uses do_action() Calls 'incassoos_collect_collection'
 * @uses do_action() Calls 'incassoos_collected_collection'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Collection success.
 */
function incassoos_collect_collection( $post = 0 ) {
	global $wpdb;

	$post = incassoos_get_collection( $post, false );

	// Bail when the Collection wasn't found
	if ( ! $post )
		return false;

	// Bail when the Collection is not staged
	if ( ! incassoos_is_collection_staged( $post ) )
		return false;

	// Run action before collecting
	do_action( 'incassoos_collect_collection', $post );

	// Update collection status
	wp_update_post( array( 'ID' => $post->ID, 'post_status' => incassoos_get_collected_status_id() ) );

	// TODO: Send emails to associated consumers through the action hook below
	// incassoos_send_collection_emails( $post );

	// Update collected date
	update_post_meta( $post->ID, 'collected', wp_date( 'Y-m-d H:i:s' ) );

	// Run action after collecting
	do_action( 'incassoos_collected_collection', $post );

	return true;
}

/** Email *********************************************************************/

/**
 * Return the Collection's transaction description
 *
 * When the setting for transaction description is empty, the Collection's title
 * will be used as the default, prepended with the defined organization name.
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_transaction_description'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Collection's transaction description.
 */
function incassoos_get_collection_transaction_description( $post = 0 ) {
	$post        = incassoos_get_collection( $post );
	$description = '';

	if ( $post ) {
		$title       = incassoos_get_collection_title( $post );
		$description = str_replace( '%TITLE%', $title, incassoos_get_transaction_description() );

		// Fallback to just the title
		if ( ! $description ) {
			$organization = incassoos_get_organization_name();
			$description  = $organization ? sprintf( '%s - %s', $organization, $title ) : $title;
		}
	}
	
	return apply_filters( 'incassoos_get_collection_transaction_description', $description, $post );
}

/**
 * Return the Collection's email content for the user
 *
 * @since 1.0.0
 *
 * @param  WP_User|int $user User object or ID.
 * @param  WP_Post|int $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_collection_email_content( $user, $post = 0 ) {
	echo incassoos_get_collection_email_content( $user, $post );
}

/**
 * Return the Collection's email content for the user
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'incassoos_collection_email_content'
 *
 * @param  WP_User|int $user User object or ID.
 * @param  WP_Post|int $post Optional. Post object or ID. Defaults to the current post.
 * @return string Collection email content
 */
function incassoos_get_collection_email_content( $user, $post = 0 ) {
	$post = incassoos_get_collection( $post );
	$user = is_a( $user, 'WP_User' ) ? $user->ID : (int) $user;

	ob_start();

	// Enable custom hooking
	do_action( 'incassoos_collection_email_content', $post, $user );

	$content = ob_get_clean();

	return $content;
}

/**
 * Output the Collection's email salutation
 *
 * @since 1.0.0
 *
 * @param  int          $user User ID.
 * @param  WP_Post|bool $post Post object or False when not found.
 */
function incassoos_collection_email_salutation( $post, $user ) {
	echo wpautop( incassoos_get_custom_email_salutation( $user ) );
}

/**
 * Output the user's Collection amounts table
 *
 * @since 1.0.0
 *
 * @param  int          $user User ID.
 * @param  WP_Post|bool $post Post object or False when not found.
 */
function incassoos_collection_email_amounts_table( $post, $user ) {
	$total = incassoos_get_collection_consumer_total( $user );

	// Bail when the user has no stake
	if ( ! $total || ! $post )
		return;

	// Rearrange filters
	add_filter(    'incassoos_get_activity_date', 'incassoos_filter_activity_date',  10, 3 );
	remove_filter( 'the_title',                   'incassoos_filter_occasion_title', 10, 2 );

	?>

	<p><?php printf(
		esc_html__( 'As registered per %s, you will be charged for the following expenses:', 'incassoos' ),
		incassoos_get_collection_date( $post )
	); ?></p>

	<table cellspacing="0" cellpadding="0" border="0" width="100%">
		<tr>
			<th width="100px" style="text-align: left;"><?php esc_html_e( 'Date', 'incassoos' ); ?></th>
			<th style="text-align: left;"><?php esc_html_e( 'Description', 'incassoos' ); ?></th>
			<th width="100px" style="text-align: right;"><?php esc_html_e( 'Amount', 'incassoos' ); ?></th>
		</tr>

		<?php foreach ( incassoos_get_collection_consumer_assets( $user, $post ) as $item_id ) : ?>

			<tr>
				<td width="100px"><?php incassoos_the_asset_date( $item_id, 'd-m-Y' ); ?></td>
				<td><?php incassoos_the_asset_title( $item_id ); ?></td>
				<td width="100px" style="text-align: right;"><?php incassoos_the_asset_consumer_total( $user, $item_id, true ); ?></td>
			</tr>

		<?php endforeach; ?>

		<tr>
			<td></td>
			<td><?php esc_html_e( 'Total', 'incassoos' ); ?></td>
			<td width="100px" style="text-align: right;"><?php incassoos_the_format_currency( $total ); ?></td>
		</tr>
	</table>

	<p><?php esc_html_e( '*) The mentioned date is the one at which the item was registered.', 'incassoos' ); ?></p>

	<?php

	// Rearrange filters
	remove_filter( 'incassoos_get_activity_date', 'incassoos_filter_activity_date',  10, 3 );
	add_filter(    'the_title',                   'incassoos_filter_occasion_title', 10, 2 );
}

/**
 * Output the user's Collection withdrawal mention
 *
 * @since 1.0.0
 *
 * @param  int          $user User ID.
 * @param  WP_Post|bool $post Post object or False when not found.
 */
function incassoos_collection_email_withdrawal_mention( $post, $user ) {
	$total = incassoos_get_collection_consumer_total( $user );

	// Bail when the user has no stake
	if ( ! $total || ! $post )
		return;

	?>

	<p><?php printf(
		esc_html__( 'The total amount of %1$s will be withdrawn from your account (%2$s) on %3$s.', 'incassoos' ),
		incassoos_get_format_currency( $total ),
		incassoos_get_user_iban( $user ),
		incassoos_get_collection_date( $post )
	); ?></p>

	<?php
}

/**
 * Output the Collection's email closing
 *
 * @since 1.0.0
 *
 * @param  int          $user User ID.
 * @param  WP_Post|bool $post Post object or False when not found.
 */
function incassoos_collection_email_closing( $post, $user ) {
	echo wpautop( incassoos_get_custom_email_closing() );
}
