<?php

/**
 * Taxonomy API: Incassoos_Consumer_Type class
 *
 * @package Incassoos
 * @subpackage Core
 */

/**
 * Class used to implement the Incassoos_Consumer_Type object
 *
 * @since 1.0.0
 *
 * @property-read object $data Sanitized term data.
 */
final class Incassoos_Consumer_Type {

	/**
	 * Consumer type identifier.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $id;

	/**
	 * The consumer type's label.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $label = '';

	/**
	 * The consumer type's description.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $description = '';

	/**
	 * The consumer type's avatar url.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $avatar_url = '';

	/**
	 * The consumer type's avatar url callback.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	public $avatar_url_callback = '';

	/**
	 * The consumer type's term ID.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	public $term_id = 0;

	/**
	 * Whether the consumer type is internal. Internal types are removed from queries.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $_internal = false;

	/**
	 * Retrieve Incassoos_Consumer_Type instance.
	 *
	 * @since 1.0.0
	 *
	 * @param  string|WP_Term $type Consumer type ID or label or term object.
	 * @return Incassoos_Consumer_Type|false Type object, if found. False if the type was not found.
	 */
	public static function get_instance( $type_id ) {
		$plugin = incassoos();
		$_type  = false;

		if ( ! isset( $plugin->consumer_types ) ) {
			$plugin->consumer_types = array();
		}

		// Special case: Unknown user type
		if ( incassoos_is_unknown_consumer_type_id( $type_id ) ) {
			$unknown_user_id = incassoos_get_user_id_from_unknown_consumer_type( $type_id );
			$type_id         = incassoos_get_unknown_consumer_type_id_base();
		} else {
			$unknown_user_id = false;
		}

		// Get type from id or label
		if ( is_string( $type_id ) ) {

			// Get type by id
			if ( isset( $plugin->consumer_types[ $type_id ] ) ) {
				$_type = $plugin->consumer_types[ $type_id ];

			// Get type by label
			} elseif ( $type_id_by_label = array_search( $type_id, wp_list_pluck( $plugin->consumer_types, 'label' ) ) ) {
				$_type = $plugin->consumer_types[ $type_id_by_label ];

			// Get type by term slug
			} else {
				$_type = get_term_by( 'slug', $type_id, incassoos_get_consumer_type_tax_id() );
			}

		// Get type by term
		} elseif ( is_a( $type_id, 'WP_Term' ) ) {
			$_type = $type_id;
		}

		// Convert from term object
		if ( is_a( $_type, 'WP_Term' ) ) {
			$_type->id    = $_type->slug;
			$_type->label = $_type->name;
		}

		// When handling Unknown user type
		if ( $unknown_user_id ) {
			$_type->id              = incassoos_get_unknown_consumer_type_id( $unknown_user_id );
			$_type->label           = sprintf( $_type->label_user, $unknown_user_id );
			$_type->unknown_user_id = $unknown_user_id;
		}

		// Type is not found
		if ( ! $_type ) {
			return false;
		}

		return new Incassoos_Consumer_Type( $_type );
	}

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Incassoos_Consumer_Type|object|array $type Type object.
	 */
	public function __construct( $type ) {
		if ( is_object( $type ) ) {
			$_type = get_object_vars( $type );
		} else {
			$_type = $type;
		}

		foreach ( $_type as $key => $value ) {
			$this->$key = $value;
		}
	}

	/**
	 * Converts an object to array.
	 *
	 * @since 1.0.0
	 *
	 * @return array Object as array.
	 */
	public function to_array() {
		return get_object_vars( $this );
	}

	/**
	 * Getter.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key Property to get.
	 * @return mixed Property value.
	 */
	public function __get( $key ) {
		switch ( $key ) {
			case 'label_count':
				/* translators: 1: Consumer type label, 2: Count. */
				return sprintf( _x( '%1$s %2$s', 'Consumer type count', 'incassoos' ),
					$this->label,
					'<span class="count">(%s)</span>'
				);
		}
	}

	/**
	 * Return whether the type is internal.
	 *
	 * @since 1.0.0
	 *
	 * @return bool Type is internal.
	 */
	public function is_internal() {
		return (bool) $this->_internal;
	}

	/**
	 * Return whether the type is a term.
	 *
	 * @since 1.0.0
	 *
	 * @return bool Type is a term.
	 */
	public function is_term() {
		return (bool) $this->term_id;
	}

	/**
	 * Return whether the type is found for the search term
	 *
	 * @since 1.0.0
	 *
	 * @param  string $search_term Search term
	 * @return bool Type is found
	 */
	public function find( $search_term ) {

		// Find by id
		if ( false !== stripos( $this->id, $search_term ) ) {
			return true;

		// Find by label
		} elseif ( false !== stripos( $this->label, $search_term ) ) {
			return true;

		// Find by description
		} elseif ( false !== stripos( $this->description, $search_term ) ) {
			return true;
		}

		return false;
	}
}
