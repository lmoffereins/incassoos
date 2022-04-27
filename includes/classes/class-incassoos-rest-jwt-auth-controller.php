<?php

/**
 * Incassoos REST JWT Authentication
 *
 * @package Incassoos
 * @subpackage REST_API
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Incassoos_REST_JWT_Auth_Controller' ) ) :
/**
 * The Incassoos REST JWT Auth class
 *
 * @since 1.0.0
 */
class Incassoos_REST_JWT_Auth_Controller {

	/**
	 * Setup this class
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->namespace = incassoos_get_rest_namespace();
		$this->rest_base = incassoos_get_authorization_rest_base();
		$this->enabled   = get_option( '_incassoos_jwt_auth_enabled', false );

		// Bail when JWT authentication is not enabled
		if ( ! $this->enabled )
			return;

		$this->includes();
		$this->setup_actions();
	}

	/**
	 * Include the required files
	 *
	 * @since 1.0.0
	 */
	public function includes() {
		require_once( incassoos()->includes_dir . 'vendor/php-jwt/BeforeValidException.php' );
		require_once( incassoos()->includes_dir . 'vendor/php-jwt/ExpiredException.php' );
		require_once( incassoos()->includes_dir . 'vendor/php-jwt/SignatureInvalidException.php' );
		require_once( incassoos()->includes_dir . 'vendor/php-jwt/JWT.php' );
	}

	/**
	 * Setup default actions and filters
	 *
	 * @since 1.0.0
	 */
	public function setup_actions() {
		add_filter( 'rest_api_init',              array( $this, 'filter_cors_headers' ), 15    );
		add_filter( 'rest_authentication_errors', array( $this, 'authenticate'        ),  5, 3 );
	}

