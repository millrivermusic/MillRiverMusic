<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array(
	'page_builder' => array(
		'title'       => __( 'RAW Code', 'the-core' ),
		'description' => __( 'Add a raw code', 'the-core' ),
		'tab'         => __( 'Content Elements', 'the-core' ),
		'popup_size'  => 'medium',
		'title_template' => '{{ if (!o.shortcode_name) { }} {{- title}} {{ } }} {{ if (o.shortcode_name) { }} {{- o.shortcode_name}} {{ } }}',
		'popup_header_elements' => '<a class="fw-docs-link" target="_blank" href="http://docs.themefuse.com/the-core/your-theme/shortcodes/raw-code"><span class="fw-docs-info dashicons dashicons-editor-help"></span>'.__('Go to Docs', 'the-core').'</a>',
	)
);
