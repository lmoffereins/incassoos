<?php

/**
 * Incassoos User Functions
 *
 * @package Incassoos
 * @subpackage Main
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Return the user object
 *
 * @since 1.0.0
 * 
 * @param  mixed $user_id User object or identifier. Defaults to the current user ID.
 * @param  string $by Optional. Property to get the user by, passed to {@see get_user_by()}. Defaults to 'id'.
 * @return WP_User|bool User object or false when not found.
 */
function incassoos_get_user( $user = false, $by = 'id' ) {

	// Default to the current user
	if ( ! $user ) {
		$user = get_current_user_id();
	}

	$user = is_a( $user, 'WP_User' ) ? $user : get_user_by( $by, $user );

	if ( ! $user || ! $user->exists() ) {
		$user = false;
	}

	return $user;
}

/**
 * Return user query object of users matching criteria
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_user_query_args'
 *
 * @param  array $args Optional. User query args for {@see get_users()}.
 * @return WP_User_Query User query
 */
function incassoos_get_user_query( $args = array() ) {

	// Enable filtering the user query arguments
	$args = apply_filters( 'incassoos_get_user_query_args', $args );
	$args = wp_parse_args( $args, array(
		'orderby' => 'display_name'
	) );

	return new WP_User_Query( $args );
}

/**
 * Return list of users matching criteria
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_users'
 *
 * @param  array $args Optional. User query args for {@see get_users()}.
 * @return array List of users.
 */
function incassoos_get_users( $args = array() ) {
	$args['count_total'] = false;

	$user_query = incassoos_get_user_query( $args );
	$users      = $user_query->get_results();

	return (array) apply_filters( 'incassoos_get_users', $users, $args );
}

/**
 * Return list of grouped users matching criteria
 *
 * @since 1.0.0
 *
 * @param  array $args Optional. User query args for {@see get_users()}.
 * @return array List of grouped users.
 */
function incassoos_get_grouped_users( $args = array() ) {
	$users   = incassoos_get_users( $args );
	$grouped = incassoos_group_users( $users );

	return $grouped;
}

/**
 * Group the given users by their list group
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_group_users'
 *
 * @param  array $users Users to group. List of user objects or IDs.
 * @return array List of grouped users.
 */
function incassoos_group_users( $users = array() ) {
	$grouped = array();

	foreach ( $users as $user ) {
		$group = (object) incassoos_get_user_list_group( $user );

		// Add group 
		if ( ! isset( $grouped[ $group->id ] ) ) {
			$group->users = array();
			$grouped[ $group->id ] = $group;
		}

		// Add user to group
		$grouped[ $group->id ]->users[] = $user;
	}

	// Sort groups
	$grouped = wp_list_sort( $grouped, 'order' );

	return apply_filters( 'incassoos_group_users', $grouped, $users );
}

/**
 * Return the user display name
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_user_display_name'
 *
 * @param  mixed $user_id User object or identifier. Defaults to the current user ID.
 * @param  string $by Optional. Property to get the user by, passed to {@see get_user_by()}. Defaults to 'id'.
 * @return string User display name
 */
function incassoos_get_user_display_name( $user = false, $by = 'id' ) {
	$user         = incassoos_get_user( $user, $by );
	$display_name = '';

	if ( $user ) {
		$display_name = $user->display_name;

		if ( ! $display_name ) {
			$display_name = $user->user_login;
		}
	}

	return apply_filters( 'incassoos_get_user_display_name', $display_name, $user );
}

/**
 * Return the user IBAN
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_user_iban'
 *
 * @param  mixed $user_id User object or identifier. Defaults to the current user ID.
 * @param  string $by Optional. Property to get the user by, passed to {@see get_user_by()}. Defaults to 'id'.
 * @return string User IBAN
 */
function incassoos_get_user_iban( $user = false, $by = 'id' ) {
	$user = incassoos_get_user( $user, $by );
	$iban = '';

	if ( $user ) {
		$iban = $user->get( '_incassoos_iban' );
	}

	return apply_filters( 'incassoos_get_user_iban', $iban, $user );
}

/**
 * Return the user consumption limit
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_user_consumption_limit'
 *
 * @param  mixed $user_id User object or property. Defaults to the current user ID.
 * @param  string $by Optional. Property to get the user by, passed to {@see get_user_by()}. Defaults to 'id'.
 * @return float User consumption limit.
 */
function incassoos_get_user_consumption_limit( $user = false, $by = 'id' ) {
	$user  = incassoos_get_user( $user, $by );
	$limit = 0;

	if ( $user ) {
		$limit = (float) $user->get( '_incassoos_consumption_limit' );
	}

	return (float) apply_filters( 'incassoos_get_user_consumption_limit', $limit, $user );
}

