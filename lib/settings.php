<?php

if ( ! defined( 'WPINC' ) ) {
	die;
}

function flowplayer_ovp_add_admin_menu(  ) {
	add_options_page(
		'Flowplayer OVP Settings',
		'Flowplayer OVP',
		'manage_options',
		'flowplayer_ovp',
		'flowplayer_ovp_options_page'
	);
}
add_action( 'admin_menu', 'flowplayer_ovp_add_admin_menu' );

function flowplayer_ovp_settings_init(  ) {
	register_setting( 'flowplayer_ovp_settings', 'flowplayer_ovp_settings' );

	add_settings_section(
		'flowplayer_ovp_keys',
		__( 'API keys', 'flowplayer_ovp' ),
		'flowplayer_ovp_settings_section_callback',
		'flowplayer_ovp_settings'
	);

	add_settings_field(
		'flowplayer_ovp_site_id',
		__( 'Site ID', 'flowplayer_ovp' ),
		'flowplayer_ovp_settings_field_render',
		'flowplayer_ovp_settings',
		'flowplayer_ovp_keys',
		['field_id' => 'flowplayer_ovp_site_id']
	);

	add_settings_field(
		'flowplayer_ovp_api_key',
		__( 'API key', 'flowplayer_ovp' ),
		'flowplayer_ovp_settings_field_render',
		'flowplayer_ovp_settings',
		'flowplayer_ovp_keys',
		['field_id' => 'flowplayer_ovp_api_key']
	);
}
add_action( 'admin_init', 'flowplayer_ovp_settings_init' );

function flowplayer_ovp_settings_field_render( $args ) {
	$options = get_option( 'flowplayer_ovp_settings' );
	$field_id = $args['field_id'];
	$defval = is_array($options) && array_key_exists($field_id, $options) ? $options[$field_id] : '';
	?>
	<input type='text' class="regular-text" name='flowplayer_ovp_settings[<?php print $field_id; ?>]' value='<?php echo $defval; ?>'>
	<?php
}

function flowplayer_ovp_settings_section_callback(  ) {
	echo __( 'You can find these keys from your <a href="https://flowplayer.com/app/workspace/settings/publishing" target="blank">Flowplayer OVP workspace settings</a>, by clicking <em>API key</em> link from the top right corner.', 'flowplayer_ovp' );
}

function flowplayer_ovp_options_page(  ) {
	?>
	<form action='options.php' method='post'>

		<h2>Flowplayer OVP</h2>

		<?php
		settings_fields( 'flowplayer_ovp_settings' );
		do_settings_sections( 'flowplayer_ovp_settings' );
		submit_button();
		?>

	</form>
	<?php
}

function flowplayer_ovp_get_settings() {
	$options = get_option( 'flowplayer_ovp_settings' );

	return [
		'site_id' => array_key_exists('flowplayer_ovp_site_id', $options) ? $options['flowplayer_ovp_site_id'] : '',
		'api_key' => array_key_exists('flowplayer_ovp_api_key', $options) ? $options['flowplayer_ovp_api_key'] : '',
	];
}

