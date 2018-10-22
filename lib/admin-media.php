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
	<script type="text/html" id="tmpl-flowplayer-display-details">
		<h2>
			<?php _e( 'Video Details', 'flowplayer_ovp' ); ?>
		</h2>
		<div class="attachment-info">
			<div class="thumbnail thumbnail-video">
				<img src="{{ data.icon }}" class="icon" draggable="false" alt="" />
			</div>
			<div class="details">
				<div class="filename">{{ data.filename }}</div>
				<div class="uploaded">{{ data.dateFormatted }}</div>
				<# if ( data.fileLength ) { #>
					<div class="file-length"><?php _e( 'Length:', 'flowplayer_ovp' ); ?> {{ data.fileLength }}</div>
				<# } #>
			</div>
		</div>

		<label class="setting" data-setting="title">
			<span class="name"><?php _e( 'Title', 'flowplayer_ovp' ); ?></span>
			<input type="text" value="{{ data.title }}" readonly />
		</label>
		<label class="setting" data-setting="description">
			<span class="name"><?php _e( 'Description', 'flowplayer_ovp' ); ?></span>
			<textarea readonly>{{ data.description }}</textarea>
		</label>
		<label class="setting" data-setting="category">
			<span class="name"><?php _e( 'Category', 'flowplayer_ovp' ); ?></span>
			<input type="text" value="{{ data.category }}" readonly />
		</label>
		<label class="setting" data-setting="tags">
			<span class="name"><?php _e( 'Tags', 'flowplayer_ovp' ); ?></span>
			<input type="text" value="{{ data.tags }}" readonly />
		</label>
		<# if (data.externalId !== '') { #>
		<label class="setting" data-setting="external-id">
			<span class="name"><?php _e( 'External ID', 'flowplayer_ovp' ); ?></span>
			<input type="text" value="{{ data.externalId }}" readonly />
		</label>
		<# } #>

		<div class="compat-meta"></div>
	</script>

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
