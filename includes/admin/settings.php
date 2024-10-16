<?php

/**
 * Incassoos Settings Functions
 *
 * @package Incassoos
 * @subpackage Administration
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Settings **************************************************************/

/**
 * Register plugin settings
 *
 * @see incassoos_register_settings()
 *
 * @since 1.0.0
 */
function incassoos_admin_register_settings() {

	// Bail if no sections available
	$sections = incassoos_admin_get_settings_sections();
	if ( empty( $sections ) )
		return false;

	// Loop through sections
	foreach ( (array) $sections as $section_id => $section ) {

		// Only add section and fields if section has fields
		$fields = incassoos_admin_get_settings_fields_for_section( $section_id );
		if ( empty( $fields ) )
			continue;

		// Define section page
		if ( ! empty( $section['page'] ) ) {
			$page = $section['page'];
		} else {
			$page = 'incassoos';
		}

		// Add the section
		add_settings_section( $section_id, $section['title'], $section['callback'], $page );

		// Loop through fields for this section
		foreach ( (array) $fields as $field_id => $field ) {

			// Add the field
			if ( ! empty( $field['callback'] ) && ! empty( $field['title'] ) ) {
				add_settings_field( $field_id, $field['title'], $field['callback'], $page, $section_id, $field['args'] );
			}

			/**
			 * Setting is registered in {@see incassoos_register_settings()}.
			 */
		}
	}
}

/**
 * Return plugin settings sections
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_admin_get_settings_sections' with the sections
 * @return array Settings sections
 */
function incassoos_admin_get_settings_sections() {
	return (array) apply_filters( 'incassoos_admin_get_settings_sections', array(

		// Main settings
		'incassoos_settings_main' => array(
			'title'    => __( 'Main Settings', 'incassoos' ),
			'callback' => 'incassoos_admin_setting_callback_main_section',
			'page'     => 'incassoos'
		),

		// User settings
		'incassoos_settings_user' => array(
			'title'    => __( 'User Settings', 'incassoos' ),
			'callback' => 'incassoos_admin_setting_callback_user_section',
			'page'     => 'incassoos'
		),

		// Collection settings
		'incassoos_settings_collection' => array(
			'title'    => __( 'Collection Settings', 'incassoos' ),
			'callback' => 'incassoos_admin_setting_callback_collection_section',
			'page'     => 'incassoos'
		),

		// Occasion settings
		'incassoos_settings_occasion' => array(
			'title'    => __( 'Occasion Settings', 'incassoos' ),
			'callback' => 'incassoos_admin_setting_callback_occasion_section',
			'page'     => 'incassoos'
		),

		// Order settings
		'incassoos_settings_order' => array(
			'title'    => __( 'Order Settings', 'incassoos' ),
			'callback' => 'incassoos_admin_setting_callback_order_section',
			'page'     => 'incassoos'
		),

		// Email settings
		'incassoos_settings_email' => array(
			'title'    => __( 'Email Settings', 'incassoos' ),
			'callback' => 'incassoos_admin_setting_callback_email_section',
			'page'     => 'incassoos'
		),

		// Slug settings
		'incassoos_settings_slugs' => array(
			'title'    => __( 'Slug Settings', 'incassoos' ),
			'callback' => 'incassoos_admin_setting_callback_slugs_section',
			'page'     => 'incassoos'
		),

		// Application settings
		'incassoos_settings_application' => array(
			'title'    => __( 'Application Settings', 'incassoos' ),
			'callback' => 'incassoos_admin_setting_callback_application_section',
			'page'     => 'incassoos'
		),
	) );
}

/**
 * Return plugin settings fields
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_admin_get_settings_fields' with the fields
 * @return array Settings fields
 */
