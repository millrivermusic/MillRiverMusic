<?php
class WooCommerce_Reverb_API {
	private $api_url;

	public function __construct() {
		$this->reverb_url = REVERB_URL;
		$this->api_key = get_option( 'wc_reverb_api_key' );
		$this->personal_access_token = get_option( 'wc_reverb_personal_access_token' );
		$this->api_url = $this->reverb_url . 'api';

		if ( get_option( 'wc_reverb_sandbox_account' ) == 'yes' ) {
			$this->reverb_url = REVERB_SANDBOX_URL;
			$this->api_url =  $this->reverb_url . 'api';
		}


		add_filter( 'add_curl_ssl', array( $this, 'add_curl_ssl' ) );

	}

	function add_curl_ssl( $args ) {
		$args['sslverify'] = true;
		$args['sslcertificates'] = ABSPATH . WPINC . '/certificates/ca-bundle.crt';
		$args['stream'] = false;
		$args['headers']['user-agent'] = $_SERVER['HTTP_USER_AGENT'];
		$args['filename'] = '';
		$args['decompress'] = false;

		return $args;
	}


	function get_reverb_account_info() {
		$reverb_account_info = array();
		$http = new WP_Http_Curl();
		$headers = array(
			'Authorization' => 'Bearer ' . $this->api_key,
			'content-type' => 'application/hal+json',
			'Accept-Version' => '2.0' );

		$shop_request = $http->request( $this->api_url . '/shop', apply_filters( 'add_curl_ssl', array( 'headers' => $headers, 'method' => 'GET' ) ) );
		if ( !is_wp_error( $shop_request ) ) {
			$response = json_decode( $shop_request['body'] );
			if ( !empty( $response ) ) {
				$reverb_account_info = $response;
				set_transient( 'reverb_account_info', $reverb_account_info, 12 * HOUR_IN_SECONDS );
			}
		}

		return $reverb_account_info;
	}

	function get_reverb_shipping_profiles() {
		if ( false === ( $reverb_shipping_profiles = get_transient( 'reverb_shipping_profiles' ) ) ) {
			$http = new WP_Http_Curl();
			$headers = array(
				'Authorization' => 'Bearer ' . $this->api_key,
				'content-type' => 'application/hal+json',
				'Accept-Version' => '2.0' );
			$shop_request = $http->request( $this->api_url . '/shop', apply_filters( 'add_curl_ssl', array( 'headers' => $headers, 'method' => 'GET' ) ) );

			if ( !is_wp_error( $shop_request ) ) {
				$response = json_decode( $shop_request['body'] );
				if ( !empty( $response->shipping_profiles ) ) {
					$reverb_shipping_profiles = $response->shipping_profiles;
					set_transient( 'reverb_shipping_profiles', $reverb_shipping_profiles, 12 * HOUR_IN_SECONDS );
				}
			}
		}
		return $reverb_shipping_profiles;
	}

	function get_reverb_categories() {
		if ( !( $reverb_categories = get_transient( 'reverb_categories' ) ) ) {
			$response = wp_remote_get( $this->api_url . '/categories' );
			if ( !is_wp_error( $response ) && $response['response']['code'] == 200 ) {

				$body = json_decode( $response['body'] );
				$full = $body->categories;

				if ( !empty( $full ) ) {
					foreach ( $full as $parent_cat ) {
						if ( $parent_cat->uuid ) {
							$reverb_categories[] = array( 'id' => $parent_cat->uuid, 'text' => $parent_cat->name );
							if ( !empty( $parent_cat->subcategories ) ) {
								foreach ( $parent_cat->subcategories as $sub_cat ) {
									$reverb_categories[] = array( 'id' => $sub_cat->uuid, 'text' => $parent_cat->name . '/' . $sub_cat->name );
								}
							}
						}
					}
				}
			}


			set_transient( 'reverb_categories', $reverb_categories, 12 * HOUR_IN_SECONDS );
		}

		return $reverb_categories;
	}

	function get_listing_by_sku( $sku ) {
		$http = new WP_Http_Curl();
		$headers = array(
			'Authorization' => 'Bearer ' . $this->api_key,
			'content-type' => 'application/hal+json',
			'Accept-Version' => '2.0' );
		$current_listing_request = $http->request( $this->api_url . '/my/listings?sku=' . $sku . '&state=all', apply_filters( 'add_curl_ssl', array( 'headers' => $headers, 'method' => 'GET' ) ) );

		if ( !is_wp_error( $current_listing_request ) ) {
			$response = json_decode( $current_listing_request['body'] );

			if ( !empty( $response->listings ) ) {
				return $response->listings[0];
			}

		}

		return false;
	}

