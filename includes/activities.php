<?php

/**
 * Incassoos Activity Functions
 *
 * @package Incassoos
 * @subpackage Main
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Post Type *****************************************************************/

/**
 * Return the Activity post type
 *
 * @since 1.0.0
 *
 * @return string Post type name
 */
function incassoos_get_activity_post_type() {
	return incassoos()->activity_post_type;
}

/**
 * Return the labels for the Activity post type
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_activity_post_type_labels'
 * @return array Activity post type labels
 */
function incassoos_get_activity_post_type_labels() {
	return apply_filters( 'incassoos_get_activity_post_type_labels', array(
		'name'                  => __( 'Incassoos Activities',         'incassoos' ),
		'menu_name'             => __( 'Activities',                   'incassoos' ),
		'singular_name'         => __( 'Activity',                     'incassoos' ),
		'all_items'             => __( 'All Activities',               'incassoos' ),
		'add_new'               => __( 'New Activity',                 'incassoos' ),
		'add_new_item'          => __( 'Create New Activity',          'incassoos' ),
		'edit'                  => __( 'Edit',                         'incassoos' ),
		'edit_item'             => __( 'Edit Activity',                'incassoos' ),
		'new_item'              => __( 'New Activity',                 'incassoos' ),
		'view'                  => __( 'View Activity',                'incassoos' ),
		'view_item'             => __( 'View Activity',                'incassoos' ),
		'view_items'            => __( 'View Activities',              'incassoos' ), // Since WP 4.7
		'search_items'          => __( 'Search Activities',            'incassoos' ),
		'not_found'             => __( 'No activities found',          'incassoos' ),
		'not_found_in_trash'    => __( 'No activities found in Trash', 'incassoos' ),
		'insert_into_item'      => __( 'Insert into activity',         'incassoos' ),
		'uploaded_to_this_item' => __( 'Uploaded to this activity',    'incassoos' ),
		'filter_items_list'     => __( 'Filter activities list',       'incassoos' ),
		'items_list_navigation' => __( 'Activities list navigation',   'incassoos' ),
		'items_list'            => __( 'Activities list',              'incassoos' ),
	) );
}

/**
 * Return an array of features the Activity post type supports
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_activity_post_type_supports'
 * @return array Activity post type support
 */
function incassoos_get_activity_post_type_supports() {
	return apply_filters( 'incassoos_get_activity_post_type_supports', array(
		'title',
		'incassoos-notes'
	) );
}

/** Taxonomy: Activity Category ***********************************************/

/**
 * Return the Activity Category taxonomy
 *
 * @since 1.0.0
 *
 * @return string Taxonomy name
 */
function incassoos_get_activity_cat_tax_id() {
	return incassoos()->activity_cat_tax_id;
}

/**
 * Return the labels for the Activity Category taxonomy
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_activity_cat_tax_labels'
 * @return array Activity Category taxonomy labels
 */
function incassoos_get_activity_cat_tax_labels() {
	return apply_filters( 'incassoos_get_activity_cat_tax_labels', array(
		'name'                       => __( 'Incassoos Activity Categories',        'incassoos' ),
		'menu_name'                  => __( 'Categories',                           'incassoos' ),
		'singular_name'              => __( 'Activity Category',                    'incassoos' ),
		'search_items'               => __( 'Search Activity Categories',           'incassoos' ),
		'popular_items'              => null, // Disable tagcloud
		'all_items'                  => __( 'All Activity Categories',              'incassoos' ),
		'no_items'                   => __( 'No Activity Category',                 'incassoos' ),
		'edit_item'                  => __( 'Edit Activity Category',               'incassoos' ),
		'update_item'                => __( 'Update Activity Category',             'incassoos' ),
		'add_new_item'               => __( 'Add New Activity Category',            'incassoos' ),
		'new_item_name'              => __( 'New Activity Category Name',           'incassoos' ),
		'view_item'                  => __( 'View Activity Category',               'incassoos' ),

		'separate_items_with_commas' => __( 'Separate categories with commas',      'incassoos' ),
		'add_or_remove_items'        => __( 'Add or remove categories',             'incassoos' ),
		'choose_from_most_used'      => __( 'Choose from the most used categories', 'incassoos' ),
		'not_found'                  => __( 'No categories found.',                 'incassoos' ),
		'no_terms'                   => __( 'No categories',                        'incassoos' ),
		'items_list_navigation'      => __( 'Activity categories list navigation',  'incassoos' ),
		'items_list'                 => __( 'Activity categories list',             'incassoos' ),
	) );
}

