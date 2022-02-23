<?php

/**
 * Incassoos REST Consumers Controller Class
 *
 * @package Incassoos
 * @subpackage REST_API
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Incassoos_REST_Consumers_Controller' ) ) :
/**
 * The Incassoos REST Consumers Controller class
 *
 * @see WP_REST_Controller
 *
 * @since 1.0.0
 */
class Incassoos_REST_Consumers_Controller extends WP_REST_Controller {

	/**
	 * Setup this class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->namespace = incassoos_get_rest_namespace();
		$this->rest_base = incassoos_get_consumers_rest_base();
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
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true
				),
				'name'            => array(
					'description' => __( 'Display name of the object.', 'incassoos' ),
					'type'        => 'string',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true
				),
				'avatarUrl'          => array(
					'description' => __( "Path to the object's avatar image", 'incassoos' ),
					'type'        => 'string',
					'format'      => 'uri',
					'context'     => array( 'view', 'edit' ),
					'readonly'    => true
				),
				'show'            => array(
					'description' => __( 'Visibility status for the object.', 'incassoos' ),
					'type'        => 'boolean',
					'context'     => array( 'view', 'edit' ),
				),
				'consumptionLimit' => array(
					'description' => __( 'Consumption limit for the object.', 'incassoos' ),
					'type'        => 'integer',
					'context'     => array( 'view', 'edit' )
				),
				'customSort'      => array(
					'description' => __( 'Custom sort value for ordering the object.', 'incassoos' ),
					'type'        => 'integer',
					'context'     => array( 'view' ),
					'readonly'    => true
				),
				'group'           => array(
					'description' => __( 'List group for the object.', 'incassoos' ),
					'type'        => 'object',
					'context'     => array( 'view' ),
					'readonly'    => true,
					'properties'  => array(
						'id'   => array(
							'description' => __( 'Unique identifier for the list group.', 'incassoos' ),
							'type'        => 'integer',
							'context'     => array( 'view' ),
							'readonly'    => true
						),
						'name' => array(
							'description' => __( 'Name of the list group.', 'incassoos' ),
							'type'        => 'string',
							'context'     => array( 'view' ),
							'readonly'    => true
						),
						'order' => array(
							'description' => __( 'Sort value for ordering the list group.', 'incassoos' ),
							'type'        => 'integer',
							'context'     => array( 'view' ),
							'readonly'    => true
						),
					)
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
		if ( ! current_user_can( 'access_incassoos_rest_consumers' ) ) {
			return new WP_Error( 'rest_forbidden_context', esc_html__( 'Sorry, you are not allowed to view these items.', 'incassoos' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Get the user, if the ID is valid.
	 *
	 * @since 1.0.0
	 *
	 * @param int $id Supplied ID.
	 * @return WP_User|WP_Error User object if ID is valid, WP_Error otherwise.
	 */
	protected function get_user( $id ) {
		$error = new WP_Error( 'rest_user_invalid_id', __( 'Invalid user ID.' ), array( 'status' => 404 ) );
		if ( (int) $id <= 0 ) {
			return $error;
		}

		$user = incassoos_get_user( (int) $id );
		if ( empty( $user ) ) {
			return $error;
		}

		return $user;
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
		 * This array defines mappings between public API query parameters whose
		 * values are accepted as-passed, and their internal WP_User_Query parameter
		 * name equivalents (some are the same). Only values which are also
		 * present in $registered will be set.
		 */
		$parameter_mappings = array(
			// 'exclude'        => 'exclude',
			// 'include'        => 'include',
			// 'offset'         => 'offset',
			'order'          => 'order',
			'orderby'        => 'orderby',
			'page'           => 'paged',
			// 'search'         => 'search',
		);

		/*
		 * For each known parameter which is both registered and present in the request,
		 * set the parameter's value on the query $args.
		 */
		foreach ( $parameter_mappings as $api_param => $wp_param ) {
			if ( isset( $registered[ $api_param ], $request[ $api_param ] ) ) {
				$args[ $wp_param ] = $request[ $api_param ];
			}
		}

		// Ensure our per_page parameter overrides any provided number filter.
		if ( isset( $registered['per_page'] ) ) {
			$args['number'] = $request['per_page'];
		}

		// Query all userdata
		$args['fields'] = 'all';

		// Filter the query arguments for a request
		$query_args = apply_filters( 'incassoos_rest_consumers_query', $args, $request );

		$items_query  = incassoos_get_user_query( $query_args );
		$query_result = $items_query->get_results();

		$items = array();

		foreach ( $query_result as $item ) {
			$data    = $this->prepare_item_for_response( $item, $request );
			$items[] = $this->prepare_response_for_collection( $data );
		}

		$page = (int) $query_args['paged'];
		$total_items = $items_query->total_users;

		if ( $total_items < 1 ) {
			// Out-of-bounds, run the query again without LIMIT for total count.
			unset( $query_args['paged'] );

			$count_query = incassoos_get_user_query( $query_args );
			$count_query->get_results();
			$total_items = $count_query->total_users;
		}

		$max_pages = ceil( $total_items / (int) $items_query->query_vars['number'] );

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
	 * @param WP_User         $item    Item object.
	 * @param WP_REST_Request $request Request object.
	 * @return WP_REST_Response Response object.
	 */
	public function prepare_item_for_response( $item, $request ) {

		$schema = $this->get_item_schema();

		// Base fields for every item.
		$data = array();

		if ( ! empty( $schema['properties']['id'] ) ) {
			$data['id'] = $item->ID;
		}

		if ( ! empty( $schema['properties']['name'] ) ) {
			$data['name'] = incassoos_get_user_display_name( $item );
		}

		if ( ! empty( $schema['properties']['avatar'] ) ) {
			$size = $request->get_param( 'avatar_size' );
			$data['avatarUrl'] = get_avatar_url( $item->ID, $size ? array( 'size' => $size ) : array() );
		}

		if ( ! empty( $schema['properties']['show'] ) ) {
			$data['show'] = ! $item->get( '_incassoos_hide_in_list' );
		}

		if ( ! empty( $schema['properties']['consumptionLimit'] ) ) {
			$data['consumptionLimit'] = incassoos_get_user_consumption_limit( $item );
		}

		// Default the custom sort value to 0
		if ( ! empty( $schema['properties']['customSort'] ) ) {
			$data['customSort'] = 0;
		}

		if ( ! empty( $schema['properties']['group'] ) ) {
			$data['group'] = incassoos_get_user_list_group( $item );
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
		 * @param WP_User          $item     User object.
		 * @param WP_REST_Request  $request  Request object.
		 */
		return apply_filters( 'incassoos_rest_prepare_consumer', $response, $item, $request );
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
			'default'     => 'login',
			'enum'        => array( 'ID', 'login', 'include', 'name', 'nicename', 'email', 'registered' ),
		);

		$query_params['avatar_size'] = array(
			'description' => __( 'Size of the avatar image in the avatar url.' ),
			'type'        => 'integer',
		);

		/**
		 * Filter collection parameters for the posts controller.
		 *
		 * This filter registers the collection parameter, but does not map the
		 * collection parameter to an internal WP_User_Query parameter. Use the
		 * `incassoos_rest_consumers_query` filter to set WP_User_Query parameters.
		 *
		 * @since 1.0.0
		 *
		 * @param array $query_params JSON Schema-formatted collection parameters.
		 */
		return apply_filters( "incassoos_rest_consumers_collection_params", $query_params );
	}

	/**
	 * Checks if a given request has access to update a user.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return true|WP_Error True if the request has access to update the item, WP_Error object otherwise.
	 */
	public function update_item_permissions_check( $request ) {
		$item = $this->get_user( $request['id'] );
		if ( is_wp_error( $item ) ) {
			return $item;
		}

		if ( ! current_user_can( 'edit_incassoos_consumer', $item->ID ) ) {
			return new WP_Error( 'rest_cannot_edit', __( 'Sorry, you are not allowed to edit this item.', 'incassoos' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Updates a single user.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function update_item( $request ) {
		$item = $this->get_user( $request['id'] );
		if ( is_wp_error( $item ) ) {
			return $item;
		}

		$schema = $this->get_item_schema();

		if ( ! empty( $schema['properties']['show'] ) && isset( $request['show'] ) ) {
			if ( $request['show'] ) {
				delete_user_meta( $item->ID, '_incassoos_hide_in_list' );
			} else {
				update_user_meta( $item->ID, '_incassoos_hide_in_list', '1' );
			}
		}

		if ( ! empty( $schema['properties']['consumptionLimit'] ) && isset( $request['consumptionLimit'] ) ) {
			if ( ! (float) $request['consumptionLimit'] ) {
				delete_user_meta( $item->ID, '_incassoos_consumption_limit' );
			} else {
				update_user_meta( $item->ID, '_incassoos_consumption_limit', (float) $request['consumptionLimit'] );
			}
		}

		/**
		 * Fires after a single user is created or updated via the REST API.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_User         $item     Inserted or updated user object.
		 * @param WP_REST_Request $request  Request object.
		 * @param bool            $creating True when creating a post, false when updating.
		 */
		do_action( 'incassoos_rest_insert_user', $item, $request, false );

		$item = incassoos_get_user( $item->ID );

		$request->set_param( 'context', 'edit' );

		$response = $this->prepare_item_for_response( $item, $request );

		return rest_ensure_response( $response );
	}
}

endif; // class_exists
