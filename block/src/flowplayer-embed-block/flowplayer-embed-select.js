const { wp } = window;

const { Component, Fragment } = wp.element;
const { BlockControls } = wp.editor;
const { Button, IconButton, Toolbar } = wp.components;
const { __ } = wp.i18n;

import FlowplayerEmbedRender from './flowplayer-embed-render';

const getFlowplayerMediaFrame = () => {
	const defaultMediaFrame = wp.media.view.MediaFrame.Post;
	return wp.media.view.MediaFrame.Post.extend( {
		createStates: function createStates() {
			this.states.add( [
				new wp.media.controller.Library( {
					id: 'flowplayer',
					title: 'Add Flowplayer embed',
					priority: 20,
					router: 'empty',
					toolbar: 'flowplayer',
					filterable: false,
					searchable: true,
					date: false,
					selection: false,
					multiple: false,
					editable: false,
					library: wp.media.query( _.defaults( {
						flowplayer: 'flowplayer',
					}, this.options.library ) ),
					displaySettings: false,
					url: '',
				} ),
			] );
		},

		bindHandlers: function() {
			defaultMediaFrame.prototype.bindHandlers.apply( this, arguments );

			this.on( 'toolbar:create:flowplayer', this.createToolbar, this );
			this.on( 'toolbar:render:flowplayer', this.flowplayerToolbar, this );
		},

		flowplayerToolbar: function( view ) {
			const controller = this;

			this.selectionStatusToolbar( view );

			view.set( 'insert', {
				style: 'primary',
				priority: 80,
				text: wp.media.view.l10n.insertIntoPost,
				requires: { selection: true },

				click: function() {
					const state = controller.state();
					const attachment = state.get( 'selection' ).pop();
					const options = attachment.toJSON();
					options.settings = state.display( attachment ).toJSON();

					const playerId = controller.fp_playerSettings.get( 'fp_embed_player' );
					const fullUrl = options.url + '&pi=' + playerId;

					state.set( 'fp-embed', {
						vid: options.url.match( /\?id=(.*)/i )[ 1 ],
						pid: playerId,
						fullUrl,
					} );
					controller.close();
					state.trigger( 'select' );
				},
			} );
		},

	} );
};

class FlowplayerEmbedSelect extends Component {
	constructor() {
		super( ...arguments );
		this.openModal = this.openModal.bind( this );
		this.onSelect = this.onSelect.bind( this );

		const flowplayerMediaFrame = getFlowplayerMediaFrame();
		this.frame = new flowplayerMediaFrame( {
			state: 'flowplayer',
		} );
		wp.media.frame = this.frame;

		this.frame.on( 'select', this.onSelect );
	}

	componentWillUnmount() {
		this.frame.remove();
	}

	onSelect() {
		const attachment = this.frame.state().get( 'fp-embed' );

		this.props.setAttributes( {
			pid: attachment.pid,
			vid: attachment.vid,
		} );
	}

	openModal() {
		this.frame.open();
	}

	render() {
		if ( this.props.attributes && this.props.attributes.vid ) {
			const { vid, pid } = this.props.attributes;
			return (
				<Fragment>
					<BlockControls>
						<Toolbar>
							<IconButton
								className="components-toolbar__control"
								label={ __( 'Select another embed' ) }
								icon="edit"
								onClick={ this.openModal }
							/>
						</Toolbar>
					</BlockControls>
					<FlowplayerEmbedRender vid={ vid } pid={ pid } />
				</Fragment>
			);
		}

		const label = __( 'Select Flowplayer embed' );
		return (
			<div className="components-placeholder">
				<Button
					isLarge
					className="editor-media-placeholder__button"
					onClick={ this.openModal }
				>
					{ label }
				</Button>
			</div>
		);
	}
}

export default FlowplayerEmbedSelect;
