<?php

/**
 * Incassoos Consumers CSV Exporter class
 *
 * @package Incassoos
 * @subpackage Export
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Include dependencies
if ( ! class_exists( 'Incassoos_CSV_Exporter', false ) ) {
	require_once( incassoos()->includes_dir . 'classes/abstract-incassoos-csv-exporter.php' );
}

if ( ! class_exists( 'Incassoos_Consumers_CSV_Exporter' ) ) :
/**
 * The Incassoos Consumers CSV Exporter class
 *
 * @since 1.0.0
 */
class Incassoos_Consumers_CSV_Exporter extends Incassoos_CSV_Exporter {

	/**
	 * Setup this class
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Set file type
		$this->file_type = incassoos_get_consumers_export_type_id();

		// Require the decryption key
		if ( incassoos_is_encryption_enabled() && ! incassoos_get_decryption_key() ) {
			$this->add_error( 'incassoos_missing_decryption_key', esc_html__( 'The required decryption key was not provided.', 'incassoos' ) );
			return;
		}

		$columns   = $this->_get_columns();
		$file_data = $this->_get_file_data();

		// Setup base class
		parent::__construct( $columns, $file_data );

		// Set file name
		$this->set_filename(
			sprintf( '%s-%s-%s.csv',
				esc_html__( 'Incassoos', 'incassoos' ),
				esc_html__( 'Consumers', 'incassoos' ),
				date( 'Ymd' )
			)
		);
	}

	/** Structure *******************************************************/

	/**
	 * Return the file columns
	 *
	 * @since 1.0.0
	 *
	 * @return array File columns
	 */
	public function _get_columns() {

		// Start columns
		$columns = array(
			'group_id'   => esc_html__( 'Group ID', 'incassoos' ),
			'group_name' => esc_html__( 'Group',    'incassoos' ),
			'user_id'    => esc_html__( 'User ID',  'incassoos' ),
			'user_name'  => esc_html__( 'User',     'incassoos' )
		);

		// Add consumer fields
		foreach ( incassoos_admin_get_consumers_fields() as $field_id => $args ) {
			$columns[ $field_id ] = $args['label'];
		}

		return $columns;
	}

	/**
	 * Return the file data
	 *
	 * @since 1.0.0
	 *
	 * @uses apply_filters() Calls 'incassoos_export-{file_type}-get_data_row'
	 *
	 * @return array File data
	 */
	public function _get_file_data() {
		$fields = incassoos_admin_get_consumers_fields();
		$rows   = array();

		// Consumer groups
		foreach ( incassoos_get_grouped_users() as $group ) {

			// Consumer group users
			foreach ( $group->users as $user ) {
				$row = array(
					'group_id'      => $group->id,
					'group_name'    => $group->name,
					'user_id'       => $user->ID,
					'user_name'     => $user->display_name
				);

				// Add consumer fields
				foreach ( $fields as $field_id => $args ) {
					$row[ $field_id ] = call_user_func( $args['display_callback'], $user, $field_id );
				}

				$rows[] = apply_filters( "incassoos_export-{$this->file_type}-get_data_row", $row, $user, $group, $this );
			}
		}

		return $rows;
	}
}

endif; // class_exists
