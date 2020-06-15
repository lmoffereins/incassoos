<?php

/**
 * Incassoos Admin Functions
 *
 * @package Incassoos
 * @subpackage Administration
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Incassoos_Admin' ) ) :
/**
 * The Incassoos Admin class
 *
 * @since 1.0.0
 */
class Incassoos_Admin {

	/**
	 * Setup this class
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->setup_globals();
		$this->includes();
		$this->setup_actions();
	}

	/**
	 * Define default class globals
	 *
	 * @since 1.0.0
	 */
	private function setup_globals() {

		/** Paths *************************************************************/

		$this->admin_dir = trailingslashit( incassoos()->includes_dir . 'admin' );
		$this->admin_url = trailingslashit( incassoos()->includes_url . 'admin' );
	}

	/**
	 * Include the required files
	 *
	 * @since 1.0.0
	 */
	private function includes() {
		require( $this->admin_dir . 'consumers.php' );
		require( $this->admin_dir . 'dashboard.php' );
		require( $this->admin_dir . 'exporting.php' );
		require( $this->admin_dir . 'functions.php' );
		require( $this->admin_dir . 'metaboxes.php' );
		require( $this->admin_dir . 'settings.php'  );
	}

	/**
	 * Define default actions and filters
	 *
	 * @since 1.0.0
	 */
	private function setup_actions() {

		/** Core **************************************************************/

		add_filter( 'incassoos_map_meta_caps', array( $this, 'map_meta_caps'       ), 10, 4 );
		add_action( 'admin_enqueue_scripts',   array( $this, 'enqueue_scripts'     ), 10    );
		add_filter( 'admin_body_class',        array( $this, 'admin_body_class'    ), 10    );
		add_filter( 'display_post_states',     array( $this, 'display_post_states' ), 10, 2 );

		/** Posts *************************************************************/

		$collection = incassoos_get_collection_post_type();
		$coll_scrn  = "edit-{$collection}";
		$activity   = incassoos_get_activity_post_type();
		$actv_scrn  = "edit-{$activity}";
		$occasion   = incassoos_get_occasion_post_type();
		$occn_scrn  = "edit-{$occasion}";
		$order      = incassoos_get_order_post_type();
		$cons_scrn  = "edit-{$order}";
		$product    = incassoos_get_product_post_type();

		add_filter( "views_edit-{$activity}",    array( $this, 'list_table_views'          ), 10    );
		add_filter( "views_edit-{$occasion}",    array( $this, 'list_table_views'          ), 10    );
		add_filter( "views_edit-{$order}",       array( $this, 'list_table_views'          ), 10    );
		add_filter( "bulk_actions-{$coll_scrn}", array( $this, 'list_table_bulk_actions'   ), 10    );
		add_filter( "bulk_actions-{$actv_scrn}", array( $this, 'list_table_bulk_actions'   ), 10    );
		add_filter( "bulk_actions-{$occn_scrn}", array( $this, 'list_table_bulk_actions'   ), 10    );
		add_filter( "bulk_actions-{$cons_scrn}", array( $this, 'list_table_bulk_actions'   ), 10    );
		add_filter( 'list_table_primary_column', array( $this, 'list_table_primary_column' ), 10, 2 );
		add_filter( 'parse_query',               array( $this, 'list_table_filter_posts'   ),  5    );

		/** Single Post *******************************************************/

		add_filter( 'wp_editor_settings',      array( $this, 'wp_editor_settings'      ), 10, 2 );
		add_action( "save_post_{$collection}", array( $this, 'collection_save_metabox' ), 10, 2 );
		add_action( "save_post_{$activity}",   array( $this, 'activity_save_metabox'   ), 10, 2 );
		add_action( "save_post_{$occasion}",   array( $this, 'occasion_save_metabox'   ), 10, 2 );
		add_action( "save_post_{$order}",      array( $this, 'order_save_metabox'      ), 10, 2 );
		add_action( "save_post_{$product}",    array( $this, 'product_save_metabox'    ), 10, 2 );
	}

	/** Public methods **************************************************/

