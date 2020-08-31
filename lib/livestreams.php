<?php

if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Fetch livestream data from OVP API
 */
function flowplayer_embed_fetch_livestreams( $query ) {
	$settings = flowplayer_embed_get_settings();

  $request = wp_remote_get(
    sprintf(
      'https://api.flowplayer.com/platform/v3/livestreams?%s',
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

  $livestreams = array_map(
    function( $livestream ) {
      return flowplayer_embed_livestream_to_media_attachment( $livestream );
    },
    $data->assets
  );

  wp_send_json_success( $livestreams );
}

/**
 * Convert OVP API response livestream entity to WordPress attachment
 */
function flowplayer_embed_livestream_to_media_attachment( $livestream ) {
	return array(
		'type'          => 'remote',
		'id'            => $livestream->id,
		'title'         => $livestream->name,
		'filename'      => $livestream->name,
		'url'           => sprintf(
			'https://play.flowplayer.com/api/video/embed.jsp?id=%s',
			$livestream->id
		),
		'link'          => false,
		'alt'           => $livestream->name,
		'author'        => '',
		'description'   => $livestream->description,
		'caption'       => '',
		'name'          => $livestream->name,
		'status'        => 'inherit',
		'uploadedTo'    => 0,
		'date'          => strtotime( $livestream->created_at ) * 1000,
		'modified'      => strtotime( $livestream->created_at ) * 1000,
		'menuOrder'     => 0,
		'mime'          => 'remote/flowplayer',
		'subtype'       => 'flowplayer',
		'icon'          => $livestream->images[0]->url,
		'dateFormatted' => mysql2date( get_option( 'date_format' ), $livestream->created_at ),
		'category'      => __( 'Playlist', 'flowplayer_embed' ),
		'externalId'    => $livestream->id,
		'tags'          => $livestream->tags,
		'editLink'      => false,
		'nonces'        => array(
			'update' => false,
			'delete' => false,
		),
	);
}