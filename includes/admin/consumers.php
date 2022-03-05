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
 * @param string $field Optional. Single field to return.
 * @return array Consumers fields or single field when requested.
 */
function incassoos_admin_get_consumers_fields( $field = '' ) {

	// Define fields
	$fields = apply_filters( 'incassoos_admin_get_consumers_fields', array(

		// IBAN
		'_incassoos_iban' => array(
			'label' => __( 'IBAN', 'incassoos' ),
			'type'  => 'text'
		),

		// Hide in list
		'_incassoos_hide_in_list' => array(
			'label' => __( 'Hide in list', 'incassoos' ),
			'type'  => 'checkbox'
		),

		// Consumption limit
		'_incassoos_consumption_limit' => array(
			'label' => __( 'Consumption limit', 'incassoos' ),
			'type'  => 'price'
		),
	) );

	// Parse arguments
	foreach ( $fields as $_field => $args ) {
		$fields[ $_field ] = wp_parse_args( $args, array(
			'label'           => '',
			'get_callback'    => 'incassoos_admin_consumers_meta_get_callback',
			'update_callback' => 'incassoos_admin_consumers_meta_update_callback',
			'input_callback'  => 'incassoos_admin_consumers_input_callback',
			'type'            => 'text',
			'options'         => array()
		) );
	}

	// Return single field
	if ( $field && isset( $fields[ $field ] ) ) {
		return $fields[ $field ];
	}

	return $fields;
}

/**
 * Return the admin consumers field's value
 *
 * @since 1.0.0
 *
 * @param  WP_User $user User object
 * @param  string $field Meta field key
 * @return mixed Field value
 */
function incassoos_admin_consumers_meta_get_callback( $user, $field ) {
	return implode( ',', (array) $user->get( $field ) );
}

/**
 * Update the admin consumers field's value
 *
 * @since 1.0.0
 *
 * @param  WP_User $user User object
 * @param  mixed   $value New user field value
 * @param  string  $field Meta field key
 * @return mixed Update success.
 */
function incassoos_admin_consumers_meta_update_callback( $user, $value, $field ) {
	if ( empty( $value ) ) {
		return delete_user_meta( $user->ID, $field );
	} else {
		return update_user_meta( $user->ID, $field, $value );
	}
}

/**
 * Output the admin consumers field's input field
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_admin_consumers_input_callback'
 *
 * @param  string $field Meta field key
 */