function incassoos_admin_get_settings_fields() {
	return (array) apply_filters( 'incassoos_admin_get_settings_fields', array(

		/** Main Section **************************************************/

		'incassoos_settings_main' => array(

			// Currency
			'_incassoos_currency' => array(
				'title'             => esc_html__( 'Currency', 'incassoos' ),
				'callback'          => 'incassoos_admin_setting_callback_currency',
				'sanitize_callback' => 'incassoos_sanitize_currency',
				'args'              => array()
			),
		),

		/** User Section **************************************************/

		'incassoos_settings_user' => array(),

		/** Collection Section ********************************************/

		'incassoos_settings_collection' => array(

			// Transaction description
			'_incassoos_transaction_description' => array(
				'title'             => esc_html__( 'Transaction Description', 'incassoos' ),
				'callback'          => 'incassoos_admin_setting_callback_transaction_description',
				'sanitize_callback' => 'incassoos_sanitize_iso20022_before_tokens',
				'args'              => array()
			),

			// Organization Name
			'_incassoos_organization_name' => array(
				'title'             => esc_html__( 'Organization Name', 'incassoos' ),
				'callback'          => 'incassoos_admin_setting_callback_organization_name',
				'sanitize_callback' => 'incassoos_sanitize_iso20022',
				'args'              => array()
			),

			// Account Holder
			'_incassoos_account_holder' => array(
				'title'             => esc_html__( 'Account Holder', 'incassoos' ),
				'callback'          => 'incassoos_admin_setting_callback_account_holder',
				'sanitize_callback' => 'incassoos_sanitize_iso20022',
				'args'              => array()
			),

			// Account IBAN
			'_incassoos_account_iban' => array(
				'title'             => esc_html__( 'Account IBAN', 'incassoos' ),
				'callback'          => 'incassoos_admin_setting_callback_account_iban',
				'sanitize_callback' => 'incassoos_sanitize_account_iban',
				'args'              => array()
			),

			// SEPA Creditor Identifier
			'_incassoos_sepa_creditor_id' => array(
				'title'             => esc_html__( 'SEPA Creditor Identifier', 'incassoos' ),
				'callback'          => 'incassoos_admin_setting_callback_sepa_creditor_id',
				'sanitize_callback' => 'incassoos_sanitize_sepa_creditor_id',
				'args'              => array()
			),

			// Withdrawal delay
			'_incassoos_collection_withdrawal_delay' => array(
				'title'             => esc_html__( 'Withdrawal delay', 'incassoos' ),
				'callback'          => 'incassoos_admin_setting_callback_collection_withdrawal_delay',
				'sanitize_callback' => 'absint',
				'args'              => array()
			),
		),

		/** Occasion Section **********************************************/

		'incassoos_settings_occasion' => array(

			// Email notification on close/reopen
			'_incassoos_occasion_email_on_close_or_reopen' => array(
				'title'             => esc_html__( 'Email on Close or Reopen', 'incassoos' ),
				'callback'          => 'incassoos_admin_setting_callback_occasion_email_on_close_or_reopen',
				'sanitize_callback' => 'absint',
				'args'              => array()
			),
		),

		/** Order Section *************************************************/

		'incassoos_settings_order' => array(

			// Order time lock
			'_incassoos_order_time_lock' => array(
				'title'             => esc_html__( 'Time Lock', 'incassoos' ),
				'callback'          => 'incassoos_admin_setting_callback_order_time_lock',
				'sanitize_callback' => 'absint',
				'args'              => array()
			),
		),

		/** Email Section *************************************************/

		'incassoos_settings_email' => array(

			// Sender email address
			'_incassoos_sender_email_address' => array(
				'title'             => esc_html__( 'Sender Email Address', 'incassoos' ),
				'callback'          => 'incassoos_admin_setting_callback_sender_email_address',
				'sanitize_callback' => 'sanitize_email',
				'args'              => array()
			),

			// Custom salutation
			'_incassoos_custom_email_salutation' => array(
				'title'             => esc_html__( 'Custom Salutation', 'incassoos' ),
				'callback'          => 'incassoos_admin_setting_callback_richtext',
				'sanitize_callback' => 'incassoos_sanitize_richtext',
				'args'              => array(
					'setting'     => '_incassoos_custom_email_salutation',
					'description' => sprintf( esc_html__( 'Use the %s tag to insert the name of the recipient.', 'incassoos' ), '<code>{{recipient.name}}</code>' )
				)
			),

			// Custom closing
			'_incassoos_custom_email_closing' => array(
				'title'             => esc_html__( 'Custom Closing', 'incassoos' ),
				'callback'          => 'incassoos_admin_setting_callback_richtext',
				'sanitize_callback' => 'incassoos_sanitize_richtext',
				'args'              => array(
					'setting' => '_incassoos_custom_email_closing'
				)
			)
		),

		/** Slugs Section *************************************************/

		'incassoos_settings_slugs' => array(

			// Application slug
			'_incassoos_app_slug' => array(
				'title'             => esc_html__( 'Application UI', 'incassoos' ),
				'callback'          => 'incassoos_admin_setting_callback_slug',
				'sanitize_callback' => 'incassoos_sanitize_slug',
				'args'              => array(
					'setting'     => '_incassoos_app_slug',
					'default'     => 'incassoos',
					'description' => esc_html__( "This slug will only be used when the 'Front page' setting is not enabled.", 'incassoos' )
				)
			),
		),

		/** Application Section *******************************************/

		'incassoos_settings_application' => array(

			// Application on front page
			'_incassoos_app_on_front' => array(
				'title'             => esc_html__( 'Front page', 'incassoos' ),
				'callback'          => 'incassoos_admin_setting_callback_app_on_front',
				'sanitize_callback' => 'intval',
				'args'              => array()
			),

			// // JWT Authentication
			// '_incassoos_jwt_auth_enabled' => array(
			// 	'title'             => esc_html__( 'Authentication', 'incassoos' ),
			// 	'callback'          => 'incassoos_admin_setting_callback_jwt_auth_enabled',
			// 	'sanitize_callback' => 'incassoos_sanitize_jwt_auth_enabled',
			// 	'args'              => array()
			// ),

			// // JWT Secret
			// '_incassoos_jwt_auth_secret' => array(
			// 	'title'             => esc_html__( 'Secret Key', 'incassoos' ),
			// 	'callback'          => 'incassoos_admin_setting_callback_jwt_auth_secret',
			// 	'sanitize_callback' => 'remove_accents',
			// 	'args'              => array()
			// ),

			// // JWT Invalidate Tokens
			// '_incassoos_jwt_auth_invalidate_tokens' => array(
			// 	'title'             => esc_html__( 'Invalidate Tokens', 'incassoos' ),
			// 	'callback'          => 'incassoos_admin_setting_callback_jwt_auth_invalidate_tokens',
			// 	'sanitize_callback' => false,
			// 	'args'              => array()
			// ),
		),
	) );
}

