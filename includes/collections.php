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
 * @param  array $args {
 *     Optional. Additional post requirements.
 *
 *     @type bool $is_collected Whether the Collection should be collected.
 * }
 * @return WP_Post|bool Collection post object or False when not found.
 */
function incassoos_get_collection( $post = 0, $args = array() ) {

	// Get the post
	$post = get_post( $post );

	// Return false when this is not a Collection
	if ( ! $post || incassoos_get_collection_post_type() !== $post->post_type ) {
		$post = false;

	// Check collected status
	} elseif ( isset( $args['is_collected'] ) ) {

		// Return false when collection status does not match the request
		if ( (bool) $args['is_collected'] !== incassoos_is_collection_collected( $post ) ) {
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
 * @param  string      $date_format Optional. Timestamp's date format to return. Defaults to the `date_format` option.
 */
function incassoos_the_collection_created( $post = 0, $date_format = '' ) {
	echo incassoos_get_collection_created( $post, $date_format );
}

/**
 * Return the Collection's created date
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_created'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  string      $date_format Optional. Timestamp's date format to return. Defaults to the `date_format` option.
 * @return string Collection's created date
 */
function incassoos_get_collection_created( $post = 0, $date_format = '' ) {
	$post = incassoos_get_collection( $post );
	$date = $post ? $post->post_date : '';

	// Default to the registered date format
	if ( empty( $date_format ) ) {
		$date_format = get_option( 'date_format' );
	}

	if ( $date ) {
		$date = mysql2date( $date_format, $date );
	}

	return apply_filters( 'incassoos_get_collection_created', $date, $post, $date_format );
}

/**
 * Output the Collection's staged date
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  string      $date_format Optional. Timestamp's date format to return. Defaults to the `date_format` option.
 */
function incassoos_the_collection_staged( $post = 0, $date_format = '' ) {
	echo incassoos_get_collection_staged( $post, $date_format );
}

/**
 * Return the Collection's staged date
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_staged'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  string      $date_format Optional. Timestamp's date format to return. Defaults to the `date_format` option.
 * @return string Collection's staged date.
 */
function incassoos_get_collection_staged( $post = 0, $date_format = '' ) {
	$post = incassoos_get_collection( $post );
	$date = get_post_meta( $post ? $post->ID : 0, 'staged', true );

	// Default to the registered date format
	if ( empty( $date_format ) ) {
		$date_format = get_option( 'date_format' );
	}

	if ( $date ) {
		$date = mysql2date( $date_format, $date );
	} else {
		$date = '';
	}

	return apply_filters( 'incassoos_get_collection_staged', $date, $post, $date_format );
}

/**
 * Output the Collection's collected date
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  string      $date_format Optional. Timestamp's date format to return. Defaults to the `date_format` option.
 */
function incassoos_the_collection_date( $post = 0, $date_format = '' ) {
	echo incassoos_get_collection_date( $post, $date_format );
}

/**
 * Return the Collection's collected date
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_date'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  string      $date_format Optional. Timestamp's date format to return. Defaults to the `date_format` option.
 * @return string Collection's collected date.
 */
function incassoos_get_collection_date( $post = 0, $date_format = '' ) {
	$post = incassoos_get_collection( $post );
	$date = get_post_meta( $post ? $post->ID : 0, 'collected', true );

	// Default to the registered date format
	if ( empty( $date_format ) ) {
		$date_format = get_option( 'date_format' );
	}

	if ( $date ) {
		$date = mysql2date( $date_format, $date );
	} else {
		$date = '';
	}

	return apply_filters( 'incassoos_get_collection_date', $date, $post, $date_format );
}

/**
 * Output the Collection's withdrawal date
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  string      $date_format Optional. Timestamp's date format to return. Defaults to the `date_format` option.
 */
function incassoos_the_collection_withdrawal_date( $post = 0, $date_format = '' ) {
	echo incassoos_get_collection_withdrawal_date( $post, $date_format );
}

/**
 * Return the Collection's withdrawal date
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_withdrawal_date'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  string      $date_format Optional. Timestamp's date format to return. Defaults to the `date_format` option.
 * @return string Collection's withdrawal date.
 */
function incassoos_get_collection_withdrawal_date( $post = 0, $date_format = '' ) {
	$post  = incassoos_get_collection( $post );
	$delay = incassoos_get_collection_withdrawal_delay();
	$date  = incassoos_get_collection_date( $post, $delay ? 'Y-m-d' : $date_format );

	// Default to the registered date format
	if ( empty( $date_format ) ) {
		$date_format = get_option( 'date_format' );
	}

	if ( $date ) {
		if ( $delay ) {
			$date = date( $date_format, strtotime( $date . " + {$delay} day" ) );
		}
	} else {
		$date = '';
	}

	return apply_filters( 'incassoos_get_collection_withdrawal_date', $date, $post, $date_format );
}

/**
 * Output the Collection's total value
 * 
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  bool|array|null $num_format Optional. Whether to apply currency format. Pass array as format args. Pass
 *                                     null to skip format parsing. Defaults to false.
 */
function incassoos_the_collection_total( $post = 0, $num_format = false ) {
	echo incassoos_get_collection_total( $post, $num_format );
}

/**
 * Return the Collection's total value
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_total'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  bool|array|null $num_format Optional. Whether to apply currency format. Pass array as format args. Pass
 *                                     null to skip format parsing. Defaults to false.
 * @return string|float Collection total value.
 */
function incassoos_get_collection_total( $post = 0, $num_format = false ) {
	$post  = incassoos_get_collection( $post );
	$total = get_post_meta( $post ? $post->ID : 0, 'total', true );

	// Get total from raw calculation
	if ( false === $total && $post ) {
		$total = incassoos_get_collection_total_raw( $post );
		update_post_meta( $post->ID, 'total', $total );
	}

	$total = (float) apply_filters( 'incassoos_get_collection_total', (float) $total, $post );

	// Apply currency format
	if ( null !== $num_format ) {
		$total = incassoos_parse_currency( $total, $num_format );
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
	$activities = incassoos_get_collection_activities( $post );
	$orders     = incassoos_get_collection_orders( $post );

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

/**
 * Output the Collection's hint
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_collection_hint( $post = 0 ) {
	echo incassoos_get_collection_hint( $post );
}

/**
 * Return the Collection's hint
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_hint'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Collection hint.
 */
function incassoos_get_collection_hint( $post = 0 ) {
	$post = incassoos_get_collection( $post );
	$hint = esc_html__( 'Not yet collected', 'incassoos' );

	// Get total from raw calculation
	if ( $post && incassoos_is_collection_collected( $post ) ) {
		$hint = sprintf( esc_html__( 'Collected on %s', 'incassoos' ), incassoos_get_collection_date( $post ) );
	}

	return apply_filters( 'incassoos_get_collection_hint', $hint, $post );
}

/**
 * Return whether the Collection's consumer emails are sent
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Collection's consumer emails are sent
 */
function incassoos_is_collection_collect_consumer_emails_sent( $post = 0 ) {
	$post    = incassoos_get_collection( $post );
	$dates   = incassoos_get_collection_collect_consumer_emails_sent( $post );
	$is_sent = ! empty( $dates );

	return apply_filters( 'incassoos_is_collection_collect_consumer_emails_sent', $is_sent, $post );
}

/**
 * Return the Collection's send dates for consumer emails
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_collect_consumer_emails_sent'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  string      $date_format Optional. Timestamp's date format to return. Defaults to the `date_format` option.
 * @return array Collection consumer emails sent dates
 */
function incassoos_get_collection_collect_consumer_emails_sent( $post = 0, $date_format = '' ) {
	$post  = incassoos_get_collection( $post );
	$dates = $post ? get_post_meta( $post->ID, 'consumer_emails_sent', false ) : array();

	// Default to the registered date format
	if ( empty( $date_format ) ) {
		$date_format = get_option( 'date_format' );
	}

	foreach ( $dates as $index => $date ) {
		$dates[ $index ] = mysql2date( $date_format, $date );
	}

	return apply_filters( 'incassoos_get_collection_collect_consumer_emails_sent', $dates, $post, $date_format );
}

/** Assets ********************************************************************/

/**
 * Return whether the post can be collected
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_is_post_collectable'
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

			// Other
			default :
				$retval = (bool) apply_filters( 'incassoos_is_post_collectable', $retval, $post );
		}
	}

	return $retval;
}

/**
 * Return whether the post is collected
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_is_post_collected'
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

			// Other
			default :
				$retval = (bool) apply_filters( 'incassoos_is_post_collected', $retval, $post );
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
 * @param  array       $query_args Optional. Additional query arguments for {@see WP_Query}.
 * @return array Collection activities
 */
function incassoos_get_collection_activities( $post = 0, $query_args = array() ) {
	$post  = incassoos_get_collection( $post );
	$posts = array();

	// Query activities
	if ( $post ) {

		// Query by post parent
		$query_args['post_parent'] = $post->ID;

		// Query posts
		$posts = incassoos_get_activities( $query_args );
	}

	return (array) apply_filters( 'incassoos_get_collection_activities', $posts, $post, $query_args );
}

/**
 * Output the Collection's activity count
 * 
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  array       $query_args Optional. Additional query arguments for {@see WP_Query}.
 */
function incassoos_the_collection_activity_count( $post = 0, $query_args = array() ) {
	echo incassoos_get_collection_activity_count( $post, $query_args );
}

/**
 * Return the Collection's activity count
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_activity_count'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  array       $query_args Optional. Additional query arguments for {@see WP_Query}.
 * @return int Collection activity count
 */
function incassoos_get_collection_activity_count( $post = 0, $query_args = array() ) {
	$post  = incassoos_get_collection( $post );
	$posts = incassoos_get_collection_activities( $post, $query_args );
	$count = count( $posts );

	return (int) apply_filters( 'incassoos_get_collection_activity_count', $count, $post, $query_args );
}

/**
 * Return the Collection's order occasions
 * 
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  array       $query_args Optional. Additional query arguments for {@see WP_Term_Query}.
 * @return array Collection order occasions
 */
function incassoos_get_collection_occasions( $post = 0, $query_args = array() ) {
	$post  = incassoos_get_collection( $post );
	$posts = array();

	// Query order occasions
	if ( $post ) {

		// Query by post parent
		$query_args['post_parent'] = $post->ID;

		// Query posts
		$posts = incassoos_get_occasions( $query_args );
	}

	return (array) apply_filters( 'incassoos_get_collection_occasions', $posts, $post, $query_args );
}

/**
 * Output the Collection's order occasion count
 * 
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  array       $query_args Optional. Additional query arguments for {@see WP_Query}.
 */
function incassoos_the_collection_occasion_count( $post = 0, $query_args = array() ) {
	echo incassoos_get_collection_occasion_count( $post, $query_args );
}

/**
 * Return the Collection's order occasion count
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_occasion_count'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  array       $query_args Optional. Additional query arguments for {@see WP_Query}.
 * @return int Collection order occasion count
 */
function incassoos_get_collection_occasion_count( $post = 0, $query_args = array() ) {
	$post  = incassoos_get_collection( $post );
	$posts = incassoos_get_collection_occasions( $post, $query_args );
	$count = count( $posts );

	return (int) apply_filters( 'incassoos_get_collection_occasion_count', $count, $post, $query_args );
}

/**
 * Return the Collection's orders
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_orders'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  array       $query_args Optional. Additional query arguments for {@see WP_Query}.
 * @return array Collection orders
 */
function incassoos_get_collection_orders( $post = 0, $query_args = array() ) {
	$post      = incassoos_get_collection( $post );
	$occasions = incassoos_get_collection_occasions( $post );
	$posts     = array();

	// Query orders
	if ( $post && $occasions ) {

		// Query by post parent
		$query_args['post_parent__in'] = $occasions;

		// Query posts
		$posts = incassoos_get_orders( $query_args );
	}

	return (array) apply_filters( 'incassoos_get_collection_orders', $posts, $post, $query_args );
}

/**
 * Output the Collection's order count
 * 
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  array       $query_args Optional. Additional query arguments for {@see WP_Query}.
 */
function incassoos_the_collection_order_count( $post = 0, $query_args = array() ) {
	echo incassoos_get_collection_order_count( $post, $query_args );
}

/**
 * Return the Collection's order count
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_order_count'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  array       $query_args Optional. Additional query arguments for {@see WP_Query}.
 * @return int Collection order count
 */
function incassoos_get_collection_order_count( $post = 0, $query_args = array() ) {
	$post  = incassoos_get_collection( $post );
	$posts = incassoos_get_collection_orders( $post, $query_args );
	$count = count( $posts );

	return (int) apply_filters( 'incassoos_get_collection_order_count', $count, $post, $query_args );
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
 * @param  array $query_args Optional. Additional query arguments for {@see WP_User_Query}.
 * @return array Collection consumer user objects
 */
function incassoos_get_collection_consumer_users( $post = 0, $query_args = array() ) {
	$post      = incassoos_get_collection( $post );
	$consumers = incassoos_get_collection_consumers( $post );
	$users     = array();

	if ( $consumers ) {

		// Query selected users
		$user_ids = ! empty( $query_args['include'] ) ? array_intersect( (array) $query_args['include'], $consumers ) : $consumers;
		$query_args['include'] = array_map( 'intval', array_unique( array_filter( $user_ids ) ) );

		// Query users
		$users = incassoos_get_users( $query_args );
	}

	return apply_filters( 'incassoos_get_collection_consumer_users', $users, $post, $query_args );
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
 * Return the Collection's unknown consumers
 *
 * @since 1.0.0
 *
 * @global WPDB $wpdb
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_unknown_consumers'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return array Collection unknown consumers
 */
function incassoos_get_collection_unknown_consumers( $post = 0 ) {
	global $wpdb;

	$post        = incassoos_get_collection( $post );
	$consumers   = incassoos_get_collection_consumers( $post );
	$unknown_ids = array();

	if ( $post && $consumers ) {

		// Define post meta query
		$user_ids = implode( ',', $consumers );
		$sql      = "SELECT ID FROM {$wpdb->users} WHERE ID IN ($user_ids)";

		// Query all types
		if ( $values = $wpdb->get_col( $sql ) ) {
			$unknown_ids = array_diff( $consumers, array_filter( $values ) );
		}
	}

	return apply_filters( 'incassoos_get_collection_unknown_consumers', $unknown_ids, $post );
}

/**
 * Return whether the Collection has unknown consumers
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_collection_has_unknown_participant'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Collection has unknown consumers
 */
function incassoos_collection_has_unknown_consumers( $post = 0 ) {
	$post    = incassoos_get_collection( $post );
	$unknown = (bool) incassoos_get_collection_unknown_consumers( $post );

	return (bool) apply_filters( 'incassoos_collection_has_unknown_consumers', $unknown, $post );
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

		// Consider unknown users
		foreach ( incassoos_get_collection_unknown_consumers( $post ) as $user_id ) {
			$types[] = incassoos_get_unknown_user_consumer_type_id( $user_id );
		}
	}

	return (array) apply_filters( 'incassoos_get_collection_consumer_types', $types, $post );
}

/**
 * Output the Collection's consumer total value
 * 
 * @since 1.0.0
 *
 * @param  int|WP_User|string $consumer Consumer user object or ID or consumer type id.
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  bool|array|null $num_format Optional. Whether to apply currency format. Pass array as format args. Pass
 *                                     null to skip format parsing. Defaults to false.
 */
function incassoos_the_collection_consumer_total( $consumer, $post = 0, $num_format = false ) {
	echo incassoos_get_collection_consumer_total( $consumer, $post, $num_format );
}

/**
 * Return the Collection's consumer total value
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_consumer_total'
 *
 * @param  int|WP_User|string $consumer Consumer user object or ID or consumer type id.
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  bool|array|null $num_format Optional. Whether to apply currency format. Pass array as format args. Pass
 *                                     null to skip format parsing. Defaults to false.
 * @return string|float Collection consumer total value.
 */
function incassoos_get_collection_consumer_total( $consumer, $post = 0, $num_format = false ) {
	$_consumer = is_a( $consumer, 'WP_User' ) ? $consumer->ID : $consumer;
	$post      = incassoos_get_collection( $post );
	$total     = 0;

	if ( $post ) {

		// Get collected amount
		if ( incassoos_is_collection_collected( $post ) ) {

			// Consider unknown users
			if ( incassoos_is_unknown_user_consumer_type_id( $consumer ) ) {
				$_consumer = incassoos_get_user_id_from_unknown_user_consumer_type( $consumer );
			}

			$totals = get_post_meta( $post->ID, 'totals', true );
			$total  = isset( $totals[ $_consumer ] ) ? (float) $totals[ $_consumer ] : 0;

			// Consider all unknown users
			if ( incassoos_get_unknown_user_consumer_type_id_base() === $consumer ) {
				foreach ( incassoos_get_collection_unknown_consumers( $post ) as $user_id ) {
					if ( isset( $totals[ $user_id ] ) ) {
						$total += $totals[ $user_id ];
					}
				}
			}

		// Get calculated amount
		} else {
			$total = incassoos_get_collection_consumer_total_raw( $consumer, $post );
		}
	}

	$total = (float) apply_filters( 'incassoos_get_collection_consumer_total', (float) $total, $consumer, $post, $num_format );

	// Apply currency format
	if ( null !== $num_format ) {
		$total = incassoos_parse_currency( $total, $num_format );
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
 * @param  int|WP_User|string $consumer Consumer user object or ID or consumer type id.
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Collection consumer raw total value.
 */
function incassoos_get_collection_consumer_total_raw( $consumer, $post = 0 ) {
	global $wpdb;

	$_consumer = is_a( $consumer, 'WP_User' ) ? $consumer->ID : $consumer;
	$post      = incassoos_get_collection( $post );
	$total     = 0;

	// Query assets
	$assets = incassoos_get_collection_consumer_raw_assets( $consumer, $post );

	if ( $post && $assets ) {

		// Consider unknown users
		if ( incassoos_is_unknown_user_consumer_type_id( $consumer ) ) {
			$_consumer = incassoos_get_user_id_from_unknown_user_consumer_type( $consumer );

		// Consider all unknown users
		} elseif ( incassoos_get_unknown_user_consumer_type_id_base() === $consumer ) {
			$_consumer = incassoos_get_collection_unknown_consumers( $post );
		}

		// Define post meta query
		$post_ids = implode( ',', $assets );
		$sql      = $wpdb->prepare( "SELECT post_id, meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id IN ($post_ids) AND ( meta_key = %s OR meta_key = %s OR meta_key = %s )", 'price', 'prices', 'total' );

		// Query all prices
		if ( $values = $wpdb->get_results( $sql ) ) {
			foreach ( $values as $value ) {

				// Price, Prices, Total in that order specifically
				if ( 'price' === $value->meta_key ) {

					// Find whether a consumer price was defined
					$post_rows = wp_list_filter( $values, array( 'post_id' => $value->post_id ) );
					$prices    = wp_list_filter( $post_rows, array( 'meta_key' => 'prices' ) );

					if ( $prices ) {
						$prices = reset( $prices );
						$prices = maybe_unserialize( $prices->meta_value );
					}

					// Rely on post price
					foreach ( (array) $_consumer as $__consumer ) {
						if ( ! isset( $prices[ $__consumer ] ) ) {
							$total += (float) $value->meta_value;
						}
					}

				} elseif ( 'prices' === $value->meta_key ) {
					$prices = maybe_unserialize( $value->meta_value );

					// When a consumer price was defined
					foreach ( (array) $_consumer as $__consumer ) {
						if ( isset( $prices[ $__consumer ] ) ) {
							$total += (float) $prices[ $__consumer ];
						}
					}

				// Totals for Orders only
				} elseif ( 'total' === $value->meta_key && incassoos_get_order( $value->post_id ) ) {
					$total += (float) $value->meta_value;
				}
			}
		}
	}

	return (float) apply_filters( 'incassoos_get_collection_consumer_total_raw', (float) $total, $consumer, $post );
}

/**
 * Return whether the Collection has any consumer with a negative total value
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_collection_has_consumer_with_negative_total'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Has Collection any consumer with negative total?
 */
function incassoos_collection_has_consumer_with_negative_total( $post = 0 ) {
	$post                = incassoos_get_collection( $post );
	$with_negative_value = false;

	if ( $post ) {

		// Walk consumer users
		foreach ( incassoos_get_collection_consumer_users( $post ) as $user ) {

			// Find the first negative consumer total
			if ( incassoos_get_collection_consumer_total( $user->ID, $post ) < 0 ) {
				$with_negative_value = true;
				break;
			}
		}
	}

	return (bool) apply_filters( 'incassoos_collection_has_consumer_with_negative_total', $with_negative_value, $post );
}

/**
 * Return the Collection's assets
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_assets'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  array       $query_args Optional. Additional query arguments for {@see WP_Query}.
 * @return array Collection assets
 */
function incassoos_get_collection_assets( $post = 0, $query_args = array() ) {
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

		$query_args = apply_filters( 'incassoos_get_collection_assets_args', wp_parse_args( $query_args, $defaults ), $post, $query_args );

		$query = new WP_Query( $query_args );
		$posts = $query->posts;

		// Default to empty array
		if ( ! $posts ) {
			$posts = array();
		}
	}

	return (array) apply_filters( 'incassoos_get_collection_assets', $posts, $post, $query_args );
}

/**
 * Return the Collection's consumer assets
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_consumer_assets'
 *
 * @param  int|WP_User|string $consumer Consumer user object or ID or consumer type id.
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  array       $query_args Optional. Additional query arguments for {@see WP_Query}.
 * @return array Collection consumer assets
 */
function incassoos_get_collection_consumer_assets( $consumer, $post = 0, $query_args = array() ) {
	$post  = incassoos_get_collection( $post );
	$posts = array();

	if ( $post ) {

		// Raw assets with parent
		$raw_posts = incassoos_get_collection_consumer_raw_assets( $consumer, $post, array( 'fields' => 'id=>parent' ) );
		$via_posts = array_unique( wp_list_pluck( wp_list_filter( $raw_posts, array( 'post_parent' => $post->ID ), 'NOT' ), 'post_parent' ) );
		$others    = wp_list_pluck( wp_list_filter( $raw_posts, array( 'post_parent' => $post->ID ) ), 'ID' );

		// Query by post ID
		$query_args['post_parent__in'] = false;
		$query_args['post__in'] = array_values( array_merge( $via_posts, $others ) );

		// Query assets
		$posts = incassoos_get_collection_assets( $post, $query_args );
	}

	return (array) apply_filters( 'incassoos_get_collection_consumer_assets', $posts, $consumer, $post, $query_args );
}

/**
 * Return the Collection's raw assets
 *
 * Raw assets contain the lowest level objects with registered consumer prices. This
 * excludes Occasions, since they are only bundles of Orders.
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_raw_assets'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  array       $query_args Optional. Additional query arguments for {@see WP_Query}.
 * @return array Collection raw assets
 */
function incassoos_get_collection_raw_assets( $post = 0, $query_args = array() ) {
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

		$query_args = apply_filters( 'incassoos_get_collection_raw_assets_args', wp_parse_args( $query_args, $defaults ), $post, $query_args );

		$query = new WP_Query( $query_args );
		$posts = $query->posts;

		// Default to empty array
		if ( ! $posts ) {
			$posts = array();
		}
	}

	return (array) apply_filters( 'incassoos_get_collection_raw_assets', $posts, $post, $query_args );
}

/**
 * Return the Collection's consumer raw assets
 *
 * Raw assets contain the lowest level objects with registered consumer prices. This
 * excludes Occasions, since these are only bundles of Orders.
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_consumer_raw_assets'
 *
 * @param  int|WP_User|string $consumer Consumer user object or ID or consumer type id.
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  array       $query_args Optional. Additional query arguments for {@see WP_Query}.
 * @return array Collection consumer raw assets
 */
function incassoos_get_collection_consumer_raw_assets( $consumer, $post = 0, $query_args = array() ) {
	$_consumer = is_a( $consumer, 'WP_User' ) ? $consumer->ID : $consumer;
	$post      = incassoos_get_collection( $post );
	$posts     = array();

	if ( $post ) {

		// Consider unknown users
		if ( incassoos_is_unknown_user_consumer_type_id( $consumer ) ) {
			$_consumer = incassoos_get_user_id_from_unknown_user_consumer_type( $consumer );

		// Consider all unknown users
		} elseif ( incassoos_get_unknown_user_consumer_type_id_base() === $consumer ) {
			$_consumer = incassoos_get_collection_unknown_consumers( $post );
		}

		// Define post meta query
		$meta_query = isset( $query_args['meta_query'] ) ? $query_args['meta_query'] : array();
		$meta_query[] = array(
			'relation' => 'OR',
			array(
				'key'     => is_numeric( $_consumer ) || is_array( $_consumer ) ? 'participant' : 'participant_type',
				'value'   => (array) $_consumer,
				'compare' => 'IN'
			),
			array(
				'key'     => is_numeric( $_consumer ) || is_array( $_consumer ) ? 'consumer' : 'consumer_type',
				'value'   => (array) $_consumer,
				'compare' => 'IN'
			)
		);
		$query_args['meta_query'] = $meta_query;

		// Query posts
		$posts = incassoos_get_collection_raw_assets( $post, $query_args );
	}

	return (array) apply_filters( 'incassoos_get_collection_consumer_raw_assets', $posts, $consumer, $post, $query_args );
}

/**
 * Return the Collection's consumer total values by asset
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_collection_consumer_total_by_asset'
 *
 * @param  int|WP_User|string $consumer Consumer user object or ID or consumer type id.
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  bool|array|null $num_format Optional. Whether to apply currency format. Pass array as format args. Pass
 *                                     null to skip format parsing. Defaults to false.
 * @return array Collection consumer total values by asset. Value is a string when formatting is applied.
 */
function incassoos_get_collection_consumer_total_by_asset( $consumer, $post = 0, $num_format = false ) {
	$_consumer = is_a( $consumer, 'WP_User' ) ? $consumer->ID : $consumer;
	$post      = incassoos_get_collection( $post );
	$totals    = array();

	if ( $post ) {

		// Consider unknown users
		if ( incassoos_is_unknown_user_consumer_type_id( $consumer ) ) {
			$_consumer = incassoos_get_user_id_from_unknown_user_consumer_type( $consumer );

		// Consider all unknown users
		} elseif ( incassoos_get_unknown_user_consumer_type_id_base() === $consumer ) {
			$_consumer = incassoos_get_collection_unknown_consumers( $post );
		}

		$raw_assets = incassoos_get_collection_consumer_assets( $consumer, $post, array( 'fields' => 'id=>parent' ) );
		if ( $assets ) {
			// TODO
		}
	}

	return apply_filters( 'incassoos_get_collection_consumer_total_by_asset', $totals, $consumer, $post, $num_format );
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

	$post = incassoos_get_collection( $post, array( 'is_collected' => false ) );

	// Bail when the Collection wasn't found
	if ( ! $post )
		return false;

	// Bail when the Collection is already staged or it is collected
	if ( incassoos_is_collection_staged( $post ) || incassoos_is_collection_collected( $post ) )
		return false;

	// Bail when the Collection contains negative totals
	if ( incassoos_collection_has_consumer_with_negative_total( $post ) )
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
	update_post_meta( $post->ID, 'staged', date( 'Y-m-d H:i:s' ) );

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

	$post = incassoos_get_collection( $post, array( 'is_collected' => false ) );

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

	$post = incassoos_get_collection( $post, array( 'is_collected' => false ) );

	// Bail when the Collection wasn't found
	if ( ! $post )
		return false;

	// Bail when the Collection is not staged
	if ( ! incassoos_is_collection_staged( $post ) )
		return false;

	// Run action before collecting
	do_action( 'incassoos_collect_collection', $post );

	// Update collection status and collector
	wp_update_post( array(
		'ID'          => $post->ID,
		'post_status' => incassoos_get_collected_status_id(),
		'post_author' => get_current_user_id()
	) );

	// Update collected date
	update_post_meta( $post->ID, 'collected', date( 'Y-m-d H:i:s' ) );

	// Run action after collecting
	do_action( 'incassoos_collected_collection', $post );

	return true;
}

/**
 * Return whether the provided data is valid for an Collection
 *
 * @since 1.0.0
 *
 * @param  array|object $args Collection attributes to update
 * @return WP_Error|bool Error object on invalidation, true when validated
 */
function incassoos_validate_collection( $args = array() ) {

	// Array-fy when an object was provided
	if ( ! is_array( $args ) ) {
		$args = (array) $args;
	}

	$update = isset( $args['ID'] ) && ! empty( $args['ID'] );

	// Parse defaults
	$args = wp_parse_args( $args, array(
		'post_title' => ''
	) );

	// Validate title
	$title = incassoos_validate_title( $args['post_title'] );
	if ( is_wp_error( $title ) ) {
		return $title;
	}

	return true;
}

/** Email *********************************************************************/

/**
 * Send a Collection's email for testing purposes
 *
 * The email contains the content of a randomly selected consumer.
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Was the email sent?
 */
function incassoos_send_collection_collect_test_email( $post = 0 ) {
	$post = incassoos_get_collection( $post );
	$args = array( 'incassoos_email_type' => 'incassoos-collection-collect-test' );
	$sent = false;

	if ( $post ) {

		// Find a random Collection's consumer
		$consumers = incassoos_get_collection_consumers( $post );
		$key_rand  = array_rand( $consumers );

		// Test email prefixes
		$test_prefix_title   = esc_attr__( '// TEST EMAIL // %s', 'incassoos' );
		$test_prefix_content = sprintf( esc_html__( '&mdash; This is a test email sent from %s &mdash;', 'incassoos' ), site_url() );

		// Define email details
		$args['to']      = incassoos_get_sender_email_address(); // Send to self
		$args['post']    = $post; // Provide post object to later filters
		$args['user_id'] = $consumers[ $key_rand ];
		$args['subject'] = sprintf( $test_prefix_title, incassoos_get_collection_transaction_description( $post ) );
		$args['message'] = '<p>' . $test_prefix_content . '</p>' . incassoos_get_collection_collect_email_content( $consumers[ $key_rand ], $post );

		// Send the email
		$sent = incassoos_send_email( $args );
	}

	return $sent;
}

/**
 * Send a Collection's email to its consumers
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Were the emails sent?
 */
function incassoos_send_collection_collect_consumer_emails( $post = 0 ) {
	$post = incassoos_get_collection( $post );
	$args = array( 'incassoos_email_type' => 'incassoos-collection-collect-consumer' );
	$sent = false;

	if ( $post ) {
		$sent = array();

		foreach ( incassoos_get_collection_consumers( $post ) as $user_id ) {
			$user = incassoos_get_user( $user_id );
			if ( $user ) {

				// Define email details
				$args['to']      = $user->user_email;
				$args['post']    = $post; // Provide post object to later filters
				$args['user_id'] = $user->ID;
				$args['subject'] = incassoos_get_collection_transaction_description( $post );
				$args['message'] = incassoos_get_collection_collect_email_content( $user->ID, $post );

				// Send the email
				$sent[ $user->ID ] = $is_sent = incassoos_send_email( $args );

				// Break out when sending failed
				if ( ! $is_sent ) {
					break;
				}
			}
		}

		$sent = ! in_array( false, $sent );

		// Register emails sent date
		if ( $sent ) {
			add_post_meta( $post->ID, 'consumer_emails_sent', date( 'Y-m-d H:i:s' ) );
		}
	}

	return $sent;
}

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
		$description = str_replace( '{{TITLE}}', $title, incassoos_get_transaction_description() );

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
function incassoos_the_collection_collect_email_content( $user, $post = 0 ) {
	echo incassoos_get_collection_collect_email_content( $user, $post );
}

/**
 * Return the Collection's email content for the user
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'incassoos_collection_collect_email_content'
 *
 * @param  WP_User|int $user User object or ID.
 * @param  WP_Post|int $post Optional. Post object or ID. Defaults to the current post.
 * @return string Collection email content
 */
function incassoos_get_collection_collect_email_content( $user, $post = 0 ) {
	$post = incassoos_get_collection( $post );
	$user = is_a( $user, 'WP_User' ) ? $user->ID : (int) $user;

	ob_start();

	// Enable custom hooking
	do_action( 'incassoos_collection_collect_email_content', $post, $user );

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
function incassoos_collection_collect_email_salutation( $post, $user ) {
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
function incassoos_collection_collect_email_amounts_table( $post, $user ) {
	$total = incassoos_get_collection_consumer_total( $user, $post );

	// Bail when the user has no stake
	if ( ! $total || ! $post )
		return;

	// Rearrange filters
	add_filter(    'incassoos_get_activity_date', 'incassoos_filter_default_activity_date_to_date_created',  10, 3 );
	remove_filter( 'the_title',                   'incassoos_filter_occasion_title', 10, 2 );

	// Get the relevant date
	$collection_date = incassoos_get_collection_date( $post );
	if ( ! $collection_date ) {
		$collection_date = wp_date( get_option( 'date_format' ) );
	}

	?>

	<p><?php printf( esc_html__( 'As registered per %s, you will be charged for the following expenses:', 'incassoos' ), $collection_date ); ?></p>

	<table cellspacing="0" cellpadding="0" border="0" width="100%">
		<tr>
			<th width="100px" style="text-align: left;"><?php esc_html_e( 'Date', 'incassoos' ); ?></th>
			<th style="text-align: left;"><?php esc_html_e( 'Description', 'incassoos' ); ?></th>
			<th width="100px" style="text-align: right;"><?php esc_html_e( 'Amount', 'incassoos' ); ?></th>
		</tr>

		<?php foreach ( incassoos_get_collection_consumer_assets( $user, $post ) as $item_id ) : ?>

			<tr>
				<td width="100px"><?php incassoos_the_post_date( $item_id, 'd-m-Y' ); ?></td>
				<td><?php incassoos_the_post_title( $item_id ); ?></td>
				<td width="100px" style="text-align: right;"><?php incassoos_the_post_consumer_total( $user, $item_id, true ); ?></td>
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
	remove_filter( 'incassoos_get_activity_date', 'incassoos_filter_default_activity_date_to_date_created',  10, 3 );
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
function incassoos_collection_collect_email_withdrawal_mention( $post, $user ) {
	$total = incassoos_get_collection_consumer_total( $user, $post );

	// Bail when the user has no stake
	if ( ! $total || ! $post )
		return;

	$withdrawal_date = incassoos_get_collection_withdrawal_date( $post );

	// Default to now + delay
	if ( empty( $withdrawal_date ) ) {
		$delay = incassoos_get_collection_withdrawal_delay();
		if ( $delay ) {
			$withdrawal_date = date( get_option( 'date_format' ), strtotime( "+ {$delay} day" ) );
		}
	}

	echo wpautop( sprintf(
		esc_html__( 'The total amount of %1$s will be withdrawn from your account (%2$s) on or around %3$s.', 'incassoos' ),
		incassoos_get_format_currency( $total ),
		incassoos_get_user_iban( $user ) ?: '?',
		$withdrawal_date
	) );
}

/**
 * Output the Collection's email closing
 *
 * @since 1.0.0
 *
 * @param  int          $user User ID.
 * @param  WP_Post|bool $post Post object or False when not found.
 */
function incassoos_collection_collect_email_closing( $post, $user ) {
	echo wpautop( incassoos_get_custom_email_closing() );
}
