<?php

/**
 * Incassoos Functions
 *
 * @package Incassoos
 * @subpackage Main
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Versions ******************************************************************/

/**
 * Output the plugin version
 *
 * @since 1.0.0
 */
function incassoos_version() {
	echo incassoos_get_version();
}

	/**
	 * Return the plugin version
	 *
	 * @since 1.0.0
	 *
	 * @return string The plugin version
	 */
	function incassoos_get_version() {
		return incassoos()->version;
	}

/**
 * Output the plugin database version
 *
 * @since 1.0.0
 */
function incassoos_db_version() {
	echo incassoos_get_db_version();
}

	/**
	 * Return the plugin database version
	 *
	 * @since 1.0.0
	 *
	 * @return string The plugin version
	 */
	function incassoos_get_db_version() {
		return incassoos()->db_version;
	}

/**
 * Output the plugin database version directly from the database
 *
 * @since 1.0.0
 */
function incassoos_db_version_raw() {
	echo incassoos_get_db_version_raw();
}

	/**
	 * Return the plugin database version directly from the database
	 *
	 * @since 1.0.0
	 *
	 * @return string The current plugin version
	 */
	function incassoos_get_db_version_raw() {
		return get_option( 'incassoos_db_version', '' );
	}

/** Rewrite *******************************************************************/

/**
 * Return the rewrite ID for the app interface
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_app_rewrite_id'
 * @return string Rewrite ID
 */
function incassoos_get_app_rewrite_id() {
	return apply_filters( 'incassoos_get_app_rewrite_id', 'incassoos' );
}

/**
 * Delete a blogs rewrite rules, so that they are automatically rebuilt on
 * the subsequent page load.
 *
 * @since 1.0.0
 */
function incassoos_delete_rewrite_rules() {
	delete_option( 'rewrite_rules' );
}

/** Slugs *********************************************************************/

/**
 * Return the slug for the application interface
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_app_slug'
 * @return string Slug
 */
function incassoos_get_app_slug() {
	return apply_filters( 'incassoos_get_app_slug', get_option( '_incassoos_app_slug', 'incassoos' ) );
}

/**
 * Return whether the application interface is shown on the front page
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_is_app_on_front'
 * @return bool Is the app shown on the front page?
 */
function incassoos_is_app_on_front() {
	return (bool) apply_filters( 'incassoos_is_app_on_front', get_option( '_incassoos_app_on_front', 'incassoos' ) );
}

/** URLs **********************************************************************/

/**
 * Return the url for the admin dashboard page
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_admin_url'
 * @return string Url
 */
function incassoos_get_admin_url() {
	return apply_filters( 'incassoos_get_admin_url', add_query_arg( 'page', 'incassoos', admin_url( 'admin.php' ) ) );
}

/**
 * Return the url for the app interface
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_app_url'
 * @return string Url
 */
function incassoos_get_app_url() {
	return apply_filters( 'incassoos_get_app_url', incassoos_is_app_on_front() ? home_url() : home_url( user_trailingslashit( incassoos_get_app_slug() ) ) );
}

/** Posts *********************************************************************/

/**
 * Return the plugin post type names
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_plugin_post_types'
 *
 * @return array Plugin post types
 */
function incassoos_get_plugin_post_types() {
	return apply_filters( 'incassoos_get_plugin_post_types', array(
		incassoos_get_collection_post_type(),
		incassoos_get_activity_post_type(),
		incassoos_get_occasion_post_type(),
		incassoos_get_order_post_type(),
		incassoos_get_product_post_type()
	) );
}

/**
 * Return whether the given post type belongs to the plugin
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_is_plugin_post_type'
 *
 * @param  string $post_type Optional. Post type to check. Defaults to the current post's post type.
 * @return bool Is this a plugin post type?
 */
function incassoos_is_plugin_post_type( $post_type = '' ) {

	// Default to the current post type
	if ( ! $post_type ) {
		$post_type = get_post_type();
	}

	// Check post type
	$is = in_array( $post_type, incassoos_get_plugin_post_types(), true );

	return (bool) apply_filters( 'incassoos_is_plugin_post_type', $is, $post_type );
}

/**
 * Return the plugin object type of the given post type
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_object_type'
 *
 * @param  string $post_type Optional. Post type. Defaults to the current post type.
 * @return string Plugin object type
 */
function incassoos_get_object_type( $post_type = '' ) {
	$type = '';

	// Default to the current post type
	if ( ! $post_type ) {
		$post_type = get_post_type();
	}

	$types = array(
		incassoos_get_collection_post_type() => 'collection',
		incassoos_get_activity_post_type()   => 'activity',
		incassoos_get_occasion_post_type()   => 'occasion',
		incassoos_get_order_post_type()      => 'order',
		incassoos_get_product_post_type()    => 'product',
	);

	if ( isset( $types[ $post_type ] ) ) {
		$type = $types[ $post_type ];
	}

	return apply_filters( 'incassoos_get_object_type', $type, $post_type );
}

/**
 * Return the plugin's post type of the given object type
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_object_post_type'
 *
 * @param  string $object_type Object type name
 * @return string Post type name
 */
function incassoos_get_object_post_type( $object_type ) {
	$post_type = '';
	$getter    = "incassoos_get_{$object_type}_post_type";

	if ( function_exists( $getter ) ) {
		$post_type = call_user_func( $getter );
	}

	return apply_filters( 'incassoos_get_object_post_type', $post_type, $object_type );
}

/**
 * Return a label for the post type
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_post_type_label'
 *
 * @param  string $post_type  Optional. Post type name.
 * @param  string $label_type Optional. Label type. Defaults to 'singular_name'.
 * @return string Post type label
 */
function incassoos_get_post_type_label( $post_type = '', $label_type = 'singular_name' ) {

	// Default to the current post type
	if ( empty( $post_type ) ) {
		$post_type = get_post_type();
	}

	$label = '';
	$pto   = get_post_type_object( $post_type );

	if ( $pto && isset( $pto->labels->{$label_type} ) ) {
		$label = $pto->labels->{$label_type};
	}

	return apply_filters( 'incassoos_get_post_type_label', $label, $post_type, $label_type );
}

/**
 * Modify the post type link
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_filter_post_type_link'
 *
 * @param  string  $post_link Post link
 * @param  WP_Post $post      Post object
 * @param  bool    $leavename [description]
 * @param  bool    $sample    [description]
 * @return string Post link
 */
function incassoos_filter_post_type_link( $post_link, $post, $leavename, $sample ) {

	// Concerning plugin post types
	if ( incassoos_is_plugin_post_type( $post->post_type ) ) {

		// Refer to the post's admin page
		$post_link = add_query_arg( array( 'post' => $post->ID, 'action' => 'view' ), admin_url( 'post.php' ) );
	}

	return apply_filters( 'incassoos_filter_post_type_link', $post_link, $post, $leavename, $sample );
}

/**
 * Return the validation result of the post data for the given post type
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_validate_post'
 *
 * @param  array  $postarr Post data.
 * @param  string $post_type Optional. Post type. Defaults to the current post type.
 * @return WP_Error|bool Error object on invalidation, true when validated, false when not validated.
 */
function incassoos_validate_post( $postarr, $post_type = '' ) {
	$validated = false;

	// Derive post type from post data
	if ( ! $post_type && isset( $postarr['post_type'] ) ) {
		$post_type = $postarr['post_type'];
	}

	// Default to the current post type
	if ( ! $post_type ) {
		$post_type = get_post_type();
	}

	$types = array(
		incassoos_get_collection_post_type() => 'incassoos_validate_collection',
		incassoos_get_activity_post_type()   => 'incassoos_validate_activity',
		incassoos_get_occasion_post_type()   => 'incassoos_validate_occasion',
		incassoos_get_order_post_type()      => 'incassoos_validate_order',
		incassoos_get_product_post_type()    => 'incassoos_validate_product',
	);

	if ( isset( $types[ $post_type ] ) ) {
		$validated = call_user_func_array( $types[ $post_type ], array( $postarr ) );
	}

	return apply_filters( 'incassoos_validate_post', $validated, $postarr, $post_type );
}

/**
 * The following are post type-agnostic approaches of plugin post details. This logic
 * is usually applied to fetch post details when there is a benefit of not knowing the
 * post's post type per se.
 */

/**
 * Output the post's title
 * 
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_post_title( $post = 0 ) {
	echo incassoos_get_post_title( $post );
}

/**
 * Return the post's title
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_post_title'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Post title.
 */
function incassoos_get_post_title( $post = 0 ) {
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

	// Order
	} elseif ( $_post = incassoos_get_order( $post ) ) {
		$title = incassoos_get_order_title( $_post );

	// Product
	} elseif ( $_post = incassoos_get_product( $post ) ) {
		$title = incassoos_get_product_title( $_post );

	// Custom post
	} else {
		$title = apply_filters( 'incassoos_get_post_title', $title, $post );
	}

	return $title;
}

/**
 * Output the post's date
 * 
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  string      $date_format Optional. Timestamp's date format to return. Defaults to the `date_format` option.
 */
function incassoos_the_post_date( $post = 0, $date_format = '' ) {
	echo incassoos_get_post_date( $post, $date_format );
}

/**
 * Return the post's date
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_post_date'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  string      $date_format Optional. Timestamp's date format to return. Defaults to the `date_format` option.
 * @return string Post date.
 */
function incassoos_get_post_date( $post = 0, $date_format = '' ) {
	$date = '';

	// Collection
	if ( $_post = incassoos_get_collection( $post ) ) {
		$date = incassoos_get_collection_date( $_post, $date_format );

	// Activity
	} elseif ( $_post = incassoos_get_activity( $post ) ) {
		$date = incassoos_get_activity_date( $_post, $date_format );

	// Occasion
	} elseif ( $_post = incassoos_get_occasion( $post ) ) {
		$date = incassoos_get_occasion_date( $_post, $date_format );

	// Order
	} elseif ( $_post = incassoos_get_order( $post ) ) {
		$date = incassoos_get_order_created( $_post );

	// Product
	} elseif ( $_post = incassoos_get_product( $post ) ) {
		$date = incassoos_get_product_created( $_post );

	// Custom post
	} else {

		// Default to the registered date format
		if ( empty( $date_format ) ) {
			$date_format = get_option( 'date_format' );
		}

		$date = apply_filters( 'incassoos_get_post_date', $date, $post, $date_format );
	}

	return $date;
}

/**
 * Output the post's url
 * 
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_post_url( $post = 0 ) {
	echo incassoos_get_post_url( $post );
}

/**
 * Return the post's url
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_post_url'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Post url.
 */
function incassoos_get_post_url( $post = 0 ) {
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

	// Order
	} elseif ( $_post = incassoos_get_order( $post ) ) {
		$url = incassoos_get_order_url( $_post );

	// Product
	} elseif ( $_post = incassoos_get_product( $post ) ) {
		$url = incassoos_get_product_url( $_post );

	// Custom post
	} else {
		$url = apply_filters( 'incassoos_get_post_url', $url, $post );
	}

	return $url;
}

/**
 * Output the post's link
 * 
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_post_link( $post = 0 ) {
	echo incassoos_get_post_link( $post );
}

/**
 * Return the post's link
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_post_link'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Post link.
 */
function incassoos_get_post_link( $post = 0 ) {
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

	// Order
	} elseif ( $_post = incassoos_get_order( $post ) ) {
		$link = incassoos_get_order_link( $_post );

	// Product
	} elseif ( $_post = incassoos_get_product( $post ) ) {
		$link = incassoos_get_product_link( $_post );

	// Custom post
	} else {
		$link = apply_filters( 'incassoos_get_post_link', $link, $post );
	}

	return $link;
}

