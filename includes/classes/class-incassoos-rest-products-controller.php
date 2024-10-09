<?php

/**
 * Incassoos REST Products Controller Class
 *
 * @package Incassoos
 * @subpackage REST_API
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Incassoos_REST_Products_Controller' ) ) :
/**
 * The Incassoos REST Products Controller class
 *
 * @see WP_REST_Posts_Controller
 *
 * @since 1.0.0
 */
class Incassoos_REST_Products_Controller extends WP_REST_Posts_Controller {

	/**
	 * Setup this class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->post_type = incassoos_get_product_post_type();
		$this->namespace = incassoos_get_rest_namespace();
		$this->rest_base = incassoos_get_products_rest_base();

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
				'permission_callback' => array( $this, 'delete_item_permissions_check' ),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/untrash', array(
			'args' => array(
				'id' => array(
					'description' => __( 'Unique identifier for the object.' ),
					'type'        => 'integer',
				),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'untrash_item' ),
				'permission_callback' => array( $this, 'untrash_item_permissions_check' ),
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
		$schema = incassoos_rest_remove_schema_properties( $schema, array(
			'parent'
		) );

		$schema['properties'] = array_merge( $schema['properties'], array(
			'price'           => array(
				'description' => __( 'The price of the product.', 'incassoos' ),
				'type'        => 'number',
				'context'     => array( 'view', 'edit' )
			),
			'menu_order'      => array(
				'description' => __( 'The order of the object in relation to other object of its type.', 'incassoos' ),
				'type'        => 'integer',
				'context'     => array( 'view', 'edit' )
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
		if ( ! current_user_can( 'access_incassoos_rest_products' ) ) {
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

		if ( ! empty( $schema['properties']['price'] ) ) {
			$object['price'] = incassoos_get_product_price( $post );
		}

		if ( ! empty( $schema['properties']['menu_order'] ) ) {
			$object['menu_order'] = incassoos_get_product_menu_order( $post );
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

		// Price
		if ( isset( $request['price'] ) ) {
			$result = incassoos_update_product_price( $request['price'], $object );

			if ( ! $result ) {
				return new WP_Error(
					'incassoos_rest_invalid_product_price_field',
					__( 'Could not update the price of the product.', 'incassoos' ),
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
	 * Checks if a given request has access to restore a post from the Trash.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has access to untrash the item, WP_Error object otherwise.
	 */
	public function untrash_item_permissions_check( $request ) {
		$post = $this->get_post( $request['id'] );
		if ( is_wp_error( $post ) ) {
			return $post;
		}

		if ( $post && ! $this->check_delete_permission( $post ) ) {
			return new WP_Error(
				'incassoos_rest_user_cannot_untrash',
				__( 'Sorry, you are not allowed to restore this item from the Trash.', 'incassoos' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Restore a single post from the Trash.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function untrash_item( $request ) {
		$post = $this->get_post( $request['id'] );
		if ( is_wp_error( $post ) ) {
			return $post;
		}

		$id = $post->ID;

		if ( ! $this->check_delete_permission( $post ) ) {
			return new WP_Error(
				'incassoos_rest_user_cannot_untrash',
				__( 'Sorry, you are not allowed to restore this item from the Trash.', 'incassoos' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		$request->set_param( 'context', 'edit' );

		// Only untrash if we have already trashed.
		if ( 'trash' !== $post->post_status ) {
			return new WP_Error(
				'incassoos_rest_not_trashed',
				__( 'The post has not been trashed.', 'incassoos' ),
				array( 'status' => 410 )
			);
		}

		$result   = wp_untrash_post( $id );
		$post     = get_post( $id );
		$response = $this->prepare_item_for_response( $post, $request );

		if ( ! $result ) {
			return new WP_Error(
				'incassoos_rest_cannot_untrash',
				__( 'The post cannot be restored from the Trash.', 'incassoos' ),
				array( 'status' => 500 )
			);
		}

		/**
		 * Fires immediately after a single post is untrashed via the REST API.
		 *
		 * They dynamic portion of the hook name, `$this->post_type`, refers to the post type slug.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_Post          $post     The deleted or trashed post.
		 * @param WP_REST_Response $response The response data.
		 * @param WP_REST_Request  $request  The request sent to the API.
		 */
		do_action( "incassoos_rest_untrash_{$this->post_type}", $post, $response, $request );

		return $response;
	}
}

endif; // class_exists
