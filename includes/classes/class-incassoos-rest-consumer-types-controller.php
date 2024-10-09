<?php

/**
 * Incassoos REST Consumer Types Controller Class
 *
 * @package Incassoos
 * @subpackage REST_API
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Incassoos_REST_Consumer_Types_Controller' ) ) :
/**
 * The Incassoos REST Consumer Types Controller class
 *
 * @see WP_REST_Controller
 *
 * @since 1.0.0
 */
class Incassoos_REST_Consumer_Types_Controller extends WP_REST_Controller {

	/**
	 * Setup this class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->namespace = incassoos_get_rest_namespace();
		$this->rest_base = incassoos_get_consumer_types_rest_base();
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
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'                => $this->get_collection_params(),
			),
			'schema' => array( $this, 'get_public_item_schema' )
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\w-]+)', array(
			'args' => array(
				'id' => array(
					'description' => __( 'Unique identifier for the object.' ),
					'type'        => 'string',
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

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\w-]+)/archive', array(
			'args' => array(
				'id' => array(
					'description' => __( 'Unique identifier for the object.' ),
					'type'        => 'string',
				),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'archive_item' ),
				'permission_callback' => array( $this, 'archive_item_permissions_check' ),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\w-]+)/unarchive', array(
			'args' => array(
				'id' => array(
					'description' => __( 'Unique identifier for the object.' ),
					'type'        => 'string',
				),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'unarchive_item' ),
				'permission_callback' => array( $this, 'unarchive_item_permissions_check' ),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
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
			'title'      => 'incassoos-consumers',
			'type'       => 'object',
			'properties' => array(
				'id'              => array(
					'description' => __( 'Unique identifier for the object.', 'incassoos' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'name'            => array(
					'description' => __( 'Display name of the object.', 'incassoos' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true,
				),
				'avatarUrl'       => array(
					'description' => __( "Path to the object's avatar image", 'incassoos' ),
					'type'        => 'string',
					'format'      => 'uri',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true
				),
				'archived'        => array(
					'description' => __( 'Whether the object is archived.', 'incassoos' ),
					'type'        => 'boolean',
					'context'     => array( 'view', 'edit' ),
				),
				'_builtin'        => array(
					'description' => __( 'Whether the object is builtin.', 'incassoos' ),
					'type'        => 'boolean',
					'context'     => array( 'view' ),
				),
				'_applicationFields' => array(
					'description' => __( 'List of additional field names in this endpoint to make known to the application.', 'incassoos' ),
					'type'        => 'array',
					'context'     => array( 'view', 'edit' )
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
	public function get_items_permissions_check( $request ) {

		// Check access
		if ( ! current_user_can( 'access_incassoos_rest_consumer_types' ) ) {
			return new WP_Error( 'rest_forbidden_context', esc_html__( 'Sorry, you are not allowed to view these items.', 'incassoos' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Get the consumer type, if the ID is valid.
	 *
	 * @since 1.0.0
	 *
	 * @param int|string $id Supplied ID.
	 * @return Incassoos_Consumer_Type|WP_Error Consumer type object if ID is valid, WP_Error otherwise.
	 */
	protected function get_consumer_type( $id ) {
		$error = new WP_Error( 'rest_consumer_type_invalid_id', __( 'Invalid consumer type ID.' ), array( 'status' => 404 ) );
		if ( is_numeric( $id ) && (int) $id <= 0 ) {
			return $error;
		}

		$type = incassoos_get_consumer_type( $id );
		if ( empty( $type ) ) {
			return $error;
		}

		return $type;
	}

	/**
	 * Retreives a collection of objects
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_items( $request ) {

		// Retrieve the list of registered collection query parameters.
		$registered = $this->get_collection_params();
		$args = array();

		/*
		 * For each known parameter which is both registered and present in the request,
		 * set the parameter's value on the query $args.
		 */
		foreach ( array_keys( $registered ) as $api_param ) {
			if ( isset( $registered[ $api_param ], $request[ $api_param ] ) ) {
				$args[ $api_param ] = $request[ $api_param ];
			}
		}

		// Filter the query arguments for a request
		$query_args = apply_filters( 'incassoos_rest_consumer_types_query', $args, $request );

		$items_query  = incassoos_query_consumer_types( $query_args );
		$query_result = $items_query->query_result;

		$items = array();

		foreach ( $query_result as $item ) {
			$data    = $this->prepare_item_for_response( $item, $request );
			$items[] = $this->prepare_response_for_collection( $data );
		}

		$page = (int) $query_args['page'];
		$total_items = $items_query->total_count;

		$max_pages = ceil( $total_items / (int) $items_query->query_vars['per_page'] );

		if ( $page > $max_pages && $total_items > 0 ) {
			return new WP_Error( 'rest_post_invalid_page_number', __( 'The page number requested is larger than the number of pages available.' ), array( 'status' => 400 ) );
		}

