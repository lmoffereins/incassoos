<?php

/**
 * Incassoos Exporting Functions
 *
 * @package Incassoos
 * @subpackage Exporting
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Process the export file request for a collection
 *
 * @since 1.0.0
 *
 * @param int $post_id Post ID
 */
function incassoos_export_collection_file( $post_id ) {
	$post = incassoos_get_collection( $post_id, array( 'is_collected' => true ) );

	// Bail when the post is not a collected Collection
	if ( ! $post ) {
		return;
	}

	// Nonce check
	check_admin_referer( 'export_collection-' . $post->ID, 'collection_export_nonce' );

	// Export type
	$type_id     = isset( $_POST['export-type'] ) ? $_POST['export-type'] : '';
	$export_type = incassoos_get_export_type( $type_id );
	$errors      = array();

	// Bail when the export type does not exist
	if ( ! $export_type ) {
		$errors[] = esc_html__( 'Invalid export type selected.', 'incassoos' );
	}

	// Bail when the export class is not present
	if ( empty( $errors ) && ! class_exists( $export_type->class_name ) ) {
		$errors[] = esc_html__( 'Export type class does not exist.', 'incassoos' );
	}

	// Bail when the user cannot export
	if ( empty( $errors ) && ! current_user_can( 'export_incassoos_collection', $post->ID ) ) {
		$errors[] = esc_html__( 'You are not allowed to export this Collection.', 'incassoos' );
	}

	// Bail when the decryption key was required but not provided
	if ( empty( $errors ) && incassoos_get_export_type_require_decryption_key( $type_id ) ) {
		$decryption_key = isset( $_POST['export-decryption-key'] ) ? $_POST['export-decryption-key'] : false;

		// Try to set the decryption key
		$result = incassoos_set_decryption_key( $decryption_key );

		if ( is_wp_error( $result ) ) {
			$errors[] = $result->get_error_message();
		} else if ( ! $result ) {
			$errors[] = esc_html__( 'Invalid decryption key provided.', 'incassoos' );
		}
	}

	// Continue when no errors were found
	if ( empty( $errors ) ) {

		// Get export class
		$class = $export_type->class_name;

		// Construct file
		$file = new $class( $post );

		// Bail when construction failed
		if ( method_exists( $file, 'has_errors' ) && $file->has_errors() ) {
			$errors = array_merge( $errors, $file->get_errors() );

		// Offer file download
		} else {
			incassoos_download_text_file( $file );
		}
	}

	// Log any errors
	if ( ! empty( $errors ) ) {
		set_transient( 'incassoos_export_errors-' . $post->ID, $errors );
	}

	// Still here? Redirect to the Collection's page
	wp_redirect( incassoos_get_collection_url( $post ) );
	exit();
}

/**
 * Display the logged errors for the SEPA file export
 *
 * @since 1.0.0
 *
 * @param WP_Post $post Post object
 */
function incassoos_export_error_notice( $post ) {

	// Bail when no errors are logged
	if ( ! $errors = get_transient( 'incassoos_export_errors-' . $post->ID ) )
		return;

	?>

	<div class="notice notice-error is-dismissible incassoos-notice">
		<?php /* translators: 1. error amount 2. toggle link */ ?>
		<p><?php printf(
			_n(
				'Error: %2$s',
				'The file could not be exported due to %1$d errors. %2$s',
				count( $errors ),
				'incassoos'
			),
			count( $errors ),
			count( $errors ) > 1
				? sprintf( '<button type="button" class="button-link">%s</button>', esc_html__( 'Show errors', 'incassoos' ) )
				: $errors[0]
		); ?></p>

		<?php if ( count( $errors ) > 1 ) : foreach ( $errors as $message ) : ?>

		<p><?php echo $message; ?></p>

		<?php endforeach; endif; ?>
	</div>

	<?php

	// Remove logged errors afterwards
	delete_transient( 'incassoos_export_errors-' . $post->ID );
}