/**
 * Output the post's total value
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  bool|array|null $num_format Optional. Whether to apply currency format. Pass array as format args. Pass
 *                                     null to skip format parsing. Defaults to false.
 */
function incassoos_the_post_total( $post = 0, $num_format = false ) {
	echo incassoos_get_post_total( $post, $num_format );
}

/**
 * Return the post's total value
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_post_total'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  bool|array|null $num_format Optional. Whether to apply currency format. Pass array as format args. Pass
 *                                     null to skip format parsing. Defaults to false.
 * @return string Post total value.
 */
function incassoos_get_post_total( $post = 0, $num_format = false ) {
	$total = '';

	// Collection
	if ( $_post = incassoos_get_collection( $post ) ) {
		$total = incassoos_get_collection_total( $_post, $num_format );

	// Activity
	} elseif ( $_post = incassoos_get_activity( $post ) ) {
		$total = incassoos_get_activity_total( $_post, $num_format );

	// Occasion
	} elseif ( $_post = incassoos_get_occasion( $post ) ) {
		$total = incassoos_get_occasion_total( $_post, $num_format );

	// Order
	} elseif ( $_post = incassoos_get_order( $post ) ) {
		$total = incassoos_get_order_total( $_post, $num_format );

	// Custom post
	} else {
		$total = apply_filters( 'incassoos_get_post_total', $total, $post, $num_format );
	}

	return $total;
}

/**
 * Output the post's consumer total value
 * 
 * @since 1.0.0
 *
 * @param  int|WP_User|string $consumer Consumer user object or ID or consumer type id.
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  bool|array|null $num_format Optional. Whether to apply currency format. Pass array as format args. Pass
 *                                     null to skip format parsing. Defaults to false.
 */
function incassoos_the_post_consumer_total( $consumer, $post = 0, $num_format = false ) {
	echo incassoos_get_post_consumer_total( $consumer, $post, $num_format );
}

/**
 * Return the post's consumer total value
 * 
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_post_consumer_total'
 *
 * @param  int|WP_User|string $consumer Consumer user object or ID or consumer type id.
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  bool|array|null $num_format Optional. Whether to apply currency format. Pass array as format args. Pass
 *                                     null to skip format parsing. Defaults to false.
 * @return string|float Asset consumer total value.
 */
function incassoos_get_post_consumer_total( $consumer, $post = 0, $num_format = false ) {
	$consumer = is_a( $consumer, 'WP_User' ) ? $consumer->ID : $consumer;
	$total    = 0;

	// Collection
	if ( $_post = incassoos_get_collection( $post ) ) {
		$total = incassoos_get_collection_consumer_total( $consumer, $_post, $num_format );

	// Activity
	} elseif ( $_post = incassoos_get_activity( $post ) ) {
		$total = incassoos_get_activity_participant_price( $consumer, $_post, $num_format );

	// Occasion
	} elseif ( $_post = incassoos_get_occasion( $post ) ) {
		$total = incassoos_get_occasion_consumer_total( $consumer, $_post, $num_format );

	// Custom post
	} else {
		$total = apply_filters( 'incassoos_get_post_consumer_total', $total, $post, $consumer, $num_format );

		// Apply currency format
		if ( ! is_string( $total ) && null !== $num_format ) {
			$total = incassoos_get_format_currency( $total, $num_format );
		}
	}

	return $total;
}

/**
 * Return whether the post is considered published
 *
 * Checks if the post has any non-draft status.
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Post ID or object
 * @return bool Is the post published?
 */
function incassoos_is_post_published( $post = 0 ) {
	$post         = get_post( $post );
	$is_published = false;

	if ( $post ) {
		$is_published = ! in_array( $post->post_status, array( 'auto-draft', 'draft' ), true );
	}

	return (bool) apply_filters( 'incassoos_is_post_published', $is_published, $post );
}

/**
 * Return whether the post has a total value
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Post ID or object
 * @return bool Dows the post have a total?
 */
function incassoos_is_post_with_total( $post = 0 ) {
	$post       = get_post( $post );
	$with_total = (bool) incassoos_get_post_total( $post, null );

	return (bool) apply_filters( 'incassoos_is_post_with_total', $with_total, $post );
}

/**
 * Modify whether the post should be prevented from inserting
 *
 * This function checks whether the inserted data for the non-draft post is
 * valid. When invalidated, the function prevents the inserting of the post
 * by returning `true` to the 'wp_insert_post_empty_content' filter. Said
 * filter signals whether the post is considered 'empty', but it has no
 * customizable error message.
 *
 * To display the proper error message to the user, the following functions
 * are put in place to handle this for the post-new.php admin page:
 * - @see {incassoos_admin_redirect_post_location()} Defines redirect url with error params.
 * - @see {incassoos_admin_post_notices()} Displays error post notice.
 * - @see {incassoos_admin_post_updated_messages()} Holds the relevant error messages.
 *
 * The filter in `wp_insert_post()` by default only defines 'empty' posts when
 * its post type has support for post title, content and excerpt. Since most plugin
 * assets do not have support for each of those items, custom validaters are put
 * in place to handle situations where they might be considered empty or should
 * not be inserted at all.
 *
 * @see wp_insert_post()
 *
 * @since 1.0.0
 *
 * @param  bool  $prevent Whether the post should not be inserted
 * @param  array $postarr Original post insert data
 * @return bool  Should the post not be inserted?
 */
function incassoos_prevent_insert_post( $prevent, $postarr ) {

	// Ignore auto-drafts being created when opening post-new.php
	if ( 'auto-draft' === $postarr['post_status'] ) {
		return $prevent;
	}

	// Validate post
	$validated = incassoos_validate_post( $postarr );

	// When the asset invalidates, prevent inserting
	if ( is_wp_error( $validated ) ) {
		$prevent = true;
	}

	return $prevent;
}

/**
 * Modify the post's data before insert or update
 *
 * @since 1.0.0
 *
 * @global WPDB $wpdb
 *
 * @param  array $data    Post insert data
 * @param  array $postarr Original post insert data
 * @return array          Post insert data
 */
function incassoos_insert_post_data( $data, $postarr ) {
	global $wpdb;

	// Order
	if ( incassoos_get_order_post_type() === $postarr['post_type'] ) {

		// Orders cannot be in draft state. Auto-upgrade draft posts to published posts
		if ( 'draft' === $data['post_status'] ) {
			$data['post_status'] = 'publish';
			$data['post_date_gmt'] = get_gmt_from_date( $data['post_date'] );
		}

		// When saving from post-new.php or other contexts
		if ( 'auto-draft' !== $data['post_status'] && ! empty( $postarr['ID'] ) ) {

			// Set post name to its ID
			$data['post_name'] = 'order-' . $postarr['ID'];

			/* translators: post ID */
			$data['post_title'] = sprintf( esc_html__( 'Order %d', 'incassoos' ), $postarr['ID'] );
		}
	}

	// Product
	if ( incassoos_get_product_post_type() === $postarr['post_type'] ) {

		// Push product to end of the list
		if ( 0 === $data['menu_order'] ) {
			$max_order = $wpdb->get_var( $wpdb->prepare( "SELECT MAX( menu_order ) FROM {$wpdb->posts} WHERE post_type = %s", $postarr['post_type'] ) );
			$data['menu_order'] = absint( $max_order ) + 1;
		}
	}

	return $data;
}

/**
 * Output the HTML select dropdown for posts
 *
 * @since 1.0.0
 *
 * @param  array  $args Optional. Additional dropdown arguments.
 */
function incassoos_dropdown_posts( $args = array() ) {
	echo incassoos_get_dropdown_posts( $args );
}

/**
 * Return the HTML select dropdown for posts
 *
 * @see wp_dropdown_pages()
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_dropdown_posts'
 *
 * @param  array  $args Optional. Additional dropdown arguments.
 * @return string HTML select dropdown.
 */
function incassoos_get_dropdown_posts( $args = array() ) {
	$defaults = array(
		'name' => 'page_id',
		'id' => '',
		'class' => '',
		'selected' => 0,
		'show_option_none' => '',
		'show_option_no_change' => '',
		'option_none_value' => '',
		'value_field' => 'ID',

		// Query args
		'posts' => array(),
		'posts_per_page' => -1,
		'suppress_filters' => false,
	);

	$r = wp_parse_args( $args, $defaults );
	$posts = $r['posts'];

	if ( empty( $posts ) ) {
		// The name arg queries for `post_name` so omit it
		$qr = $r; unset( $qr['name'] );
		$posts = get_posts( $qr );
	}

	$output = '';
	// Back-compat with old system where both id and name were based on $name argument
	if ( empty( $r['id'] ) ) {
		$r['id'] = $r['name'];
	}

	if ( ! empty( $posts ) ) {
		$class = '';
		if ( ! empty( $r['class'] ) ) {
			$class = " class='" . esc_attr( $r['class'] ) . "'";
		}

		$output = "<select name='" . esc_attr( $r['name'] ) . "'" . $class . " id='" . esc_attr( $r['id'] ) . "'>\n";
		if ( $r['show_option_no_change'] ) {
			$output .= "\t<option value=\"-1\">" . $r['show_option_no_change'] . "</option>\n";
		}
		if ( $r['show_option_none'] ) {
			$output .= "\t<option value=\"" . esc_attr( $r['option_none_value'] ) . '">' . $r['show_option_none'] . "</option>\n";
		}
		$output .= walk_page_dropdown_tree( $posts, 0, $r );
		$output .= "</select>\n";
	}

	return apply_filters( 'incassoos_get_dropdown_posts', $output, $args, $posts );
}

/**
 * Make a duplicate post of the post
 *
 * @since 1.0.0
 *
 * @global WPDB $wpdb
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return int|bool New post ID or False when unsuccessful.
 */
function incassoos_duplicate_post( $post = 0 ) {
	global $wpdb;

	$post        = get_post( $post );
	$new_post_id = false;
	$tax_input   = array();
	$meta_input  = array();

	if ( $post ) {

		// Run action before duplicating
		do_action( 'incassoos_duplicate_post', $post );

		// Collect post taxonomies
		foreach ( get_object_taxonomies( $post->post_type ) as $taxonomy ) {
			$tax_input[ $taxonomy ] = wp_get_object_terms( $post->ID, $taxonomy, array( 'fields' => 'ids' ) );
		}

		// Collect post meta
		$meta_input = get_post_meta( $post->ID );

		// Remove unique metas
		foreach ( $meta_input as $meta_key => $meta_value ) {
			if ( in_array( $meta_key, array( '_edit_last', '_edit_lock', '_wp_old_slug' ) ) ) {
				unset( $meta_input[ $meta_key ] );
			}
		}

		// Setup duplicate post details
		$args = apply_filters( 'incassoos_duplicate_post_args', array(
			'post_title'     => incassoos_increment_post_title( $post ),
			'post_content'   => $post->post_content,
			'post_excerpt'   => $post->post_excerpt,
			'post_author'    => get_current_user_id(),
			'post_type'      => $post->post_type,
			'post_status'    => 'publish',
			'post_parent'    => 0,
			'post_name'      => null,
			'menu_order'     => $post->menu_order,
			'post_password'  => $post->post_password,
			'comment_status' => $post->comment_status,
			'ping_status'    => $post->ping_status,
			'to_ping'        => $post->to_ping,
			'tax_input'      => $tax_input
			// Do not provide meta_input to `wp_insert_post()` because it uses `update_post_meta()`
			// when `add_post_meta()` is preferred. This is done below after the post is created.
		), $post );

		// Add meta input to the root args for use in custom plugin validation
		foreach ( $meta_input as $meta_key => $meta_values ) {

			// Values from `get_post_meta()` come in arrays. Single meta values should not be handled as arrays.
			$args[ $meta_key ] = 1 === count( $meta_values ) ? maybe_unserialize( $meta_values[0] ) : $meta_values;
		}

		// Insert the new post
		$new_post_id = wp_insert_post( $args, true );

		if ( $new_post_id && ! is_wp_error( $new_post_id ) ) {

			// Add post meta. Values from `get_post_meta()` come in arrays.
			foreach ( $meta_input as $meta_key => $meta_values ) {
				foreach ( $meta_values as $meta_value ) {
					add_post_meta( $new_post_id, $meta_key, maybe_unserialize( $meta_value ) );
				}
			}

			// Run action after duplicating
			do_action( 'incassoos_duplicated_post', $new_post_id, $post );
		}
	}

	// Parse errors to false
	if ( is_wp_error( $new_post_id ) ) {
		$new_post_id = false;
	}

	return $new_post_id;
}

