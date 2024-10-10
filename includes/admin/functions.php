<?php

/**
 * Incassoos Admin Functions
 *
 * @package Incassoos
 * @subpackage Administration
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Menu ****************************************************************/

/**
 * Register the plugin's admin menu pages
 *
 * @since 1.0.0
 */
function incassoos_register_admin_menu() {

	// Collect highlightable pages
	$hooks = array(
		// Post type
		'post-new.php',
		'post.php',
		// Taxonomy
		'edit-tags.php',
		'term.php',
	);

	// Dashboard admin page
	$dashboard_page = add_menu_page(
		esc_html__( 'Incassoos Dashboard', 'incassoos' ),
		esc_html__( 'Incassoos', 'incassoos' ),
		'incassoos_admin_page-incassoos',
		'incassoos',
		'incassoos_admin_page',
		'dashicons-forms',
		50
	);

	// Manage posts
	foreach ( incassoos_get_plugin_post_types() as $post_type ) {
		$hooks[] = incassoos_admin_add_submenu_post_type( $post_type );
	}

	// Consumers page
	$hooks[] = $consumers_page = add_submenu_page(
		'incassoos',
		esc_html__( 'Incassoos Consumers', 'incassoos' ),
		esc_html__( 'Consumers', 'incassoos' ),
		'incassoos_admin_page-incassoos-consumers',
		'incassoos-consumers',
		'incassoos_admin_page'
	);

	// Settings page
	if ( incassoos_admin_page_has_settings( 'incassoos' ) ) {
		$hooks[] = $settings_page = add_submenu_page(
			'incassoos',
			esc_html__( 'Incassoos Settings', 'incassoos' ),
			esc_html__( 'Settings', 'incassoos' ),
			'incassoos_admin_page-incassoos-settings',
			'incassoos-settings',
			'incassoos_admin_page'
		);

		add_action( "load-{$settings_page}", 'incassoos_admin_load_settings_page' );
		add_action( "load-{$settings_page}", 'incassoos_admin_settings_help_tabs' );
	}

	// Encryption page
	$hooks[] = $encryption_page = add_submenu_page(
		'incassoos',
		esc_html__( 'Incassoos Encryption', 'incassoos' ),
		esc_html__( 'Encryption', 'incassoos' ),
		'incassoos_admin_page-incassoos-encryption',
		'incassoos-encryption',
		'incassoos_admin_page'
	);

	// Register admin page hooks
	add_action( "load-{$dashboard_page}",                    'incassoos_admin_load_dashboard_page' );
	add_action( "load-{$consumers_page}",                    'incassoos_admin_load_consumers_page' );
	add_action( 'incassoos_admin_page-incassoos',            'incassoos_admin_dashboard_page'      );
	add_action( 'incassoos_admin_page-incassoos-consumers',  'incassoos_admin_consumers_page'      );
	add_action( 'incassoos_admin_page-incassoos-settings',   'incassoos_admin_settings_page'       );
	add_action( 'incassoos_admin_page-incassoos-encryption', 'incassoos_admin_encryption_page'     );

	// Help tabs
	add_action( 'load-edit.php',                             'incassoos_admin_post_type_help_tabs'   );
	add_action( 'load-post-new.php',                         'incassoos_admin_single_post_help_tabs' );
	add_action( 'load-post.php',                             'incassoos_admin_single_post_help_tabs' );
	add_action( 'load-edit-tags.php',                        'incassoos_admin_taxonomy_help_tabs'    );
	add_action( "load-{$dashboard_page}",                    'incassoos_admin_dashboard_help_tabs'   );
	add_action( "load-{$consumers_page}",                    'incassoos_admin_consumers_help_tabs'   );
	add_action( "load-{$encryption_page}",                   'incassoos_admin_encryption_help_tabs'  );

	foreach ( $hooks as $hook ) {
		add_action( "admin_head-{$hook}", 'incassoos_admin_menu_highlight' );
	}
}

/**
 * Modify the highlighed menu for the current admin page
 *
 * @since 1.0.0
 *
 * @global string $parent_file
 * @global string $submenu_file
 */
function incassoos_admin_menu_highlight() {
	global $parent_file, $submenu_file;

	// Get the screen
	$screen = get_current_screen();

	/**
	 * Tweak the post type and taxonomy subnav menus to show the right
	 * top menu and submenu item.
	 */

	// Plugin post types
	if ( incassoos_is_plugin_post_type( $screen->post_type ) ) {
		$parent_file  = 'incassoos';
		$submenu_file = "edit.php?post_type={$screen->post_type}";

	// Plugin taxonomies
	} elseif ( incassoos_is_plugin_taxonomy( $screen->taxonomy ) ) {
		$parent_file  = 'incassoos';
		$post_types   = incassoos_get_plugin_taxonomy_post_types();

		if ( isset( $post_types[ $screen->taxonomy ] ) ) {
			$submenu_file = "edit.php?post_type=" . $post_types[ $screen->taxonomy ];
		} else {
			$submenu_file = 'incassoos';
		}

	// Default plugin home
	} elseif ( 'incassoos' === $parent_file && null === $submenu_file ) {
		$parent_file  = 'incassoos';
		$submenu_file = 'incassoos';
	}
}

/**
 * Add plugin admin submenu page for the given post type
 *
 * @since 1.0.0
 *
 * @param string $post_type Post type name
 * @param string $file_or_callback Optional. Menu file or function. Defaults to the post type's edit.php
 * @return false|string Result from {@see add_submenu_page()}
 */
function incassoos_admin_add_submenu_post_type( $post_type = '', $file_or_callback = '' ) {
	if ( ! $post_type_object = get_post_type_object( $post_type ) )
		return false;

	$menu_file = "edit.php?post_type={$post_type}";

	// Remove the default admin menu and its submenus, to prevent
	// the `$parent_file` override in `get_admin_page_parent()`
	remove_menu_page( $menu_file );
	unset( $GLOBALS['submenu'][ $menu_file ] );

	// Re-register post-new.php submenu, since it was removed
	add_submenu_page(
		'incassoos',
		$post_type_object->labels->add_new_item,
		$post_type_object->labels->add_new,
		$post_type_object->show_ui ? 'exist' : 'do_not_allow',
		"post-new.php?post_type={$post_type}"
	);

	return add_submenu_page(
		'incassoos',
		$post_type_object->label,
		$post_type_object->labels->menu_name,
		$post_type_object->show_ui ? 'exist' : 'do_not_allow',
		! empty( $file_or_callback ) ? $file_or_callback : $menu_file
	);
}

/**
 * Add plugin admin submenu page for the given taxonomy
 *
 * @since 1.0.0
 *
 * @param string $taxonomy Taxonomy name
 * @param string $file_or_callback Optional. Menu file or function. Defaults to the taxonomy's edit-tags.php
 * @return false|string Result from {@see add_submenu_page()}
 */
function incassoos_admin_add_submenu_taxonomy( $taxonomy = '', $file_or_callback = '' ) {
	if ( ! $taxonomy = get_taxonomy( $taxonomy ) )
		return false;

	$menu_file = "edit-tags.php?taxonomy={$taxonomy->name}";

	return add_submenu_page(
		'incassoos',
		$taxonomy->labels->name,
		$taxonomy->labels->menu_name,
		$taxonomy->show_ui ? 'exist' : 'do_not_allow',
		! empty( $file_or_callback ) ? $file_or_callback : $menu_file
	);
}

/**
 * Remove the individual admin menu items
 *
 * Admin pages remain registered, but they disappear from the admin menu.
 *
 * @since 1.0.0
 */
function incassoos_remove_admin_menu() {

	// Remove post-new.php items from admin menu
	foreach ( incassoos_get_plugin_post_types() as $post_type ) {
		remove_submenu_page( 'incassoos', "post-new.php?post_type={$post_type}" );
	}

	remove_submenu_page( 'incassoos', 'incassoos-consumers'  );
	remove_submenu_page( 'incassoos', 'incassoos-settings'   );
	remove_submenu_page( 'incassoos', 'incassoos-encryption' );
}

/** Pages ***************************************************************/

/**
 * Modify the set of removable url query arguments
 *
 * @since 1.0.0
 *
 * @param array $args Removable query args
 * @return array Removable query args
 */
function incassoos_admin_removable_query_args( $args ) {

	// Invalidate tokens (JWT Authorization)
	$args[] = 'tokens-invalidated';

	return $args;
}

/**
 * Return whether the current admin page is a plugin page
 *
 * @since 1.0.0
 *
 * @global $parent_file Parent base
 *
 * @uses apply_filters() Calls 'incassoos_admin_is_plugin_page'
 *
 * @return bool Is this a plugin page?
 */
function incassoos_admin_is_plugin_page() {

	// Define return value
	$retval = false;

	// Get the screen context
	$screen = get_current_screen();

	/**
	 * The screen's parentage is not set untill before the '*_admin_notices' hooks.
	 *
	 * @see wp-admin/admin-header.php
	 */
	$parent = empty( $screen->parent_base ) ? $GLOBALS['parent_file'] : $screen->parent_base;

	// Plugin page
	if ( 'incassoos' === $parent || 0 === strpos( $screen->base, 'incassoos' ) ) {
		$retval = true;
	}

	// Post type page
	if ( incassoos_is_plugin_post_type( $screen->post_type ) ) {
		$retval = true;
	}

	// Taxonomy page
	if ( incassoos_is_plugin_taxonomy( $screen->taxonomy ) ) {
		$retval = true;
	}

	return (bool) apply_filters( 'incassoos_admin_is_plugin_page', $retval );
}

/**
 * Output the contents of the main admin page
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'incassoos_admin_page-{$page}'
 */
function incassoos_admin_page() {
	$pages           = incassoos_admin_get_pages();
	$current_page    = incassoos_admin_get_current_page();
	$show_page_title = empty( $pages[ $current_page ]['hide_page_title'] );

	?>

	<div class="wrap">

		<?php if ( $show_page_title ) : ?>

		<h1 class="page-title"><?php incassoos_admin_the_page_title(); ?></h1>

		<?php endif; ?>

		<?php do_action( "incassoos_admin_page-{$current_page}" ); ?>

	</div>

	<?php
}

/**
 * Output the contents of the admin header
 *
 * @since 1.0.0
 */
function incassoos_admin_header() {

	// Bail when this is not a plugin page
	if ( ! incassoos_admin_is_plugin_page() )
		return;

	// Define home url
	$pages     = incassoos_admin_get_pages();
	$home_page = $pages ? ( function_exists( 'array_key_first' ) ? array_key_first( $pages ) : array_keys( $pages )[0] ) : false;
	$home_url  = empty( $home_page ) ? '' : esc_url( add_query_arg( array( 'page' => $home_page ), admin_url( 'admin.php' ) ) );

	?>

	<div class="incassoos-admin-header">
		<span class="plugin-title">
			<i class="icon dashicons dashicons-forms"></i>
			<a href="<?php echo $home_url; ?>"><?php esc_html_e( 'Incassoos', 'incassoos' ); ?></a>
		</span>

		<div class="nav-wrapper"><?php incassoos_admin_page_nav(); ?></div>
	</div>

	<?php
}

/**
 * Output the admin settings page tabs items
 *
 * @since 1.0.0
 */