function incassoos_admin_consumers_input_callback( $field ) {
	$_field = incassoos_admin_get_consumers_fields( $field );
	$input  = '';

	switch ( $_field['type'] ) {

		// Checkbox
		case 'checkbox' :
			if ( ! $_field['options'] ) {
				$input = '<input type="checkbox" name="' . esc_attr( $field ) . '" value="1" />';
				break;
			}

		// Radio
		case 'radio' :
			foreach ( $_field['options'] as $option => $opt_label ) {
				$id = esc_attr( $field ) . '-' . esc_attr( $option );
				$input .= sprintf( '<input type="%s" name="%s" id="%s" value="%s" />',
					$_field['type'],
					esc_attr( $field ) . ( 'checkbox' === $_field['type'] ? '[]' : '' ),
					$id,
					esc_attr( $option )
				);
				$input .= '<label for="' . $id . '">' . $opt_label . '</label>';
			}
			break;

		// Select
		case 'select' :
			$input = '<select name="' . esc_attr( $field ) . '">';
			foreach ( $_field['options'] as $option => $opt_label ) {
				$input .= sprintf( '<option value="%s">%s</option>', esc_attr( $option ), esc_html( $opt_label ) );
			}
			$input .= '</select>';
			break;

		// Textarea
		case 'textarea' :
			$input = '<textarea name="' . esc_attr( $field ) . '"></textarea>';
			break;

		// Price
		case 'price' :

			// Formatting
			$format_args     = incassoos_get_currency_format_args();
			$min_price_value = 1 / pow( 10, $format_args['decimals'] );

			$input = '<input type="number" class="small-text" min="0" step="' . $min_price_value . '" name="' . esc_attr( $field ) . '" value="" />';
			break;

		// Other
		default :
			$type = $_field['type'] ? esc_attr( $_field['type'] ) : 'text';
			$input = '<input type="' . $type . '" name="' . esc_attr( $field ) . '" value="" />';
	}

	echo apply_filters( 'incassoos_admin_consumers_input_callback', $input, $field );
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
	$doaction = isset( $_REQUEST['action'] ) && -1 != $_REQUEST['action'] ? $_REQUEST['action'] : false;

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
	} else {
		return;
	}

	$sendback = add_query_arg( 'page', 'incassoos-consumers', admin_url( 'admin.php' ) );

	switch ( $doaction ) {

		// Inline edit
		case 'edituser' :
			check_admin_referer( 'incassoos_admin_consumers_quick_edit', '_inline_edit' );

			// Bail when the user cannot be edited
			if ( ! current_user_can( 'edit_incassoos_consumer', $user->ID ) )
				return;

			foreach ( incassoos_admin_get_consumers_fields() as $column => $args ) {
				$value = isset( $_REQUEST[ $column ] ) ? $_REQUEST[ $column ] : null;
				$_success = call_user_func( $args['update_callback'], $user, $value, $column );

				if ( is_wp_error( $_success ) || ! $_success ) {
					$success = false;
				}
			}

			// Redirect to consumers page
			$redirect_url = add_query_arg( 'updated', $user->ID, $sendback );

			wp_redirect( $redirect_url );
			exit();

			break;

		// Bulk edit: hide
		case 'bulk-default-hide' :
			check_admin_referer( 'incassoos-bulk-consumers', '_bulk_edit' );

			// Bail when the users cannot be edited
			if ( ! current_user_can( 'edit_incassoos_consumers' ) )
				return;

			// Update user meta per user
			foreach ( $users as $user_id ) {
				update_user_meta( $user_id, '_incassoos_hide_in_list', 1 );
			}

			// Redirect to consumers page
			$redirect_url = add_query_arg( 'updated', implode( ',', $users ), $sendback );

			wp_redirect( $redirect_url );
			exit();

			break;

		// Bulk edit: show
		case 'bulk-default-show' :
			check_admin_referer( 'incassoos-bulk-consumers', '_bulk_edit' );

			// Bail when the users cannot be edited
			if ( ! current_user_can( 'edit_incassoos_consumers' ) )
				return;

			// Update user meta per user
			foreach ( $users as $user_id ) {
				update_user_meta( $user_id, '_incassoos_hide_in_list', 0 );
			}

			// Redirect to consumers page
			$redirect_url = add_query_arg( 'updated', implode( ',', $users ), $sendback );

			wp_redirect( $redirect_url );
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
	$can_bulk_edit = current_user_can( 'edit_incassoos_consumers' );

	if ( isset( $_GET['updated']) ) :
		$updated = explode( ',', $_GET['updated'] );
	?>

	<div id="message" class="updated notice notice-success is-dismissible"><p>
		<?php printf(
			esc_html( _n( 'Successfully updated user %s.', 'Successfully updated users %s', count( $updated ), 'incassoos' ) ),
			'<strong>' . wp_sprintf_l( '%l', array_map( 'incassoos_get_user_display_name', $updated ) ) . '</strong>'
		); ?>
	</p></div>

	<?php endif; ?>

	<p><?php esc_html_e( 'Manage consumers and their attributes for Incassoos.', 'incassoos' ); ?></p>

	<form method="post" class="incassoos-item-list">

		<?php if ( $can_bulk_edit ) : ?>

		<div id="select-meta" class="tablenav hide-if-no-js">
			<button type="button" id="toggle-bulk-edit" class="button alignleft"><?php esc_html_e( 'Toggle bulk edit mode', 'incassoos' ); ?></button>
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

				<button type="button" id="show-default-items" class="button-link"><?php esc_html_e( 'Show default', 'incassoos' ); ?></button>
				<button type="button" id="reverse-group-order" class="button-link" title="<?php esc_attr_e( 'Reverse group order', 'incassoos' ); ?>">
					<span class="screen-reader-text"><?php esc_html_e( 'Reverse group order', 'incassoos' ); ?></span>
				</button>
			</div>

			<ul class="sublist groups">
				<?php foreach ( incassoos_get_grouped_users() as $group ) : ?>

				<li id="group-<?php echo $group->id; ?>" class="group">
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

								<?php foreach ( incassoos_admin_get_consumers_fields() as $column => $args ) : ?>

									<span class="user-<?php echo esc_attr( $column ); ?>"><?php echo call_user_func( $args['get_callback'], $user, $column ); ?></span>

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
				<option value="bulk-default-hide"><?php esc_html_e( 'Hide by default', 'incassoos' ); ?></option>
				<option value="bulk-default-show"><?php esc_html_e( 'Show by default', 'incassoos' ); ?></option>
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

				<?php foreach ( incassoos_admin_get_consumers_fields() as $column => $args ) : ?>

					<label>
						<span class="title"><?php echo $args['label']; ?></span>
						<span class="input-text-wrap"><?php call_user_func( $args['input_callback'], $column ); ?></span>
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

	// Add class for hidden consumers
	if ( incassoos_user_hide_by_default( $user ) ) {
		$class[] = 'hide-by-default'; /* Don't use 'hide-in-list' which affects search differently in admin.js */
	}

	// Add class for missing IBAN
	if ( ! incassoos_get_user_iban( $user ) ) {
		$class[] = 'no-iban';
	}

	return (array) apply_filters( 'incassoos_admin_consumers_list_class', $class, $user );
}
