<?php

/**
 * Plugin Name:       WooCommerce Reverb
 * Plugin URI:        http://store.sleepinggiantstudios.com/product/woocommerce-reverb/
 * Description:       List your WooCommerce products on Reverb.com with just the click of a button
 * Version:           2.0.21
 * Author:            Sleeping Giant Studios
 * Author URI:        http://sleepinggiantstudios.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

if ( ! defined( 'WC_REVERB_ALLOW_SANDBOX' ) ) {
	define( 'WC_REVERB_ALLOW_SANDBOX', false );
}

if (  ! function_exists( 'is_woocommerce_active' ) ) {
	require_once( 'woo-includes/woo-functions.php' );
}

function activate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-reverb-activator.php';
	WooCommerce_Reverb_Activator::activate();
}

function deactivate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-reverb-deactivator.php';
	WooCommerce_Reverb_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_plugin_name' );
register_deactivation_hook( __FILE__, 'deactivate_plugin_name' );

require plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-reverb.php';

function run_woocommerce_reverb() {
	global $woocommerce_reverb;
	$woocommerce_reverb = new WooCommerce_Reverb();
	$woocommerce_reverb->run();

}

if ( is_woocommerce_active() ) {

	$active_plugins = (array) get_option( 'active_plugins', array() );

	if(in_array( 'advanced-custom-fields/acf.php', $active_plugins ) || array_key_exists( 'advanced-custom-fields/acf.php', $active_plugins )){
		add_action( 'admin_notices', 'SGS_Plugin_License::acf_activated' );
	}else{
		run_woocommerce_reverb();

		/**
		 * Displays an inactive message if the API License Key has not yet been activated
		 */
		if ( get_option( 'wc_reverb_activated' ) != 'Activated' ) {
		    add_action( 'admin_notices', 'SGS_Plugin_License::inactive_notice' );
		}	
	}
	
}else{
	add_action( 'admin_notices', 'SGS_Plugin_License::wc_deactivated' );
}



class SGS_Plugin_License {

	/**
	 * Self Upgrade Values
	 */
	// Base URL to the remote upgrade API Manager server. If not set then the Author URI is used.
	public $upgrade_url = 'http://store.sleepinggiantstudios.com';

	/**
	 * @var string
	 */
	public $version = '2.0.21';

	/**
	 * @var string
	 * This version is saved after an upgrade to compare this db version to $version
	 */
	public $api_manager_example_version_name = 'plugin_api_manager_example_version';

	/**
	 * @var string
	 */
	public $plugin_url;

	/**
	 * @var string
	 * used to defined localization for translation, but a string literal is preferred
	 *
	 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/issues/59
	 * http://markjaquith.wordpress.com/2011/10/06/translating-wordpress-plugins-and-themes-dont-get-clever/
	 * http://ottopress.com/2012/internationalization-youre-probably-doing-it-wrong/
	 */
	public $text_domain = 'woocommerce-reverb';

	/**
	 * Data defaults
	 * @var mixed
	 */
	private $ame_software_product_id;

	public $ame_data_key;
	public $ame_api_key;
	public $ame_activation_email;
	public $ame_product_id_key;
	public $ame_instance_key;
	public $ame_deactivate_checkbox_key;
	public $ame_activated_key;

	public $ame_deactivate_checkbox;
	public $ame_activation_tab_key;
	public $ame_deactivation_tab_key;
	public $ame_settings_menu_title;
	public $ame_settings_title;
	public $ame_menu_tab_activation_title;
	public $ame_menu_tab_deactivation_title;

	public $ame_options;
	public $ame_plugin_name;
	public $ame_product_id;
	public $ame_renew_license_url;
	public $ame_instance_id;
	public $ame_domain;
	public $ame_software_version;
	public $ame_plugin_or_theme;

	public $ame_update_version;

	/**
	 * Used to send any extra information.
	 * @var mixed array, object, string, etc.
	 */
	public $ame_extra;

    /**
     * @var The single instance of the class
     */
    protected static $_instance = null;

    public static function instance() {
        if ( is_null( self::$_instance ) ) {
        	self::$_instance = new self();
        }

        return self::$_instance;
    }

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.2
	 */
	public function __clone() {}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.2
	 */
	public function __wakeup() {}

