<?php

/**
 * Incassoos Actions
 *
 * @package Incassoos
 * @subpackage Main
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/** Sub-actions ***************************************************************/

add_action( 'plugins_loaded',              'incassoos_loaded',                     10    );
add_action( 'init',                        'incassoos_init',                       10    );
add_action( 'incassoos_init',              'incassoos_register',                    0    );
add_action( 'widgets_init',                'incassoos_widgets_init',               10    );
add_action( 'rest_api_init',               'incassoos_rest_api_init',              10    );
add_action( 'registered_post_type',        'incassoos_registered_post_type',       10    );
add_action( 'registered_taxonomy',         'incassoos_registered_taxonomy',        10    );
add_action( 'wp_roles_init',               'incassoos_roles_init',                 10    ); // Since WP 4.7
add_action( 'after_setup_theme',           'incassoos_after_setup_theme',          10    );
add_filter( 'map_meta_cap',                'incassoos_map_meta_caps',              10, 4 );
add_filter( 'post_class',                  'incassoos_post_class',                 10, 3 );

/** Utility *******************************************************************/

add_action( 'incassoos_activation',        'incassoos_delete_rewrite_rules',       10    );
add_action( 'incassoos_deactivation',      'incassoos_remove_caps',                10    );
add_action( 'incassoos_deactivation',      'incassoos_delete_rewrite_rules',       10    );

/** Query *********************************************************************/

add_action( 'parse_query',                 'incassoos_parse_query',                 2    ); // Early for overrides
add_action( 'parse_query',                 'incassoos_parse_query_vars',           10    );
add_filter( 'posts_search',                'incassoos_posts_search',               10, 2 );
add_filter( 'posts_where_paged',           'incassoos_posts_where_paged',          10, 2 );
add_filter( 'posts_request',               'incassoos_filter_wp_query',            10, 2 );
add_filter( 'posts_pre_query',             'incassoos_bypass_wp_query',            10, 2 ); // Since WP 4.6

/** REST **********************************************************************/

add_filter( 'incassoos_rest_api_init',     'incassoos_register_rest_routes',       10    );

/** Template ******************************************************************/

add_filter( 'show_admin_bar',              'incassoos_show_admin_bar',             10    );
add_filter( 'document_title_parts',        'incassoos_document_title_parts',       10    ); // Since WP 4.4
add_action( 'wp_enqueue_scripts',          'incassoos_enqueue_scripts',            10    );

// Theme Compat
add_filter( 'template_include',            'incassoos_template_include_theme_supports', 10 );

// App page
add_action( 'incassoos_app_head',          'incassoos_enqueue_scripts',             1    );
add_action( 'incassoos_app_head',          'wp_resource_hints',                     2    );
add_action( 'incassoos_app_head',          'wp_no_robots',                          1    );
add_action( 'incassoos_app_head',          'print_emoji_detection_script',          7    );
add_action( 'incassoos_app_head',          'wp_print_styles',                       8    );
add_action( 'incassoos_app_head',          'wp_print_head_scripts',                 9    );
add_action( 'incassoos_app_footer',        'wp_print_footer_scripts',              20    );

/** Post **********************************************************************/

add_filter( 'the_title',                    'incassoos_filter_occasion_title',      10, 2 );
add_filter( 'list_pages',                   'incassoos_filter_occasion_title',      10, 2 );
add_filter( 'the_title',                    'incassoos_filter_order_title',         10, 2 );
add_filter( 'wp_insert_post_empty_content', 'incassoos_prevent_insert_post',        10, 2 );
add_filter( 'wp_insert_post_data',          'incassoos_insert_post_data',           10, 2 );
add_action( 'post_updated',                 'incassoos_update_product_menu_orders', 20, 3 );
add_action( 'save_post',                    'incassoos_update_occasion_total',      20    );
add_action( 'after_delete_post',            'incassoos_update_occasion_total',      20    );

