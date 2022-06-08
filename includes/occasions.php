<?php

/**
 * Incassoos Occasion Functions
 *
 * @package Incassoos
 * @subpackage Main
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Post type *****************************************************************/

/**
 * Return the Occasion post type
 *
 * @since 1.0.0
 *
 * @return string Post type name
 */
function incassoos_get_occasion_post_type() {
	return incassoos()->occasion_post_type;
}

/**
 * Return the labels for the Occasion post type
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_occasion_post_type_labels'
 * @return array Occasion post type labels
 */
function incassoos_get_occasion_post_type_labels() {
	return apply_filters( 'incassoos_get_occasion_post_type_labels', array(
		'name'                  => __( 'Incassoos Order Occasions', 'incassoos' ),
		'menu_name'             => __( 'Occasions',                       'incassoos' ),
		'singular_name'         => __( 'Occasion',                        'incassoos' ),
		'all_items'             => __( 'All Occasions',                   'incassoos' ),
		'add_new'               => __( 'New Occasion',                    'incassoos' ),
		'add_new_item'          => __( 'Create New Occasion',             'incassoos' ),
		'edit'                  => __( 'Edit',                            'incassoos' ),
		'edit_item'             => __( 'Edit Occasion',                   'incassoos' ),
		'new_item'              => __( 'New Occasion',                    'incassoos' ),
		'view'                  => __( 'View Occasion',                   'incassoos' ),
		'view_item'             => __( 'View Occasion',                   'incassoos' ),
		'view_items'            => __( 'View Occasions',                  'incassoos' ), // Since WP 4.7
		'search_items'          => __( 'Search Occasions',                'incassoos' ),
		'not_found'             => __( 'No occasions found',              'incassoos' ),
		'not_found_in_trash'    => __( 'No occasions found in Trash',     'incassoos' ),
		'insert_into_item'      => __( 'Insert into occasion',            'incassoos' ),
		'uploaded_to_this_item' => __( 'Uploaded to this occasion',       'incassoos' ),
		'filter_items_list'     => __( 'Filter occasions list',           'incassoos' ),
		'items_list_navigation' => __( 'Occasions list navigation',       'incassoos' ),
		'items_list'            => __( 'Occasions list',                  'incassoos' ),
	) );
}

/**
 * Return an array of features the Occasion post type supports
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_occasion_post_type_supports'
 * @return array Occasion post type support
 */
function incassoos_get_occasion_post_type_supports() {
	return apply_filters( 'incassoos_get_occasion_post_type_supports', array(
		'title',
		'incassoos-notes'
	) );
}

/** Taxonomy: Occasion Type ****************************************************/

/**
 * Return the Occasion Type taxonomy
 *
 * @since 1.0.0
 *
 * @return string Taxonomy name
 */
function incassoos_get_occasion_type_tax_id() {
	return incassoos()->occasion_type_tax_id;
}

/**
 * Return the labels for the Occasion Type taxonomy
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_occasion_type_tax_labels'
 * @return array Occasion Type taxonomy labels
 */
function incassoos_get_occasion_type_tax_labels() {
	return apply_filters( 'incassoos_get_occasion_type_tax_labels', array(
		'name'                       => __( 'Incassoos Occasion Types',        'incassoos' ),
		'menu_name'                  => __( 'Types',                           'incassoos' ),
		'singular_name'              => __( 'Occasion Type',                   'incassoos' ),
		'search_items'               => __( 'Search Occasion Types',           'incassoos' ),
		'popular_items'              => null, // Disable tagcloud
		'all_items'                  => __( 'All Occasion Types',              'incassoos' ),
		'no_items'                   => __( 'No Occasion Type',                'incassoos' ),
		'edit_item'                  => __( 'Edit Occasion Type',              'incassoos' ),
		'update_item'                => __( 'Update Occasion Type',            'incassoos' ),
		'add_new_item'               => __( 'Add New Occasion Type',           'incassoos' ),
		'new_item_name'              => __( 'New Occasion Type Name',          'incassoos' ),
		'view_item'                  => __( 'View Occasion Type',              'incassoos' ),

		'separate_items_with_commas' => __( 'Separate types with commas',      'incassoos' ),
		'add_or_remove_items'        => __( 'Add or remove types',             'incassoos' ),
		'choose_from_most_used'      => __( 'Choose from the most used types', 'incassoos' ),
		'not_found'                  => __( 'No types found.',                 'incassoos' ),
		'no_terms'                   => __( 'No types',                        'incassoos' ),
		'items_list_navigation'      => __( 'Occasion types list navigation',  'incassoos' ),
		'items_list'                 => __( 'Occasion types list',             'incassoos' ),
		'back_to_items'              => __( '&larr; Go to Occasion Types',     'incassoos' ),
	) );
}

/**
 * Act when the Occasion Type taxonomy has been registered
 *
 * @since 1.0.0
 */
function incassoos_registered_occasion_type_taxonomy() {
	$taxonomy = incassoos_get_occasion_type_tax_id();

	// Admin
	add_action( "{$taxonomy}_add_form_fields",  'incassoos_admin_taxonomy_add_form_fields',  10    );
	add_action( "{$taxonomy}_edit_form_fields", 'incassoos_admin_taxonomy_edit_form_fields', 10, 2 );
}

/**
 * Return the Occasion Types
 *
 * @since 1.0.0
 *
 * @param  array $query_args Optional. Query args for {@see WP_Term_Query}.
 * @return array Occasion type data.
 */
