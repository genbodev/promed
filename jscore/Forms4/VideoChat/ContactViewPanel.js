Ext6.define('videoChat.ContactViewPanel', {
	extend: 'Ext6.Component',
	alias: 'widget.swVideoChatContactViewPanel',
	cls: 'video-chat-contact-view',

	needBackground: false,
	contact: null,
	stream: null,

	avatarTpl: [
		'<div id="{id}-contact-avatar" class="video-chat-contact-avatar">',
			'<tpl if="Ext6.isEmpty(values.Avatar)"><div class="empty-img"></div></tpl>',
			'<tpl if="!Ext6.isEmpty(values.Avatar)"><div class="img" style="background-image: url({Avatar})"></div></tpl>',
		'</div>',
		'<div id="{id}-contact-user" class="video-chat-contact-user">',
			'{SurName} {FirName} {SecName}',
		'</div>',
		'<div id="remote-video-block-{userKey}" class="remote-video-block" style="display: none;">',
			'<video id="remote-video-{userKey}" autoplay class="remote-video"></video>',
		'</div>',
	],

	videoTpl: [
		'<div id="remote-video-block-{userKey}" class="remote-video-block">',
			'<video id="remote-video-{userKey}" autoplay class="remote-video"></video>',
		'</div>'
	],

	defaultData: {
		Avatar: null,
		SurName: '',
		FirName: '',
		SecName: '',
		userKey: ''
	},

	setContact: function(contact) {
		var me = this;

		if (!contact) {
			me.contact = null;
			me.setData(me.defaultData);
			return;
		}

		me.contact = contact;

		var data = {};
		Object.keys(me.defaultData).forEach(function(field) {
			if (contact.get(field) != undefined) {
				data[field] = contact.get(field);
			} else {
				data[field] = me.defaultData[field];
			}
		});
		data.userKey = contact.get('id');
		data.id = me.getId();

		var tpl = null;
		
		if (contact.get('screenmuted') || contact.get('videomuted') || !contact.get('videocall')) {
			tpl = me.avatarTpl;
		} else {
			tpl = me.videoTpl;
		}

		me.setHtml(tpl.apply(data));
		me.data = data;
	},

	setStream: function(stream) {
		var me = this;

		me.stream = stream;

		if (me.rendered) {
			var video = Ext6.get('remote-video-'+me.data.userKey);

			if (video && video.dom) {
				video.dom.srcObject = stream || null;
			}
		}
	},

	afterRender: function() {
		var me = this;

		me.setWidth(null);
		me.setHeight(null);

		if (me.stream) {
			me.setStream(me.stream);
		}
	},

	initComponent: function() {
		var me = this;

		me.avatarTpl = new Ext6.XTemplate(me.avatarTpl);
		me.videoTpl = new Ext6.XTemplate(me.videoTpl);

		me.data = Ext6.apply({}, me.defaultData);

		if (me.contact) {
			me.setContact(me.contact);
		}
		
		if (me.needBackground) {
			me.setStyle('background', 'rgba(0, 0, 0, 0) url("../img/icons/videochat/callContactWait.png") no-repeat scroll center center');
		}

		me.callParent(arguments);
	}
});