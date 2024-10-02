<?php

/**
 * Incassoos Admin Help Functions
 *
 * @package Incassoos
 * @subpackage Administration
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Register help tabs for the posts list table page
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_admin_post_type_help_tabs'
 */
function incassoos_admin_post_type_help_tabs() {
	$post_type = get_current_screen()->post_type;

	// Bail when not a plugin post type
	if ( ! incassoos_is_plugin_post_type( $post_type ) ) {
		return;
	}

	$screen_content = array(
		'title'   => __( 'Screen Content' ),
		'content' =>
			'<p>' . __( 'You can customize the display of this screen&#8217;s contents in a number of ways:' ) . '</p>' .
			'<ul>' .
				'<li>' . __( 'You can hide/display columns based on your needs and decide how many items to list per screen using the Screen Options tab.', 'incassoos' ) . '</li>' .
				'<li>' . __( 'You can filter the list of items by post status using the text links above the items list to only show items with that status. The default view is to show all items.', 'incassoos' ) . '</li>' .
				'<li>' . __( 'You can refine the list to show only items in a specific category or from a specific month by using the dropdown menus above the items list. Click the Filter button after making your selection.', 'incassoos' ) . '</li>' .
			'</ul>',
	);

	$help = apply_filters( 'incassoos_admin_post_type_help_tabs', array(

		// Collection
		incassoos_get_collection_post_type() => array(
			'overview'       => array(
				'title'   => __( 'Overview' ),
				'content' =>
					'<p>' . __( 'This screen provides access to all of the Incassoos collections. You can customize the display of this screen to suit your workflow.', 'incassoos' ) . '</p>' .
					'<p>' . __( 'Collections are the sets of activities and consumptions that are to be collected from consumers. Collections are similar to posts in that they have a title, body text, and associated metadata. Collections are not categorized or tagged.', 'incassoos' ) . '</p>',
			),
			'screen-content' => $screen_content,
			'action-links'   => array(
				'title'   => __( 'Available Actions' ),
				'content' =>
					'<p>' . __( 'Hovering over a row in the items list will display action links that allow you to manage your item. You can perform the following actions:', 'incassoos' ) . '</p>' .
					'<ul>' .
						'<li>' . __( '<strong>Edit</strong> takes you to the editing screen for that item. You can also reach that screen by clicking on the item title. Whether the link is available depends on your item&#8217;s status.', 'incassoos' ) . '</li>' .
						'<li>' . __( '<strong>Trash</strong> removes your item from this list and places it in the Trash, from which you can permanently delete it.', 'incassoos' ) . '</li>' .
						'<li>' . __( '<strong>View</strong> will show you the final state of that item. View will take you to a screen similar to the editing screen where you can only view its details. Whether the link is available depends on your item&#8217;s status.', 'incassoos' ) . '</li>' .
					'</ul>',
			)
		),

		// Activity
		incassoos_get_activity_post_type() => array(
			'overview'       => array(
				'title'   => __( 'Overview' ),
				'content' =>
					'<p>' . __( 'This screen provides access to all of the Incassoos activities. You can customize the display of this screen to suit your workflow.', 'incassoos' ) . '</p>' .
					'<p>' . __( 'Activities are the individual events for which participants will be charged a price. Activities are similar to posts in that they have a title and associated metadata, but they do not have a body text. Activities can be categorized by assigning an Activity Category.', 'incassoos' ) . '</p>' .
					'<p>' . __( 'The button for managing Activity Categories takes you to the admin screen for that taxonomy.', 'incassoos' ) . '</p>',
			),
			'screen-content' => $screen_content,
			'action-links'   => array(
				'title'   => __( 'Available Actions' ),
				'content' =>
					'<p>' . __( 'Hovering over a row in the items list will display action links that allow you to manage your item. You can perform the following actions:', 'incassoos' ) . '</p>' .
					'<ul>' .
						'<li>' . __( '<strong>Edit</strong> takes you to the editing screen for that item. You can also reach that screen by clicking on the item title. Whether the link is available depends on your item&#8217;s status.', 'incassoos' ) . '</li>' .
						'<li>' . __( '<strong>Trash</strong> removes your item from this list and places it in the Trash, from which you can permanently delete it.', 'incassoos' ) . '</li>' .
						'<li>' . __( '<strong>View</strong> will show you the final state of that item. View will take you to a screen similar to the editing screen where you can only view its details. Whether the link is available depends on your item&#8217;s status.', 'incassoos' ) . '</li>' .
						'<li>' . __( '<strong>Duplicate</strong> will create a new activity with the same details as that item.', 'incassoos' ) . '</li>' .
					'</ul>',
			)
		),

		// Occasion
		incassoos_get_occasion_post_type() => array(
			'overview'       => array(
				'title'   => __( 'Overview' ),
				'content' =>
					'<p>' . __( 'This screen provides access to all of the Incassoos occasions. You can customize the display of this screen to suit your workflow.', 'incassoos' ) . '</p>' .
					'<p>' . __( 'Occasions are events at which specific products are consumed. Occasions are similar to posts in that they have a title and associated metadata, but they do not have a body text. Occasions can be categorized by assigning an Occasion Type.', 'incassoos' ) . '</p>' .
					'<p>' . __( 'The button for managing Occasion Types takes you to the admin screen for that taxonomy.', 'incassoos' ) . '</p>',
			),
			'screen-content' => $screen_content,
			'action-links'   => array(
				'title'   => __( 'Available Actions' ),
				'content' =>
					'<p>' . __( 'Hovering over a row in the items list will display action links that allow you to manage your item. You can perform the following actions:', 'incassoos' ) . '</p>' .
					'<ul>' .
						'<li>' . __( '<strong>Edit</strong> takes you to the editing screen for that item. You can also reach that screen by clicking on the item title. Whether the link is available depends on your item&#8217;s status.', 'incassoos' ) . '</li>' .
						'<li>' . __( '<strong>View</strong> takes you to the details screen for that item. It is similar to the editing screen where you can only view its details. Whether the link is available depends on your item&#8217;s status.', 'incassoos' ) . '</li>' .
						'<li>' . __( '<strong>Trash</strong> removes your item from this list and places it in the Trash, from which you can permanently delete it.', 'incassoos' ) . '</li>' .
						'<li>' . __( '<strong>Close</strong> will change the status of that item, so that no additional orders can be registered for it. Open will undo this change. Which link is available depends on your item&#8217;s status.', 'incassoos' ) . '</li>' .
					'</ul>',
			)
		),

		// Order
		incassoos_get_order_post_type() => array(
			'overview'       => array(
				'title'   => __( 'Overview' ),
				'content' =>
					'<p>' . __( 'This screen provides access to all of the Incassoos orders. You can customize the display of this screen to suit your workflow.', 'incassoos' ) . '</p>' .
					'<p>' . __( 'Orders are the individual consumptions at an Occasion. Unlike posts, orders are not assigned an individual title or body text, but they contain a list of consumed products. Orders are not categorized or tagged.', 'incassoos' ) . '</p>',
			),
			'screen-content' => $screen_content,
			'action-links'   => array(
				'title'   => __( 'Available Actions' ),
				'content' =>
					'<p>' . __( 'Hovering over a row in the items list will display action links that allow you to manage your item. You can perform the following actions:', 'incassoos' ) . '</p>' .
					'<ul>' .
						'<li>' . __( '<strong>Edit</strong> takes you to the editing screen for that item. You can also reach that screen by clicking on the item title. Whether the link is available depends on your item&#8217;s status.', 'incassoos' ) . '</li>' .
						'<li>' . __( '<strong>Trash</strong> removes your item from this list and places it in the Trash, from which you can permanently delete it.', 'incassoos' ) . '</li>' .
						'<li>' . __( '<strong>View</strong> will show you the final state of that item. View will take you to a screen similar to the editing screen where you can only view its details. Whether the link is available depends on your item&#8217;s status.', 'incassoos' ) . '</li>' .
					'</ul>',
			)
		),

		// Product
		incassoos_get_product_post_type() => array(
			'overview'       => array(
				'title'   => __( 'Overview' ),
				'content' =>
					'<p>' . __( 'This screen provides access to all of the Incassoos products. You can customize the display of this screen to suit your workflow.', 'incassoos' ) . '</p>' .
					'<p>' . __( 'Products are the items that are available for consumption at Occasions. Products are similar to posts in that they have a title and associated metadata, but they do not have a body text. Products can be categorized by assigning a Product Category.', 'incassoos' ) . '</p>' .
					'<p>' . __( 'The button for managing Product Categories takes you to the admin screen for that taxonomy.', 'incassoos' ) . '</p>',
			),
			'screen-content' => $screen_content,
			'action-links'   => array(
				'title'   => __( 'Available Actions' ),
				'content' =>
					'<p>' . __( 'Hovering over a row in the items list will display action links that allow you to manage your item. You can perform the following actions:', 'incassoos' ) . '</p>' .
					'<ul>' .
						'<li>' . __( '<strong>Edit</strong> takes you to the editing screen for that item. You can also reach that screen by clicking on the item title.', 'incassoos' ) . '</li>' .
						'<li>' . __( '<strong>Trash</strong> removes your item from this list and places it in the Trash, from which you can permanently delete it.', 'incassoos' ) . '</li>' .
					'</ul>',
			)
		)
	) );

	if ( isset( $help[ $post_type ] ) ) {
		foreach ( $help[ $post_type ] as $help_id => $title_and_content ) {
			get_current_screen()->add_help_tab( array_merge( array( 'id' => $help_id ), $title_and_content ) );
		}
	}

	get_current_screen()->set_help_sidebar(
		'<p><strong>' . __( 'For more information:' ) . '</strong></p>' .
		'<p>' . __( '<a href="https://github.com/lmoffereins/incassoos">Development</a>', 'incassoos' ) . '</p>'
	);
}

