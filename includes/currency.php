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
