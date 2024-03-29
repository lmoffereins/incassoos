<?php

/**
 * Incassoos Formatting Functions
 *
 * @package Incassoos
 * @subpackage Main
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Sanitization **************************************************************/

/**
 * Sanitize rich text content when saving the settings page.
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_sanitize_currency'
 * 
 * @param string $content
 * @return string
 */
function incassoos_sanitize_richtext( $content = '' ) {

	// Sanitize like a post content field
	$value = sanitize_post_field( 'content', $content, null, 'db' );

	return apply_filters( 'incassoos_sanitize_richtext', $value, $content );
}

/**
 * Sanitize currency when saving the settings page.
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_sanitize_currency'
 * 
 * @param string $currency
 * @return string
 */
function incassoos_sanitize_currency( $currency = '' ) {

	// Match a defined currency
	if ( in_array( $currency, array_keys( incassoos_get_currencies() ) ) ) {
		$value = $currency;

	// Default to USD
	} else {
		$value = 'USD';
	}

	return apply_filters( 'incassoos_sanitize_currency', $value, $currency );
}

/**
 * Sanitize XML content for ISO 20022 format before tokens are parsed when saving the settings page.
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_sanitize_iso20022_before_tokens'
 *
 * @param string $content
 * @return string
 */
function incassoos_sanitize_iso20022_before_tokens( $content = '' ) {

	// Setup tag switchers
	$replace_tags = array( '{{' => '/+', '}}' => '+/' );
	$restore_tags = array_combine( array_values( $replace_tags ), array_keys( $replace_tags ) );

	// Substitute parsable tags
	$value = strtr( $content, $replace_tags );

	// Parse ISO 20022
	$value = incassoos_sanitize_iso20022( $value );

	// Restore parsable tags
	$value = strtr( $value, $restore_tags );

	// Filter the result and return
	return apply_filters( 'incassoos_sanitize_iso20022_before_tokens', $value, $content );
}

/**
 * Sanitize XML content for ISO 20022 format when saving the settings page.
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_sanitize_iso20022'
 *
 * @param string $content
 * @return string
 */
function incassoos_sanitize_iso20022( $content = '' ) {

	// Parse accented characters
	$value = remove_accents( $content );

	// Don't allow chars outside of basic UTF-8
	$value = preg_replace( '/[^a-zA-Z0-9\/\-?:\(\).,`+\s]/', '', $value );

	// Trim off whitespace
	$value = trim( $value );

	// Limit string length to 70 chars
	$value = substr( $value, 0, 70 );

	// Filter the result and return
	return apply_filters( 'incassoos_sanitize_iso20022', $value, $content );
}

/**
 * Sanitize permalink slugs when saving the settings page.
 * 
 * @see bbp_sanitize_slug()
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_sanitize_slug'
 *
 * @param string $slug
 * @return string
 */
function incassoos_sanitize_slug( $slug = '' ) {

	// Don't allow multiple slashes in a row
	$value = preg_replace( '#/+#', '/', str_replace( '#', '', $slug ) );

	// Strip out unsafe or unusable chars
	$value = esc_url_raw( $value );

	// esc_url_raw() adds a scheme via esc_url(), so let's remove it
	$value = str_replace( 'http://', '', $value );

	// Trim off first and last slashes.
	//
	// We already prevent double slashing elsewhere, but let's prevent
	// accidental poisoning of options values where we can.
	$value = ltrim( $value, '/' );
	$value = rtrim( $value, '/' );

	// Filter the result and return
	return apply_filters( 'incassoos_sanitize_slug', $value, $slug );
}

/**
 * Sanitize user lists when saving the settings page.
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_sanitize_user_list'
 *
 * @param  string $list List of user slugs or ids
 * @return array
 */
