<?php

/**
 * Incassoos Dashboard Functions
 *
 * @package Incassoos
 * @subpackage Administration
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Act when the Dashboard admin page is being loaded
 *
 * @see wp-admin/index.php
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'incassoos_admin_load_dashboard_page'
 */
function incassoos_admin_load_dashboard_page() {

	/** Load WordPress dashboard API */
	require_once( ABSPATH . 'wp-admin/includes/dashboard.php' );

	do_action( 'incassoos_admin_load_dashboard_page' );

	wp_enqueue_script( 'dashboard' );

	if ( wp_is_mobile() ) {
		wp_enqueue_script( 'jquery-touch-punch' );
	}
}

/**
 * Output the contents of the Dashboard admin page
 *
 * @see wp-admin/index.php
 *
 * @since 1.0.0
 */
function incassoos_admin_dashboard_page() { ?>

	<div id="dashboard-widgets-wrap">

		<?php wp_dashboard(); ?>

	</div><!-- dashboard-widgets-wrap -->

	<?php
}

/**
 * Add plugin admin dashboard widgets
 *
 * @since 1.0.0
 */
function incassoos_admin_add_dashboard_widgets() {

	// At a Glance
	wp_add_dashboard_widget(
		'incassoos_dashboard_status',
		__( 'At a Glance' ),
		'incassoos_admin_dashboard_status_widget'
	);

	// History
	wp_add_dashboard_widget(
		'incassoos_dashboard_history',
		_x( 'History', 'Dashboard widget', 'incassoos' ),
		'incassoos_admin_dashboard_history_widget'
	);

	// Quick actions
	wp_add_dashboard_widget(
		'incassoos_dashboard_actions',
		_x( 'Quick actions', 'Dashboard widget', 'incassoos' ),
		'incassoos_admin_dashboard_actions_widget'
	);
}

/**
 * Output the contents of the Current Status dashboard widget
 *
 * @since 1.0.0
 */
function incassoos_admin_dashboard_status_widget() {

	// Assets
	$collection = incassoos_get_collection_post_type();
	$activity   = incassoos_get_activity_post_type();
	$occasion   = incassoos_get_occasion_post_type();
	$order      = incassoos_get_order_post_type();
	$product    = incassoos_get_product_post_type();

	// Counts
	$collection_count = wp_count_posts( $collection );
	$activity_count   = wp_count_posts( $activity   );
	$occasion_count   = wp_count_posts( $occasion   );
	$order_count      = wp_count_posts( $order      );
	$product_count    = wp_count_posts( $product    );

	// Collect statuses to display
	$statuses = apply_filters( 'incassoos_admin_dashboard_statuses', array(

		// Collections
		'collection-count' => sprintf( '<a href="%s">%s</a>',
			esc_url( add_query_arg( array( 'post_type' => $collection, 'post_status' => 'publish' ), admin_url( 'edit.php' ) ) ),
			sprintf( _n( '%s Collection', '%s Collections', $collection_count->publish, 'incassoos' ), $collection_count->publish )
		),

		// Activities
		'activity-count' => sprintf( '<a href="%s">%s</a>',
			esc_url( add_query_arg( array( 'post_type' => $activity, 'post_status' => 'publish' ), admin_url( 'edit.php' ) ) ),
			sprintf( _n( '%s Activity', '%s Activities', $activity_count->publish, 'incassoos' ), $activity_count->publish )
		),

		// Occasions
		'occasion-count' => sprintf( '<a href="%s">%s</a>',
			esc_url( add_query_arg( array( 'post_type' => $occasion, 'post_status' => 'publish' ), admin_url( 'edit.php' ) ) ),
			sprintf( _n( '%s Occasion', '%s Occasions', $occasion_count->publish, 'incassoos' ), $occasion_count->publish )
		),

		// Orders
		'order-count' => sprintf( '<a href="%s">%s</a>',
			esc_url( add_query_arg( array( 'post_type' => $order, 'post_status' => 'publish' ), admin_url( 'edit.php' ) ) ),
			sprintf( _n( '%s Order', '%s Orders', $order_count->publish, 'incassoos' ), $order_count->publish )
		),

		// Products
		'product-count' => sprintf( '<a href="%s">%s</a>',
			esc_url( add_query_arg( array( 'post_type' => $product, 'post_status' => 'publish' ), admin_url( 'edit.php' ) ) ),
			sprintf( _n( '%s Product', '%s Products', $product_count->publish, 'incassoos' ), $product_count->publish )
		),
	) );

	?>

	<div class="main">
		<?php if ( ! empty( $statuses ) ) : ?>

		<ul>
			<?php foreach ( $statuses as $status => $label ) : ?>

			<li class="<?php echo esc_attr( $status ); ?>"><?php echo $label; ?></li>

			<?php endforeach; ?>
		</ul>

		<?php else : ?>

		<p class="description"><?php esc_html_e( 'There is currently nothing to display here.', 'incassoos' ); ?></p>

		<?php endif; ?>
	</div>

	<?php
}