/**
 * Get settings fields by section
 *
 * @since 1.0.0
 *
 * @param string $section_id Section id
 * @return mixed False if section is invalid, array of fields otherwise
 */
function incassoos_admin_get_settings_fields_for_section( $section_id = '' ) {

	// Bail if section is empty
	if ( empty( $section_id ) )
		return false;

	// Bail when the current user cannot see this section
	if ( ! current_user_can( $section_id ) )
		return false;

	$fields = incassoos_admin_get_settings_fields();
	$retval = isset( $fields[ $section_id ] ) ? $fields[ $section_id ] : false;

	return (array) apply_filters( 'incassoos_admin_get_settings_fields_for_section', $retval, $section_id );
}

/**
 * Return whether the admin page has registered settings
 *
 * @since 1.0.0
 *
 * @param string $page
 * @return bool Does the admin page have settings?
 */
function incassoos_admin_page_has_settings( $page = '' ) {

	// Bail when page is empty
	if ( empty( $page ) )
		return false;

	// Loop through the available sections
	$sections = wp_list_filter( incassoos_admin_get_settings_sections(), array( 'page' => $page ) );
	foreach ( (array) $sections as $section_id => $section ) {

		// Find out whether the section has fields
		$fields = incassoos_admin_get_settings_fields_for_section( $section_id );
		if ( ! empty( $fields ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Return the capability for updating plugin settings
 *
 * The settings page capability is further mapped in `Incassoos_Admin::map_meta_caps()`.
 *
 * @since 1.0.0
 *
 * @return string Capability
 */
function incassoos_admin_get_option_page_cap() {
	return 'incassoos_admin_page-incassoos-settings';
}

/** Main Section **********************************************************/

/**
 * Main settings section description for the settings page
 *
 * @since 1.0.0
 */
function incassoos_admin_setting_callback_main_section() { /* Nothing to display */ }

/**
 * Currency setting field
 *
 * @since 1.0.0
 */
function incassoos_admin_setting_callback_currency() {
	$value = get_option( '_incassoos_currency', '' );

	?>

	<select name="_incassoos_currency" id="_incassoos_currency">
		<?php foreach ( incassoos_get_currencies() as $currency => $args ) : ?>
			<option value="<?php echo esc_attr( $currency ); ?>" <?php selected( $currency, $value ); ?>><?php printf( '%s (%s)', esc_html( $args['name'] ), esc_html( $args['symbol'] ) ); ?></option>
		<?php endforeach; ?>
	</select>

	<?php
}

/** User Section **********************************************************/

/**
 * User settings section description for the settings page
 *
 * @since 1.0.0
 */
function incassoos_admin_setting_callback_user_section() { ?>

	<p><?php esc_html_e( 'Manage roles and other user related settings.', 'incassoos' ); ?></p>

	<?php
}

/**
 * Register settings to manage users for each role
 *
 * @since 1.0.0
 *
 * @param  array $settings Settings fields
 * @return array Settings fields
 */
function incassoos_admin_user_roles_settings_fields( $settings ) {

	// Register settings for each role
	foreach ( incassoos_get_dynamic_roles() as $role => $args ) {
		$settings['incassoos_settings_user']["_incassoos_users_for_role-{$role}"] = array(
			'title'             => sprintf( esc_html__( 'Users for role %s', 'incassoos' ), $args['name'] ),
			'callback'          => 'incassoos_admin_setting_callback_users_for_role',
			'sanitize_callback' => 'incassoos_sanitize_user_list',
			'args'              => array(
				'role' => $role,
				'name' => $args['name']
			)
		);
	}

	return $settings;
}

/**
 * Currency setting field
 *
 * @since 1.0.0
 */
function incassoos_admin_setting_callback_users_for_role( $args ) {

	// Bail when the setting is not defined
	if ( ! isset( $args['role'] ) )
		return;

	$setting = '_incassoos_users_for_role-' . $args['role'];
	$value = implode( ',', get_option( $setting, array() ) );

	?>

	<input name="<?php echo $setting; ?>" id="<?php echo $setting; ?>" type="text" class="regular-text" value="<?php echo $value; ?>" />

	<p class="description"><?php printf( esc_html__( 'Select the users that should have the role of %s.', 'incassoos' ), $args['name'] ); ?></p>

	<?php
}

/** Collection Section ****************************************************/

/**
 * Collection settings section description for the settings page
 *
 * @since 1.0.0
 */
function incassoos_admin_setting_callback_collection_section() { ?>

	<p><?php esc_html_e( 'Manage the details for the management of collections.', 'incassoos' ); ?></p>

	<?php
}

/**
 * Transaction description setting field
 *
 * @since 1.0.0
 */
function incassoos_admin_setting_callback_transaction_description() { ?>

	<input name="_incassoos_transaction_description" id="_incassoos_transaction_description" type="text" class="regular-text" value="<?php echo get_option( '_incassoos_transaction_description', '' ); ?>" />

	<p class="description"><?php printf( esc_html__( 'Use the %s tag to insert the title of the related collection.', 'incassoos' ), '<code>{{collection.title}}</code>' ); ?></p>

	<?php
}

/**
 * Organization name setting field
 *
 * @since 1.0.0
 */
function incassoos_admin_setting_callback_organization_name() { ?>

	<input name="_incassoos_organization_name" id="_incassoos_organization_name" type="text" class="regular-text" value="<?php echo get_option( '_incassoos_organization_name', '' ); ?>" />

	<?php
}

/**
 * Organisation creditor name setting field
 *
 * @since 1.0.0
 */
function incassoos_admin_setting_callback_account_holder() { ?>

	<input name="_incassoos_account_holder" id="_incassoos_account_holder" type="text" class="regular-text" value="<?php echo get_option( '_incassoos_account_holder', '' ); ?>" />

	<?php
}

/**
 * Organisation creditor IBAN setting field
 *
 * @since 1.0.0
 */
function incassoos_admin_setting_callback_account_iban() {

	// Get option value
	$option = get_option( '_incassoos_account_iban', '' );

	?>

	<input name="_incassoos_account_iban" id="_incassoos_account_iban" type="text" class="regular-text" value="<?php echo $option; ?>" />

	<?php

	// Provide way for decrypting option
	incassoos_admin_setting_decrypt_button( array( 'option_name' => '_incassoos_account_iban', 'option_value' => $option ) );
}

/**
 * Organisation SEPA creditor identifier setting field
 *
 * @since 1.0.0
 */
function incassoos_admin_setting_callback_sepa_creditor_id() {

	// Get option value
	$option = get_option( '_incassoos_sepa_creditor_id', '' );

	?>

	<input name="_incassoos_sepa_creditor_id" id="_incassoos_sepa_creditor_id" type="text" class="regular-text" value="<?php echo $option; ?>" />

	<?php

	// Provide way for decrypting option
	incassoos_admin_setting_decrypt_button( array( 'option_name' => '_incassoos_sepa_creditor_id', 'option_value' => $option ) );
}

/**
 * Default collection withdrawal margin setting field
 *
 * @since 1.0.0
 */
function incassoos_admin_setting_callback_collection_withdrawal_delay() { ?>

	<input name="_incassoos_collection_withdrawal_delay" id="_incassoos_collection_withdrawal_delay" type="number" min="0" class="small-text" value="<?php echo get_option( '_incassoos_collection_withdrawal_delay', 5 ); ?>" />

	<p class="description"><?php esc_html_e( 'The amount of days between communicating the collection withdrawal and the actual transaction execution.', 'incassoos' ); ?></p>

	<?php
}

/** Occasion Section ******************************************************/

/**
 * Occasion settings section description for the settings page
 *
 * @since 1.0.0
 */
function incassoos_admin_setting_callback_occasion_section() { ?>

	<p><?php esc_html_e( 'Manage the details for the management of occasions.', 'incassoos' ); ?></p>

	<?php
}

/**
 * Occasion email on close or reopen setting field
 *
 * @since 1.0.0
 */
function incassoos_admin_setting_callback_occasion_email_on_close_or_reopen() { ?>

	<input name="_incassoos_occasion_email_on_close_or_reopen" id="_incassoos_occasion_email_on_close_or_reopen" type="checkbox" value="1" <?php checked( get_option( '_incassoos_occasion_email_on_close_or_reopen', false ) ); ?>>
	<label for="_incassoos_occasion_email_on_close_or_reopen"><?php esc_html_e( 'Send an email to each Supervisor when an occasion is closed or reopened.', 'incassoos' ); ?></label>

	<?php
}

/** Order Section *********************************************************/

/**
 * Order settings section description for the settings page
 *
 * @since 1.0.0
 */
function incassoos_admin_setting_callback_order_section() { ?>

	<p><?php esc_html_e( 'Manage the details for the management of orders.', 'incassoos' ); ?></p>

	<?php
}

/**
 * Order time lock setting field
 *
 * @since 1.0.0
 */
function incassoos_admin_setting_callback_order_time_lock() {

	ob_start(); ?>

	<input type="number" step="1" min="0" name="_incassoos_order_time_lock" id="_incassoos_order_time_lock" class="code small-text" value="<?php echo get_option( '_incassoos_order_time_lock', 0 ); ?>">

	<?php $input = ob_get_clean(); ?>

	<label for="_incassoos_order_time_lock">
		<?php printf( esc_html__( 'Enable editing of uncollected orders up to %s minutes after they are created.', 'incassoos' ), $input ); ?>
	</label>

	<?php
}

/** Email Section *********************************************************/

/**
 * Email settings section description for the settings page
 *
 * @since 1.0.0
 */
function incassoos_admin_setting_callback_email_section() { ?>

	<p><?php esc_html_e( 'Customize the details of outgoing emails.', 'incassoos' ); ?></p>

	<?php
}

/**
 * Outgoing email address setting field
 *
 * @since 1.0.0
 */
function incassoos_admin_setting_callback_sender_email_address() { ?>

	<input name="_incassoos_sender_email_address" id="_incassoos_sender_email_address" type="text" class="regular-text" value="<?php echo get_option( '_incassoos_sender_email_address', '' ); ?>" />

	<?php
}

/**
 * Rich text setting field
 *
 * @since 1.0.0
 *
 * @param array $args Setting field arguments
 */
function incassoos_admin_setting_callback_richtext( $args = array() ) {

	// Bail when the setting is not defined
	if ( ! isset( $args['setting'] ) || empty( $args['setting'] ) )
		return;

	$setting = esc_attr( $args['setting'] );
	$default = isset( $args['default'] ) ? $args['default'] : '';

	// Use WP Editor
	wp_editor( get_option( $setting, $default ), $setting, array(
		'textarea_rows' => 5,
		'editor_height' => 150,
		'media_buttons' => false,
		'quicktags'     => false,
		'teeny'         => true
	) );

	if ( isset( $args['description'] ) ) {
		echo '<p class="description">' .  $args['description'] . '</p>';
	}
}

/** Slugs Section *********************************************************/

/**
 * Slugs settings section description for the settings page
 *
 * @since 1.0.0
 */
function incassoos_admin_setting_callback_slugs_section() {

	// Flush rewrite rules when this section is saved
	if ( isset( $_GET['settings-updated'] ) && isset( $_GET['page'] ) ) {
		incassoos_delete_rewrite_rules();
	}

	?>

	<p><?php esc_html_e( "Customize the structure of the plugin's front-facing pages.", 'incassoos' ); ?></p>

	<?php
}

/**
 * Slug setting field
 *
 * @since 1.0.0
 *
 * @param array $args Setting field arguments
 */
function incassoos_admin_setting_callback_slug( $args = array() ) {

	// Bail when the setting is not defined
	if ( ! isset( $args['setting'] ) || empty( $args['setting'] ) )
		return;

	$setting = esc_attr( $args['setting'] );
	$default = isset( $args['default'] ) ? $args['default'] : '';

	?>

	<input name="<?php echo $setting; ?>" id="<?php echo $setting; ?>" type="text" class="regular-text code" value="<?php echo get_option( $args['setting'], $default ); ?>" />

	<?php if ( isset( $args['description'] ) ) {
		echo '<p class="description">' .  $args['description'] . '</p>';
	}
}

/** Application Section ***************************************************/

/**
 * Application settings section description for the settings page
 *
 * @since 1.0.0
 */
function incassoos_admin_setting_callback_application_section() { ?>

	<p><?php esc_html_e( 'Manage settings for the use of the Incassoos application on the website and connected apps outside of the website.', 'incassoos' ); ?></p>

	<?php
}

/**
 * Application on front page setting field
 *
 * @since 1.0.0
 */
function incassoos_admin_setting_callback_app_on_front() { ?>

	<input name="_incassoos_app_on_front" id="_incassoos_app_on_front" type="checkbox" value="1" <?php checked( get_option( '_incassoos_app_on_front', false ) ); ?>>
	<label for="_incassoos_app_on_front"><?php esc_html_e( 'Put the Incassoos application on the front page of this site.', 'incassoos' ); ?></label>

	<?php
}

/**
 * JWT Authentication setting field
 *
 * @since 1.0.0
 */
function incassoos_admin_setting_callback_jwt_auth_enabled() { ?>

	<input name="_incassoos_jwt_auth_enabled" id="_incassoos_jwt_auth_enabled" type="checkbox" value="1" <?php checked( get_option( '_incassoos_jwt_auth_enabled', false ) ); ?>>
	<label for="_incassoos_jwt_auth_enabled"><?php esc_html_e( 'Enable JSON Web Tokens (JWT) authentication for connecting Incassoos applications to this website.', 'incassoos' ); ?></label>

	<?php
}

/**
 * Sanitize the input for the JWT Authentication setting
 *
 * Updates htaccess rules when this setting is updated.
 *
 * @since 1.0.0
 * 
 * @param mixed $input Raw input 
 * @return int Sanitized input
 */
function incassoos_sanitize_jwt_auth_enabled( $input ) {

	// Sanitize input value
	$input = absint( $input );

	// Define htaccess lines
	$lines = $input ? array(
		'',
		'# ' . esc_html__( 'Enable JWT authentication', 'incassoos' ),
		// 'RewriteEngine on',
		// 'RewriteCond %{HTTP_AUTHORIZATION} ^(.*)',
		// 'RewriteRule ^(.*) - [E=HTTP_AUTHORIZATION:%1]'
		'SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1'
	) : '';

	// Update plugin section in htaccess file
	insert_with_markers( get_home_path() . '.htaccess', 'Incassoos / JWT Auth', $lines );

	return $input;
}

/**
 * JWT Authentication Secret setting field
 *
 * @since 1.0.0
 */
function incassoos_admin_setting_callback_jwt_auth_secret() { ?>

	<input name="_incassoos_jwt_auth_secret" id="_incassoos_jwt_auth_secret" type="text" class="regular-text code" value="<?php echo get_option( '_incassoos_jwt_auth_secret' ); ?>">

	<p>
		<?php printf( __( 'Provide the required secret key for encoding the JWT authentication with Incassoos applications. You can grab a randomly generated key from <a target="_blank" href="%s">WordPress\'s salt API</a>. Renew this value to invalidate all existing authenticated sessions.', 'incassoos' ), 'https://api.wordpress.org/secret-key/1.1/salt/' ); ?>
	</p>

	<?php
}

/**
 * JWT Invalidate Tokens setting field
 *
 * @since 1.0.0
 */
function incassoos_admin_setting_callback_jwt_auth_invalidate_tokens() {

	// Define action url
	$action_url = wp_nonce_url( add_query_arg( array( 'page' => 'incassoos-settings', 'action' => 'invalidate-tokens' ), admin_url( 'admin.php' ) ), 'jwt-auth-invalidate-tokens' );

	?>

	<p>
		<a class="button button-secondary" id="_incassoos_jwt_auth_invalidate_tokens" type="button" href="<?php echo esc_url( $action_url ); ?>"><?php esc_html_e( 'Invalidate tokens', 'incassoos' ); ?></a>
	</p>

	<p>
		<?php esc_html_e( 'This action invalidates all current user sessions and forces all application users to login again in order to connect with this site.', 'incassoos' ); ?>
	</p>

	<?php
}

/**
 * Invalidate JWT Authorization tokens when requested
 *
 * @since 1.0.0
 */
function incassoos_admin_jwt_auth_invalidate_tokens() {

	// Check request context and validity
	if ( isset( $_GET['page'] ) && 'incassoos-settings' === $_GET['page']
		&& isset( $_GET['action'] ) && 'invalidate-tokens' === $_GET['action']
		&& isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'jwt-auth-invalidate-tokens' )
		&& current_user_can( 'manage_options' )
	) {

		// Load REST controller
		require_once( incassoos()->includes_dir . 'classes/class-incassoos-rest-jwt-auth-controller.php' );

		// Invalidate all tokens
		$success = Incassoos_REST_JWT_Auth_Controller::invalidate_all();

		// Redirect to settings page
		wp_safe_redirect( add_query_arg( array( 'page' => 'incassoos-settings', 'tokens-invalidated' => true ), admin_url( 'admin.php' ) ) );
		exit;
	}
}

/**
 * Modify the list of settings fields for JWT Authorization
 *
 * @since 1.0.0
 *
 * @global WPDB $wpdb
 *
 * @param array $fields Settings fields
 * @return array Settings fields
 */
function incassoos_admin_jwt_auth_settings_fields( $fields ) {
	global $wpdb;

	// When no user token are registered, remove the Invalidate Tokens setting
	if ( ! $wpdb->get_var( $wpdb->prepare( "SELECT 1 FROM {$wpdb->usermeta} WHERE meta_key = %s", '_incassoos_jwt_auth_token' ) ) ) {
		unset( $fields['incassoos_settings_application']['_incassoos_jwt_auth_invalidate_tokens'] );
	}

	return $fields;
}

/**
 * Display a button for decrypting the singular option value
 *
 * @since 1.0.0
 *
 * @param array $args {
 *     List of arguments
 *
 *     @type string $option_name  The option name
 *     @type string $option_value The option's value to check for redaction
 * }
 */
function incassoos_admin_setting_decrypt_button( $args = array() ) {

	// Bail when the user cannot decrypt options
	if ( ! incassoos_is_encryption_enabled() || ! current_user_can( 'decrypt_incassoos_data' ) ) {
		return;
	}

	$args = wp_parse_args( $args, array(
		'option_name'  => '',
		'option_value' => ''
	) );

	// Bail when the option is not encrypted
	if ( ! incassoos_is_option_redacted( $args['option_name'], $args['option_value'] ) ) {
		return;
	}

	$key_id    = "incassoos-{$args['option_name']}-decryption-key";
	$button_id = "incassoos-decrypt-{$args['option_name']}";

	?>

	<div class="decrypt-option-value-wrapper require-decryption-key-wrapper">
		<label class="screen-reader-text" for="<?php echo $key_id; ?>"><?php esc_html_e( 'Decryption key', 'incassoos' ); ?></label>
		<input type="password" name="<?php echo $key_id; ?>" placeholder="<?php esc_attr_e( 'Decryption key&hellip;', 'incassoos' ); ?>" />
		<button type="button" id="<?php echo $button_id; ?>" class="button button-secondary decrypt-option-value" data-option-name="<?php echo $args['option_name']; ?>"><?php esc_html_e( 'Show value', 'incassoos' ); ?></button>
		<span class="spinner"></span>
	</div>

	<?php
}

/** Page ****************************************************************/

/**
 * Act when the Settings admin page is being loaded
 *
 * @see wp-admin/index.php
 *
 * @since 1.0.0
 *
 * @uses do_action() Calls 'incassoos_admin_load_settings_page'
 */
function incassoos_admin_load_settings_page() {
	do_action( 'incassoos_admin_load_settings_page' );
}

/**
 * Display custom admin notices on the settings page
 *
 * @since 1.0.0
 */
function incassoos_admin_settings_notices() {

	// Bail when this is not the settings page
	if ( ! isset( $_GET['page'] ) || 'incassoos-settings' !== $_GET['page'] )
		return;

	$messages = array();

	// JWT Authorization
	if ( isset( $_GET['tokens-invalidated'] ) && $_GET['tokens-invalidated'] ) {
		$messages[] = esc_html__( 'All application tokens are successfully invalidated.', 'incassoos' );
	}

	// Display notices
	foreach ( $messages as $message ) {
		wp_admin_notice( $message, array( 'type' => 'success', 'dismissible' => true, 'additional_classes' => array( 'updated' ) ) );
	}
}

/**
 * Output the contents of the Settings admin page
 *
 * @since 1.0.0
 */
function incassoos_admin_settings_page() {

	// Get the settings page name
	$settings_page = incassoos_admin_get_current_page();
	if ( 'incassoos-settings' === $settings_page ) {
		$settings_page = 'incassoos';
	}

	// Display settings errors
	settings_errors();

	?>

	<form action="options.php" method="post">

		<?php settings_fields( $settings_page ); ?>

		<?php do_settings_sections( $settings_page ); ?>

		<?php submit_button(); ?>

	</form>

	<?php
}
