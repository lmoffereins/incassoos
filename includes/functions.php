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
 * Return the slug for the app interface
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_app_slug'
 * @return string Slug
 */
function incassoos_get_app_slug() {
	return apply_filters( 'incassoos_get_app_slug', get_option( '_incassoos_app_slug', 'incassoos' ) );
}

/** URLs **********************************************************************/

/**
 * Return the url for the app interface
 *
 * @since 1.0.0
 *
 * @return string Url
 */
function incassoos_get_app_url() {
	return home_url( user_trailingslashit( incassoos_get_app_slug() ) );
}

/** Posts *********************************************************************/

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
	$is = in_array( $post_type, array(
		incassoos_get_collection_post_type(),
		incassoos_get_activity_post_type(),
		incassoos_get_occasion_post_type(),
		incassoos_get_order_post_type(),
		incassoos_get_product_post_type()
	), true );

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
 * @return string              Post type name
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
	$post = get_post( $post );
	return (bool) apply_filters( 'incassoos_is_post_published', ! in_array( $post->post_status, array( 'auto-draft', 'draft' ), true ), $post );
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
 * its post type has support post title, content and excerpt. Since most plugin
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

	if ( $post ) {

		// Run action before duplicating
		do_action( 'incassoos_duplicate_post', $post );

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
		), $post );

		// Insert the new post
		$new_post_id = wp_insert_post( $args );

		if ( $new_post_id && ! is_wp_error( $new_post_id ) ) {

			// Duplicate taxonomies
			foreach ( get_object_taxonomies( $post->post_type ) as $taxonomy ) {
				wp_set_object_terms( $new_post_id, wp_get_object_terms( $post->ID, $taxonomy, array( 'fields' => 'ids' ) ), $taxonomy );
			}

			// Duplicate meta
			$post_meta = $wpdb->get_results( $wpdb->prepare( "SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id = %d", $post->ID ) );

			// Remove unique metas
			$post_meta = array_filter( $post_meta, function( $m ) {
				return ! in_array( $m->meta_key, array( '_edit_last', '_edit_lock', '_wp_old_slug' ) );
			} );

			if ( $post_meta ) {
				$meta_sql = "INSERT INTO {$wpdb->postmeta} (post_id, meta_key, meta_value) VALUES";

				foreach ( array_values( $post_meta ) as $k => $meta ) {
					$meta_sql .= $k > 0 ? ', ' : '';
					$meta_sql .= $wpdb->prepare( " (%d, %s, %s)", $new_post_id, $meta->meta_key, $meta->meta_value );
				}

				// Run meta query
				$wpdb->query( $meta_sql );
			}
		}

		// Run action after duplicating
		do_action( 'incassoos_duplicated_post', $new_post_id, $post );
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
	$is = in_array( $taxonomy, array(
		incassoos_get_activity_cat_tax_id(),
		incassoos_get_occasion_type_tax_id(),
		incassoos_get_product_cat_tax_id()
	), true );

	return apply_filters( 'incassoos_is_plugin_taxonomy', $is, $taxonomy );
}

/**
 * Modify the term link
 *
 * @since 1.0.0
 *
 * @param  string $url Term url
 * @param  WP_Term $term Term object
 * @param  string $taxonomy Taxonomy name
 * @return string Term url
 */