/**
 * Register help tabs for the single post edit page
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_admin_single_post_help_tabs'
 */
function incassoos_admin_single_post_help_tabs() {
	$post_type = get_current_screen()->post_type;

	// Bail when not a plugin post type
	if ( ! incassoos_is_plugin_post_type( $post_type ) ) {
		return;
	}

	if ( post_type_supports( $post_type, 'editor' ) ) {
		$customize_display = '<p>' . __( 'The title field and the Text Editing Area are fixed in place, but you can reposition all the other boxes using drag and drop. You can also minimize or expand them by clicking the title bar of each box. Use the Screen Options tab to unhide more boxes or to choose a 1- or 2-column layout for this screen.', 'incassoos' ) . '</p>';
	} else {
		$customize_display = '<p>' . __( 'The title field is fixed in place, but you can reposition all the other boxes using drag and drop. You can also minimize or expand them by clicking the title bar of each box. Use the Screen Options tab to unhide more boxes or to choose a 1- or 2-column layout for this screen.', 'incassoos' ) . '</p>';
	}

	$item_settings = '';

	if ( post_type_supports( $post_type, 'title' ) ) {
		$item_settings .= '<p>' . __( '<strong>Title</strong> &mdash; The title for the item.', 'incassoos' ) . '</p>';
	}

	if ( post_type_supports( $post_type, 'editor' ) ) {
		$item_settings .= '<p>' . __( '<strong>Post editor</strong> &mdash; Enter the text for the item. There are two modes of editing: Visual and Text. Choose the mode by clicking on the appropriate tab.', 'incassoos' ) . '</p>' .
			'<p>' . __( 'Visual mode gives you an editor that is similar to a word processor.', 'incassoos' ) . '</p>' .
			'<p>' . __( 'The Text mode allows you to enter HTML along with your post text. Note that &lt;p&gt; and &lt;br&gt; tags are converted to line breaks when switching to the Text editor to make it less cluttered. When you type, a single line break can be used instead of typing &lt;br&gt;, and two line breaks instead of paragraph tags. The line breaks are converted back to tags automatically.' ) . '</p>' .
			'<p>' . __( 'You can enable distraction-free writing mode using the icon to the right. This feature is not available for old browsers or devices with small screens, and requires that the full-height editor be enabled in Screen Options.' ) . '</p>' .
			'<p>' . sprintf(
				/* translators: %s: Alt + F10 */
				__( 'Keyboard users: When you&#8217;re working in the visual editor, you can use %s to access the toolbar.' ), '<kbd>Alt + F10</kbd>' ) . '</p>';
	}

	if ( post_type_supports( $post_type, 'incassoos-notes' ) ) {
		$item_settings .= '<p>' . __( '<strong>Notes</strong> &mdash; Use the Notes box to register additional notes for your item. For example, you could provide some details on the context of the item or add a message to fellow Incassoos users or your future self about the rationale behind some decisions.', 'incassoos' ) . '</p>';
	}

	$help = apply_filters( 'incassoos_admin_single_post_help_tabs', array(

		// Collection
		incassoos_get_collection_post_type() => array(
			'overview'       => array(
				'title'   => __( 'Overview' ),
				'content' =>
					'<p>' . __( 'Collections are the sets of activities and consumptions that are to be collected from consumers. Collections are similar to posts in that they have a title, body text, and associated metadata. Collections are not categorized or tagged.', 'incassoos' ) . '</p>',
			),
			'customize-display' => array(
				'title'   => __( 'Customizing This Display' ),
				'content' => $customize_display .
					'<p>' . __( 'The post content is used for the email body in the consumers email distribution.', 'incassoos' ) . '</p>',
			),
			'item-settings' => array(
				'title'   => __( 'Collection Settings', 'incassoos' ),
				'content' => $item_settings .
					'<p>' . __( '<strong>Activities</strong> &mdash; Select the activities to include in the collection.', 'incassoos' ) . '</p>' .
					'<p>' . __( '<strong>Occasions</strong> &mdash; Select the occasions to include in the collection.', 'incassoos' ) . '</p>',
			),
			'item-attributes' => array(
				'title'   => __( 'Collection Attributes', 'incassoos' ),
				'content' =>
					'<p>' . __( 'When any activities or occasions are registered for this item, several details are added on this screen, including:', 'incassoos' ) . '</p>' .
					'<p>' . __( '<strong>Consumers</strong> &mdash; The list of consumers with their respective consumptions for this collection.', 'incassoos' ) . '</p>',
			),
			'item-workflow' => array(
				'title'   => __( 'Collection Workflow', 'incassoos' ),
				'content' =>
					'<p>' . __( "<strong>Create</strong> &mdash; Set the collection's attributes when it is created.", 'incassoos' ) . '</p>' .
					'<p>' . __( '<strong>Update</strong> &mdash; Remember to click Update to save settings entered or changed.', 'incassoos' ) . '</p>' .
					'<p>' . __( '<strong>Stage</strong> &mdash; Before a collection is ready for processing, click Stage to prepare the item for review. Details of a staged collection cannot be changed, nor can the related items.', 'incassoos' ) . '</p>' .
					'<p>' . __( '<strong>Unstage</strong> &mdash; When changes need to be made to a staged collection, click Unstage.', 'incassoos' ) . '</p>' .
					'<p>' . __( '<strong>Submit</strong> &mdash; On review of a staged collection, click Submit for definitive processing. Submission is final and cannot be reverted. Related items will be locked and uneditable as well.', 'incassoos' ) . '</p>',
			),
		),

		// Activity
		incassoos_get_activity_post_type() => array(
			'overview'       => array(
				'title'   => __( 'Overview' ),
				'content' =>
					'<p>' . __( 'Activities are the individual events for which participants will be charged a price. Activities are similar to posts in that they have a title and associated metadata, but they do not have a body text. Activities can be categorized by assigning an Activity Category.', 'incassoos' ) . '</p>',
			),
			'customize-display' => array(
				'title'   => __( 'Customizing This Display' ),
				'content' => $customize_display,
			),
			'item-settings' => array(
				'title'   => __( 'Activity Settings', 'incassoos' ),
				'content' => $item_settings .
					'<p>' . __( '<strong>Date</strong> &mdash; Select the date the activity was scheduled. Leave empty when no particular date applies.', 'incassoos' ) . '</p>' .
					'<p>' . __( '<strong>Category</strong> &mdash; Select a category for the activity.', 'incassoos' ) . '</p>' .
					'<p>' . __( '<strong>Price</strong> &mdash; Enter the default price for the activity.', 'incassoos' ) . '</p>' .
					'<p>' . __( '<strong>Partition</strong> &mdash; Select whether the price of the activity should be partitioned for the participants.', 'incassoos' ) . '</p>' .
					'<p>' . __( '<strong>Participants</strong> &mdash; Select the participants of the activity.', 'incassoos' ) . '</p>' .
					'<p>' . __( 'Participants can be selected individually or in bulk. Bulk selection of participants is available through clicking their respective group heading or by selecting an option from the Quick Select menu.', 'incassoos' ) . '</p>' .
					'<p>' . __( 'When required, the price per participant can be changed to differ from the default price. You can change their price after the participant is selected, then click the pencil icon to open the custom price input field. Click the X to cancel the custom price.', 'incassoos' ) . '</p>',
			),
		),

		// Occasion
		incassoos_get_occasion_post_type() => array(
			'overview'       => array(
				'title'   => __( 'Overview' ),
				'content' =>
					'<p>' . __( 'Occasions are events at which specific products are consumed. Occasions are similar to posts in that they have a title and associated metadata, but they do not have a body text. Occasions can be categorized by assigning an Occasion Type.', 'incassoos' ) . '</p>',
			),
			'customize-display' => array(
				'title'   => __( 'Customizing This Display' ),
				'content' => $customize_display,
			),
			'item-settings' => array(
				'title'   => __( 'Occasion Settings', 'incassoos' ),
				'content' => $item_settings .
					'<p>' . __( '<strong>Date</strong> &mdash; Select the date the occasion was scheduled.', 'incassoos' ) . '</p>' .
					'<p>' . __( '<strong>Type</strong> &mdash; Select a type for the occasion.', 'incassoos' ) . '</p>',
			),
			'item-attributes' => array(
				'title'   => __( 'Occasion Attributes', 'incassoos' ),
				'content' =>
					'<p>' . __( 'When any orders are registered for this item, additional details are shown on this screen, including:', 'incassoos' ) . '</p>' .
					'<p>' . __( '<strong>Consumers</strong> &mdash; The list of consumers with their respective orders for this occasion.', 'incassoos' ) . '</p>' .
					'<p>' . __( '<strong>Products</strong> &mdash; The list of products that are consumed in the orders for this occasion.', 'incassoos' ) . '</p>',
			),
			'item-workflow' => array(
				'title'   => __( 'Occasion Workflow', 'incassoos' ),
				'content' =>
					'<p>' . __( "<strong>Create</strong> &mdash; Set the occasion's attributes when it is created.", 'incassoos' ) . '</p>' .
					'<p>' . __( '<strong>Close</strong> &mdash; To prevent further orders being created for the occasion, click Close. Only closed occasions can be collected by the Incassoos Collector.', 'incassoos' ) . '</p>' .
					'<p>' . __( '<strong>Reopen</strong> &mdash; To undo closing the occasion for new orders, click Reopen.', 'incassoos' ) . '</p>',
			),
		),

		// Order
		incassoos_get_order_post_type() => array(
			'overview'       => array(
				'title'   => __( 'Overview' ),
				'content' =>
					'<p>' . __( 'Orders are the individual consumptions at an Occasion. Unlike posts, orders are not assigned an individual title or body text, but they contain a list of consumed products. Orders are not categorized or tagged.', 'incassoos' ) . '</p>' .
					'<p>' . sprintf( __( 'Changes to the order can only be made within the given timeframe of %s minutes after initial creation.', 'incassoos' ), incassoos_get_order_time_lock() ) . '</p>',
			),
			'customize-display' => array(
				'title'   => __( 'Customizing This Display' ),
				'content' => $customize_display,
			),
			'item-settings' => array(
				'title'   => __( 'Order Settings', 'incassoos' ),
				'content' => 
					'<p>' . __( "<strong>Title</strong> &mdash; The title is automatically derived from the order's consumer name.", 'incassoos' ) . '</p>' . $item_settings .
					'<p>' . __( '<strong>Products</strong> &mdash; Indicate the number of products that comprise the order.', 'incassoos' ) . '</p>' .
					'<p>' . __( '<strong>Consumer</strong> &mdash; Enter the username of the consumer or select a consumer type when no single consumer should be specified.', 'incassoos' ) . '</p>' .
					'<p>' . __( '<strong>Occasion</strong> &mdash; Select the occasion for which the order is consumed.', 'incassoos' ) . '</p>',
			),
		),

		// Product
		incassoos_get_product_post_type() => array(
			'overview'       => array(
				'title'   => __( 'Overview' ),
				'content' =>
					'<p>' . __( 'Products are the items that are available for consumption at Occasions. Products are similar to posts in that they have a title and associated metadata, but they do not have a body text. Products can be categorized by assigning a Product Category.', 'incassoos' ) . '</p>',
			),
			'customize-display' => array(
				'title'   => __( 'Customizing This Display' ),
				'content' => $customize_display,
			),
			'item-settings' => array(
				'title'   => __( 'Product Settings', 'incassoos' ),
				'content' => $item_settings .
					'<p>' . __( '<strong>Price</strong> &mdash; Enter a price for the product.', 'incassoos' ) . '</p>' .
					'<p>' . __( '<strong>Category</strong> &mdash; Select a category for the product.', 'incassoos' ) . '</p>' .
					'<p>' . __( '<strong>Order</strong> &mdash; Products are usually ordered alphabetically, but you can choose your own order by entering a number (1 for first, etc.) in this field.', 'incassoos' ) . '</p>',
			),
		)
	) );

	if ( isset( $help[ $post_type ] ) ) {
		foreach ( $help[ $post_type ] as $help_id => $title_and_content ) {
			get_current_screen()->add_help_tab( array_merge( array( 'id' => $help_id ), $title_and_content ) );
		}
	}

	get_current_screen()->set_help_sidebar(
		'<p><strong>' . __( 'For more information:' ) . '</strong></p>' .
		'<p>' . __( '<a href="https://github.com/lmoffereins/incassoos">Development</a>', 'incassoos' ) . '</p>'
	);
}