/**
 * Return an incremented version of the post's title
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_increment_post_title'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return string Incremented post title
 */
function incassoos_increment_post_title( $post = 0 ) {
	$post      = get_post( $post );
	$new_title = '';

	if ( $post ) {
		$counter = 1;

		// Find part after the last space
		$pos = strrpos( $post->post_title, ' ' );
		if ( $pos ) $pos++; // Skip over space
		$end_part = ( false !== $pos ) ? substr( $post->post_title, $pos ) : false;

		// Increment numeric value
		if ( is_numeric( $end_part ) ) {
			$counter   = (int) $end_part + 1;
			$new_title = substr_replace( $post->post_title, $counter, $pos );

			// Check whether the new title already exists
			while ( get_page_by_title( $new_title, OBJECT, $post->post_type ) ) {
				$counter++;
				$new_title = substr_replace( $post->post_title, $counter, $pos );
			}

		// Increment roman numeral
		} elseif ( incassoos_is_roman( $end_part ) ) {
			$islower     = $end_part === strtolower( $end_part );
			$counter     = incassoos_roman2int( $end_part ) + 1;
			$replacement = incassoos_int2roman( $counter );
			$new_title   = substr_replace( $post->post_title, $islower ? strtolower( $replacement ) : $replacement, $pos );

			// Check whether the new title already exists
			while ( get_page_by_title( $new_title, OBJECT, $post->post_type ) ) {
				$counter++;
				$replacement = incassoos_int2roman( $counter );
				$new_title = substr_replace( $post->post_title, $islower ? strtolower( $replacement ) : $replacement, $pos );
			}

		// Append just the counter
		} else {
			$new_title = $post->post_title . " {$counter}";
		}
	}

	return apply_filters( 'incassoos_increment_post_title', $new_title, $post );
}

/** Taxonomies ****************************************************************/

/**
 * Return the set of plugin taxonomy names
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_plugin_taxonomies'
 *
 * @return array Plugin taxonomies
 */
function incassoos_get_plugin_taxonomies() {
	return apply_filters( 'incassoos_get_plugin_taxonomies', array(
		incassoos_get_activity_cat_tax_id(),
		incassoos_get_occasion_type_tax_id(),
		incassoos_get_product_cat_tax_id()
	) );
}

/**
 * Return whether the given taxonomy belongs to the plugin
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_is_plugin_taxonomy'
 *
 * @param  string $taxonomy Optional. Taxonomy to check. Defaults to empty string.
 * @return bool Is this a plugin taxonomy?
 */
function incassoos_is_plugin_taxonomy( $taxonomy = '' ) {

	// Check taxonomy
	$is = in_array( $taxonomy, incassoos_get_plugin_taxonomies(), true );

	return apply_filters( 'incassoos_is_plugin_taxonomy', $is, $taxonomy );
}

/**
 * Return plugin taxonomies mapped to post types
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_plugin_taxonomy_post_types'
 *
 * @return array Map of taxonomies to post types
 */
function incassoos_get_plugin_taxonomy_post_types() {
	return apply_filters( 'incassoos_get_plugin_taxonomy_post_types', array(
		incassoos_get_activity_cat_tax_id()  => incassoos_get_activity_post_type(),
		incassoos_get_occasion_type_tax_id() => incassoos_get_occasion_post_type(),
		incassoos_get_product_cat_tax_id()   => incassoos_get_product_post_type()
	) );
}

/**
 * Modify the term link
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_filter_term_link'
 *
 * @param  string $url Term url
 * @param  WP_Term $term Term object
 * @param  string $taxonomy Taxonomy name
 * @return string Term url
 */
function incassoos_filter_term_link( $url, $term, $taxonomy ) {

	// Concerning plugin taxonomies
	if ( incassoos_is_plugin_taxonomy( $taxonomy ) ) {

		// Map taxonomies to post types
		$tax_map = incassoos_get_plugin_taxonomy_post_types();

		// Define base admin url
		if ( isset( $tax_map[ $taxonomy ] ) ) {
			$url = add_query_arg( array(
				'post_type' => $tax_map[ $taxonomy ],
				$taxonomy   => $term->term_id
			), admin_url( 'edit.php' ) );
		}
	}

	return apply_filters( 'incassoos_filter_term_link', $url, $term, $taxonomy );
}

/**
 * Process term meta when saving a taxonomy term
 *
 * @since 1.0.0
 *
 * @param  int    $term_id  Term ID.
 * @param  int    $tt_id    Term taxonomy ID.
 * @param  string $taxonomy Taxonomy name.
 */
function incassoos_save_term_meta( $term_id, $tt_id, $taxonomy ) {

	// Occasion Type
	if ( incassoos_get_occasion_type_tax_id() === $taxonomy ) {

		// Term default as a checkbox
		if ( ! isset( $_POST['term-default'] ) ) {
			delete_term_meta( $term_id, '_default' );
		} else {
			foreach ( incassoos_get_occasion_types() as $term ) {
				delete_term_meta( $term->term_id, '_default' );
			}
			update_term_meta( $term_id, '_default', 1 );
		}
	}
}

/**
 * Return whether the given term is the default
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_is_default_term'
 *
 * @param  WP_Term|int $term Term object or id.
 * @return bool Is this a default term?
 */
function incassoos_is_default_term( $term ) {
	$term = get_term( $term );
	$is   = false;

	if ( $term && ! is_wp_error( $term ) ) {
		$is = (bool) get_term_meta( $term->term_id, '_default', true );
	}

	return (bool) apply_filters( 'incassoos_is_default_term', $is, $term );
}

/** Notes *********************************************************************/

/**
 * Output the post's notes
 *
 * @since 1.0.0
 *
 * @param  WP_Post|int $post Optional. Post object or ID. Defaults to the current post.
 */
function incassoos_the_post_notes( $post = 0 ) {
	echo incassoos_get_post_notes( $post );
}

/**
 * Return the post's notes
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_post_notes'
 *
 * @param  WP_Post|int $post Optional. Post object or ID. Defaults to the current post.
 * @return string Post notes.
 */
function incassoos_get_post_notes( $post = 0 ) {
	$post  = get_post( $post );
	$notes = '';

	// Get post notes from excerpt
	if ( $post && post_type_supports( $post->post_type, 'incassoos-notes' ) ) {
		$notes = $post->post_excerpt;
	}

	return apply_filters( 'incassoos_get_post_notes', $notes, $post );
}

/** Menus *********************************************************************/

/**
 * Return the available custom plugin nav menu items
 *
 * @since 1.0.0
 *
 * @return array Custom nav menu item data objects
 */
function incassoos_nav_menu_get_items() {

	// Try to return items from cache
	if ( ! empty( incassoos()->wp_nav_menu_items ) ) {
		return incassoos()->wp_nav_menu_items;
	} else {
		incassoos()->wp_nav_menu_items = new stdClass;
	}

	// Setup nav menu items
	$items = (array) apply_filters( 'incassoos_nav_menu_get_items', array(

		// Application
		'app' => array(
			'title'       => esc_html_x( 'Application', 'Menu item label', 'incassoos' ),
			'url'         => incassoos_get_app_url(),
			'is_current'  => incassoos_is_app()
		),

		// Administration
		'admin' => array(
			'title'       => esc_html_x( 'Administration', 'Menu item label', 'incassoos' ),
			'url'         => incassoos_get_admin_url()
		)
	) );

	// Set default arguments
	foreach ( $items as $object => &$item ) {
		$item = (object) wp_parse_args( $item, array(
			'id'          => "incassoos-{$object}",
			'object'      => $object,
			'title'       => '',
			'type'        => 'incassoos',
			'type_label'  => esc_html_x( 'Incassoos', 'Menu type label', 'incassoos' ),
			'url'         => '',
			'is_current'  => false,
			'is_parent'   => false,
			'is_ancestor' => false,
		) );
	}

	// Assign items to global
	incassoos()->wp_nav_menu_items = $items;

	return $items;
}

/**
 * Setup details of nav menu item for plugin pages
 *
 * @since 1.0.0
 *
 * @param WP_Post $menu_item Nav menu item object
 * @return WP_Post Nav menu item object
 */
function incassoos_setup_nav_menu_item( $menu_item ) {

	// Plugin page
	if ( 'incassoos' === $menu_item->type ) {

		// This is a registered custom menu item
		if ( $item = wp_list_filter( incassoos_nav_menu_get_items(), array( 'object' => $menu_item->object ) ) ) {
			$item = reset( $item );

			// Item doesn't come from the DB
			if ( ! isset( $menu_item->post_type ) ) {
				$menu_item->ID = -1;
				$menu_item->db_id = 0;
				$menu_item->menu_item_parent = 0;
				$menu_item->object_id = -1;
				$menu_item->target = '';
				$menu_item->attr_title = '';
				$menu_item->description = '';
				$menu_item->classes = '';
				$menu_item->xfn = '';
			}

			// Set item classes
			if ( ! is_array( $menu_item->classes ) ) {
				$menu_item->classes = array();
			}

			// Set item details
			$menu_item->type_label = $item->type_label;
			$menu_item->url        = $item->url;

			// This is the current page
			if ( $item->is_current ) {
				$menu_item->classes[] = 'current_page_item';
				$menu_item->classes[] = 'current-menu-item';

			// This is the parent page
			} elseif ( $item->is_parent ) {
				$menu_item->classes[] = 'current_page_parent';
				$menu_item->classes[] = 'current-menu-parent';

			// This is an ancestor page
			} elseif ( $item->is_ancestor ) {
				$menu_item->classes[] = 'current_page_ancestor';
				$menu_item->classes[] = 'current-menu-ancestor';
			}
		}

		// Prevent rendering when the user has no access
		if ( empty( $menu_item->url ) ) {
			$menu_item->_invalid = true;
		}

		// Enable plugin filtering
		$menu_item = apply_filters( 'incassoos_setup_nav_menu_item', $menu_item );

		// Prevent rendering when the user has no access
		if ( empty( $menu_item->url ) ) {
			$menu_item->_invalid = true;
		}
	}

	return $menu_item;
}

/**
 * Add custom plugin pages to the available nav menu items
 *
 * @see wp_nav_menu_item_post_type_meta_box()
 *
 * @since 1.0.0
 *
 * @global int        $_nav_menu_placeholder
 * @global int|string $nav_menu_selected_id
 *
 * @param string $object Not used.
 * @param array  $box {
 *     Post type menu item meta box arguments.
 *
 *     @type string       $id       Meta box 'id' attribute.
 *     @type string       $title    Meta box title.
 *     @type string       $callback Meta box display callback.
 *     @type WP_Post_Type $args     Extra meta box arguments (the post type object for this meta box).
 * }
 */
