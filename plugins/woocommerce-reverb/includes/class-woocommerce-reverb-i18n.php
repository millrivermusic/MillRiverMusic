<?php

class WooCommerce_Reverb_i18n {

	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'woocommerce-reverb',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