	/**
	 * Return whether this is a plugin admin page
	 *
	 * @since 1.0.0
	 *
	 * @return bool Plugin admin page
	 */
	public function is_incassoos() {
		$scrn = get_current_screen();

		return is_admin() && (
			   incassoos_is_plugin_post_type( $scrn->post_type )
			|| incassoos_is_plugin_taxonomy( $scrn->taxonomy )
			|| 0 === strpos( $scrn->base, 'incassoos_page' )
			|| 'toplevel_page_incassoos' === $scrn->base
		);
	}

	/**
	 * Modify the mapped caps for the admin meta capability
	 *
	 * @since 1.0.0
	 *
	 * @uses apply_filters() Calls 'incassoos_admin_map_meta_caps'
	 *
	 * @param array $caps Mapped caps
	 * @param string $cap Required meta capability
	 * @param int $user_id User ID
	 * @param array $args Additional arguments
	 * @return array Mapped caps
	 */
	public function map_meta_caps( $caps, $cap, $user_id = 0, $args = array() ) {

		switch ( $cap ) {

			/** Admin Pages *************************************************/

			// Dashboard
			case 'incassoos_admin_page-incassoos' :

				// Defer to dashboard caps
				$caps = array( 'view_incassoos_dashboard' );

				break;

			// Users
			case 'incassoos_admin_page-incassoos-consumers' :

				// Defer to consumers caps
				$caps = array( 'edit_incassoos_consumers' );

				break;

			// Settings
			case 'incassoos_admin_page-incassoos-settings' :

				// Block when without settings
				if ( ! incassoos_admin_page_has_settings( 'incassoos' ) ) {
					$caps = array( 'do_not_allow' );

				// Defer to settings caps
				} else {
					$caps = array( 'edit_incassoos_settings' );
				}

				break;

			// Collection admin
			case 'incassoos_collection_admin' :

				// Defer to viewing caps
				$caps = array( 'view_incassoos_collections' );

				break;

			// Activity admin
			case 'incassoos_activity_admin' :

				// Defer to viewing caps
				$caps = array( 'view_incassoos_activities' );

				break;

			// Occasion admin
			case 'incassoos_occasion_admin' :

				// Defer to viewing caps
				$caps = array( 'view_incassoos_occasions' );

				break;

			// Order admin
			case 'incassoos_order_admin' :

				// Defer to viewing caps
				$caps = array( 'view_incassoos_orders' );

				break;

			// Product admin
			case 'incassoos_product_admin' :

				// Defer to viewing caps
				$caps = array( 'view_incassoos_products' );

				break;

			/** Settings Sections *******************************************/

			case 'incassoos_settings_main' :
			case 'incassoos_settings_collecting' :
			case 'incassoos_settings_emails' :
			case 'incassoos_settings_slugs' :

				// Defer to collecting caps
				$caps = array( 'collect_incassoos_collections' );

				break;
		}

		return (array) apply_filters( 'incassoos_admin_map_meta_caps', $caps, $cap, $user_id, $args );
	}