function incassoos_nav_menu_metabox( $object, $box ) {
	global $nav_menu_selected_id;

	$walker = new Walker_Nav_Menu_Checklist();
	$args   = array( 'walker' => $walker );

	$tab_name     = 'incassoos-tab';
	$removed_args = array(
		'action',
		'customlink-tab',
		'edit-menu-item',
		'menu-item',
		'page-tab',
		'_wpnonce',
	);
	$view_all_url = esc_url( add_query_arg( $tab_name, 'all', remove_query_arg( $removed_args ) ) );

	?>
	<div id="posttype-incassoos" class="posttypediv">

		<div id="incassoos-all" class="tabs-panel tabs-panel-view-all tabs-panel-active" role="region" aria-label="<?php echo esc_attr_x( 'All items', 'Menu administration label', 'incassoos' ); ?>" tabindex="0">
			<ul id="incassooschecklist" data-wp-lists="list:incassoos" class="categorychecklist form-no-clear">
				<?php echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', incassoos_nav_menu_get_items() ), 0, (object) $args ); ?>
			</ul>
		</div><!-- /.tabs-panel -->

		<p class="button-controls wp-clearfix" data-items-type="posttype-incassoos">
			<span class="list-controls hide-if-no-js">
				<input type="checkbox"<?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?> id="<?php echo esc_attr( $tab_name ); ?>" class="select-all" />
				<label for="<?php echo esc_attr( $tab_name ); ?>"><?php _e( 'Select All' ); ?></label>
			</span>

			<span class="add-to-menu">
				<input type="submit"<?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?> class="button submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu' ); ?>" name="add-incassoos-menu-item" id="submit-posttype-incassoos" />
				<span class="spinner"></span>
			</span>
		</p>

	</div><!-- /.posttypediv -->
	<?php
}

/**
 * Set plugin item navs for the Customizer
 *
 * @since 1.0.0
 *
 * @param array $item_types Nav item types
 * @return array Nav item types
 */
function incassoos_customize_nav_menu_set_item_types( $item_types ) {

	// Plugin pages
	$item_types['incassoos'] = array(
		'title'      => esc_html__( 'Incassoos', 'incassoos' ),
		'type_label' => esc_html_x( 'Incassoos', 'Menu type label', 'incassoos' ),
		'type'       => 'incassoos',
		'object'     => 'incassoos_nav'
	);

	return $item_types;
}

/**
 * Add custom plugin pages to the available menu items in the Customizer
 *
 * @since 1.0.0
 *
 * @param array $items The array of menu items.
 * @param string $type The object type.
 * @param string $object The object name.
 * @param int $page The current page number.
 * @return array Menu items
 */
function incassoos_customize_nav_menu_available_items( $items, $type, $object, $page ) {

	// Plugin pages - first query only
	if ( 'incassoos' === $type && 0 === $page ) {

		// Add plugin items
		foreach ( incassoos_nav_menu_get_items() as $item ) {
			$items[] = (array) $item; 
		}
	}

	return $items;
}

/**
 * Add custom plugin pages to the searched menu items in the Customizer
 *
 * @since 1.0.0
 *
 * @param array $items The array of menu items.
 * @param array $args Includes 'pagenum' and 's' (search) arguments.
 * @return array Menu items
 */
function incassoos_customize_nav_menu_searched_items( $items, $args ) {

	// Define search context
	$search = strtolower( $args['s'] );
	$_items = incassoos_nav_menu_get_items();
	$titles = wp_list_pluck( $_items, 'title' );
	$words  = array( 'incassoos', 'app' );

	// Search query matches a part of the item titles
	foreach ( array_keys( array_filter( $titles, function( $title ) use ( $search ) {
		return false !== strpos( strtolower( $title ), $search );
	}) ) as $item_key ) {
		$items[] = (array) $_items[ $item_key ];
		unset( $_items[ $item_key ] );
	}

	// Search query matches a part of the provided words
	if ( array_filter( $words, function( $word ) use ( $search ) {
		return false !== strpos( $word, $search );
	}) ) {

		// Append all custom items
		foreach ( $_items as $item ) {
			$items[] = (array) $item;
		}
	}

	return $items;
}

/** Admin Bar *****************************************************************/

/**
 * Modify the admin bar
 *
 * @since 1.0.0
 *
 * @param WP_Admin_Bar $wp_admin_bar The admin bar object
 */
function incassoos_admin_bar_menu( $wp_admin_bar ) {

	// Link to application under Site
	if ( current_user_can( 'view_incassoos_application' ) ) {
		$wp_admin_bar->add_node(
			array(
				'parent' => 'site-name',
				'id'     => 'site-name-incassoos-app',
				'title'  => __( 'Incassoos Application', 'incassoos' ),
				'href'   => incassoos_get_app_url()
			)
		);
	}

	// Link to the plugin dashboard under Site
	if ( current_user_can( 'view_incassoos_dashboard' ) ) {
		$wp_admin_bar->add_node(
			array(
				'parent' => 'site-name',
				'id'     => 'site-name-incassoos-home',
				'title'  => __( 'Incassoos Home', 'incassoos' ),
				'href'   => incassoos_get_admin_url()
			)
		);
	}
}

/** REST **********************************************************************/

/**
 * Register plugin REST routes
 *
 * @since 1.0.0
 */
function incassoos_register_rest_routes() {

	// Load classes
	require_once( incassoos()->includes_dir . 'classes/class-incassoos-rest-settings-controller.php' );
	require_once( incassoos()->includes_dir . 'classes/class-incassoos-rest-jwt-auth-controller.php' );
	require_once( incassoos()->includes_dir . 'classes/class-incassoos-rest-occasions-controller.php' );
	require_once( incassoos()->includes_dir . 'classes/class-incassoos-rest-orders-controller.php' );
	require_once( incassoos()->includes_dir . 'classes/class-incassoos-rest-products-controller.php' );
	require_once( incassoos()->includes_dir . 'classes/class-incassoos-rest-consumers-controller.php' );
	require_once( incassoos()->includes_dir . 'classes/class-incassoos-rest-consumer-types-controller.php' );

	// Settings
	$controller = new Incassoos_REST_Settings_Controller();
	$controller->register_routes();

	// JWT Authentication
	$controller = new Incassoos_REST_JWT_Auth_Controller();
	$controller->register_routes();

	// Occasions
	$controller = new Incassoos_REST_Occasions_Controller();
	$controller->register_routes();

	// Orders
	$controller = new Incassoos_REST_Orders_Controller();
	$controller->register_routes();

	// Products
	$controller = new Incassoos_REST_Products_Controller();
	$controller->register_routes();

	// Consumers
	$controller = new Incassoos_REST_Consumers_Controller();
	$controller->register_routes();

	// Consumer Types
	$controller = new Incassoos_REST_Consumer_Types_Controller();
	$controller->register_routes();
}

/**
 * Return the plugin's REST namespace
 *
 * @since 1.0.0
 *
 * @return string REST namespace
 */
function incassoos_get_rest_namespace() {
	return apply_filters( 'incassoos_get_rest_namespace', 'incassoos/v1' );
}

/**
 * Return the REST base for the authorization component
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_settings_rest_base'
 *
 * @return string Settings REST base
 */
function incassoos_get_settings_rest_base() {
	return apply_filters( 'incassoos_get_settings_rest_base', 'settings' );
}

/**
 * Return the REST base for the authorization component
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_authorization_rest_base'
 *
 * @return string Authorization REST base
 */
function incassoos_get_authorization_rest_base() {
	return apply_filters( 'incassoos_get_authorization_rest_base', 'auth' );
}

/**
 * Return the REST base for the Occasions component
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_occasions_rest_base'
 *
 * @return string Occasions REST base
 */
function incassoos_get_occasions_rest_base() {
	return apply_filters( 'incassoos_get_occasions_rest_base', 'occasions' );
}

/**
 * Return the REST base for the Orders component
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_orders_rest_base'
 *
 * @return string Orders REST base
 */
function incassoos_get_orders_rest_base() {
	return apply_filters( 'incassoos_get_orders_rest_base', 'orders' );
}

/**
 * Return the REST base for the products component
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_products_rest_base'
 *
 * @return string Products REST base
 */
function incassoos_get_products_rest_base() {
	return apply_filters( 'incassoos_get_products_rest_base', 'products' );
}

/**
 * Return the REST base for the consumers component
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_consumers_rest_base'
 *
 * @return string Consumers REST base
 */
function incassoos_get_consumers_rest_base() {
	return apply_filters( 'incassoos_get_consumers_rest_base', 'consumers' );
}

/**
 * Return the REST base for the consumer types component
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_consumer_types_rest_base'
 *
 * @return string Consumer types REST base
 */
function incassoos_get_consumer_types_rest_base() {
	return apply_filters( 'incassoos_get_consumer_types_rest_base', 'consumer-types' );
}

/**
 * Return whether the current context is a REST request
 *
 * This exists as long as WP core does not have a similar function.
 *
 * @link https://core.trac.wordpress.org/ticket/42061
 *
 * @return bool Is this a REST request?
 */
function incassoos_doing_rest() {
	return defined( 'REST_REQUEST' ) && REST_REQUEST;
}

/**
 * Return the asset's REST url
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_rest_url'
 *
 * @param  string $object_type Object type name.
 * @return string Rest url
 */
function incassoos_get_rest_url( $object_type ) {

	// Define variable(s)
	$post_type = incassoos_get_object_post_type( $object_type );
	$namespace = incassoos_get_rest_namespace();
	$url       = '';

	if ( $post_type ) {
		$post_type = get_post_type_object( $post_type );
		$url = trailingslashit( get_rest_url( null, $namespace . '/' . $post_type->rest_base ) );
	} elseif ( 'consumer' === $object_type ) {
		$url = trailingslashit( get_rest_url( null, $namespace . '/' . incassoos_get_consumers_rest_base() ) );
	}

	return apply_filters( 'incassoos_get_rest_url', $url, $object_type );
}

/**
 * Return whether the REST request's route is in the plugin's namespace
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_match_rest_route_namespace'
 *
 * @param WP_REST_Request|string $request Optional. Route or REST request object. Defaults to the current rest route.
 * @param string $namespace Optional. Namespace to match. Defaults to the plugin's namespace.
 * @return bool Is this a plugin's route?
 */
function incassoos_match_rest_route_namespace( $route = null, $namespace = '' ) {

	// Define return value
	$retval = false;

	// Default to the base plugin's namespace
	if ( empty( $namespace ) ) {
		$namespace = incassoos_get_rest_namespace();
	}

	// Use the request's route
	if ( is_a( $route, 'WP_REST_Request' ) ) {
		$route = $route->get_route();

	// Use the current route
	} elseif ( null === $route && incassoos_doing_rest() ) {
		$route = untrailingslashit( $GLOBALS['wp']->query_vars['rest_route'] );
	}

	// Check the route's namespace
	if ( $route ) {
		$retval = ( 0 === strpos( $route, '/' . $namespace ) );
	}

	return (bool) apply_filters( 'incassoos_match_rest_route_namespace', $retval, $route, $namespace );
}

/**
 * Return the value for the object's REST field
 *
 * @since 1.0.0
 *
 * @param  array $object Request object
 * @param  string $field_name Request field name
 * @param  WP_REST_Request $request Current REST request
 * @return mixed|bool Field value or False when not found.
 */
function incassoos_get_rest_field( $object, $field_name, $request ) {
	$object_type = incassoos_get_object_type( $object['type'] );
	$callback    = "incassoos_get_{$object_type}_{$field_name}";

	if ( function_exists( $callback ) ) {
		return call_user_func( $callback, $object['id'] );
	}

	return false;
}

