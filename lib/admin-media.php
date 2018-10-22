<?php

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Enqueue admin js
 */
function flowplayer_ovp_scripts() {
	$settings = flowplayer_ovp_get_settings();

	if ( $settings['site_id'] !== '' ) {
		wp_enqueue_script(
			'flowplayer-ovp-js',
			plugin_dir_url( dirname( __FILE__ ) ) . 'js/admin.js',
			array( 'jquery' ),
			'',
			true
		);
	}
}
add_action( 'print_media_templates', 'flowplayer_ovp_scripts' );

/**
 * Enqueue extra admin templates
 */
function flowplayer_ovp_print_media_templates() {
	$players = flowplayer_ovp_fetch_players();
	?>
	<script type="text/html" id="tmpl-flowplayer-display-settings">
		<h2><?php _e( 'Flowplayer embed settings', 'flowplayer_ovp' ); ?></h2>

		<label class="setting align">
			<span><?php _e( 'Player', 'flowplayer_ovp' ); ?></span>
			<select class="flowplayer_ovp_player"
				data-setting="fp_ovp_player"
			>
				<# _.each(data.players, function(label, id) { #>
					<option value="{{ id }}">{{ label }}</option>
				<# }); #>
			</select>
		</label>

	</script>
	<?php
}
add_action( 'print_media_templates', 'flowplayer_ovp_print_media_templates' );
