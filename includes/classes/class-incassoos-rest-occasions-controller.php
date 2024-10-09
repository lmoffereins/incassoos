<?php

/**
 * Incassoos REST Occasions Controller Class
 *
 * @package Incassoos
 * @subpackage REST_API
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Incassoos_REST_Occasions_Controller' ) ) :
/**
 * The Incassoos REST Occasions Controller class
 *
 * @see WP_REST_Posts_Controller
 *
 * @since 1.0.0
 */
class Incassoos_REST_Occasions_Controller extends WP_REST_Posts_Controller {

	/**
	 * Setup this class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->post_type = incassoos_get_occasion_post_type();
		$this->namespace = incassoos_get_rest_namespace();
		$this->rest_base = incassoos_get_occasions_rest_base();

		$this->meta = new WP_REST_Post_Meta_Fields( $this->post_type );
	}

	/**
	 * Registers the routes for the objects of the controller.
	 *
	 * @see register_rest_route()
	 *
	 * @since 1.0.0
	 */
	public function register_routes() {

		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => $this->get_collection_params(),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'create_item_permissions_check' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
			'args' => array(
				'id' => array(
					'description' => __( 'Unique identifier for the object.' ),
					'type'        => 'integer',
				),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_item' ),
				'permission_callback' => array( $this, 'update_item_permissions_check' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_item' ),
				'permission_callback' => array( $this, 'delete_item_permissions_check' )
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/close', array(
			'args' => array(
				'id' => array(
					'description' => __( 'Unique identifier for the object.' ),
					'type'        => 'integer',
				),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'close_item' ),
				'permission_callback' => array( $this, 'close_item_permissions_check' ),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/reopen', array(
			'args' => array(
				'id' => array(
					'description' => __( 'Unique identifier for the object.' ),
					'type'        => 'integer',
				),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'reopen_item' ),
				'permission_callback' => array( $this, 'reopen_item_permissions_check' ),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );
	}

	/**
	 * Checks if a given post type can be viewed or managed.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post_Type|string $post_type Post type name or object.
	 * @return bool Whether the post type is allowed in REST.
	 */
	protected function check_is_post_type_allowed( $post_type ) {
		return true;
	}

	/**
	 * Retrieves the post's schema, conforming to JSON Schema.
	 *
	 * @since 1.0.0
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		$schema = parent::get_item_schema();
		$schema = incassoos_rest_remove_schema_properties( $schema );

		$schema['properties'] = array_merge( $schema['properties'], array(
			'occasion_date' => array(
				'description' => __( 'The date the occasion was scheduled, in no particular timezone.', 'incassoos' ),
				'type'        => 'string',
				'format'      => 'date-time',
				'required'    => true,
				'context'     => array( 'view', 'edit' ),
				'arg_options' => array(
					'validate_callback' => 'incassoos_validate_date'
				)
			),
			'defaultProductCategory' => array(
				'description' => __( 'The default product category for the object.', 'incassoos' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' )
			),
			'consumers'     => array(
				'description' => __( 'List of identifiers for consumers that are registered for the occasion.', 'incassoos' ),
				'type'        => 'array',
				'items'       => array(
					'type' => 'integer'
				),
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true
			),
			'closed'        => array(
				'description' => __( 'The date the occasion was closed.', 'incassoos' ),
				'type'        => 'string',
				'format'      => 'date-time',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true
			),
			'_applicationFields' => array(
				'description' => __( 'List of additional field names in this endpoint to make known to the application.', 'incassoos' ),
				'type'        => 'array',
				'context'     => array( 'view', 'edit' )
			)
		) );

		// Make raw title available in the view context
		$schema['properties']['title']['properties']['raw']['context'][] = 'view';

		/**
		 * Taxonomy terms are added through the default post controller.
		 */

		return $schema;
	}

