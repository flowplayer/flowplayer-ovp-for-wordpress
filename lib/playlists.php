<?php

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Fetch playlist data from OVP API
 */
function flowplayer_embed_fetch_playlists( $query ) {
	$settings = flowplayer_embed_get_settings();

  $request = wp_remote_get(
    sprintf(
      'https://api.flowplayer.com/platform/v3/playlists?%s',
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

  $playlists = array_map(
    function( $playlist ) {
      return flowplayer_embed_playlist_to_media_attachment( $playlist );
    },
    $data->assets
  );

  wp_send_json_success( $playlists );
}

/**
 * Convert OVP API response playlist entity to WordPress attachment
 */
function flowplayer_embed_playlist_to_media_attachment( $playlist ) {
	return array(
		'type'          => 'remote',
		'id'            => $playlist->id,
		'title'         => $playlist->name,
		'filename'      => $playlist->name,
		'url'           => sprintf(
			'https://play.flowplayer.com/api/video/embed.jsp?id=%s',
			$playlist->id
		),
		'link'          => false,
		'alt'           => $playlist->name,
		'author'        => '',
		'description'   => $playlist->description,
		'caption'       => '',
		'name'          => $playlist->name,
		'status'        => 'inherit',
		'uploadedTo'    => 0,
		'date'          => strtotime( $playlist->created_at ) * 1000,
		'modified'      => strtotime( $playlist->created_at ) * 1000,
		'menuOrder'     => 0,
		'mime'          => 'remote/flowplayer',
		'subtype'       => 'flowplayer',
		'icon'          => $playlist->items[0]->images[0]->url,
		'dateFormatted' => mysql2date( get_option( 'date_format' ), $playlist->created_at ),
		'category'      => __( 'Playlist', 'flowplayer_embed' ),
		'externalId'    => $playlist->id,
		'tags'          => '',
		'editLink'      => false,
		'nonces'        => array(
			'update' => false,
			'delete' => false,
		),
	);
}