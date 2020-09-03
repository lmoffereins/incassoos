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
 * @since 1.0.0
 */
function incassoos_register_settings() {

	// Bail if no sections available
	$sections = incassoos_admin_get_settings_sections();
	if ( empty( $sections ) )
		return false;

	// Loop through sections
	foreach ( (array) $sections as $section_id => $section ) {

		// Only proceed if current user can see this section
		if ( ! current_user_can( $section_id ) )
			continue;

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

			// Register the setting
			if ( ! empty( $field['sanitize_callback'] ) ) {
				register_setting( $page, $field_id, $field['sanitize_callback'] );
			}
		}
	}
}

/**
 * Return admin settings sections
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

		// Collecting settings
		'incassoos_settings_collecting' => array(
			'title'    => __( 'Collecting Settings', 'incassoos' ),
			'callback' => 'incassoos_admin_setting_callback_collecting_section',
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
 * Return admin settings fields
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

			// Order time lock
			'_incassoos_order_time_lock' => array(
				'title'             => esc_html__( 'Time Lock', 'incassoos' ),
				'callback'          => 'incassoos_admin_setting_callback_order_time_lock',
				'sanitize_callback' => 'absint',
				'args'              => array()
			),
		),

		/** User Section **************************************************/

		'incassoos_settings_user' => array(),

		/** Collecting Section ********************************************/

		'incassoos_settings_collecting' => array(

			// Transaction description
			'_incassoos_transaction_description' => array(
				'title'             => esc_html__( 'Transaction Description', 'incassoos' ),
				'callback'          => 'incassoos_admin_setting_callback_transaction_description',
				'sanitize_callback' => 'incassoos_sanitize_transaction_description',
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
				'sanitize_callback' => 'incassoos_sanitize_iban',
				'args'              => array()
			),

			// SEPA Creditor Identifier
			'_incassoos_sepa_creditor_id' => array(
				'title'             => esc_html__( 'SEPA Creditor Identifier', 'incassoos' ),
				'callback'          => 'incassoos_admin_setting_callback_sepa_creditor_id',
				'sanitize_callback' => '', // TODO: something else
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
					'description' => sprintf( esc_html__( 'Use the %s tag to insert the full name of the user at hand.', 'incassoos' ), '<code>%NAME%</code>' )
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

			// App
			'_incassoos_app_slug' => array(
				'title'             => esc_html__( 'App UI', 'incassoos' ),
				'callback'          => 'incassoos_admin_setting_callback_slug',
				'sanitize_callback' => 'incassoos_sanitize_slug',
				'args'              => array(
					'setting' => '_incassoos_app_slug',
					'default' => 'incassoos'
				)
			),
		),

		/** Application Section *******************************************/

		'incassoos_settings_application' => array(

			// JWT Authentication
			'_incassoos_jwt_auth_enabled' => array(
				'title'             => esc_html__( 'Authentication', 'incassoos' ),
				'callback'          => 'incassoos_admin_setting_callback_jwt_auth_enabled',
				'sanitize_callback' => 'incassoos_sanitize_jwt_auth_enabled',
				'args'              => array()
			),

			// JWT Secret
			'_incassoos_jwt_auth_secret' => array(
				'title'             => esc_html__( 'Secret Key', 'incassoos' ),
				'callback'          => 'incassoos_admin_setting_callback_jwt_auth_secret',
				'sanitize_callback' => 'remove_accents',
				'args'              => array()
			),

			// JWT Invalidate Tokens
			'_incassoos_jwt_auth_invalidate_tokens' => array(
				'title'             => esc_html__( 'Invalidate Tokens', 'incassoos' ),
				'callback'          => 'incassoos_admin_setting_callback_jwt_auth_invalidate_tokens',
				'sanitize_callback' => false,
				'args'              => array()
			),
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

/** Collecting Section ****************************************************/

/**
 * Collecting settings section description for the settings page
 *
 * @since 1.0.0
 */
function incassoos_admin_setting_callback_collecting_section() { ?>

	<p><?php esc_html_e( 'Manage the details for the creation of your debit collection documents.', 'incassoos' ); ?></p>

	<?php
}

/**
 * Transaction description setting field
 *
 * @since 1.0.0
 */
function incassoos_admin_setting_callback_transaction_description() { ?>

	<input name="_incassoos_transaction_description" id="_incassoos_transaction_description" type="text" class="regular-text" value="<?php echo get_option( '_incassoos_transaction_description', '' ); ?>" />

	<p class="description"><?php printf( esc_html__( 'Use the %s tag to insert the title of the collection at hand.', 'incassoos' ), '<code>%TITLE%</code>' ); ?></p>

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
 * Organisatsion creditor name setting field
 *
 * @since 1.0.0
 */
function incassoos_admin_setting_callback_account_holder() { ?>

	<input name="_incassoos_account_holder" id="_incassoos_account_holder" type="text" class="regular-text" value="<?php echo get_option( '_incassoos_account_holder', '' ); ?>" />

	<?php
}

/**
 * Organisatsion creditor IBAN setting field
 *
 * @since 1.0.0
 */
function incassoos_admin_setting_callback_account_iban() { ?>

	<input name="_incassoos_account_iban" id="_incassoos_account_iban" type="text" class="regular-text" value="<?php echo get_option( '_incassoos_account_iban', '' ); ?>" />

	<?php
}

/**
 * Organisatsion SEPA creditor identifier setting field
 *
 * @since 1.0.0
 */
function incassoos_admin_setting_callback_sepa_creditor_id() { ?>

	<input name="_incassoos_sepa_creditor_id" id="_incassoos_sepa_creditor_id" type="text" class="regular-text" value="<?php echo get_option( '_incassoos_sepa_creditor_id', '' ); ?>" />

	<?php
}

/** Email Section *********************************************************/

/**
 * Email settings section description for the settings page
 *
 * @since 1.0.0
 */
function incassoos_admin_setting_callback_email_section() { ?>

	<p><?php esc_html_e( "Define modifications for outgoing emails.", 'incassoos' ); ?></p>

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

	<p><?php esc_html_e( 'Manage authentication settings for the use of Incassoos applications outside of the website.', 'incassoos' ); ?></p>

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

	foreach ( $messages as $message ) {
		echo '<div class="notice updated is-dismissible"><p>' . $message . '</p></div>';
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
