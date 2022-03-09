<?php

/**
 * Incassoos Accounts Functions
 *
 * @package Incassoos
 * @subpackage Main
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** IBAN **********************************************************************/

/**
 * Sanitize text for the IBAN format
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_sanitize_iban'
 *
 * @param string $input Value to sanitize
 * @param bool $validate Optional. Whether to validate the IBAN. Defaults to true.
 * @return string Sanitized IBAN
 */
function incassoos_sanitize_iban( $input = '', $validate = true ) {

	// Only allow alphanumeric
	$value = preg_replace( '/[^a-zA-Z0-9]/', '', trim( $input ) );

	/**
	 * Define the country's specific IBAN regexes.
	 *
	 * @link https://www.swift.com/sites/default/files/resources/swift_standards_ibanregistry.pdf
	 */
	$regex = array(
		'AL' => '/^(AL[0-9]{2}[0-9]{8}[a-zA-Z0-9]{16})/',          // Albania
		'AD' => '/^(AD[0-9]{2}[0-9]{8}[a-zA-Z0-9]{12})/',          // Andorra
		'AT' => '/^(AT[0-9]{2}[0-9]{16})/',                        // Austria
		'AZ' => '/^(AZ[0-9]{2}[A-Z]{4}[a-zA-Z0-9]{20})/',          // Azerbaijan
		'BH' => '/^(BH[0-9]{2}[0-9]{4}[a-zA-Z0-9]{14})/',          // Bahrain
		'BE' => '/^(BE[0-9]{2}[0-9]{12})/',                        // Belgium
		'BA' => '/^(BA[0-9]{2}[0-9]{16})/',                        // Bosnia and Herzegovina
		'BR' => '/^(BR[0-9]{2}[0-9]{23}[A-Z]{1}[a-zA-Z0-9]{1})/',  // Brazil
		'BG' => '/^(BG[0-9]{2}[A-Z]{4}[0-9]{6}[a-zA-Z0-9]{8})/',   // Bulgaria
		'CR' => '/^(CR[0-9]{2}[0-9]{17})/',                        // Costa Rica
		'HR' => '/^(HR[0-9]{2}[0-9]{17})/',                        // Croatia
		'CY' => '/^(CY[0-9]{2}[0-9]{8}[a-zA-Z0-9]{16})/',          // Cyprus
		'CZ' => '/^(CZ[0-9]{2}[0-9]{20})/',                        // Czech Republic
		'DK' => '/^(DK[0-9]{2}[0-9]{14})/',                        // Denmark
		'DO' => '/^(DO[0-9]{2}[a-zA-Z0-9]{4}[0-9]{20})/',          // Dominican Republic
		'EE' => '/^(EE[0-9]{2}[0-9]{16})/',                        // Estonia
		'FO' => '/^(FO[0-9]{2}[0-9]{14})/',                        // Faroer Islands
		'FI' => '/^(FI[0-9]{2}[0-9]{14})/',                        // Finland
		'FR' => '/^(FR[0-9]{2}[0-9]{10}[a-zA-Z0-9]{11}[0-9]{2})/', // France
		'GE' => '/^(GE[0-9]{2}[A-Z]{2}[0-9]{16})/',                // Georgia
		'DE' => '/^(DE[0-9]{2}[0-9]{18})/',                        // Germany
		'GI' => '/^(GI[0-9]{2}[A-Z]{4}[a-zA-Z0-9]{15})/',          // Gibraltar
		'GR' => '/^(GR[0-9]{2}[0-9]{7}[a-zA-Z0-9]{16})/',          // Greece
		'GL' => '/^(GL[0-9]{2}[0-9]{14})/',                        // Greenland
		'GT' => '/^(GT[0-9]{2}[a-zA-Z0-9]{24})/',                  // Guatemala
		'HU' => '/^(HU[0-9]{2}[0-9]{24})/',                        // Hungary
		'IS' => '/^(IS[0-9]{2}[0-9]{22})/',                        // Iceland
		'IE' => '/^(IE[0-9]{2}[A-Z]{4}[0-9]{14})/',                // Ireland
		'IL' => '/^(IL[0-9]{2}[0-9]{19})/',                        // Israel
		'IT' => '/^(IT[0-9]{2}[A-Z]{1}[0-9]{10}[a-zA-Z0-9]{12})/', // Italy
		'JO' => '/^(JO[0-9]{2}[A-Z]{4}[0-9]{4}[a-zA-Z0-9]{18})/',  // Jordan
		'KZ' => '/^(KZ[0-9]{2}[0-9]{3}[a-zA-Z0-9]{13})/',          // Kazachstan
		'XK' => '/^(XK[0-9]{2}[0-9]{16})/',                        // Republic of Kosovo
		'KW' => '/^(KW[0-9]{2}[A-Z]{4}[a-zA-Z0-9]{22})/',          // Kuwait
		'LV' => '/^(LV[0-9]{2}[A-Z]{4}[a-zA-Z0-9]{13})/',          // Latvia
		'LB' => '/^(LB[0-9]{2}[0-9]{4}[a-zA-Z0-9]{20})/',          // Lebanon
		'LI' => '/^(LI[0-9]{2}[0-9]{5}[a-zA-Z0-9]{12})/',          // Liechtenstein
		'LT' => '/^(LT[0-9]{2}[0-9]{16})/',                        // Lithuania
		'LU' => '/^(LU[0-9]{2}[0-9]{3}[a-zA-Z0-9]{13})/',          // Luxembourg
		'MK' => '/^(MK[0-9]{2}[0-9]{3}[a-zA-Z0-9]{10}[0-9]{2})/',  // Macedonia
		'MT' => '/^(MT[0-9]{2}[A-Z]{4}[0-9]{5}[a-zA-Z0-9]{18})/',  // Malta
		'MR' => '/^(MR[0-9]{2}[0-9]{23})/',                        // Mauritania
		'MU' => '/^(MU[0-9]{2}[A-Z]{4}[0-9]{19}[A-Z]{3})/',        // Mauritius
		'MD' => '/^(MD[0-9]{2}[a-zA-Z0-9]{20})/',                  // Moldova
		'MC' => '/^(MC[0-9]{2}[0-9]{10}[a-zA-Z0-9]{11}[0-9]{2})/', // Monaco
		'ME' => '/^(ME[0-9]{2}[0-9]{18})/',                        // Montenegro
		'NL' => '/^(NL[0-9]{2}[A-Z]{4}[0-9]{10})/',                // Netherlands
		'NO' => '/^(NO[0-9]{2}[0-9]{11})/',                        // Norway
		'PK' => '/^(PK[0-9]{2}[A-Z]{4}[a-zA-Z0-9]{16})/',          // Pakistan
		'PS' => '/^(PS[0-9]{2}[A-Z]{4}[a-zA-Z0-9]{21})/',          // Palestine, State of
		'PL' => '/^(PL[0-9]{2}[0-9]{24})/',                        // Poland
		'PT' => '/^(PT[0-9]{2}[0-9]{21})/',                        // Portugal
		'QA' => '/^(QA[0-9]{2}[A-Z]{4}[a-zA-Z0-9]{21})/',          // Qatar
		'RO' => '/^(RO[0-9]{2}[A-Z]{4}[a-zA-Z0-9]{16})/',          // Romania
		'LC' => '/^(LC[0-9]{2}[A-Z]{4}[a-zA-Z0-9]{24})/',          // Saint Lucia
		'SA' => '/^(SA[0-9]{2}[A-Z]{1}[0-9]{10}[a-zA-Z0-9]{12})/', // San Marino
		'ST' => '/^(ST[0-9]{2}[0-9]{21})/',                        // Sao Tome And Principe
		'SA' => '/^(SA[0-9]{2}[0-9]{2}[a-zA-Z0-9]{18})/',          // Saudi Arabia
		'RS' => '/^(RS[0-9]{2}[0-9]{18})/',                        // Serbia
		'SC' => '/^(SC[0-9]{2}[A-Z]{4}[0-9]{20}[A-Z]{3})/',        // Seychellen
		'SK' => '/^(SK[0-9]{2}[0-9]{20})/',                        // Slovakia
		'SI' => '/^(SI[0-9]{2}[0-9]{15})/',                        // Slovenia
		'ES' => '/^(ES[0-9]{2}[0-9]{20})/',                        // Spain
		'SE' => '/^(SE[0-9]{2}[0-9]{20})/',                        // Sweden
		'CH' => '/^(CH[0-9]{2}[0-9]{5}[a-zA-Z0-9]{12})/',          // Switzerland
		'TL' => '/^(TL[0-9]{2}[0-9]{19})/',                        // Timor-Leste
		'TN' => '/^(TN[0-9]{2}[0-9]{20})/',                        // Tunisia
		'TR' => '/^(TR[0-9]{2}[0-9]{6}[a-zA-Z0-9]{16})/',          // Turkey
		'UA' => '/^(UA[0-9]{2}[0-9]{6}[a-zA-Z0-9]{19})/',          // Ukraine
		'AE' => '/^(AE[0-9]{2}[0-9]{19})/',                        // United Arab Emirates
		'GB' => '/^(GB[0-9]{2}[A-Z]{4}[0-9]{14})/',                // United Kingdom
		'VG' => '/^(VG[0-9]{2}[A-Z]{4}[0-9]{16})/',                // Virgin Islands, British
	);

	// Validate IBAN spec by country
	$country = substr( $value, 0, 2 );

	if ( array_key_exists( $country, $regex ) ) {
		preg_match( $regex[ $country ], $value, $matches );

		// Get matched part
		$value = $matches ? $matches[1] : '';
	}

	// Validate IBAN by modulo 97
	if ( $value && $validate ) {

		// Move first 4 chars to the back. Replace chars with numbers
		$check = substr( $value, 4 ) . substr( $value, 0, 4 );
		$check = str_replace( range( 'A', 'Z' ), range( 10, 35 ), strtoupper( $check ) );

		// Find modulus of the rearranged value
		if ( function_exists( 'bcmod' ) ) {
			$mod = bcmod( $check, '97' );
		} else {
			$take = 5;
			$mod  = '';

			do {
				$a     = (int) $mod . substr( $check, 0, $take );
				$check = substr( $check, $take );
				$mod   = $a % 97;
			} while ( strlen( $check ) );
		}

		// Bail value when validation fails
		if ( 1 !== (int) $mod ) {
			$value = '';
		}
	}

	// Filter the result and return
	return apply_filters( 'incassoos_sanitize_iban', $value, $input, $validate );
}

