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
 * @param  mixed $user User object or identifier. Defaults to the current user ID.
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
 * Return the user ID
 *
 * @since 1.0.0
 *
 * @param  mixed $user User object or identifier. Defaults to the current user ID.
 * @param  string $by Optional. Property to get the user by, passed to {@see get_user_by()}. Defaults to 'id'.
 * @return int|bool User ID or false when not found.
 */
function incassoos_get_user_id( $user = false, $by = 'id' ) {
	$user = incassoos_get_user( $user, $by );

	if ( $user ) {
		return $user->ID;
	} else {
		return false;
	}
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

/** Query *****************************************************************/

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
 * Modify the user query before parsing the query vars
 *
 * @since 1.0.0
 *
 * @param WP_User_Query $users_query
 */
function incassoos_pre_get_users( $users_query ) {

	// Filter shortcut for shown/hidden consumers
	if ( null !== $users_query->get( 'incassoos_hidden_consumers' ) ) {
		$meta_query = (array) $users_query->get( 'meta_query' ) ?: array();
		$meta_query[] = array(
			'key'     => '_incassoos_hidden_consumer',
			'value'   => array( '1' ),
			'compare' => $users_query->get( 'incassoos_hidden_consumers' ) ? 'IN' : 'NOT IN'
		);
		$users_query->set( 'meta_query', $meta_query );
	}
}

/**
 * Modify the user query when querying users
 *
 * @since 1.0.0
 *
 * @global $wpdb WPDB
 *
 * @uses apply_filters() Calls 'incassoos_pre_user_query'
 *
 * @param WP_User_Query $users_query
 */
function incassoos_pre_user_query( $users_query ) {
	global $wpdb;

	// Plugin implementation of non-exclusive 'include'
	$include = $users_query->get( 'incassoos_include' );
	if ( ! empty( $include ) ) {
		$ids                      = implode( ',', wp_parse_id_list( $include ) );
		$where_sql                = substr( $users_query->query_where, 6 ); // Remove leading 'WHERE '
		$users_query->query_where = "WHERE $wpdb->users.ID IN ($ids) OR ($where_sql)";

		// Catch-all to ensure unique rows
		if ( ! empty( $users_query->meta_query->queries ) && 0 !== strpos( $users_query->query_fields, 'DISTINCT ' ) ) {
			$users_query->query_fields = 'DISTINCT ' . $users_query->query_fields;
		}
	}
}

/** Template **************************************************************/

/**
 * Return the user's username
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_user_username'
 *
 * @param  mixed $user User object or identifier. Defaults to the current user ID.
 * @param  string $by Optional. Property to get the user by, passed to {@see get_user_by()}. Defaults to 'id'.
 * @return string User username
 */
function incassoos_get_user_username( $user = false, $by = 'id' ) {
	$user     = incassoos_get_user( $user, $by );
	$username = '';

	if ( $user ) {
		$username = $user->user_login;
	}

	return apply_filters( 'incassoos_get_user_username', $username, $user );
}

/**
 * Return the user's display name
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_user_display_name'
 *
 * @param  mixed $user User object or identifier. Defaults to the current user ID.
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
 * @param  mixed $user User object or identifier. Defaults to the current user ID.
 * @param  string $by Optional. Property to get the user by, passed to {@see get_user_by()}. Defaults to 'id'.
 * @return string User IBAN
 */
function incassoos_get_user_iban( $user = false, $by = 'id' ) {
	$user = incassoos_get_user( $user, $by );
	$iban = '';

	// Get IBAN. Value may be redacted when encryption is enabled
	if ( $user ) {
		$iban = $user->get( '_incassoos_iban' );
	}

	return apply_filters( 'incassoos_get_user_iban', $iban, $user );
}

/**
 * Return the user spending limit
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_user_spending_limit'
 *
 * @param  mixed $user_id User object or property. Defaults to the current user ID.
 * @param  string $by Optional. Property to get the user by, passed to {@see get_user_by()}. Defaults to 'id'.
 * @return float User spending limit.
 */
function incassoos_get_user_spending_limit( $user = false, $by = 'id' ) {
	$user  = incassoos_get_user( $user, $by );
	$limit = 0;

	if ( $user ) {
		$limit = (float) $user->get( '_incassoos_spending_limit' );
	}

	return (float) apply_filters( 'incassoos_get_user_spending_limit', $limit, $user );
}

/**
 * Return whether the user is a hidden consumer
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_is_consumer_hidden'
 *
 * @param  mixed $user_id User object or property. Defaults to the current user ID.
 * @param  string $by Optional. Property to get the user by, passed to {@see get_user_by()}. Defaults to 'id'.
 * @return bool Hide user by default.
 */
function incassoos_is_consumer_hidden( $user = false, $by = 'id' ) {
	$user = incassoos_get_user( $user, $by );
	$hide = false;

	if ( $user ) {
		$hide = (bool) $user->get( '_incassoos_hidden_consumer', false );
	}

	return (bool) apply_filters( 'incassoos_is_consumer_hidden', $hide, $user );
}

/** Lists *****************************************************************/

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

/** Caps ******************************************************************/

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

/** Security ******************************************************************/

/**
 * Return the list of encryptable usermeta
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_encryptable_usermeta'
 *
 * @param string $meta_key Optional. Provide meta key when requesting details
 *                         of a single encryptable usermeta.
 * @return array|bool All or single encryptable usermeta or False when not found
 */
function incassoos_get_encryptable_usermeta( $meta_key = '' ) {

	// Try to return items from cache
	if ( ! empty( incassoos()->encryption->usermeta ) ) {
		$encryptable = incassoos()->encryption->usermeta;
	} else {
		/**
		 * Filter the list of encryptable usermeta
		 *
		 * Usermeta can be added to the list in one of the following ways:
		 * - Just the meta key. The suffix '_encrypted' will be added to the meta key for the
		 *   equivalent encrypted usermeta.
		 * - The meta key as array key with the name for the equivalent encrypted usermeta as array value.
		 * - The meta key as array key with their encryption parameters in an array {
		 *    string $meta_key_encrypted   Optional. Name for the encrypted usermeta. Defaults to the
		 *                                 plain meta key with the suffix '_encrypted'.
		 *    string $redact_callback      Optional. Callback name for redacting the original value.
		 *                                 Defaults to `incassoos_redact_text`.
		 *    array  $redact_callback_args Optional. Additional arguments for the redaction callback.
		 *                                 Defaults to an empty array.
		 *    string $is_redacted_callback Optional. Callback name for checking whether the value is
		 *                                 redacted. Defaults to `incassoos_is_value_redacted`.
		 *   }
		 *
		 * @since 1.0.0
		 *
		 * @param array $usermeta Encryptable usermeta
		 */
		$usermeta = (array) apply_filters( 'incassoos_get_encryptable_usermeta', array(

			// The IBAN of the user
			'_incassoos_iban'             => array(
				'meta_key_encrypted'   => '_incassoos_encrypted_iban',
				'redact_callback'      => 'incassoos_redact_iban',
				'is_redacted_callback' => 'incassoos_is_iban_redacted'
			)
		) );

		$encryptable = array();

		// Parse defaults
		foreach ( $usermeta as $mkey => $args ) {

			// Provided just the plain meta key
			if ( is_numeric( $mkey ) && is_string( $args ) ) {
				$mkey = $args;
				$args     = array();
			} elseif ( is_string( $args ) ) {
				$args = array( 'meta_key_encrypted' => $args );
			}

			$encryptable[ $mkey ] = wp_parse_args( $args, array(
				'meta_key_encrypted'   => "{$mkey}_encrypted",
				'redact_callback'      => 'incassoos_redact_text',
				'redact_callback_args' => array(),
				'is_redacted_callback' => 'incassoos_is_value_redacted'
			) );
		}

		// Set items in cache
		incassoos()->encryption->usermeta = $encryptable;
	}

	// Single usermeta requested
	if ( $meta_key ) {

		// Define retval
		$retval = false;

		// Find the usermeta
		if ( isset( $encryptable[ $meta_key ] ) ) {
			$retval = $encryptable[ $meta_key ];
		}

		return $retval;
	} else {
		return $encryptable;
	}
}

/**
 * Register actions and filters for encryptable usermeta
 *
 * @since 1.0.0
 */
function incassoos_register_encryptable_usermeta() {

	// Load encryptable usermeta into cache
	incassoos_get_encryptable_usermeta();

	// Register encryption action for `add_user_meta()`
	add_action( 'added_user_meta', 'incassoos_encryption_for_add_user_meta', 10, 4 );

	// Register encryption filter for `get_user_meta()`
	add_action( 'get_user_metadata', 'incassoos_encryption_for_get_user_meta', 10, 4 );

	/*
	 * Register encryption filter for `update_user_meta()`
	 *
	 * User metadata cannot be modified directly before update - other than
	 * through `sanitize_meta()` which is also used outside of `update_metadata()`.
	 *
	 * This filter will effectively short-circuit the usermeta update for
	 * encryptable usermeta, while updating the redacted value and storing the
	 * equivalent encrypted meta value.
	 */
	add_filter( 'update_user_metadata', 'incassoos_encryption_for_update_user_meta', 10, 5 );

	// Register encryption action for `delete_user_meta()`
	add_action( 'deleted_user_meta', 'incassoos_encryption_for_delete_user_meta', 10, 4 );

	// Register actions for enabling/disabling encryption
	add_action( 'incassoos_enable_encryption',  'incassoos_encrypt_encryptable_usermeta', 10 );
	add_action( 'incassoos_disable_encryption', 'incassoos_decrypt_encryptable_usermeta', 10 );
}

/**
 * Apply encryption when a user's metadata is added
 *
 * @since 1.0.0
 *
 * @param int    $mid      Meta ID
 * @param int    $user_id  User ID
 * @param string $meta_key Meta key
 * @param mixed  $value    Meta value
 */
function incassoos_encryption_for_add_user_meta( $mid, $user_id, $meta_key, $value ) {

	// Bail when encryption is not enabled
	if ( ! incassoos_is_encryption_enabled() ) {
		return;
	}

	// Get encryptable usermeta
	$args = incassoos_get_encryptable_usermeta( $meta_key );

	// Re-update the usermeta which will trigger the encryption
	if ( $args ) {
		update_user_meta( $user_id, $meta_key, $value );
	}
}

/**
 * Apply encryption when a user's metadata is added
 *
 * @since 1.0.0
 *
 * @param mixed  $value    Meta value
 * @param int    $user_id  User ID
 * @param string $meta_key Meta key
 * @param bool   $single   Whether to return only the first value of the metadata.
 * @return mixed Meta value
 */
function incassoos_encryption_for_get_user_meta( $value, $user_id, $meta_key, $single ) {

	// Bail when encryption is not enabled
	if ( ! incassoos_is_encryption_enabled() ) {
		return $value;
	}

	// Get encryptable usermeta
	$args = incassoos_get_encryptable_usermeta( $meta_key );

	// Bail when usermeta is not encryptable
	if ( ! $args ) {
		return $value;
	}

	// Bail when user cannot decrypt data
	if ( ! current_user_can( 'decrypt_incassoos_data' ) ) {
		return $value;
	}

	// Look for decryption key
	$decryption_key = incassoos_get_decryption_key();

	// Bail when no decryption key is available
	if ( ! $decryption_key ) {
		return $value;
	}

	// Get encrypted usermeta value
	$encrypted_value = get_user_meta( $user_id, $args['meta_key_encrypted'], $single );

	// Decrypt usermeta value
	if ( $encrypted_value ) {

		// Support multi-value metadata
		$decrypted_value = array();
		foreach ( (array) $encrypted_value as $item ) {
			$decrypted_item = incassoos_decrypt_value( $item, $decryption_key );

			// Bail when an error occurred
			if ( is_wp_error( $decrypted_item ) ) {
				wp_die( $decrypted_item );
			}

			$decrypted_value[] = $decrypted_item;
		}

		// Set return value
		$value = $single ? $decrypted_value[0] : $decrypted_value;
	}

	return $value;
}

/**
 * Apply encryption when a user's metadata is updated
 *
 * @since 1.0.0
 *
 * @param  mixed  $check      Whether to continue updating the metadata value
 * @param  int    $user_id    User ID
 * @param  string $meta_key   Meta key
 * @param  mixed  $value      Meta value
 * @param  mixed  $prev_value Previous meta value
 * @return null|bool Whether to continue updating the metadata value
 */
function incassoos_encryption_for_update_user_meta( $check, $user_id, $meta_key, $value, $prev_value ) {

	// Bail when encryption is not enabled
	if ( ! incassoos_is_encryption_enabled() ) {
		return $check;
	}

	// Get encryptable usermeta
	$args = incassoos_get_encryptable_usermeta( $meta_key );

	// Bail when usermeta is not encryptable
	if ( ! $args ) {
		return $check;
	}

	// Ignore redacted values to prevent encryption of already encrypted data
	if ( call_user_func_array( $args['is_redacted_callback'], array( $value, $args['redact_callback_args'] ) ) ) {
		return $check;
	}

	// Encrypt the value
	$encrypted_value = incassoos_encrypt_value( $value );

	// Bail when encryption failed
	if ( is_wp_error( $encrypted_value ) ) {
		wp_die( $encrypted_value );
	}

	// Store encrypted usermeta
	update_user_meta( $user_id, $args['meta_key_encrypted'], $encrypted_value );

	// Set redacted value for saving
	$redacted_value = call_user_func_array( $args['redact_callback'], array( $value, $args['redact_callback_args'] ) );

	// Store redacted usermeta
	update_user_meta( $user_id, $meta_key, $redacted_value );

	// Return success
	return true;
}

/**
 * Remove associated encryption when a user's metadata is deleted
 *
 * @since 1.0.0
 *
 * @param array  $mids     Deleted meta IDs
 * @param int    $user_id  User ID
 * @param string $meta_key Meta key
 * @param string $value    Meta Value
 */
function incassoos_encryption_for_delete_user_meta( $mids, $user_id, $meta_key, $value ) {

	// Bail when encryption is not enabled
	if ( ! incassoos_is_encryption_enabled() ) {
		return;
	}

	// Get encryptable usermeta
	$args = incassoos_get_encryptable_usermeta( $meta_key );

	// Delete associated encrypted usermeta
	if ( $args ) {
		delete_user_meta( $user_id, $args['meta_key_encrypted'] );
	}
}

/**
 * Apply encryption to the encryptable usermeta in bulk
 *
 * @since 1.0.0
 *
 * @global WPDB $wpdb
 */
function incassoos_encrypt_encryptable_usermeta() {
	global $wpdb;

	// Walk encryptable usermeta
	foreach ( incassoos_get_encryptable_usermeta() as $meta_key => $args ) {

		// Get affected users
		$user_ids = $wpdb->get_col( $wpdb->prepare( "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s", $meta_key ) );

		// Walk users
		foreach ( $user_ids as $user_id ) {

			// Get plain meta value
			$plain_value = get_user_meta( $user_id, $meta_key, true );
			if ( $plain_value ) {

				// Re-update the usermeta which will trigger the encryption
				update_user_meta( $user_id, $meta_key, $plain_value );
			}
		}
	}
}

/**
 * Decrypt the encrypted encryptable usermeta in bulk
 *
 * @since 1.0.0
 *
 * @global WPDB $wpdb
 *
 * @param string $decryption_key The decryption key
 */
function incassoos_decrypt_encryptable_usermeta( $decryption_key ) {
	global $wpdb;

	// Walk encryptable usermeta
	foreach ( incassoos_get_encryptable_usermeta() as $meta_key => $args ) {

		// Get affected users
		$user_ids = $wpdb->get_col( $wpdb->prepare( "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s", $meta_key ) );

		// Walk users
		foreach ( $user_ids as $user_id ) {

			// Get encrypted usermeta value
			$encrypted_value = get_user_meta( $user_id, $args['meta_key_encrypted'], true );
			if ( $encrypted_value ) {

				// Decrypt meta value
				$decrypted_value = incassoos_decrypt_value( $encrypted_value, $decryption_key );

				// Bail when an error occurred
				if ( is_wp_error( $decrypted_value ) ) {
					wp_die( $decrypted_value );
				}

				// Overwrite redacted usermeta with the decrypted value
				update_user_meta( $user_id, $meta_key, $decrypted_value );

				// Remove encrypted usermeta
				delete_user_meta( $user_id, $args['meta_key_encrypted'] );
			}
		}
	}
}

/** Update ********************************************************************/

/**
 * Set the consumer as hidden
 *
 * @since 1.0.0
 *
 * @param  int|WP_User $user Optional. User ID or object. Defaults to the current user.
 * @return bool Update success
 */
function incassoos_set_consumer_hidden( $user = 0 ) {
	$user    = incassoos_get_user( $user );
	$success = false;

	if ( $user ) {
		$success = update_user_meta( $user->ID, '_incassoos_hidden_consumer', 1 );
	}

	return $success;
}

/**
 * Set the consumer as shown
 *
 * Effectively removes the '_incassoos_hidden_consumer' user attribute.
 *
 * @since 1.0.0
 *
 * @param  int|WP_User $user Optional. User ID or object. Defaults to the current user.
 * @return bool Update success
 */
function incassoos_set_consumer_shown( $user = 0 ) {
	$user    = incassoos_get_user( $user );
	$success = false;

	if ( $user ) {
		$success = delete_user_meta( $user->ID, '_incassoos_hidden_consumer' );
	}

	return $success;
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
