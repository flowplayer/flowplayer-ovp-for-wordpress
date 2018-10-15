<?php

if ( ! defined( 'WPINC' ) ) {
	die;
}

function flowplayer_ovp_query_attachments( $args ) {
	if (isset($_REQUEST['query']['flowplayer'])) {
		$settings = flowplayer_ovp_get_settings();

		// Fetch attachments
		$request = wp_remote_get(
			sprintf(
				'https://api.flowplayer.com/ovp/web/video/v2/site/%s.json?api_key=%s&page_size=%s&page=%s',
				$settings['site_id'],
				$settings['api_key'],
				filter_var($_REQUEST['query']['posts_per_page'], FILTER_SANITIZE_NUMBER_INT),
				filter_var($_REQUEST['query']['paged'], FILTER_SANITIZE_NUMBER_INT)
			)
		);

    if( is_wp_error( $request ) ) {
      return false; // Bail early
    }
    $body = wp_remote_retrieve_body( $request );
		$data = json_decode( $body );

		// Map attachments to expected format
		$videos = array_map('flowplayer_ovp_video_to_media_attachment', $data->videos);

		wp_send_json_success($videos);
	}

	return $args;
}
add_filter( 'ajax_query_attachments_args', 'flowplayer_ovp_query_attachments', 99);

function flowplayer_ovp_video_to_media_attachment( $video ) {
	return array(
			'type'				=> 'remote',
			'id'          => $video->id,
			'title'       => $video->name,
			'filename'    => $video->name,
			'url'         => sprintf(
				'https://play.flowplayer.com/api/video/embed.jsp?id=%s',
				$video->id
			),
			'link'        => false,
			'alt'         => $video->name,
			'author'      => '',
			'description' => $video->description,
			'caption'     => '',
			'name'        => $video->name,
			'status'      => 'inherit',
			'uploadedTo'  => 0,
			'date'        => strtotime($video->created_at) * 1000,
			'modified'    => strtotime($video->created_at) * 1000,
			'menuOrder'   => 0,
			'mime'        => 'remote/flowplayer',
			'subtype'     => "flowplayer",
			'icon'        => $video->images->thumbnail_url,
			'dateFormatted' => mysql2date(get_option('date_format'), $video->created_at),
			'nonces'      => array(
					'update' => false,
					'delete' => false,
			),
			'editLink'   => false,
	);
}

function flowplayer_ovp_ajax_load_players() {
	$players = flowplayer_ovp_fetch_players();

	wp_send_json_success($players);
}
add_action( 'wp_ajax_flowplayer_ovp_load_players', 'flowplayer_ovp_ajax_load_players' );

function flowplayer_ovp_fetch_players() {
	$settings = flowplayer_ovp_get_settings();

	$request = wp_remote_get(
		sprintf(
			'https://api.flowplayer.com/ovp/web/player/v2/workspaces/%s.json?api_key=%s',
			$settings['site_id'],
			$settings['api_key']
		)
	);

	if( is_wp_error( $request ) ) {
		return false; // Bail early
	}
	$body = wp_remote_retrieve_body( $request );
	$data = json_decode( $body );

	$players = [];

	foreach ($data->players as $player) {
		$players[$player->id] = $player->name;
	}

	return $players;
}
