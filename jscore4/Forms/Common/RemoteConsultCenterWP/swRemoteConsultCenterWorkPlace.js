/*
 * АРМ Центра удалённой консультации
*/

Ext.define('common.RemoteConsultCenterWP.swRemoteConsultCenterWorkPlace', {
    extend: 'Ext.window.Window',
	alias: 'widget.swRemoteConsultCenterWorkPlace',
    autoShow: true,
	maximized: true,
	refId : 'RemoteConsultCenter',
	id:  'RemoteConsultCenter',
	renderTo: Ext.getCmp('inPanel').body,
	constrain: true,
	closable: true,
	layout: {
        type: 'fit'
    },
	
    initComponent: function() {
        var window = this;
		
		this.GridPanel = Ext.create('Ext.grid.Panel',{
			datePickerRange: null,
			getGroupName: function(id, rows, count){
				var emptyGroup = rows[0].data.RemoteConsultCenterResearch_id,
					title = '',
					numrecords = 0;

				if (emptyGroup==0 && count==1){
					numrecords = ' (нет записей)';
				}
				else {
					numrecords =  count > 1 ? ' ('+count+' записей)' : ' ('+count+" запись)";;
				}
				switch(rows[0].data.RemoteConsultCenterResearch_status){
					case 0: 
						{title = 'Ожидающие обслуживания '+numrecords; break;}
					case 1: {title = 'Обслуженные'+numrecords; break;}
//					default: {title = 'Undefined Group '+numrecords; break;}
				}			
				return title
			},
			region: 'center',
			autoScroll: true,
			stripeRows: true,
			refId: 'RemoteConsultCenter_Grid',
			features: [{
				ftype: 'grouping',
				groupHeaderTpl: '{[this.owner.grid.getGroupName(values.name, values.rows, values.rows.length)]}',
				hideGroupedHeader: false,
				startCollapsed: true
			}],
			viewConfig: {
				loadingText: 'Загрузка'
			},
			listeners:{
				celldblclick: function(cmp, td, cellIndex, record, tr, rowIndex, e, eOpts){
					//actionsToolbar.down('button[itemId=showCard]').fireHandler()
				}
			},

			store: new Ext.data.Store({
				autoLoad: false,
				storeId: 'RemoteConsultCenter_Grid_Store',
				groupField: 'RemoteConsultCenterResearch_status',
				fields: [
					{name: 'RemoteConsultCenterResearch_id', type: 'int'},
					{name: 'RemoteConsultCenterResearch_status', type: 'int'},
					{name: 'EvnDirection_setDT',type: 'string'},
					{name: 'EvnDirection_Num', type: 'string'},
					{name: 'Person_FIO', type: 'string'},
					{name: 'UslugaComplex_Name', type: 'string'},
				],
				proxy: {
					limitParam: undefined,
					startParam: undefined,
					paramName: undefined,
					pageParam: undefined,
					type: 'ajax',
					url: '/?c=EvnFuncRequest&m=loadRemoteConsultCenterResearchList',
					reader: {
						type: 'json'
					},
					actionMethods: {
						create : 'POST',
						read   : 'POST',
						update : 'POST',
						destroy: 'POST'
					}
				},
				listeners: {
					load: function(me, records, successful, eOpts){
						for (var j=0; j<2; j++) {
							if(!me.getGroups(j) || (('length' in me.getGroups(j))&&(me.getGroups(j).length == 0))){
									me.add({
										RemoteConsultCenterResearch_id: 0,
										RemoteConsultCenterResearch_status: j
									});
							}
						}
					}
				}

			}),
			columns: [
				{dataIndex: 'RemoteConsultCenterResearch_id',  text: 'ID', key: true, hidden: true, hideable: false},
				{dataIndex: 'RemoteConsultCenterResearch_status',  hidden: true, hideable: false},
				{dataIndex: 'EvnDirection_setDT', /*dateFormat: 'd.m.Y',*/ header: 'Дата направления', width: 120},
				{dataIndex: 'EvnDirection_Num', header: 'Номер направления', width: 160},
				{dataIndex: 'Person_FIO', header: 'ФИО пациента',  width: 320},
				{dataIndex: 'UslugaComplex_Name', header: 'Список услуг ', width: 420},
				
			],
			
			dockedItems: [
			{
				xtype: 'toolbar',
				items: [{
					text:'Обновить',
					tooltip:'Обновить список заявок',
					iconCls:'refresh16',
					handler: function() {
						this.refreshGrid();
					}.bind(this)
				}, {
					text:'Обслужить заявку',
					tooltip:'Обслужить выбранную заявку',
					iconCls:'option',
					handler: function() {
						this.serveRequest();
					}.bind(this)
				}]
			}]

			
		})
		var parentObject = this;
		this.datePickerRange = Ext.create('sw.datePickerRange',{
		
			reloadParentGrid: function(){
				
				var params = {};

				if (this.dateFields){
					params['begDate'] = Ext.Date.format(this.dateFrom, 'd.m.Y'),
					params['endDate'] =  Ext.Date.format(this.dateTo, 'd.m.Y')
				}
				parentObject.refreshGrid({params:params});
			}
		});
				
		var datesToolbar = Ext.create('Ext.toolbar.Toolbar', {
			region: 'north',
			//height: 29,
			items: [
				Ext.create('sw.datePrevDay'),
				this.datePickerRange,
				Ext.create('sw.dateNextDay'),
				Ext.create('sw.dateCurrentDay'),
				Ext.create('sw.dateCurrentWeek'),
				Ext.create('sw.dateCurrentMonth')              
			]
		})
		
//		'Ext.panel.Panel',
//		layout: {
//			type: 'border'
//		},
		
		Ext.applyIf(window, {
			items: [
				{
//					xtype: 'BaseForm',
					xtype: 'panel',
					id: 'RemoteConsultCenter_BaseForm',
					layout: {
						type: 'border'
					},
                    items: [
						datesToolbar,
						this.GridPanel
					]
						
				}
			]
		});
		window.callParent(arguments);
	},
	
	show: function() {
		this.callParent(arguments);
		
		this.refreshGrid();
		
		
	},
	
	refreshGrid: function(options) {
		
		var opts = {};
		
		opts.params = !!options && (typeof options.params == 'object') && options.params || {};
		
		opts.callback = function(records, operation, success) {
			if (!!options && (typeof options.callback == 'object')) {
				options.callback(records, operation, success);
			}
		}
		
		
//		if (!!options && typeof options.params == 'object') {
//			opts.params = {};
//		}
		if (!opts.params.hasOwnProperty('begDate')) {
			var dateFrom = (this.datePickerRange && this.datePickerRange.dateFrom)?this.datePickerRange.dateFrom:new Date(Date.now());
			opts.params.begDate = Ext.Date.format(dateFrom, 'd.m.Y');
		}
		if (!opts.params.hasOwnProperty('endDate')) {
			var dateTo = (this.datePickerRange && this.datePickerRange.dateTo)?this.datePickerRange.dateTo:new Date(Date.now());
			opts.params.endDate = Ext.Date.format(dateTo, 'd.m.Y');
		}
		
		this.GridPanel.getStore().removeAll();
		this.GridPanel.getStore().load(opts)
	},
	
	serveRequest: function() {
		getWnd('swRemoteConsultCenterServeRequestWindow').show();
	}

});