function incassoos_admin_page_nav() {

	// Get the admin pages
	$pages = incassoos_admin_get_pages();
	$page  = incassoos_admin_get_current_page();

	// Walk registered pages
	foreach ( $pages as $slug => $args ) {

		// Skip empty pages
		if ( empty( $args ) || empty( $args['page_title'] ) || ! empty( $args['hide_nav_menu_item'] ) )
			continue;

		// Print the tab item
		printf( '<a class="nav-item nav-item-%s" href="%s"><span class="nav-item-title">%s</span></a>',
			( $page === $slug ) ? "{$slug} nav-item-active" : $slug,
			esc_url( add_query_arg( array( 'page' => $slug ), admin_url( 'admin.php' ) ) ),
			$args['page_title']
		);
	}
}

/**
 * Display the admin page title
 *
 * @since 1.0.0
 */
function incassoos_admin_the_page_title() {
	echo esc_html( incassoos_admin_get_page_title() );
}

/**
 * Return the admin page title
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_admin_get_page_title'
 *
 * @return string Admin page title
 */
function incassoos_admin_get_page_title() {
	$pages        = incassoos_admin_get_pages();
	$current_page = incassoos_admin_get_current_page();
	$title        = '';

	if ( isset( $pages[ $current_page ] ) ) {
		$title = $pages[ $current_page ]['page_title'];
	}

	return apply_filters( 'incassoos_admin_get_page_title', $title );
}

/**
 * Return the admin page pages
 *
 * @since 0.0.7
 *
 * @uses apply_filters() Calls 'incassoos_admin_get_pages'
 * @return array Tabs as $page-slug => $label
 */
function incassoos_admin_get_pages() {

	// Define page list
	$pages = array(
		'incassoos'            => __( 'Dashboard', 'incassoos' ),
		'incassoos-consumers'  => __( 'Consumers', 'incassoos' ),
		'incassoos-settings'   => __( 'Settings',  'incassoos' ),
		'incassoos-encryption' => array(
			'page_title'      => __( 'Encryption', 'incassoos' ),
			'hide_page_title' => true
		)
	);

	// Add the settings page
	if ( ! incassoos_admin_page_has_settings( 'incassoos' ) ) {
		unset( $pages['incassoos-settings'] );
	}

	foreach ( $pages as $page => $args ) {
		$pages[ $page ] = wp_parse_args(
			is_array( $args ) ? $args : array( 'page_title' => $args ),
			array(
				'hide_page_title'    => false,
				'hide_nav_menu_item' => false
			)
		);
	}

	$pages = (array) apply_filters( 'incassoos_admin_get_pages', $pages );

	// Limit pages by user capability to match menu page's caps
	foreach ( array_keys( $pages ) as $page ) {
		if ( ! current_user_can( "incassoos_admin_page-{$page}" ) ) {
			unset( $pages[ $page ] );
		}
	}

	return $pages;
}

/**
 * Return whether any admin page pages are registered
 *
 * @since 1.0.0
 *
 * @return bool Haz admin page pages?
 */
function incassoos_admin_has_pages() {
	return (bool) incassoos_admin_get_pages();
}

/**
 * Return the current admin page in the plugin's context
 *
 * @since 1.0.0
 *
 * @return string The current admin page. Defaults to empty string.
 */
function incassoos_admin_get_current_page() {

	// Define return value
	$retval = '';

	// Get page list slugs
	$pages = array_keys( incassoos_admin_get_pages() );

	// Check page in the plugin context
	if ( incassoos_admin_is_plugin_page() && isset( $_GET['page'] ) && in_array( $_GET['page'], $pages ) ) {
		$retval = $_GET['page'];
	}

	return $retval;
}

/** Misc ****************************************************************/

/**
 * Return the date format for date abbreviation titles
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_admin_get_abbr_date_format'
 *
 * @param mixed $context Optional. Abbreviation context. Can be a WP_Post object.
 * @return string Date format
 */
function incassoos_admin_get_abbr_date_format( $context = '' ) {
	return apply_filters( 'incassoos_admin_get_abbr_date_format', 'Y/m/d H:i:s', $context );
}

/** Posts ***************************************************************/

/**
 * Output admin posts list management helper tools
 *
 * @since 1.0.0
 *
 * @param string $which Top or bottom
 */
function incassoos_admin_manage_posts_tablenav( $which ) {

	// Bail when this is not the top tablenav
	if ( 'top' !== $which )
		return;

	// Get the current post type
	$post_type = get_current_screen()->post_type;

	switch ( $post_type ) {

		// Activity
		case incassoos_get_activity_post_type() :

			$tax_object = get_taxonomy( incassoos_get_activity_cat_tax_id() );

			// Display link to manage categories
			if ( current_user_can( $tax_object->cap->manage_terms ) ) {
				printf( '<div class="alignleft actions incassoos-activity-cat-link"><a href="%s" class="wp-core-ui button">%s</a></div>',
					'edit-tags.php?taxonomy=' . $tax_object->name . '&post_type=' . $post_type,
					esc_html__( 'Manage Activity Categories', 'incassoos' )
				);
			}

			break;

		// Occasion
		case incassoos_get_occasion_post_type() :

			$tax_object = get_taxonomy( incassoos_get_occasion_type_tax_id() );

			// Display link to manage types
			if ( current_user_can( $tax_object->cap->manage_terms ) ) {
				printf( '<div class="alignleft actions incassoos-occasion-type-link"><a href="%s" class="wp-core-ui button">%s</a></div>',
					'edit-tags.php?taxonomy=' . $tax_object->name . '&post_type=' . $post_type,
					esc_html__( 'Manage Occasion Types', 'incassoos' )
				);
			}

			break;

		// Product
		case incassoos_get_product_post_type() :

			$tax_object = get_taxonomy( incassoos_get_product_cat_tax_id() );

			// Display link to manage categories
			if ( current_user_can( $tax_object->cap->manage_terms ) ) {
				printf( '<div class="alignleft actions incassoos-product-cat-link"><a href="%s" class="wp-core-ui button">%s</a></div>',
					'edit-tags.php?taxonomy=' . $tax_object->name . '&post_type=' . $post_type,
					esc_html__( 'Manage Product Categories', 'incassoos' )
				);
			}

			break;
	}
}

/**
 * Modify the list of columns in the admin posts list table
 *
 * @since 1.0.0
 *
 * @param array $columns Columns
 * @param string $post_type Post type name
 * @return array Columns
 */
function incassoos_admin_posts_add_columns( $columns, $post_type ) {

	// Rename Activity Category column
	$tax_key = 'taxonomy-' . incassoos_get_activity_cat_tax_id();
	if ( isset( $columns[ $tax_key ] ) ) {
		$columns[ $tax_key ] = esc_html_x( 'Category', 'Taxonomy admin column', 'incassoos' );
	}

	// Rename Occasion Type column
	$tax_key = 'taxonomy-' . incassoos_get_occasion_type_tax_id();
	if ( isset( $columns[ $tax_key ] ) ) {
		$columns[ $tax_key ] = esc_html_x( 'Type', 'Taxonomy admin column', 'incassoos' );
	}

	// Rename Product Category column
	$tax_key = 'taxonomy-' . incassoos_get_product_cat_tax_id();
	if ( isset( $columns[ $tax_key ] ) ) {
		$columns[ $tax_key ] = esc_html_x( 'Category', 'Taxonomy admin column', 'incassoos' );
	}

	// Collection
	if ( incassoos_get_collection_post_type() === $post_type ) {

		// Append Collection details
		if ( $title_pos = array_search( 'title', array_keys( $columns ) ) ) {

			// Insert after title
			$columns = array_slice( $columns, 0, $title_pos + 1 ) + array(
				'collected'   => esc_html_x( 'Collected',   'Admin column', 'incassoos' ),
				'distributed' => esc_html_x( 'Distributed', 'Admin column', 'incassoos' ),
				'assets'      => esc_html_x( 'Assets',      'Admin column', 'incassoos' ),
				'consumers'   => esc_html_x( 'Consumers',   'Admin column', 'incassoos' ),
				'total'       => esc_html_x( 'Total',       'Admin column', 'incassoos' ),
			) + array_slice( $columns, $title_pos + 1 );
		}
	}

	// Activity
	if ( incassoos_get_activity_post_type() === $post_type ) {

		// Append Activity details
		if ( $title_pos = array_search( 'title', array_keys( $columns ) ) ) {

			// Insert after title
			$columns = array_slice( $columns, 0, $title_pos + 1 ) + array(
				'activity_date' => esc_html_x( 'Activity Date', 'Admin column', 'incassoos' ),
				'participants'  => esc_html_x( 'Participants',  'Admin column', 'incassoos' ),
				'price'         => esc_html_x( 'Price',         'Admin column', 'incassoos' ),
				'total'         => esc_html_x( 'Total',         'Admin column', 'incassoos' ),
			) + array_slice( $columns, $title_pos + 1 );
		}

		if ( $date_pos = array_search( 'date', array_keys( $columns ) ) ) {

			// Insert before date
			$columns = array_slice( $columns, 0, $date_pos ) + array(
				'collection' => esc_html_x( 'Collection', 'Admin column', 'incassoos' ),
			) + array_slice( $columns, $date_pos );
		}
	}

	// Occasion
	if ( incassoos_get_occasion_post_type() === $post_type ) {

		// Append Occasion details
		if ( $title_pos = array_search( 'title', array_keys( $columns ) ) ) {

			// Insert after title
			$columns = array_slice( $columns, 0, $title_pos + 1 ) + array(
				'occasion_date' => esc_html_x( 'Occasion Date', 'Admin column', 'incassoos' ),
				'orders'        => esc_html_x( 'Orders',        'Admin column', 'incassoos' ),
				'consumers'     => esc_html_x( 'Consumers',     'Admin column', 'incassoos' ),
				'total'         => esc_html_x( 'Total',         'Admin column', 'incassoos' ),
			) + array_slice( $columns, $title_pos + 1 );
		}

		if ( $date_pos = array_search( 'date', array_keys( $columns ) ) ) {

			// Insert before date
			$columns = array_slice( $columns, 0, $date_pos ) + array(
				'collection' => esc_html_x( 'Collection', 'Admin column', 'incassoos' ),
			) + array_slice( $columns, $date_pos );
		}
	}

	// Order
	if ( incassoos_get_order_post_type() === $post_type ) {

		// Append Order details
		if ( $title_pos = array_search( 'title', array_keys( $columns ) ) ) {

			// Replace title column
			unset( $columns['title'] );
			$columns = array_slice( $columns, 0, $title_pos ) + array(
				'consumer' => esc_html_x( 'Consumer', 'Admin column', 'incassoos' )
			) + array_slice( $columns, $title_pos );

			// Insert after title
			$columns = array_slice( $columns, 0, $title_pos + 1 ) + array(
				'products' => esc_html_x( 'Products', 'Admin column', 'incassoos' ),
				'total'    => esc_html_x( 'Total',    'Admin column', 'incassoos' ),
			) + array_slice( $columns, $title_pos + 1 );
		}

		if ( $date_pos = array_search( 'date', array_keys( $columns ) ) ) {

			// Insert before date
			$columns = array_slice( $columns, 0, $date_pos ) + array(
				'occasion' => esc_html_x( 'Occasion', 'Admin column', 'incassoos' ),
				'author'   => esc_html_x( 'Author',   'Admin column', 'incassoos' ),
			) + array_slice( $columns, $date_pos );
		}
	}

	// Product
	if ( incassoos_get_product_post_type() === $post_type ) {

		// Append Product details
		if ( $title_pos = array_search( 'title', array_keys( $columns ) ) ) {

			// Insert after title
			$columns = array_slice( $columns, 0, $title_pos + 1 ) + array(
				'price' => esc_html_x( 'Price', 'Admin column', 'incassoos' ),
			) + array_slice( $columns, $title_pos + 1 );
		}
	}

	return $columns;
}