		$response = rest_ensure_response( $items );

		$response->header( 'X-WP-Total', (int) $total_items );
		$response->header( 'X-WP-TotalPages', (int) $max_pages );

		$request_params = $request->get_query_params();
		$base = add_query_arg( $request_params, rest_url( sprintf( '%s/%s', $this->namespace, $this->rest_base ) ) );

		if ( $page > 1 ) {
			$prev_page = $page - 1;

			if ( $prev_page > $max_pages ) {
				$prev_page = $max_pages;
			}

			$prev_link = add_query_arg( 'page', $prev_page, $base );
			$response->link_header( 'prev', $prev_link );
		}
		if ( $max_pages > $page ) {
			$next_page = $page + 1;
			$next_link = add_query_arg( 'page', $next_page, $base );

			$response->link_header( 'next', $next_link );
		}

		return $response;
	}

	/**
	 * Prepares a single item output for response.
	 *
	 * @since 1.0.0
	 *
	 * @param Incassoos_Consumer_Type $item    Item object.
	 * @param WP_REST_Request         $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public function prepare_item_for_response( $item, $request ) {

		$schema = $this->get_item_schema();

		// Base fields for every item.
		$data = array();

		if ( ! empty( $schema['properties']['id'] ) ) {
			$data['id'] = $item->id;
		}

		if ( ! empty( $schema['properties']['name'] ) ) {
			$data['name'] = incassoos_get_consumer_type_title( $item );
		}

		if ( ! empty( $schema['properties']['avatarUrl'] ) ) {
			$size = $request->get_param( 'avatar_size' );
			$data['avatarUrl'] = incassoos_get_consumer_type_avatar_url( $item, $size ? array( 'size' => $size ) : array() );
		}

		if ( ! empty( $schema['properties']['archived'] ) ) {
			$data['archived'] = incassoos_is_consumer_type_archived( $item );
		}

		if ( ! empty( $schema['properties']['_builtin'] ) ) {
			$data['_builtin'] = $item->is_builtin();
		}

		if ( ! empty( $schema['properties']['_applicationFields'] ) ) {
			$data['_applicationFields'] = array_keys( $this->get_additional_fields() );
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
		 * @param WP_REST_Response        $response The response object.
		 * @param Incassoos_Consumer_Type $item     Consumer type object.
		 * @param WP_REST_Request         $request  Request object.
		 */
		return apply_filters( 'incassoos_rest_prepare_consumer_type', $response, $item, $request );
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

		$query_params['context']['default'] = 'view';

		$query_params['order'] = array(
			'description' => __( 'Order sort attribute ascending or descending.' ),
			'type'        => 'string',
			'default'     => 'asc',
			'enum'        => array( 'asc', 'desc' ),
		);

		$query_params['orderby'] = array(
			'description' => __( 'Sort collection by object attribute.' ),
			'type'        => 'string',
			'default'     => 'name',
			'enum'        => array( 'id', 'name' ),
		);

		$query_params['avatar_size'] = array(
			'description' => __( 'Size of the avatar image in the avatar url.' ),
			'type'        => 'integer',
		);

		/**
		 * Filter collection parameters for the consumer types controller.
		 *
		 * This filter registers the collection parameter, but does not map the
		 * collection parameter to an internal query parameter. Use the
		 * `incassoos_rest_consumer_types_query` filter to set query parameters.
		 *
		 * @since 1.0.0
		 *
		 * @param array $query_params JSON Schema-formatted collection parameters.
		 */
		return apply_filters( 'incassoos_rest_consumer_types_collection_params', $query_params );
	}

	/**
	 * Checks if a given request has access to update a consumer type.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has access to update the item, WP_Error object otherwise.
	 */
	public function update_item_permissions_check( $request ) {
		$item = $this->get_consumer_type( $request['id'] );
		if ( is_wp_error( $item ) ) {
			return $item;
		}

		if ( ! current_user_can( 'edit_incassoos_consumer_type', $item->id ) ) {
			return new WP_Error( 'rest_cannot_edit', __( 'Sorry, you are not allowed to edit this item.', 'incassoos' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Updates a single consumer type.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_item( $request ) {
		$item = $this->get_consumer_type( $request['id'] );
		if ( is_wp_error( $item ) ) {
			return $item;
		}

		$schema = $this->get_item_schema();

		if ( ! empty( $schema['properties']['archived'] ) && isset( $request['archived'] ) ) {
			if ( $request['archived'] ) {
				incassoos_archive_consumer_type( $item );
			} else {
				incassoos_unarchive_consumer_type( $item );
			}
		}

		/**
		 * Fires after a single consumer type is created or updated via the REST API.
		 *
		 * @since 1.0.0
		 *
		 * @param Incassoos_Consumer_Type $item     Inserted or updated consumer type object.
		 * @param WP_REST_Request         $request  Request object.
		 * @param bool                    $creating True when creating a post, false when updating.
		 */
		do_action( 'incassoos_rest_insert_consumer_type', $item, $request, false );

		$item = incassoos_get_consumer_type( $item->id );

		$request->set_param( 'context', 'edit' );

		$response = $this->prepare_item_for_response( $item, $request );

		return rest_ensure_response( $response );
	}

	/**
	 * Checks if a given request has access to archive a consumer type
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has access to archive the item, WP_Error object otherwise.
	 */
	public function archive_item_permissions_check( $request ) {
		$item = $this->get_consumer_type( $request['id'] );
		if ( is_wp_error( $item ) ) {
			return $item;
		}

		if ( $item && ! current_user_can( 'archive_incassoos_consumer_type', $item->id ) ) {
			return new WP_Error(
				'incassoos_rest_user_cannot_archive',
				__( 'Sorry, you are not allowed to archive this item.', 'incassoos' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Archive a single consumer type
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function archive_item( $request ) {
		$item = $this->get_consumer_type( $request['id'] );
		if ( is_wp_error( $item ) ) {
			return $item;
		}

		$id = $item->id;

		if ( ! current_user_can( 'archive_incassoos_consumer_type', $id ) ) {
			return new WP_Error(
				'incassoos_rest_user_cannot_archive',
				__( 'Sorry, you are not allowed to archive this item.', 'incassoos' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		$request->set_param( 'context', 'edit' );

		// Only archive if we haven't already.
		if ( incassoos_is_consumer_type_archived( $item ) ) {
			return new WP_Error(
				'incassoos_rest_is_archived',
				__( 'The consumer type is archived.', 'incassoos' ),
				array( 'status' => 410 )
			);
		}

		$result   = incassoos_archive_consumer_type( $item );
		$item     = incassoos_get_consumer_type( $id );
		$response = $this->prepare_item_for_response( $item, $request );

		if ( ! $result ) {
			return new WP_Error(
				'incassoos_rest_cannot_archive',
				__( 'The consumer type cannot be archived.', 'incassoos' ),
				array( 'status' => 500 )
			);
		}

		/**
		 * Fires immediately after a single consumer type is archived via the REST API.
		 *
		 * @since 1.0.0
		 *
		 * @param Incassoos_Consumer_Type $item     The archived consumer type.
		 * @param WP_REST_Response        $response The response data.
		 * @param WP_REST_Request         $request  The request sent to the API.
		 */
		do_action( 'incassoos_rest_archive_consumer_type', $item, $response, $request );

		return $response;
	}

	/**
	 * Checks if a given request has access to unarchive a consumer type
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has access to unarchive the item, WP_Error object otherwise.
	 */
	public function unarchive_item_permissions_check( $request ) {
		$item = $this->get_consumer_type( $request['id'] );
		if ( is_wp_error( $item ) ) {
			return $item;
		}

		if ( $item && ! current_user_can( 'unarchive_incassoos_consumer_type', $item->id ) ) {
			return new WP_Error(
				'incassoos_rest_user_cannot_unarchive',
				__( 'Sorry, you are not allowed to unarchive this item.', 'incassoos' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		return true;
	}

	/**
	 * Unarchive a single consumer type
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function unarchive_item( $request ) {
		$item = $this->get_consumer_type( $request['id'] );
		if ( is_wp_error( $item ) ) {
			return $item;
		}

		$id = $item->id;

		if ( ! current_user_can( 'unarchive_incassoos_consumer_type', $id ) ) {
			return new WP_Error(
				'incassoos_rest_user_cannot_unarchive',
				__( 'Sorry, you are not allowed to unarchive this item.', 'incassoos' ),
				array( 'status' => rest_authorization_required_code() )
			);
		}

		$request->set_param( 'context', 'edit' );

		// Only unarchive if we have already archived.
		if ( incassoos_is_consumer_type_not_archived( $item ) ) {
			return new WP_Error(
				'incassoos_rest_is_not_archived',
				__( 'The consumer type is not archived.', 'incassoos' ),
				array( 'status' => 410 )
			);
		}

		$result   = incassoos_unarchive_consumer_type( $item );
		$item     = incassoos_get_consumer_type( $id );
		$response = $this->prepare_item_for_response( $item, $request );

		if ( ! $result ) {
			return new WP_Error(
				'incassoos_rest_cannot_unarchive',
				__( 'The consumer type cannot be unarchived.', 'incassoos' ),
				array( 'status' => 500 )
			);
		}

		/**
		 * Fires immediately after a single consumer type is unarchived via the REST API.
		 *
		 * @since 1.0.0
		 *
		 * @param Incassoos_Consumer_Type $item     The unarchived consumer type.
		 * @param WP_REST_Response        $response The response data.
		 * @param WP_REST_Request         $request  The request sent to the API.
		 */
		do_action( 'incassoos_rest_unarchive_consumer_type', $item, $response, $request );

		return $response;
	}
}

endif; // class_exists
