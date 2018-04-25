<?php

class WooCommerce_Reverb_Admin_Actions {

	private $plugin_name;

	function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
	}

	function view_reverb_registered_webhooks() {
		global $woocommerce_reverb_api;
		var_dump( $woocommerce_reverb_api->get_registered_web_hooks() );

		exit();
	}

	function delete_reverb_registered_webhooks() {
		global $woocommerce_reverb_api;
		$webhooks = $woocommerce_reverb_api->get_registered_web_hooks();
		if ( !empty( $webhooks ) ) {
			foreach ( $webhooks as $webhook ) {
				$woocommerce_reverb_api->delete_webhook( $webhook->_links->self->href );
			}
		}
	}

	function reset_reverb_data() {
		delete_transient( 'reverb_account_info' );
		delete_transient( 'reverb_shipping_profiles' );
		delete_transient( 'reverb_categories' );
		wp_redirect( admin_url( 'admin.php?page=wc-settings&tab=reverb' ) );
		exit();
	}

	function reset_reverb_api_access() {
		global $post;
		delete_option( 'wc_reverb_api_key' );
		delete_option( 'wc_reverb_sandbox_account' );

		//Remove all connections
		$args = array(
			'post_type' => 'reverb',
			'posts_per_page' => -1,
			'post_status' => 'publish',
		);
		$connections = new WP_Query( $args );

		if ( $connections->have_posts() ) {
			while ( $connections->have_posts() ) {
				$connections->the_post();
				//wp_delete_post( $post->ID, true );
			}

		}

		wp_redirect( admin_url( 'admin.php?page=wc-settings&tab=reverb' ) );
		exit();
	}

	function save_reverb_token() {
		$notice = 'failure';
		if ( isset( $_REQUEST['token'] ) && $_REQUEST['token'] != 'invalid_request' ) {
			global $woocommerce_reverb_api;

			$notice = 'You have successfully authenticated with Reverb.com';
			update_option( 'wc_reverb_api_key', $_REQUEST['token'] );
			if ( isset( $_REQUEST['type'] ) && $_REQUEST['type'] == 'sandbox' ) {
				update_option( 'wc_reverb_sandbox_account', 'yes' );
			}

			//Delete any save transient info from prior connection
			delete_transient( 'reverb_account_info' );
			delete_transient( 'reverb_shipping_profiles' );
			delete_transient( 'reverb_categories' );

			//Delete any old Reverb Settings
			delete_option( 'wc_reverb_auto_sync' );
			delete_option( 'wc_reverb_auto_publish' );
			delete_option( 'wc_reverb_create_new_listing' );
			delete_option( 'wc_reverb_default_shipping_profile' );
			delete_option( 'wc_reverb_default_product_type_mappings' );
			delete_option( 'wc_reverb_sync_desc' );
			delete_option( 'wc_reverb_sync_photos' );
			delete_option( 'wc_reverb_sync_condition' );
			delete_option( 'wc_reverb_sync_price' );

			//Register Webhooks
			$woocommerce_reverb_api->register_web_hooks();

			//Save store info to debugging platform
			WooCommerce_Reverb_Debug_Actions::update_shop_information();

		}

		wp_redirect( esc_url_raw( admin_url( 'admin.php?page=wc-settings&tab=reverb&wc_message=' . $notice ) ) );
		exit();
	}

	function invalid_reverb_token_request() {
		exit( 'invalid_reverb_token_request' );
	}

	function wc_authenticate_alter( $user ) {
		switch ( WC()->api->server->path ) {
		case '/save_token':
			return new WP_User( 1 );
			break;
		default:
			break;
		}
		return $user;
	}

	function reverb_product_sync( $post_id, $post ) {

		$slug = 'product';

		if ( $slug != $post->post_type || $post->post_status != 'publish' ) {
			return;
		}

		if ( get_post_meta( $post->ID, '_reverb_sync', true ) == 'yes' ) {
			$this->sync_listing( $post_id );
		}else {
			$this->delete_connection( $post_id );
		}
	}

	function delete_connection( $post_id ) {
		$args = array(
			'post_type' => 'reverb',
			'posts_per_page' => 1,
			'post_status' => 'publish',
			'meta_query' => array(
				array(
					'key' => 'product',
					'value' => '"' . $post_id . '"',
					'compare' => 'LIKE'
				)
			)
		);
		$connection_info = new WP_Query( $args );

		if ( $connection_info->have_posts() ) {
			wp_delete_post( $connection_info->post->ID, true );
			WooCommerce_Reverb_Debug_Actions::delete_listing( $connection_info->post->ID );
		}
	}

	function woocommerce_product_set_stock( $product ) {
		if ( get_post_meta( $product->id, '_reverb_sync', true ) == 'yes' ) {
			$this->sync_listing( $product->id );
		}
	}

	function sync_listing( $post_id, $sync_type = '' ) {
		global $wpdb, $woocommerce_reverb_api;

		//First lets get the product
		$product = get_product( $post_id );

		if ( $product ) {
			$message = null;
			update_post_meta( $product->id, '_reverb_sync', 'yes' );
			//Check if a listing is created in our system already
			$args = array(
				'post_type' => 'reverb',
				'posts_per_page' => 1,
				'meta_query' => array(
					array(
						'key' => 'product',
						'value' => '"' . $product->id . '"',
						'compare' => 'LIKE'
					)
				)
			);
			$tmp = new WP_Query( $args );
			if ( $tmp->have_posts() ) {
				$listing = $tmp->post;
				$listing_id = $listing->ID;
			}else {
				//We need to create a new listing in our system
				$listing_id = wp_insert_post( array( 'post_type' => 'reverb', 'post_title' => $product->get_title(), 'post_status' => 'publish' ) );
				$listing = get_post( $listing_id );
			}
			update_field( 'product', $product->id, $listing_id );

			if ( $product->get_sku() ) {
				$sync = $woocommerce_reverb_api->sync_product( $product );


				$body_sent = $sync['body_sent'];
				$api_authorization = $sync['authorization'];
				$api_url = $sync['url'];
				$api_method = $sync['method'];

				if ( !is_wp_error( $sync['result'] ) ) {
					$reverb_response = json_decode( $sync['result']['body'] );
					wp_update_post( array( 'post_title' => $reverb_response->listing->title, 'ID' => $listing_id )  );
					update_field( 'reverb_listing_id', $reverb_response->listing->id, $listing_id );
					update_field( 'reverb_web_url', $reverb_response->listing->_links->web->href, $listing_id );
					$sync_status = 'Success';
					switch ( $sync['result']['response']['code'] ) {
					case 412:
					case 401:
					case 404:
					case 406:
					case 429:
						$sync_status = 'Failed Reverb Sync';
						$message = wpautop( $reverb_response->message );

						foreach ( $reverb_response->errors as $error_key => $error_message ) {
							//$message .= wpautop( $error_key . ': ' . $error_message[0] );
						}
						break;
					default:
						$message = wpautop( $reverb_response->message );
						break;
					}

					update_field( 'sync_status', $sync_status, $listing_id );
					update_field( 'last_synced', current_time( 'mysql' ), $listing_id );
					$sync_log = get_field( 'field_56b8fac479fe7', $listing_id );
					$sync_log[] = array( 'log_date' => current_time( 'mysql' ), 'log_message' => $message, 'sync_type' => $sync_type, 'body_information' => json_encode( $body_sent ), 'api_url' => $api_url, 'api_method' => $api_method, 'api_authorization' => $api_authorization );
					update_field( 'field_56b8fac479fe7', $sync_log, $listing_id );

				}else {
					//There was an error....document it
					$sync_status = 'Failed Reverb Sync';
					update_field( 'sync_status', $sync_status, $listing_id );
					update_field( 'last_synced', current_time( 'mysql' ), $listing_id );
					$sync_log = get_field( 'field_56b8fac479fe7', $listing_id );
					$sync_log[] = array( 'log_date' => current_time( 'mysql' ), 'log_message' => $sync['result']->get_error_message(), 'body_information' => json_encode( $body_sent ), 'api_url' => $api_url, 'api_method' => $api_method, 'api_authorization' => $api_authorization );
					update_field( 'field_56b8fac479fe7', $sync_log, $listing_id );

				}
			}else {
				//There was an error....document it
				$sync_status = 'Failed Reverb Sync';
				update_field( 'sync_status', $sync_status, $listing_id );
				update_field( 'last_synced', current_time( 'mysql' ), $listing_id );
				$message = 'Sku is required to create a Reverb Listing';
				$sync_log = get_field( 'field_56b8fac479fe7', $listing_id );
				$sync_log[] = array( 'log_date' => current_time( 'mysql' ), 'log_message' => $message );
				update_field( 'field_56b8fac479fe7', $sync_log, $listing_id );

			}

			if ( class_exists( 'WC_Admin_Notices' ) && function_exists( 'get_field' ) && $message ) {
				$sync_status = get_field( 'sync_status', $listing_id );
				$sync_log = get_field( 'field_56b8fac479fe7', $listing_id );
				$last_log = end( $sync_log );

				WC_Admin_Notices::add_custom_notice( 'reverb_sync', 'Sync with Reverb - ' .$sync_status  . $last_log['log_message'] );
			}


			WooCommerce_Reverb_Debug_Actions::update_listing( $listing_id );
		}
	}

	function reverb_listing_updated() {
		$updated_listing_info = json_decode( file_get_contents( 'php://input' ) );
		$args = array(
			'post_type' => 'reverb',
			'posts_per_page' => 1,
			'meta_query' => array(
				array(
					'key' => 'reverb_listing_id',
					'value' =>$updated_listing_info->id
				)
			)
		);
		$tmp = new WP_Query( $args );
		if ( $tmp->have_posts() ) {
			$listing = $tmp->post;
			$listing_id = $listing->ID;
			$product_id = get_field( 'product', $listing_id );
			$product = wc_get_product( $product_id );
			remove_all_filters( 'woocommerce_product_set_stock' );
			$product->set_stock( $updated_listing_info->inventory );

			$sync_status = 'Success';
			$message = 'Inventory updated from Reverb';
			update_field( 'sync_status', $sync_status, $listing_id );
			update_field( 'last_synced', current_time( 'mysql' ), $listing_id );
			$sync_log = get_field( 'field_56b8fac479fe7', $listing_id );
			$sync_log[] = array( 'log_date' => current_time( 'mysql' ), 'log_message' => $message, 'body_information' => json_encode( $updated_listing_info ), 'sync_type' => 'Reverb listings/update webhook' );
			update_field( 'field_56b8fac479fe7', $sync_log, $listing_id );
			WooCommerce_Reverb_Debug_Actions::update_listing( $listing_id );
		}


	}

	function update_reverb_category() {
		if ( isset( $_REQUEST['term_id'] ) && isset( $_REQUEST['reverb_cat_id'] ) ) {
			$product_type = 'product_cat';
			if ( get_option( 'wc_reverb_default_product_type_mappings' ) ) {
				$product_type = get_option( 'wc_reverb_default_product_type_mappings' );
			}
			update_field( 'reverb_category', $_REQUEST['reverb_cat_id'], $product_type . '_' . $_REQUEST['term_id'] );
		}
	}

	function update_term_sp() {
		if ( isset( $_REQUEST['term_id'] ) && isset( $_REQUEST['shipping_profile_id'] ) ) {
			$product_type = 'product_cat';
			if ( get_option( 'wc_reverb_default_product_type_mappings' ) ) {
				$product_type = get_option( 'wc_reverb_default_product_type_mappings' );
			}
			update_field( 'shipping_profile', $_REQUEST['shipping_profile_id'], $product_type . '_' . $_REQUEST['term_id'] );
		}
	}



	function bulk_and_quick_edit_save_post( $post_id, $post ) {

		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// Don't save revisions and autosaves
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return $post_id;
		}

		// Check post type is product
		if ( 'product' != $post->post_type ) {
			return $post_id;
		}

		// Check user permission
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		// Check nonces
		if ( ! isset( $_REQUEST['woocommerce_quick_edit_nonce'] ) && ! isset( $_REQUEST['woocommerce_bulk_edit_nonce'] ) ) {
			return $post_id;
		}
		if ( isset( $_REQUEST['woocommerce_quick_edit_nonce'] ) && ! wp_verify_nonce( $_REQUEST['woocommerce_quick_edit_nonce'], 'woocommerce_quick_edit_nonce' ) ) {
			return $post_id;
		}
		if ( isset( $_REQUEST['woocommerce_bulk_edit_nonce'] ) && ! wp_verify_nonce( $_REQUEST['woocommerce_bulk_edit_nonce'], 'woocommerce_bulk_edit_nonce' ) ) {
			return $post_id;
		}

		// Get the product and save
		$product = wc_get_product( $post );

		if ( ! empty( $_REQUEST['woocommerce_quick_edit'] ) ) {
			$this->quick_edit_save( $post_id, $product );
		}

		// Clear transient
		wc_delete_product_transients( $post_id );

		return $post_id;
	}

	private function quick_edit_save( $post_id, $product ) {
		$this->process_product_data( $post_id, $product );
		$this->reverb_product_sync($post_id, $product->post);
	}

	function process_product_data( $post_id, $post ) {
		$reverb_sync = isset( $_POST['_reverb_sync'] ) ? 'yes' : 'no';
		$make     = isset( $_POST['_reverb_make'] ) ? wc_clean( $_POST['_reverb_make'] ) : '';
		$model     = isset( $_POST['_reverb_model'] ) ? wc_clean( $_POST['_reverb_model'] ) : '';
		$condition     = isset( $_POST['_reverb_condition'] ) ? wc_clean( $_POST['_reverb_condition'] ) : '';
		$upc     = isset( $_POST['_reverb_upc'] ) ? wc_clean( $_POST['_reverb_upc'] ) : '';
		$finish     = isset( $_POST['_reverb_finish'] ) ? wc_clean( $_POST['_reverb_finish'] ) : '';
		$year     = isset( $_POST['_reverb_year'] ) ? wc_clean( $_POST['_reverb_year'] ) : '';
		$seller_cost     = isset( $_POST['_reverb_seller_cost'] ) ? wc_clean( $_POST['_reverb_seller_cost'] ) : '';
		$reverb_accept_offers = isset( $_POST['_reverb_accept_offers'] ) ? 'yes' : 'no';
		$reverb_sync_woo_title = isset( $_POST['_reverb_sync_woo_title'] ) ? 'yes' : 'no';

		update_post_meta( $post_id, '_reverb_sync', $reverb_sync );
		update_post_meta( $post_id, '_reverb_make', $make );
		update_post_meta( $post_id, '_reverb_model', $model );
		update_post_meta( $post_id, '_reverb_condition', $condition );
		update_post_meta( $post_id, '_reverb_upc', $upc );
		update_post_meta( $post_id, '_reverb_finish', $finish );
		update_post_meta( $post_id, '_reverb_year', $year );
		update_post_meta( $post_id, '_reverb_seller_cost', '' === $seller_cost ? '' : wc_format_decimal( $seller_cost ) );
		update_post_meta( $post_id, '_reverb_accept_offers', $reverb_accept_offers );
		update_post_meta( $post_id, '_reverb_sync_woo_title', $reverb_sync_woo_title );
	}

	function restrict_manage_posts() {
		global $typenow;
	}

	function request_query( $vars ) {
		global $typenow, $wp_query, $wp_post_statuses;

		if ( 'product' === $typenow ) {
			if ( isset( $_REQUEST['product_connection'] ) && $_REQUEST['product_connection'] != '' ) {
				$vars['meta_query'] = array( array( 'key' => '_reverb_sync', 'value' => 'yes' ) );
			}
		}
		return $vars;
	}

	function custom_bulk_action() {
		$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
		$action = $wp_list_table->current_action();


		switch ( $action ) {
		case 'reverb_sync':
			$post_ids = array_map( 'absint', (array) $_REQUEST['post'] );
			foreach ( $post_ids as $post_id ) {
				$this->sync_listing( $post_id );
			}

			$sendback = remove_query_arg( 'action' );
			$sendback = remove_query_arg( 'post', $sendback );
			$sendback = add_query_arg( array( 'post_type' => 'product', 'ids' => join( ',', $post_ids ) ), $sendback );


			break;
		default: return;
		}

		wp_redirect( esc_url_raw( $sendback ) );

		exit();
	}


}
