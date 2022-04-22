<?php

/**
 * Incassoos CSV Exporter abstract class
 *
 * @package Incassoos
 * @subpackage Export
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Include dependencies
if ( ! class_exists( 'Incassoos_File_Exporter', false ) ) {
	require_once( incassoos()->includes_dir . 'classes/abstract-incassoos-file-exporter.php' );
}

if ( ! class_exists( 'Incassoos_CSV_Exporter' ) ) :
/**
 * The Incassoos CSV Exporter abstraction
 *
 * @since 1.0.0
 */
abstract class Incassoos_CSV_Exporter extends Incassoos_File_Exporter {

	/**
	 * Holds the file type
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $file_type = 'inc_csv_file';

	/**
	 * Holds the file extension
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $file_extension = 'csv';

	/**
	 * Holds the filename
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $filename = 'incassoos-file.csv';

	/**
	 * Holds the file columns
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $file_columns = array();

	/**
	 * Holds the file data
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $file_data = array();

	/**
	 * Holds the file delimiter
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $delimiter = ';';

	/**
	 * Setup this class
	 *
	 * @since 1.0.0
	 */
	public function __construct( $file_columns, $file_data ) {

		// Set file assets
		$this->set_columns( $file_columns );
		$this->set_file_data( $file_data );
	}

	/** Validation ******************************************************/

	/**
	 * Validate file components and register any errors
	 *
	 * @since 1.0.0
	 *
	 * @return bool Is validation successfull?
	 */
	public function validate_file() {
		return ! $this->has_errors();
	}

	/** Export **********************************************************/

	/**
	 * Return the export file contents
	 *
	 * @since 1.0.0
	 *
	 * @return string File contents or False when invalid.
	 */
	public function get_file() {
		$file  = chr( 239 ) . chr( 187 ) . chr( 191 );
		$file .= $this->export_column_headers();
		$file .= $this->export_file_data();

		return $file;
	}

	/**
	 * Return column headers for export
	 *
	 * @since 1.0.0
	 *
	 * @return string CSV column headers
	 */
	protected function export_column_headers() {
		$columns = $this->get_columns();
		$row     = array();
		$buffer  = fopen( 'php://output', 'w' );
		ob_start();

		foreach ( $columns as $column_id => $column_label ) {
			$row[] = $this->format_value( $column_label );
		}

		$this->fputcsv( $buffer, $row );

		return ob_get_clean();
	}

	/**
	 * Return row data for export
	 *
	 * @since 1.0.0
	 *
	 * @return string CSV row data
	 */
	protected function export_file_data() {
		$columns   = $this->get_columns();
		$file_data = $this->get_file_data();
		$buffer    = fopen( 'php://output', 'w' );
		ob_start();

		foreach ( $file_data as $row_data ) {
			$row = array();
			foreach ( $columns as $column_id => $column_label ) {

				if ( isset( $row_data[ $column_id ] ) ) {
					$row[] = $this->format_value( $row_data[ $column_id ] );
				} else {
					$row[] = '';
				}
			}

			$this->fputcsv( $buffer, $row );
		}

		return ob_get_clean();
	}

	/**
	 * Format and escape a value to be used in a CSV context
	 *
	 * @since 1.0.0
	 *
	 * @uses apply_filters() Calls 'incassoos_export-{file_type}-format_value'
	 *
	 * @param string $input CSV value to format
	 * @return string Formatted CSV value
	 */
	public function format_value( $input ) {
		if ( is_array( $input ) ) {
			$formatted = implode( ',', $input );
		} elseif ( ! is_scalar( $input ) ) {
			$formatted = '';
		} elseif ( is_bool( $input ) ) {
			$formatted = $input ? 1 : 0;
		} else {
			$formatted = $input;
		}

		$use_mb = function_exists( 'mb_convert_encoding' );

		if ( $use_mb ) {
			$encoding  = mb_detect_encoding( $formatted, 'UTF-8, ISO-8859-1', true );
			$formatted = 'UTF-8' === $encoding ? $formatted : utf8_encode( $formatted );
		}

		$formatted = apply_filters( "incassoos_export-{$this->file_type}-format_value", $formatted, $input, $this );

		return $this->escape_value( $formatted );
	}