/**
 * Output content of the admin posts list table columns
 *
 * @since 1.0.0
 *
 * @param string $column Column name
 * @param int $post_id Post ID
 */
function incassoos_admin_posts_custom_column( $column, $post_id ) {

	$post_type = get_post_type( $post_id );
	$posts_url = add_query_arg( 'post_type', $post_type, admin_url( 'edit.php' ) );

	// Formatting
	$abbr_date_format = incassoos_admin_get_abbr_date_format( $post_id );
	$date_format      = get_option( 'date_format' );

	switch ( $post_type ) {

		// Collection
		case incassoos_get_collection_post_type() :
			switch ( $column ) {
				case 'collected' :
					printf( '<span title="%s">%s</span>',
						/**
						 * @see WP_Posts_List_Table::column_date()
						 */
						esc_attr( incassoos_get_collection_date( $post_id, $abbr_date_format ) ),
						incassoos_get_collection_date( $post_id )
					);
					break;
				case 'distributed' :
					foreach ( incassoos_get_collection_consumer_collect_emails_sent( $post_id, 'U' ) as $date ) :
						printf( '<span title="%s">%s</span>',
							/**
							 * @see WP_Posts_List_Table::column_date()
							 */
							esc_attr( wp_date( $abbr_date_format, $date ) ),
							wp_date( $date_format, $date )
						);
					endforeach;
					break;
				case 'assets' :
					$num_activities = incassoos_get_collection_activity_count( $post_id );
					$num_occasions  = incassoos_get_collection_occasion_count( $post_id );
					$num_orders     = incassoos_get_collection_order_count( $post_id );

					// Display activities
					if ( $num_activities ) {
						printf( 
							'<a href="%s" aria-label="%s">%s</a>',
							esc_url( add_query_arg( array( 'post_type' => incassoos_get_activity_post_type(), 'collection' => $post_id ), admin_url( 'edit.php' ) ) ),
							/* translators: %s: Post title */
							esc_attr( sprintf( __( 'View activities of &#8220;%s&#8221;', 'incassoos' ), _draft_or_post_title( $post_id ) ) ),
							sprintf( _n( '%d Activity', '%d Activities', $num_activities, 'incassoos' ), $num_activities )
						);
					}

					// Display occasions
					if ( $num_occasions ) {
						printf( 
							'<a href="%s" aria-label="%s">%s</a>',
							esc_url( add_query_arg( array( 'post_type' => incassoos_get_occasion_post_type(), 'collection' => $post_id ), admin_url( 'edit.php' ) ) ),
							/* translators: %s: Post title */
							esc_attr( sprintf( __( 'View occasions of &#8220;%s&#8221;', 'incassoos' ), _draft_or_post_title( $post_id ) ) ),
							sprintf( _n( '%d Occasion', '%d Occasions', $num_occasions, 'incassoos' ), $num_occasions )
						);

						// Display orders
						if ( $num_orders ) {
							echo '<span>' . sprintf( _n( '%d Order', '%d Orders', $num_orders, 'incassoos' ), $num_orders ) . '</span>';
						}
					}

					// Default to dash
					if ( ! $num_activities && ! $num_occasions ) {
						echo '&mdash;';
					}
					break;
				case 'consumers' :
					incassoos_the_collection_consumer_count( $post_id );
					break;
				case 'total' :
					incassoos_the_collection_total( $post_id, true );
					break;
			}

			break;

		// Activity
		case incassoos_get_activity_post_type() :
			switch ( $column ) {
				case 'activity_date' :
					incassoos_the_activity_date( $post_id );
					break;
				case 'participants' :
					incassoos_the_activity_participant_count( $post_id );
					break;
				case 'price' :
					incassoos_the_activity_price( $post_id, true );
					break;
				case 'total' :
					incassoos_the_activity_total( $post_id, true );
					break;
				case 'collection' :

					// Display collection link
					if ( $collection = incassoos_get_activity_collection_id( $post_id ) ) {
						printf( '<a href="%s" title="%s">%s</a>',
							esc_url( add_query_arg( 'collection', $collection, $posts_url ) ),
							esc_attr( sprintf( __( 'View activities of &#8220;%s&#8221;', 'incassoos' ), incassoos_get_collection_title( $collection ) ) ),
							incassoos_get_collection_title( $collection )
						);

					// Default to dash
					} else {
						echo '&mdash;';
					}
					break;
			}

			break;

		// Occasion
		case incassoos_get_occasion_post_type() :
			switch ( $column ) {
				case 'occasion_date' :
					incassoos_the_occasion_date( $post_id );
					break;
				case 'orders' :
					incassoos_the_occasion_order_count( $post_id );
					printf(
						'<a class="view" href="%s" aria-label="%s">%s</a>',
						esc_url( add_query_arg( array( 'post_type' => incassoos_get_order_post_type(), 'occasion' => $post_id ), admin_url( 'edit.php' ) ) ),
						/* translators: %s: Post title */
						esc_attr( sprintf( __( 'View orders of &#8220;%s&#8221;', 'incassoos' ), _draft_or_post_title( $post_id ) ) ),
						__( 'View' )
					);
					break;
				case 'consumers' :
					incassoos_the_occasion_consumer_count( $post_id );
					break;
				case 'total' :
					incassoos_the_occasion_total( $post_id, true );
					break;
				case 'collection' :

					// Display collection link
					if ( $collection = incassoos_get_occasion_collection_id( $post_id ) ) {
						printf( '<a href="%s" title="%s">%s</a>',
							esc_url( add_query_arg( 'collection', $collection, $posts_url ) ),
							esc_attr( sprintf( __( 'View occasions of &#8220;%s&#8221;', 'incassoos' ), incassoos_get_collection_title( $collection ) ) ),
							incassoos_get_collection_title( $collection )
						);

					// Default to dash
					} else {
						echo '&mdash;';
					}
					break;
			}

			break;

		// Order
		case incassoos_get_order_post_type() :
			switch ( $column ) {
				case 'consumer' :

					// Get consumer or type
					$consumer  = incassoos_get_order_consumer_id( $post_id );
					$query_arg = 'consumer';
					if ( ! $consumer ) {
						$consumer  = incassoos_get_order_consumer_type( $post_id );
						$query_arg = 'consumer_type';
					}

					// Display consumer (type) filter link
					if ( $consumer ) {
						printf(
							'<strong><span class="title"><a href="%s">%s</a></span>' . _post_states( get_post( $post_id ), false ) . '</strong>',
							esc_url( add_query_arg( $query_arg, $consumer, $posts_url ) ),
							incassoos_get_order_consumer_title( $post_id )
						);

					// Default to dash
					} else {
						echo '<strong><span class="title">&mdash;</span></strong>';
					}
					break;
				case 'products' :
					incassoos_the_order_product_count( $post_id );
					break;
				case 'total' :
					incassoos_the_order_total( $post_id, true );
					break;
				case 'occasion' :

					// Display Occasion link
					if ( $occasion = incassoos_get_order_occasion( $post_id ) ) {
						printf(
							'<a href="%s" title="%s">%s</a>',
							esc_url( add_query_arg( 'occasion', $occasion->ID, $posts_url ) ),
							esc_attr( incassoos_get_occasion_collection_hint( $occasion ) ),
							incassoos_get_occasion_title( $occasion )
						);
					} else {
						echo '&mdash;';
					}
					break;
			}

			break;

		// Product
		case incassoos_get_product_post_type() :
			switch ( $column ) {
				case 'price' :
					incassoos_the_product_price( $post_id, true );
					break;
			}

			break;
	}
}

/**
 * Output the default admin meta column content
 *
 * @since 1.0.0
 *
 * @param string $column Column name
 * @param int $post_id Post ID
 */
function incassoos_admin_posts_custom_meta_column( $column, $post_id ) {
	$meta = get_post_meta( $post_id, $column, true );
	echo ( ! empty( $meta ) ) ? $meta : '&mdash;';
}

/**
 * Modify the post's row actions in the admin posts list
 *
 * @since 1.0.0
 *
 * @param  array $actions Post row actions
 * @param  WP_Post $post Post object
 * @return array Post row actions
 */
