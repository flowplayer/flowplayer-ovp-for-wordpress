<?php

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Parse ajax queries
 */
function flowplayer_ovp_query_attachments( $args ) {
	if ( isset( $_REQUEST['query']['flowplayer'] ) ) {
		$settings = flowplayer_ovp_get_settings();

		// Fetch attachments.
		$query = array(
			'api_key'   => $settings['api_key'],
			'page_size' => filter_var( $_REQUEST['query']['posts_per_page'], FILTER_SANITIZE_NUMBER_INT ),
			'page'      => filter_var( $_REQUEST['query']['paged'], FILTER_SANITIZE_NUMBER_INT ),
		);

		if ( isset( $_REQUEST['query']['s'] ) ) {
			$query['search'] = urlencode( sanitize_text_field( $_REQUEST['query']['s'] ) );
		}

		if ( isset( $_REQUEST['query']['post_parent'] ) ) {
			$query['categories'] = urlencode( sanitize_text_field( $_REQUEST['query']['post_parent'] ) );

			// Null this, since we've essentially hijacked this argument.
			$_REQUEST['query']['post_parent'] = null;
		}

		$request = wp_remote_get(
			sprintf(
				'https://api.flowplayer.com/ovp/web/video/v2/site/%s.json?%s',
				$settings['site_id'],
				build_query( $query )
			)
		);

		if ( is_wp_error( $request ) ) {
			return false; // Bail early.
		}
		$body = wp_remote_retrieve_body( $request );
		$data = json_decode( $body );

		$categories = flowplayer_ovp_fetch_categories();

		// Map attachments to expected format.
		$videos = array_map(
			function( $video ) use ( $categories ) {
				return flowplayer_ovp_video_to_media_attachment( $video, $categories );
			},
			$data->videos
		);

		wp_send_json_success( $videos );
	}

	return $args;
}
add_filter( 'ajax_query_attachments_args', 'flowplayer_ovp_query_attachments', 99 );

/**
 * Convert OVP API response video entity to WordPress attachment
 */
function flowplayer_ovp_video_to_media_attachment( $video, $categories ) {
	return array(
		'type'          => 'remote',
		'id'            => $video->id,
		'title'         => $video->name,
		'filename'      => $video->name,
		'fileLength'    => duration( $video->duration ),
		'url'           => sprintf(
			'https://play.flowplayer.com/api/video/embed.jsp?id=%s',
			$video->id
		),
		'link'          => false,
		'alt'           => $video->name,
		'author'        => '',
		'description'   => $video->description,
		'caption'       => '',
		'name'          => $video->name,
		'status'        => 'inherit',
		'uploadedTo'    => 0,
		'date'          => strtotime( $video->created_at ) * 1000,
		'modified'      => strtotime( $video->created_at ) * 1000,
		'menuOrder'     => 0,
		'mime'          => 'remote/flowplayer',
		'subtype'       => 'flowplayer',
		'icon'          => $video->images->thumbnail_url,
		'dateFormatted' => mysql2date( get_option( 'date_format' ), $video->created_at ),
		'category'      => flowplayer_ovp_find_category_name( $video->categoryid, $categories ),
		'externalId'    => $video->externalvideoid,
		'tags'          => str_replace( ',', ', ', $video->tags ),
		'editLink'      => false,
		'nonces'        => array(
			'update' => false,
			'delete' => false,
		),
	);
}

/**
 * Convert video duration to pretty format
 */
function duration( $seconds_count ) {
	$delimiter = ':';
	$seconds   = $seconds_count % 60;
	$minutes   = floor( $seconds_count / 60 );
	$hours     = floor( $seconds_count / 3600 );

	$seconds = str_pad( $seconds, 2, '0', STR_PAD_LEFT );
	$minutes = str_pad( $minutes, 2, '0', STR_PAD_LEFT ) . $delimiter;
	$hours   = $hours > 0 ? str_pad( $hours, 2, '0', STR_PAD_LEFT ) . $delimiter : '';

	return "$hours$minutes$seconds";
}

/**
 * Add ajax endpoint for loading player configs
 */
function flowplayer_ovp_ajax_load_players() {
	$players = flowplayer_ovp_fetch_players();

	wp_send_json_success( $players );
}
add_action( 'wp_ajax_flowplayer_ovp_load_players', 'flowplayer_ovp_ajax_load_players' );

/**
 * Fetch players from OVP API
 */
function flowplayer_ovp_fetch_players() {
	$settings = flowplayer_ovp_get_settings();

	$request = wp_remote_get(
		sprintf(
			'https://api.flowplayer.com/ovp/web/player/v2/workspaces/%s.json?api_key=%s',
			$settings['site_id'],
			$settings['api_key']
		)
	);

	if ( is_wp_error( $request ) ) {
		return false; // Bail early.
	}
	$body = wp_remote_retrieve_body( $request );
	$data = json_decode( $body );

	$players = [];

	foreach ( $data->players as $player ) {
		$players[ $player->id ] = $player->name;
	}

	return $players;
}

/**
 * Add ajax endpoint for loading categories
 */
function flowplayer_ovp_ajax_load_categories() {
	$categories = flowplayer_ovp_fetch_categories();

	wp_send_json_success( $categories );
}
add_action( 'wp_ajax_flowplayer_ovp_load_categories', 'flowplayer_ovp_ajax_load_categories' );

/**
 * Fetch categories from OVP API
 */
function flowplayer_ovp_fetch_categories() {
	$settings = flowplayer_ovp_get_settings();

	$request = wp_remote_get(
		sprintf(
			'https://api.flowplayer.com/ovp/web/category/v2/site/%s.json?api_key=%s',
			$settings['site_id'],
			$settings['api_key']
		)
	);

	if ( is_wp_error( $request ) ) {
		return false; // Bail early.
	}
	$body = wp_remote_retrieve_body( $request );
	$data = json_decode( $body );

	$categories = [];

	foreach ( $data->categories as $category ) {
		if ( ! $category->hidden ) {
			$categories[] = $category;
		}
	}

	return flowplayer_ovp_nest_categories( $categories );
}

require_once( 'categories.php' );
