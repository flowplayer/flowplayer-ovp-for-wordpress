export const FlowplayerEmbedRender = function( { vid, pid } ) {
	return (
		<div
			className="flowplayer-embed-container"
			style={ {
				position: 'relative',
				'padding-bottom': '56.25%',
				height: 0,
				overflow: 'hidden',
				'max-width': '100%',
			} }
		>
			<iframe
				src={ `https://ljsp.lwcdn.com/api/video/embed.jsp?id=${ vid }&pi=${ pid }` }
				style={ {
					position: 'absolute',
					top: 0,
					left: 0,
					width: '100%',
					height: '100%',
				} }
				webkitAllowFullScreen mozallowfullscreen allowFullScreen
				title="0" byline="0" portrait="0" width="640" height="360" frameBorder="0" allow="autoplay"></iframe>
		</div>
	);
};

export default FlowplayerEmbedRender;