/**
 * Register help tabs for the taxonomy pages
 *
 * @since 1.0.0
 *
 * @uses apply_filters() Calls 'incassoos_admin_taxonomy_help_tabs'
 */
function incassoos_admin_taxonomy_help_tabs() {
	$taxonomy = get_current_screen()->taxonomy;

	// Bail when not a plugin taxonomy
	if ( ! incassoos_is_plugin_taxonomy( $taxonomy ) || 'term.php' === $GLOBALS['pagenow'] ) {
		return;
	}

	$adding_terms = '<p>' . __( 'When adding a new term on this screen, you&#8217;ll fill in the following fields:', 'incassoos' ) . '</p>' .
		'<ul>' .
			'<li>' . __( '<strong>Name</strong> &mdash; The name is how it appears on your site.' ) . '</li>' .
			'<li>' . __( '<strong>Slug</strong> &mdash; The &#8220;slug&#8221; is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.' ) . '</li>' .
			'<li>' . __( '<strong>Description</strong> &mdash; The description is not prominent by default; however, some themes may display it.' ) . '</li>' .
		'</ul>' .
		'<p>' . __( 'You can change the display of this screen using the Screen Options tab to set how many items are displayed per screen and to display/hide columns in the table.' ) . '</p>';

	$help = apply_filters( 'incassoos_admin_taxonomy_help_tabs', array(

		// Activity Category
		incassoos_get_activity_cat_tax_id() => array(
			'overview' => array(
				'title'   => __( 'Overview' ),
				'content' => 
					'<p>' . __( 'You can create groups of activities by using Activity Categories. Activity Category names must be unique and Activity Categories are separate from the categories you use for posts.', 'incassoos' ) . '</p>' .
					'<p>' . __( 'You can delete Activity Categories in the Bulk Action pull-down, but that action does not delete the activities within the category.', 'incassoos' ) . '</p>',
			),
			'adding-terms' => array(
				'title'   => __( 'Adding Categories' ),
				'content' => $adding_terms
			)
		),

		// Occasion Type
		incassoos_get_occasion_type_tax_id() => array(
			'overview' => array(
				'title'   => __( 'Overview' ),
				'content' => 
					'<p>' . __( 'You can create groups of occasions by using Occasion Types. Occasion Type names must be unique and Occasion Types are separate from the categories you use for posts.', 'incassoos' ) . '</p>' .
					'<p>' . __( 'You can delete Occasion Types in the Bulk Action pull-down, but that action does not delete the occasions within the type.', 'incassoos' ) . '</p>',
			),
			'adding-terms' => array(
				'title'   => __( 'Adding Types', 'incassoos' ),
				'content' => $adding_terms
			)
		),

		// Product Category
		incassoos_get_product_cat_tax_id() => array(
			'overview' => array(
				'title'   => __( 'Overview' ),
				'content' => 
					'<p>' . __( 'You can create groups of products by using Product Categories. Product Category names must be unique and Product Categories are separate from the categories you use for posts.', 'incassoos' ) . '</p>' .
					'<p>' . __( 'You can delete Product Categories in the Bulk Action pull-down, but that action does not delete the products within the category.', 'incassoos' ) . '</p>',
			),
			'adding-terms' => array(
				'title'   => __( 'Adding Categories' ),
				'content' => $adding_terms
			)
		),

		// Consumer Type
		incassoos_get_consumer_type_tax_id() => array(
			'overview' => array(
				'title'   => __( 'Overview' ),
				'content' =>
					'<p>' . __( 'You can assign orders to other consumers than users by using Consumer Types. Consumer Type names must be unique.', 'incassoos' ) . '</p>' .
					'<p>' . __( 'You can delete Consumer Types in the Bulk Action pull-down, but that action does not delete the consumptions related to the type.', 'incassoos' ) . '</p>' .
					'<p>' . __( 'You can not change or delete built-in Consumer Types that are registered as part of the plugin or those added by extensions.', 'incassoos' ) . '</p>',
			),
			'adding-terms' => array(
				'title'   => __( 'Adding Types', 'incassoos' ),
				'content' => $adding_terms
			)
		)
	) );

	if ( isset( $help[ $taxonomy ] ) ) {
		foreach ( $help[ $taxonomy ] as $help_id => $title_and_content ) {
			get_current_screen()->add_help_tab( array_merge( array( 'id' => $help_id ), $title_and_content ) );
		}
	}

	get_current_screen()->set_help_sidebar(
		'<p><strong>' . __( 'For more information:' ) . '</strong></p>' .
		'<p>' . __( '<a href="https://github.com/lmoffereins/incassoos">Development</a>', 'incassoos' ) . '</p>'
	);
}