/**
 * Act when the Activity Category taxonomy has been registered
 *
 * @since 1.0.0
 */
function incassoos_registered_activity_cat_taxonomy() {
	add_action( 'incassoos_rest_api_init', 'incassoos_register_activity_cat_rest_fields' );
}

/**
 * Register REST fields for the Activity Category taxonomy
 *
 * @since 1.0.0
 */
function incassoos_register_activity_cat_rest_fields() {

	// Get assets
	$activity = incassoos_get_activity_post_type();

	// Add category to Activity
	register_rest_field(
		$activity,
		'categories',
		array(
			'get_callback' => 'incassoos_get_activity_rest_activity_categories'
		)
	);
}

/**
 * Return the value for the 'activity_categories' activity REST API field
 *
 * @since 1.0.0
 *
 * @param array $object Request object
 * @param string $field_name Request field name
 * @param WP_REST_Request $request Current REST request
 * @return array Location term(s)
 */
function incassoos_get_activity_rest_activity_categories( $object, $field_name, $request ) {
	return wp_get_object_terms( $object['id'], incassoos_get_activity_cat_tax_id() );
}

/**
 * Return whether the given post has any or the given Activity Category
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  int|WP_Term $term Optional. Term object or ID. Defaults to any term.
 * @return bool Object has a/the Activity Category
 */
function incassoos_activity_has_category( $post = 0, $term = 0 ) {
	return has_term( $term, incassoos_get_activity_cat_tax_id(), $post );
}

/**
 * Output the Activity's category
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_activity_category( $post = 0 ) {
	the_terms( $post, incassoos_get_activity_cat_tax_id() );
}

/** Template ******************************************************************/

/**
 * Return the Activity
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return WP_Post|bool Activity post object or False when not found.
 */
function incassoos_get_activity( $post = 0 ) {

	// Get the post
	$post = get_post( $post );

	// Return false when this is not a Activity
	if ( ! $post || incassoos_get_activity_post_type() !== $post->post_type ) {
		$post = false;
	}

	return $post;
}

/**
 * Output the Activity's title
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_activity_title( $post = 0 ) {
	echo incassoos_get_activity_title( $post );
}

/**
 * Return the Activity's title
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_activity_title'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Activity title.
 */
function incassoos_get_activity_title( $post = 0 ) {
	$post  = incassoos_get_activity( $post );
	$title = $post ? get_the_title( $post ) : '';

	return apply_filters( 'incassoos_get_activity_title', $title, $post );
}

/**
 * Output the Activity's url
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_activity_url( $post = 0 ) {
	echo incassoos_get_activity_url( $post );
}

/**
 * Return the Activity's url
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_activity_url'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Activity's url
 */
function incassoos_get_activity_url( $post = 0 ) {
	$post = incassoos_get_activity( $post );
	$url  = $post ? add_query_arg( array( 'post' => $post->ID, 'action' => 'edit' ), admin_url( 'post.php' ) ) : '';

	return apply_filters( 'incassoos_get_activity_url', $url, $post );
}

/**
 * Output the Activity's link
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_activity_link( $post = 0 ) {
	echo incassoos_get_activity_link( $post );
}

/**
 * Return the Activity's link
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_activity_link'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Activity's link
 */
