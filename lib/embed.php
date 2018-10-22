<?php

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Register the custom oembed handler
 */
function flowplayer_ovp_register_embed_handler() {
	wp_embed_register_handler(
		'flowplayer_ovp',
		'#https?://play\.flowplayer\.com/api/video/embed\.jsp\?id=([a-zA-Z0-9_-]+)&(?:amp;)?pi=([a-zA-Z0-9_-]+)#i',
		'flowplayer_ovp_embed_handler'
	);
}
add_action( 'init', 'flowplayer_ovp_register_embed_handler' );

/**
 * Convert the recognised embed url to the actual embed code
 */
function flowplayer_ovp_embed_handler( $matches, $attr, $url, $rawattr ) {
	$embed = sprintf(
		'<div class="flowplayer-embed-container" style="position: relative; padding-bottom: 56.25%%; height: 0; overflow: hidden; max-width:100%%;"> <iframe style="position: absolute; top: 0; left: 0; width: 100%%; height: 100%%;" webkitAllowFullScreen mozallowfullscreen allowfullscreen src="https://ljsp.lwcdn.com/api/video/embed.jsp?id=%1$s&pi=%2$s" title="0" byline="0" portrait="0" width="640" height="360" frameborder="0" allow="autoplay"></iframe> </div>',
		esc_attr( $matches[1] ),
		esc_attr( $matches[2] )
	);

	return apply_filters( 'embed_flowplayer_ovp', $embed, $matches, $attr, $url, $rawattr );
}
