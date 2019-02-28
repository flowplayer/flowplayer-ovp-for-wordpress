/**
 * BLOCK: flowplayer-embed-block
 */
import FlowplayerEmbedSelect from './flowplayer-embed-select';
import FlowplayerEmbedRender from './flowplayer-embed-render';

const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { Path, SVG } = wp.components;

/**
 * Register: Flowplayer Embed Block
 */
registerBlockType( 'flowplayer-embed/flowplayer-embed-block', {
	title: __( 'Flowplayer' ),
	icon: <SVG xmlns="http://www.w3.org/2000/svg" viewBox="0 0 330 330"><Path fill="#006680" d="M165,6.66C77.69,6.66,6.66,77.69,6.66,165S77.69,323.34,165,323.34s158.34-71,158.34-158.34S252.31,6.66,165,6.66Zm0,293.59C90.43,300.25,29.76,239.58,29.76,165S90.43,29.75,165,29.75,300.25,90.42,300.25,165,239.57,300.25,165,300.25Zm64.77-144.49-88.23-50.94c-8.8-5.08-16-.92-16,9.24V215.94c0,10.16,7.2,14.32,16,9.24l88.23-50.94C238.58,169.16,238.58,160.84,229.78,155.76Z" /></SVG>,
	category: 'embed',
	keywords: [
		__( 'Flowplayer Embed' ),
	],

	attributes: {
		vid: {
			type: 'string',
		},
		pid: {
			type: 'string',
		},
	},

	edit: function( props ) {
		return (
			<FlowplayerEmbedSelect { ...props } />
		);
	},

	save: function( props ) {
		const { vid, pid } = props.attributes;
		return (
			<FlowplayerEmbedRender vid={ vid } pid={ pid } />
		);
	},
} );
