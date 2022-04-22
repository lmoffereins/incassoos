<?php

/**
 * Incassoos Post Consumers CSV Exporter class
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

if ( ! class_exists( 'Incassoos_Post_Consumers_CSV_Exporter' ) ) :
/**
 * The Incassoos Post Consumers CSV Exporter class
 *
 * @since 1.0.0
 */
class Incassoos_Post_Consumers_CSV_Exporter extends Incassoos_CSV_Exporter {

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
		$this->file_type = incassoos_get_post_consumers_export_type_id();

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
					incassoos_get_activity_post_type() === $this->post->post_type
						? esc_html__( 'Participants', 'incassoos' )
						: esc_html__( 'Consumers',    'incassoos' ),
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

			// Collection
			case incassoos_get_collection_post_type() :
				$columns = array(
					'id'              => esc_html__( 'Collection ID',   'incassoos' ),
					'collection'      => esc_html__( 'Collection',      'incassoos' ),
					'collection_date' => esc_html__( 'Collection date', 'incassoos' ),
					'user_id'         => esc_html__( 'User ID',         'incassoos' ),
					'user_name'       => esc_html__( 'User',            'incassoos' ),
					'asset_id'        => esc_html__( 'Item ID',         'incassoos' ),
					'asset_name'      => esc_html__( 'Item',            'incassoos' ),
					'asset_date'      => esc_html__( 'Item date',       'incassoos' ),
					'total'           => esc_html__( 'Total',           'incassoos' )
				);
				break;

			// Activity
			case incassoos_get_activity_post_type() :
				$columns = array(
					'id'             => esc_html__( 'Activity ID',   'incassoos' ),
					'activity'       => esc_html__( 'Activity',      'incassoos' ),
					'activity_date'  => esc_html__( 'Activity date', 'incassoos' ),
					'user_id'        => esc_html__( 'User ID',       'incassoos' ),
					'user_name'      => esc_html__( 'User',          'incassoos' ),
					'price'          => esc_html__( 'Price',         'incassoos' )
				);
				break;

			// Occasion
			case incassoos_get_occasion_post_type() :
				$columns = array(
					'id'            => esc_html__( 'Occasion ID',   'incassoos' ),
					'occasion'      => esc_html__( 'Occasion',      'incassoos' ),
					'occasion_date' => esc_html__( 'Occasion date', 'incassoos' ),
					'user_id'       => esc_html__( 'User ID',       'incassoos' ),
					'user_name'     => esc_html__( 'User',          'incassoos' ),
					'order_count'   => esc_html__( 'Order count',   'incassoos' ),
					'total'         => esc_html__( 'Total',         'incassoos' )
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

			// Collection
			case incassoos_get_collection_post_type() :
				$post_id    = $this->post->ID;
				$post_title = incassoos_get_collection_title( $this->post );
				$post_date  = incassoos_get_collection_date( $this->post, 'Y-m-d' );

				// Consumer users
				foreach ( incassoos_get_collection_consumer_users() as $user ) {
					foreach ( incassoos_get_collection_consumer_assets( $user->ID, $this->post ) as $item_id ) {
						$rows[] = apply_filters( "incassoos_export-{$this->file_type}-get_{$this->object_type}_data_row", array(
							'id'              => $post_id,
							'collection'      => $post_title,
							'collection_date' => $post_date,
							'user_id'         => $user->ID,
							'user_name'       => $user->display_name,
							'asset_id'        => $item_id,
							'asset_name'      => incassoos_get_post_title( $item_id ),
							'asset_date'      => incassoos_get_post_date( $item_id, 'Y-m-d' ),
							'total'           => incassoos_get_post_consumer_total( $user->ID, $item_id, true )
						), $item_id, $user, $this );
					}
				}

				// Consumer types
				foreach ( incassoos_get_collection_consumer_types() as $type_id ) {
					$type_name = incassoos_get_consumer_type_title( $type_id );

					foreach ( incassoos_get_collection_consumer_assets( $user, $this->post ) as $item_id ) {
						$rows[] = apply_filters( "incassoos_export-{$this->file_type}-get_{$this->object_type}_data_row", array(
							'id'              => $post_id,
							'collection'      => $post_title,
							'collection_date' => $post_date,
							'user_id'         => $type_id,
							'user_name'       => $type_name,
							'asset_id'        => $item_id,
							'asset_name'      => incassoos_get_post_title( $item_id ),
							'asset_date'      => incassoos_get_post_date( $item_id, 'Y-m-d' ),
							'total'           => incassoos_get_post_consumer_total( $type_id, $item_id, true )
						), $item_id, $type_id, $this );
					}
				}
				break;

			// Activity
			case incassoos_get_activity_post_type() :
				$post_id    = $this->post->ID;
				$post_title = incassoos_get_activity_title( $this->post );
				$post_date  = incassoos_get_activity_date( $this->post , 'Y-m-d');

				foreach ( incassoos_get_activity_participant_users() as $user ) {
					$rows[] = apply_filters( "incassoos_export-{$this->file_type}-get_{$this->object_type}_data_row", array(
						'id'            => $post_id,
						'activity'      => $post_title,
						'activity_date' => $post_date,
						'user_id'       => $user->ID,
						'user_name'     => $user->display_name,
						'price'         => incassoos_get_activity_participant_price( $user->ID, $this->post, true )
					), $user, $this );
				}
				break;

			// Occasion
			case incassoos_get_occasion_post_type() :
				$post_id    = $this->post->ID;
				$post_title = incassoos_get_occasion_title( $this->post );
				$post_date  = incassoos_get_occasion_date( $this->post, 'Y-m-d' );

				// Consumer users
				foreach ( incassoos_get_occasion_consumer_users() as $user ) {
					$rows[] = apply_filters( "incassoos_export-{$this->file_type}-get_{$this->object_type}_data_row", array(
						'id'            => $post_id,
						'occasion'      => $post_title,
						'occasion_date' => $post_date,
						'user_id'       => $user->ID,
						'user_name'     => $user->display_name,
						'order_count'   => incassoos_get_occasion_consumer_order_count( $user->ID, $this->post ),
						'total'         => incassoos_get_occasion_consumer_total( $user->ID, $this->post, true )
					), $user, $this );
				}

				// Consumer types
				foreach ( incassoos_get_occasion_consumer_types() as $type_id ) {
					$rows[] = apply_filters( "incassoos_export-{$this->file_type}-get_{$this->object_type}_data_row", array(
						'id'            => $post_id,
						'occasion'      => $post_title,
						'occasion_date' => $post_date,
						'user_id'       => $type_id,
						'user_name'     => incassoos_get_consumer_type_title( $type_id ),
						'order_count'   => incassoos_get_occasion_consumer_order_count( $type_id, $this->post ),
						'total'         => incassoos_get_occasion_consumer_total( $type_id, $this->post, true )
					), $user, $this );
				}
				break;
		}

		return apply_filters( "incassoos_export-{$this->file_type}-get_{$this->object_type}_data", $rows, $this );
	}
}

endif; // class_exists
