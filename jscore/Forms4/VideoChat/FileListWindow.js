Ext6.define('videoChat.FileListWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.swVideoChatFileListWindow',
	renderTo: main_center_panel.body.dom,
	autoShow: false,
	cls: 'arm-window-new',
	border: 'region',
	title: 'Файлы',
	width: 800,
	height: 500,
	modal: false,
	
	getContact: function(file) {
		var me = this;
		if (me.engine.user.get('id') == file.get('pmUser_sid')) {
			return me.engine.user;
		}
		return me.getContacts().find(function(user) {
			return user.get('id') == file.get('pmUser_sid');
		});
	},
	
	search: function(force) {
		var me = this;
		var store = me.FileGrid.store;
		var params = me.SearchForm.form.getValues();
		
		if (params.fileTypeName == 'null') {
			delete params.fileTypeName;
		}
		
		params.pmUser_cid_list = me.getContacts().map(function(user) {
			return user.get('id');
		});
		
		if (params.query) {
			var users = me.getContacts().concat(me.engine.user);
			
			params.pmUser_sid_list = users.filter(function(user) {
				var fio = [user.get('SurName'), user.get('FirName'), user.get('SecName')].join(' ');
				return fio.slice(0, params.query.length).toLowerCase() == params.query.toLowerCase();
			}).map(function(user) {
				return user.get('id');
			});
		}

		if (force || Ext6.encode(store.lastOptions.params) != Ext6.encode(params)) {
			store.load({params: params});
		}
	},
	
	show: function() {
		var me = this;
		
		me.FileGrid.store.removeAll();
		me.SearchForm.form.reset();
		
		me.callParent(arguments);
		
		me.getContacts = arguments[0].getContacts;
		
		me.TypeComboField.store.load();
		
		me.QueryField.focus();
		
		me.search(true);
	},
	
	initComponent: function() {
		var me = this;
		
		me.engine = sw.Promed.VideoChat;
		
		me.DateRangeField = Ext6.create('swDateRangeField', {
			name: 'dateRange',
			width: 205,
			listeners: {
				change: function(field, value) {
					var begDate, endDate;
					var range = value.split(' — ');
					
					if (range[0]) {
						begDate = Ext6.Date.parse(range[0], 'd.m.Y');
					}
					if (range[1]) {
						endDate = Ext6.Date.parse(range[1], 'd.m.Y');
					}
					
					if (begDate && (range.length == 1 || endDate)) {
						me.search();
					}
				}
			}
		});
		
		me.TypeComboField = Ext6.create('swBaseCombobox', {
			store: Ext6.create('Ext6.data.Store', {
				fields: [
					{name: 'name'}
				],
				proxy: {
					type: 'ajax',
					url: '/?c=VideoChat&m=loadFileTypeList',
					reader: {type: 'json'}
				}
			}),
			listeners: {
				change: function(field, value) {
					field.store.filters.removeAll();
					
					if (Ext6.isEmpty(value)) {
						field.select(field.store.getAt(0));
						me.search();
					} else {
						field.store.filterBy(function(record) {
							if (Ext6.isEmpty(record.get('name'))) return true;
							return record.get('name').slice(0, value.length).toLowerCase() == value.toLowerCase();
						});
						field.expand();
					}
				},
				select: function(field, record) {
					field.store.filters.removeAll();
					me.search();
				}
			},
			allowBlank: true,
			matchFieldWidth: false,
			valueField: 'name',
			displayField: 'name',
			name: 'fileTypeName',
			emptyText: 'Тип файла',
			width: 200
		});
		
		me.QueryField = Ext6.create('sw.form.QueryField', {
			name: 'query',
			emptyText: 'Поиск',
			query: me.search.bind(me),
			margin: 0,
			flex: 1
		});
		
		me.SearchForm = Ext6.create('Ext6.form.Panel', {
			region: 'north',
			layout: 'hbox',
			border: false,
			bodyPadding: 10,
			bodyStyle: {
				background: '#eee'
			},
			defaults: {
				style: 'margin-right: 10px;',
				height: 32
			},
			trackResetOnLoad: false,
			items: [
				me.DateRangeField,
				me.TypeComboField,
				me.QueryField
			]
		});
		
		var senderNameTpl = new Ext6.XTemplate('{SurName} {FirName} {SecName}');
		var senderRenderer = function(value, meta, file) {
			var user = me.getContact(file);
			var name = senderNameTpl.apply(user.data);
			meta.tdAttr += ' data-qtip="'+name+'"';
			return name;
		};
		
		var linkRenderer = function(value, meta, file) {
			meta.tdAttr += ' data-qtip="' + file.get('file_name') + '"';
			return value;
		};
		
		me.FileGrid = Ext6.create('Ext6.grid.Panel', {
			region: 'center',
			cls: 'grid-common',
			border: false,
			store: Ext6.create('Ext6.data.Store', {
				autoLoad: false,
				fields: [
					{name: 'id', type: 'int'},
					{name: 'file_name', type: 'string'},
					{name: 'file_type_mime', type: 'string'},
					{name: 'file_type_name', type: 'string'},
					{name: 'file_link', type: 'string'},
					{name: 'dt', type: 'date'}
				],
				proxy: {
					type: 'ajax',
					url: '/?c=VideoChat&m=loadFileList',
					reader: {type: 'json'}
				}
			}),
			columns: [
				{dataIndex: 'dt', header: 'Дата/Время', type: 'date', formatter: 'date("d.m.Y H:i:s")', width: 140},
				{dataIndex: 'pmUser_sid', header: 'Отправитель', renderer: senderRenderer, width: 250},
				{dataIndex: 'file_type_name', header: 'Тип', type: 'string', width: 110},
				{dataIndex: 'file_link', header: 'Ссылка', renderer: linkRenderer, flex: 1}
			]
		});
		
		Ext6.apply(me, {
			items: [
				me.SearchForm,
				me.FileGrid
			]
		});
		
		me.callParent(arguments);
	}
});