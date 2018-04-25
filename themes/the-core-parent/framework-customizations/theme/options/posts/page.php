<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$the_core_admin_url          = admin_url();
$options            = array(
	'page-side' => array(
		'title'    => __( 'Header Image', 'the-core' ),
		'type'     => 'box',
		'context'  => 'side',
		'priority' => 'low',
		'options'  => array(
			'header_image' => array(
				'label' => __( 'Add Image', 'the-core' ),
				'desc'  => __( 'Upload header image', 'the-core' ),
				'help'  => __( 'You can set a general header image for all your pages from the Theme Settings page under the', 'the-core' ) . ' <a target="_blank" href="' . $the_core_admin_url . 'themes.php?page=fw-settings#fw-options-tab-pages">' . __( 'Pages tab', 'the-core' ) . '</a>',
				'type'  => 'upload',
			),
		),
	),
);