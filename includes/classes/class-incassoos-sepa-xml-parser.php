<?php

/**
 * Incassoos SEPA XML Parser class
 *
 * @package Incassoos
 * @subpackage Export
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Incassoos_SEPA_XML_Parser' ) ) :
/**
 * The Incassoos SEPA XML Parser class
 *
 * @since 1.0.0
 */
class Incassoos_SEPA_XML_Parser {

	/**
	 * Holds the XML document object
	 *
	 * @since 1.0.0
	 * @var DOMDocument object
	 */
	private $xml;

	/**
	 * Whether this is a debit payment.
	 *
	 * @since 1.0.0
	 * @var boolean
	 */
	private $is_debit = true;

	/**
	 * Author party details.
	 * 
	 * @since 1.0.0
	 * @var string
	 */
	public $party = array();

	/**
	 * Holds additional Group Header tags.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	public $header_tags = array();

	/**
	 * Holds additional Payment Information tags.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	public $payment_tags = array();

	/**
	 * Holds additional Transaction Information tags.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	public $transaction_tags = array();

	/**
	 * Holds the transactions to process.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $_transactions = array();

	/**
	 * Iterator for the current transaction in the loop.
	 *
	 * @since 1.0.0
	 * @var integer
	 */
	public $current_transaction = -1;

	/**
	 * Holds the list of file errors
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private $errors = array();

	/**
	 * Setup this class
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Configuration settings
	 * @param array $debit Optional. Whether to generate a debit file. Defaults to true.
	 */
	public function __construct( $args = array(), $debit = true ) {

		// Init XML object
		$this->xml = new DOMDocument( '1.0', 'UTF-8' );
		$this->xml->formatOutput = true;

		// Set debit status
		$this->is_debit = (bool) $debit;

		// Parse arguments
		if ( $args ) {
			$args = wp_parse_args( $args, array(
				'party'            => array(),
				'transactions'     => array(),
				'collection_date'  => '',
				'due_date'         => '',
				'header_tags'      => array(),
				'payment_tags'     => array(),
				'transaction_tags' => array(),
			) );

			// Parse party details
			$this->party = (object) wp_parse_args( $args['party'], array(
				'organization' => false,
				'name'         => false,
				'iban'         => false,
				'bic'          => false,
				'creditor_id'  => false,
				'currency'     => '',
			) );

			// Parse transactions
			$this->set_transactions( $args['transactions'] );

			// Set collection's dates
			$this->set_collection_date( $args['collection_date'] );
			$this->set_due_date(        $args['due_date']        );

			// Additional header tags
			if ( $args['header_tags'] ) {
				$this->header_tags = $args['header_tags'];
			}

			// Additional payment tags
			if ( $args['payment_tags'] ) {
				$this->payment_tags = $args['payment_tags'];
			}

			// Additional transaction tags
			if ( $args['transaction_tags'] ) {
				$this->transaction_tags = $args['transaction_tags'];
			}

			// Validate and build the file
			if ( $this->validate_file() ) {
				$this->build_file();
			}
		}
	}

	/** Setup methods ***************************************************/

	/**
	 * Setup transactions for the payment
	 *
	 * @since 1.0.0
	 *
	 * @param array $transactions Transactions
	 */
	public function set_transactions( $transactions = array() ) {

		// Empty transactions collection
		$this->_transactions = array();

		// Parse 'n push new transactions
		foreach ( (array) $transactions as $index => $t ) {
			$t = (object) wp_parse_args( (array) $t, array(
				'amount'      => 0,
				'description' => '',
				'party'       => array()
			) );

			// Generate default mandate identifier
			$index    = isset( $t->party['id'] ) ? (int) $t->party['id'] : $index;
			$mandate  = zeroise( $index + date( 'm' ) * 100 + date( 'H' ), 4 ) . '-' . zeroise( round( mt_rand() / 1000000 ), 4 );

			// Parse defaults
			$t->party = (object) wp_parse_args( $t->party, array(
				'name'         => false,
				'iban'         => false,
				'bic'          => false,
				'mandate'      => $mandate,
				'mandate_date' => strtotime( '2009-01-11' ),
			) );

			$this->_transactions[] = $t;
		}
	}

