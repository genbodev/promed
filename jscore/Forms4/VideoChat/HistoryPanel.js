Ext6.define('videoChat.HistoryPanel', {
	extend: 'Ext6.Panel',
	alias: 'widget.swVideoChatHistoryPanel',
	requires: [
		'videoChat.model.Call'
	],
	layout: 'border',
	
	listeners: {
		show: function() {
			var me = this;
			
			me.grid.store.load({
				params: {
					pmUser_id: sw.Promed.VideoChat.user.get('id')
				}
			});
		}
	},
	
	initComponent: function() {
		var me = this;
		
		me.engine = sw.Promed.VideoChat;
		
		var selfUserId = sw.Promed.VideoChat.user.get('id');
		
		var contactsRenderer = function(value, meta, record) {
			meta.classes.push('contact');
			
			var ids = value.filter(userId => userId != selfUserId);
			
			var contacts = ids.map(function(id) {
				var user = me.engine.contactManager.getRecord(id, true);
				if (!user) return '<span class="replace-user-' + id + '">' + id + '</span>';
				return user.get('FullName');
			});
			
			return contacts.join('<br/>');
		};
		
		var recordsRenderer = function(records, meta, record) {
			if (!Ext6.isArray(records)) {
				return '';
			}
			
			return '<div style="display: flex; flex-wrap: wrap;">' + records.map(function(url) {
				return '<a style="margin-right: 5px;" target="_blank" href="' + url + '">' + url.split('/').pop() + '</a>';
			}).join('') + '</div>';
		};
		
		me.grid = Ext6.create('Ext6.grid.Panel', {
			region: 'center',
			cls: 'grid-common',
			border: false,
			columns: [
				{dataIndex: 'begDT', type: 'date', formatter: 'date("d.m.Y H:i:s")', width: 140, header: 'Дата/Время'},
				{dataIndex: 'duration', type: 'string', width: 150, header: 'Продолжительность'},
				{dataIndex: 'callTypeName', type: 'string', width: 150, header: 'Тип'},
				{dataIndex: 'pmUser_ids', type: 'string', width: 250, header: 'Собеседники', renderer: contactsRenderer},
				{dataIndex: 'records', flex: 1, header: 'Записи', renderer: recordsRenderer}
			],
			store: Ext6.create('Ext6.data.Store', {
				model: 'videoChat.model.Call',
				proxy: {
					type: 'ajax',
					url: '/?c=VideoChat&m=loadCallList',
					reader: {type: 'json'}
				},
				listeners: {
					load: function(store, records) {
						var map = {};
						records.forEach(function(record) {
							record.get('pmUser_ids').forEach(function(id) {
								id = Number(id);
								if (map[id]) {
									map[id].push(record);
								} else {
									map[id] = [record];
								}
							});
						});
						
						Object.keys(map).forEach(function(id) {
							if (me.engine.contactManager.getRecord(id, true)) return;
							
							me.engine.contactManager.getRecord(id).then(function(user) {
								me.grid.el.query('span.replace-user-' + id, false).forEach(function(el) {
									el.setHtml(user.get('FullName'));
								});
							});
						});
					}
				}
			})
		});
		
		Ext6.apply(me, {
			items: [
				me.grid
			]
		});
		
		me.callParent(arguments);
	}
});