function incassoos_get_activity_link( $post = 0 ) {
	$post = incassoos_get_activity( $post );
	$link = $post ? sprintf( '<a href="%s">%s</a>', esc_url( incassoos_get_activity_url( $post ) ), incassoos_get_activity_title( $post ) ) : '';

	return apply_filters( 'incassoos_get_activity_link', $link, $post );
}

/**
 * Output the Activity's author name
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_activity_author( $post = 0 ) {
	echo incassoos_get_activity_author( $post );
}

/**
 * Return the Activity's author name
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_activity_author'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Activity author name or False when not found.
 */
function incassoos_get_activity_author( $post = 0 ) {
	$post   = incassoos_get_activity( $post );
	$author = get_userdata( $post ? $post->post_author : 0 );

	if ( $author && $author->exists() ) {
		$author = $author->display_name;
	} else {
		$author = '';
	}

	return apply_filters( 'incassoos_get_activity_author', $author, $post );
}

/**
 * Output the Activity's price
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  bool|array|null $format Optional. Whether to apply currency format. Pass array as format args. Pass
 *                                  null to skip format parsing. Defaults to false.
 */
function incassoos_the_activity_price( $post = 0, $format = false ) {
	echo incassoos_get_activity_price( $post, $format );
}

/**
 * Return the Activity's price
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_activity_price'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  bool|array|null $format Optional. Whether to apply currency format. Pass array as format args. Pass
 *                                  null to skip format parsing. Defaults to false.
 * @return string|float Activity price.
 */
function incassoos_get_activity_price( $post = 0, $format = false ) {
	$post  = incassoos_get_activity( $post );
	$price = get_post_meta( $post ? $post->ID : 0, 'price', true );

	if ( ! $price ) {
		$price = 0;
	}

	$price = (float) apply_filters( 'incassoos_get_activity_price', (float) $price, $post );

	// Apply currency format
	if ( null !== $format ) {
		$price = incassoos_parse_currency( $price, $format );
	}

	return $price;
}

/**
 * Output the Activity's created date
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  string      $format Optional. Timestamp's date format to return. Defaults to the `date_format` option.
 */
function incassoos_the_activity_created( $post = 0, $format = '' ) {
	echo incassoos_get_activity_created( $post, $format );
}

/**
 * Return the Activity's created date
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_activity_created'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  string      $format Optional. Timestamp's date format to return. Defaults to the `date_format` option.
 * @return string Activity created date.
 */
function incassoos_get_activity_created( $post = 0, $format = '' ) {
	$post = incassoos_get_activity( $post );
	$date = $post ? $post->post_date : '';

	// Default to the registered date format
	if ( empty( $format ) ) {
		$format = get_option( 'date_format' );
	}

	if ( $date ) {
		$date = mysql2date( $format, $date );
	}

	return apply_filters( 'incassoos_get_activity_created', $date, $post, $format );
}

/**
 * Output the Activity's date
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  string      $format Optional. Timestamp's date format to return. Defaults to the `date_format` option.
 */
function incassoos_the_activity_date( $post = 0, $format = '' ) {
	echo incassoos_get_activity_date( $post, $format );
}

/**
 * Return the Activity's date
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_activity_date'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  string      $format Optional. Timestamp's date format to return. Defaults to the `date_format` option.
 * @return string Activity date.
 */
function incassoos_get_activity_date( $post = 0, $format = '' ) {
	$post = incassoos_get_activity( $post );
	$date = get_post_meta( $post ? $post->ID : 0, 'activity_date', true );

	// Default to the registered date format
	if ( empty( $format ) ) {
		$format = get_option( 'date_format' );
	}

	if ( $date ) {
		$date = mysql2date( $format, $date );
	} else {
		$date = '';
	}

	return apply_filters( 'incassoos_get_activity_date', $date, $post, $format );
}

/**
 * Return whether the Activity is registered for the same date as it was created
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_is_activity_same_date_created'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Are the Activity's dates the same?
 */
