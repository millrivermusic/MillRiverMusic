<?php

class WooCommerce_Reverb_Debug_Actions {

	private $plugin_name;

	function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
	}

	public static function update_listing($listing_id){
		$listing = get_post($listing_id);
		$meta = get_post_meta( $listing_id );
		if($listing){
			
			$endpoint = esc_url_raw( 'https://store.sleepinggiantstudios.com/wp-json/wc_reverb_info/v1/listing/update' );
			$product_id = get_field('product', $listing_id);
			$product = get_product($product_id);
			$args = array( 
				'site_url' => admin_url( 'admin-ajax.php' ),
				'title' => $listing->post_title,
				'product_title' => get_the_title( $product_id ),
				'product_sku' => $product->get_sku(),
				'product_permalink' => get_permalink( $product_id ),
				'listing_id' => $listing_id,
				'meta' => $meta,
				);
			$params = array(
			    'body'      => $args,
			    'sslverify' => false,
			    'timeout'   => 5,
			);

			$request = wp_safe_remote_post( $endpoint, $params );

			if( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) != 200 ) {
				return false;
			}

			$response = wp_remote_retrieve_body( $request );

			return $response;
		}
		
	}

	public static function delete_listing($listing_id){
			$endpoint = esc_url_raw( 'https://store.sleepinggiantstudios.com/wp-json/wc_reverb_info/v1/listing/delete' );
			
			$args = array( 
				'site_url' => admin_url( 'admin-ajax.php' ),
				'listing_id' => $listing_id,
				);
			$params = array(
			    'body'      => $args,
			    'sslverify' => false,
			    'timeout'   => 5,
			);

			$request = wp_safe_remote_post( $endpoint, $params );

			if( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) != 200 ) {
				return false;
			}

			$response = wp_remote_retrieve_body( $request );

			return $response;		
	}

	public static function update_shop_information(){
		global $woocommerce_reverb_api;


		$endpoint = esc_url_raw( 'https://store.sleepinggiantstudios.com/wp-json/wc_reverb_info/v1/reverb-shop/update' );

		$woocommerce_reverb_api = new WooCommerce_Reverb_API();
		$shop_info = $woocommerce_reverb_api->get_reverb_account_info();
		$available_profiles = $woocommerce_reverb_api->get_reverb_shipping_profiles();
		if(!empty($available_profiles)){
	    		$shipping_profile_options[''] = __( 'Please select default shipping profile', 'woocommerce' );
	    		foreach($available_profiles as $profile){
	    			$shipping_profile_options[$profile->id] = $profile->name;
	    		}
	    	}

		$args = array( 
			'site_url' => admin_url( 'admin-ajax.php' ),
			'shop_name' => $shop_info->name,
			'shop_description' => $shop_info->description,
			'api_key' => get_option('wc_reverb_api_key'),
			'personal_access_token' => get_option('wc_reverb_personal_access_token'),
			'auto_publish' => get_option('wc_reverb_auto_publish'),
			'create_new_listings' => get_option('wc_reverb_create_new_listing'),
			'default_shipping_profile' => $shipping_profile_options[get_option('wc_reverb_default_shipping_profile')],
			'default_product_type_mappings' => get_option('wc_reverb_default_product_type_mappings'),
			'description' => get_option('wc_reverb_sync_desc'),
			'photos' => get_option('wc_reverb_sync_photos'),
			'condition' => get_option('wc_reverb_sync_condition'),
			'price' => get_option('wc_reverb_sync_price'),

			);

		$params = array(
		    'body'      => $args,
		    'sslverify' => false,
		    'timeout'   => 5,
		);

		$request = wp_safe_remote_post( $endpoint, $params );

		if( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) != 200 ) {
			return false;
		}

		$response = wp_remote_retrieve_body( $request );

		return $response;
	}


}
