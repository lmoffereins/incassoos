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
 * Display or return user match dropdown element
 *
 * Don't forget to load the appropriate javascript to enable the user matching.
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_dropdown_user_matches'
 *
 * @param  array $args Dropdown arguments
 * @return string HTML dropdown list of user matches
 */
function incassoos_dropdown_user_matches( $args = array() ) {

	// Parse default args
	$parsed_args = wp_parse_args( $args, array(
		'echo'              => 1,
		'id'                => 'user-quick-select',
		'class'             => 'quick-select',
		'tab_index'         => 0,
		'option_none_value' => __( '&mdash; Quick select &mdash;', 'incassoos' )
	));

	// Get match options
	$matches     = incassoos_get_user_match_options();
	$selectors   = array_filter( $matches, function( $g ) { return ( false === strpos( $g, '_' ) ); }, ARRAY_FILTER_USE_KEY ); // PHP 5.6+
	$deselectors = array_diff_key( $matches, $selectors );

	$class = esc_attr( $parsed_args['class'] );
	$id    = esc_attr( $parsed_args['id'] );

	$tab_index = $parsed_args['tab_index'];
	$tab_index_attribute = '';
	if ( (int) $tab_index > 0 ) {
		$tab_index_attribute = " tabindex=\"$tab_index\"";
	}

	$output  = "<select id='$id' class='$class' $tab_index_attribute>\n";
	$output .= '<option value="-1">' . esc_html( $parsed_args['option_none_value'] ) . '</option>';

	if ( $selectors && $deselectors ) {

		// Selectors
		$output .= '<optgroup label="' . esc_attr__( 'Selecting', 'incassoos' ) . '">';
		foreach ( $selectors as $match_id => $label ) {
			$output .= '<option value="' . esc_attr( $match_id ) . '">' . esc_html( $label ) . '</option>';
		}
		$output .= '</optgroup>';

		// Deselectors
		$output .= '<optgroup label="'. esc_attr__( 'Deselecting', 'incassoos' ) . '">';
		foreach ( $deselectors as $match_id => $label ) {
			$output .= '<option value="' . esc_attr( $match_id ) . '">' . esc_html( $label ) . '</option>';
		}
		$output .= '</optgroup>';

	} else {
		foreach ( $matches as $match_id => $label ) {
			$output .= '<option value="' . esc_attr( $match_id ) . '">' . esc_html( $label ) . '</option>';
		}
	}

	$output .= '</select>';
	$output = apply_filters( 'incassoos_dropdown_user_matches', $output, $parsed_args );

	if ( $parsed_args['echo'] ) {
		echo $output;
	}

	return $output;
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

/**
 * Return whether the user can view the post (type)
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_user_can_view_post'
 *
 * @param WP_Post|int|string $post Post object or ID or post type to check for.
 * @param int                $user Optional. User object or ID. Defaults to the current user ID.
 * @return bool User can view the post (type)
 */
function incassoos_user_can_view_post( $post, $user = 0 ) {
	$user   = incassoos_get_user( $user );
	$retval = false;

	// Post type
	if ( $user && is_string( $post ) && post_type_exists( $post ) ) {
		$post_type_object = get_post_type_object( $post );
		$cap              = property_exists( $post_type_object->cap, 'view_posts' );
		$retval           = user_can( $user->ID, $cap ? $post_type_object->cap->view_posts : $post_type_object->cap->edit_posts );

	// Single post
	} elseif ( $user ) {
		$post = get_post( $post );
		if ( $post ) {
			$post_type_object = get_post_type_object( $post->post_type );
			$cap              = property_exists( $post_type_object->cap, 'view_post' );
			$retval           = user_can( $user->ID, $cap ? $post_type_object->cap->view_post : $post_type_object->cap->edit_post, $post->ID );
		}
	}

	return apply_filters( 'incassoos_user_can_view_post', $retval, $post, $user );
}

/**
 * Return whether the user can create posts of the post type
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_user_can_create_post'
 *
 * @param string $post_type Post type to check for.
 * @param int    $user      Optional. User object or ID. Defaults to the current user ID.
 * @return bool User can create posts of the post type
 */
function incassoos_user_can_create_post( $post_type, $user = 0 ) {
	$user   = incassoos_get_user( $user );
	$retval = false;

	// Post type
	if ( $user && post_type_exists( $post_type ) ) {
		$post_type_object = get_post_type_object( $post_type );
		$retval           = user_can( $user->ID, $post_type_object->cap->edit_posts );
	}

	return apply_filters( 'incassoos_user_can_create_post', $retval, $post_type, $user );
}

/**
 * Return whether the user can edit the post (type)
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_user_can_edit_post'
 *
 * @param WP_Post|int|string $post Post object or ID or post type to check for.
 * @param int                $user Optional. User object or ID. Defaults to the current user ID.
 * @return bool User can edit the post (type)
 */
function incassoos_user_can_edit_post( $post, $user = 0 ) {
	$user   = incassoos_get_user( $user );
	$retval = false;

	// Post type
	if ( $user && is_string( $post ) && post_type_exists( $post ) ) {
		$post_type_object = get_post_type_object( $post );
		$retval           = user_can( $user->ID, $post_type_object->cap->edit_posts );

	// Single post
	} elseif ( $user ) {
		$post = get_post( $post );
		if ( $post ) {
			$post_type_object = get_post_type_object( $post->post_type );
			$retval           = user_can( $user->ID, $post_type_object->cap->edit_post, $post->ID );
		}
	}

	return apply_filters( 'incassoos_user_can_edit_post', $retval, $post, $user );
}

/**
 * Return whether the user can delete the post (type)
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_user_can_delete_post'
 *
 * @param WP_Post|int|string $post Post object or ID or post type to check for.
 * @param int                $user Optional. User object or ID. Defaults to the current user ID.
 * @return bool User can delete the post (type)
 */
function incassoos_user_can_delete_post( $post, $user = 0 ) {
	$user   = incassoos_get_user( $user );
	$retval = false;

	// Post type
	if ( $user && is_string( $post ) && post_type_exists( $post ) ) {
		$post_type_object = get_post_type_object( $post );
		$retval           = user_can( $user->ID, $post_type_object->cap->delete_posts );

	// Single post
	} elseif ( $user ) {
		$post = get_post( $post );
		if ( $post ) {
			$post_type_object = get_post_type_object( $post->post_type );
			$retval           = user_can( $user->ID, $post_type_object->cap->delete_post, $post->ID );
		}
	}

	return apply_filters( 'incassoos_user_can_delete_post', $retval, $post, $user );
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
