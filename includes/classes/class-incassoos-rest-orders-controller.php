<?php

/**
 * Incassoos REST Orders Controller Class
 *
 * @package Incassoos
 * @subpackage REST_API
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Incassoos_REST_Orders_Controller' ) ) :
/**
 * The Incassoos REST Orders Controller class
 *
 * @see WP_REST_Posts_Controller
 *
 * @since 1.0.0
 */
class Incassoos_REST_Orders_Controller extends WP_REST_Posts_Controller {

	/**
	 * Setup this class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->post_type = incassoos_get_order_post_type();
		$this->namespace = incassoos_get_rest_namespace();
		$this->rest_base = incassoos_get_orders_rest_base();

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
			'parent'          => array(
				'description' => __( 'The ID for the parent of the object.' ),
				'type'        => 'integer',
				'required'    => true,
				'context'     => array( 'view', 'edit' ),
			),
			'consumer'        => array(
				'description' => __( 'Unique identifier for the consumer (type) of the order.', 'incassoos' ),
				'type'        => 'string',
				'required'    => true,
				'context'     => array( 'view', 'edit' ),
				'arg_options' => array(
					'validate_callback' => 'incassoos_validate_consumer_id',
				)
			),
			'consumer_name'   => array(
				'description' => __( 'The name of the consumer of the order.', 'incassoos' ),
				'type'        => 'string',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true
			),
			'products'        => array(
				'description' => __( 'List of consumed products for the order.', 'incassoos' ),
				'type'        => 'array',
				'items'       => array(
					'type' => 'object'
				),
				'required'    => true,
				'context'     => array( 'view', 'edit' ),
				'arg_options' => array(
					'validate_callback' => 'incassoos_validate_order_products',
				)
			),
			'_applicationFields' => array(
				'description' => __( 'List of additional field names in this endpoint to make known to the application.', 'incassoos' ),
				'type'        => 'array',
				'context'     => array( 'view', 'edit' )
			)
		) );

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
		if ( ! current_user_can( 'access_incassoos_rest_orders' ) ) {
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

		if ( ! empty( $schema['properties']['consumer'] ) ) {
			$object['consumer'] = incassoos_get_order_consumer( $post );
		}

		if ( ! empty( $schema['properties']['consumer_name'] ) ) {
			$object['consumer_name'] = incassoos_get_order_consumer_title( $post );
		}

		if ( ! empty( $schema['properties']['products'] ) ) {
			$object['products'] = array_values( incassoos_get_order_products( $post ) );
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
	 * Retrieves the query params for the posts collection.
	 *
	 * @since 1.0.0
	 *
	 * @return array Collection parameters.
	 */
	public function get_collection_params() {
		$query_params = parent::get_collection_params();

		/**
		 * Support querying by parent regardless of hierarchical post type
		 */
		$query_params['parent'] = array(
			'description'       => __( 'Limit result set to items with particular parent IDs.' ),
			'type'              => 'array',
			'items'             => array(
				'type'          => 'integer',
			),
			'default'           => array(),
		);

		return $query_params;
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

		// Consumer
		if ( isset( $request['consumer'] ) ) {
			$result = incassoos_update_order_consumer( $request['consumer'], $object );

			if ( ! $result ) {
				return new WP_Error(
					'incassoos_rest_invalid_order_consumer_field',
					__( 'Invalid consumer ID or consumer type.', 'incassoos' ),
					array( 'status' => 400 )
				);
			}
		}

		// Products
		if ( isset( $request['products'] ) ) {
			$result = incassoos_update_order_products( $request['products'], $object );

			if ( ! $result ) {
				return new WP_Error(
					'incassoos_rest_invalid_order_products_field',
					__( 'Could not update the products of the order.', 'incassoos' ),
					array( 'status' => 400 )
				);
			}
		}

		/**
		 * Taxonomy terms are auto-updated through the default post controller.
		 */

		return parent::update_additional_fields_for_object( $object, $request );
	}
}

endif; // class_exists
