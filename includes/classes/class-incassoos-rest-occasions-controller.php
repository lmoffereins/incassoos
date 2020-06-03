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
			'close'        => array(
				'description' => __( 'Whether to close the occasion.', 'incassoos' ),
				'type'        => 'boolean',
				'context'     => array( 'edit' )
			)
		) );

		// Make raw title available in the view context
		$schema['properties']['title']['properties']['raw']['context'][] = 'view';

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

		$object['occasion_date'] = $this->prepare_date_response( incassoos_get_occasion_date( $post, 'Y-m-d H:i:s' ) );
		$object['consumers'] = incassoos_get_occasion_consumers( $post );
		$object['closed'] = $this->prepare_date_response( incassoos_get_occasion_closed_date( $post, 'Y-m-d H:i:s' ) );

		$type = incassoos_get_occasion_type( $post, true );
		$object[ incassoos_get_occasion_type_tax_id() ] = $type ? $type->term_id : 0;

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

		// Close
		if ( isset( $request['close'] ) && $request['close'] ) {

			// Check access
			if ( ! current_user_can( 'close_incassoos_occasion', $object->ID ) ) {
				return new WP_Error(
					'rest_forbidden_context',
					__( 'Sorry, you are not allowed to close this item.', 'incassoos' ),
					array( 'status' => 400 )
				);
			}

			// Close the occasion
			$result = incassoos_close_occasion( $object->ID );

			if ( ! $result ) {
				return new WP_Error(
					'incassoos_rest_occasion_cannot_close',
					__( 'Could not close the occasion.', 'incassoos' ),
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
