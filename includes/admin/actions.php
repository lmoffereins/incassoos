<?php

/**
 * Incassoos Admin Actions
 *
 * @package Incassoos
 * @subpackage Administration
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Sub-actions ***************************************************************/

add_action( 'admin_init',                  'incassoos_admin_init',                  10    );
add_action( 'admin_menu',                  'incassoos_admin_menu',                  10    );
add_action( 'admin_head',                  'incassoos_admin_head',                  10    );
add_action( 'admin_notices',               'incassoos_admin_notices',               10    );

/** Core **********************************************************************/

add_action( 'incassoos_admin_init',        'incassoos_admin_register_settings',     10    );
add_action( 'incassoos_admin_init',        'incassoos_setup_updater',              999    );
add_action( 'incassoos_admin_menu',        'incassoos_register_admin_menu',         10    );
add_action( 'incassoos_admin_head',        'incassoos_remove_admin_menu',           10    );

/** Post **********************************************************************/

add_action( 'manage_posts_extra_tablenav', 'incassoos_admin_manage_posts_tablenav', 10    );
add_filter( 'manage_posts_columns',        'incassoos_admin_posts_add_columns',     10, 2 );
add_filter( 'manage_posts_custom_column',  'incassoos_admin_posts_custom_column',   10, 2 );
add_filter( 'post_row_actions',            'incassoos_admin_post_row_actions',      10, 2 );
add_action( 'restrict_manage_posts',       'incassoos_admin_restrict_manage_posts', 10, 2 );

// Single
add_action( 'load-post.php',               'incassoos_admin_load_post_view',         10    );
add_action( 'load-edit.php',               'incassoos_admin_load_posts_view',        10    );
add_action( 'incassoos_admin_init',        'incassoos_admin_handle_post_action',     10    );
add_action( 'post_action_view',            'incassoos_admin_post_action_view',       10    );
add_action( 'post_action_inc_close',       'incassoos_admin_post_action_close',      10    );
add_action( 'post_action_inc_reopen',      'incassoos_admin_post_action_reopen',     10    );
add_action( 'post_action_inc_stage',       'incassoos_admin_post_action_stage',      10    );
add_action( 'post_action_inc_unstage',     'incassoos_admin_post_action_unstage',    10    );
add_action( 'post_action_inc_collect',     'incassoos_admin_post_action_collect',    10    );
add_action( 'post_action_inc_duplicate',   'incassoos_admin_post_action_duplicate',  10    );
add_action( 'post_action_inc_doaction',    'incassoos_admin_post_action_doaction',   10    );
add_action( 'admin_post_inc_download',     'incassoos_admin_post_action_download',   10    ); // Using admin-post.php
add_filter( 'redirect_post_location',      'incassoos_admin_redirect_post_location', 10, 2 );
add_action( 'add_meta_boxes',              'incassoos_admin_add_meta_boxes',         10, 2 );
add_filter( 'post_updated_messages',       'incassoos_admin_post_updated_messages',  10    );
add_action( 'incassoos_admin_notices',     'incassoos_admin_post_notices',           10    );
add_action( 'incassoos_admin_notices',     'incassoos_admin_post_action_notices',    10    );

// Actions
add_action( 'incassoos_admin_collection_send_collect_test_email',      'incassoos_admin_collection_send_collect_test_email',      10, 2 );
add_action( 'incassoos_admin_collection_send_collect_consumer_emails', 'incassoos_admin_collection_send_collect_consumer_emails', 10, 2 );
add_action( 'incassoos_admin_post_tool_recalculate_total',             'incassoos_tool_recalculate_post_total',                   10, 2 );

/** Taxonomy ******************************************************************/

add_filter( 'term_name',                   'incassoos_admin_filter_term_name',      90, 3 );

/** Nav Menus *****************************************************************/

add_action( 'load-nav-menus.php',          'incassoos_admin_add_nav_menu_meta_box', 10    );

/** Pages *********************************************************************/

add_filter( 'removable_query_args',                'incassoos_admin_removable_query_args',       10 );
add_action( 'incassoos_admin_notices',             'incassoos_admin_header',                      2 );
add_action( 'incassoos_admin_load_dashboard_page', 'incassoos_admin_add_dashboard_widgets',      10 );
add_action( 'incassoos_admin_load_settings_page',  'incassoos_admin_jwt_auth_invalidate_tokens', 10 );
add_filter( 'incassoos_admin_get_settings_fields', 'incassoos_admin_jwt_auth_settings_fields',   10 );
add_filter( 'incassoos_admin_get_settings_fields', 'incassoos_admin_user_roles_settings_fields', 10 );
add_action( 'incassoos_admin_notices',             'incassoos_admin_settings_notices',           10 );
add_filter( 'option_page_capability_incassoos',    'incassoos_admin_get_option_page_cap',        10 );

/** Ajax **********************************************************************/

add_action( 'wp_ajax_incassoos_enable_encryption',    'incassoos_admin_ajax_enable_encryption',    10 );
add_action( 'wp_ajax_incassoos_disable_encryption',   'incassoos_admin_ajax_disable_encryption',   10 );
add_action( 'wp_ajax_incassoos_decrypt_option_value', 'incassoos_admin_ajax_decrypt_option_value', 10 );