function incassoos_get_occasion_types( $query_args = array() ) {

	// Parse defaults
	$query_args = wp_parse_args( $query_args, array(
		'taxonomy'   => incassoos_get_occasion_type_tax_id(),
		'hide_empty' => false
	) );

	return get_terms( $query_args );
}

/**
 * Return the default Occasion Type's term id
 *
 * @since 1.0.0
 * 
 * @uses apply_filters() Calls 'incassoos_get_default_occasion_type'
 *
 * @return int Occasion Type term id.
 */
function incassoos_get_default_occasion_type() {
	$terms = incassoos_get_occasion_types( array(
		'fields'     => 'ids',
		'meta_key'   => '_default',
		'meta_value' => 1
	) );
	$term = 0;

	if ( $terms ) {
		$term = $terms[0];
	}

	return apply_filters( 'incassoos_get_default_occasion_type', $term );
}

/**
 * Will update term count based on the Occasion post type with custom post statuses
 *
 * @since 1.0.0
 *
 * @see _update_post_term_count()
 *
 * @global WPDB $wpdb
 *
 * @param array  $terms    Term ids
 * @param string $taxonomy Taxonomy
 */
function incassoos_update_occasion_term_count( $terms, $taxonomy ) {
	global $wpdb;

	// Get post statuses for Occasions
	$post_statuses = array( 'publish', incassoos_get_collected_status_id() );

	foreach ( (array) $terms as $term ) {
		$count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->term_relationships, $wpdb->posts WHERE $wpdb->posts.ID = $wpdb->term_relationships.object_id AND post_type = %s AND post_status IN ('" . implode( "', '", $post_statuses ) . "') AND term_taxonomy_id = %d", incassoos_get_occasion_post_type(), $term ) );

		/** This action is documented in wp-includes/taxonomy.php */
		do_action( 'edit_term_taxonomy', $term, $taxonomy->name );
		$wpdb->update( $wpdb->term_taxonomy, compact( 'count' ), array( 'term_taxonomy_id' => $term ) );

		/** This action is documented in wp-includes/taxonomy.php */
		do_action( 'edited_term_taxonomy', $term, $taxonomy->name );
	}
}

/** Template ******************************************************************/

/**
 * Return the Occasion
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return WP_Term|bool Occasion post object or False when not found.
 */
function incassoos_get_occasion( $post = 0 ) {

	// Get the post
	$post = get_post( $post );

	// Return false when this is not an Occasion
	if ( ! $post || incassoos_get_occasion_post_type() !== $post->post_type ) {
		$post = false;
	}

	return $post;
}

/**
 * Output the Occasion's title
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_occasion_title( $post = 0 ) {
	echo incassoos_get_occasion_title( $post );
}

/**
 * Return the Occasion's title
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_occasion_title'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Occasion title
 */
function incassoos_get_occasion_title( $post = 0 ) {
	$post  = incassoos_get_occasion( $post );
	$title = $post ? get_the_title( $post ) : '';

	return apply_filters( 'incassoos_get_occasion_title', $title, $post );
}

/**
 * Output the Occasion's url
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_occasion_url( $post = 0 ) {
	echo incassoos_get_occasion_url( $post );
}

/**
 * Return the Occasion's url
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_occasion_url'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Occasion url
 */
function incassoos_get_occasion_url( $post = 0 ) {
	$post = incassoos_get_occasion( $post );
	$url  = $post ? add_query_arg( array( 'post' => $post->ID, 'action' => 'edit' ), admin_url( 'post.php' ) ) : '';

	return apply_filters( 'incassoos_get_occasion_url', $url, $post );
}

/**
 * Output the Occasion's link
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_occasion_link( $post = 0 ) {
	echo incassoos_get_occasion_link( $post );
}

/**
 * Return the Occasion's link
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_occasion_link'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Occasion link
 */
function incassoos_get_occasion_link( $post = 0 ) {
	$post = incassoos_get_occasion( $post );
	$link = $post ? sprintf( '<a href="%s">%s</a>', esc_url( incassoos_get_occasion_url( $post ) ), incassoos_get_occasion_title( $post ) ) : '';

	return apply_filters( 'incassoos_get_occasion_link', $link, $post );
}

/**
 * Output the Occasion's author name
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_occasion_author( $post = 0 ) {
	echo incassoos_get_occasion_author( $post );
}

/**
 * Return the Occasion's author name
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_occasion_author'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Occasion author name or False when not found.
 */
function incassoos_get_occasion_author( $post = 0 ) {
	$post   = incassoos_get_occasion( $post );
	$author = get_userdata( $post ? $post->post_author : 0 );

	if ( $author && $author->exists() ) {
		$author = $author->display_name;
	} else {
		$author = '';
	}

	return apply_filters( 'incassoos_get_occasion_author', $author, $post );
}

/**
 * Output the Occasion's created date
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  string      $date_format Optional. Timestamp's date format to return. Defaults to the `date_format` option.
 */
function incassoos_the_occasion_created( $post = 0, $date_format = '' ) {
	echo incassoos_get_occasion_created( $post, $date_format );
}

/**
 * Return the Occasion's created date
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_occasion_created'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  string      $date_format Optional. Timestamp's date format to return. Defaults to the `date_format` option.
 * @return string Occasion created date.
 */
