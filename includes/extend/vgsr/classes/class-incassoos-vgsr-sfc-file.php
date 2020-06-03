<?php

/**
 * Incassoos VGSR SFC File class
 *
 * @package Incassoos
 * @subpackage Export
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Incassoos_VGSR_SFC_File' ) ) :
/**
 * The Incassoos VGSR SFC File class
 *
 * The .sfc file structure was originally developed for the FiscaatAutomaat
 * accounting software system. The file contains the accouting rules to import
 * into the system and is designed with a simple line based text format.
 *
 * @since 1.0.0
 */
class Incassoos_VGSR_SFC_File {

	/**
	 * The collectable Collection post
	 *
	 * @since 1.0.0
	 * @var WP_Post
	 */
	private $post;

	/**
	 * Setup this class
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post|int $post Optional. Post object or ID. Defaults to the current post.
	 */
	public function __construct( $post = 0 ) {

		// Require the Collection to be collected
		$this->post = incassoos_get_collection( $post, true );
	}

	/**
	 * Create and return the Collection's VGSR SFC file
	 *
	 * @since 1.0.0
	 *
	 * @return string|false VGSR SFC file or False when invalid.
	 */
	public function get_file() {

		// Bail when the Collection is invalid
		if ( ! $this->post )
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

		return $file;
	}

	/**
	 * Return the Collection's SFC filename
	 *
	 * @since 1.0.0
	 *
	 * @return string Filename
	 */
	public function get_filename() {
		if ( $this->post ) {
			return sprintf( '%s-SFC-%s.sfc', incassoos_get_organization_name(), incassoos_get_collection_date( $this->post, 'Y-m-d' ) );
		}
	}

	/**
	 * Get the Collection's Activity line data
	 * 
	 * @since 1.0.0
	 *
	 * @return array Collection Activity line data
	 */
	public function get_activity_lines() {
		$activities = incassoos_get_collection_activities( $this->post );
		$totals     = 0;
		$retval     = array();

		// Walk Activities
		foreach ( $activities as $item_id ) {
			$title   = incassoos_get_activity_title( $item_id );
			$total   = incassoos_get_activity_total( $item_id );
			$totals += $total;

			// Append activity date
			if ( $date  = incassoos_get_activity_date( $item_id, 'Y-m-d' ) ) {
				$title .= " ($date)";
			}

			// Setup line data
			$retval[] = array(
				'title'  => $title,
				'debit'  => $total > 0 ? $total : 0,
				'credit' => $total > 0 ? 0 : abs( $total ),
			);
		}

		// Append counter line
		$retval[] = array(
			'title'  => sprintf( __( 'All activities per %s', 'incassoos' ), incassoos_get_collection_date( $this->post, 'Y-m-d' ) ),
			'debit'  => $totals < 0 ? abs( $totals ) : 0,
			'credit' => $totals < 0 ? 0 : $totals,
		);

		return $retval;
	}

	/**
	 * Get the Collection's Occasion line data
	 * 
	 * @since 1.0.0
	 *
	 * @return array Collection Occasion line data
	 */
	public function get_occasion_lines() {
		$occasions      = incassoos_get_collection_occasions( $this->post );
		$date           = incassoos_get_collection_date( $this->post, 'Y-m-d' );
		$consumer_types = incassoos_get_consumer_types();
		$retval         = array();

		// Define totals by type
		$totals         = array( 'all' => 0 ) + array_combine( $consumer_types, array_fill( 0, count( $consumer_types ), 0 ) );

		// Walk Occasions
		foreach ( $occasions as $item_id ) {
			$totals['all'] = incassoos_get_occasion_total( $item_id );

			// Distinguish totals per consumer type
			foreach ( $consumer_types as $type ) {

				// Add type total, subtract from all
				if ( $total = incassoos_get_occasion_consumer_total( $type, $item_id ) ) {
					$totals[ $type ] += $total;
					$totals['all']   -= $total;
				}
			}
		}

		// Walk defined totals
		foreach ( $totals as $type => $total ) {
			$title = ( 'all' === $type )
				? sprintf( __( 'Order revenue collected per %s', 'incassoos' ), "($date)" )
				: sprintf( __( 'Order revenue for %1$s per %2$s', 'incassoos' ), incassoos_get_consumer_type_title( $type ), "($date)" );

			// Setup line data
			$retval[] = array(
				'title'  => $title,
				'debit'  => $total > 0 ? $total : 0,
				'credit' => $total > 0 ? 0 : abs( $total ),
			);
		}

		// Append counter line
		$counter_total = array_sum( $totals );
		$retval[] = array(
			'title'  => sprintf( __( 'Order revenue per %s', 'incassoos' ), $date ),
			'debit'  => $counter_total < 0 ? abs( $counter_total ) : 0,
			'credit' => $counter_total < 0 ? 0 : $counter_total,
		);

		return $retval;
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
		$date   = incassoos_get_collection_date( $this->post, 'Y-m-d' );
		$retval = '';

		foreach ( $lines as $line ) {

			// Sanitize line
			$line = wp_parse_args( $line, array( 'ledger_id' => -1 ) );
			$line = array_map( 'esc_html', $line );

			// Build line
			$retval .= "#{$date}#," . $line['ledger_id'] . ',"' . $line['title'] . '",' . $line['credit'] . ',' . $line['debit'] . "\n";
		}

		return $retval;
	}
}

endif; // class_exists