function incassoos_sanitize_user_list( $list = '' ) {

	// Separate values
	$value = wp_parse_list( $list );

	foreach ( $value as $key => $id_or_slug ) {
		$user = false;

		// Find user by id or slug
		if ( is_numeric( $id_or_slug ) ) {
			$user = get_userdata( (int) $id_or_slug );
		} else {
			$user = get_user_by( 'slug', $id_or_slug );
		}

		// Remove user when it does not exist
		if ( empty( $user ) || ! $user->exists() ) {
			unset( $value[ $key ] );

		// Keep the id
		} else {
			$value[ $key ] = $user->ID;
		}
	}

	// Reset array
	$value = array_values( $value );

	return apply_filters( 'incassoos_sanitize_user_list', $value, $list );
}

/**
 * Return a redacted version of a text
 *
 * The returned value may be smaller in length, depending on how
 * the `$length` parameter is used.
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_redact_text'
 *
 * @param  string $input Text to apply redaction to
 * @param  array  $args  Additional parameters {
 *  - $keep   int|array Optional. Amount of trailing characters to not redact. Provide two values in an
 *                      array to keep leading characters as well.
 *  - $char   string    Optional. Character to use for redaction. Defaults to 'X'.
 *  - $length bool|int  Optional. The length of the applied redaction. When True the exact length of the
 *                      redacted text is applied. Defaults to True.
 * }
 * @return string Redacted text
 */
function incassoos_redact_text( $input, $args = array() ) {
	$input = (string) $input;

	// Parse defaults
	$args = wp_parse_args( $args, array(
		'keep'   => 3,
		'char'   => 'X',
		'length' => true
	) );

	$redacted = '';
	$keep     = array_map( 'absint', array_values( (array) $args['keep'] ) );

	// Default to keep 0 leading characters
	if ( 1 === count( $keep ) ) {
		$keep = array( 0, $keep[0] );
	}

	// Ignore leading keeps when the total keep length matches the input size
	if ( strlen( $input ) <= array_sum( $keep ) ) {
		$keep[0] = 0;
	}

	// Require at least one redaction before keeps
	$keep[1] = min( max( strlen( $input ) - 1, 0 ), $keep[1] );

	// WHen there is a value to redact
	if ( $input ) {

		// Define redaction
		$redaction = str_repeat( $args['char'], ( true === $args['length'] ) ? max( strlen( $input ) - $keep[0] - $keep[1], 1) : (int) $args['length'] );

		// Create redacted text
		$redacted = substr( $input, 0, $keep[0] ) . $redaction . substr( $input, strlen( $input ) - $keep[1] );
	}

	return apply_filters( 'incassoos_redact_text', $redacted, $input, $args );
}

/**
 * Return whether the given value is already redacted
 *
 * @since 1.0.0
 *
 * @param  string $input Value to check
 * @param  array  $args  Redaction parameters. See {@see incassoos_redact_text()}.
 * @return bool Is value redacted?
 */
function incassoos_is_value_redacted( $input, $args = array() ) {
	return ! empty( $input ) && $input === incassoos_redact_text( $input, $args );
}

/**
 * Replace all tokens in the input text with appropriate values
 *
 * @since 1.0.0
 *
 * @see bp_core_replace_tokens_in_text()
 *
 * @uses apply_filters() Calls 'incassoos_replace_tokens_in_text'
 *
 * @param string $text   Text to replace tokens in.
 * @param array  $tokens Token names and replacement values for the $text.
 * @return string
 */
function incassoos_replace_tokens_in_text( $text, $tokens ) {
	$unescaped = array();
	$escaped   = array();

	foreach ( $tokens as $token => $value ) {
		if ( ! is_string( $value ) && is_callable( $value ) ) {
			$value = call_user_func( $value );
		}

		// Tokens could be objects or arrays.
		if ( ! is_scalar( $value ) ) {
			continue;
		}

		$unescaped[ '{{{' . $token . '}}}' ] = $value;
		$escaped[ '{{' . $token . '}}' ]     = esc_html( $value );
	}

	$text = strtr( $text, $unescaped ); // Do first
	$text = strtr( $text, $escaped );

	return apply_filters( 'incassoos_replace_tokens_in_text', $text, $tokens );
}