	/**
	 * Set the SEPA collection date
	 *
	 * @since 1.0.0
	 *
	 * @param string $date Optional. Collection date timestamp. Defaults to now.
	 */
	public function set_collection_date( $date = '' ) {
		$this->_collection_date = $date ? strtotime( $date ) : time();
	}

	/**
	 * Set the SEPA due date
	 *
	 * @since 1.0.0
	 *
	 * @param string $date Optional. Due date timestamp. Defaults to five days from now.
	 */
	public function set_due_date( $date = '' ) {
		$this->_due_date = $date ? strtotime( $date ) : strtotime( '+5 days' );
	}

	/**
	 * Return the due date in the given format
	 *
	 * @since 1.0.0
	 *
	 * @param  string $format Optional. Date format. Defaults to 'Y-m-d'.
	 * @return string Formatted due date
	 */
	public function get_due_date( $format = 'Y-m-d' ) {
		if ( ! $this->_due_date ) {
			$this->set_due_date();
		}

		return date( $format, $this->_due_date );
	}

	/**
	 * Return whether this is a Direct Debit
	 *
	 * @since 1.0.0
	 *
	 * @return boolean Is this a Direct Debit?
	 */
	public function is_debit() {
		return $this->is_debit;
	}

	/**
	 * Return whether this is a Credit Transfer
	 *
	 * @since 1.0.0
	 *
	 * @return boolean Is this a Credit Transfer?
	 */
	public function is_credit() {
		return ! $this->is_debit;
	}

	/**
	 * Return the payment's Payments Initiation (PAIN) code
	 *
	 * @since 1.0.0
	 *
	 * @return string PAIN code
	 */
	public function get_pain() {
		return $this->is_debit()
			? '008.001.02'  // Direct Debit PAIN
			: '001.001.03'; // Credit Transfer PAIN
	}

	/** Validation ******************************************************/

	/**
	 * Validate file components and register any errors
	 *
	 * @since 1.0.0
	 *
	 * @return array Validation errors
	 */
	public function validate_file() {

		// Reset errors list
		$this->errors = array();
		$failed_details = array();

		// Party details
		foreach ( array(
			'organization',
			'name',
			'iban',
			'bic',
			'creditor_id',
			'currency',
		) as $detail ) {
			if ( empty( $this->party->{$detail} ) ) {
				$failed_details[] = "<code>{$detail}</code>";
			}
		}

		// Construct error message
		if ( $failed_details ) {
			$detail_list = count( $failed_details ) > 1 ? wp_sprintf_l( '%l', $failed_details ) : $failed_details[0];
			$this->errors[] = sprintf( esc_html__( 'The file has an invalid value for the following detail(s) of the creditor/organization: %s', 'incassoos' ), $detail_list );
		}

		// Transaction errors
		foreach ( $this->_transactions as $t ) {
			$failed_details = array();

			// Check presence required details
			foreach ( array(
				'amount',
				'description',
				'name',
				'iban',
				'bic',
			) as $detail ) {
				if ( empty( $t->{$detail} ) && empty( $t->party->{$detail} ) ) {
					$failed_details[] = "<code>{$detail}</code>";
				}
			}

			// Skip when no errors were found
			if ( ! $failed_details )
				continue;

			// Setup error message, by party name...
			if ( ! empty( $t->party->name ) ) {
				$message = sprintf( esc_html__( 'The transaction for %1$s has an invalid value for the following detail(s): %2$s', 'incassoos' ), '<span class="party">' . $t->party->name . '</span>', '%s' );
			// ... by party id
			} elseif ( ! empty( $t->party->id ) ) {
				$message = sprintf( esc_html__( 'The transaction for party with ID %1$s has an invalid value for the following detail(s): %2$s', 'incassoos' ), $t->part->id, '%s' );
			// ... without party
			} else {
				$message = esc_html__( 'A transaction has an invalid value for the following detail(s): %s', 'incassoos' );
			}

			// Construct error message
			$this->errors[] = sprintf( $message, count( $failed_details ) > 1 ? wp_sprintf_l( '%l', $failed_details ) : $failed_details[0] );
		}

		return ! $this->has_errors();
	}

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

