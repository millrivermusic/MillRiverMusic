<?php

class WooCommerce_Reverb_Admin_UI {

	private $plugin_name;


	function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
	}

	function plugin_action_links( $links ) {
		$action_links = array(
			'settings' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=reverb' ) . '" title="' . esc_attr( __( 'View WooCommerce Reverb Settings', 'woocommerce-reverb' ) ) . '">' . __( 'Settings', 'woocommerce-reverb' ) . '</a>',
		);

		return array_merge( $action_links, $links );
	}

	function admin_notices() {
?>
    <div id="message" class="updated woocommerce-message wc-connect">
		<p><?php _e( '<strong>Welcome to WooCommerce Reverb</strong> &#8211; You&lsquo;re almost ready to start listing your WooCommerce products on Reverb.com  :)', 'woocommerce' ); ?></p>
		<p class="submit"><a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=reverb' ) ); ?>" class="button-primary"><?php _e( 'Connect to Reverb.com', 'woocommerce' ); ?></a> <a class="button-secondary skip" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wc-hide-notice', 'install' ), 'woocommerce_hide_notices_nonce', '_wc_notice_nonce' ) ); ?>"><?php _e( 'Skip Setup', 'woocommerce' ); ?></a></p>
	</div>
    <?php
	}

	function bulk_edit() {

	}

	function quick_edit() {

		include plugin_dir_path( dirname( __FILE__ ) ) . 'admin/views/html-quick-edit-product.php';
	}

	function product_data_tab( $product_data_tabs ) {
		$product_data_tabs['reverb-tab'] = array(
			'class' => array( 'show_if_simple' ),
			'label' => 'Reverb',
			'target' => 'reverb_product_data',
		);
		return $product_data_tabs;
	}

	function product_data_panel() {
		global $woocommerce, $post;
?>
	<!-- id below must match target registered in above add_my_custom_product_data_tab function -->
	<div id="reverb_product_data" class="panel woocommerce_options_panel ">
		<?php


		$args = array(
			'post_type' => 'reverb',
			'posts_per_page' => 1,
			'post_status' => 'publish',
			'meta_query' => array(
				array(
					'key' => 'product',
					'value' => '"' . $post->ID . '"',
					'compare' => 'LIKE'
				)
			)
		);
		$connection_info = new WP_Query( $args );
		$sync_desc = '';
		if ( $connection_info->have_posts() ) {
			$listing = $connection_info->post;
			$sync_desc = '<a href="' . add_query_arg( array( 'page' => 'wc-settings', 'tab' => 'reverb', 'section' => 'sync_status', 'listing_id' => $listing->ID ), admin_url( 'admin.php' ) ) . '" target="_blank">View Connection Information</a>';

		}
		woocommerce_wp_checkbox( array(
				'id'            => '_reverb_sync',
				'label'         => 'Sync with Reverb',
				'description'   => $sync_desc,
				'default'    => '0',
				'desc_tip'     => false,
			) );

		woocommerce_wp_checkbox( array(
				'id'            => '_reverb_sync_woo_title',
				'label'         => 'Sync WooCommerce Title',
				'description'   => 'Sync WooCommerce Product Title. If left unchecked, the Reverb title will default to Make Model Year (Fender Stratocaster 2017). If checked, your WooCommerce Product title will be the Reverb listing title.',
				'default'    => '0',
				'desc_tip'     => true,
				'wrapper_class' => 'reverb_fields'
			) );

		woocommerce_wp_text_input( array( 'id' => '_reverb_make', 'label' => __( 'Make', 'woocommerce' ), 'placeholder' => '', 'desc_tip' => 'true', 'description' => __( 'Reverb provides a Make and Model guesser - if you do not specify these fields, we will try to extract the fields from the title.', 'woocommerce' ), 'type' => 'text', 'wrapper_class' => 'reverb_fields' ) );
		woocommerce_wp_text_input( array( 'id' => '_reverb_model', 'label' => __( 'Model', 'woocommerce' ), 'placeholder' => get_the_title(), 'desc_tip' => 'true', 'description' => __( 'Reverb provides a Make and Model guesser - if you do not specify these fields, we will try to extract the fields from the title.', 'woocommerce' ), 'type' => 'text', 'wrapper_class' => 'reverb_fields' ) );



		$conditions = array( '' => 'Select Condition', 'Non Functioning' => 'Non Functioning', 'Poor' => 'Poor', 'Fair' => 'Fair', 'Good' => 'Good', 'Very Good' => 'Very Good', 'Excellent' => 'Excellent', 'Mint' => 'Mint', 'Brand New' => 'Brand New' );
		woocommerce_wp_select( array( 'id' => '_reverb_condition', 'label' => __( 'Condition', 'woocommerce' ), 'options' => $conditions, 'desc_tip' => false, 'description' => '', 'wrapper_class' => 'reverb_fields' ) );

		woocommerce_wp_text_input( array( 'id' => '_reverb_upc', 'label' => __( 'UPC', 'woocommerce' ), 'placeholder' => '', 'desc_tip' => 'false', 'description' => '', 'type' => 'text', 'wrapper_class' => 'reverb_fields' ) );
		woocommerce_wp_text_input( array( 'id' => '_reverb_finish', 'label' => __( 'Finish', 'woocommerce' ), 'placeholder' => '', 'desc_tip' => 'false', 'description' => '', 'type' => 'text', 'wrapper_class' => 'reverb_fields' ) );
		woocommerce_wp_text_input( array( 'id' => '_reverb_year', 'label' => __( 'Year', 'woocommerce' ), 'placeholder' => '', 'desc_tip' => 'true', 'description' => __( 'The year field takes fuzzy language incuding ranges, like "1980s" or "1979-1981", "mid 80s" or exact years like "1959"', 'woocommerce' ), 'type' => 'text', 'wrapper_class' => 'reverb_fields' ) );
		woocommerce_wp_text_input( array( 'id' => '_reverb_seller_cost', 'label' => __( 'Seller Cost', 'woocommerce' ), 'placeholder' => '', 'desc_tip' => 'false', 'description' => '', 'type' => 'text', 'datatype' => 'price', 'wrapper_class' => 'reverb_fields' ) );

		woocommerce_wp_checkbox( array(
				'id'            => '_reverb_accept_offers',
				'label'         => 'Accept Offers',
				'description'   => 'Allows you to negotiate a final price with interested buyers.',
				'default'    => '0',
				'desc_tip'     => true,
				'wrapper_class' => 'reverb_fields'
			) );

?>
	</div>
	<?php
	}

	function woocommerce_product_filters( $filters ) {
		global $wp_query;
		//Reverb connection filtering
		( isset( $_REQUEST['product_connection'] ) ) ? $product_connection = $_REQUEST['product_connection'] : $product_connection = true;
		$output  = '<select name="product_connection" id="dropdown_product_connection">';
		$output .= '<option value="">' . __( 'Show all connections', 'woocommerce-reverb' ) . '</option>';
		$output .= '<option value="reverb" ' . selected( 'reverb', $product_connection, false ) . '>' . __( 'Synced with Reverb', 'woocommerce-reverb' ) . '</option>';

		$output .= '</select>';

		$filters .= $output;
		return $filters;
	}

	function post_row_actions( $actions, $post ) {
		if ( 'product' === $post->post_type ) {
			$reverb_actions = array(
				'reverb_create' => '<a href="' . add_query_arg( array( 'action' => 'reverb_sync', 'post' => $post->ID ) )  . '">Sync with Reverb</a>'
			);
			return array_merge( $actions, $reverb_actions );
		}
		return $actions;
	}

	function bulk_admin_footer() {
		global $post_type;

		if ( 'product' == $post_type ) {
?>
			<script type="text/javascript">
			jQuery(function() {
				jQuery('<option>').val('reverb_sync').text('<?php _e( 'Sync with Reverb', 'woocommerce' )?>').appendTo('select[name="action"]');
				jQuery('<option>').val('reverb_sync').text('<?php _e( 'Sync with Reverb', 'woocommerce' )?>').appendTo('select[name="action2"]');
			});
			</script>
			<?php
		}
	}

	function product_columns( $columns ) {
		$columns['connections'] = 'Connections';
		return $columns;
	}

	function product_columns_content( $column_name, $post_id ) {
		global $post, $the_product;

		if ( empty( $the_product ) || $the_product->get_id() != $post->ID ) {
			$the_product = wc_get_product( $post );
		}

		if ( $column_name == 'connections' ) {
			$args = array(
				'post_type' => 'reverb',
				'posts_per_page' => 1,
				'meta_query' => array(
					array(
						'key' => 'product',
						'value' => '"' . $post_id . '"',
						'compare' => 'LIKE'
					)
				)
			);
			$tmp = new WP_Query( $args );
			if ( $tmp->have_posts() ) {
				$listing = $tmp->post;
				$class = '';
				if ( $sync_status = get_field( 'sync_status', $listing->ID ) ) {
					if ( $sync_status == 'Failed Reverb Sync' ) {
						$class = 'failed';
					}
				}
				echo '<a href="' . add_query_arg( array( 'page' => 'wc-settings', 'tab' => 'reverb', 'section' => 'sync_status', 'listing_id' => $listing->ID ), admin_url( 'admin.php' ) ) . '" target="_blank"><img class="' . $class . '" src="' . plugin_dir_url( __FILE__ ) . 'img/reverb-16x16.png' . '" /></a>';


			}
			$product_meta = get_post_meta( $post_id );
			if ( array_key_exists( '_reverb_sync', $product_meta ) ) {
				echo '
					<div class="hidden" id="woocommerce_reverb_inline_' . $post->ID . '">
						<div class="reverb_sync">' . $product_meta['_reverb_sync'][0] . '</div>
						<div class="reverb_make">' . $product_meta['_reverb_make'][0] . '</div>
						<div class="reverb_model">' . $product_meta['_reverb_model'][0] . '</div>
						<div class="reverb_condition">' . $product_meta['_reverb_condition'][0] . '</div>
						<div class="reverb_upc">' . $product_meta['_reverb_upc'][0] . '</div>
						<div class="reverb_finish">' . $product_meta['_reverb_finish'][0] . '</div>
						<div class="reverb_year">' . $product_meta['_reverb_year'][0] . '</div>
						<div class="reverb_seller_cost">' . $product_meta['_reverb_seller_cost'][0] . '</div>
						<div class="reverb_accept_offers">' . $product_meta['_reverb_accept_offers'][0] . '</div>
						<div class="reverb_sync_woo_title">' . $product_meta['_reverb_sync_woo_title'][0] . '</div>
					</div>
				';
			}
		}
	}


}