	/**
	 * Enqueue admin scripts and styles
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {

		// Bail when not on a plugin page
		if ( ! $this->is_incassoos() )
			return;

		/** Scripts *****************************************************/

		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'incassoos-admin', incassoos()->assets_url . 'js/admin.js', array( 'jquery', 'jquery-ui-datepicker' ) );
		wp_localize_script( 'incassoos-admin', 'incAdminL10n', array(
			'settings' => array(
				'formatCurrency'  => incassoos_get_currency_format_args(),
				'consumersFields' => incassoos_admin_get_consumers_fields(),
				'ids'             => array(
					'occasionType' => incassoos_get_occasion_type_tax_id()
				),
				'occasionTypeDefault' => incassoos_get_default_occasion_type()
			),
			'l10n' => array(
				'showSelectedAll'   => __( 'Showing selected',                   'incassoos' ),
				'showSelectedOnly'  => __( 'Show selected',                      'incassoos' ),
				'showVisibleAll'    => __( 'Showing visible',                    'incassoos' ),
				'showVisibleOnly'   => __( 'Show visible',                       'incassoos' ),
				'termMetaDefault'   => _x( 'Default', 'Term meta',               'incassoos' ),
				'toggleCloseErrors' => _x( 'Hide errors', 'Notice toggle label', 'incassoos' ),
				'toggleOpenErrors'  => _x( 'Show errors', 'Notice toggle label', 'incassoos' ),
			)
		) );

		/** Styles ******************************************************/

		wp_enqueue_style( 'incassoos-datepicker', incassoos()->assets_url . 'css/datepicker.css', array( 'common' ), '20150908' );
		wp_enqueue_style( 'incassoos-admin', incassoos()->assets_url . 'css/admin.css', array( 'common', 'incassoos-datepicker' ) );

		// Define additional custom styles
		$css = array();

		// List columns
		$css[] = ".fixed .column-taxonomy-" . incassoos_get_activity_cat_tax_id() .
		       ", .fixed .column-taxonomy-" . incassoos_get_occasion_type_tax_id() .
		       ", .fixed .column-taxonomy-" . incassoos_get_product_cat_tax_id() . " { width: 10%; }";

		// Post
		$css[] = ".post-type-" . incassoos_get_order_post_type() . ":not(.incassoos-post-view) #post-body-content { margin: 0px; }";
		$css[] = ".post-type-" . incassoos_get_order_post_type() . " #major-publishing-actions { border: none; }";

		if ( ! empty( $css ) ) {
			wp_add_inline_style( 'incassoos-admin', implode( "\n", $css ) );
		}
	}

	/**
	 * Modify the admin body class
	 *
	 * @since 1.0.0
	 *
	 * @param  string $class Admin body class
	 * @return string Admin body class
	 */
	public function admin_body_class( $class ) {
		$scrn = get_current_screen();

		// Add class for admin pages that are in post-view-only mode
		if ( incassoos_admin_is_post_view( $GLOBALS['post'] ) ) {
			$class .= ' incassoos-post-view';

		// Add class for admin pages that are in post-edit mode
		} elseif ( 'post' === $scrn->base && incassoos_is_plugin_post_type( $scrn->post_type ) ) {
			$class .= ' incassoos-post-edit';
		}

		return $class;
	}

	/**
	 * Modify the list of displayed post states
	 *
	 * @since 1.0.0
	 *
	 * @param array $states Post states
	 * @param WP_Post $post Post object
	 * @return array Post states
	 */
	public function display_post_states( $states, $post ) {

		// Post is collected
		if ( incassoos_is_post_collected( $post ) ) {
			$states['collected'] = _x( 'Collected', 'post status', 'incassoos' );
		}

		return $states;
	}

	/** Posts ***********************************************************/

	/**
	 * Modify the items in the list table views
	 *
	 * @since 1.0.0
	 *
	 * @global WPDB $wpdb
	 *
	 * @param  array $views List table views
	 * @return array List table views
	 */
	public function list_table_views( $views ) {
		global $wpdb;

		// Remove view Mine
		unset( $views['mine'] );

		$post_type = $GLOBALS['post_type'];
		$exclude_states = get_post_stati( array( 'show_in_admin_all_list' => false ) );
		$count_by_meta_sql = "
			SELECT COUNT( 1 )
			FROM $wpdb->posts p
			INNER JOIN $wpdb->postmeta pm ON p.ID = pm.post_id
			WHERE post_type = %s
			AND post_status NOT IN ( '" . implode( "','", $exclude_states ) . "' )
			AND pm.meta_key = %s
			AND pm.meta_value = %d
		";

		switch ( $post_type ) {

			// Activities
			case incassoos_get_activity_post_type() :

				// Filtering by Collection
				if ( ! empty( $_REQUEST['collection'] ) ) {
					if ( $collection = incassoos_get_collection( $_REQUEST['collection'] ) ) {
						$views['collection'] = sprintf(
							'<a href="%s" class="current">%s</a>',
							esc_url( add_query_arg( array( 'post' => $collection->ID, 'action' => 'edit' ), admin_url( 'post.php' ) ) ),
							sprintf(
								__( 'Collection: %s', 'incassoos' ),
								sprintf( '%s <span class="count">(%s)</span>', incassoos_get_collection_title( $collection ), number_format_i18n( incassoos_get_collection_activity_count( $collection ) ) )
							)
						);
					}
				}

				break;

			// Occasions
			case incassoos_get_occasion_post_type() :

				// Filtering by Collection
				if ( ! empty( $_REQUEST['collection'] ) ) {
					if ( $collection = incassoos_get_collection( $_REQUEST['collection'] ) ) {
						$views['collection'] = sprintf(
							'<a href="%s" class="current">%s</a>',
							esc_url( add_query_arg( array( 'post' => $collection->ID, 'action' => 'edit' ), admin_url( 'post.php' ) ) ),
							sprintf(
								__( 'Collection: %s', 'incassoos' ),
								sprintf( '%s <span class="count">(%s)</span>', incassoos_get_collection_title( $collection ), number_format_i18n( incassoos_get_collection_occasion_count( $collection ) ) )
							)
						);
					}
				}

				break;

			// Orders
			case incassoos_get_order_post_type() :

				// Filtering by consumer
				if ( ! empty( $_REQUEST['consumer'] ) ) {
					if ( $user = get_userdata( $_REQUEST['consumer'] ) ) {
						$views['consumer'] = sprintf(
							'<a href="%s" class="current">%s <span class="count">(%s)</span></a>',
							esc_url( add_query_arg( array( 'post_type' => $post_type, 'consumer' => $user->ID ), admin_url( 'edit.php' ) ) ),
							esc_html( $user->display_name ),
							number_format_i18n(
								/**
								 * @see WP_Posts_List_Table::__construct()
								 */
								intval( $wpdb->get_var( $wpdb->prepare( $count_by_meta_sql, $post_type, 'consumer', $user->ID ) ) )
							)
						);
					}
				}

				// Filtering by consumer type
				if ( ! empty( $_REQUEST['consumer_type'] ) ) {
					if ( $consumer_type = incassoos_get_consumer_type( $_REQUEST['consumer_type'] ) ) {
						/**
						 * @see WP_Posts_List_Table::__construct()
						 */
						$post_count = intval( $wpdb->get_var( $wpdb->prepare( $count_by_meta_sql, $post_type, 'consumer_type', $consumer_type->id ) ) );

						$views['consumer_type'] = sprintf(
							'<a href="%s" class="current">%s</a>',
							esc_url( add_query_arg( array( 'post_type' => $post_type, 'consumer_type' => $consumer_type->id ), admin_url( 'edit.php' ) ) ),
							sprintf(
								translate_nooped_plural( $consumer_type->label_count, $post_count ),
								number_format_i18n( $post_count )
							)
						);
					}
				}

				// Filtering by Occasion
				if ( ! empty( $_REQUEST['occasion'] ) ) {
					if ( $occasion = incassoos_get_occasion( $_REQUEST['occasion'] ) ) {
						$views['occasion'] = sprintf(
							'<a href="%s" class="current">%s</a>',
							esc_url( add_query_arg( array( 'post' => $occasion->ID, 'action' => 'edit' ), admin_url( 'post.php' ) ) ),
							sprintf(
								__( 'Occasion: %s', 'incassoos' ),
								sprintf( '%s <span class="count">(%s)</span>', incassoos_get_occasion_title( $occasion ), number_format_i18n( incassoos_get_occasion_order_count( $occasion ) ) )
							)
						);
					}
				}

				break;
		}

		return $views;
	}

	/**
	 * Filter the list table bulk actions
	 *
	 * @since 1.0.0
	 *
	 * @param  array $actions Bulk actions
	 * @return array Bulk actions
	 */
	public function list_table_bulk_actions( $actions ) {

		switch ( get_current_screen()->post_type ) {
			case incassoos_get_collection_post_type() :
			case incassoos_get_activity_post_type() :
			case incassoos_get_occasion_post_type() :
			case incassoos_get_order_post_type() :

				// Disable bulk editing
				unset( $actions['edit'] );
				break;
		}

		return $actions;
	}

	/**
	 * Modify the primary list table column
	 *
	 * @since 1.0.0
	 *
	 * @param  string $column Primary column
	 * @param  string $screen_id Sreen ID
	 * @return string Primary column
	 */
	public function list_table_primary_column( $column, $screen_id ) {

		// Editing Orders
		if ( 'edit-' . incassoos_get_order_post_type() ) {
			$column = 'consumer';
		}

		return $column;
	}

	/**
	 * Filter list table to include posts of the specified filters
	 *
	 * @since 1.0.0
	 *
	 * @param  WP_Query $posts_query
	 */
	public function list_table_filter_posts( $posts_query ) {

		// Bail when this is not the main query
		if ( ! $posts_query->is_main_query() )
			return;

		// Bail when filters are suppressed on this query
		if ( true === $posts_query->get( 'suppress_filters' ) )
			return;

		// Posts list table
		if ( ! is_admin() || 'edit.php' !== $GLOBALS['pagenow'] )
			return;

		// Activities
		if ( incassoos_get_activity_post_type() === $GLOBALS['post_type'] ) {

			// Filter by Collection
			if ( ! empty( $_REQUEST['collection'] ) ) {
				$posts_query->set( 'post_parent', (int) $_REQUEST['collection'] );
			}
		}

		// Occasion
		if ( incassoos_get_occasion_post_type() === $GLOBALS['post_type'] ) {

			// Filter by Collection
			if ( ! empty( $_REQUEST['collection'] ) ) {
				$posts_query->set( 'post_parent', (int) $_REQUEST['collection'] );
			}
		}

		// Orders
		if ( incassoos_get_order_post_type() === $GLOBALS['post_type'] ) {

			// Filter by consumer
			if ( ! empty( $_REQUEST['consumer'] ) ) {
				$posts_query->set( 'incassoos_consumer', $_REQUEST['consumer'] );
			}

			// Filter by consumer type
			if ( ! empty( $_REQUEST['consumer_type'] ) ) {
				$posts_query->set( 'incassoos_consumer_type', $_REQUEST['consumer_type'] );
			}

			// Filter by Occasion
			if ( ! empty( $_REQUEST['occasion'] ) ) {
				$posts_query->set( 'post_parent', (int) $_REQUEST['occasion'] );
			}
		}
	}

	/**
	 * Modify the settings for the post editor
	 *
	 * @since 1.0.0
	 *
	 * @param  array  $settings  Editor settings
	 * @param  string $editor_id Editor instance ID
	 * @return array Editor settings
	 */
	public function wp_editor_settings( $settings, $editor_id ) {
		$post_type  = get_current_screen()->post_type;
		$collection = incassoos_get_collection_post_type();
		$activity   = incassoos_get_activity_post_type();
		$occasion   = incassoos_get_occasion_post_type();

		// Main content editor
		if ( 'content' === $editor_id ) {

			// For Activities or Occasions
			if ( in_array( $post_type, array( $activity, $occasion ) ) ) {

				// Limit editor height
				$settings['textarea_rows'] = 5;
				$settings['editor_height'] = 150;
			}

			// For Collections, Activities or Occasions
			if ( in_array( $post_type, array( $collection, $activity, $occasion ) ) ) {

				// Limit editor options
				$settings['media_buttons'] = false;
				$settings['teeny'] = true;
			}
		}

		return $settings;
	}

	/**
	 * Save when the Collection's metabox is submitted
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id Post ID
	 * @param WP_Post $post Post object
	 */
	public function collection_save_metabox( $post_id, $post = 0 ) {

		// Bail when the metabox cannot be saved
		if ( ! incassoos_admin_save_metabox_check( $post, incassoos_get_collection_post_type() ) )
			return;

		// Bail when nonce does not verify
		if ( isset( $_POST['collection_activities_metabox_nonce'] ) && wp_verify_nonce( $_POST['collection_activities_metabox_nonce'], 'collection_activities_metabox' ) ) {

			// Activities
			$activities  = incassoos_get_collection_activities( $post );
			$cactivities = isset( $_POST['collection-activity'] ) ? (array) $_POST['collection-activity'] : array();

			// Remove previous matches
			foreach ( array_diff( $activities, $cactivities ) as $item_id ) {
				wp_update_post( array(
					'ID'          => (int) $item_id,
					'post_parent' => 0
				) );
			}

			// Update new matches
			foreach ( array_diff( $cactivities, $activities ) as $item_id ) {
				wp_update_post( array(
					'ID'          => (int) $item_id,
					'post_parent' => $post_id
				) );
			}
		}

		// Bail when nonce does not verify
		if ( isset( $_POST['collection_occasions_metabox_nonce'] ) && wp_verify_nonce( $_POST['collection_occasions_metabox_nonce'], 'collection_occasions_metabox' ) ) {

			// Occasions
			$occasions  = incassoos_get_collection_occasions( $post );
			$coccasions = isset( $_POST['collection-occasion'] ) ? (array) $_POST['collection-occasion'] : array();

			// Remove previous matches
			foreach ( array_diff( $occasions, $coccasions ) as $item_id ) {
				wp_update_post( array(
					'ID'          => (int) $item_id,
					'post_parent' => 0
				) );
			}

			// Update new matches
			foreach ( array_diff( $coccasions, $occasions ) as $item_id ) {
				wp_update_post( array(
					'ID'          => (int) $item_id,
					'post_parent' => $post_id
				) );
			}
		}

		// Update collection total
		update_post_meta( $post_id, 'total', incassoos_get_collection_total_raw( $post_id ) );
	}

	/**
	 * Save when the Activity's metabox is submitted
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id Post ID
	 * @param WP_Post $post Post object
	 */
	public function activity_save_metabox( $post_id, $post = 0 ) {

		// Bail when the metabox cannot be saved
		if ( ! incassoos_admin_save_metabox_check( $post, incassoos_get_activity_post_type() ) )
			return;

		// Bail when nonce does not verify
		if ( isset( $_POST['activity_details_metabox_nonce'] ) && wp_verify_nonce( $_POST['activity_details_metabox_nonce'], 'activity_details_metabox' ) ) {

			/**
			 * Save posted inputs:
			 * - Activity Category taxonomy
			 * - Price
			 */

			foreach ( array(
				incassoos_get_activity_cat_tax_id(),
			) as $taxonomy ) {
				$_taxonomy = get_taxonomy( $taxonomy );

				if ( ! $_taxonomy || ! current_user_can( $_taxonomy->cap->assign_terms ) )
					continue;

				// Set taxonomy term
				if ( isset( $_POST["taxonomy-{$taxonomy}"] ) ) {
					wp_set_object_terms( $post_id, (int) $_POST["taxonomy-{$taxonomy}"], $taxonomy, false );

				// Remove taxonomy term
				} elseif ( $terms = wp_get_object_terms( $post_id ) ) {
					wp_remove_object_terms( $post_id, $terms, $taxonomy );
				}
			}

			if ( isset( $_POST['activity-date'] ) ) {
				incassoos_update_activity_date( $_POST['activity-date'], $post_id );
			}

			if ( isset( $_POST['price'] ) ) {
				update_post_meta( $post_id, 'price', $_POST['price'] );
			}
		}

		// Bail when nonce does not verify
		if ( isset( $_POST['activity_participants_metabox_nonce'] ) && wp_verify_nonce( $_POST['activity_participants_metabox_nonce'], 'activity_participants_metabox' ) ) {

			/**
			 * Save posted inputs:
			 * - Participants
			 * - Participant prices
			 */

			// Remove all previous matches
			delete_post_meta( $post_id, 'participant' );

			if ( isset( $_POST['activity-participant'] ) ) {
				foreach ( (array) $_POST['activity-participant'] as $item_id ) {
					add_post_meta( $post_id, 'participant', $item_id );
				}

				// Update prices
				if ( isset( $_POST['participant-price'] ) ) {
					$price = get_post_meta( $post->ID, 'price', true );

					// Get valid custom prices for registered participants only
					$prices = array_filter( $_POST['participant-price'], function( $p ) { return $p !== $price && ! empty( $p ); });
					$prices = array_intersect_key( $prices, array_flip( $_POST['activity-participant'] ) );
					$prices = array_map( 'incassoos_parse_currency', $prices );

					update_post_meta( $post_id, 'prices', $prices );
				}
			}
		}

		// Update activity total
		update_post_meta( $post_id, 'total', incassoos_get_activity_total_raw( $post_id ) );
	}

	/**
	 * Save when the Occasion's metabox is submitted
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id Post ID
	 * @param WP_Post $post Post object
	 */
	public function occasion_save_metabox( $post_id, $post = 0 ) {

		// Bail when the metabox cannot be saved
		if ( ! incassoos_admin_save_metabox_check( $post, incassoos_get_occasion_post_type() ) )
			return;

		// Bail when nonce does not verify
		if ( isset( $_POST['occasion_details_metabox_nonce'] ) && wp_verify_nonce( $_POST['occasion_details_metabox_nonce'], 'occasion_details_metabox' ) ) {

			/**
			 * Save posted inputs:
			 * - Date
			 * - Occasion Type taxonomy
			 */

			if ( isset( $_POST['occasion-date'] ) ) {
				incassoos_update_occasion_date( $_POST['occasion-date'], $post_id );
			}

			foreach ( array(
				incassoos_get_occasion_type_tax_id(),
			) as $taxonomy ) {
				$_taxonomy = get_taxonomy( $taxonomy );

				if ( ! $_taxonomy || ! current_user_can( $_taxonomy->cap->assign_terms ) )
					continue;

				// Set taxonomy term
				if ( isset( $_POST["taxonomy-{$taxonomy}"] ) ) {
					wp_set_object_terms( $post_id, (int) $_POST["taxonomy-{$taxonomy}"], $taxonomy, false );

				// Remove taxonomy term
				} elseif ( $terms = wp_get_object_terms( $post_id ) ) {
					wp_remove_object_terms( $post_id, $terms, $taxonomy );
				}
			}
		}

		// Update occasion total
		update_post_meta( $post_id, 'total', incassoos_get_occasion_total_raw( $post_id ) );
	}

	/**
	 * Save when the Order's metabox is submitted
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id Post ID
	 * @param WP_Post $post Post object
	 */
	public function order_save_metabox( $post_id, $post = 0 ) {

		// Bail when the metabox cannot be saved
		if ( ! incassoos_admin_save_metabox_check( $post, incassoos_get_order_post_type() ) )
			return;

		// Bail when nonce does not verify
		if ( isset( $_POST['order_details_metabox_nonce'] ) && wp_verify_nonce( $_POST['order_details_metabox_nonce'], 'order_details_metabox' ) ) {

			/**
			 * Save posted inputs:
			 * - Consumer
			 *
			 * Occasion is already saved in core through the `post_parent` name key.
			 */

			// Consumer
			if ( isset( $_POST['consumer'] ) ) {
				incassoos_update_order_consumer( $_POST['consumer'], $post_id );
			}
		}

		// Bail when nonce does not verify
		if ( isset( $_POST['order_products_metabox_nonce'] ) && wp_verify_nonce( $_POST['order_products_metabox_nonce'], 'order_products_metabox' ) ) {

			/**
			 * Save posted inputs:
			 * - Consumer
			 */

			// Products
			if ( isset( $_POST['products'] ) ) {
				$products = wp_list_filter( $_POST['products'], array( 'amount' => 0 ), 'NOT' );
				incassoos_update_order_products( $products, $post_id );
			}
		}
	}

	/**
	 * Save when the Product's metabox is submitted
	 *
	 * @since 1.0.0
	 *
	 * @param int $post_id Post ID
	 * @param WP_Post $post Post object
	 */
	public function product_save_metabox( $post_id, $post = 0 ) {

		// Bail when the metabox cannot be saved
		if ( ! incassoos_admin_save_metabox_check( $post, incassoos_get_product_post_type() ) )
			return;

		// Bail when nonce does not verify
		if ( empty( $_POST['product_details_metabox_nonce'] ) || ! wp_verify_nonce( $_POST['product_details_metabox_nonce'], 'product_details_metabox' ) )
			return;

		/**
		 * Save posted inputs:
		 * - Product Category taxonomy
		 * - Price
		 */

		foreach ( array(
			incassoos_get_product_cat_tax_id(),
		) as $taxonomy ) {
			$_taxonomy = get_taxonomy( $taxonomy );

			if ( ! $_taxonomy || ! current_user_can( $_taxonomy->cap->assign_terms ) )
				continue;

			// Set taxonomy term
			if ( isset( $_POST["taxonomy-{$taxonomy}"] ) ) {
				wp_set_object_terms( $post_id, (int) $_POST["taxonomy-{$taxonomy}"], $taxonomy, false );

			// Remove taxonomy term
			} elseif ( $terms = wp_get_object_terms( $post_id ) ) {
				wp_remove_object_terms( $post_id, $terms, $taxonomy );
			}
		}

		// Meta
		foreach ( array(
			'price' => 'price',
		) as $posted_key => $meta ) {
			if ( isset( $_POST[ $posted_key ] ) ) {
				update_post_meta( $post_id, $meta, $_POST[ $posted_key ] );
			}
		}
	}
}

/**
 * Setup the extension logic for BuddyPress
 *
 * @since 1.0.0
 *
 * @uses Incassoos_Admin
 */
function incassoos_admin() {
	incassoos()->admin = new Incassoos_Admin;
}

endif; // class_exists
