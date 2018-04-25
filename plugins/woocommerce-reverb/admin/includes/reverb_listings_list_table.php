<?php
if ( !class_exists( 'WP_List_Table' ) ) :
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
endif;
class Reverb_Listings_List_Table extends WP_List_Table {
	function get_columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'product' => 'Product',
			'variant' => 'Variant',
			'title' => 'Reverb Title',
			'sku' => 'SKU',
			'sync_status' => 'Sync Status',
			'sync_details' => 'Sync Details',
			'last_synced' => 'Last Synced' );

		return $columns;
	}

	function get_pagenum() {
		$pagenum = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
		if ( isset( $_REQUEST['paged'] ) ) {
			$pagenum = $_REQUEST['paged'];
		}
		return $pagenum;
	}

	protected function extra_tablenav( $which ) {
		if ( $which == 'top' ) {
			global $wp_query;
			//Reverb connection filtering
			$output  = '<select name="listing_sync_status" id="dropdown_listing_sync_status">';
			$output .= '<option value="">' . __( 'Show all statuses', 'woocommerce-reverb' ) . '</option>';
			$output .= '<option value="success" ' . selected( 'success', ( isset( $_REQUEST['listing_sync_status'] ) ? $_REQUEST['listing_sync_status'] : '' ), false ) . '>' . __( 'Success', 'woocommerce-reverb' ) . '</option>';
			$output .= '<option value="failed reverb sync" ' . selected( 'failed reverb sync', ( isset( $_REQUEST['listing_sync_status'] ) ? $_REQUEST['listing_sync_status'] : '' ), false ) . '>' . __( 'Failed', 'woocommerce-reverb' ) . '</option>';
			$output .= '<option value="missing_data" ' . selected( 'missing_data', ( isset( $_REQUEST['listing_sync_status'] ) ? $_REQUEST['listing_sync_status'] : '' ), false ) . '>' . __( 'Missing Data', 'woocommerce-reverb' ) . '</option>';
			$output .= '<option value="deleted" ' . selected( 'deleted', ( isset( $_REQUEST['listing_sync_status'] ) ? $_REQUEST['listing_sync_status'] : '' ), false ) . '>' . __( 'Deleted', 'woocommerce-reverb' ) . '</option>';

			$output .= '</select>';
			$output .= get_submit_button( __( 'Filter' ), 'button', 'filter_action', false, array( 'id' => 'post-query-submit' ) );
			echo $output;
		}


	}

	function prepare_items() {
		global $wpdb;

		$search = ( isset( $_REQUEST['s'] ) ) ? $_REQUEST['s'] : false;
		$status_filter = ( isset( $_REQUEST['listing_sync_status'] ) ) ? $_REQUEST['listing_sync_status'] : false;
		$listing_id = ( isset( $_REQUEST['listing_id'] ) ) ? $_REQUEST['listing_id'] : false;
		$per_page = 20;

		$args = array(
			'post_type' => 'reverb',
			'posts_per_page' => $per_page,
			'post_status' => 'publish',
			'orderby' => 'meta_value_date',
			'meta_key' => 'last_synced',
			'paged' => $this->get_pagenum()
		);

		if ( $listing_id ) {
			$args['post__in'] = array( $listing_id );
		}
		$meta_query = array();
		if ( $search ) {
			$products = new WP_Query( array(
					'post_type' => 'product',
					'meta_key' => '_sku',
					'meta_value' => $search,
					'meta_compare' => 'LIKE',
					'posts_per_page' => -1,
					'fields' => 'ids'
				) );
			if ( $products->have_posts() ) {
				$meta_query[] = array(
					'key' => 'product',
					'value' => $products->posts,
					'compare' => 'IN'
				);

			}
			$meta_query[] = array(
				'key' => 'reverb_listing_id',
				'value' => $_REQUEST['s']
			);
		}
		if ( $status_filter ) {
			$meta_query[] = array(
				'key' => 'sync_status',
				'value' => $status_filter
			);
		}
		if ( !empty( $meta_query ) ) {
			$meta_query['relation'] = 'OR';
			$args['meta_query'] = $meta_query;
		}

		$listings = new WP_Query( $args );


		$columns = $this->get_columns();
		$hidden = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );


		$current_page = $this->get_pagenum();

		$total_items = $listings->found_posts;

		$this->set_pagination_args( array( 'total_items' => $total_items, 'per_page' => $per_page ) );
		$this->items = $listings->posts;
	}

	function get_bulk_actions() {
		$actions = array(
			'reverb_sync'    => 'Sync with Reverb'
		);
		return $actions;
	}

	function column_cb( $listing ) {
		return sprintf(
			'<input type="checkbox" name="listing[]" value="%s" />', $listing->ID
		);
	}

	public function column_product( $listing ) {
		$product_id = get_field( 'product', $listing );
		if ( $product_id ) {
			$edit_link = get_edit_post_link( $product_id );
			$title     = _draft_or_post_title( $product_id );

			echo '<strong><a class="row-title" href="' . esc_url( $edit_link ) . '">' . esc_html( $title ) . '</a>';


			echo '</strong>';


		}
	}

	public function column_variant( $listing ) {
		if ( $variant = get_field( 'reverb_listing_id', $listing ) ) {
			echo $variant;
		}
	}

	public function column_title( $listing ) {
		if ( $reverb_url = get_field( 'reverb_web_url', $listing ) ) {
			echo '<a href="' . $reverb_url . '" target="_blank">' . $listing->post_title . '</a>';
		}else {
			echo $listing->post_title;
		}

	}

	public function column_sku( $listing ) {
		$product_id = get_field( 'product', $listing );
		if ( $product = wc_get_product( $product_id ) ) {
			echo $product->get_sku();
		}
	}

	public function column_sync_status( $listing ) {
		if ( $sync_status = get_field( 'sync_status', $listing->ID ) ) {
			echo $sync_status;
		}
	}

	public function column_sync_details( $listing ) {
		if ( $sync_log = get_field( 'sync_log', $listing->ID ) ) {
			if ( !empty( $sync_log ) ) {
				$current_item = end( $sync_log );
				echo $current_item['log_message'];
			}
		}
	}

	public function column_last_synced( $listing ) {
		if ( $last_synced = get_field( 'last_synced', $listing->ID ) ) {
			echo $last_synced;
		}
	}





}
?>
