<?php
/**
 * WooCommerce Product Settings
 *
 * @author      WooThemes
 * @category    Admin
 * @package     WooCommerce/Admin
 * @version     2.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_Settings_Reverb' ) ) :

class WC_Settings_Reverb extends WC_Settings_Page {

	public function __construct() {
		global $woocommerce_reverb_api;
		
		$this->id    = 'reverb';
		$this->label = __( 'Reverb', 'woocommerce' );

		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_page' ), 20 );
		add_action( 'woocommerce_settings_' . $this->id, array( $this, 'output' ) );
		add_action( 'woocommerce_settings_save_' . $this->id, array( $this, 'save' ) );
		add_action( 'woocommerce_settings_save_' . $this->id, array($woocommerce_reverb_api, 'register_web_hooks'));
		add_action( 'woocommerce_sections_' . $this->id, array( $this, 'output_sections' ) );
		add_action( 'woocommerce_settings_form_method_tab_' . $this->id, array( $this, 'form_method' ) );

		add_action('woocommerce_admin_field_label', array($this, 'label_field'));
	}

	function label_field($value){
		
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
			</th>
			<td class="forminp forminp-<?php echo sanitize_title( $value['type'] ) ?>">
				<p><?php echo $value['value']; ?></p>
			</td>
		</tr>
		<?php
	}

	public function get_sections() {
		global $woocommerce_reverb_api, $license_status_check;
		if(!$license_status_check){
			$license_status = $this->license_key_status();
			$license_status_check = ( ! empty( $license_status['status_check'] ) && $license_status['status_check'] == 'active' ) ? 'Activated' : 'Deactivated';	
		}
			
		
		
		update_option( SPL()->ame_activated_key, $license_status_check );

		$sections = array('' 		=> __( 'Global Settings', 'woocommerce-reverb' ),);
		if($license_status_check == 'Activated'){
			if(!$woocommerce_reverb_api->api_key){
				$sections = array(
					'' 		=> __( 'Global Settings', 'woocommerce-reverb' ),
					'plugin_license_deactivate' 		=> __( 'Deactivate Plugin License', 'woocommerce-reverb' ),
				);
			}else{
				$sections = array(
					''          		=> __( 'Global Settings', 'woocommerce-reverb' ),
					'field_sync'          		=> __( 'Field Sync Settings', 'woocommerce-reverb' ),
					'sync_status'       => __( 'Sync Status', 'woocommerce-reverb' ),
					'ptm' 		=> __( 'Product Type Mappings', 'woocommerce-reverb' ),
					'plugin_license_deactivate' 		=> __( 'Deactivate Plugin License', 'woocommerce-reverb' ),
				);	
			}
			
		}
		

		return apply_filters( 'woocommerce_get_sections_' . $this->id, $sections );
	}

	public function save() {
		global $current_section, $license_status_check;

		switch($current_section){
			case '':
				if(!$license_status_check){
					$license_status = $this->license_key_status();
					$license_status_check = ( ! empty( $license_status['status_check'] ) && $license_status['status_check'] == 'active' ) ? 'Activated' : 'Deactivated';	
				}
				if($license_status_check == 'Deactivated'){
					$settings = $this->get_settings( $current_section );
					$input = array('api_key' => $_POST['wc_reverb_license_key'], 'activation_email' => $_POST['wc_reverb_license_email']);
					$options = $this->validate_options($input);
					$update = array(
						SPL()->ame_api_key => $options['api_key'],
						SPL()->ame_activation_email => $options['activation_email']
						);

					$merge_options = array_merge( SPL()->ame_options, $update );
					update_option( SPL()->ame_data_key, $merge_options );
					update_option('wc_reverb_license_key', $options['api_key']);
					update_option('wc_reverb_license_email', $options['activation_email']);
					update_option('wc_reverb_deactivate_license', false);
					$license_status_check = get_option( SPL()->ame_activated_key );
				}else{
					$settings = $this->get_settings( $current_section );
					WC_Admin_Settings::save_fields( $settings );
					WooCommerce_Reverb_Debug_Actions::update_shop_information();
				}
				
				break;
			case 'plugin_license_deactivate':
				
				if(isset($_POST['wc_reverb_deactivate_license'])){
					$this->license_key_deactivation('on');
					update_option('wc_reverb_license_key', $options['api_key']);
					update_option('wc_reverb_license_email', $options['activation_email']);
				}
				
				$settings = $this->get_settings( $current_section );
				WC_Admin_Settings::save_fields( $settings );
				if(isset($_POST['wc_reverb_deactivate_license'])){
					$license_status_check = get_option( SPL()->ame_activated_key );
				}
				break;
			case 'field_sync':
				$settings = $this->get_settings( $current_section );
				WC_Admin_Settings::save_fields( $settings );
				WooCommerce_Reverb_Debug_Actions::update_shop_information();
				break;
			default:
				$settings = $this->get_settings( $current_section );
				WC_Admin_Settings::save_fields( $settings );
				break;
		}
	}

	public function output() {
		global $woocommerce_reverb_api, $license_status_check, $current_section;

		switch($current_section){
			case 'sync_status':
				$GLOBALS['hide_save_button'] = true;

				echo '<h3>Sync Status</h3>';
				$lt = new Reverb_Listings_List_Table();
				$lt->prepare_items();
		        echo '<form method="GET">';
		        echo '<input type="hidden" name="page" value="wc-settings" />';
		        echo '<input type="hidden" name="tab" value="reverb" />';
		        echo '<input type="hidden" name="section" value="sync_status" />';
		        $lt->search_box( __( 'Search Reverb Listings' ), 'reverb_listings' ); 
		        $lt->display();
		        echo '</form>';
				break;
			case 'ptm':
				$GLOBALS['hide_save_button'] = true;

				echo '<h3>Product Type Mapping</h3>';
				$lt = new Reverb_PTM_List_Table();
		        $lt->prepare_items();

		        echo '<form method="GET">';
		        echo '<input type="hidden" name="page" value="wc-settings" />';
		        echo '<input type="hidden" name="tab" value="reverb" />';
		        echo '<input type="hidden" name="section" value="ptm" />';
	        	$lt->search_box( __( 'Search' ), 'ptm' );
		        $lt->display();
		        echo '</form>';
				break;
			case '':
				if($license_status_check == 'Deactivated'){
					$settings = $this->get_settings( $current_section );
					WC_Admin_Settings::output_fields( $settings );	
				}else{
					if(!$woocommerce_reverb_api->api_key){
						$GLOBALS['hide_save_button'] = true;
						echo '<h2>Reverb Account Information</h2>';
						echo '<a class="button-primary" href="' . REVERB_URL . 'oauth/authorize?client_id=' . REVERB_CLIENT_ID . '&redirect_uri=' . REVERB_REDIRECT_URI . '&response_type=code&scope=public+read_listings+write_listings+read_profile+write_profile+read_orders+write_orders&state=' . add_query_arg(array('action' => 'save_reverb_token'), admin_url( 'admin-ajax.php' )) . '">Login with Reverb.com account</a>';
						if(WC_REVERB_ALLOW_SANDBOX){
							echo '<a class="button-primary sandbox" href="' . REVERB_SANDBOX_URL . 'oauth/authorize?client_id=' . REVERB_SANDBOX_CLIENT_ID . '&redirect_uri=' . REVERB_SANDBOX_REDIRECT_URI . '&response_type=code&scope=public+read_listings+write_listings+read_profile+write_profile+read_orders+write_orders&state=' . add_query_arg(array('action' => 'save_reverb_token'), admin_url( 'admin-ajax.php' )) . '">Login with Sandbox Reverb.com account</a>';	
						}
					}
	    				
					$settings = $this->get_settings( $current_section );
					WC_Admin_Settings::output_fields( $settings );	
					
				}
				
				break;
			default:
				$settings = $this->get_settings( $current_section );
				WC_Admin_Settings::output_fields( $settings );
				break;
		}

	}

	public function get_settings( $current_section = '' ) {
		global $woocommerce_reverb_api, $license_status_check;
		switch($current_section){
			case '':
				
				if(!$license_status_check){
					$license_status = $this->license_key_status();
					$license_status_check = ( ! empty( $license_status['status_check'] ) && $license_status['status_check'] == 'active' ) ? 'Activated' : 'Deactivated';	
				}

				$global_settings = $license_settings = array();
				


				$pl_description = "<span class='dashicons dashicons-no' style='color: #ca336c;'></span>";
				if($license_status_check == 'Activated'){
					$pl_description = "<span class='dashicons dashicons-yes' style='color: #66ab03;'></span>";
				}

				$license_settings = array(
					'pl_title' => array(
							'name'     => __( 'Plugin License', 'woocommerce-reverb' ),
				            'desc' => '',
				            'type'     => 'title',
						),
					'pl_status' => array(
							'name' => 'License Key Status',
							'type' => 'label',
							'value' => $license_status_check
						),
					'pl_license_key' => array(
			            'name' => __( 'License Key', 'woocommerce-reverb' ),
			            'type' => 'text',
			            'css' => 'width:50%;',
			            'desc' => $pl_description,
			            'id'   => 'wc_reverb_license_key',
			            
			        ),
			        'pl_license_email' => array(
			            'name' => __( 'License Email', 'woocommerce-reverb' ),
			            'type' => 'text',
			            'css' => 'width:50%;',
			            'desc' => $pl_description,
			            'id'   => 'wc_reverb_license_email',
			            
			        ),
					'pl_end' => array(
			             'type' => 'sectionend',
			        	)
					);	

				if($license_status_check == 'Activated'){
					$license_settings['pl_license_key']['custom_attributes'] = array('readonly' => 'readonly');
					$license_settings['pl_license_email']['custom_attributes'] = array('readonly' => 'readonly');
				}

				if($license_status_check == 'Activated' && $woocommerce_reverb_api->api_key){
					
					$shop_info = $woocommerce_reverb_api->get_reverb_account_info();

					$shipping_profile_options = array('' => 'No shipping profiles available');
			    	$available_profiles = $woocommerce_reverb_api->get_reverb_shipping_profiles();
			    	if(!empty($available_profiles)){
			    		$shipping_profile_options[''] = __( 'Please select default shipping profile', 'woocommerce' );
			    		foreach($available_profiles as $profile){
			    			$shipping_profile_options[$profile->id] = $profile->name;
			    		}
			    	}

			    	$product_taxonomies = get_object_taxonomies('product', 'objects');
			    	$product_type_map = array();
			    	if(!empty($product_taxonomies)){
			    		foreach($product_taxonomies as $tax){
			    			//Just include product_cat and anything other than shipping classes or product attribute stuff
			    			if($tax->hierarchical && strpos($tax->name, 'pa_') === false && strpos($tax->name, 'shipping_class') === false){
			    				$product_type_map[$tax->name] = $tax->label;
			    			}
			    		}
			    	}

			    	$api_key_description = $global_title_description = '';
			    	if(isset($_REQUEST['reverb_debugging']) && $_REQUEST['reverb_debugging']){
			    		$global_title_description = '<a class="button-primary" href="' . add_query_arg(array('action' => 'reset_reverb_data'), admin_url('admin-ajax.php')) . '">Reset Reverb Data</a>';
			    		$api_key_description = '<a class="button-primary" href="' . add_query_arg(array('action' => 'reset_reverb_api_access'), admin_url('admin-ajax.php')) . '">Log out of your Reverb.com account</a>';
			    	}
			    	
				    $global_settings = array(
				    	'reverb_title' => array(
				            'name'     => __( 'Reverb Shop Information', 'woocommerce-reverb' ),
				            'type'     => 'title',
				            'desc'     => '',
				            'id' => ''
				        ),
				    	'reverb_shop_name' => array(
				        	'name' => __( 'Shop Name'),
				        	'type' => 'label',
				        	'value' => $shop_info->name
				        	),
				    	'reverb_shop_description' => array(
				        	'name' => __( 'Shop Description'),
				        	'type' => 'label',
				        	'value' => $shop_info->description
				        	),
				    	'reverb_end' => array(
				             'type' => 'sectionend',
				        ),
				        'global_title' => array(
				            'name'     => __( 'Global Settings', 'woocommerce-reverb' ),
				            'type'     => 'title',
				            'desc'     => $global_title_description . ' ' . $api_key_description,
				            'id' => ''
				        ),
				        'create_new_listing' => array(
				        	'name' => __('Create new listings'),
				        	'desc' => 'If this setting is off, only updates will be synced.  New listings will not be automatically created.',
				        	'class' => 'switch',
				        	'custom_attributes' => array('class' => 'switch'),
				        	'type' => 'checkbox',
				        	'id'  => 'wc_reverb_create_new_listing',
				        	'desc_tip' => true
				        	),
				        'auto_publish' => array(
				        	'name' => __('Automatically publish'),
				        	'desc' => 'To publish the listing right away requires more fields such as images and shipping rates, and my not always be possible.',
				        	'class' => 'switch',
				        	'custom_attributes' => array('class' => 'switch'),
				        	'type' => 'checkbox',
				        	'id'  => 'wc_reverb_auto_publish',
				        	'desc_tip' => true
				        	),
				        'default_shipping_profile' => array(
							'title'    => __( 'Default Shipping Profile', 'woocommerce' ),
							'desc'     => 'You can <a href="' . $woocommerce_reverb_api->reverb_url . 'my/selling/shipping_rates" target="_blank">set up shipping profiles here</a>.',
							'id'       => 'wc_reverb_default_shipping_profile',
							'default'  => '',
							'type'     => 'select',
							'class'    => 'wc-enhanced-select',
							'desc_tip' => false,
							'options'  => $shipping_profile_options
						),
						'default_product_type_mappings' => array(
							'title'    => __( 'Default Product Type Mapping', 'woocommerce' ),
							'desc'     => '',
							'id'       => 'wc_reverb_default_product_type_mappings',
							'default'  => 'product_cat',
							'type'     => 'select',
							'class'    => 'wc-enhanced-select',
							'desc_tip' => false,
							'options'  => $product_type_map
						),
				        'global_end' => array(
				             'type' => 'sectionend',
				        ),
				        
				    );
				}

				


			    $settings = array_merge($global_settings, $license_settings);

				break;			
			case 'field_sync':
				$settings = array(
					'field_sync_title' => array(
			            'name'     => __( 'Field Sync Settings', 'woocommerce-reverb' ),
			            'desc' => 'On first time listing create, we will always sync all fields.  These settings control whether the fields sync on subsquent updates, allowing you to change your Reverb listing independently of changes to the WooCommerce product.',
			            'type'     => 'title',
			        ),
			        'sync_desc' => array(
			        	'name' => __('Description'),
			        	'desc' => 'You may want to turn this off if you have emails/phone numbers in your descriptions, which are not allowed on Reverb',
			        	'class' => 'switch',
			        	'custom_attributes' => array('class' => 'switch'),
			        	'type' => 'checkbox',
			        	'id'  => 'wc_reverb_sync_desc',
			        	'desc_tip' => true
			        	),
			        'sync_photos' => array(
			        	'name' => __('Photos'),
			        	'desc' => 'On first sync time to Reverb, photos will be ignored if Reverb already has photos.  On subsequent syncs, only new photos will be copied over.  Reordering will not be synced.',
			        	'class' => 'switch',
			        	'custom_attributes' => array('class' => 'switch'),
			        	'type' => 'checkbox',
			        	'id'  => 'wc_reverb_sync_photos',
			        	'desc_tip' => true
			        	),
			        'sync_condition' => array(
			        	'name' => __('Condition'),
			        	'desc' => 'On first time listing create, we will always sync the condition.  This setting controls whether we sync the field on updates.  If the condtion is not specified, we will use Brand New for inventory items and Mint for non-inventory items.',
			        	'class' => 'switch',
			        	'custom_attributes' => array('class' => 'switch'),
			        	'type' => 'checkbox',
			        	'id'  => 'wc_reverb_sync_condition',
			        	'desc_tip' => true
			        	),
		        	'sync_price' => array(
			        	'name' => __('Price'),
			        	'desc' => 'On first time listing create, we will always sync price.  If you set special prices on Reverb, turn off this setting to avoid updating the price.',
			        	'class' => 'switch',
			        	'custom_attributes' => array('class' => 'switch'),
			        	'type' => 'checkbox',
			        	'id'  => 'wc_reverb_sync_price',
			        	'desc_tip' => true
			        	),
			        'field_sync_end' => array(
			             'type' => 'sectionend',
			        )
				);
				break;


			case 'plugin_license_deactivate':
				if(!$license_status_check){
					$license_status = $this->license_key_status();
					$license_status_check = ( ! empty( $license_status['status_check'] ) && $license_status['status_check'] == 'active' ) ? 'Activated' : 'Deactivated';	
				}
				
				if($license_status_check == 'Deactivated'){
					$GLOBALS['hide_save_button'] = true;
					$settings = array(
						'pld_title' => array(
								'name'     => __( 'Plugin License Deactivated', 'woocommerce-reverb' ),
					            'desc' => '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=reverb' ) . '">Click here to reactivate your plugin license.</a>',
					            'type'     => 'title',
							),
						
						'pld_end' => array(
				             'type' => 'sectionend',
				        	)
						);	
				}else{
					$settings = array(
						'pld_title' => array(
								'name'     => __( 'Deactivate Plugin License', 'woocommerce-reverb' ),
					            'desc' => '',
					            'type'     => 'title',
							),
						'sync_desc' => array(
				        	'name' => __('Deactivate License Key'),
				        	'desc' => 'Deactivates License Key so it can be used on another blog.',
				        	'type' => 'checkbox',
				        	'id'  => 'wc_reverb_deactivate_license',
				        	'desc_tip' => false
				        	),
						'pld_end' => array(
				             'type' => 'sectionend',
				        	)
						);	
				}
				
				break;

		}

		return apply_filters( 'woocommerce_get_settings_' . $this->id, $settings, $current_section );
	}

	public function validate_options( $input ) {

		// Load existing options, validate, and update with changes from input before returning
		$options = SPL()->ame_options;

		$options[SPL()->ame_api_key] = trim( $input[SPL()->ame_api_key] );
		$options[SPL()->ame_activation_email] = trim( $input[SPL()->ame_activation_email] );

		/**
		  * Plugin Activation
		  */
		$api_email = trim( $input[SPL()->ame_activation_email] );
		$api_key = trim( $input[SPL()->ame_api_key] );

		$activation_status = get_option( SPL()->ame_activated_key );
		$checkbox_status = get_option( SPL()->ame_deactivate_checkbox );

		$current_api_key = SPL()->ame_options[SPL()->ame_api_key];

		if ( $activation_status == 'Deactivated' || $activation_status == '' || $api_key == '' || $api_email == '' || $checkbox_status == 'on' || $current_api_key != $api_key  ) {
			/**
			 * If this is a new key, and an existing key already exists in the database,
			 * deactivate the existing key before activating the new key.
			 */
			if ( $current_api_key != $api_key )
				$this->replace_license_key( $current_api_key );

			$args = array(
				'email' => $api_email,
				'licence_key' => $api_key,
				);


			$activate_results = json_decode( SPL()->key()->activate( $args ), true );

			if ( $activate_results['activated'] === true ) {
				WC_Admin_Settings::add_message(__( 'Plugin activated. ', SPL()->text_domain ) . "{$activate_results['message']}.");
				update_option( SPL()->ame_activated_key, 'Activated' );
				update_option( SPL()->ame_deactivate_checkbox, 'off' );
			}

			if ( $activate_results == false ) {
				add_settings_error( 'api_key_check_text', 'api_key_check_error', __( 'Connection failed to the License Key API server. Try again later.', SPL()->text_domain ), 'error' );
				WC_Admin_Settings::add_error(__( 'Connection failed to the License Key API server. Try again later.', 'woocommerce-reverb' ));
				$options[SPL()->ame_api_key] = '';
				$options[SPL()->ame_activation_email] = '';
				update_option( SPL()->ame_options[SPL()->ame_activated_key], 'Deactivated' );
			}

			if ( isset( $activate_results['code'] ) ) {
				WC_Admin_Settings::add_error("{$activate_results['error']}. {$activate_results['additional info']}");
				$options[SPL()->ame_api_key] = '';
				$options[SPL()->ame_activation_email] = '';
				update_option( SPL()->ame_options[SPL()->ame_activated_key], 'Deactivated' );

		} // End Plugin Activation

		}

		return $options;
	}

	public function license_key_deactivation( $input ) {
		$activation_status = get_option( SPL()->ame_activated_key );

		$args = array(
			'email' => SPL()->ame_options[SPL()->ame_activation_email],
			'licence_key' => SPL()->ame_options[SPL()->ame_api_key],
			);

		$options = ( $input == 'on' ? 'on' : 'off' );

		if ( $options == 'on' && $activation_status == 'Activated' && SPL()->ame_options[SPL()->ame_api_key] != '' && SPL()->ame_options[SPL()->ame_activation_email] != '' ) {

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

				WC_Admin_Settings::add_message(__( 'Plugin license deactivated. ', SPL()->text_domain ) . "{$activate_results['activations_remaining']}.");

				return $options;
			}

			if ( isset( $activate_results['code'] ) ) {

					WC_Admin_Settings::add_error("{$activate_results['error']}. {$activate_results['additional info']}");
					$options[SPL()->ame_api_key] = '';
					$options[SPL()->ame_activation_email] = '';
					update_option( SPL()->ame_options[SPL()->ame_activated_key], 'Deactivated' );

			}

		} else {

			return $options;
		}

	}

	public function replace_license_key( $current_api_key ) {

		$args = array(
			'email' => SPL()->ame_options[SPL()->ame_activation_email],
			'licence_key' => $current_api_key,
			);

		$reset = SPL()->key()->deactivate( $args ); // reset license key activation

		if ( $reset == true )
			return true;

		return add_settings_error( 'not_deactivated_text', 'not_deactivated_error', __( 'The license could not be deactivated. Use the License Deactivation tab to manually deactivate the license before activating a new license.', SPL()->text_domain ), 'updated' );
	}

	public function license_key_status() {
		$activation_status = get_option( SPL()->ame_activated_key );

		$args = array(
			'email' => SPL()->ame_options[SPL()->ame_activation_email],
			'licence_key' => SPL()->ame_options[SPL()->ame_api_key],
			);

		return json_decode( SPL()->key()->status( $args ), true );
	}

	public function form_method( $method ) {
		global $current_section;

		if ( 'sync_status' == $current_section || 'ptm' == $current_section) {
			return 'get';
		}

		return 'post';
	}
}

endif;

return new WC_Settings_Reverb();
