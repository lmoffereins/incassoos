<?php

/**
 * Incassoos Exporting Functions
 *
 * @package Incassoos
 * @subpackage Exporting
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** SEPA **********************************************************************/

/**
 * Add SEPA download link to the Collection details metabox
 *
 * @since 1.0.0
 *
 * @param  WP_Post $post Post object
 */
function incassoos_sepa_collection_details( $post = 0 ) {

	// Collection is collected
	if ( incassoos_is_collection_collected( $post ) ) { ?>

		<p>
			<label><?php esc_html_e( 'SEPA:', 'incassoos' ); ?></label>
			<span class="value"><?php printf(
				'<a href="%s">%s</a>',
				esc_url( wp_nonce_url( add_query_arg( array( 'post' => $post->ID, 'action' => 'inc_export_file_sepa' ), admin_url( 'post.php' ) ), 'export-file-sepa_' . $post->ID ) ),
				esc_html__( 'Download file', 'incassoos' )
			); ?></span>
		</p>

		<?php
	}
}

/**
 * Process the SEPA file download action
 *
 * @since 1.0.0
 *
 * @param  mixed $post_id Post ID
 */
function incassoos_sepa_export_post_action( $post_id ) {
	$post = incassoos_get_collection( $post_id, true );

	// Bail when the post is not a collected Collection
	if ( ! $post )
		return;

	// Nonce check
	check_admin_referer( 'export-file-sepa_' . $post->ID );

	// Require classes
	require_once( incassoos()->includes_dir . 'classes/class-incassoos-sepa-xml-parser.php' );
	require_once( incassoos()->includes_dir . 'classes/class-incassoos-sepa-xml-file.php' );

	// Construct file
	$file = new Incassoos_SEPA_XML_File( $post );

	// Log any errors
	if ( $file->has_errors() ) {
		set_transient( 'inc-sepa-file-errors_' . $post->ID, $file->get_errors() );

	// Offer file download
	} else {
		incassoos_download_text_file( $file );
	}
	// incassoos_download_text_file( $file );

	// Still here? Redirect to the Collection page
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
function incassoos_sepa_export_error_notice( $post ) {

	// Bail when no errors are logged
	if ( ! $transient = get_transient( 'inc-sepa-file-errors_' . $post->ID ) )
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
	delete_transient( 'inc-sepa-file-errors_' . $post->ID );
}
