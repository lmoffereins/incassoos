<?php

/**
 * Incassoos VGSR Actions
 *
 * @package Incassoos
 * @subpackage VGSR
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Registration **************************************************************/

add_action( 'incassoos_register', 'incassoos_vgsr_register_consumer_types', 20 );
add_action( 'incassoos_register', 'incassoos_vgsr_register_export_types',   20 );

/** Utility *******************************************************************/

add_action( 'incassoos_activation',        'incassoos_delete_rewrite_rules',       10    );
add_action( 'incassoos_deactivation',      'incassoos_remove_caps',                10    );
add_action( 'incassoos_deactivation',      'incassoos_delete_rewrite_rules',       10    );
