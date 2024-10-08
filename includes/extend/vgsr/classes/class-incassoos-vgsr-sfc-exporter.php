<?php

/**
 * Incassoos VGSR SFC Exporter class
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

if ( ! class_exists( 'Incassoos_VGSR_SFC_Exporter' ) ) :
/**
 * The Incassoos VGSR SFC Exporter class
 *
 * The .sfc file structure was originally developed for the FiscaatAutomaat
 * accounting software system. The file contains the accounting rules for import
 * into FiscaatAutomaat and its setup follows a simple line based text format.
 *
 * @since 1.0.0
 */
class Incassoos_VGSR_SFC_Exporter extends Incassoos_File_Exporter {

	/**
	 * The collectable Collection post
	 *
	 * @since 1.0.0
	 * @var WP_Post
	 */
	private $post;

	/**
	 * The collection date
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $collection_date;

	/**
	 * Setup this class
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post|int $post Optional. Post object or ID. Defaults to the current post.
	 */
	public function __construct( $post = 0 ) {

		// Set file type and extension
		$this->file_type = incassoos_vgsr_get_sfc_export_type_id();
		$this->file_extension = 'sfc';

		// Require the Collection to be collected
		$this->post = $post = incassoos_get_collection( $post, array( 'is_collected' => true ) );

		// Bail when invalid
		if ( ! $this->validate_file() ) {
			return;
		}

		// Set collection date
		$this->collection_date = incassoos_get_collection_date( $post, 'Y-m-d' );

		// Set file name
		$this->set_filename(
			sprintf( '%s-SFC-%s-%s.sfc',
				incassoos_get_organization_name(),
				incassoos_get_collection_title( $post ),
				incassoos_get_collection_date( $post, 'Ymd' )
			)
		);
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

		// Reset errors list
		$this->errors = new WP_Error();

		if ( ! $this->post ) {
			$this->add_error( 'incassoos_invalid_post', esc_html__( 'The file is invalid because the Collection is not collected.', 'incassoos' ) );
		}

		return ! $this->has_errors();
	}

	/** Export **********************************************************/

	/**
	 * Create and return the Collection's VGSR SFC file
	 *
	 * @since 1.0.0
	 *
	 * @uses apply_filters() Calls 'incassoos_export-{file_type}-get_file'
	 *
	 * @return string|false VGSR SFC file or False when invalid.
	 */
	public function get_file() {
		$file_type = $this->file_type;

		// Bail when the file is invalid
		if ( $this->has_errors() )
			return false;

		$file = '';

		// Parse activity lines
		if ( $alines = $this->get_activity_lines() ) {
			$file .= $this->parse_lines( $alines );
		}

		// Parse occasion lines
		if ( $olines = $this->get_occasion_lines() ) {
			$file .= $this->parse_lines( $olines );
		}

		return apply_filters( "incassoos_export-{$file_type}-get_file", $file, $this );
	}

	/** Structure *******************************************************/

	/**
	 * Get the Collection's Activity line data
	 * 
	 * @since 1.0.0
	 *
	 * @uses apply_filters() Calls 'incassoos_vgsr_sfc_activity_lines'
	 *
	 * @return array Collection Activity line data
	 */
	public function get_activity_lines() {
		$post           = $this->post;
		$activities     = incassoos_get_collection_activities( $post );
		$date           = incassoos_get_collection_date( $post, 'j-n-Y' );
		$consumer_types = incassoos_get_consumer_types();
		$retval         = array();

		// Define totals by type
		$titles         = array();
		$totals         = array_flip( $activities ) + array_combine( $consumer_types, array_fill( 0, count( $consumer_types ), 0 ) );

		// Provide default activity date
		add_filter( 'incassoos_get_activity_date', 'incassoos_filter_activity_date_default_to_date_created', 10, 3 );

		// Walk Activities
		foreach ( $activities as $post_id ) {
			$titles[ $post_id ] = incassoos_get_activity_title( $post_id );
			$totals[ $post_id ] = incassoos_get_activity_total( $post_id, null );

			// Append activity date
			if ( $_date = incassoos_get_activity_date( $post_id, 'j-n-Y' ) ) {
				$titles[ $post_id ] .= " ($_date)";
			}

			// Distinguish totals per consumer type
			foreach ( $consumer_types as $type_id ) {
				$type_price = incassoos_get_activity_participant_price( $type_id, $post_id, null );

				// Add type total, subtract from activity
				if ( ! empty( $type_price ) ) {
					$totals[ $type_id ] += $type_price;
					$totals[ $post_id ] -= $type_price;
				}
			}
		}

		// Remove filter
		remove_filter( 'incassoos_get_activity_date', 'incassoos_filter_activity_date_default_to_date_created', 10, 3 );

		// Walk defined totals
		foreach ( $totals as $item_id => $total ) {

			// Skip lines with 0
			if ( ! $total ) {
				continue;
			}

			$title = isset( $titles[ $item_id ] )
				? $titles[ $item_id ]
				/* translators: 1: Activity title 2: Date */
				: sprintf( __( 'Activity revenue for %1$s per %2$s', 'incassoos' ), incassoos_get_consumer_type_title( $item_id ), "($date)" );

			// Setup line data
			$retval[] = array(
				'item_id' => $item_id,
				'title'   => $title,
				'debit'   => $total > 0 ? $total : 0,
				'credit'  => $total > 0 ? 0 : abs( $total ),
			);
		}

		// Append counter line
		$counter_total = array_sum( $totals );
		$retval[] = array(
			'item_id'   => 'activities-counter',
			'ledger_id' => 10, // Nog te ontvangen incasso's
			/* translators: %s: Date */
			'title'     => sprintf( __( 'All activities per %s', 'incassoos' ), incassoos_get_collection_date( $post, 'j-n-Y' ) ),
			'debit'     => $counter_total < 0 ? abs( $counter_total ) : 0,
			'credit'    => $counter_total < 0 ? 0 : $counter_total,
		);

		return apply_filters( 'incassoos_vgsr_sfc_activity_lines', $retval, $totals );
	}

