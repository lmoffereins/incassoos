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
	$post = incassoos_get_collection( $post_id, true );

	// Bail when the post is not a collected Collection
	if ( ! $post )
		return;

	// Nonce check
	check_admin_referer( 'export_collection-' . $post->ID, 'collection_export_nonce' );

	// Export types
	$export_types = incassoos_get_collection_export_types();
	$export_type  = isset( $_POST['export-type'] ) ? $_POST['export-type'] : '';

	// Bail when the export type is not supported
	if ( ! $export_type || ! in_array( $export_type, array_keys( $export_types ), true ) )
		return;

	$class = $export_types[ $export_type ]['class_name'];

	// Bail when the class is not present
	if ( ! class_exists( $class ) )
		return;

	// Construct file
	$file = new $class( $post );

	// Log any errors
	if ( method_exists( $file, 'has_errors' ) && $file->has_errors() ) {
		set_transient( 'inc_export_errors-' . $post->ID, $file->get_errors() );

	// Offer file download
	} else {
		incassoos_download_text_file( $file );
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
	if ( ! $transient = get_transient( 'inc_export_errors-' . $post->ID ) )
		return;

	?>

	<div class="notice notice-error is-dismissible incassoos-notice">
		<?php /* translators: 1. error amount 2. toggle link */ ?>
		<p><?php printf(
			_n(
				'The file could not be exported due to %1$d error. %2$s',
				'The file could not be exported due to %1$d errors. %2$s',
				count( $transient ),
				'incassoos'
			),
			count( $transient ),
			sprintf( '<button type="button" class="button-link">%s</button>', esc_html__( 'Show errors', 'incassoos' ) )
		); ?></p>

		<?php foreach ( $transient as $message ) : ?>

		<p><?php echo $message; ?></p>

		<?php endforeach; ?>
	</div>

	<?php

	// Remove logged errors afterwards
	delete_transient( 'inc_export_errors-' . $post->ID );
}

/** SEPA **********************************************************************/

/**
 * Add the SEPA collection export type
 *
 * @since 1.0.0
 *
 * @param array $export_types Export types
 * @param array Export types
 */
function incassoos_export_sepa_export_type( $export_types ) {

	// Require classes
	require_once( incassoos()->includes_dir . 'classes/class-incassoos-sepa-xml-parser.php' );
	require_once( incassoos()->includes_dir . 'classes/class-incassoos-sepa-xml-file.php' );

	// SEPA
	$export_types['inc_sepa'] = array(
		'label'      => esc_html__( 'SEPA file', 'incassoos' ),
		'class_name' => 'Incassoos_SEPA_XML_File'
	);

	return $export_types;
}