function incassoos_filter_term_link( $url, $term, $taxonomy ) {

	// Define base admin url
	$admin_url = add_query_arg( array( 'taxonomy' => $taxonomy, 'term' => $term->slug ), admin_url( 'edit.php' ) );

	switch ( $taxonomy ) {

		// Activity Category
		case incassoos_get_activity_cat_tax_id() :
			$url = add_query_arg( 'post_type', incassoos_get_activity_post_type(), $admin_url );
			break;

		// Occasion Type
		case incassoos_get_occasion_type_tax_id() :
			$url = add_query_arg( 'post_type', incassoos_get_occasion_post_type(), $admin_url );
			break;

		// Product Category
		case incassoos_get_product_cat_tax_id() :
			$url = add_query_arg( 'post_type', incassoos_get_product_post_type(), $admin_url );
			break;
	}

	return $url;
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

		// App interface
		'app' => array(
			'title'       => esc_html__( 'Incassoos App', 'incassoos' ),
			'type_label'  => esc_html_x( 'Incassoos', 'Menu type label', 'incassoos' ),
			'url'         => incassoos_get_app_url(),
			'is_current'  => true//incassoos_is_app(),
		),
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

	?>
	<div id="incassoos-menu" class="posttypediv">

		<div id="tabs-panel-incassoos-menu" class="tabs-panel tabs-panel-active">
			<ul id="incassoos-menu-checklist" class="categorychecklist form-no-clear">
				<?php echo walk_nav_menu_tree( array_map( 'wp_setup_nav_menu_item', incassoos_nav_menu_get_items() ), 0, (object) $args ); ?>
			</ul>
		</div><!-- /.tabs-panel -->

		<p class="button-controls wp-clearfix">
			<span class="list-controls">
				<a href="<?php
					echo esc_url( add_query_arg(
						array(
							'selectall' => 1,
						),
						remove_query_arg( array(
							'action',
							'customlink-tab',
							'edit-menu-item',
							'menu-item',
							'page-tab',
							'_wpnonce',
						) )
					));
				?>#incassoos-menu" class="select-all aria-button-if-js"><?php _e( 'Select All' ); ?></a>
			</span>

			<span class="add-to-menu">
				<input type="submit"<?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?> class="button submit-add-to-menu right" value="<?php esc_attr_e( 'Add to Menu' ); ?>" name="add-incassoos-menu-item" id="submit-incassoos-menu" />
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
 * Return the currency
 *
 * @since 1.0.0
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
 * @param mixed $default Optional. Default return value.
 * @return string Custom email salutation
 */
function incassoos_get_custom_email_salutation( $user, $default = '' ) {
	$value = get_option( '_incassoos_custom_email_salutation', $default );
	$user  = get_userdata( $user );

	// Parse user name
	if ( $user && $user->exists() ) {
		$value = str_replace( '%USER%', $user->display_name, $value );
	}

	return apply_filters( 'incassoos_get_custom_email_salutation', $value, $user );
}

/**
 * Return the custom email closing
 *
 * @since 1.0.0
 *
 * @param mixed $default Optional. Default return value.
 * @return string Custom email closing
 */
function incassoos_get_custom_email_closing( $default = '' ) {
	return apply_filters( 'incassoos_get_custom_email_closing', get_option( '_incassoos_custom_email_closing', $default ) );
}

/**
 * Return the App's default Occasion Selector tab
 *
 * @since 1.0.0
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
 * @return string Occassion title
 */
function incassoos_app_get_default_occasion_title() {
	return apply_filters( 'incassoos_app_get_default_occasion_title', __( 'Drinks', 'incassoos' ) );
}

/** Security ******************************************************************/

/**
 * Return the redacted version of an IBAN
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_redact_iban'
 *
 * @param  string $iban IBAN
 * @return string Redacted IBAN
 */
function incassoos_redact_iban( $iban ) {
	return apply_filters( 'incassoos_redact_iban', incassoos_redact_text( $iban, array( 2, 4 ) ), $iban );
}

/**
 * Return a redacted version of a text
 *
 * The returned value may be smaller in length, because the redaction will replace
 * all non-kept parts of the input with just the `$redaction` content.
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_redact_text'
 *
 * @param  string    $input Text to redact
 * @param  int|array $keep  Optional. Amount of trailing characters to not redact. Provide two values in an
 *                           array to keep leading characters as well.
 * @param  string    $redaction Optional. Characters to use for redaction. Defaults to '****'.
 * @return string Redacted text
 */
function incassoos_redact_text( $input, $keep = 4, $redaction = '****' ) {
	$keep     = array_map( 'absint', array_values( (array) $keep ) );
	$redacted = '';

	// Default to keep 0 leading characters
	if ( 1 === count( $keep ) ) {
		$keep = array_merge( array( 0 ), $keep );
	}

	if ( $input ) {

		// Ignore leading keeps when the total keep lengths match the input's
		if ( strlen( $input ) <= array_sum( $keep ) ) {
			$keep[0] = 0;
		}

		// Require a non-empty redaction
		if ( ! $redaction ) {
			$redaction = '****';
		}

		// Create redacted text
		$redacted = substr( $input, 0, $keep[0] ) . $redaction . substr( $input, strlen( $input ) - $keep[1] );
	}

	return apply_filters( 'incassoos_redact_text', $redacted, $input, $keep, $redaction );
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
 * Register a Collection export type
 *
 * @since 1.0.0
 *
 * @uses apply_filters Calls 'incassoos_register_export_type'
 *
 * @param  string $type_id Export type id.
 * @param  array  $args    Optional. Export type parameters.
 * @return bool Registration success.
 */
function incassoos_register_export_type( $type_id, $args = array() ) {
	$plugin  = incassoos();
	$type_id = sanitize_title( $type_id );

	// Bail when type param is invalid
	if ( empty( $type_id ) ) {
		return false;
	}

	// Keep original arguments
	$original_args = $args;

	// Parse defaults
	$args['id'] = $type_id;
	$args = wp_parse_args( $args, array(
		'label'      => ucfirst( $type_id ),
		'class_name' => ''
	) );

	// Allow filtering
	$export_type = apply_filters( 'incassoos_register_export_type', $args, $type_id, $original_args );

	// Define consumer types collection
	if ( ! isset( $plugin->export_types ) ) {
		$plugin->export_types = array();
	}

	// Add type to collection
	$plugin->export_types[ $type_id ] = (object) $export_type;

	return true;
}

/**
 * Unregister a export type
 *
 * @since 1.0.0
 *
 * @param  string $type_id Export type id.
 * @return bool Unregistration success.
 */
function incassoos_unregister_export_type( $type_id ) {
	unset( incassoos()->export_types[ $type_id ] );

	return true;
}

/**
 * Return the export type object
 *
 * @since 1.0.0
 *
 * @uses apply_filters Calls 'incassoos_get_export_type'
 *
 * @param  string $type Export type id or label.
 * @return object|bool Export type object or False when not found.
 */
function incassoos_get_export_type( $type = '' ) {
	$plugin      = incassoos();
	$type_id     = sanitize_title( $type );
	$type_object = false;

	if ( ! isset( $plugin->export_types ) ) {
		$plugin->export_types = array();
	}

	// Get type by id
	if ( isset( $plugin->export_types[ $type_id ] ) ) {
		$type_object = $plugin->export_types[ $type_id ];

	// Get type by label
	} elseif ( $type_id = array_search( $type, wp_list_pluck( $plugin->export_types, 'label' ) ) ) {
		$type_object = $plugin->export_types[ $type_id ];
	}

	return apply_filters( 'incassoos_get_export_type', $type_object, $type );
}

/**
 * Return whether the export type exists
 *
 * @since 1.0.0
 *
 * @param  string $type Export type id or label
 * @return bool Does export type exist?
 */
function incassoos_export_type_exists( $type ) {
	return !! incassoos_get_export_type( $type );
}

/**
 * Return the ids of all defined export types
 *
 * @since 1.0.0
 *
 * @return array Export type ids
 */
function incassoos_get_export_types() {
	return array_keys( incassoos()->export_types );
}

/**
 * Output the export type title
 *
 * @since 1.0.0
 *
 * @param  string $type Export type id
 */
function incassoos_the_export_type_title( $type ) {
	echo incassoos_get_export_type_title( $type );
}

/**
 * Return the export type title
 *
 * @since 1.0.0
 *
 * @param  string $type Export type id
 * @return string Export type title
 */
function incassoos_get_export_type_title( $type ) {
	$export_type = incassoos_get_export_type( $type );
	$title       = ucfirst( $type );

	if ( $export_type ) {
		$title = $export_type->label;
	}

	return apply_filters( 'incassoos_get_export_type_title', $title, $export_type );
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

	$args        = apply_filters( 'incassoos_send_email_args', $args );
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

	// Invalid file
	if ( ! $file )
		return false;

	// File creator
	if ( is_object( $file ) && method_exists( $file, 'get_file' ) ) {
		$content = $file->get_file();

		// Use creator filename
		if ( ! $filename && method_exists( $file, 'get_filename' ) ) {
			$filename = $file->get_filename();
		}

	// File content
	} elseif ( is_string( $file ) && $filename ) {
		$content = $file;

	} else {
		return false;
	}

	// Start file headers
	if ( ! headers_sent() ) {
		nocache_headers();
		header( 'Robots: none' );
		header( 'Content-Type: ' . incassoos_get_file_type( $filename ) );
		header( 'Content-Description: File Transfer' );
		header( 'Content-Disposition: inline; filename="' . $filename . '"' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		header( 'Content-Transfer-Encoding: binary' );
		header( 'Content-Length: ' . mb_strlen( $content ) );

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
