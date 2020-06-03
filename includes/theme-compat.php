<?php

/**
 * Incassoos Theme Compatibility Functions
 *
 * Note that we're not really utilizing theme compatibility, only
 * parts of it to work worryfree with standalone page templates.
 * 
 * @package Incassoos
 * @subpackage Theme
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Return the path to the plugin's theme compat directory
 *
 * @since 1.0.0
 *
 * @return string Path to theme compat directory
 */
function incassoos_get_theme_compat_dir() {
	return trailingslashit( incassoos()->themes_dir . 'default' );
}

/**
 * Return the stack of template path locations
 *
 * @since 1.0.0
 *
 * @return array Template locations
 */
function incassoos_get_template_stack() {
	return apply_filters( 'incassoos_get_template_stack', array(
		get_stylesheet_directory(),      // Child theme
		get_template_directory(),        // Parent theme
		incassoos_get_theme_compat_dir() // Plugin theme-compat
	) );
}

/**
 * Return the template folder locations to look for files
 *
 * @since 1.0.0
 *
 * @return array Template folders
 */
function incassoos_get_template_locations() {
	return apply_filters( 'incassoos_get_template_locations', array(
		'incassoos', // Plugin folder
		''           // Root folder
	) );
}

/**
 * Reset WordPress globals with dummy data to prevent templates
 * reporting missing data.
 *
 * @see bbPress's bbp_theme_compat_reset_post()
 *
 * @since 1.0.0
 *
 * @global WP_Query $wp_query
 * @global WP_Post $post
 * @param array $args Reset post arguments
 */
function incassoos_theme_compat_reset_post( $args = array() ) {
	global $wp_query, $post;

	// Switch defaults if post is set
	if ( isset( $wp_query->post ) ) {
		$dummy = wp_parse_args( $args, array(
			'ID'                    => $wp_query->post->ID,
			'post_status'           => $wp_query->post->post_status,
			'post_author'           => $wp_query->post->post_author,
			'post_parent'           => $wp_query->post->post_parent,
			'post_type'             => $wp_query->post->post_type,
			'post_date'             => $wp_query->post->post_date,
			'post_date_gmt'         => $wp_query->post->post_date_gmt,
			'post_modified'         => $wp_query->post->post_modified,
			'post_modified_gmt'     => $wp_query->post->post_modified_gmt,
			'post_content'          => $wp_query->post->post_content,
			'post_title'            => $wp_query->post->post_title,
			'post_excerpt'          => $wp_query->post->post_excerpt,
			'post_content_filtered' => $wp_query->post->post_content_filtered,
			'post_mime_type'        => $wp_query->post->post_mime_type,
			'post_password'         => $wp_query->post->post_password,
			'post_name'             => $wp_query->post->post_name,
			'guid'                  => $wp_query->post->guid,
			'menu_order'            => $wp_query->post->menu_order,
			'pinged'                => $wp_query->post->pinged,
			'to_ping'               => $wp_query->post->to_ping,
			'ping_status'           => $wp_query->post->ping_status,
			'comment_status'        => $wp_query->post->comment_status,
			'comment_count'         => $wp_query->post->comment_count,
			'filter'                => $wp_query->post->filter,

			'is_404'                => false,
			'is_page'               => false,
			'is_single'             => false,
			'is_singular'           => false,
			'is_archive'            => false,
			'is_tax'                => false,
		) );
	} else {
		$dummy = wp_parse_args( $args, array(
			'ID'                    => -9999,
			'post_status'           => 'publish',
			'post_author'           => 0,
			'post_parent'           => 0,
			'post_type'             => 'page',
			'post_date'             => 0,
			'post_date_gmt'         => 0,
			'post_modified'         => 0,
			'post_modified_gmt'     => 0,
			'post_content'          => '',
			'post_title'            => '',
			'post_excerpt'          => '',
			'post_content_filtered' => '',
			'post_mime_type'        => '',
			'post_password'         => '',
			'post_name'             => '',
			'guid'                  => '',
			'menu_order'            => 0,
			'pinged'                => '',
			'to_ping'               => '',
			'ping_status'           => '',
			'comment_status'        => 'closed',
			'comment_count'         => 0,
			'filter'                => 'raw',

			'is_404'                => false,
			'is_page'               => false,
			'is_single'             => false,
			'is_archive'            => false,
			'is_tax'                => false,
		) );
	}

	// Bail if dummy post is empty
	if ( empty( $dummy ) ) {
		return;
	}

	// Set the $post global
	$post = new WP_Post( (object) $dummy );

	// Copy the new post global into the main $wp_query
	$wp_query->post        = $post;
	$wp_query->posts       = array( $post );

	// Prevent comments form from appearing
	$wp_query->post_count  = 1;
	$wp_query->is_404      = $dummy['is_404'];
	$wp_query->is_page     = $dummy['is_page'];
	$wp_query->is_single   = $dummy['is_single'];
	$wp_query->is_singular = $dummy['is_page'] || $dummy['is_single'];
	$wp_query->is_archive  = $dummy['is_archive'];
	$wp_query->is_tax      = $dummy['is_tax'];

	// Clean up the dummy post
	unset( $dummy );

	/**
	 * Force the header back to 200 status if not a deliberate 404
	 *
	 * @see http://bbpress.trac.wordpress.org/ticket/1973
	 */
	if ( ! $wp_query->is_404() ) {
		status_header( 200 );
	}
}

/**
 * Retrieve the path of the highest priority template file that exists.
 *
 * @since 1.0.0
 *
 * @param array $template_names Template hierarchy
 * @param bool $load Optional. Whether to load the file when it is found. Default to false.
 * @param bool $require_once Optional. Whether to require_once or require. Default to true.
 * @return string Path of the template file when located.
 */
function incassoos_locate_template( $template_names, $load = false, $require_once = true ) {

	// No file found yet
	$located = '';

	// Get template stack and locations
	$stack     = incassoos_get_template_stack();
	$locations = incassoos_get_template_locations();

	// Try to find a template file
	foreach ( (array) $template_names as $template_name ) {

		// Skip empty template
		if ( empty( $template_name ) )
			continue;

		// Loop through the template stack
		foreach ( $stack as $template_dir ) {

			// Loop through the template locations
			foreach ( $locations as $location ) {

				// Construct template location
				$template_location = trailingslashit( $template_dir ) . $location;

				// Skip empty locations
				if ( empty( $template_location ) )
					continue;

				// Locate template file
				if ( file_exists( trailingslashit( $template_location ) . $template_name ) ) {
					$located = trailingslashit( $template_location ) . $template_name;
					break 3;
				}
			}
		}
	}

	// Maybe load the template when it was located
	if ( $load && ! empty( $located ) ) {
		load_template( $located, $require_once );
	}

	return $located;
}
