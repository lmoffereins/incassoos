<?php

/**
 * The Incassoos Plugin
 *
 * @package Incassoos
 * @subpackage Main
 */

/**
 * Plugin Name:       Incassoos
 * Description:       Register, manage and collect consumptions
 * Plugin URI:        https://github.com/lmoffereins/incassoos/
 * Version:           1.0.0-beta.10
 * Author:            Laurens Offereins
 * Author URI:        https://github.com/lmoffereins/
 * Text Domain:       incassoos
 * Domain Path:       /languages/
 * GitHub Plugin URI: lmoffereins/incassoos
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Incassoos' ) ) :
/**
 * The main plugin class
 *
 * @since 1.0.0
 */
final class Incassoos {

	/**
	 * Setup and return the singleton pattern
	 *
	 * @since 1.0.0
	 *
	 * @uses Incassoos::setup_globals()
	 * @uses Incassoos::setup_actions()
	 * @return The single Incassoos
	 */
	public static function instance() {

		// Store instance locally
		static $instance = null;

		if ( null === $instance ) {
			$instance = new Incassoos;
			$instance->setup_globals();
			$instance->includes();
			$instance->setup_actions();
		}

		return $instance;
	}

	/**
	 * Prevent the plugin class from being loaded more than once
	 */
	private function __construct() { /* Nothing to do */ }

	/** Private methods *************************************************/

	/**
	 * Setup default class globals
	 *
	 * @since 1.0.0
	 */
	private function setup_globals() {

		/** Versions ****************************************************/

		$this->version              = '1.0.0-beta.10';
		$this->db_version           = 10000;

		/** Paths *******************************************************/

		// Setup some base path and URL information
		$this->file                 = __FILE__;
		$this->basename             = plugin_basename( $this->file );
		$this->plugin_dir           = plugin_dir_path( $this->file );
		$this->plugin_url           = plugin_dir_url ( $this->file );

		// Includes
		$this->includes_dir         = trailingslashit( $this->plugin_dir . 'includes' );
		$this->includes_url         = trailingslashit( $this->plugin_url . 'includes' );

		// Assets
		$this->assets_dir           = trailingslashit( $this->plugin_dir . 'assets' );
		$this->assets_url           = trailingslashit( $this->plugin_url . 'assets' );

		// Templates
		$this->themes_dir           = trailingslashit( $this->plugin_dir . 'templates' );
		$this->themes_url           = trailingslashit( $this->plugin_url . 'templates' );

		// Languages
		$this->lang_dir             = trailingslashit( $this->plugin_dir . 'languages' );

		/** Identifiers *************************************************/

		// Post type
		$this->collection_post_type = apply_filters( 'incassoos_collection_post_type', 'inc_collection' );
		$this->activity_post_type   = apply_filters( 'incassoos_activity_post_type',   'inc_activity'   );
		$this->occasion_post_type   = apply_filters( 'incassoos_occasion_post_type',   'inc_occasion'   );
		$this->order_post_type      = apply_filters( 'incassoos_order_post_type',      'inc_order'      );
		$this->product_post_type    = apply_filters( 'incassoos_product_post_type',    'inc_product'    );

		// Taxonomy
		$this->activity_cat_tax_id  = apply_filters( 'incassoos_activity_cat_tax',  'inc_activity_category' );
		$this->occasion_type_tax_id = apply_filters( 'incassoos_occasion_type_tax', 'inc_occasion_type'     );
		$this->product_cat_tax_id   = apply_filters( 'incassoos_product_cat_tax',   'inc_product_category'  );
		$this->consumer_type_tax_id = apply_filters( 'incassoos_consumer_type_tax', 'inc_consumer_type'     );

		// Status
		$this->staged_status_id     = apply_filters( 'incassoos_staged_post_status',    'staged'    );
		$this->collected_status_id  = apply_filters( 'incassoos_collected_post_status', 'collected' );

		// Consumer type
		$this->unknown_consumer_type = apply_filters( 'incassoos_unknown_consumer_type', 'unknown' );
		$this->guest_consumer_type   = apply_filters( 'incassoos_guest_consumer_type',   'guest'   );

		// Export type
		$this->sepa_export_type              = apply_filters( 'incassoos_sepa_export_type',              'inc_sepa'              );
		$this->post_consumers_export_type    = apply_filters( 'incassoos_post_consumers_export_type',    'inc_post_consumers'    );
		$this->post_consumptions_export_type = apply_filters( 'incassoos_post_consumptions_export_type', 'inc_post_consumptions' );
		$this->post_products_export_type     = apply_filters( 'incassoos_post_products_export_type',     'inc_post_products'     );
		$this->consumers_export_type         = apply_filters( 'incassoos_consumers_export_type',         'inc_consumers'         );

		/** Misc ********************************************************/

		$this->encryption           = new stdClass();
		$this->extend               = new stdClass();
		$this->domain               = 'incassoos';
	}

