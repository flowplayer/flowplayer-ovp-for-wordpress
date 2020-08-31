<?php

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Parse ajax queries
 */
function flowplayer_embed_query_attachments( $args ) {
	if ( isset( $_REQUEST['query']['flowplayer'] ) ) {
		$settings = flowplayer_embed_get_settings();

		// Fetch attachments.
		$query = array(
			'page_size' => filter_var( $_REQUEST['query']['posts_per_page'], FILTER_SANITIZE_NUMBER_INT ),
			'page'      => filter_var( $_REQUEST['query']['paged'], FILTER_SANITIZE_NUMBER_INT ) - 1,
		);

		if ( isset( $_REQUEST['query']['s'] ) ) {
			$query['search'] = urlencode( sanitize_text_field( $_REQUEST['query']['s'] ) );
		}

		if ( isset( $_REQUEST['query']['post_parent'] ) ) {
			$category = urlencode( sanitize_text_field( $_REQUEST['query']['post_parent'] ) );

			// Null this, since we've essentially hijacked this argument.
			$_REQUEST['query']['post_parent'] = null;

			if( $category === 'playlists' ) {
				// Pivot to playlist fetching and return early
				return flowplayer_embed_fetch_playlists( $query );
			}

			if( $category === 'livestreams' ) {
				// Pivot to playlist fetching and return early
				return flowplayer_embed_fetch_livestreams( $query );
			}

			$query['categories'] = $category;
		}

		$request = wp_remote_get(
			sprintf(
				'https://api.flowplayer.com/platform/v3/videos?%s',
				build_query( $query )
			), array(
				'headers' => array(
					'Content-Type' => 'application/json',
					'x-flowplayer-api-key' => $settings['api_key']
				)
			)
		);

		if ( is_wp_error( $request ) ) {
			return false; // Bail early.
		}
		$body = wp_remote_retrieve_body( $request );
		$data = json_decode( $body );

		$categories = flowplayer_embed_fetch_categories();

		// Map attachments to expected format.
		$videos = array_map(
			function( $video ) use ( $categories ) {
				return flowplayer_embed_video_to_media_attachment( $video, $categories );
			},
			$data->assets
		);

		wp_send_json_success( $videos );
	}

	return $args;
}
add_filter( 'ajax_query_attachments_args', 'flowplayer_embed_query_attachments', 99 );

/**
 * Convert OVP API response video entity to WordPress attachment
 */
function flowplayer_embed_video_to_media_attachment( $video, $categories ) {
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
		'icon'          => $video->images[0]->url,
		'dateFormatted' => mysql2date( get_option( 'date_format' ), $video->created_at ),
		'category'      => flowplayer_embed_find_category_name( $video->category_id, $categories ),
		'externalId'    => $video->id,
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
function flowplayer_embed_ajax_load_players() {
	$players = flowplayer_embed_fetch_players();

	wp_send_json_success( $players );
}
add_action( 'wp_ajax_flowplayer_embed_load_players', 'flowplayer_embed_ajax_load_players' );

/**
 * Fetch players from OVP API
 */
function flowplayer_embed_fetch_players() {
	$settings = flowplayer_embed_get_settings();

	$request = wp_remote_get(
		sprintf(
			'https://api.flowplayer.com/ovp/web/player/v2/workspaces/%s.json?api_key=%s',
			$settings['site_id'],
			$settings['api_key']
		)
	);

	if ( wp_remote_retrieve_response_code( $request ) !== 200 ) {
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
function flowplayer_embed_ajax_load_categories() {
	$categories = flowplayer_embed_fetch_categories();

	wp_send_json_success( $categories );
}
add_action( 'wp_ajax_flowplayer_embed_load_categories', 'flowplayer_embed_ajax_load_categories' );

/**
 * Fetch categories from OVP API
 */
function flowplayer_embed_fetch_categories() {
	$settings = flowplayer_embed_get_settings();

	$request = wp_remote_get(
		sprintf(
			'https://api.flowplayer.com/platform/v3/categories?%s',
			build_query( array('page_size' => 100) )
		), array(
			'headers' => array(
				'Content-Type' => 'application/json',
				'x-flowplayer-api-key' => $settings['api_key']
			)
		)
	);

	if ( wp_remote_retrieve_response_code( $request ) !== 200 ) {
		return false; // Bail early.
	}
	$body = wp_remote_retrieve_body( $request );
	$data = json_decode( $body );

	$categories = [];

	foreach ( $data->assets as $category ) {
		if ( ! $category->hidden ) {
			$categories[] = $category;
		}
	}

	return array_merge(
		flowplayer_embed_nest_categories( $categories ),
		array(
			array(
				'id' => 'playlists',
				'concatid' => 'playlists',
				'name' => __( 'Playlists', 'flowplayer_embed' )
			)
		),
		$settings['show_livestreams'] == 'on'
			? array(
				array(
					'id' => 'livestreams',
					'concatid' => 'livestreams',
					'name' => __( 'Livestreams', 'flowplayer_embed' )
				)
			) 
			: array()
	);
}

require_once( 'categories.php' );
require_once( 'playlists.php' );
require_once( 'livestreams.php' );