	/**
	 * Escape a string to be used in a CSV context
	 *
	 * Malicious input can inject formulas into CSV files, opening up the possibility
	 * for phishing attacks and disclosure of sensitive information.
	 *
	 * Additionally, Excel exposes the ability to launch arbitrary commands through
	 * the DDE protocol.
	 *
	 * @see https://www.contextis.com/resources/blog/comma-separated-vulnerabilities/
	 * @see https://hackerone.com/reports/72785
	 *
	 * @since 1.0.0
	 *
	 * @param string $input CSV value to escape
	 * @return string Escaped CSV value
	 */
	public function escape_value( $input ) {
		$active_content_triggers = array( '=', '+', '-', '@' );

		if ( in_array( mb_substr( $input, 0, 1 ), $active_content_triggers, true ) ) {
			$input = "'" . $input;
		}

		return $input;
	}

	/**
	 * Write to the CSV file
	 * 
	 * Ensures escaping works across versions of PHP. PHP 5.5.4 uses '\' as
	 * the default escape character. This is not RFC-4180 compliant. \0 disables
	 * the escape character.
	 *
	 * @see https://bugs.php.net/bug.php?id=43225
	 * @see https://bugs.php.net/bug.php?id=50686
	 * @see https://github.com/woocommerce/woocommerce/issues/19514
	 * @see https://github.com/woocommerce/woocommerce/issues/24579
	 *
	 * @since 1.0.0
	 *
	 * @param resource $buffer Resource to write to
	 * @param array    $row    Row with values to write
	 */
	protected function fputcsv( $buffer, $row ) {
		if ( version_compare( PHP_VERSION, '5.5.4', '<' ) ) {
			ob_start();

			$temp = fopen( 'php://output', 'w' );
    		fputcsv( $temp, $row, $this->get_delimiter(), '"' );
			fclose( $temp );

			$row = ob_get_clean();
			$row = str_replace( '\\"', '\\""', $row );

			fwrite( $buffer, $row );
		} else {
			fputcsv( $buffer, $row, $this->get_delimiter(), '"', "\0" );
		}
	}

	/** Structure *******************************************************/

	/**
	 * Return the file columns
	 *
	 * @since 1.0.0
	 *
	 * @uses apply_filters() Calls 'incassoos_export-{file_type}-get_columns'
	 *
	 * @return array Columns as key => label
	 */
	public function get_columns() {
		return apply_filters( "incassoos_export-{$this->file_type}-get_columns", $this->file_columns, $this );
	}

	/**
	 * Set the file columns property
	 *
	 * @since 1.0.0
	 *
	 * @param array $columns Columns as key => label
	 */
	public function set_columns( $columns = array() ) {
		$this->file_columns = array();

		foreach ( $columns as $column_id => $column_label ) {
			$this->file_columns[ $column_id ] = sanitize_text_field( $column_label );
		}
	}

	/**
	 * Return file data
	 *
	 * @since 1.0.0
	 *
	 * @uses apply_filters() Calls 'incassoos_export-{file_type}-get_file_data'
	 *
	 * @return array File data
	 */
	public function get_file_data() {
		return apply_filters( "incassoos_export-{$this->file_type}-get_file_data", $this->file_data, $this );
	}

	/**
	 * Set the file data property
	 *
	 * @since 1.0.0
	 *
	 * @param array $data File data
	 */
	public function set_file_data( $data = array() ) {
		$this->file_data = array();

		foreach ( $data as $row ) {
			$this->file_data[] = $row;
		}
	}

	/**
	 * Return the file delimiter
	 *
	 * @since 1.0.0
	 *
	 * @uses apply_filters() Calls 'incassoos_export-{file_type}-get_delimiter'
	 *
	 * @return string Delimiter
	 */
	public function get_delimiter() {
		return apply_filters( "incassoos_export-{$this->file_type}-get_delimiter", $this->delimiter, $this );
	}
}

endif; // class_exists
