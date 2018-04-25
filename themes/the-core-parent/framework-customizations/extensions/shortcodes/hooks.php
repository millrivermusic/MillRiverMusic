<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}


if ( ! function_exists( 'fw_disable_default_shortcodes' ) ) :
	function fw_disable_default_shortcodes( $to_disabled ) {
		$to_disabled[] = 'call_to_action';
		$to_disabled[] = 'notification';

		if ( ! class_exists('RevSlider') ) {
			$to_disabled[] = 'revolution_slider';
		}

		if( ! function_exists('fw_ext_breadcrumbs') ) {
			$to_disabled[] = 'fw_breadcrumbs';
		}

		return $to_disabled;
	}

	add_filter( 'fw_ext_shortcodes_disable_shortcodes', 'fw_disable_default_shortcodes', 10, 2 );
endif;