	/**
	 * Registers the routes for the object of the controller.
	 *
	 * @see register_rest_route()
	 *
	 * @since 1.0.0
	 */
	public function register_routes() {

		// Bail when JWT authentication is not enabled
		if ( ! $this->enabled )
			return;

		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'tokenate' ),
				'permission_callback' => '__return_true',
			),
			'schema' => array( $this, 'get_item_schema' )
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/validate', array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'validate' ),
				'permission_callback' => '__return_true',
			)
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/invalidate', array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'invalidate' ),
				'permission_callback' => '__return_true',
			)
		) );
	}

	/**
	 * Checks if a given request has access to this endpoint.
	 *
	 * @since 1.0.0
	 *
	 * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function get_permissions_check( $user_id = 0 ) {

		// Default to the current user
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		// Check access
		if ( ! user_can( $user_id, 'access_incassoos_rest_api' ) ) {
			return new WP_Error( 'incassoos_rest_forbidden', esc_html__( 'Sorry, you are not allowed to access the Incassoos REST API.', 'incassoos' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Return whether this is an authorization request
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Optional. REST request object. Defaults to the current request.
	 * @return bool Is this an authorization request?
	 */
	public function is_auth_request( $request = '' ) {
		$route = is_a( $request, 'WP_REST_Request' ) ? $request->get_route() : untrailingslashit( $GLOBALS['wp']->query_vars['rest_route'] );

		return 0 === strpos( $route, '/' . $this->namespace . '/' . $this->rest_base );
	}

	/**
	 * Authenticate the current user in WP's rest authentication location
	 *
	 * @see WP_REST_Server::check_authentication()
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Error|Boolean $retval Authentication value
	 * @return WP_Error|Boolean Authentication value
	 */
	public function authenticate( $retval ) {

		// Another authentication method was used
		if ( ! is_null( $retval ) ) {
			return $retval;
		}

		// Bail when this is not a rest request
		if ( ! incassoos_doing_rest() ) {
			return $retval;
		}

		// Concerning a plugin's rest route
		if ( incassoos_match_rest_route_namespace() && ! $this->is_auth_request() ) {

			// Validate the token in the request
			$response = $this->validate();

			// An error occurred when validating
			if ( is_wp_error( $response ) ) {

				// When the header was not provided, report the error
				if ( 'incassoos_rest_auth_no_header' !== $response->get_error_code() ) {
					$retval = $response;
				}

			// Set the current user, continue the request
			} else {
				$user = get_user_by( 'login', $response->data['data']['user_login'] );

				wp_set_current_user( $user->ID );

				// Check access
				$retval = $this->get_permissions_check();
			}
		}

		return $retval;
	}

	/**
	 * Create a token for a single user
	 *
	 * @since 1.0.0
	 *
	 * @uses apply_filters() Calls 'incassoos_rest_jwt_auth_args'
	 * @uses apply_filters() Calls 'incassoos_rest_jwt_auth_tokenate'
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function tokenate( $request ) {

		// Get authentication secret key
		$secret = get_option( '_incassoos_jwt_auth_secret', false );
		if ( ! $secret ) {
			return new WP_Error(
				'incassoos_rest_auth_bad_config',
				esc_html__( 'REST authentication is not configured properly.', 'incassoos' ),
				array( 'status' => 403 )
			);
		}

		// Try to authenticate the user with username/password combination
		$user = wp_authenticate(
			$request->get_param( 'username' ),
			$request->get_param( 'password' )
		);

		if ( is_wp_error( $user ) ) {
			$code = $user->get_error_code();
			return new WP_Error(
				"incassoos_rest_auth-{$code}",
				trim( strip_tags( $user->get_error_message( $code ) ) ),
				array( 'status' => 403 )
			);
		}

		// Check access
		$access_check = $this->get_permissions_check( $user->ID );
		if ( is_wp_error( $access_check ) ) {
			return $access_check;
		}

		// The user exists, setup token
		$issued_at = time();
		$args = apply_filters( 'incassoos_rest_jwt_auth_args', array(
			'iss'  => get_bloginfo('url'),
			'iat'  => $issued_at,
			'nbf'  => $issued_at,
			'exp'  => $issued_at + (DAY_IN_SECONDS * 7),
			'data' => array(
				'user' => array(
					'id' => $user->ID
				)
			)
		), $user );

		// Generate token, setup token data
		$token_data = apply_filters( 'incassoos_rest_jwt_auth_tokenate', array(
			'token'             => JWT::encode( $args, $secret ),
			'user_login'        => $user->data->user_login, // Use as user identifier. Least likely to change over time
			'user_display_name' => $user->data->display_name
		), $user );

		// Register user token, to enable later invalidation
		add_user_meta( $user->ID, '_incassoos_jwt_auth_token', $token_data['token'] );

		return rest_ensure_response( array(
			'code' => 'incassoos_rest_auth_success',
			'data' => array_merge( $token_data, array(
				'status' => 200,
				'roles'  => incassoos_get_roles_for_user( $user->ID )
			) )
		) );
	}

	/**
	 * Validate a single authorization token
	 *
	 * @since 1.0.0
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function validate() {

		// Get the authorization header token
		$auth_token = $this->get_request_auth_token();
		if ( is_wp_error( $auth_token ) ) {
			return $auth_token;
		}

		// Get authentication secret key
		$secret = get_option( '_incassoos_jwt_auth_secret', false );
		if ( ! $secret ) {
			return new WP_Error(
				'incassoos_rest_auth_bad_config',
				esc_html__( 'Authentication is not configured properly.', 'incassoos' ),
				array( 'status' => 403 )
			);
		}

		// Decode the token
		try {

			$token = JWT::decode( $auth_token, $secret, array( 'HS256' ) );

			// Validate ISS
			if ( $token->iss !== get_bloginfo('url') ) {
				return new WP_Error(
					'incassoos_rest_auth_invalid_iss',
					esc_html__( 'Authentication ISS does not match the server.', 'incassoos' ),
					array( 'status' => 403 )
				);
			}

			// Validate user
			$user = get_user_by( 'id', $token->data->user->id );
			if ( ! $user || ! $user->exists() ) {
				return new WP_Error(
					'incassoos_rest_auth_invalid_user',
					esc_html__( 'Authentication did not match a user account.', 'incassoos' ),
					array( 'status' => 403 )
				);
			}

			// Validate token
			$user_tokens = get_user_meta( $user->ID, '_incassoos_jwt_auth_token', false );
			if ( ! in_array( $auth_token, $user_tokens ) ) {
				return new WP_Error(
					'incassoos_rest_auth_invalid_token',
					esc_html__( 'Authentication token is invalid.', 'incassoos' ),
					array( 'status' => 403 )
				);
			}

			// Check access
			$access_check = $this->get_permissions_check( $user->ID );
			if ( is_wp_error( $access_check ) ) {
				return $access_check;
			}

			// Define response of the validated token
			return rest_ensure_response( array(
				'code' => 'incassoos_rest_auth_success',
				'data' => array(
					'status'            => 200,
					'user_login'        => $user->user_login, // Use as identifier. Least likely to change over time
					'user_display_name' => $user->data->display_name,
					'roles'             => incassoos_get_roles_for_user( $user->ID )
				)
			) );

		// Something went wrong during decoding
		} catch ( Exception $error ) {
			return new WP_Error(
				'incassoos_rest_auth_invalid_token',
				sprintf( esc_html__( 'Authorization error: %s.', 'incassoos' ), esc_html( $error->getMessage() ) ),
				array( 'status' => 403 )
			);
		}
	}

	/**
	 * Invalidate a single authorization token
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function invalidate( $request ) {

		// Try to validate the response first
		$response = $this->validate( $request );

		// Remove validated token from user
		if ( ! is_wp_error( $response ) ) {
			delete_user_meta( $response->data['data']['user_id'], '_incassoos_jwt_auth_token', $this->get_request_auth_token() );
		}

		return rest_ensure_response( $response );
	}

	/**
	 * Invalidate all registered authorization tokens
	 *
	 * @since 1.0.0
	 *
	 * @return bool Deletion success
	 */
	public static function invalidate_all() {
		return delete_metadata( 'user', null, '_incassoos_jwt_auth_token', '', true );
	}

	/**
	 * Return the authorization token from the request headers
	 *
	 * @since 1.0.0
	 *
	 * @return string|WP_Error Authorization token or WP_Error when not found
	 */
	public function get_request_auth_token() {

		// Find the authorization header
		$header = isset( $_SERVER['HTTP_AUTHORIZATION'] ) ? $_SERVER['HTTP_AUTHORIZATION'] : false;
		if ( ! $header ) {
			$header = isset( $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ) ? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] : false;
		}

		if ( ! $header ) {
			return new WP_Error(
				'incassoos_rest_auth_no_header',
				esc_html__( 'Authorization header is not found.', 'incassoos' ),
				array( 'status' => 403 )
			);
		}

		// Extract token from the header
		list( $auth_token ) = sscanf( $header, 'Bearer %s' );
		if ( ! $auth_token ) {
			return new WP_Error(
				'incassoos_rest_auth_bad_header',
				esc_html__( 'Authorization header is malformed.', 'incassoos' ),
				array( 'status' => 403 )
			);
		}

		return $auth_token;
	}

	/**
	 * Allow all CORS for plugin requests
	 *
	 * @since 1.0.0
	 */
	public function filter_cors_headers() {

		// Allow CORS for plugin routes
		if ( incassoos_match_rest_route_namespace() ) {

			// Unhook default WP filter
			remove_filter( 'rest_pre_serve_request', 'rest_send_cors_headers' );

			// Hook plugin filter
			add_filter( 'rest_pre_serve_request', array( $this, 'send_cors_headers' ) );
		}
	}

	/**
	 * Send CORS headers to allow all
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $retval
	 * @return mixed
	 */
	public function send_cors_headers( $retval ) {
		header( 'Access-Control-Allow-Origin: *' );
		header( 'Access-Control-Allow-Headers: Access-Control-Allow-Headers, Content-Type, Authorization' );
		header( 'Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, PATCH, DELETE' );
		header( 'Access-Control-Allow-Credentials: true' );

		return $retval;
	}

	/**
	 * Retreives the object's schema, conforming to JSON Schema.
	 *
	 * @since 1.0.0
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {

		$schema = array(
			'$schema'    => 'http://json-schema.org/schema#',
			'title'      => 'incassoos-jwt-auth',
			'type'       => 'object',
			'properties' => array(
				'token'             => array(
					'description' => __( 'Unique authentication token of the user.', 'incassoos' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'user_login'        => array(
					'description' => __( 'Login name of the user.', 'incassoos' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'user_email'        => array(
					'description' => __( 'Email address of the user.', 'incassoos' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'user_display_name' => array(
					'description' => __( 'Display name of the user.', 'incassoos' ),
					'type'        => 'string',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
			),
		);

		return $schema;
	}
}

endif; // class_exists