function incassoos_admin_post_row_actions( $actions, $post ) {
	$post_type_object = get_post_type_object( $post->post_type );

	// Collection
	if ( incassoos_get_collection( $post ) ) {

		// Provide view link for locked
		if ( incassoos_is_collection_locked( $post ) || ! current_user_can( $post_type_object->cap->edit_posts ) ) {
			$actions['view'] = sprintf(
				'<a href="%s" aria-label="%s">%s</a>',
				esc_url( add_query_arg( array( 'post' => $post->ID, 'action' => 'view' ), admin_url( 'post.php' ) ) ),
				/* translators: %s: Post title */
				esc_attr( sprintf( __( 'View &#8220;%s&#8221;' ), _draft_or_post_title( $post ) ) ),
				__( 'View' )
			);
		}

		// Disable inline editing
		unset( $actions['inline hide-if-no-js'] );
	}

	// Activity
	if ( incassoos_get_activity( $post ) ) {

		// Provide view link for collected
		if ( incassoos_is_activity_collected( $post ) || ! current_user_can( $post_type_object->cap->edit_posts ) ) {
			$actions['view'] = sprintf(
				'<a href="%s" aria-label="%s">%s</a>',
				esc_url( add_query_arg( array( 'post' => $post->ID, 'action' => 'view' ), admin_url( 'post.php' ) ) ),
				/* translators: %s: Post title */
				esc_attr( sprintf( __( 'View &#8220;%s&#8221;' ), _draft_or_post_title( $post ) ) ),
				__( 'View' )
			);
		}

		// Disable inline editing
		unset( $actions['inline hide-if-no-js'] );

		// Duplicate
		if ( current_user_can( $post_type_object->cap->create_posts ) ) {
			$actions['inc_duplicate'] = sprintf(
				'<a href="%s" aria-label="%s">%s</a>',
				esc_url( wp_nonce_url( add_query_arg( array( 'post' => $post->ID, 'action' => 'inc_duplicate' ), admin_url( 'post.php' ) ), 'duplicate-activity_' . $post->ID ) ),
				/* translators: %s: Post title */
				esc_attr( sprintf( __( 'Duplicate &#8220;%s&#8221;', 'incassoos' ), _draft_or_post_title( $post ) ) ),
				__( 'Duplicate', 'incassoos' )
			);
		}
	}

	// Occasion
	if ( incassoos_get_occasion( $post ) ) {

		// Provide view link for locked
		if ( incassoos_is_occasion_locked( $post ) || ! current_user_can( $post_type_object->cap->edit_posts ) ) {
			$actions['view'] = sprintf(
				'<a href="%s" aria-label="%s">%s</a>',
				esc_url( add_query_arg( array( 'post' => $post->ID, 'action' => 'view' ), admin_url( 'post.php' ) ) ),
				/* translators: %s: Post title */
				esc_attr( sprintf( __( 'View &#8220;%s&#8221;' ), _draft_or_post_title( $post ) ) ),
				__( 'View' )
			);
		}

		// Disable inline editing
		unset( $actions['inline hide-if-no-js'] );

		// Close
		if ( current_user_can( 'close_incassoos_occasion', $post->ID ) ) {
			$actions['inc_close'] = sprintf(
				'<a href="%s" aria-label="%s">%s</a>',
				esc_url( wp_nonce_url( add_query_arg( array( 'post' => $post->ID, 'action' => 'inc_close' ), admin_url( 'post.php' ) ), 'close-occasion_' . $post->ID ) ),
				/* translators: %s: Post title */
				esc_attr( sprintf( __( 'Close &#8220;%s&#8221;', 'incassoos' ), _draft_or_post_title( $post ) ) ),
				_x( 'Close', 'Opposite of reopen', 'incassoos' )
			);
		}

		// Reopen
		if ( current_user_can( 'reopen_incassoos_occasion', $post->ID ) ) {
			$actions['inc_reopen'] = sprintf(
				'<a href="%s" aria-label="%s">%s</a>',
				esc_url( wp_nonce_url( add_query_arg( array( 'post' => $post->ID, 'action' => 'inc_reopen' ), admin_url( 'post.php' ) ), 'reopen-occasion_' . $post->ID ) ),
				/* translators: %s: Post title */
				esc_attr( sprintf( __( 'Reopen &#8220;%s&#8221;', 'incassoos' ), _draft_or_post_title( $post ) ) ),
				__( 'Reopen', 'incassoos' )
			);
		}
	}

	// Order
	if ( incassoos_get_order( $post ) ) {

		$view_action = sprintf(
			'<a href="%s" aria-label="%s">%s</a>',
			esc_url( add_query_arg( array( 'post' => $post->ID, 'action' => 'view' ), admin_url( 'post.php' ) ) ),
			/* translators: %s: Post title */
			esc_attr( sprintf( __( 'View &#8220;%s&#8221;' ), _draft_or_post_title( $post ) ) ),
			__( 'View' )
		);

		// Make edit action for viewing
		if ( isset( $actions['edit'] ) ) {
			$actions['edit'] = $view_action;

		// Insert at first position
		} else {
			$actions = array( 'view' => $view_action ) + $actions;
		}

		// Disable inline editing
		unset( $actions['inline hide-if-no-js'] );
	}

	// Product
	if ( incassoos_get_product( $post ) ) {

		// Disable inline editing
		unset( $actions['inline hide-if-no-js'] );
	}

	return $actions;
}

/**
 * Display filter dropdowns for the posts list table
 *
 * @since 1.0.0
 *
 * @param  string $post_type Post type
 * @param  string $which Location of the table nav
 */
function incassoos_admin_restrict_manage_posts( $post_type, $which ) {

	// Bail when we're not in the top
	if ( 'top' !== $which ) {
		return;
	}

	// Activity
	if ( incassoos_get_activity_post_type() === $post_type ) {
		$taxonomy = incassoos_get_activity_cat_tax_id();
		$term     = get_query_var( $taxonomy ) ?: get_query_var( 'term' );
		if ( is_string( $term ) && ! is_numeric( $term ) ) {
			$term = get_term_by( 'slug', $term, $taxonomy );
			if ( $term ) {
				$term = $term->term_id;
			}
		}

		$dropdown_options = array(
			'taxonomy'        => $taxonomy,
			'name'            => $taxonomy,
			'hide_if_empty'   => true,
			'show_option_all' => get_taxonomy( $taxonomy )->labels->all_items,
			'hide_empty'      => 0,
			'hierarchical'    => 1,
			'show_count'      => 0,
			'orderby'         => 'name',
			'selected'        => $term
		);

		echo '<label class="screen-reader-text" for="term">' . _x( 'Filter by category', 'Activity category', 'incassoos' ) . '</label>';
		wp_dropdown_categories( $dropdown_options );
	}

	// Occasion
	if ( incassoos_get_occasion_post_type() === $post_type ) {
		$taxonomy = incassoos_get_occasion_type_tax_id();
		$term     = get_query_var( $taxonomy ) ?: get_query_var( 'term' );
		if ( is_string( $term ) && ! is_numeric( $term ) ) {
			$term = get_term_by( 'slug', $term, $taxonomy );
			if ( $term ) {
				$term = $term->term_id;
			}
		}

		$dropdown_options = array(
			'taxonomy'        => $taxonomy,
			'name'            => $taxonomy,
			'hide_if_empty'   => true,
			'show_option_all' => get_taxonomy( $taxonomy )->labels->all_items,
			'hide_empty'      => 0,
			'hierarchical'    => 1,
			'show_count'      => 0,
			'orderby'         => 'name',
			'selected'        => $term
		);

		echo '<label class="screen-reader-text" for="term">' . _x( 'Filter by type', 'Occasion type', 'incassoos' ) . '</label>';
		wp_dropdown_categories( $dropdown_options );
	}

	// Product
	if ( incassoos_get_product_post_type() === $post_type ) {
		$taxonomy = incassoos_get_product_cat_tax_id();
		$term     = get_query_var( $taxonomy ) ?: get_query_var( 'term' );
		if ( is_string( $term ) && ! is_numeric( $term ) ) {
			$term = get_term_by( 'slug', $term, $taxonomy );
			if ( $term ) {
				$term = $term->term_id;
			}
		}

		$dropdown_options = array(
			'taxonomy'        => $taxonomy,
			'name'            => $taxonomy,
			'hide_if_empty'   => true,
			'show_option_all' => get_taxonomy( $taxonomy )->labels->all_items,
			'hide_empty'      => 0,
			'hierarchical'    => 1,
			'show_count'      => 0,
			'orderby'         => 'name',
			'selected'        => $term
		);

		echo '<label class="screen-reader-text" for="term">' . _x( 'Filter by category', 'Product category', 'incassoos' ) . '</label>';
		wp_dropdown_categories( $dropdown_options );
	}
}

/** Single Post *********************************************************/

/**
 * Return whether the admin post should be viewed only, ie. remove editing elements.
 *
 * This mark helps the plugin know which pages require layout modifications
 * to support view-only style admin interfaces.
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_admin_is_post_view'
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @return bool View post only.
 */
function incassoos_admin_is_post_view( $post = 0 ) {
	$post   = get_post( $post );
	$retval = false;

	if ( $post ) {
		$post_type_object = get_post_type_object( $post->post_type );
		$retval           = ! current_user_can( $post_type_object->cap->edit_post, $post->ID );
	}

	return (bool) apply_filters( 'incassoos_admin_is_post_view', $retval, $post );
}

/**
 * When loading admin's post.php switch editing and viewing action
 *
 * @since 1.0.0
 */
