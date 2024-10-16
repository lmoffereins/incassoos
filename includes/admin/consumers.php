<?php

/**
 * Incassoos Admin Consumers Functions
 *
 * @package Incassoos
 * @subpackage Administration
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Return the admin consumers fields
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_admin_get_consumers_fields'
 *
 * @return array Consumers fields
 */
function incassoos_admin_get_consumers_fields() {

	// Define fields
	$fields = apply_filters( 'incassoos_admin_get_consumers_fields', array(

		// IBAN
		'_incassoos_iban' => array(
			'label' => __( 'IBAN', 'incassoos' ),
			'type'  => 'text'
		),

		// Debit mandate date
		'_incassoos_debit_mandate_date' => array(
			'label' => __( 'Mandate date', 'incassoos' ),
			'type'  => 'date'
		),

		// Archived
		'_incassoos_archived_consumer' => array(
			'label'           => _x( 'Archived', 'Consumer status', 'incassoos' ),
			'type'            => 'checkbox',
			'update_callback' => 'incassoos_admin_consumers_field_update_archived'
		),

		// Spending limit
		'_incassoos_spending_limit' => array(
			'label' => __( 'Spending limit', 'incassoos' ),
			'type'  => 'price'
		),
	) );

	// Parse defaults
	foreach ( $fields as $field_id => $args ) {
		$fields[ $field_id ] = wp_parse_args( $args, array(
			'label'            => '',
			'get_callback'     => 'incassoos_admin_consumers_field_get_callback',
			'update_callback'  => 'incassoos_admin_consumers_field_update_callback',
			'input_callback'   => 'incassoos_admin_consumers_field_input_callback',
			'display_callback' => 'incassoos_admin_consumers_field_display_callback',
			'type'             => 'text',
			'options'          => array()
		) );
	}

	return $fields;
}

/**
 * Return a single admin consumers field
 *
 * @since 1.0.0
 *
 * @param string $field_id Meta field key
 * @return array|bool Single field or False when not found
 */
function incassoos_admin_get_consumers_field( $field_id ) {
	$fields = incassoos_admin_get_consumers_fields();

	// Return single field
	if ( $field_id && isset( $fields[ $field_id ] ) ) {
		return $fields[ $field_id ];
	}

	return false;
}

/**
 * Return the admin consumers field's value
 *
 * @since 1.0.0
 *
 * @param WP_User $user User object
 * @param string $field_id Meta field key
 * @return mixed Field value
 */
function incassoos_admin_consumers_field_get_callback( $user, $field_id ) {
	$field = incassoos_admin_get_consumers_field( $field_id );
	$value = implode( ',', (array) $user->get( $field_id ) );

	// Parse database date value
	if ( $field && 'date' === $field['type'] ) {
		$value = mysql2date( 'd-m-Y', $value );
	}

	return $value;
}

/**
 * Update the admin consumers field's value
 *
 * @since 1.0.0
 *
 * @param WP_User $user User object
 * @param mixed   $value New user field value
 * @param string  $field_id Meta field key
 * @return mixed Update success.
 */
function incassoos_admin_consumers_field_update_callback( $user, $value, $field_id ) {
	if ( empty( $value ) ) {
		return delete_user_meta( $user->ID, $field_id );
	} else {
		$field = incassoos_admin_get_consumers_field( $field_id );

		// Parse date input
		if ( $field && 'date' === $field['type'] ) {

			// Parse input date
			$date = strtotime( trim( $value ) );

			// Save mysql date string
			if ( $date ) {
				$value = date( 'Y-m-d 00:00:00', $date );
			} else {
				$value = '';
			}
		}

		return update_user_meta( $user->ID, $field_id, $value );
	}
}

/**
 * Update callback for the consumer's Archived field
 *
 * @since 1.0.0
 *
 * @param WP_User $user User object
 * @param mixed   $value New user field value
 * @return bool Update success.
 */
function incassoos_admin_consumers_field_update_archived( $user, $value ) {
	if ( empty( $value ) ) {
		return incassoos_unarchive_consumer( $user );
	} else {
		return incassoos_archive_consumer( $user );
	}
}

