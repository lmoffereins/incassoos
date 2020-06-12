<?php
/**
 * Incassoos View Posts Administration Screen.
 *
 * @see wp-admin/edit.php
 *
 * @package Incassoos
 * @subpackage Administration
 */

/** WordPress Administration Bootstrap */
require_once __DIR__ . '/admin.php';

/**
 * @global string $typenow
 */
global $typenow;

if ( ! $typenow ) {
	wp_die( __( 'Invalid post type.' ) );
}

if ( ! in_array( $typenow, get_post_types( array( 'show_ui' => true ) ), true ) ) {
	wp_die( __( 'Sorry, you are not allowed to view posts in this post type.', 'incassoos' ) );
}

if ( 'attachment' === $typenow ) {
	if ( wp_redirect( admin_url( 'upload.php' ) ) ) {
		exit;
	}
}

/**
 * @global string       $post_type
 * @global WP_Post_Type $post_type_object
 * @global string       $parent_file
 * @global string       $submenu_file
 * @global string       $post_new_file
 */
global $post_type, $post_type_object, $parent_file, $submenu_file, $post_new_file;

$post_type        = $typenow;
$post_type_object = get_post_type_object( $post_type );

if ( ! $post_type_object ) {
	wp_die( __( 'Invalid post type.' ) );
}

if ( ! current_user_can( $post_type_object->cap->view_posts ) ) {
	wp_die(
		'<h1>' . __( 'You need a higher level of permission.' ) . '</h1>' .
		'<p>' . __( 'Sorry, you are not allowed to view posts in this post type.', 'incassoos' ) . '</p>',
		403
	);
}

$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
$pagenum       = $wp_list_table->get_pagenum();

$parent_file   = "edit.php?post_type=$post_type";
$submenu_file  = "edit.php?post_type=$post_type";
$post_new_file = "post-new.php?post_type=$post_type";

$wp_list_table->prepare_items();

wp_enqueue_script( 'heartbeat' );

$title = $post_type_object->labels->name;

get_current_screen()->set_screen_reader_content(
	array(
		'heading_views'      => $post_type_object->labels->filter_items_list,
		'heading_pagination' => $post_type_object->labels->items_list_navigation,
		'heading_list'       => $post_type_object->labels->items_list,
	)
);

add_screen_option(
	'per_page',
	array(
		'default' => 20,
		'option'  => 'edit_' . $post_type . '_per_page',
	)
);

require_once ABSPATH . 'wp-admin/admin-header.php';
?>
<div class="wrap">
<h1 class="wp-heading-inline">
<?php
echo esc_html( $post_type_object->labels->name );
?>
</h1>

<?php
if ( isset( $_REQUEST['s'] ) && strlen( $_REQUEST['s'] ) ) {
	/* translators: %s: Search query. */
	printf( ' <span class="subtitle">' . __( 'Search results for &#8220;%s&#8221;' ) . '</span>', get_search_query() );
}
?>

<hr class="wp-header-end">

<?php $wp_list_table->views(); ?>

<form id="posts-filter" method="get">

<?php $wp_list_table->search_box( $post_type_object->labels->search_items, 'post' ); ?>

<input type="hidden" name="post_status" class="post_status_page" value="<?php echo ! empty( $_REQUEST['post_status'] ) ? esc_attr( $_REQUEST['post_status'] ) : 'all'; ?>" />
<input type="hidden" name="post_type" class="post_type_page" value="<?php echo $post_type; ?>" />

<?php if ( ! empty( $_REQUEST['author'] ) ) { ?>
<input type="hidden" name="author" value="<?php echo esc_attr( $_REQUEST['author'] ); ?>" />
<?php } ?>

<?php $wp_list_table->display(); ?>

</form>

<?php
if ( $wp_list_table->has_items() ) {
	$wp_list_table->inline_edit();
}
?>

<div id="ajax-response"></div>
<br class="clear" />
</div>

<?php
require_once ABSPATH . 'wp-admin/admin-footer.php';
exit;