function incassoos_get_occasion_created( $post = 0, $date_format = '' ) {
	$post = incassoos_get_occasion( $post );
	$date = $post ? $post->post_date : '';

	// Default to the registered date format
	if ( empty( $date_format ) ) {
		$date_format = get_option( 'date_format' );
	}

	if ( $date ) {
		$date = mysql2date( $date_format, $date );
	}

	return apply_filters( 'incassoos_get_occasion_created', $date, $post, $date_format );
}

/**
 * Output the Occasion's date
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  string      $date_format Optional. Timestamp's date format to return. Defaults to the `date_format` option.
 */
function incassoos_the_occasion_date( $post = 0, $date_format = '' ) {
	echo incassoos_get_occasion_date( $post, $date_format );
}

/**
 * Return the Occasion's date
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_occasion_date'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  string      $date_format Optional. Timestamp's date format to return. Defaults to the `date_format` option.
 * @return string Occasion date.
 */
function incassoos_get_occasion_date( $post = 0, $date_format = '' ) {
	$post = incassoos_get_occasion( $post );
	$date = get_post_meta( $post ? $post->ID : 0, 'occasion_date', true );

	// Default to the registered date format
	if ( empty( $date_format ) ) {
		$date_format = get_option( 'date_format' );
	}

	if ( $date ) {
		$date = mysql2date( $date_format, $date );
	} else {
		$date = '';
	}

	return apply_filters( 'incassoos_get_occasion_date', $date, $post, $date_format );
}

/**
 * Return whether the Occasion is registered for the same date as it was created
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_is_occasion_same_date_created'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Are the Occasion's dates the same?
 */
function incassoos_is_occasion_same_date_created( $post = 0 ) {
	$post      = incassoos_get_occasion( $post );
	$same_date = $post && ( incassoos_get_occasion_created( $post ) === incassoos_get_occasion_date( $post ) );

	return (bool) apply_filters( 'incassoos_is_occasion_same_date_created', $same_date, $post );
}

/**
 * Return whether the given post has any or the given Occasion Type
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  int|WP_Term $term Optional. Term object or ID. Defaults to any term.
 * @return bool Object has a/the Occasion Type
 */
function incassoos_occasion_has_type( $post = 0, $term = 0 ) {
	return has_term( $term, incassoos_get_occasion_type_tax_id(), $post );
}

/**
 * Output the Occasion's Type
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_occasion_type( $post = 0 ) {
	the_terms( $post, incassoos_get_occasion_type_tax_id() );
}

/**
 * Return the Occasion's Type
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_occasion_type'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return WP_Term|bool Occasion Type term id.
 */
function incassoos_get_occasion_type( $post = 0, $object = false ) {

	// Bail when post does not exist
	if ( ! $post = incassoos_get_occasion( $post ) )
		return false;

	// Define return variable
	$type = false;

	// Get the Article's Edition terms
	$term_args = array( 'fields' => ( false === $object ) ? 'ids' : 'all' );
	$terms     = wp_get_object_terms( $post->ID, incassoos_get_occasion_type_tax_id(), $term_args );

	// Assign term ID when found
	if ( ! empty( $terms ) ) {
		$type = $terms[0];
	}

	return apply_filters( 'incassoos_get_occasion_type', $type, $post, $object );
}

/**
 * Output the Occasion's total value
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  bool|array|null $num_format Optional. Whether to apply currency format. Pass array as format args. Pass
 *                                     null to skip format parsing. Defaults to false.
 */
function incassoos_the_occasion_total( $post = 0, $num_format = false ) {
	echo incassoos_get_occasion_total( $post, $num_format );
}

/**
 * Return the Occasion's total value
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_occasion_total'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  bool|array|null $num_format Optional. Whether to apply currency format. Pass array as format args. Pass
 *                                     null to skip format parsing. Defaults to false.
 * @return string|float Occasion total value.
 */
function incassoos_get_occasion_total( $post = 0, $num_format = false ) {
	$post  = incassoos_get_occasion( $post );
	$total = get_post_meta( $post ? $post->ID : 0, 'total', true );

	// Get total from raw calculation
	if ( false === $total && $post ) {
		$total = incassoos_get_occasion_total_raw( $post );
		update_post_meta( $post->ID, 'total', $total );
	}

	$total = (float) apply_filters( 'incassoos_get_occasion_total', (float) $total, $post );

	// Apply currency format
	if ( null !== $num_format ) {
		$total = incassoos_parse_currency( $total, $num_format );
	}

	return $total;
}

/**
 * Return the Occasion's raw total value
 *
 * @since 1.0.0
 *
 * @global WPDB $wpdb
 *
 * @uses apply_filters() Calls 'incassoos_get_occasion_total_raw'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return float Occasion raw total value.
 */
function incassoos_get_occasion_total_raw( $post = 0 ) {
	global $wpdb;

	$post   = incassoos_get_occasion( $post );
	$orders = incassoos_get_occasion_orders( $post );
	$total  = 0;

	// Query all total values
	if ( $orders ) {
		$post_ids = implode( ',', $orders );
		$sql      = $wpdb->prepare( "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id IN ($post_ids) AND meta_key = %s", 'total' );

		if ( $values = $wpdb->get_col( $sql ) ) {
			$total = array_sum( array_map( 'floatval', $values ) );
		}
	}

	return (float) apply_filters( 'incassoos_get_occasion_total_raw', $total, $post );
}

/**
 * Output the Occasion's closed date
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  string      $date_format Optional. Timestamp's date format to return. Defaults to the `date_format` option.
 */
function incassoos_the_occasion_closed_date( $post = 0, $date_format = '' ) {
	echo incassoos_get_occasion_closed_date( $post, $date_format );
}