	/**
	 * Checks if a given request has access to read objects.
	 *
	 * @since 1.0.0
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
	 */
	public function get_items_permissions_check( $request ) {

		// Check access
		if ( ! current_user_can( 'access_incassoos_rest_occasions' ) ) {
			return new WP_Error( 'rest_forbidden_context', __( 'Sorry, you are not allowed to view these items.', 'incassoos' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Adds basic object data and the values from additional fields to a data object.
	 *
	 * @since 1.0.0
	 *
	 * @global WP_Post $post
	 *
	 * @param array           $object  Data object.
	 * @param WP_REST_Request $request Full details about the request.
	 * @return array Modified data object with additional fields.
	 */
	public function add_additional_fields_to_object( $object, $request ) {
		global $post;

		$schema = $this->get_item_schema();

		if ( ! empty( $schema['properties']['occasion_date'] ) ) {
			$object['occasion_date'] = $this->prepare_date_response( incassoos_get_occasion_date( $post, 'Y-m-d H:i:s' ) );
		}

		if ( ! empty( $schema['properties']['defaultProductCategory'] ) ) {
			$object['defaultProductCategory'] = incassoos_get_occasion_default_product_category( $post );
		}

		if ( ! empty( $schema['properties']['consumers'] ) ) {
			$object['consumers'] = incassoos_get_occasion_consumers( $post );
		}

		if ( ! empty( $schema['properties']['closed'] ) ) {
			$object['closed'] = $this->prepare_date_response( incassoos_get_occasion_closed_date_gmt( $post, 'Y-m-d H:i:s' ) );
		}

		if ( ! empty( $schema['properties']['_applicationFields'] ) ) {
			$object['_applicationFields'] = array_keys( $this->get_additional_fields() );
		}

		/**
		 * Support additional fields defined for the post type
		 */
		$object = parent::add_additional_fields_to_object( $object, $request );

		return $object;
	}

	/**
	 * Update values of additional fields added to a data object.
	 *
	 * @since 1.0.0
	 *
	 * @param array           $object  Data Object.
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool|WP_Error True on success, WP_Error object if a field cannot be updated.
	 */
	public function update_additional_fields_for_object( $object, $request ) {

		// Occasion date
		if ( isset( $request['occasion_date'] ) ) {
			$result = incassoos_update_occasion_date( $request['occasion_date'], $object );

			if ( ! $result ) {
				return new WP_Error(
					'incassoos_rest_invalid_date_field',
					__( 'Could not update the date of the occasion.', 'incassoos' ),
					array( 'status' => 400 )
				);
			}
		}

		// Default product category
		if ( isset( $request['defaultProductCategory'] ) ) {
			$result = incassoos_update_occasion_default_product_category( $request['defaultProductCategory'], $object );

			if ( ! $result ) {
				return new WP_Error(
					'incassoos_rest_invalid_default_product_category',
					__( 'Could not update the default product category of the occasion.', 'incassoos' ),
					array( 'status' => 400 )
				);
			}
		}

		/**
		 * Taxonomy terms are auto-updated through the default post controller.
		 */

		return parent::update_additional_fields_for_object( $object, $request );
	}

	/**
	 * Checks if a given request has access to close an Occasion
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has access to close the item, WP_Error object otherwise.
	 */
	public function close_item_permissions_check( $request ) {
		$post = $this->get_post( $request['id'] );
		if ( is_wp_error( $post ) ) {
			return $post;
		}

		if ( $post && ! current_user_can( 'close_incassoos_occasion', $post ) ) {
			return new WP_Error(
				'incassoos_rest_user_cannot_close',
				__( 'Sorry, you are not allowed to close this item.', 'incassoos' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Close a single Occasion
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function close_item( $request ) {
		$post = $this->get_post( $request['id'] );
		if ( is_wp_error( $post ) ) {
			return $post;
		}

		$id = $post->ID;

		if ( ! current_user_can( 'close_incassoos_occasion', $post ) ) {
			return new WP_Error(
				'incassoos_rest_user_cannot_close',
				__( 'Sorry, you are not allowed to close this item.', 'incassoos' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		$request->set_param( 'context', 'edit' );

		// Only close if we haven't already.
		if ( incassoos_is_occasion_locked( $post ) ) {
			return new WP_Error(
				'incassoos_rest_is_locked',
				__( 'The item is locked.', 'incassoos' ),
				array( 'status' => 410 )
			);
		}

		$result   = incassoos_close_occasion( $post );
		$post     = get_post( $id );
		$response = $this->prepare_item_for_response( $post, $request );

		if ( ! $result ) {
			return new WP_Error(
				'incassoos_rest_cannot_close',
				__( 'The item cannot be closed.', 'incassoos' ),
				array( 'status' => 500 )
			);
		}

		/**
		 * Fires immediately after a single occasion is closed via the REST API.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_Post          $post     The closed post.
		 * @param WP_REST_Response $response The response data.
		 * @param WP_REST_Request  $request  The request sent to the API.
		 */
		do_action( "incassoos_rest_close_occasion", $post, $response, $request );

		return $response;
	}

	/**
	 * Checks if a given request has access to reopen an Occasion
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has access to reopen the item, WP_Error object otherwise.
	 */
	public function reopen_item_permissions_check( $request ) {
		$post = $this->get_post( $request['id'] );
		if ( is_wp_error( $post ) ) {
			return $post;
		}

		if ( $post && ! current_user_can( 'reopen_incassoos_occasion', $post ) ) {
			return new WP_Error(
				'incassoos_rest_user_cannot_reopen',
				__( 'Sorry, you are not allowed to reopen this item.', 'incassoos' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Reopen a single Occasion
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function reopen_item( $request ) {
		$post = $this->get_post( $request['id'] );
		if ( is_wp_error( $post ) ) {
			return $post;
		}

		$id = $post->ID;

		if ( ! current_user_can( 'reopen_incassoos_occasion', $post ) ) {
			return new WP_Error(
				'incassoos_rest_user_cannot_reopen',
				__( 'Sorry, you are not allowed to reopen this item.', 'incassoos' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		$request->set_param( 'context', 'edit' );

		// Only reopen if we have already closed.
		if ( ! incassoos_is_occasion_closed( $post ) ) {
			return new WP_Error(
				'incassoos_rest_is_not_closed',
				__( 'The item is not closed.', 'incassoos' ),
				array( 'status' => 410 )
			);
		}

		$result   = incassoos_reopen_occasion( $post );
		$post     = get_post( $id );
		$response = $this->prepare_item_for_response( $post, $request );

		if ( ! $result ) {
			return new WP_Error(
				'incassoos_rest_cannot_reopen',
				__( 'The item cannot be reopened.', 'incassoos' ),
				array( 'status' => 500 )
			);
		}

		/**
		 * Fires immediately after a single post is reopened via the REST API.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_Post          $post     The reopened post.
		 * @param WP_REST_Response $response The response data.
		 * @param WP_REST_Request  $request  The request sent to the API.
		 */
		do_action( "incassoos_rest_reopen_occasion", $post, $response, $request );

		return $response;
	}
}

endif; // class_exists