	/** Structure *******************************************************/

	/**
	 * Create the SEPA XML file structure
	 *
	 * @since 1.0.0
	 */
	public function build_file() {

		// Start file
		$this->setup_root_tag();

		// File contents
		$this->setup_group_header();
		$this->setup_payment_information();
	}

	/**
	 * Return the SEPA XML file
	 *
	 * @since 1.0.0
	 *
	 * @return string SEPA XML file or False when invalid.
	 */
	public function get_file() {
		return $this->xml->saveXML();
	}

	/**
	 * Return the SEPA XML filename
	 *
	 * @since 1.0.0
	 *
	 * @return string SEPA XML filename
	 */
	public function get_filename() {
		return sprintf( '%s-SEPA-%s-%s.xml', $this->party->organization, date( 'Ymd', $this->_collection_date ), date( 'Ymd' ) );
	}

	/**
	 * Setup the file's root tag
	 *
	 * @since 1.0.0
	 */
	public function setup_root_tag() {

		// Create root tag
		$root = $this->xml->createElement( 'Document' );
		$root->setAttribute( 'xmlns',     'urn:iso:std:iso:20022:tech:xsd:pain.' . $this->get_pain() );
		$root->setAttribute( 'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance' );

		// Create collection tag
		$collection = $this->xml->createElement( $this->is_debit() ? 'CstmrDrctDbtInitn' : 'CstmrCdtTrfInitn' );

		// Add collection to root
		$root->appendChild( $collection );

		// Add root to xmlument
		$this->xml->appendChild( $root );
	}

	/**
	 * Setup the file's Group Header tag
	 *
	 * @since 1.0.0
	 */
	private function setup_group_header() {

		// Create GrpHdr tag
		$tag = $this->xml->createElement( 'GrpHdr' );

		// Parse header tags
		$time = time();
		$header_tags = wp_parse_args( $this->header_tags, array(
			'MsgId'    => date( 'YmdHis', $time ),       // Message identifier
			'CreDtTm'  => date( 'Y-m-d\TH:i:s', $time ), // Timestamp ISO 8601
			'NbOfTxs'  => count( $this->_transactions ), // Number of transactions
			'InitgPty' => $this->party->organization     // Initiating party name
		) );

		// Setup tags and add to header
		$this->append_tags( $header_tags, $tag );

		// Add header to collection
		$this->append_tag( $tag );
	}

	/**
	 * Setup the file's Payment Information tag
	 *
	 * @since 1.0.0
	 */
	private function setup_payment_information() {

		// Create payment information tag
		$tag = $this->xml->createElement( 'PmtInf' );

		// Parse and setup tags, append to payment information
		$this->append_tags( $this->get_payment_information_tags(), $tag );

		// Add payment information to collection
		$this->append_tag( $tag );

		// Add transaction information
		while ( $this->have_transactions() ) {

			// Create transaction tag
			$ttag = $this->xml->createElement( $this->is_debit() ? 'DrctDbtTxInf' : 'CdtTrfTxInf' );

			// Parse and setup tags and append to transaction
			$this->append_tags( $this->get_transaction_information_tags(), $ttag );

			// Add transaction to payment
			$tag->appendChild( $ttag );
		}
	}