/**
 * Return whether the IBAN validates
 *
 * @param  string $iban IBAN
 * @return bool Is IBAN validated?
 */
function incassoos_validate_iban( $iban ) {
	return ! empty( $iban ) && $iban === incassoos_sanitize_iban( $iban );
}

/**
 * Return the redacted version of an IBAN
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_redact_iban'
 *
 * @param  string $iban IBAN
 * @return string Redacted IBAN
 */
function incassoos_redact_iban( $iban ) {
	return apply_filters( 'incassoos_redact_iban', incassoos_redact_text( $iban, array( 'keep' => array( 2, 3 ) ) ), $iban );
}

/**
 * Return whether the IBAN value is already redacted
 *
 * @since 1.0.0
 *
 * @param  string $iban IBAN
 * @return bool Is IBAN redacted?
 */
function incassoos_is_iban_redacted( $iban ) {
	return $iban === incassoos_redact_iban( $iban );
}

/**
 * Return the BIC from the IBAN
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_bic_from_iban'
 *
 * @param  string $iban Valid IBAN to find the BIC for
 * @return string BIC
 */
function incassoos_get_bic_from_iban( $iban = '' ) {

	// Bail when the IBAN does not validate
	if ( ! incassoos_validate_iban( $iban ) ) {
		return false;
	}

	$country = substr( $iban, 0, 2 );
	$bic_map = array();
	$bic     = '';

	switch ( $country ) {
		case 'NL' :
			/**
			 * BIC codes for NL
			 *
			 * @link https://www.betaalvereniging.nl/giraal-en-online-betalen/sepa-documentatie-voor-nederland/bic-afleiden-uit-iban/ (2020-08-22)
			 */
			$codes = array(
				'ABNANL2A',
				'ABNCNL2A',
				'ADYBNL2A',
				'AEGONL2U',
				'ANDLNL2A',
				'ARBNNL22',
				'ARSNNL21',
				'ASNBNL21',
				'ATBANL2A',
				'BARCNL22',
				'BCDMNL22',
				'BCITNL2A',
				'BICKNL2A',
				'BINKNL21',
				'BITSNL2A',
				'BKCHNL2R',
				'BKMGNL2A',
				'BLGWNL21',
				'BMEUNL21',
				'BNDANL2A',
				'BNGHNL2G',
				'BNPANL2A',
				'BOFANLNX',
				'BOFSNL21002',
				'BOTKNL2X',
				'BUNQNL2A',
				'CHASNL2X',
				'CITCNL2A',
				'CITINL2X',
				'COBANL2X',
				'DELENL22',
				'DEUTNL2A',
				'DHBNNL2R',
				'DLBKNL2A',
				'DNIBNL2G',
				'EBPBNL22',
				'EBURNL21',
				'FBHLNL2A',
				'FLORNL2A',
				'FRNXNL2A',
				'FVLBNL22',
				'FXBBNL22',
				'GILLNL2A',
				'HANDNL2A',
				'HHBANL22',
				'HSBCNL2A',
				'ICBCNL2A',
				'ICBKNL2A',
				'ICEPNL21',
				'INGBNL2A',
				'ISAENL2A',
				'ISBKNL2A',
				'KABANL2A',
				'KASANL2A',
				'KNABNL2H',
				'KOEXNL2A',
				'KREDNL2X',
				'LOCYNL2A',
				'LOYDNL2A',
				'LPLNNL2F',
				'MHCBNL2A',
				'MOYONL21',
				'NNBANL2G',
				'NWABNL2G',
				'PCBCNL2A',
				'RABONL2U',
				'RBRBNL21',
				'SNSBNL2A',
				'SOGENL2A',
				'TRIONL2U',
				'UGBINL2A',
				'VOWANL21',
				'VPAYNL22',
				'ZWLBNL21'
			);

			// Abstract into array( bank code => bic )
			foreach ( $codes as $code ) {
				$bic_map[ substr( $code, 0, 4 ) ] = $code;
			};

			// Get bank code
			$regex = '/^' . $country . '[0-9]{2}([A-Z]{4})[0-9]{10}/';
			preg_match( $regex, $iban, $matches );
			$bank_code = $matches ? $matches[1] : '';

			// Get BIC from bank code
			if ( $bank_code && isset( $bic_map[ $bank_code ] ) ) {
				$bic = $bic_map[ $bank_code ];
			}

		break;
	}

	return apply_filters( 'incassoos_get_bic_from_iban', $bic, $iban );
}