/**
 * Run the update logic for the object's REST field
 *
 * @since 1.0.0
 *
 * @param  array $field_request Field request data
 * @param  WP_Post $object Request object
 * @param  string $field_name Request field name
 * @param  WP_REST_Request $request Current REST request
 * @return bool Update success.
 */
function incassoos_update_rest_field( $field_request, $object, $field_name, $request ) {
	$object_type = incassoos_get_object_type( $object->post_type );
	$callback    = "incassoos_update_{$object_type}_{$field_name}";

	if ( function_exists( $callback ) ) {
		return call_user_func( $callback, $field_request, $object->ID );
	}

	return false;
}

/**
 * Remove properties from the defined REST schema
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_rest_remove_schema_properties'
 *
 * @param  array $schema     REST schema
 * @param  array $properties Properties to remove
 * @return array Filtered REST schema
 */
function incassoos_rest_remove_schema_properties( $schema, $properties = array() ) {
	$properties = apply_filters( 'incassoos_rest_remove_schema_properties', array_merge(
		$properties,
		array(
			'guid',
			'slug',
			'template',
			'type',
			'link'
		)
	) );

	foreach ( $properties as $property ) {
		unset( $schema['properties'][ $property ] );
	}

	return $schema;
}

/** Settings ******************************************************************/

/**
 * Register plugin settings
 *
 * @see incassoos_admin_register_settings()
 *
 * @since 1.0.0
 */
function incassoos_register_settings() {

	// Make settings functionality available
	require_once incassoos()->includes_dir . 'admin/settings.php';

	// Bail if no sections available
	$sections = incassoos_admin_get_settings_sections();
	if ( empty( $sections ) )
		return false;

	// Loop through sections
	foreach ( (array) $sections as $section_id => $section ) {

		// Only add section and fields if section has fields
		$fields = incassoos_admin_get_settings_fields_for_section( $section_id );
		if ( empty( $fields ) )
			continue;

		// Define section page
		if ( ! empty( $section['page'] ) ) {
			$page = $section['page'];
		} else {
			$page = 'incassoos';
		}

		// Loop through fields for this section
		foreach ( (array) $fields as $field_id => $field ) {

			// Set default sanitizer
			if ( ! isset( $field['sanitize_callback'] ) ) {
				$field['sanitize_callback'] = '';
			}

			// Register the setting
			register_setting( $page, $field_id, $field['sanitize_callback'] );
		}
	}
}

/**
 * Return the currency
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_currency'
 *
 * @param mixed $default Optional. Default return value.
 * @return string Currency
 */
function incassoos_get_currency( $default = 'USD' ) {
	return apply_filters( 'incassoos_get_currency', get_option( '_incassoos_currency', $default ) );
}

/**
 * Return the order time lock in minutes
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_order_time_lock'
 *
 * @param mixed $default Optional. Default return value.
 * @return int Order time lock in minutes
 */
function incassoos_get_order_time_lock( $default = 0 ) {
	return (int) apply_filters( 'incassoos_get_order_time_lock', get_option( '_incassoos_order_time_lock', $default ) );
}

/**
 * Return the Transaction description
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_transaction_description'
 *
 * @param mixed $default Optional. Default return value.
 * @return string Transaction description
 */
function incassoos_get_transaction_description( $default = '' ) {
	return apply_filters( 'incassoos_get_transaction_description', get_option( '_incassoos_transaction_description', $default ) );
}

/**
 * Return the Organization name
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_organization_name'
 *
 * @param mixed $default Optional. Default return value.
 * @return string Organization name
 */
function incassoos_get_organization_name( $default = '' ) {
	return apply_filters( 'incassoos_get_organization_name', get_option( '_incassoos_organization_name', $default ) );
}

/**
 * Return the Account Holder name
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_account_holder'
 *
 * @param mixed $default Optional. Default return value.
 * @return string Account Holder name
 */
function incassoos_get_account_holder( $default = '' ) {
	return apply_filters( 'incassoos_get_account_holder', get_option( '_incassoos_account_holder', $default ) );
}

/**
 * Return the Account IBAN
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_account_iban'
 *
 * @param  mixed $default Optional. Default return value.
 * @return string Account IBAN
 */
function incassoos_get_account_iban( $default = '' ) {
	return apply_filters( 'incassoos_get_account_iban', get_option( '_incassoos_account_iban', $default ) );
}

/**
 * Return the SEPA Creditor Identifier
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_sepa_creditor_id'
 *
 * @param mixed $default Optional. Default return value.
 * @return string SEPA Creditor Identifier
 */
function incassoos_get_sepa_creditor_id( $default = '' ) {
	return apply_filters( 'incassoos_get_sepa_creditor_id', get_option( '_incassoos_sepa_creditor_id', $default ) );
}

/**
 * Return the sender email address
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_sender_email_address'
 *
 * @param mixed $default Optional. Default return value.
 * @return string Sender email address
 */
function incassoos_get_sender_email_address( $default = '' ) {
	return apply_filters( 'incassoos_get_sender_email_address', get_option( '_incassoos_sender_email_address', $default ) );
}

/**
 * Return the custom email salutation
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_custom_email_salutation'
 *
 * @param mixed $default Optional. Default return value.
 * @return string Custom email salutation
 */
function incassoos_get_custom_email_salutation( $user, $default = '' ) {
	$value = get_option( '_incassoos_custom_email_salutation', $default );
	$user  = get_userdata( $user );

	// Parse user name
	if ( $user && $user->exists() ) {
		$value = str_replace( '{{USERNAME}}', $user->display_name, $value );
	}

	return apply_filters( 'incassoos_get_custom_email_salutation', $value, $user );
}

/**
 * Return the custom email closing
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_custom_email_closing'
 *
 * @param mixed $default Optional. Default return value.
 * @return string Custom email closing
 */
function incassoos_get_custom_email_closing( $default = '' ) {
	return apply_filters( 'incassoos_get_custom_email_closing', get_option( '_incassoos_custom_email_closing', $default ) );
}

/**
 * Return the default collection withdrawal delay in days
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_default_collection_withdrawal_delay'
 *
 * @param mixed $default Optional. Default return value.
 * @return int Default collection withdrawal delay
 */
function incassoos_get_default_collection_withdrawal_delay( $default = 5 ) {
	return (int) apply_filters( 'incassoos_get_default_collection_withdrawal_delay', get_option( '_incassoos_default_collection_withdrawal_delay', $default ) );
}

/**
 * Return the App's default Occasion Selector tab
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_app_get_default_occassion_selector_tab'
 *
 * @return string Occassion Selector tab id
 */
function incassoos_app_get_default_occassion_selector_tab() {
	return apply_filters( 'incassoos_app_get_default_occassion_selector_tab', 'create' );
}

/**
 * Return the App's default Occasion title
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_app_get_default_occasion_title'
 *
 * @return string Occassion title
 */
function incassoos_app_get_default_occasion_title() {
	return apply_filters( 'incassoos_app_get_default_occasion_title', __( 'Drinks', 'incassoos' ) );
}

/** Export ********************************************************************/

/**
 * Return the SEPA export type id
 *
 * @since 1.0.0
 *
 * @return string SEPA export type id
 */
function incassoos_get_sepa_export_type_id() {
	return incassoos()->sepa_export_type;
}

/**
 * Return the post consumers export type id
 *
 * @since 1.0.0
 *
 * @return string Post consumers export type id
 */
function incassoos_get_post_consumers_export_type_id() {
	return incassoos()->post_consumers_export_type;
}

/**
 * Return the contextual labels for the post consumers export type
 *
 * @since 1.0.0
 *
 * @param mixed $context Context. Expects a post object.
 * @return array Export type labels
 */
function incassoos_get_post_consumers_export_type_labels( $context = null ) {
	$post_type = get_post_type( $context );

	switch ( $post_type ) {

		// Activity
		case incassoos_get_activity_post_type() :
			$labels = array(
				'name'        => esc_html__( 'Post participants (csv)',   'incassoos' ),
				'export_file' => esc_html__( 'Export participants (csv)', 'incassoos' )
			);
			break;

		// Other
		default :
			$labels = array(
				'name'        => esc_html__( 'Post consumers (csv)',   'incassoos' ),
				'export_file' => esc_html__( 'Export consumers (csv)', 'incassoos' )
			);
	}

	return (array) apply_filters( 'incassoos_get_post_consumers_export_type_labels', $labels, $context );
}

/**
 * Return to show the post consumers export type for the context
 *
 * @since 1.0.0
 *
 * @param mixed $context Context. Expects a post object.
 * @return bool Show the export type?
 */
function incassoos_show_post_consumers_export_type( $context ) {

	// Match post types
	$show = in_array( get_post_type( $context ), array(
		incassoos_get_collection_post_type(),
		incassoos_get_activity_post_type(),
		incassoos_get_occasion_post_type()
	), true );

	// Require post to be collected
	if ( ! incassoos_is_post_collected( $context ) ) {
		$show = false;
	}

	return (bool) apply_filters( 'incassoos_show_post_consumers_export_type', $show, $context );
}

/**
 * Return the post consumptions export type id
 *
 * @since 1.0.0
 *
 * @return string Post consumptions export type id
 */
function incassoos_get_post_consumptions_export_type_id() {
	return incassoos()->post_consumptions_export_type;
}

/**
 * Return to show the post consumptions export type for the context
 *
 * @since 1.0.0
 *
 * @param mixed $context Context. Expects a post object.
 * @return bool Show the export type?
 */
function incassoos_show_post_consumptions_export_type( $context ) {
	$post_type = get_post_type( $context );
	$show      = false;

	// Require occasion to be locked
	if ( incassoos_get_occasion_post_type() === $post_type && incassoos_is_occasion_locked( $context ) ) {
		$show = true;
	}

	return (bool) apply_filters( 'incassoos_show_post_consumptions_export_type', $show, $context );
}

/**
 * Return the post products export type id
 *
 * @since 1.0.0
 *
 * @return string Post products export type id
 */
function incassoos_get_post_products_export_type_id() {
	return incassoos()->post_products_export_type;
}

/**
 * Return to show the post products export type for the context
 *
 * @since 1.0.0
 *
 * @param mixed $context Context. Expects a post object.
 * @return bool Show the export type?
 */
function incassoos_show_post_products_export_type( $context ) {
	$post_type = get_post_type( $context );
	$show      = false;

	// Require occasion to be locked
	if ( incassoos_get_occasion_post_type() === $post_type && incassoos_is_occasion_locked( $context ) ) {
		$show = true;

	// Require order to be locked
	} elseif ( incassoos_get_order_post_type() === $post_type && incassoos_is_order_locked( $context ) ) {
		$show = true;
	}

	return (bool) apply_filters( 'incassoos_show_post_products_export_type', $show, $context );
}

/**
 * Return the consumers export type id
 *
 * @since 1.0.0
 *
 * @return string Consumers export type id
 */
function incassoos_get_consumers_export_type_id() {
	return incassoos()->consumers_export_type;
}

/**
 * Register a Collection export type
 *
 * @since 1.0.0
 *
 * @uses apply_filters Calls 'incassoos_register_export_type'
 *
 * @param  string $export_type_id Export type id.
 * @param  array  $args           Optional. Export type parameters.
 * @return bool Registration success.
 */