function incassoos_admin_load_post_view() {

	// Bail when not a get request
	if ( 'GET' != strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	// Get the post query var
	$post_id = isset( $_REQUEST['post'] ) ? (int) $_REQUEST['post'] : false;

	// Bail when post or action query var are missing
	if ( ! $post_id || ! isset( $_REQUEST['action'] ) )
		return;

	// Define base request without action
	$base_args = array_diff_key( $_GET, array_flip( array( 'action' ) ) );
	$base_url  = add_query_arg( $base_args, admin_url( 'post.php' ) );

	// Edit: Redirect to view mode when the post is already collected or an Order
	if ( 'edit' === $_REQUEST['action'] && incassoos_admin_is_post_view( $post_id ) ) {
		wp_redirect( add_query_arg( array( 'action' => 'view' ), $base_url ) );
		exit();
	}

	// View: Redirect to edit mode when the post is not collected and not an Order
	if ( 'view' === $_REQUEST['action'] && ! incassoos_admin_is_post_view( $post_id ) ) {
		wp_redirect( add_query_arg( array( 'action' => 'edit' ), $base_url ) );
		exit();
	}
}

/**
 * Reset the post action parameter when unintentionally overwritten
 *
 * In the post edit form the 'action' parameter may be defined twice, because of the plugin's
 * custom postaction logic. This may result in overwriting WP's default 'action' parameter for
 * updating the post. In this function we check when an actual update is intended, so the
 * original action is used.
 *
 * @since 1.0.0
 */
function incassoos_admin_handle_post_action() {

	// Bail when not a post request
	if ( 'POST' != strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	// Detect when an actual update is intended while the 'action' parameter is overwritten with 'inc_postaction'.
	// Reset the 'action' parameter to the original action.
	if ( isset( $_POST['save'] ) && isset( $_POST['action'] ) && 'inc_postaction' === $_POST['action'] ) {
		$_REQUEST['action'] = $_POST['action'] = $_POST['originalaction'];
	}
}

/**
 * Display the admin page for the post's view action
 *
 * This is an alternative post admin page without any editing options.
 *
 * @see wp-admin/post.php `$action=edit`
 *
 * @since 1.0.0
 *
 * @param  mixed $post_id Post ID
 */
function incassoos_admin_post_action_view( $post_id ) {
	global $parent_file, $submenu_file, $post_new_file, $post, $post_type,
	       $post_type_object, $title, $typenow, $post_ID, $user_ID;

	// Bail when the post is not in view mode
	if ( ! incassoos_admin_is_post_view( $post_id ) )
		return;

	if ( ! in_array( $typenow, get_post_types( array( 'show_ui' => true ) ), true ) ) {
		wp_die( __( 'Sorry, you are not allowed to view posts in this post type.', 'incassoos' ) );
	}

	if ( ! current_user_can( $post_type_object->cap->view_post, $post_id ) ) {
		wp_die( __( 'Sorry, you are not allowed to view this item.', 'incassoos' ) );
	}

	if ( 'trash' == $post->post_status )
		wp_die( __( 'You can&#8217;t view this item because it is in the Trash. Please restore it and try again.', 'incassoos' ) );

	if ( isset( $post_type_object ) && $post_type_object->show_in_menu && $post_type_object->show_in_menu !== true ) {
		$parent_file = $post_type_object->show_in_menu;
	} else {
		$parent_file = "edit.php?post_type=$post_type";
	}

	$submenu_file = "edit.php?post_type=$post_type";
	$post_new_file = "post-new.php?post_type=$post_type";

	$title = $post_type_object->labels->view_item;
	$post = get_post($post_id, OBJECT, 'edit');

	if ( post_type_supports($post_type, 'comments') ) {
		wp_enqueue_script('admin-comments');
		enqueue_comment_hotkeys_js();
	}

	// Replace default title UI
	if ( post_type_supports( $post_type, 'title' ) || incassoos_get_order( $post ) ) {
		add_action( 'edit_form_after_title', 'incassoos_admin_view_form_after_title' );
		remove_post_type_support( $post_type, 'title' );
	}

	// Replace default content UI
	if ( post_type_supports( $post_type, 'editor' ) ) {
		add_action( 'edit_form_after_editor', 'incassoos_admin_view_form_after_editor' );
		remove_post_type_support( $post_type, 'editor' );
	}

	// Load admin post UI
	include( ABSPATH . 'wp-admin/edit-form-advanced.php' );
	include( ABSPATH . 'wp-admin/admin-footer.php' );

	exit();
}

/**
 * Parse the admin close action for an Occasion
 *
 * @since 1.0.0
 *
 * @param  mixed $post_id Post ID
 */
function incassoos_admin_post_action_close( $post_id ) {
	$post = incassoos_get_occasion( $post_id );

	// Bail when the post is not an Occasion
	if ( ! $post )
		return;

	// Nonce check
	check_admin_referer( 'close-occasion_' . $post->ID );

	// Bail when the user cannot close
	if ( ! current_user_can( 'close_incassoos_occasion', $post->ID ) ) {
		wp_die( __( 'Sorry, you are not allowed to close this item.', 'incassoos' ) );
	}

	// Bail when the Occasion is trashed
	if ( 'trash' == $post->post_status ) {
		wp_die( __( 'You can&#8217;t close this item because it is in the Trash. Please restore it and try again.', 'incassoos' ) );
	}

	// Stage-it
	$success = incassoos_close_occasion( $post );

	// Something went wrong
	if ( ! $success ) {
		wp_die( __( 'Sorry, something went wrong. The requested action could not be executed.', 'incassoos' ) );
	}

	// Redirect to Occasion page
	wp_redirect( add_query_arg( array( 'message' => 11 ), incassoos_get_occasion_url( $post ) ) );
	exit();
}

/**
 * Parse the admin reopen action for an Occasion
 *
 * @since 1.0.0
 *
 * @param  mixed $post_id Post ID
 */
function incassoos_admin_post_action_reopen( $post_id ) {
	$post = incassoos_get_occasion( $post_id );

	// Bail when the post is not an Occasion
	if ( ! $post )
		return;

	// Nonce check
	check_admin_referer( 'reopen-occasion_' . $post->ID );

	// Bail when the user cannot reopen
	if ( ! current_user_can( 'reopen_incassoos_occasion', $post->ID ) ) {
		wp_die( __( 'Sorry, you are not allowed to reopen this item.', 'incassoos' ) );
	}

	// Bail when the Occasion is trashed
	if ( 'trash' == $post->post_status ) {
		wp_die( __( 'You can&#8217;t reopen this item because it is in the Trash. Please restore it and try again.', 'incassoos' ) );
	}

	// Stage-it
	$success = incassoos_reopen_occasion( $post );

	// Something went wrong
	if ( ! $success ) {
		wp_die( __( 'Sorry, something went wrong. The requested action could not be executed.', 'incassoos' ) );
	}

	// Redirect to Occasion page
	wp_redirect( add_query_arg( array( 'message' => 12 ), incassoos_get_occasion_url( $post ) ) );
	exit();
}

/**
 * Parse the admin stage action for a Collection
 *
 * @since 1.0.0
 *
 * @param  mixed $post_id Post ID
 */
function incassoos_admin_post_action_stage( $post_id ) {
	$post = incassoos_get_collection( $post_id );

	// Bail when the post is not a Collection
	if ( ! $post )
		return;

	// Nonce check
	check_admin_referer( 'stage-collection_' . $post->ID );

	// Bail when the user cannot collect
	if ( ! current_user_can( 'stage_incassoos_collection', $post->ID ) ) {
		wp_die( __( 'Sorry, you are not allowed to collect this item.', 'incassoos' ) );
	}

	// Bail when the Collection is trashed
	if ( 'trash' == $post->post_status ) {
		wp_die( __( 'You can&#8217;t stage this item because it is in the Trash. Please restore it and try again.', 'incassoos' ) );
	}

	// Stage-it
	$success = incassoos_stage_collection( $post );

	// Something went wrong
	if ( ! $success ) {
		wp_die( __( 'Sorry, something went wrong. The requested action could not be executed.', 'incassoos' ) );
	}

	// Redirect to Collection page
	wp_redirect( add_query_arg( array( 'message' => 11 ), incassoos_get_collection_url( $post ) ) );
	exit();
}

/**
 * Parse the admin unstage action for a Collection
 *
 * @since 1.0.0
 *
 * @param  mixed $post_id Post ID
 */
function incassoos_admin_post_action_unstage( $post_id ) {
	$post = incassoos_get_collection( $post_id );

	// Bail when the post is not a Collection
	if ( ! $post )
		return;

	// Nonce check
	check_admin_referer( 'unstage-collection_' . $post->ID );

	// Bail when the user cannot collect
	if ( ! current_user_can( 'unstage_incassoos_collection', $post->ID ) ) {
		wp_die( __( 'Sorry, you are not allowed to unstage this item.', 'incassoos' ) );
	}

	// Unstage-it
	$success = incassoos_unstage_collection( $post );

	// Something went wrong
	if ( ! $success ) {
		wp_die( __( 'Sorry, something went wrong. The requested action could not be executed.', 'incassoos' ) );
	}

	// Redirect to Collection page
	wp_redirect( add_query_arg( array( 'message' => 12 ), incassoos_get_collection_url( $post ) ) );
	exit();
}

/**
 * Parse the admin collect action for a Collection
 *
 * @since 1.0.0
 *
 * @param  mixed $post_id Post ID
 */
function incassoos_admin_post_action_collect( $post_id ) {
	$post = incassoos_get_collection( $post_id );

	// Bail when the post is not a Collection
	if ( ! $post )
		return;

	// Nonce check
	check_admin_referer( 'collect-collection_' . $post->ID );

	// Bail when the user cannot collect
	if ( ! current_user_can( 'collect_incassoos_collection', $post->ID ) ) {
		wp_die( __( 'Sorry, you are not allowed to collect this item.', 'incassoos' ) );
	}

	// Bail when the Collection is trashed
	if ( 'trash' == $post->post_status ) {
		wp_die( __( 'You can&#8217;t collect this item because it is in the Trash. Please restore it and try again.', 'incassoos' ) );
	}

	// Collect-it
	$success = incassoos_collect_collection( $post );

	// Something went wrong
	if ( ! $success ) {
		wp_die( __( 'Sorry, something went wrong. The requested action could not be executed.', 'incassoos' ) );
	}

	// Redirect to Collection page
	wp_redirect( add_query_arg( array( 'message' => 13 ), incassoos_get_collection_url( $post ) ) );
	exit();
}

/**
 * Parse the admin duplicate action for an Activity
 *
 * @since 1.0.0
 *
 * @param  mixed $post_id Post ID
 */
function incassoos_admin_post_action_duplicate( $post_id ) {
	$post = incassoos_get_activity( $post_id );

	// Bail when the post is not an Activity
	if ( ! $post )
		return;

	// Nonce check
	check_admin_referer( 'duplicate-activity_' . $post->ID );

	$post_type_object = get_post_type_object( $post->post_type );

	// Bail when the user cannot create Activities
	if ( ! current_user_can( $post_type_object->cap->create_posts ) ) {
		wp_die(
			'<h1>' . __( 'Cheatin&#8217; uh?' ) . '</h1>' .
			'<p>' . __( 'Sorry, you are not allowed to create posts as this user.' ) . '</p>',
			403
		);
	}

	// Duplicate-it
	$success = incassoos_duplicate_post( $post );

	// Something went wrong
	if ( ! $success ) {
		wp_die( __( 'Sorry, something went wrong. The requested action could not be executed.', 'incassoos' ) );
	}

	// Redirect to the new Activity page
	wp_redirect( incassoos_get_activity_url( $success ) );
	exit();
}

/**
 * Modify the admin post updated messages
 *
 * @since 1.0.0
 *
 * @param  array $messages Post updated messages
 * @return array Post updated messages
 */
function incassoos_admin_post_updated_messages( $messages ) {

	// Collection
	$messages[ incassoos_get_collection_post_type() ] = array(
		 1 => __( 'Collection updated.',   'incassoos' ),
		 4 => __( 'Collection updated.',   'incassoos' ),
		 6 => __( 'Collection created.',   'incassoos' ),
		 7 => __( 'Collection saved.',     'incassoos' ),
		 8 => __( 'Collection submitted.', 'incassoos' ),

		// Custom
		11 => __( 'Collection staged.',               'incassoos' ),
		12 => __( 'Collection unstaged.',             'incassoos' ),
		13 => __( 'Collection collected.',            'incassoos' ),
		14 => __( 'Collection test email sent.',      'incassoos' ),
		15 => __( 'Collection consumer emails sent.', 'incassoos' ),

		// Error codes
		'incassoos_empty_title'   => __( 'Empty title.',              'incassoos' ),
		'incassoos_empty_content' => __( 'Empty collection message.', 'incassoos' ),
	);

	// Activity
	$messages[ incassoos_get_activity_post_type() ] = array(
		 1 => __( 'Activity updated.',   'incassoos' ),
		 4 => __( 'Activity updated.',   'incassoos' ),
		 6 => __( 'Activity created.',   'incassoos' ),
		 7 => __( 'Activity saved.',     'incassoos' ),
		 8 => __( 'Activity submitted.', 'incassoos' ),

		// Error codes
		'incassoos_empty_title'   => __( 'Empty title.',           'incassoos' ),
		'incassoos_invalid_date'  => __( 'Invalid activity date.', 'incassoos' ),
		'incassoos_empty_price'   => __( 'Empty price.',           'incassoos' ),
		'incassoos_invalid_price' => __( 'Invalid price.',         'incassoos' ),
	);

	// Occasion
	$messages[ incassoos_get_occasion_post_type() ] = array(
		 1 => __( 'Occasion updated.',   'incassoos' ),
		 4 => __( 'Occasion updated.',   'incassoos' ),
		 6 => __( 'Occasion created.',   'incassoos' ),
		 7 => __( 'Occasion saved.',     'incassoos' ),
		 8 => __( 'Occasion submitted.', 'incassoos' ),

		// Custom
		11 => __( 'Occasion closed.',    'incassoos' ),
		12 => __( 'Occasion reopened.',  'incassoos' ),

		// Error codes
		'incassoos_empty_title'  => __( 'Empty title.',           'incassoos' ),
		'incassoos_empty_date'   => __( 'Empty occasion date.',   'incassoos' ),
		'incassoos_invalid_date' => __( 'Invalid occasion date.', 'incassoos' ),
	);

	$time_lock = incassoos_get_order_time_lock();

	// Order
	$messages[ incassoos_get_order_post_type() ] = array(
		 1 => __( 'Order updated.',   'incassoos' ),
		 4 => __( 'Order updated.',   'incassoos' ),
		 6 => __( 'Order created.',   'incassoos' ),
		 7 => __( 'Order saved.',     'incassoos' ),
		 8 => __( 'Order submitted.', 'incassoos' ),

		// Error codes
		'incassoos_order_time_locked'       => $time_lock
			? sprintf( __( 'Sorry, the order cannot be edited beyond %d minutes after initial creation.', 'incassoos' ), $time_lock )
			: __( 'Sorry, the order cannot be edited after initial creation.', 'incassoos' ),
		'incassoos_order_invalid_parent'    => __( 'Invalid occasion.',                      'incassoos' ),
		'incassoos_order_locked_occasion'   => __( 'The occasion is closed for new orders.', 'incassoos' ),
		'incassoos_user_invalid_id_or_type' => __( 'Invalid consumer ID or consumer type.',  'incassoos' ),
		'incassoos_user_invalid_id'         => __( 'Invalid consumer ID.',                   'incassoos' ),
		'incassoos_consumer_invalid_type'   => __( 'Invalid consumer type.',                 'incassoos' ),
		'incassoos_order_invalid_products'  => __( 'Invalid order products.',                'incassoos' ),
	);

	// Product
	$messages[ incassoos_get_product_post_type() ] = array(
		 1 => __( 'Product updated.',   'incassoos' ),
		 4 => __( 'Product updated.',   'incassoos' ),
		 6 => __( 'Product created.',   'incassoos' ),
		 7 => __( 'Product saved.',     'incassoos' ),
		 8 => __( 'Product submitted.', 'incassoos' ),

		// Error codes
		'incassoos_empty_title'   => __( 'Empty title.',   'incassoos' ),
		'incassoos_empty_price'   => __( 'Empty price.',   'incassoos' ),
		'incassoos_invalid_price' => __( 'Invalid price.', 'incassoos' ),
	);

	return $messages;
}

/**
 * Modify the redirect post location
 *
 * @since 1.0.0
 *
 * @param  string $location The destination url
 * @param  int    $post_id  Post ID
 * @return string The destination url
 */
function incassoos_admin_redirect_post_location( $location, $post_id ) {
	$is_save_or_publish = isset( $_POST['save'] ) || isset( $_POST['publish'] );
	$is_autodraft = 'auto-draft' === get_post_status( $post_id );

	// Save post in admin
	if ( $is_save_or_publish && $is_autodraft ) {

		// Validate post
		$validated = incassoos_validate_post( $_POST, get_post_type( $post_id ) );

		// Get the reported validation error. Return to sender.
		if ( is_wp_error( $validated ) ) {
			$location = add_query_arg( 'error', $validated->get_error_code(), wp_get_referer() );
		}
	}

	return $location;
}

/**
 * Display admin notices on a post's page
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'post_updated_messages'
 */
function incassoos_admin_post_notices() {

	// Get the screen context
	$screen = get_current_screen();

	// Bail when this is not a plugin's post page
	if ( 'post' !== $screen->base || empty( $screen->post_type ) || ! incassoos_is_plugin_post_type( $screen->post_type ) )
		return;

	// Bail when no error was reported
	if ( ! isset( $_GET['error'] ) || ! $_GET['error'] )
		return;

	// Fetch messages
	$messages = apply_filters( 'post_updated_messages', array(
		'post'       => array(),
		'page'       => array(),
		'attachment' => array()
	) );

	$types = array(
		incassoos_get_collection_post_type() => esc_html__( 'Collection could not be saved: %s', 'incassoos' ),
		incassoos_get_activity_post_type()   => esc_html__( 'Activity could not be saved: %s',   'incassoos' ),
		incassoos_get_occasion_post_type()   => esc_html__( 'Occasion could not be saved: %s',   'incassoos' ),
		incassoos_get_order_post_type()      => esc_html__( 'Order could not be saved: %s',      'incassoos' ),
		incassoos_get_product_post_type()    => esc_html__( 'Product could not be saved: %s',    'incassoos' ),
	);

	// Get error prefix
	if ( isset( $types[ $screen->post_type ] ) ) {
		$prefix = $types[ $screen->post_type ];
	} else {
		$prefix = esc_html__( 'Post could not be saved: %s', 'incassoos' );
	}

	// Get error for display
	if ( isset( $messages[ $screen->post_type ][ $_GET['error'] ] ) ) {
		$error = $messages[ $screen->post_type ][ $_GET['error'] ];
	} else {
		$error = esc_html__( 'Something went wrong.', 'incassoos' );
	}

	echo '<div class="notice error is-dismissible"><p>' . sprintf( $prefix, $error ) . '</p></div>';
}

/**
 * Modify whether to show the checkbox column in the post list table
 *
 * @since 1.0.0
 *
 * @param  bool    $show Whether to show the checkbox
 * @param  WP_Post $post Post object
 * @return bool Whether to show the checkbox
 */
function incassoos_admin_list_table_show_post_cb( $show, $post ) {

	// Activity
	if ( incassoos_get_activity( $post ) ) {
		$post_type_object = get_post_type_object( $post->post_type );

		// Enable bulk edit for duplication
		if ( current_user_can( $post_type_object->cap->create_posts ) ) {
			$show = true;
		}
	}

	return $show;
}

/**
 * Display admin notices on a post's bulk edit page
 *
 * @since 1.0.0
 *
 * @see wp-admin/edit.php
 *
 * @uses apply_filters() Calls 'incassoos_admin_bulk_counts'
 * @uses apply_filters() Calls 'incassoos_admin_bulk_messages'
 */
function incassoos_admin_bulk_notices() {

	// Get the screen context
	$screen    = get_current_screen();
	$post_type = $screen->post_type;

	// Bail when this is not a plugin's bulk edit page
	if ( 'edit' !== $screen->base || empty( $post_type ) || ! incassoos_is_plugin_post_type( $post_type ) )
		return;

	// Fetch counts
	$bulk_counts = apply_filters( 'incassoos_admin_bulk_counts', array(
		'duplicated' => isset( $_REQUEST['duplicated'] ) ? $_REQUEST['duplicated'] : 0
	) );

	// Fetch messages
	$bulk_messages = apply_filters( 'incassoos_admin_bulk_messages', array(
		'post'                             => array(
			'duplicated' => _n( '%s post duplicated.', '%s posts duplicated.', $bulk_counts['duplicated'] )
		),
		incassoos_get_activity_post_type() => array(
			'duplicated' => _n( '%s activity duplicated.', '%s activities duplicated.', $bulk_counts['duplicated'] )
		)
	), $bulk_counts );

	$messages = array();
	foreach ( array_filter( $bulk_counts ) as $message => $count ) {
		if ( isset( $bulk_messages[ $post_type ][ $message ] ) ) {
			$messages[] = sprintf( $bulk_messages[ $post_type ][ $message ], number_format_i18n( $count ) );
		} elseif ( isset( $bulk_messages['post'][ $message ] ) ) {
			$messages[] = sprintf( $bulk_messages['post'][ $message ], number_format_i18n( $count ) );
		}
	}

	if ( $messages ) {
		echo '<div id="message" class="updated notice is-dismissible"><p>' . implode( ' ', $messages ) . '</p></div>';
	}
	unset( $messages );

	$_SERVER['REQUEST_URI'] = remove_query_arg( array_keys( $bulk_counts ), $_SERVER['REQUEST_URI'] );
}

/**
 * Run dedicated hook for the 'inc_postaction' post action on wp-admin/post.php
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'incassoos_admin_{object_type}_{action_type}_{action_id}'
 * @uses do_action() Calls 'incassoos_admin_post_{action_type}_{action_id}'
 *
 * @param int $post_id Post ID
 */
function incassoos_admin_post_action_postaction( $post_id ) {
	$post = get_post( $post_id );

	// Bail when the post is not found
	if ( ! $post ) {
		return;
	}

	$object_type = incassoos_get_object_type( $post->post_type );

	// Bail when this is not ours
	if ( ! incassoos_is_plugin_post_type( $post->post_type ) || empty( $object_type ) ) {
		return;
	}

	// Dynamic nonce check
	check_admin_referer( "postaction_{$object_type}-{$post->ID}", "{$object_type}_postaction_nonce" );

	// Get and dissect action type
	$action       = isset( $_POST['post-action-type'] ) ? $_POST['post-action-type'] : '';
	$action_parts = explode( '-', $action );
	$action_type  = $action_parts[0];
	$action_id    = substr( $action, strlen( $action_type ) + 1 );

	// Handle exports all the same
	if ( 'export' === $action_type ) {

		// Start file export dryrun
		incassoos_admin_export_file( array(
			'post_id'        => $post->ID,
			'export_type_id' => $action_id,
			'dryrun'         => true,
			'decryption_key' => isset( $_POST['action-decryption-key'] ) ? $_POST['action-decryption-key'] : false
		) );
	} else {

		// Run dedicated object type hook
		do_action( "incassoos_admin_{$object_type}_{$action_type}_{$action_id}", $post, $action );

		// Run post type-agnostic hook
		do_action( "incassoos_admin_post_{$action_type}_{$action_id}", $post, $action );
	}

	// Still here? Redirect to the post's page
	wp_redirect( incassoos_get_post_url( $post ) );
	exit();
}

/**
 * Parse the admin test email action for a Collection
 *
 * @since 1.0.0
 *
 * @param  mixed $post_id Post ID
 */
function incassoos_admin_collection_send_test_collect_email( $post_id ) {
	$post = incassoos_get_collection( $post_id );

	// Bail when this is not a Collection
	if ( ! $post )
		return;

	// Bail when the user cannot collect
	if ( ! current_user_can( 'collect_incassoos_collection', $post->ID ) ) {
		wp_die( __( 'Sorry, you are not allowed to collect this item.', 'incassoos' ) );
	}

	// Bail when the Collection is trashed
	if ( 'trash' == $post->post_status ) {
		wp_die( __( 'You can&#8217;t collect this item because it is in the Trash. Please restore it and try again.', 'incassoos' ) );
	}

	// Send-it
	$success = incassoos_send_collection_test_collect_email( $post );

	// Something went wrong
	if ( ! $success ) {
		wp_die( __( 'Sorry, something went wrong. The requested action could not be executed.', 'incassoos' ) );
	}

	// Redirect to Collection page
	wp_redirect( add_query_arg( array( 'message' => 14 ), incassoos_get_collection_url( $post ) ) );
	exit();
}

/**
 * Parse the admin consumer emails action for a Collection
 *
 * @since 1.0.0
 *
 * @param  mixed $post_id Post ID
 */
function incassoos_admin_collection_send_consumer_collect_emails( $post_id ) {
	$post = incassoos_get_collection( $post_id, array( 'is_collected' => true ) );

	// Bail when this is not a Collection
	if ( ! $post )
		return;

	// Bail when the user cannot collect
	if ( ! current_user_can( 'distribute_incassoos_collection', $post->ID ) ) {
		wp_die( __( 'Sorry, you are not allowed to distribute this item.', 'incassoos' ) );
	}

	// Bail when the Collection is trashed
	if ( 'trash' == $post->post_status ) {
		wp_die( __( 'You can&#8217;t collect this item because it is in the Trash. Please restore it and try again.', 'incassoos' ) );
	}

	// Send-it
	$success = incassoos_send_collection_consumer_collect_emails( $post );

	// Something went wrong
	if ( ! $success ) {
		wp_die( __( 'Sorry, something went wrong. The requested action could not be executed.', 'incassoos' ) );
	}

	// Redirect to Collection page
	wp_redirect( add_query_arg( array( 'message' => 15 ), incassoos_get_collection_url( $post ) ) );
	exit();
}

/**
 * Process a file download request
 *
 * The action fires irrespective of whether a `post_id` is provided.
 * Requires the 'file-id' parameter in the $_GET global.
 *
 * @since 1.0.0
 */
function incassoos_admin_post_action_download() {
	$file_id = isset( $_REQUEST['file-id'] ) ? $_REQUEST['file-id'] : false;

	// Bail when post or file is not provided
	if ( ! $file_id ) {
		return;
	}

	// Get the previously stored export details
	$export_details = incassoos_get_export_details( $file_id );

	if ( $export_details ) {

		// Start serving the download file
		incassoos_admin_export_file( $export_details );
	}

	// Setup redirect url
	if ( isset( $_GET['_page'] ) ) {
		$redirect_url = add_query_arg( array( 'page' => esc_attr( $_GET['_page'] ) ), 'admin.php' );
	} elseif ( isset( $_GET['post'] ) ) {
		$redirect_url = add_query_arg( array( 'post' => esc_attr( $_GET['post'] ), 'action' => 'edit' ), admin_url( 'post.php' ) );
	} else {
		$redirect_url = add_query_arg( array( 'page' => 'incassoos' ), admin_url( 'admin.php' ) );
	}

	// Still here? Notify user
	wp_die( sprintf( __( 'Sorry, the file download you requested is expired. <a href="%s">Return to the previous page.</a>', 'incassoos' ), $redirect_url ) );
}

/**
 * Display the logged messages for the post action
 *
 * @since 1.0.0
 */
function incassoos_admin_post_action_notices() {

	// Find (post) context
	$post    = get_post();
	$post_id = $post ? $post->ID : 0;

	// Find whether any feedback was defined
	$feedback = get_transient( "incassoos_admin_post_action_notice-{$post_id}" );

	// Bail when no feedback is logged
	if ( ! $feedback ) {
		return;
	}

	$errors  = isset( $feedback['errors']  ) ? $feedback['errors']  : false;
	$success = isset( $feedback['success'] ) ? $feedback['success'] : false;

	// Display error messages first
	if ( $errors ) : ?>

	<div class="notice notice-error is-dismissible incassoos-notice">
		<p><?php printf(
			/* translators: 1. Error amount 2. Button */
			_n(
				'Error: %2$s',
				'The action could not be executed due to %1$d errors. %2$s',
				count( $errors ),
				'incassoos'
			),
			count( $errors ),
			count( $errors ) > 1
				? sprintf( '<button type="button" class="button-link">%s</button>', esc_html__( 'Show errors', 'incassoos' ) )
				: $errors[0]
		); ?></p>

		<?php if ( count( $errors ) > 1 ) : foreach ( $errors as $message ) : ?>

		<p><?php echo $message; ?></p>

		<?php endforeach; endif; ?>
	</div>

	<?php endif;

	// Display success messages second
	if ( $success ) : ?>

	<div class="notice notice-success is-dismissible incassoos-notice">
		<?php foreach ( $success as $message ) : ?>

		<p><?php echo $message; ?></p>

		<?php endforeach; ?>
	</div>

	<?php endif;

	// Remove logged feedback afterwards
	delete_transient( "incassoos_admin_post_action_notice-{$post_id}" );
}

/** Multiple Posts ******************************************************/

/**
 * Return whether the admin posts should be viewed only, ie. remove editing elements.
 *
 * This mark helps the plugin know which pages require layout modifications
 * to support view-only style admin interfaces.
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_admin_is_posts_view'
 *
 * @param string $post_type Optional. Post type name. Defaults to the page's post type.
 * @return bool View posts only.
 */
function incassoos_admin_is_posts_view( $post_type = '' ) {

	// Default to the page's post type
	if ( ! $post_type && function_exists( 'get_current_screen' ) ) {
		$post_type = get_current_screen()->post_type;
	}

	$retval = false;

	if ( $post_type && incassoos_is_plugin_post_type( $post_type ) ) {
		$post_type_object = get_post_type_object( $post_type );
		$retval           = ! current_user_can( $post_type_object->cap->edit_posts ) && current_user_can( $post_type_object->cap->view_posts );
	}

	return (bool) apply_filters( 'incassoos_admin_is_posts_view', $retval, $post_type );
}

/**
 * When loading admin's post.php switch editing and viewing action
 *
 * @since 1.0.0
 */
function incassoos_admin_load_posts_view() {

	// Bail when not a get request
	if ( 'GET' != strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	// Get the post_type query var
	$post_type = isset( $_REQUEST['post_type'] ) ? $_REQUEST['post_type'] : false;

	// Bail when post type is missing or we're not viewing posts
	if ( ! $post_type || ! incassoos_admin_is_posts_view( $post_type ) )
		return;

	// Define base request without action
	$base_args = array_diff_key( $_GET, array_flip( array( 'action' ) ) );
	$base_url  = add_query_arg( $base_args, admin_url( 'page.php' ) );

	// Load view page
	// This page is a version of edit.php without links or options to edit posts.
	include incassoos()->admin->admin_dir . '/view.php';
}

/** Taxonomies **********************************************************/

/**
 * Modify the term's list table name
 *
 * @since 1.0.0
 *
 * @param  string $name Term name
 * @param  WP_Term $term Term object
 * @return string Term name
 */
function incassoos_admin_filter_term_name( $name, $term ) {
	$a_term = is_a( $term, 'WP_Term' );

	// Taxonomy supports default terms
	if ( $a_term && incassoos_taxonomy_supports_default_terms( $term->taxonomy ) ) {

		// Default term
		if ( incassoos_is_default_term( $term ) ) {
			/* translators: %s: Term name */
			$name = sprintf( __( '%s <span class="status">&mdash; Default</span>', 'incassoos' ), $name );
		}
	}

	// Taxonomy supports archived terms
	if ( $a_term && incassoos_taxonomy_supports_archived_terms( $term->taxonomy ) ) {

		// Archived term
		if ( incassoos_is_term_archived( $term ) ) {
			/* translators: %s: Term name */
			$name = sprintf( __( '%s <span class="status">&mdash; Archived</span>', 'incassoos' ), $name );
		}
	}

	return $name;
}

/**
 * Output the input fields for the 'create term' action form
 *
 * @since 1.0.0
 *
 * @param  string $taxonomy Taxonomy name
 */
function incassoos_admin_taxonomy_add_form_fields( $taxonomy ) {

	// Taxonomy supports default terms
	if ( incassoos_taxonomy_supports_default_terms( $taxonomy ) ) : ?>

	<div class="form-field term-default-wrap">
		<label for="term-default"><?php esc_html_e( 'Default', 'incassoos' ); ?></label>
		<input type="checkbox" id="term-default" name="term-default" value="1" />

		<p class="description"><?php esc_html_e( 'Is it the default item?', 'incassoos' ); ?></p>
	</div>

	<?php endif;

	// Taxonomy supports archived terms
	if ( incassoos_taxonomy_supports_archived_terms( $taxonomy ) ) : ?>

	<div class="form-field term-archived-wrap">
		<label for="term-archived"><?php echo esc_html( _x( 'Archived', 'Term status', 'incassoos' ) ); ?></label>
		<input type="checkbox" id="term-archived" name="term-archived" value="1" />

		<p class="description"><?php esc_html_e( 'When archived, it will not appear on your site along with linked items.', 'incassoos' ); ?></p>
	</div>

	<?php endif;

	// Consumer Type
	if ( incassoos_get_consumer_type_tax_id() === $taxonomy ) :

		// Formatting
		$format_args     = incassoos_get_currency_format_args();
		$min_price_value = 1 / pow( 10, $format_args['decimals'] );
	?>

	<div class="form-field term-spending-limit-wrap">
		<label for="term-spending-limit"><?php esc_html_e( 'Spending limit', 'incassoos' ); ?></label>
		<input type="number" id="term-spending-limit" name="term-spending-limit" class="small-text" min="0" step="<?php echo $min_price_value; ?>" value="0" />

		<p class="description"><?php esc_html_e( 'Spending limit of the item per occasion.', 'incassoos' ); ?></p>
	</div>

	<?php endif;
}

/**
 * Output the input fields for the 'edit term' action form
 *
 * @since 1.0.0
 *
 * @param  WP_Term $term     Term object
 * @param  string  $taxonomy Taxonomy name
 */
function incassoos_admin_taxonomy_edit_form_fields( $term, $taxonomy ) {

	// Taxonomy supports default terms
	if ( incassoos_taxonomy_supports_default_terms( $taxonomy ) ) : ?>

	<tr class="form-field term-default-wrap">
		<th scope="row" valign="top">
			<label for="term-default"><?php esc_html_e( 'Default', 'incassoos' ); ?></label>
		</th>
		<td>
			<input type="checkbox" id="term-default" name="term-default" value="1" <?php checked( get_term_meta( $term->term_id, '_default', true ) ); ?>/>

			<p class="description"><?php esc_html_e( 'Is it the default item?', 'incassoos' ); ?></p>
		</td>
	</tr>

	<?php endif;

	// Taxonomy supports archived terms
	if ( incassoos_taxonomy_supports_archived_terms( $taxonomy ) ) : ?>

	<tr class="form-field term-archived-wrap">
		<th scope="row" valign="top">
			<label for="term-archived"><?php echo esc_html( _x( 'Archived', 'Term status', 'incassoos' ) ); ?></label>
		</th>
		<td>
			<input type="checkbox" id="term-archived" name="term-archived" value="1" <?php checked( get_term_meta( $term->term_id, '_incassoos_archived', true ) ); ?>/>

			<p class="description"><?php esc_html_e( 'When archived, it will not appear on your site along with linked items.', 'incassoos' ); ?></p>
		</td>
	</tr>

	<?php endif;

	// Consumer Type
	if ( incassoos_get_consumer_type_tax_id() === $taxonomy ) :

		// Formatting
		$format_args     = incassoos_get_currency_format_args();
		$min_price_value = 1 / pow( 10, $format_args['decimals'] );
	?>

	<tr class="form-field term-archived-wrap">
		<th scope="row" valign="top">
			<label for="term-spending-limit"><?php esc_html_e( 'Spending limit', 'incassoos' ); ?></label>
		</th>
		<td>
			<input type="number" id="term-spending-limit" name="term-spending-limit" class="small-text" min="0" step="<?php echo $min_price_value; ?>" value="<?php echo get_term_meta( $term->term_id, '_incassoos_spending_limit', true ); ?>" />

			<p class="description"><?php esc_html_e( 'Spending limit of the item per occasion.', 'incassoos' ); ?></p>
		</td>
	</tr>

	<?php endif;
}

/** Actions *************************************************************/

/**
 * Return the admin action types for the post
 *
 * @since 1.0.0
 *
 * @uses apply_filters() 'incassoos_admin_get_{object_type}_action_types'
 *
 * @param  WP_Post|int $post Post object or ID
 * @return array Post action types
 */
function incassoos_admin_get_post_action_types( $post ) {
	$post         = get_post( $post );
	$object_type  = incassoos_get_object_type( $post->post_type ) ?: 'post';

	// Define default action types
	$action_types = array(
		'distribution' => array(
			'name'    => esc_html__( 'Distribution', 'incassoos' ),
			'actions' => array()
		),
		'exporting' => array(
			'name'    => esc_html__( 'Exporting', 'incassoos' ),
			'actions' => array()
		),
		'tools' => array(
			'name'    => esc_html__( 'Tools', 'incassoos' ),
			'actions' => array()
		)
	);

	if ( $post ) {
		switch ( $post->post_type ) {

			// Collection
			case incassoos_get_collection_post_type() :

				// Distribution: send collect test email
				if ( incassoos_is_collection_staged( $post ) ) {
					$action_types['distribution']['actions']['send-test_collect_email'] = array( 'label' => esc_html__( 'Send test-email', 'incassoos' ) );
				}

				// Distribution: send collect consumer emails
				if ( incassoos_is_collection_collected( $post ) ) {
					$sent = incassoos_is_collection_consumer_collect_emails_sent( $post );
					$action_types['distribution']['actions']['send-consumer_collect_emails'] = array(
						'label'                => $sent ? esc_html__( 'Resend consumer emails', 'incassoos' ) : esc_html__( 'Send consumer emails', 'incassoos' ),
						'require_confirmation' => $sent
					);
				}

				break;
		}

		// Exporting
		foreach ( incassoos_get_export_types( $post ) as $export_type_id => $export_type ) {

			// Skip when user cannot export
			if ( ! current_user_can( "export_incassoos_{$object_type}", $post->ID, $export_type_id ) ) {
				continue;
			}

			// Add export action
			$action_types['exporting']['actions']["export-{$export_type_id}"] = array(
				'label'                  => incassoos_get_export_type_label( $export_type_id, 'export_file' ),
				'require_decryption_key' => incassoos_get_export_type_is_decryption_key_required( $export_type_id ) || incassoos_get_export_type_is_decryption_key_optional( $export_type_id )
			);
		}

		// Tools: recalculate total value
		if ( incassoos_is_post_with_total( $post ) ) {
			$action_types['tools']['actions']['tool-recalculate_total'] = array( 'label' => esc_html__( 'Recalculate total value', 'incassoos' ) );
		}
	}

	$action_types = apply_filters( "incassoos_admin_get_{$object_type}_action_types", $action_types, $post );

	// Find non-empty grouped and ungrouped action types
	$grouped_actions   = array_filter( $action_types, function( $el ) { return isset( $el['name'] ) && ! empty( $el['actions'] ); } );
	$ungrouped_actions = array_filter( $action_types, function( $el ) { return ! ( isset( $el['name'] ) && isset( $el['actions'] ) ); } );

	// Append collected ungrouped action types
	if ( ! empty( $ungrouped_actions ) ) {
		$grouped_actions[] = array(
			'name'    => esc_html_x( 'Other', 'Group name', 'incassoos' ),
			'actions' => $ungrouped_actions
		);
	}

	return $grouped_actions;
}

/**
 * Display or return actions dropdown element
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_admin_dropdown_post_action_types'
 *
 * @param  WP_Post|int $post Post object or ID
 * @param  array       $args Dropdown arguments
 * @return string HTML dropdown list of post actions
 */
function incassoos_admin_dropdown_post_action_types( $post, $args = array() ) {
	$post         = get_post( $post );
	$action_types = incassoos_admin_get_post_action_types( $post );
	$output       = '';

	// Bail when post and actions are not found
	if ( ! $post || empty( $action_types ) ) {
		return;
	}

	$parsed_args = wp_parse_args( $args, array(
		'echo'              => 1,
		'id'                => 'post-action-type',
		'class'             => '',
		'tab_index'         => 0,
		'option_none_value' => __( '&mdash; Actions &mdash;', 'incassoos' )
	) );

	$class = esc_attr( $parsed_args['class'] );
	$id    = esc_attr( $parsed_args['id'] );
	$name  = isset( $parsed_args['name'] ) ? esc_attr( $parsed_args['name'] ) : $id;

	$tab_index = $parsed_args['tab_index'];
	$tab_index_attribute = '';
	if ( (int) $tab_index > 0 ) {
		$tab_index_attribute = " tabindex=\"$tab_index\"";
	}

	$output  = "<select id='$id' name='$name' class='$class' $tab_index_attribute>\n";
	$output .= '<option value="-1">' . esc_html( $parsed_args['option_none_value'] ) . '</option>';

	// Grouped actions
	foreach ( $action_types as $group ) {
		if ( ! empty( $group['actions'] ) ) {
			$output .= '<optgroup label="'. esc_attr( $group['name'] ) . '">';
			foreach ( $group['actions'] as $action_id => $args ) {
				$require_confirmation   = isset( $args['require_confirmation'] )   ? ' data-require-confirmation="' . (int) $args['require_confirmation'] . '"'   : '';
				$require_decryption_key = isset( $args['require_decryption_key'] ) ? ' data-require-decryption-key="' . (int) $args['require_decryption_key'] . '"' : '';
				$output .= '<option value="' . esc_attr( $action_id ) . '"' . $require_confirmation . $require_decryption_key . '>' . esc_html( $args['label'] ) . '</option>';
			}
			$output .= '</optgroup>';
		}
	}

	$output .= '</select>';
	$output = apply_filters( 'incassoos_admin_dropdown_post_action_types', $output, $post, $parsed_args );

	if ( $parsed_args['echo'] ) {
		echo $output;
	}

	return $output;
}

/** Nav Menus ***********************************************************/

/**
 * Register the plugin's nav menu metabox
 *
 * @since 1.0.0
 */
function incassoos_admin_add_nav_menu_meta_box() {
	add_meta_box( 'add-incassoos-nav-menu', __( 'Incassoos', 'incassoos' ), 'incassoos_nav_menu_metabox', 'nav-menus', 'side', 'default' );
}

/** Pages ***************************************************************/

/**
 * Modify the list table class name
 *
 * @since 1.0.0
 *
 * @param  string $class_name Class name
 * @param  array $args        List table arguments
 * @return string Class name
 */
function incassoos_admin_list_table_class_name( $class_name, $args ) {

	// Taxonomy: Consumer Type
	if ( isset( $args['screen'] ) && isset( $args['screen']->taxonomy )
		&& incassoos_get_consumer_type_tax_id() === $args['screen']->taxonomy
	) {

		// Include dependencies
		require_once( incassoos()->includes_dir . 'classes/class-incassoos-consumer-types-list-table.php' );

		$class_name = 'Incassoos_Consumer_Types_List_Table';
	}

	return $class_name;
}

/** Exporting ***********************************************************/

/**
 * Process the export file request for a post
 *
 * @since 1.0.0
 *
 * @param array $args Additional export arguments
 * @return bool|WP_Error Export success or error object
 */
function incassoos_admin_export_file( $args = array() ) {
	$parsed_args = wp_parse_args( $args, array(
		'post_id'        => 0,
		'export_type_id' => '',
		'dryrun'         => false,
		'decryption_key' => false
	) );

	// Post context
	$post        = $parsed_args['post_id'] ? get_post( $parsed_args['post_id'] ) : false;
	$object_type = $post ? incassoos_get_object_type( $post->post_type ) : '';

	// Export type
	$export_type = incassoos_get_export_type( $parsed_args['export_type_id'] );
	$feedback    = array();
	$retval      = true;

	// Run checks, break out on error
	do {

		// Bail when the export type does not exist
		if ( ! $export_type ) {
			$retval = new WP_Error( 'incassoos_invalid_export_type', esc_html__( 'Invalid export type selected.', 'incassoos' ) );
			break;
		}

		// Get export class
		$class = $export_type->class_name;
		if ( ! class_exists( $class ) && ! empty( $export_type->class_file ) ) {
			require_once( $export_type->class_file );
		}

		// Bail when the export class is not present
		if ( ! class_exists( $class ) ) {
			$retval = new WP_Error( 'incassoos_export_type_class_not_found', esc_html__( 'Export type file processor could not be found.', 'incassoos' ) );
			break;
		}

		// Bail when the user cannot export the post
		if ( $post && ! current_user_can( "export_incassoos_{$object_type}", $post->ID, $export_type->id ) ) {
			$retval = new WP_Error( 'incassoos_no_access', esc_html__( 'You are not allowed to export data from this post.', 'incassoos' ) );
			break;
		}

		// Is decryption key optional?
		$optional_decryption_key = incassoos_get_export_type_is_decryption_key_optional( $export_type->id );

		// Bail when the decryption key was required but not provided
		if ( incassoos_get_export_type_is_decryption_key_required( $export_type->id ) || $optional_decryption_key ) {

			// When optional, only proceed when key was provided
			if ( ! $optional_decryption_key || ! empty( $parsed_args['decryption_key'] ) ) {

				// Try to set the decryption key
				$result = incassoos_set_decryption_key( $parsed_args['decryption_key'] );

				if ( is_wp_error( $result ) || ! $result ) {
					$retval = $result ?: new WP_Error( 'incassoos_invalid_decryption_key', esc_html__( 'Invalid decryption key provided.', 'incassoos' ) );
					break;
				}
			}
		}

		// Construct file
		$file = new $class( $post );

		// Bail when not setup properly
		if ( ! is_a( $file, 'Incassoos_File_Exporter' ) ) {
			$retval = new WP_Error( 'incassoos_invalid_file_exporter', sprintf( esc_html__( 'File exporter is not of type `%s`.', 'incassoos' ), 'Incassoos_File_Exporter' ) );

		// Bail when construction failed
		} else if ( method_exists( $file, 'has_errors' ) && $file->has_errors() ) {
			$retval = new WP_Error();
			foreach ( $file->get_errors() as $message ) {
				$retval->add( 'incassoos_export_file_error', $message );
			}

		// When dry-running without errors, delay download
		} else if ( $parsed_args['dryrun'] ) {

			// Save export details for download on next page load
			unset( $parsed_args['dryrun'] );
			$file_id = incassoos_save_export_details( $parsed_args );

			// Report feedback
			if ( $file_id ) {

				// Setup download url
				$download_url = add_query_arg( array( 'action' => 'inc_download', 'file-id' => $file_id ), admin_url( 'admin-post.php' ) );
				if ( $post ) {
					$download_url = add_query_arg( 'post', $post->ID, $download_url );
				} elseif ( isset( $_GET['page'] ) ) {
					$download_url = add_query_arg( '_page', esc_attr( $_GET['page'] ), $download_url );
				}

				// Provide download hint
				$feedback['success'] = array( sprintf( __( 'Your download will start shortly&hellip; <a data-autostart-download="1" href="%s">Click here if it doesn\'t.</a>', 'incassoos' ), $download_url ) );
			} else {
				$retval = new WP_Error( 'incassoos_unknown_export_error', esc_html__( 'Something went wrong preparing your download. Please try again.', 'incassoos' ) );
			}

		// Offer file download
		} else {
			$feedback = incassoos_download_text_file( $file );
		}

	} while ( 0 );

	// Collect errors
	if ( is_wp_error( $retval ) ) {
		$feedback['errors'] = $retval->get_error_messages();
	}

	// Log any feedback
	if ( ! empty( $feedback ) ) {
		$feedback_id = $parsed_args['post_id'];
		set_transient( "incassoos_admin_post_action_notice-{$feedback_id}", $feedback );
	}

	return $retval;
}
