Ext6.define('videoChat.MessagesPanel', {
	extend: 'Ext6.Panel',
	alias: 'widget.swVideoChatMessagesPanel',
	requires: [
		'videoChat.MessagesView'
	],
	minHeight: 200,

	listeners: {
		show: function() {
			var me = this;
			me.sendFileField.triggers.filebutton.el.setWidth(
				me.sendFileField.button.getWidth()
			);
		}
	},

	setContacts: function(users) {
		var me = this;
		if (!Ext6.isArray(users)) {
			users = [users];
		}
		me.contacts = users;
		me.textField.reset();

		var pmUser_cid_list = me.getContacts().map(function(user) {
			return user.get('id');
		});

		me.mask('Загрузка...');
		me.messagesView.load({
			params: {
				pmUser_cid_list: pmUser_cid_list
			},
			callback: function() {
				me.unmask();
			}
		});
	},

	getContacts: function() {
		return this.contacts;
	},

	sendMessage: function() {
		var me = this;

		var text = me.textField.getValue();
		if (Ext6.isEmpty(text)) {
			return;
		}

		me.textField.reset();

		var pmUser_gid_list = me.getContacts().map(function(user) {
			return user.get('id');
		});

		var message = {
			pmUser_gid_list: Ext6.encode(pmUser_gid_list),
			text: text
		};

		Ext6.Ajax.request({
			url: '/?c=VideoChat&m=sendTextMessage',
			params: message
		});
	},

	sendFile: function() {
		var me = this;

		var pmUser_gid_list = me.getContacts().map(function(user) {
			return user.get('id');
		});

		var message = {
			pmUser_gid_list: Ext6.encode(pmUser_gid_list)
		};

		me.mask('Отправка...');
		me.sendFileForm.submit({
			url: '/?c=VideoChat&m=sendFileMessage',
			params: message,
			success: function(request, response) {
				me.unmask();
			},
			failure: function(request, response) {
				me.unmask();
				if (response.result.Error_Msg) {
					Ext6.Msg.alert(langs('Ошибка'), response.result.Error_Msg);
				}
			}
		});
	},
	
	openFileList: function() {
		var me = this;
		
		getWnd('swVideoChatFileListWindow').show({
			getContacts: me.getContacts.bind(me)
		});
	},

	initComponent: function() {
		var me = this;

		me.contacts = [];
		me.engine = sw.Promed.VideoChat;

		me.messagesView = Ext6.create('videoChat.MessagesView', {
			flex: 1,
			width: '100%',
			getContacts: me.getContacts.bind(me),
			store: Ext6.create('Ext6.data.ChainedStore', {
				source: me.engine.messageStore
			})
		});

		me.textField = Ext6.create('Ext6.form.TextArea', {
			labelAlign: 'top',
			name: 'text',
			width: '100%',
			grow: true,
			growMin: 30,
			growMax: 100,
			growAppend: '-',
			userCls: 'video-chat-message-field',
			emptyText: 'Сообщение...',
			enableKeyEvents: true,
			listeners: {
				keydown: function(field, e) {
					if (e.ctrlKey && e.getKey() == e.ENTER) {
						e.stopEvent();
						me.sendMessage();
					}
				}
			}
		});

		me.sendMessageButton = Ext6.create('Ext6.Button', {
			text: 'Отправить сообщение',
			margin: '0 4 0 0',
			padding: '4 10',
			handler: function() {
				me.sendMessage();
			}
		});

		me.sendFileField = Ext6.create('Ext6.form.File', {
			name: 'FileMessage',
			allowBlank: false,
			buttonOnly: true,
			margin: '0',
			buttonConfig: {
				text: 'Отправить файл',
				ui: 'default-toolbar-small',
				margin: '0 4 0 0',
				padding: '4 10'
			},
			listeners: {
				change: function () {
					me.sendFile();
				}
			}
		});
		
		me.openFileListButton = Ext6.create('Ext6.Button', {
			text: 'Список файлов',
			margin: '0 4 0 0',
			padding: '4 10',
			handler: function() {
				me.openFileList();
			}
		});

		me.sendFileForm = Ext6.create('Ext6.form.Panel', {
			border: false,
			padding: '0',
			width: 130,
			items: [
				me.sendFileField
			]
		});

		Ext6.apply(me, {
			layout: 'vbox',

			items: [
				me.messagesView,
				me.textField
			],
			dockedItems: [{
				xtype: 'toolbar',
				dock: 'bottom',
				border: false,
				userCls: 'video-chat-message-toolbar',
				height: 38,
				items: [
					me.sendMessageButton,
					me.sendFileForm,
					me.openFileListButton
				]
			}]
		});

		me.callParent(arguments);
	}
});