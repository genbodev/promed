Ext6.define('videoChat.ContactSelectWindow', {
	extend: 'base.BaseForm',
	alias: 'widget.swVideoChatContactSelectWindow',
	requires: [
		'videoChat.model.Contact'
	],
	autoShow: false,
	cls: 'arm-window-new swVideoChatWindow',
	title: 'Выбор контакта',
	renderTo: main_center_panel.body.dom,
	width: 420,
	height: 480,
	modal: true,

	select: function(record) {
		var me = this;
		if (!record) record = me.grid.selection;
		if (!record) return;
		me.callback(record);
		me.hide();
	},

	load: function(force) {
		var me = this;
		var store = me.grid.getStore();
		var counter = me.contactCountPanel;

		var params =  {
			query: me.queryField.getValue()
		};

		if (force || !store.lastOptions || Ext6.encode(store.lastOptions.params) != Ext6.encode(params)) {
			store.removeAll();
			counter.refresh();

			store.load({
				params: params,
				callback: function() {
					counter.refresh();
				}
			});
		}
	},

	show: function() {
		var me  = this;

		me.callback = Ext6.emptyFn;

		me.callParent(arguments);

		if (arguments[0] && arguments[0].callback) {
			me.callback = arguments[0].callback;
		}

		me.grid.store.filterBy(function(record) {
			return (
				record.get('Status') == 'online' &&
				!record.get('audiocall') && !record.get('videocall')
			);
		});

		me.load();
	},

	initComponent: function() {
		var me  = this;

		me.engine = sw.Promed.VideoChat;

		me.queryField = Ext6.create('sw.form.QueryField', {
			flex: 1,
			name: 'contact',
			emptyText: 'Поиск',
			query: me.load.bind(me)
		});

		me.contactCountPanel = Ext6.create('Ext6.Panel', {
			border: false,
			style: 'text-align: right;',
			tpl: new Ext6.XTemplate('Найдено: {count} пользователя'),
			html: 'Найдено: 0 пользователя',
			refresh: function() {
				var panel = me.contactCountPanel;
				var count = me.grid.store.count();
				panel.setHtml(panel.tpl.apply({count: count}));
			}
		});

		me.filterPanel = Ext6.create('Ext6.form.Panel', {
			userCls: 'ContactSearchPanel',
			border: false,
			region: 'north',
			bodyPadding: 10,
			defaults: {
				labelWidth: 75,
				anchor: '100%',
				matchFieldWidth: false
			},
			items: [
				me.queryField,
				me.contactCountPanel
			]
		});

		var contactTpl = new Ext6.XTemplate(
			'<div class="contact-cell">',
				'<div class="contact-avatar-panel">',
					'<tpl if="Ext6.isEmpty(values.Avatar)"><div class="contact-avatar empty"></div></tpl>',
					'<tpl if="!Ext6.isEmpty(values.Avatar)"><div class="contact-avatar photo" style="background-image: url({Avatar})"></div></tpl>',
					'<span class="contact-avatar-icon contact-{Status}"></span>',
				'</div>',
				'<div class="contact-text-panel">',
					'<p class="contact-text" style="font: 13px/17px Roboto; color: #000;">{SurName} {FirName} {SecName}</p>',
					'<p class="contact-subtext"style="font: 12px/17px Roboto; color: #999;">{Login}</p>',
				'</div>',
			'</div>'
		);

		var contactRenderer = function(value, meta, record) {
			return contactTpl.apply(record.data);
		};

		me.grid = Ext6.create('Ext6.grid.Panel', {
			border: false,
			region: 'center',
			store: me.engine.contactManager.createStore(),
			columns: [
				{dataIndex: 'Contact', flex: 1, renderer: contactRenderer}
			],
			selModel: {
				mode: 'SINGLE',
				listeners: {
					select: function() {
						me.down('#'+me.getId()+'-select-btn').enable();
					}
				}
			},
			listeners: {
				itemdblclick: function(grid, record) {
					me.select(record);
				}
			}
		});

		Ext6.apply(me, {
			layout: 'border',
			items: [
				me.filterPanel,
				me.grid
			],
			buttons: [
				'->',
				{
					id: me.getId()+'-select-btn',
					cls: 'buttonAccept',
					text: 'Выбрать',
					disabled: true,
					handler: function() {
						me.select();
					}
				}
			]
		});

		me.callParent(arguments);
	}
});