<?php

/**
 * Incassoos_Consumer_Types_List_Table class
 *
 * @package Incassoos
 * @subpackage Administration
 */

/**
 * Class used to implement displaying built-in consumer types in a list table.
 *
 * @since 1.0.0
 *
 * @see WP_Terms_List_Table
 */
class Incassoos_Consumer_Types_List_Table extends WP_Terms_List_Table {
	/**
	 * Load and prepare the table items
	 *
	 * @since 1.0.0
	 */
	public function prepare_items() {
		$taxonomy = $this->screen->taxonomy;

		$per_page = $this->get_items_per_page( "edit_{$taxonomy}_per_page" );

		$search = ! empty( $_REQUEST['s'] ) ? trim( wp_unslash( $_REQUEST['s'] ) ) : '';

		$args = array(
			'search'     => $search,
			'page'       => $this->get_pagenum(),
			'number'     => $per_page, // Keep parameter for use in WP_Terms_List_Table
			'per_page'   => $per_page,
		);

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$args['orderby'] = trim( wp_unslash( $_REQUEST['orderby'] ) );
		}

		if ( ! empty( $_REQUEST['order'] ) ) {
			$args['order'] = trim( wp_unslash( $_REQUEST['order'] ) );
		}

		$args['offset'] = ( $args['page'] - 1 ) * $args['per_page'];

		// Save the values because 'number' and 'offset' can be subsequently overridden.
		$this->callback_args = $args;

		$query = incassoos_query_consumer_types( $args );

		$this->items = $query->query_result;

		$this->set_pagination_args(
			array(
				'total_items' => $query->total_count,
				'per_page'    => $per_page,
			)
		);
	}

	/**
	 * Return the available table columns
	 *
	 * @since 1.0.0
	 *
	 * @return string[] Array of column titles keyed by their column name.
	 */
	public function get_columns() {
		$columns = array(
			'cb'          => '<input type="checkbox" />',
			'name'        => _x( 'Name', 'type name' ),
			'description' => __( 'Description' ),
			'slug'        => __( 'Slug' ),
		);

		return $columns;
	}

	/**
	 * Return the sortable table columns
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	protected function get_sortable_columns() {
		return array(
			'name'        => array( 'name', false, _x( 'Name', 'type name' ), __( 'Table ordered by Name.' ), 'asc' ),
			'description' => array( 'description', false, __( 'Description' ), __( 'Table ordered by Description.' ) ),
			'slug'        => array( 'slug', false, __( 'Slug' ), __( 'Table ordered by Slug.' ) ),
		);
	}

	/**
	 * Output a single table row
	 *
	 * Can be called from AJAX functions like
	 * - wp_ajax_add_tag()
	 * - wp_ajax_inline_save_tax()
	 *
	 * @since 1.0.0
	 *
	 * @param Incassoos_Consumer_Type|WP_Term $tag Type or term object. Named `$tag` to match parent class for PHP 8 named parameter support.
	 */
	public function single_row( $tag, $level = 0 ) {
		// Restores the more descriptive, specific name for use within this method.
		// Support external calls to this method with a WP_Term parameter
		$type = incassoos_get_consumer_type( $tag );

		// To support inline editing of terms, use the term id
		$id = $type->is_term() ? $type->term_id : $type->id;

		echo '<tr id="tag-' . $id . '" class="level-0">';
		$this->single_row_columns( $type );
		echo '</tr>';
	}

	/**
	 * Return the contents of the name column
	 *
	 * @since 1.0.0
	 *
	 * @param Incassoos_Consumer_Type $type Type object.
	 * @return string
	 */
	public function column_name( $type ) {
		$taxonomy = $this->screen->taxonomy;

		$name = $type->label;

		// For terms
		if ( $type->is_term() ) {
			/**
			 * Filters display of the term name in the terms list table.
			 *
			 * The default output may include padding due to the term's
			 * current level in the term hierarchy.
			 *
			 * @since 1.0.0
			 *
			 * @see WP_Terms_List_Table::column_name()
			 *
			 * @param string $pad_tag_name The term name, padded if not top-level.
			 * @param WP_Term $tag         Term object.
			 */
			$name = apply_filters( 'term_name', $name, $type->get_term() );

			$qe_data = get_term( $type->term_id, $taxonomy, OBJECT, 'edit' );

			$uri = wp_doing_ajax() ? wp_get_referer() : $_SERVER['REQUEST_URI'];

			$edit_link = get_edit_term_link( $type, $taxonomy );

			if ( $edit_link ) {
				$edit_link = add_query_arg(
					'wp_http_referer',
					urlencode( wp_unslash( $uri ) ),
					$edit_link
				);
				$name      = sprintf(
					'<a class="row-title" href="%s" aria-label="%s">%s</a>',
					esc_url( $edit_link ),
					/* translators: %s: Taxonomy term name. */
					esc_attr( sprintf( __( '&#8220;%s&#8221; (Edit)' ), $type->name ) ),
					$name
				);
			}
		}

		$output = sprintf(
			'<strong>%s</strong><br />',
			$name
		);

		/** This filter is documented in wp-admin/includes/class-wp-terms-list-table.php */
		$quick_edit_enabled = apply_filters( 'quick_edit_enabled_for_taxonomy', true, $taxonomy );

		if ( $type->is_term() && $quick_edit_enabled ) {
			$output .= '<div class="hidden" id="inline_' . $qe_data->term_id . '">';
			$output .= '<div class="name">' . $qe_data->name . '</div>';

			/** This filter is documented in wp-admin/edit-tag-form.php */
			$output .= '<div class="slug">' . apply_filters( 'editable_slug', $qe_data->slug, $qe_data ) . '</div>';
			$output .= '<div class="parent">' . $qe_data->parent . '</div></div>';
		}

		return $output;
	}

	/**
	 * Return the contents of the slug column
	 *
	 * @since 1.0.0
	 *
	 * @param Incassoos_Consumer_Type $type Type object.
	 * @return string
	 */
	public function column_slug( $type ) {
		return $type->id;
	}

	/**
	 * Return the contents of a custom column
	 *
	 * @since 1.0.0
	 *
	 * @uses apply_filters() Calls 'incassoos_manage_consumer_type_custom_column'
	 *
	 * @param Incassoos_Consumer_Type $item Type object. Named `$item` to match parent class for PHP 8 named parameter support.
	 * @param string  $column_name Name of the column.
	 * @return string
	 */
	public function column_default( $item, $column_name ) {
		// Restores the more descriptive, specific name for use within this method.
		$type = $item;

		/**
		 * Filters the displayed columns in the consumer types list table.
		 *
		 * @since 1.0.0
		 *
		 * @param string                  $string      Custom column output. Default empty.
		 * @param string                  $column_name Name of the column.
		 * @param Incassoos_Consumer_Type $type        Type object.
		 */
		return apply_filters( 'incassoos_manage_consumer_type_custom_column', '', $column_name, $type );
	}
}