	/**
	 * Include the required files
	 *
	 * @since 1.0.0
	 */
	private function includes() {

		/** Core ********************************************************/

		require( $this->includes_dir . 'accounts.php'     );
		require( $this->includes_dir . 'actions.php'      );
		require( $this->includes_dir . 'activities.php'   );
		require( $this->includes_dir . 'capabilities.php' );
		require( $this->includes_dir . 'collections.php'  );
		require( $this->includes_dir . 'currency.php'     );
		require( $this->includes_dir . 'formatting.php'   );
		require( $this->includes_dir . 'functions.php'    );
		require( $this->includes_dir . 'locale.php'       );
		require( $this->includes_dir . 'occasions.php'    );
		require( $this->includes_dir . 'orders.php'       );
		require( $this->includes_dir . 'products.php'     );
		require( $this->includes_dir . 'sub-actions.php'  );
		require( $this->includes_dir . 'template.php'     );
		require( $this->includes_dir . 'theme-compat.php' );
		require( $this->includes_dir . 'users.php'        );
		require( $this->includes_dir . 'update.php'       );

		/** Classes *****************************************************/

		require( $this->includes_dir . 'classes/class-incassoos-consumer-type.php' );

		/** Admin *******************************************************/

		if ( is_admin() ) {
			require( $this->includes_dir . 'admin/admin.php' );
		}

		/** Extend ******************************************************/

		require( $this->includes_dir . 'extend/buddypress/buddypress.php' );
		require( $this->includes_dir . 'extend/members.php'               );
		require( $this->includes_dir . 'extend/vgsr/vgsr.php'             );
	}