function incassoos_is_activity_same_date_created( $post = 0 ) {
	$post      = incassoos_get_activity( $post );
	$same_date = $post && ( incassoos_get_activity_created( $post ) === incassoos_get_activity_date( $post ) );

	return (bool) apply_filters( 'incassoos_is_activity_same_date_created', $same_date, $post );
}

/**
 * Return the Activity's list of participant ids
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_activity_participants'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return array Activity's list of participant identifiers.
 */
function incassoos_get_activity_participants( $post = 0 ) {
	$post  = incassoos_get_activity( $post );
	$users = get_post_meta( $post ? $post->ID : 0, 'participant', false );
	$users = $users ? array_unique( array_filter( $users ) ) : array();

	return (array) apply_filters( 'incassoos_get_activity_participants', $users, $post );
}

/**
 * Output the Activity's participant count
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_activity_participant_count( $post = 0 ) {
	echo incassoos_get_activity_participant_count( $post );
}

/**
 * Return the Activity's participant count
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_activity_participant_count'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return int Activity's participant count.
 */
function incassoos_get_activity_participant_count( $post = 0 ) {
	$post  = incassoos_get_activity( $post );
	$users = incassoos_get_activity_participants( $post );
	$count = count( array_unique( $users ) );

	return (int) apply_filters( 'incassoos_get_activity_participant_count', $count, $post );
}

/**
 * Return whether the participant is registered for the Activity
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_activity_has_participant'
 *
 * @param  int|WP_Post|string $participant Optional. Participant user object or ID. Defaults to the current user.
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Is the participant registered for the Activity?
 */
function incassoos_activity_has_participant( $participant = 0, $post = 0 ) {
	$post  = incassoos_get_activity( $post );
	$users = incassoos_get_activity_participants( $post );

	// Get the user id
	if ( is_a( $participant, 'WP_User' ) ) {
		$participant = $participant->ID;

	// Default to the current user
	} elseif ( ! $participant ) {
		$participant = get_current_user_id();
	}

	$retval = in_array( $participant, $users );

	return (bool) apply_filters( 'incassoos_activity_has_participant', $retval, $post, $participant );
}

/**
 * Return the Activity's participant prices
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_activity_participant_prices'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return array Activity's participant prices
 */
function incassoos_get_activity_participant_prices( $post = 0 ) {
	$post   = incassoos_get_activity( $post );
	$users  = incassoos_get_activity_participants( $post );
	$prices = array();

	if ( $users ) {
		$price      = incassoos_get_activity_price( $post );
		$raw_prices = incassoos_get_activity_prices_raw( $post );

		// Join specified raw prices with the default price for all other participants
		$prices = $raw_prices + array_fill_keys( array_diff( $users, array_keys( $raw_prices ) ), $price );
	}

	return (array) apply_filters( 'incassoos_get_activity_participant_prices', $prices, $post );
}

/**
 * Return the Activity's raw prices
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_activity_prices_raw'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return array Activity's raw prices
 */
function incassoos_get_activity_prices_raw( $post = 0 ) {
	$post   = incassoos_get_activity( $post );
	$prices = get_post_meta( $post ? $post->ID : 0, 'prices', true );

	// Prices are a serialized array
	if ( $prices ) {
		$prices = array_map( 'floatval', maybe_unserialize( $prices ) );
	} else {
		$prices = array();
	}

	return (array) apply_filters( 'incassoos_get_activity_prices_raw', $prices, $post );
}

/**
 * Output the Activity's participant price
 *
 * @since 1.0.0
 *
 * @param  int}WP_user|string $participant Participant user object or ID or participant type id.
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  bool|array|null $format Optional. Whether to apply currency format. Pass array as format args. Pass
 *                                  null to skip format parsing. Defaults to false.
 */
function incassoos_the_activity_participant_price( $participant, $post = 0, $format = false ) {
	echo incassoos_get_activity_participant_price( $participant, $post, $format );
}

