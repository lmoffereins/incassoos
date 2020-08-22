<?php

/**
 * Incassoos Currency Functions
 *
 * @package Incassoos
 * @subpackage Main
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Sanitize IBAN format when saving the settings page.
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_sanitize_iban'
 *
 * @param string $iban
 * @param bool $validate Optional. Whether to validate the IBAN. Defaults to true.
 * @return string
 */
function incassoos_sanitize_iban( $iban = '', $validate = true ) {

	// Only allow alphanumeric
	$value = preg_replace( '/[^a-zA-Z0-9]/', '', trim( $iban ) );

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
	if ( $validate ) {

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
	return apply_filters( 'incassoos_sanitize_iban', $value, $iban, $validate );
}

/**
 * Sanitize BIC format when saving the settings page.
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_sanitize_bic'
 *
 * @param string $content
 * @param string $iban Optional. IBAN to get the BIC from.
 * @return string
 */
function incassoos_sanitize_bic( $content = '', $iban = '' ) {

	// Get BIC from IBAN
	if ( $iban && $bic = incassoos_get_bic_from_iban( $iban ) ) {
		$value = $bic;
	}

	// Sanitize otherwise
	if ( ! $value ) {

		// Parse accented characters
		$value = remove_accents( $content );

		// Don't allow chars outside of upper alphanumeric
		$value = preg_replace( '/[^A-Z0-9]/', '', strtoupper( $value ) );

		// Trim off whitespace
		$value = trim( $value );
	}

	// Limit string length to 70 chars
	$value = substr( $value, 0, 69 );

	// Filter the result and return
	return apply_filters( 'incassoos_sanitize_bic', $value, $content, $iban );
}

/**
 * Return the BIC from the IBAN
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_bic_from_iban'
 *
 * @param  string $iban Valid IBAN to find the BIC with.
 * @return string BIC
 */
function incassoos_get_bic_from_iban( $iban = '' ) {
	$iban    = incassoos_sanitize_iban( $iban );
	$country = $iban ? substr( $iban, 0, 2 ) : '';
	$bic_map = array();
	$bic     = '';

	switch ( $country ) {
		case 'NL' :
			/**
			 * BIC codes for NL
			 *
			 * @link https://www.betaalvereniging.nl/giraal-en-online-betalen/sepa-documentatie-voor-nederland/bic-afleiden-uit-iban/ (07/14/2017)
			 */
			$file  = file_get_contents( incassoos()->resources_dir . 'bic/bic_NL.txt' );
			$codes = explode( "\n", $file );
		
			foreach ( $codes as $code ) {
				$bic_map[ substr( $code, 0, 4 ) ] = $code;
			};

		break;
	}

	// Get bank code
	$regex = '/^' . $country . '[0-9]{2}([A-Z]{4})[0-9]{10}/';
	preg_match( $regex, $iban, $matches );
	$bank_code = $matches ? $matches[1] : '';

	// Get BIC from bank code
	if ( $bank_code && isset( $bic_map[ $bank_code ] ) ) {
		$bic = $bic_map[ $bank_code ];
	}

	return apply_filters( 'incassoos_get_bic_from_iban', $bic, $iban );
}

/**
 * Short-circuit the meta get logic for the user's BIC meta
 *
 * @since 1.0.0
 *
 * @param  mixed  $retval   Meta short-circuit value
 * @param  int    $user_id  User ID
 * @param  string $meta_key Meta key
 * @param  bool   $single   Whether to return a single value
 * @return mixed  User BIC meta short-circuit value.
 */
function incassoos_filter_get_bic_meta( $retval, $user_id, $meta_key, $single ) {

	// Short-circuit BIC	
	if ( '_incassoos_bic' === $meta_key ) {

		// Get BIC from IBAN
		if ( $bic = incassoos_get_bic_from_iban( incassoos_get_user_iban( $user_id ) ) ) {
			$retval = $single ? $bic : array( $bic );
		}
	}

	return $retval;
}

/**
 * Short-circuit the meta update logic for the user's BIC meta
 *
 * @since 1.0.0
 *
 * @param  mixed  $retval     Meta short-circuit value
 * @param  int    $user_id    User ID
 * @param  string $meta_key   Meta key
 * @param  mixed  $meta_value Meta value
 * @param  mixed  $prev_value Previous value
 * @return mixed  User BIC meta update short-circuit value.
 */
function incassoos_filter_update_bic_meta( $retval, $user_id, $meta_key, $meta_value, $prev_value ) {

	// Short-circuit BIC	
	if ( '_incassoos_bic' === $meta_key ) {

		// Get BIC from IBAN, prevent update, delete record
		if ( $bic = incassoos_get_bic_from_iban( incassoos_get_user_iban( $user_id ) ) ) {
			$retval = delete_user_meta( $user_id, $meta_key );
		}
	}

	return $retval;	
}

/**
 * Get currency details or list of all available currencies
 * 
 * Items have the format: 'ISO-4217 code' => array( 'symbol', 'name' )
 * 
 * @link https://github.com/piwik/piwik/blob/master/core/DataFiles/Currencies.php
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_currencies' with the currencies
 *
 * @param string $currency Optional. Currency ISO code. Defaults to none.
 * @return array All currencies or single currency.
 */
function incassoos_get_currencies( $currency = '' ) {

	// Collect all currencies
	$currencies = array( 

		// Top 5 by global trading volume
		'USD' => array( 'symbol' => '$',            'name' => __( 'US dollar',                        'incassoos' ) ),
		'EUR' => array( 'symbol' => '€',            'name' => __( 'Euro',                             'incassoos' ) ),
		'JPY' => array( 'symbol' => '¥',            'name' => __( 'Japanese yen',                     'incassoos' ) ),
		'GBP' => array( 'symbol' => '£',            'name' => __( 'British pound',                    'incassoos' ) ),
		'CHF' => array( 'symbol' => 'Fr',           'name' => __( 'Swiss franc',                      'incassoos' ) ),

		'AFN' => array( 'symbol' => '؋',            'name' => __( 'Afghan afghani',                   'incassoos' ) ),
		'ALL' => array( 'symbol' => 'L',            'name' => __( 'Albanian lek',                     'incassoos' ) ),
		'DZD' => array( 'symbol' => 'د.ج',          'name' => __( 'Algerian dinar',                   'incassoos' ) ),
		'AOA' => array( 'symbol' => 'Kz',           'name' => __( 'Angolan kwanza',                   'incassoos' ) ),
		'ARS' => array( 'symbol' => '$',            'name' => __( 'Argentine peso',                   'incassoos' ) ),
		'AMD' => array( 'symbol' => 'դր.',          'name' => __( 'Armenian dram',                    'incassoos' ) ),
		'AWG' => array( 'symbol' => 'ƒ',            'name' => __( 'Aruban florin',                    'incassoos' ) ),
		'AUD' => array( 'symbol' => '$',            'name' => __( 'Australian dollar',                'incassoos' ) ),
		'AZN' => array( 'symbol' => 'm',            'name' => __( 'Azerbaijani manat',                'incassoos' ) ),
		'BSD' => array( 'symbol' => '$',            'name' => __( 'Bahamian dollar',                  'incassoos' ) ),
		'BHD' => array( 'symbol' => '.د.ب',        'name' => __( 'Bahraini dinar',                   'incassoos' ) ),
		'BDT' => array( 'symbol' => '৳',            'name' => __( 'Bangladeshi taka',                 'incassoos' ) ),
		'BBD' => array( 'symbol' => '$',            'name' => __( 'Barbadian dollar',                 'incassoos' ) ),
		'BYR' => array( 'symbol' => 'Br',           'name' => __( 'Belarusian ruble',                 'incassoos' ) ),
		'BZD' => array( 'symbol' => '$',            'name' => __( 'Belize dollar',                    'incassoos' ) ),
		'BMD' => array( 'symbol' => '$',            'name' => __( 'Bermudian dollar',                 'incassoos' ) ),
		'BTN' => array( 'symbol' => 'Nu.',          'name' => __( 'Bhutanese ngultrum',               'incassoos' ) ),
		'BOB' => array( 'symbol' => 'Bs.',          'name' => __( 'Bolivian boliviano',               'incassoos' ) ),
		'BAM' => array( 'symbol' => 'KM',           'name' => __( 'Bosnia Herzegovina mark',          'incassoos' ) ),
		'BWP' => array( 'symbol' => 'P',            'name' => __( 'Botswana pula',                    'incassoos' ) ),
		'BRL' => array( 'symbol' => 'R$',           'name' => __( 'Brazilian real',                   'incassoos' ) ),
	//	'GBP' => array( 'symbol' => '£',            'name' => __( 'British pound',                    'incassoos' ) ),
		'BND' => array( 'symbol' => '$',            'name' => __( 'Brunei dollar',                    'incassoos' ) ),
		'BGN' => array( 'symbol' => 'лв',           'name' => __( 'Bulgarian lev',                    'incassoos' ) ),
		'BIF' => array( 'symbol' => 'Fr',           'name' => __( 'Burundian franc',                  'incassoos' ) ),
		'KHR' => array( 'symbol' => '៛',            'name' => __( 'Cambodian riel',                   'incassoos' ) ),
		'CAD' => array( 'symbol' => '$',            'name' => __( 'Canadian dollar',                  'incassoos' ) ),
		'CVE' => array( 'symbol' => '$',            'name' => __( 'Cape Verdean escudo',              'incassoos' ) ),
		'KYD' => array( 'symbol' => '$',            'name' => __( 'Cayman Islands dollar',            'incassoos' ) ),
		'XAF' => array( 'symbol' => 'Fr',           'name' => __( 'Central African CFA franc',        'incassoos' ) ),
		'CLP' => array( 'symbol' => '$',            'name' => __( 'Chilean peso',                     'incassoos' ) ),
		'CNY' => array( 'symbol' => '元',           'name' => __( 'Chinese yuan',                     'incassoos' ) ),
		'COP' => array( 'symbol' => '$',            'name' => __( 'Colombian peso',                   'incassoos' ) ),
		'KMF' => array( 'symbol' => 'Fr',           'name' => __( 'Comorian franc',                   'incassoos' ) ),
		'CDF' => array( 'symbol' => 'Fr',           'name' => __( 'Congolese franc',                  'incassoos' ) ),
		'CRC' => array( 'symbol' => '₡',            'name' => __( 'Costa Rican colón',                'incassoos' ) ),
		'HRK' => array( 'symbol' => 'kn',           'name' => __( 'Croatian kuna',                    'incassoos' ) ),
		'XPF' => array( 'symbol' => 'F',            'name' => __( 'CFP franc',                        'incassoos' ) ),
		'CUC' => array( 'symbol' => '$',            'name' => __( 'Cuban convertible peso',           'incassoos' ) ),
		'CUP' => array( 'symbol' => '$',            'name' => __( 'Cuban peso',                       'incassoos' ) ),
		'CMG' => array( 'symbol' => 'ƒ',            'name' => __( 'Curaçao and Sint Maarten guilder', 'incassoos' ) ),
		'CZK' => array( 'symbol' => 'Kč',           'name' => __( 'Czech koruna',                     'incassoos' ) ),
		'DKK' => array( 'symbol' => 'kr',           'name' => __( 'Danish krone',                     'incassoos' ) ),
		'DJF' => array( 'symbol' => 'Fr',           'name' => __( 'Djiboutian franc',                 'incassoos' ) ),
		'DOP' => array( 'symbol' => '$',            'name' => __( 'Dominican peso',                   'incassoos' ) ),
		'XCD' => array( 'symbol' => '$',            'name' => __( 'East Caribbean dollar',            'incassoos' ) ),
		'EGP' => array( 'symbol' => 'ج.م',          'name' => __( 'Egyptian pound',                   'incassoos' ) ),
		'ERN' => array( 'symbol' => 'Nfk',          'name' => __( 'Eritrean nakfa',                   'incassoos' ) ),
		'EEK' => array( 'symbol' => 'kr',           'name' => __( 'Estonian kroon',                   'incassoos' ) ),
		'ETB' => array( 'symbol' => 'Br',           'name' => __( 'Ethiopian birr',                   'incassoos' ) ),
		// 'EUR' => array( 'symbol' => '€',            'name' => __( 'Euro',                             'incassoos' ) ),
		'FKP' => array( 'symbol' => '£',            'name' => __( 'Falkland Islands pound',           'incassoos' ) ),
		'FJD' => array( 'symbol' => '$',            'name' => __( 'Fijian dollar',                    'incassoos' ) ),
		'GMD' => array( 'symbol' => 'D',            'name' => __( 'Gambian dalasi',                   'incassoos' ) ),
		'GEL' => array( 'symbol' => 'ლ',           'name' => __( 'Georgian lari',                    'incassoos' ) ),
		'GHS' => array( 'symbol' => '₵',            'name' => __( 'Ghanaian cedi',                    'incassoos' ) ),
		'GIP' => array( 'symbol' => '£',            'name' => __( 'Gibraltar pound',                  'incassoos' ) ),
		'GTQ' => array( 'symbol' => 'Q',            'name' => __( 'Guatemalan quetzal',               'incassoos' ) ),
		'GNF' => array( 'symbol' => 'Fr',           'name' => __( 'Guinean franc',                    'incassoos' ) ),
		'GYD' => array( 'symbol' => '$',            'name' => __( 'Guyanese dollar',                  'incassoos' ) ),
		'HTG' => array( 'symbol' => 'G',            'name' => __( 'Haitian gourde',                   'incassoos' ) ),
		'HNL' => array( 'symbol' => 'L',            'name' => __( 'Honduran lempira',                 'incassoos' ) ),
		'HKD' => array( 'symbol' => '$',            'name' => __( 'Hong Kong dollar',                 'incassoos' ) ),
		'HUF' => array( 'symbol' => 'Ft',           'name' => __( 'Hungarian forint',                 'incassoos' ) ),
		'ISK' => array( 'symbol' => 'kr',           'name' => __( 'Icelandic króna',                  'incassoos' ) ),
		'INR' => array( 'symbol' => '‎₹',            'name' => __( 'Indian rupee',                     'incassoos' ) ),
		'IDR' => array( 'symbol' => 'Rp',           'name' => __( 'Indonesian rupiah',                'incassoos' ) ),
		'IRR' => array( 'symbol' => '﷼',           'name' => __( 'Iranian rial',                     'incassoos' ) ),
		'IQD' => array( 'symbol' => 'ع.د',          'name' => __( 'Iraqi dinar',                      'incassoos' ) ),
		'ILS' => array( 'symbol' => '₪',            'name' => __( 'Israeli new shekel',               'incassoos' ) ),
		'JMD' => array( 'symbol' => '$',            'name' => __( 'Jamaican dollar',                  'incassoos' ) ),
	//	'JPY' => array( 'symbol' => '¥',            'name' => __( 'Japanese yen',                     'incassoos' ) ),
		'JOD' => array( 'symbol' => 'د.ا',           'name' => __( 'Jordanian dinar',                  'incassoos' ) ),
		'KZT' => array( 'symbol' => '₸',            'name' => __( 'Kazakhstani tenge',                'incassoos' ) ),
		'KES' => array( 'symbol' => 'Sh',           'name' => __( 'Kenyan shilling',                  'incassoos' ) ),
		'KWD' => array( 'symbol' => 'د.ك',         'name' => __( 'Kuwaiti dinar',                    'incassoos' ) ),
		'KGS' => array( 'symbol' => 'лв',           'name' => __( 'Kyrgyzstani som',                  'incassoos' ) ),
		'LAK' => array( 'symbol' => '₭',            'name' => __( 'Lao kip',                          'incassoos' ) ),
		'LVL' => array( 'symbol' => 'Ls',           'name' => __( 'Latvian lats',                     'incassoos' ) ),
		'LBP' => array( 'symbol' => 'ل.ل',         'name' => __( 'Lebanese pound',                   'incassoos' ) ),
		'LSL' => array( 'symbol' => 'L',            'name' => __( 'Lesotho loti',                     'incassoos' ) ),
		'LRD' => array( 'symbol' => '$',            'name' => __( 'Liberian dollar',                  'incassoos' ) ),
		'LYD' => array( 'symbol' => 'ل.د',          'name' => __( 'Libyan dinar',                     'incassoos' ) ),
		'LTL' => array( 'symbol' => 'Lt',           'name' => __( 'Lithuanian litas',                 'incassoos' ) ),
		'MOP' => array( 'symbol' => 'P',            'name' => __( 'Macanese pataca',                  'incassoos' ) ),
		'MKD' => array( 'symbol' => 'ден',          'name' => __( 'Macedonian denar',                 'incassoos' ) ),
		'MGA' => array( 'symbol' => 'Ar',           'name' => __( 'Malagasy ariary',                  'incassoos' ) ),
		'MWK' => array( 'symbol' => 'MK',           'name' => __( 'Malawian kwacha',                  'incassoos' ) ),
		'MYR' => array( 'symbol' => 'RM',           'name' => __( 'Malaysian ringgit',                'incassoos' ) ),
		'MVR' => array( 'symbol' => 'ރ.',           'name' => __( 'Maldivian rufiyaa',                'incassoos' ) ),
		'MRO' => array( 'symbol' => 'UM',           'name' => __( 'Mauritanian ouguiya',              'incassoos' ) ),
		'MUR' => array( 'symbol' => '₨',            'name' => __( 'Mauritian rupee',                  'incassoos' ) ),
		'MXN' => array( 'symbol' => '$',            'name' => __( 'Mexican peso',                     'incassoos' ) ),
		'MDL' => array( 'symbol' => 'L',            'name' => __( 'Moldovan leu',                     'incassoos' ) ),
		'MNT' => array( 'symbol' => '₮',            'name' => __( 'Mongolian tögrög',                 'incassoos' ) ),
		'MAD' => array( 'symbol' => 'د.م.',         'name' => __( 'Moroccan dirham',                  'incassoos' ) ),
		'MZN' => array( 'symbol' => 'MTn',          'name' => __( 'Mozambican metical',               'incassoos' ) ),
		'MMK' => array( 'symbol' => 'K',            'name' => __( 'Myanma kyat',                      'incassoos' ) ),
		'NAD' => array( 'symbol' => '$',            'name' => __( 'Namibian dollar',                  'incassoos' ) ),
		'NPR' => array( 'symbol' => '₨',            'name' => __( 'Nepalese rupee',                   'incassoos' ) ),
		'ANG' => array( 'symbol' => 'ƒ',            'name' => __( 'Netherlands Antillean guilder',    'incassoos' ) ),
		'TWD' => array( 'symbol' => '$',            'name' => __( 'New Taiwan dollar',                'incassoos' ) ),
		'NZD' => array( 'symbol' => '$',            'name' => __( 'New Zealand dollar',               'incassoos' ) ),
		'NIO' => array( 'symbol' => 'C$',           'name' => __( 'Nicaraguan córdoba',               'incassoos' ) ),
		'NGN' => array( 'symbol' => '₦',            'name' => __( 'Nigerian naira',                   'incassoos' ) ),
		'KPW' => array( 'symbol' => '₩',            'name' => __( 'North Korean won',                 'incassoos' ) ),
		'NOK' => array( 'symbol' => 'kr',           'name' => __( 'Norwegian krone',                  'incassoos' ) ),
		'OMR' => array( 'symbol' => 'ر.ع.',         'name' => __( 'Omani rial',                       'incassoos' ) ),
		'PKR' => array( 'symbol' => '₨',            'name' => __( 'Pakistani rupee',                  'incassoos' ) ),
		'PAB' => array( 'symbol' => 'B/.',          'name' => __( 'Panamanian balboa',                'incassoos' ) ),
		'PGK' => array( 'symbol' => 'K',            'name' => __( 'Papua New Guinean kina',           'incassoos' ) ),
		'PYG' => array( 'symbol' => '₲',            'name' => __( 'Paraguayan guaraní',               'incassoos' ) ),
		'PEN' => array( 'symbol' => 'S/.',          'name' => __( 'Peruvian nuevo sol',               'incassoos' ) ),
		'PHP' => array( 'symbol' => '₱',            'name' => __( 'Philippine peso',                  'incassoos' ) ),
		'PLN' => array( 'symbol' => 'zł',           'name' => __( 'Polish złoty',                     'incassoos' ) ),
		'QAR' => array( 'symbol' => 'ر.ق',          'name' => __( 'Qatari riyal',                     'incassoos' ) ),
		'RON' => array( 'symbol' => 'L',            'name' => __( 'Romanian leu',                     'incassoos' ) ),
		'RUB' => array( 'symbol' => 'руб.',         'name' => __( 'Russian ruble',                    'incassoos' ) ),
		'RWF' => array( 'symbol' => 'Fr',           'name' => __( 'Rwandan franc',                    'incassoos' ) ),
		'SHP' => array( 'symbol' => '£',            'name' => __( 'Saint Helena pound',               'incassoos' ) ),
		'SVC' => array( 'symbol' => '₡',            'name' => __( 'Salvadoran colón',                 'incassoos' ) ),
		'WST' => array( 'symbol' => 'T',            'name' => __( 'Samoan tala',                      'incassoos' ) ),
		'STD' => array( 'symbol' => 'Db',           'name' => __( 'São Tomé and Príncipe dobra',      'incassoos' ) ),
		'SAR' => array( 'symbol' => 'ر.س',         'name' => __( 'Saudi riyal',                      'incassoos' ) ),
		'RSD' => array( 'symbol' => 'дин. or din.', 'name' => __( 'Serbian dinar',                    'incassoos' ) ),
		'SCR' => array( 'symbol' => '₨',            'name' => __( 'Seychellois rupee',                'incassoos' ) ),
		'SLL' => array( 'symbol' => 'Le',           'name' => __( 'Sierra Leonean leone',             'incassoos' ) ),
		'SGD' => array( 'symbol' => '$',            'name' => __( 'Singapore dollar',                 'incassoos' ) ),
		'SBD' => array( 'symbol' => '$',            'name' => __( 'Solomon Islands dollar',           'incassoos' ) ),
		'SOS' => array( 'symbol' => 'Sh',           'name' => __( 'Somali shilling',                  'incassoos' ) ),
		'ZAR' => array( 'symbol' => 'R',            'name' => __( 'South African rand',               'incassoos' ) ),
		'KRW' => array( 'symbol' => '₩',            'name' => __( 'South Korean won',                 'incassoos' ) ),
		'LKR' => array( 'symbol' => 'Rs',           'name' => __( 'Sri Lankan rupee',                 'incassoos' ) ),
		'SDG' => array( 'symbol' => 'جنيه سوداني', 'name' => __( 'Sudanese pound',                   'incassoos' ) ),
		'SRD' => array( 'symbol' => '$',            'name' => __( 'Surinamese dollar',                'incassoos' ) ),
		'SZL' => array( 'symbol' => 'L',            'name' => __( 'Swazi lilangeni',                  'incassoos' ) ),
		'SEK' => array( 'symbol' => 'kr',           'name' => __( 'Swedish krona',                    'incassoos' ) ),
	//	'CHF' => array( 'symbol' => 'Fr',           'name' => __( 'Swiss franc',                      'incassoos' ) ),
		'SYP' => array( 'symbol' => 'ل.س',         'name' => __( 'Syrian pound',                     'incassoos' ) ),
		'TJS' => array( 'symbol' => 'ЅМ',           'name' => __( 'Tajikistani somoni',               'incassoos' ) ),
		'TZS' => array( 'symbol' => 'Sh',           'name' => __( 'Tanzanian shilling',               'incassoos' ) ),
		'THB' => array( 'symbol' => '฿',            'name' => __( 'Thai baht',                        'incassoos' ) ),
		'TOP' => array( 'symbol' => 'T$',           'name' => __( 'Tongan paʻanga',                   'incassoos' ) ),
		'TTD' => array( 'symbol' => '$',            'name' => __( 'Trinidad and Tobago dollar',       'incassoos' ) ),
		'TND' => array( 'symbol' => 'د.ت',          'name' => __( 'Tunisian dinar',                   'incassoos' ) ),
		'TRY' => array( 'symbol' => 'TL',           'name' => __( 'Turkish lira',                     'incassoos' ) ),
		'TMM' => array( 'symbol' => 'm',            'name' => __( 'Turkmenistani manat',              'incassoos' ) ),
		'UGX' => array( 'symbol' => 'Sh',           'name' => __( 'Ugandan shilling',                 'incassoos' ) ),
		'UAH' => array( 'symbol' => '₴',            'name' => __( 'Ukrainian hryvnia',                'incassoos' ) ),
		'AED' => array( 'symbol' => 'د.إ',           'name' => __( 'United Arab Emirates dirham',      'incassoos' ) ),
	//	'USD' => array( 'symbol' => '$',            'name' => __( 'United States dollar',             'incassoos' ) ),
		'UYU' => array( 'symbol' => '$',            'name' => __( 'Uruguayan peso',                   'incassoos' ) ),
		'UZS' => array( 'symbol' => 'лв',           'name' => __( 'Uzbekistani som',                  'incassoos' ) ),
		'VUV' => array( 'symbol' => 'Vt',           'name' => __( 'Vanuatu vatu',                     'incassoos' ) ),
		'VEF' => array( 'symbol' => 'Bs F',         'name' => __( 'Venezuelan bolívar',               'incassoos' ) ),
		'VND' => array( 'symbol' => '₫',            'name' => __( 'Vietnamese đồng',                  'incassoos' ) ),
		'XOF' => array( 'symbol' => 'Fr',           'name' => __( 'West African CFA franc',           'incassoos' ) ),
		'YER' => array( 'symbol' => '﷼',           'name' => __( 'Yemeni rial',                      'incassoos' ) ),
		'ZMK' => array( 'symbol' => 'ZK',           'name' => __( 'Zambian kwacha',                   'incassoos' ) ),
		'ZWL' => array( 'symbol' => '$',            'name' => __( 'Zimbabwean dollar',                'incassoos' ) ),
	);

	// Return single currency details when ISO is provided
	if ( ! empty( $currency ) ) {

		// Default to 'USD'
		if ( ! isset( $currencies[ $currency ] ) ) {
			$currency = 'USD';
		} 

		$details = $currencies[ $currency ];

	// Return all currencies
	} else {
		$details = $currencies;
	}

	return apply_filters( 'incassoos_get_currencies', $details, $currency );
}

/**
 * Output the value in a currency format
 *
 * @since 1.0.0
 *
 * @param  float|int $value Numeric value to format.
 * @param  array $args Optional. Additional format settings.
 */
function incassoos_the_format_currency( $value, $args = array() ) {
	echo incassoos_get_format_currency( $value, $args );
}

/**
 * Return the value in a currency format
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_get_format_currency'
 *
 * @param  float|int $value Numeric value to format.
 * @param  array $args Optional. Additional format settings.
 * @return string Formatted currency value
 */
function incassoos_get_format_currency( $value, $args = array() ) {

	// Parse arguments
	$args = wp_parse_args( $args, incassoos_get_currency_format_args() );

	// Parse numbers
	$format = ! empty( $args['format'] ) ? $args['format'] : '%s';
	$format = sprintf( $format, number_format_i18n( $value, $args['decimals'] ) );

	return apply_filters( 'incassoos_get_format_currency', $format, $value, $args );
}

/**
 * Return the currency format arguments
 *
 * @since 1.0.0
 *
 * @global WP_Locale $wp_locale
 *
 * @uses apply_filters() Calls 'incassoos_get_currency_format_args'
 *
 * @return array Currency format arguments
 */
function incassoos_get_currency_format_args() {
	global $wp_locale;

	// Enable filtering
	$args     = apply_filters( 'incassoos_get_currency_format_args', array() );
	$currency = incassoos_get_currencies( incassoos_get_currency() );

	// Default arguments
	$args = wp_parse_args( $args, array(
		'format'        => $currency['symbol'] . ' %s',
		'decimals'      => 2,

		// Supply interpunction for use outside of `number_format_i18n()`
		'decimal_point' => $wp_locale->number_format['decimal_point'],
		'thousands_sep' => $wp_locale->number_format['thousands_sep'],
	) );

	return $args;
}

/**
 * Parse the currency value to match the applicable format
 *
 * @since 1.0.0
 *
 * @param  float      $total  Currency value
 * @param  bool|array $format Optional. Whether to apply currency format. Pass array as custom format args.
 * @return string Parsed currency, formatted when requested.
 */
function incassoos_parse_currency( $value = 0, $format = false ) {

	// Parse float
	$value = (float) $value;

	// Apply currency format
	if ( $format ) {
		$value = incassoos_get_format_currency( $value, (array) $format );

	// Default parse float
	} else {
		$format = incassoos_get_currency_format_args();
		$value  = sprintf( '%.' . (int) $format['decimals'] . 'f', $value );
	}

	return $value;
}
