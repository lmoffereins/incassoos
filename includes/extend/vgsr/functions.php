<?php

/**
 * Incassoos VGSR Functions
 *
 * @package Incassoos
 * @subpackage VGSR
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Consumer Types ******************************************************/

/**
 * Return the Cash consumer type id
 *
 * @since 1.0.0
 *
 * @return string Cash consumer type id
 */
function incassoos_vgsr_get_cash_consumer_type_id() {
	return incassoos()->extend->vgsr->cash_consumer_type;
}

/**
 * Return the On the House consumer type id
 *
 * @since 1.0.0
 *
 * @return string On the House consumer type id
 */
function incassoos_vgsr_get_on_the_house_consumer_type_id() {
	return incassoos()->extend->vgsr->on_the_house_consumer_type;
}

/** Users ***************************************************************/

/**
 * Context-aware wrapper for `is_user_vgsr()`
 *
 * @since 1.0.0
 *
 * @param int $user_id User ID. Optional. Defaults to the current user.
 * @return bool The user is VGSR lid
 */
function incassoos_is_user_vgsr( $user_id = 0 ) {
	return ( function_exists( 'vgsr' ) && is_user_vgsr( $user_id ) );
}