/**
 * Output the contents of the History dashboard widget
 *
 * @since 1.0.0
 */
function incassoos_admin_dashboard_history_widget() {

	// Assets
	$collection = incassoos_get_collection_post_type();
	$activity   = incassoos_get_activity_post_type();
	$occasion   = incassoos_get_occasion_post_type();
	$order      = incassoos_get_order_post_type();

	// Counts
	$collection_count = wp_count_posts( $collection );
	$activity_count   = wp_count_posts( $activity   );
	$occasion_count   = wp_count_posts( $occasion   );
	$order_count      = wp_count_posts( $order      );

	// Status
	$collected = incassoos_get_collected_status_id();

	// Collect statuses to display
	$statuses = apply_filters( 'incassoos_admin_dashboard_statuses', array(

		// Collections
		'collection-count' => sprintf( '<a href="%s">%s</a>',
			esc_url( add_query_arg( array( 'post_type' => $collection, 'post_status' => $collected ), admin_url( 'edit.php' ) ) ),
			sprintf( _n( '%s Collection', '%s Collections', $collection_count->{$collected}, 'incassoos' ), $collection_count->{$collected} )
		),

		// Activities
		'activity-count' => sprintf( '<a href="%s">%s</a>',
			esc_url( add_query_arg( array( 'post_type' => $activity, 'post_status' => $collected ), admin_url( 'edit.php' ) ) ),
			sprintf( _n( '%s Activity', '%s Activities', $activity_count->{$collected}, 'incassoos' ), $activity_count->{$collected} )
		),

		// Occasions
		'occasion-count' => sprintf( '<a href="%s">%s</a>',
			esc_url( add_query_arg( array( 'post_type' => $occasion, 'post_status' => $collected ), admin_url( 'edit.php' ) ) ),
			sprintf( _n( '%s Occasion', '%s Occasions', $occasion_count->{$collected}, 'incassoos' ), $occasion_count->{$collected} )
		),

		// Orders
		'order-count' => sprintf( '<a href="%s">%s</a>',
			esc_url( add_query_arg( array( 'post_type' => $order, 'post_status' => $collected ), admin_url( 'edit.php' ) ) ),
			sprintf( _n( '%s Order', '%s Orders', $order_count->{$collected}, 'incassoos' ), $order_count->{$collected} )
		),
	) );

	?>

	<div class="main">
		<?php if ( ! empty( $statuses ) ) : ?>

		<ul>
			<?php foreach ( $statuses as $status => $label ) : ?>

			<li class="<?php echo esc_attr( $status ); ?>"><?php echo $label; ?></li>

			<?php endforeach; ?>
		</ul>

		<?php else : ?>

		<p class="description"><?php esc_html_e( 'There is currently nothing to display here.', 'incassoos' ); ?></p>

		<?php endif; ?>
	</div>

	<?php
}

/**
 * Output the contents of the Quick actions dashboard widget
 *
 * @since 1.0.0
 */
function incassoos_admin_dashboard_actions_widget() {
	$actions = array();

	// Assets
	$assets = array(
		incassoos_get_collection_post_type(),
		incassoos_get_activity_post_type(),
		incassoos_get_occasion_post_type(),
		incassoos_get_order_post_type(),
		incassoos_get_product_post_type()
	);

	// Define actions
	foreach ( $assets as $post_type ) {
		$post_type_object = get_post_type_object( $post_type );

		$actions[ "new-{$post_type}" ] = sprintf( '<a class="button button-secondary" href="%s">%s</a>',
			esc_url( add_query_arg( array( 'post_type' => $post_type ), admin_url( 'post-new.php' ) ) ),
			$post_type_object->labels->add_new
		);
	}

	// Collect actions to display
	$actions = apply_filters( 'incassoos_admin_dashboard_actions', $actions );

	?>

	<div class="main">
		<?php if ( ! empty( $actions ) ) : ?>

			<?php foreach ( $actions as $status => $label ) : ?>

			<span class="<?php echo esc_attr( $status ); ?>"><?php echo $label; ?></span>

			<?php endforeach; ?>

		<?php else : ?>

		<p class="description"><?php esc_html_e( 'There is currently nothing to display here.', 'incassoos' ); ?></p>

		<?php endif; ?>
	</div>

	<?php
}