	function sync_product( $product ) {
		$sync = true;
		$url = $this->api_url . '/listings';
		$method = 'POST';

		$current_listing = $this->get_listing_by_sku( $product->get_sku() );
		if ( $current_listing ) {
			$existing_listing = true;
			$url .= '/' . $current_listing->id;
			$method = 'PUT';

		}

		$http = new WP_Http_Curl();

		$headers = array(
			'Authorization' => 'Bearer ' . $this->api_key,
			'content-type' => 'application/hal+json',
			'cache-control' => 'no-cache',
			'Accept-Version' => '2.0' );


		$make = get_post_meta( $product->id, '_reverb_make', true );
		$model = get_post_meta( $product->id, '_reverb_model', true );
		if ( $model == '' ) {
			$model = $product->get_title();
		}
		$condition = get_post_meta( $product->id, '_reverb_condition', true );
		$upc = get_post_meta( $product->id, '_reverb_upc', true );
		$finish = get_post_meta( $product->id, '_reverb_finish', true );
		$year = get_post_meta( $product->id, '_reverb_year', true );
		$seller_cost = get_post_meta( $product->id, '_reverb_seller_cost', true );
		$accept_offers = false;
		if ( get_post_meta( $product->id, '_reverb_accept_offers', true ) == 'yes' ) {
			$accept_offers = true;
		}
		$sync_woo_title = false;
		if ( get_post_meta( $product->id, '_reverb_sync_woo_title', true ) == 'yes' ) {
			$sync_woo_title = true;
		}

		$shipping_profile = null;
		if ( get_option( 'wc_reverb_default_shipping_profile' ) ) {
			$shipping_profile = get_option( 'wc_reverb_default_shipping_profile' );
		}

		$product_type_map_tax = 'product_cat';
		if ( get_option( 'wc_reverb_default_product_type_mappings' ) ) {
			$product_type_map_tax = get_option( 'wc_reverb_default_product_type_mappings' );
		}
		$categories = get_the_terms( $product->id, $product_type_map_tax );
		if ( !empty( $categories ) ) {
			foreach ( $categories as $cat ) {
				if ( get_field( 'reverb_category', $cat ) && !$category_uuid ) {
					$category_uuid = get_field( 'reverb_category', $cat );
					if ( get_field( 'shipping_profile', $cat ) ) {
						$shipping_profile = get_field( 'shipping_profile', $cat );
					}

				}

			}


		}

		if ( $existing_listing ) {
			$body = array(
				'title' => $make . ' ' . $model . ' ' . $year,
				'make' => $make,
				'sku' => $product->get_sku(),
				'model' => $model,
				'upc' => $upc,
				'finish' => $finish,
				'year' => $year,
				'seller_cost' => $seller_cost,
				'offers_enabled' => $accept_offers,

			);

			if($sync_woo_title){
				$body['title'] = $product->get_title();
			}

			if ( $category_uuid ) {
				$body['category_uuids'] = $category_uuid;
			}

			if ( $shipping_profile ) {
				$body['shipping_profile_id'] = $shipping_profile;
			}

			if ( $product->managing_stock() ) {
				$body['has_inventory'] = true;
				$body['inventory'] = $product->get_stock_quantity();
			}

			if ( get_option( 'wc_reverb_sync_desc' ) == 'yes' ) {
				$body['description'] = apply_filters( 'the_content', $product->post->post_content );
			}
			if ( get_option( 'wc_reverb_sync_photos' ) == 'yes' ) {
				$photos = array();

				if ( has_post_thumbnail( $product->id ) ) {
					$src = wp_get_attachment_image_src( get_post_thumbnail_id( $product->id ), 'full' );
					$photos[] = $src[0];
				}
				$attachment_ids = $product->get_gallery_attachment_ids();
				if ( !empty( $attachment_ids ) ) {
					foreach ( $attachment_ids as $attachment_id ) {
						$src = wp_get_attachment_image_src( $attachment_id, 'full' );
						$photos[] = $src[0];
					}
				}
				$body['photos'] = $photos;
			}
			if ( get_option( 'wc_reverb_sync_condition' ) == 'yes' ) {
				$body['condition'] = $condition;
			}
			if ( get_option( 'wc_reverb_sync_price' ) == 'yes' ) {
				$body['price'] = $product->get_price();
			}

		}else {
			//Send all of the info we have...
			$body = array(
				'title' => $make . ' ' . $model . ' ' . $year,
				'make' => $make,
				'model' => $model,
				'condition' => $condition,
				'upc' => $upc,
				'finish' => $finish,
				'year' => $year,
				'seller_cost' => $seller_cost,
				'offers_enabled' => $accept_offers,
				'sku' => $product->get_sku(),
				'description' => apply_filters( 'the_content', $product->post->post_content ),
				'price' => $product->get_price(),
			);

			if($sync_woo_title){
				$body['title'] = $product->get_title();
			}

			if ( $category_uuid ) {
				$body['category_uuids'] = $category_uuid;
			}

			if ( $shipping_profile ) {
				$body['shipping_profile_id'] = $shipping_profile;
			}

			if ( $product->managing_stock() ) {
				$body['has_inventory'] = true;
				$body['inventory'] = $product->get_stock_quantity();
			}

			$photos = array();

			if ( has_post_thumbnail( $product->id ) ) {
				$src = wp_get_attachment_image_src( get_post_thumbnail_id( $product->id ), 'full' );
				$photos[] = $src[0];
			}

			$attachment_ids = $product->get_gallery_attachment_ids();
			if ( !empty( $attachment_ids ) ) {
				foreach ( $attachment_ids as $attachment_id ) {
					$src = wp_get_attachment_image_src( $attachment_id, 'full' );
					$photos[] = $src[0];
				}
			}

			$body['photos'] = $photos;

			if ( get_option( 'wc_reverb_create_new_listing' ) == 'no' ) {
				$sync = false;
				$reason = new WP_Error( 'woocommerce_reverb_cannot_create_new_listing', 'WooCommerce Reverb cannot create new listings.  You can <a href="' . admin_url( 'admin.php?page=wc-settings&tab=reverb' ) . '">change this setting here</a>.' );
			}
		}

		if ( get_option( 'wc_reverb_auto_publish' ) == 'yes' ) {
			$body['publish'] = true;
		}

		if ( $body['has_inventory'] && $body['inventory'] == 0 ) {
			$body['publish'] = false;
		}

		if ( $sync ) {
			$args = apply_filters( 'add_curl_ssl', array( 'body' => wp_json_encode( $body ), 'headers' => $headers, 'method' => $method, 'authorization' => $headers['Authorization'] ) );

			$result = $http->request( $url, $args );
			return array( 'result' => $result, 'body_sent' => $body, 'url' => $url, 'method' => $method, 'authorization' => $headers['Authorization'] );
		}else {
			return array( 'result' => $reason, 'body_sent' => '' );
		}


	}

