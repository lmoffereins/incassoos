<?php

/**
 * Incassoos Collection SEPA XML Exporter class
 *
 * @package Incassoos
 * @subpackage Export
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Include dependencies
if ( ! class_exists( 'Incassoos_SEPA_XML_Exporter', false ) ) {
	require_once( incassoos()->includes_dir . 'classes/class-incassoos-sepa-xml-exporter.php' );
}

if ( ! class_exists( 'Incassoos_Collection_SEPA_XML_Exporter' ) ) :
/**
 * The Incassoos Collection SEPA XML Exporter class
 *
 * @since 1.0.0
 */
class Incassoos_Collection_SEPA_XML_Exporter extends Incassoos_SEPA_XML_Exporter {

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

		// Require the decryption key
		if ( incassoos_is_encryption_enabled() && ! incassoos_get_decryption_key() ) {
			$this->errors[] = esc_html__( 'The required decryption key was not provided.', 'incassoos' );
			return;
		}

		// Require the Collection to be collected
		$this->post = incassoos_get_collection( $post, array( 'is_collected' => true ) );

		if ( $this->post ) {
			$iban = incassoos_get_account_iban();
			$args = array(
				'party'        => array(
					'organization' => incassoos_get_organization_name(),
					'name'         => incassoos_get_account_holder(),
					'iban'         => $iban,
					'bic'          => incassoos_get_bic_from_iban( $iban ),
					'creditor_id'  => incassoos_get_sepa_creditor_id(),
					'currency'     => incassoos_get_currency(),
				),
				'transactions' => $this->get_transactions()
			);

			// Setup base class
			parent::__construct( $args );
		}
	}

	/**
	 * Return the Collection's export filename
	 *
	 * @since 1.0.0
	 *
	 * @return string Export filename
	 */
	public function get_filename() {
		if ( $this->post ) {
			return sprintf( '%s-SEPA-%s-%s.xml',
				$this->party->organization,
				incassoos_get_collection_title( $this->post ),
				incassoos_get_collection_date( $this->post, 'Ymd' )
			);
		} else {
			return parent::get_filename();
		}
	}

	/**
	 * Return the Collection's transaction data
	 *
	 * @since 1.0.0
	 *
	 * @return array Collection transaction data
	 */
	public function get_transactions() {
		$users  = incassoos_get_collection_consumer_users( $this->post );
		$retval = array();

		foreach ( $users as $user ) {
			$iban     = incassoos_get_user_iban( $user->ID );
			$retval[] = array(
				'amount'      => incassoos_get_collection_consumer_total( $user->ID, $this->post ),
				'description' => incassoos_get_collection_transaction_description( $this->post ),
				'party'       => array(
					'id'   => $user->ID,
					'name' => incassoos_get_user_display_name( $user->ID ),
					'iban' => $iban,
					'bic'  => incassoos_get_bic_from_iban( $iban )
				)
			);
		}

		return $retval;
	}
}

endif; // class_exists