	public function __construct() {

		register_activation_hook( __FILE__, array( $this, 'activation' ) );

		// Ready for translation
		//load_plugin_textdomain( $this->text_domain, false, dirname( untrailingslashit( plugin_basename( __FILE__ ) ) ) . '/languages' );

		if ( is_admin() ) {

			// Check for external connection blocking
			add_action( 'admin_notices', array( $this, 'check_external_blocking' ) );

			/**
			 * Software Product ID is the product title string
			 * This value must be unique, and it must match the API tab for the product in WooCommerce
			 */
			$this->ame_software_product_id = 'WooCommerce Reverb';

			/**
			 * Set all data defaults here
			 */
			$this->ame_data_key 				= 'wc_reverb_license';
			$this->ame_api_key 					= 'api_key';
			$this->ame_activation_email 		= 'activation_email';
			$this->ame_product_id_key 			= 'wc_reverb_product_id';
			$this->ame_instance_key 			= 'wc_reverb_instance';
			$this->ame_deactivate_checkbox_key 	= 'wc_reverb_deactivate_checkbox';
			$this->ame_activated_key 			= 'wc_reverb_activated';

			/**
			 * Set all software update data here
			 */
			$this->ame_options 				= get_option( $this->ame_data_key );
			$this->ame_plugin_name 			= untrailingslashit( plugin_basename( __FILE__ ) ); // same as plugin slug. if a theme use a theme name like 'twentyeleven'
			$this->ame_product_id 			= get_option( $this->ame_product_id_key ); // Software Title
			$this->ame_renew_license_url 	= 'http://store.sleepinggiantstudios.com/my-account/'; // URL to renew a license. Trailing slash in the upgrade_url is required.
			$this->ame_instance_id 			= get_option( $this->ame_instance_key ); // Instance ID (unique to each blog activation)
			/**
			 * Some web hosts have security policies that block the : (colon) and // (slashes) in http://,
			 * so only the host portion of the URL can be sent. For example the host portion might be
			 * www.example.com or example.com. http://www.example.com includes the scheme http,
			 * and the host www.example.com.
			 * Sending only the host also eliminates issues when a client site changes from http to https,
			 * but their activation still uses the original scheme.
			 * To send only the host, use a line like the one below:
			 *
			 * $this->ame_domain = str_ireplace( array( 'http://', 'https://' ), '', home_url() ); // blog domain name
			 */
			$this->ame_domain 				= str_ireplace( array( 'http://', 'https://' ), '', home_url() ); // blog domain name
			$this->ame_software_version 	= $this->version; // The software version
			$this->ame_plugin_or_theme 		= 'plugin'; // 'theme' or 'plugin'

			// Performs activations and deactivations of API License Keys
			require_once( plugin_dir_path( __FILE__ ) . 'am/classes/class-wc-key-api.php' );

			// Checks for software updatess
			require_once( plugin_dir_path( __FILE__ ) . 'am/classes/class-wc-plugin-update.php' );

			$options = get_option( $this->ame_data_key );

			/**
			 * Check for software updates
			 */
			if ( ! empty( $options ) && $options !== false ) {

				$this->update_check(
					$this->upgrade_url,
					$this->ame_plugin_name,
					$this->ame_product_id,
					$this->ame_options[$this->ame_api_key],
					$this->ame_options[$this->ame_activation_email],
					$this->ame_renew_license_url,
					$this->ame_instance_id,
					$this->ame_domain,
					$this->ame_software_version,
					$this->ame_plugin_or_theme,
					$this->text_domain
					);

			}

		}


	}

	/** Load Shared Classes as on-demand Instances **********************************************/

	/**
	 * API Key Class.
	 *
	 * @return WC_Reverb_Api_Manager_Key
	 */
	public function key() {
		return WC_Reverb_Api_Manager_Key::instance();
	}

	/**
	 * Check for software updates.
	 *
	 * @param        $upgrade_url
	 * @param        $plugin_name
	 * @param        $product_id
	 * @param        $api_key
	 * @param        $activation_email
	 * @param        $renew_license_url
	 * @param        $instance
	 * @param        $domain
	 * @param        $software_version
	 * @param        $plugin_or_theme
	 * @param        $text_domain
	 * @param string $extra
	 *
	 * @return \WC_Reverb_API_Manager_Update_API_Check
	 */
	public function update_check( $upgrade_url, $plugin_name, $product_id, $api_key, $activation_email, $renew_license_url, $instance, $domain, $software_version, $plugin_or_theme, $text_domain, $extra = '' ) {

		return WC_Reverb_API_Manager_Update_API_Check::instance( $upgrade_url, $plugin_name, $product_id, $api_key, $activation_email, $renew_license_url, $instance, $domain, $software_version, $plugin_or_theme, $text_domain, $extra );
	}

	public function plugin_url() {
		if ( isset( $this->plugin_url ) ) {
			return $this->plugin_url;
		}

		return $this->plugin_url = plugins_url( '/', __FILE__ );
	}

	/**
	 * Generate the default data arrays
	 */
	public function activation() {
		$global_options = array(
			$this->ame_api_key 				=> '',
			$this->ame_activation_email 	=> '',
					);

		update_option( $this->ame_data_key, $global_options );

		$single_options = array(
			$this->ame_product_id_key 			=> $this->ame_software_product_id,
			$this->ame_instance_key 			=> wp_generate_password( 12, false ),
			$this->ame_deactivate_checkbox_key 	=> 'on',
			//$this->ame_activated_key 			=> 'Deactivated',
			//'wc_reverb_license_key' => '',
			//'wc_reverb_license_email' => '',
			//'wc_reverb_deactivate_license' => ''
			);

		foreach ( $single_options as $key => $value ) {
			update_option( $key, $value );
		}

		$curr_ver = get_option( $this->api_manager_example_version_name );

		// checks if the current plugin version is lower than the version being installed
		if ( version_compare( $this->version, $curr_ver, '>' ) ) {
			// update the version
			update_option( $this->api_manager_example_version_name, $this->version );
		}

	}

