<?php

/**
 * Incassoos File Exporter abstract class
 *
 * @package Incassoos
 * @subpackage Export
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Incassoos_File_Exporter' ) ) :
/**
 * The Incassoos File Exporter abstraction
 *
 * @since 1.0.0
 */
abstract class Incassoos_File_Exporter {

	/**
	 * Holds the file type
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $file_type = 'inc_file';

	/**
	 * Holds the file extension
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $file_extension = 'txt';

	/**
	 * Holds the filename
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $filename = 'incassoos-file.txt';

	/**
	 * Holds the list of file errors
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $errors = array();

	/** Validation ******************************************************/

	/**
	 * Validate file components and register any errors
	 *
	 * @since 1.0.0
	 *
	 * @return bool Is file validated?
	 */
	abstract public function validate_file();

	/**
	 * Return whether the file has any errors
	 *
	 * @since 1.0.0
	 *
	 * @return bool Whether there are any errors
	 */
	public function has_errors() {
		return ! empty( $this->errors );
	}

	/**
	 * Return the file's errors
	 *
	 * @since 1.0.0
	 *
	 * @return array List of error messages
	 */
	public function get_errors() {
		return $this->errors;
	}

	/** Export **********************************************************/

	/**
	 * Send headers for the file export
	 *
	 * @since 1.0.0
	 */
	public function send_headers() {

		// Disable GZIP
		@ini_set( 'zlib.output_compression', 'Off' );
		@ini_set( 'output_buffering', 'Off' );
		@ini_set( 'output_handler', '' );
		if ( function_exists( 'apache_setenv' ) ) {
			@apache_setenv( 'no-gzip', 1 );
		}

		// Remove export limits
		ignore_user_abort( true );
		incassoos_set_time_limit( 0 );

		// Disable cache
		nocache_headers();
		header( 'Robots: none' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );
	}

	/**
	 * Return the export file contents
	 *
	 * @since 1.0.0
	 *
	 * @return string File contents or False when invalid.
	 */
	abstract public function get_file();

	/**
	 * Set the filename property
	 *
	 * @since 1.0.0
	 *
	 * @param string Export filename
	 */
	public function set_filename( $filename ) {
		$extension = '.' . str_replace( '.', '', $this->file_extension );
		$this->filename = sanitize_file_name( str_replace( $extension, '', $filename ) . $extension );
	}

	/**
	 * Return the filename
	 *
	 * @since 1.0.0
	 *
	 * @uses apply_filters() Calls 'incassoos_export-{file_type}-get_filename'
	 *
	 * @return string Export filename
	 */
	public function get_filename() {
		return sanitize_file_name( apply_filters( "incassoos_export-{$this->file_type}-get_filename", $this->filename, $this ) );
	}
}

endif; // class_exists
