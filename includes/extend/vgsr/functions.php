<?php

/**
 * Incassoos VGSR Functions
 *
 * @package Incassoos
 * @subpackage VGSR
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Consumer Types ******************************************************/

/**
 * Return the Cash consumer type id
 *
 * @since 1.0.0
 *
 * @return string Cash consumer type id
 */
function incassoos_vgsr_get_cash_consumer_type_id() {
	return incassoos()->extend->vgsr->cash_consumer_type;
}

/**
 * Return the PIN consumer type id
 *
 * @since 1.0.0
 *
 * @return string PIN consumer type id
 */
function incassoos_vgsr_get_pin_consumer_type_id() {
	return incassoos()->extend->vgsr->pin_consumer_type;
}

/**
 * Return the On the House consumer type id
 *
 * @since 1.0.0
 *
 * @return string On the House consumer type id
 */
function incassoos_vgsr_get_on_the_house_consumer_type_id() {
	return incassoos()->extend->vgsr->on_the_house_consumer_type;
}

/**
 * Register VGSR consumer types
 *
 * @since 1.0.0
 */
function incassoos_vgsr_register_consumer_types() {

	// Remove Guest type
	incassoos_unregister_consumer_type( incassoos_get_guest_consumer_type_id() );

	// Cash
	incassoos_register_consumer_type(
		incassoos_vgsr_get_cash_consumer_type_id(),
		array(
			'label'       => _x( 'Cash', 'Consumer type', 'incassoos' ),
			'label_count' => _nx_noop( 'Cash <span class="count">(%s)</span>', 'Cash <span class="count">(%s)</span>', 'Consumer type', 'incassoos' ),
			'description' => __( 'Built-in type for anonymous consumptions with cash payment.', 'incassoos' )
		)
	);

	// PIN
	incassoos_register_consumer_type(
		incassoos_vgsr_get_pin_consumer_type_id(),
		array(
			'label'       => _x( 'PIN', 'Consumer type', 'incassoos' ),
			'label_count' => _nx_noop( 'PIN <span class="count">(%s)</span>', 'PIN <span class="count">(%s)</span>', 'Consumer type', 'incassoos' ),
			'description' => __( 'Built-in type for anonymous consumptions with digital payment.', 'incassoos' )
		)
	);

	// On the House
	incassoos_register_consumer_type(
		incassoos_vgsr_get_on_the_house_consumer_type_id(),
		array(
			'label'       => _x( 'On the House', 'Consumer type', 'incassoos' ),
			'label_count' => _nx_noop( 'On the House <span class="count">(%s)</span>', 'On the House <span class="count">(%s)</span>', 'Consumer type', 'incassoos' ),
			'description' => __( 'Built-in type for consumptions booked on the house.', 'incassoos' ),
			// 'avatar_url'  => vgsr()->assets_url . 'images/logo.png' // TODO: implement and use vgsr_get_image()
		)
	);
}

/** Export Types ********************************************************/

/**
 * Return the VGSR SFC export type id
 *
 * @since 1.0.0
 *
 * @return string VGSR SFC export type id
 */
function incassoos_vgsr_get_sfc_export_type_id() {
	return incassoos()->extend->vgsr->sfc_export_type;
}

/**
 * Register VGSR export types
 *
 * @since 1.0.0
 */
function incassoos_vgsr_register_export_types() {

	// SFC
	incassoos_register_export_type(
		incassoos_vgsr_get_sfc_export_type_id(),
		array(
			'labels'                => array(
				'name'        => esc_html__( 'SFC file',        'incassoos' ),
				'export_file' => esc_html__( 'Export SFC file', 'incassoos' )
			),
			'class_name'            => 'Incassoos_VGSR_SFC_Exporter',
			'class_file'            => incassoos()->extend->vgsr->plugin_dir . 'classes/class-incassoos-vgsr-sfc-exporter.php',
			'show_in_list_callback' => 'incassoos_is_collection_collected'
		)
	);
}

/** Users ***************************************************************/

/**
 * Context-aware wrapper for `is_user_vgsr()`
 *
 * @since 1.0.0
 *
 * @param int $user_id User ID. Optional. Defaults to the current user.
 * @return bool The user is VGSR lid
 */
function incassoos_is_user_vgsr( $user_id = 0 ) {
	return ( function_exists( 'vgsr' ) && is_user_vgsr( $user_id ) );
}

/** Admin ***************************************************************/

/**
 * Register and modify the admin post's metaboxes
 *
 * @since 1.0.0
 *
 * @param string $post_type Post type name
 * @param WP_Post $post Current post object
 */
function incassoos_vgsr_admin_add_meta_boxes( $post_type, $post ) {

	// Bail when not doing post metaboxes
	if ( ! is_a( $post, 'WP_Post' ) )
		return;

	// Collection
	if ( incassoos_get_collection_post_type() === $post_type ) {

		// Display SFC content
		if ( incassoos_collection_has_assets( $post ) && current_user_can( 'export_incassoos_collection', $post->ID, incassoos_vgsr_get_sfc_export_type_id() ) ) {
			add_meta_box(
				'incassoos_vgsr_collection_sfc',
				esc_html__( 'Collection SFC preview', 'incassoos' ),
				'incassoos_vgsr_admin_collection_sfc_metabox',
				null,
				'normal',
				'low'
			);
		}
	}
}

/**
 * Output the contents of the VGSR Collection SFC metabox
 *
 * @since 1.0.0
 *
 * @param WP_Post $post Current post object
 */
function incassoos_vgsr_admin_collection_sfc_metabox( $post ) {

	// Export type
	$export_type = incassoos_get_export_type( incassoos_vgsr_get_sfc_export_type_id() );

	// Get export class
	$class = $export_type->class_name;
	if ( ! class_exists( $class ) && ! empty( $export_type->class_file ) ) {
		require_once( $export_type->class_file );
	}

	// Construct file
	$file = new $class( $post );

	?>

	<div class="incassoos-file-content">
		<?php if ( $file->has_errors() ) : ?>

		<div class="notice notice-error">
			<?php foreach ( $file->get_errors() as $message ) : ?>
			<p><?php echo $message; ?></p>
			<?php endforeach; ?>
		</div>

		<?php else : ?>

		<pre><?php echo $file->get_file(); ?></pre>

		<?php endif; ?>
	</div>

	<?php
}
