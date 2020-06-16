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
		'_incassoos_iban'   => array(
			'label' => __( 'IBAN', 'incassoos' ),
		),

		// BIC
		'_incassoos_bic'    => array(
			'label' => __( 'BIC', 'incassoos' ),
		),

		// Don't show
		'_incassoos_noshow' => array(
			'label' => __( "Don't show", 'incassoos' ),
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
			$input = '<input type="number" min="0" step="0.01" name="' . esc_attr( $field ) . '" value="" />';
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
 */
function incassoos_admin_load_consumers_page() {

	// Bail when not a post request
	if ( 'POST' != strtoupper( $_SERVER['REQUEST_METHOD'] ) )
		return;

	// Get the user query var
	$user_id = isset( $_REQUEST['user'] ) ? (int) $_REQUEST['user'] : false;
	$user    = incassoos_get_user( $user_id );

	// Bail when user or action query var are missing
	if ( ! $user || ! isset( $_REQUEST['action'] ) )
		return;

	switch ( $_REQUEST['action'] ) {
		case 'edituser' :
			check_admin_referer( 'incassoos_admin_consumers_quick_edit', '_inline_edit' );

			// Bail when the user cannot be edited
			if ( ! current_user_can( 'edit_incassoos_consumer', $user->ID ) )
				return;

			$success = true;

			if ( $user ) {
				foreach ( incassoos_admin_get_consumers_fields() as $column => $args ) {
					$value = isset( $_REQUEST[ $column ] ) ? $_REQUEST[ $column ] : null;
					$_success = call_user_func( $args['update_callback'], $user, $value, $column );

					if ( is_wp_error( $_success ) || ! $_success ) {
						$success = false;
					}
				}
			}

			// Redirect to consumers page
			wp_redirect( add_query_arg( array( 'page' => 'incassoos-consumers', 'updated' => $user->ID ), admin_url( 'admin.php' ) ) );
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

	if ( isset( $_GET['updated']) ) : ?>

	<div id="message" class="updated notice notice-success is-dismissible"><p>
		<?php printf( esc_html__( 'Successfully updated user %s.', 'incassoos' ), '<strong>' . incassoos_get_user_display_name( $_GET['updated'] ) . '</strong>' ); ?>
	</p></div>

	<?php endif; ?>

	<p><?php esc_html_e( 'Manage consumers and their specific Incassoos attributes.', 'incassoos' ); ?></p>

	<form method="post" class="incassoos-item-list postbox">
		<div id="select-matches" class="hide-if-no-js">
			<label for="consumer-search" class="screen-reader-text"><?php esc_html_e( 'Search consumers', 'incassoos' ); ?></label>
			<input type="search" id="consumer-search" placeholder="<?php esc_attr_e( 'Search consumers&hellip;', 'incassoos' ); ?>" />

			<button type="button" id="show-visible" class="button-link"><?php esc_html_e( 'Show visible', 'incassoos' ); ?></button>
			<button type="button" id="reverse-group-order" class="button-link" title="<?php esc_attr_e( 'Reverse group order', 'incassoos' ); ?>">
				<span class="screen-reader-text"><?php esc_html_e( 'Reverse group order', 'incassoos' ); ?></span>
			</button>
		</div>

		<ul class="sublist groups">
			<?php foreach ( incassoos_get_grouped_users() as $group ) : ?>

			<li id="group-<?php echo $group->id; ?>" class="group">
				<h4 class="sublist-header item-content"><?php echo esc_html( $group->name ); ?></h4>

				<ul class="users">
					<?php foreach ( $group->users as $user ) : ?>

					<li id="user-<?php echo $user->ID; ?>" class="consumer <?php echo implode( ' ', incassoos_admin_consumers_list_class( $user ) ); ?>">
						<button type="button" class="consumer-name"><?php echo incassoos_get_user_display_name( $user->ID ); ?></button>

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
	</form>

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

	// Add class for noshow consumers
	if ( $user->get( '_incassoos_noshow' ) ) {
		$class[] = 'noshow';
	}

	// Add class for missing IBAN
	if ( ! incassoos_get_user_iban( $user ) ) {
		$class[] = 'no-iban';
	}

	return (array) apply_filters( 'incassoos_admin_consumers_list_class', $class, $user );
}