	/**
	 * Return the tags for the Payment Information part
	 *
	 * @todo Implement Credit Transfer variant
	 *
	 * @since 1.0.0
	 *
	 * @return array Payment Information tags
	 */
	public function get_payment_information_tags() {
		return wp_parse_args( $this->payment_tags, array(
			'PmtInfId' => 'PmtInfId-001',                      // Payment information identifier
			'PmtMtd'   => $this->is_debit() ? 'DD' : 'CT',     // Payment method
			'PmtTpInf' => array(                               // Payment type information
				'SvcLvl' => array(                             // Service level
					'Cd' => 'SEPA',                            // SEPA scheme
				),
				'LclInstrm' => array(                          // ?
					'Cd' => 'CORE',                            // CORE or B2B transaction
				),
				'SeqTp'  => 'FRST',                            // Sequence type (FRST: first, RCUR: recurring, FNAL: final, OOFF: one-off)
			),
			'ReqdColltnDt' => $this->get_due_date(),           // Requested collection due date
			'Cdtr'     => array(                               // Creditor details
				'Nm' => $this->party->name,                    // Creditor's name
			),
			'CdtrAcct' => array(                               // Creditor account
				'Id' => array(                                 // Account identifier
					'IBAN' => $this->party->iban,              // Creditor's IBAN
				),
			),
			'CdtrAgt' => array(                                // Creditor agent
				'FinInstnId' => array(                         // Financial institution identifier
					'BIC' => $this->party->bic,                // Creditor's financial institution's BIC
				),
			),
			'CdtrSchmeId' => array(                            // Creditor schema identifier
				'Id' => array(                                 // Identifier set
					'PrvtId' => array(                         // Private entity
						'Othr' => array(                       // Other details
							'Id' => $this->party->creditor_id, // Creditor identifier
							'SchmeNm' => array(                // Scheme name
								'Prtry' => 'SEPA',             // ?
							),
						),
					),
				),
			),
		) );
	}

	/**
	 * Return the tags for the Payment Information part
	 *
	 * @since 1.0.0
	 *
	 * @return array Payment Information tags
	 */
	public function get_transaction_information_tags() {
		return $this->is_debit() ? $this->get_debit_information_tags() : $this->get_credit_information_tags();
	}

	/**
	 * Return the tags for the Debit Transaction Information
	 *
	 * @since 1.0.0
	 *
	 * @return array Debit Transaction Information tags
	 */
	public function get_debit_information_tags() {

		// Get looped transaction
		$transaction = $this->get_transaction();
		$mandate_date = date( 'Y-m-d', $transaction->party->mandate_date );

		// Parse debit tags
		return wp_parse_args( $this->transaction_tags, array(
			'PmtId' => array(                                         // Transaction identifier
				'EndToEndId' => $this->get_unique_transaction_id(),   // Identifier
			),
			'InstdAmt' => array(
				$transaction->amount,                                 // Transaction amount
				'Ccy' => $this->party->currency,                      // Transaction currency
			),
			'DrctDbtTx' => array(                                     // ?
				'MndtRltdInf' => array(                               // Mandate information
					'MndtId'    => $transaction->party->mandate,      // Mandate identifier
					'DtOfSgntr' => $mandate_date                      // Signature date
				),
			),
			'Dbtr' => array(                                          // Debtor details
				'Nm' => $transaction->party->name,                    // Debtor's name
			),
			'DbtrAcct' => array(                                      // Debtor account
				'Id' => array(                                        // Account identifier
					'IBAN' => $transaction->party->iban,              // Debtor's IBAN
				),
			),
			'DbtrAgt' => array(                                       // Debtor agent
				'FinInstnId' => array(                                // Financial institution identifier
					'BIC' => $transaction->party->bic,                // Debtor's financial institution's BIC
				),
			),
			'InstrForCdtrAgt' => 'ALL',                               // Creditor agent instruction
			'RmtInf' => array(                                        // Additional information
				'Ustrd' => $transaction->description,                 // Transaction description
			),
		) );
	}

