<?php

/**
 * Incassoos SEPA XML File class
 *
 * @package Incassoos
 * @subpackage Export
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Incassoos_SEPA_XML_File' ) ) :
/**
 * The Incassoos SEPA XML File class
 *
 * @since 1.0.0
 */
class Incassoos_SEPA_XML_File extends Incassoos_SEPA_XML_Parser {

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
	 * Return the Collection's XML filename
	 *
	 * @since 1.0.0
	 *
	 * @return string Filename
	 */
	public function get_filename() {
		if ( $this->post ) {
			return sprintf( '%s-SEPA-%s.xml', $this->party->organization, incassoos_get_collection_date( $this->post, 'Y-m-d' ) );
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
