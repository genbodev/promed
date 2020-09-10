Ext6.define('common.PolkaWP.RemoteMonitoring.InviteHistoryWindow', {
	addCodeRefresh: Ext.emptyFn,
	closeToolText: 'Закрыть',

	alias: 'widget.swRemoteMonitoringInviteHistoryWindow',
	title: 'История включения в программу',
	extend: 'base.BaseForm',
	maximized: false,
	width: 600,
	height: 400,
	layout: 'border',
	
	modal: true,

	findWindow: false,
	closable: true,
	cls: 'arm-window-new arm-window-new-without-padding invite-history-remote-monitoring-window',
	renderTo: Ext6.getBody(),

	autoScroll: true,
	autoShow: false,
	closable: true,
	closeAction: 'hide',
	draggable: true,

	show: function() {
		var me = this;
		me.callParent(arguments);
		
		if(!arguments[0] || !arguments[0]['PersonLabel_id']) {
			me.errorInParams();
			return false;
		}
		me.PersonLabel_id = arguments[0]['PersonLabel_id'];
		me.PersonFio = arguments[0]['PersonFio'] ? arguments[0]['PersonFio'] : '';
		me.setTitle('История включения в программу ' + me.PersonFio);
		me.grid.store.load({params:{PersonLabel_id: me.PersonLabel_id}});
	},
	initComponent: function() {
		var me = this;
		
		me.gridcolumns
		
		me.grid = new Ext6.grid.Panel({
			xtype: 'grid',
			cls: 'remote-monitor-history-grid',
			flex: 100,
			region: 'center',
			border: true,
			columns: [
				{	header: langs('Дата'), dataIndex: 'eventDate', type: 'string', width: 100, formatter: 'date("d.m.Y")' },
				{	header: langs('Событие'), dataIndex: 'eventName', type: 'string', flex: 1,
					renderer: function(value, metaData, record) {
						switch(record.get('eventType')) {
							case 0: //приглашения
								if(record.get('statusId')==1) {
									return 'Отправлено приглашение';
								} else {
									if(record.get('statusId')==2) return 'Изменен статус приглашения: приглашение принято';
									else return 'Изменен статус приглашения: приглашение '+(record.get('statusId')==1 ? 'принято' : 'отклонено');
								}
								break;
							case 1: //создана карта наблюдения
								return 'Пациент включен в программу дистанционного мониторинга';
								break;
							case 2: //закрыта карта наблюдения
								return 'Пациент выбыл из программы дистанционного мониторинга';
								break;
						}
					},
					sorter: function(a, b) {
						if(a.get('eventType')!=b.get('eventType'))
							return a.get('eventType') < b.get('eventType') ? 1 : (a.get('eventType') > b.get('eventType') ? -1 : 0);
						else return a.get('statusId') < b.get('statusId') ? 1 : (a.get('statusId') > b.get('statusId') ? -1 : 0);
					}
				}
			],
			store: new Ext6.data.Store({
				autoLoad: false,
				proxy: {
					type: 'ajax',
					actionMethods:  {create: "POST", read: "POST", update: "POST", destroy: "POST"},
					url: '/?c=PersonDisp&m=loadLabelInviteHistory',
					reader: {
						type: 'json',
						rootProperty: 'data',
						totalProperty: 'totalCount'
					}
				},
				sorters: [{
						property: 'eventDate',
						direction: 'DESC'
					}
				],
				listeners: {
					load: function(data) {
						//~ if(me.grid.getStore().getCount()>0) //TAG: загрузка первой записи
							//~ me.grid.setSelection(me.grid.getStore().getAt(0));
						//~ me.loadPersonLabelCounts(); //количество записей во вкладках
						//~ me.queryById('tabs').enable();
					}
				}
			})
		});

		Ext6.apply(me, {
			items: [
				me.grid
			],
			border: false,
			buttons:
			[ '->'
			, {
				userCls:'buttonCancel buttonPoupup',
				text: langs('Закрыть'),
				margin: 0,
				handler: function() {
					me.hide();
				}
			}]
		});

		this.callParent(arguments);
	}
});