/**
 * Return the Occasion's closed date
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_occasion_closed_date'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  string      $date_format Optional. Timestamp's date format to return. Defaults to the `date_format` option.
 * @return string|bool Occasion closed date or False when not found.
 */
function incassoos_get_occasion_closed_date( $post = 0, $date_format = '' ) {
	$post = incassoos_get_occasion( $post );
	$date = get_post_meta( $post ? $post->ID : 0, 'closed', true );

	// Default to the registered date format
	if ( empty( $date_format ) ) {
		$date_format = get_option( 'date_format' );
	}

	if ( $date ) {
		$date = mysql2date( $date_format, $date );
	} else {
		$date = '';
	}

	return apply_filters( 'incassoos_get_occasion_closed_date', $date, $post, $date_format );
}

/**
 * Return whether the Occasion is closed
 *
 * An Occasion is marked closed when it should no longer accept new consumption
 * entries. This is before the Collection parent is collected.
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_is_occasion_closed'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Occasion is closed
 */
function incassoos_is_occasion_closed( $post = 0 ) {
	$post   = incassoos_get_occasion( $post );
	$closed = $post && (bool) get_post_meta( $post->ID, 'closed', true );

	return (bool) apply_filters( 'incassoos_is_occasion_closed', $closed, $post );
}

/**
 * Return whether the Occasion is collected
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_is_occasion_collected'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Occasion is collected
 */
function incassoos_is_occasion_collected( $post = 0 ) {
	$post      = incassoos_get_occasion( $post );
	$collected = $post && ( incassoos_get_collected_status_id() === $post->post_status );

	return (bool) apply_filters( 'incassoos_is_occasion_collected', $collected, $post );
}

/**
 * Return whether the Occasion is locked
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_is_occasion_locked'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Occasion is locked
 */
function incassoos_is_occasion_locked( $post = 0 ) {
	$post   = incassoos_get_occasion( $post );
	$locked = $post && ( incassoos_is_occasion_closed( $post ) || incassoos_is_occasion_collected( $post ) );

	return (bool) apply_filters( 'incassoos_is_occasion_locked', $locked, $post );
}

/**
 * Return whether the Occassion is collectable
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_is_occasion_collectable'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Occassion is collectable
 */
function incassoos_is_occasion_collectable( $post = 0 ) {
	$post        = incassoos_get_occasion( $post );
	$collectable = $post && ! incassoos_is_occasion_collected( $post ) && incassoos_is_occasion_closed( $post );

	return (bool) apply_filters( 'incassoos_is_occasion_collectable', $collectable, $post );
}

/**
 * Query and return the Occasions
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_occasions'
 *
 * @param  array $query_args Optional. Additional query arguments for {@see WP_Query}.
 * @return array Occasions
 */
function incassoos_get_occasions( $query_args = array() ) {

	// Parse query arguments
	$query_args = wp_parse_args( $query_args, array(
		'fields'         => 'ids',
		'post_type'      => incassoos_get_occasion_post_type(),
		'posts_per_page' => -1
	) );

	$query = new WP_Query( $query_args );
	$posts = $query->posts;

	// Default to empty array
	if ( ! $posts ) {
		$posts = array();
	}

	return apply_filters( 'incassoos_get_occasions', $posts, $query_args );
}

/**
 * Return the uncollected Occasions
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_uncollected_occasions'
 *
 * @param  array $query_args Optional. Additional query arguments for {@see WP_Query}.
 * @return array Uncollected Occasions
 */
function incassoos_get_uncollected_occasions( $query_args = array() ) {

	// Define query arguments
	$query_args['incassoos_collected'] = false;

	// Query posts
	$posts = incassoos_get_occasions( $query_args );

	return apply_filters( 'incassoos_get_uncollected_occasions', $posts, $query_args );
}

/**
 * Return the Occasion's orders
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_occasion_orders'
 *
 * @param  int|WP_Post $post Optional. Term object or ID post object. Defaults to the current post.
 * @param  array       $query_args Optional. Additional query arguments for {@see WP_Query}.
 * @return array Occasion orders
 */
function incassoos_get_occasion_orders( $post = 0, $query_args = array() ) {
	$post  = incassoos_get_occasion( $post );
	$posts = array();

	// Query orders
	if ( $post ) {

		// Query by post parent
		$query_args['post_parent'] = $post->ID;

		// Query posts
		$posts = incassoos_get_orders( $query_args );
	}

	return (array) apply_filters( 'incassoos_get_occasion_orders', $posts, $post, $query_args );
}

/**
 * Output the Occasion's order count
 * 
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Term object or ID post object. Defaults to the current post.
 * @param  array       $query_args Optional. Additional query arguments for {@see WP_Query}.
 */
function incassoos_the_occasion_order_count( $post = 0, $query_args = array() ) {
	echo incassoos_get_occasion_order_count( $post, $query_args );
}

/**
 * Return the Occasion's order count
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_occasion_order_count'
 *
 * @param  int|WP_Post $post Optional. Term object or ID post object. Defaults to the current post.
 * @param  array       $query_args Optional. Additional query arguments for {@see WP_Query}.
 * @return int Occasion order count
 */
function incassoos_get_occasion_order_count( $post = 0, $query_args = array() ) {
	$post  = incassoos_get_occasion( $post );
	$posts = incassoos_get_occasion_orders( $post, $query_args );
	$count = count( $posts );

	return (int) apply_filters( 'incassoos_get_occasion_order_count', $count, $post, $query_args );
}

