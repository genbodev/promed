Ext6.define('videoChat.MessageView', {
	extend: 'Ext6.Component',
	alias: 'widget.swVideoChatMessageView',
	cls: 'video-chat-message-view',

	message: null,
	contact: null,
	onLoadImg: Ext6.emptyFn,

	tpl: new Ext6.XTemplate([
		'<div class="container {[this.userClass(values.pmUser_sid)]}">',
			'<div class="avatar {[this.avatarClass(values.pmUser_sid)]}" style="{[this.avatar(values.pmUser_sid)]}"></div>',
			'<div class="data">',
				'<p class="title">',
					'{[this.time(values.dt)]} {[this.date(values.dt)]} {[this.fullName(values.pmUser_sid)]}',
				'</p>',
				'<p class="text">',
					'{[this.prepareText(values.text)]}',
				'</p>',
			'</div>',
		'</div>'
	]),

	updateMessage: function(message) {
		var me = this;
		me.message = message;
		me.setData(message.data);
	},

	afterRender: function() {
		var me = this;
		var img = me.el.down('.text img');
		if (img) img.on('load', me.onLoadImg);
	},

	initComponent: function() {
		var me = this;

		me.engine = sw.Promed.VideoChat;
		me.data = me.message.data;

		me.callParent(arguments);

		Ext6.apply(me.tpl, {
			date: function(dt) {
				return Ext6.Date.format(dt, 'd.m.Y');
			},
			time: function(dt) {
				return Ext6.Date.format(dt, 'H:i:s');
			},
			prepareText: function(text) {
				return text.replace(/\n/g, '<br>');
			},
			avatarClass: function(id) {
				var user = me.engine.user;
				if (user.get('id') != id && me.contact.get('id') == id) {
					user = me.contact;
				}
				return Ext.isEmpty(user.get('Avatar'))?'empty':'photo';
			},
			avatar: function(id) {
				var user = me.engine.user;
				if (user.get('id') != id && me.contact.get('id') == id) {
					user = me.contact;
				}
				if (Ext.isEmpty(user.get('Avatar'))) {
					return '';
				}
				return 'background-image: url('+user.get('Avatar')+')';
			},
			fullName: function(id) {
				var user = me.engine.user;
				if (user.get('id') != id && me.contact.get('id') == id) {
					user = me.contact;
				}
				return [user.get('SurName'), user.get('FirName'), user.get('SecName')].join(' ');
			},
			userClass: function(pmUser_sid) {
				return (me.engine.user.get('id') == pmUser_sid?'self':'other');
			}
		});
	}
});