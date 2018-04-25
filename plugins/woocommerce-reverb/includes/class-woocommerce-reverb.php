<?php

class WooCommerce_Reverb {

	protected $loader;

	protected $plugin_name;

	protected $version;

	public function __construct() {
		global $woocommerce_reverb_api;
		$this->plugin_name = 'woocommerce-reverb';
		$this->version = '2.0.21';

		$this->define_constants();
		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

		//ACF
            
        add_filter('acf/settings/path', array($this,'my_acf_settings_path'));
        add_filter('acf/settings/dir', array($this,'my_acf_settings_dir'));
        //add_filter('acf/settings/show_admin', '__return_false');
        
        
	}

	private function define_constants(){
		$this->define( 'REVERB_URL', 'https://reverb.com/' );
		$this->define( 'REVERB_CLIENT_ID', '351b4452da0b31cf5e5baf6718de6ce4e761ebbc19b1cfbeed2d84d9129fd5b4' );
		$this->define( 'REVERB_REDIRECT_URI', 'https://store.sleepinggiantstudios.com/wp-json/wc_reverb_info/v1/oauth_success' );
		$this->define( 'REVERB_SANDBOX_URL', 'https://sandbox.reverb.com/' );
		$this->define( 'REVERB_SANDBOX_CLIENT_ID', '3729e04ee7b9bb702d7e41ffb076d834fcef5fd69565b25fefacd00702d194f7' );
		$this->define( 'REVERB_SANDBOX_REDIRECT_URI', 'https://store.sleepinggiantstudios.com/wp-json/wc_reverb_info/v1/sandbox_oauth_success');
		$this->define( 'WC_REVERB_PLUGIN_BASENAME', 'woocommerce-reverb/woocommerce-reverb.php' );
		
	}

	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	private function load_dependencies() {
		global $woocommerce_reverb_api;

		include_once( plugin_dir_path( __FILE__ ) . 'acf/acf.php' );
    	include_once( plugin_dir_path( __FILE__ ) . 'acf-field-groups.php' );

    	require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-reverb-api.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocommerce-reverb-loader.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocommerce-reverb-i18n.php';
		
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-woocommerce-reverb-debug-actions.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-woocommerce-reverb-admin.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-woocommerce-reverb-admin-actions.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-woocommerce-reverb-admin-ui.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/includes/reverb_listings_list_table.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/includes/reverb_ptm_list_table.php';
		
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-woocommerce-reverb-public.php';

		$this->loader = new WooCommerce_Reverb_Loader();
		$woocommerce_reverb_api = new WooCommerce_Reverb_API();

	}

	private function set_locale() {

		$plugin_i18n = new WooCommerce_Reverb_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	private function define_admin_hooks() {
		global $woocommerce_reverb_api;
		$admin = new WooCommerce_Reverb_Admin( $this->get_plugin_name(), $this->get_version() );
		$admin_actions = new WooCommerce_Reverb_Admin_Actions( $this->get_plugin_name(), $this->get_version() );
		$admin_ui = new WooCommerce_Reverb_Admin_UI( $this->get_plugin_name(), $this->get_version() );

		//Basic Admin Stuff
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_scripts' );
		$this->loader->add_action('init', $admin, 'custom_post_types');
		
		$this->loader->add_filter('acf/load_field/name=reverb_category', $admin, 'acf_load_category_field_choices');
		$this->loader->add_filter('acf/format_value/name=product', $admin, 'acf_format_value_product', 10, 3);
		$this->loader->add_filter('admin_notices', $admin, 'remove_sync_notices', 100);
		

		//Actions
		$this->loader->add_filter('woocommerce_api_check_authentication', $admin_actions, 'wc_authenticate_alter', 1);
		$this->loader->add_action('wp_ajax_update_reverb_category', $admin_actions, 'update_reverb_category');
		$this->loader->add_action('wp_ajax_update_term_sp', $admin_actions, 'update_term_sp');
		$this->loader->add_action('wp_ajax_reverb_listing_updated', $admin_actions, 'reverb_listing_updated');
		$this->loader->add_action('wp_ajax_nopriv_reverb_listing_updated', $admin_actions, 'reverb_listing_updated');
		$this->loader->add_action('wp_ajax_save_reverb_token', $admin_actions, 'save_reverb_token');
		$this->loader->add_action('wp_ajax_reset_reverb_api_access', $admin_actions, 'reset_reverb_api_access');
		$this->loader->add_action('wp_ajax_reset_reverb_data', $admin_actions, 'reset_reverb_data');
		$this->loader->add_action('wp_ajax_view_reverb_registered_webhooks', $admin_actions, 'view_reverb_registered_webhooks');
		$this->loader->add_action('wp_ajax_delete_reverb_registered_webhooks', $admin_actions, 'delete_reverb_registered_webhooks');
		
		$this->loader->add_action('save_post', $admin_actions, 'bulk_and_quick_edit_save_post', 100, 2);
		$this->loader->add_action('woocommerce_process_product_meta', $admin_actions, 'reverb_product_sync', 100 , 2);
		$this->loader->add_action('woocommerce_product_set_stock', $admin_actions, 'woocommerce_product_set_stock');
		$this->loader->add_action('woocommerce_process_product_meta', $admin_actions, 'process_product_data', 10, 2);
		$this->loader->add_action('restrict_manage_posts', $admin_actions, 'restrict_manage_posts');
		if($woocommerce_reverb_api->api_key){
			$this->loader->add_filter('request', $admin_actions, 'request_query');
			$this->loader->add_action('load-edit.php', $admin_actions, 'custom_bulk_action');
		}
		
		//Settings
		$this->loader->add_filter('woocommerce_get_settings_pages', $admin, 'woocommerce_get_settings_pages');

		//UI
		$this->loader->add_filter('plugin_action_links_' . WC_REVERB_PLUGIN_BASENAME, $admin_ui, 'plugin_action_links');
		if($woocommerce_reverb_api->api_key){		
			$this->loader->add_filter('woocommerce_product_filters', $admin_ui, 'woocommerce_product_filters');
			$this->loader->add_filter('manage_product_posts_columns', $admin_ui, 'product_columns');
			$this->loader->add_action('manage_product_posts_custom_column', $admin_ui, 'product_columns_content', 10, 2);

			$this->loader->add_filter('woocommerce_product_data_tabs', $admin_ui, 'product_data_tab');
			$this->loader->add_action('woocommerce_product_data_panels', $admin_ui, 'product_data_panel');

			//Bulk/Quick Edit
			$this->loader->add_action('bulk_edit_custom_box', $admin_ui, 'bulk_edit', 10, 2);
			$this->loader->add_action('woocommerce_product_quick_edit_end', $admin_ui, 'quick_edit');
		
		}

		

		
	}

	private function define_public_hooks() {

		$plugin_public = new WooCommerce_Reverb_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	public function run() {
		$this->loader->run();
	}

	public function get_plugin_name() {
		return $this->plugin_name;
	}

	public function get_loader() {
		return $this->loader;
	}

	public function get_version() {
		return $this->version;
	}

	function my_acf_settings_path( $path ) {
         
            // update path
            $path = plugin_dir_path( __FILE__ ) . '/acf/';
            
            // return
            return $path;
        
    }
         
    function my_acf_settings_dir( $dir ) {
     
        // update path
        $dir = plugin_dir_url( __FILE__ ) . '/acf/';
        
        // return
        return $dir;
        
    }

}