/**
 * Register help tabs for the Dashboard page
 *
 * @since 1.0.0
 */
function incassoos_admin_dashboard_help_tabs() {
	get_current_screen()->add_help_tab(
		array(
			'id'      => 'overview',
			'title'   => __( 'Overview' ),
			'content' =>
				'<p>' . __( 'Welcome to your Incassoos dashboard! This is the screen you will see when you start with Incassoos, and gives you access to all the management features of the plugin. This page provides quick access to the current status of your Incassoos installation and frequently used actions. You can get help for any screen by clicking the Help tab above the screen title.', 'incassoos' ) . '</p>',
		)
	);

	get_current_screen()->add_help_tab(
		array(
			'id'      => 'help-layout',
			'title'   => __( 'Layout' ),
			'content' =>
				'<p>' . __( 'You can use the following controls to arrange your Dashboard screen to suit your workflow.', 'incassoos' ) . '</p>' .
				'<p>' . __( '<strong>Screen Options</strong> &mdash; Use the Screen Options tab to choose which Dashboard boxes to show.' ) . '</p>' .
				'<p>' . __( '<strong>Drag and Drop</strong> &mdash; To rearrange the boxes, drag and drop by clicking on the title bar of the selected box and releasing when you see a gray dotted-line rectangle appear in the location you want to place the box.' ) . '</p>',
		)
	);

	get_current_screen()->add_help_tab(
		array(
			'id'      => 'help-content',
			'title'   => __( 'Content' ),
			'content' =>
				'<p>' . __( 'The boxes on your Dashboard screen are:' ) . '</p>' .
				'<p>' . __( '<strong>At a Glance</strong> &mdash; Displays a summary of the content on your site and identifies whether encryption is enabled.', 'incassoos' ) . '</p>' .
				'<p>' . __( '<strong>Recent Activity</strong> &mdash; Shows the recently collected items, and the most recently created items.', 'incassoos' ) . '</p>' .
				'<p>' . __( '<strong>Uncollected Items</strong> &mdash; Shows the items that have not been collected yet.', 'incassoos' ) . '</p>' .
				'<p>' . __( '<strong>Quick Actions</strong> &mdash; Provides quick access to frequently used actions, if any are available for you.', 'incassoos' ) . '</p>',
		)
	);

	get_current_screen()->set_help_sidebar(
		'<p><strong>' . __( 'For more information:' ) . '</strong></p>' .
		'<p>' . __( '<a href="https://github.com/lmoffereins/incassoos">Development</a>', 'incassoos' ) . '</p>'
	);
}

