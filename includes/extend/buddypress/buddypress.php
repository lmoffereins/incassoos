<?php

/**
 * Incassoos BuddyPress Extension
 *
 * @package Incassoos
 * @subpackage BuddyPress
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Incassoos_BuddyPress' ) ) :
/**
 * The Incassoos BuddyPress class
 *
 * @since 1.0.0
 */
class Incassoos_BuddyPress {

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

		$this->plugin_dir = trailingslashit( incassoos()->includes_dir . 'extend/buddypress' );
		$this->plugin_url = trailingslashit( incassoos()->includes_url . 'extend/buddypress' );
	}

	/**
	 * Include the required files
	 *
	 * @since 1.0.0
	 */
	private function includes() {
		require( $this->plugin_dir . 'actions.php'   );
		require( $this->plugin_dir . 'functions.php' );
	}

	/**
	 * Define default actions and filters
	 *
	 * @since 1.0.0
	 */
	private function setup_actions() {

		// Init
		add_filter( 'incassoos_register', array( $this, 'register' ), 10 );

		// Settings
		add_filter( 'incassoos_admin_get_settings_fields', array( $this, 'settings_fields' ), 10 );

		// Email
		add_filter( 'bp_get_email_args',        array( $this, 'email_query_args'  ), 10, 2 );
		add_filter( 'posts_pre_query',          array( $this, 'posts_pre_query'   ), 10, 2 );
		add_filter( 'incassoos_pre_send_email', array( $this, 'send_email'        ), 10, 2 );
		add_filter( 'bp_get_email_post',        array( $this, 'filter_email_post' ), 10, 3 );
		add_filter( 'bp_email_get_headers',     array( $this, 'email_get_headers' ),  7, 4 ); // After BP's core filter for defaults
		add_filter( 'bp_email_get_tokens',      array( $this, 'email_get_tokens'  ),  7, 4 ); // After BP's core filter for defaults
		add_action( 'bp_send_email',            array( $this, 'send_email_setup'  ), 10, 4 );
	}

	/** Public methods **************************************************/

	/**
	 * Set hooks when the plugin is registered
	 *
	 * @since 1.0.0
	 */
	public function register() {

		// When using BP email templates
		if ( incassoos_bp_use_email_template() ) {

			// Collection collect email: remove salutation
			remove_action( 'incassoos_collection_collect_email_content', 'incassoos_collection_collect_email_salutation', 5, 2 );
		}
	}

	/** Settings ********************************************************/

	/**
	 * Modify the admin settings fields
	 *
	 * @since 1.0.0
	 *
	 * @param  array $fields Settings fields
	 * @return array Settings fields
	 */
	public function settings_fields( $fields ) {

		// Use BP's email template
		$fields['incassoos_settings_email']['_incassoos_bp_use_email_template'] = array(
			'title'             => esc_html__( 'BuddyPress email template', 'incassoos' ),
			'callback'          => 'incassoos_bp_admin_setting_callback_use_email_template',
			'sanitize_callback' => 'intval',
			'args'              => array()
		);

		return $fields;
	}

	/** Email ***********************************************************/

	/**
	 * Modify the email args before querying for the BP email post
	 *
	 * @since 1.0.0
	 *
	 * @param  array  $args       Email query args
	 * @param  string $email_type Email type
	 * @return array Email query args
	 */
	public function email_query_args( $args, $email_type ) {

		// Make sure the email type is sent along to WP_Query
		if ( ! isset( $args['bp_email_type'] ) ) {
			$args['bp_email_type'] = $email_type;
		}

		return $args;
	}

	/**
	 * Modify the posts query result
	 *
	 * Blocks DB queries for non-existent plugin email types.
	 *
	 * @since 1.0.0
	 *
	 * @param null $retval Current return value
	 * @param WP_Query $query Query object
	 * @return null|array
	 */
	public function posts_pre_query( $retval, $query ) {

		// Get query's email type
		$email_type = $query->get( 'bp_email_type' );

		// Bail when this is not an email post query
		if ( ! $email_type )
			return $retval;

		// Bail when not querying for plugin email types
		if ( 0 !== strpos( $email_type, 'incassoos' ) )
			return $retval;

		// Return result of a single non-existent post
		return array( 0 );
	}

	/**
	 * Email delivery method that uses BP email templates
	 *
	 * @since 1.0.0
	 *
	 * @see incassoos_send_email()
	 *
	 * @param null $retval Current return value
	 * @param array $args Email arguments
	 * @return null|bool
	 */
	public function send_email( $retval, $args ) {

		// Require plugin email type
		if ( isset( $args['incassoos_email_type'] ) ) {

			// Use BP email template?
			if ( incassoos_bp_use_email_template() ) {

				// Provide list of tokens
				if ( ! isset( $args['tokens'] ) ) {
					$args['tokens'] = array();
				}

				// Set generic email tokens
				$args['tokens'] = wp_parse_args( $args['tokens'], array(
					'email.content'   => bp_core_replace_tokens_in_text( $args['message'],   $args['tokens'] ),
					'email.plaintext' => bp_core_replace_tokens_in_text( $args['plaintext'], $args['tokens'] ),

					// Pass email arguments to downstream
					'incassoos.args'  => $args
				) );

				// Use BP to send the email
				$retval = bp_send_email( $args['incassoos_email_type'], $args['to'], $args );
			}
		}

		return $retval;
	}

	/**
	 * Modify the post object for a BP email for plugin email types
	 *
	 * NOTE: unlike in BP itself, there is no registered email post for plugin emails.
	 * To support the email logic a fake dummy post object is constructed as a stand-in.
	 * This may result in issues where BP's email hooks do expect an actual post object.
	 *
	 * @since 1.0.0
	 *
	 * @see bp_get_email()
	 * @see BP_Email
	 *
	 * @param WP_Post $post Email post object
	 * @param string $email_type Email type
	 * @param array $args Email arguments
	 * @return WP_Post Email post object
	 */
	public function filter_email_post( $post, $email_type, $args ) {

		// Bail when this is not a plugin email type
		if ( 0 !== strpos( $email_type, 'incassoos' ) )
			return $post;

		// Setup dummy post object
		$dummy = array(
			'ID'                    => -9999,
			'post_status'           => 'publish',
			'post_author'           => 0,
			'post_parent'           => 0,
			'post_type'             => bp_get_email_post_type(), // Needed to load template file
			'post_date'             => 0,
			'post_date_gmt'         => 0,
			'post_modified'         => 0,
			'post_modified_gmt'     => 0,
			'post_content'          => '{{{email.content}}}',
			'post_title'            => '',
			'post_excerpt'          => '{{{email.plaintext}}}',
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

			// Provide email type for potentially specific template(s)
			'incassoos_email_type'  => $email_type
		);

		// Create the post object
		$post = new WP_Post( (object) $dummy );

		return $post;
	}

	/**
	 * Modify the email headers
	 *
	 * @since 1.0.0
	 *
	 * @see bp_email_set_default_headers()
	 * @see BP_Email::get()
	 *
	 * @param array    $headers       Email headers
	 * @param string   $property_name Unused
	 * @param string   $transform     Unused
	 * @param BP_Email $email         Email being sent
	 * @return array
	 */
	public function email_get_headers( $headers, $property_name, $transform, $email ) {

		// Get tokens
		$tokens = $email->get_tokens();

		// Bail wen this is not a plugin email type
		if ( ! isset( $tokens['incassoos.args'] ) )
			return $headers;

		// Get email arguments passed from upstream
		$_headers = $tokens['incassoos.args']['headers'];

		// Consider headers
		if ( ! empty( $_headers ) ) {

			// Walk headers
			foreach ( $tokens['incassoos.args']['headers'] as $header ) {
				list( $name, $content ) = explode( ':', $header, 2 );

				// Skip generic headers
				if ( ! in_array( strtolower( $name ), array( 'from', 'cc', 'bcc', 'content-type' ), true ) ) {
					$headers[ $name ] = $content;
				}
			}
		}

		return $headers;
	}

	/**
	 * Modify the email tokens
	 *
	 * @since 1.0.0
	 *
	 * @see bp_email_set_default_tokens()
	 * @see BP_Email::get()
	 *
	 * @param array    $tokens        Email tokens
	 * @param string   $property_name Unused
	 * @param string   $transform     Unused
	 * @param BP_Email $email         Email being sent
	 * @return array
	 */
	public function email_get_tokens( $tokens, $property_name, $transform, $email ) {

		// Bail wen this is not a plugin email type
		if ( ! isset( $tokens['incassoos.args'] ) )
			return $tokens;

		// Get email arguments passed from upstream
		$args = $tokens['incassoos.args'];

		// Check email type
		switch ( $args['incassoos_email_type'] ) {

			// Collection test collect email
			case 'incassoos-collection-test-collect':

				/**
				 * Set recipient details
				 *
				 * Because this email is sent to a the sender's email address, the recipient
				 * details are not parsed correctly.
				 */
				if ( isset( $args['user_id'] ) ) {
					$user = incassoos_get_user( $args['user_id'] );

					if ( $user ) {
						$tokens['recipient.name']     = wp_specialchars_decode( bp_core_get_user_displayname( $user->ID ), ENT_QUOTES );
						$tokens['recipient.username'] = $user->user_login;
					}
				}

				break;
		}

		return $tokens;
	}

	/**
	 * Setup BP email details for plugin email types
	 *
	 * @since 1.0.0
	 *
	 * @see bp_get_email()
	 *
	 * @param BP_Email $email Email object
	 * @param string $email_type Email type
	 * @param array $args Email arguments
	 * @return BP_Email Email post object
	 */
	public function send_email_setup( $email, $email_type, $to, $args ) {

		// Bail when this is not a plugin email type
		if ( 0 !== strpos( $email_type, 'incassoos' ) )
			return $email;

		// Define email from
		$email->set_from( $args['from'], $args['from_name'] );

		// Define email subject
		$email->set_subject( $args['subject'] );

		// Define email cc
		if ( ! empty( $args['cc'] ) ) {
			$email->set_cc( array_unique( array_merge( $email->get_cc(), $args['cc'] ) ) );
		}

		// Define email bcc
		if ( ! empty( $args['bcc'] ) ) {
			$email->set_bcc( array_unique( array_merge( $email->get_bcc(), $args['bcc'] ) ) );
		}
	}
}

/**
 * Setup the extension logic for BuddyPress
 *
 * @since 1.0.0
 *
 * @uses Incassoos_BuddyPress
 */
function incassoos_buddypress() {
	incassoos()->extend->buddypress = new Incassoos_BuddyPress;
}

endif; // class_exists