/**
 * Return the Activity's participant price
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_activity_participant_price'
 *
 * @param  int}WP_user|string $participant Participant user object or ID or participant type id.
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  bool|array|null $format Optional. Whether to apply currency format. Pass array as format args. Pass
 *                                  null to skip format parsing. Defaults to false.
 * @return string|float Activity participant price.
 */
function incassoos_get_activity_participant_price( $participant, $post = 0, $format = false ) {
	$post  = incassoos_get_activity( $post );
	$total = 0;

	// Is Activity participant
	if ( incassoos_activity_has_participant( $participant, $post ) ) {
		$prices = incassoos_get_activity_participant_prices( $post );
		$total  = $prices[ $participant ];
	}

	$total = (float) apply_filters( 'incassoos_get_activity_participant_price', (float) $total, $post, $participant );

	// Apply currency format
	if ( null !== $format ) {
		$total = incassoos_parse_currency( $total, $format );
	}

	return $total;
}

/**
 * Return whether the participant has a custom Activity price
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_activity_participant_has_custom_price'
 *
 * @param  int}WP_user|string $participant Participant user object or ID or participant type id.
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Does the Activity participant have a custom price?
 */
function incassoos_activity_participant_has_custom_price( $participant, $post = 0 ) {
	$post   = incassoos_get_activity( $post );
	$retval = false;

	// Is Activity participant
	if ( incassoos_activity_has_participant( $participant, $post ) ) {
		$prices = incassoos_get_activity_prices_raw( $post );
		$retval = isset( $prices[ $participant ] ) && $prices[ $participant ] !== incassoos_get_activity_price( $post );
	}

	return (bool) apply_filters( 'incassoos_activity_participant_has_custom_price', $retval, $post, $participant );
}

/**
 * Output the Activity's total price
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  bool|array|null $format Optional. Whether to apply currency format. Pass array as format args. Pass
 *                                  null to skip format parsing. Defaults to false.
 */
function incassoos_the_activity_total( $post = 0, $format = false ) {
	echo incassoos_get_activity_total( $post, $format );
}

/**
 * Return the Activity's total price
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_activity_total'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  bool|array|null $format Optional. Whether to apply currency format. Pass array as format args. Pass
 *                                  null to skip format parsing. Defaults to false.
 * @return string|float Activity total price.
 */
function incassoos_get_activity_total( $post = 0, $format = false ) {
	$post  = incassoos_get_activity( $post );
	$total = get_post_meta( $post ? $post->ID : 0, 'total', true );

	// Get total from raw calculation
	if ( false === $total && $post ) {
		$total = incassoos_get_activity_total_raw( $post );
		update_post_meta( $post->ID, 'total', $total );
	}

	$total = (float) apply_filters( 'incassoos_get_activity_total', (float) $total, $post );

	// Apply currency format
	if ( null !== $format ) {
		$total = incassoos_parse_currency( $total, $format );
	}

	return $total;
}

/**
 * Return the Activity's raw total price
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_activity_total_raw'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return float Activity raw total price.
 */
function incassoos_get_activity_total_raw( $post = 0 ) {
	$post   = incassoos_get_activity( $post );
	$prices = incassoos_get_activity_participant_prices( $post );
	$total  = $prices ? array_sum( array_map( 'floatval', $prices ) ) : 0;

	return (float) apply_filters( 'incassoos_get_activity_total_raw', $total, $post );
}

/**
 * Return whether the Activity is collected
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_is_activity_collected'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Activity is collected
 */
function incassoos_is_activity_collected( $post = 0 ) {
	$post      = incassoos_get_activity( $post );
	$collected = $post && ( incassoos_get_collected_status_id() === $post->post_status );

	return (bool) apply_filters( 'incassoos_is_activity_collected', $collected, $post );
}

/**
 * Return whether the Activty is collectable
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_is_activity_collectable'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Activty is collectable
 */
