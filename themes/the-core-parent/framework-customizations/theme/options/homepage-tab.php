<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

global $the_core_colors, $the_core_typography;
$the_core_template_directory  = get_template_directory_uri();
$the_core_admin_url           = admin_url();
$the_core_color_settings      = fw_get_db_settings_option('color_settings', $the_core_colors);
$the_core_typography_settings = fw_get_db_settings_option('typography_settings', $the_core_typography);

$options = array(
	'header-homepage-box' => array(
		'title'   => __( 'Homepage Header', 'the-core' ),
		'attr'    => array('class' => 'prevent-auto-close'),
		'type'    => 'box',
		'options' => array(
			'general_homepage_header' => array(
				'type'          => 'multi',
				'label'         => false,
				'attr'          => array(
					'class' => 'fw-option-type-multi-show-borders',
				),
				'inner-options' => array(
					'posts_header_height'          => array(
						'label'   => __( 'Header Height', 'the-core' ),
						'desc'    => __( "Select the header height in pixels (Ex: 300)", "the-core" ),
						'type'    => 'radio-text',
						'value'   => 'fw-section-height-md',
						'choices' => array(
							'auto'                 => __( 'auto', 'the-core' ),
							'fw-section-height-sm' => __( 'small', 'the-core' ),
							'fw-section-height-md' => __( 'medium', 'the-core' ),
							'fw-section-height-lg' => __( 'large', 'the-core' ),
						),
						'custom'  => 'custom_width',
					),
					'posts_header_image'           => array(
						'label' => __( 'Header Image', 'the-core' ),
						'desc'  => __( 'Upload a header image', 'the-core' ),
						'type'  => 'upload'
					),
					'posts_header_overlay_options' => array(
						'type'    => 'multi-picker',
						'label'   => false,
						'desc'    => false,
						'picker'  => array(
							'posts_header_overlay' => array(
								'type'         => 'switch',
								'label'        => __( 'Overlay Color', 'the-core' ),
								'desc'         => __( 'Enable image overlay color?', 'the-core' ),
								'value'        => 'no',
								'right-choice' => array(
									'value' => 'yes',
									'label' => __( 'Yes', 'the-core' ),
								),
								'left-choice'  => array(
									'value' => 'no',
									'label' => __( 'No', 'the-core' ),
								),
							),
						),
						'choices' => array(
							'no'  => array(),
							'yes' => array(
								'posts_header_overlay_color'         => array(
									'label'   => __( '', 'the-core' ),
									'desc'    => __( 'Select the image overlay color', 'the-core' ),
									'help'    => __( 'The default color palette can be changed from the', 'the-core' ) . ' <a target="_blank" href="' . $the_core_admin_url . 'themes.php?page=fw-settings&_focus_tab=fw-options-tab-styling">' . __( 'Styling section', 'the-core' ) . '</a> ' . __( 'found in the Theme Settings page', 'the-core' ),
									'value'   => '',
									'choices' => $the_core_color_settings,
									'type'    => 'color-palette'
								),
								'posts_header_overlay_opacity_image' => array(
									'type'       => 'short-slider',
									'value'      => 80,
									'properties' => array(
										'min' => 0,
										'max' => 100,
										'sep' => 1,
									),
									'label'      => __( '', 'the-core' ),
									'desc'       => __( 'Select the overlay color opacity in %', 'the-core' ),
								)
							),
						),
					),
					'header_image_overlap' => array(
						'label'   => __( 'Header Image Overlap', 'the-core' ),
						'desc'    => __( 'Select the header image overlap value in pixels (Ex: 100)', 'the-core' ),
						'help'    => __( 'The content that follows will overlap the header with the specified pixel amount.', 'the-core' ),
						'type'    => 'radio-text',
						'choices' => array(
							''                      => __( 'none', 'the-core' ),
							'fw-content-overlay-sm' => __( 'small', 'the-core' ),
							'fw-content-overlay-md' => __( 'medium', 'the-core' ),
							'fw-content-overlay-lg' => __( 'large', 'the-core' ),
						),
						'custom'  => 'custom_width',
						'value'   => ''
					),
					'home_title_group' => array(
						'type'    => 'group',
						'options' => array(
							'header_title_typography' => array(
								'attr' => array(
									'data-advanced-for' => 'home_header_title_advanced_styling',
									'class' => 'fw-advanced-button'
								),
								'type' => 'popup',
								'label' => __('Custom Style', 'the-core'),
								'button' => __('Styling', 'the-core'),
								'size' => 'small',
								'popup-options' => array(
									'title' => array(
										'label' => __('Title', 'the-core'),
										'type' => 'tf-typography',
										'value' => array(
											'google_font' => $the_core_typography_settings['h1']['google_font'],
											'subset' => $the_core_typography_settings['h1']['subset'],
											'variation' => $the_core_typography_settings['h1']['variation'],
											'family' => $the_core_typography_settings['h1']['family'],
											'style' => $the_core_typography_settings['h1']['style'],
											'weight' => $the_core_typography_settings['h1']['weight'],
											'size' => $the_core_typography_settings['h1']['size'],
											'line-height' => $the_core_typography_settings['h1']['line-height'],
											'letter-spacing' => $the_core_typography_settings['h1']['letter-spacing'],
											'color-palette' => '',
										)
									),
								),
							),
							'custom_title_text' => array(
								'attr' => array('class' => 'home_header_title_advanced_styling'),
								'label' => __('Title', 'the-core'),
								'desc' => __('Enter a title', 'the-core'),
								'type' => 'text',
							),
							'header_subtitle_typography' => array(
								'attr'          => array(
									'data-advanced-for' => 'homepage_header_subtitle_advanced_styling',
									'class'             => 'fw-advanced-button'
								),
								'type'          => 'popup',
								'label'         => __( 'Custom Style', 'the-core' ),
								'button'        => __( 'Styling', 'the-core' ),
								'size'          => 'small',
								'popup-options' => array(
									'subtitle' => array(
										'label' => __( 'Description', 'the-core' ),
										'type'  => 'tf-typography',
										'value' => array(
											'google_font'    => $the_core_typography_settings['subtitles']['google_font'],
											'subset'         => $the_core_typography_settings['subtitles']['subset'],
											'variation'      => $the_core_typography_settings['subtitles']['variation'],
											'family'         => $the_core_typography_settings['subtitles']['family'],
											'style'          => $the_core_typography_settings['subtitles']['style'],
											'weight'         => $the_core_typography_settings['subtitles']['weight'],
											'size'           => $the_core_typography_settings['subtitles']['size'],
											'line-height'    => $the_core_typography_settings['subtitles']['line-height'],
											'letter-spacing' => $the_core_typography_settings['subtitles']['letter-spacing'],
											'color-palette'  => '',
										)
									),
								),
							),
							'custom_subtitle_text'         => array(
								'attr'  => array( 'class' => 'homepage_header_subtitle_advanced_styling' ),
								'label' => __( 'Description', 'the-core' ),
								'desc'  => __( 'Enter a description', 'the-core' ),
								'type'  => 'text',
							),
							'post_title_alignment' => array(
								'label' => __('Alignment', 'the-core'),
								'desc' => __('Choose the title and description alignment', 'the-core'),
								'type' => 'image-picker',
								'value' => 'fw-heading-center',
								'choices' => array(
									'fw-heading-left' => array(
										'small' => array(
											'height' => 50,
											'src' => $the_core_template_directory . '/images/image-picker/left-position.jpg',
											'title' => __('Left', 'the-core')
										),
									),
									'fw-heading-center' => array(
										'small' => array(
											'height' => 50,
											'src' => $the_core_template_directory . '/images/image-picker/center-position.jpg',
											'title' => __('Center', 'the-core')
										),
									),
									'fw-heading-right' => array(
										'small' => array(
											'height' => 50,
											'src' => $the_core_template_directory . '/images/image-picker/right-position.jpg',
											'title' => __('Right', 'the-core')
										),
									),
								),
							),
						)
					),
					'posts_header_content_position' => array(
						'label'   => __( 'Content Position', 'the-core' ),
						'desc'    => __( "Adjust the content vertical position in pixels (Ex: -20 or 20)", "the-core" ),
						'help'    => __( "Let's you fine tune the header content position on the vertical axis. Input a negative value if you want your content to go up or a positive value if you want your content to go down.", "the-core" ),
						'type'    => 'short-text',
						'value'   => '',
					),
					'default_home_page_info' => array(
						'label' => false,
						'desc' => '<i class="fw-info-symbol dashicons dashicons-info"></i>' . esc_html__('These options apply only when you choose to display your latest posts on your homepage (from Settings > Reading).', 'the-core'),
						'type' => 'html',
						'html' => '',
					),
				)
			)
		)
	),
);