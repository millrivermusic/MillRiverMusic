<?php

class WooCommerce_Reverb_Admin_Notices extends WC_Admin_Notices {

	private $plugin_name;

	
	private $core_notices = array(
		'wc-reverb-install'        => 'reverb_install_notice'
	);
	

	function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		
		if ( current_user_can( 'manage_woocommerce' ) ) {
			add_action( 'admin_print_styles', array( $this, 'add_notices' ) );
		}
	}

	public function add_notices() {
		$notices = get_option( 'woocommerce_reverb_admin_notices', array() );

		if ( $notices ) {
			wp_enqueue_style( 'woocommerce-activation', plugins_url(  '/assets/css/activation.css', WC_PLUGIN_FILE ) );
			foreach ( $notices as $notice ) {
				if ( ! empty( $this->core_notices[ $notice ] ) && apply_filters( 'woocommerce_show_admin_notice', true, $notice ) ) {
					add_action( 'admin_notices', array( $this, $this->core_notices[ $notice ] ) );
				}
			}
		}
	}

	function reverb_install_notice(){
		?>
		<div id="message" class="updated woocommerce-message wc-connect">
			<p><?php _e( '<strong>Welcome to WooCommerce Reverb</strong> &#8211; You&lsquo;re almost ready to start listing your WooCommerce products on Reverb.com  :)', 'woocommerce' ); ?></p>
			<p class="submit"><a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=reverb' ) ); ?>" class="button-primary"><?php _e( 'Connect to Reverb.com', 'woocommerce' ); ?></a> <a class="button-secondary skip" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'wc-hide-notice', 'install' ), 'woocommerce_hide_notices_nonce', '_wc_notice_nonce' ) ); ?>"><?php _e( 'Skip Setup', 'woocommerce' ); ?></a></p>
		</div>
		<?php
	}


}