	/**
	 * Get the Collection's Occasion line data
	 *
	 * NOTE: debit/credit amounts are reversed for consumptions
	 * 
	 * @since 1.0.0
	 *
	 * @uses apply_filters() Calls 'incassoos_vgsr_sfc_occasion_lines'
	 *
	 * @return array Collection Occasion line data
	 */
	public function get_occasion_lines() {
		$post           = $this->post;
		$occasions      = incassoos_get_collection_occasions( $post );
		$date           = incassoos_get_collection_date( $post, 'j-n-Y' );
		$consumer_types = incassoos_get_consumer_types();
		$retval         = array();

		// Define totals by type
		$totals         = array( 'all' => 0 ) + array_combine( $consumer_types, array_fill( 0, count( $consumer_types ), 0 ) );

		// Walk Occasions
		foreach ( $occasions as $post_id ) {
			$totals['all'] += incassoos_get_occasion_total( $post_id, null );

			// Distinguish totals per consumer type
			foreach ( $consumer_types as $type_id ) {
				$total = incassoos_get_occasion_consumer_total( $type_id, $post_id, null );

				// Add type total, subtract from all
				if ( ! empty( $total ) ) {
					$totals[ $type_id ] += $total;
					$totals['all']      -= $total;
				}
			}
		}

		// Walk defined totals
		foreach ( $totals as $item_id => $total ) {

			// Skip lines with 0
			if ( ! $total ) {
				continue;
			}

			if ( 'all' === $item_id ) {
				$ledger_id = 10; // Nog te ontvangen incasso's
				/* translators: %s: Date */
				$title = sprintf( __( 'Order revenue collected per %s', 'incassoos' ), "($date)" );
			} else {
				$ledger_id = -1;
				/* translators: 1: Occasion title 2: Date */
				$title = sprintf( __( 'Order revenue for %1$s per %2$s', 'incassoos' ), incassoos_get_consumer_type_title( $item_id ), "($date)" );
			}

			// Setup line data
			$retval[] = array(
				'item_id'   => $item_id,
				'ledger_id' => $ledger_id,
				'title'     => $title,
				'debit'     => $total > 0 ? 0 : abs( $total ),
				'credit'    => $total > 0 ? $total : 0,
			);
		}

		// Append counter line
		$counter_total = array_sum( $totals );
		$retval[] = array(
			'item_id'   => 'occasions-counter',
			'ledger_id' => 6, // Resultaat sociëteitscommissie
			/* translators: %s: Date */
			'title'     => sprintf( __( 'Order revenue per %s', 'incassoos' ), $date ),
			'debit'     => $counter_total < 0 ? 0 : $counter_total,
			'credit'    => $counter_total < 0 ? abs( $counter_total ) : 0,
		);

		return apply_filters( 'incassoos_vgsr_sfc_occasion_lines', $retval, $totals );
	}

	/**
	 * Parse and return individual lines
	 *
	 * @since 1.0.0
	 *
	 * @param  array $lines Lines with raw data
	 * @return string Parsed lines
	 */
	public function parse_lines( $lines ) {
		$retval = '';

		foreach ( $lines as $line ) {

			// Sanitize line
			$line = wp_parse_args( $line, array( 'ledger_id' => -1 ) );
			$line = array_map( 'esc_html', $line );

			// Build line
			$retval .= sprintf('#%s#,%d,"%s",%s,%s' . "\r",
				$this->collection_date,
				$line['ledger_id'],
				$line['title'],
				$line['credit'],
				$line['debit']
			);
		}

		return $retval;
	}
}

endif; // class_exists