/** Settings **************************************************************/

/**
 * Filter the value of the Account IBAN option before update
 *
 * @since 1.0.0
 *
 * @param  mixed $value Input value
 * @return mixed Modified value
 */
function incassoos_sanitize_account_iban( $value ) {

	// Ignore redacted values
	if ( incassoos_is_iban_redacted( $value ) ) {
		return $value;
	}

	// Sanitize and validate IBAN value
	$iban = incassoos_sanitize_iban( $value, true );

	// Report settings error
	if ( ! $iban && function_exists( 'add_settings_error' ) ) {
		add_settings_error( '_incassoos_account_iban', "invalid_{$option}", esc_html__( 'Invalid value for Account IBAN.', 'incassoos' ) );
	}

	return $value;
}

/**
 * Filter the value of the SEPA Creditor Identifier option before update
 *
 * @since 1.0.0
 *
 * @param  mixed $value Input value
 * @return mixed Modified value
 */
function incassoos_sanitize_sepa_creditor_id( $value ) {

	// TODO: define sanitization method for SEPA Creditor Identifier

	return $value;
}

/** Validation ****************************************************************/

/**
 * Validates a post title
 *
 * @since 1.0.0
 *
 * @param string $value Post title
 * @return string|WP_Error Title or error when invalid
 */
function incassoos_validate_title( $value ) {
	if ( empty( $value ) ) {
		return new WP_Error( 'incassoos_empty_title', __( 'Empty title.', 'incassoos' ) );
	}

	return $value;
}

/**
 * Validates an RFC3339 time as a datestamp.
 *
 * @since 1.0.0
 *
 * @param string $value RFC3339 timestamp.
 * @param string $format Date format to validate. Accepts 'd-m-Y' and 'Y-m-d'. Defaults to 'Y-m-d'.
 * @return string|WP_Error Datestamp or error when invalid
 */
function incassoos_validate_date( $value, $format = 'Y-m-d' ) {

	// Define format's regex
	switch ( $format ) {
		case 'd-m-Y' :
			$regex = '#^\d{2}-\d{2}-\d{4}$#';
			break;
		default :
			$regex = '#^\d{4}-\d{2}-\d{2}$#';
	}

	if ( empty( $value ) ) {
		return new WP_Error( 'incassoos_empty_date', __( 'Empty date.', 'incassoos' ) );
	}

	if ( ! preg_match( $regex, $value, $matches ) ) {
		return new WP_Error( 'incassoos_invalid_date', __( 'Invalid date.', 'incassoos' ) );
	}

	return $value;
}

/**
 * Return whether the value is a valid price
 *
 * @since 1.0.0
 *
 * @param mixed $value Value to validate
 * @param array $options {
 *     List of validation options
 *
 *     @type bool $allow_negative Optional. Whether to allow for negative values. Defaults to false.
 *     @type bool $allow_zero     Optional. Whether to allow for zero. Defaults to false.
 * }
 * @return string|WP_Error Price or error when invalid
 */
function incassoos_validate_price( $value, $options = array() ) {
	$value   = floatval( $value );
	$options = wp_parse_args( $options, array(
		'allow_negative' => false,
		'allow_zero'     => false
	) );

	if ( empty( $value ) && ! $options['allow_zero'] ) {
		return new WP_Error( 'incassoos_empty_price', __( 'Empty price.', 'incassoos' ) );
	}

	if ( $value < 0 && ! $options['allow_negative'] ) {
		return new WP_Error( 'incassoos_invalid_price', __( 'Invalid price.', 'incassoos' ) );
	}

	return $value;
}

/**
 * Return whether the value is a valid order consumer identifier
 *
 * Checks for both user ids and consumer types.
 *
 * @since 1.0.0
 *
 * @param  mixed $value Value to validate
 * @return mixed|WP_Error Validated consumer id or error when invalid
 */
