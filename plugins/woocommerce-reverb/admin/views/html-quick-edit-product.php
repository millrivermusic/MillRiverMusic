<?php
/**
 * Admin View: Quick Edit Product
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>


	<br class="clear" />
	

		<h4><?php _e( 'Reverb', 'woocommerce' ); ?></h4>

		<div>
			<label class="alignleft ">
				<span><?php _e( 'Sync with Reverb ', 'woocommerce' ); ?></span>
				<input type="checkbox" name="_reverb_sync" value="yes">
				
			</label>
			<br class="clear" />
			<label class="reverb_fields ">
				<span><?php _e( 'Sync WooCommerce Title', 'woocommerce' ); ?></span>
				<input type="checkbox" name="_reverb_sync_woo_title" value="yes">
				
			</label>
			<br class="clear" />
			<label class="reverb_fields">
				<span class="title"><?php _e( 'Make', 'woocommerce' ); ?></span>
				<span class="input-text-wrap">
					<input type="text" name="_reverb_make" class="text " value="">
				</span>
			</label>
			<br class="clear" />
			<label class="reverb_fields">
				<span class="title"><?php _e( 'Model', 'woocommerce' ); ?></span>
				<span class="input-text-wrap">
					<input type="text" name="_reverb_model" class="text " value="">
				</span>
			</label>
			<br class="clear" />
			<label class="alignleft reverb_fields">
				<span class="title"><?php _e( 'Condition', 'woocommerce' ); ?></span>
				<span class="input-text-wrap">
					<select name="_reverb_condition">
					<?php
						$conditions = array('' => 'Select Condition', 'Non Functioning' => 'Non Functioning', 'Poor' => 'Poor', 'Fair' => 'Fair', 'Good' => 'Good', 'Very Good' => 'Very Good', 'Excellent' => 'Excellent', 'Mint' => 'Mint', 'Brand New' => 'Brand New');
						foreach ( $conditions as $key => $value ) {
							echo '<option value="' . esc_attr( $key ) .'">'. $value .'</option>';
						}
					?>
					</select>
				</span>
			</label>
			<br class="clear" />
			<label class="reverb_fields">
				<span class="title"><?php _e( 'UPC', 'woocommerce' ); ?></span>
				<span class="input-text-wrap">
					<input type="text" name="_reverb_upc" class="text " value="">
				</span>
			</label>
			<br class="clear" />
			<label class="reverb_fields">
				<span class="title"><?php _e( 'Finish', 'woocommerce' ); ?></span>
				<span class="input-text-wrap">
					<input type="text" name="_reverb_finish" class="text " value="">
				</span>
			</label>
			<br class="clear" />
			<label class="reverb_fields">
				<span class="title"><?php _e( 'Year', 'woocommerce' ); ?></span>
				<span class="input-text-wrap">
					<input type="text" name="_reverb_year" class="text " value="">
				</span>
			</label>
			<br class="clear" />
			<label class="reverb_fields">
				<span class="title"><?php _e( 'Seller Cost', 'woocommerce' ); ?></span>
				<span class="input-text-wrap">
					<input type="text" name="_reverb_seller_cost" class="text " value="">
				</span>
			</label>
			<br class="clear" />
			<label class="reverb_fields ">
				<span><?php _e( 'Accept Offers', 'woocommerce' ); ?></span>
				<input type="checkbox" name="_reverb_accept_offers" value="yes">
				
			</label>

			
		</div>

	