function incassoos_register_export_type( $export_type_id, $args = array() ) {
	$plugin         = incassoos();
	$export_type_id = sanitize_title( $export_type_id );

	// Bail when type param is invalid
	if ( empty( $export_type_id ) ) {
		return false;
	}

	// Keep original arguments
	$original_args = $args;

	// Parse defaults
	$args['id'] = $export_type_id;
	$args = wp_parse_args( $args, array(
		'labels'                 => array(),
		'labels_callback'        => '',
		'class_name'             => '',
		'class_file'             => '',
		'show_in_list_callback'  => '__return_true',
		'require_decryption_key' => false
	) );

	// Parse labels
	if ( is_callable( $args['labels_callback'] ) ) {
		$args['labels'] = (array) call_user_func( $args['labels_callback'] );
	}

	$args['labels'] = wp_parse_args( $args['labels'], array(
		'name'        => ucfirst( $export_type_id ),
		'export_file' => sprintf( esc_html__( 'Export %s', 'incassoos' ), $export_type_id )
	) );

	// Allow filtering
	$export_type = apply_filters( 'incassoos_register_export_type', $args, $export_type_id, $original_args );

	// Define list of export types
	if ( ! isset( $plugin->export_types ) ) {
		$plugin->export_types = array();
	}

	// Add type to list of types
	$plugin->export_types[ $export_type_id ] = (object) $export_type;

	return true;
}

/**
 * Unregister a export type
 *
 * @since 1.0.0
 *
 * @param  string $export_type_id Export type id.
 * @return bool Unregistration success.
 */
function incassoos_unregister_export_type( $export_type_id ) {
	unset( incassoos()->export_types[ $export_type_id ] );

	return true;
}

/**
 * Return the export type object
 *
 * @since 1.0.0
 *
 * @uses apply_filters Calls 'incassoos_get_export_type'
 *
 * @param string $export_type_id Export type id or label.
 * @param mixed $context Optional. Context for custom setup of export type.
 * @return object|bool Export type object or False when not found.
 */
function incassoos_get_export_type( $export_type_id, $context = null ) {
	$plugin      = incassoos();
	$type_id     = sanitize_title( $export_type_id );
	$type_object = false;

	if ( ! isset( $plugin->export_types ) ) {
		$plugin->export_types = array();
	}

	// Get type by id
	if ( isset( $plugin->export_types[ $type_id ] ) ) {
		$type_object = $plugin->export_types[ $type_id ];

	// Get type by label
	} elseif ( $type_id = array_search( $export_type_id, wp_list_pluck( $plugin->export_types, 'label' ) ) ) {
		$type_object = $plugin->export_types[ $type_id ];
	}

	// Consider the context
	if ( $type_object && null !== $context ) {

		// Apply contextual labels
		if ( is_callable( $type_object->labels_callback ) ) {
			$type_object->labels = call_user_func_array( $type_object->labels_callback, array( $context ) );
		}
	}

	return apply_filters( 'incassoos_get_export_type', $type_object, $export_type_id, $context );
}

/**
 * Return whether the export type exists
 *
 * @since 1.0.0
 *
 * @param  string $export_type_id Export type id or label
 * @return bool Does export type exist?
 */
function incassoos_export_type_exists( $export_type_id ) {
	return (bool) incassoos_get_export_type( $export_type_id );
}

/**
 * Return the ids of all defined export types
 *
 * @since 1.0.0
 *
 * @return array Export type ids
 */
function incassoos_get_export_type_ids() {
	return array_keys( incassoos()->export_types );
}

/**
 * Return the objects of the defined export types based on context
 *
 * @since 1.0.0
 *
 * @param mixed $context Optional. Context for custom setup of export type.
 * @return array Export type objects
 */
function incassoos_get_export_types( $context = null ) {
	$export_types = array();

	foreach ( incassoos_get_export_type_ids() as $export_type_id ) {
		$export_type = incassoos_get_export_type( $export_type_id, $context );
		$callback = $export_type->show_in_list_callback;

		if ( null === $context || ( is_callable( $callback ) && call_user_func_array( $callback, array( $context ) ) ) ) {
			$export_types[ $export_type_id ] = $export_type;
		}
	}

	return $export_types;
}

/**
 * Output the export type label
 *
 * @since 1.0.0
 *
 * @param  string $export_type_id Export type id
 * @param  string $label_type     Optional. Label type key. Defaults to 'name'.
 */
function incassoos_the_export_type_label( $export_type_id, $label_type = 'name' ) {
	echo incassoos_get_export_type_label( $export_type_id, $label_type );
}

/**
 * Return the export type label
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_export_type_label'
 *
 * @param  string $export_type_id Export type id
 * @param  string $label_type     Optional. Label type key. Defaults to 'name'.
 * @return string Export type label
 */
function incassoos_get_export_type_label( $export_type_id, $label_type = 'name' ) {
	$export_type = incassoos_get_export_type( $export_type_id );
	$label       = ucfirst( $export_type_id );

	if ( $export_type && isset( $export_type->labels[ $label_type ] ) ) {
		$label = $export_type->labels[ $label_type ];
	}

	return apply_filters( 'incassoos_get_export_type_label', $label, $export_type, $label_type );
}

/**
 * Return whether the export type requires the decryption key
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_export_type_is_decryption_key_required'
 *
 * @param  string $export_type_id Export type id
 * @return bool Export type requires the decryption key
 */
function incassoos_get_export_type_is_decryption_key_required( $export_type_id ) {
	$export_type = incassoos_get_export_type( $export_type_id );
	$is_required = false;

	// Only require the decryption key when encryption is enabled and explictly required
	if ( $export_type && incassoos_is_encryption_enabled() ) {
		$is_required = true === $export_type->require_decryption_key;
	}

	return (bool) apply_filters( 'incassoos_get_export_type_is_decryption_key_required', $is_required, $export_type );
}

/**
 * Return whether the export type optionally uses the decryption key
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_export_type_is_decryption_key_optional'
 *
 * @param  string $export_type_id Export type id
 * @return bool Export type optionally uses the decryption key
 */
function incassoos_get_export_type_is_decryption_key_optional( $export_type_id ) {
	$export_type = incassoos_get_export_type( $export_type_id );
	$is_optional = false;

	// Only require the decryption key when encryption is enabled and optionally required
	if ( $export_type && incassoos_is_encryption_enabled() ) {
		$is_optional = 'optional' === $export_type->require_decryption_key;
	}

	return (bool) apply_filters( 'incassoos_get_export_type_is_decryption_key_optional', $is_optional, $export_type );
}

/**
 * Store file data for a subsequent export
 *
 * This is used when transferring export details between page calls.
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_save_export_details_expiration'
 *
 * @param  array $export_details Export details to be saved
 * @return string|bool File identifier when saved, false when saving failed
 */
function incassoos_save_export_details( $export_details = array() ) {

	// Bail when data is not a non-empty array
	if ( empty( $export_details ) || ! is_array( $export_details ) ) {
		return false;
	}

	// Generate identifier for the file
	$file_id = wp_generate_uuid4();

	// Setup encryption parameters. Nonces are user- and time-dependent, but are only 10 chars long.
	// Encryption requires the intialization vector (iv) to be 16 chars long.
	$nonce = wp_create_nonce( "incassoos_export_details-{$file_id}" );
	$iv    = substr( wp_hash( $nonce ), 0, 16 );

	// Encrypt export details for it may contain private info
	$export_details = base64_encode( openssl_encrypt( maybe_serialize( $export_details ), 'AES-256-CBC', $nonce, 0, $iv ) );

	// Make export details expire. Default to 15 minutes.
	$expiration = apply_filters( 'incassoos_save_export_details_expiration', 15 * MINUTE_IN_SECONDS );
	$success    = set_transient( $file_id, $export_details, $expiration );

	// Bail when storing the transient failed
	if ( ! $success ) {
		return false;
	}

	return $file_id;
}

/**
 * Get export details for a previously initiated export
 *
 * This is used when transferring export details between page calls.
 *
 * @since 1.0.0
 *
 * @param  string $file_id File identifier
 * @return array|bool Export details or false when details were not found
 */
function incassoos_get_export_details( $file_id ) {

	// Bail when the file id is invalid
	if ( ! wp_is_uuid( $file_id, 4 ) ) {
		return false;
	}

	$transient = get_transient( $file_id );

	// Bail when the transient was not found
	if ( ! $transient ) {
		return false;
	}

	// Setup encryption parameters. Nonces are user- and time-dependent, but are only 10 chars long.
	// Decryption requires the intialization vector (iv) to be 16 chars long.
	$nonce = wp_create_nonce( "incassoos_export_details-{$file_id}" );
	$iv    = substr( wp_hash( $nonce ), 0, 16 );

	// Decrypt export details
	$export_details = maybe_unserialize( openssl_decrypt( base64_decode( $transient ), 'AES-256-CBC', $nonce, 0, $iv ) );

	return $export_details;
}

/**
 * Delete export details for a previously initiated export
 *
 * @since 1.0.0
 *
 * @param  string $file_id File identifier
 * @return bool Were the export details deleted successfully?
 */
function incassoos_delete_export_details( $file_id ) {

	// Bail when the file id is invalid
	if ( ! wp_is_uuid( $file_id, 4 ) ) {
		return false;
	}

	return delete_transient( $file_id );
}

/** Email *********************************************************************/

/**
 * Send an email in the context of the plugin
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_send_email_args'
 *
 * @param  array  $args Email parameters
 * @return bool Was the email sent?
 */
function incassoos_send_email( $args = array() ) {
	$original_args = $args;

	// Set mail-to to the provided user
	if ( ! empty( $args['user_id'] ) && empty( $args['to'] ) ) {
		$user = get_user_by( 'id', $args['user_id'] );
		if ( $user ) {
			$args['to'] = $user->user_email;
		}
	}

	// Parse default attributes
	$args = wp_parse_args( $args, array(
		'to'          => incassoos_get_sender_email_address(), // By default send to self, for safety reasons
		'from'        => incassoos_get_sender_email_address(),
		'from_name'   => incassoos_get_account_holder(),
		'subject'     => '',
		'message'     => '',
		'headers'     => array(),
		'attachments' => array()
	) );

	// Set the From header
	$args['headers']['from'] = sprintf( ! empty( $args['from_name'] ) ? 'From: "%s" <%s>' : 'From: %2$s', $args['from_name'], $args['from'] );

	// Assume all mails are in HTML
	$args['headers']['content-type'] = 'Content-Type: text/html';

	$args        = apply_filters( 'incassoos_send_email_args', $args, $original_args );
	$to          = $args['to'];
	$subject     = $args['subject'];
	$message     = $args['message'];
	$headers     = $args['headers'];
	$attachments = $args['attachments'];

	return wp_mail( $to, $subject, $message, $headers, $attachments );
}

/** Files *********************************************************************/

/**
 * Process a text file and offer it for download
 *
 * @since 1.0.0
 *
 * @param  mixed $file File creator or file content.
 * @param  string $filename Optional. Name of the downloaded file.
 */
