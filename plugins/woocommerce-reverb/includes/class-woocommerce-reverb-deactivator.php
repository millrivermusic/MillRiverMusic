<?php

class WooCommerce_Reverb_Deactivator {

	public static function deactivate() {
		$activation_status = get_option( SPL()->ame_activated_key );

		$args = array(
			'email' => SPL()->ame_options[SPL()->ame_activation_email],
			'licence_key' => SPL()->ame_options[SPL()->ame_api_key],
			);

		if ( $activation_status == 'Activated' && SPL()->ame_options[SPL()->ame_api_key] != '' && SPL()->ame_options[SPL()->ame_activation_email] != '' ) {

			// deactivates license key activation
			$activate_results = json_decode( SPL()->key()->deactivate( $args ), true );


			if ( $activate_results['deactivated'] === true ) {
				$update = array(
					SPL()->ame_api_key => '',
					SPL()->ame_activation_email => ''
					);

				$merge_options = array_merge( SPL()->ame_options, $update );

				update_option( SPL()->ame_data_key, $merge_options );

				update_option( SPL()->ame_activated_key, 'Deactivated' );

			}

		}
	}

}
