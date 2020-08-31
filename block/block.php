<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue Gutenberg block assets for backend editor.
 */
function flowplayer_embed_block_editor_assets() {
	if( !flowplayer_embed_is_configured() ) {
		return;
	}

	wp_enqueue_script(
		'flowplayer_embed_block-js',
		plugins_url( '/block/dist/blocks.build.js', dirname( __FILE__ ) ),
		array(
			'wp-blocks',
			'wp-components',
			'wp-editor',
			'wp-element',
			'wp-i18n',
		),
		'0.3.0',
		true
	);
}
add_action( 'enqueue_block_editor_assets', 'flowplayer_embed_block_editor_assets' );