/**
 * Register help tabs for the Settings page
 *
 * @since 1.0.0
 */
function incassoos_admin_settings_help_tabs() {
	get_current_screen()->add_help_tab(
		array(
			'id'      => 'overview',
			'title'   => __( 'Overview' ),
			'content' =>
				'<p>' . __( 'This screen provides options for managing and configuring Incassoos.', 'incassoos' ) . '</p>' .
				'<p>' . __( 'You must click the Save Changes button at the bottom of the screen for new settings to take effect.' ) . '</p>',
		)
	);

	get_current_screen()->set_help_sidebar(
		'<p><strong>' . __( 'For more information:' ) . '</strong></p>' .
		'<p>' . __( '<a href="https://github.com/lmoffereins/incassoos">Development</a>', 'incassoos' ) . '</p>'
	);
}

/**
 * Register help tabs for the Consumers page
 *
 * @since 1.0.0
 */
function incassoos_admin_consumers_help_tabs() {
	get_current_screen()->add_help_tab(
		array(
			'id'      => 'overview',
			'title'   => __( 'Overview' ),
			'content' =>
				'<p>' . __( 'This screen gives an overview of the available consumers for Incassoos and a way of managing their relevant attributes.', 'incassoos' ) . '</p>' .
				'<p>' . __( 'You can filter the list of consumers by searching for them by name. Click Hide archived to show only public consumers. The default view is to show all consumers.', 'incassoos' ) . '</p>' .
				'<p>' . __( 'The button for managing Consumer Types takes you to the admin screen for that taxonomy.', 'incassoos' ) . '</p>',
		)
	);

	get_current_screen()->add_help_tab(
		array(
			'id'      => 'actions',
			'title'   => __( 'Available Actions' ),
			'content' =>
				'<p>' . __( "<strong>Quick Edit</strong> &mdash; For editing a single consumer's attributes, click the row of the consumer to open the Quick Edit menu. This gives access to all relevant consumer details.", 'incassoos' ) . '</p>' .
				'<p>' . __( '<strong>Bulk Edit</strong> &mdash; For editing multiple consumers at once, click Open bulk edit mode. Consumers can be selected individually or in bulk. Bulk selection of consumers is available through clicking their respective group heading or by selecting an option from the Quick Select menu.', 'incassoos' ) . '</p>' .
				'<p>' . __( 'Actions available for bulk editing are Archive consumers or Unarchive consumers.', 'incassoos' ) . '</p>' .
				'<p>' . __( '<strong>Export</strong> &mdash; Exporting consumers provides the option to download consumers with their attributes in a CSV formatted file.', 'incassoos' ) . '</p>',
		)
	);

	get_current_screen()->set_help_sidebar(
		'<p><strong>' . __( 'For more information:' ) . '</strong></p>' .
		'<p>' . __( '<a href="https://github.com/lmoffereins/incassoos">Development</a>', 'incassoos' ) . '</p>'
	);
}

/**
 * Register help tabs for the Encryption page
 *
 * @since 1.0.0
 */
function incassoos_admin_encryption_help_tabs() {
	$overview  = '<p>' . __( 'This screen gives insight in the process and status of encryption in Incassoos.', 'incassoos' ) . '</p>';
	$overview .= '<p>' . __( 'Only users with the appropriate capabilities are allowed to enable or disable encryption.', 'incassoos' ) . '</p>';

	if ( current_user_can( 'decrypt_incassoos_data' ) ) {
		$overview .= '<p>' . __( 'When encryption is not enabled, you can start a wizard that will guide you through the steps for doing so.', 'incassoos' ) . '</p>';
		$overview .= '<p>' . __( 'When encryption is enabled, you can start a wizard that will guide you through the steps for disabling encryption.', 'incassoos' ) . '</p>';
	}

	get_current_screen()->add_help_tab(
		array(
			'id'      => 'overview',
			'title'   => __( 'Overview' ),
			'content' => $overview
		)
	);

	get_current_screen()->set_help_sidebar(
		'<p><strong>' . __( 'For more information:' ) . '</strong></p>' .
		'<p>' . __( '<a href="https://github.com/lmoffereins/incassoos">Development</a>', 'incassoos' ) . '</p>'
	);
}