/**
 * Return the Occasion's consumers
 * 
 * @since 1.0.0
 *
 * @global WPDB $wpdb
 *
 * @uses apply_filters() Calls 'incassoos_get_occasion_consumers'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return array Occasion consumer ids
 */
function incassoos_get_occasion_consumers( $post = 0 ) {
	global $wpdb;

	$post   = incassoos_get_occasion( $post );
	$orders = incassoos_get_occasion_orders( $post );
	$users  = array();

	if ( $post && $orders ) {

		// Define post meta query
		$post_ids = implode( ',', $orders );
		$sql      = $wpdb->prepare( "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id IN ($post_ids) AND meta_key = %s", 'consumer' );

		// Query all users
		if ( $values = $wpdb->get_col( $sql ) ) {
			$users = array_values( array_map( 'intval', array_unique( array_filter( $values ) ) ) );
		}
	}

	return (array) apply_filters( 'incassoos_get_occasion_consumers', $users, $post );
}

/**
 * Return the Occasion's consumer users
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_occasion_consumer_users'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  array $query_args Optional. Additional query arguments for {@see WP_User_Query}.
 * @return array Occasion consumer user objects
 */
function incassoos_get_occasion_consumer_users( $post = 0, $query_args = array() ) {
	$post      = incassoos_get_occasion( $post );
	$consumers = incassoos_get_occasion_consumers( $post );
	$users     = array();

	if ( $consumers ) {

		// Query selected users
		$user_ids = ! empty( $query_args['include'] ) ? array_intersect( (array) $query_args['include'], $consumers ) : $consumers;
		$query_args['include'] = array_map( 'intval', array_unique( array_filter( $user_ids ) ) );

		// Query users
		$users = incassoos_get_users( $query_args );
	}

	return apply_filters( 'incassoos_get_occasion_consumer_users', $users, $post, $query_args, $consumers );
}

/**
 * Output the Occasion's consumer count
 * 
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_occasion_consumer_count( $post = 0 ) {
	echo incassoos_get_occasion_consumer_count( $post );
}

/**
 * Return the Occasion's consumer count
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_occasion_consumer_count'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return int Occasion consumer count.
 */
function incassoos_get_occasion_consumer_count( $post = 0 ) {
	$post  = incassoos_get_occasion( $post );
	$users = incassoos_get_occasion_consumers( $post );
	$count = count( array_unique( $users ) );

	return (int) apply_filters( 'incassoos_get_occasion_consumer_count', $count, $post );
}

/**
 * Return the Occasion's unknown consumers
 *
 * @since 1.0.0
 *
 * @global WPDB $wpdb
 *
 * @uses apply_filters() Calls 'incassoos_get_occasion_unknown_consumers'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return array Occasion unknown consumers
 */
function incassoos_get_occasion_unknown_consumers( $post = 0 ) {
	global $wpdb;

	$post        = incassoos_get_occasion( $post );
	$consumers   = incassoos_get_occasion_consumers( $post );
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

	return apply_filters( 'incassoos_get_occasion_unknown_consumers', $unknown_ids, $post );
}

/**
 * Return whether the Occasion has unknown consumers
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_occasion_has_unknown_participant'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Occasion has unknown consumers
 */
function incassoos_occasion_has_unknown_consumers( $post = 0 ) {
	$post    = incassoos_get_occasion( $post );
	$unknown = (bool) incassoos_get_occasion_unknown_consumers( $post );

	return (bool) apply_filters( 'incassoos_occasion_has_unknown_consumers', $unknown, $post );
}

/**
 * Return the Occasion's consumer types
 * 
 * @since 1.0.0
 *
 * @global WPDB $wpdb
 *
 * @uses apply_filters() Calls 'incassoos_get_occasion_consumer_types'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return array Occasion consumer types
 */
function incassoos_get_occasion_consumer_types( $post = 0 ) {
	global $wpdb;

	$post   = incassoos_get_occasion( $post );
	$orders = incassoos_get_occasion_orders( $post );
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
		foreach ( incassoos_get_occasion_unknown_consumers( $post ) as $user_id ) {
			$types[] = incassoos_get_unknown_user_consumer_type_id( $user_id );
		}
	}

	return (array) apply_filters( 'incassoos_get_occasion_consumer_types', $types, $post );
}

/**
 * Output the Occasion's consumer total value
 * 
 * @since 1.0.0
 *
 * @param  int}WP_user|string $consumer Consumer user object or ID or consumer type id.
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  bool|array|null $num_format Optional. Whether to apply currency format. Pass array as format args. Pass
 *                                     null to skip format parsing. Defaults to false.
 */
function incassoos_the_occasion_consumer_total( $consumer, $post = 0, $num_format = false ) {
	echo incassoos_get_occasion_consumer_total( $consumer, $post, $num_format );
}

/**
 * Return the Occasion's consumer total value
 * 
 * @since 1.0.0
 *
 * @global WPDB $wpdb
 *
 * @uses apply_filters() Calls 'incassoos_get_occasion_consumer_total'
 *
 * @param  int}WP_user|string $consumer Consumer user object or ID or consumer type id.
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  bool|array|null $num_format Optional. Whether to apply currency format. Pass array as format args. Pass
 *                                     null to skip format parsing. Defaults to false.
 * @return string|float Occasion consumer total value.
 */