	/**
	 * Deletes all data if plugin deactivated
	 * @return void
	 */
	public function uninstall() {
		global $blog_id;

		$this->license_key_deactivation();

		// Remove options
		if ( is_multisite() ) {

			switch_to_blog( $blog_id );

			foreach ( array(
					$this->ame_data_key,
					$this->ame_product_id_key,
					$this->ame_instance_key,
					$this->ame_deactivate_checkbox_key,
					$this->ame_activated_key,
					'wc_reverb_license_key',
					'wc_reverb_license_email',
					'wc_reverb_deactivate_license'
					) as $option) {

					delete_option( $option );

					}

			restore_current_blog();

		} else {

			foreach ( array(
					$this->ame_data_key,
					$this->ame_product_id_key,
					$this->ame_instance_key,
					$this->ame_deactivate_checkbox_key,
					$this->ame_activated_key,
					'wc_reverb_license_key',
					'wc_reverb_license_email',
					'wc_reverb_deactivate_license'
					) as $option) {

					delete_option( $option );

					}

		}

	}

	/**
	 * Deactivates the license on the API server
	 * @return void
	 */
	public function license_key_deactivation() {

		$activation_status = get_option( $this->ame_activated_key );

		$api_email = $this->ame_options[$this->ame_activation_email];
		$api_key = $this->ame_options[$this->ame_api_key];

		$args = array(
			'email' => $api_email,
			'licence_key' => $api_key,
			);

		if ( $activation_status == 'Activated' && $api_key != '' && $api_email != '' ) {
			$this->key()->deactivate( $args ); // reset license key activation
		}
	}

    /**
     * Displays an inactive notice when the software is inactive.
     */
	public static function inactive_notice() { ?>
		<?php if ( ! current_user_can( 'manage_options' ) ) return; ?>
		<?php if ( isset( $_GET['page'] ) && 'wc-settings' == $_GET['page'] && isset($_GET['tab']) && $_GET['tab'] == 'reverb' ) return; ?>
		<div id="message" class="error">
			<p><?php printf( __( 'The WooCommerce Reverb License Key has not been activated, so the plugin is inactive! %sClick here%s to activate the license key and the plugin.', 'woocommerce-reverb' ), '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=reverb' ) ) . '">', '</a>' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Check for external blocking contstant
	 * @return string
	 */
	public function check_external_blocking() {
		// show notice if external requests are blocked through the WP_HTTP_BLOCK_EXTERNAL constant
		if( defined( 'WP_HTTP_BLOCK_EXTERNAL' ) && WP_HTTP_BLOCK_EXTERNAL === true ) {

			// check if our API endpoint is in the allowed hosts
			$host = parse_url( $this->upgrade_url, PHP_URL_HOST );

			if( ! defined( 'WP_ACCESSIBLE_HOSTS' ) || stristr( WP_ACCESSIBLE_HOSTS, $host ) === false ) {
				?>
				<div class="error">
					<p><?php printf( __( '<b>Warning!</b> You\'re blocking external requests which means you won\'t be able to get %s updates. Please add %s to %s.', 'api-manager-example' ), $this->ame_software_product_id, '<strong>' . $host . '</strong>', '<code>WP_ACCESSIBLE_HOSTS</code>'); ?></p>
				</div>
				<?php
			}

		}
	}

	public static function wc_deactivated() {
		echo '<div class="error"><p>' . sprintf( __( 'WooCommerce Reverb requires %s to be installed and active.', 'woocommerce-reverb' ), '<a href="http://www.woothemes.com/woocommerce/" target="_blank">WooCommerce</a>' ) . '</p></div>';
	}

	public static function acf_activated() {
		echo '<div class="error"><p>' . sprintf( __( 'It appears Advanced Custom Fields is currently active.  WooCommerce Reverb includes and requires Advanced Custom Fields Pro.  Please <a href="' . add_query_arg('s', 'advanced custom fields', admin_url( 'plugins.php') ) . '">deactivate</a> Advanced Custom Fields for WooCommerce Reverb to run correctly.  If you still need assistance, feel free to contact our <a href="http://store.sleepinggiantstudios.com/support/" target="_blank">support team</a>.', 'woocommerce-reverb' ), '' ) . '</p></div>';
	}

} // End of class

function SPL() {
    return SGS_Plugin_License::instance();
}

// Initialize the class instance only once
SPL();