	/**
	 * Return the tags for the Credit Transaction Information
	 *
	 * @todo Verify Credit Transfer tags
	 *
	 * @since 1.0.0
	 *
	 * @return array Credit Transaction Information tags
	 */
	public function get_credit_information_tags() {

		// Get looped transaction
		$transaction = $this->get_transaction();
		$mandate_date = date( 'Y-m-d', $transaction->party->mandate_date );

		// Parse credit tags
		return wp_parse_args( $this->transaction_tags, array(
			'PmtId' => array(                                         // Transaction identifier
				'EndToEndId' => $this->get_unique_transaction_id(),   // Identifier
			),
			'InstdAmt' => array(
				$transaction->amount,                                 // Transaction amount
				'Ccy' => $this->party->currency,                      // Transaction currency
			),
			'CdtTrfTx' => array(                                      // ?
				'MndtRltdInf' => array(                               // Mandate information
					'MndtId'    => $transaction->party->mandate,      // Mandate identifier
					'DtOfSgntr' => $mandate_date                      // Signature date
				),
			),
			'Cdt' => array(                                           // Creditor details
				'Nm' => $transaction->party->name,                    // Creditor's name
			),
			'CdtAcct' => array(                                       // Creditor account
				'Id' => array(                                        // Account identifier
					'IBAN' => $transaction->party->iban,              // Creditor's IBAN
				),
			),
			'CdtAgt' => array(                                        // Creditor agent
				'FinInstnId' => array(                                // Financial institution identifier
					'BIC' => $transaction->party->bic,                // Creditor's financial institution's BIC
				),
			),
			'InstrForDbtrAgt' => 'ALL',                               // Debtor agent instruction
			'RmtInf' => array(                                        // Additional information
				'Ustrd' => $transaction->description,                 // Transaction description
			),
		) );
	}

	/**
	 * Append new tags to an existing DOMElement
	 *
	 * @since 1.0.0
	 *
	 * @param array $args List of tags to append as $tag_name => $content
	 * @param DOMElement $root Element root for the new tags
	 */
	public function append_tags( $args = array(), $root = null ) {

		// Bail when the root tag is not an DOMElement
		if ( ! is_a( $root, 'DOMElement' ) )
			return;

		// Walk the tag list
		foreach ( array_filter( (array) $args ) as $tag => $content ) {

			// Create tag
			$tag = $this->xml->createElement( $tag );

			// Parse sub-tags
			if ( is_array( $content ) ) {
				$keys = array_keys( $content );

				// Add attributes and content
				if ( is_numeric( $keys[0] ) ) {

					// Set tag content
					$tag->nodeValue = trim( $content[ $keys[0] ] );
					unset( $content[ $keys[0] ] );

					// Add attributes
					foreach ( $content as $attr => $value ) {
						if ( strlen( trim( $value ) ) ) {
							$tag->setAttribute( $attr, trim( $value ) );
						}
					}

				// Append child tags
				} else {
					$tag = $this->append_tags( $content, $tag );
				}

			// Parse tag content
			} elseif ( strlen( trim( $content ) ) ) {
				$tag->nodeValue = trim( $content );
			}

			// Add tag to root
			$root->appendChild( $tag );
		}

		return $root;
	}

	/**
	 * Append the tag to the XML document at the given path
	 *
	 * @since 1.0.0
	 *
	 * @param DOMElement $tag Element to append
	 * @param string $path Optional. Path to query beyond root/collection.
	 */
	public function append_tag( $tag, $path = '' ) {

		// Define path at root/collection/{path}
		$path = empty( $path ) ? '' : '/' . trim( $path, '/' );
		$path = '//Document/' . ( $this->is_debit() ? 'CstmrDrctDbtInitn' : 'CstmrCdtTrfInitn' ) . $path;

		// Query path, add tag
		$xpath = new DOMXPath( $this->xml );
		$xpath->query( $path )->item( 0 )->appendChild( $tag );
	}

	/** Transactions ****************************************************/

	/**
	 * Return whether there are any transactions left to loop over
	 *
	 * @since 1.0.0
	 *
	 * @return bool Do we have transactions left?
	 */
	protected function have_transactions() {
		if ( isset( $this->_transactions[ $this->current_transaction + 1 ] ) ) {
			$this->current_transaction++;
			return true;
		} else {

			// Rewind iterator
			$this->current_transaction = 0;
			return false;
		}
	}

	/**
	 * Return the currently iterated transaction
	 *
	 * @since 1.0.0
	 *
	 * @return object Current transaction
	 */
	protected function get_transaction() {
		return $this->_transactions[ $this->current_transaction ];
	}

	/**
	 * Return a unique transaction identifier
	 *
	 * @since 1.0.0
	 *
	 * @return string Transaction identifier
	 */
	private function get_unique_transaction_id() {
		return sprintf( '%s-%s-%s', date( 'Ymd' ), $this->get_transaction()->party->mandate, '0001' );
 	}
}

endif; // class_exists