function incassoos_get_occasion_consumer_total( $consumer, $post = 0, $num_format = false ) {
	global $wpdb;

	$_consumer = is_a( $consumer, 'WP_User' ) ? $consumer->ID : $consumer;
	$post      = incassoos_get_occasion( $post );
	$total     = 0;

	if ( $post ) {

		// Consider unknown users
		if ( incassoos_is_unknown_user_consumer_type_id( $consumer ) ) {
			$_consumer = incassoos_get_user_id_from_unknown_user_consumer_type( $consumer );

		// Consider all unknown users
		} elseif ( incassoos_get_unknown_user_consumer_type_id_base() === $consumer ) {
			$_consumer = incassoos_get_occasion_unknown_consumers( $post );
		}

		// Query orders
		$query_type = is_numeric( $_consumer ) || is_array( $_consumer ) ? 'incassoos_consumer' : 'incassoos_consumer_type';
		$orders     = incassoos_get_occasion_orders( $post, array( $query_type => $_consumer ) );

		if ( $orders ) {

			// Define post meta query
			$post_ids = implode( ',', $orders );
			$sql      = $wpdb->prepare( "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id IN ($post_ids) AND meta_key = %s", 'total' );

			// Query all totals
			if ( $values = $wpdb->get_col( $sql ) ) {
				$total = array_sum( array_map( 'floatval', $values ) );
			}
		}
	}

	$total = (float) apply_filters( 'incassoos_get_occasion_consumer_total', (float) $total, $consumer, $post, $num_format );

	// Apply currency format
	if ( null !== $num_format ) {
		$total = incassoos_parse_currency( $total, $num_format );
	}

	return $total;
}

/**
 * Return the Occasion's consumer orders
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_occasion_consumer_orders'
 *
 * @param  int}WP_user|string $consumer Consumer user object or ID or consumer type id.
 * @param  int|WP_Post $post Optional. Term object or ID post object. Defaults to the current post.
 * @param  array       $query_args Optional. Additional query arguments for {@see WP_Query}.
 * @return array Occasion consumer orders
 */
function incassoos_get_occasion_consumer_orders( $consumer, $post = 0, $query_args = array() ) {
	$_consumer = is_a( $consumer, 'WP_User' ) ? $consumer->ID : $consumer;
	$post      = incassoos_get_occasion( $post );
	$posts     = array();

	if ( $post ) {

		// Consider unknown users
		if ( incassoos_is_unknown_user_consumer_type_id( $consumer ) ) {
			$_consumer = incassoos_get_user_id_from_unknown_user_consumer_type( $consumer );

		// Consider all unknown users
		} elseif ( incassoos_get_unknown_user_consumer_type_id_base() === $consumer ) {
			$_consumer = incassoos_get_occasion_unknown_consumers( $post );
		}

		// Define post meta query
		$meta_query = isset( $query_args['meta_query'] ) ? $query_args['meta_query'] : array();
		$meta_query[] = array(
			array(
				'key'     => is_numeric( $_consumer ) || is_array( $_consumer ) ? 'consumer' : 'consumer_type',
				'value'   => (array) $_consumer,
				'compare' => 'IN'
			)
		);
		$query_args['meta_query'] = $meta_query;

		// Query posts
		$posts = incassoos_get_occasion_orders( $post, $query_args );
	}

	return (array) apply_filters( 'incassoos_get_occasion_consumer_orders', $posts, $consumer, $post, $query_args );
}

/**
 * Output the Occasion's consumer order count
 * 
 * @since 1.0.0
 *
 * @param  int}WP_user|string $consumer Consumer user object or ID or consumer type id.
 * @param  int|WP_Post $post Optional. Term object or ID post object. Defaults to the current post.
 * @param  array       $query_args Optional. Additional query arguments for {@see WP_Query}.
 */
function incassoos_the_occasion_consumer_order_count( $consumer, $post = 0, $query_args = array() ) {
	echo incassoos_get_occasion_consumer_order_count( $consumer, $post, $query_args );
}

/**
 * Return the Occasion's consumer order count
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_occasion_consumer_order_count'
 *
 * @param  int}WP_user|string $consumer Consumer user object or ID or consumer type id.
 * @param  int|WP_Post $post Optional. Term object or ID post object. Defaults to the current post.
 * @param  array       $query_args Optional. Additional query arguments for {@see WP_Query}.
 * @return int Occasion consumer order count
 */
function incassoos_get_occasion_consumer_order_count( $consumer, $post = 0, $query_args = array() ) {
	$post  = incassoos_get_occasion( $post );
	$posts = incassoos_get_occasion_consumer_orders( $consumer, $post, $query_args );
	$count = count( $posts );

	return (int) apply_filters( 'incassoos_get_occasion_consumer_order_count', $count, $consumer, $post, $query_args );
}

/**
 * Return the Occasion's products
 * 
 * @since 1.0.0
 *
 * @global WPDB $wpdb
 *
 * @uses apply_filters() Calls 'incassoos_get_occasion_products'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return array Occasion products
 */
