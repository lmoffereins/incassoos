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

/** Users *********************************************************************/

add_filter( 'incassoos_get_caps_for_role', 'incassoos_vgsr_filter_caps_for_role', 10, 2 );
add_filter( 'incassoos_get_dynamic_roles', 'incassoos_vgsr_filter_dynamic_roles', 10    );

/** Admin *********************************************************************/

add_action( 'add_meta_boxes', 'incassoos_vgsr_admin_add_meta_boxes', 10, 2 );