function incassoos_is_activity_collectable( $post = 0 ) {
	$post        = incassoos_get_activity( $post );
	$collectable = $post && ! incassoos_is_activity_collected( $post );

	return (bool) apply_filters( 'incassoos_is_activity_collectable', $collectable, $post );	
}

/**
 * Query and return the Activities
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_activities'
 *
 * @param  array $args Optional. Additional query arguments for {@see WP_Query}.
 * @return array Activities
 */
function incassoos_get_activities( $args = array() ) {

	// Parse query arguments
	$args = wp_parse_args( $args, array(
		'fields'         => 'ids',
		'post_type'      => incassoos_get_activity_post_type(),
		'posts_per_page' => -1
	) );

	$query = new WP_Query( $args );
	$posts = $query->posts;

	// Default to empty array
	if ( ! $posts ) {
		$posts = array();
	}

	return apply_filters( 'incassoos_get_activities', $posts, $args );
}

/**
 * Return the uncollected Activities
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_uncollected_activities'
 *
 * @param  array $args Optional. Additional query arguments for {@see WP_Query}.
 * @return array Uncollected Activities
 */
function incassoos_get_uncollected_activities( $args = array() ) {

	// Define query arguments
	$args['incassoos_collected'] = false;

	// Query posts
	$posts = incassoos_get_activities( $args );

	return apply_filters( 'incassoos_get_uncollected_activities', $posts, $args );
}

/** Filters *******************************************************************/

/**
 * Modify the Activity's post class
 *
 * @since 1.0.0
 *
 * @param  array       $classes Post class names
 * @param  string      $class   Added class names
 * @param  int}WP_Post $post_id Post ID
 * @return array       Post class names
 */
function incassoos_filter_activity_class( $classes, $class, $post_id ) {
	$post = incassoos_get_activity( $post_id );

	// When this is an Activity
	if ( $post ) {

		// Activity is collected
		if ( incassoos_is_activity_collected( $post ) ) {
			$classes[] = 'activity-collected';
		}
	}

	return $classes;
}

/**
 * Modify the Activity's date
 *
 * Defaults the activity date to its created date.
 *
 * @since 1.0.0
 *
 * @param  string      $date   Activity date
 * @param  int}WP_Post $post   Optional. Post object or ID. Defaults to the current post.
 * @param  string      $format Date format
 * @return string              Activity date
 */
function incassoos_filter_activity_date( $date, $post = 0, $format = false ) {

	// Default the activity date to the created date
	if ( ! $date ) {
		$date = incassoos_get_activity_created( $post, $format ) . '*';
	}

	return $date;
}

/** Collection ****************************************************************/

/**
 * Return the Activity's Collection post
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return WP_Post|false Collection post object or False when not found.
 */
function incassoos_get_activity_collection( $post = 0 ) {
	$post       = incassoos_get_activity( $post );
	$collection = incassoos_get_activity_collection_id( $post );

	return incassoos_get_collection( $collection );
}

/**
 * Output the Activity's Collection ID
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_activity_collection_id( $post = 0 ) {
	echo incassoos_get_activity_collection_id( $post );
}

/**
 * Return the Activity's Collection ID
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_activity_collection_id'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return int Activity's Collection ID
 */
function incassoos_get_activity_collection_id( $post = 0 ) {
	$post    = incassoos_get_activity( $post );
	$post_id = $post ? $post->post_parent : 0;

	return (int) apply_filters( 'incassoos_get_activity_collection_id', $post_id, $post );
}

/**
 * Output the Activity's Collection title
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_activity_collection_title( $post = 0 ) {
	echo incassoos_get_activity_collection_title( $post );
}

/**
 * Return the Activity's Collection title
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Activity's Collection title
 */
function incassoos_get_activity_collection_title( $post = 0 ) {
	$collection = incassoos_get_activity_collection( $post );
	$title      = $collection ? incassoos_get_collection_title( $collection ) : '';

	return $title;
}

