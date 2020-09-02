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
 * Sanitize XML content for ISO 20022 format when saving the settings page.
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_sanitize_transaction_description'
 *
 * @param string $content
 * @return string
 */
function incassoos_sanitize_transaction_description( $content = '' ) {

	// Define parsable title tag
	$sub = '/-TITLE-/';

	// Substitute parsable title tag
	$value = str_replace( '%TITLE%', $sub, $content );

	// Parse ISO 20022
	$value = incassoos_sanitize_iso20022( $value );

	// Re-substitute title tag
	$value = str_replace( $sub, '%TITLE%', $value );

	// Filter the result and return
	return apply_filters( 'incassoos_sanitize_transaction_description', $value, $content );
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
 *  - $length bool|int  Optional. The length of the applied redaction. Provide True to apply the exact
 *                      length of the redacted text. Defaults to 4.
 * }
 * @return string Redacted text
 */
function incassoos_redact_text( $input, $args = array() ) {

	// Parse defaults
	$args = wp_parse_args( $args, array(
		'keep'   => 4,
		'char'   => 'X',
		'length' => 4
	) );

	$redacted = '';
	$keep     = array_map( 'absint', array_values( (array) $args['keep'] ) );

	// Default to keep 0 leading characters
	if ( 1 === count( $keep ) ) {
		$keep = array_merge( array( 0 ), $keep );
	}

	if ( $input ) {

		// Ignore leading keeps when the total keep lengths match the input's
		if ( strlen( $input ) <= array_sum( $keep ) ) {
			$keep[0] = 0;
		}

		// Define redaction
		$redaction = str_repeat( $args['char'], ( true === $args['length'] ) ? strlen( $input ) - $keep[0] - $keep[1] : (int) $args['length'] );

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
 * @return string|WP_Error Price or error when invalid
 */
function incassoos_validate_price( $value ) {
	$value = floatval( $value );

	if ( empty( $value ) ) {
		return new WP_Error( 'incassoos_empty_price', __( 'Empty price.', 'incassoos' ) );
	}

	if ( $value <= 0 ) {
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
		return new WP_Error( 'incassoos_user_invalid_id_or_type', __( 'Invalid consumer ID or type.', 'incassoos' ) );
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
