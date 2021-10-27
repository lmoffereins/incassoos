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

	// Recent activity
	wp_add_dashboard_widget(
		'incassoos_dashboard_recent',
		_x( 'Recent activity', 'Dashboard widget', 'incassoos' ),
		'incassoos_admin_dashboard_recent_widget'
	);

	// Uncollected items
	wp_add_dashboard_widget(
		'incassoos_dashboard_uncollected',
		_x( 'Uncollected items', 'Dashboard widget', 'incassoos' ),
		'incassoos_admin_dashboard_uncollected_widget'
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

	// Pre-calculate
	$collections = $collection_count->publish + $collection_count->{incassoos_get_staged_status_id()};

	// Collect statuses to display
	$statuses = apply_filters( 'incassoos_admin_dashboard_statuses', array(

		// Collections
		'collection-count' => sprintf( '<a href="%s">%s</a>',
			esc_url( add_query_arg( array( 'post_type' => $collection, 'post_status' => 'publish' ), admin_url( 'edit.php' ) ) ),
			sprintf( _n( '%s Collection', '%s Collections', $collections, 'incassoos' ), $collections )
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
 * Output the contents of the Recent activity dashboard widget
 *
 * @since 1.0.0
 */
function incassoos_admin_dashboard_recent_widget() {
	$recently_collected = $recently_created = false;

	echo '<div class="main">';

	// Recently collected
	if ( current_user_can( 'view_incassoos_collections' ) ) {
		$recently_collected = incassoos_admin_dashboard_recent_posts( array(
			'id'          => 'recently-collected',
			'title'       => esc_html__( 'Recently collected', 'incassoos' ),
			'detail_cb'   => 'incassoos_get_collection_total',
			'detail_args' => array( 0, true ),
			'max'         => 5,
			'status'      => incassoos_get_collected_status_id(),
			'post_type'   => incassoos_get_collection_post_type(),
			'order'       => 'DESC',
			'orderby'     => 'meta_collected',
			'meta_query'  => array(
				'relation'       => 'AND',
				'meta_collected' => array(
					'key'     => 'collected',
					'compare' => 'EXISTS'
				)
			)
		) );
	}

	// Recently created
	$recently_created = incassoos_admin_dashboard_recent_posts( array(
		'id'          => 'recently-created',
		'title'       => esc_html__( 'Recently created', 'incassoos' ),
		'detail_cb'   => 'incassoos_get_post_type_label',
		'detail_args' => array(),
		'max'         => 15,
		'status'      => 'any',
		'post_type'   => array_filter( incassoos_get_plugin_post_types(), 'incassoos_user_can_view_post' ),
		'order'       => 'DESC'
	) );

	if ( ! $recently_collected && ! $recently_created ) {
		echo '<div class="no-activity">';
		echo '<p>' . esc_html__( 'No activity yet!' ) . '</p>';
		echo '</div>';
	}

	echo '</div>';
}

/**
 * Output the contents of the Uncollected items dashboard widget
 *
 * @since 1.0.0
 */
function incassoos_admin_dashboard_uncollected_widget() {
	$recent_collections = $recent_activities = $recent_occasions = false;

	echo '<div class="main">';

	// Collections
	if ( current_user_can( 'view_incassoos_collections' ) ) {
		$recent_collections = incassoos_admin_dashboard_recent_posts( array(
			'id'             => 'recently-collected',
			'title'          => esc_html__( '%d Collections', 'incassoos' ),
			'detail_cb'      => 'incassoos_get_collection_total',
			'detail_args'    => array( 0, true ),
			'count_in_title' => true,
			'max'            => -1,
			'status'         => array( 'publish', incassoos_get_staged_status_id() ),
			'post_type'      => incassoos_get_collection_post_type(),
			'order'          => 'DESC',
		) );
	}

	// Activities
	if ( current_user_can( 'view_incassoos_activities' ) ) {
		$recent_activities = incassoos_admin_dashboard_recent_posts( array(
			'id'             => 'uncollected-activities',
			'title'          => esc_html__( '%d Activities', 'incassoos' ),
			'detail_cb'      => 'incassoos_get_activity_total',
			'detail_args'    => array( 0, true ),
			'count_in_title' => true,
			'max'            => -1,
			'status'         => 'publish',
			'post_type'      => incassoos_get_activity_post_type(),
			'order'          => 'DESC',
		) );
	}

	// Occasions
	if ( current_user_can( 'view_incassoos_occasions' ) ) {
		$recent_occasions = incassoos_admin_dashboard_recent_posts( array(
			'id'             => 'uncollected-occasions',
			'title'          => esc_html__( '%d Occasions', 'incassoos' ),
			'detail_cb'      => 'incassoos_get_occasion_total',
			'detail_args'    => array( 0, true ),
			'count_in_title' => true,
			'max'            => -1,
			'status'         => 'publish',
			'post_type'      => incassoos_get_occasion_post_type(),
			'order'          => 'DESC',
		) );
	}

	if ( ! $recent_collections && ! $recent_activities && ! $recent_occasions ) {
		echo '<div class="no-activity">';
		echo '<p>' . esc_html__( 'No collectable items yet!', 'incassoos' ) . '</p>';
		echo '</div>';
	}

	echo '</div>';
}

/**
 * Generates Publishing Soon and Recently Published sections.
 *
 * @see wp_dashboard_recent_posts()
 *
 * @since 1.0.0
 *
 * @param array $args {
 *     An array of query and display arguments.
 *
 *     @type int    $max     Number of posts to display.
 *     @type string $status  Post status.
 *     @type string $order   Designates ascending ('ASC') or descending ('DESC') order.
 *     @type string $title   Section title.
 *     @type string $id      The container id.
 * }
 * @return bool False if no posts were found. True otherwise.
 */
function incassoos_admin_dashboard_recent_posts( $args ) {
	$query_args = wp_parse_args( $args, array(
		'post_type'      => 'post',
		'post_status'    => $args['status'],
		'orderby'        => 'date',
		'order'          => $args['order'],
		'posts_per_page' => intval( $args['max'] ),
		'no_found_rows'  => true,
		'cache_results'  => false,
		'perm'           => ( 'future' === $args['status'] ) ? 'editable' : 'readable',
	) );

	// Remove non-query args
	unset( $query_args['id'], $query_args['title'] );

	/**
	 * Filters the query arguments used for the Recent Posts widget.
	 *
	 * @since 1.0.0
	 *
	 * @param array $query_args The arguments passed to WP_Query to produce the list of posts.
	 * @param array $args       The initial function arguments.
	 */
	$query_args = apply_filters( 'incassoos_admin_dashboard_recent_posts_query_args', $query_args, $args );
	$posts      = new WP_Query( $query_args );

	if ( $posts->have_posts() ) {

		echo '<div id="' . $args['id'] . '" class="activity-block recent-posts">';

		if ( isset( $args['count_in_title'] ) && ! empty( $args['count_in_title'] ) ) {
			$args['title'] = sprintf( $args['title'], $posts->post_count );
		}

		echo '<h3>' . $args['title'] . '</h3>';

		echo '<ul>';

		$today    = current_time( 'Y-m-d' );
		$tomorrow = current_datetime()->modify( '+1 day' )->format( 'Y-m-d' );
		$year     = current_time( 'Y' );

		while ( $posts->have_posts() ) {
			$posts->the_post();

			$time = get_the_time( 'U' );
			if ( gmdate( 'Y-m-d', $time ) == $today ) {
				$relative = __( 'Today' );
			} elseif ( gmdate( 'Y-m-d', $time ) == $tomorrow ) {
				$relative = __( 'Tomorrow' );
			} elseif ( gmdate( 'Y', $time ) !== $year ) {
				/* translators: Date and time format for recent posts on the dashboard, from a different calendar year, see https://www.php.net/date */
				$relative = date_i18n( __( 'M jS Y' ), $time );
			} else {
				/* translators: Date and time format for recent posts on the dashboard, see https://www.php.net/date */
				$relative = date_i18n( __( 'M jS' ), $time );
			}

			// Use the post edit link for those who can edit, the permalink otherwise.
			$recent_post_link = current_user_can( 'edit_post', get_the_ID() ) ? get_edit_post_link() : get_permalink();

			// Get the post's detail
			$post_detail = isset( $args['detail_cb'] ) && is_callable( $args['detail_cb'] ) ? call_user_func_array( $args['detail_cb'], $args['detail_args'] ) : false;

			$draft_or_post_title = _draft_or_post_title();
			printf( '<li><span class="post-date">%1$s</span> <a class="post-link" href="%2$s" aria-label="%3$s" title="%4$s">%4$s</a> %5$s</li>',
				/* translators: 1: Relative date, 2: Time. */
				sprintf( _x( '%1$s, %2$s', 'dashboard' ), $relative, get_the_time() ),
				$recent_post_link,
				/* translators: %s: Post title. */
				esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;' ), $draft_or_post_title ) ),
				$draft_or_post_title,
				! empty( $post_detail ) ? '<span class="post-detail">' . $post_detail . '</span>' : ''
			);
		}

		echo '</ul>';
		echo '</div>';

	} else {
		return false;
	}

	wp_reset_postdata();

	return true;
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
