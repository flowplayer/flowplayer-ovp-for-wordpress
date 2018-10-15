<?php
/**
 *
 * @package   Flowplayer OVP
 * @author    Janne Ala-Äijälä <janne.ala-aijala@flowplayer.com>
 * @license   GPL-2.0+
 * @link      https://flowplayer.com/
 * @copyright 2018 Flowplayer Ltd
 *
 * @wordpress-plugin
 * Plugin Name: Flowplayer OVP
 * Description: Embed videos from Flowplayer online video platform directly into posts via WordPress media gallery
 * Version:     0.1.0
 * Author:      Flowplayer ltd.
 * Author URI:  https://flowplayer.com/
 * Text Domain: flowplayer-ovp
 * Domain Path: /languages
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$dir = dirname( __FILE__ );

require_once( $dir . '/lib/embed.php' );

if ( is_admin() ) {
	require_once( $dir . '/lib/settings.php' );
	require_once( $dir . '/lib/admin-media.php' );
	require_once( $dir . '/lib/admin-ajax.php' );
}
