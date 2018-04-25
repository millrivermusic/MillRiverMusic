<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

if ( ! fw_ext( 'portfolio' ) ) {
	// if portfolio extensions is disabled return
	return;
}

$cfg = array(
	'page_builder' => array(
		'title'       => __( 'Porfolio', 'the-core' ),
		'description' => __( 'Add a Portfolio', 'the-core' ),
		'tab'         => __( 'Content Elements', 'the-core' ),
		'popup_size'  => 'medium',
		'title_template' => '{{ if (!o.shortcode_name) { }} {{- title}} {{ } }} {{ if (o.shortcode_name) { }} {{- o.shortcode_name}} {{ } }}',
		'popup_header_elements' => '<a class="fw-docs-link" target="_blank" href="http://docs.themefuse.com/the-core/your-theme/shortcodes/portfolio-1"><span class="fw-docs-info dashicons dashicons-editor-help"></span>'.__('Go to Docs', 'the-core').'</a>',
	)
);