<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

$cfg = array(
	'page_builder' => array(
		'title'       => __( 'Icon Box', 'the-core' ),
		'description' => __( 'Add an Icon Box', 'the-core' ),
		'tab'         => __( 'Content Elements', 'the-core' ),
		'popup_size'  => 'medium',
		'title_template' => '{{ if (!o["box-title"]) { }} {{- title}} {{ } }} {{ if (o["box-title"]) { }} {{- o["box-title"].trim()}} {{ } }}',
		'popup_header_elements' => '<a class="fw-docs-link" target="_blank" href="http://docs.themefuse.com/the-core/your-theme/shortcodes/icon-box-1"><span class="fw-docs-info dashicons dashicons-editor-help"></span>'.__('Go to Docs', 'the-core').'</a>',
	)
);