/**
 * Output the admin consumers field's input element
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_admin_consumers_field_input_callback'
 *
 * @param string $field_id Meta field key
 */
function incassoos_admin_consumers_field_input_callback( $field_id ) {
	$field = incassoos_admin_get_consumers_field( $field_id );
	$element = '';

	switch ( $field['type'] ) {

		// Checkbox
		case 'checkbox' :
			if ( ! $field['options'] ) {
				$element = '<input type="checkbox" name="' . esc_attr( $field_id ) . '" value="1" />';
				break;
			}

		// Radio
		case 'radio' :
			foreach ( $field['options'] as $option => $opt_label ) {
				$id = esc_attr( $field_id ) . '-' . esc_attr( $option );
				$element .= sprintf( '<input type="%s" name="%s" id="%s" value="%s" />',
					$field['type'],
					esc_attr( $field_id ) . ( 'checkbox' === $field['type'] ? '[]' : '' ),
					$id,
					esc_attr( $option )
				);
				$element .= '<label for="' . $id . '">' . $opt_label . '</label>';
			}
			break;

		// Select
		case 'select' :
			$element = '<select name="' . esc_attr( $field_id ) . '">';
			foreach ( $field['options'] as $option => $opt_label ) {
				$element .= sprintf( '<option value="%s">%s</option>', esc_attr( $option ), esc_html( $opt_label ) );
			}
			$element .= '</select>';
			break;

		// Textarea
		case 'textarea' :
			$element = '<textarea name="' . esc_attr( $field_id ) . '"></textarea>';
			break;

		// Price
		case 'price' :

			// Formatting
			$format_args     = incassoos_get_currency_format_args();
			$min_price_value = 1 / pow( 10, $format_args['decimals'] );

			$element = '<input type="number" class="small-text" min="0" step="' . $min_price_value . '" name="' . esc_attr( $field_id ) . '" value="" />';
			break;

		// Date using datepicker
		case 'date' :
			$element = '<input type="text" name="' . esc_attr( $field_id ) . '" placeholder="dd-mm-yyyy" class="datepicker" value="" />';
			break;

		// Other
		default :
			$type = $field['type'] ? esc_attr( $field['type'] ) : 'text';
			$element = '<input type="' . $type . '" name="' . esc_attr( $field_id ) . '" value="" />';
	}

	echo apply_filters( 'incassoos_admin_consumers_field_input_callback', $element, $field_id );
}

/**
 * Return the admin consumers field's display value
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_admin_consumers_field_display_callback'
 *
 * @param WP_User $user User object
 * @param string $field_id Meta field key
 * @return string Field display value
 */
function incassoos_admin_consumers_field_display_callback( $user, $field_id ) {
	$field = incassoos_admin_get_consumers_field( $field_id );
	$value = $field ? call_user_func( $field['get_callback'], $user, $field_id ) : '';

	switch ( $field['type'] ) {

		// Price
		case 'price' :
			$value = incassoos_parse_currency( $value, true );
			break;
	}

	return apply_filters( 'incassoos_admin_consumers_field_display_callback', $value, $field_id );
}

/** Page ****************************************************************/

/**
 * Act when the admin consumers page is loaded
 *
 * @since 1.0.0
 *
 * @global WPDB $wpdb
 */