add_filter( 'incassoos_post_class',        'incassoos_filter_collection_class',    10, 3 );
add_filter( 'incassoos_post_class',        'incassoos_filter_activity_class',      10, 3 );
add_filter( 'incassoos_post_class',        'incassoos_filter_occasion_class',      10, 3 );
add_filter( 'incassoos_post_class',        'incassoos_filter_order_class',         10, 3 );

add_filter( 'incassoos_get_post_notes',    'wptexturize',                          10    );
add_filter( 'incassoos_get_post_notes',    'convert_smilies',                      20    );
add_filter( 'incassoos_get_post_notes',    'wpautop',                              10    );

/** Taxonomy ******************************************************************/

add_action( 'create_term',                 'incassoos_save_term_meta',             10, 3 );
add_action( 'edit_term',                   'incassoos_save_term_meta',             10, 3 );
add_filter( 'term_link',                   'incassoos_filter_term_link',           10, 3 );

/** User **********************************************************************/

add_action( 'incassoos_loaded',            'incassoos_filter_user_roles_option',      16    );
add_action( 'incassoos_loaded',            'incassoos_filter_capabilities_user_meta', 16    );
add_action( 'incassoos_roles_init',        'incassoos_add_plugin_roles',              10    );
add_filter( 'editable_roles',              'incassoos_filter_blog_editable_roles',    10    );
add_filter( 'incassoos_map_meta_caps',     'incassoos_map_collection_caps',           10, 4 );
add_filter( 'incassoos_map_meta_caps',     'incassoos_map_activity_caps',             10, 4 );
add_filter( 'incassoos_map_meta_caps',     'incassoos_map_occasion_caps',             10, 4 );
add_filter( 'incassoos_map_meta_caps',     'incassoos_map_order_caps',                10, 4 );
add_filter( 'incassoos_map_meta_caps',     'incassoos_map_product_caps',              10, 4 );
add_filter( 'incassoos_map_meta_caps',     'incassoos_map_generic_caps',              10, 4 );
add_filter( 'get_user_metadata',           'incassoos_filter_get_bic_meta',           10, 4 );
add_filter( 'update_user_metadata',        'incassoos_filter_update_bic_meta',        10, 5 );

/** Menu **********************************************************************/

add_filter( 'customize_nav_menu_available_item_types', 'incassoos_customize_nav_menu_set_item_types',  10    );
add_filter( 'customize_nav_menu_available_items',      'incassoos_customize_nav_menu_available_items', 10, 4 );
add_filter( 'customize_nav_menu_searched_items',       'incassoos_customize_nav_menu_searched_items',  10, 2 );
add_filter( 'wp_setup_nav_menu_item',                  'incassoos_setup_nav_menu_item',                10    );

/** Email *********************************************************************/

add_filter( 'incassoos_collection_email_content', 'incassoos_collection_email_salutation',          5, 2 );
add_filter( 'incassoos_collection_email_content', 'incassoos_the_collection_content',              10    );
add_filter( 'incassoos_collection_email_content', 'incassoos_collection_email_amounts_table',      20, 2 );
add_filter( 'incassoos_collection_email_content', 'incassoos_collection_email_withdrawal_mention', 50, 2 );
add_filter( 'incassoos_collection_email_content', 'incassoos_collection_email_closing',            90, 2 );

/** Widgets *******************************************************************/

// add_action( 'incassoos_widgets_init', array( 'Incassoos_Enrollments_Widget', 'register' ) );
// add_action( 'incassoos_widgets_init', array( 'Incassoos_Partners_Widget',    'register' ) );

/** Admin *********************************************************************/

if ( is_admin() ) {
	add_action( 'incassoos_register', 'incassoos_admin', 5 );
}

/** Extend ********************************************************************/

// add_action( 'bp_core_loaded', 'incassoos_buddypress', 10 );
add_action( 'incassoos_init', 'incassoos_members',    10 );
add_action( 'vgsr_ready',     'incassoos_vgsr',       10 );
// add_action( 'incassoos_init', 'incassoos_wpseo',      10 );