	/**
	 * Setup default actions and filters
	 *
	 * @since 1.0.0
	 */
	private function setup_actions() {

		/** Activation **************************************************/

		add_action( 'activate_'   . $this->basename, 'incassoos_activation'   );
		add_action( 'deactivate_' . $this->basename, 'incassoos_deactivation' );

		/** Textdomain **************************************************/

		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ), 20 );

		/** Content *****************************************************/

		add_action( 'incassoos_register', array( $this, 'register_post_types'     ) );
		add_action( 'incassoos_register', array( $this, 'register_taxonomies'     ) );
		add_action( 'incassoos_register', array( $this, 'register_post_statuses'  ) );
		add_action( 'incassoos_register', array( $this, 'register_consumer_types' ) );
		add_action( 'incassoos_register', array( $this, 'register_export_types'   ) );

		/** Permalinks **************************************************/

		add_action( 'incassoos_init', array( $this, 'add_rewrite_tags'  ), 20 );
		add_action( 'incassoos_init', array( $this, 'add_rewrite_rules' ), 30 );
	}

	/** Plugin **********************************************************/

	/**
	 * Load the translation file for current language. Checks the languages
	 * folder inside the plugin first, and then the default WordPress
	 * languages folder.
	 *
	 * Note that custom translation files inside the plugin folder will be
	 * removed on plugin updates. If you're creating custom translation
	 * files, please use the global language folder.
	 *
	 * @since 1.0.0
	 *
	 * @uses apply_filters() Calls 'plugin_locale' with {@link get_locale()} value
	 * @uses load_textdomain() To load the textdomain
	 * @uses load_plugin_textdomain() To load the textdomain
	 */
	public function load_textdomain() {

		// Traditional WordPress plugin locale filter
		$locale        = apply_filters( 'plugin_locale', get_locale(), $this->domain );
		$mofile        = sprintf( '%1$s-%2$s.mo', $this->domain, $locale );

		// Setup paths to current locale file
		$mofile_local  = $this->lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/incassoos/' . $mofile;

		// Look in global /wp-content/languages/incassoos folder
		load_textdomain( $this->domain, $mofile_global );

		// Look in local /wp-content/plugins/incassoos/languages/ folder
		load_textdomain( $this->domain, $mofile_local );

		// Look in global /wp-content/languages/plugins/
		load_plugin_textdomain( $this->domain );
	}

	/** Public methods **************************************************/

	/**
	 * Register plugin post types
	 *
	 * @since 1.0.0
	 */
	public function register_post_types() {

		/** Collection **************************************************/

		register_post_type(
			incassoos_get_collection_post_type(),
			array(
				'labels'              => incassoos_get_collection_post_type_labels(),
				'supports'            => incassoos_get_collection_post_type_supports(),
				'description'         => __( 'Incassoos Collections', 'incassoos' ),
				'capabilities'        => incassoos_get_collection_post_type_caps(),
				'capability_type'     => array( 'incassoos_collection', 'incassoos_collections' ),
				'hierarchical'        => false,
				'public'              => false,
				'has_archive'         => false,
				'rewrite'             => false,
				'query_var'           => false,
				'exclude_from_search' => true,
				'show_ui'             => current_user_can( 'incassoos_collection_admin' ),
				'show_in_nav_menus'   => false,
				'can_export'          => true,
				'menu_icon'           => 'dashicons-forms',
			)
		);

		/** Activity ****************************************************/

		register_post_type(
			incassoos_get_activity_post_type(),
			array(
				'labels'              => incassoos_get_activity_post_type_labels(),
				'supports'            => incassoos_get_activity_post_type_supports(),
				'description'         => __( 'Incassoos Activities', 'incassoos' ),
				'capabilities'        => incassoos_get_activity_post_type_caps(),
				'capability_type'     => array( 'incassoos_activity', 'incassoos_activities' ),
				'hierarchical'        => false,
				'public'              => false,
				'has_archive'         => false,
				'rewrite'             => false,
				'query_var'           => false,
				'exclude_from_search' => true,
				'show_ui'             => current_user_can( 'incassoos_activity_admin' ),
				'show_in_nav_menus'   => false,
				'can_export'          => true,
				'menu_icon'           => 'dashicons-forms',
			)
		);

		/** Occasion ****************************************************/

		register_post_type(
			incassoos_get_occasion_post_type(),
			array(
				'labels'                => incassoos_get_occasion_post_type_labels(),
				'supports'              => incassoos_get_occasion_post_type_supports(),
				'description'           => __( 'Incassoos Occasions', 'incassoos' ),
				'capabilities'          => incassoos_get_occasion_post_type_caps(),
				'capability_type'       => array( 'incassoos_occasion', 'incassoos_occasions' ),
				'hierarchical'          => false,
				'public'                => false,
				'has_archive'           => false,
				'rewrite'               => false,
				'query_var'             => false,
				'exclude_from_search'   => true,
				'show_ui'               => current_user_can( 'incassoos_occasion_admin' ),
				'show_in_nav_menus'     => false,
				'can_export'            => true,
				'menu_icon'             => 'dashicons-screenoptions',
			)
		);

		/** Order *************************************************/

		register_post_type(
			incassoos_get_order_post_type(),
			array(
				'labels'                => incassoos_get_order_post_type_labels(),
				'supports'              => incassoos_get_order_post_type_supports(),
				'description'           => __( 'Incassoos Orders', 'incassoos' ),
				'capabilities'          => incassoos_get_order_post_type_caps(),
				'capability_type'       => array( 'incassoos_order', 'incassoos_orders' ),
				'hierarchical'          => false,
				'public'                => false,
				'has_archive'           => false,
				'rewrite'               => false,
				'query_var'             => false,
				'exclude_from_search'   => true,
				'show_ui'               => current_user_can( 'incassoos_order_admin' ),
				'show_in_nav_menus'     => false,
				'can_export'            => true,
				'menu_icon'             => 'dashicons-screenoptions',
			)
		);

		/** Product *****************************************************/

		register_post_type(
			incassoos_get_product_post_type(),
			array(
				'labels'                => incassoos_get_product_post_type_labels(),
				'supports'              => incassoos_get_product_post_type_supports(),
				'description'           => __( 'Incassoos Products', 'incassoos' ),
				'capabilities'          => incassoos_get_product_post_type_caps(),
				'capability_type'       => array( 'incassoos_product', 'incassoos_products' ),
				'hierarchical'          => false,
				'public'                => false,
				'has_archive'           => false,
				'rewrite'               => false,
				'query_var'             => false,
				'exclude_from_search'   => true,
				'show_ui'               => current_user_can( 'incassoos_product_admin' ),
				'show_in_nav_menus'     => false,
				'can_export'            => true,
				'menu_icon'             => 'dashicons-screenoptions',
			)
		);
	}

	/**
	 * Register plugin taxonomies
	 *
	 * @since 1.0.0
	 */
	public function register_taxonomies() {

		/** Activity Category *******************************************/

		register_taxonomy(
			incassoos_get_activity_cat_tax_id(),
			incassoos_get_activity_post_type(),
			array(
				'labels'                => incassoos_get_activity_cat_tax_labels(),
				'capabilities'          => incassoos_get_activity_cat_tax_caps(),
				'update_count_callback' => 'incassoos_update_activity_term_count',
				'hierarchical'          => false,
				'public'                => false,
				'rewrite'               => false,
				'query_var'             => false,
				'show_tagcloud'         => false,
				'show_in_quick_edit'    => true,
				'show_admin_column'     => true,
				'show_in_nav_menus'     => false,
				'show_ui'               => current_user_can( 'incassoos_activity_cat_admin' ),
				'meta_box_cb'           => false, // No metaboxing
				'show_in_rest'          => true   // Relevant for querying and updating
			)
		);

		/** Occasion Type ***********************************************/

		register_taxonomy(
			incassoos_get_occasion_type_tax_id(),
			incassoos_get_occasion_post_type(),
			array(
				'labels'                => incassoos_get_occasion_type_tax_labels(),
				'capabilities'          => incassoos_get_occasion_type_tax_caps(),
				'update_count_callback' => 'incassoos_update_occasion_term_count',
				'hierarchical'          => false,
				'public'                => false,
				'rewrite'               => false,
				'query_var'             => false,
				'show_tagcloud'         => false,
				'show_in_quick_edit'    => true,
				'show_admin_column'     => true,
				'show_in_nav_menus'     => false,
				'show_ui'               => current_user_can( 'incassoos_occasion_type_admin' ),
				'meta_box_cb'           => false, // No metaboxing
				'show_in_rest'          => true   // Relevant for querying and updating
			)
		);

		/** Product Category ********************************************/

		register_taxonomy(
			incassoos_get_product_cat_tax_id(),
			incassoos_get_product_post_type(),
			array(
				'labels'                => incassoos_get_product_cat_tax_labels(),
				'capabilities'          => incassoos_get_product_cat_tax_caps(),
				'update_count_callback' => '_update_post_term_count',
				'hierarchical'          => false,
				'public'                => false,
				'rewrite'               => false,
				'query_var'             => false,
				'show_tagcloud'         => false,
				'show_in_quick_edit'    => true,
				'show_admin_column'     => true,
				'show_in_nav_menus'     => false,
				'show_ui'               => current_user_can( 'incassoos_product_cat_admin' ),
				'meta_box_cb'           => false, // No metaboxing
				'show_in_rest'          => true   // Relevant for querying and updating
			)
		);

		/** Consumer Type ***********************************************/

		register_taxonomy(
			incassoos_get_consumer_type_tax_id(),
			array(), // No association with an object type
			array(
				'labels'                => incassoos_get_consumer_type_tax_labels(),
				'capabilities'          => incassoos_get_consumer_type_tax_caps(),
				'update_count_callback' => '',
				'hierarchical'          => false,
				'public'                => false,
				'rewrite'               => false,
				'query_var'             => false,
				'show_tagcloud'         => false,
				'show_in_quick_edit'    => false,
				'show_admin_column'     => false,
				'show_in_nav_menus'     => false,
				'show_ui'               => current_user_can( 'incassoos_consumer_type_admin' ),
				'meta_box_cb'           => false, // No metaboxing
				'show_in_rest'          => true   // Relevant for querying and updating
			)
		);
	}

	/**
	 * Register post statuses
	 *
	 * @since 1.0.0
	 */
	public function register_post_statuses() {

		// Staged
		register_post_status(
			incassoos_get_staged_status_id(),
			apply_filters( 'incassoos_register_staged_post_status', array(
				'label'                     => _x( 'Staged', 'Post status', 'incassoos' ),
				'label_count'               => _nx_noop( 'Staged <span class="count">(%s)</span>', 'Staged <span class="count">(%s)</span>', 'Post status', 'incassoos' ),
				'protected'                 => true,
				'public'                    => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true
			) )
		);

		// Collected
		register_post_status(
			incassoos_get_collected_status_id(),
			apply_filters( 'incassoos_register_collected_post_status', array(
				'label'                     => _x( 'Collected', 'Post status', 'incassoos' ),
				'label_count'               => _nx_noop( 'Collected <span class="count">(%s)</span>', 'Collected <span class="count">(%s)</span>', 'Post status', 'incassoos' ),
				'protected'                 => true,
				'public'                    => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true
			) )
		);
	}

	/**
	 * Register consumer types
	 *
	 * @since 1.0.0
	 */
	public function register_consumer_types() {

		// Unknown user
		incassoos_register_consumer_type(
			incassoos_get_unknown_consumer_type_id_base(),
			array(
				'label'       => _x( 'Unknown users', 'Consumer type', 'incassoos' ),
				'label_user'  => _x( 'Unknown user [%s]', 'Consumer type', 'incassoos' ),
				'description' => __( 'Built-in type for consumers that no longer exist in the system.', 'incassoos' ),
				'_hidden'     => true
			)
		);

		// Guest
		incassoos_register_consumer_type(
			incassoos_get_guest_consumer_type_id(),
			array(
				'label'       => _x( 'Guest', 'Consumer type', 'incassoos' ),
				'description' => __( 'Built-in type for unregistered consumers.', 'incassoos' )
			)
		);
	}

	/**
	 * Register export types
	 *
	 * @since 1.0.0
	 */
	public function register_export_types() {

		// SEPA
		incassoos_register_export_type(
			incassoos_get_sepa_export_type_id(),
			array(
				'labels'                 => array(
					'name'        => esc_html__( 'SEPA file',        'incassoos' ),
					'export_file' => esc_html__( 'Export SEPA file', 'incassoos' )
				),
				'class_name'             => 'Incassoos_Collection_SEPA_XML_Exporter',
				'class_file'             => $this->includes_dir . 'classes/class-incassoos-collection-sepa-xml-exporter.php',
				'show_in_list_callback'  => 'incassoos_is_collection_collected',
				'require_decryption_key' => true
			)
		);

		// Post consumers CSV
		incassoos_register_export_type(
			incassoos_get_post_consumers_export_type_id(),
			array(
				'labels_callback'       => 'incassoos_get_post_consumers_export_type_labels',
				'class_name'            => 'Incassoos_Post_Consumers_CSV_Exporter',
				'class_file'            => $this->includes_dir . 'classes/class-incassoos-post-consumers-csv-exporter.php',
				'show_in_list_callback' => 'incassoos_show_post_consumers_export_type'
			)
		);

		// Post consumptions CSV
		incassoos_register_export_type(
			incassoos_get_post_consumptions_export_type_id(),
			array(
				'labels'                => array(
					'name'        => esc_html__( 'Post consumptions (csv)',   'incassoos' ),
					'export_file' => esc_html__( 'Export consumptions (csv)', 'incassoos' )
				),
				'class_name'            => 'Incassoos_Post_Consumptions_CSV_Exporter',
				'class_file'            => $this->includes_dir . 'classes/class-incassoos-post-consumptions-csv-exporter.php',
				'show_in_list_callback' => 'incassoos_show_post_consumptions_export_type'
			)
		);

		// Post products CSV
		incassoos_register_export_type(
			incassoos_get_post_products_export_type_id(),
			array(
				'labels'                => array(
					'name'        => esc_html__( 'Post products (csv)',   'incassoos' ),
					'export_file' => esc_html__( 'Export products (csv)', 'incassoos' )
				),
				'class_name'            => 'Incassoos_Post_Products_CSV_Exporter',
				'class_file'            => $this->includes_dir . 'classes/class-incassoos-post-products-csv-exporter.php',
				'show_in_list_callback' => 'incassoos_show_post_products_export_type'
			)
		);

		// Consumers CSV
		incassoos_register_export_type(
			incassoos_get_consumers_export_type_id(),
			array(
				'labels'                 => array(
					'name'        => esc_html__( 'Consumers (csv)',        'incassoos' ),
					'export_file' => esc_html__( 'Export consumers (csv)', 'incassoos' )
				),
				'class_name'             => 'Incassoos_Consumers_CSV_Exporter',
				'class_file'             => $this->includes_dir . 'classes/class-incassoos-consumers-csv-exporter.php',
				'show_in_list_callback'  => '__return_false',
				'require_decryption_key' => 'optional'
			)
		);
	}

	/**
	 * Register plugin rewrite tags
	 *
	 * @since 1.0.0
	 */
	public function add_rewrite_tags() {
		add_rewrite_tag( '%' . incassoos_get_app_rewrite_id() . '%', '([1]{1,})' ); // Application page tag
	}

	/**
	 * Register plugin rewrite rules
	 *
	 * Setup rules to create the following structures:
	 * - /{app}/
	 *
	 * @since 1.0.0
	 */
	public function add_rewrite_rules() {

		// Priority
		$priority  = 'top';

		// Slugs
		$app_slug  = incassoos_get_app_slug();

		// Unique rewrite ID's
		$app_id    = incassoos_get_app_rewrite_id();

		// Generic rules
		$root_rule = '/?$';

		/** Add *********************************************************/

		// Page rules
		if ( ! incassoos_is_app_on_front() ) {
			add_rewrite_rule( $app_slug . $root_rule, 'index.php?' . $app_id . '=1', $priority );
		}
	}
}

/**
 * Return single instance of the plugin's main class
 *
 * @since 1.0.0
 * 
 * @return Incassoos
 */
function incassoos() {
	return Incassoos::instance();
}

// Initiate plugin on load
incassoos();

endif; // class_exists
