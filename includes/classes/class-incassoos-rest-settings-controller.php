<?php

/**
 * Incassoos Settings Authentication
 *
 * @package Incassoos
 * @subpackage REST_API
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Incassoos_REST_Settings_Controller' ) ) :
/**
 * The Incassoos REST JWT Auth class
 *
 * @since 1.0.0
 */
class Incassoos_REST_Settings_Controller extends WP_REST_Controller {

	/**
	 * Setup this class
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->namespace = incassoos_get_rest_namespace();
		$this->rest_base = incassoos_get_settings_rest_base();

		$this->setup_actions();
	}

	/**
	 * Setup default actions and filters
	 *
	 * @since 1.0.0
	 */
	public function setup_actions() {
		add_filter( 'rest_endpoints_description', array( $this, 'endpoint_description' ) );
	}

	/**
	 * Enhance this endpoint's route description
	 *
	 * @param  array $data Route description
	 * @return array Route description
	 */
	public function endpoint_description( $data ) {

		// Is this endpoint self-aware?
		if ( isset( $data['_links'], $data['_links']['self'] ) ) {

			// Is this the current endpoint?
			if ( strpos( $data['_links']['self'][0]['href'], $this->namespace . '/' . $this->rest_base ) ) {

				// Signal that this is the settings endpoint
				$data['isSettings'] = true;
			}
		}

		return $data;
	}

	/**
	 * Registers the routes for the object of the controller.
	 *
	 * @see register_rest_route()
	 *
	 * @since 1.0.0
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_settings' ),
				'permission_callback' => array( $this, 'get_settings_permissions_check' ),
				'args'                => array(),
			),
			'schema' => array( $this, 'get_public_item_schema' )
		) );
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
			'title'      => 'incassoos-settings',
			'type'       => 'object',
			'properties' => array(
				'api' => array(
					'description' => __( 'Settings concerning the use of the API, like route names.', 'incassoos' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'login' => array(
					'description' => __( 'Settings concerning login.', 'incassoos' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'occasions' => array(
					'description' => __( 'Settings concerning occasions.', 'incassoos' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'consumers' => array(
					'description' => __( 'Settings concerning consumers.', 'incassoos' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'readonly'    => true,
				),
				'products' => array(
					'description' => __( 'Settings concerning products.', 'incassoos' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'readonly'    => true,
				)
			),
		);

		return $this->add_additional_fields_schema( $schema );
	}

	/**
	 * Checks if a given request has access to read objects.
	 *
	 * @since 1.0.0
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function get_settings_permissions_check( $request ) {

		// Check access
		if ( ! current_user_can( 'access_incassoos_rest_settings' ) ) {
			return new WP_Error( 'rest_forbidden_context', __( 'Sorry, you are not allowed to view these items.', 'incassoos' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Retreives a collection of objects
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_settings( $request ) {

		$schema = $this->get_item_schema();

		// Base fields for every item.
		$data = array();

		if ( ! empty( $schema['properties']['api'] ) ) {
			$data['api'] = array(
				'namespace'  => incassoos_get_rest_namespace(),
				'routes'     => array(
					'settings'      => incassoos_get_settings_rest_base(),
					'authorization' => incassoos_get_authorization_rest_base(),
					'occasions'     => incassoos_get_occasions_rest_base(),
					'orders'        => incassoos_get_orders_rest_base(),
					'products'      => incassoos_get_products_rest_base(),
					'consumers'     => incassoos_get_consumers_rest_base(),
					'consumerTypes' => incassoos_get_consumer_types_rest_base()
				)
			);
		}

		if ( ! empty( $schema['properties']['login'] ) ) {
			$data['login'] = array(
				'loginAttemptsAllowed' => apply_filters( 'incassoos_settings_login_attempts_allowed', 3 ),
				'loginAttemptsTimeout' => apply_filters( 'incassoos_settings_login_attempts_timeout', 2 * 60 )
			);
		}

		if ( ! empty( $schema['properties']['occasions'] ) ) {
			$data['occasions'] = array(
				'occasionType' => array(
					'taxonomyId'   => incassoos_get_occasion_type_tax_id(),
					'defaultValue' => incassoos_get_default_occasion_type(),
					'items'        => incassoos_get_occasion_types( array( 'fields' => 'id=>name' ) )
				)
			);
		}

		if ( ! empty( $schema['properties']['consumers'] ) ) {
			$data['consumers'] = array(
				'customSortName' => false
			);
		}

		if ( ! empty( $schema['properties']['products'] ) ) {
			$data['products'] = array(
				'customSortName' => _x( 'Menu Order', 'Products Sort By option', 'incassoos' )
			);
		}

		$context = ! empty( $request['context'] ) ? $request['context'] : 'view';
		$data    = $this->add_additional_fields_to_object( $data, $request );
		$data    = $this->filter_response_by_context( $data, $context );

		// Wrap the data in a response object.
		$response = rest_ensure_response( $data );

		/**
		 * Filters the item data for a response.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_REST_Response $response The response object.
		 * @param WP_REST_Request  $request  Request object.
		 */
		$response = apply_filters( 'incassoos_rest_prepare_settings', $response, $request );
		$response = rest_ensure_response( $response );

		$response->header( 'X-WP-Total', 1 );
		$response->header( 'X-WP-TotalPages', 1);

		return $response;
	}
}

endif; // class_exists
