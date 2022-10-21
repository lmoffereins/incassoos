<?php

/**
 * Incassoos BuddyPress Functions
 *
 * @package Incassoos
 * @subpackage BuddyPress
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Return whether to use the BuddyPress email template
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_bp_use_email_template'
 *
 * @param  bool $default Default value
 * @return bool Use BuddyPress email template?
 */
function incassoos_bp_use_email_template( $default = false ) {
	return (bool) apply_filters( 'incassoos_bp_use_email_template', get_option( '_incassoos_bp_use_email_template', $default ) );
}

/** Settings ******************************************************************/

/**
 * Use BuddyPress email template
 *
 * @since 1.0.0
 */
function incassoos_bp_admin_setting_callback_use_email_template() { ?>

	<input name="_incassoos_bp_use_email_template" id="_incassoos_bp_use_email_template" type="checkbox" value="1" <?php checked( get_option( '_incassoos_bp_use_email_template', false ) ); ?>>
	<label for="_incassoos_bp_use_email_template"><?php esc_html_e( 'Use the BuddyPress email template for Incassoos emails.', 'incassoos' ); ?></label>

	<?php
}
