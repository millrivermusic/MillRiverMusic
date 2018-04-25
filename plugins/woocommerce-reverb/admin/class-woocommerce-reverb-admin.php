<?php

class WooCommerce_Reverb_Admin {

	private $plugin_name;
	private $version;


	function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;

		
	}

	function remove_sync_notices(){
		WC_Admin_Notices::remove_notice('reverb_sync');
	}

	function enqueue_styles() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woocommerce-reverb-admin.css', array(), $this->version, 'all' );
	}
	
	function enqueue_scripts() {
		if(function_exists('acf_enqueue_scripts')){
			acf_enqueue_scripts();	
		}
		
		$screen = get_current_screen();

		if($screen->id == 'product_page_woocommerce-reverb-product-type-mappings' || $screen->id == 'woocommerce_page_wc-settings'){
			global $woocommerce_reverb_api;

			wp_enqueue_style( 'select2' );
			wp_enqueue_script( 'select2' );
			wp_enqueue_script( $this->plugin_name . '_ptm', plugin_dir_url( __FILE__ ) . 'js/product-type-mappings.js', array( 'jquery','select2', 'backbone' ), $this->version, true );	

			$reverb_categories = $woocommerce_reverb_api->get_reverb_categories();
			
	    	$available_profiles = $woocommerce_reverb_api->get_reverb_shipping_profiles();
	    	if(!empty($available_profiles)){
	    		foreach($available_profiles as $profile){
	    			$shipping_profile_options[] = array('id' => $profile->id, 'text' =>$profile->name);
	    		}
	    	}
			    	
			$data = array(
				'reverb_cats' => $reverb_categories,
				'shipping_profiles' => $shipping_profile_options,
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'update_term_action' => 'update_reverb_category',
				'update_term_sp_action' => 'update_term_sp'
				);
			wp_localize_script( $this->plugin_name . '_ptm', 'vars', $data );
		}

		wp_enqueue_style('bootstrap-switch', plugin_dir_url(__FILE__) . 'css/bootstrap-switch.css');
		wp_enqueue_script( 'bootstrap-switch', plugin_dir_url( __FILE__ ) . 'js/bootstrap-switch.js', array( 'jquery' ), $this->version, true );	
		wp_enqueue_script( $this->plugin_name . '_admin', plugin_dir_url( __FILE__ ) . 'js/admin.js', array( 'jquery','bootstrap-switch', 'backbone' ), $this->version, true );	

	}	

	function custom_post_types(){
		$labels = array(
			'name'               => _x( 'Reverb Listings', 'post type general name', 'woocommerce-reverb' ),
			'singular_name'      => _x( 'Reverb Listing', 'post type singular name', 'woocommerce-reverb' ),
			'menu_name'          => _x( 'Reverb Listings', 'admin menu', 'woocommerce-reverb' ),
			'name_admin_bar'     => _x( 'Reverb Listing', 'add new on admin bar', 'woocommerce-reverb' ),
			'add_new'            => _x( 'Add New', 'Reverb Listing', 'woocommerce-reverb' ),
			'add_new_item'       => __( 'Add New Reverb Listing', 'woocommerce-reverb' ),
			'new_item'           => __( 'New Reverb Listing', 'woocommerce-reverb' ),
			'edit_item'          => __( 'Edit Reverb Listing', 'woocommerce-reverb' ),
			'view_item'          => __( 'View Reverb Listing', 'woocommerce-reverb' ),
			'all_items'          => __( 'All Reverb Listings', 'woocommerce-reverb' ),
			'search_items'       => __( 'Search Reverb Listings', 'woocommerce-reverb' ),
			'parent_item_colon'  => __( 'Parent Reverb Listings:', 'woocommerce-reverb' ),
			'not_found'          => __( 'No Reverb Listings found.', 'woocommerce-reverb' ),
			'not_found_in_trash' => __( 'No Reverb Listings found in Trash.', 'woocommerce-reverb' )
		);

		$args = array(
			'labels'             => $labels,
	        'description'        => __( 'Description.', 'woocommerce-reverb' ),
			'public'             => false,
			'publicly_queryable' => false,
			'exclude_from_search' => true,
			'show_ui'            => false,
			'show_in_menu'       => false,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'reverb' ),
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title' ),
			'rewrite'			 => false
		);

		register_post_type( 'reverb', $args );
	}	

	function woocommerce_get_settings_pages($settings){
		$settings[] = include('class-woocommerce-reverb-admin-settings.php');
		return $settings;
	}

	function custom_taxonomies(){


			$labels = array(
				'name'                       => _x( 'Conditions', 'Taxonomy General Name', 'text_domain' ),
				'singular_name'              => _x( 'Condition', 'Taxonomy Singular Name', 'text_domain' ),
				'menu_name'                  => __( 'Condition', 'text_domain' ),
				'all_items'                  => __( 'All Items', 'text_domain' ),
				'parent_item'                => __( 'Parent Item', 'text_domain' ),
				'parent_item_colon'          => __( 'Parent Item:', 'text_domain' ),
				'new_item_name'              => __( 'New Item Name', 'text_domain' ),
				'add_new_item'               => __( 'Add New Item', 'text_domain' ),
				'edit_item'                  => __( 'Edit Item', 'text_domain' ),
				'update_item'                => __( 'Update Item', 'text_domain' ),
				'view_item'                  => __( 'View Item', 'text_domain' ),
				'separate_items_with_commas' => __( 'Separate items with commas', 'text_domain' ),
				'add_or_remove_items'        => __( 'Add or remove items', 'text_domain' ),
				'choose_from_most_used'      => __( 'Choose from the most used', 'text_domain' ),
				'popular_items'              => __( 'Popular Items', 'text_domain' ),
				'search_items'               => __( 'Search Items', 'text_domain' ),
				'not_found'                  => __( 'Not Found', 'text_domain' ),
				'no_terms'                   => __( 'No items', 'text_domain' ),
				'items_list'                 => __( 'Items list', 'text_domain' ),
				'items_list_navigation'      => __( 'Items list navigation', 'text_domain' ),
			);
			$args = array(
				'labels'                     => $labels,
				'hierarchical'               => true,
				'public'                     => true,
				'show_ui'                    => true,
				'show_admin_column'          => true,
				'show_in_nav_menus'          => true,
				'show_tagcloud'              => false,
			);
			register_taxonomy( 'reverb_condition', array( 'product' ), $args );

			$labels = array(
				'name'                       => _x( 'Make', 'Taxonomy General Name', 'text_domain' ),
				'singular_name'              => _x( 'Make', 'Taxonomy Singular Name', 'text_domain' ),
				'menu_name'                  => __( 'Make', 'text_domain' ),
				'all_items'                  => __( 'All Items', 'text_domain' ),
				'parent_item'                => __( 'Parent Item', 'text_domain' ),
				'parent_item_colon'          => __( 'Parent Item:', 'text_domain' ),
				'new_item_name'              => __( 'New Item Name', 'text_domain' ),
				'add_new_item'               => __( 'Add New Item', 'text_domain' ),
				'edit_item'                  => __( 'Edit Item', 'text_domain' ),
				'update_item'                => __( 'Update Item', 'text_domain' ),
				'view_item'                  => __( 'View Item', 'text_domain' ),
				'separate_items_with_commas' => __( 'Separate items with commas', 'text_domain' ),
				'add_or_remove_items'        => __( 'Add or remove items', 'text_domain' ),
				'choose_from_most_used'      => __( 'Choose from the most used', 'text_domain' ),
				'popular_items'              => __( 'Popular Items', 'text_domain' ),
				'search_items'               => __( 'Search Items', 'text_domain' ),
				'not_found'                  => __( 'Not Found', 'text_domain' ),
				'no_terms'                   => __( 'No items', 'text_domain' ),
				'items_list'                 => __( 'Items list', 'text_domain' ),
				'items_list_navigation'      => __( 'Items list navigation', 'text_domain' ),
			);
			$args = array(
				'labels'                     => $labels,
				'hierarchical'               => true,
				'public'                     => true,
				'show_ui'                    => true,
				'show_admin_column'          => true,
				'show_in_nav_menus'          => true,
				'show_tagcloud'              => false,
			);
			register_taxonomy( 'reverb_make', array( 'product' ), $args );

			if(!get_option('reverb_conditions')){
				//Add in Reverbs conditions
				wp_insert_term('Non Functioning', 'reverb_condition');
				wp_insert_term('Poor', 'reverb_condition');
				wp_insert_term('Fair', 'reverb_condition');
				wp_insert_term('Good', 'reverb_condition');
				wp_insert_term('Very good', 'reverb_condition');
				wp_insert_term('Excellent', 'reverb_condition');
				wp_insert_term('Mint', 'reverb_condition');
				wp_insert_term('Like New', 'reverb_condition');
				wp_insert_term('Brand New', 'reverb_condition');

				update_option( 'reverb_conditions', true );
			}
	}

	

	function acf_load_category_field_choices($field){
		global $woocommerce_reverb_api;
		$reverb_categories = $woocommerce_reverb_api->get_reverb_categories();
		if(!empty($reverb_categories)){
			foreach($reverb_categories as $item){
				$field['choices'][$item['id']] = $item['text'];
			}
		}
		
		return $field;
	}

	function acf_format_value_product($value, $post_id, $field){
		if(is_array( $value )){
			$value = reset($value);
		}

		return $value;
	}

}