function incassoos_get_occasion_products( $post = 0 ) {
	global $wpdb;

	$post     = incassoos_get_occasion( $post );
	$orders   = incassoos_get_occasion_orders( $post );
	$products = array();

	if ( $post && $orders ) {

		// Define post meta query
		$post_ids = implode( ',', $orders );
		$sql      = $wpdb->prepare( "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id IN ($post_ids) AND meta_key = %s", 'products' );

		// Query all prodcuts
		if ( $values = $wpdb->get_col( $sql ) ) {
			foreach ( $values as $value ) {
				foreach ( maybe_unserialize( $value ) as $product ) {

					// Parse meta value
					$product = wp_parse_args( $product, array(
						'id'     => 0,
						'name'   => _x( 'Unknown', 'Product', 'incassoos' ),
						'price'  => 0,
						'amount' => 0
					) );

					// Identify unique product by name and price
					$title = $product['name'] . '-' . $product['price'];

					// Set product
					if ( ! isset( $products[ $title ] ) ) {
						$products[ $title ] = (object) array(
							'id'     => $product['id'],
							'name'   => $product['name'],
							'price'  => $product['price'],
							'amount' => 0,
						);
					}

					// Add amount
					$products[ $title ]->amount += $product['amount'];
				}
			}

			$products = array_values( $products );
		}
	}

	return (array) apply_filters( 'incassoos_get_occasion_products', $products, $post );
}

/**
 * Output the Occasion's product count
 * 
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Term object or ID post object. Defaults to the current post.
 */
function incassoos_the_occasion_product_count( $post = 0 ) {
	echo incassoos_get_occasion_product_count( $post );
}

/**
 * Return the Occasion's product count
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_occasion_product_count'
 *
 * @param  int|WP_Post $post Optional. Term object or ID post object. Defaults to the current post.
 * @return int Occasion product count
 */
function incassoos_get_occasion_product_count( $post = 0 ) {
	$post     = incassoos_get_occasion( $post );
	$products = incassoos_get_occasion_products( $post );
	$count    = array_sum( wp_list_pluck( $products, 'amount' ) );

	return (int) apply_filters( 'incassoos_get_occasion_product_count', $count, $post );
}

/** Filters *******************************************************************/

/**
 * Modify the Occasion's post title
 *
 * @since 1.0.0
 *
 * @param  string      $title Post title
 * @param  int}WP_Post $post  Optional. Post object or ID. Defaults to the current post.
 * @return string             Post title
 */
function incassoos_filter_occasion_title( $title, $post = 0 ) {

	// When this is an Occasion
	if ( incassoos_get_occasion( $post ) ) {
		$date = incassoos_get_occasion_date( $post, 'Y-m-d' );

		// Append the occasion date
		if ( $date ) {
			$title = sprintf( '%s (%s)', $title, $date );
		}
	}

	return $title;
}

/**
 * Modify the Occasion's post class
 *
 * @since 1.0.0
 *
 * @param  array       $classes Post class names
 * @param  string      $class   Added class names
 * @param  int}WP_Post $post_id Post ID
 * @return array       Post class names
 */
function incassoos_filter_occasion_class( $classes, $class, $post_id ) {
	$post = incassoos_get_occasion( $post_id );

	// When this is an Occasion
	if ( $post ) {

		// Occasion is closed
		if ( incassoos_is_occasion_closed( $post ) ) {
			$classes[] = 'occasion-closed';
		}

		// Occasion is collected
		if ( incassoos_is_occasion_collected( $post ) ) {
			$classes[] = 'occasion-collected';
		}

		// Occasion is locked
		if ( incassoos_is_occasion_locked( $post ) ) {
			$classes[] = 'occasion-locked';
		}
	}

	return $classes;
}

/** Collection ****************************************************************/

/**
 * Return the Occasion's Collection post
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return WP_Post|false Collection post object or False when not found.
 */
function incassoos_get_occasion_collection( $post = 0 ) {
	$post       = incassoos_get_occasion( $post );
	$collection = incassoos_get_occasion_collection_id( $post );

	return incassoos_get_collection( $collection );
}

/**
 * Output the Occasion's Collection ID
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_occasion_collection_id( $post = 0 ) {
	echo incassoos_get_occasion_collection_id( $post );
}

/**
 * Return the Occasion's Collection ID
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_occasion_collection_id'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return int Occasion's Collection ID
 */
function incassoos_get_occasion_collection_id( $post = 0 ) {
	$post   = incassoos_get_occasion( $post );
	$parent = $post ? $post->post_parent : 0;

	return (int) apply_filters( 'incassoos_get_occasion_collection_id', $parent, $post );
}

/**
 * Output the Occasion's Collection title
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_occasion_collection_title( $post = 0 ) {
	echo incassoos_get_occasion_collection_title( $post );
}

/**
 * Return the Occasion's Collection title
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Occasion's Collection title
 */
function incassoos_get_occasion_collection_title( $post = 0 ) {
	$collection = incassoos_get_occasion_collection( $post );
	$title      = $collection ? incassoos_get_collection_title( $collection ) : '';

	return $title;
}

/**
 * Output the Occasion's Collection collected date
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  string      $date_format Optional. Timestamp's date format to return. Defaults to the `date_format` option.
 */
function incassoos_the_occasion_collection_date( $post = 0, $date_format = '' ) {
	echo incassoos_get_occasion_collection_date( $post, $date_format );
}

/**
 * Return the Occasion's Collection collected date
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  string      $date_format Optional. Timestamp's date format to return. Defaults to the `date_format` option.
 * @return string Occasion's Collection collected date
 */
function incassoos_get_occasion_collection_date( $post = 0, $date_format = '' ) {
	$collection = incassoos_get_occasion_collection( $post );
	$date       = $collection ? incassoos_get_collection_date( $collection, $date_format ) : '';

	return $date;
}

/**
 * Output the Occasion's Collection url
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_occasion_collection_url( $post = 0 ) {
	echo incassoos_get_occasion_collection_url( $post );
}

/**
 * Return the Occasion's Collection url
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Occasion's Collection url
 */
