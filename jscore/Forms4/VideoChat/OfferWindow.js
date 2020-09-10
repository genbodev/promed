Ext6.define('videoChat.OfferWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.swVideoChatOfferWindow',
	autoShow: false,
	renderTo: main_center_panel.body.dom,
	layout: 'vbox',
	justifyContent: 'center',
	constrain: true,
	cls: 'video-chat-offer',
	title: 'Вызов',
	draggable: true,
	header: false,
	alwaysOnTop: true,
	modal: true,

	listeners: {
		beforehide: function() {
			var me = this;

			if (me.status == 'accept') {
				me.accept();
			} else {
				me.refuse();
			}
		}
	},

	setUserInfo: function(userInfo) {
		var me = this;
		Ext6.getCmp(me.getId()+'-avatar').setData(userInfo);
		Ext6.getCmp(me.getId()+'-user').setData(userInfo);
	},

	show: function() {
		var me = this;

		me.callParent(arguments);

		me.accept = Ext6.emptyFn;
		me.refuse = Ext6.emptyFn;
		me.status = null;

		if (arguments[0] && arguments[0].accept) {
			me.accept = arguments[0].accept;
		}
		if (arguments[0] && arguments[0].refuse) {
			me.refuse = arguments[0].refuse;
		}
		if (arguments[0] && arguments[0].userInfo) {
			me.setUserInfo(arguments[0].userInfo);
		}
	},

	initComponent: function() {
		var me = this;

		Ext6.apply(me, {
			items: [{
				layout: 'hbox',
				border: false,
				style: {
					'padding': '30px 59px',
					'width': '478px',
					'height': '150px',
				},
				items: [{
					xtype: 'button',
					cls: 'video-chat-offer-refuse',
					width: 60,
					height: 60,
					handler: function() {
						me.status = 'refuse';
						me.hide();
					}
				},
					{
					border: false,
					width: 80,
					cls: 'video-chat-offer-spinner-left',
					html: '<div class="call-ignore">' +
					'<div class="bubble">' +
					'<div class="circle"></div>' +
					'</div>' +
					'<div class="bubble">' +
					'<div class="circle"></div>' +
					'</div>' +
					'<div class="bubble">' +
					'<div class="circle"></div>' +
					'</div>' +
					'<div class="bubble">' +
					'<div class="circle"></div>' +
					'</div>' +
					'</div>'

				}, {
					border: false,
					id: me.getId()+'-avatar',
					cls: 'video-chat-offer-avatar',
					width: 80,
					height: 80,
					tpl: [
						'<tpl if="Ext6.isEmpty(values.Avatar)"><div class="empty-img"/></tpl>',
						'<tpl if="!Ext6.isEmpty(values.Avatar)"><div class="img" style="background-image: url({Avatar})"/></tpl>'
					],
					data: {
						Avatar: null
					}
				},
					{
					border: false,
					width: 80,
					cls: 'video-chat-offer-spinner-right',
					html: '<div class="call-accept">' +
					'<div class="bubble">' +
					'<div class="circle"></div>' +
					'</div>' +
					'<div class="bubble">' +
					'<div class="circle"></div>' +
					'</div>' +
					'<div class="bubble">' +
					'<div class="circle"></div>' +
					'</div>' +
					'<div class="bubble">' +
					'<div class="circle"></div>' +
					'</div>' +
					'</div>'
				}, {
					xtype: 'button',
					cls: 'video-chat-offer-accept',
					width: 60,
					height: 60,
					handler: function() {
						me.status = 'accept';
						me.hide();
					}
				}]
			}, {
				border: false,
				id: me.getId()+'-user',
				region: 'center',
				cls: 'video-chat-offer-user',
				tpl: '{SurName} {FirName} {SecName}',
				data: {
					SurName: '',
					FirName: '',
					SecName: ''
				}
			},
				{
				border: false,
				id: me.getId()+'-status',
				cls: 'video-chat-offer-status',
				html: 'Звонит'
			}]
		});

		me.callParent(arguments);
	}
});