function incassoos_validate_consumer_id( $value ) {
	if ( empty( $value ) ) {
		return new WP_Error( 'incassoos_user_invalid_id_or_type', __( 'Invalid consumer ID or consumer type.', 'incassoos' ) );
	}

	// Consumer ID
	if ( is_numeric( $value ) ) {
		$user = get_userdata( (int) $value );

		if ( empty( $user ) || ! $user->exists() ) {
			return new WP_Error( 'incassoos_user_invalid_id', __( 'Invalid consumer ID.', 'incassoos' ) );
		}

	// Consumer type
	} elseif ( ! incassoos_consumer_type_exists( $value ) ) {
		return new WP_Error( 'incassoos_consumer_invalid_type', __( 'Invalid consumer type.', 'incassoos' ) );
	}

	return $value;
}

/** Generic *******************************************************************/

/**
 * Convert given MySQL date string into a different format corrected for GMT
 *
 * @since 1.0.0
 *
 * @see mysql2date()
 *
 * @param string $format    Format of the date to return.
 * @param string $date      Date string to convert.
 * @param bool   $translate Whether the return date should be translated. Default true.
 * @return string|int|false Integer if `$format` is 'U' or 'G', string otherwise.
 *                          False on failure.
 */
function incassoos_mysql2date_gmt( $format, $date, $translate = true ) {
	if ( empty( $date ) ) {
		return false;
	}

	$datetime = date_create( $date );

	if ( false === $datetime ) {
		return false;
	}

	// Redefine date from GMT date
	$time = $datetime->getTimestamp() + (int) ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS );
	$date = gmdate( 'Y-m-d H:i:s', $time );

	// Use core function to process
	return mysql2date( $format, $date, $translate );
}

/** Roman Numerals ************************************************************/

/**
 * Return whether the text is a roman numeral
 *
 * @since 1.0.0
 *
 * @param  string $roman Text to check
 * @return bool Text is a roman numeral
 */
function incassoos_is_roman( $roman = '' ) {
	return (bool) incassoos_roman2int( $roman );
}

/**
 * Return the roman numeral equivalent of an integer
 *
 * @since 1.0.0
 *
 * @param  int $int Integer to translate
 * @return string Roman numeral equivalent
 */
function incassoos_int2roman( $int = 0 ) {
	$int = intval( $int );
	$map = array(
		'M'  => 1000,
		'CM' => 900,
		'D'  => 500,
		'CD' => 400,
		'C'  => 100,
		'XC' => 90,
		'L'  => 50,
		'XL' => 40,
		'X'  => 10,
		'IX' => 9,
		'V'  => 5,
		'IV' => 4,
		'I'  => 1
	);
	$retval = '';

	while ( $int > 0 ) {
		foreach ( $map as $r => $val ) {
			if ( $int - $val >= 0 ) {
				$int    -= $val;
				$retval .= $r;
				break;
			}
		}
	}

	return $retval;
}

/**
 * Return the integer equivalent of a roman numeral
 *
 * @since 1.0.0
 *
 * @param  string $roman Roman numeral to translate
 * @return int Integer equivalent
 */
function incassoos_roman2int( $roman = '' ) {

	// Bail when this is not a string
	if ( ! is_string( $roman ) )
		return 0;

	$roman = strtoupper( $roman );
	$map = array(
		'M'  => 1000,
		'CM' => 900,
		'D'  => 500,
		'CD' => 400,
		'C'  => 100,
		'XC' => 90,
		'L'  => 50,
		'XL' => 40,
		'X'  => 10,
		'IX' => 9,
		'V'  => 5,
		'IV' => 4,
		'I'  => 1
	);
	$retval = 0;

	while ( '' !== $roman ) {
		$prev = $roman;
		foreach ( $map as $r => $val ) {
			if ( 0 === strpos( $roman, $r ) ) {
				$roman   = substr( $roman, strlen( $r ) );
				$retval += $val;
				break;
			}
		}

		// Prevent endless loop when no character is matched
		if ( $prev === $roman ) {
			$retval = 0;
			break;
		}
	}

	return $retval;
}