function incassoos_get_occasion_collection_url( $post = 0 ) {
	$collection = incassoos_get_occasion_collection( $post );
	$url        = $collection ? incassoos_get_collection_url( $collection ) : '';

	return $url;
}

/**
 * Output the Occasion's Collection link
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_occasion_collection_link( $post = 0 ) {
	echo incassoos_get_occasion_collection_link( $post );
}

/**
 * Return the Occasion's Collection link
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Occasion's Collection link
 */
function incassoos_get_occasion_collection_link( $post = 0 ) {
	$collection = incassoos_get_occasion_collection( $post );
	$link       = $collection ? incassoos_get_collection_link( $collection ) : '';

	return $link;
}

/**
 * Output the Occasion's Collection hint
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_occasion_collection_hint( $post = 0 ) {
	echo incassoos_get_occasion_collection_hint( $post );
}

/**
 * Return the Occasion's Collection hint
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Occasion's Collection hint
 */
function incassoos_get_occasion_collection_hint( $post = 0 ) {
	$collection = incassoos_get_occasion_collection( $post );
	$hint       = incassoos_get_collection_hint( $collection ); // Provides default value

	return $hint;
}

/**
 * Return whether the Occasion's Collection is collected
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Occasion's Collection is collected
 */
function incassoos_is_occasion_collection_collected( $post = 0 ) {
	$collection   = incassoos_get_occasion_collection( $post );
	$is_collected = $collection ? incassoos_is_collection_collected( $collection ) : false;

	return $is_collected;
}

/** Update ********************************************************************/

/**
 * Update the Occasion's date
 *
 * @since 1.0.0
 *
 * @param  string      $date Date string
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Update success.
 */
function incassoos_update_occasion_date( $date, $post = 0 ) {
	$post    = incassoos_get_occasion( $post );
	$success = false;

	if ( $post ) {

		// Parse input date
		$date = strtotime( trim( $date ) );

		// Save mysql date string
		if ( $date ) {
			$date = date( 'Y-m-d 00:00:00', $date );
			$success = update_post_meta( $post->ID, 'occasion_date', $date );
		}
	}

	return $success;
}

/**
 * Update the Occasion's total value
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Update success.
 */
function incassoos_update_occasion_total( $post = 0 ) {
	$_post   = incassoos_get_occasion( $post );
	$success = false;

	// Maybe it's an Order
	if ( ! $_post ) {
		$_post = incassoos_get_order_occasion( $post );
	}

	// Update from raw total
	if ( $_post ) {
		$success = update_post_meta( $_post->ID, 'total', incassoos_get_occasion_total_raw( $_post ) );
	}

	return $success;
}

/**
 * Close the Occasion
 * 
 * @since 1.0.0
 *
 * @global WPDB $wpdb
 *
 * @uses do_action() Calls 'incassoos_close_occasion'
 * @uses do_action() Calls 'incassoos_closed_occasion'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Closing success.
 */
function incassoos_close_occasion( $post = 0 ) {
	global $wpdb;

	$post = incassoos_get_occasion( $post, false );

	// Bail when the Occasion wasn't found
	if ( ! $post )
		return false;

	// Bail when the Occasion is already closed or it is collected
	if ( incassoos_is_occasion_closed( $post ) || incassoos_is_occasion_collected( $post ) )
		return false;

	// Run action before closing
	do_action( 'incassoos_close_occasion', $post );

	// Update closed date
	update_post_meta( $post->ID, 'closed', wp_date( 'Y-m-d H:i:s' ) );

	// Run action after closing
	do_action( 'incassoos_closed_occasion', $post );

	return true;
}

/**
 * Reopen the Occasion
 * 
 * @since 1.0.0
 *
 * @global WPDB $wpdb
 *
 * @uses do_action() Calls 'incassoos_reopen_occasion'
 * @uses do_action() Calls 'incassoos_reopened_occasion'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool Opening success.
 */
function incassoos_reopen_occasion( $post = 0 ) {
	global $wpdb;

	$post = incassoos_get_occasion( $post, false );

	// Bail when the Occasion wasn't found
	if ( ! $post )
		return false;

	// Bail when the Occasion is not closed
	if ( ! incassoos_is_occasion_closed( $post ) )
		return false;

	// Run action before reopening
	do_action( 'incassoos_reopen_occasion', $post );

	// Remove closed date
	delete_post_meta( $post->ID, 'closed' );

	// Run action after reopening
	do_action( 'incassoos_reopened_occasion', $post );

	return true;
}

/**
 * Return whether the provided data is valid for an Occasion
 *
 * @since 1.0.0
 *
 * @param  array $args Occasion attributes to update
 * @return WP_Error|bool Error object on invalidation, true when validated
 */
function incassoos_validate_occasion( $args = array() ) {
	$update = isset( $args['ID'] ) && ! empty( $args['ID'] );

	// Parse defaults
	$args = wp_parse_args( $args, array(
		'post_title'    => '',
		'occasion_date' => $update ? incassoos_get_occasion_date( $args['ID'], 'd-m-Y' ) : ''
	) );

	// Validate title
	$title = incassoos_validate_title( $args['post_title'] );
	if ( is_wp_error( $title ) ) {
		return $title;
	}

	// Validate date
	$date = incassoos_validate_date( $args['occasion_date'], 'd-m-Y' );
	if ( is_wp_error( $date ) ) {
		return $date;
	}

	return true;
}
