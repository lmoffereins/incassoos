<?php

/**
 * Incassoos VGSR Extension
 *
 * @package Incassoos
 * @subpackage VGSR
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Incassoos_VGSR' ) ) :
/**
 * The Incassoos VGSR class
 *
 * @since 1.0.0
 */
class Incassoos_VGSR {

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

		$this->plugin_dir = trailingslashit( incassoos()->includes_dir . 'extend/vgsr' );
		$this->plugin_url = trailingslashit( incassoos()->includes_url . 'extend/vgsr' );

		/** Identifiers *******************************************************/

		// Consumer type
		$this->cash_consumer_type         = apply_filters( 'inc_cash_consumer_type',         'cash'         );
		$this->on_the_house_consumer_type = apply_filters( 'inc_on_the_house_consumer_type', 'on_the_house' );
	}

	/**
	 * Include the required files
	 *
	 * @since 1.0.0
	 */
	private function includes() {
		require( $this->plugin_dir . 'functions.php' );
	}

	/**
	 * Define default actions and filters
	 *
	 * @since 1.0.0
	 */
	private function setup_actions() {

		add_action( 'incassoos_init', array( $this, 'register_consumer_types' ), 10 );

		// Users
		add_filter( 'incassoos_get_user_query_args',              array( $this, 'get_users_args'                   ), 10    );
		add_filter( 'incassoos_get_user_list_group',              array( $this, 'get_user_list_group'              ), 10, 2 );
		add_filter( 'incassoos_rest_consumers_collection_params', array( $this, 'rest_consumers_collection_params' ), 10    );
		add_filter( 'incassoos_rest_prepare_consumer',            array( $this, 'rest_prepare_consumer'            ), 10, 3 );

		// Collection
		add_filter( 'default_title',                         array( $this, 'default_title'           ), 10, 2 );
		add_action( 'incassoos_collection_details_metabox',  array( $this, 'collection_details'      ), 10    );
		add_action( 'post_action_inc_export_file_sfc',       array( $this, 'post_action_sfc'         ), 10    );

		// Activity
		add_filter( 'incassoos_get_user_match_options',      array( $this, 'user_match_options'      ), 10, 2 );
		add_filter( 'incassoos_get_user_match_ids',          array( $this, 'user_match_ids'          ), 10, 2 );

		// Email
		add_filter( 'incassoos_get_custom_email_salutation', array( $this, 'custom_email_salutation' ), 10, 2 );

		// Settings
		add_filter( 'incassoos_admin_get_users_fields',      array( $this, 'users_fields'            ), 10    );
		add_filter( 'incassoos_admin_get_settings_fields',   array( $this, 'settings_fields'         ), 10    );
		add_filter( 'incassoos_rest_prepare_settings',       array( $this, 'rest_prepare_settings'   ), 10, 2 );
	}

	/** Public methods **************************************************/

	/**
	 * Register consumer types
	 *
	 * @since 1.0.0
	 */
	public function register_consumer_types() {

		// Remove Guest type
		incassoos_unregister_consumer_type( incassoos_get_guest_consumer_type_id() );

		// Cash
		incassoos_register_consumer_type(
			incassoos_vgsr_get_cash_consumer_type_id(),
			array(
				'label'       => _x( 'Cash', 'Consumer type', 'incassoos' ),
				'label_count' => _nx_noop( 'Cash <span class="count">(%s)</span>', 'Cash <span class="count">(%s)</span>', 'Consumer type', 'incassoos' ),
			)
		);

		// On the House
		incassoos_register_consumer_type(
			incassoos_vgsr_get_on_the_house_consumer_type_id(),
			array(
				'label'       => _x( 'On the House', 'Consumer type', 'incassoos' ),
				'label_count' => _nx_noop( 'On the House <span class="count">(%s)</span>', 'On the House <span class="count">(%s)</span>', 'Consumer type', 'incassoos' ),
			)
		);
	}

	/**
	 * Modify the user query arguments for the plugin's users
	 *
	 * @since 1.0.0
	 *
	 * @param  array $args User query arguments
	 * @return array       User query arguments
	 */
	public function get_users_args( $args ) {

		// Default to only apply to VGSR members when not specified otherwise
		if ( ! isset( $args['include'] ) ) {
			$args['vgsr'] = 'all';
		}

		// Default to ordering by anciënniteit
		if ( ! isset( $args['orderby'] ) ) {
			$args['orderby'] = 'ancienniteit';
		}

		// Default to network users
		if ( ! isset( $args['blog_id'] ) ) {
			$args['blog_id'] = 0;
		}

		return $args;
	}

	/**
	 * Modify the user's list group
	 *
	 * @since 1.0.0
	 *
	 * @param  mixed        $group User's list group
	 * @param  WP_User|bool $user  User object or False when not found.
	 * @return mixed User's list group
	 */
	public function get_user_list_group( $group, $user ) {

		// Apply jaargroepen for listing users
		if ( $user ) {
			$jaargroep = vgsr_get_jaargroep( $user );

			if ( $jaargroep ) {
				$group = array(
					'id'    => $jaargroep,
					'name'  => sprintf( __( 'Jaargroep %s', 'incassoos' ), $jaargroep ),
					'order' => $jaargroep
				);
			} else {
				$group = false;
			}
		}

		return $group;
	}

	/**
	 * Filters the collection parameters for a users request.
	 *
	 * @since 1.0.0
	 *
	 * @param array $params Collection parameters
	 * @return array Collection parameters
	 */
	public function rest_consumers_collection_params( $params ) {

		// Overwrite the default to order by relevance, then ancienniteit
		$params['orderby']['enum'][]  = 'ancienniteit-relevance';
		$params['orderby']['default'] = 'ancienniteit-relevance';

		return $params;
	}

	/**
	 * Filters the item data for a consumer response.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Response $response The response object.
	 * @param WP_User          $item     User object.
	 * @param WP_REST_Request  $request  Request object.
	 * @return WP_REST_Response The response object
	 */
	public function rest_prepare_consumer( $response, $item, $request ) {

		// Set ancienniteit as custom sort value
		if ( isset( $response->data['customSort'] ) ) {
			$response->data['customSort'] = vgsr_get_ancienniteit( $item->ID );
		}

		// Reverse jaargroep order for App
		if ( $response->data['group']['order'] >= 1950 ) {
			$response->data['group']['order'] = 9999 - $response->data['group']['order'];
		}

		return $response;
	}

	/**
	 * Define the default post title for a post type
	 *
	 * @see get_default_post_to_edit()
	 *
	 * @since 1.0.0
	 *
	 * @param  string $default_title Default post title
	 * @param  object $post          Default post object
	 * @return string                Default post title
	 */
	public function default_title( $default_title, $post ) {

		// Collection
		if ( incassoos_get_collection_post_type() === $post->post_type ) {
			$default_title = ucfirst( date_i18n( 'F Y' ) );
		}

		return $default_title;
	}

	/**
	 * Add to the Collection details metabox
	 *
	 * @since 1.0.0
	 *
	 * @param  WP_Post $post Post object
	 */
	public function collection_details( $post ) {

		// Collection is collected
		if ( incassoos_is_collection_collected( $post ) ) { ?>

			<p>
				<label><?php esc_html_e( 'SFC:', 'incassoos' ); ?></label>
				<span class="value"><?php printf(
					'<a href="%s">%s</a>',
					esc_url( wp_nonce_url( add_query_arg( array( 'post' => $post->ID, 'action' => 'inc_export_file_sfc' ), admin_url( 'post.php' ) ), 'export-file-sfc_' . $post->ID ) ),
					esc_html__( 'Download file', 'incassoos' )
				); ?></span>
			</p>

			<?php
		}
	}

	/**
	 * Process the SFC file download action
	 *
	 * @since 1.0.0
	 *
	 * @param  mixed $post_id Post ID
	 */
	public function post_action_sfc( $post_id ) {
		$post = incassoos_get_collection( $post_id, true );

		// Bail when the post is not a collected Collection
		if ( ! $post )
			return;

		// Nonce check
		check_admin_referer( 'export-file-sfc_' . $post->ID );

		// Require class
		require_once( $this->plugin_dir . 'classes/class-incassoos-vgsr-sfc-file.php' );

		// Offer file download
		incassoos_download_text_file( new Incassoos_VGSR_SFC_File( $post ) );

		// Still here? Redirect to the Collection page
		wp_redirect( incassoos_get_collection_url( $post ) );
		exit();
	}

	/**
	 * Modify the collection of user match options
	 *
	 * Starting a match with a ...
	 *  _ means to deselect the matched items
	 *  ! means to select the items that do not match
	 *
	 * @since 1.0.0
	 *
	 * @param  array $matches User match options
	 * @return array User match options
	 */
	public function user_match_options( $matches ) {

		// Dummy --
		$matches = array_merge( $matches, array(

			// Lid type match
			'lid'             => __( 'Leden',                      'incassoos' ),
			'_lid'            => __( 'Leden',                      'incassoos' ),
			'_!lid'           => __( 'Niet-Leden',                 'incassoos' ),

			// Starts-with-M match
			'starts-with-m'   => __( 'Starts with M',              'incassoos' ),
			'_starts-with-m'  => __( 'Deselect Starts with M',     'incassoos' ),

			// Starts-not-with-M match
			'!starts-with-m'  => __( 'Starts not with M',          'incassoos' ),
			'_!starts-with-m' => __( 'Deselect Starts not with M', 'incassoos' ),
		) );

		return $matches;
	}

	/**
	 * Modify the user's matching matches
	 *
	 * @since 1.0.0
	 *
	 * @param  array $match_ids The user's match ids
	 * @param  WP_User|bool $user User ID or False when not found.
	 * @return array The user's match ids
	 */
	public function user_match_ids( $match_ids, $user ) {

		if ( $user ) {

			// VGSR lid type
			if ( $type = vgsr_get_lid_type( $user->ID ) ) {
				$match_ids[] = $type;
			}

			// Starts with M
			if ( 0 === strpos( strtolower( $user->display_name ), 'm' ) ) {
				$match_ids[] = 'starts-with-m';
			}
		}

		return $match_ids;
	}

	/**
	 * Modify the Collection's custom email salutation
	 *
	 * @since 1.0.0
	 *
	 * @param  string  $value Collection email salutation.
	 * @param  WP_User $user  User object or False when not found.
	 * @return string Collection email salutation
	 */
	public function custom_email_salutation( $value, $user ) {
		return vgsr_get_salutation( $user );
	}

	/**
	 * Modify the admin users fields
	 *
	 * @since 1.0.0
	 *
	 * @param  array $fields Users fields
	 * @return array          Users fields
	 */
	public function users_fields( $fields ) {

		// Consumption limit
		$fields['_incassoos_consumption_limit'] = array(
			'label'           => __( 'Consumption limit', 'incassoos' ),
			'get_callback'    => 'incassoos_admin_users_meta_get_callback',
			'update_callback' => 'incassoos_admin_users_meta_update_callback',
			'input_callback'  => 'incassoos_admin_users_input_callback',
			'type'            => 'number'
		);

		return $fields;
	}

	/**
	 * Modify the admin settings fields
	 *
	 * @since 1.0.0
	 *
	 * @param  array $fields Settings fields
	 * @return array Settings fields
	 */
	public function settings_fields( $fields ) {

		// We're providing the salutation ourselves
		unset( $fields['incassoos_settings_email']['_incassoos_custom_email_salutation'] );

		return $fields;
	}

	/**
	 * Filters the data for the settings response.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Response $response The response object.
	 * @param WP_REST_Request  $request  Request object.
	 * @return WP_REST_Response The response object
	 */
	public function rest_prepare_settings( $response, $request ) {

		// Consumer settings
		if ( isset( $response->data['consumers'] ) ) {

			// Custom value sorting
			$response->data['consumers']['customSortName'] = __( 'Anciënniteit', 'incassoos' );
		}

		return $response;
	}
}

/**
 * Setup the extension logic for VGSR
 *
 * @since 1.0.0
 *
 * @uses Incassoos_VGSR
 */
function incassoos_vgsr() {
	incassoos()->extend->vgsr = new Incassoos_VGSR;
}

endif; // class_exists
