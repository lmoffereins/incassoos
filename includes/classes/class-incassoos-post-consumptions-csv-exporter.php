<?php

/**
 * Incassoos Post Consumptions CSV Exporter class
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

if ( ! class_exists( 'Incassoos_Post_Consumptions_CSV_Exporter' ) ) :
/**
 * The Incassoos Post Consumptions CSV Exporter class
 *
 * @since 1.0.0
 */
class Incassoos_Post_Consumptions_CSV_Exporter extends Incassoos_CSV_Exporter {

	/**
	 * The post
	 *
	 * @since 1.0.0
	 * @var WP_Post
	 */
	private $post;

	/**
	 * The post's object type
	 *
	 * @since 1.0.0
	 * @var string
	 */
	private $object_type;

	/**
	 * Setup this class
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post|int $post Optional. Post object or ID. Defaults to the current post.
	 */
	public function __construct( $post = 0 ) {

		// Set file type
		$this->file_type = incassoos_get_post_consumptions_export_type_id();

		// Set the post context
		$this->post = $post = get_post( $post );
		$this->object_type  = incassoos_get_object_type( $post->post_type ) ?: 'default';

		if ( $post ) {
			$columns   = $this->get_post_columns();
			$file_data = $this->get_post_data();

			// Setup base class
			parent::__construct( $columns, $file_data );

			// Set file name
			$this->set_filename(
				sprintf( '%s-%s-%s-%s.csv',
					incassoos_get_post_type_label( $this->post->post_type ),
					incassoos_get_post_title( $this->post ),
					esc_html__( 'Consumptions', 'incassoos' ),
					date( 'Ymd' )
				)
			);
		}
	}

	/** Structure *******************************************************/

	/**
	 * Return the post file columns
	 *
	 * @since 1.0.0
	 *
	 * @uses apply_filters() Calls 'incassoos_export-{file_type}-get_{object_type}_columns'
	 *
	 * @return array Post file columns
	 */
	public function get_post_columns() {
		switch ( $this->post->post_type ) {

			// Occasion
			case incassoos_get_occasion_post_type() :
				$columns = array(
					'id'             => esc_html__( 'Occasion ID',    'incassoos' ),
					'occasion'       => esc_html__( 'Occasion',       'incassoos' ),
					'occasion_date'  => esc_html__( 'Occasion date',  'incassoos' ),
					'order_id'       => esc_html__( 'Order ID',       'incassoos' ),
					'order_datetime' => esc_html__( 'Order datetime', 'incassoos' ),
					'user_id'        => esc_html__( 'User ID',        'incassoos' ),
					'user_name'      => esc_html__( 'User',           'incassoos' ),
					'product_id'     => esc_html__( 'Product ID',     'incassoos' ),
					'product_name'   => esc_html__( 'Product',        'incassoos' ),
					'amount'         => esc_html__( 'Amount',         'incassoos' ),
					'price'          => esc_html__( 'Price',          'incassoos' ),
					'total'          => esc_html__( 'Total',          'incassoos' )
				);
				break;

			default :
				$columns = array();
		}

		return apply_filters( "incassoos_export-{$this->file_type}-get_{$this->object_type}_columns", $columns, $this );
	}

	/**
	 * Return the post file data
	 *
	 * @since 1.0.0
	 *
	 * @uses apply_filters() Calls 'incassoos_export-{file_type}-get_{object_type}_data_row'
	 * @uses apply_filters() Calls 'incassoos_export-{file_type}-get_{object_type}_data'
	 *
	 * @return array Post file data
	 */
	public function get_post_data() {
		$rows = array();

		switch ( $this->post->post_type ) {

			// Occasion
			case incassoos_get_occasion_post_type() :
				$post_id    = $this->post->ID;
				$post_title = incassoos_get_occasion_title( $this->post );
				$post_date  = incassoos_get_occasion_date( $this->post, 'Y-m-d' );

				foreach ( incassoos_get_occasion_orders( $this->post ) as $order_id ) {
					$order_date = incassoos_get_order_created( $order_id, 'Y-m-d H:i:s' );
					$user_id    = incassoos_get_order_consumer_id( $order_id );
					$user_name  = incassoos_get_order_consumer_title( $order_id );

					foreach ( incassoos_get_order_products( $order_id ) as $product ) {
						$rows[] = apply_filters( "incassoos_export-{$this->file_type}-get_{$this->object_type}_data_row", array(
							'id'            => $post_id,
							'occasion'      => $post_title,
							'occasion_date' => $post_date,
							'order_id'      => $order_id,
							'order_date'    => $order_date,
							'user_id'       => $user_id,
							'user_name'     => $user_name,
							'product_id'    => $product['id'],
							'product_name'  => $product['name'],
							'amount'        => $product['amount'],
							'price'         => incassoos_parse_currency( $product['price'], true ),
							'total'         => incassoos_parse_currency( $product['amount'] * $product['price'], true )
						), $product, $order_id, $this );
					}
				}
				break;
		}

		return apply_filters( "incassoos_export-{$this->file_type}-get_{$this->object_type}_data", $rows, $this );
	}
}

endif; // class_exists
