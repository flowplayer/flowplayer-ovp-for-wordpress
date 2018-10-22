var defaultMediaFrame = wp.media.view.MediaFrame.Post;

// Extend the default media library frame to add a new tab
wp.media.view.MediaFrame.Post = defaultMediaFrame.extend({
	players: [],
	playerSettings: null,

	initialize: function() {
		defaultMediaFrame.prototype.initialize.apply( this, arguments );
		var controller = this;

		var flowplayerLibrary = new wp.media.controller.Library({
			id:         'flowplayer',
			title:      'Add Flowplayer embed',
			priority:   20,
			router:     'empty',
			toolbar:    'flowplayer',
			filterable: false,
			searchable: true,
			date:       false,
			selection:  false,
			multiple:   false,
			editable:   false,
			library:    wp.media.query( _.defaults({
										flowplayer: 'flowplayer',
									}, controller.options.library ) ),
			displaySettings: false,
			url:        '',
		});

		controller.states.add([ flowplayerLibrary ]);
		controller.playerSettings = new Backbone.Model();

		jQuery.post(ajaxurl, {
			'action': 'flowplayer_ovp_load_players',
		}, function(response) {
			controller.setPlayers(response.data);
			controller.playerSettings.set('fp_ovp_player', response.data[0]);
		});
	},

	setPlayers: function(players) {
		this.players = players;
	},

	bindHandlers: function() {
		defaultMediaFrame.prototype.bindHandlers.apply( this, arguments );

		this.on( 'router:create:empty', this.emptyRouter, this );
		this.on( 'content:render:browse', this.flowplayerContent, this );
		this.on( 'toolbar:create:flowplayer', this.createToolbar, this );
		this.on( 'toolbar:render:flowplayer', this.flowplayerToolbar, this );
	},

	emptyRouter: function( router ) {
		router.view = new wp.media.view.Router({
			controller: this
		});
	},

	flowplayerContent: function( browser ) {
		var state = this.state(),
				selection = browser.options.selection;

		if( state.get('id') === 'flowplayer' ) {
			var controller = this;

			browser.listenTo(selection, 'selection:single', function() {
				controller.createFpSettings(browser);
			});

			if(selection.length) {
				controller.createFpSettings(browser);
			}
		}
	},

	createFpSettings: function(browser) {
		var FlowplayerSettings = wp.media.view.Settings.extend({
			className: 'flowplayer-display-settings',
			template:  wp.template('flowplayer-display-settings'),
		});

		browser.sidebar.set({
			'flowplayer':	new FlowplayerSettings({
				priority: 120,
				players: this.players,
				model: this.playerSettings,
			})
		});
	},

	flowplayerToolbar: function( view ) {
		var controller = this;

		this.selectionStatusToolbar( view );

		view.set( 'insert', {
			style:    'primary',
			priority: 80,
			text:     wp.media.view.l10n.insertIntoPost,
			requires: { selection: true },

			click: function() {
				var state = controller.state();
				var attachment = state.get('selection').pop();
				var options = attachment.toJSON();
				options.settings = state.display( attachment ).toJSON();

				var player_id = controller.playerSettings.get('fp_ovp_player');
				var url = options.url + '&pi=' + player_id;

				wp.media.post( 'send-link-to-editor', {
					nonce:      wp.media.view.settings.nonce.sendToEditor,
					attachment: options,
					src:				url,
					html:       url,
					post_id:    wp.media.view.settings.post.id,
				}).done( function( resp ) {
					wp.media.editor.insert( resp );
				});

				controller.close();
				state.reset();
			}
		});
	},

});
