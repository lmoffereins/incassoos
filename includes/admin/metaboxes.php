<?php

/**
 * Incassoos Admin Metaboxes
 *
 * @package Incassoos
 * @subpackage Administration
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Register and modify the admin post's metaboxes
 *
 * @since 1.0.0
 *
 * @param string $post_type Post type name
 * @param WP_Post $post Current post object
 */
function incassoos_admin_add_meta_boxes( $post_type, $post ) {

	// Bail when not doing post metaboxes
	if ( ! is_a( $post, 'WP_Post' ) )
		return;

	$post_type_object      = get_post_type_object( $post->post_type );
	$can_publish_or_delete = ( 'post-new.php' === $GLOBALS['pagenow'] ) || current_user_can( $post_type_object->cap->edit_post, $post->ID ) || current_user_can( $post_type_object->cap->delete_post, $post->ID );

	// Collection
	if ( incassoos_get_collection_post_type() === $post_type ) {

		// Replace core's post submit box
		remove_meta_box( 'submitdiv', null, 'side' );
		remove_meta_box( 'slugdiv', null, 'normal' );

		add_meta_box(
			'incassoos_collection_details',
			esc_html__( 'Collection Details', 'incassoos' ),
			'incassoos_admin_collection_details_metabox',
			null,
			'side',
			incassoos_is_collection_locked( $post ) ? 'high' : 'default'
		);

		// Only display submit box for publish or delete actions
		if ( $can_publish_or_delete ) {
			add_meta_box(
				'submitdiv',
				esc_html__( 'Publish' ),
				'incassoos_admin_post_submit_metabox',
				null,
				'side',
				'high'
			);
		}

		add_meta_box(
			'incassoos_collection_activities',
			sprintf(
				/* translators: counter */
				esc_html__( 'Collection Activities %s', 'incassoos' ),
				'<span class="count">(' . incassoos_get_collection_activity_count( $post ) . ')</span>'
			),
			'incassoos_admin_collection_activities_metabox',
			null,
			'side',
			'high'
		);

		add_meta_box(
			'incassoos_collection_occasions',
			sprintf(
				/* translators: counter */
				esc_html__( 'Collection Occasions %s', 'incassoos' ),
				'<span class="count">(' . incassoos_get_collection_occasion_count( $post ) . ')</span>'
			),
			'incassoos_admin_collection_occasions_metabox',
			null,
			'side',
			'high'
		);

		// Display consumers
		if ( incassoos_get_collection_total( $post ) ) {
			add_meta_box(
				'incassoos_collection_consumers',
				sprintf(
					/* translators: counter */
					esc_html__( 'Collection Consumers %s', 'incassoos' ),
					'<span class="count">(' . incassoos_get_collection_consumer_count( $post ) . ')</span>'
				),
				'incassoos_admin_collection_consumers_metabox',
				null,
				'normal',
				'high'
			);
		}
	}

	// Activity
	if ( incassoos_get_activity_post_type() === $post_type ) {
		add_meta_box(
			'incassoos_activity_details',
			esc_html__( 'Activity Details', 'incassoos' ),
			'incassoos_admin_activity_details_metabox',
			null,
			'side',
			'high'
		);

		// Replace core's post submit box
		remove_meta_box( 'submitdiv', null, 'side' );
		remove_meta_box( 'slugdiv', null, 'normal' );

		// Only display submit box for publish or delete actions
		if ( $can_publish_or_delete ) {
			add_meta_box(
				'submitdiv',
				esc_html__( 'Publish' ),
				'incassoos_admin_post_submit_metabox',
				null,
				'side',
				'high'
			);
		}

		add_meta_box(
			'incassoos_activity_participants',
			sprintf(
				/* translators: counter */
				esc_html__( 'Activity Participants %s', 'incassoos' ),
				'<span class="count">(' . incassoos_get_activity_participant_count( $post ) . ')</span>'
			),
			'incassoos_admin_activity_participants_metabox',
			null,
			'normal',
			'high'
		);
	}

	// Occasion
	if ( incassoos_get_occasion_post_type() === $post_type ) {
		add_meta_box(
			'incassoos_occasion_details',
			esc_html__( 'Occasion Details', 'incassoos' ),
			'incassoos_admin_occasion_details_metabox',
			null,
			'side',
			'high'
		);

		// Replace core's post submit box
		remove_meta_box( 'submitdiv', null, 'side' );
		remove_meta_box( 'slugdiv', null, 'normal' );

		// Only display submit box for publish or delete actions
		if ( $can_publish_or_delete ) {
			add_meta_box(
				'submitdiv',
				esc_html__( 'Publish' ),
				'incassoos_admin_post_submit_metabox',
				null,
				'side',
				'high'
			);
		}

		// Display Order consumers and products
		if ( incassoos_get_occasion_order_count( $post ) ) {
			add_meta_box(
				'incassoos_occasion_consumers',
				sprintf(
					/* translators: counter */
					esc_html__( 'Occasion Consumers %s', 'incassoos' ),
					'<span class="count">(' . incassoos_get_occasion_consumer_count( $post ) . ')</span>'
				),
				'incassoos_admin_occasion_consumers_metabox',
				null,
				'normal',
				'high'
			);

			// Products
			add_meta_box(
				'incassoos_occasion_products',
				sprintf(
					/* translators: counter */
					esc_html__( 'Occasion Products %s', 'incassoos' ),
					'<span class="count">(' . incassoos_get_occasion_product_count( $post ) . ')</span>'
				),
				'incassoos_admin_occasion_products_metabox',
				null,
				'side',
				'default'
			);
		}
	}

	// Order
	if ( incassoos_get_order_post_type() === $post_type ) {
		add_meta_box(
			'incassoos_order_details',
			esc_html__( 'Order Details', 'incassoos' ),
			'incassoos_admin_order_details_metabox',
			null,
			'side',
			'high'
		);

		// Replace core's post submit box
		remove_meta_box( 'submitdiv', null, 'side'   );
		remove_meta_box( 'slugdiv',   null, 'normal' );

		// Only display submit box for publish or delete actions
		if ( $can_publish_or_delete ) {
			add_meta_box(
				'submitdiv',
				esc_html__( 'Publish' ),
				'incassoos_admin_post_submit_metabox',
				null,
				'side',
				'high'
			);
		}

		add_meta_box(
			'incassoos_order_products',
			esc_html__( 'Order Products', 'incassoos' ),
			'incassoos_admin_order_products_metabox',
			null,
			'normal',
			'high'
		);
	}

	// Product
	if ( incassoos_get_product_post_type() === $post_type ) {
		add_meta_box(
			'incassoos_product_details',
			esc_html__( 'Product Details', 'incassoos' ),
			'incassoos_admin_product_details_metabox',
			null,
			'side',
			'high'
		);

		// Replace core's post submit box
		remove_meta_box( 'submitdiv', null, 'side'   );
		remove_meta_box( 'slugdiv',   null, 'normal' );

		// Custom submit box
		add_meta_box(
			'submitdiv',
			esc_html__( 'Publish' ),
			'incassoos_admin_post_submit_metabox',
			null,
			'side',
			'high'
		);
	}

	// Notes metabox
	if ( post_type_supports( $post_type, 'incassoos-notes' ) ) {

		// Only when editing or having notes
		if ( $can_publish_or_delete || incassoos_get_post_notes( $post ) ) {
			add_meta_box(
				'incassoos_notes',
				esc_html__( 'Notes', 'incassoos' ),
				'incassoos_admin_notes_metabox',
				null,
				'side',
				'low'
			);
		}
	}
}

