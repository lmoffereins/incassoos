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
	$dashboard = add_menu_page(
		esc_html__( 'Incassoos Dashboard', 'incassoos' ),
		esc_html__( 'Incassoos', 'incassoos' ),
		'incassoos_admin_page-incassoos',
		'incassoos',
		'incassoos_admin_page',
		'dashicons-forms',
		50
	);

	// Manage posts
	$hooks[] = incassoos_admin_submenu_post_type( incassoos_get_collection_post_type() );
	$hooks[] = incassoos_admin_submenu_post_type( incassoos_get_activity_post_type()   );
	$hooks[] = incassoos_admin_submenu_post_type( incassoos_get_occasion_post_type()   );
	$hooks[] = incassoos_admin_submenu_post_type( incassoos_get_order_post_type()      );
	$hooks[] = incassoos_admin_submenu_post_type( incassoos_get_product_post_type()    );

	// Consumers page
	$hooks[] = add_submenu_page(
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
	}

	// Register admin page hooks
	add_action( "load-{$dashboard}",                        'incassoos_admin_load_dashboard_page' );
	add_action( 'incassoos_admin_page-incassoos',           'incassoos_admin_dashboard_page'      );
	add_action( 'incassoos_admin_page-incassoos-consumers', 'incassoos_admin_consumers_page'      );
	add_action( 'incassoos_admin_page-incassoos-settings',  'incassoos_admin_settings_page'       );

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

	// Main post types
	if ( in_array( $screen->post_type, array(
		incassoos_get_collection_post_type(),
		incassoos_get_activity_post_type(),
		incassoos_get_occasion_post_type(),
		incassoos_get_order_post_type(),
		incassoos_get_product_post_type(),
	) ) ) {
		$parent_file  = 'incassoos';
		$submenu_file = "edit.php?post_type={$screen->post_type}";

	// Activity specific taxonomies
	} elseif ( in_array( $screen->taxonomy, array(
		incassoos_get_activity_cat_tax_id(),
	) ) ) {
		$parent_file  = 'incassoos';
		$submenu_file = "edit.php?post_type=" . incassoos_get_activity_post_type();

	// Occasion specific taxonomies
	} elseif ( in_array( $screen->taxonomy, array(
		incassoos_get_occasion_type_tax_id(),
	) ) ) {
		$parent_file  = 'incassoos';
		$submenu_file = "edit.php?post_type=" . incassoos_get_occasion_post_type();

	// Product specific taxonomies
	} elseif ( in_array( $screen->taxonomy, array(
		incassoos_get_product_cat_tax_id(),
	) ) ) {
		$parent_file  = 'incassoos';
		$submenu_file = "edit.php?post_type=" . incassoos_get_product_post_type();

	// Default to settings
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
 * @param string $function Optional. Menu file or function. Defaults to the post type's edit.php
 * @return false|string Result from {@see add_submenu_page()}
 */
function incassoos_admin_submenu_post_type( $post_type = '', $function = '' ) {
	if ( ! $post_type_object = get_post_type_object( $post_type ) )
		return false;

	$menu_file = "edit.php?post_type={$post_type}";

	// Remove the default admin menu and its submenus, to prevent
	// the `$parent_file` override in `get_admin_page_parent()`
	remove_menu_page( $menu_file );
	unset( $GLOBALS['submenu'][ $menu_file ] );

	return add_submenu_page(
		'incassoos',
		$post_type_object->label,
		$post_type_object->labels->menu_name,
		$post_type_object->show_ui ? 'exist' : 'do_not_allow',
		! empty( $function ) ? $function : $menu_file
	);
}

/**
 * Add plugin admin submenu page for the given taxonomy
 *
 * @since 1.0.0
 *
 * @param string $taxonomy Taxonomy name
 * @param string $function Optional. Menu file or function. Defaults to the taxonomy's edit-tags.php
 * @return false|string Result from {@see add_submenu_page()}
 */
function incassoos_admin_submenu_taxonomy( $taxonomy = '', $function = '' ) {
	if ( ! $taxonomy = get_taxonomy( $taxonomy ) )
		return false;

	$menu_file = "edit-tags.php?taxonomy={$taxonomy->name}";

	return add_submenu_page(
		'incassoos',
		$taxonomy->labels->name,
		$taxonomy->labels->menu_name,
		$taxonomy->show_ui ? 'exist' : 'do_not_allow',
		! empty( $function ) ? $function : $menu_file
	);
}


/**
 * Remove the individual recount and converter menus.
 * They are grouped together by h2 tabs
 *
 * @since 1.0.0
 */
function incassoos_remove_admin_menu() {
	remove_submenu_page( 'incassoos', 'incassoos-consumers' );
	remove_submenu_page( 'incassoos', 'incassoos-settings'  );
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
 * Output the contents of the main admin page
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'incassoos_admin_page-{$page}'
 */
function incassoos_admin_page() { ?>

	<div class="wrap">

		<h1><?php esc_html_e( 'Incassoos', 'incassoos' ); ?></h1>

		<h2 class="nav-tab-wrapper"><?php incassoos_admin_page_tabs(); ?></h2>

		<?php do_action( 'incassoos_admin_page-' . incassoos_admin_page_get_current_page() ); ?>

	</div>

	<?php

}

/**
 * Display the admin settings page tabs items
 *
 * @since 1.0.0
 */
function incassoos_admin_page_tabs() {

	// Get the admin pages
	$pages = incassoos_admin_page_get_pages();
	$page  = incassoos_admin_page_get_current_page();

	// Walk registered pages
	foreach ( $pages as $slug => $label ) {

		// Skip empty pages
		if ( empty( $label ) )
			continue;

		// Print the tab item
		printf( '<a class="nav-tab%s" href="%s">%s</a>',
			( $page === $slug ) ? ' nav-tab-active' : '',
			esc_url( add_query_arg( array( 'page' => $slug ), admin_url( 'admin.php' ) ) ),
			$label
		);
	}
}

/**
 * Return the admin page pages
 *
 * @since 0.0.7
 *
 * @uses apply_filters() Calls 'incassoos_admin_page_get_pages'
 * @return array Tabs as $page-slug => $label
 */
function incassoos_admin_page_get_pages() {

	// Setup return value
	$pages = array(
		'incassoos'           => esc_html__( 'Dashboard', 'incassoos' ),
		'incassoos-consumers' => esc_html__( 'Consumers', 'incassoos' )
	);

	// Add the settings page
	if ( incassoos_admin_page_has_settings( 'incassoos' ) ) {
		$pages['incassoos-settings'] = esc_html__( 'Settings', 'incassoos' );
	}

	$pages = (array) apply_filters( 'incassoos_admin_page_get_pages', $pages );

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
function incassoos_admin_page_has_pages() {
	return (bool) incassoos_admin_page_get_pages();
}

/**
 * Return the current admin page
 *
 * @since 1.0.0
 *
 * @return string The current admin page. Defaults to the first page.
 */
function incassoos_admin_page_get_current_page() {

	$pages = array_keys( incassoos_admin_page_get_pages() );
	$page  = ( isset( $_GET['page'] ) && in_array( $_GET['page'], $pages ) ) ? $_GET['page'] : false;

	// Default to the first page
	if ( ! $page && $pages ) {
		$page = reset( $pages );
	}

	return $page;
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

	switch ( get_current_screen()->post_type ) {

		// Activity
		case incassoos_get_activity_post_type() :

			$tax_object = get_taxonomy( incassoos_get_activity_cat_tax_id() );

			// Display link to manage categories
			if ( current_user_can( $tax_object->cap->manage_terms ) ) {
				printf( '<div class="alignleft actions incassoos-activity-cat-link"><a href="%s" class="wp-core-ui button">%s</a></div>', 'edit-tags.php?taxonomy=' . $tax_object->name, esc_html__( 'Manage Activity Categories', 'incassoos' ) );
			}

			break;

		// Occasion
		case incassoos_get_occasion_post_type() :

			$tax_object = get_taxonomy( incassoos_get_occasion_type_tax_id() );

			// Display link to manage types
			if ( current_user_can( $tax_object->cap->manage_terms ) ) {
				printf( '<div class="alignleft actions incassoos-occasion-type-link"><a href="%s" class="wp-core-ui button">%s</a></div>', 'edit-tags.php?taxonomy=' . $tax_object->name, esc_html__( 'Manage Occasion Types', 'incassoos' ) );
			}

			break;

		// Product
		case incassoos_get_product_post_type() :

			$tax_object = get_taxonomy( incassoos_get_product_cat_tax_id() );

			// Display link to manage categories
			if ( current_user_can( $tax_object->cap->manage_terms ) ) {
				printf( '<div class="alignleft actions incassoos-product-cat-link"><a href="%s" class="wp-core-ui button">%s</a></div>', 'edit-tags.php?taxonomy=' . $tax_object->name, esc_html__( 'Manage Product Categories', 'incassoos' ) );
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
				'assets'    => esc_html_x( 'Assets',    'Admin column', 'incassoos' ),
				'consumers' => esc_html_x( 'Consumers', 'Admin column', 'incassoos' ),
				'total'     => esc_html_x( 'Total',     'Admin column', 'incassoos' ),
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

	switch ( $post_type ) {

		// Collection
		case incassoos_get_collection_post_type() :
			switch ( $column ) {
				case 'assets' :
					$num_activities = incassoos_get_collection_activity_count( $post_id );
					$num_occasions  = incassoos_get_collection_occasion_count( $post_id );
					$num_orders     = incassoos_get_collection_order_count( $post_id );

					// Display activities
					if ( $num_activities ) {
						printf( 
							'<a href="%s" aria-label="%s">%s</a>',
							esc_url( add_query_arg( array( 'post_type' => incassoos_get_activity_post_type(), 'collection' => $post_id ), admin_url( 'edit.php' ) ) ),
							/* translators: %s: post title */
							esc_attr( sprintf( __( 'View activities of &#8220;%s&#8221;', 'incassoos' ), _draft_or_post_title() ) ),
							sprintf( _n( '%d Activity', '%d Activities', $num_activities, 'incassoos' ), $num_activities )
						);
					}

					// Display occasions
					if ( $num_occasions ) {
						printf( 
							'<a href="%s" aria-label="%s">%s</a>',
							esc_url( add_query_arg( array( 'post_type' => incassoos_get_occasion_post_type(), 'collection' => $post_id ), admin_url( 'edit.php' ) ) ),
							/* translators: %s: post title */
							esc_attr( sprintf( __( 'View occasions of &#8220;%s&#8221;', 'incassoos' ), _draft_or_post_title() ) ),
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
							incassoos_is_collection_collected( $collection )
								? sprintf( esc_attr__( 'Collected on %s', 'incassoos' ), incassoos_get_collection_date( $collection ) )
								: esc_attr__( 'Not yet collected', 'incassoos' ),
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
						/* translators: %s: post title */
						esc_attr( sprintf( __( 'View orders of &#8220;%s&#8221;', 'incassoos' ), _draft_or_post_title() ) ),
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
							incassoos_is_collection_collected( $collection )
								? sprintf( esc_attr__( 'Collected on %s', 'incassoos'), incassoos_get_collection_date( $collection ) )
								: esc_attr__( 'Not yet collected', 'incassoos'),
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
							'<span class="title"><a href="%s">%s</a></span>',
							esc_url( add_query_arg( $query_arg, $consumer, $posts_url ) ),
							incassoos_get_order_consumer_title( $post_id )
						);

					// Default to dash
					} else {
						echo '<span class="title">&mdash;</span>';
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
							'<a href="%s">%s</a>',
							esc_url( add_query_arg( 'occasion', $occasion->ID, $posts_url ) ),
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
				/* translators: %s: post title */
				esc_attr( sprintf( __( 'View &#8220;%s&#8221;' ), _draft_or_post_title() ) ),
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
				/* translators: %s: post title */
				esc_attr( sprintf( __( 'View &#8220;%s&#8221;' ), _draft_or_post_title() ) ),
				__( 'View' )
			);
		}

		// Disable inline editing
		unset( $actions['inline hide-if-no-js'] );

		// Duplicate
		if ( current_user_can( $post_type_object->cap->edit_posts ) ) {
			$actions['inc_duplicate'] = sprintf(
				'<a href="%s" aria-label="%s">%s</a>',
				esc_url( wp_nonce_url( add_query_arg( array( 'post' => $post->ID, 'action' => 'inc_duplicate' ), admin_url( 'post.php' ) ), 'duplicate-activity_' . $post->ID ) ),
				/* translators: %s: post title */
				esc_attr( sprintf( __( 'Duplicate &#8220;%s&#8221;', 'incassoos' ), _draft_or_post_title() ) ),
				__( 'Duplicate', 'incassoos' )
			);
		}
	}

	// Occasion
	if ( incassoos_get_occasion( $post ) ) {

		// Provide view link for collected
		if ( incassoos_is_occasion_collected( $post ) || ! current_user_can( $post_type_object->cap->edit_posts ) ) {
			$actions['view'] = sprintf(
				'<a href="%s" aria-label="%s">%s</a>',
				esc_url( add_query_arg( array( 'post' => $post->ID, 'action' => 'view' ), admin_url( 'post.php' ) ) ),
				/* translators: %s: post title */
				esc_attr( sprintf( __( 'View &#8220;%s&#8221;' ), _draft_or_post_title() ) ),
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
				/* translators: %s: post title */
				esc_attr( sprintf( __( 'Close &#8220;%s&#8221;', 'incassoos' ), _draft_or_post_title() ) ),
				__( 'Close', 'incassoos' )
			);
		}

		// Reopen
		if ( current_user_can( 'reopen_incassoos_occasion', $post->ID ) ) {
			$actions['inc_reopen'] = sprintf(
				'<a href="%s" aria-label="%s">%s</a>',
				esc_url( wp_nonce_url( add_query_arg( array( 'post' => $post->ID, 'action' => 'inc_reopen' ), admin_url( 'post.php' ) ), 'reopen-occasion_' . $post->ID ) ),
				/* translators: %s: post title */
				esc_attr( sprintf( __( 'Reopen &#8220;%s&#8221;', 'incassoos' ), _draft_or_post_title() ) ),
				__( 'Reopen', 'incassoos' )
			);
		}
	}

	// Order
	if ( incassoos_get_order( $post ) ) {

		$view_action = sprintf(
			'<a href="%s" aria-label="%s">%s</a>',
			esc_url( add_query_arg( array( 'post' => $post->ID, 'action' => 'view' ), admin_url( 'post.php' ) ) ),
			/* translators: %s: post title */
			esc_attr( sprintf( __( 'View &#8220;%s&#8221;' ), _draft_or_post_title() ) ),
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

	return $actions;
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
	       $post_type_object, $title, $is_IE, $post_ID, $user_ID, $action;

	// Bail when the post is not collected or not an Order
	if ( ! incassoos_admin_is_post_view( $post_id ) )
		return;

	$post = get_post( $post_id );
	$post_type = $post->post_type;
	$post_type_object = get_post_type_object( $post_type );

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
		11 => __( 'Collection staged.',    'incassoos' ),
		12 => __( 'Collection unstaged.',  'incassoos' ),
		13 => __( 'Collection collected.', 'incassoos' ),
	);

	// Activity
	$messages[ incassoos_get_activity_post_type() ] = array(
		 1 => __( 'Activity updated.',   'incassoos' ),
		 4 => __( 'Activity updated.',   'incassoos' ),
		 6 => __( 'Activity created.',   'incassoos' ),
		 7 => __( 'Activity saved.',     'incassoos' ),
		 8 => __( 'Activity submitted.', 'incassoos' ),
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
		 'incassoos_order_time_locked'      => $time_lock
			? sprintf( __( 'Sorry, the order cannot be edited beyond %d minutes after initial creation.', 'incassoos' ), $time_lock )
			: __( 'Sorry, the order cannot be edited after initial creation.', 'incassoos' ),
		 'incassoos_order_invalid_parent'   => __( 'Invalid occasion.', 'incassoos' ),
		 'incassoos_order_locked_occasion'  => __( 'The occasion is closed for new orders.', 'incassoos' ),
		 'incassoos_user_invalid_id'        => __( 'Invalid consumer ID.', 'incassoos' ),
		 'incassoos_consumer_invalid_type'  => __( 'Invalid consumer type.', 'incassoos' ),
		 'incassoos_order_invalid_products' => __( 'Invalid order products.', 'incassoos' ),
	);

	// Product
	$messages[ incassoos_get_product_post_type() ] = array(
		 1 => __( 'Product updated.',   'incassoos' ),
		 4 => __( 'Product updated.',   'incassoos' ),
		 6 => __( 'Product created.',   'incassoos' ),
		 7 => __( 'Product saved.',     'incassoos' ),
		 8 => __( 'Product submitted.', 'incassoos' ),
	);

	return $messages;
}

/**
 * Modify the redirect post location
 *
 * @param  string $location The destination url
 * @param  int    $post_id  Post ID
 * @return string The destination url
 */
function incassoos_admin_redirect_post_location( $location, $post_id ) {

	// Save post in admin
	if ( isset( $_POST['save'] ) || isset( $_POST['publish'] ) ) {

		switch ( get_post_type( $post_id ) ) {

			// Order
			case incassoos_get_order_post_type() :

				// Post was not saved. 
				if ( 'auto-draft' === get_post_status( $post_id ) ) {

					// Get the reported validation error. Return to sender.
					$validated = incassoos_validate_order( $_POST );
					if ( is_wp_error( $validated ) ) {
						$location = add_query_arg( 'error', $validated->get_error_code(), wp_get_referer() );
					}
				}

				break;
		}
	}

	return $location;
}

/**
 * Display admin notices on a post's page
 *
 * @since 1.0.0
 */
function incassoos_admin_post_notices() {

	// Get the screen context
	$scrn = get_current_screen();

	// Bail when this is not a plugin's post page
	if ( 'post' !== $scrn->base || empty( $scrn->post_type ) || ! incassoos_is_plugin_post_type( $scrn->post_type ) )
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

	switch ( $scrn->post_type ) {

		// Collection
		case incassoos_get_collection_post_type() :
			$prefix = esc_html__( 'Collection could not be saved: %s', 'incassoos' );
			break;

		// Activity
		case incassoos_get_activity_post_type() :
			$prefix = esc_html__( 'Activity could not be saved: %s', 'incassoos' );
			break;

		// Occasion
		case incassoos_get_occasion_post_type() :
			$prefix = esc_html__( 'Occasion could not be saved: %s', 'incassoos' );
			break;

		// Order
		case incassoos_get_order_post_type() :
			$prefix = esc_html__( 'Order could not be saved: %s', 'incassoos' );
			break;

		default :
			$prefix = esc_html__( 'Post could not be saved: %s', 'incassoos' );
			break;
	}

	// Get error for display
	if ( isset( $messages[ $scrn->post_type ][ $_GET['error'] ] ) ) {
		$error = $messages[ $scrn->post_type ][ $_GET['error'] ];
	} else {
		$error = esc_html__( 'Something went wrong.', 'incassoos' );
	}

	echo '<div class="notice error is-dismissible"><p>' . sprintf( $prefix, $error ) . '</p></div>';
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

	// Occasion Type
	if ( is_a( $term, 'WP_Term' ) && incassoos_get_occasion_type_tax_id() === $term->taxonomy ) {

		// Default term
		if ( incassoos_is_default_term( $term ) ) {
			/* translators: %s: term name */
			$name = sprintf( __( '%s <span class="status">&mdash; Default</span>', 'incassoos' ), $name );
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

	// Occasion Type
	if ( incassoos_get_occasion_type_tax_id() === $taxonomy ) : ?>

		<div class="form-field term-default-wrap">
			<label for="term-default"><?php esc_html_e( 'Default', 'incassoos' ); ?></label>
			<input type="checkbox" id="term-default" name="term-default" value="1" />

			<p class="description"><?php esc_html_e( 'Mark whether this should be the default term.', 'incassoos' ); ?></p>
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

	// Occasion Type
	if ( incassoos_get_occasion_type_tax_id() === $taxonomy ) : ?>

		<tr class="form-field term-default-wrap">
			<th scope="row" valign="top">
				<label for="term-default"><?php esc_html_e( 'Default', 'incassoos' ); ?></label>
			</th>
			<td>
				<input type="checkbox" id="term-default" name="term-default" value="1" <?php checked( get_term_meta( $term->term_id, '_default', true ) ); ?>/>

				<p class="description"><?php esc_html_e( 'Mark whether this should be the default term.', 'incassoos' ); ?></p>
			</td>
		</tr>

	<?php endif;
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