/**
 * Return the user's group used for sub-listing
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_user_list_group'
 *
 * @param  mixed $user_id User object or property. Defaults to the current user ID.
 * @param  string $by Optional. Property to get the user by, passed to {@see get_user_by()}. Defaults to 'id'.
 * @return array User's list group data.
 */
function incassoos_get_user_list_group( $user = false, $by = 'id' ) {
	$user  = incassoos_get_user( $user, $by );
	$group = array();

	// Default to grouping by first letter
	if ( $user ) {
		$letter = preg_replace( "/[^a-zA-Z0-9]+/", '', strtolower( incassoos_get_user_display_name( $user ) ) )[0];
		$range  = array_flip( range( 'a', 'z' ) );
		$group  = array(
			'id'    => $range[ $letter ],
			'name'  => strtoupper( $letter ),
			'order' => $range[ $letter ]
		);
	}

	// Enable filtering
	$group = apply_filters( 'incassoos_get_user_list_group', $group, $user );

	// Parse group
	if ( $group && ! is_array( $group ) ) {
		$group = array(
			'id'    => $group,
			'name'  => $group,
			'order' => $group
		);
	} else {
		$group = wp_parse_args( (array) $group, array(
			'id'    => 0,
			'name'  => esc_html_x( 'Others', 'No list group', 'incassoos' ),
			'order' => null
		) );
	}

	return $group;
}

/**
 * Return list of user match selection options
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_user_match_options'
 *
 * @return array List of user match options as key => label.
 */
function incassoos_get_user_match_options() {

	// Default options
	$options = array(
		'all'  => __( 'Select all',   'incassoos' ),
		'_all' => __( 'Deselect all', 'incassoos' )
	);

	/**
	 * Filter the user match options
	 *
	 * The following prefixes allow for advanced selections:
	 *  `!` Indicates an inverted selection on the matched users
	 *  `_` Indicates a deselect on the matched users
	 */
	return (array) apply_filters( 'incassoos_get_user_match_options', $options );
}

/**
 * Return the user's match ids
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_user_match_ids'
 *
 * @param  mixed $user_id User object or property. Defaults to the current user ID.
 * @param  string $by Optional. Property to get the user by, passed to {@see get_user_by()}. Defaults to 'id'.
 * @return array User's match ids
 */
function incassoos_get_user_match_ids( $user = false, $by = 'id' ) {
	$user = incassoos_get_user( $user, $by );
	$match_ids = array();

	if ( $user ) {

		// Get match ids from user meta
		$meta = $user->get( '_incassoos_match_ids' );

		if ( $meta ) {
			$match_ids = implode( ',', array_filter( $meta ) );
		}
	}

	return (array) apply_filters( 'incassoos_get_user_match_ids', $match_ids, $user );
}

/**
 * Output a comma seperated list of the user's match ids
 *
 * @since 1.0.0
 *
 * @param  mixed $user_id User object or property. Defaults to the current user ID.
 * @param  string $by Optional. Property to get the user by, passed to {@see get_user_by()}. Defaults to 'id'.
 */
function incassoos_the_user_match_ids_list( $user = false, $by = 'id' ) {
	echo incassoos_get_user_match_ids_list( $user, $by );
}

/**
 * Return a comma seperated list of the user's match ids
 *
 * @since 1.0.0
 *
 * @param  mixed $user_id User object or property. Defaults to the current user ID.
 * @param  string $by Optional. Property to get the user by, passed to {@see get_user_by()}. Defaults to 'id'.
 * @return array User's match ids
 */
function incassoos_get_user_match_ids_list( $user = false, $by = 'id' ) {
	return implode( ',', incassoos_get_user_match_ids( $user, $by ) );
}

/** Listings ******************************************************************/

function incassoos_get_top_spenders( $args = array() ) {
	$consumers = array();
	$args = wp_parse_args( $args, array(
		'post'      => false,   // Post object or ID, post type, or false for all
		'post_type' => false,   // Post type of activity, order, or product, or false for all
		'timing'    => array(), // 0: Since, 1: Up to. Empty values yield all time
		'limit'     => 10
	) );

	// Context is a post
	if ( is_a( $args['post'], 'WP_Post' ) || is_numeric( $args['post'] ) ) {
		$post = get_post( $args['post'] );
	} else {
		$post = false;
	}

	if ( $args['timing'][0] ) {
		// $args[];
	}

	switch ( $args['post'] ) {

		// Per Occasion
		case incassoos_get_occasion_post_type() :

			break;

		// Per collection
		case incassoos_get_collection_post_type() :

			break;

		// Overall
		default :

			break;
	}

	return apply_filters( 'incassoos_get_top_spenders', $consumers, $args );
}
