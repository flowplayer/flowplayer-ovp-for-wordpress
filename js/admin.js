var defaultMediaFrame = wp.media.view.MediaFrame.Post;

// Extend the default media library frame to add a new tab
wp.media.view.MediaFrame.Post = defaultMediaFrame.extend({
	fp_players: [],
	fp_playerSettings: null,
	fp_categories: [],

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
		controller.fp_playerSettings = new Backbone.Model();

		jQuery.post(ajaxurl, {
			'action': 'flowplayer_ovp_load_players',
		}, function(response) {
			controller.setPlayers(response.data);
			controller.fp_playerSettings.set('fp_ovp_player', response.data[0]);
		});

		jQuery.post(ajaxurl, {
			'action': 'flowplayer_ovp_load_categories',
		}, function(response) {
			controller.setCategories(response.data);
		});
	},

	setPlayers: function(players) {
		this.fp_players = players;
	},

	setCategories: function(categories) {
		this.fp_categories = categories;
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

			var categoryFilter = wp.media.view.AttachmentFilters.extend({
				className: 'attachment-filters fp-attachment-filters',
				createFilters: function() {
					var filters = {};
					_.each( controller.fp_categories || {}, function( category ) {
						filters[ category.id ] = {
							text: category.name,
							props: {
								uploadedTo: category.concatid,
							}
						};
					});

					filters.all = {
						text: 'All',
						props: {
							uploadedTo: null
						},
						priority: 10
					};
					this.filters = filters;
				}
			})

			browser.toolbar.set( 'categoryFilter', new categoryFilter({
				controller: controller,
				model:      browser.collection.props,
				priority:   -80
			}).render() );

			browser.listenTo(selection, 'selection:single', function() {
				controller.createFpSidebar(browser);
			});

			if(selection.length) {
				controller.createFpSidebar(browser);
			}
		}
	},

	createFpSidebar: function(browser) {
		var FlowplayerDetails = wp.media.View.extend({
			tagName:   'div',
			className: 'attachment-details',
			template:  wp.template('flowplayer-display-details'),

			render: function() {
				this.views.detach();
				this.$el.html( this.template( this.model.toJSON() ) );
				this.views.render();

				return this;
			},

		});

		var FlowplayerSettings = wp.media.view.Settings.extend({
			className: 'attachment-display-settings',
			template:  wp.template('flowplayer-display-settings'),
		});

		browser.sidebar.unset('details');

		browser.sidebar.set({
			'flowplayer-details': new FlowplayerDetails({
				priority: 40,
				model: browser.options.selection.single(),
			}),
			'flowplayer-settings':	new FlowplayerSettings({
				priority: 120,
				players: this.fp_players,
				model: this.fp_playerSettings,
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

				var player_id = controller.fp_playerSettings.get('fp_ovp_player');
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