/**
 * Output the Activity's Collection collected date
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  string      $format Optional. Timestamp's date format to return. Defaults to the `date_format` option.
 */
function incassoos_the_activity_collection_date( $post = 0, $format = '' ) {
	echo incassoos_get_activity_collection_date( $post, $format );
}

/**
 * Return the Activity's Collection collected date
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  string      $format Optional. Timestamp's date format to return. Defaults to the `date_format` option.
 * @return string Activity's Collection collected date
 */
function incassoos_get_activity_collection_date( $post = 0, $format = '' ) {
	$collection = incassoos_get_activity_collection( $post );
	$date       = $collection ? incassoos_get_collection_date( $collection, $format ) : '';

	return $date;
}

/**
 * Output the Activity's Collection url
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_activity_collection_url( $post = 0 ) {
	echo incassoos_get_activity_collection_url( $post );
}

/**
 * Return the Activity's Collection url
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Activity's Collection url
 */
function incassoos_get_activity_collection_url( $post = 0 ) {
	$collection = incassoos_get_activity_collection( $post );
	$url        = $collection ? incassoos_get_collection_url( $collection ) : '';

	return $url;
}

/**
 * Output the Activity's Collection link
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_activity_collection_link( $post = 0 ) {
	echo incassoos_get_activity_collection_link( $post );
}

/**
 * Return the Activity's Collection link
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Activity's Collection link
 */
function incassoos_get_activity_collection_link( $post = 0 ) {
	$collection = incassoos_get_activity_collection( $post );
	$link       = $collection ? incassoos_get_collection_link( $collection ) : '';

	return $link;
}

/**
 * Output the Activity's Collection hint
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_activity_collection_hint( $post = 0 ) {
	echo incassoos_get_activity_collection_hint( $post );
}

/**
 * Return the Activity's Collection hint
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Activity's Collection hint
 */
function incassoos_get_activity_collection_hint( $post = 0 ) {
	$collection = incassoos_get_activity_collection( $post );
	$hint       = incassoos_get_collection_hint( $collection ); // Provides default value

	return $hint;
}

/** Update ********************************************************************/

/**
 * Update the Activity's date
 *
 * @since 1.0.0
 *
 * @param  string      $date Date string
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Update success.
 */
function incassoos_update_activity_date( $date, $post = 0 ) {
	$post    = incassoos_get_activity( $post );
	$success = false;

	if ( $post ) {

		// Parse input date
		$date = strtotime( trim( $date ) );

		// Save mysql date string
		if ( $date ) {
			$date = date( 'Y-m-d 00:00:00', $date );
			$success = update_post_meta( $post->ID, 'activity_date', $date );
		}
	}

	return $success;
}

/**
 * Return whether the provided data is valid for an Activity
 *
 * @since 1.0.0
 *
 * @param  array $args Activity attributes to update
 * @return WP_Error|bool Error object on invalidation, true when validated
 */
function incassoos_validate_activity( $args = array() ) {
	$update = isset( $args['ID'] ) && ! empty( $args['ID'] );

	// Parse defaults
	$args = wp_parse_args( $args, array(
		'post_title'    => '',
		'activity_date' => $update ? incassoos_get_activity_date( $args['ID'], 'd-m-Y' ) : '',
		'price'         => $update ? incassoos_get_activity_price( $args['ID'], null ) : 0
	) );

	// Validate title
	$title = incassoos_validate_title( $args['post_title'] );
	if ( is_wp_error( $title ) ) {
		return $title;
	}

	// Validate date. May be empty
	$date = incassoos_validate_date( $args['activity_date'], 'd-m-Y' );
	if ( ! empty( $args['activity_date'] ) && is_wp_error( $date ) ) {
		return $date;
	}

	// Validate price
	$price = incassoos_validate_price( $args['price'] );
	if ( is_wp_error( $price ) ) {
		return $price;
	}

	return true;
}
