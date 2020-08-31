<?php

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Register settings page
 */
function flowplayer_embed_add_admin_menu() {
	add_options_page(
		'Flowplayer Platform Embed Settings',
		'Flowplayer Embed',
		'manage_options',
		'flowplayer_embed',
		'flowplayer_embed_options_page'
	);
}
add_action( 'admin_menu', 'flowplayer_embed_add_admin_menu' );

/**
 * Register settings fields
 */
function flowplayer_embed_settings_init() {
	register_setting( 'flowplayer_embed_settings', 'flowplayer_embed_settings' );

	add_settings_section(
		'flowplayer_embed_keys',
		__( 'API keys', 'flowplayer_embed' ),
		'flowplayer_embed_settings_section_callback',
		'flowplayer_embed_settings'
	);

	add_settings_field(
		'flowplayer_embed_site_id',
		__( 'Site ID', 'flowplayer_embed' ),
		'flowplayer_embed_settings_field_render',
		'flowplayer_embed_settings',
		'flowplayer_embed_keys',
		[ 'field_id' => 'flowplayer_embed_site_id' ]
	);

	add_settings_field(
		'flowplayer_embed_api_key',
		__( 'API key', 'flowplayer_embed' ),
		'flowplayer_embed_settings_field_render',
		'flowplayer_embed_settings',
		'flowplayer_embed_keys',
		[ 'field_id' => 'flowplayer_embed_api_key' ]
	);

	add_settings_section(
		'flowplayer_embed_livestreams',
		__( 'Livestreams', 'flowplayer_embed' ),
		'flowplayer_embed_settings_livestreams_section_callback',
		'flowplayer_embed_settings'
	);

	add_settings_field(
		'flowplayer_embed_show_livestreams',
		__( 'Enable livestreams', 'flowplayer_embed' ),
		'flowplayer_embed_settings_checkbox_render',
		'flowplayer_embed_settings',
		'flowplayer_embed_livestreams',
		[ 'field_id' => 'flowplayer_embed_show_livestreams' ]
	);
}
add_action( 'admin_init', 'flowplayer_embed_settings_init' );

/**
 * Renders single setting text field
 */
function flowplayer_embed_settings_field_render( $args ) {
	$options  = get_option( 'flowplayer_embed_settings' );
	$field_id = $args['field_id'];
	$defval   = is_array( $options ) && array_key_exists( $field_id, $options ) ? $options[ $field_id ] : '';
	?>
	<input type='text' class="regular-text" name='flowplayer_embed_settings[<?php print $field_id; ?>]' value='<?php echo $defval; ?>'>
	<?php
}

/**
 * Renders single setting checkbox
 */
function flowplayer_embed_settings_checkbox_render( $args ) {
	$options  = get_option( 'flowplayer_embed_settings' );
	$field_id = $args['field_id'];
	$defval   = is_array( $options ) && array_key_exists( $field_id, $options ) ? $options[ $field_id ] : '';
	?>
	<input type='checkbox' name='flowplayer_embed_settings[<?php print $field_id; ?>]' <?php if ($defval === 'on') echo 'checked="checked"'; ?>>
	<?php
}

/**
 * Render section
 */
function flowplayer_embed_settings_section_callback() {
	echo __( 'You can find these keys from your <a href="https://flowplayer.com/app/workspace/settings" target="blank">Flowplayer app workspace settings</a>.', 'flowplayer_embed' );
}

/**
 * Render section
 */
function flowplayer_embed_settings_livestreams_section_callback() {
	echo __( 'Since livestreams are available only for Enterprise level users, please enable them separately to show them in embed dialog.', 'flowplayer_embed' );
}

/**
 * Settings page template
 */
function flowplayer_embed_options_page() {
	?>
	<form action='options.php' method='post'>

		<h2>Flowplayer Platform Embed</h2>

		<?php
		settings_fields( 'flowplayer_embed_settings' );
		do_settings_sections( 'flowplayer_embed_settings' );
		submit_button();
		?>

	</form>
	<?php
}

/**
 * Get all flowplayer_embed settings
 */
function flowplayer_embed_get_settings() {
	$options = get_option( 'flowplayer_embed_settings' );

	return [
		'site_id' => array_key_exists( 'flowplayer_embed_site_id', $options ) ? $options['flowplayer_embed_site_id'] : '',
		'api_key' => array_key_exists( 'flowplayer_embed_api_key', $options ) ? $options['flowplayer_embed_api_key'] : '',
		'show_livestreams' => array_key_exists( 'flowplayer_embed_show_livestreams', $options ) ? $options['flowplayer_embed_show_livestreams'] : '',
	];
}

/**
 * Check if settings exists
 */
function flowplayer_embed_is_configured() {
	$settings = flowplayer_embed_get_settings();

	return $settings['site_id'] !== '' && $settings['api_key'] !== '';
}