/**
 * Return whether the current user can save the metabox
 *
 * @since 1.0.0
 *
 * @param  int|WP_Post $post Optional. Post object or ID. Defaults to the current post.
 * @param  string $post_type Optional. Post type to check against.
 * @return bool Metabox can be saved.
 */
function incassoos_admin_save_metabox_check( $post = 0, $post_type = '' ) {

	// Bail when doing an autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
		return false;

	// Bail when not a post request
	if ( 'POST' != strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return false;

	$post = get_post( $post );

	// Bail when the post type does not match
	if ( $post_type && $post_type !== $post->post_type )
		return false;

	// Get post type object
	$post_type_object = get_post_type_object( $post->post_type );

	// Bail when current user is not capable
	if ( ! current_user_can( $post_type_object->cap->edit_post, $post->ID ) )
		return false;

	return true;
}

/** General *************************************************************/

/**
 * Output the contents of the Order post submit metabox
 *
 * @see post_submit_meta_box()
 *
 * @since 1.0.0
 *
 * @param WP_Post $post Current post object
 */
function incassoos_admin_post_submit_metabox( $post ) {

	// Get details
	$post_type_object = get_post_type_object( $post->post_type );
	$is_post_view     = incassoos_admin_is_post_view( $post );

	?>

	<div class="submitbox" id="submitpost">
		<div id="major-publishing-actions">
			<div id="delete-action">
				<?php
				if ( current_user_can( $post_type_object->cap->delete_post, $post->ID ) ) {
					if ( ! EMPTY_TRASH_DAYS ) {
						$delete_text = __( 'Delete Permanently' );
					} else {
						$delete_text = __( 'Move to Trash' );
					}
					?>
				<a class="submitdelete deletion" href="<?php echo get_delete_post_link( $post->ID ); ?>"><?php echo $delete_text; ?></a><?php
				} ?>
			</div>

			<div id="publishing-action">
				<span class="spinner"></span>
				<?php
				if ( ! in_array( $post->post_status, array( 'publish', 'future', 'private' ) ) || 0 == $post->ID ) {
					if ( current_user_can( $post_type_object->cap->publish_posts ) ) : ?>
						<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e( 'Publish' ); ?>" />
						<?php submit_button( __( 'Save' ), 'primary large', 'publish', false ); ?>
					<?php endif;
				} elseif ( ! $is_post_view ) { ?>
						<input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Update') ?>" />
						<input name="save" type="submit" class="button button-primary button-large" id="publish" value="<?php esc_attr_e( 'Update' ) ?>" />
				<?php
				} ?>
			</div>
			<div class="clear"></div>
		</div>
	</div>

	<?php
}

/**
 * Display the post title for the `view` admin post action
 *
 * @since 1.0.0
 *
 * @param  WP_Post $post Post object.
 */
function incassoos_admin_view_form_after_title( $post ) { ?>

	<div id="titlediv">
		<div id="titlewrap">
			<h2 id="title"><?php the_title(); ?></h2>
		</div>
	</div>

	<?php
}

/**
 * Display the post content for the `view` admin post action
 *
 * @see do_meta_boxes()
 *
 * @since 1.0.0
 *
 * @param  WP_Post $post Post object.
 */
function incassoos_admin_view_form_after_editor( $post ) {
	$widget_title   = _x( 'Content', 'Post metabox title', 'incassoos' );
	$widget_content = apply_filters( 'the_content', $post->post_content );

	// Bail when there is no content
	if ( ! $widget_content )
		return;

	?>

	<div class="meta-box-sortables">
		<div id="postdivrich" class="postbox">
			<?php
				echo '<button type="button" class="handlediv" aria-expanded="true">';
				echo '<span class="screen-reader-text">' . sprintf( __( 'Toggle panel: %s' ), $widget_title ) . '</span>';
				echo '<span class="toggle-indicator" aria-hidden="true"></span>';
				echo '</button>';

				echo "<h2 class='hndle'><span>{$widget_title}</span></h2>\n";
				echo '<div class="inside">' . "\n";
				echo $widget_content;
				echo "</div>\n";
			?>
		</div>
	</div>

	<?php
}

/** Collection **********************************************************/

/**
 * Output the contents of the Collection Details metabox
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'incassoos_collection_details_metabox'
 *
 * @param WP_Post $post Current post object
 */
function incassoos_admin_collection_details_metabox( $post ) {

	// Get details
	$is_post_view     = incassoos_admin_is_post_view( $post );
	$post_type_object = get_post_type_object( $post->post_type );

	// Permissions
	$can_stage   = current_user_can( 'stage_incassoos_collection',   $post->ID );
	$can_unstage = current_user_can( 'unstage_incassoos_collection', $post->ID );

	// Collecting action urls
	$base_url    = add_query_arg( array( 'post' => $post->ID ), admin_url( 'post.php' ) );
	$stage_url   = wp_nonce_url( add_query_arg( array( 'action' => 'inc_stage'   ), $base_url ), 'stage-collection_'   . $post->ID );
	$unstage_url = wp_nonce_url( add_query_arg( array( 'action' => 'inc_unstage' ), $base_url ), 'unstage-collection_' . $post->ID );
	$collect_url = wp_nonce_url( add_query_arg( array( 'action' => 'inc_collect' ), $base_url ), 'collect-collection_' . $post->ID );

	// Export
	$export_types = incassoos_get_export_types();
	$can_export   = current_user_can( 'export_incassoos_collection', $post->ID ) && ! empty( $export_types );

	?>

	<div class="incassoos-object-details">

		<p>
			<label><?php esc_html_e( 'Created:', 'incassoos' ); ?></label>
			<span id="collection-created" class="value"><?php incassoos_the_collection_created( $post ); ?></span>
		</p>

		<?php if ( ! $can_stage ) : ?>

		<p>
			<label><?php esc_html_e( 'Total:', 'incassoos' ); ?></label>
			<span id="collection-total" class="value"><?php incassoos_the_collection_total( $post, true ); ?></span>
		</p>

		<?php endif; ?>

		<?php if ( incassoos_is_collection_locked( $post ) ) : ?>

		<p>
			<label><?php
			if ( incassoos_is_collection_staged() ) {
				esc_html_e( 'Staged:', 'incassoos' );
			} else {
				esc_html_e( 'Collected:', 'incassoos' );
			} ?></label>
			<span id="collection-date" class="value"><?php
			if ( incassoos_is_collection_staged() ) {
				incassoos_the_collection_staged( $post );
			} else {
				incassoos_the_collection_date( $post );
			} ?></span>
		</p>

		<?php endif; ?>

		<p>
			<label><?php esc_html_e( 'Author:', 'incassoos' ); ?></label>
			<span id="collection-author" class="value"><?php incassoos_the_collection_author( $post ); ?></span>
		</p>

		<?php do_action( 'incassoos_collection_details_metabox', $post ); ?>

		<?php if ( ! incassoos_is_collection_locked( $post ) ) : ?>

		<p class="warning">
			<?php if ( ! current_user_can( $post_type_object->cap->edit_posts ) ) {
				esc_html_e( 'This Collection is not collected yet.', 'incassoos' );
			} else {
				esc_html_e( 'Please publish any changes first, before staging the Collection.', 'incassoos' );
			} ?>
		</p>

		<?php endif; ?>

	</div>

	<?php if ( $can_stage || $can_unstage || $can_export ) : ?>

	<div id="major-publishing-actions">
		<?php if ( $can_stage ) : ?>

		<div class="publishing-notice">
			<label><?php esc_html_e( 'Total', 'incassoos' ); ?></label>
			<span id="collection-total">
				<span class="value" title="<?php echo esc_attr( incassoos_get_collection_total( $post, true ) ); ?>"><?php incassoos_the_collection_total( $post, true ); ?></span>
			</span>
		</div>

		<?php elseif ( $can_export ) : ?>

		<div class="publishing-notice">
			<label class="screen-reader-text" for="collection-export-type"><?php esc_html_e( 'Select collection export type', 'incassoos' ); ?></label>
			<select id="collection-export-type" name="export-type">
				<option value=""><?php esc_html_e( '&mdash; Export &mdash;', 'incassoos' ); ?></option>
				<?php foreach ( $export_types as $type ) : ?>

				<option value="<?php echo esc_attr( $type ); ?>"><?php incassoos_the_export_type_title( $type ); ?></option>

				<?php endforeach; ?>
			</select>
		</div>

		<?php endif; ?>

		<div id="publishing-action">
			<span class="spinner"></span>
			<?php if ( $can_stage ) : ?>
				<a class="button button-primary button-large" id="stage-collection" href="<?php echo esc_url( $stage_url ); ?>"><?php esc_html_e( 'Stage', 'incassoos' ); ?></a>
			<?php elseif ( $can_unstage ) : ?>
				<a class="button button-secondary button-large" id="unstage-collection" href="<?php echo esc_url( $unstage_url ); ?>"><?php esc_html_e( 'Unstage', 'incassoos' ); ?></a>
			<?php endif; ?>
			<?php if ( current_user_can( 'collect_incassoos_collection', $post->ID ) ) : ?>
				<a class="button button-primary button-large" id="collect-collection" href="<?php echo esc_url( $collect_url ); ?>"><?php esc_html_e( 'Submit', 'incassoos' ); ?></a>
			<?php elseif ( $can_export ) : ?>
				<?php wp_nonce_field( 'export_collection-' . $post->ID, 'collection_export_nonce' ); ?>
				<input type="hidden" name="action" value="inc_export" />
				<input type="submit" class="button button-secondary button-large" id="export-collection" name="export-collection" value="<?php esc_attr_e( 'Export', 'incassoos' ); ?>" />
			<?php endif; ?>
		</div>
		<div class="clear"></div>
	</div>

	<?php endif; ?>

	<?php wp_nonce_field( 'collection_details_metabox', 'collection_details_metabox_nonce' ); ?>

	<?php
}

/**
 * Output the contents of the Collection Activities metabox
 *
 * @since 1.0.0
 *
 * @param WP_Post $post Current post object
 */
function incassoos_admin_collection_activities_metabox( $post ) {

	// Get collection assets
	$is_post_view = incassoos_admin_is_post_view( $post );
	$cactivities  = incassoos_get_collection_activities( $post );

	if ( $is_post_view ) {
		$activities = $cactivities;
	} else {
		$activities = incassoos_get_uncollected_activities( array( 'incassoos_empty' => false ) );
	}

	?>

	<div class="incassoos-item-list">
		<?php if ( $activities ) : ?>

		<ul class="assets">
			<?php foreach ( $activities as $item_id ) : ?>

			<li id="post-<?php echo $item_id; ?>" class="asset collection-activity">
				<?php if ( ! $is_post_view ) : ?>

				<input id="collection-activity-<?php echo $item_id; ?>" type="checkbox" value="<?php echo $item_id; ?>" name="collection-activity[]" class="select-activity" <?php checked( in_array( $item_id, $cactivities ) ); ?> />
				<label for="collection-activity-<?php echo $item_id; ?>">
					<span class="title"><?php incassoos_the_activity_title( $item_id ); ?></span>

					<?php if ( incassoos_get_activity_date( $item_id ) ) : ?>
						<span class="item-date"><?php incassoos_the_activity_date( $item_id ); ?></span>
					<?php endif; ?>

					<div class="details">
						<span class="activity-participant-count"><?php incassoos_the_activity_participant_count( $item_id ); ?></span>
						<span class="activity-total"><?php incassoos_the_activity_total( $item_id, true ); ?></span>
						<span class="view-action">
							<a target="_blank" href="<?php echo esc_url( incassoos_get_activity_url( $item_id ) ); ?>"><?php esc_html_e( 'View' ); ?>
						</a></span>
					</div>
				</label>

				<?php else : ?>

				<span class="title"><?php incassoos_the_activity_link( $item_id ); ?></span>

				<?php if ( incassoos_get_activity_date( $item_id ) ) : ?>
					<span class="item-date"><?php incassoos_the_activity_date( $item_id ); ?></span>
				<?php endif; ?>

				<div class="details">
					<span class="activity-participant-count"><?php incassoos_the_activity_participant_count( $item_id ); ?></span>
					<span class="activity-total"><?php incassoos_the_activity_total( $item_id, true ); ?></span>
				</div>

				<?php endif; ?>
			</li>

			<?php endforeach; ?>

		</ul>

		<?php else : ?>

		<p><?php esc_html_e( 'There are no collectable activities found.', 'incassoos' ); ?></p>

		<?php endif; ?>
	</div>

	<?php wp_nonce_field( 'collection_activities_metabox', 'collection_activities_metabox_nonce' ); ?>

	<?php
}

/**
 * Output the contents of the Collection Occasions metabox
 *
 * @since 1.0.0
 *
 * @param WP_Post $post Current post object
 */
function incassoos_admin_collection_occasions_metabox( $post ) {

	// Get collection assets
	$is_post_view = incassoos_admin_is_post_view( $post );
	$coccasions   = incassoos_get_collection_occasions( $post );

	if ( $is_post_view ) {
		$occasions = $coccasions;
	} else {
		$occasions = incassoos_get_uncollected_occasions( array( 'incassoos_empty' => false ) );
	}

	?>

	<div class="incassoos-item-list">
		<?php if ( $occasions ) : ?>

		<ul class="assets">
			<?php foreach ( $occasions as $item_id ) : ?>

			<li id="post-<?php echo $item_id; ?>" class="asset collection-occasion">
				<?php if ( ! $is_post_view ) : ?>

				<input id="collection-occasion-<?php echo $item_id; ?>" type="checkbox" value="<?php echo $item_id; ?>" name="collection-occasion[]" class="select-occasion" <?php checked( in_array( $item_id, $coccasions ) ); ?> />
				<label for="collection-occasion-<?php echo $item_id; ?>">
					<span class="title"><?php incassoos_the_occasion_title( $item_id ); ?></span>

					<?php if ( incassoos_get_occasion_date( $item_id ) ) : ?>
						<span class="item-date"><?php incassoos_the_occasion_date( $item_id ); ?></span>
					<?php endif; ?>

					<div class="details">
						<span class="occasion-order-count"><?php incassoos_the_occasion_order_count( $item_id ); ?></span>
						<span class="occasion-total"><?php incassoos_the_occasion_total( $item_id, true ); ?></span>
						<span class="view-action">
							<a target="_blank" href="<?php echo esc_url( incassoos_get_occasion_url( $item_id ) ); ?>"><?php esc_html_e( 'View' ); ?></a>
						</span>
					</div>
				</label>

				<?php else : ?>

				<span class="title"><?php incassoos_the_occasion_link( $item_id ); ?></span>

				<?php if ( incassoos_get_occasion_date( $item_id ) ) : ?>
					<span class="item-date"><?php incassoos_the_occasion_date( $item_id ); ?></span>
				<?php endif; ?>

				<div class="details">
					<span class="occasion-order-count"><?php incassoos_the_occasion_order_count( $item_id ); ?></span>
					<span class="occasion-total"><?php incassoos_the_occasion_total( $item_id, true ); ?></span>
				</div>

				<?php endif; ?>
			</li>

			<?php endforeach; ?>
		</ul>

		<?php else : ?>

		<p><?php esc_html_e( 'There are no collectable occasions found.', 'incassoos' ); ?></p>

		<?php endif; ?>
	</div>

	<?php wp_nonce_field( 'collection_occasions_metabox', 'collection_occasions_metabox_nonce' ); ?>

	<?php
}

/**
 * Output the contents of the Collection Consumers metabox
 *
 * @since 1.0.0
 *
 * @param WP_Post $post Current post object
 */
function incassoos_admin_collection_consumers_metabox( $post ) {

	// Get details
	$consumers      = incassoos_get_collection_consumer_users( $post );
	$consumer_types = incassoos_get_collection_consumer_types( $post );

	?>

	<div class="incassoos-item-list">
		<div id="select-matches" class="hide-if-no-js">
			<label for="consumer-search" class="screen-reader-text"><?php esc_html_e( 'Search consumers', 'incassoos' ); ?></label>
			<input type="search" id="consumer-search" placeholder="<?php esc_attr_e( 'Search consumers&hellip;', 'incassoos' ); ?>" />

			<button type="button" id="reverse-group-order" class="button-link" title="<?php esc_attr_e( 'Reverse group order', 'incassoos' ); ?>">
				<span class="screen-reader-text"><?php esc_html_e( 'Reverse group order', 'incassoos' ); ?></span>
			</button>
			<button type="button" id="toggle-list-columns" class="button-link" title="<?php esc_attr_e( 'Toggle list columns', 'incassoos' ); ?>">
				<span class="screen-reader-text"><?php esc_html_e( 'Toggle list columns', 'incassoos' ); ?></span>
			</button>
		</div>

		<ul class="sublist list-columns groups">
			<?php foreach ( incassoos_group_users( $consumers ) as $group ) : ?>

			<li id="group-<?php echo $group->id; ?>" class="group">
				<h4 class="sublist-header"><?php echo esc_html( $group->name ); ?></h4>

				<ul class="users">
					<?php foreach ( $group->users as $user ) : ?>

					<li id="user-<?php echo $user->ID; ?>" class="consumer collection-consumer selector">
						<button type="button" class="button-link open-details">
							<span class="consumer-name title"><?php echo $user->display_name; ?></span>
							<span class="consumer-total total"><?php incassoos_the_collection_consumer_total( $user->ID, $post, true ); ?></span>
						</button>

						<ul class="item-details">
							<?php foreach ( incassoos_get_collection_consumer_assets( $user->ID, $post ) as $item_id ) : ?>

							<li>
								<span class="title"><?php incassoos_the_asset_link( $item_id ); ?></span>
								<span class="total"><?php incassoos_the_asset_consumer_total( $user->ID, $item_id, true ); ?></span>
							</li>

							<?php endforeach; ?>
						</ul>
					</li>

					<?php endforeach; ?>
				</ul>
			</li>

			<?php endforeach; ?>

			<?php if ( $consumer_types ) : ?>

			<li class="group">
				<h4 class="sublist-header"><?php esc_html_e( 'Consumer Types', 'incassoos' ); ?></h4>
				<ul class="users">
					<?php foreach ( $consumer_types as $consumer_type ) : ?>

					<li id="type-<?php echo esc_attr( $consumer_type ); ?>" class="consumer collection-consumer-type selector">
						<button type="button" class="button-link open-details">
							<span class="consumer-name title"><?php incassoos_the_consumer_type_title( $consumer_type ); ?></span>
							<span class="total"><?php incassoos_the_collection_consumer_total( $consumer_type, $post, true ); ?></span>
						</button>

						<ul class="item-details">
							<?php foreach ( incassoos_get_collection_consumer_assets( $consumer_type, $post ) as $item_id ) : ?>

							<li>
								<span class="title"><?php incassoos_the_asset_link( $item_id ); ?></span>
								<span class="total"><?php incassoos_the_asset_consumer_total( $consumer_type, $item_id, true ); ?></span>
							</li>

							<?php endforeach; ?>
						</ul>
					</li>

					<?php endforeach; ?>
				</ul>
			</li>

			<?php endif; ?>
		</ul>
	</div>

	<?php
}

/** Activity ************************************************************/

/**
 * Output the contents of the Activity Details metabox
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'incassoos_activity_details_metabox'
 *
 * @param WP_Post $post Current post object
 */
function incassoos_admin_activity_details_metabox( $post ) {

	// Get details
	$is_post_view     = incassoos_admin_is_post_view( $post );
	$activity_cat_tax = incassoos_get_activity_cat_tax_id();
	$format_args      = incassoos_get_currency_format_args();

	?>

	<div class="incassoos-object-details">

		<p>
			<label><?php esc_html_e( 'Created:', 'incassoos' ); ?></label>
			<span id="activity-created" class="value"><?php incassoos_the_activity_created( $post ); ?></span>
		</p>

		<?php if ( ! $is_post_view || incassoos_get_activity_date( $post ) ) : ?>

		<p>
			<label for="activity-date"><?php esc_html_e( 'Date:', 'incassoos' ); ?></label>

			<?php if ( ! $is_post_view ) : ?>
				<input type="text" name="activity-date" id="activity-date" value="<?php echo esc_attr( mysql2date( 'd-m-Y', get_post_meta( $post->ID, 'activity_date', true ) ) ); ?>" class="datepicker" />
			<?php else : ?>
				<span id="activity-date" class="value"><?php incassoos_the_activity_date( $post ); ?></span>
			<?php endif; ?>
		</p>

		<?php endif; ?>

		<p>
			<label for="price"><?php esc_html_e( 'Price:', 'incassoos' ); ?></label>

			<?php if ( ! $is_post_view ) : ?>
				<input type="number" name="price" id="price" step="0.01" min="0" value="<?php echo esc_attr( number_format( get_post_meta( $post->ID, 'price', true ), absint( $format_args['decimals'] ) ) ); ?>" />
			<?php else : ?>
				<span id="price" class="value"><?php incassoos_the_activity_price( $post, true ); ?></span>
			<?php endif; ?>
		</p>

		<?php if ( ! $is_post_view || incassoos_activity_has_category( $post ) ) : ?>

		<p>
			<label for="taxonomy-<?php echo $activity_cat_tax; ?>"><?php esc_html_e( 'Category:', 'incassoos' ); ?></label>

			<?php if ( ! $is_post_view ) :
				$cat_terms = wp_get_object_terms( $post->ID, $activity_cat_tax, array( 'fields' => 'ids' ) );

				wp_dropdown_categories( array(
					'name'             => "taxonomy-{$activity_cat_tax}",
					'taxonomy'         => $activity_cat_tax,
					'hide_empty'       => false,
					'selected'         => $cat_terms ? $cat_terms[0] : 0,
					'show_option_none' => esc_html__( '&mdash; No Category &mdash;', 'incassoos' ),
				) );

			else : ?>

			<span id="taxonomy-<?php echo $activity_cat_tax; ?>" class="value"><?php incassoos_the_activity_category( $post ); ?></span>

			<?php endif; ?>
		</p>

		<?php endif; ?>

		<?php if ( $is_post_view ) : ?>

		<p>
			<label><?php esc_html_e( 'Count:', 'incassoos' ); ?></label>
			<span id="activity-participant-count">
				<span class="value"><?php incassoos_the_activity_participant_count( $post ); ?></span>
			</span>
		</p>

		<?php endif; ?>

		<p>
			<label><?php esc_html_e( 'Total:', 'incassoos' ); ?></label>
			<span id="activity-total">
				<?php if ( 'post-new.php' !== $GLOBALS['pagenow'] ) : ?>
					<span class="value"><?php incassoos_the_activity_total( $post, true ); ?></span>
				<?php endif; ?>
			</span>
		</p>

		<p>
			<label><?php esc_html_e( 'Author:', 'incassoos' ); ?></label>
			<span id="activity-author" class="value"><?php incassoos_the_activity_author( $post ); ?></span>
		</p>

		<?php if ( $is_post_view ) : ?>

		<p>
			<label><?php esc_html_e( 'Collection:', 'incassoos' ); ?></label>
			<span id="activity-collection" class="value" title="<?php printf( esc_attr__( 'Collected on %s', 'incassoos' ), incassoos_get_activity_collection_date( $post ) ); ?>"><?php incassoos_the_activity_collection_link( $post ); ?></span>
		</p>

		<?php endif; ?>

		<?php do_action( 'incassoos_activity_details_metabox', $post ); ?>

	</div>

	<?php wp_nonce_field( 'activity_details_metabox', 'activity_details_metabox_nonce' ); ?>

	<?php
}

/**
 * Output the contents of the Activity Participants metabox
 *
 * @since 1.0.0
 *
 * @param WP_Post $post Current post object
 */
function incassoos_admin_activity_participants_metabox( $post ) {

	// Get details
	$is_post_view = incassoos_admin_is_post_view( $post );
	$participants = incassoos_get_activity_participants( $post );
	$users        = incassoos_get_users( $is_post_view ? array( 'include' => $participants ) : array() );

	// Price, without currency
	$format_args  = incassoos_get_currency_format_args();
	$price        = number_format( (float) get_post_meta( $post->ID, 'price', true ), absint( $format_args['decimals'] ) );

	if ( ! $prices = get_post_meta( $post->ID, 'prices', true ) ) {
		$prices = array();
	}

	// List item class
	$item_class = array( 'consumer', 'activity-participant' );
	if ( ! $is_post_view ) {
		$item_class[] = 'selector';
	}

	?>

	<div class="incassoos-item-list">
		<div id="select-matches" class="hide-if-no-js">
			<?php if ( ! $is_post_view ) :

				// Get match options
				$matches     = incassoos_get_user_match_options();
				$selectors   = array_filter( $matches, function( $g ) { return ( false === strpos( $g, '_' ) ); }, ARRAY_FILTER_USE_KEY ); // PHP 5.6+
				$deselectors = array_diff_key( $matches, $selectors );

			?>

			<label for="participant-quick-select" class="screen-reader-text"><?php esc_html_e( 'Quick select participants', 'incassoos' ); ?></label>
			<select id="participant-quick-select">
				<option value="-1"><?php esc_html_e( '&mdash; Quick select &mdash;', 'incassoos' ); ?></option>

				<?php if ( $selectors && $deselectors ) : ?>

				<optgroup label="<?php esc_attr_e( 'Selecting', 'incassoos' ); ?>">
					<?php foreach ( $selectors as $match_id => $label ) : ?>
						<option value="<?php echo esc_attr( $match_id ); ?>"><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</optgroup>

				<optgroup label="<?php esc_attr_e( 'Deselecting', 'incassoos' ); ?>">
					<?php foreach ( $deselectors as $match_id => $label ) : ?>
						<option value="<?php echo esc_attr( $match_id ); ?>"><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</optgroup>

				<?php else : ?>

				<?php foreach ( $matches as $match_id => $label ) : ?>

				<option value="<?php echo esc_attr( $match_id ); ?>"><?php echo esc_html( $label ); ?></option>

				<?php endforeach; ?>

				<?php endif; ?>
			</select>

			<?php endif; ?>

			<label for="consumer-search" class="screen-reader-text"><?php esc_html_e( 'Search participants', 'incassoos' ); ?></label>
			<input type="search" id="consumer-search" placeholder="<?php esc_attr_e( 'Search participants&hellip;', 'incassoos' ); ?>" />

			<?php if ( ! $is_post_view ) : ?>

			<button type="button" id="show-selected" class="button-link"><?php esc_html_e( 'Show selected', 'incassoos' ); ?></button>

			<?php endif; ?>

			<button type="button" id="reverse-group-order" class="button-link" title="<?php esc_attr_e( 'Reverse group order', 'incassoos' ); ?>">
				<span class="screen-reader-text"><?php esc_html_e( 'Reverse group order', 'incassoos' ); ?></span>
			</button>
			<button type="button" id="toggle-list-columns" class="button-link" title="<?php esc_attr_e( 'Toggle list columns', 'incassoos' ); ?>">
				<span class="screen-reader-text"><?php esc_html_e( 'Toggle list columns', 'incassoos' ); ?></span>
			</button>
		</div>

		<ul class="sublist list-columns groups">
			<?php foreach ( incassoos_group_users( $users ) as $group ) : ?>

			<li id="group-<?php echo $group->id; ?>" class="group">
				<h4 class="sublist-header item-content">
					<?php if ( ! $is_post_view ) : ?>
					
					<button type="button" class="button-link title select-group-users" id="select-group-<?php echo $group->id; ?>" title="<?php esc_attr_e( 'Select or deselect all users in the group', 'incassoos' ); ?>"><?php echo esc_html( $group->name ); ?></button>

					<?php else : ?>

					<?php echo esc_html( $group->name ); ?>

					<?php endif; ?>
				</h4>
				<ul class="users">

					<?php

					foreach ( $group->users as $user ) :
						$_item_class = $item_class;
						$has_custom_price = false;

						if ( incassoos_activity_participant_has_custom_price( $user->ID, $post ) ) {
							$_item_class[] = 'has-custom-price';
							$has_custom_price = true;
						}

					?>

					<li id="user-<?php echo $user->ID; ?>" class="<?php echo implode( ' ', $_item_class ); ?>">
						<?php if ( ! $is_post_view ) : ?>

						<div class="item-content">
							<input type="checkbox" name="activity-participant[]" id="participant-<?php echo $user->ID; ?>" class="select-user" value="<?php echo $user->ID; ?>" <?php checked( in_array( $user->ID, $participants ) ); ?> data-matches="<?php incassoos_the_user_match_ids_list( $user->ID ); ?>" />
							<label for="participant-<?php echo $user->ID; ?>" class="consumer-name title"><?php echo $user->display_name; ?></label>

							<span class="price-input">
								<input type="number" name="participant-price[<?php echo $user->ID; ?>]" class="custom-price" step="0.01" min="0" value="<?php if ( $has_custom_price ) { echo esc_attr( number_format( $prices[ $user->ID ], absint( $format_args['decimals'] ) ) ); } ?>" placeholder="<?php echo esc_attr( $price ); ?>" <?php if ( ! $has_custom_price ) { echo 'disabled="disabled"'; } ?> />
								<button type="button" class="button-link toggle-custom-price cancel-custom-price" title="<?php esc_attr_e( 'Cancel the custom price', 'incassoos' ); ?>">
									<span class="screen-reader-text"><?php esc_html_e( 'Cancel the custom price', 'incassoos' ); ?></span>
								</button>
								<button type="button" class="button-link toggle-custom-price set-custom-price" title="<?php esc_attr_e( 'Set a custom price', 'incassoos' ); ?>">
									<span class="screen-reader-text"><?php esc_html_e( 'Set a custom price', 'incassoos' ); ?></span>
								</button>
							</span>
						</div>

						<?php else : ?>

						<div class="item-content">
							<span class="consumer-name title"><?php echo $user->display_name; ?></span>
							<span class="price"><?php incassoos_the_activity_participant_price( $user->ID, $post, true ); ?></span>
						</div>

						<?php endif; ?>
					</li>

					<?php endforeach; ?>
				</ul>
			</li>

			<?php endforeach; ?>
		</ul>
	</div>

	<?php wp_nonce_field( 'activity_participants_metabox', 'activity_participants_metabox_nonce' ); ?>

	<?php
}

/** Occasion ************************************************************/

/**
 * Output the contents of the Occasion Details metabox
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'incassoos_occasion_details_metabox'
 *
 * @param WP_Post $post Current post object
 */
function incassoos_admin_occasion_details_metabox( $post ) {

	// Get details
	$is_post_view      = incassoos_admin_is_post_view( $post );
	$occasion_type_tax = incassoos_get_occasion_type_tax_id();

	// Permissions
	$can_close  = current_user_can( 'close_incassoos_occasion',  $post->ID );
	$can_reopen = current_user_can( 'reopen_incassoos_occasion', $post->ID );

	// Closing action urls
	$base_url   = add_query_arg( array( 'post' => $post->ID ), admin_url( 'post.php' ) );
	$close_url  = wp_nonce_url( add_query_arg( array( 'action' => 'inc_close'  ), $base_url ), 'close-occasion_'  . $post->ID );
	$reopen_url = wp_nonce_url( add_query_arg( array( 'action' => 'inc_reopen' ), $base_url ), 'reopen-occasion_' . $post->ID );

	?>

	<div class="incassoos-object-details">

		<?php if ( ! incassoos_is_occasion_same_date_created( $post ) ) : ?>

		<p>
			<label><?php esc_html_e( 'Created:', 'incassoos' ); ?></label>
			<span id="occasion-created" class="value"><?php incassoos_the_occasion_created( $post ); ?></span>
		</p>

		<?php endif; ?>

		<p>
			<label for="occasion-date"><?php esc_html_e( 'Date:', 'incassoos' ); ?></label>

			<?php if ( ! $is_post_view ) : ?>
				<input type="text" name="occasion-date" id="occasion-date" value="<?php echo esc_attr( mysql2date( 'd-m-Y', get_post_meta( $post->ID, 'occasion_date', true ) ) ); ?>" class="datepicker" />
			<?php else : ?>
				<span id="occasion-date" class="value"><?php incassoos_the_occasion_date( $post ); ?></span>
			<?php endif; ?>
		</p>

		<?php if ( ! $is_post_view || incassoos_occasion_has_type( $post ) ) : ?>

		<p>
			<label for="taxonomy-<?php echo $occasion_type_tax; ?>"><?php esc_html_e( 'Type:', 'incassoos' ); ?></label>

			<?php if ( ! $is_post_view ) :
				$type_terms = wp_get_object_terms( $post->ID, $occasion_type_tax, array( 'fields' => 'ids' ) );

				wp_dropdown_categories( array(
					'name'             => "taxonomy-{$occasion_type_tax}",
					'taxonomy'         => $occasion_type_tax,
					'hide_empty'       => false,
					'selected'         => $type_terms ? $type_terms[0] : incassoos_get_default_occasion_type(),
					'show_option_none' => esc_html__( '&mdash; No Type &mdash;', 'incassoos' ),
				) );

			else : ?>

			<span id="taxonomy-<?php echo $occasion_type_tax; ?>" class="value"><?php incassoos_the_occasion_type( $post ); ?></span>

			<?php endif; ?>
		</p>

		<?php endif; ?>

		<p>
			<label><?php esc_html_e( 'Content:', 'incassoos' ); ?></label>
			<span class="value">
				<span id="occasion-consumers">
					<?php
						$count = incassoos_get_occasion_consumer_count( $post );
						printf( _n( '%d Consumer', '%d Consumers', $count, 'incassoos' ), $count );
					?>
				</span>
				<span id="occasion-orders">
					<?php
						$count = incassoos_get_occasion_order_count( $post );
						printf( '<a href="%s">%s</a>',
							esc_url( add_query_arg( array(
								'post_type' => incassoos_get_order_post_type(),
								'occasion'   => $post->ID
							), admin_url( 'edit.php' ) ) ),
							sprintf( _n( '%d Orders', '%d Orders', $count, 'incassoos' ), $count )
						);
					?>
				</span>
			</span>
		</p>

		<p>
			<label><?php esc_html_e( 'Total:', 'incassoos' ); ?></label>
			<span id="occasion-total" class="value"><?php incassoos_the_occasion_total( $post, true ); ?></span>
		</p>

		<p>
			<label><?php esc_html_e( 'Author:', 'incassoos' ); ?></label>
			<span id="occasion-author" class="value"><?php incassoos_the_occasion_author( $post ); ?></span>
		</p>

		<?php if ( $is_post_view ) : ?>

		<p>
			<label><?php esc_html_e( 'Closed:', 'incassoos' ); ?></label>
			<span id="occasion-closed" class="value"><?php incassoos_the_occasion_closed_date( $post, 'Y-m-d H:i:s' ); ?></span>
		</p>

		<p>
			<label><?php esc_html_e( 'Collection:', 'incassoos' ); ?></label>
			<span id="occasion-collection" class="value" title="<?php printf( esc_attr__( 'Collected on %s', 'incassoos' ), incassoos_get_occasion_collection_date( $post ) ); ?>"><?php incassoos_the_occasion_collection_link( $post ); ?></span>
		</p>

		<?php endif; ?>

		<?php do_action( 'incassoos_occasion_details_metabox', $post ); ?>

	</div>

	<?php if ( $can_close || $can_reopen ) : ?>

	<div id="major-publishing-actions">
		<?php if ( $can_reopen ) : ?>

		<div class="publishing-notice">
			<label title="<?php printf( esc_attr__( 'The occasion was closed on %1$s at %2$s.', 'incassoos' ), incassoos_get_occasion_closed_date( $post ), incassoos_get_occasion_closed_date( $post, get_option( 'time_format' ) ) ); ?>"><?php esc_html_e( 'Closed for new orders.', 'incassoos' ); ?></label>
		</div>

		<?php endif; ?>

		<div id="publishing-action">
			<span class="spinner"></span>
			<?php if ( $can_close ) : ?>
				<a class="button button-primary button-large" id="close-occasion" href="<?php echo esc_url( $close_url ); ?>"><?php esc_html_e( 'Close', 'incassoos' ); ?></a>
			<?php elseif ( $can_reopen ) : ?>
				<a class="button button-secondary button-large" id="reopen-occasion" href="<?php echo esc_url( $reopen_url ); ?>"><?php esc_html_e( 'Reopen', 'incassoos' ); ?></a>
			<?php endif; ?>
		</div>
		<div class="clear"></div>
	</div>

	<?php endif; ?>

	<?php wp_nonce_field( 'occasion_details_metabox', 'occasion_details_metabox_nonce' ); ?>

	<?php
}

/**
 * Output the contents of the Occasions Products metabox
 *
 * @since 1.0.0
 *
 * @param WP_Post $post Current post object
 */
function incassoos_admin_occasion_products_metabox( $post ) {

	// Get occasion products
	$products = incassoos_get_occasion_products( $post );
	$products = wp_list_sort( $products, 'amount', 'DESC' ); /* Since WP 4.7 */

	?>

	<div class="incassoos-item-list">
		<?php if ( $products ) : ?>

		<ul class="products">
			<?php foreach ( $products as $product ) : ?>

			<li id="post-<?php echo $product->id; ?>" class="product occasion-product">
				<span class="label">
					<span class="title"><?php echo esc_html( $product->name ); ?></span>
					<span class="price"><?php incassoos_the_format_currency( $product->price ); ?></span>
				</span>
				<span class="total"><?php echo esc_html( $product->amount ); ?></span>
			</li>

			<?php endforeach; ?>
		</ul>

		<?php else : ?>

		<p><?php esc_html_e( 'There are no consumed products found.', 'incassoos' ); ?></p>

		<?php endif; ?>
	</div>

	<?php
}

/**
 * Output the contents of the Occasion Consumers metabox
 *
 * @since 1.0.0
 *
 * @param WP_Post $post Current post object
 */
function incassoos_admin_occasion_consumers_metabox( $post ) {

	// Get details
	$consumers      = incassoos_get_occasion_consumer_users( $post );
	$consumer_types = incassoos_get_occasion_consumer_types( $post );

	?>

	<div class="incassoos-item-list">
		<div id="select-matches" class="hide-if-no-js">
			<label for="consumer-search" class="screen-reader-text"><?php esc_html_e( 'Search consumers', 'incassoos' ); ?></label>
			<input type="search" id="consumer-search" placeholder="<?php esc_attr_e( 'Search consumers&hellip;', 'incassoos' ); ?>" />

			<button type="button" id="reverse-group-order" class="button-link" title="<?php esc_attr_e( 'Reverse group order', 'incassoos' ); ?>">
				<span class="screen-reader-text"><?php esc_html_e( 'Reverse group order', 'incassoos' ); ?></span>
			</button>
			<button type="button" id="toggle-list-columns" class="button-link" title="<?php esc_attr_e( 'Toggle list columns', 'incassoos' ); ?>">
				<span class="screen-reader-text"><?php esc_html_e( 'Toggle list columns', 'incassoos' ); ?></span>
			</button>
		</div>

		<ul class="sublist list-columns groups">
			<?php foreach ( incassoos_group_users( $consumers ) as $group ) : ?>

			<li id="group-<?php echo $group->id; ?>" class="group">
				<h4 class="sublist-header"><?php echo esc_html( $group->name ); ?></h4>
				<ul class="users">
					<?php foreach ( $group->users as $user ) : ?>

					<li id="user-<?php echo $user->ID; ?>" class="consumer occasion-consumer selector">
						<button type="button" class="button-link open-details">
							<span class="consumer-name title"><?php echo $user->display_name; ?></span>
							<span class="total"><?php incassoos_the_occasion_consumer_total( $user->ID, $post, true ); ?></span>
						</button>

						<ul class="item-details">
							<?php foreach ( incassoos_get_occasion_consumer_orders( $user->ID, $post ) as $item_id ) : ?>

							<li>
								<span class="title"><?php printf( '<a href="%s">%s</a>', esc_url( incassoos_get_order_url( $item_id ) ), incassoos_get_order_product_list( $item_id ) ); ?></span>
								<span class="total"><?php incassoos_the_order_total( $item_id, true ); ?></span>
							</li>

							<?php endforeach; ?>
						</ul>
					</li>

					<?php endforeach; ?>
				</ul>
			</li>

			<?php endforeach; ?>

			<?php if ( $consumer_types ) : ?>

			<li class="group">
				<h4 class="sublist-header"><?php esc_html_e( 'Consumer Types', 'incassoos' ); ?></h4>
				<ul class="users">
					<?php foreach ( $consumer_types as $consumer_type ) : ?>

					<li id="type-<?php echo esc_attr( $consumer_type ); ?>" class="consumer occasion-consumer-type selector">
						<button type="button" class="button-link open-details">
							<span class="consumer-name title"><?php incassoos_the_consumer_type_title( $consumer_type ); ?></span>
							<span class="total"><?php incassoos_the_occasion_consumer_total( $consumer_type, $post, true ); ?></span>
						</button>

						<ul class="item-details">
							<?php foreach ( incassoos_get_occasion_consumer_orders( $consumer_type, $post ) as $item_id ) : ?>

							<li>
								<span class="title"><?php printf( '<a href="%s">%s</a>', esc_url( incassoos_get_order_url( $item_id ) ), incassoos_get_order_product_list( $item_id ) ); ?></span>
								<span class="total"><?php incassoos_the_order_total( $item_id, true ); ?></span>
							</li>

							<?php endforeach; ?>
						</ul>
					</li>

					<?php endforeach; ?>
				</ul>
			</li>

			<?php endif; ?>
		</ul>
	</div>

	<?php
}

/** Order *********************************************************/

/**
 * Output the contents of the Order Details metabox
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'incassoos_order_details_metabox'
 *
 * @param WP_Post $post Current post object
 */
function incassoos_admin_order_details_metabox( $post ) {

	// Get details
	$is_post_view = ( $GLOBALS['pagenow'] !== 'post-new.php' ) && incassoos_admin_is_post_view( $post );
	$occasion_tax  = incassoos_get_occasion_post_type();

	?>

	<div class="incassoos-object-details">

		<?php if ( $is_post_view ) : ?>

		<p>
			<label><?php esc_html_e( 'Created:', 'incassoos' ); ?></label>
			<span id="order-created" class="value"><?php incassoos_the_order_created( $post ); ?></span>
		</p>

		<?php endif; ?>

		<p>
			<label for="consumer"><?php esc_html_e( 'Consumer:', 'incassoos' ); ?></label>

			<?php if ( ! $is_post_view ) : ?>
				<input type="text" name="consumer" id="consumer" value="<?php incassoos_the_order_consumer( $post ); ?>" />
			<?php else : ?>
				<span id="consumer" class="value"><?php incassoos_the_order_consumer_title( $post ); ?></span>
			<?php endif; ?>
		</p>

		<p>
			<label for="occasion"><?php esc_html_e( 'Occasion:', 'incassoos' ); ?></label>

			<?php if ( ! $is_post_view ) :

				incassoos_dropdown_posts( array(
					'name'         => 'post_parent',
					'post_type'    => incassoos_get_occasion_post_type(),
					'selected'     => $post->post_parent,

					// Query arguments. Don't create orders for closed occasions
					'meta_key'     => 'closed',
					'meta_compare' => 'NOT EXISTS'
				) );

			else : ?>

			<span id="occasion" class="value">
				<?php incassoos_the_order_occasion_link( $post ); ?>
			</span>

			<?php endif; ?>
		</p>

		<?php if ( $is_post_view ) : ?>

		<p>
			<label><?php esc_html_e( 'Total:', 'incassoos' ); ?></label>
			<span id="order-total" class="value"><?php incassoos_the_order_total( $post, true ); ?></span>
		</p>

		<p>
			<label><?php esc_html_e( 'Author:', 'incassoos' ); ?></label>
			<span id="order-author" class="value"><?php incassoos_the_order_author( $post ); ?></span>
		</p>

		<?php endif; ?>

		<?php do_action( 'incassoos_order_details_metabox', $post ); ?>

	</div>

	<?php wp_nonce_field( 'order_details_metabox', 'order_details_metabox_nonce' ); ?>

	<?php
}

/**
 * Output the contents of the Order Products metabox
 *
 * @since 1.0.0
 *
 * @param WP_Post $post Current post object
 */
function incassoos_admin_order_products_metabox( $post ) {

	// Get details
	$is_post_view   = ( $GLOBALS['pagenow'] !== 'post-new.php' ) && incassoos_admin_is_post_view( $post );
	$order_products = incassoos_get_order_products( $post );

	if ( $is_post_view ) {
		$products = $order_products;
	} else {
		$products = incassoos_get_products();
	}

	// List item class
	$item_class = array( 'order-product' );
	if ( ! $is_post_view ) {
		$item_class[] = 'selector';
	}

	?>

	<div class="incassoos-item-list">
		<?php if ( $products ) : ?>

		<ul class="products <?php if ( ! $is_post_view ) echo 'list-columns'; ?>">
			<?php

			foreach ( $products as $product ) :
				$product_id = is_array( $product ) ? $product['id'] : (int) $product;
				$amount_ordered = isset( $order_products[ $product_id ] ) ? $order_products[ $product_id ]['amount'] : 0;

			?>

			<li id="post-<?php echo $product_id; ?>" class="<?php echo implode( ' ', $item_class ); ?>">
				<?php if ( ! $is_post_view ) : ?>

				<div class="product-content">
					<input type="hidden" name="products[<?php echo $product_id; ?>][id]" value="<?php echo $product_id; ?>" />
					<input type="number" name="products[<?php echo $product_id; ?>][amount]" id="product-<?php echo $product_id; ?>" class="value" value="<?php echo $amount_ordered; ?>" step="1" />
					<label for="product-<?php echo $product_id; ?>">
						<span class="title"><?php incassoos_the_product_title( $product_id ); ?></span>
						<span class="price"><?php incassoos_the_product_price( $product_id, true ); ?></span>
					</label>
				</div>

				<?php else : ?>

				<div class="product-content">
					<span class="order-product-amount value"><?php echo esc_html( $product['amount'] ); ?></span>
					<span id="product-<?php echo $product['id']; ?>" class="label">
						<span class="title"><?php echo esc_html( $product['name'] ); ?></span>
						<span class="price"><?php incassoos_the_format_currency( $product['price'] ); ?></span>
					</span>
				</div>

				<?php endif; ?>
			</li>

			<?php endforeach; ?>
		</ul>

		<?php else : ?>

		<p><?php esc_html_e( 'There were no products found.', 'incassoos' ); ?></p>

		<?php endif; ?>
	</div>

	<?php wp_nonce_field( 'order_products_metabox', 'order_products_metabox_nonce' ); ?>

	<?php
}

/** Product *************************************************************/

/**
 * Output the contents of the Product Details metabox
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'incassoos_product_details_metabox'
 *
 * @param WP_Post $post Current post object
 */
function incassoos_admin_product_details_metabox( $post ) {

	// Get taxonomies
	$product_cat_tax = incassoos_get_product_cat_tax_id( $post );
	$price           = incassoos_get_product_price( $post );

	?>

	<div class="incassoos-object-details">

		<p>
			<label><?php esc_html_e( 'Created:', 'incassoos' ); ?></label>
			<span id="product-created" class="value"><?php incassoos_the_product_created( $post ); ?></span>
		</p>

		<p>
			<label for="price"><?php esc_html_e( 'Price:', 'incassoos' ); ?></label>
			<input type="number" name="price" id="price" step="0.01" min="0" value="<?php echo esc_attr( $price ); ?>" />
		</p>

		<p>
			<label for="taxonomy-<?php echo $product_cat_tax; ?>"><?php esc_html_e( 'Category:', 'incassoos' ); ?></label>
			<?php
				$cat_terms = wp_get_object_terms( $post->ID, $product_cat_tax, array( 'fields' => 'ids' ) );

				wp_dropdown_categories( array(
					'name'             => "taxonomy-{$product_cat_tax}",
					'taxonomy'         => $product_cat_tax,
					'hide_empty'       => false,
					'selected'         => $cat_terms ? $cat_terms[0] : 0,
					'show_option_none' => esc_html__( '&mdash; No Category &mdash;', 'incassoos' ),
				) );
			?>
		</p>

		<?php do_action( 'incassoos_product_details_metabox', $post ); ?>

	</div>

	<?php wp_nonce_field( 'product_details_metabox', 'product_details_metabox_nonce' ); ?>

	<?php
}

/** Notes ***************************************************************/

/**
 * Output the contents of the Notes metabox
 *
 * @since 1.0.0
 *
 * @param WP_Post $post Current post object
 */
function incassoos_admin_notes_metabox( $post ) {

	// Get details
	$is_post_view = incassoos_admin_is_post_view( $post );

	// Display editor
	if ( ! $is_post_view ) {

		// Use the WordPress editor
		// wp_editor( $post->post_excerpt, 'excerpt', array(
		// 	'textarea_rows' => 5,
		// 	'editor_height' => 150,
		// 	'media_buttons' => false,
		// 	'teeny'         => true,
		// 	'quicktags'     => false,
		// ) );

		echo '<textarea name="excerpt" id="excerpt" rows="8" cols="40">' . esc_textarea( $post->post_excerpt ) . '</textarea>';

	// Display notes
	} else {
		incassoos_the_post_notes( $post );
	}
}