function incassoos_download_text_file( $file, $filename = '' ) {

	// Bail when the file parameter is missing
	if ( ! $file ) {
		return false;
	}

	// File exporter
	if ( is_a( $file, 'Incassoos_File_Exporter' ) && method_exists( $file, 'get_file' ) ) {
		$exporter = true;
		$content = $file->get_file();

		// Use creator filename
		if ( ! $filename && method_exists( $file, 'get_filename' ) ) {
			$filename = $file->get_filename();
		}

	// File content
	} elseif ( is_string( $file ) && $filename ) {
		$exporter = false;
		$content = $file;

	// Bail when the parameters are invalid
	} else {
		return false;
	}

	// Do the download
	if ( ! headers_sent() ) {

		// Send headers from file exporter
		if ( $exporter ) {
			$file->send_headers();
		} else {
			nocache_headers();
			header( 'Robots: none' );
		}

		header( 'Content-Type: ' . incassoos_get_file_type( $filename ) . '; charset=utf-8' );
		header( 'Content-Description: File Transfer' );
		header( 'Content-Disposition: inline; filename="' . $filename . '"' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Content-Length: ' . strlen( $content ) );

		// Output file content
		echo $content;
		exit;
	}
}

/**
 * Return the file's mime type
 *
 * @since 1.0.0
 *
 * @param  string $filename Filename to read the type from
 * @return string File mime type
 */
function incassoos_get_file_type( $filename ) {
	$parts = explode( '.', $filename );
	$ext   = $parts[ count( $parts ) - 1 ];
	$type  = '';

	foreach ( wp_get_mime_types() as $exts => $mime ) {
		if ( in_array( $ext, explode( '|', $exts ) ) ) {
			$type = $mime;
		}
	}

	// Default to text
	if ( ! $type ) {
		if ( 'xml' === $ext ) {
			$type = 'text/xml';
		} else {
			$type = 'text/plain';
		}
	}

	return $type;
}

/**
 * Wrapper for set_time_limit
 *
 * @since 1.0.0
 *
 * @param int $limit Time limit in seconds
 */
function incassoos_set_time_limit( $limit = 0 ) {
	if ( function_exists( 'set_time_limit' ) && false === strpos( ini_get( 'disable_functions' ), 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
		@set_time_limit( $limit );
	}
}

/** Security ******************************************************************/

/**
 * Return whether encryption through libsodium is supported in the
 * current installation.
 *
 * Checks for the presence of the 'sodium_crypto_box_seal' function. This
 * function should either be available through the libsodium PECL extension,
 * as part of the 'sodium_compat' library present in WP 5.2+ or part of
 * PHP 7.2+ by default.
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_is_encryption_supported'
 * @return bool Is encryption supported?
 */
function incassoos_is_encryption_supported() {
	return apply_filters( 'incassoos_is_encryption_supported', function_exists( 'sodium_crypto_box_seal' ) );
}

/**
 * Enable encryption
 *
 * Generates encryption keys for the plugin's primary encryption processes.
 * The encryption (public) key is stored in the site's options. The decryption
 * (private) key should not be stored in the database and be saved by the user
 * generating the keys.
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'incassoos_enable_encryption'
 *
 * @return bool|WP_Error True on succes or error object
 */
function incassoos_enable_encryption() {

	// Bail when encryption is already enabled
	if ( incassoos_is_encryption_enabled() ) {
		return new WP_Error(
			'incassoos_encryption_already_enabled',
			esc_html__( 'Encryption is already enabled.', 'incassoos' )
		);
	}

	// Check capabilities
	if ( ! current_user_can( 'decrypt_incassoos_data' ) ) {
		return new WP_Error(
			'incassoos_user_not_allowed_enable_encryption',
			esc_html__( 'You are not allowed to enable encryption.', 'incassoos' )
		);
	}

	// Generate encryption keys
	$keys = incassoos_generate_encryption_keys();
	if ( is_wp_error( $keys ) ) {
		return $keys;
	}

	// Put keys into vars `$encryption_key` and `$decryption_key`
	extract( $keys );

	// Store the public encryption key
	update_option( '_incassoos_encryption_key', $encryption_key );

	// Hook after
	do_action( 'incassoos_enable_encryption' );

	return $decryption_key;
}

/**
 * Disable encryption
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'incassoos_disable_encryption'
 * @uses do_action() Calls 'incassoos_disabled_encryption'
 *
 * @param  string $decryption_key The encoded decryption key
 * @return bool|WP_Error Disabling success error object
 */
function incassoos_disable_encryption( $decryption_key ) {

	// Bail when encryption is not enabled
	if ( ! incassoos_is_encryption_enabled() ) {
		return new WP_Error(
			'incassoos_encryption_not_enabled',
			esc_html__( 'Encryption is not enabled.', 'incassoos' )
		);
	}

	// Validate decryption key
	$validated = incassoos_validate_decryption_key( $decryption_key );
	if ( is_wp_error( $validated ) ) {
		return $validated;
	}

	// Check capabilities
	if ( ! current_user_can( 'decrypt_incassoos_data' ) ) {
		return new WP_Error(
			'incassoos_user_not_allowed_disable_encryption',
			esc_html__( 'You are not allowed to disable encryption.', 'incassoos' )
		);
	}

	// Force False on the check for whether encryption is enabled. Not untill
	// the encryption key option is deleted will `incassoos_is_encryption_enabled()`
	// return True. However, the encryption key must remain available to be
	// able to decrypt encrypted values using `incassoos_decrypt_value()`.
	add_filter( 'incassoos_is_encryption_enabled', '__return_false' );

	// Hook before. Use this for decrypting data while the encryption key
	// still exists in the database
	do_action( 'incassoos_disable_encryption', $decryption_key );

	// Remove public encryption key
	delete_option( '_incassoos_encryption_key' );

	// Hook after
	do_action( 'incassoos_disabled_encryption' );

	return true;
}

/**
 * Generate encryption keys
 *
 * @since 1.0.0
 *
 * @param string $password Optional. Additional encryption password
 * @return array|WP_Error Encoded encryption keys or error object
 */
function incassoos_generate_encryption_keys( $password = null ) {

	// Bail when encryption is already enabled
	if ( incassoos_is_encryption_enabled() ) {
		return new WP_Error(
			'incassoos_encryption_already_enabled',
			esc_html__( 'Encryption is already enabled.', 'incassoos' )
		);
	}

	// Bail when encyrption is not supported
	if ( ! incassoos_is_encryption_supported() ) {
		return new WP_Error(
			'incassoos_encryption_not_available',
			esc_html__( 'Encryption is not available on this system.', 'incassoos' )
		);
	}

	// Check capabilities
	if ( ! current_user_can( 'decrypt_incassoos_data' ) ) {
		return new WP_Error(
			'incassoos_user_not_allowed_generate_encryption_keys',
			esc_html__( 'You are not allowed to generate encryption keys.', 'incassoos' )
		);
	}

	try {

		// Generate new keys
		$keypair = sodium_crypto_box_keypair();

	// Return error when keys could not be generated
	} catch ( Exception $exception ) {
		return new WP_Error(
			'incassoos_encryption_no_keys',
			esc_html__( 'Something went wrong when generating encryption keys.', 'incassoos' )
		);
	}

	// Wrap keys in array
	$keys = array(
		'encryption_key' => base64_encode( sodium_crypto_box_publickey( $keypair ) ),
		'decryption_key' => base64_encode( sodium_crypto_box_secretkey( $keypair ) )
	);

	return $keys;
}

/**
 * Return the encryption key
 *
 * @since 1.0.0
 *
 * @return string Encoded encryption key
 */
function incassoos_get_encryption_key() {
	return get_option( '_incassoos_encryption_key' );
}

/**
 * Return whether encryption is enabled
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_is_encryption_enabled'
 *
 * @return bool Is encryption enabled?
 */
function incassoos_is_encryption_enabled() {
	return (bool) apply_filters( 'incassoos_is_encryption_enabled', (bool) incassoos_get_encryption_key() );
}

/**
 * Return the encryption key that is associated with the decryption key
 *
 * @since 1.0.0
 *
 * @param  string $decryption_key The encoded decryption key
 * @return string|WP_Error Encoded encryption key, error object when unsuccessfull.
 */
function incassoos_get_encryption_key_from_decription_key( $decryption_key ) {
	try {

		// Derive encryption key from decryption key
		$encryption_key = base64_encode( sodium_crypto_box_publickey_from_secretkey( base64_decode( $decryption_key ) ) );

	// Keypair is invalid
	} catch ( Exception $exception ) {
		$encryption_key = new WP_Error(
			'incassoos_invalid_decryption_key',
			esc_html__( 'The provided decryption key is not a valid key.', 'incassoos' )
		);
	}

	return $encryption_key;
}

/**
 * Return whether this is the correct decryption key
 *
 * Validation of the decryption key is done by deriving an encryption key from the
 * provided decryption key and then comparing it to the registered encryption key.
 *
 * @since 1.0.0
 *
 * @param  string $decryption_key The encoded decryption key
 * @return bool|WP_Error True when valid, error object when invalid.
 */
function incassoos_validate_decryption_key( $decryption_key ) {
	$encryption_key = incassoos_get_encryption_key_from_decription_key( $decryption_key );
	$validated = true;

	// Get error data from encryption key
	if ( is_wp_error( $encryption_key ) ) {
		$validated = $encryption_key;
	}

	// Compare encryption keys
	if ( ! is_wp_error( $encryption_key ) && $encryption_key !== incassoos_get_encryption_key() ) {
		$validated = new WP_Error(
			'incassoos_incorrect_decryption_key',
			esc_html__( 'The provided decryption key is not the correct key.', 'incassoos' )
		);
	}

	return $validated;
}

/**
 * Return the decryption key from the user's session
 *
 * @since 1.0.0
 *
 * @return string|bool The encoded decryption key or False when not set
 */
function incassoos_get_decryption_key() {
	
	// Bail early when encryption is not enabled
	if ( ! incassoos_is_encryption_enabled() ) {
		return true;
	}

	// Return key when defined
	if ( isset( incassoos()->encryption->decryption_key ) ) {
		return incassoos()->encryption->decryption_key;
	} else {
		return false;
	}
}

/**
 * Save the decryption key temporarily in the user's session
 *
 * @since 1.0.0
 *
 * @param string $decryption_key The encoded decryption key
 * @return bool|WP_Error Saving success or error object when failed
 */
function incassoos_set_decryption_key( $decryption_key ) {

	// Bail early when encryption is not enabled
	if ( ! incassoos_is_encryption_enabled() ) {
		return true;
	}

	// Bail early when the decryption key was already set
	if ( incassoos_get_decryption_key() ) {
		return true;
	}

	// Define retval
	$validated = incassoos_validate_decryption_key( $decryption_key );
	if ( is_wp_error( $validated ) ) {
		return $validated;
	}

	// Set decryption key in cache
	incassoos()->encryption->decryption_key = $decryption_key;

	return true;
}

/**
 * Remove the decryption key from the user's session
 *
 * @since 1.0.0
 *
 * @return bool Remval success
 */
function incassoos_remove_decryption_key() {

	// Unset decryption key from cache
	incassoos()->encryption->decryption_key = null;

	return true;
}

/**
 * Return the encrypted version of a text
 *
 * @since 1.0.0
 *
 * @param  string $input Value to encrypt
 * @return string Encrypted string
 */
function incassoos_encrypt_value( $input ) {

	// When encryption is enabled
	if ( incassoos_is_encryption_enabled() ) {
		try {

			// Encrypt input
			$encrypted = sodium_crypto_box_seal( (string) $input, base64_decode( incassoos_get_encryption_key() ) );

			// Set encrypted value
			if ( $encrypted ) {
				$input = base64_encode( $encrypted );
			}

		// Setup error when encrypting failed
		} catch ( Exception $exception ) {
			$input = new WP_Error(
				'incassoos_encrypt_value_error',
				sprintf( esc_html__( 'Encrypting the value resulted in an error: %s', 'incassoos' ), $exception->getMessage() )
			);
		}
	}

	return $input;
}

/**
 * Return the decrypted version of an encrypted text
 *
 * @since 1.0.0
 *
 * @param  string $input          Value to decrypt
 * @param  string $decryption_key The encoded decryption key
 * @return string Decrypted string
 */
function incassoos_decrypt_value( $input, $decryption_key ) {

	// When encryption is enabled
	if ( incassoos_is_encryption_enabled() || doing_action( 'incassoos_disable_encryption' ) ) {

		// Check capabilities
		if ( ! current_user_can( 'decrypt_incassoos_data' ) ) {
			return new WP_Error(
				'incassoos_user_not_allowed_decrypt_data',
				esc_html__( 'You are not allowed to decrypt data.', 'incassoos' )
			);
		}

		try {

			// Construct keypair
			$keypair = sodium_crypto_box_keypair_from_secretkey_and_publickey(
				base64_decode( $decryption_key ),
				base64_decode( incassoos_get_encryption_key() )
			);

			// Decrypt input
			$decrypted = sodium_crypto_box_seal_open( base64_decode( $input ), $keypair );

			// Set decrypted value
			if ( $decrypted ) {
				$input = $decrypted;
			}

		// Setup error when decrypting failed
		} catch ( Exception $exception ) {
			$input = new WP_Error(
				'incassoos_decrypt_value_error',
				sprintf( esc_html__( 'Decrypting the value resulted in an error: %s', 'incassoos' ), $exception->getMessage() )
			);
		}
	}

	return $input;
}

/**
 * Return the list of encryptable options
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_encryptable_options'
 * @return array Encryptable options
 */
function incassoos_get_encryptable_options() {

	// Try to return items from cache
	if ( ! empty( incassoos()->encryption->options ) ) {
		$encryptable = incassoos()->encryption->options;
	} else {
		/**
		 * Filter the list of encryptable options
		 *
		 * Options can be added to the list in one of the following ways:
		 * - Just the option name. The suffix '_encrypted' will be added to the option name for the
		 *   equivalent encrypted option.
		 * - The option name as array key with the name for the equivalent encrypted option as array value.
		 * - The option name as array key with their encryption parameters in an array {
		 *    string $option_name_encrypted Optional. Name for the encrypted option. Defaults to the
		 *                                  plain option name with the suffix '_encrypted'.
		 *    string $redact_callback       Optional. Callback name for redacting the original value.
		 *                                  Defaults to `incassoos_redact_text`.
		 *    array  $redact_callback_args  Optional. Additional arguments for the redaction callback.
		 *                                  Defaults to an empty array.
		 *    string $is_redacted_callback  Optional. Callback name for checking whether the value is
		 *                                  redacted. Defaults to `incassoos_is_value_redacted`.
		 *   }
		 *
		 * @since 1.0.0
		 *
		 * @param array $options Encryptable options
		 */
		$options = (array) apply_filters( 'incassoos_get_encryptable_options', array(

			// The IBAN of the organization
			'_incassoos_account_iban'     => array(
				'option_name_encrypted' => '_incassoos_encrypted_account_iban',
				'redact_callback'       => 'incassoos_redact_iban',
				'is_redacted_callback'  => 'incassoos_is_iban_redacted'
			),

			// The SEPA Creditor Identifier of the organization
			'_incassoos_sepa_creditor_id' => array(
				'option_name_encrypted' => '_incassoos_encrypted_sepa_creditor_id',
				'redact_callback_args'  => array( 'keep' => array( 2, 4 ) )
			)
		) );

		$encryptable = array();

		// Parse defaults
		foreach ( $options as $option_name => $args ) {

			// Provided just the plain option name
			if ( is_numeric( $option_name ) && is_string( $args ) ) {
				$option_name = $args;
				$args        = array();
			} elseif ( is_string( $args ) ) {
				$args = array( 'option_name_encrypted' => $args );
			}

			$encryptable[ $option_name ] = wp_parse_args( $args, array(
				'option_name_encrypted' => "{$option_name}_encrypted",
				'redact_callback'       => 'incassoos_redact_text',
				'redact_callback_args'  => array(),
				'is_redacted_callback'  => 'incassoos_is_value_redacted'
			) );
		}

		// Set items in cache
		incassoos()->encryption->options = $encryptable;
	}

	return $encryptable;
}

/**
 * Return the details of a single encryptable option
 *
 * @since 1.0.0
 *
 * @param  string $option Option name
 * @return array|bool Option details or False when not found
 */
function incassoos_get_encryptable_option( $option ) {

	// Define retval
	$retval = false;

	// Get encryptable options
	$options = incassoos_get_encryptable_options();

	// Find the option
	if ( isset( $options[ $option ] ) ) {
		$retval = $options[ $option ];
	}

	return $retval;
}

/**
 * Register actions and filters for encryptable options
 *
 * @since 1.0.0
 */
function incassoos_register_encryptable_options() {

	// Get encryptable options
	$options = incassoos_get_encryptable_options();

	// Register encryption actions for `add_option()`
	foreach ( $options as $option => $args ) {
		add_action( "add_option_{$option}", 'incassoos_encryption_for_add_option', 10, 2 );
	}

	// Register encryption filters for `get_option()`
	foreach ( $options as $option => $args ) {
		add_filter( "pre_option_{$option}", 'incassoos_encryption_for_get_option', 10, 3 );
	}

	// Register encryption filters for `update_option()`
	foreach ( $options as $option => $args ) {
		add_filter( "pre_update_option_{$option}", 'incassoos_encryption_for_update_option', 10, 3 );
	}

	// Register encryption actions for `delete_option()`
	foreach ( $options as $option => $args ) {
		add_action( "delete_option_{$option}", 'incassoos_encryption_for_delete_option', 10 );
	}

	// Register actions for enabling/disabling encryption
	add_action( 'incassoos_enable_encryption',  'incassoos_encrypt_encryptable_options', 10 );
	add_action( 'incassoos_disable_encryption', 'incassoos_decrypt_encryptable_options', 10 );
}

/**
 * Apply encryption when an option is added
 *
 * @since 1.0.0
 *
 * @param string $option Option name
 * @param mixed  $value  Option value
 */
function incassoos_encryption_for_add_option( $option, $value ) {

	// Bail when encryption is not enabled
	if ( ! incassoos_is_encryption_enabled() ) {
		return;
	}

	// Re-update the option which will trigger encryption
	update_option( $option, $value );
}

/**
 * Parse encryption when an option is requested
 *
 * @since 1.0.0
 *
 * @param mixed  $value   Option value
 * @param string $option  Option name
 * @param mixed  $default Default option value
 * @return mixed 
 */
function incassoos_encryption_for_get_option( $value, $option, $default ) {

	// Bail when encryption is not enabled
	if ( ! incassoos_is_encryption_enabled() ) {
		return $value;
	}

	// Get encryptable option
	$args = incassoos_get_encryptable_option( $option );

	// Bail when option is not encryptable
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

	// Get encrypted option value
	$encrypted_value = get_option( $args['option_name_encrypted'], false );

	// Decrypt option value
	if ( $encrypted_value ) {
		$decrypted_value = incassoos_decrypt_value( $encrypted_value, $decryption_key );

		// Bail when an error occurred
		if ( is_wp_error( $decrypted_value ) ) {
			wp_die( $decrypted_value );
		}

		// Set return value
		$value = $decrypted_value;
	}

	return $value;
}

/**
 * Apply encryption when an option is updated
 *
 * @since 1.0.0
 *
 * @param  mixed  $value     Option value
 * @param  mixed  $old_value Previous option value
 * @param  string $option    Option name
 * @return mixed Redacted or untouched option value
 */
function incassoos_encryption_for_update_option( $value, $old_value, $option ) {

	// Bail when encryption is not enabled
	if ( ! incassoos_is_encryption_enabled() ) {
		return $value;
	}

	// Get encryptable option
	$args = incassoos_get_encryptable_option( $option );

	// Bail when option is not encryptable
	if ( ! $args ) {
		return $value;
	}

	// Ignore redacted values to prevent encryption of already encrypted data
	if ( call_user_func_array( $args['is_redacted_callback'], array( $value, $args['redact_callback_args'] ) ) ) {
		return $value;
	}

	// Encrypt the value
	$encrypted_value = incassoos_encrypt_value( $value );

	// Bail when encryption failed
	if ( is_wp_error( $encrypted_value ) ) {
		wp_die( $encrypted_value );
	}

	// Store encrypted option
	update_option( $args['option_name_encrypted'], $encrypted_value );

	// Set redacted value for saving
	$value = call_user_func_array( $args['redact_callback'], array( $value, $args['redact_callback_args'] ) );

	return $value;
}

/**
 * Remove associated encryption when an option is deleted
 *
 * @since 1.0.0
 *
 * @param string $option Option name
 */
function incassoos_encryption_for_delete_option( $option ) {

	// Bail when encryption is not enabled
	if ( ! incassoos_is_encryption_enabled() ) {
		return;
	}

	// Get encryptable option
	$args = incassoos_get_encryptable_option( $option );

	// Delete associated encrypted option
	if ( $args ) {
		delete_option( $args['option_name_encrypted'] );
	}
}

/**
 * Apply encryption to the encryptable options in bulk
 *
 * @since 1.0.0
 */
function incassoos_encrypt_encryptable_options() {

	// Walk encryptable options
	foreach ( incassoos_get_encryptable_options() as $option_name => $args ) {

		// Get plain option value
		$plain_value = get_option( $option_name, false );
		if ( $plain_value ) {

			// Re-update the option which will trigger encryption
			update_option( $option_name, $plain_value );
		}
	}
}

/**
 * Decrypt the encrypted encryptable options in bulk
 *
 * @since 1.0.0
 *
 * @param string $decryption_key The encoded decryption key
 */
function incassoos_decrypt_encryptable_options( $decryption_key ) {

	// Walk encryptable options
	foreach ( incassoos_get_encryptable_options() as $option_name => $args ) {

		// Get encrypted option value
		$encrypted_value = get_option( $args['option_name_encrypted'], false );
		if ( $encrypted_value ) {

			// Decrypt option value
			$decrypted_value = incassoos_decrypt_value( $encrypted_value, $decryption_key );

			// Bail when an error occurred
			if ( is_wp_error( $decrypted_value ) ) {
				wp_die( $decrypted_value );
			}

			// Overwrite redacted option with the decrypted value
			update_option( $option_name, $decrypted_value );

			// Remove encrypted option
			delete_option( $args['option_name_encrypted'] );
		}
	}
}

/**
 * Return whether the option is encryptable and redacted
 *
 * @since 1.0.0
 *
 * @param  string $option_name Option name
 * @param  string $value       Option value to check
 * @return bool Option is both encryptable and redacted
 */
function incassoos_is_option_redacted( $option_name, $value ) {

	// Bail when encryption is not enabled
	if ( ! incassoos_is_encryption_enabled() ) {
		return false;
	}

	// Get encryptable option
	$args = incassoos_get_encryptable_option( $option_name );

	// Bail when option is not registered as encryptable
	if ( ! $args ) {
		return false;
	}

	return call_user_func_array( $args['is_redacted_callback'], array( $value, $args['redact_callback_args'] ) );
}

/** Tools ***************************************************************/

/**
 * Recalculate a post's total value
 *
 * @since 1.0.0
 *
 * @param int|WP_Post $post Optional. Post ID or post object. Defaults to the current post.
 */
function incassoos_tool_recalculate_post_total( $post = 0 ) {
	$post = get_post( $post );

	// Bail when the post is invalid
	if ( ! $post ) {
		return;
	}

	switch ( $post->post_type ) {
		case incassoos_get_collection_post_type() :
		case incassoos_get_activity_post_type() :
		case incassoos_get_occasion_post_type() :
		case incassoos_get_order_post_type() :

			// Get object type
			$object_type = incassoos_get_object_type( $post->post_type );
			if ( $object_type ) {

				// Query the raw total
				$callback = "incassoos_get_{$object_type}_total_raw";
				if ( function_exists( $callback ) ) {
					$total = call_user_func( $callback, $post );

					// Save the raw total as new total
					update_post_meta( $post->ID, 'total', $total );
				}
			}
			break;
	}
}