function incassoos_admin_load_consumers_page() {
	global $wpdb;

	// Bail when not a post request
	if ( 'POST' != strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	$dobulk   = isset( $_REQUEST['bulkaction'] ) && ! empty( $_REQUEST['bulkaction'] );
	$doexport = ! $dobulk && isset( $_REQUEST['exportaction'] ) ? $_REQUEST['exportaction'] : false;
	$doaction = isset( $_REQUEST['action'] ) && -1 != $_REQUEST['action'] ? $_REQUEST['action'] : $doexport;

	// Get the user query var
	if ( isset( $_REQUEST['user'] ) ) {
		$user_id = $dobulk ? (array) $_REQUEST['user'] : array( (int) $_REQUEST['user'] );
		$users   = array_map( 'incassoos_get_user', $user_id );

		if ( ! $dobulk ) {
			$user = $users[0];
			if ( ! $user ) {
				return;
			}
		} else {
			$users = wp_list_pluck( array_filter( $users ), 'ID' );
		}
	}

	$sendback = add_query_arg( 'page', 'incassoos-consumers', admin_url( 'admin.php' ) );

	switch ( $doaction ) {

		// Inline edit
		case 'edituser' :
			check_admin_referer( 'incassoos_admin_consumers_quick_edit', '_inline_edit' );

			// Bail when the user cannot be edited
			if ( ! current_user_can( 'edit_incassoos_consumer', $user->ID ) )
				return;

			foreach ( incassoos_admin_get_consumers_fields() as $field_id => $args ) {
				$value = isset( $_REQUEST[ $field_id ] ) ? $_REQUEST[ $field_id ] : null;
				$_success = call_user_func( $args['update_callback'], $user, $value, $field_id );

				if ( is_wp_error( $_success ) || ! $_success ) {
					$success = false;
				}
			}

			// Redirect to consumers page
			$redirect_url = add_query_arg( 'updated', $user->ID, $sendback );

			wp_redirect( $redirect_url );
			exit();

			break;

		// Bulk edit: archived consumer
		case 'bulk-set-archived-consumer' :
			check_admin_referer( 'incassoos-bulk-consumers', '_bulk_edit' );

			// Bail when the users cannot be edited
			if ( ! current_user_can( 'edit_incassoos_consumers' ) )
				return;

			// Update user meta per user
			foreach ( $users as $user_id ) {
				incassoos_archive_consumer( $user_id );
			}

			// Redirect to consumers page
			$redirect_url = add_query_arg( 'updated', implode( ',', $users ), $sendback );

			wp_redirect( $redirect_url );
			exit();

			break;

		// Bulk edit: unarchive consumer
		case 'bulk-set-unarchive-consumer' :
			check_admin_referer( 'incassoos-bulk-consumers', '_bulk_edit' );

			// Bail when the users cannot be edited
			if ( ! current_user_can( 'edit_incassoos_consumers' ) )
				return;

			// Update user meta per user
			foreach ( $users as $user_id ) {
				incassoos_unarchive_consumer( $user_id );
			}

			// Redirect to consumers page
			$redirect_url = add_query_arg( 'updated', implode( ',', $users ), $sendback );

			wp_redirect( $redirect_url );
			exit();

			break;

		// Download consumers
		case 'incassoos-export-consumers' :
			check_admin_referer( 'incassoos-export-consumers' );

			// Bail when the users cannot be exported
			if ( ! current_user_can( 'export_incassoos_consumers' ) )
				return;

			// Start file export dryrun
			incassoos_admin_export_file( array(
				'export_type_id' => incassoos_get_consumers_export_type_id(),
				'dryrun'         => true,
				'decryption_key' => isset( $_POST['export-decryption-key'] ) ? $_POST['export-decryption-key'] : false
			) );

			// Redirect to consumers page
			wp_redirect( $sendback );
			exit();

			break;
	}
}

/**
 * Output the contents of the Consumers admin page
 *
 * @since 1.0.0
 */
function incassoos_admin_consumers_page() {
	$can_bulk_edit          = current_user_can( 'edit_incassoos_consumers' );
	$can_export             = current_user_can( 'export_incassoos_consumers' );
	$require_decryption_key = false;

	// Determine context for decryption key input for export action
	if ( incassoos_is_encryption_enabled() ) {
		$export_type_id = incassoos_get_consumers_export_type_id();

		if ( incassoos_get_export_type_is_decryption_key_required( $export_type_id ) ) {
			$require_decryption_key = true;
		} elseif ( incassoos_get_export_type_is_decryption_key_optional( $export_type_id ) ) {
			$require_decryption_key = current_user_can( 'decrypt_incassoos_data' );
		}
	}

	if ( isset( $_GET['updated']) ) {
		$updated = explode( ',', $_GET['updated'] );
		$message = sprintf(
			esc_html( _n( 'Successfully updated user %s.', 'Successfully updated users %s', count( $updated ), 'incassoos' ) ),
			'<strong>' . wp_sprintf_l( '%l', array_map( 'incassoos_get_user_display_name', $updated ) ) . '</strong>'
		);

		// Display notice
		wp_admin_notice( $message, array( 'type' => 'success', 'dismissible' => true, 'additional_classes' => array( 'updated' ) ) );
	}

	?>

	<p><?php esc_html_e( 'Manage consumers and their attributes for Incassoos.', 'incassoos' ); ?></p>

	<form method="post" class="incassoos-item-list hiding-archived-items">

		<?php if ( $can_bulk_edit ) : ?>

		<div id="select-meta" class="tablenav hide-if-no-js">
			<button type="button" id="toggle-bulk-edit" class="button alignleft"><?php esc_html_e( 'Open bulk edit mode', 'incassoos' ); ?></button>

			<?php if ( $can_export ) : ?>

			<div class="import-export-consumers">
				<span class="spinner"></span>
				<div class="export-consumers-wrapper <?php if ( $require_decryption_key ) { echo 'require-decryption-key-wrapper'; } ?>">
					<?php if ( $require_decryption_key ) : ?>

					<label class="screen-reader-text" for="export-decryption-key"><?php esc_html_e( 'Decryption key', 'incassoos' ); ?></label>
					<input type="password" name="export-decryption-key" placeholder="<?php esc_attr_e( 'Decryption key&hellip;', 'incassoos' ); ?>" />

					<?php endif; ?>

					<a href="<?php echo add_query_arg( 'taxonomy', incassoos_get_consumer_type_tax_id(), admin_url( 'edit-tags.php' ) ); ?>" class="wp-core-ui button align-right"><?php esc_html_e( 'Manage Consumer Types', 'incassoos' ); ?></a>
					<?php wp_nonce_field( 'incassoos-export-consumers' ); ?>
					<input type="hidden" name="exportaction" value="incassoos-export-consumers" />
					<?php submit_button( esc_html__( 'Export', 'incassoos' ), 'button-secondary', 'export-consumers', false ); ?>
				</div>
			</div>

			<?php endif; ?>
		</div>

		<?php endif; ?>

		<div class="postbox">
			<div id="select-matches" class="item-list-header hide-if-no-js">

				<?php if ( $can_bulk_edit ) : ?>

				<label for="consumer-quick-select" class="screen-reader-text"><?php esc_html_e( 'Quick select consumers', 'incassoos' ); ?></label>
				<?php incassoos_dropdown_user_matches( array( 'id' => 'consumer-quick-select' ) ); ?>

				<?php endif; ?>

				<label for="consumers-item-search" class="screen-reader-text"><?php esc_html_e( 'Search consumers', 'incassoos' ); ?></label>
				<input type="search" id="consumers-item-search" class="list-search" placeholder="<?php esc_attr_e( 'Search consumers&hellip;', 'incassoos' ); ?>" />

				<button type="button" id="show-archived-items" class="button-link"><?php esc_html_e( 'Show archived', 'incassoos' ); ?></button>
				<button type="button" id="reverse-group-order" class="button-link" title="<?php esc_attr_e( 'Reverse group order', 'incassoos' ); ?>">
					<span class="screen-reader-text"><?php esc_html_e( 'Reverse group order', 'incassoos' ); ?></span>
				</button>
			</div>

			<ul class="sublist groups">
				<?php foreach ( incassoos_get_grouped_users() as $group ) :
					$group_hidden = empty( array_filter( wp_list_pluck( $group->users, 'ID' ), 'incassoos_is_consumer_not_archived' ) ) ? 'hidden' : '';
				?>

				<li id="group-<?php echo $group->id; ?>" class="group <?php echo $group_hidden; ?>">
					<h4 class="sublist-header item-content">
						<button type="button" class="button-link title select-group-users" id="select-group-<?php echo $group->id; ?>" title="<?php esc_attr_e( 'Select or deselect all users in the group', 'incassoos' ); ?>"><?php echo esc_html( $group->name ); ?></button>
						<span class="title"><?php echo esc_html( $group->name ); ?></span>
					</h4>

					<ul class="users">
						<?php foreach ( $group->users as $user ) : ?>

						<li id="user-<?php echo $user->ID; ?>" class="consumer <?php echo implode( ' ', incassoos_admin_consumers_list_class( $user ) ); ?>">
							<button type="button" class="consumer-name">
								<input type="checkbox" name="user[]" class="select-user" value="<?php echo $user->ID; ?>" data-matches="<?php incassoos_the_user_match_ids_list( $user->ID ); ?>" />
								<span class="user-name"><?php echo incassoos_get_user_display_name( $user->ID ); ?></span>
							</button>

							<div class="details" style="display:none;">
								<span class="user-id"><?php echo $user->ID; ?></span>

								<?php foreach ( incassoos_admin_get_consumers_fields() as $field_id => $args ) : ?>

									<span class="user-<?php echo esc_attr( $field_id ); ?>"><?php echo call_user_func( $args['get_callback'], $user, $field_id ); ?></span>

								<?php endforeach; ?>
							</div>
						</li>

						<?php endforeach; ?>
					</ul>
				</li>

				<?php endforeach; ?>
			</ul>
		</div>
	</form>

	<div style="display:none;" id="bulkedit">
		<div class="bulk-edit alignleft actions">
			<label for="bulk-action-selector" class="screen-reader-text"><?php esc_html_e( 'Select bulk action' ); ?></label>
			<select id="bulk-action-selector" name="action">
				<option value="-1"><?php esc_html_e( 'Bulk actions' ); ?></option>
				<option value="bulk-set-archived-consumer"><?php esc_html_e( 'Archive consumers', 'incassoos' ); ?></option>
				<option value="bulk-set-unarchive-consumer"><?php esc_html_e( 'Unarchive consumers', 'incassoos' ); ?></option>
			</select>
			<?php wp_nonce_field( 'incassoos-bulk-consumers', '_bulk_edit' ); ?>
			<?php submit_button( __( 'Apply' ), 'action', 'bulkaction', false, array( 'id' => 'doaction' ) ); ?>
		</div>
	</div>

	<div style="display:none;" id="inlineedit">
		<div class="inline-edit inline-edit-row" style="display: none">

			<fieldset>
				<legend class="inline-edit-legend screen-reader-text"><?php esc_html_e( 'Quick Edit' ); ?></legend>
				<div class="inline-edit-fields">

				<?php foreach ( incassoos_admin_get_consumers_fields() as $field_id => $args ) : ?>

					<label>
						<span class="title"><?php echo esc_html( $args['label'] ); ?></span>
						<span class="input-text-wrap"><?php call_user_func( $args['input_callback'], $field_id ); ?></span>
					</label>

				<?php endforeach; ?>
			</div></fieldset>

			<p class="inline-edit-save submit">
				<button type="button" class="cancel button alignleft"><?php esc_html_e( 'Cancel' ); ?></button>
				<input type="submit" class="save button button-primary alignright" value="<?php esc_attr_e( 'Update' ); ?>" />
				<span class="spinner"></span>
				<span class="error" style="display:none;"></span>
				<?php wp_nonce_field( 'incassoos_admin_consumers_quick_edit', '_inline_edit', false ); ?>
				<input type="hidden" name="action" value="edituser" />
				<input type="hidden" name="user" value="0" />
				<br class="clear" />
			</p>
		</div>
	</div>

	<?php
}

/**
 * Return the admin consumers user's classes
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_admin_consumers_list_class'
 *
 * @param  WP_User $user User object
 * @return array User classes
 */
function incassoos_admin_consumers_list_class( $user ) {
	$class = array();

	// Add class for archived consumers
	if ( incassoos_is_consumer_archived( $user ) ) {
		$class[] = 'archived-consumer'; /* Don't use 'hide-in-list' classname which affects search in admin.js */
	}

	// Add class for missing IBAN
	if ( ! incassoos_get_user_iban( $user ) ) {
		$class[] = 'no-iban';
	}

	return (array) apply_filters( 'incassoos_admin_consumers_list_class', $class, $user );
}