	function get_my_listings() {
		$http = new WP_Http_Curl();
		$headers = array(
			'Authorization' => 'Bearer ' . $this->api_key,
			'content-type' => 'application/hal+json',
			'Accept-Version' => '2.0' );
		$current_listing_request = $http->request( $this->api_url . '/my/listings', apply_filters( 'add_curl_ssl', array( 'headers' => $headers, 'method' => 'GET' ) ) );
		return $current_listing_request;
	}

	function register_web_hooks() {
		$registrations = $this->get_registered_web_hooks();
		if ( empty( $registrations ) ) {

			if ( $this->api_key ) {
				$http = new WP_Http_Curl();

				$url = $this->api_url . '/webhooks/registrations';
				$method = 'POST';
				$headers = array(
					'Authorization' => 'Bearer ' . $this->api_key,
					'content-type' => 'application/hal+json',
					'Accept-Version' => '2.0' );
				$body = json_encode( array(
						'url' => add_query_arg( array( 'action' => 'reverb_listing_updated' ), admin_url( 'admin-ajax.php' ) ),
						'topic' => 'listings/update'
					) );

				$args = apply_filters( 'add_curl_ssl', array( 'body' => $body, 'headers' => $headers, 'method' => $method ) );

				$result = $http->request( $url, $args );
			}
		}
	}

	function delete_webhook( $webhook ) {
		if ( $this->api_key && $webhook ) {
			$http = new WP_Http_Curl();

			$url = $webhook;
			$method = 'DELETE';
			$headers = array(
				'Authorization' => 'Bearer ' . $this->api_key,
				'content-type' => 'application/hal+json',
				'Accept-Version' => '2.0' );

			$args = apply_filters( 'add_curl_ssl', array( 'headers' => $headers, 'method' => $method ) );

			$result = $http->request( $url, $args );
			return $result;
		}
	}

	function get_registered_web_hooks() {
		$registrations = array();
		if ( $this->api_key ) {
			$http = new WP_Http_Curl();

			$url = $this->api_url . '/webhooks/registrations';
			$method = 'GET';
			$headers = array(
				'Authorization' => 'Bearer ' . $this->api_key,
				'content-type' => 'application/hal+json',
				'Accept-Version' => '2.0' );
			$args = apply_filters( 'add_curl_ssl', array( 'headers' => $headers, 'method' => $method ) );
			$result = $http->request( $url, $args );
			$body = json_decode( $result['body'] );

			$registrations = $body->registrations;
		}
		return $registrations;
	}
}
?>
